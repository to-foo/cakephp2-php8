<?php
class LangComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
		$this->_controller->loadModel('Language');
		App::import('Core', 'L10n');
	}

	public function Selected() {
		return $this->_controller->Cookie->read('lang');
	}
	public function Choice() {
		$lang_array = $this->_controller->Language->find('all');
		if(isset($this->_controller->data['Language']['beschreibung'])) {
			$lang_beschreibung = $this->_controller->data['Language']['beschreibung'];
		}

		foreach($lang_array as $languages){
			foreach($languages as $language) {
				$lang_choise[$language['id']] = $language['beschreibung'];
				if(isset($lang_beschreibung) && ($language['id'] == $lang_beschreibung)) {
					$this->_controller->Cookie->write('lang', $language['id'], false, '+365 day');
				}
			}
		}
	return $lang_choise;
	}

	public function Change() {
//		if(Configure::check('DynamicLanguage') && !Configure::read('DynamicLanguage')) return;
		CakeSession::write('here', $this->_controller->request->here);
		$lang = $this->_controller->Cookie->read('lang');
		$this->_controller->Language->recursive = -1;
		$language = $this->_controller->Language->find('first', array('conditions'=>array('Language.id'=>$lang)));
		if(!empty($language)) {
			Configure::write('Config.language', $language['Language']['locale']);
//			setlocale(LC_ALL, $language['Language']['locale'], 'deu', 'de_DE', 'german', 'de', 'eng', 'en_US', 'english', 'en');
			setlocale(LC_ALL, $language['Language']['iso']);
		}
	}

	public function Discription() {
		$languages = $this->_controller->Language->find('first', array('conditions'=>array('Language.id' => $this->_controller->Cookie->read('lang'))));
		if(!empty($languages)) return $languages['Language']['locale'];
		return null;
	}

/*
	public function Selected() {
		return $this->_controller->Cookie->read('lang');
	}
	public function Choice() {
		$this->_controller->loadModel('Languages');
		$lang_array = $this->_controller->Languages->find('all');
		if(isset($this->_controller->data['Language']['beschreibung'])) {
			$lang_beschreibung = $this->_controller->data['Language']['beschreibung'];
		}

		foreach($lang_array as $languages){
			foreach($languages as $language) {
				$lang_choise[$language['id']] = $language['beschreibung'];
				if(isset($lang_beschreibung) && ($language['id'] == $lang_beschreibung)) {
					$this->_controller->Cookie->write('lang', $language['id'], false, '+365 day');
				}
			}
		}
	return $lang_choise;
	}

	public function Change() {
		$lang = $this->_controller->Cookie->read('lang');
		$this->_controller->loadModel('Languages');
		foreach($this->_controller->Languages->find('all') as $languages){
			foreach($languages as $language) {
				if($language['id'] == $lang) {
					Configure::write('Config.language', $language['locale']);
					break;
				}
			}
		}
	}

	public function Discription() {
		$this->_controller->loadModel('Languages');
		$output = null;
		$languages = $this->_controller->Languages->find('all');
		foreach($this->_controller->Languages->find('all') as $languages){
			if($languages['Languages']['id'] == $this->_controller->Cookie->read('lang')) {
				$output = $languages['Languages']['locale'];
				break;
			}
		}
	return $output;
	}
*/
}
