window.addEvent('domready', function() {
	var editorLoaded = false;
	var EditGo = function() {
		var item = issuudata;
		var modalForm = new ModalBox({allowManualClose: false,width:350,top:25});
		var item_actpanel = new Element('div',{'class':'actpanel'});
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		var news_btn_cncl = new Element('img', {'src':'/img/edt/btnund.png','width':'20','height':'16','title':'Отменить'}).
			inject(item_actpanel_cnt).addEvent('click',function() {modalForm.close();});
		var news_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click',function() {
			item_actpanel.addClass('ajax-loading');
			var arrPost = {
				"docUrl":$('e_docUrl').get('value')
			};
			new Request.JSON({url: '/ajx/'+currpage.pageurl+'_updissu', onComplete: function(sres){
				if (sres.res!=undefined?sres.res=='t':false) {
					item_actpanel.removeClass('ajax-loading').removeClass('save-error');
					$('e_docId').set('value',sres.id);				
					new Request.JSON({url:'/ajx/' + currpage.pageurl + '_seciuplurl', onComplete:function (sres) {
						if (sres != undefined ? sres.res == 't' : false) window.location.reload();
					}}).post({'section_id':currpage.id, 'url':'http://image.issuu.com/'+sres.id+'/jpg/page_1_thumb_large.jpg'});	
				} else 
				{
					item_actpanel.removeClass('ajax-loading').addClass('save-error');
					var hideError = function(){ item_actpanel.removeClass('save-error'); };
					hideError.delay(5000);
				}		
			}}).post(arrPost);
		});
		var tablediv = new Element('table',{'width':300,'border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
			new Element('tr').grab(new Element('th',{'colspan':2,'style':'padding-bottom:5px;','text':'Параметры pdf issuu:'})),
			new Element('tr').adopt(
				new Element('td',{'align':'left','text':'URL*:'}),
				new Element('td',{'align':'left'}).grab(new Element('input',{'type':'text','id':'e_docUrl','style':"width:99%",'value':item.docUrl}))
			),
			new Element('tr').adopt(
				new Element('td',{'align':'left','text':'Идентификатор:'}),
				new Element('td',{'align':'left'}).grab(new Element('input',{'type':'text','id':'e_docId','style':"width:99%",'value':item.docId,'readonly':'readonly'}))
			),
			new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
				new Element('br'),item_actpanel
			))
		));
		modalForm.create(document.body,tablediv);
	}
	new Element('img', {'src':'/img/edt/btnopt.gif','width':16,'height':16,'title':'Редактор превью'}).inject($('issuuedit')).addEvent('click',EditGo);
});