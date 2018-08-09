<?php // Generate DB model from DBMS Postgres

class pgCmsModelGenerator {

	private function TableNameToModel($name) {
		$names = array();
		foreach (explode('_',$name) as $name) {
			$names[] = mb_ucfirst($name);
		}
		return 'model'.implode('',$names);
	}

	private function ColumnNameToModel($name) {
		$names = array();
		foreach (explode('_',$name) as $name) {
			$names[] = mb_ucfirst($name);
		}
		$names[0] = mb_strtolower($names[0]);
		return implode('',$names);
	}

	/**
	 * Returns the column descriptions for a table.
	 *
	 * The return value is an associative array keyed by the column name,
	 * as returned by the RDBMS.
	 *
	 * The value of each array element is an associative array
	 * with the following keys:
	 *
	 * SCHEMA_NAME      => string; name of database or schema
	 * TABLE_NAME       => string;
	 * COLUMN_NAME      => string; column name
	 * MODEL_NAME		=> property name at the model
	 * COLUMN_POSITION  => number; ordinal position of column in table
	 * DATA_TYPE        => string; SQL datatype name of column
	 * COMPLETE_TYPE	=> type as it presented into database
	 * DEFAULT          => string; default expression of column, null if none
	 * NULLABLE         => boolean; true if column can have nulls
	 * LENGTH           => number; length of CHAR/VARCHAR
	 * SCALE            => number; scale of NUMERIC/DECIMAL
	 * PRECISION        => number; precision of NUMERIC/DECIMAL
	 * UNSIGNED         => boolean; unsigned property of an integer type
	 * PRIMARY          => boolean; true if column is part of the primary key
	 * PRIMARY_POSITION => integer; position of column in primary key
	 * IDENTITY         => integer; true if column is auto-generated with unique values
	 * COMMENT          => string; field comment
	 *
	 * @param  string $tableName
	 * @param  string $schemaName OPTIONAL
	 *
	 * @return array
	 */
	public function describeTable($tableName, $schemaName = 'public') {
		/* @var $sql pgdb */
		global $sql;
		$query = "SELECT
				a.attnum,
				n.nspname,
				c.relname,
				a.attname AS colname,
				t.typname AS type,
				a.atttypmod,
				FORMAT_TYPE(a.atttypid, a.atttypmod) AS complete_type,
				d.adsrc AS default_value,
				a.attnotnull AS notnull,
				a.attlen AS length,
				co.contype,
				ARRAY_TO_STRING(co.conkey, ',') AS conkey,
				col_description(c.oid,a.attnum)
			FROM pg_attribute AS a
				JOIN pg_class AS c ON a.attrelid = c.oid
				JOIN pg_namespace AS n ON c.relnamespace = n.oid
				JOIN pg_type AS t ON a.atttypid = t.oid
				LEFT OUTER JOIN pg_constraint AS co ON (co.conrelid = c.oid
					AND a.attnum = ANY(co.conkey) AND co.contype = 'p')
				LEFT OUTER JOIN pg_attrdef AS d ON d.adrelid = c.oid AND d.adnum = a.attnum
			WHERE a.attnum > 0 AND c.relname = " . $sql->t($tableName);
		if ($schemaName) {
			$query .= " AND n.nspname = " . $sql->t($schemaName);
		}
		$query .= ' ORDER BY a.attnum';
		//$stmt = $sql->query($sql);
		// Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
		$result        = $sql->queryObj($query);
		$result->result_type = PGSQL_NUM;
		$attnum         = 0;
		$nspname        = 1;
		$relname        = 2;
		$colname        = 3;
		$type           = 4;
		$atttypemod     = 5;
		$complete_type  = 6;
		$default_value  = 7;
		$notnull        = 8;
		$length         = 9;
		$contype        = 10;
		$conkey         = 11;
		$coldescription = 12;
		$dbfields       = array();
		$modelfields       = array();
		foreach ($result as $row) {
			$defaultValue = $row[$default_value];
			if ($row[$type] == 'varchar' || $row[$type] == 'bpchar' || $row[$type] == 'text') {
				if (preg_match('/character(?: varying)?(?:\((\d+)\))?/', $row[$complete_type], $matches)) {
					if (isset($matches[1])) {
						$row[$length] = $matches[1];
					} else {
						$row[$length] = null; // unlimited
					}
				}
				if (preg_match("/^'(.*?)'::(?:character varying|bpchar|text)$/", $defaultValue, $matches)) {
					$defaultValue = $matches[1];
				}
			}
			list($primary, $primaryPosition, $identity) = array(false, null, false);
			if ($row[$contype] == 'p') {
				$primary         = true;
				$primaryPosition = array_search($row[$attnum], explode(',', $row[$conkey])) + 1;
				$identity        = (bool)(preg_match('/^nextval/', $row[$default_value]));
			}
			$fieldInfo = array(
				//'SCHEMA_NAME'      => mb_strtolower($row[$nspname]),
				//'TABLE_NAME'       => mb_strtolower($row[$relname]),
				'COLUMN_NAME'      => mb_strtolower($row[$colname]),
				'MODEL_NAME'	   => $this->ColumnNameToModel($row[$colname]),
				//'COLUMN_POSITION'  => $row[$attnum],
				'DATA_TYPE'        => $row[$type],
				'COMPLETE_TYPE'    => $row[$complete_type],
				'DEFAULT'          => $defaultValue,
				'NULLABLE'         => (bool)($row[$notnull] != 't'),
				'LENGTH'           => $row[$length],
				//'SCALE'            => null, // @todo_
				//'PRECISION'        => null, // @todo_
				//'UNSIGNED'         => null, // @todo_
				'PRIMARY'          => $primary,
                //'PRIMARY_POSITION' => $primaryPosition,
				'IDENTITY'         => $identity,
				'COMMENT'		   => $row[$coldescription],
			);
			$dbfields[$fieldInfo['COLUMN_NAME']] = $fieldInfo['MODEL_NAME'];
			$modelfields[$fieldInfo['MODEL_NAME']] = $fieldInfo;
		}

		$tableComment = $sql->query_one('SELECT obj_description('.$sql->t($schemaName.'.'.$tableName).'::regclass, \'pg_class\')');

		return array(
			'table'=>$tableName,
			'model'=>$this->TableNameToModel($tableName),
			'schema'=>$schemaName,
			'comment'=>$tableComment,
			//'dbfields'=>$dbfields,
			'fields'=>$modelfields,
		);
	}

    /**
     * Создание модели
     *
     * @param  string $tableName
     * @param  string $schemaName OPTIONAL
     *
     * @return void
     * @throws CmsException
     */
	public function generate($tableName, $schemaName = 'public') {
		$tableInfo = $this->describeTable($tableName,$schemaName);
        $isView = preg_match('/view$/iu',$tableInfo['model'])===1;
        $primary = ''; $primaryDB = '';

        $tableCommentRaw = $tableInfo['comment'];
        if (!$tableInfo['comment']) CmsLogger::logError($tableInfo['model'].' Без описания');
        $tableAttr = array();
        if (mb_strpos($tableInfo['comment'],'|')!==false) {
            $tableCommentInfo = explode('|',$tableInfo['comment']);
            for($i=1;$i<count($tableCommentInfo);$i++) {
                @list($a,$v) = explode('=',$tableCommentInfo[$i]);
                $tableAttr[$a]=$v;
            }
            unset($a,$v);
            if (isset($tableAttr['name'])) $objnameDB = $tableAttr['name'];
            $tableInfo['comment'] = $tableCommentInfo[0];
        }
        if (array_key_exists('i',$tableAttr)) {
            CmsLogger::log("  $schemaName.$tableName пропущена");
            return;
        }

		$template = file_get_contents('akcms/models/cmsModelTemplate.php');
		//echo $template;

		$fieldsProperties = array();
		$fieldsStatic = array();

		$maxNameLength = 0; foreach ($tableInfo['fields'] as &$field) if (mb_strlen($field['MODEL_NAME'])>$maxNameLength) $maxNameLength = mb_strlen($field['MODEL_NAME']); $maxNameLength++;
		foreach ($tableInfo['fields'] as &$field) {

            if (!$field['COMMENT']) CmsLogger::logError($tableInfo['model'].'.'.$field['COLUMN_NAME'].' Без описания');
            $field['COMMENT'] = str_replace("\r",'',$field['COMMENT']);
            $field['COMMENT'] = str_replace(PHP_EOL,', ',$field['COMMENT']);
            $fieldAttr = array();
            if (mb_strpos($field['COMMENT'],'|')!==false) {
                $fieldCommentInfo = explode('|',$field['COMMENT']);
                for($i=1;$i<count($fieldCommentInfo);$i++) {
                    @list($a,$v) = explode('=',$fieldCommentInfo[$i]);
                    $fieldAttr[$a]=$v;
                }
                unset($a,$v);
                $field['COMMENT'] = $fieldCommentInfo[0];
            }
            if (array_key_exists('i',$fieldAttr)) {
                CmsLogger::log("  Пропуск поля $field[COLUMN_NAME]");
                continue;
            } //Пропус поля
            if (array_key_exists('>',$fieldAttr)) {
                $field['RELATE_TO'] = $fieldAttr['>'];
            } //Пропус поля

			// string|integer|int|boolean|bool|float|double|object|mixed|array|resource|void|null|callback|false|true|self
			$typeSimple = '';
			switch ($field['DATA_TYPE']) {
				case 'int':
				case 'int4':
				case 'int8':
				case 'serial2':
				case 'serial4':
				case 'serial8':
					$typeSimple = 'int';
					$field['FIELD_CLASS'] = 'FieldInt';//
					unset($field['LENGTH']);
				break;
				case '_int8':
					$typeSimple = 'array';
					$field['QUOTE_FUNCTION'] = 'a_d';
					$field['FIELD_CLASS'] = 'FieldManyInt';//
					unset($field['LENGTH']);
					break;

				case 'float4':
				case 'float8':
					$typeSimple = 'float';
					$field['FIELD_CLASS'] = 'FieldDouble';//
					unset($field['LENGTH']);
					break;
				case '_float8':
					$typeSimple = 'array';
					$field['FIELD_CLASS'] = 'FieldManyDouble';//
					unset($field['LENGTH']);
					break;

				case 'timestamp':
				case 'timestamptz':
					$typeSimple = 'string';
					$field['FIELD_CLASS'] = 'FieldDateTime';//
					unset($field['LENGTH']);
					break;
				case 'date':
					$typeSimple = 'string';
					$field['FIELD_CLASS'] = 'CMSFieldDate';//
					unset($field['LENGTH']);
					break;

				case 'text':
					$typeSimple = 'string';
					$field['FIELD_CLASS'] = 'FieldText';//
					unset($field['LENGTH']);
					break;
				case 'varchar':
					$typeSimple = 'string';
					$field['FIELD_CLASS'] = 'FieldString';//
					break;
				case '_text':
					$typeSimple = 'string';
					$field['FIELD_CLASS'] = 'FieldManyText';//
					unset($field['LENGTH']);
					break;

				case 'bool':
					$typeSimple = 'bool';
					$field['FIELD_CLASS'] = 'FieldBool';//
					unset($field['LENGTH']);
					break;

				default: throw new CmsException('Неизвестный тип данных: '.$field['DATA_TYPE'].' ('.$field['COMPLETE_TYPE'].')');
			}

			$comment = str_replace("\r\n",', ',$field['COMMENT']);
			$fieldsProperties[] = ' * @property '.str_pad($typeSimple,8).''.$field['MODEL_NAME'].' '.$comment;
			$fieldsStatic[] = "    ".'public static $_'.str_pad($field['MODEL_NAME'],$maxNameLength).' = \''.$field['COLUMN_NAME'].'\';';
            if ($field['PRIMARY']) {
                $primary = $field['MODEL_NAME'];
                $primaryDB = $field['COLUMN_NAME'];
            }
			$tableInfo['fieldsDB'][$field['COLUMN_NAME']]=$field['MODEL_NAME'];
			unset($field['SCHEMA_NAME']);
			unset($field['TABLE_NAME']);
			unset($field['MODEL_NAME']);
			unset($field['DEFAULT']);
			unset($field['COLUMN_POSITION']);
            unset($field['PRIMARY']);
			unset($field['PRIMARY_POSITION']);
			unset($field['DATA_TYPE']);
            unset($field['COMPLETE_TYPE']);
			unset($field['IDENTITY']);
			//unset($field['COMMENT']);
		}
        $tableInfo['primary'] = $primary;
        $tableInfo['primaryDB'] = $primaryDB;

        $tableInfo['tables'][] = [
            'table'=>$tableInfo['table'],
            'primary'=>$tableInfo['primary'],
            'primaryDB'=>$tableInfo['primaryDB'],
            'schema'=>$tableInfo['schema'],
            'prefix'=>''
        ];
        $tableVar = $tableInfo;
        unset($tableVar['model']);
		$tableVar = var_export($tableVar,true);
		$tableVar = preg_replace('/\n\s*array \(/','array(',$tableVar);
		$tableVar = str_replace("\n","\n  ",$tableVar);

		$template = str_replace('cmsModelTemplate',$tableInfo['model'],$template);
		$comment = str_replace("\r\n",' * ',$tableInfo['comment']);
		$template = str_replace('{#properties#}',implode("\n",$fieldsProperties),$template);
		$template = str_replace('    //{#staticfields#}',implode("\n",$fieldsStatic),$template);
		$template = str_replace('{#tableName#}',$tableInfo['table'],$template);
		$template = str_replace('{#schemaName#}',$tableInfo['schema'],$template);
		$template = str_replace('{#tablecomment#}',$comment,$template);
		$template = str_replace('$struct = array()','$struct = '.$tableVar,$template);

        if (mb_strpos($tableInfo['model'],'modelCms')===0)
            $modelPath = 'akcms/models/'.$tableInfo['model'].'.php';
		else $modelPath = ('akcms/u/models/').$tableInfo['model'].'.php';

		if (!file_exists($modelPath)) {
			file_put_contents($modelPath,$template);
            CmsLogger::logInfo('Модель '.$tableInfo['model'].' создана '.mb_strlen($template));
		} else {
			$splitter = '/*** customer extensions ***/';
			$oldTemplate = explode($splitter,file_get_contents($modelPath));
			$newTemplate = explode($splitter,$template);

			$updateTemplate = $newTemplate[0].$splitter.$oldTemplate[1];
			file_put_contents($modelPath,$updateTemplate);
            CmsLogger::logInfo('Модель '.$tableInfo['model'].' обновлена '.mb_strlen($updateTemplate));
		}
	}
}

/**
 * GenModel - Generate DB model from DBMS Postgres
 */
class genModel extends cliUnit {

    /**
     * Generate all models
     * @param string $filter
     * @throws CmsException
     */
    public function genAllAction($filter = ''){
        global $sql,$cfg;

        $md = new pgCmsModelGenerator();
        profiler::showOverallTimeToTerminal();

        $filter = str_replace('*','%',$filter);
        if ($filter=='')
            $_filter = sprintf(' AND NOT (tablename ilike %s)',
                $sql->t('cms_%')
            );
        else $_filter = ' AND tablename like '.$sql->t($filter);

        $query = 'select tablename from pg_tables where schemaname='.$sql->t($cfg['db'][1]['schema']).$_filter.' order by 1';
        $tablesNames = $sql->query_all_column($query);
        if (count($tablesNames)>0)
            foreach ($tablesNames as $tableName) {
                CmsLogger::logInfo('Генерация '.$tableName);
                $md->generate($tableName,$cfg['db'][1]['schema']);
            }
        else CmsLogger::logInfo('Нет подходящих таблиц');
    }

    public function runAction(){
        global $sql,$cfg;

        profiler::showOverallTimeToTerminal(true);

//        $modelCmsGalleryPhotos = (new modelCmsGalleryPhotos())->where(
//            [modelCmsGalleryPhotos::$_cgpCreated,'>','2018-04-01'],
//            [modelCmsGalleryPhotos::$_idCgp,'>',86]
//        )->join(new modelCmsGaleries())->get();

//        foreach ($modelCmsGalleryPhotos as $modelCmsGalleryPhoto) {
//            var_log_terminal($modelCmsGalleryPhoto);
//        }


        $tagList = (new modelCmsGalleryPhotos())
            ->join(new modelCmsGaleries())
            ->join(new modelCmsGallerySec())
            ->fields()
            ->get();


        try {

            /*
                $cs =(new modelCmsSections())->fields(array(
                    modelCmsSections::$_SectionId,
                    modelCmsSections::$_SecGlrId,
                    //modelCmsSections::$_SecCreated,
                    modelCmsSections::$_SecPage,
                    modelCmsSections::$_SecUrlFull,

                ))->
                where(array(
                    array(modelCmsSections::$_SecUrlFull,'=',new rawsql(modelCmsSections::$_SecUrl)),
                    //array(modelCmsSections::$_SecGlrId,'<>',0),
                ))->OR_(modelCmsSections::$_SectionId,'=Any',[2,3])

                ->get();
            */


            //$cs = new modelCmsSections(1);
            //$cs->SecCreated = $cs->SecCreated;
            //$cs->update();
            //$cs->insert();

            echo "\n";
            CmsLogger::log('Готово');

        } catch (Exception $e) {
            $sql->command('rollback');
            echo 'Caught exception: ',$e->getMessage(), "\n";
            echo $e->getTraceAsString();
            //echo $e->getMessage()."\n";
            #print_r_($e);
            //sleep(6);
            echo PHP_EOL;
        }




    }
}

