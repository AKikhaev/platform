window.addEvent('domready', function() {
	var myScript = Asset.javascript('/akcms/js/v1/obj_glr_ed.js', {onLoad: function(){}});
	var editorLoaded = false;
	var EditAdvert = function(catitem_id) {
		var item = addata;
		if (!editorLoaded) {
			editorLoaded = true;
			tinyMCE.init({
				mode : "textareas",
				theme : "advanced",
				plugins : "safari,table,advimage,inlinepopups,searchreplace,print,contextmenu,paste,fullscreen,noneditable,nonbreaking,visualchars", //,autosave
				file_browser_callback : "tinyBrowser",
				//content_css : "/akcms/css/v1/content.css",
				theme_advanced_buttons1 : "fullscreen,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,|,cleanup,|,forecolor,backcolor", //,restoredraft
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,grabimg,|,visualchars,code",
				theme_advanced_buttons3 : "tablecontrols,|,removeformat,visualaid,|,sub,sup,|,charmap,nonbreaking,|,print",
				theme_advanced_toolbar_location : "top",
				//theme_advanced_toolbar_location : "external",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,
				editor_selector : "_e_cnt_short",
				invalid_elements : "iframe,script",
				language : 'ru',
				relative_urls : false,
				setup : grabimgs_tiny
			});
		};	
		var modalForm = new ModalBox({allowManualClose: false,width:1050,top:25,onClose: function() {
			tinyMCE.execCommand('mceRemoveControl', false, 'e_ad_desc');			
		}});
		var item_actpanel = new Element('div',{'class':'actpanel'});
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		var news_btn_cncl = new Element('img', {'src':'/img/edt/btnund.png','width':'20','height':'16','title':'Отменить'}).
			inject(item_actpanel_cnt).addEvent('click',function() {modalForm.close();});
		var news_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click',function() {
			item_actpanel.addClass('ajax-loading');
			var arrPost = {
				"head":$('e_ad_head').get('value'),
				"title":$('e_ad_title').get('value'),
				"message":tinyMCE.get('e_ad_desc').getContent(),
				"show":$('e_ad_show').checked?'t':'f'
			};
			var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_updad', onComplete: function(sres){
				if (sres=='t') {
					item_actpanel.removeClass('ajax-loading').removeClass('save-error');
					catitem = arrPost;
					window.location.reload();
				} else 
				{
					item_actpanel.removeClass('ajax-loading').addClass('save-error');
					var hideError = function(){ item_actpanel.removeClass('save-error'); };
					hideError.delay(5000);
				}		
			}}).post(arrPost);
		});
		var objGlrDiv = new Element('div',{});
		var tablediv = new Element('table',{'width':600+418,'border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
			new Element('tr').grab(new Element('th',{'colspan':3,'text':'Редактирование объявления:'})),
			new Element('tr').adopt(
				new Element('td',{'colspan':2,'align':'right'}).adopt(
					new Element('input',{'type':'checkbox','id':'e_ad_show','value':'t'}),new Element('label',{'for':'e_ad_show','html':'Показывать объявление'})
				),
				new Element('td',{'rowspan':11,'width':(catitem_id==0?0:412),'align':'left','valign':'top','style':'padding-left:5px;'}).adopt(objGlrDiv)
			),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left','text':'Залоговок*:'})),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left'}).adopt(new Element('input',{'type':'text','id':'e_ad_head',style:"width:99%",'value':item.head}))),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left','text':'Краткий текст*:'})),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left'}).adopt(new Element('input',{'type':'text','id':'e_ad_title',style:"width:99%",'value':item.title}))),			
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left','text':'Описание:'})),
			new Element('tr').grab(new Element('td',{'colspan':2}).grab(new Element('textarea',{'id':'e_ad_desc','style':'height:40px;width:99%;overflow:auto;','text':item.message}))),
			new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
				new Element('br'),item_actpanel
			))
		));
		modalForm.create(document.body,tablediv);
		$('e_ad_show').checked = item.show=='t';
		editObjGalery(objGlrDiv,'Pg_Advert',1,330);
		//var e_datepicker = new DatePicker('#e_news_date', { pickerClass: 'datepicker_vista', format: 'Y-m-d', inputOutputFormat: 'Y-m-d' });
		//$('e_news_date').set('value',e_datepicker.format(new Date(),e_datepicker.options.inputOutputFormat));
		tinyMCE.execCommand('mceAddControl', false, 'e_ad_desc');
	}
	new Element('img', {'src':'/img/edt/btnopt.gif','width':16,'height':16,'title':'Редактор объявления'}).inject($('adedit')).addEvent('click',EditAdvert);
});