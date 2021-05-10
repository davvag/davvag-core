
WEBDOCK.component().register(function(exports, scope){
    var $progress,$progressBar,$closebutton,$modal;
    exports.initialize = function(){
        
        clearCeate();
    }

    function clearCeate(){
        clear();
        //if(cropperdiv==null){
        bodyEt=$("body");
        bodyEt.append("<div id='davvagfileupload' class='modal fade' tabindex='-1' role='dialog' aria-labelledby='exampleModalLabel' aria-hidden='true'><div class='modal-dialog' role='document'><div class='modal-content'><div class='modal-header'> <h5 class='modal-title' id='modalLabel'>Uploading Please Wait</h5></div><div class='modal-body'><div class='progress'><div id='progress-bar' class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100' style='width: 0%'></div></div></div><div class='modal-footer'><button type='button' id='close-button' class='btn btn-secondary' data-dismiss='modal'>Close</button></div></div></div></div>");
        //}
        $progress = $('.progress');
        $progressBar = $('.progress-bar');
        $closebutton=document.getElementById("close-button");
        $closebutton.style.visibility = "hidden";
        $modal=$('#davvagfileupload');
    }

    function clear(){
        
        cropperdiv= document.getElementById('davvagfileupload');
        if(cropperdiv){
            cropperdiv.remove();
        }
    }

    exports.close=function(){
        //$modal.modal("hide");
        console.log("close uploader");
        $modal.modal('toggle');
        //$modal.close();
    }

    function complete(){
        $('.modal-title').html("<h5 class='modal-title' id='modalLabel'>Uploading Completed</h5>");
        $('.modal-body').html("You may close the window. Upload has successfully completed");
        $closebutton.style.visibility = "visible";
    }
    exports.upload=function(newfiles,classname,id,cb){
        $modal.modal({backdrop: 'static', keyboard: false});
        if(!newfiles){
            cb(newfiles);
            return;
        }
        var imagecount=newfiles.length;
        var completed=0
        var percent = '0';
        var percentage = '0%';
        uploaderInstance = exports.getShellComponent("soss-uploader");
            for (var i = 0; i < newfiles.length; i++) {
                console.log(i);
                        var filename =id!=null?id.toString()+"-"+newfiles[i].name:newfiles[i].name;
                        console.log(filename);
                        uploaderInstance.services.uploadFile(newfiles[i], classname, filename)
                        .then(function(result2){
                           
                            //$.notify("product Image Has been uploaded", "info");
                            //newfiles[i].status=true;
                            //newfiles[i].result=result2;
                            completed++;
                            percent = Math.round((completed / imagecount) * 100);
                            percentage = percent + '%';
                            $progressBar.width(percentage).attr('aria-valuenow', percent).text(percentage);
                            if(imagecount==completed){
                                //$('#davvag-fileupload').modal("hide");
                                complete();
                                cb(newfiles);
                            }
                            
                        })
                        .error(function(e){
                            completed++;
                            percent = Math.round((completed / imagecount) * 100);
                            percentage = percent + '%';
                            $progressBar.width(percentage).attr('aria-valuenow', percent).text(percentage);
                            //newfiles[i].status=false;
                            //newfiles[i].message=e;
                            if(imagecount==completed){
                                //$('#davvag-fileupload').modal("hide");
                                complete();
                                //$closebutton.style.visibility = "visible";
                                cb(newfiles);
                            }
                        });
                  
            }
    }    

});
