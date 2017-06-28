window.addEvent('domready', function() {
	var addHover = function(el,elline) {
		el.addClass('hacts').addEvent('mouseenter', function(){
			this.removeClass('hacts');
			if (elline != undefined) elline.addClass('hvrline');
		}).addEvent('mouseleave', function(){
			if (elline != undefined) elline.removeClass('hvrline');
			if (!this.hasClass('editing')) this.addClass('hacts');
		});
		return el;
	};
	var getGlr = function(glrId) {
		var thisglr = null;
		glra.glrs.each(function (glritem){
			if (glritem.id_glr==glrId) thisglr = glritem;
		});
		return thisglr;
	};
	var editGalery = function(glrId,glrType) {
		var drawPhoto = function(thisglr,thisphoto,photosdiv,scrolldown) {
			var imgpath_sml = '/img/gallery/s/'+thisphoto.cgp_file;
			var fileParts = imgpath_sml.split('.');
			var fileExt = fileParts[fileParts.length-1];
			if (fileExt=='flv') imgpath_sml = imgpath_sml + '.jpg';
			if (fileExt=='mp3') imgpath_sml = '/img/units/imgsnd.png';
			var photodiv = new  Element('div',{'class':'glrprws'});
			var item_actpanel = new Element('nobr').inject(new Element('div',{'class':'actpanel'}).inject(photodiv));			
			var cpg_btn_sic = new Element('img',{'src':'/img/edt/glr_btn_sic.gif','title':'Задать центральную точку'}).inject(item_actpanel).addEvent('click', function() {
				if (confirm('Определить центр изображения?'))
				{
					ajxImgInfo(item_actpanel,1);
					var jsonRequest = new Request.JSON({url: '/img/fd/', onComplete: function(sres) {
						if (sres!=undefined) ajxImgInfo(item_actpanel,2); else ajxImgInfo(item_actpanel,3);
					}}).post({'u':'gallery/'+thisphoto.id_cgp+'.jpg'});
				}
			});			
			var cpg_btn_hdr = new Element('img',{'src':'/img/edt/glr_head.png','title':'Поставить на обложку'}).inject(item_actpanel).addEvent('click', function() {
				if (confirm('Поставить это изображение на обложку галереи?'))
				{
					ajxImgInfo(item_actpanel,1);
					var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrcpghdr', onComplete: function(sres) {
						if (sres=='t') {
							ajxImgInfo(item_actpanel,2);
							thisglr.glr_file = thisphoto.cgp_file;
							photosdiv.getElements('div.glrprws').each(function(thisdiv) {
								thisdiv.removeClass('glrhdrimg');
							});
							photodiv.addClass('glrhdrimg');
						} else ajxImgInfo(item_actpanel,3);
					}}).post({'id_glr':thisglr.id_glr,'id_cgp':thisphoto.id_cgp});
				}
			});			
			var cpg_btn_drp = new Element('img',{'src':'/img/edt/btndrp.png','title':'Удалить'}).inject(item_actpanel).addEvent('click', function() {
				if (glra.glr_ph[thisglr.id_glr].length<2) alert('Нельзя удалить последнее изображение!\nУдалите галерею если это необходимо.'); 
				else if (thisglr.glr_file == thisphoto.cgp_file) alert('Нельзя удалить изображение с обложки!\nВыберите на обложку другое избражение прежде чем удалить это.');
				else if (confirm('Удалить изображение?'))
				{
					ajxImgInfo(item_actpanel,1);
					var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrcpgdrp', onComplete: function(sres) {
						if (sres=='t') {
							glra.glr_ph[thisglr.id_glr].erase(thisphoto);
							photodiv.destroy();
							if (glra.glr_ph[thisglr.id_glr].length==1) photosdiv.getElement('div.glrprws').addClass('glrhdrimg');
						} else ajxImgInfo(item_actpanel,3);
					}}).post({'id_cgp':thisphoto.id_cgp});
				}
			});
			if (thisglr.glr_file == thisphoto.cgp_file) photodiv.addClass('glrhdrimg');
			new Element('img',{'src':imgpath_sml,'class':'glrimgs','alt':'','title':thisphoto.cgp_name}).inject(photodiv);
			new Element('input',{'class':'glrimgsn','value':thisphoto.cgp_name}).inject(photodiv).addEvent('blur', function() {
				if (thisphoto.cgp_name!=this.value) {
					thisphoto.cgp_name = this.value;
					ajxImgInfo(item_actpanel,1);
					var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrcpgnmupd', onComplete: function(sres) {
						if (sres=='t') ajxImgInfo(item_actpanel,2); else ajxImgInfo(item_actpanel,3);
					}}).post({'id_cgp':thisphoto.id_cgp,'cgp_name':thisphoto.cgp_name});
				}
			});
			addHover(photodiv).inject(photosdiv);
			if (scrolldown) (function() { photosdiv.scrollTop = photosdiv.getScrollSize().y; }).delay(500);
			return photodiv;
		};
		var thisglr = (glrId!==0?getGlr(glrId):{'id_glr':0,'glr_name':'','glr_desc':'','glr_file':'','glr_type':glrType});
		var modalForm = new ModalBox({allowManualClose: false,width:450,top:30, onClose: function() {
			window.location.reload();
		}});
		var doUpload = function() {
			//if (upldiv.hasClass('ajax-loading')) return;
			ajxImgInfo(upldiv,1);
			infodiv.empty();
			var req = new JsHttpRequest();
			req.onreadystatechange = function () {
                if (req.readyState == 4) {
                    ajxImgInfo(upldiv, 2);
                    infodiv.set('html', req.responseJS.msg + req.responseText);
                    thisglr.glr_name = glrnameinp.value;
                    //uplform.reset();
                    var uplfie1 = new Element('input', {'type':'file', 'id':'uplfie', 'name':'uplfile'}).addEvent('change', doUpload).inject(uplfie, 'after');
                    uplfie.destroy();
                    uplfie = uplfie1;
                    if (req.responseJS.status == 4) {
                        var thisphoto = {'id_cgp':req.responseJS.id_cgp, 'cgp_name':'', 'cgp_file':req.responseJS.cgp_file};
                        if (glrId == 0) {
                            thisglr.id_glr = req.responseJS.id_glr;
                            glrId = thisglr.id_glr;
                            glridhdnid.value = thisglr.id_glr;
                            thisglr.glr_file = thisphoto.cgp_name;
                            glra.glrs.include(thisglr);
                        }
                        drawPhoto(thisglr, thisphoto, photosdiv, true);
                        if (glra.glr_ph[thisglr.id_glr] != undefined) glra.glr_ph[thisglr.id_glr].include(thisphoto);
                        else glra.glr_ph[thisglr.id_glr] = [thisphoto];
                    }
                    //glrnameinp.value = thisglr.glr_name;
                    glrnameinp.fireEvent('keyup');
                }
            };
			req.open(null, '/ajx/'+currpage.pageurl+'_glrupl', true);
			req.send({q:uplform});
			//this.set('disabled','disabled');
		};
		var uplfie = new Element('input',{'type':'file','id':'uplfie','name':'uplfile','disabled':'disabled'}).addEvent('change', doUpload);
		var infodiv = new Element('span',{'class':'errornotice','style':'padding-left: 10px;'});
		var multidiv = new Element('div',{'class':'glrmultdiv'});
		var upldiv = new Element('div').adopt(multidiv,uplfie,infodiv);
		modalForm.create(document.body, new Element('div',{'html':'<form accept-charset="UTF-8" action="/ajx/'+currpage.pageurl+'_glrupl" method="post" enctype="multipart/form-data" onsubmit="return false" id="uplform" name="uplform">'}));
		var glrnameinp = new Element('input',{'type':'text','name':'glrnameinp','id':'glrnameinp','value':thisglr.glr_name});
		var glrnameinpdiv = new Element('div',{'id':'glrnameinpdiv'}).adopt(new Element('span',{'html':'Название:&nbsp;'}),glrnameinp);
		var glrnameinpHandle=function () {
			glrnameinp.addEvent('keyup', function() {
				uplfie.disabled = this.value.length<1;
			}).addEvent('blur', function() {
				if (thisglr.glr_name!=this.value && this.value.length>0 && thisglr.id_glr>0) {
					thisglr.glr_name = this.value;
					ajxImgInfo(glrnameinpdiv,1);
					var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrnameupd', onComplete: function(sres) {
						if (sres=='t') ajxImgInfo(glrnameinpdiv,2); else ajxImgInfo(glrnameinpdiv,3);
					}}).post({'id_glr':thisglr.id_glr,'glr_name':thisglr.glr_name});
				}
			});
			glrnameinp.fireEvent('keyup');
			glrdescinp.set('disabled',false);
			new Element('iframe',{'id':'glrmultfrm','src':'/js/plupload/_u_glr.html?glr='+thisglr.id_glr+'&rnd='+Math.random(1,999999)+'&url='+currpage.pageurl}).inject(multidiv);
		};
        var glrdescinp = new Element('textarea',{'type':'text','name':'glrdescinp','id':'glrdescinp','disabled':'disabled','value':thisglr.glr_desc,'style':'width: 410px;background-color:inherit;border:1px solid #aaaaaa; color: #fff; padding-right:0;'}).addEvent('blur', function() {
                if (thisglr.glr_desc!=this.value && thisglr.id_glr>0) {
                    thisglr.glr_desc = this.value;
					ajxImgInfo(glrdescinp,1);
                    var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrdescupd', onComplete: function(sres) {
						if (sres=='t') ajxImgInfo(glrdescinp,2); else ajxImgInfo(glrdescinp,3);
                    }}).post({'id_glr':thisglr.id_glr,'glr_desc':thisglr.glr_desc});
                }
        });
		var glridhdnid = new Element('input',{'type':'hidden','name':'id_glr','id':'id_glr','value':thisglr.id_glr});
		var glridhdntype = new Element('input',{'type':'hidden','name':'glr_type','id':'glr_type','value':thisglr.glr_type});
		var photosdiv = new Element('div',{'style':'height:150px;overflow: auto;'});
		var uplform = $('uplform').adopt(new Element('span',{'html':(glrId==0?'Создание':'Изменение')+' галереи:'}),glrnameinpdiv,glridhdnid,glridhdntype,glrdescinp,photosdiv,new Element('div',{'html':'Добавить фото:&nbsp;'}).grab(upldiv));
		if (glrId!=0) {
			if (glra.glr_ph[thisglr.id_glr]!=false)
			glra.glr_ph[thisglr.id_glr].each(function(thisphoto) {
				drawPhoto(thisglr,thisphoto,photosdiv,false);
			});
			glrnameinpHandle();
		} else {
			var btnnewglr = new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Создать','class':'hidden'}).inject(glrnameinp,'after').addEvent('click',function() {
				ajxImgInfo(glrnameinpdiv,1);
				var arrPost = {
					'glr_name':$('glrnameinp').value,
					'glr_type':glrType
				};
				var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrnew', onComplete: function(sres){
					if (sres!='f') {
						ajxImgInfo(glrnameinpdiv,2);
						thisglr.id_glr = sres;
						thisglr.glr_name = arrPost.glr_name;
						glrnameinp.removeEvents('keyup');
						glrnameinpHandle();
						btnnewglr.destroy();
					} else ajxImgInfo(glrnameinpdiv,3);	
				}}).post(arrPost);
			});
			var btnnewglr_ = new Element('img', {'src':'/img/edt/btnsve_.png','width':20,'height':16,'title':'Создать'}).inject(glrnameinp,'after');
			glrnameinp.addEvent('keyup', function() {
				if (this.value.length>0) { btnnewglr.removeClass('hidden'); btnnewglr_.addClass('hidden'); }
					else { btnnewglr.addClass('hidden'); btnnewglr_.removeClass('hidden'); }
			});
			
		}
		drawExtPhoto = function(thisphoto) {
			var photodiv = drawPhoto(thisglr,thisphoto,photosdiv,true);
			if (glra.glr_ph[thisglr.id_glr] != undefined) glra.glr_ph[thisglr.id_glr].include(thisphoto);
			else glra.glr_ph[thisglr.id_glr] = [thisphoto];
			if (glra.glr_ph[thisglr.id_glr].length<2) {			
				ajxImgInfo(photodiv,1);
				var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrcpghdr', onComplete: function(sres) {
					if (sres=='t') {
						ajxImgInfo(photodiv,2);
						thisglr.glr_file = thisphoto.cgp_file;
						photodiv.addClass('glrhdrimg');
					} else ajxImgInfo(photodiv,3);
				}}).post({'id_glr':thisglr.id_glr,'id_cgp':thisphoto.id_cgp});			
			} 			
		};
	};	
	var EditGlrsOpts = function() {
        var modalForm = new ModalBox({allowManualClose: false,width:540,top:30,onClose: function() {}});
		var item_actpanel = new Element('div',{'class':'actpanel'});
		var item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		new Element('img', {'src':'/img/edt/btnund.png','width':'20','height':'16','title':'Отменить'}).
			inject(item_actpanel_cnt).addEvent('click',function() {modalForm.close();});
		new Element('img', {'src':'/img/edt/btnsve.png','width':20,'height':16,'title':'Сохранить'}).inject(item_actpanel_cnt).addEvent('click',function() {
			ajxImgInfo(item_actpanel,1);
			var arrPost = {
                'glr_order':[]
			};
            $$('#e_glr_all_order option').each(function(unititem){
                arrPost.glr_order.push(unititem.value)
            });
			new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrsetordr', onComplete: function(sres){
				if (sres=='t') {
					ajxImgInfo(item_actpanel,2); window.location.reload();
				} else ajxImgInfo(item_actpanel,3);		
			}}).post(arrPost);
		});
		var tablediv = new Element('table',{'width':'99%','border':0,'align':'center','cellpadding':0,'cellspacing':0}).grab(new Element('tbody').adopt(
			new Element('tr').grab(new Element('th',{'colspan':2,'text':'Дополнительные параметры:'})),
            new Element('tr').grab(new Element('td',{'colspan':2,'id':'e_glr_loader','html':'<br/>Порядок сортировки галерей:'})),
            new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
                new Element('select',{'id':'e_glr_all_order','size':12,'style':'width:99%;height:200px;'})
            )),
            new Element('tr').grab(new Element('td',{'colspan':2}).adopt(
                new Element('br'),item_actpanel
            ))

        ));
		modalForm.create(document.body,tablediv);
		ajxImgInfo($('e_glr_loader'),1);
		new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrlist', onComplete: function(sres){
			if (sres!=undefined && sres.r=='t') {
				ajxImgInfo($('e_glr_loader'),0);
				kcms.floodSelect($('e_glr_all_order'),sres.d,'k','v',-1,'');
				new Sortables('#e_glr_all_order', {
					clone: true,
					revert: true,
					opacity: 0.7
				});
			} else ajxImgInfo($('e_glr_loader'),3);		
		}}).post();
	};
	
	var glrs = $('glrs');
	var glrdiv = $('glry');
	if (glrs!=null) {
		item_actpanel = new Element('div',{'class':'actpanel'}).inject(glrs,'before'),
		item_actpanel_cnt = new Element('nobr').inject(item_actpanel);		
		new Element('img', {'src':'/img/edt/btnopt.gif','width':16,'height':16,'title':'Параметры галерей'}).inject(item_actpanel_cnt).addEvent('click',EditGlrsOpts);
		new Element('img',{'src':'/img/edt/btnadd.png','title':'Добавить галерею'}).inject(item_actpanel_cnt).addEvent('click', function() {editGalery(0,1);});
		new Element('img',{'src':'/img/edt/btnadd.png','title':'Добавить галерею'}).inject(glrs,'after').addEvent('click', function() {editGalery(0,1);});	
	}
	else if (glrdiv!=null){
		glrdiv=glrdiv.getElement('div.glrind'),glrId = glrdiv.id.substring(3),thisglr = getGlr(glrId);
		item_actpanel = new Element('div',{'class':'actpanel'}).inject(glrdiv),
		item_actpanel_cnt = new Element('nobr').inject(item_actpanel);
		new Element('div',{'class':'clrbth'}).inject(glrdiv);
		new Element('img',{'src':'/img/edt/btnedt.png','title':'Редактировать'}).inject(item_actpanel_cnt).addEvent('click',function() {editGalery(glrId,1); });
		if (thisglr.glr_sys == undefined || thisglr.glr_sys=='f') new Element('img',{'src':'/img/edt/btndrp.png','title':'Удалить'}).inject(item_actpanel_cnt).addEvent('click', function() {
			if (confirm('Удалить галерею со всем содержимым?')) {
				ajxImgInfo(item_actpanel,1);
				var jsonRequest = new Request.JSON({url: '/ajx/'+currpage.pageurl+'_glrdrp', onComplete: function(sres) {
					if (sres=='t') {
						window.location.href="/_/"+currpage.pagemainurl;
					} else ajxImgInfo(item_actpanel,3);
				}}).post({'id_glr':glrId});
			}
		});	
	}
});