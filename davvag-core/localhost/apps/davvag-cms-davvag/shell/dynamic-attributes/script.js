WEBDOCK.component().register(function(exports){

    function renderForm(attributejson,varibaleembed,id){
        var formData = [{"type":"text","label":"Test","req":1},{"type":"textarea","label":"123456789","req":0},{"type":"select","label":"example","req":0,"choices":[{"label":"1","sel":0},{"label":"2","sel":0},{"label":"3","sel":0}]}];
        createForm(formData,id,varibaleembed);
    }

    function createForm(arr,id,varibaleembed){
        var $formTmp = $('<form class="form-horizontal form-bordered"></form>');
    
        arr.forEach( function(obj, idx) {
            var $fieldSet,
                $selctOpts = $('<select class="form-control" name=""></select>'),
                inputType = obj.type; 
                
            switch (inputType){
                case 'text':
                    $fieldSet = $('<div class="form-group"></div>');
                    $fieldSet.append('<label class="col-sm-3 control-label">'+obj.label+'</label>');
                    $txt=$('<div class="col-sm-6"></div>');
                    if ( obj.req === 1) {
                        $txt.append('<input class="form-control" type="text" required>');
                    } else {
                        $txt.append('<input class="form-control" type="text">');
                    }
                    $fieldSet.append($txt); 
                    $formTmp.append($fieldSet);
                    break;
                case 'textarea':
                    $fieldSet = $('<div class="form-group"></div>');
                    $fieldSet.append('<label  class="col-sm-3 control-label">'+obj.label+'</label>');
                    $txt=$('<div class="col-sm-6"></div>');
                    $txt.append('<textarea class="form-control" rows="4" cols="50"></textarea>');
                    $fieldSet.append($txt); 
                    $formTmp.append($fieldSet);
                    break;
                case 'select':
                    $fieldSet = $('<div class="form-group"></div>');
                    $fieldSet.append('<label  class="col-sm-3 control-label">'+obj.label+'</label>');
                    $txt=$('<div class="col-sm-6"></div>');
                    addOptions($selctOpts, obj.choices);
                    $txt.append($selctOpts);
                    $fieldSet.append($txt);                     
                    $formTmp.append($fieldSet);
                    break;
                default:
                    alert('There was no input type found.');
                    break;
            }               
        });
    
        // render to body.
        document.getElementById(id).innerHTML = $formTmp.html();
        
    
    
        // Loop for the select options.
        function addOptions(elem, arr){
            arr.forEach(function(obj){
                elem.append('<option value="'+obj.sec+'">'+obj.label+'</option>');              
            });
        }
    }

    exports.renderForm = renderForm;

});
