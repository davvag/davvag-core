WEBDOCK.component().register(function(exports){
    var scope;
    var chapterCode="";

    function loadbyChatpter(Chapter){
        loadBook(bindData.C.ChapterCode,Chapter,1,0);
    }

    function loadBook(ChapterCode, Chapter,vF,vT){
        try{
        var handler = exports.getShellComponent("soss-data");
        var query=[{storename:"bible_pod_orderby",parameters:{ChapterCode:ChapterCode,Chapter:Chapter.toString()}}];
            if(bindData.Chapters.length==0){
                query.push({storename:"bible_chapters_pod_orderby",parameters:{}});
            }
            if(bindData.tmpCode!=ChapterCode){
                bindData.tmpCode=ChapterCode;
                query.push({storename:"bible_pod_chapters",parameters:{ChapterCode:ChapterCode}});
            }
            
            handler.services.q(query)
            .then(function(r){
                console.log(JSON.stringify(r));
                if(r.success){
                    if(r.result.bible_chapters_pod_orderby!=null){
                        bindData.C=r.result.bible_chapters_pod_orderby[0];
                        bindData.Chapters=r.result.bible_chapters_pod_orderby;
                    }
                    if(r.result.bible_pod_chapters!=null){
                        bindData.ids=r.result.bible_pod_chapters;
                        bindData.Chapter=r.result.bible_pod_chapters[0];
                    }
                    bindData.BibleVerses=r.result.bible_pod_orderby;
                    bindData.BibleText=getText();
                    //bindData.ids=[];
                    
                    //bindData.ids.push()
                }
            })
            .error(function(error){
                console.log(error.responseJSON);
            });
        }catch(e){
            console.log(e);
        }
    }

    bindData={
        Book:"උත්පත්ති",
        Code:"Gen",
        Chapter:{Chapter:1},
        Chapters:[],
        ids:[],
        BibleVerses:[],
        BibleText:"no text loaded",
        C:{ChapterCode:""},
        tmpCode:""
    };

    function getText(){
        if(bindData.BibleVerses.length!=0){
            BibleText="";
            bindData.BibleVerses.forEach(element => {
                if(element.topic==1){
                    BibleText+='<h4>'+element.vText+'</h4></br>';
                }else{
                    BibleText+='<span style="color:#b3002d;font-style:italic;"> '+element.verse+'. </span>'+element.vText;
                }
            });
            return BibleText;
        }else{
            return "no text loaded";
        }

    }

    var vueData =  {
        methods:{
            navigate: function(id){
                handler = exports.getShellComponent("soss-routes");
                handler.appNavigate(id ? "/uom?uomid=" + id : "/uom");
            },
            itemselect:function(c){
                bindData.Chapter.Chapter=1;
                loadBook(c.ChapterCode,1,1,0);
            },
            chapterSelect:loadbyChatpter
        },
        data :bindData,
        onReady: function(s){
            scope = s;
            loadBook("GEN",1,1,0);
        }
    } 

    exports.vue = vueData;
    exports.onReady = function(element){
    }
});
