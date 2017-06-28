<?php # Карта сайта в виде структуры <ul>
class Pg_SiteMap extends PgUnitAbstract {

	function initAjx()
	{
		return array(
		);
	}

	function _rigthList()
	{
		return array(
		);
	}

	function initAcl()
	{
		return array(
		);
	}

	function render()
	{ # http://javascript.ru/ui/tree
		$siteAllMenu = $GLOBALS['page']->getAllMenu();
		$res = '
		<style>
		ul.tree, ul.tree ul { 
			list-style-type: none; 
			background: url(/img/units/tr_vl.png) repeat-y; 
			margin: 0; 
			padding: 0; 
		} 
		ul.tree ul { margin-left: 10px; } 
		ul.tree li { 
			margin: 0; 
			padding: 0 12px; 
			line-height: 20px; 
			background: url(/img/units/tr_n.png) no-repeat; 
		} 
		ul.tree li.last { 
			background: #fff url(/img/units/tr_ln.png) no-repeat; 
		}
		</style>
		<script type="text/javascript">
		window.addEvent(\'domready\', function() {
			$$(\'ul.tree li:last-child\').addClass(\'last\');
		});
		</script>
		';
		return $res.'<ul class="tree">'.VisualTheme::buildSiteMap($siteAllMenu).'</ul>';
	}
}
