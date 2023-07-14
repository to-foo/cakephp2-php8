<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');
App::uses('CakeTime', 'Utility');
App::uses('Sanitize', 'Utility');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {

	private $inserted_ids = array();

	function getInsertID($number=1) {
		if($number == 0) return $this->inserted_ids;
		else return join(',', array_slice($this->inserted_ids, -1*abs($number)));
	}

	public function findByIndex($key, $conditions=array(), $returnFieldOnEmpty=true) {
		$model = $this->alias;
		$table = $this->table;
		$table = Inflector::tableize($model);

		if(!empty($conditions)) {
			if(isset($conditions['conditions'])) $conditions = $conditions['conditions'];
			$_where = $this->getDataSource()->conditions($conditions);
			$result = $this->query('CALL byKey("'.$table.'", "'.$key.'", "'.$_where.'");');
		} else {
			$result = $this->query('CALL byKey("'.$table.'", "'.$key.'", "");');
		}

		if($returnFieldOnEmpty && empty($result)) {
			$Model = ClassRegistry::init($model);

			if(!$Model->hasField($key)) return $result;

			return $Model->find('list', array('fields'=>array($Model->primaryKey, $key), 'conditions'=>$conditions));
		}
		return $result;
	}

    function afterSave($created,$options = array()) {
        if($created) {
            $this->inserted_ids[] = parent::getInsertID();
        }
        return true;
    }

	public function _set_Utf8_encoding($input) {
		$db = ConnectionManager::getDatasource('default');
		if(method_exists($db, 'setUTF8Encoding'))
		{
			if(is_string($input)) {
				return $db->setUTF8Encoding($input);
			} else {
				return $input;
			}
		}
		else
		{
//			CakeLog::error('Function setUTF8Encoding does not exist in database model');
			return $input;
		}
	}

	public function _ReplaceIntegers($result) {
		foreach($result as $k=>$v){
			if(is_array($v))
			{
				$result[$k] = $this->_ReplaceIntegers($v);
			}
			elseif(isset($this->_schema[$k]) && isset($this->_schema[$k]['type']) && $this->_schema[$k]['type'] == 'integer')
			{
				$result[$k] = (int)$v;
			}
		}

		return $result;
	}

	public function _ReplaceCorrruptedUtf8Strings($result) {
		return $result;
		$db = ConnectionManager::getDatasource('default');
		foreach($result as $k=>$v){
			if(is_array($v))
			{
				$result[$k] = $this->_ReplaceCorrruptedUtf8Strings($v);
			}
			elseif(isset($this->_schema[$k]) && isset($this->_schema[$k]['type']) && array_search($this->_schema[$k]['type'], array('string', 'text', 'binary')) !== false)
			{
				$result[$k] = $this->_set_Utf8_encoding($v);
			}

		}

		return $result;
	}

	//numerische Felder in der Datenbank mit numerischen Nullen f�llen, damit Anzeigen richtig funktionieren
	public function afterFind($results, $primary = false) {
		$results = $this->_ReplaceIntegers($results);
		$results = $this->_ReplaceCorrruptedUtf8Strings($results);
		$results = $this->_DateFormateFind($results);
		return $results;
	}

	protected function ChangeFloatVal($Format,$Data,$Schema){
		if(empty($Data)) $Data = 0.00 ;
		if($Format == 'floatval' && is_string($Data)){

			$length = 3;

			if(isset($Schema['length'])){

				$LenghtArray = explode(',',$Schema['length']);
				$length = $LenghtArray[1];

			}

			$Data = number_format(($Data),$length, '.', '');

			return $Data;
		}

		$Data = call_user_func($Format, $Data);

		return $Data;
	}

	public function beforeSave($options = array()) {

		$db = ConnectionManager::getDatasource('default');

		foreach($this->data as $report=>$values) {

			$this->$report = ClassRegistry::init($report);
			$schema = $this->$report->schema();

			foreach($values as $k => $v) {
				if(is_array($v)) continue 2; // Multi select Felder werden mit doppeltem Index angegeben --> Überspringen
				while(preg_match('/^\(.*\)$/', $v)) { $v = preg_replace('/ \( \s* ( ( [^()]*? | (?R) )* ) \s* \) /x', '$1', $v); }

				if(preg_match('/^null$/i', $v)) $v = null;

				if(isset($schema[$k]) && isset($schema[$k]['type'])) {
					if(array_search($schema[$k]['type'], array('string', 'text', 'binary')) !== false)
					{
						 $v = $this->_set_Utf8_encoding($v);
					}
					elseif(preg_match('/\(+[^\)]*\)+/i', $v))
					{
						$tmp = $v;
						$tmp = $this->query('SELECT '.$v);
						while(is_array(reset($tmp))) $tmp = reset($tmp);
						$v = reset($tmp);
					}

					if($schema[$k]['null'] && empty($v)) $v = null;
					else {
						if(isset($db->columns[$schema[$k]['type']]['srcformatter'])) {
							$v = call_user_func($db->columns[$schema[$k]['type']]['srcformatter'], $v);
						}

						if(isset($db->columns[$schema[$k]['type']]['formatter']) && $v !== null)
						{
						if(isset($db->columns[$schema[$k]['type']]['format']))
							{
								$v = @call_user_func($db->columns[$schema[$k]['type']]['formatter'], $db->columns[$schema[$k]['type']]['format'], $v);
							} else {

								$v = $this->ChangeFloatVal($db->columns[$schema[$k]['type']]['formatter'],$v,$schema[$k]);

							}
						}
					}

					// Leere Felder löschen, damit weiter unten korrekte Stadardwerte eingesetzt werden
					//if(empty($v) && !$schema[$k]['null']) unset($values[$k]);
				}

				$v = Sanitize::stripScripts($v);

				$this->data[$report][$k] = trim($v);

			}

			// Fehlende Felder aus der Datenbank oder mit default-Werten ersetzen
			foreach(array_diff_key($schema, $values, array_flip(array('id'))) as $miss_k=>$miss_v) {
				if(!empty($values[$miss_k])) continue;

				$tmp = $this->$report->find('first', array('conditions'=>array($this->$report->name.'.'.$this->$report->primaryKey => $this->$report->{$this->$report->primaryKey})));
				if(!empty($tmp[$report][$miss_k])) {
					$this->data[$report][$miss_k] = $tmp[$report][$miss_k];
					continue;
				}

				switch($miss_v['type'])
				{
					case 'integer':
					case 'boolean':
						$this->data[$report][$miss_k] = intval($miss_v['default']);
						break;

					case 'string':
					case 'text':
					case 'binary':
						$this->data[$report][$miss_k] = empty($miss_v['default']) ? '' : $miss_v['default'];
						break;

					case 'datetime':
					case 'datetime2':
					case 'date':
					case 'time':
						$this->data[$report][$miss_k] = null;//date($db->columns[$schema[$k]['type']]['format']);
						break;
				}

				if (isset($this->data[$report][$miss_k]) && strtolower($this->data[$report][$miss_k]) == 'null') $this->data[$report][$miss_k] = null;
			}
		}

		$options = $this->_DateFormateSave($options);

		return true;
	}

	public function _DateFormateSave($options) {

		$Model = $this->name;
		$Schema = $this->_schema;
		$Lang = Configure::read('Config.language');

		if(empty($Lang)) return $options;

		$Dateformat = Configure::read('Dateformat');

		if(empty($Dateformat)) return $options;
		if(!isset($Dateformat[$Lang])) return $options;

		$CurrentDateformat = $Dateformat[$Lang];

		foreach($this->data[$Model] as $key => $value){

			if(!isset($Schema[$key])) continue;

			$type = $Schema[$key]['type'];

			if($this->data[$Model][$key] != null) $this->data[$Model][$key] = $this->_dateFormatbeforeSave($CurrentDateformat,$type,$this->data[$Model][$key]);
		}

		return $options;
	}

	public function _DateFormateFind($results) {

		if(Configure::check('Dateformat') === false) return $results;

		$Model = $this->name;
		$Schema = $this->_schema;
		$Lang = Configure::read('Config.language');

		if(empty($Lang)) return $results;

		$Dateformat = Configure::read('Dateformat');

		if(empty($Dateformat)) return $results;
		if(!isset($Dateformat[$Lang])) return $results;

		$CurrentDateformat = $Dateformat[$Lang];

		foreach ($results as $key => $val) {

			if(!isset($val[$Model])) continue;

			foreach($val[$Model] as $_key => $_val){

				if(!isset($Schema[$_key])) continue;

				$type = $Schema[$_key]['type'];
				$results[$key][$Model][$_key] = $this->_dateFormatAfterFind($CurrentDateformat,$type,$results[$key][$Model][$_key]);
			}

			foreach ($val as $_key => $_value) {

				if(is_object($this->$_key)){

					$Subschema = $this->$_key->_schema;

					foreach($_value as $__key => $__value){

						if(is_int($__key)){

							$Subsubschema = $this->$_key->_schema;

							foreach($__value as $___key => $___value){

								if(!isset($Subsubschema[$___key])) continue;

								$type = $Subsubschema[$___key]['type'];
								$results[$key][$_key][$__key][$___key] = $this->_dateFormatAfterFind($CurrentDateformat,$type,$results[$key][$_key][$__key][$___key]);
							}

							continue;
						}

						if(!isset($Subschema[$__key])) continue;

						$type = $Subschema[$__key]['type'];
						$results[$key][$_key][$__key] = $this->_dateFormatAfterFind($CurrentDateformat,$type,$results[$key][$_key][$__key]);

					}
				}
			}
		}

		return $results;
	}

	public function _dateFormatbeforeSave($CurrentDateformat,$type,$Value) {

		$Timezone = Configure::read('Config.timezone');

		switch ($type) {
			case 'datetime':
			$Value = CakeTime::toServer($Value, $Timezone, $format = 'Y-m-d H:i:s');
			break;

			case 'date':
			$Value = CakeTime::toServer($Value, $Timezone, $format = 'Y-m-d');
			break;

			case 'time':
			$Value = CakeTime::toServer($Value, $Timezone, $format = 'H:i:s');
			default:

			break;
		}

		return $Value;
	}

	public function _dateFormatAfterFind($CurrentDateformat,$type,$Value) {

		switch ($type) {
			case 'datetime':

				if(!isset($CurrentDateformat[$type])) return $Value;
				if($Value == null) return $Value;

				$Value = date($CurrentDateformat[$type], strtotime($Value));

				break;

			case 'date':

				if(!isset($CurrentDateformat[$type])) return $Value;
				if($Value == null) return $Value;

				$Value = date($CurrentDateformat[$type], strtotime($Value));

				break;

			case 'time':

				if(!isset($CurrentDateformat[$type])) return $Value;
				if($Value == null) return $Value;

				$Value = date($CurrentDateformat[$type], strtotime($Value));

				break;

			default:

				break;
		}

		return $Value;
	}
}
