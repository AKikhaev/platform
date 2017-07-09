window.addEvent('domready', function() {
	if($$('div.txtsh_hidden,div.txtsh_shown').length>0) $$('div.txtsh_hidden,div.txtsh_shown').each(function(div){
		div.getElement('.txtsh_hdr').addEvent('click',function() {
			this.getParent().toggleClass('txtsh_hidden').toggleClass('txtsh_shown');
		});
	});
});