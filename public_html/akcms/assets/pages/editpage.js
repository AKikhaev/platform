var _akcms = _akcms || {};
_akcms.loadaing = {
    template:$("<div class='loading'></div>"),
    tag:null,
    start: function(){this.tag = this.template.clone().appendTo(document.body);},
    finish: function(){if (this.tag!==null) { this.tag.remove(); }}
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
_akcms.editPage = {
    getNodeId:function(el){
        return el.closest("li").prop("id");
    },
    modalDiv:null,
    secLi:null,
    buttonSecDrop:null,
    secImageChosen:false,
    secImageImg:null,
    secImageBtn:null,
    secImageInp:null,
    secImageData:null,
    secItem:{},
    form:null,
    makeEditPage:function(parentId,secId,el){
        var _this = this;
        this.form.classList.remove("was-validated");
        this.form.reset();
        this.secLi = el===undefined?null:el;
        _this.secItem = secId!==0?document.akcms.secs[secId]:{
            "section_id":0,
            "sec_parent_id":parentId,
            "sec_url":"",
            "sec_nameshort":"",
            "sec_namefull":"",
            "sec_showinmenu":"t",
            "sec_openfirst":"f",
            "sec_title":"",
            "sec_keywords":"",
            "sec_description":"",
            "sec_enabled":"f",
            "sec_howchild":1,
            "_p_hc":0,
            "sec_from":"",
            "sec_page_child":"text",
            "sec_page":(parentId!==0?document.akcms.secs[parentId].sec_page_child:""),
            "sec_imgfile":"",
            "sec_to_news":"t"
        };
        $("#e_sec__head").html((secId===0?"Добавление":"Редактирование")+" страницы");

        $("#e_sec_namefull").val(_this.secItem.sec_namefull);
        $("#e_sec_nameshort").val(_this.secItem.sec_nameshort);
        $("#e_sec_url").val(_this.secItem.sec_url);
        if (_this.secItem.sec_from==="") {
            var d = new Date(); //d.setSeconds(0); d.setMinutes(0); d.setHours(30);
            $("#e_sec_from").data("Zebra_DatePicker").set_date(d);
        }
        else { $("#e_sec_from").data("Zebra_DatePicker").set_date(_this.secItem.sec_from); } //$("#e_sec_from").prop("value",_this.secItem.sec_from);
        $("#e_sec_howchild").val(_this.secItem.sec_howchild);
        _akcms.floodSelect($("#e_sec_page"),document.akcms.sec_pages,"k","v",_this.secItem.sec_page,"","c");
        $("#e_sec_title").val(_this.secItem.sec_title);
        $("#e_sec_description").val(_this.secItem.sec_description);
        $("#e_sec_keywords").val(_this.secItem.sec_keywords);
        $("#e_sec_showinmenu").prop("checked",_this.secItem.sec_showinmenu==="t");
        $("#e_sec_openfirst").prop("checked",_this.secItem.sec_openfirst==="t");
        $("#e_sec_enabled").prop("checked",_this.secItem.sec_enabled==="t").change();
        _this.secImageChosen = false;
        if (_this.secItem.sec_imgfile==="") {
            _this.secImageImg.attr("src", "").attr("data-src", "holder.js/100x100?text=Загрузить&size=9&fg=#777");
            Holder.run({images: "#e_sec_imgfile"});
        }
        else { _this.secImageImg.prop("src","/img/pages/nt/"+_this.secItem.sec_imgfile+"?"+Math.random()); }
        $("#e_sec_imgfile_btn_url").prop("disabled",secId===0);
        $("#makeEditPageGo").text(secId===0?"Создать":"Сохранить");
        _this.modalDiv.collapse("show");
        $("html, body").animate({ scrollTop: _this.modalDiv.offset().top-20 }, 500);
    },
    onSecBtnSave: function(){
        var _this = this;

        var e_sec_url = $("#e_sec_url");
        var e_sec_namefull = $("#e_sec_namefull");
        var e_sec_nameshort = $("#e_sec_nameshort");

        if (e_sec_nameshort.val()===""){ e_sec_nameshort.val( e_sec_namefull.val() ); }
        if (e_sec_url.val()==="") { e_sec_url.val( _akcms.ru2lt.t( e_sec_nameshort.val() ) ); }

        if (this.form.checkValidity()) {
            _this.secItem.sec_namefull = $("#e_sec_namefull").val();
            _this.secItem.sec_nameshort = $("#e_sec_nameshort").val();
            _this.secItem.sec_url = $("#e_sec_url").val();
            _this.secItem.sec_from = $("#e_sec_from").val();
            _this.secItem.sec_howchild = $("#e_sec_howchild").val();
            _this.secItem.sec_page = $("#e_sec_page").val();
            _this.secItem.sec_title = $("#e_sec_title").val();
            _this.secItem.sec_description = $("#e_sec_description").val();
            _this.secItem.sec_keywords = $("#e_sec_keywords").val();
            _this.secItem.sec_showinmenu = $("#e_sec_showinmenu").prop("checked")?"t":"f";
            _this.secItem.sec_openfirst = $("#e_sec_openfirst").prop("checked")?"t":"f";
            _this.secItem.sec_enabled = $("#e_sec_enabled").prop("checked")?"t":"f";
            //_this.secItem.sec_to_news = $("#e_sec_to_news").prop("checked")?"t":"f";

            var ajaxUrl = "/ajx/";
            if (_this.secItem.section_id!==0) { ajaxUrl += document.akcms.secs[_this.secItem.section_id].sec_url_full; }
            else if (_this.secItem.sec_parent_id!==0) { ajaxUrl += document.akcms.secs[_this.secItem.sec_parent_id].sec_url_full; }

            this.secImageInp.prop("data-src",ajaxUrl + "_seciupl");

            _akcms.loadaing.start();
            $.ajax({
                type: "POST",
                url: ajaxUrl+"_sec"+(_this.secItem.section_id!==0?"sve":"ins"),
                data: _this.secItem,
                //processData: false,
                //contentType: false,
                dataType: "json"
            }).done(function(sres) {
                _akcms.loadaing.finish();
                if (typeof(sres.r)!=="undefined"?sres.r==="t":false) {
                    _this.secItem.sec_url_full = sres.url;
                    ajaxUrl = "/ajx/"+sres.url;

                    if (_this.secItem.section_id===0) {
                        _this.secItem.section_id = sres.id;
                        if (_this.secImageChosen) {
                            _this.secImageData.url = ajaxUrl+"_seciupl";
                            _this.secImageData.submit();
                            setTimeout(function () {
                                document.location = "/_/" + sres.url;
                            },1000);
                        } else {
                            document.location = "/_/" + sres.url;
                        }
                    }
                    else {
                        document.akcms.secs[_this.secItem.section_id] = _this.secItem;
                        _akcms.alerts.success("Изменения сохранены");
                        if (_this.secLi!==null) {
                            _this.secLi.find("a.text").text(_this.secItem.sec_nameshort).prop("href","/_/" + sres.url);
                            _this.secLi.find("a.view").prop("href","/" + sres.url);
                            _this.secLi.find("span.node-icon").prop("class","icon node-icon " + sres.icon);
                        }
                    }
                    _this.modalDiv.collapse("hide");
                } else {
                    sres.error.each(function(error){
                        if (error.f==="!" && error.s==="!") {
                            _akcms.alerts.warning("Недостаточно привеллегий!");
                            alert("Недостаточно привеллегий!");
                        }
                        else {
                            $("#e_" + error.f).addClass("is-invalid");
                            //errTips.newTip("e_" + error.f, error.s);
                        }
                    });
                }
            }).fail(function(jqXHR, textStatus) {
                _akcms.loadaing.finish();
                _akcms.alerts.warning("Неудалось сохранить! " + textStatus);
            });
        }
        this.form.classList.add("was-validated");
    },
    onSecBtnDrop: function(){
        var _this = this;
        if (confirm("Удалить "+(this.secItem.section_id===0?"Новый раздел":"\""+this.secItem.sec_nameshort+"\"")+"?")) {
            if (_this.secItem.section_id===0) { _this.modalDiv.collapse("hide"); }
            else {
                $.ajax({
                    type: "POST",
                    url: "/ajx/" + _this.secItem.sec_url_full + "_secdrp",
                    data: {
                        "section_id": _this.secItem.section_id
                    },
                    success: function (sres) {
                        if (typeof(sres.r) !== "undefined" ? sres.r === "t" : false) {
                            document.location = "/_/" + sres.url;
                        }
                    },
                    dataType: "json"
                });
            }
        }
    },
    bindTabInsteadEnter: function(){
        var _this = this;
        $(_this.form).find("input,select").keypress(function(event){
            if(event.which === 13 && $(this).prop("type")!=="submit") {
                event.preventDefault(); event.stopPropagation();
                var list = $(_this.form).find("input,select,textarea,button");
                var i = $.inArray(this,list)+1; if (i>=list.length) { i = 0; }
                list.eq(i).focus();
            }
        });
    },
    onImageChosen:function(e){
        var _this=this;
        var file = this.secImageInp[0].files[0];
        _this.setFileToImage(file);
    },
    setFileToImage:function(file){
        var _this=this;
        var reader = new FileReader();

        //https://jsfiddle.net/xor3L8db/
        reader.onload = function(e) {
            //_this.secImageImg.prop('src',reader.result);
            var img = document.createElement("img");
            img.addEventListener("load",function (e) {
                var width = img.width, height = img.height;
                var max_width = 400, max_height = 400, x_ratio = max_width / width, y_ratio = max_height / height;
                var tn_width, tn_height, cntrX, cntrY, tn_cntrX, tn_cntrY, putX, putY, lngst;
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
                document.getElementById("e_sec_imgfile").src = canvas.toDataURL("image/jpeg");
            },false);
            img.src = reader.result;
        };

        if (file) { reader.readAsDataURL(file); }
        else { Holder.run({images: "#e_sec_imgfile"}); }
    },
    onSecImageUrl:function(){
        var _this = this;
        var url = prompt("Введите адрес изображения","");
        if (url!==false) {
            $.ajax({
                type: "POST",
                url: "/ajx/" + this.secItem.sec_url_full + "_seciuplurl",
                data: {
                    "section_id": this.secItem.section_id,
                    "url": url
                },
                success: function (sres) {
                    if (sres.res === "t") {
                        _this.secItem.sec_imgfile = _this.secItem.section_id + ".jpg";
                        _this.secImageImg.prop("src", "/img/pages/nt/" + _this.secItem.sec_imgfile + "?" + Math.random());
                        document.akcms.secs[_this.secItem.section_id].sec_imgfile = _this.secItem.sec_imgfile;
                    }
                },
                dataType: "json"
            });
        }
    },
    onSecImageDrop:function(){
        var _this = this;
        if (_this.secItem.sec_imgfile !== "" && _this.secItem.section_id !== 0) {
            $.ajax({
                type: "POST",
                url: "/ajx/"+this.secItem.sec_url_full+"_secidrp",
                data: {
                    "section_id":this.secItem.section_id
                },
                success: function (sres) {
                    if (sres==="t") {
                        document.akcms.secs[_this.secItem.section_id].sec_imgfile = _this.secItem.sec_imgfile;
                    }
                },
                dataType: "json"
            });
        }
        _this.secItem.sec_imgfile = "";
        _this.secImageChosen = false;
        _this.secImageImg.prop("src","");
        Holder.run({images: "#e_sec_imgfile"});
    },
    init:function(){
        var _this = this;
        _this.form=$("#makeEditPageGo").closest("form")[0];
        _this.form.addEventListener("submit", function(e) {
            e.preventDefault();
            e.stopPropagation();
            _this.onSecBtnSave();
        }, false);
        _this.bindTabInsteadEnter();
        _this.modalDiv = $("#modalMakeEditSec");
        _this.buttonSecDrop = $("#makeEditPageDrop").click($.proxy(_this.onSecBtnDrop,_this));
        _this.secImageImg = $("#e_sec_imgfile");
        _this.secImageBtn = $("#e_sec_imgfile_btn");
        _this.secImageInp = $("#e_sec_imgfile_inp").prop("accept","image/jpeg,image/png"); //.change( $.proxy(_this.onImageChosen,_this) );
        _akcms.loadaing.start();
        $(document.body).append($("<div class='admcntrl_cnt inedit'><div id='admcntrl' class='admcntrl'><nobr><img class='admlogo' width='212' height='19' src='/img/adm/adm_logo.png' alt=''><img id='admexit' src='/img/edt/btnlgout.png' title='Выход'><a href='/_/'><img src='/img/edt/btnhome.png' title='На главную редактора'></a><a id='akcms_viewPage' href='#'><img src='/img/edt/btnview.png' title='Посмотреть страницу'></a></nobr></div></div>"));
        $("#admexit").click(function(){if (confirm("Выйти из панели управления?")) { document.location="/_logout/"; }});

        //https://fontawesome.com/v4.7.0/icons/
        var treeLoaded = false,treeviewDiv = $("#treeAdmMenu"),$treeview = null;
        if (treeviewDiv!==null) {
            $treeview = treeviewDiv.treeview({
                expandIcon: "fa fa-plus-square",
                collapseIcon: "fa fa-minus-square",
                emptyIcon: "emptyIcon",
                nodeIcon: "fa fa-file-text-o",
                color: "#593196",
                backColor: "#F9F8FC",
                onhoverColor: "#a991d4",
                borderColor: "#ddd",
                showBorder: true,
                showTags: true,
                highlightSelected: true,
                selectedColor: "white",
                selectedBackColor: "#4c3880",
                enableLinks: true,
                links: true,
                preventUnselect: true,
                tagsClass: "badge badge-secondary",
                onNodeSelected: function (event, node) {
                    var href = "/" + document.akcms.secs[node.id]["sec_url_full"];
                    if (href === "//") { href = "/"; }
                    if (treeLoaded) {
                        history.pushState(null, null, "/_" + href);
                        $("#akcms_viewPage").attr("href", href);
                        _akcms.loadaing.start();
                        $.ajax({
                            type: "POST",
                            url: "/ajx/" + href + "_sec_data",
                            data: [],
                            success: function (sres) {
                                document.akcms.currpage = sres;
                                _akcms.loadaing.finish();
                            },
                            dataType: "json"
                        });
                        //document.location.href=node.href;
                    }
                    //console.log(node);
                },
                onRendered: function (e) {
                    //$(e.target).treeview("collapseAll", { silent: true })
                    $treeview.collapseAll();
                    $.each($treeview.getNodes(), function (n, node) {
                        if (node.href === "//") { node.href = "/"; }
                        if (currpage.id === node.id) {
                            $treeview.revealNode(node);
                            $treeview.selectNode(node);
                            $treeview.expandNode(node);
                            $("#akcms_viewPage").attr("href", node.href);
                        }
                        node.$el.append(
                            $("<div class='node-buttons'>" +
                                "<a target='_blank' class='view' href='" + node.href + "'><i class='node-button fa fa-eye' data-toggle='tooltip' data-placement='top' title='Посмотреть страницу'></i></a> " +
                                "<i class='node-button sec_new fa fa-plus-square' data-toggle='tooltip' data-placement='top' title='Создать дочернюю страницу'></i>" +
                                "<i class='node-button sec_edit fa fa-pencil-square' data-toggle='tooltip' data-placement='top' title='Редактировать страницу'></i>" +
                                "</div>")
                        );
                        //console.log(node);
                    });

                    $(".sec_new").click(function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        _this.makeEditPage(_this.getNodeId($(this)), 0, $(this).closest("li"));
                    });
                    $(".sec_edit").click(function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        _this.makeEditPage(0, _this.getNodeId($(this)), $(this).closest("li"));
                    });
                    treeLoaded = true;
                    $("[data-toggle=tooltip]").tooltip();
                    _akcms.loadaing.finish();
                },
                data: document.akcms.treeViewData
            }).treeview(true);
        }
        $("#btn_sec_new_root").click(function(e){ e.preventDefault(); e.stopPropagation(); _this.makeEditPage(0,0); });

        $("#e_sec_from").Zebra_DatePicker({
            "format": "Y-m-d H:i:s",
            default_position:"below",
            show_icon:false,
            show_week_number: "Нед",
            months: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
            //days: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
            days_abbr: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            lang_clear_date: "Очистить",
            show_select_today: "Сегодня"
        });

        _akcms.floodSelect($("#e_sec_howchild"),[
            {"k":1,"v":"По порядку"},
            {"k":2,"v":"По дате (с новых)","c":"mnu_hc2"},
            {"k":3,"v":"По дате (со старых)","c":"mnu_hc3"},
            {"k":0,"v":"Не отображать","c":"mnu_hc0"}
        ],"k","v","","","c");

        $("#e_sec_enabled").change(function(){
            var $this = $(this);
            var label = $("label[for="+ $this.attr("id") +"]");
            if ($this.prop("checked")) { label.removeClass("text-danger"); }
            else { label.addClass("text-danger"); }
        });

        $("#e_sec_imgfile_btn,#e_sec_imgfile").click(function(e){
            $("#e_sec_imgfile_inp").trigger("click");
        });

        $("#e_sec_imgfile_btn_url").click($.proxy(_this.onSecImageUrl,_this));
        $("#e_sec_imgfile_btn_drop").click($.proxy(_this.onSecImageDrop,_this));

        _this.secImageInp.fileupload({
            dataType: "json",
            replaceFileInput:false,
            dropZone:_this.modalDiv,
            //
            add: function (e, data) {
                _this.setFileToImage(data.files[0]);
                if (_this.secItem.section_id===0) {
                    _this.secImageData = data;
                    _this.secImageChosen = true;
                } else {
                    var ajaxUrl = "/ajx/";
                    ajaxUrl += document.akcms.secs[_this.secItem.section_id]["sec_url_full"];
                    data.url = ajaxUrl+"_seciupl";
                    data.submit();
                    _this.secItem.sec_imgfile = _this.secItem.section_id+".jpg";
                }
            },
            formData: function(){
                return [{"name":"section_id","value":_this.secItem.section_id}];
            },
            //autoUpload: false,
            done: function (e, data) {
                console.log(data);
                $.each(data.result.files, function (index, file) {
                    $("<p/>").text(file.name).appendTo(document.body);
                });
            }
        });

        $(document).bind("drop dragover", function (e) {
            e.preventDefault();
        });

        // $(document).ready(function(){
        //
        //     // Fetch all the forms we want to apply custom Bootstrap validation styles to
        //     var forms = document.getElementsByClassName("needs-validation");
        //
        //     // Loop over them and prevent submission
        //     var validation = Array.prototype.filter.call(forms, function(form) {
        //         form.addEventListener("submit", function(event) {
        //             if (form.checkValidity() === false) {
        //                 event.preventDefault();
        //                 event.stopPropagation();
        //             }
        //             form.classList.add("was-validated");
        //         }, false);
        //     });
        // });

    }

};