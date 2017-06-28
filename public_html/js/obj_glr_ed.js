window.addEvent('domready', function() {
	editObjGalery = function(glrObjDiv,objName,objId,phDivHeight,divHdr) {
		if (divHdr==undefined) divHdr = 'Фотографии';
		var drawPhoto = function(thisphoto,photosdiv,scrolldown) {
			var imgpath_sml = '/img/objph/s/'+thisphoto.cop_file;
			var fileParts = imgpath_sml.split('.');
			var fileExt = fileParts[fileParts.length-1];
			//if (fileExt=='flv') imgpath_sml = imgpath_sml + '.jpg';
			//if (fileExt=='mp3') imgpath_sml = '/img/units/imgsnd.png';
			var photodiv = new  Element('div',{'style':'padding: 1px;','class':'glrprws'});
			var item_actpanel = new Element('nobr').inject(new Element('div',{'class':'actpanel'}).inject(photodiv));			
			var cpg_btn_sic = new Element('img',{'src':'/img/edt/glr_btn_sic.gif','title':'Задать центральную точку'}).inject(item_actpanel).addEvent('click', function() {
				if (confirm('Определить центр изображения?'))
				{
					ajxImgInfo(item_actpanel,1);
					var jsonRequest = new Request.JSON({url: '/img/fd/', onComplete: function(sres) {
						if (sres!=undefined) ajxImgInfo(item_actpanel,2); else ajxImgInfo(item_actpanel,3);
					}}).post({'u':'objph/'+thisphoto.id_cop+'.jpg'});
				}
			});
			/*
			var cpg_btn_hdr = new Element('img',{'src':'/img/edt/glr_head.png','title':'Поставить на обложку'}).inject(item_actpanel).addEvent('click', function() {
				if (confirm('Поставить это изображение на обложку галереи?'))
				{
					ajxImgInfo(item_actpanel,1);
					var jsonRequest = new Request.JSON({url: '/ajx/_sys/_glrcpghdr', onComplete: function(sres) {
						if (sres=='t') {
							ajxImgInfo(item_actpanel,2);
							//thisglr.glr_file = thisphoto.cop_file;
							photosdiv.getElements('div.glrprws').each(function(thisdiv) {
								thisdiv.removeClass('glrhdrimg');
							});
							photodiv.addClass('glrhdrimg');
						} else ajxImgInfo(item_actpanel,3);
					}}).post({'id_cop':thisphoto.id_cop});
				}
			});
			*/
			var cpg_btn_drp = new Element('img',{'src':'/img/edt/btndrp.png','title':'Удалить'}).inject(item_actpanel).addEvent('click', function() {
				//if (glra.glr_ph[thisglr.id_glr].length<2) alert('Нельзя удалить последнее изображение!\nУдалите галерею если это необходимо.'); 
				//else if (thisglr.glr_file == thisphoto.cop_file) alert('Нельзя удалить изображение с обложки!\nВыберите на обложку другое избражение прежде чем удалить это.');
				//else 
				if (confirm('Удалить изображение?'))
				{
					ajxImgInfo(item_actpanel,1);
					var jsonRequest = new Request.JSON({url: '/ajx/_sys/_glrcpgdrp', onComplete: function(sres) {
						if (sres=='t') {
							photodiv.destroy();
							//if (glra.glr_ph[thisglr.id_glr].length==1) photosdiv.getElement('div.glrprws').addClass('glrhdrimg');
						} else ajxImgInfo(item_actpanel,3);
					}}).post({'id_cop':thisphoto.id_cop});
				}
			});
			//if (thisglr.glr_file == thisphoto.cop_file) photodiv.addClass('glrhdrimg');
			new Element('img',{'src':imgpath_sml,'class':'glrimgs','alt':'','title':thisphoto.cop_name}).inject(photodiv);
			new Element('input',{'class':'glrimgsn','value':thisphoto.cop_name}).inject(photodiv).addEvent('blur', function() {
				if (thisphoto.cop_name!=this.value) {
					thisphoto.cop_name = this.value;
					ajxImgInfo(item_actpanel,1);
					var jsonRequest = new Request.JSON({url: '/ajx/_sys/_glrcpgnmupd', onComplete: function(sres) {
						if (sres=='t') ajxImgInfo(item_actpanel,2); else ajxImgInfo(item_actpanel,3);
					}}).post({'id_cop':thisphoto.id_cop,'cop_name':thisphoto.cop_name});
				}
			});
			kcms.addHover(photodiv).inject(photosdiv);
			if (scrolldown) (function() { photosdiv.scrollTop = photosdiv.getScrollSize().y; }).delay(500);
			return photodiv;
		};
		var multidiv = new Element('div',{'class':'objglrmult'});
		new Element('iframe',{'src':'/js/plupload/_u_obj_glr.html?glr='+5+'&rnd='+Math.random()+'&obj='+objName+'&objid='+objId}).inject(multidiv);
		var photosdiv = new Element('div',{'style':'height:'+(phDivHeight-38)+'px;overflow: auto;'});
        var ThisObjDiv = new Element('div',{'style':'border-top:1px solid gray;'}).adopt(new Element('div',{'html':divHdr,'style':'text-align:center;'}),multidiv,photosdiv);
		glrObjDiv.grab(ThisObjDiv);
		ajxImgInfo(photosdiv,1);
		new Request.JSON({url: '/ajx/_sys/_glrilst', onComplete: function(sres){
			if (sres!=undefined && sres.r=='t') {
				sres.d.each(function(itm) {
					drawPhoto(itm,photosdiv,false);
				});
				ajxImgInfo(photosdiv,0);
			} else ajxImgInfo(photosdiv,3);	
		}}).post({'obj':objName,'obj_id':objId});
        kcms._exchdata[objName+objId+'_drawphoto'] = function(thisphoto) {
			var photodiv = drawPhoto(thisphoto,photosdiv,true);
			/*
			if (glra.glr_ph[thisglr.id_glr].length<2) {
				ajxImgInfo(photodiv,1);
				var jsonRequest = new Request.JSON({url: '/ajx/_sys/_glrcpghdr', onComplete: function(sres) {
					if (sres=='t') {
						ajxImgInfo(photodiv,2);
						thisglr.glr_file = thisphoto.cop_file;
						photodiv.addClass('glrhdrimg');
					} else ajxImgInfo(photodiv,3);
				}}).post({'id_glr':thisglr.id_glr,'id_cop':thisphoto.id_cop});			
			}
			*/			
		};
	};	
});