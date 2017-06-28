window.addEvent('domready', function() {
	var u_items = cmntsi,idpref='e_cmnt_';
	var MakeEditUnitI = function (uitem_id) {
        var uitem = u_items[uitem_id];
        var modalForm = new ModalBox({allowManualClose:false, width:650, top:30});
        var item_actpanel = new Element('div', {'class':'actpanel'});
        var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
        var gb_btn_cncl = new Element('img', {'src':'/img/edt/btnund.png', 'width':'20', 'height':'16', 'title':'Отменить'}).
            inject(item_actpanel_cnt).addEvent('click', function () {
                modalForm.close();
            });
        var gb_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png', 'width':20, 'height':16, 'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click', function () {
            item_actpanel.addClass('ajax-loading');
            var arrPost = {
                "cmnt_id":uitem.cmnt_id,
                "cmnt_name":$(idpref + 'name').get('value'),
                "cmnt_message":$(idpref + 'message').get('value'),
                "cmnt_enabled":$(idpref + 'enabled').checked ? 't' : 'f'
            };
            var jsonRequest = new Request.JSON({url:'/ajx/' + currpage.pageurl + '_cmntsve', onComplete:function (sres) {
                if (sres == 't') {
                    item_actpanel.removeClass('ajax-loading').removeClass('save-error');
                    uitem = arrPost;
                    window.location.reload();
                } else {
                    item_actpanel.removeClass('ajax-loading').addClass('save-error');
                    var hideError = function () {
                        item_actpanel.removeClass('save-error');
                    };
                    hideError.delay(5000);
                }
            }}).post(arrPost);
        });
        var tablediv = new Element('table', {'width':600, 'border':0, 'align':'center', 'cellpadding':0, 'cellspacing':0}).grab(new Element('tbody').adopt(
            new Element('tr').grab(new Element('th', {'colspan':2, 'text':'Редактирование сообщения:'})),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center'}).adopt(
                new Element('span', {'text':'Дата: ' + uitem.cmnt_date + ' Email:' + uitem.cmnt_email})
            )),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center', 'text':'Имя*:'})),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center'}).adopt(new Element('input', {'type':'text', 'name':idpref + 'name', 'id':idpref + 'name', style:"width:99%", 'value':uitem.cmnt_name}))),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center', 'text':'Сообщение*:'})),
            new Element('tr').grab(new Element('td', {'colspan':2}).grab(new Element('textarea', {'id':idpref + 'message', 'style':'height:100px;width:600px;overflow:auto;', 'text':uitem.cmnt_message}))),
            new Element('tr').grab(new Element('td', {'colspan':2}).adopt(
                new Element('br'),
                new Element('input', {'type':'checkbox', 'name':idpref + 'enabled', 'id':idpref + 'enabled', 'value':'t'}), new Element('label', {'for':idpref + 'enabled', 'html':'Показывать сообщение'}), item_actpanel
            ))
        ));
        modalForm.create(document.body, tablediv);
        $(idpref + 'enabled').checked = uitem.cmnt_enabled == 't';
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
				item_actpanel.addClass('ajax-loading');
				var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_cmntdrp', onComplete: function(sres) {
					if (sres=='t') {
						uitem_div.destroy();
					} else 
					{
						item_actpanel.removeClass('ajax-loading').addClass('save-error');
						var hideError = function(){ item_actpanel.removeClass('save-error'); };
						hideError.delay(5000);
					}
				}}).post({'cmnt_id':uitem['cmnt_id']});
			}
		});
			
		//alert(uitem_id);
	};

	$('cmnts').getElements('div.cmntitem').each(function(uitem) { UnitProcess(uitem); });
});