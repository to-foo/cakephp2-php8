<?php
class MessagesToolComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function GenerateFlashMessage($Data){

		if(isset($this->_controller->request->data['json_true'])) $Data =$this->__GenerateFlashMessageJson($Data);
		else $Data = $this->__GenerateFlashMessageHtml($Data);

		return $Data;
	}

	protected function __GenerateFlashMessageHtml($Data){

		if(!isset($Data['FlashMessages'])) return $Data;
		if(count($Data['FlashMessages']) == 0) return $Data;

		foreach ($Data['FlashMessages'] as $key => $value) {

			switch ($value['type']) {

				case 'deactive':
				$this->_controller->Flash->deactive($value['message'],array('key' => 'deactive'));
				break;

				case 'error':
				$this->_controller->Flash->error($value['message'],array('key' => 'error'));
				break;

				case 'intime':
				$this->_controller->Flash->intime($value['message'],array('key' => 'intime'));
				break;

				case 'progress':
				$this->_controller->Flash->progress($value['message'],array('key' => 'progress'));
				break;

				case 'success':
				$this->_controller->Flash->success($value['message'],array('key' => 'success'));
				break;

				case 'warning':
				$this->_controller->Flash->warning($value['message'],array('key' => 'warning'));
				break;


				}
		}

		return $Data;

	}

	protected function __GenerateFlashMessageJson($Data){

		return $Data;

	}

}
