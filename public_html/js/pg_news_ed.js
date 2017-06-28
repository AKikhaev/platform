window.addEvent('domready', function() {
	var news_editorLoaded = false;
	var MakeEditNews = function (newsitem_id) {
        newsitem = newsitem_id != 0 ? newsi[newsitem_id] : {"news_id":"0", "news_date":"", "news_head":"", "news_image":"", "news_short":"", "news_content":"", "news_sec_id":null, "news_enabled":"t", "news_detaillink":"f"};
        if (!news_editorLoaded) {
            news_editorLoaded = true;
            tinyMCE.init({
                mode:"textareas",
                theme:"advanced",
                plugins:"safari,table,advimage,inlinepopups,searchreplace,print,contextmenu,paste,fullscreen,noneditable,nonbreaking,visualchars,autosave",
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
                editor_selector:"_e_cnt_short",
                invalid_elements:"iframe,script",
                language:'ru',
                relative_urls:false,
                setup:grabimgs_tiny
            });
        }
        var modalForm = new ModalBox({allowManualClose:false, width:650, top:5, onClose:function () {
            tinyMCE.execCommand('mceRemoveControl', false, 'e_news_short');
            tinyMCE.execCommand('mceRemoveControl', false, 'e_news_content');
        }});
        var item_actpanel = new Element('div', {'class':'actpanel'});
        var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
        var news_btn_cncl = new Element('img', {'src':'/img/edt/btnund.png', 'width':'20', 'height':'16', 'title':'Отменить'}).
            inject(item_actpanel_cnt).addEvent('click', function () {
                modalForm.close();
            });
        var news_btn_sve = new Element('img', {'src':'/img/edt/btnsve.png', 'width':20, 'height':16, 'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click', function () {
            item_actpanel.addClass('ajax-loading');
            var arrPost = {
                "news_id":newsitem.news_id,
                "news_date":$('e_news_date').get('value'),
                "news_head":$('e_news_head').get('value'),
                "news_short":tinyMCE.get('e_news_short').getContent(),
                "news_content":tinyMCE.get('e_news_content').getContent(),
                "news_sec_id":null,
                "news_enabled":$('e_news_enabled').checked ? 't' : 'f',
                "news_detaillink":$('e_news_detaillink').checked ? 't' : 'f'
            };
            var jsonRequest = new Request.JSON({url:'/ajx/' + currpage.pageurl + '_news' + (newsitem_id != 0 ? 'sve' : 'ins'), onComplete:function (sres) {
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

        var tablediv = new Element('table', {'width':600, 'border':0, 'align':'center', 'cellpadding':0, 'cellspacing':0}).grab(new Element('tbody').adopt(
            new Element('tr').grab(new Element('th', {'colspan':2, 'text':(newsitem_id == 0 ? 'Добавление' : 'Редактирование') + ' новости:'})),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center'}).adopt(
                new Element('label', {'for':'e_news_date', 'text':'Дата:'}), new Element('input', {'type':'text', 'name':'e_news_date', 'id':'e_news_date', 'value':newsitem.news_date}),
                new Element('input', {'type':'checkbox', 'name':'e_news_detaillink', 'id':'e_news_detaillink', 'value':'t'}), new Element('label', {'for':'e_news_detaillink', 'html':'Ссылка &quot;<u>подробнее...</u>&quot;'}),
                new Element('input', {'type':'checkbox', 'name':'e_news_enabled', 'id':'e_news_enabled', 'value':'t'}), new Element('label', {'for':'e_news_enabled', 'html':'Показывать новость'})
            )),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center', 'text':'Заголовок*:'})),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center'}).adopt(new Element('input', {'type':'text', 'name':'e_news_head', 'id':'e_news_head', style:"width:99%", 'value':newsitem.news_head}))),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center', 'text':'Краткий текст*:'})),
            new Element('tr').grab(new Element('td', {'colspan':2}).grab(new Element('textarea', {'id':'e_news_short', 'style':'height:100px;width:600px;overflow:auto;', 'text':newsitem.news_short}))),
            new Element('tr').grab(new Element('td', {'colspan':2, 'align':'center', 'text':'Полный текст:'})),
            new Element('tr').grab(new Element('td', {'colspan':2}).grab(new Element('textarea', {'id':'e_news_content', 'style':'height:180px;width:600px;overflow:auto;', 'text':newsitem.news_content}))),
            new Element('tr').grab(new Element('td', {'colspan':2}).adopt(
                new Element('br'), item_actpanel,
                new Element('span', {'html':'<form accept-charset="UTF-8" action="/ajx/' + currpage.pageurl + '_newsiupl" method="post" enctype="multipart/form-data" onsubmit="return false" id="upliform" name="upliform">'})
            ))
        ));
        modalForm.create(document.body, tablediv);
        var upliform = $('upliform');
        if (newsitem_id != 0) {
            upliform.grab(new Element('span', {'html':' Изображение: '}));
            var phtimg = null,
                cleanPhoto = function (e) {
                    if (e.control) {
                        if (confirm('Удалить изображение?')) {
                            upldiv.addClass('ajax-loading');
                            var jsonRequest = new Request.JSON({url:'/ajx/' + currpage.pageurl + '_newsidrp', onComplete:function (sres) {
                                if (sres == 't') {
                                    upldiv.removeClass('ajax-loading');
                                    if (phtimg != null) phtimg.destroy();
                                } else {
                                    upldiv.removeClass('ajax-loading').addClass('save-error');
                                    var hideError = function () {
                                        upldiv.removeClass('save-error');
                                    };
                                    hideError.delay(5000);
                                }
                            }}).post({'news_id':newsitem_id});
                        }
                    }
                },
                drawPhoto = function () {
                    if (phtimg != null) phtimg.destroy();
                    if (newsitem.news_image != '')
                        phtimg = new Element('img', {'src':'/img/news/s/' + newsitem.news_image + '?' + Math.floor(Math.random() * 999), 'alt':'', 'title':'ctrl+click чтобы удалить', 'align':'top', 'hspace':2, 'vspace':1}).inject(uplfie, 'before').addEvent('click', cleanPhoto);
                    else phtimg = null;
                },
                doUpload = function () {
                    upldiv.addClass('ajax-loading');
                    infodiv.empty();
                    var req = new JsHttpRequest();
                    req.onreadystatechange = function () {
                        if (req.readyState == 4) {
                            upldiv.removeClass('ajax-loading');
                            infodiv.set('html', req.responseJS.msg + req.responseText);
                            var uplfie1 = new Element('input', {'type':'file', 'id':'uplfie', 'name':'uplfile'}).addEvent('change', doUpload).inject(uplfie, 'after');
                            uplfie.destroy();
                            uplfie = uplfie1;
                            if (req.responseJS.status == 4) {
                                newsitem.news_image = req.responseJS.i_file;
                                drawPhoto();
                            }
                        }
                    };
                    req.open(null, '/ajx/' + currpage.pageurl + '_newsiupl', true);
                    req.send({q:upliform});
                },
                uplfie = new Element('input', {'type':'file', 'id':'uplfie', 'name':'uplfile'}).addEvent('change', doUpload),
                infodiv = new Element('span', {'class':'errornotice', 'style':'padding-left: 10px;'}),
                upldiv = new Element('span').adopt(uplfie, new Element('input', {'name':'news_id', 'type':'hidden', 'value':newsitem_id}), infodiv).inject(upliform);
            drawPhoto();
        }
        $('e_news_detaillink').checked = newsitem.news_detaillink == 't';
        $('e_news_enabled').checked = newsitem.news_enabled == 't';
        var e_datepicker = new DatePicker('#e_news_date', { pickerClass:'datepicker_vista', format:'Y-m-d', inputOutputFormat:'Y-m-d' });
        //$('e_news_date').set('value',e_datepicker.format(new Date(),e_datepicker.options.inputOutputFormat));
        tinyMCE.execCommand('mceAddControl', false, 'e_news_short');
        tinyMCE.execCommand('mceAddControl', false, 'e_news_content');
    };
		
	var NewsProcess = function(newsitem_div) {
		var newsitem_id = newsitem_div.id.substring(5),
		newsitem = newsi[newsitem_id],
		item_actpanel = new Element('div',{'class':'actpanel'}).inject(newsitem_div),
		item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		new Element('div',{'class':'clrbth'}).inject(newsitem_div);
		
		var news_btn_edt = new Element('img',{'src':'/img/edt/btnedt.png','title':'Редактировать'}).inject(item_actpanel_cnt).addEvent('click',function() {MakeEditNews(newsitem_id); });
	
		var news_btn_drp = new Element('img',{'src':'/img/edt/btndrp.png','title':'Удалить'}).inject(item_actpanel_cnt).addEvent('click', function() {
			if (confirm('Удалить новость?'))
			{
				item_actpanel.addClass('ajax-loading');
				var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_newsdrp', onComplete: function(sres) {
					if (sres=='t') {
						newsitem_div.destroy();
					} else 
					{
						item_actpanel.removeClass('ajax-loading').addClass('save-error');
						var hideError = function(){ item_actpanel.removeClass('save-error'); };
						hideError.delay(5000);
					}
				}}).post({'news_id':newsitem['news_id']});
			}
		});
	};
	if (newsi.noadd==undefined) new Element('img',{'src':'/img/edt/btnadd.png','title':'Добавить новость'}).inject($('news')).addEvent('click', function() {MakeEditNews(0);});
	else $('news').getElements('div.newsitem').each(function(newsitem) { NewsProcess(newsitem); });
});