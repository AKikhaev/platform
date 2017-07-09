window.addEvent('domready', function() {
	var news_editorLoaded = false;
	var MakeEditNews = function (newsitem_id) {
        newsitem = newsi[newsitem_id];
        var modalForm = new ModalBox({allowManualClose:false, width:250, top:50});
        var item_actpanel = new Element('div', {'class':'actpanel'});
        var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
        var news_btn_cncl = new Element('img', {'src':'/img/edt/btnund.png', 'width':'20', 'height':'16', 'title':'Отменить'}).
            inject(item_actpanel_cnt).addEvent('click', function () {
                modalForm.close();
            });
        var news_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png', 'width':20, 'height':16, 'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click', function () {
            item_actpanel.addClass('ajax-loading');
            var arrPost = {
                "section_id":newsitem.section_id,
                "sec_created":$('e_sec_created').get('value'),
                "sec_to_news":$('e_sec_to_news').checked ? 't' : 'f'
            };
            var jsonRequest = new Request.JSON({url:'/ajx/' + currpage.pageurl + '_secnewssve', onComplete:function (sres) {
                if (sres == 't') {
                    item_actpanel.removeClass('ajax-loading').removeClass('save-error');
                    newsitem = arrPost;
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

        var tablediv = new Element('table', {'width':200, 'border':0, 'align':'center', 'cellpadding':0, 'cellspacing':0}).grab(new Element('tbody').adopt(
            new Element('tr').grab(new Element('th', {'colspan':2, 'text':' Редактирование параметров:'})),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center', style:'font-size:11px', 'text':newsitem.sec_namefull})),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center'}).adopt(
                new Element('br'), new Element('label', {'for':'e_sec_created', 'text':'Дата:'}), new Element('input', {'type':'text', 'name':'e_sec_created', 'id':'e_sec_created', 'value':newsitem.sec_created})
            )),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center'}).adopt(
                new Element('input', {'type':'checkbox', 'name':'e_sec_to_news', 'id':'e_sec_to_news', 'value':'t'}), new Element('label', {'for':'e_sec_to_news', 'html':'Показывать новость'})
            )),
            new Element('tr').grab(new Element('td', {'colspan':2}).adopt(
                new Element('br'), item_actpanel
            ))
        ));
        modalForm.create(document.body, tablediv);
        $('e_sec_to_news').checked = newsitem.sec_to_news == 't';
        var e_datepicker = new DatePicker('#e_sec_created', { pickerClass:'datepicker_vista', format:'Y-m-d H:i:s', inputOutputFormat:'Y-m-d H:i:s', timePicker:true });
        //$('e_sec_created').set('value',e_datepicker.format(new Date(),e_datepicker.options.inputOutputFormat));
    };
	var NewsProcess = function(newsitem_div) {
		var newsitem_id = newsitem_div.id.substring(5),
		newsitem = newsi[newsitem_id],
		item_actpanel = new Element('div',{'class':'actpanel'}).inject(newsitem_div),
		item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		new Element('div',{'class':'clrbth'}).inject(newsitem_div);		
		var news_btn_edt = new Element('img',{'src':'/img/edt/btnedt.png','title':'Редактировать'}).inject(item_actpanel_cnt).addEvent('click',function() {MakeEditNews(newsitem_id); });
	};
	$('news').getElements('div.newsitem').each(function(newsitem) { NewsProcess(newsitem); });
});