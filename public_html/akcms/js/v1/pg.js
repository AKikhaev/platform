window.addEvent('domready', function() {
	//var box = new CeraBox({group:true,errorLoadingMessage:'Неудалось открыть. Попробуйте позднее.',titleFormat: 'Фото {number} / {total} - {title}'});
	//if($$('a._imgview').length>0) box.addItems('a._imgview'); //,{group: false}
	$$('a._imgview').cerabox({
		//group: false,
		preventScrolling: true
	});
});