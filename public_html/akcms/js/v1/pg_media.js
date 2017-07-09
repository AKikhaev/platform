window.addEvent('domready', function() {
	var players=[],p_count=0;
	var mediabox = function (slctr, tp) {
		var p_h = tp == 'a' ? 24 : 320;
		slctr = $$(slctr);
		if (slctr.length > 0)
			slctr.each(function (aitem) {
				++p_count;
				new Element('div', {'id':'pc' + p_count}).inject(aitem.addClass('hidden'), 'after');
				if (!Browser.ie) aitem.destroy();
				if (Browser.ie7!=undefined) {
					players.push(new Swiff('/akcms/js/v1/player/player.swf', {
					id: 'po'+p_count,
					width: 480,
					height: p_h,
					container: 'pc'+p_count,
					params: {
					wmode: 'transparent',
					allowfullscreen: 'true',
					allowscriptaccess:'always'
					},
					vars: {file:aitem.href}
					}));
				}
				else {
					var po = jwplayer('pc' + p_count).setup({
						'flashplayer':'/akcms/js/v1/player/player.swf',
						'id':'po' + p_count,
						'width':'480',
						'height':p_h,
						'file':aitem.href,
						'image':'',
						'controlbar':'bottom'
					});
					players.push(po);

					jwplayer('pc' + p_count).onPlay(function () {
						players.each(function (poi) {
							if (po.id != poi.id) poi.pause(true);
						});
					});
				}
			});
	};
	mediabox('a._audio','a');
	mediabox('a._video','v');
});