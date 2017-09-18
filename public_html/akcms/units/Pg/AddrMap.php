<?php # Карта Yandex

class Pg_AddrMap extends PgUnitAbstract {
	public $imgnewspath = 'img/news/';

	public function initAjx()
	{
		global $page;
		return array(
		);
	}
  
	public function _rigthList()
	{
		return array(
		);
	}

	public function render()
	{
		global $sql,$page,$shape;
        #http://api.yandex.ru/maps/tools/constructor/
        #http://webmap-blog.ru/yandex-maps/dobavlyaem-metku-na-kartu-ispolzuya-api-yandeks-kart-versii-2-x;
		$res = '
        <div id="ymaps-map-id" style="height: 350px;border: 1px solid rgba(97,97,97,0.38)"></div>
        <script type="text/javascript">
        function fid_ymaps(ymaps) {
            var map = new ymaps.Map("ymaps-map-id", {
                center: [38.97629, 45.04327],
                zoom: 16,
                type: "yandex#map"
            });
            map.controls
                .add("zoomControl")
                .add("mapTools");
                //.add(new ymaps.control.TypeSelector(["yandex#map", "yandex#satellite", "yandex#hybrid", "yandex#publicMap"]));
            map.geoObjects
                .add(new ymaps.Placemark([38.9758, 45.0434], {
                    balloonContent: "<b>Белый дом</b><br/>Краснодар, улица Красная, 135<br/><img src=\"http://photo.vsedomarossii.ru/area_23/city_1128/street_4780/135_2.jpg\" width=200 height=150  />",
                    iconContent: "Белый дом, альма-матер"
                }, {
                    //preset: "twirl#darkorangeDotIcon"
                    preset: "twirl#darkorangeStretchyIcon"
                }))
        }
        </script>
        <script type="text/javascript" src="http://api-maps.yandex.ru/2.0-stable/?lang=ru-RU&coordorder=longlat&load=package.full&wizard=constructor&onload=fid_ymaps"></script>
		';
		
		return $res;
	}
  
}
