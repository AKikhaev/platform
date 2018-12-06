var _akcms = _akcms || {};
_akcms.loading = {
    template:$("<div class='akcms_loading'></div>"),
    tag:null,
    start: function(){
            var $this = this;
            $this.tag = $this.template.clone().appendTo(document.body);
            setTimeout(function () { $this.tag.addClass("akcms_loading_started"); },1);
        },
    finish: function(){if (this.tag!==null) { this.tag.remove(); }},
    stop: function(){ this.finish(); }
};
_akcms.ru2lt = {
    t : function (inp) {
        var a = inp.toLowerCase().split("");
        for (var i=0,aL=a.length;i<aL;i++) {var c = this.ru2en[a[i]]; a[i] = c==null?a[i]:c;}
        var s =  a.join("");
        return s.replace(/[^0-9a-z_-]/g, "");
    },
    init:function() {
        this.ru_str = "абвгдеёжзийклмнопрстуфхцчшщъыьэюя№ ";
        this.lt_str = ["a","b","v","g","d","e","e","zh","z","i","j","k","l","m","n","o","p","r","s","t","u","f",
            "h","c","ch","sh","shh","","y","","e","yu","ya","n","_"];
        this.ru2en = {};
        for(var i = 0,l = this.ru_str.length; i < l; i++) {
            this.ru2en[this.ru_str.charAt(i)] = this.lt_str[i];
        }
    }
};_akcms.ru2lt.init();
_akcms.alerts={
    template: $("<div class='alert alert-dismissible fade show' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>"),
    show:function(msg,type,wait){
        var el = this.template.clone().prepend("<span>"+msg+"</span>");
        if (type!==undefined) { el.addClass("alert-"+type); }
        el.prependTo($("#alertContainer")).alert();
        if (wait!==undefined && wait>0) {
            window.setTimeout(function () {
                el.alert("close");
            }, wait);
        }
    },
    primary:  function (msg) { this.show(msg,"primary"); },
    secondary:function (msg) { this.show(msg,"secondary"); },
    success:  function (msg) { this.show(msg,"success",5000); },
    danger:   function (msg) { this.show(msg,"danger"); },
    warning:  function (msg) { this.show(msg,"warning"); },
    info:     function (msg) { this.show(msg,"info",5000); },
    light:    function (msg) { this.show(msg,"light"); },
    dark:     function (msg) { this.show(msg,"dark"); }
};
_akcms.floodSelect=function(selTag,selItms,_id,_name,defId,promptText,_class) {
    selTag.empty();
    if (promptText!==undefined && promptText!=="") { selTag.append($("<option value='A-' style='font-weight:bold;'/>").text(promptText)); }
    $.each(selItms,function(n,selItm) {
        var option = $("<option/>").text(selItm[_name]).val(selItm[_id]).appendTo(selTag);
        if (selItm[_class]!==undefined) { option.addClass(selItm[_class]); }
    });
    if (defId!==undefined && defId!=="") { selTag.val(defId); }
};
_akcms.switchClass=function(isTrue,el,className){
    if (isTrue) { el.addClass(className); }
    else { el.removeClass(className); }
};
_akcms.cookie = {
    set: function(name,value,expires, options) {
        if (options===undefined) { options = {path:"/"}; }
        var expires_date = new Date();
        if ( expires ) {
            expires_date.setHours(expires_date.getHours() + expires);
        }
        document.cookie = name+"="+encodeURIComponent( value ) +
            ( ( expires ) ? ";expires="+expires_date.toGMTString() : "" ) +
            ( ( options.path ) ? ";path=" + options.path : "/" ) +
            ( ( options.domain ) ? ";domain=" + options.domain : "" ) +
            ( ( options.secure ) ? ";secure" : "" );
    },
    get:function(key) {
        var keyValue = document.cookie.match("(^|;) ?" + key + "=([^;]*)(;|$)");
        return keyValue ? decodeURIComponent(keyValue[2]) : null;
    },
    remove:function(name) {
        this.set(name, "", -10);
    }
};
_akcms.replaceAll = function(str, find, replace) {
    return str.replace(new RegExp(find, "g"), replace);
};
_akcms.FileUploader = function(container,options){
    var _this = this,filesUpload = [];
    if (typeof container === "string") { container = $(container); }

    var settings = $.extend({
        container:container,
        ajaxPrefix:"/ajx/_sys/",
        paramName: "file[]",
        dropZone: document.body,
        tn_width:100,
        tn_height:100,
        obj:null,
        objId:null,
        objField:null,
        addBtn:"#addFileUpload",
        uploadBtn:"#uploadFileUpload",
        accept:"image/jpeg,image/png",
        maxNumberOfFiles:0,
        maxChunkSize:512*1024,
        onDone: null,

        color: "#556b2f",
        backgroundColor: "white"
    }, options );
    if (typeof settings.dropZone === "string") { settings.dropZone = $(settings.dropZone); }
    if (typeof settings.addBtn === "string") { settings.addBtn = $(settings.addBtn); }

    var id = "FUC" + settings.obj + settings.objId + settings.objField +  Math.floor(Math.random() * 1000);
    var template = $(
        "<div class='FileUploader_Item' style='width: "+settings.tn_width+"px; height: "+settings.tn_height+"px'>" +
        "<img width='"+settings.tn_width+"' height='"+settings.tn_height+"' class='FileUploader_img'>" +
        "<div class='FileUploader_progress'></div>" +
        "<div class='FileUploader_rotateBtn'><i class='fa fa-repeat text-primary'></i></div>" +
        "<div class='FileUploader_dropBtn'><i class='fa fa-times text-danger'></i></div>" +
        "</div>");

    var addEvents = function (itemDiv,data) {
        var imgEl = itemDiv.find("img.FileUploader_img");
        itemDiv.find("div.FileUploader_rotateBtn").click(function(){
            var angle = data.formData.angle;
            angle = (angle + 90) % 360;
            data.formData.angle = angle;
            imgEl.removeClass("rotate90 rotate180 rotate270 rotate0").addClass("rotate"+angle);
        });
        itemDiv.find("div.FileUploader_dropBtn").click(function(){
            var name = data.files[0].name;
            if (window.confirm("Удалить "+name+"?"))
            {
                itemDiv.remove();
                data.removed = true;
                if (typeof data.files[0].server !== "undefined" && data.files[0].server === true) {
                    $.ajax({
                        type: "POST",
                        url: settings.ajaxPrefix+"_objectFileRemove",
                        data: {
                            obj:settings.obj,
                            objId:settings.objId,
                            objField:settings.objField,
                            id:data.cof_id
                        },
                        dataType: "json"
                    }).done(function (resultData) {
                        if (resultData.error) {
                            window.console.log(resultData.error);
                        } else {
                            //removed
                        }
                    }).fail(function (jqXHR, textStatus) {
                        window.console.log(textStatus);
                    });
                } else {
                    //todo removing from queue or server
                    /*
                    var array = [2, 5, 9];
                    console.log(array)
                    var index = array.indexOf(5);
                    if (index > -1) {
                      array.splice(index, 1);
                    }
                    // array = [2, 9]
                    console.log(array);
                     */
                }


            }
        });


    };

    var showUploadingFileThumbnail = function(data){
        var reader = new FileReader();
        var file = data.files[0];

        //https://jsfiddle.net/xor3L8db/
        reader.onload = function(e) {
            //_this.secImageImg.prop('src',reader.result);
            var img = document.createElement("img");
            img.addEventListener("load",function (e) {
                var width = img.width, height = img.height;
                var max_width = settings.tn_width, max_height = settings.tn_height, x_ratio = max_width / width, y_ratio = max_height / height;
                var tn_width, tn_height, cntrX, cntrY, tn_cntrX, tn_cntrY, putX, putY;
                cntrX = width/2;cntrY = height/2;
                tn_width = Math.ceil(y_ratio * width);
                tn_height = max_height;
                tn_cntrX = y_ratio*cntrX;
                //tn_cntrY = y_ratio*cntrY;
                putX = max_width/2-tn_cntrX;
                putY = 0;
                if (putX>0) { putX = 0; }
                if (putX<max_width-tn_width) { putX = max_width-tn_width; }

                if (tn_width < max_width) {
                    tn_height = Math.ceil(x_ratio * height);
                    tn_width = max_width;
                    //tn_cntrX = x_ratio*cntrX;
                    tn_cntrY = x_ratio*cntrY;
                    putX = 0;
                    putY = max_height/2-tn_cntrY;
                    if (putY>0) { putY = 0; }
                    if (putY<max_height-tn_height) { putY = max_height-tn_height; }
                }

                var canvas = document.createElement("canvas");
                canvas.width = max_width; canvas.height = max_height;
                var ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0,0,width,height,putX,putY,tn_width,tn_height);

                var itemDiv = template.clone().appendTo(container);
                itemDiv.find("img.FileUploader_img").attr("src",canvas.toDataURL("image/jpeg"));
                addEvents(itemDiv,data);
                data.context = itemDiv;

                //document.getElementById("e_sec_imgfile").src = canvas.toDataURL("image/jpeg");
                //ctx = null; canvas = null; img = null; // clean
            },false);
            img.src = reader.result;
        };
        if (file) { reader.readAsDataURL(file); }
    };

    var createServerFile = function(itemData){
        var itemDiv = template.clone();
        itemDiv.find("img.FileUploader_img").attr("src",itemData.urlPreview);
        itemDiv.find("div.FileUploader_rotateBtn").remove();
        itemData.files = [{
            name:itemData.cof_file,
            server:true
        }];
        addEvents(itemDiv,itemData);
        return itemDiv;
    }

    this.getServerFiles = function() {
        $.ajax({
            type: "POST",
            url: settings.ajaxPrefix+"_objectFileList",
            data: {
                obj:settings.obj,
                objId:settings.objId,
                objField:settings.objField
            },
            dataType: "json"
        }).done(function (filesList) {
            if (filesList.error) {
                window.console.log(filesList.error);
            } else {
                container.empty();
                filesList.forEach(function (itemData) {
                    createServerFile(itemData).appendTo(container);
                });
            }
        }).fail(function (jqXHR, textStatus) {
            window.console.log(textStatus);
        });
    };

    if (settings.obj!==null) {
        this.getServerFiles();
    }

    //https://github.com/blueimp/jQuery-File-Upload/wiki/Options
    var fileInput = $("<input type='file' name='"+settings.paramName+"' class='d-none hidden' id='"+id+"' multiple accept='"+settings.accept+"'>")
        .appendTo(document.body)
        .fileupload({
            dataType: "json",
            replaceFileInput:true,
            dropZone:settings.dropZone,
            url:settings.ajaxPrefix+"_objectFileUpload",
            paramName:settings.paramName,
            maxNumberOfFiles:settings.maxNumberOfFiles,
            maxChunkSize:settings.maxChunkSize,
            //autoUpload: false,
            add: function (e, data) {
                data.formData = { angle:0 };
                showUploadingFileThumbnail(data);
                filesUpload.push(data);

                {
                    // var ajaxUrl = "/ajx/";
                    // ajaxUrl += document.akcms.secs[_this.secItem.section_id]["sec_url_full"];
                    // data.url = ajaxUrl+"_seciupl";
                    // data.submit();
                    // _this.secItem.sec_imgfile = _this.secItem.section_id+".jpg";
                }
            },
            progress:function (e, data) {
                data.context.find("div.FileUploader_progress").css("width",parseInt(data.loaded / data.total * 100, 10) + "px");
            },
            done: function (e, data) {
                var itemDiv = createServerFile(data.result).insertAfter(data.context);
                data.context.remove();
                data.context = itemDiv;
            },
            fail: function (e, data) {
                data.context.remove();
                window.console.log(data.textStatus + ": " + data.files[0].name + " " + data.errorThrown);
            },
            // submit: function (e, data) {
                // data.formData.obj = settings.obj;
                // data.formData.objId = settings.objId;
                // data.formData.objField = settings.objField;
                // data.formData.fileSize = data.files[0].size;
                // if (settings.maxChunkSize>0 && data.formData.fileSize>settings.maxChunkSize) {
                //     data.formData.maxChunkSize = settings.maxChunkSize;
                //     data.formData.chunks = Math.ceil(data.formData.fileSize/settings.maxChunkSize);
                //     data.formData.chunkNum = 1;
                // }
            // },
            // chunksend: function (e,data) {
            //     console.log(e,data);
            //     data.formData.chunkNum = 2;
            // },
            chunkdone:function (e,data) {
                data.formData.chunkNum++;
                data.formData.id = data.result.cof_id;
            },
            stop: function () {
                if (settings.onDone!==null) { settings.onDone(); }
            }
            // done: function (e, data) {
            //     console.log(data);
            //     $.each(data.result.files, function (index, file) {
            //         $("<p/>").text(file.name).appendTo(document.body);
            //     });
            // }
        });

    settings.addBtn.click(function(e){
        e.preventDefault(); e.stopPropagation();
        $("input#"+id).trigger("click");
        //fileInput.trigger("click");
    });

    this.setObj = function(obj,objId,objField) {
        settings.obj = obj;
        settings.objId = objId;
        settings.objField = objField;
        settings.autoUpload = true;
    };

    this.setAjaxPrefix = function(ajaxPrefix){
        settings.ajaxPrefix = ajaxPrefix;
    };

    this.uploadAll = function(){
        var countStarted = 0;
        filesUpload.forEach(function (data) {
            if (typeof data.removed !== "undefined" && data.removed) { return; }
            data.url = settings.ajaxPrefix+"_objectFileUpload";
            data.formData.obj = settings.obj;
            data.formData.objId = settings.objId;
            data.formData.objField = settings.objField;
            data.formData.fileSize = data.files[0].size;
            if (settings.maxChunkSize>0 && data.formData.fileSize>settings.maxChunkSize) {
                data.formData.chunkCount = Math.ceil(data.files[0].size/settings.maxChunkSize);
                data.formData.maxChunkSize = settings.maxChunkSize;
                data.formData.chunkNum = 1;
            }
            data.submit();
            countStarted++;
        });
        filesUpload = [];
        if (settings.onDone!==null && countStarted===0) { settings.onDone(); }
    };

    this.done = function(callBack){
        settings.onDone = callBack;
        return this;
    };

    $(settings.uploadBtn).click(function (e) {
        e.preventDefault(); e.stopPropagation();
        _this.uploadAll();
    });
};
