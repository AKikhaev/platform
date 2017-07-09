window.addEvent('domready', function() {
	var myScript = Asset.javascript('/akcms/js/v1/obj_glr_ed.js', {onLoad: function(){}});
	var news_editorLoaded = false;
	var MakeEditCati = function(catitem_id) {
		var catitem = catitem_id!=0?cati[catitem_id]:{"cati_id":"0","cati_nameshort":"","cati_namefull":"","cati_photofile":"","cati_desc":"","cati_sec_id":null,"cati_show":"t","cati_bcost":"","cati_cost":0,"cati_costold":0,"cati_artcl":""};
		if (!news_editorLoaded) {
			news_editorLoaded = true;
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
		var modalForm = new ModalBox({allowManualClose: false,width:650+(catitem_id==0?0:400),top:25,onClose: function() {
			tinyMCE.execCommand('mceRemoveControl', false, 'e_cati_desc');			
		}});
		var item_actpanel = new Element('div',{'class':'actpanel'});
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		var news_btn_cncl = new Element('img', {'src':'/img/edt/btnund.png','width':'20','height':'16','title':'Отменить'}).
			inject(item_actpanel_cnt).addEvent('click',function() {modalForm.close();});
		var news_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click',function() {
			item_actpanel.addClass('ajax-loading');
			var arrPost = {
				"cati_id":catitem.cati_id,
				"cati_nameshort":$('e_cati_nameshort').get('value'),
				"cati_namefull":$('e_cati_namefull').get('value'),
				"cati_desc":tinyMCE.get('e_cati_desc').getContent(),
				"cati_sec_id":null,
				"cati_bcost":$('e_cati_bcost').get('value'),
				"cati_show":$('e_cati_show').checked?'t':'f',
				"cati_cost":$('e_cati_cost').get('value'),
				"cati_costold":$('e_cati_costold').get('value'),
				"cati_artcl":$('e_cati_artcl').get('value')
			};
			var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_catisve', onComplete: function(sres){
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
		var tablediv = new Element('table',{'width':600+(catitem_id==0?0:(412+6)),'border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
			new Element('tr').grab(new Element('th',{'colspan':3,'text':(catitem_id==0?'Добавление':'Редактирование')+' товара:'})),
			new Element('tr').adopt(
				new Element('td',{'colspan':2,'align':'right'}).adopt(
					new Element('input',{'type':'checkbox','name':'e_news_enabled','id':'e_cati_show','value':'t'}),new Element('label',{'for':'e_cati_show','html':'Показывать товар'})
				),
				new Element('td',{'rowspan':11,'width':(catitem_id==0?0:412),'align':'left','valign':'top','style':'padding-left:5px;'}).adopt(objGlrDiv)
			),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left','text':'Краткий заголовок*:'})),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left'}).adopt(new Element('input',{'type':'text','name':'e_cati_nameshort','id':'e_cati_nameshort',style:"width:99%",'value':catitem.cati_nameshort}))),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left','text':'Полный заголовок*:'})),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left'}).adopt(new Element('input',{'type':'text','name':'e_cati_namefull','id':'e_cati_namefull',style:"width:99%",'value':catitem.cati_namefull}))),
			
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left','text':'Описание:'})),
			new Element('tr').grab(new Element('td',{'colspan':2}).grab(new Element('textarea',{'id':'e_cati_desc','style':'height:40px;width:99%;overflow:auto;','text':catitem.cati_desc}))),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left'}).adopt(
				new Element('label',{'for':'e_cati_bcost','html':' от/по/дог.: '}),new Element('input',{'type':'text','name':'e_cati_bcost','id':'e_cati_bcost','value':catitem.cati_bcost}),
				new Element('label',{'for':'e_cati_cost','html':' Цена: '}),new Element('input',{'type':'text','name':'e_cati_cost','id':'e_cati_cost','value':catitem.cati_cost})
			)),
			new Element('tr').grab(new Element('td',{'colspan':2,'align':'left'}).adopt(
				new Element('label',{'for':'e_cati_costold','html':' Артикул: '}),new Element('input',{'type':'text','name':'e_cati_artcl','id':'e_cati_artcl','value':catitem.cati_artcl}),
				new Element('label',{'for':'e_cati_costold','html':' Cтарая цена: '}),new Element('input',{'type':'text','name':'e_cati_costold','id':'e_cati_costold','value':catitem.cati_costold})
			)),
			new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
				new Element('br'),item_actpanel,
				new Element('span',{'html':'<form accept-charset="UTF-8" action="/ajx/'+currpage.pageurl+'_catiiupl" method="post" enctype="multipart/form-data" onsubmit="return false" id="upliform" name="upliform">'})
			))
		));
		modalForm.create(document.body,tablediv);
		var upliform = $('upliform');
		if (catitem_id!=0) {
			upliform.grab(new Element('span',{'html':' Изображение: '}));
			var phtimg = null,
			cleanPhoto = function(e) {
				if (e.control) {
					if (confirm('Удалить изображение?')) {
						upldiv.addClass('ajax-loading');
						var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_catiidrp', onComplete: function(sres) {
							if (sres=='t') {
								upldiv.removeClass('ajax-loading');
								if (phtimg!=null) phtimg.destroy();
							} else 
							{
								upldiv.removeClass('ajax-loading').addClass('save-error');
								var hideError = function(){ upldiv.removeClass('save-error'); };
								hideError.delay(5000);
							}
						}}).post({'cati_id':catitem_id});
					}
				}
			},
			drawPhoto = function() {
				if (phtimg!=null) phtimg.destroy();
				if (catitem.cati_photofile!='')
					phtimg = new Element('img',{'src':'/img/cat/s/'+catitem.cati_photofile+'?'+Math.floor(Math.random()*999),'alt':'','title':'ctrl+click чтобы удалить','align':'top','hspace':2,'vspace':1}).inject(uplfie,'before').addEvent('click',cleanPhoto);
				else phtimg=null;
			},
			doUpload = function() {
				upldiv.addClass('ajax-loading');
				infodiv.empty();
				var req = new JsHttpRequest();
				req.onreadystatechange = function() {
					if (req.readyState == 4) {
						upldiv.removeClass('ajax-loading');
						infodiv.set('html',req.responseJS.msg+req.responseText);
						var uplfie1 = new Element('input',{'type':'file','id':'uplfie','name':'uplfile'}).addEvent('change', doUpload).inject(uplfie,'after');
						uplfie.destroy(); uplfie = uplfie1;
						if (req.responseJS.status==4) {
							catitem.cati_photofile = req.responseJS.i_file;
							drawPhoto();
						}
					}
				}
				req.open(null, '/ajx/'+currpage.pageurl+'_catiiupl', true);
				req.send({q:upliform});
			},
			uplfie = new Element('input',{'type':'file','id':'uplfie','name':'uplfile'}).addEvent('change', doUpload),
			infodiv = new Element('span',{'class':'errornotice','style':'padding-left: 10px;'}),
			upldiv = new Element('span').adopt(uplfie,new Element('input',{'name':'cati_id','type':'hidden','value':catitem_id}),infodiv).inject(upliform);
			drawPhoto();
			editObjGalery(objGlrDiv,'cati',catitem_id,330);
		}
		$('e_cati_show').checked = catitem.cati_show=='t';
		//var e_datepicker = new DatePicker('#e_news_date', { pickerClass: 'datepicker_vista', format: 'Y-m-d', inputOutputFormat: 'Y-m-d' });
		//$('e_news_date').set('value',e_datepicker.format(new Date(),e_datepicker.options.inputOutputFormat));
		tinyMCE.execCommand('mceAddControl', false, 'e_cati_desc');
	}
	var EditSortOrder = function() {
        var modalForm = new ModalBox({allowManualClose: false,width:540,top:30,onClose: function() {}});
		var item_actpanel = new Element('div',{'class':'actpanel'});
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		new Element('img', {'src':'/img/edt/btnund.png','width':'20','height':'16','title':'Отменить'}).
			inject(item_actpanel_cnt).addEvent('click',function() {modalForm.close();});
		new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click',function() {
			ajxImgInfo(item_actpanel,1);
			var arrPost = {
                'itm_order':[]
			};
            $$('#e_srtr_all_order option').each(function(unititem){
                arrPost.itm_order.push(unititem.value)
            });
			new Request.JSON({url: '/ajx/'+currpage.pageurl+'_catsetordr', onComplete: function(sres){
				if (sres=='t') {
					ajxImgInfo(item_actpanel,2); window.location.reload();
				} else ajxImgInfo(item_actpanel,3);		
			}}).post(arrPost);
		});
		var tablediv = new Element('table',{'width':'99%','border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
			new Element('tr').grab(new Element('th',{'colspan':2,'text':'Дополнительные параметры:'})),
            new Element('tr').grab(new Element('td',{'colspan':2,'id':'e_srtr_loader','html':'<br/>Порядок сортировки изделий:'})),
            new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
                new Element('select',{'id':'e_srtr_all_order','size':12,'style':'width:99%;height:200px;'})
            )),
            new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
                new Element('br'),item_actpanel
            ))

        ));
		modalForm.create(document.body,tablediv);
		ajxImgInfo($('e_srtr_loader'),1);
		new Request.JSON({url: '/ajx/'+currpage.pageurl+'_catlist', onComplete: function(sres){
			if (sres!=undefined && sres.r=='t') {
				ajxImgInfo($('e_srtr_loader'),0);
				kcms.floodSelect($('e_srtr_all_order'),sres.d,'k','v',-1,'');
				new Sortables('#e_srtr_all_order', {
					clone: true,
					revert: true,
					opacity: 0.7
				});
			} else ajxImgInfo($('e_srtr_loader'),3);		
		}}).post();
	};	
		
	var CatProcess = function(catitem_div,showDelete) {
		var catitem_id = catitem_div.id.substring(5),
		catitem = cati[catitem_id],
		item_actpanel = new Element('div',{'class':'actpanel'}).inject(catitem_div),
		item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		if (catitem_id=='') return;
		new Element('div',{'class':'clrbth'}).inject(catitem_div);
		
		var cati_btn_edt = new Element('img',{'src':'/img/edt/btnedt.png','title':'Редактировать'}).inject(item_actpanel_cnt).addEvent('click',function() {MakeEditCati(catitem_id); });
	
		if (showDelete) var cati_btn_drp = new Element('img',{'src':'/img/edt/btndrp.png','title':'Удалить'}).inject(item_actpanel_cnt).addEvent('click', function() {
			if (confirm('Удалить товар?'))
			{
				item_actpanel.addClass('ajax-loading');
				var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_catidrp', onComplete: function(sres) {
					if (sres=='t') {
						catitem_div.destroy();
					} else 
					{
						item_actpanel.removeClass('ajax-loading').addClass('save-error');
						var hideError = function(){ item_actpanel.removeClass('save-error'); };
						hideError.delay(5000);
					}
				}}).post({'cati_id':catitem['cati_id']});
			}
		});
	};
	var ctlg=$('ctlg'),catis=$('catis');
	if (ctlg!=null) {
		item_actpanel = new Element('div',{'class':'actpanel'}).inject(ctlg,'before'),
		item_actpanel_cnt = new Element('nobr').inject(item_actpanel);		
		new Element('img', {'src':'/img/edt/btnopt.gif','width':16,'height':16,'title':'Сорировка изделий'}).inject(item_actpanel_cnt).addEvent('click',EditSortOrder);
		new Element('img',{'src':'/img/edt/btnadd.png','title':'Добавить товар'}).inject(item_actpanel_cnt).addEvent('click', function() {MakeEditCati(0);});
		new Element('img',{'src':'/img/edt/btnadd.png','title':'Добавить товар'}).inject(ctlg).addEvent('click', function() {MakeEditCati(0);});
	}
	if (catis!=null) catis.getElements('div.cati').each(function(catitem) { CatProcess(catitem,true); });
});