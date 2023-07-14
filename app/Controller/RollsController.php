<?php
App::uses('AppController', 'Controller');
/**
 * Rolls Controller
 *
 * @property Roll $Roll
 */
class RollsController extends AppController {

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Roll->recursive = 0;
		$this->set('rolls', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Roll->exists($id)) {
			throw new NotFoundException(__('Invalid roll'));
		}
		$options = array('conditions' => array('Roll.' . $this->Roll->primaryKey => $id));
		$this->set('roll', $this->Roll->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Roll->create();
			if ($this->Roll->save($this->request->data)) {
				$this->Session->setFlash(__('Roll was saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('Roll could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Roll->exists($id)) {
			throw new NotFoundException(__('Invalid roll'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Roll->save($this->request->data)) {
				$this->Session->setFlash(__('Roll was saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('Roll could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Roll.' . $this->Roll->primaryKey => $id));
			$this->request->data = $this->Roll->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Roll->id = $id;
		if (!$this->Roll->exists()) {
			throw new NotFoundException(__('Invalid roll'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Roll->delete()) {
			$this->Session->setFlash(__('Roll was deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Roll was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
