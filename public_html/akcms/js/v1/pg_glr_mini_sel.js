window.addEvent('domready', function() {
	var EditGlrMiniOpts = function() {
        var modalForm = new ModalBox({allowManualClose: false,width:550,top:30,onClose: function() {}});
		var item_actpanel = new Element('div',{'class':'actpanel'});
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		new Element('img', {'src':'/img/edt/btnund.png','width':'20','height':'16','title':'Отменить'}).
			inject(item_actpanel_cnt).addEvent('click',function() {modalForm.close();});
		new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click',function() {
			ajxImgInfo(item_actpanel,1);
			var arrPost = {
                'glrs':[]
			};
            $('e_glrmini_items').getSelected().each(function(unititem){
                arrPost.glrs.push(unititem.value)
            });
			new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrsec', onComplete: function(sres){
				if (sres=='t') {
					ajxImgInfo(item_actpanel,2); window.location.reload();
				} else ajxImgInfo(item_actpanel,3);		
			}}).post(arrPost);
		});
		var tablediv = new Element('table',{'width':'99%','border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
			new Element('tr').grab(new Element('th',{'colspan':2,'text':'Дополнительные параметры:','style':'text-align:left;'})),
            new Element('tr').grab(new Element('td',{'colspan':2,'id':'e_glrmini_loader','html':'Отображать галереи:'})),
            new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
				new Element('select',{'id':'e_glrmini_items','multiple':true,'size':12,'style':'width:99%;height:200px;'})
            )),
            new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
                new Element('br'),item_actpanel
            ))

        ));
		modalForm.create(document.body,tablediv);
		ajxImgInfo($('e_glrmini_loader'),1);
		new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrlist', onComplete: function(sres){
			if (sres!=undefined && sres.r=='t') {
				ajxImgInfo($('e_glrmini_loader'),0);
				var glrsobj = {}; glrs_mini.each(function(i) {glrsobj[i]=true;});
				kcms.floodSelect($('e_glrmini_items'),sres.d.reverse(),'k','v',glrsobj,'');//
				(new multipleSelectFilter()).init($('e_glrmini_items'));
			} else ajxImgInfo($('e_glrmini_loader'),3);		
		}}).post();
	};
	
	var glrmini = $('glrmini');
	if (glrmini!=null) {
		item_actpanel = new Element('div',{'class':'actpanel'}).inject(glrmini,'before'),
		item_actpanel_cnt = new Element('nobr').inject(item_actpanel);		
		new Element('img', {'src':'/img/edt/btnopt.gif','width':16,'height':16,'title':'Параметры галерей'}).inject(item_actpanel_cnt).addEvent('click',EditGlrMiniOpts);
	}
});