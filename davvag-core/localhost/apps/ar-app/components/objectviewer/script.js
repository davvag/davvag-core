WEBDOCK.component().register(function(exports){
    var scope;
    var handler;
    var pInstance, validatorInstance;
    var routeData;
	var scene, camera, renderer, clock, deltaTime, totalTime;

	var arToolkitSource, arToolkitContext;

	var markerRoot1;

	var mesh1;

    var bindData = {
        item:{},
        submitErrors: undefined,
		i18n: undefined,
		loading:"",
		itmeid:"PeugeotOnyxConcept",
		path:"",
		camaraz:3
    };

    var validator;
    function loadValidator(){
        validator = validatorInstance.newValidator (scope);
        validator.map ("item.name",true, "You should enter a name");
        validator.map ("item.symbol",true, "You should enter a symbol");
    }

                
    exports.getAppComponent("i18n","i18n", function(i18n){
        i18n.initialize(exports, bindData, function(){
            console.log ("i18n loaded!!!");
        });
    });

    

function initialize()
{
	pInstance = exports.getShellComponent("soss-routes");
	routeData = pInstance.getInputData();
	if (routeData.id)
		bindData.itmeid=routeData.id;
	if(routeData.path)
		bindData.path=routeData.path;
	if(routeData.camaraz)
		bindData.camaraz=routeData.camaraz;
	if (!Detector.webgl) {
		Detector.addGetWebGLMessage();
	}
	init();
	animate();
}






var container;

var camera, controls, scene, renderer;
var lighting, ambient, keyLight, fillLight, backLight;



function init() {

	container = document.getElementById("camera");
	//document.body.appendCshild(container);

	/* Camera */

	camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 1000);
	camera.position.z = bindData.camaraz;

	/* Scene */

	scene = new THREE.Scene();
	lighting = true;

	ambient = new THREE.AmbientLight( 0xcccccc, 1.0 );
	scene.add( ambient );
	//scene.add(ambientLight);

	keyLight = new THREE.DirectionalLight(new THREE.Color('hsl(30, 100%, 75%)'), 1.0);
	keyLight.position.set(-100, 0, 100);

	fillLight = new THREE.DirectionalLight(new THREE.Color('hsl(240, 100%, 75%)'), 0.75);
	fillLight.position.set(100, 0, 100);

	backLight = new THREE.DirectionalLight(0xffffff, 1.0);
	backLight.position.set(100, 0, -100).normalize();

	ambient.intensity = 0.25;
	scene.add(keyLight);
	scene.add(fillLight);
	scene.add(backLight);
	/* Model */

	var mtlLoader = new THREE.MTLLoader();
	
	if(bindData.path!=""){
		mtlLoader.setPath('assets/ar-app/obj/'+bindData.path+'/');
		mtlLoader.setBaseUrl('assets/ar-app/obj/'+bindData.path+'/');
	}
	else{
		mtlLoader.setBaseUrl('assets/ar-app/obj/');
		mtlLoader.setPath('assets/ar-app/obj/');
	}
	mtlLoader.load(bindData.itmeid+'.mtl', function (materials) {

		materials.preload();
		if(materials.materials.default!=null){
			materials.materials.default.map.magFilter = THREE.NearestFilter;
			materials.materials.default.map.minFilter = THREE.LinearFilter;
		}

		var objLoader = new THREE.OBJLoader();
		objLoader.setMaterials(materials);
		if(bindData.path!="")
			objLoader.setPath('assets/ar-app/obj/'+bindData.path+'/');
		else
			objLoader.setPath('assets/ar-app/obj/');
		objLoader.load(bindData.itmeid+'.obj', function (object) {
			//var box = new THREE.Box3().setFromObject(object);
			//box.center(object.position);
			//object.localToWorld(box);
			//object.position.multiplyScalar(-1);
			scene.add(object);

		}, onProgress, onError );

	});

	/* Renderer */

	renderer = new THREE.WebGLRenderer();
	renderer.setPixelRatio(window.devicePixelRatio);
	renderer.setSize(window.innerWidth, window.innerHeight);
	renderer.setClearColor(new THREE.Color("hsl(0, 0%, 10%)"));

	container.appendChild(renderer.domElement);

	/* Controls */

	controls = new THREE.OrbitControls(camera, renderer.domElement);
	controls.enableDamping = true;
	controls.dampingFactor = 0.25;
	controls.enableZoom = false;

	/* Events */

	window.addEventListener('resize', onWindowResize, false);
	window.addEventListener('keydown', onKeyboardEvent, false);

}

function onProgress(xhr) { 
	bindData.loading=(xhr.loaded / xhr.total * 100) + '% loaded';
	console.log( (xhr.loaded / xhr.total * 100) + '% loaded' ); }
function onError(xhr) { console.log( 'An error happened' ); }


function onWindowResize() {

	camera.aspect = window.innerWidth / window.innerHeight;
	camera.updateProjectionMatrix();

	renderer.setSize(window.innerWidth, window.innerHeight);

}

function onKeyboardEvent(e) {

	if (e.code === 'KeyL') {

		lighting = !lighting;

		if (lighting) {

			ambient.intensity = 0.25;
			scene.add(keyLight);
			scene.add(fillLight);
			scene.add(backLight);

		} else {

			ambient.intensity = 1.0;
			scene.remove(keyLight);
			scene.remove(fillLight);
			scene.remove(backLight);

		}

	}

}

function animate() {

	requestAnimationFrame(animate);

	controls.update();

	render();

}

function render() {

	renderer.render(scene, camera);

}



    var vueData =   {
        methods: {
            navigateBack: function(){
                initialize();
        			//animate();
            }
        },
        data : bindData,
        onReady : function(s){
            initialize();
        }
    }

    exports.vue = vueData;
    exports.onReady = function(element){
		//initialize();
        //animate();
    }
});
