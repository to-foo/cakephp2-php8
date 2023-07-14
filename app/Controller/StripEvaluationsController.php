<?php
App::uses('AppController', 'Controller');
/**
 * StripEvaluations Controller
 *
 * @property StripEvaluation $StripEvaluation
 */
class StripEvaluationsController extends AppController {

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->StripEvaluation->recursive = 0;
		$this->set('stripEvaluations', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->StripEvaluation->exists($id)) {
			throw new NotFoundException(__('Invalid strip evaluation'));
		}
		$options = array('conditions' => array('StripEvaluation.' . $this->StripEvaluation->primaryKey => $id));
		$this->set('stripEvaluation', $this->StripEvaluation->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->StripEvaluation->create();
			if ($this->StripEvaluation->save($this->request->data)) {
				$this->Session->setFlash(__('The strip evaluation has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The strip evaluation could not be saved. Please, try again.'));
			}
		}
		$stripDatas = $this->StripEvaluation->StripDatum->find('list');
		$this->set(compact('stripDatas'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->StripEvaluation->exists($id)) {
			throw new NotFoundException(__('Invalid strip evaluation'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->StripEvaluation->save($this->request->data)) {
				$this->Session->setFlash(__('The strip evaluation has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The strip evaluation could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('StripEvaluation.' . $this->StripEvaluation->primaryKey => $id));
			$this->request->data = $this->StripEvaluation->find('first', $options);
		}
		$stripDatas = $this->StripEvaluation->StripDatum->find('list');
		$this->set(compact('stripDatas'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->StripEvaluation->id = $id;
		if (!$this->StripEvaluation->exists()) {
			throw new NotFoundException(__('Invalid strip evaluation'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->StripEvaluation->delete()) {
			$this->Session->setFlash(__('Strip evaluation deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Strip evaluation was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
