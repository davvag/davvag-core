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
		loading:""
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
	scene = new THREE.Scene();

	let ambientLight = new THREE.AmbientLight( 0xcccccc, 1.0 );
	scene.add( ambientLight );
				
	camera = new THREE.Camera();
	scene.add(camera);

	renderer = new THREE.WebGLRenderer({
		antialias : true,
		alpha: true
	});
	renderer.setClearColor(new THREE.Color('lightgrey'), 0)
	renderer.setSize( 640, 480 );
	renderer.domElement.style.position = 'absolute'
	renderer.domElement.style.top = '0px'
	renderer.domElement.style.left = '0px'
	document.body.appendChild( renderer.domElement );

	clock = new THREE.Clock();
	deltaTime = 0;
	totalTime = 0;
	
	////////////////////////////////////////////////////////////
	// setup arToolkitSource
	////////////////////////////////////////////////////////////

	arToolkitSource = new THREEx.ArToolkitSource({
		sourceType : 'webcam'
	});

	function onResize()
	{
		arToolkitSource.onResize()	
		arToolkitSource.copySizeTo(renderer.domElement)	
		if ( arToolkitContext.arController !== null )
		{
			arToolkitSource.copySizeTo(arToolkitContext.arController.canvas)	
		}	
	}

	arToolkitSource.init(function onReady(){
		onResize()
	});
	
	// handle resize event
	window.addEventListener('resize', function(){
		onResize()
	});
	
	////////////////////////////////////////////////////////////
	// setup arToolkitContext
	////////////////////////////////////////////////////////////	

	// create atToolkitContext
	arToolkitContext = new THREEx.ArToolkitContext({
		cameraParametersUrl: 'assets/ar-app/data/camera_para.dat',
		detectionMode: 'mono'
	});
	
	// copy projection matrix to camera when initialization complete
	arToolkitContext.init( function onCompleted(){
		camera.projectionMatrix.copy( arToolkitContext.getProjectionMatrix() );
	});

	////////////////////////////////////////////////////////////
	// setup markerRoots
	////////////////////////////////////////////////////////////

	// build markerControls
	markerRoot1 = new THREE.Group();
	scene.add(markerRoot1);
	let markerControls1 = new THREEx.ArMarkerControls(arToolkitContext, markerRoot1, {
		type: 'pattern', patternUrl: "assets/ar-app/data/hiro.patt",
	})

	let geometry1 = new THREE.PlaneBufferGeometry(1,1, 4,4);
	let loader = new THREE.TextureLoader();
	// let texture = loader.load( 'images/earth.jpg', render );
	let material1 = new THREE.MeshBasicMaterial( { color: 0x0000ff, opacity: 0.5 } );
	mesh1 = new THREE.Mesh( geometry1, material1 );
	mesh1.rotation.x = -Math.PI/2;
	//markerRoot1.add( mesh1 );
	
	function onProgress(xhr) { 
		bindData.loading=(xhr.loaded / xhr.total * 100) + '% loaded';
		console.log( (xhr.loaded / xhr.total * 100) + '% loaded' ); }
	function onError(xhr) { console.log( 'An error happened' ); }
	
	new THREE.MTLLoader()
		.setPath( 'assets/ar-app/models/helecopter/' )
		.load( 'chopper.mtl', function ( materials ) {
			materials.preload();
			new THREE.OBJLoader()
				.setMaterials( materials )
				.setPath( 'assets/ar-app/models/helecopter/' )
				.load( 'chopper.obj', function ( group ) {
					mesh0 = group;
					//mesh0.material.side = THREE.DoubleSide;
					group.position.y = 0.01;
					group.position.x = 0.01;
					group.scale.set(0.30,0.30,0.30);
					markerRoot1.add(group);
					animate();
				}, onProgress, onError );
		});
}


function update()
{
	// update artoolkit on every frame
	if(mesh0){
		mesh0.rotation.y += 0.01;
	}

	if ( arToolkitSource.ready !== false )
		arToolkitContext.update( arToolkitSource.domElement );
}


function render()
{
	renderer.render( scene, camera );
}


function animate()
{
	requestAnimationFrame(animate);
	deltaTime = clock.getDelta();
	totalTime += deltaTime;
	update();
	render();
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
