<?php // phpMorphy test
class words extends cliUnit {
    public function runAction(){
        global $sql;

        try {

            $morphy = phpMorphyAdapter::getInstance();

            $obj["srchw_word"] = 'СТУЛ';
            $base_forms = $morphy->getAllFormsWithGramInfo(array($obj["srchw_word"]));

            var_dump__($obj,$base_forms);

        } catch (Exception $e) {
            $sql->command('rollback');
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

    }
}


