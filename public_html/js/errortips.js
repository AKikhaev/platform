var ErrorTips = new Class({
	Implements: [Options],
	options: {
		elroot:null
	},
	tips:[],
	initialize: function(options) {
		this.options.elroot = document.body;
		this.setOptions(options);
		window.addEvent('resize',(function(){
			this.upgradePos();
		}).bind(this));
	},
	setPos: function(el,elt) {
		var p=el.getPosition(),s=el.getSize(),tp={x:p.x+s.x+2,y:p.y};
		elt.setPosition(tp);	
	},
	upgradePos: function() {
		this.tips.each(function(tip){
			this.setPos(tip.el,tip.elt);
		},this);
	},
	newTip:function(eln,msg){
		var el = this.options.elroot.getElementById(eln); if (el==null) alert(eln); else el.addClass('errfld');
		if (el!=null) {
			var elt = new Element('div',{'class':'errtip','text':msg}).inject(document.body).set('tween', {duration: 'short',}).fade('hide');
			this.setPos(el,elt);
			//(function(){elt.fade('in');}).delay(10);
			var thisObj = this;
			el.addEvent('focus',function(){ thisObj.showTip(eln); });
			el.addEvent('blur',function(){ thisObj.hideTip(eln); });
			this.tips.include({'eln':eln,'el':el,'elt':elt});
		} else alert(eln);
	},
	hideTip:function(eln) {
		var toWork = null;
		this.tips.each(function(tip){
			if (tip.eln==eln) toWork = tip;			
		});
		if (toWork!=null) {
			toWork.elt.fade('out');
		}
	},
	showTip:function(eln) {
		var toWork = null;
		this.tips.each(function(tip){
			if (tip.eln==eln) toWork = tip;
		});
		if (toWork!=null) {
			toWork.elt.fade('in');
		}
	},
	removeAllTip:function() {
		this.tips.each(function(tip){
			tip.elt.destroy();
			tip.el.removeClass('errfld');
		});
		this.tips.empty();
	},
	removeTip:function(eln) {
		var toWork = null;
		this.tips.each(function(tip){
			if (tip.eln==eln) toWork = tip;	
		});
		if (toWork!=null) {
			toWork.elt.destroy();	
			this.tips.erase(toWork);
		}
	}
});