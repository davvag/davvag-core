// Run: node --test tests/cms-html-embeds.test.cjs
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const apps = process.env.CMS_EMBED_APP_ROOT || path.join(__dirname, '../davvag-core/localhost/apps');

function harness() {
    const nodes = [];
    const tasks = [];
    const loaded = [];
    const ready = [];
    const versions = [];
    const catalogRequests = [];
    const descriptors = {allowed: {components: {one: {}, two: {}}, description: {version: 'fresh'}}};
    function matches(n, selector) {
        return [...selector.matchAll(/\[([^\]]+)\]/g)].every(m => n.attrs[m[1]] !== undefined);
    }
    const node = (app, component) => {
        const result = {attrs: {'webdock-app': app, 'webdock-component': component}, children: [], connected: true, html: ''};
        result.querySelector = selector => result.children.find(n => matches(n, selector));
        nodes.push(result);
        return result;
    };
    function $(value) {
        if (value && value.jquery) return value;
        const items = typeof value === 'string' ? nodes.filter(n => n.connected && '#' + n.attrs.id === value) : (Array.isArray(value) ? value : [value]).filter(Boolean);
        return {
            jquery: true, length: items.length,
            find(selector) {return $(items.flatMap(n => n.children).filter(n => matches(n, selector)));},
            each(fn) {items.forEach((n, i) => fn.call(n, i, n)); return this;},
            attr(key, value) {if (value === undefined) return items[0]?.attrs[key]; items.forEach(n => n.attrs[key] = value); return this;},
            html(value) {if (value === undefined) return items[0]?.html; items.forEach(n => n.html = value); return this;},
            empty() {return this.html('');},
            append(value) {items.forEach(n => n.html += value); return this;}
        };
    }
    const permitted = {'allowed': {version: 'old'}};
    const catalog = {value: permitted, success: true};
    let shellLookups = 0;
    const exported = {
        getShellComponent(name) {
            shellLookups++;
            assert.equal(name, 'left-menu');
            return {getApps(cb) {tasks.push(() => cb(permitted));}};
        }
    };
    const context = {
        $, console, window: {},
        document: {getElementById(id) {return nodes.find(n => n.connected && n.attrs.id === id);}, documentElement: {contains: n => n.connected}},
        Vue: function(options) {this.$destroy = () => {};},
        WEBDOCK: {
            component() {return {register(fn) {fn(exported);}};},
            callRest(url) {
                catalogRequests.push(url);
                const request = {
                    success(cb) {tasks.push(() => cb({success: catalog.success, result: catalog.value})); return request;},
                    error() {return request;}
                };
                return request;
            },
            componentManager: {
                downloadAppDescriptor(app, cb) {
                    loaded.push(app);
                    tasks.push(() => cb(descriptors[app]));
                },
                downloadComponents(app, descriptor, cb, version) {
                    versions.push(version);
                    tasks.push(cb);
                },
                getOnDemand(app, descriptor, component, cb, version) {
                    versions.push(version);
                    tasks.push(() => cb([{object: {type: 'mainView', view: '<p>' + component + '</p>'}}], {}, {
                        onReady(host) {ready.push([component, host.attr('id')]);}
                    }));
                }
            }
        }
    };
    context.Vue.nextTick = cb => tasks.push(cb);
    vm.createContext(context);
    function load(relative) {vm.runInContext(fs.readFileSync(path.join(apps, relative), 'utf8'), context);}
    load('davvag-tools/services/davvag-app-downloader/script.js');
    const loader = {...exported};
    const options = {getApps(cb) {tasks.push(() => cb(permitted));}, isCurrent() {return true;}};
    function flush() {let limit = 100; while (tasks.length) {assert.ok(limit-- > 0, 'queue must finish'); tasks.shift()();}}
    function section(...components) {const parent = node(); parent.children = components.map(c => node('allowed', c)); return parent;}
    return {node, section, $, tasks, loaded, ready, versions, context, exported, loader, options, flush, load, permitted, descriptors, catalog, catalogRequests, shellLookups: () => shellLookups};
}

test('concurrent CMS sections load every embed once with unique IDs and fresh descriptor versions', () => {
    const h = harness();
    const a = h.section('one', 'two');
    const b = h.section('one', 'two');
    let completed = 0;
    h.loader.RenderHTML(h.$(a), () => completed++, null, null, null, h.options);
    h.loader.RenderHTML(h.$(b), () => completed++, null, null, null, h.options);
    h.flush();
    assert.equal(completed, 4);
    assert.equal(h.ready.length, 4);
    assert.equal(new Set(h.ready.map(r => r[1])).size, 4);
    assert.deepEqual(h.versions, Array(8).fill('fresh'));
    assert.equal(h.shellLookups(), 0);
});

test('missing permissions and invalid components fail inline and later embeds still load', () => {
    const h = harness();
    const parent = h.section('one', 'missing', 'two');
    parent.children[0].attrs['webdock-app'] = 'denied';
    let errors = 0;
    h.loader.RenderHTML(h.$(parent), null, () => errors++, null, null, h.options);
    h.flush();
    assert.equal(errors, 2);
    assert.match(parent.children[0].html, /not in the available app list/);
    assert.match(parent.children[1].html, /not registered/);
    assert.deepEqual(h.loaded, ['allowed', 'allowed']);
    assert.equal(h.ready[0][0], 'two');
});

test('navigation cancels pending component mounts at every asynchronous loading stage', () => {
    for (let stage = 0; stage < 4; stage++) {
        const h = harness();
        const parent = h.section('one', 'two');
        let current = true;
        h.options.isCurrent = () => current;
        h.loader.RenderHTML(h.$(parent), null, null, null, null, h.options);
        for (let i = 0; i < stage; i++) h.tasks.shift()();
        current = false;
        h.flush();
        assert.equal(h.ready.length, 0);
    }
});

test('existing hosting-console calls use the CMS provider when available, and left-menu in dock', () => {
    for (const cms of [false, true]) {
        const h = harness();
        if (cms) h.context.window.CMSV7 = h.options;
        h.loader.RenderHTML(h.$(h.section('one', 'two')));
        h.flush();
        assert.equal(h.ready.length, 2);
        assert.equal(h.shellLookups(), cms ? 0 : 2);
    }
});

test('CMS waits for the DOM update, ignores unrelated Vue updates, and cancels removed sections', () => {
    const h = harness();
    h.exported.getAppComponent = (app, component, cb) => {
        assert.equal(app, 'davvag-tools');
        h.tasks.push(() => cb(h.loader));
    };
    h.context.window.apps = {allowed: {version: 'old'}};
    h.load('davvag-cms-v7/components/dock-shell/script.js');
    const directive = h.exported.vue.directives.cmsApps;
    const section = h.section('one');
    directive.inserted(section);
    assert.equal(h.loaded.length, 0);
    h.flush();
    assert.equal(h.ready.length, 1);
    directive.componentUpdated(section, {value: 'same', oldValue: 'same'});
    h.flush();
    assert.equal(h.ready.length, 1);
    const removed = h.section('two');
    directive.inserted(removed);
    directive.unbind(removed);
    h.flush();
    assert.equal(h.ready.length, 1);
    let destroyed = false;
    section.children[0].__vue__ = {$destroy() {destroyed = true;}};
    directive.unbind(section);
    assert.equal(destroyed, true);
});

test('component-only markup discovers different source apps from their descriptors', () => {
    const h = harness();
    h.permitted.other = {version: 'old'};
    h.descriptors.other = {components: {'different-widget': {}}, description: {version: 'fresh'}};
    const parent = h.section('one', 'different-widget');
    parent.children.forEach(n => delete n.attrs['webdock-app']);
    h.loader.RenderHTML(h.$(parent), null, null, null, null, h.options);
    h.flush();
    assert.equal(h.ready.length, 2);
    assert.equal(parent.children[0].attrs['webdock-app'], 'allowed');
    assert.equal(parent.children[1].attrs['webdock-app'], 'other');
});

test('ambiguous components require an explicit app; inaccessible apps are never inspected', () => {
    const h = harness();
    h.permitted.other = {version: 'old'};
    h.descriptors.other = h.descriptors.allowed;
    h.descriptors.denied = {components: {secret: {}}, description: {version: 'fresh'}};
    const parent = h.section('one', 'secret', 'one');
    delete parent.children[0].attrs['webdock-app'];
    delete parent.children[1].attrs['webdock-app'];
    h.loader.RenderHTML(h.$(parent), null, null, null, null, h.options);
    h.flush();
    assert.match(parent.children[0].html, /multiple apps.*Specify webdock-app/);
    assert.match(parent.children[1].html, /No accessible CMS app provides component/);
    assert.ok(!h.loaded.includes('denied'));
    assert.equal(h.ready.length, 1);
});

test('concurrent automatic discovery shares descriptor requests and cancels after navigation', () => {
    const h = harness();
    for (const component of ['one', 'two']) {
        const parent = h.section(component);
        delete parent.children[0].attrs['webdock-app'];
        h.loader.RenderHTML(h.$(parent), null, null, null, null, h.options);
    }
    h.tasks.shift()();
    h.tasks.shift()();
    assert.deepEqual(h.loaded, ['allowed']);
    h.options.isCurrent = () => false;
    h.flush();
    assert.equal(h.ready.length, 0);
});

test('CMS fetches its own catalog instead of trusting a stale menu list, and retries failed lists', () => {
    const h = harness();
    h.context.window.apps = {denied: {version: 'stale'}};
    h.exported.getAppComponent = (app, component, cb) => cb(h.loader);
    h.load('davvag-cms-v7/components/dock-shell/script.js');
    const directive = h.exported.vue.directives.cmsApps;
    h.catalog.success = false;
    const first = h.section('one');
    delete first.children[0].attrs['webdock-app'];
    directive.inserted(first);
    h.flush();
    assert.equal(h.ready.length, 0);
    h.catalog.success = true;
    const second = h.section('one');
    delete second.children[0].attrs['webdock-app'];
    directive.inserted(second);
    h.flush();
    assert.equal(h.ready.length, 1);
    assert.deepEqual(h.catalogRequests, Array(2).fill('components/object/apps?tags=showincms'));
    assert.deepEqual(Object.keys(h.context.window.apps), ['denied']);
    assert.ok(!h.loaded.includes('denied'));
});

test('a failed descriptor lookup does not silently select a possibly ambiguous app', () => {
    const h = harness();
    h.permitted.offline = {version: 'old'};
    const parent = h.section('one');
    delete parent.children[0].attrs['webdock-app'];
    h.loader.RenderHTML(h.$(parent), null, null, null, null, h.options);
    h.flush();
    assert.match(parent.children[0].html, /Unable to inspect available apps/);
    assert.equal(h.ready.length, 0);
});
