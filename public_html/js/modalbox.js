var ModalBox = new Class({
	Implements: [Options],
	options: {
		Overlay : true,
		OverlayOpacity: 0.8,
		allowManualClose: true,
		width: 200,
		height: 400,
		top: 50,
		left:  0,
		zindex: 2000,
		content: '',
		id: '',
		onClose: null
	},

	overlay : null,
	handle : null,
	contentbox: null,

	initialize: function(options) {
		this.setOptions(options);
	},

	close : function() {
		this.overlay.set('morph', {'duration':200}).morph({'opacity':0});
		this.handle.set('morph', {'duration':200}).morph({'opacity':0});
		var thisobj = this;
		var closeFade = function() {
			if (thisobj.options.onClose!=null) thisobj.options.onClose();
			thisobj._destroy()
		};
		closeFade.delay(250);
	},

	create : function(id, content) {
		var target = $(id);
		if (!target) return;

		this.overlay = new Element('div', {'class':'hideWindow',styles:{'top':0,'height':target.getSize().y+target.getScrollSize().y,'opacity':0,'z-index':this.options.zindex}});

		var left = Math.round(target.getWidth()/2 - this.options.width/2);

		this.handle = new Element('div', {'class':'dWindow','id':this.options.id,styles:{'width':this.options.width+2,'height':this.options.height+2,'left':left,'top':this.options.top,'z-index':this.options.zindex+1}});

		var bar = new Element('div', {'class':'topBar'});
		// Имитируем скругления
		var b1 = new Element('div', {'class':'dw1'}).grab(new Element('div', {'class':'dw11'})).inject(bar);
		new Element('div', {'class':'dw2'}).inject(bar);
		new Element('div', {'class':'dw3'}).inject(bar);
		new Element('div', {'class':'dw4'}).inject(bar);

		var closeBtn = new Element('div', {'class':'closeBtn'});
		closeBtn.addEvent('click', this.close.bind(this));

		closeBtn.inject(bar);
		bar.inject(this.handle);

		// Собственно содержимое блока
		var СontentBox = new Element('div', {'class':'handle'});
		СontentBox.adopt(content);
		СontentBox.inject(this.handle);

		// Имитируем нижнее скругление
		var bar1 = new Element('div', {'class':'topBar'});
		new Element('div', {'class':'dw4'}).inject(bar1);
		new Element('div', {'class':'dw3'}).inject(bar1);
		new Element('div', {'class':'dw2'}).inject(bar1);
		var b11 = new Element('div', {'class':'dw1'}).grab(new Element('div', {'class':'dw11'})).inject(bar1);
		bar1.inject(this.handle);

		this.overlay.inject(target);
		this.handle.inject(target);
		this.contentbox = СontentBox;

		if (this.options.allowManualClose)
			this.overlay.addEvent('click', this.close.bind(this));

		this.overlay.fade('hide').set('morph', {'duration':400}).morph({'opacity':0.8});
		this.handle.fade('hide').set('morph', {'duration':400}).morph({'opacity':1});

	},

	_destroy : function(){
		if ($type(this.handle) == 'element') this.handle.destroy();
		if ($type(this.overlay) == 'element') this.overlay.destroy();
	}

});