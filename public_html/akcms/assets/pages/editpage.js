var _akcms = _akcms || {};
_akcms.loadaing = {
    template:$('<div class="loading"></div>'),
    tag:null,
    start: function(){this.tag = this.template.clone().appendTo(document.body);},
    finish: function(){if (this.tag!==null) this.tag.remove();}
};
_akcms.ru2lt = {
    t : function (inp) {
        var a = inp.toLowerCase().split("");
        for (var i=0,aL=a.length;i<aL;i++) {var c = this.ru2en[a[i]]; a[i] = c==null?a[i]:c}
        var s =  a.join("")
        return s.replace(/[^0-9a-z_-]/g, '');
    },
    init:function() {
        this.ru_str = "абвгдеёжзийклмнопрстуфхцчшщъыьэюя№ ";
        this.lt_str = ['a','b','v','g','d','e','e','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f',
            'h','c','ch','sh','shh','','y','','e','yu','ya','n','_'];
        this.ru2en = {};
        for(var i = 0,l = this.ru_str.length; i < l; i++)
            this.ru2en[this.ru_str.charAt(i)] = this.lt_str[i];
    }
};_akcms.ru2lt.init();
_akcms.alerts={
    template: $('<div class="alert alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'),
    show:function(msg,type,wait){
        var el = this.template.clone().prepend('<span>'+msg+'</span>');
        if (type!==undefined) el.addClass('alert-'+type);
        el.prependTo($('#alertContainer')).alert();
        if (wait!==undefined && wait>0)
        window.setTimeout(function () {
            el.alert('close');
        },wait);
    },
    primary:function (msg) {this.show(msg,'primary');},
    secondary:function (msg) {this.show(msg,'secondary');},
    success:function (msg) {this.show(msg,'success',5000);},
    danger:function (msg) {this.show(msg,'danger',0);},
    warning:function (msg) {this.show(msg,'warning',0);},
    info:function (msg) {this.show(msg,'info',5000);},
    light:function (msg) {this.show(msg,'light');},
    dark:function (msg) {this.show(msg,'dark');}
};
_akcms.floodSelect=function(selTag,selItms,_id,_name,defId,promptText,_class) {
    selTag.empty();
    if (promptText!==undefined && promptText!=='') selTag.append($('<option value="A-" style="font-weight:bold;"/>').text(promptText));
    $.each(selItms,function(n,selItm) {
        var option = $('<option/>').text(selItm[_name]).val(selItm[_id]).appendTo(selTag);
        if (selItm[_class]!==undefined) option.addClass(selItm[_class]);
    });
    if (defId!==undefined && defId!=='') selTag.val(defId);
};
_akcms.editPage = {
    getNodeId:function(el){
        return el.closest('li').prop('id');
    },
    secItem:{},
    form:null,
    makeEditPage:function(parentId,secId,closeEvent){
        var _this = this;
        this.form.classList.remove('was-validated');
        _this.secItem = secId!==0?document.akcms.secs[secId]:{
            "section_id":0,
            "sec_parent_id":parentId,
            "sec_url":'',
            "sec_nameshort":'',
            "sec_namefull":'',
            "sec_showinmenu":"t",
            "sec_openfirst":"f",
            "sec_title":"",
            "sec_keywords":"",
            "sec_description":"",
            'sec_enabled':'f',
            'sec_howchild':1,
            '_p_hc':0,
            'sec_from':'',
            'sec_page_child':'text',
            'sec_page':(parentId!==0?document.akcms.secs[parentId].sec_page_child:''),
            'sec_imgfile':''
        };
        $('#e_sec__head').html((secId===0?'Добавление':'Редактирование')+' страницы');

        $('#e_sec_namefull').val(_this.secItem.sec_namefull);
        $('#e_sec_nameshort').val(_this.secItem.sec_nameshort);
        $('#e_sec_url').val(_this.secItem.sec_url);
        if (_this.secItem.sec_from==='') {
            var d = new Date(); //d.setSeconds(0); d.setMinutes(0); d.setHours(30);
            $('#e_sec_from').data('Zebra_DatePicker').set_date(d);
        }
        else $('#e_sec_from').data('Zebra_DatePicker').set_date(_this.secItem.sec_from); //$('#e_sec_from').prop('value',_this.secItem.sec_from);
        $('#e_sec_howchild').val(_this.secItem.sec_howchild);
        _akcms.floodSelect($('#e_sec_page'),document.akcms.sec_pages,'k','v',_this.secItem.sec_page,'','c');
        $('#e_sec_title').val(_this.secItem.sec_title);
        $('#e_sec_description').val(_this.secItem.sec_description);
        $('#e_sec_keywords').val(_this.secItem.sec_keywords);
        $('#e_sec_showinmenu').prop('checked',_this.secItem.sec_showinmenu==='t');
        $('#e_sec_openfirst').prop('checked',_this.secItem.sec_openfirst==='t');
        $('#e_sec_enabled').prop('checked',_this.secItem.sec_enabled==='t').change();
        if (_this.secItem.sec_imgfile==='') {
            $('#e_sec_imgfile').attr('src', '').attr('data-src', 'holder.js/100x100?text=Загрузить&size=9&fg=#777');
            Holder.run({images: '#e_sec_imgfile'});
        }
        else $('#e_sec_imgfile').prop('src','/img/pages/nt/'+_this.secItem.sec_imgfile+'?'+Math.random());
        $('#makeEditPageGo').text(secId===0?'Создать':'Сохранить');
        $('#modalMakeEditSec').collapse('show');
    },
    onBtnSave: function(){
        var _this = this;

        var e_sec_url = $('#e_sec_url');
        var e_sec_namefull = $('#e_sec_namefull');
        var e_sec_nameshort = $('#e_sec_nameshort');

        if (e_sec_nameshort.val()==='') e_sec_nameshort.val( e_sec_namefull.val() );
        if (e_sec_url.val()==='') e_sec_url.val( _akcms.ru2lt.t( e_sec_nameshort.val() ) );

        if (this.form.checkValidity()) {
            _this.secItem.sec_namefull = $('#e_sec_namefull').val();
            _this.secItem.sec_nameshort = $('#e_sec_nameshort').val();
            _this.secItem.sec_url = $('#e_sec_url').val();
            _this.secItem.sec_from = $('#e_sec_from').val();
            _this.secItem.sec_howchild = $('#e_sec_howchild').val();
            _this.secItem.sec_page = $('#e_sec_page').val();
            _this.secItem.sec_title = $('#e_sec_title').val();
            _this.secItem.sec_description = $('#e_sec_description').val();
            _this.secItem.sec_keywords = $('#e_sec_keywords').val();
            _this.secItem.sec_showinmenu = $('#e_sec_showinmenu').prop('checked')?'t':'f';
            _this.secItem.sec_openfirst = $('#e_sec_openfirst').prop('checked')?'t':'f';
            _this.secItem.sec_enabled = $('#e_sec_enabled').prop('checked')?'t':'f';
            //_this.secItem.sec_to_news = $('#e_sec_to_news').prop('checked')?'t':'f';

            var ajaxUrl = '/ajx/';
            if (_this.secItem.section_id!==0) ajaxUrl += document.akcms.secs[_this.secItem.section_id]['sec_url_full'];
            else if (parentId!==0) ajaxUrl += document.akcms.secs[_this.secItem.sec_parent_id]['sec_url_full'];

            _akcms.loadaing.start();
            $.ajax({
                type: 'POST',
                url: ajaxUrl+'_sec'+(_this.secItem.section_id!==0?'sve':'ins'),
                data: _this.secItem,
                //processData: false,
                //contentType: false,
                dataType: 'json'
            }).done(function(sres) {
                _akcms.loadaing.finish();
                if (typeof(sres.r)!=='undefined'?sres.r==='t':false) {
                    document.akcms.secs[_this.secItem.section_id] = _this.secItem;
                    _akcms.alerts.success('Изменения сохранены');
                    $('#modalMakeEditSec').collapse('hide');
                } else {
                    sres.error.each(function(error){
                        if (error.f==='!' && error.s==='!') {
                            _akcms.alerts.warning('Недостаточно привеллегий!');
                            alert('Недостаточно привеллегий!');
                        }
                        else {
                            $('#e_' + error.f).addClass('is-invalid');
                            //errTips.newTip('e_' + error.f, error.s);
                        }
                    });
                }
            }).fail(function(jqXHR, textStatus) {
                _akcms.loadaing.finish();
                _akcms.alerts.warning('Неудалось сохранить! ' + textStatus)
            });
        }
        this.form.classList.add('was-validated');
    },
    bindEnterTab: function(){
        var _this = this;
        $(_this.form).find('input,select').keypress(function(event){
            if(event.which === 13 && $(this).prop('type')!=='submit') {
                event.preventDefault(); event.stopPropagation();
                var list = $(_this.form).find('input,select,textarea,button');
                var i = $.inArray(this,list)+1; if (i>=list.length) i = 0;
                list.eq(i).focus();
            }
        });
    },
    init:function(){
        var _this = this;
        _this.form=$('#makeEditPageGo').closest('form')[0];
        _this.form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();
            _this.onBtnSave();
        }, false);
        _this.bindEnterTab();
        _akcms.loadaing.start();
        $(document.body).append($('<div class="admcntrl_cnt inedit"><div id="admcntrl" class="admcntrl"><nobr><img class="admlogo" width="212" height="19" src="/img/adm/adm_logo.png" alt=""><img id="admexit" src="/img/edt/btnlgout.png" title="Выход"><a href="/_/"><img src="/img/edt/btnhome.png" title="На главную редактора"></a><a id="akcms_viewPage" href="#"><img src="/img/edt/btnview.png" title="Посмотреть страницу"></a></nobr></div></div>'));
        $('#admexit').click(function(){if (confirm('Выйти из панели управления?')) document.location='/_logout/';});

        //https://fontawesome.com/v4.7.0/icons/
        var treeLoaded = false,$treeview = $('#treeAdmMenu').treeview({
            expandIcon: "node-button fa fa-plus-square",
            collapseIcon: "node-button fa fa-minus-square",
            emptyIcon: 'emptyIcon',
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
            links:true,
            preventUnselect:true,
            tagsClass:'badge badge-secondary',
            onNodeSelected: function(event, node) {
                if (treeLoaded) {
                    history.pushState(null, null, '/_'+node.href);
                    _akcms.loadaing.start();
                    $.ajax({
                        type: 'POST',
                        url: '/ajx'+node.href+'/_sec_data',
                        data: [],
                        success: function (sres) {
                            document.akcms.currpage = sres;
                            _akcms.loadaing.finish();
                        },
                        dataType: 'json'
                    });
                    //document.location.href=node.href;
                }
                //console.log(node);
            },
            onRendered:function(e){
                //$(e.target).treeview("collapseAll", { silent: true })
                $treeview.collapseAll();
                $.each($treeview.getNodes(),function (n,node) {
                    if (currpage.id===node.id) {
                        $treeview.revealNode(node);
                        $treeview.selectNode(node);
                    }
                    node.$el.append(
                        $('<div class="node-buttons">' +
                        '<i class="node-button sec_new fa fa-plus-square" data-toggle="tooltip" data-placement="top" title="Создать дочернюю страницу"></i> ' +
                        '<i class="node-button sec_edit fa fa-pencil-square" data-toggle="tooltip" data-placement="top" title="Редактировать страницу"></i>' +
                        '</div>')
                    );
                    //console.log(node);
                });

                $('.sec_new' ).click(function(e){ e.preventDefault(); e.stopPropagation(); _this.makeEditPage(_this.getNodeId($(this)),0); });
                $('.sec_edit').click(function(e){ e.preventDefault(); e.stopPropagation(); _this.makeEditPage(0,_this.getNodeId($(this))); });
                treeLoaded = true;
                $('[data-toggle="tooltip"]').tooltip();
                _akcms.loadaing.finish();
            },
            data: document.akcms.treeViewData
        }).treeview(true);

        $('#e_sec_from').Zebra_DatePicker({
            'format': 'Y-m-d H:i:s',
            default_position:'below',
            show_icon:false,
            show_week_number: 'Нед',
            months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            //days: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Субота'],
            days_abbr: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            lang_clear_date: 'Очистить',
            show_select_today: 'Сегодня'
        });

        _akcms.floodSelect($('#e_sec_howchild'),[
            {'k':1,'v':'По порядку'},
            {'k':2,'v':'По дате (с новых)','c':'mnu_hc2'},
            {'k':3,'v':'По дате (со старых)','c':'mnu_hc3'},
            {'k':0,'v':'Не отображать','c':'mnu_hc0'}
        ],'k','v','','','c');

        $('#e_sec_enabled').change(function(){
            $this = $(this);
            $label = $('label[for="'+ $this.attr('id') +'"]');
            if ($this.prop('checked')) $label.removeClass('text-danger');
            else $label.addClass('text-danger');
        });

        // $(document).ready(function(){
        //
        //     // Fetch all the forms we want to apply custom Bootstrap validation styles to
        //     var forms = document.getElementsByClassName('needs-validation');
        //
        //     // Loop over them and prevent submission
        //     var validation = Array.prototype.filter.call(forms, function(form) {
        //         form.addEventListener('submit', function(event) {
        //             if (form.checkValidity() === false) {
        //                 event.preventDefault();
        //                 event.stopPropagation();
        //             }
        //             form.classList.add('was-validated');
        //         }, false);
        //     });
        // });

    }

};