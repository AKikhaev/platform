window.addEvent('domready', function() {
	var u_items = gbi.i,answerers=gbi.a,alltags=gbi.t,idpref='e_gb_';
	var u_editorLoaded = false;
	var MakeEditUnitI = function (uitem_id) {
        var uitem = u_items[uitem_id];
        if (!u_editorLoaded) {
            news_editorLoaded = true;
            tinyMCE.init({
                mode:"textareas",
                theme:"advanced",
                plugins:"safari,table,advimage,inlinepopups,searchreplace,print,contextmenu,paste,fullscreen,noneditable,nonbreaking,visualchars,autosave", //
                file_browser_callback:"tinyBrowser",
                //content_css : "/css/content.css",
                theme_advanced_buttons1:"fullscreen,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,|,cleanup,|,forecolor,backcolor,restoredraft",
                theme_advanced_buttons2:"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,grabimg,|,visualchars,code",
                theme_advanced_buttons3:"tablecontrols,|,removeformat,visualaid,|,sub,sup,|,charmap,nonbreaking,|,print",
                theme_advanced_toolbar_location:"top",
                //theme_advanced_toolbar_location : "external",
                theme_advanced_toolbar_align:"left",
                theme_advanced_statusbar_location:"bottom",
                theme_advanced_resizing:true,
                editor_selector:"_e_ee",
                invalid_elements:"iframe,script",
                language:'ru',
                relative_urls:false,
                setup:grabimgs_tiny
            });
        }
        var modalForm = new ModalBox({allowManualClose:false, width:650, top:30, onClose:function () {
            tinyMCE.execCommand('mceRemoveControl', false, idpref + 'answer');
        }});
        var item_actpanel = new Element('div', {'class':'actpanel'});
        var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
        var gb_btn_cncl = new Element('img', {'src':'/img/edt/btnund.png', 'width':'20', 'height':'16', 'title':'Отменить'}).
            inject(item_actpanel_cnt).addEvent('click', function () {
                modalForm.close();
            });
        var gb_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png', 'width':20, 'height':16, 'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click', function () {
            ajxImgInfo(item_actpanel, 1);
            var arrPost = {
                "gb_id":uitem.gb_id,
                "gb_name":$(idpref + 'name').get('value'),
                "gb_message":$(idpref + 'message').get('value'),
                "gb_answer":tinyMCE.get(idpref + 'answer').getContent(),
                "gb_answerer_id":$(idpref + 'answerer').get('value'),
                "gb_tags":$(idpref + 'tags').get('value'),
				"gb_sendmail":$(idpref + 'sendmail').checked ? 't' : 'f',
                "gb_enabled":$(idpref + 'enabled').checked ? 't' : 'f'
            };
            var jsonRequest = new Request.JSON({url:'/ajx/' + currpage.pageurl + '_gbsve', onComplete:function (sres) {
                if (sres == 't') {
                    ajxImgInfo(item_actpanel, 2);
                    uitem = arrPost;
                    window.location.reload();
                } else ajxImgInfo(item_actpanel, 3);
            }}).post(arrPost);
        });
        var tablediv = new Element('table', {'width':600, 'border':0, 'align':'center', 'cellpadding':0, 'cellspacing':0}).grab(new Element('tbody').adopt(
            new Element('tr').grab(new Element('th', {'colspan':2, 'text':'Редактирование сообщения:'})),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center'}).adopt(
                new Element('span', {'text':'Дата: ' + uitem.gb_date + ' Email:' + uitem.gb_email})
            )),
            new Element('tr').adopt(
                new Element('td', {'text':'Имя*:', style:'width:65px'}),
                new Element('td').grab(new Element('input', {'type':'text', 'id':idpref + 'name', style:'width:99%', 'value':uitem.gb_name}))
            ),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center', 'text':'Сообщение*:'})),
            new Element('tr').grab(new Element('td', {'colspan':2}).grab(new Element('textarea', {'id':idpref + 'message', 'style':'height:100px;width:600px;overflow:auto;', 'text':uitem.gb_message}))),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center', 'text':'Ответ:'})),
            new Element('tr').grab(new Element('td', {'colspan':2}).grab(new Element('textarea', {'id':idpref + 'answer', 'style':'height:100px;width:600px;overflow:auto;', 'text':uitem.gb_answer}))),
            new Element('tr').adopt(
                new Element('td', {'text':'Отвечает:'}),
                new Element('td').grab(new Element('select', {'id':idpref + 'answerer', 'style':'width:99%;'}))
            ),
            new Element('tr').grab(new Element('td', {'colspan':2}).adopt(
                new Element('input', {'type':'checkbox', 'id':idpref + 'sendmail', 'value':'f'}), new Element('label', {'for':idpref + 'sendmail', 'html':'Отправить email пользователю'}),
                new Element('input', {'type':'checkbox', 'id':idpref + 'enabled', 'value':'t'}), new Element('label', {'for':idpref + 'enabled', 'html':'Показывать вопрос'})
            )),
            new Element('tr').grab(new Element('td', {'colspan':2}).adopt(
                item_actpanel, new Element('input', {'type':'text', 'style':'width:600px;', 'id':idpref + 'tags', 'value':uitem.tags})
            ))
        ));
        modalForm.create(document.body, tablediv);
        $(idpref + 'enabled').checked = uitem.gb_enabled == 't';
        tinyMCE.execCommand('mceAddControl', false, idpref + 'answer');
        kcms.floodSelect($(idpref + 'answerer'), answerers, 'k', 'v', uitem.gb_answerer_id, 'Отвечает:');
        var tgsbox = new TextboxList(idpref + 'tags', {unique:true, plugins:{autocomplete:{method:'binary', placeholder:'Начните ввод чтобы увидеть подсказку'}}});
        var alltags_ = [];
        alltags.each(function (tag) {
            alltags_ = alltags_.include([tag, tag]);
        });
        tgsbox.plugins['autocomplete'].setValues(alltags_);
    };
		
	var UnitProcess = function(uitem_div) {
		var uitem_id = uitem_div.id.substring(3),
		uitem = u_items[uitem_id],
		item_actpanel = new Element('div',{'class':'actpanel'}).inject(uitem_div),
		item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		new Element('div',{'class':'clrbth'}).inject(uitem_div);
		
		var gb_btn_edt = new Element('img',{'src':'/img/edt/btnedt.png','title':'Редактировать'}).inject(item_actpanel_cnt).addEvent('click',function() {MakeEditUnitI(uitem_id); });
	
		var gb_btn_drp = new Element('img',{'src':'/img/edt/btndrp.png','title':'Удалить'}).inject(item_actpanel_cnt).addEvent('click', function() {
			if (confirm('Удалить сообщение?'))
			{
				ajxImgInfo(item_actpanel,1);
				var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_gbdrp', onComplete: function(sres) {
					if (sres=='t') {
						uitem_div.destroy();
					} else ajxImgInfo(item_actpanel,3);
				}}).post({'gb_id':uitem['gb_id']});
			}
		});
			
		//alert(uitem_id);
	};

	$('gstbk').getElements('div.gbitem').each(function(uitem) { UnitProcess(uitem); });
});