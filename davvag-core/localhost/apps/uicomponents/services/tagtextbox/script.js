WEBDOCK.component().register(function(exports){
    var instanceCount = 0;

    Vue.component('dv-tag-text', {
        props:['value'],
        data: function () {
            return {
                currentIndex: instanceCount
            }
        },
        template: '<input name="dv_tags_{{instanceCount}}" id="dv_tags_{{instanceCount}}" class="form-control" value="{{value}}" />',
        created:function() {
            instanceCount++;
            var currentInstanceId = instanceCount;
            $(document).ready(function(){   
                "use strict";            
              $('#dv_tags_' + currentInstanceId).tagsInput({width:'auto'});
            });

            
        }
    });
});
