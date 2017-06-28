window.addEvent('domready', function() {
	var myScript = Asset.javascript('/js/obj_glr_ed.js', {onLoad: function(){}});
	var EditAdvert = function(catitem_id) {
		var item = addata;
		var modalForm = new ModalBox({allowManualClose: false,width:720,top:25,onClose: function() {
		}});
		var item_actpanel = new Element('div',{'class':'actpanel'});
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		var news_btn_cncl = new Element('img', {'src':'/img/edt/btnund.png','width':'20','height':'16','title':'Отменить'}).
			inject(item_actpanel_cnt).addEvent('click',function() {modalForm.close();});
		var news_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click',function() {
			item_actpanel.addClass('ajax-loading');
			var arrPost = {
				//"head":$('e_ad_head').get('value'),
				//"title":$('e_ad_title').get('value'),
				//"message":'',
				//"show":$('e_ad_show').checked?'t':'f'
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
		var objGlrDiv = new Element('div',{'style':'overflow: scroll;height:380px;width:412px;'});
		var tablediv = new Element('table',{'width':200+418,'border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
			new Element('tr').grab(new Element('th',{'colspan':3,'text':'Баннеры:'})),
			new Element('tr').adopt(
				new Element('td',{'colspan':1,'align':'left'}).adopt(
					new Element('img',{'src':'/img/t/bnnrspg_min.gif','id':'e_ad_show','value':'t'})
				),
				new Element('td',{'rowspan':1,'width':(catitem_id==0?0:412),'align':'left','valign':'top','style':'padding-left:5px;'}).adopt(objGlrDiv)
			),
			new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
				new Element('br'),item_actpanel
			))
		));
		modalForm.create(document.body,tablediv);
		$('e_ad_show').checked = item.show=='t';
		editObjGalery(objGlrDiv,'Pg_Banners',1,200,'Баннер 1:');
        editObjGalery(objGlrDiv,'Pg_Banners',2,200,'Баннер 2:');
        editObjGalery(objGlrDiv,'Pg_Banners',3,200,'Баннер 3:');
        editObjGalery(objGlrDiv,'Pg_Banners',4,200,'Баннер 4:');
        editObjGalery(objGlrDiv,'Pg_Banners',5,200,'Баннер 5:');
		//var e_datepicker = new DatePicker('#e_news_date', { pickerClass: 'datepicker_vista', format: 'Y-m-d', inputOutputFormat: 'Y-m-d' });
		//$('e_news_date').set('value',e_datepicker.format(new Date(),e_datepicker.options.inputOutputFormat));
	}
	new Element('img', {'src':'/img/edt/btnopt.gif','width':16,'height':16,'title':'Редактор объявления'}).inject($('adedit')).addEvent('click',EditAdvert);
});