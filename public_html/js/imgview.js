var ImgView = new Class({
	Implements: [Options],
	options: {
		Overlay : true,
		OverlayOpacity: 0.6,
		allowManualClose: false,
		height: 485,
		top: 100,
		id: '',
		onClose: null,
		photoPath: '',
		srcfield: 'cgp_file',
		ttlfield: 'cgp_name',
		glrNmWhenEmpt: false
	},
	
	overlay: null,
	itemtitle: null,
	handle: null,
	initialize: function(options) {
		this.setOptions(options);
	},
	
	close: function() {
		this.MediaCurrTag.empty();
		this.overlay.set('morph', {'duration':200}).morph({'opacity':0});
		this.handle.set('morph', {'duration':200}).morph({'opacity':0});
		var thisobj = this;
		var closeFade = function() {
			thisobj.overlay.get('morph').cancel(); thisobj.handle.get('morph').cancel();
			if (thisobj.options.onClose!=null) thisobj.options.onClose();
			thisobj._destroy()
		};
		closeFade.delay(200);
	},
	
	attachPage: function() {
		var gimgs=[], thisobj = this;
		$$('A[rel^="g|"]').each(function(atg,i){
			var ginf = atg.get('rel').split('.'); gimgs[ginf[1]] = gimgs[ginf[1]] || [];
			gimgs[ginf[1]][i]={"id_cgp":ginf[2],"cgp_glr_id":ginf[1],"cgp_name":atg.title,"cgp_file":atg.href};
			atg.addEvent('click',function(e){ e.stop(); thisobj.create(document.body,'test',gimgs[ginf[1]],i); });
		});
		var ain=0;
		$$('a._imgview').each(function(atg){
			++ain;var ai={"id_cgp":'ai'+ain,"cgp_glr_id":'ai'+ain,"cgp_name":atg.title,"cgp_file":atg.href};
			atg.addEvent('click',function(e){ e.stop(); thisobj.create(document.body,'test',[ai],0); });
		});	
		var gg=[];
		$$('a._imgglrview').each(function(atg,i){
			gg[i]={"id_cgp":'gg',"cgp_glr_id":'gn'+i,"cgp_name":atg.title,"cgp_file":atg.href};
			atg.addEvent('click',function(e){ e.stop(); thisobj.create(document.body,'test',gg,i); });
		});		
		return this;
	},

	create: function(id,glrName,imgList,imageNum) {
		var target = $(id), imgCurr = imageNum, imgListPreCache = [], imgListLoaded = [], thisobj = this;
		if (!target) return;
		
			var imgFadeIn = function (fadeImgNum) {
				if (fadeImgNum!=imgCurr) return;
				ImgCurrTag.morph({'opacity':1});
				thisobj.itemtitle.morph({'opacity':1});
			};
		
			var onImgLoadedFadeId = false;
			var onImgLoaded = function(fadeImgNum) {
				ImgCurrTag.removeEvents('load');
				if (onImgLoadedFadeId) imgFadeIn(fadeImgNum);
			};

		var loadImage = function(imgNum,noFade) {
			if (imgNum>=0 && imgNum<=imgList.length-1) {
				imgCurr = imgNum; var drtn = noFade?0:300;

				var fileParts = imgList[imgCurr][thisobj.options.srcfield].split('.'),
				fileExt = fileParts[fileParts.length-1],
				itmttl = thisobj.options.glrNmWhenEmpt?(imgList[imgCurr][thisobj.options.ttlfield]==''?glrName:imgList[imgCurr][thisobj.options.ttlfield]):imgList[imgCurr][thisobj.options.ttlfield];
				
				if (fileExt=='flv'||fileExt=='mp3') {
					ImgCurrTag.set('morph', {'duration':drtn}).morph({'opacity':0});
					thisobj.itemtitle.set('morph', {'duration':drtn}).morph({'opacity':0});
					MediaCurrTag.set('morph', {'duration':drtn}).morph({'opacity':0}).get('morph').chain(function(){
						MediaCurrTag.addClass('imgview_m');
						ImgCurrTag.addClass('hidden');
						var sfvvars = {file: thisobj.options.photoPath+imgList[imgCurr][thisobj.options.srcfield]};
                        if (fileExt == 'mp3') {
                            sfvvars['stretching'] = 'fill';
                            sfvvars['plugins'] = 'revolt-1';
                        }
                        new Swiff('/js/player/player.swf', {
							id: 'mpl',
							width: 480,
							height: 362,
							container: 'jwplayer',
							params: {
								wmode: 'transparent',
								allowfullscreen: 'true'
							},
							vars: sfvvars
						});								
						MediaCurrTag.morph({'opacity':1});
						thisobj.itemtitle.set('html',itmttl);
					});
				}
				else {
					if (!noFade && imgListPreCache[imgNum]==undefined) {
						imgListPreCache[imgNum] = new Element('img');
						if (window.opera) imgListPreCache[imgNum].addEvent('load', function (){
							imgListLoaded[imgNum] = true;
							onImgLoaded(imgNum);
						});
						imgListPreCache[imgNum].set('src',thisobj.options.photoPath+imgList[imgNum][thisobj.options.srcfield]);
					}
					MediaCurrTag.set('morph', {'duration':drtn}).morph({'opacity':0});
					thisobj.itemtitle.set('morph', {'duration':drtn}).morph({'opacity':0});
					ImgCurrTag.set('morph', {'duration':drtn}).morph({'opacity':0}).get('morph').chain(function(){
						MediaCurrTag.removeClass('imgview_m').empty(); ImgCurrTag.removeClass('hidden');
						if (!noFade && !window.opera) ImgCurrTag.addEvent('load', function() {
							ImgCurrTag.removeEvents('load');
							ImgCurrTag.setStyles({width: imgListPreCache[imgNum].width + "px",height: imgListPreCache[imgNum].height + "px"});
							imgFadeIn(imgNum);
						});
						ImgCurrTag.set('src',thisobj.options.photoPath+imgList[imgCurr][thisobj.options.srcfield]);
						thisobj.itemtitle.set('html',itmttl);
						if (!noFade && window.opera) {
							if (imgListLoaded[imgNum]!=undefined) imgFadeIn(imgNum); else onImgLoadedFadeId = true;			
						}
						if (noFade) imgFadeIn(imgNum);
					});	
				}
				
				if (imgNum-1<0)	turnl.addClass('imgview_n').removeClass('imgview_l');
					else turnl.addClass('imgview_l').removeClass('imgview_n');
				if (imgNum+1>imgList.length-1)	turnr.addClass('imgview_n').removeClass('imgview_r');
					else turnr.addClass('imgview_r').removeClass('imgview_n');
			}
		};
		
		this.overlay = new Element('div', {'class':'imgview_overlay',styles:{'top':0,'height':target.getSize().y+target.getScrollSize().y,'opacity':0}});
		this.handle = new Element('div', {'class':'imgview_area','id':this.options.id,styles:{'height':this.options.height}}); //'width': '100%',
		var closeBtn = new Element('div', {'class':'closeBtn'}).injectInside(this.handle).addEvent('click', this.close.bind(this));

		var turnl = new Element('div',{'class':'imgview_l'}).addEvent('click',function() {
			loadImage(imgCurr-1,false);
		});
		var turnr = new Element('div',{'class':'imgview_r'}).addEvent('click',function() {
			loadImage(imgCurr+1,false);
		});
		
		var ImgCurrTag = new Element('img',{'class':'imgview_pht','alt':'','title':''}).addEvent('click', function() {turnr.fireEvent('click');});
		var MediaCurrTag = new Element('div',{'id':'jwplayer','class':'jwplayer'});
		new Element('div',{'style':'height: 12px; width:10px'}).injectInside(this.handle);
		this.itemtitle = new Element('td',{'class':'imgview_hdr','colspan':'3','text':''});
		new Element('table',{'align':'center'}).grab(new Element('tbody').adopt(
			new Element('tr').adopt(new Element('td',{'valign':'middle'}).grab(turnl),new Element('td',{'height':439,'width':729,'align':'center','id':'ImgCurrDiv'}).adopt(ImgCurrTag,MediaCurrTag),new Element('td',{'valign':'middle'}).grab(turnr)),
			new Element('tr').adopt(this.itemtitle)
		)).injectInside(this.handle);
		loadImage(imgCurr,true);
		this.MediaCurrTag = MediaCurrTag;
		
		this.overlay.injectInside(target);
		var toppos = (Browser.Engine.trident && Browser.Engine.version==4)?document.body.getScroll().y+this.options.top:this.options.top;
		var imgviewAreaCnt = new Element('div',{'class':'imgview_area_cnt',styles:{'top':toppos}}).inject(target).grab(this.handle.addEvent('click', function(e){e.stop();}));
		if (Browser.Engine.trident && Browser.Engine.version==4) {
			this.handle.setStyle('left',(document.body.getSize().x-this.handle.getSize().x)/2+'px');
		}

		if (this.options.allowManualClose) {
			this.overlay.addEvent('click', this.close.bind(this));
			imgviewAreaCnt.addEvent('click', this.close.bind(this));
		}
		
		this.overlay.fade('hide').set('morph', {'duration':400}).morph({'opacity':thisobj.options.OverlayOpacity});
		this.handle.fade('hide').set('morph', {'duration':400}).morph({'opacity':1})		
	},
	
	_destroy: function(){
		if ($type(this.handle) == 'element') this.handle.destroy();
		if ($type(this.overlay) == 'element') this.overlay.destroy();
	}

});