MooTools.More={version:"1.3.1.1",build:"0292a3af1eea242b817fecf9daa127417d10d4ce"};(function(){Fx.Scroll=new Class({Extends:Fx,options:{offset:{x:0,y:0},wheelStops:true},initialize:function(c,b){this.element=this.subject=document.id(c);
this.parent(b);if(typeOf(this.element)!="element"){this.element=document.id(this.element.getDocument().body);}if(this.options.wheelStops){var d=this.element,e=this.cancel.pass(false,this);
this.addEvent("start",function(){d.addEvent("mousewheel",e);},true);this.addEvent("complete",function(){d.removeEvent("mousewheel",e);},true);}},set:function(){var b=Array.flatten(arguments);
if(Browser.firefox){b=[Math.round(b[0]),Math.round(b[1])];}this.element.scrollTo(b[0],b[1]);},compute:function(d,c,b){return[0,1].map(function(e){return Fx.compute(d[e],c[e],b);
});},start:function(c,d){if(!this.check(c,d)){return this;}var b=this.element.getScroll();return this.parent([b.x,b.y],[c,d]);},calculateScroll:function(g,f){var d=this.element,b=d.getScrollSize(),h=d.getScroll(),j=d.getSize(),c=this.options.offset,i={x:g,y:f};
for(var e in i){if(!i[e]&&i[e]!==0){i[e]=h[e];}if(typeOf(i[e])!="number"){i[e]=b[e]-j[e];}i[e]+=c[e];}return[i.x,i.y];},toTop:function(){return this.start.apply(this,this.calculateScroll(false,0));
},toLeft:function(){return this.start.apply(this,this.calculateScroll(0,false));},toRight:function(){return this.start.apply(this,this.calculateScroll("right",false));
},toBottom:function(){return this.start.apply(this,this.calculateScroll(false,"bottom"));},toElement:function(d,e){e=e?Array.from(e):["x","y"];var c=a(this.element)?{x:0,y:0}:this.element.getScroll();
var b=Object.map(document.id(d).getPosition(this.element),function(g,f){return e.contains(f)?g+c[f]:false;});return this.start.apply(this,this.calculateScroll(b.x,b.y));
},toElementEdge:function(d,g,e){g=g?Array.from(g):["x","y"];d=document.id(d);var i={},f=d.getPosition(this.element),j=d.getSize(),h=this.element.getScroll(),b=this.element.getSize(),c={x:f.x+j.x,y:f.y+j.y};
["x","y"].each(function(k){if(g.contains(k)){if(c[k]>h[k]+b[k]){i[k]=c[k]-b[k];}if(f[k]<h[k]){i[k]=f[k];}}if(i[k]==null){i[k]=h[k];}if(e&&e[k]){i[k]=i[k]+e[k];
}},this);if(i.x!=h.x||i.y!=h.y){this.start(i.x,i.y);}return this;},toElementCenter:function(e,f,h){f=f?Array.from(f):["x","y"];e=document.id(e);var i={},c=e.getPosition(this.element),d=e.getSize(),b=this.element.getScroll(),g=this.element.getSize();
["x","y"].each(function(j){if(f.contains(j)){i[j]=c[j]-(g[j]-d[j])/2;}if(i[j]==null){i[j]=b[j];}if(h&&h[j]){i[j]=i[j]+h[j];}},this);if(i.x!=b.x||i.y!=b.y){this.start(i.x,i.y);
}return this;}});Fx.Scroll.implement({scrollToCenter:function(){return this.toElementCenter.apply(this,arguments);},scrollIntoView:function(){return this.toElementEdge.apply(this,arguments);
}});function a(b){return(/^(?:body|html)$/i).test(b.tagName);}}).call(this);var Asset={javascript:function(f,c){if(!c){c={};}var a=new Element("script",{src:f,type:"text/javascript"}),g=c.document||document,b=0,d=c.onload||c.onLoad;
var e=d?function(){if(++b==1){d.call(this);}}:function(){};delete c.onload;delete c.onLoad;delete c.document;return a.addEvents({load:e,readystatechange:function(){if(["loaded","complete"].contains(this.readyState)){e.call(this);
}}}).set(c).inject(g.head);},css:function(d,a){if(!a){a={};}var b=new Element("link",{rel:"stylesheet",media:"screen",type:"text/css",href:d});var c=a.onload||a.onLoad,e=a.document||document;
delete a.onload;delete a.onLoad;delete a.document;if(c){b.addEvent("load",c);}return b.set(a).inject(e.head);},image:function(c,b){if(!b){b={};}var d=new Image(),a=document.id(d)||new Element("img");
["load","abort","error"].each(function(e){var g="on"+e,f="on"+e.capitalize(),h=b[g]||b[f]||function(){};delete b[f];delete b[g];d[g]=function(){if(!d){return;
}if(!a.parentNode){a.width=d.width;a.height=d.height;}d=d.onload=d.onabort=d.onerror=null;h.delay(1,a,a);a.fireEvent(e,a,1);};});d.src=a.src=c;if(d&&d.complete){d.onload.delay(1);
}return a.set(b);},images:function(c,b){c=Array.from(c);var d=function(){},a=0;b=Object.merge({onComplete:d,onProgress:d,onError:d,properties:{}},b);return new Elements(c.map(function(f,e){return Asset.image(f,Object.append(b.properties,{onload:function(){a++;
b.onProgress.call(this,a,e,f);if(a==c.length){b.onComplete();}},onerror:function(){a++;b.onError.call(this,a,e,f);if(a==c.length){b.onComplete();}}}));
}));}};

var AutoSwiff = new Class({
	Extends : Swiff,
	initialize: function(path,options) {
		if (Browser.Plugins.Flash.version > 0 ) {
			this.parent(path,options);
			this.versionCheck(Browser.Plugins.Flash);
		} else {
			container = document.id(options.container);
			container.set('style','border:1px solid #000;background-color:#FFF;font-size:20px;text-align:center;width:'+options.width+'px;height:'+options.height+'px;');
			new Element('div',{'html':'Чтобы просмотреть видео, необходимо <a href="http://www.adobe.com/go/getflashplayer" target="_blank">установить flash player</a>!','style':'padding:110px 120px 0'}).inject(container);
		}
	},
	versionCheck : function() {
		var flashVersion = this.options.vars.version;
		if ( Browser.Plugins.Flash.version < flashVersion ) {
			var origSwf = this;
			var installSwf = new Swiff (origSwf.options.vars.expressinstall, {
				container: origSwf.options.container,
				width: origSwf.options.width,
				height: origSwf.options.height,
				params: {},
				vars: {}
			});
		}
	}
});
