WEBDOCK.component().register(function(exports){
    var scope;

    function loadBook(ChapterCode, Chapter,vF,vT){
        var handler = exports.getComponent("soss-data");
        var query=[{storename:"bible",search:"ChapterCode:"+ChapterCode+",Chapter:"+Chapter}];
            if(bindData.Chapters.length==0){
                query.push({storename:"bible_chapters",search:""});
            }

            handler.services.q(query)
            .then(function(r){
                //console.log(JSON.stringify(r));
                if(r.success){
                    bindData.Chapters=r.result.bible_chapters;
                    bindData.BibleVerses=r,result.bible;
                }
            })
            .error(function(error){
                console.log(error.responseJSON);
            });
    }

    bindData={
        Book:"උත්පත්ති",
        Chapter:1,
        Chapters:[],
        BibleVerses:[]
    };

    var vueData =  {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/uom?uomid=" + id : "/uom");
            }
        },
        data :bindData,
        onReady: function(s){
            scope = s;
            //loadBook("GEN",1,1,0);
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
