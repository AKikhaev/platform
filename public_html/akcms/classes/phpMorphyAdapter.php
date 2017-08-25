<?php
# https://github.com/MAXakaWIZARD/phpmorphy/
# http://phpmorphy.sourceforge.net/dokuwiki/manual
class phpMorphyAdapter extends phpMorphy {
    private static $instance = null;
    private function __clone() {}
    /**
     * @return phpMorphyAdapter
     */
    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function __construct() {
		// set some options
		$opts = array(
			// storage type, follow types supported
			// STORAGE_FILE - use file operations(fread, fseek) for dictionary access, this is very slow...
			// STORAGE_SHM - load dictionary in shared memory(using shmop php extension), this is preferred mode
			// STORAGE_MEM - load dict to memory each time when phpMorphy intialized, this useful when shmop ext. not activated. Speed same as for PHPMORPHY_STORAGE_SHM type
            'storage' => phpMorphy::STORAGE_FILE,
			// Extend graminfo for getAllFormsWithGramInfo method call
			'with_gramtab' => true,
			// Enable prediction by suffix
			'predict_by_suffix' => true, 
			// Enable prediction by prefix
			'predict_by_db' => true
		);
	
		// Path to directory where dictionaries located
		$dir = 'akcms/classes/phpMorphy/_dicts/utf-8/';
	
		// Create descriptor for dictionary located in $dir directory with russian language
		parent::__construct($dir, 'ru_RU', $opts);
		//parent::phpMorphy($dict_bundle = new phpMorphy_FilesBundle($dir, 'rus'), $opts);
    }

    function getBaseForm($bulk_words,$union = true)
    {
        $base_form = parent::getBaseForm($bulk_words);
        for ($i=0; $i<count($bulk_words); $i++)
        {
            if (isset($base_form[$bulk_words[$i]])?$base_form[$bulk_words[$i]]==false:false)
                $base_form[$bulk_words[$i]][0] = $bulk_words[$i];
        }
        return $union?array_unique($this->extractValues($base_form)):$base_form;
    }

    function getAllForms($bulk_words,$union = true)
    {
        $all_forms = parent::getAllForms($bulk_words);
        for ($i=0; $i<count($bulk_words); $i++)
            if ($all_forms[$bulk_words[$i]]==false)
                $all_forms[$bulk_words[$i]][0] = $bulk_words[$i];
        return $union?array_unique($this->extractValues($all_forms)):$all_forms;
    }

    function extractValues(&$arr)
    {
        $newArr = array();
        foreach ($arr as $arr1) if (is_array($arr1)) foreach ($arr1 as $val) $newArr[] = $val;
        return $newArr;
    }
}