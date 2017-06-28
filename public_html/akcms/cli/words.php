<?php
if(php_sapi_name()!=='cli')die('<!-- not allowed -->');
chdir('../..');
ini_set('memory_limit', '228M');
require_once('akcms/core/core.php'); LOAD_CORE_CLI();

try {
	$query = 'select * from cms_srchwords';
	$qobj = $sql->queryObj($query);
	$i = 0;
	toLogInfo('Всего статей '.$qobj->count());

    $morphy = phpMorphyAdapter::getInstance();
	foreach($qobj as $obj) {
	$obj["srchw_word"] = 'СТУЛ';
		$base_forms = $morphy->getAllFormsWithGramInfo(array($obj["srchw_word"]));

		var_dump__($obj,$base_forms);
	}

	toLogInfo('Завершено!');
	//$query = "";
	//$res = $sql->command($query);
	echo "\nГотово\n";
} catch (Exception $e) {
	$sql->command('rollback');
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
