const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const apps = process.env.CMS_SESSION_APP_ROOT || path.join(__dirname, '../davvag-core/localhost/apps');

function deferred() {
    return { then(fn) { this.resolve = fn; return this; }, success(fn) { this.resolve = fn; return this; }, error(fn) { this.reject = fn; return this; } };
}
function storage() {
    return { getItem(key) { return this[key] ?? null; }, setItem(key, value) { this[key] = value; }, removeItem(key) { delete this[key]; }, clear() { for (const key of Object.keys(this)) if (typeof this[key] !== 'function') delete this[key]; } };
}
function harness() {
    const requests = [], events = {}, downloaded = [], mounted = [];
    const host = { length: 1, content: '', html(value) { this.content = value; return this; }, empty() { this.content = ''; }, attr() { return 'host'; } };
    const api = { services: { Site: () => deferred(), Page: () => deferred() } };
    let component;
    const document = { cookie: '', addEventListener() {}, documentElement: { contains: () => true } };
    const window = {
        location: { hash: '#/app/private', hostname: 'localhost', pathname: '/', href: '#/app/userapp/login' },
        addEventListener(name, fn) { (events[name] ||= []).push(fn); },
        dispatchEvent(event) { for (const fn of events[event.type] || []) fn(event); }, scrollTo() {}
    };
    const context = {
        window, document, localStorage: storage(), sessionStorage: storage(), location: window.location,
        CustomEvent: function(type, options) { this.type = type; this.detail = options?.detail; },
        console, URLSearchParams, unescape,
        $: () => ({ ...host, prop() {}, toggle() {} }),
        WEBDOCK: {
            component: () => ({ register(fn) { component = { getComponent: () => api }; fn(component); } }),
            callRest(url) { const request = deferred(); requests.push({ url, request }); return request; },
            componentManager: {
                downloadAppDescriptor(id, done) { downloaded.push(id); done({ configuration: { webdock: { startupComponent: 'main' } }, components: { main: {} } }); },
                downloadComponents(id, descriptor, done) { done(); },
                getOnDemand(id, descriptor, name, done) { done([{ object: { type: 'mainView', view: '<p>private app</p>' } }], descriptor, { onReady() { mounted.push(id); } }); }
            }
        }
    };
    context.$ = () => host;
    context.Vue = function() {};
    context.Vue.nextTick = () => {};
    vm.createContext(context);
    function load(relative, overrides = {}) {
        vm.runInContext(fs.readFileSync(path.join(apps, relative), 'utf8'), context);
        Object.assign(component, overrides);
        return component;
    }
    const dock = load('davvag-cms-v7/components/dock-shell/script.js');
    dock.vue.onReady({}, host);
    return { context, window, document, host, requests, events, downloaded, mounted, load, dock };
}

test('login refresh replaces the guest app list and the protected route mounts without a page reload', () => {
    const h = harness();
    const legacy = h.load('davvag-cms-v7/partials/partial-app/script.js');
    const render = () => legacy.vue.onReady({}, h.host, { routeParams: { appName: 'private' } });
    render();
    h.requests[0].request.resolve({ success: true, result: { userapp: {} } });
    assert.match(h.host.content, /do not have permission/);
    h.document.cookie = 'securityToken=secret-authenticated-token';
    let ready = false;
    h.window.CMSV7.refreshSession(loaded => { ready = loaded; });
    assert.equal(ready, false);
    assert.match(h.requests[1].url, /session_revision=/);
    assert.ok(!h.requests[1].url.includes('secret-authenticated-token'));
    h.requests[1].request.resolve({ success: true, result: { userapp: {}, private: {} } });
    assert.equal(ready, true);
    render();
    assert.deepEqual(h.mounted, ['private']);
    assert.equal(h.requests.length, 2);
    assert.match(h.host.content, /private app/);
});

test('a pre-login catalog response cannot overwrite the authenticated list or pending callbacks', () => {
    const h = harness();
    let pending;
    h.window.CMSV7.getApps(apps => { pending = apps; });
    h.document.cookie = 'securityToken=new-session';
    h.window.CMSV7.refreshSession(() => {});
    h.requests[1].request.resolve({ success: true, result: { private: {} } });
    h.requests[0].request.resolve({ success: true, result: { userapp: {} } });
    assert.deepEqual(Object.keys(pending), ['private']);
    let cached;
    h.window.CMSV7.getApps(apps => { cached = apps; });
    assert.deepEqual(Object.keys(cached), ['private']);
    assert.equal(h.requests.length, 2);
});

test('cookie changes and logout invalidate permissions even when no explicit refresh was sent', () => {
    const h = harness();
    h.document.cookie = 'securityToken=first-user';
    h.window.CMSV7.getApps(() => {});
    h.requests[0].request.resolve({ success: true, result: { private: {} } });
    h.document.cookie = '';
    let guest;
    h.window.CMSV7.getApps(apps => { guest = apps; });
    assert.equal(h.requests.length, 2);
    h.requests[1].request.resolve({ success: true, result: { userapp: {} } });
    assert.deepEqual(Object.keys(guest), ['userapp']);
});

test('a session change during an in-flight request fetches new permissions before releasing callers', () => {
    const h = harness();
    let completed = false;
    h.window.CMSV7.getApps(() => { completed = true; });
    h.document.cookie = 'securityToken=changed';
    h.requests[0].request.resolve({ success: true, result: { userapp: {} } });
    assert.equal(completed, false);
    assert.equal(h.requests.length, 2);
    h.requests[1].request.resolve({ success: true, result: { private: {} } });
    assert.equal(completed, true);
});

test('failed refresh reports failure and the next catalog request retries', () => {
    const h = harness();
    let loaded;
    h.window.CMSV7.refreshSession(success => { loaded = success; });
    h.requests[0].request.reject();
    assert.equal(loaded, false);
    let recovered;
    h.window.CMSV7.getApps(apps => { recovered = apps; });
    h.requests[1].request.resolve({ success: true, result: { private: {} } });
    assert.deepEqual(Object.keys(recovered), ['private']);
});

function loginHarness(componentName) {
    const h = harness();
    const login = deferred(), state = deferred();
    const services = { login: () => login, LoginState: () => state };
    const refreshes = [];
    h.window.CMSV7.refreshSession = done => refreshes.push(done);
    h.context.$ = () => ({ prop() {} });
    const component = h.load('userapp/components/' + componentName + '/script.js', {
        getComponent: () => ({ services }), getShellComponent: () => ({ getInputData: () => ({}) })
    });
    component.vue.onReady(component.vue.data);
    return { ...h, component, login, state, refreshes };
}

test('password login refreshes access before resuming the marketplace route and stores the fresh profile', () => {
    const h = loginHarness('login-form');
    h.context.sessionStorage.setItem('lmp_signin_return', '#/app/lesson-market-place/package?slug=bible');
    h.component.vue.methods.login();
    h.login.resolve({ success: true, result: { token: 'signed-in', profile: { id: 298, name: 'Fresh profile' } } });
    assert.equal(h.window.location.href, '#/app/userapp/login');
    assert.equal(h.component.vue.data.isBusy, true);
    assert.equal(JSON.parse(h.context.localStorage.profile).id, 298);
    h.refreshes[0](true);
    assert.equal(h.window.location.href, '/#/app/lesson-market-place/package?slug=bible');
    assert.equal(h.context.sessionStorage.getItem('lmp_signin_return'), null);
});

for (const name of ['login-form', 'login-switcher']) {
    test(name + ' waits for session refresh before its existing-session redirect', () => {
        const h = loginHarness(name);
        h.state.resolve({ success: true, result: { token: 'existing', profile: { id: 298 } } });
        assert.equal(h.window.location.href, '#/app/userapp/login');
        assert.equal(h.refreshes.length, 1);
        h.refreshes[0](true);
        assert.equal(h.window.location.href, '#/app/userapp/profile');
    });
}

test('password login stays on the form with a retry message when permission refresh fails', () => {
    const h = loginHarness('login-form');
    h.component.vue.methods.login();
    h.login.resolve({ success: true, result: { token: 'signed-in' } });
    h.refreshes[0](false);
    assert.equal(h.window.location.href, '#/app/userapp/login');
    assert.match(h.component.vue.data.submitErrors[0], /app access could not be refreshed/);
    assert.equal(h.component.vue.data.isBusy, false);
});

test('failed credentials do not trigger a session refresh or navigate', () => {
    const h = loginHarness('login-form');
    h.component.vue.methods.login();
    h.login.resolve({ success: false, result: 'Invalid credentials.' });
    assert.equal(h.refreshes.length, 0);
    assert.equal(h.window.location.href, '#/app/userapp/login');
});

test('the legacy route refreshes its own catalog after login when running without CMS Dock', () => {
    const h = harness();
    delete h.window.CMSV7;
    const legacy = h.load('davvag-cms-v7/partials/partial-app/script.js');
    const render = () => legacy.vue.onReady({}, h.host, { routeParams: { appName: 'private' } });
    render();
    h.requests[0].request.resolve({ success: true, result: { userapp: {} } });
    assert.match(h.host.content, /do not have permission/);
    h.document.cookie = 'securityToken=logged-in';
    render();
    h.requests[1].request.resolve({ success: true, result: { private: {} } });
    assert.deepEqual(h.mounted, ['private']);
});

test('navigation refresh replaces the session and rejects late session and notification responses', () => {
    const h = harness();
    const sessions = [], launchers = [], notifications = [];
    const nav = h.load('davvag-cms-v7/components/nav-bar/script.js', {
        getComponent: () => ({ services: {
            Session() { const request = deferred(); sessions.push(request); return request; },
            Launchers() { const request = deferred(); launchers.push(request); return request; },
            Notification() { const request = deferred(); notifications.push(request); return request; }
        } })
    });
    h.document.cookie = 'securityToken=first-session';
    nav.vue.onReady();
    sessions[0].resolve({ result: { userid: 'first', role: 'web_user' } });
    assert.equal(launchers.length, 1);
    assert.equal(notifications.length, 1);
    h.document.cookie = 'securityToken=second-session';
    h.window.dispatchEvent({ type: 'cms-v7-session-changed' });
    h.window.dispatchEvent({ type: 'cms-v7-session-changed' });
    sessions[2].resolve({ result: { userid: 'second', role: 'teacher' } });
    sessions[1].resolve({ result: { userid: 'stale', role: 'web_user' } });
    notifications[1].resolve({ result: [{ id: 2 }] });
    notifications[0].resolve({ result: [{ id: 1 }] });
    assert.equal(nav.vue.data.userData.userid, 'second');
    assert.ok(nav.vue.data.userAccess.includes('teacher'));
    assert.equal(nav.vue.data.Notify[0].id, 2);
    assert.equal(launchers.length, 2);
});
