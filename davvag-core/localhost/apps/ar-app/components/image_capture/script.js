WEBDOCK.component().register(function(exports){
    var scope;
    
    var bindData = {
        item:{},
        submitErrors: undefined,
        i18n: undefined
    };

    var video,canvas,snap,constraints;
   
    var vueData =   {
        methods: {
            navigateBack: function(){
                
            }
        },
        data : bindData,
        onReady : function(s){
            loadWebCam();
        }
    }

    function loadWebCam(){
        
        video = document.getElementById('video');
        canvas = document.getElementById('canvas');
        snap = document.getElementById("snap");
        errorMsgElement = document.querySelector('span#errorMsg');

        constraints = {
        audio: true,
        video: {
            width: 600, height: 600
        }
        };
        init();
        snap.addEventListener("click", function() {
            var context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 600, 600);
        });
        
    }

    async function init() {
        try {
          const stream = await navigator.mediaDevices.getUserMedia(constraints);
          handleSuccess(stream);
        } catch (e) {
          errorMsgElement.innerHTML = `navigator.getUserMedia error:${e.toString()}`;
        }
      }

    // Success
    function handleSuccess(stream) {
        window.stream = stream;
        video.srcObject = stream;
    }

    function letImage(){
        var canvas = document.getElementById('img-can');
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
        var image = document.getElementById('img-hh');
        image.setAttribute('src', canvas.toDataURL('image/png'));
    }
    exports.vue = vueData;
    exports.onReady = function(element){
		
    }
});
