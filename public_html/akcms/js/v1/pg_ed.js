var grabimgs_tiny = function (ed) {
    ed.addButton('grabimg', {
        title:'Дублировать изображения',
        image:'/img/edt/btngrb.gif',
        onclick:function () {
            ed.controlManager.setActive('grabimg', true);
            //ed.setProgressState(true);
            var jsonRequest = new Request.JSON({url:'/ajx/' + currpage.pageurl + '_imggrb', onComplete:function (sres) {
                if (sres.error == undefined) {
                    ed.setContent(sres.html);
                    ed.windowManager.alert('Обработанные изображения:\n' + sres.imggrbd);
                } else {
                    ed.windowManager.alert(sres.errmsg == undefined ? "Не удалось выполнить операцию!" : sres.errmsg);
                }
                ed.controlManager.setActive('grabimg', false);
                //ed.setProgressState(false);
            }}).post({"html":ed.getContent()});
        }
    });
};
function ajxImgInfo(di,code) { // 0-hide,1-ajax,2-success,3-error
	switch (code) {
		case 0:
			di.removeClass('ajax-loading').removeClass('save-error').removeClass('save-success');
			break;
		case 1:
			di.addClass('ajax-loading').removeClass('save-error').removeClass('save-success');
			break;
		case 2:
			di.removeClass('ajax-loading').removeClass('save-error').addClass('save-success');
			(function(){ di.removeClass('save-success'); }).delay(2500);
			break;
		case 3:
			di.removeClass('ajax-loading').addClass('save-error').removeClass('save-success');
			(function(){ di.removeClass('save-error'); }).delay(5000);
			break;
	}
}
kcms = {
	floodSelect:function(selTag,selItms,_id,_name,defId,promptText,_class) {
		if (promptText!=undefined && promptText!='') selTag.grab(new Element('option',{'text':promptText,'value':'A-','style':'font-weight:bold;'}));
		selItms.each(function(selItm) {
			selTag.grab(new Element('option',{'class':((_class!=undefined && selItm[_class]!=undefined)?selItm[_class]:''),'text':selItm[_name],'value':selItm[_id],'selected':(typeOf(defId)=='object'?defId[selItm[_id]]!==undefined:defId==selItm[_id])}));
		});
	},
	addHover:function(el,elline) {
		el.addClass('hacts').addEvent('mouseenter', function(){
			this.removeClass('hacts');
			if (elline != undefined) elline.addClass('hvrline');
		}).addEvent('mouseleave', function(){
			if (elline != undefined) elline.removeClass('hvrline');
			if (!this.hasClass('editing')) this.addClass('hacts');
		});
		return el;
	},
	ajxImgInfo:ajxImgInfo,
	errorsShow: function(elroot,errlst){
		errlst.each(function(err){
			var el = elroot.getElementById('e_'+err.f);
			if (el!=null) el.addClass('flderror');
		});
	},
	errorsHide: function(elroot){
		errlst.each(function(err){
			var el = $('e_'+err.f);
			if (el!=null) el.addClass('flderror');
		});
	},
    _exchdata: []
};
window.addEvent('domready', function() {
	var uVer=2; //Версия файлов
	var cnsht_editorLoaded = false;
	var EditSecOpts = function() {
		if (!cnsht_editorLoaded) {
			cnsht_editorLoaded = true;
			tinyMCE.init({
				mode : "textareas",
				theme : "advanced",
				plugins : "safari,table,advimage,inlinepopups,searchreplace,print,contextmenu,paste,fullscreen,noneditable,nonbreaking,visualchars,autosave,spellchecker",
				file_browser_callback : "tinyBrowser",
				content_css : "/akcms/css/v1/txtstyle.css?"+uVer,
				theme_advanced_buttons1 : "fullscreen,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,|,cleanup,|,forecolor,backcolor,restoredraft",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,grabimg,|,visualchars,code",
				theme_advanced_buttons3 : "tablecontrols,|,removeformat,visualaid,|,sub,sup,|,charmap,nonbreaking,|,print,|,spellchecker",
				theme_advanced_toolbar_location : "top",
				//theme_advanced_toolbar_location : "external",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,
				editor_selector : "_e_cnt_short",
				invalid_elements : "", //,script
				language : 'ru',
				relative_urls : false,
				theme_advanced_path : false,
				gecko_spellcheck:true,
				spellchecker_languages : "+Russian=ru,English=en",
				spellchecker_rpc_url : "http://speller.yandex.net/services/tinyspell",
				spellchecker_word_separator_chars : '\\s!"#$%&()*+,./:;<=>?@[\]^_{|}\xa7\xa9\xab\xae\xb1\xb6\xb7\xb8\xbb\xbc\xbd\xbe\u00bf\xd7\xf7\xa4\u201d\u201c',				
				setup : function(ed) {
					grabimgs_tiny(ed);
					var count_func = function(ed, e) {   
						var strip = (tinyMCE.activeEditor.getContent()).replace(/(<([^>]+)>)/ig,"");
						var text =strip.length + " Символов. "; // strip.split(' ').length + " Слов, " +  
						tinymce.DOM.setHTML(tinymce.DOM.get(tinyMCE.activeEditor.id + '_path_row'), text);   
					};
					
					ed.onKeyUp.add(count_func);
					ed.onChange.add(count_func);
					ed.onInit.add(count_func);					
				}						
			});
        }
        var modalForm = new ModalBox({allowManualClose: false,width:650,top:30,onClose: function() {
			tinyMCE.execCommand('mceRemoveControl', false, 'e_cnt_short');
		}});
		var item_actpanel = new Element('div',{'class':'actpanel'});
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		var cnsht_btn_cncl = new Element('img', {'src':'/img/edt/btnund.png','width':'20','height':'16','title':'Отменить'}).
			inject(item_actpanel_cnt).addEvent('click',function() {modalForm.close();});
		var cnsht_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click',function() {
			ajxImgInfo(item_actpanel,1);
			var arrPost = {
				'sec_contshort':tinyMCE.get('e_cnt_short').getContent(),
				'sec_tags':$('e_sec_tags').value,
                'sec_units':[],
				'sec_speclabel':$('e_sec_speclabel').checked?'t':'f'
			};
			var sec_units = [];
            $$('#e_sec_units option').each(function(unititem){
                arrPost.sec_units.push(unititem.value)
				sec_units.push({'k':unititem.get('value'),'v':unititem.get('text')});
            });
			var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_optsve', onComplete: function(sres){
				if (sres=='t') {
					ajxImgInfo(item_actpanel,2); window.location.reload();
				} else ajxImgInfo(item_actpanel,3);		
			}}).post(arrPost);
		});
		var tablediv = new Element('table',{'width':600,'border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
			new Element('tr').grab(new Element('th',{'colspan':2,'text':'Дополнительные параметры:'})),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'center','text':'Краткий текст:'})),
			new Element('tr').grab(new Element('td',{'colspan':2}).grab(new Element('textarea',{'id':'e_cnt_short','style':'height:100px;width:600px;overflow:auto;','text':currpage.sec_contshort}))),
			new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
				new Element('input',{'type':'checkbox','name':'e_sec_speclabel','id':'e_sec_speclabel','value':'t'}),new Element('label',{'for':'e_sec_speclabel','html':'ENG'})
			)),
            new Element('tr').grab(new Element('td',{'colspan':2,'html':'<br/>Модули на странице:'})),
            new Element('tr').grab(new Element('td',{'id':'choicer'}).adopt(
                new Element('select',{'id':'e_sec_all_units','multiple':true,'size':6,'style':'width:170px;height:90px;'}),
                new Element('span',{'html':' => '}),
                new Element('select',{'id':'e_sec_units','multiple':true,'size':6,'style':'width:170px;height:90px;'})
            )),
            new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
                new Element('br'),item_actpanel,new Element('input',{'type':'text','style':'width:500px;','name':'e_sec_tags','id':'e_sec_tags','value':currpage.sec_tags})
            ))
        ));
		modalForm.create(document.body,tablediv);
		$('e_sec_speclabel').checked = currpage.sec_speclabel=='t';
		tinyMCE.execCommand('mceAddControl', false, 'e_cnt_short');
        kcms.floodSelect($('e_sec_all_units'),currpage.sec_all_units,'k','v',-1,'');
        kcms.floodSelect($('e_sec_units'),currpage.sec_units,'k','v',-1,'');
        new Sortables('#choicer select', {
            clone: true,
            revert: true,
            opacity: 0.7
        });
		var tgsbox = new TextboxList('e_sec_tags', {unique: true, plugins: {autocomplete: {method:'binary',placeholder:'Начните ввод чтобы увидеть подсказку'}}});
		var alltags = []; currpage.all_tags.each(function(tag){alltags = alltags.include([tag,tag]);});
		tgsbox.plugins['autocomplete'].setValues(alltags);
	};
	var cnt_editorLoaded = false;
	var addPageEdit = function() {
		var pgcontent = $('pgcontent');
		var oldhtml = pgcontent.get('html');
		var item_actpanel = new Element('div',{'class':'actpanel'}).inject(pgcontent.getParent().getPrevious(),'top');
		//new Element('div',{'class':'clrbth'}).inject(item_actpanel,'after');
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		
		var item_actpanel_top = new Element('div',{'class':'actpanel'}).inject($('admcntrl'),'top');
		var item_actpanel_top_cnt = new Element('nobr').inject(item_actpanel_top);
		
		var save_cnt = function(onSuccess,onFailure) {		
			var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_cntsve', onComplete: function(sres){
				if (sres=='t') {
					oldhtml = tinyMCE.get('pgcontent').getContent();
					if (onSuccess!=null) onSuccess();
				} else if (onFailure!=null) onFailure();	
			}}).post({"sec_content":tinyMCE.get('pgcontent').getContent()});
		};
		
		var sec_btn_edt = new Element('div', {'style':'background-image: url(/img/edt/btnedt.png);width:20;','title':'Редактировать страницу','class':'btn'}).inject(item_actpanel_top_cnt).addEvent('click',function() {
			sec_btn_edt.addClass('hidden');
			MakeEditSec(0,currpage.id,function(){sec_btn_edt.removeClass('hidden');});
		});
		var cnt_btn_pprclp_top = new Element('div', {'style':'background-image: url(/img/edt/btnpprclp.png);','text':'','title':'Заметки','class':'btn'}).inject(item_actpanel_top_cnt).addEvent('click',function() {
			//ajxImgInfo(item_actpanel_top,1);
			var modalForm_rmnd = new ModalBox({allowManualClose: false,width:460,top:50,zindex:2010});
			modalForm_rmnd.create(document.body,new Element('div').grab(
			
				new Element('table',{'width':410,'border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
					new Element('tr').grab(new Element('th',{'colspan':2,'text':'Заметки:'})),
					new Element('tr').grab(new Element('td',{'colspan':2}).grab(new Element('input',{'type':'button','value':'Добавить'})))
				))
			
			
			
			));
			//save_cnt(function(){ajxImgInfo(item_actpanel_top,2);},function(){ajxImgInfo(item_actpanel_top,3);});
		});
		var cnt_btn_sve_top = new Element('div', {'style':'background-image: url(/img/edt/btnsve.png);width:20;','title':'Сохранить и продолжить','class':'btn hidden'}).inject(item_actpanel_top_cnt).addEvent('click',function() {
			ajxImgInfo(item_actpanel_top,1);
			save_cnt(function(){ajxImgInfo(item_actpanel_top,2);},function(){ajxImgInfo(item_actpanel_top,3);});
		});
		var cnt_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить','class':'hidden'}).inject(item_actpanel_cnt).addEvent('click',function() {
			ajxImgInfo(item_actpanel,1);
			save_cnt(function(){
				ajxImgInfo(item_actpanel,2);
				cnt_btn_und.fireEvent('click');
			},function(){
				ajxImgInfo(item_actpanel,3);
			});
		});
		var cnt_btn_edt = new Element('img',{'src':'/img/edt/btnedt.png','title':'Редактировать'}).inject(item_actpanel_cnt).addEvent('click',function() {
			if (!cnt_editorLoaded) {
				cnt_editorLoaded = true;
				tinyMCE.init({
					mode : "textareas",
					theme : "advanced",
					plugins : "safari,table,advimage,inlinepopups,searchreplace,print,contextmenu,paste,fullscreen,noneditable,nonbreaking,visualchars,autosave,spellchecker",
					file_browser_callback : "tinyBrowser",
					content_css : "/akcms/css/v1/txtstyle.css?"+uVer,
					theme_advanced_buttons1 : "save,fullscreen,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,|,cleanup,|,forecolor,backcolor,restoredraft",
					theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,grabimg,|,visualchars,code",
					theme_advanced_buttons3 : "tablecontrols,|,removeformat,visualaid,|,sub,sup,|,charmap,nonbreaking,|,print,|,spellchecker",
					theme_advanced_toolbar_location : "top",
					//theme_advanced_toolbar_location : "external",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true,
					editor_selector : "_cnt_editorarea",
					invalid_elements : "", //,script
					language : 'ru',
					relative_urls : false,
					autosave_ask_before_unload : false,
					gecko_spellcheck:true,
					spellchecker_languages : "+Russian=ru,English=en",
					spellchecker_rpc_url : "http://speller.yandex.net/services/tinyspell",
					spellchecker_word_separator_chars : '\\s!"#$%&()*+,./:;<=>?@[\]^_{|}\xa7\xa9\xab\xae\xb1\xb6\xb7\xb8\xbb\xbc\xbd\xbe\u00bf\xd7\xf7\xa4\u201d\u201c',				
					setup : function(ed) {
						grabimgs_tiny(ed);
						// var save_cnt_tiny = function() {
							// ed.controlManager.setActive('save',true);
							// save_cnt(
								// function(){ed.controlManager.setActive('save',false);},
								// function(){ed.controlManager.setActive('save',false);ed.windowManager.alert("Не удалось сохранить!")}
							// );
						// };
						// ed.addButton('save', {
							// title : 'Сохранить',
							//image : '/img/edt/btnsve_.gif',
							// onclick : save_cnt_tiny
						// });
						// ed.addShortcut('ctrl+s', '', save_cnt_tiny);
					}				
				});
            }
            cnt_btn_edt.addClass('hidden');
			cnt_btn_opt.addClass('hidden');
			cnt_btn_sve.removeClass('hidden');
			cnt_btn_sve_top.removeClass('hidden');
			cnt_btn_und.removeClass('hidden');
			tinyMCE.execCommand('mceAddControl', false, 'pgcontent');			
		});
		var cnt_btn_opt = new Element('img', {'src':'/img/edt/btnopt.gif','width':16,'height':16,'title':'Параметры страницы'}).inject(item_actpanel_cnt).addEvent('click',EditSecOpts);
		var cnt_btn_und = new Element('img', {'src':'/img/edt/btnclse.gif','width':'16','height':'16','title':'Отменить','class':'hidden','style':'margin: 0px 3px;'}).inject(item_actpanel_cnt).addEvent('click',function() {
			tinyMCE.execCommand('mceRemoveControl', false, 'pgcontent');
			pgcontent.set('html',oldhtml);
			cnt_btn_sve.addClass('hidden');
			cnt_btn_sve_top.addClass('hidden');
			cnt_btn_und.addClass('hidden');
			cnt_btn_edt.removeClass('hidden');
			cnt_btn_opt.removeClass('hidden');
		});		
	};
	if (currpage.pageurl.toLowerCase()===currpage.pagemainurl.toLowerCase()) addPageEdit();

	var ru2lt = {
		ru_str : "абвгдеёжзийклмнопрстуфхцчшщъыьэюя№ ",
		lt_str : ['a','b','v','g','d','e','e','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f',
			'h','c','ch','sh','shh','','y','','e','yu','ya','n','_'],
		t : function (inp) {
			var a = inp.split("");
			for (var i=0,aL=a.length;i<aL;i++) {var c = ru2lt.ru2en[a[i]]; a[i] = c==null?a[i]:c}
			var s =  a.join("")
			return s.replace(/[^0-9a-z_-]/g, '');
		},
		init:function() {
			ru2lt.ru2en = {};
			for(var i = 0,l = ru2lt.ru_str.length; i < l; i++)
			ru2lt.ru2en[ru2lt.ru_str.charAt(i)] = ru2lt.lt_str[i];
		}
	};ru2lt.init();
	var MakeEditSec = function(parentId,secId,closeEvent) {
		if (closeEvent==undefined) closeEvent = null;
        var ajaxUrl = '/ajx/';
		if (secId!==0) ajaxUrl += currpage.secs[secId]['sec_url_full'];
		else if (parentId!==0) ajaxUrl += currpage.secs[parentId]['sec_url_full'];
		secitem = secId!=0?currpage.secs[secId]:{"section_id":0,"sec_parent_id":parentId,"sec_url":'',"sec_nameshort":'',"sec_namefull":'',"sec_showinmenu":"t","sec_openfirst":"f","sec_title":"","sec_keywords":"","sec_description":"",'sec_enabled':'f','sec_howchild':1,'_p_hc':0,'sec_page_child':'second','sec_page':(parentId!=0?currpage.secs[parentId].sec_page_child:'')};
		var modalForm = new ModalBox({allowManualClose: false,width:500,top:100,onClose:closeEvent}),
		errTips = new ErrorTips({}),
		item_actpanel = new Element('div',{'class':'actpanel'}),
		item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		
		if (secId!=0 && secId!=1 && secitem['_p_hc']==1) {
			new Element('tbody').inject(new Element('table',{'border':0,'cellspacing':0,'cellpadding':0,'style':'display:inline;'}).inject(new Element('div',{'style':'float:left;'}).inject(item_actpanel_cnt))).adopt(
				new Element('tr').grab(new Element('td').grab(new Element('img',{'src':'/img/edt/btnup.png','title':'Переместить выше, +ctrl: На самый верх'}).addEvent('click', function(e) {
					if (confirm('Переместить раздел '+(e.control?'на самый верх':'выше')+'?'))
					{
						ajxImgInfo(item_actpanel,1);
						var jsonRequest = new Request.JSON({url: ajaxUrl+'_sec'+(e.control?'top':'up'), onComplete: function(sres) {
							if (sres=='t') {
								ajxImgInfo(item_actpanel,2);
								window.location.reload();
							} else ajxImgInfo(item_actpanel,3);
						}}).post({'section_id':secitem['section_id']});
					}
				}))),
				new Element('tr').grab(new Element('td').grab(new Element('img',{'src':'/img/edt/btndwn.png','title':'Переместить ниже, +ctrl: На самый низ'}).addEvent('click', function(e) {
					if (confirm('Переместить раздел '+(e.control?'в самый низ':'ниже')+'?'))
					{
						ajxImgInfo(item_actpanel,1);
						var jsonRequest = new Request.JSON({url: ajaxUrl+'_sec'+(e.control?'bttm':'dwn'), onComplete: function(sres) {
							if (sres=='t') {
								ajxImgInfo(item_actpanel,2);
								window.location.reload();
							} else ajxImgInfo(item_actpanel,3);
						}}).post({'section_id':secitem['section_id']});
					}
				})))
			);
		}
		new Element('img', {'src':'/img/edt/btnund.png','width':'20','height':'16','title':'Отменить'}).
			inject(item_actpanel_cnt).addEvent('click',function() {modalForm.close();});
		if (secId!=0 && secId!=1) new Element('img',{'src':'/img/edt/btndrp.png','title':'Удалить'}).inject(item_actpanel_cnt).addEvent('click', function() {
			if (confirm('Удалить раздел?'))
			{
				ajxImgInfo(item_actpanel,1);
				var jsonRequest = new Request.JSON({url: ajaxUrl+'_secdrp', onComplete: function(sres) {
					if (sres.r!=undefined?sres.r=='t':false) {
						ajxImgInfo(item_actpanel,2);
						if (currpage['id']==secitem['section_id']) window.location='/_/'+(sres.url=='/'?'':sres.url); else window.location.reload();
					} else ajxImgInfo(item_actpanel,3);
				}}).post({'section_id':secitem['section_id']});
			}
		});
		new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click',function() {
			errTips.removeAllTip();
			ajxImgInfo(item_actpanel,1);
			if ($('e_sec_nameshort').get('value')=='' && $('e_sec_namefull').get('value')!='') {
				$('e_sec_nameshort').set('value',$('e_sec_namefull').get('value'));
			}
			if ($('e_sec_nameshort').get('value')!='' && $('e_sec_namefull').get('value')=='') {
				$('e_sec_namefull').set('value',$('e_sec_nameshort').get('value'));
			}
			if ($('e_sec_url').value=='') $('e_sec_url').value = ru2lt.t($('e_sec_nameshort').value.toLowerCase());			
			var arrPost = {
				'section_id': secitem.section_id,
				'sec_parent_id': secitem.sec_parent_id,
				'sec_url': $('e_sec_url').get('value'),
				'sec_nameshort': $('e_sec_nameshort').get('value'),
				'sec_namefull': $('e_sec_namefull').get('value'),
				'sec_title': $('e_sec_title').get('value'),
				'sec_keywords': $('e_sec_keywords').get('value'),
				'sec_description': $('e_sec_description').get('value'),
				'sec_enabled':$('e_sec_enabled').checked?'t':'f',
				'sec_showinmenu':$('e_sec_showinmenu').checked?'t':'f',
				'sec_openfirst':$('e_sec_openfirst').checked?'t':'f',
				'sec_to_news':$('e_sec_to_news').checked?'t':'f',
				'sec_from': $('e_sec_from').get('value'),
				'sec_howchild': $('e_sec_howchild').get('value'),
				'sec_page': $('e_sec_page').get('value')
			};
			var jsonRequest = new Request.JSON({url: ajaxUrl+'_sec'+(secId!=0?'sve':'ins'), onComplete: function(sres){
				console.log(sres);
				if (sres.r!=undefined?sres.r=='t':false) {
					ajxImgInfo(item_actpanel,2);
					secitem = arrPost;
					if (currpage['id']==secitem['section_id'] || secId==0) window.location='/_/'+(sres.url=='/'?'':sres.url); else window.location.reload();
				} else {
					ajxImgInfo(item_actpanel,3);
					//kcms.errorsShow(tablediv,sres.error);
					sres.error.each(function(error){
						if (error.f==='!' && error.s==='!') alert('Недостаточно привеллегий!');
						else errTips.newTip('e_'+error.f,error.s);
					});
				}
			}}).post(arrPost);
		});
		var tablediv = new Element('table',{'width':460,'border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
			new Element('tr').grab(new Element('th',{'colspan':2,'text':(secId==0?'Добавление':'Редактирование')+' раздела:'})),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'center'}).adopt(
				new Element('input',{'type':'checkbox','name':'e_sec_showinmenu','id':'e_sec_showinmenu','value':'t'}),new Element('label',{'for':'e_sec_showinmenu','html':'Отображать'}),
				new Element('input',{'type':'checkbox','name':'e_sec_openfirst','id':'e_sec_openfirst','value':'t'}),new Element('label',{'for':'e_sec_openfirst','html':'Открыть первый подраздел'}),
				new Element('input',{'type':'checkbox','name':'e_sec_to_news','id':'e_sec_to_news','value':'t'}),new Element('label',{'for':'e_sec_to_news','html':'В новости'}),
				new Element('input',{'type':'checkbox','name':'e_sec_enabled','id':'e_sec_enabled','value':'t'}),new Element('label',{'for':'e_sec_enabled','id':'e_sec_enabled_l','html':'Опубликован'})
			)),
			new Element('tr').adopt(
				new Element('td',{'align':'right','html':'<nobr><label for="e_sec_namefull">Название*:</label> &nbsp;</nobr>','width':'100'}),
				new Element('td').grab(new Element('input',{'type':'text','style':'width:300px;','name':'e_sec_namefull','id':'e_sec_namefull','value':secitem.sec_namefull}))
			),
			new Element('tr').adopt(
				new Element('td',{'align':'right','html':'<nobr><label for="e_sec_nameshort" title="Для меню и перечислений подразделов">Краткое название*:</label> &nbsp;</nobr>','nowrap':'nowrap'}),
				new Element('td').grab(new Element('input',{'type':'text','title':'Для меню и перечислений подразделов','style':'width:300px;','name':'e_sec_nameshort','id':'e_sec_nameshort','value':secitem.sec_nameshort}))
			),
			new Element('tr').adopt(
				new Element('td',{'align':'right','html':'<nobr><label for="e_sec_title" title="SEO: Генерируется: название + стандарт, если пусто" style="color: gray;">SEO Заголовок<sup></sup></label>: &nbsp;</nobr>','nowrap':'nowrap'}),
				new Element('td').grab(new Element('input',{'type':'text','title':'Генерируется: название + стандарт, если пусто','style':'width:300px;','name':'e_sec_title','id':'e_sec_title','value':secitem.sec_title}))
			),
			new Element('tr').adopt(
				new Element('td',{'align':'right','html':'<nobr><label for="e_sec_description" title="SEO" style="color: gray;">SEO Описание<sup></sup></label>: &nbsp;</nobr>','nowrap':'nowrap'}),
				new Element('td').grab(new Element('input',{'type':'text','style':'width:300px;','name':'e_sec_description','id':'e_sec_description','value':secitem.sec_description}))
			),
			new Element('tr').adopt(
				new Element('td',{'align':'right','html':'<nobr title="SEO" style="color: gray;"><label for="e_sec_keywords">SEO Слова<sup></sup></label>: &nbsp;</nobr>','nowrap':'nowrap'}),
				new Element('td').grab(new Element('input',{'type':'text','style':'width:300px;','name':'e_sec_keywords','id':'e_sec_keywords','value':secitem.sec_keywords}))
			),
			new Element('tr').adopt(
				new Element('td',{'align':'right','html':'<nobr><label for="e_sec_url">Путь*:</label> &nbsp;</nobr>','nowrap':'nowrap'}),
				new Element('td').grab(new Element('input',{'type':'text','title':'ТОЛЬКО текущая страница латинскими буквами','style':'width:300px;','name':'e_sec_url','id':'e_sec_url','value':secitem.sec_url}))
			),
			new Element('tr').adopt(
				new Element('td',{'align':'right','html':'<nobr><label>Публиковать с*:</label> &nbsp;</nobr>','nowrap':'nowrap'}),
				new Element('td').grab(new Element('input',{'type':'text','style':'width:300px;','name':'e_sec_from','id':'e_sec_from','value':secitem.sec_from}))
			),
			new Element('tr').adopt(
				new Element('td',{'align':'right','html':'<nobr><label for="e_sec_howchild" title="Для отображения меню">Потомки в меню*:</label> &nbsp;</nobr>','nowrap':'nowrap'}),
				new Element('td').grab(new Element('select',{'type':'text','title':'Для отображения меню','style':'width:300px;','name':'e_sec_howchild','id':'e_sec_howchild','value':secitem.sec_howchild}))
			),
			new Element('tr').adopt(
				new Element('td',{'align':'right','html':'<nobr><label for="e_sec_page" title="Оформление страницы">Оформление*:</label> &nbsp;</nobr>','nowrap':'nowrap'}),
				new Element('td').grab(new Element('select',{'type':'text','title':'Для отображения меню','style':'width:300px;','name':'e_sec_page','id':'e_sec_page','value':secitem.sec_page}))
			),
			new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
				new Element('br'),item_actpanel,
				new Element('span',{'html':'<form accept-charset="UTF-8" action="/ajx/'+currpage.pageurl+'_seciupl" method="post" enctype="multipart/form-data" onsubmit="return false" id="upliform" name="upliform">'})
			))
		));

		
		modalForm.create(document.body,tablediv);
		var upliform = $('upliform');
		if (secId!=0) {
			upliform.grab(new Element('span',{'html':' Изображение: '}));
			var phtimg = null,
                cleanPhoto = function (e) {
                    if (e.control) {
                        if (confirm('Удалить изображение?')) {
                            ajxImgInfo(upldiv, 1);
                            var jsonRequest = new Request.JSON({url:ajaxUrl+'_secidrp', onComplete:function (sres) {
                                if (sres == 't') {
                                    ajxImgInfo(upldiv, 2);
                                    if (phtimg != null) phtimg.destroy();
                                } else ajxImgInfo(upldiv, 3);
                            }}).post({'section_id':secId});
                        }
                    } else if (e.shift) if (confirm('Определить центр изображения?')) {
                        ajxImgInfo(upldiv, 1);
                        //var jsonRequest = new Request.JSON({url:ajaxUrl+'_glrcpghdr', onComplete: function(sres) {
                        var jsonRequest = new Request.JSON({url:'/img/fd/', onComplete:function (sres) {
                            if (sres != undefined) {
                                ajxImgInfo(upldiv, 2);
                            } else ajxImgInfo(upldiv, 3);
                        }}).post({'u':'pages/' + secId + '.jpg'});
                    }
                },
                drawPhoto = function () {
                    if (phtimg != null) phtimg.destroy();
                    if (secitem.sec_imgfile != '')
                        phtimg = new Element('img', {'src':'/img/pages/s/' + secitem.sec_imgfile + '?' + Math.floor(Math.random() * 999), 'alt':'', 'title':'ctrl+click чтобы удалить\nshift+click задать центральную точку', 'align':'top', 'hspace':2, 'vspace':1}).inject(uplfie, 'before').addEvent('click', cleanPhoto);
                    else phtimg = null;
                },
                doUpload = function () {
                    ajxImgInfo(upldiv, 1);
                    infodiv.empty();
                    var req = new JsHttpRequest();
                    req.onreadystatechange = function () {
                        if (req.readyState == 4) {
                            ajxImgInfo(upldiv, 0);
                            infodiv.set('html', req.responseJS.msg + req.responseText);
                            (function () {
                                infodiv.empty();
                            }).delay(5000);
                            var uplfie1 = new Element('input', {'type':'file', 'id':'uplfie', 'name':'uplfile'}).addEvent('change', doUpload).inject(uplfie, 'after');
                            uplfie.destroy();
                            uplfie = uplfie1;
                            if (req.responseJS.status == 4) {
                                secitem.sec_imgfile = req.responseJS.i_file;
                                drawPhoto();
                            }
                        }
                    };
                    req.open(null, '/ajx/' + currpage.pageurl + '_seciupl', true);
                    req.send({q:upliform});
                },
                doUploadUrl = function (url) {
                    ajxImgInfo(upldiv, 1);
                    infodiv.empty();
                    var jsonRequest = new Request.JSON({url:ajaxUrl+'_seciuplurl', onComplete:function (sres) {
                        if (sres != undefined ? sres.res == 't' : false) {
                            ajxImgInfo(upldiv, 2);
                            secitem.sec_imgfile = sres.i_file;
                            drawPhoto();
                        } else ajxImgInfo(upldiv, 3);
                        infodiv.set('html', sres != undefined ? sres.msg : '');
                        (function () {
                            infodiv.empty();
                        }).delay(5000);
                    }}).post({'section_id':secId, 'url':url});
                };
			uplfie = new Element('input',{'type':'file','id':'uplfie','name':'uplfile'}).addEvent('change', doUpload);
			upldiv = new Element('span').adopt(
				uplfie,new Element('input',{'name':'section_id','type':'hidden','value':secId}),
				new Element('span',{'text':'[URL]','style':'cursor:pointer;padding-left:2px;'}).addEvent('click',function(){
					var url = prompt('Введите путь к изображению:');
					if (url!=null && url!='') doUploadUrl(url);
				})).inject(upliform);
			infodiv = new Element('span',{'class':'errornotice'}).inject(upliform,'after');
			drawPhoto();
		}		
		var e_datepicker = new DatePicker('#e_sec_from', { timePicker:true, pickerClass: 'datepicker_vista', inputOutputFormat: 'Y-m-d H:i:00', format: 'd M Y H:i' });
		$('e_sec_enabled').checked = secitem.sec_enabled=='t'; $('e_sec_showinmenu').checked = secitem.sec_showinmenu=='t'; $('e_sec_openfirst').checked = secitem.sec_openfirst=='t'; $('e_sec_to_news').checked = secitem.sec_to_news=='t';
		$('e_sec_enabled').addEvent('click',function(){
			$('e_sec_enabled_l').set('style','color:'+($('e_sec_enabled').checked?'white':'red'));
		}).fireEvent('click');
		var chldsort=[
			{'k':1,'v':'По порядку'},
			{'k':2,'v':'По дате (с новых)','c':'mnu_hc2'},
			{'k':3,'v':'По дате (со старых)','c':'mnu_hc3'},
			{'k':0,'v':'Не отображать','c':'mnu_hc0'}
		];
		kcms.floodSelect($('e_sec_howchild'),chldsort,'k','v',secitem.sec_howchild,'','c');
		kcms.floodSelect($('e_sec_page').set('disabled',secId==1),currpage.sec_pages,'k','v',secitem.sec_page,'','c');
		
		(function(){ 
			$('e_sec_namefull').setAttribute('tabIndex',0);
			$('e_sec_namefull').focus();
		}).delay(400);
	};
	var addMenuActions = function(mnudiv) {
		var secid = mnudiv.id.substring(3);
		var secitem = currpage.secs[secid];
		var item_actpanel = new Element('div',{'class':'actpanel'});
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		var sec_btn_edt = new Element('img',{'src':'/img/edt/btnedt.png','title':'Редактировать'}).inject(item_actpanel_cnt).addEvent('click',function() {MakeEditSec(0,secid);});
		item_actpanel.inject(mnudiv,'top');
		if (secitem._selected != undefined)	new Element('span',{'text':' +','style':'color:red;cursor:pointer;font-weight:bold;','title':'Добавить раздел'}).inject(mnudiv.getElement('a'),'after').addEvent('click', function() {MakeEditSec(secid,0);});	
	};
	var mainmenu = $('mnu');
	mainmenu.getElements('li.mnuitem').each(function(itemdiv){addMenuActions(kcms.addHover(itemdiv));});
	new Element('span',{'text':' +','style':'color:red;cursor:pointer;font-weight:bold;','title':'Добавить раздел'}).inject(mainmenu).addEvent('click', function() {MakeEditSec(0,0);});
});