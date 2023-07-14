<?php
class QualificationComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
		$this->_controller->Qualification->ThirdPart = 'SECTOR Cert';
	}


	public function SectorCertCheck($id) {

	//	$id = 'Z-SC-00001';

		 App::uses('HttpSocket', 'Network/Http');

	 		$options = array(
	 		  'header' => array(
	 		    'X-Auth-Token' => '18a1cbf8-3191-4cfc-a4fe-d65ef6922427',
	 		  )
	 		);


	 		$HttpSocket = new HttpSocket();
	 		$response = $HttpSocket->post('https://certcheck.sectorcert.com/api/v1/cert/de/' . $id, NULL, $options);

//			return json_decode($response->body,true);
			return $response->body;

	}

	public function ImportExamierData() {

		$this->_controller->loadModel('ImportExaminer');
		$this->_controller->loadModel('ImportExaminersCertdata');

		$ImportExaminer = $this->_controller->ImportExaminersCertdata->find('all');

		$xml = $this->_controller->Xml->DatafromXml('Examiner', 'file', null);

		$this->_CorrectCertificateLevel();

		foreach ($ImportExaminer as $key => $value) {

//			$this->_AddToNewTable($value);
//			$this->_HashCertInfo($value);
//			$Certificates =	$this->_SeperateCertInfos($value);
//			$Certificates =	$this->_CreateTimeRanges($Certificates,$value);
//				$this->_AddToExaminerTable($value);

		}
	}

	public function SaveQulification($testingmethods){

		if(!isset($this->_controller->request->data['Certificate'])) return;
		if($this->_controller->request->is('post') != true && $this->_controller->request->is('put') != true) return;

		$errors = $this->_CheckQulificationErrors();

		if(count($errors) > 0) return;

		$data = $this->_controller->request->data;

		if(!isset($data['Certificate']['testingmethod']) && isset($data['Certificate']['testingmethod_id'])){
			if(isset($testingmethods[$data['Certificate']['testingmethod_id']])){
				$data['Certificate']['testingmethod'] = $testingmethods[$data['Certificate']['testingmethod_id']];
			}
		}

		$arrayData['settings'] = $this->_controller->Xml->CollectXMLFromFile('Certificate');

		$data = $this->_CheckCertificateTestingmethod($data);

		if($data === false) return false;

		$data = $this->_CheckCertificateTestingmethodExists($data,$testingmethods);
		if($data === false) return false;

		$data = $this->_CheckCertificateSameLevel($data,$testingmethods);
		if($data === false) return false;

		$data = $this->_CheckCertificateSameSectorTestingmethodCertificate($data,$testingmethods);
		if($data === false) return false;

		$data = $this->_CollectCurrentExaminer($data);
		if($data === false) return false;

		$data = $this->_controller->Data->ChangeDropdownData($data, $arrayData, array('Certificate'));

		$data = $this->_FutureCheck($data);
		if($data === false) return false;

		$data = $this->_CollectCurrentTestingmethod($data);
		if($data === false) return false;

		$data = $this->_CollectCertificateSave($data);
		if($data === false) return false;

		$data = $this->_CollectCertificateDataSave($data);
		if($data === false) return false;

		$data = $this->_CollectCertificateUpdate($data);
		if($data === false) return false;

		$data = $this->_CertificateDataUpdate($data);
		if($data === false) return false;

		$data = $this->_CollectCertificateDataUpdate($data);
		if($data === false) return false;


		$data['projectvars'] = $this->_controller->request->projectvars['VarsArray'];

		$data['projectvars'][15] = $data['Examiner']['id'];
		$data['projectvars'][16] = $data['Certificate']['id'];
		if(isset($data['CertificateData']['id'])) $data['projectvars'][17] = $data['CertificateData']['id'];

		$data['FormName']['controller'] = 'examiners';
		$data['FormName']['action'] = 'qualifications';
		$data['FormName']['terms'] = implode('/', $data['projectvars']);

		return $data;

	}

	protected function _CollectCertificateUpdate($data){

		if(!isset($data['Certificate']['id'])) return $data;
		if($data['Certificate']['id'] == 0) return $data;
		if($data['Certificate']['id'] == '') return $data;
		if(empty($data['Certificate']['id'])) return $data;

		if(empty($data['Certificate']['certificat'])) $data['Certificate']['certificat'] = '-';

		if ($this->_controller->Examiner->Certificate->save($data['Certificate']) == false){

			$errors = $this->_controller->Examiner->Certificate->validationErrors;

			$message = __('An error has occurred');
			$this->_controller->Session->delete('Certificate.form');
			$this->_controller->Flash->error($message, array('key' => 'eroor'));

			return false;

		}

		$message = __('The qualification has been saved');
		
		$this->_controller->Session->delete('Certificate.form');
		$this->_controller->Flash->success($message, array('key' => 'success'));

		$this->_controller->Autorisierung->Logger($data['Certificate']['id'], $data['Certificate']);

		$this->_controller->Examiner->Certificate->recursive = -1;

		$certificate = $this->_controller->Examiner->Certificate->find('first',array(
			'conditions' => array(
					'Certificate.id' => $data['Certificate']['id']
				)
			)
		);

		$data['Certificate'] = $certificate['Certificate'];

		$data = $this->_UpdateCertificateTestingmethod($data);

		return $data;
	}

	protected function _CertificateDataUpdate($data){

		if(!isset($this->_controller->request->data['Certificate'])) return $data;
//		if($this->_controller->request->is('put') != true) return $data;
		if(!isset($data['Certificate']['id'])) return $data;
		if($data['Certificate']['id'] == 0) return $data;

		$this->_controller->Examiner->Certificate->CertificateData->recursive = -1;

		$certificate_data = $this->_controller->Examiner->Certificate->CertificateData->find('first',array(
			'order' => array('id DESC'),
			'conditions' => array(
				'CertificateData.certificate_id' => $data['Certificate']['id']
				)
			)		
		);

		if(count($certificate_data) == 0 && $data['Certificate']['certificate_data_active'] == 1){

			$data = $this->_CollectCertificateDataSaveAfter($data);
			return $data;

		}

		if(count($certificate_data) == 0) return $data;

		if(!isset($this->_controller->request->data['CertificateData'])) return $data;

		$Insert = $this->_controller->request->data['CertificateData'];

		$Insert['id'] = $certificate_data['CertificateData']['id'];

		if(!$this->_controller->Examiner->Certificate->CertificateData->save($Insert)){
			return false;
		}

		return $data;
	}

	protected function _CollectCertificateDataUpdate($data){

		if(!isset($data['Certificate']['id'])) return $data;
		if($data['Certificate']['id'] == 0) return $data;

		$this->_controller->Examiner->Certificate->CertificateData->recursive = -1;

		$certificate_data = $this->_controller->Examiner->Certificate->CertificateData->find('first',array(
			'order' => array('id DESC'),
			'conditions' => array(
				'CertificateData.certificate_id' => $data['Certificate']['id']
				)
			)		
		);

		if(count($certificate_data) == 0) return $data;

		$data['CertificateData'] = $certificate_data['CertificateData'];

		$update = array();

		$update['CertificateData']['id'] = $certificate_data['CertificateData']['id'];
		$update['CertificateData']['certificate_id'] = $data['Certificate']['id'];
		$update['CertificateData']['examiner_id'] = $data['Certificate']['examiner_id'];
		$update['CertificateData']['testingmethod'] = $data['Certificate']['testingmethod'];
		$update['CertificateData']['first_registration'] = $data['CertificateData']['first_registration'];
		$update['CertificateData']['recertification_in_year'] = $data['Certificate']['recertification_in_year'];
		$update['CertificateData']['renewal_in_year'] = $data['Certificate']['renewal_in_year'];
		$update['CertificateData']['horizon'] = $data['Certificate']['horizon'];
		$update['CertificateData']['user_id'] = $this->_controller->Auth->user('id');

		if($this->_controller->Examiner->Certificate->CertificateData->save($update) == false){

			$message = __('An error has occurred');
			$this->_controller->Session->delete('Certificate.form');
			$this->_controller->Flash->error($message, array('key' => 'eroor'));

			return false;

		}

		$certificate_data_option = array(
			'conditions' => array(
				'CertificateData.certificate_id' => $data['Certificate']['id'],
			),
		);

		$this->_controller->Examiner->Certificate->CertificateData->recursive = -1;
		$certificate_data = $this->_controller->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

		$data['CertificateData'] = $certificate_data['CertificateData'];

		// Wenn eine Zertifizierungsfreigabe zurück genomen wird,
        // Werden die entsprechenden Werte in der Qualifikation auf null gesetzt
		if ($data['Certificate']['certificate_data_active'] == 0) {

			$data['Certificate']['certificat'] = '-';
			$data['Certificate']['first_certification'] = 0;
			$data['Certificate']['renewal_in_year'] = 0;
			$data['Certificate']['recertification_in_year'] = 0;
			$data['Certificate']['horizon'] = 0;

			// Wenn eine Zertifizierungsfreigabe zurück genommen wird
        	// Werden alle Zertifizierungsdaten auf gelöscht gestellt
            $this->_controller->Examiner->Certificate->CertificateData->updateAll(
                array(
                    'CertificateData.deleted' => 1,
                    'CertificateData.active' => 0,
                    'CertificateData.user_id' => $this->_controller->Auth->user('id'),
                    ),
                array('CertificateData.certificate_id' => $data['Certificate']['id'])
             );

		}
		if ($data['Certificate']['certificate_data_active'] == 1) {

			$certificat_data_current = array(
				'CertificateData.first_certification' => $data['Certificate']['first_certification'],
				'CertificateData.renewal_in_year' => $data['Certificate']['renewal_in_year'],
				'CertificateData.recertification_in_year' => $data['Certificate']['recertification_in_year'],
				'CertificateData.horizon' => $data['Certificate']['horizon'],
				'CertificateData.deleted' => 0,
				'CertificateData.active' => 1,
				'CertificateData.user_id' => $this->_controller->Auth->user('id'),
			);

			$this->_controller->Examiner->Certificate->CertificateData->updateAll(
				$certificat_data_current,
				array('CertificateData.id' => $data['CertificateData']['id'])
			);

		}

		$certificate_data = $this->_controller->Examiner->Certificate->CertificateData->find('first',array(
			'order' => array('id DESC'),
			'conditions' => array(
				'CertificateData.certificate_id' => $data['Certificate']['id']
				)
			)		
		);

		$data['CertificateData'] = $certificate_data['CertificateData'];

		return $data;
	}

	protected function _CollectCertificateSave($data){
				
		if(isset($data['Certificate']['id']) && $data['Certificate']['id'] > 0) return $data;
		
		if(isset($data['Certificate']['id'])) $data['Certificate']['id'] = 0;
		if(empty($data['Certificate']['exam_date'])) $data['Certificate']['exam_date'] = $data['Certificate']['first_registration'];

		if ($this->_controller->Examiner->Certificate->save($data) == false){

			$message = __('An error has occurred');
			$this->_controller->Session->delete('Certificate.form');
			$this->_controller->Flash->error($message, array('key' => 'error'));

			return false;

		}

		$message = __('The qualification has been saved');
		
		$this->_controller->Session->delete('Certificate.form');
		$this->_controller->Flash->success($message, array('key' => 'success'));

		$certificate_id = $this->_controller->Examiner->Certificate->getLastInsertId();

		$this->_controller->Autorisierung->Logger($certificate_id, $data['Certificate']);

		$this->_controller->Examiner->Certificate->recursive = -1;

		$certificate = $this->_controller->Examiner->Certificate->find('first',array(
			'conditions' => array(
					'Certificate.id' => $certificate_id
				)
			)
		);

		$data['Certificate'] = $certificate['Certificate'];

		$data = $this->_UpdateCertificateTestingmethod($data);

		return $data;
	}

	protected function _CollectCertificateDataSave($data){

		if(!isset($data['Certificate']['id'])) return $data;
		if($data['Certificate']['certificate_data_active'] == 0) return $data;

		$certificate_data_option = array(
			'conditions' => array(
				'CertificateData.certificate_id' => $data['Certificate']['id'],
			),
		);

		$this->_controller->Examiner->Certificate->CertificateData->recursive = -1;
		$Check = $this->_controller->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

		if(count($Check) > 0) return $data;

		$certificate_data_data['CertificateData'] = array(
			'certificate_id' => $data['Certificate']['id'],
			'examiner_id' => $data['Certificate']['examiner_id'],
			'certified' => 0,
			'testingmethod' => $data['Certificate']['testingmethod'],
			'first_registration' => $data['Certificate']['first_registration'],
			'recertification_in_year' => $data['Certificate']['recertification_in_year'],
			'renewal_in_year' => $data['Certificate']['renewal_in_year'],
			'horizon' => $data['Certificate']['horizon'],
			'first_certification' => $data['Certificate']['first_certification'],
			'active' => 1,
			'deleted' => 0,
			'user_id' => $this->_controller->Auth->user('id'),
		);

		$this->_controller->Examiner->Certificate->CertificateData->create();
pr($certificate_data_data);
		if (!$this->_controller->Examiner->Certificate->CertificateData->save($certificate_data_data)) {

			$this->_controller->Examiner->Certificate->delete($data['Certificate']['id']);
			$this->_controller->Examiner->Certificate->delete($data['Certificate']['id']);
			$this->DeleteCertificateTestingmethod($data['Certificate']['id']);

			$message = __('Information could not be saved.');
			$this->_controller->Flash->error($message, array('key' => 'error'));
			return false;
		}

		$certificate_data_id = $this->_controller->Examiner->Certificate->CertificateData->getLastInsertId();

		
		$certificate_data_option = array(
			'conditions' => array(
				'CertificateData.id' => $certificate_data_id,
			),
		);

		$this->_controller->Examiner->Certificate->CertificateData->recursive = -1;

		$certificate_data = $this->_controller->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

		$data['CertificateData'] = $certificate_data['CertificateData'];

		return $data;
	}

	protected function _CollectCertificateDataSaveAfter($data){

		if(!isset($data['Certificate']['id'])) return $data;
		if($data['Certificate']['id'] == 0) return $data;
		if($data['Certificate']['certificate_data_active'] == 0) return $data;

		$certificate_data_data['CertificateData'] = array(
			'certificate_id' => $data['Certificate']['id'],
			'examiner_id' => $data['Certificate']['examiner_id'],
			'certified' => 0,
			'testingmethod' => $data['Certificate']['testingmethod'],
			'first_registration' => $data['Certificate']['first_registration'],
			'recertification_in_year' => $data['Certificate']['recertification_in_year'],
			'renewal_in_year' => $data['Certificate']['renewal_in_year'],
			'horizon' => $data['Certificate']['horizon'],
			'first_certification' => $data['Certificate']['first_certification'],
			'active' => 1,
			'deleted' => 0,
			'user_id' => $this->_controller->Auth->user('id'),
		);

		$this->_controller->Examiner->Certificate->CertificateData->create();

		if (!$this->_controller->Examiner->Certificate->CertificateData->save($certificate_data_data)) {

			$this->_controller->Examiner->Certificate->delete($data['Certificate']['id']);
			$this->_controller->Examiner->Certificate->delete($data['Certificate']['id']);
			$this->DeleteCertificateTestingmethod($data['Certificate']['id']);

			$message = __('Information could not be saved.');
			$this->_controller->Flash->error($message, array('key' => 'error'));
			return false;
		}

		$certificate_data_id = $this->_controller->Examiner->Certificate->CertificateData->getLastInsertId();

		
		$certificate_data_option = array(
			'conditions' => array(
				'CertificateData.id' => $certificate_data_id,
			),
		);

		$this->_controller->Examiner->Certificate->CertificateData->recursive = -1;

		$certificate_data = $this->_controller->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

		$data['CertificateData'] = $certificate_data['CertificateData'];

		return $data;
	}

	protected function _FutureCheck($data){

		$ExamDate = new DateTime($data['Certificate']['first_registration']);
		$CurrentDate = new DateTime();

		if ($ExamDate > $CurrentDate) {
			$message = __('Future events cannot be saved') . '.';
			$this->Flash->error($message, array('key' => 'error'));
			return false;
		}

		return $data;
	}

	protected function _CollectCurrentTestingmethod($data){

		// Muss angepasst werden, da nun mehrere Prüfvervahren gespeichert werden können

		$testingmethod_id = $data['Certificate']['testingmethod_id'];

		$this->_controller->Testingmethod->recursive = -1;

		$testingmethod_name = $this->_controller->Testingmethod->find('first', array(
			'conditions' => array(
				'Testingmethod.id' => $testingmethod_id
				), 
			array(
				'fields' => array('value')
				)
			)
		);

		if(count($testingmethod_name) == 0){
			$this->_controller->Flash->error(__('Information could not be saved. No valid testingmethode found.'), array('key' => 'error'));
			return false;
		}


		$testingmethod = $testingmethod_name['Testingmethod']['value'];
		$testingmethod = strtoupper($testingmethod);
		$data['Certificate']['testingmethod'] = $testingmethod;

		return $data;
	}

	protected function _CollectCurrentExaminer($data){

		$examiner_id = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass']['15']);

        $certificate_data_option = array(
            'conditions' => array(
                'Examiner.id' => $examiner_id,
            ),
        );

		$this->_controller->Examiner->recursive = -1;

        $certificate_data = $this->_controller->Examiner->find('first', $certificate_data_option);
	
		if(count($certificate_data) == 0){

			$this->_controller->Flash->error(__('Information could not be saved. No valid examiner found.'), array('key' => 'error'));
			return false;

		} 

		$data['Examiner'] = $certificate_data['Examiner'];

		$data['Certificate']['status'] = 0;
		$data['Certificate']['examiner_id'] = $certificate_data['Examiner']['id'];
		$data['Certificate']['user_id'] = $this->_controller->Auth->user('id');
		$data['Certificate']['exam_date'] = $data['Certificate']['first_registration'];

		$FirstRegistration = new DateTime($data['Certificate']['first_registration']);

		$data['Certificate']['first_registration'] = $FirstRegistration->format('Y-m-d');


		return $data;
	}

	protected function _CheckQulificationErrors(){

        // muss noch im Helper korrigiert werden
        foreach ($this->_controller->request->data['Certificate'] as $_key => $_certificate) {

            if ($_key == '0') {
            	
				foreach ($this->_controller->request->data['Certificate'][0] as $__key => $__certificate) {
            	
					$this->_controller->request->data['Certificate'][$__key] = $__certificate;

                }
            }
        }

        unset($this->_controller->request->data['Certificate'][0]);
        unset($this->_controller->request->data['Order']);

        $this->_controller->Examiner->Certificate->set($this->_controller->request->data);

        $errors = array();

        if ($this->_controller->Examiner->Certificate->validates()) {
        
		} else {
        	$errors = $this->_controller->Examiner->Certificate->validationErrors;
        }   

		return $errors;
	}

	protected function _CheckCertificateTestingmethod($data){

		$return = false;

		if(!isset($data['Testingmethod']['Testingmethod'])) $return = $data;
		if(empty($data['Testingmethod']['Testingmethod'])) $return = false;
		if(!is_array($data['Testingmethod']['Testingmethod'])) $return = false;
		if(is_countable($data['Testingmethod']['Testingmethod']) && count($data['Testingmethod']['Testingmethod']) == 0) $return = false;
		if(is_countable($data['Testingmethod']['Testingmethod']) && count($data['Testingmethod']['Testingmethod']) > 0) $return = $data;

		if($return === false) $this->_controller->Flash->error(__('Information could not be saved. Please select at least one test method'), array('key' => 'error'));

		return $return;

	}

	protected function _CheckCertificateTestingmethodExists($data,$testingmethods){

		if(!isset($data['Testingmethod']['Testingmethod'])) return false;
        if(empty($data['Testingmethod']['Testingmethod'])) return false;
        if(!is_array($data['Testingmethod']['Testingmethod'])) return false;
        if(count($data['Testingmethod']['Testingmethod']) == 0) return false;

		$examiner_id = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass']['15']);

		foreach($data['Testingmethod']['Testingmethod'] as $key => $value) {

			// das gleiche Prüfverfahren, das gleiche level
            $certificate_exist_options = array(
                'conditions' => array(
                    'Certificate.examiner_id' => $examiner_id,
                    'Certificate.level' => $data['Certificate']['level'],
                    'Certificate.testingmethod' => $value,
                    'Certificate.sector' => $data['Certificate']['sector'],
                    'Certificate.deleted' => 0,
                ),
            );

			$certificate_exist = $this->_controller->Examiner->Certificate->find('first', $certificate_exist_options);

            if (count($certificate_exist) > 0){

				$this->_controller->Flash->error(
					__('The following test method has been removed.') . ' ' . 
					__('This test method already has a qualification') . ': ' . $testingmethods[$value]
					, array('key' => 'error')
				);

				return false;

			} 

		}

		return $data;
	}

	protected function _CheckCertificateSameLevel($data,$testingmethods){

		$examiner_id = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass']['15']);

		// das gleiche Prüfverfahren, das gleiche level
		foreach($data['Testingmethod']['Testingmethod'] as $key => $value) {

			$certificate_exist_options = array(
				'conditions' => array(
					'Certificate.examiner_id' => $examiner_id,
					'Certificate.level' => $data['Certificate']['level'],
					'Certificate.testingmethod' => $value,
					'Certificate.sector' => $data['Certificate']['sector'],
					'Certificate.deleted' => 0,
				),
			);

			
			$certificate_exist = $this->_controller->Examiner->Certificate->find('first', $certificate_exist_options);
			
			if (count($certificate_exist) > 0) {

				$message = 
				__('Information for the following test method/qualification already exists', true) . ' ' . 
				$certificate_exist['Certificate']['sector'] . '/' . 
				$certificate_exist['Certificate']['certificat'] . '/' . 
				$certificate_exist['Certificate']['testingmethod'] . '-' . 
				$certificate_exist['Certificate']['level'];

				$this->Flash->error($message, array('key' => 'error'));

				return false;
			}
		}

		return $data;
	}

	protected function _CheckCertificateSameSectorTestingmethodCertificate($data,$testingmethods){

		$examiner_id = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass']['15']);

        // Wenn ein Zertifikat mit gleichem Sector/Prüfverfahren/Zertifikatsnummer existiert
    	// wird dies deaktiviert wenn dessen Level kleiner ist als das neu angelegte
		foreach($data['Testingmethod']['Testingmethod'] as $key => $value) {

			$certificate_exist_options = array(
                'fields' => array('id', 'level'),
                'order' => array('Certificate.id DESC'),
                'conditions' => array(
                    'Certificate.examiner_id' => $examiner_id,
                    'Certificate.testingmethod' => $value,
                    'Certificate.sector' => $data['Certificate']['sector'],
                    'Certificate.deleted' => 0,
                ),
            );

			$certificate_exist = $this->_controller->Examiner->Certificate->find('list', $certificate_exist_options);

			if (count($certificate_exist) > 0) {

                // Wenn eine Qualifikation im gleichen Sektor/Verfahren mit höherem oder gleichem Level
                // gefunden wird, wird der Vorgang abgebrochen
                if ($data['Certificate']['level'] <= max($certificate_exist)) {
                    $message = __('Information could not be saved, there is already a qualification with a higher level for this testingmethod.');
                    $this->_controller->Flash->error($message, array('key' => 'error'));
                    return false;
                }

                // Wenn eine Qualifikation im gleichen Sektor/Verfahren mit niedrigeren Level
                // gefunden wird, wird diese deaktivert
                if ($data['Certificate']['level'] > max($certificate_exist)) {
                    $this->_controller->Examiner->Certificate->updateAll(
                        array(
                            'Certificate.active' => 0,
                        ),
                        $certificate_exist_options['conditions']
                    );

                    $this->_controller->Examiner->Certificate->CertificateData->updateAll(
                        array(
                            'CertificateData.active' => 0,
                        ),
                        array(
                            'CertificateData.certificate_id' => array_flip($certificate_exist),
                        )
                    );

					$message  = __('The following lower level qualification has been disabled') . ': ';
					$message .= $certificate_exist['Certificate']['sector'] . '/' . $certificate_exist['Certificate']['certificat'] . '/' . $certificate_exist['Certificate']['testingmethod'] . '-' . $certificate_exist['Certificate']['level'];
	
                    $this->_controller->Flash->warning($message, array('key' => 'warning'));

                }
            }
		}
		
		return $data;
	}	

	protected function _UpdateCertificateTestingmethod($data){

        $this->DeleteCertificateTestingmethod($data['Certificate']['id']);
        
        $save = array();

        foreach($data['Testingmethod']['Testingmethod'] as $key => $value){
            $save[] = array(
                'testingmethod_id' => $value,
                'certificate_id' => $data['Certificate']['id'],
            );
        }

        $this->_controller->Examiner->Certificate->CertificatesTestingmethodes->saveMany($save, array('deep' => false));

		return $data;

    }

	// kann bei Nutzung der neuen Speicherfunktionen auch weg
	public function UpdateCertificateTestingmethod(){

        if(!isset($this->_controller->request->data['Testingmethod']['Testingmethod'])) return;
        if(empty($this->_controller->request->data['Testingmethod']['Testingmethod'])) return;
        if(!is_array($this->_controller->request->data['Testingmethod']['Testingmethod'])) return;
        if(count($this->_controller->request->data['Testingmethod']['Testingmethod']) == 0) return;

        $examiner_id = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][15]);
        $certificate_id = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][16]);

        $this->_controller->Examiner->Certificate->find('first');

        $this->DeleteCertificateTestingmethod($certificate_id);
        
        $data = array();

        foreach($this->_controller->request->data['Testingmethod']['Testingmethod'] as $key => $value){
            $data[] = array(
                'testingmethod_id' => $value,
                'certificate_id' => $certificate_id,
            );
        }

        $this->_controller->Examiner->Certificate->CertificatesTestingmethodes->saveMany($data, array('deep' => false));

    }

	public function DeleteCertificateTestingmethod($id){

		$this->_controller->Examiner->Certificate->CertificatesTestingmethodes->deleteAll(array('CertificatesTestingmethodes.certificate_id' => $id), false);

	}

	protected function _CorrectCertificateLevel() {

		$this->_controller->Examiner->recursive = -1;
		$this->_controller->Examiner->Certificate->recursive = -1;

		$Examiner = $this->_controller->Examiner->find('list');

		foreach ($Examiner as $key => $value) {

			$Certificate = $this->_controller->Examiner->Certificate->find('all',array('conditions' => array('Certificate.examiner_id' => $key)));

			if(count($Certificate) == 0) continue;

			$Sector = Hash::extract($Certificate, '{n}.Certificate.sector');
			$Testingmethod = Hash::extract($Certificate, '{n}.Certificate.testingmethod');

			$Sector = array_unique($Sector);
			$Testingmethod = array_unique($Testingmethod);

			if(count($Sector) == 0) continue;
			if(count($Testingmethod) == 0) continue;

			foreach ($Sector as $_key => $_value) {

				foreach ($Testingmethod as $k__ey => $__value) {

					$options = array(
						'conditions' =>
							array(
								'Certificate.examiner_id' => $key,
								'Certificate.sector' => $_value,
								'Certificate.testingmethod' => $__value,
							),
						'order' =>
							array('level')
					);

					$ThisCertificate = $this->_controller->Examiner->Certificate->find('all', $options);

					if(count($ThisCertificate) == 0) continue;
					if(count($ThisCertificate) == 1) continue;

					$level = 0;

					foreach ($ThisCertificate as $___key => $___value) {
						if($___value['Certificate']['level'] > $level){
							$level = $___value['Certificate']['level'];
						}
					}

					$options['conditions']['level <'] = $level;
					$ThisCertificate = $this->_controller->Examiner->Certificate->find('list', $options);

					if(count($ThisCertificate) == 0) continue;

					$this->_controller->Examiner->Certificate->CertificateData->updateAll(
    				array('CertificateData.active' => 0),
    				array('CertificateData.certificate_id' => $ThisCertificate)
					);
				}
			}
		}
	}

	protected function _AddToExaminerTable($value) {

		$Name = explode(',',$value['ImportExaminersCertdata']['Name']);

		if(isset($Name[0])) $Insert['Examiner']['name'] = trim($Name[0]);
		else $Insert['Examiner']['name'] = '';

		if(isset($Name[1])) $Insert['Examiner']['first_name'] = trim($Name[1]);
		else $Insert['Examiner']['first_name'] = '';

		$Insert['Examiner']['working_place'] = '-';
		$Insert['Examiner']['testingcomp_id'] = 1;

		$this->_controller->Examiner->create();
		$this->_controller->Examiner->save($Insert);
		$insertedId = $this->_controller->Examiner->getLastInsertId();

		$Insert = array();
		$Insert['ImportExaminersCertdata']['id'] = $value['ImportExaminersCertdata']['id'];
		$Insert['ImportExaminersCertdata']['examiner_id'] = $insertedId;
		$this->_controller->ImportExaminersCertdata->save($Insert);

	}

	protected function _AddToNewTable($value) {

		unset($value['ImportExaminer']['id']);

		$Insert['ImportExaminersCertdata'] = array();

		foreach ($value['ImportExaminer'] as $_key => $_value) {
			$Insert['ImportExaminersCertdata'][$_key] = $_value;
		}

		$this->_controller->ImportExaminersCertdata->create();

		if($this->_controller->ImportExaminersCertdata->save($Insert)) return true;
		else return false;

	}

	protected function _HashCertInfo($value) {

		$insert = array();
		$insert['ImportExaminersCertdata']['id'] = $value['ImportExaminersCertdata']['id'];

		foreach ($value['ImportExaminersCertdata'] as $_key => $_value) {

			if(strpos($_key,'Zertifikats_Nr_') === false) continue;
			if(empty($_value)) continue;
			if(strpos($_value,'Z-SC-') === false) continue;

			$key_array = explode('_',$_key);

			unset($key_array[0]);
			unset($key_array[1]);

			$key_ = 'Zertifikats_Data_' . implode('_',$key_array);
			$CertData = null;

			$CertData = $this->SectorCertCheck($_value);

			$insert['ImportExaminersCertdata'][$key_] = $CertData;

		}

		if($this->_controller->ImportExaminersCertdata->save($insert)) return true;
		else return false;

	}

	protected function _CreateTimeRanges($data,$examiner) {
//pr($examiner['ImportExaminersCertdata']['examiner_id']);

	if($data == null) return $data;
	if(count($data) == 0) return $data;

		foreach ($data as $key => $value) {
	//		pr($key);
			foreach ($value as $_key => $_value) {

				$Insert = array();
				$date_now = new DateTime();
				$date_start = new DateTime($_value['validity_start']);
				$date_end = new DateTime($_value['validity_end']);
				$interval_now = $date_now->diff($date_end);
				$interval = $date_start->diff($date_end);
//				pr($interval_now);
//				pr($interval->y);
//				var_dump($_value['categories']);
				if(count($_value['categories']) != 1) return $data;

				$Insert['examiner_id'] = $examiner['ImportExaminersCertdata']['examiner_id'];
				$Insert['sector'] = $_value['categories'][0]['sector'];
				$Insert['certificat'] = $_value['cert_number'];
				$Insert['third_part'] = $this->_controller->Qualification->ThirdPart;
				$Insert['testingmethod'] = Inflector::camelize(strtolower($_value['categories'][0]['method']));
				$Insert['exam_date'] = $_value['validity_start'];
				$Insert['level'] = $_value['categories'][0]['level'];
				$Insert['first_registration'] = $_value['validity_start'];
				$Insert['first_certification'] = $_value['validity_start'];
				$Insert['recertification_in_year'] = $interval->y;
				$Insert['renewal_in_year'] = $interval->y;
				$Insert['horizon'] = 6;
				$Insert['supervisor'] = 0;
				$Insert['deleted'] = 0;
				$Insert['user_id'] = 1;
				$Insert['active'] = 1;
				$Insert['certificate_data_active'] = 1;
				$Insert['rules'] = $_value['categories'][0]['rules'];
				$Insert['description'] = $_value['categories'][0]['description'];

				$this->_controller->Examiner->Certificate->create();
				$this->_controller->Examiner->Certificate->save($Insert);

				$insertedId = $this->_controller->Examiner->Certificate->getLastInsertId();

				$Insert = array();

				$Insert['certificate_id'] = $insertedId;
				$Insert['examiner_id'] = $examiner['ImportExaminersCertdata']['examiner_id'];
				$Insert['testingmethod'] = Inflector::camelize(strtolower($_value['categories'][0]['method']));
				$Insert['first_certification'] = 0;
				$Insert['certified'] = 1;
				$Insert['recertification_in_year'] = $interval->y;
				$Insert['renewal_in_year'] = $interval->y;
				$Insert['horizon'] = 6;
				$Insert['first_registration'] = $_value['validity_start'];
				$Insert['certified_date'] = $_value['validity_start'];
				$Insert['apply_for_recertification'] = 0;
				$Insert['deleted'] = 0;
				$Insert['active'] = 1;
				$Insert['user_id'] = 1;

				$this->_controller->Examiner->Certificate->CertificateData->create();
				$this->_controller->Examiner->Certificate->CertificateData->save($Insert);
			}
		}
	}

	protected function _SeperateCertInfos($value) {

		$output = array();

		$this_value = array();

		foreach ($value['ImportExaminersCertdata'] as $_key => $_value) {


	//		if(is_int(strpos($_key,'Zertifikats_Nr_'))) pr($_key);

			if(strpos($_key,'Zertifikats_Data_') === false) continue;
			if(empty($_value)) continue;

			$part = json_decode($_value,true);

			$cert_number = array_unique(Hash::extract($part, '{n}.cert_number'));
			$method = array_unique(Hash::extract($part, '{n}.categories.{n}.method'));

			if(!isset($cert_number[0])) continue;

			$this_value[$cert_number[0]] = array_flip($method);

		}

		unset($part);

		if(count($this_value) == 0) return;

		$this_value = array();

		foreach ($value['ImportExaminersCertdata'] as $_key => $_value) {
			if(strpos($_key,'Zertifikats_Data_') === false) continue;
			if(empty($_value)) continue;

			$part = json_decode($_value,true);

			if(!is_array($part)) continue;
			if(count($part) == 0) continue;

			foreach ($part as $_key => $_value) {
				foreach ($_value['categories'] as $__key => $__value) {
					$this_value[$__value['method']][$_value['validity_start']] = $_value;
				}
			}
		}

		return $this_value;

	}

public function FilterExaminerDataForPDF($Examinerdata) {

		if($this->_controller->Session->check('PrintOverview') === true){
			$SessionData = $this->_controller->Session->consume('PrintOverview');
			if(isset($SessionData['Examiner'])) $this->_controller->request->data['Examiner'] = $SessionData['Examiner'];
		}

		if(!isset($this->_controller->request->data['Examiner']['working_place'])) return $Examinerdata;
		if(!is_array($this->_controller->request->data['Examiner']['working_place'])) return $Examinerdata;
		if(count($this->_controller->request->data['Examiner']['working_place']) == 0) return $Examinerdata;

		$WorkingPlaceRequest = $this->_controller->request->data['Examiner']['working_place'];
	//	$WorkingPlaceRequest = array_flip($WorkingPlaceRequest);
		$WorkingPlace['Examiner']['working_place'] = Hash::extract($Examinerdata, '{n}.working_place');
		$WorkingPlace['Examiner']['working_place'] = array_unique($WorkingPlace['Examiner']['working_place']);

		sort($WorkingPlace['Examiner']['working_place']);
		natsort($WorkingPlace['Examiner']['working_place']);

		foreach ($WorkingPlaceRequest as $key => $value) {

			if(!isset($WorkingPlace['Examiner']['working_place'][$key])){
					unset($WorkingPlaceRequest[$key]);
					continue;
			}

			$WorkingPlaceRequest[$key] = $WorkingPlace['Examiner']['working_place'][$key];
		}

		foreach ($Examinerdata as $key => $value) {

			if(array_search($value['working_place'], $WorkingPlaceRequest) === false) unset($Examinerdata[$key]);

		}

		return $Examinerdata;
	}



	public function CollectEyeCheckInfos($value,$Examinerdata) {

		$Eyechecks = $this->Eyechecks($value);

		if(!isset($Eyechecks['EyecheckData'])) {

			$Examinerdata['eyecheck_date_color'] = false;
			$Examinerdata['eyecheck_date'] = '';

			return $Examinerdata;
		}

		$Examinerdata['eyecheck_class'] = $Eyechecks['EyecheckData']['valid_class'];

		switch ($Examinerdata['eyecheck_class']) {
			case 'certification_not_valid':
				$Examinerdata['eyecheck_date_color'][0] = 255;
				$Examinerdata['eyecheck_date_color'][1] = 0;
				$Examinerdata['eyecheck_date_color'][2] = 0;
				break;
			case 'certification_valid':
				$Examinerdata['eyecheck_date_color'][0] = 3;
				$Examinerdata['eyecheck_date_color'][1] = 128;
				$Examinerdata['eyecheck_date_color'][2] = 0;
				break;
			case 'certification_not_valid_soon':
				$Examinerdata['eyecheck_date_color'][0] = 255;
				$Examinerdata['eyecheck_date_color'][1] = 126;
				$Examinerdata['eyecheck_date_color'][2] = 0;
				break;

			default:
			$Examinerdata['eyecheck_date_color'][0] = 0;
			$Examinerdata['eyecheck_date_color'][1] = 0;
			$Examinerdata['eyecheck_date_color'][2] = 0;
				break;
		}
		$Examinerdata['eyecheck_date'] = $Eyechecks['EyecheckData']['next_certification'];

		return $Examinerdata;
	}

	public function CollectCertificateInfos($value) {

		$examinerid = $value['Examiner']['id'];
		$Examinerdata = $value['Examiner'];

		$HintTyps = array('future','futurenow','errors','warnings','hints');

		$summary = $this->CertificateSummary($this->CertificatesSectors($value), array('main'=>'Certificate','sub'=>'CertificateData'));

		$Examinerdata['qualifications_future'] = '';
		$Examinerdata['qualifications_futurenow'] = '';
		$Examinerdata['qualifications_errors'] = '';
		$Examinerdata['qualifications_warnings'] = '';
		$Examinerdata['qualifications_hints'] = '';

		$uberwachungen = '';

//		pr($summary['summary']['future']);
//		pr($summary['summary']['futurenow']);

		$Examinerdata = $this->_CollectHintTyps($Examinerdata,$HintTyps,$summary);
/*
		if(count($summary['summary']['errors']) > 0){

			$uberwachungen_typ = '';

			foreach($summary['summary']['errors'] as $key => $value){
//				$uberwachungen_typ .= $summary['qualifications'][$value['info']['certificate_id']]['certificat'] . ' ';
				$uberwachungen_typ .= $summary['qualifications'][$value['info']['certificate_id']]['testingmethod'] . '/';
				$uberwachungen_typ .= $summary['qualifications'][$value['info']['certificate_id']]['level'] . ' -> ';
				$uberwachungen_typ .= $value['info']['next_certification'] . "\n";
			}

			$Examinerdata['qualifications_errors'] = $uberwachungen_typ;
		}
*/
/*
		pr($summary['summary']['warnings']);
		pr($summary['summary']['warnings']);
		pr($summary['summary']['hints']);

		if (!empty($summary)) {
				foreach ($summary['qualifications'] as $key2 => $_summary) {
						if (!isset($_summary['certificat'])) continue;

						$uberwachungen .= $_summary['certificat'] .' '. $_summary['testingmethod'].' '.__('level').' '.$_summary['level']."\n";
				}

				$Examinerdata['qualifications'] = $uberwachungen;
		} else {
				$Examinerdata['qualifications'] = '';
		}
*/
		$Examinerdata['monitorings'] = '';
		$monsummary = $this->ExaminerMonitoringSummary($value, array('top'=>'Examiner','main'=>'ExaminerMonitoring','sub'=>'ExaminerMonitoringData'));
		$Examinerdata = $this->CollectMonitoringInfos($Examinerdata,$HintTyps,$monsummary);
		$Examinerdata = $this->CollectEyeCheckInfos($value,$Examinerdata);

		return $Examinerdata;
	}

	protected function _CollectHintTyps($Examinerdata,$HintTyps,$summary) {

		foreach ($HintTyps as $key => $value) {

			if(!isset($summary['summary'][$value])) continue;
			if(count($summary['summary'][$value]) == 0) continue;

			$uberwachungen_typ = '';

			foreach ($summary['summary'][$value] as $_key => $_value) {

				//				$uberwachungen_typ .= $summary['qualifications'][$value['info']['certificate_id']]['certificat'] . ' ';
				$uberwachungen_typ .= $summary['qualifications'][$_value['info']['certificate_id']]['testingmethod'] . '/';
				$uberwachungen_typ .= $summary['qualifications'][$_value['info']['certificate_id']]['level'] . ' -> ';
				$uberwachungen_typ .= $_value['info']['next_certification'] . "\n";

			}

			$Examinerdata['qualifications_' . $value] = trim($uberwachungen_typ);

		}

		return $Examinerdata;
	}

	protected function CollectMonitoringInfos($Examinerdata,$HintTyps,$summary) {

		if(!isset($summary['summary'])) return $Examinerdata;

		$uberwachungen_typ = '';

		if(is_array($summary['summary'])){

			foreach($summary['summary']['monitoring'] as $_key => $_value) {
				foreach ($_value as $__key => $__value) {
					foreach ($__value as $___key => $___value) {
						$uberwachungen_typ .= $___value['info']['certificat'] . '->'.$___value['info']['next_certification']."\n";
					}
				}
			}
		}

		$Examinerdata['monitorings'] = trim($uberwachungen_typ);
		return $Examinerdata;

	}


	public function ExaminerDataForPDF($Examiner) {

		$adress = null;
		$testingcomp = null;
		$contact = null;
		$output = array();

		if (!empty($Examiner['Testingcomp']['firmenname'])) {
				$testingcomp .= $Examiner['Testingcomp']['firmenname'] . "\n";
		}
		if (!empty($Examiner['Testingcomp']['firmenzusatz'])) {
				$testingcomp .= $Examiner['Testingcomp']['firmenzusatz'] . "\n";
		}
		if (!empty($Examiner['Testingcomp']['strasse'])) {
				$testingcomp .= $Examiner['Testingcomp']['strasse'] . "\n";
		}
		if (!empty($Examiner['Testingcomp']['plz'])) {
				$testingcomp .= $Examiner['Testingcomp']['plz'] . " ";
		}
		if (!empty($Examiner['Testingcomp']['ort'])) {
				$testingcomp .= $Examiner['Testingcomp']['ort'] . "\n";
		}
		if (!empty($Examiner['Testingcomp']['telefon'])) {
				$testingcomp .= __('Tel', true) . ': ' . $Examiner['Testingcomp']['telefon'] . "\n";
		}
		if (!empty($Examiner['Testingcomp']['telefax'])) {
				$testingcomp .= __('Fax', true) . ': ' . $Examiner['Testingcomp']['telefax'] . "\n";
		}
		if (!empty($Examiner['Testingcomp']['internet'])) {
				$testingcomp .= $Examiner['Testingcomp']['internet'] . "\n";
		}
		if (!empty($Examiner['Testingcomp']['email'])) {
				$testingcomp .= $Examiner['Testingcomp']['email'] . "\n";
		}

		$Examiner['Testingcomp']['summary'] = $testingcomp;

		$CurrentTestingcomp = $this->_controller->Auth->user('Testingcomp');

		if (!empty($CurrentTestingcomp['firmenname'])) {
				$adress .= $this->_controller->Auth->user('Testingcomp.firmenname') . "\n";
		}
		if (!empty($CurrentTestingcomp['firmenzusatz'])) {
				$adress .= $this->_controller->Auth->user('Testingcomp.firmenzusatz') . "\n";
		}
		if (!empty($CurrentTestingcomp['strasse'])) {
				$adress .= $this->_controller->Auth->user('Testingcomp.strasse') . "\n";
		}
		if (!empty($CurrentTestingcomp['plz'])) {
				$adress .= $this->_controller->Auth->user('Testingcomp.plz') . " ";
		}
		if (!empty($CurrentTestingcomp['ort'])) {
				$adress .= $this->_controller->Auth->user('Testingcomp.ort') . " ";
		}

		if (!empty($CurrentTestingcomp['telefon'])) {
				$contact .= __('Tel', true) . ': ' . $this->_controller->Auth->user('Testingcomp.telefon') . "\n";
		}
		if (!empty($CurrentTestingcomp['telefax'])) {
				$contact .= __('Fax', true) . ': ' . $this->_controller->Auth->user('Testingcomp.telefax') . "\n";
		}
		if (!empty($CurrentTestingcomp['internet'])) {
				$contact .= $this->_controller->Auth->user('Testingcomp.internet') . "\n";
		}
		if (!empty($CurrentTestingcomp['email'])) {
				$contact .= $this->_controller->Auth->user('Testingcomp.email') . " ";
		}

		$output['contact'] = $contact;
		$output['testingcomp'] = $testingcomp;
		$output['adress'] = $adress;

		return $output;

	}

	public function dateDiff($time1, $time2, $precision = 6) {

 	   // If not numeric then convert texts to unix timestamps
 	   if (!is_int($time1)) {
    	  $time1 = strtotime($time1);
    	}
    	if (!is_int($time2)) {
    	  $time2 = strtotime($time2);
    	}

		$timestamp1 = $time1;
		$timestamp2 = $time2;

    	// If time1 is bigger than time2
    	// Then swap time1 and time2
    	if ($time1 > $time2) {
			$timestamp_diff = $timestamp1 - $timestamp2;
    	  $ttime = $time1;
    	  $time1 = $time2;
    	  $time2 = $ttime;
    	}
		else{
			$timestamp_diff = $timestamp2 - $timestamp1;
		}

    // Set up intervals and diffs arrays
    $intervals = array('year','month','day','hour','minute','second');
    $diffs = array();

    // Loop thru all intervals
    foreach ($intervals as $interval) {
      // Create temp time from time1 and interval
      $ttime = strtotime('+1 ' . $interval, $time1);
      // Set initial values
      $add = 1;
      $looped = 0;
      // Loop until temp time is smaller than time2
      while ($time2 >= $ttime) {
        // Create new temp time from time1 and interval
        $add++;
        $ttime = strtotime("+" . $add . " " . $interval, $time1);
        $looped++;
      }

      $time1 = strtotime("+" . $looped . " " . $interval, $time1);
      $diffs[$interval] = $looped;
    }

    $count = 0;
    $times = array();
    // Loop thru all diffs
    foreach ($diffs as $interval => $value) {
      // Break if we have needed precission
      if ($count >= $precision) {
        break;
      }
      // Add value and interval
      // if value is bigger than 0
      if ($value > 0) {
        // Add s if value is not 1
        if ($value != 1) {
          $interval .= "s";
        }
        // Add value and interval to times array
        $times[] = $value . " " . __($interval);
        $count++;
      }
    }

	$desciption = array(0 => __('years',true),1 => __('months',true), 2 => __('days',true));

	$time = array();

	if(count($times) > 0){
		foreach($times as $_key => $_times){
			if (isset($desciption[$_key])){
				$time[$desciption[$_key]] = intval($_times);
			}
		}
	}

	$output['timestamp']['start'] = $timestamp1;
	$output['timestamp']['end'] = $timestamp2;
	$output['timestamp']['diff'] = $timestamp_diff;
	$output['array'] = $time;
	$output['string'] = implode(", ", $times);
//	return $output;
	return implode(", ", $times);
  }

	public function CertificatesSectors($examiner) {

		if(count($examiner) == 0) return array();

		$examiner_id = $examiner['Examiner']['id'];

		$filter_certifcation = array();

		// wenn die Funktion über die Certifcation-Aktion aufgerufen wird
		// werden nur zertifizierte Qualifikationen angezeigt
		if($this->_controller->request->params['action'] == 'certificates'){
			$filter_certifcation = array('certificate_data_active' => 1);
		}

		$this->_controller->Examiner->Certificate->Testingmethod->recursive = -1;

		$certificate_options = array(
			'conditions' => array(
				'Certificate.examiner_id' => $examiner_id,
				'Certificate.deleted' => 0,
				$filter_certifcation
				),
			'fields' => array(
				'Certificate.sector'
				),
			'group' => array(
				'Certificate.sector'
				)
			);

		$certificate = $this->_controller->Examiner->Certificate->find('list',$certificate_options);
		
		$certificates = array();
		$certificatesbyid = array();

		foreach($certificate as $_key => $_certificate){

			$certificate_options = array(
								'order' => array('testingmethod ASC','level DESC'),
								'conditions' => array(
										'Certificate.examiner_id' => $examiner_id,
										'Certificate.sector' => $_certificate,
										'Certificate.deleted' => 0,
										$filter_certifcation
									),
								);

			$data = $this->_controller->Examiner->Certificate->find('all',$certificate_options);

			if(count($data) == 0) continue;

			foreach($data as $_key => $_data){
				$certificatesbyid[$_data['Certificate']['id']] = $_data;
			}

			$certificates[$_certificate] = $data;

			foreach($data as $__key => $__data){

				$certificate_date_options = array(
					'conditions' => array(
						'CertificateData.certificate_id' => $__data['Certificate']['id'],
						'CertificateData.examiner_id' => $__data['Certificate']['examiner_id'],
						'CertificateData.deleted' => 0,
						),
					'order' => array('CertificateData.id DESC')
				);

				$certificate_date = $this->_controller->Examiner->Certificate->CertificateData->find('first',$certificate_date_options);

				if(count($certificate_date) == 0){
					$certificates[$_certificate][$__key]['CertificateData'] = array('next_certification' => __('no entry found',true),'next_certification_horizon' => __('no entry found',true));
				}

				if(count($certificate_date) > 0){

					$certificates[$_certificate][$__key]['CertificateData'] = $certificate_date['CertificateData'];

					// Test ob erneuert oder rezertifiziert wird
					$certificates[$_certificate][$__key] = $this->IsRenewal($certificates[$_certificate][$__key]);

					$certificates[$_certificate][$__key]['Examiner'] = $examiner['Examiner'];

					$certificates[$_certificate][$__key] = $this->TimeHorizons($certificates[$_certificate][$__key],array('main'=>'Certificate','sub'=>'CertificateData'));
				}

				// Wenn kein Zertifikat erteilt wird
				if($certificates[$_certificate][$__key]['Certificate']['certificate_data_active'] == 0){
					$certificates[$_certificate][$__key]['CertificateData']['valid'] = 0;
					$certificates[$_certificate][$__key]['CertificateData']['valid_class'] = 'certification_not_planned';
				}

				if(!isset($certificates[$_certificate][$__key]['CertificateData']['id'])){

					if(!isset($__data['CertificatesTestingmethodes'])) continue;

					if(count($__data['CertificatesTestingmethodes']) == 0) continue;

					foreach($__data['CertificatesTestingmethodes'] as $___key => $___value){
	
						$Testingmethod = $this->_controller->Examiner->Certificate->Testingmethod->find('first',array(
							'conditions' => array(
								'Testingmethod.id'=> $___value['testingmethod_id']
								)
							)
						);
	
						$certificates[$_certificate][$__key]['CertificatesTestingmethodes'][$___key]['name'] = $Testingmethod['Testingmethod']['name'];
						$certificates[$_certificate][$__key]['CertificatesTestingmethodes'][$___key]['verfahren'] = $Testingmethod['Testingmethod']['verfahren'];
					}	

					continue;
						
				}

				// Wenn das Zertifikat deaktiviert wurde
				if($certificates[$_certificate][$__key]['Certificate']['certificate_data_active'] == 1 && $certificates[$_certificate][$__key]['CertificateData']['active'] == 0){
					$certificates[$_certificate][$__key]['CertificateData']['valid'] = 0;
					$certificates[$_certificate][$__key]['CertificateData']['valid_class'] = 'certification_deactive';
				}
					
				if(!isset($__data['CertificatesTestingmethodes'])) continue;
				if(count($__data['CertificatesTestingmethodes']) == 0) continue;

				foreach($__data['CertificatesTestingmethodes'] as $___key => $___value){

					$Testingmethod = $this->_controller->Examiner->Certificate->Testingmethod->find('first',array(
						'conditions' => array(
							'Testingmethod.id'=> $___value['testingmethod_id']
							)
						)
					);

					$certificates[$_certificate][$__key]['CertificatesTestingmethodes'][$___key]['name'] = $Testingmethod['Testingmethod']['name'];
					$certificates[$_certificate][$__key]['CertificatesTestingmethodes'][$___key]['verfahren'] = $Testingmethod['Testingmethod']['verfahren'];
				}
				
			}
		}

		$certificates = $this->_CheckForHigherCertificates($certificates);
		$certificates = $this->_CheckForCertificatePlanned($certificates);

		return $certificates;
	}

	protected function _CheckForCertificatePlanned($data){

		foreach ($data as $key => $value) {
    		foreach ($value as $_key => $_value) {

				if($_value['Certificate']['certificate_data_active'] == 0){
					$data[$key][$_key]['CertificateStatus']['certification_planned']['status'] = 0;
				}
				if($_value['Certificate']['certificate_data_active'] == 1){
					$data[$key][$_key]['CertificateStatus']['certification_planned']['status'] = 1;
				}
			}
		}

		return $data;
	}

	protected function _CheckForHigherCertificates($data){

		foreach ($data as $key => $value) {		
		
			foreach($value as $_key => $_value) {

				$data[$key][$_key]['CertificateStatus']['has_higher_certificate']['status'] = 0;
				$data[$key][$_key]['CertificateStatus']['has_higher_certificate']['data'] = array();

				if($_value['Certificate']['active'] == 1) continue;

				$options = array(
					'conditions' => array(
						'Certificate.id !=' => $_value['Certificate']['id'],
						'Certificate.level >' => $_value['Certificate']['level'],
						'Certificate.examiner_id' => $_value['Certificate']['examiner_id'],
						'Certificate.sector' => $_value['Certificate']['sector'],
						'Certificate.testingmethod' => $_value['Certificate']['testingmethod'],
						'Certificate.deleted' => 0,
						'Certificate.active' => 1,
					)
				);

				$Certificate = $this->_controller->Examiner->Certificate->find('first',$options);

				if(count($Certificate) == 0) continue;

				$data[$key][$_key]['CertificateStatus']['has_higher_certificate']['status'] = 1;
				$data[$key][$_key]['CertificateStatus']['has_higher_certificate']['data'] = $Certificate;


			}
		
		}	

		return $data;
	}

public function WelderCertificatesSectors($welder) {

		$welder_id = $welder['Welder']['id'];

		$filter_certifcation = array();

		// wenn die Funktion über die Certifcation-Aktion aufgerufen wird
		// werden nur zertifizierte Qualifikationen angezeigt
		if($this->_controller->request->params['action'] == 'certificates'){
			$filter_certifcation = array('certificate_data_active' => 1);
		}

		$certificate_options = array(
								'conditions' => array(
										'WelderCertificate.welder_id' => $welder_id,
										'WelderCertificate.deleted' => 0,
//										'Certificate.active' => 1,
										$filter_certifcation
									),
								'fields' => array(
										'WelderCertificate.sector'
									),
								'group' => array(
										'WelderCertificate.sector'
									)
								);

		$certificate = $this->_controller->Welder->WelderCertificate->find('list',$certificate_options);

		$certificates = array();
		$certificatesbyid = array();

		foreach($certificate as $_key => $_certificate){

			$certificate_options = array(
								'order' => array('weldingmethod ASC','level DESC'),
								'conditions' => array(
										'WelderCertificate.welder_id' => $welder_id,
										'WelderCertificate.sector' => $_certificate,
										'WelderCertificate.deleted' => 0,
//										'Certificate.active' => 1,
										$filter_certifcation
									),
								);

			$data = $this->_controller->Welder->WelderCertificate->find('all',$certificate_options);

			if(count($data) > 0){

				foreach($data as $_key => $_data){
					$certificatesbyid[$_data['WelderCertificate']['id']] = $_data;
				}

				$certificates[$_certificate] = $data;

				foreach($data as $__key => $__data){
					$certificate_date_options = array(
												'conditions' => array(
													'WelderCertificateData.certificate_id' => $__data['WelderCertificate']['id'],
													'WelderCertificateData.welder_id' => $__data['WelderCertificate']['welder_id'],
													'WelderCertificateData.deleted' => 0,
//													'CertificateData.active' => 1
												),
												'order' => array('WelderCertificateData.id DESC')
											);
					$certificate_date = $this->_controller->Welder->WelderCertificate->WelderCertificateData->find('first',$certificate_date_options);
//pr($certificate_date['Certificate']['active']);
					if(count($certificate_date) == 0){
						$certificates[$_certificate][$__key]['WelderCertificateData'] = array('next_certification' => __('no entry found',true),'next_certification_horizon' => __('no entry found',true));
					}

					if(count($certificate_date) > 0){

					$certificates[$_certificate][$__key]['WelderCertificateData'] = $certificate_date['WelderCertificateData'];

					// Test ob erneuert oder rezertifiziert wird
					$certificates[$_certificate][$__key] = $this->_controller->Qualification->WelderIsRenewal($certificates[$_certificate][$__key]);

					$certificates[$_certificate][$__key]['Welder'] = $welder['Welder'];

					$certificates[$_certificate][$__key] = $this->_controller->Qualification->WelderTimeHorizons($certificates[$_certificate][$__key],array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'),$certificates[$_certificate][$__key]['WelderCertificateData']['period']);

					}

					// Wenn kein Zertifikat erteilt wird
					if($certificates[$_certificate][$__key]['WelderCertificate']['certificate_data_active'] == 0){
						$certificates[$_certificate][$__key]['WelderCertificateData']['valid'] = 0;
						$certificates[$_certificate][$__key]['WelderCertificateData']['valid_class'] = 'certification_not_planned';
					}

					// Wenn das Zertifikat deaktiviert wurde
					if($certificates[$_certificate][$__key]['WelderCertificate']['certificate_data_active'] == 1 && $certificates[$_certificate][$__key]['WelderCertificateData']['active'] == 0){
						$certificates[$_certificate][$__key]['WelderCertificateData']['valid'] = 0;
						$certificates[$_certificate][$__key]['WelderCertificateData']['valid_class'] = 'certification_deactive';
					}
				}
			}
		}
		return $certificates;
	}

	public function IsRenewal($certificate) {
		// Erneuerungen gibt es seit 10/2022 nicht mehr
		//$certified_differentiation = $certificate['CertificateData']['certified_differentiation'];

		/*switch ($certified_differentiation) {
	    case 0:
				$certificate['CertificateData']['renewal'] = false;
			break;
			case 1:
				$certificate['CertificateData']['renewal'] = false;
				break;
		}*/
		$certificate['CertificateData']['renewal'] = false;


		return $certificate;
	}

  public function WelderIsRenewal($certificate) {
		// Test ob Erneuert oder rezertifiziert wird
		if($certificate['WelderCertificateData']['renewal_in_year'] > 0){

			$count = $this->_controller->Welder->WelderCertificate->WelderCertificateData->find('count',array(
					'conditions' => array(
						'WelderCertificateData.certificate_id' => $certificate['WelderCertificateData']['certificate_id'],
						'WelderCertificateData.deleted' => 0
						)
					)
				);

			$rest = $count % 2;

			if($rest == 0){
				$certificate['CertificateData']['period'] = $certificate['WelderCertificate']['recertification_in_year'] - $certificate['WelderCertificate']['renewal_in_year'];
				$certificate['CertificateData']['renewal'] = false;
			}
			else {
				$certificate['WelderCertificateData']['period'] = $certificate['WelderCertificate']['renewal_in_year'];
				$certificate['WelderCertificateData']['renewal'] = true;
			}
		}
		else {
			$certificate['WelderCertificateData']['period'] = $certificate['WelderCertificate']['recertification_in_year'];
		}
		return $certificate;
	}

	public function DeviceCertification($data,$model) {

		if(!isset($data[$model['sub']])) return $data;

		if($data[$model['sub']]['certified'] == 0 && $data[$model['sub']]['active'] == 0){

			$data[$model['sub']]['valid'] = 0;
			$data[$model['sub']]['valid_class'] = 'certification_deactive';
			$data[$model['sub']]['certification_requested'] = $data[$model['main']]['certificat'] . ' ' . __('has not been performed',true);
			$data[$model['sub']]['certified_file'] = '';
			$data[$model['sub']]['next_certification_date'] = null;
			$data[$model['sub']]['certification_requested_class'] = null;
			$data[$model['sub']]['next_certification'] = date('Y-m-d',time());
			$data[$model['sub']]['time_to_next_certification'] = $data[$model['main']]['certificat'] . ' ' . __('is not valid',true);

			if($data[$model['main']]['file'] == 1){
				$data[$model['sub']]['certified_file_error'] = __('No file available',true);
			}

			// kommt später weg
			return $data;
		}

		if($data[$model['sub']]['certified'] == 1 && $data[$model['sub']]['active'] == 1){

			if($data[$model['sub']]['renewal_in_year'] == 0){
				$data = $this->_controller->Qualification->TimeHorizonsForDevices($data,array('main'=>$model['main'],'sub'=>$model['sub']),$data[$model['sub']]['recertification_in_year']);

			}

//			$data[$model['sub']]['valid'] = 1;
//			$data[$model['sub']]['valid_class'] = 'certification_valid';
			$data[$model['sub']]['certification_requested_class'] = null;
//			$data[$model['sub']]['certification_requested'] = $data[$model['main']]['certificat'] . ' ' . __('is valid',true);

			if($data[$model['main']]['file'] == 1 && $data[$model['sub']]['certified_file'] == ''){
				$data[$model['sub']]['certified_file_error'] = __('Certificate file not available',true);
				$data[$model['sub']]['valid_class'] = 'certification_not_valid';
				if($data[$model['sub']]['certification_requested'] != ''){
				 $data[$model['sub']]['certification_requested'] = $data[$model['sub']]['certification_requested'] . ', ' . __('Certificate file not available',true);
				}
				else {
				 $data[$model['sub']]['certification_requested'] = __('Certificate file not available',true);
				}
			}
		}
		if($data[$model['sub']]['certified'] == 1 && $data[$model['sub']]['active'] == 0){
				$data[$model['sub']]['valid_class'] = 'certification_deactive';
				$data[$model['sub']]['time_to_next_certification'] = __('This monitoring has been disabled.',true);
		}
		if($data[$model['sub']]['certified'] == 2 && $data[$model['sub']]['active'] == 1){
				$data[$model['sub']]['valid_class'] = 'certification_not_valid';
				$data[$model['sub']]['time_to_next_certification'] = __('This monitoring was switched to invalid.',true);
		}

		return $data;
	}

	public function Eyechecks($examiner) {

		$examiner_id = $examiner['Examiner']['id'];

		$certificates = array();
		$certificatesbyid = array();

		$certificate_options = array(
							'order' => array('EyecheckData.id DESC'),
							'conditions' => array(
									'EyecheckData.examiner_id' => $examiner_id,
									'EyecheckData.deleted' => 0,
//									'EyecheckData.active' => 1,
								),
							);

		$data = $this->_controller->Examiner->Eyecheck->EyecheckData->find('first',$certificate_options);

		if(count($data) > 0){

			$data = $this->_controller->Qualification->TimeHorizons($data,array('main'=>'Eyecheck','sub'=>'EyecheckData'));

			// Wenn das Zertifikat deaktiviert wurde
			if($data['EyecheckData']['active'] == 0){
				$data['EyecheckData']['valid'] = 0;
				$data['EyecheckData']['valid_class'] = 'certification_deactive';
			}
		}
		return $data;
	}

        public function WelderEyechecks($welder) {

		$welder_id = $welder['Welder']['id'];

		$certificates = array();
		$certificatesbyid = array();

		$certificate_options = array(
							'order' => array('WelderEyecheckData.id DESC'),
							'conditions' => array(
									'WelderEyecheckData.welder_id' => $welder_id,
									'WelderEyecheckData.deleted' => 0,
//									'EyecheckData.active' => 1,
								),
							);

		$data = $this->_controller->Welder->WelderEyecheck->WelderEyecheckData->find('first',$certificate_options);

		if(count($data) > 0){

			$data = $this->_controller->Qualification->WelderTimeHorizons($data,array('main'=>'WelderEyecheck','sub'=>'WelderEyecheckData'),$data['WelderEyecheckData']['recertification_in_year']);

			// Wenn das Zertifikat deaktiviert wurde
			if($data['WelderEyecheckData']['active'] == 0){
				$data['WelderEyecheckData']['valid'] = 0;
				$data['WelderEyecheckData']['valid_class'] = 'certification_deactive';
			}
		}
		return $data;
	}
public function TimeHorizonsForDevices($_data,$model/*, $period*/) {
		//	$_data[$model['sub']]['period'] = $period;

			// Testen ob der Zeitpunkt zur ersten Zertifizierung erreicht wurde
			$firstCertification = date('Y-m-d', strtotime('+' . $_data[$model['sub']]['first_certification'] . ' months', strtotime($_data[$model['sub']]['first_registration'])));

			if($firstCertification > date('Y-m-d',time())){

				$_data[$model['sub']]['first_certification_date'] = $firstCertification;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = $firstCertification;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->_controller->Qualification->dateDiff($firstCertification,date('Y-m-d',time()));
				$_data[$model['sub']]['certification_requested'] = __('The time for verification will be reached in:',true).' '.$_data[$model['sub']]['time_to_first_certification_date'];
				$_data[$model['sub']]['next_certification'] = $firstCertification;
				$_data[$model['sub']]['next_certification_date'] = __('next verification',true).' '.$firstCertification;
				$_data[$model['sub']]['valid_class'] = 'first_certification_valid_soon';
				$_data[$model['sub']]['valid'] = 0;

				return $_data;
			}

			if($firstCertification <= date('Y-m-d',time()) && $_data[$model['sub']]['certified_date'] == NULL && $_data[$model['sub']]['certified'] == 0){

				$_data[$model['sub']]['first_certification_date'] = $firstCertification;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = $firstCertification;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->_controller->Qualification->dateDiff($firstCertification,date('Y-m-d',time()));
				$_data[$model['sub']]['certification_requested'] = __('The time for the verification was reached.',true);
				$_data[$model['sub']]['next_certification'] = $firstCertification;
				$_data[$model['sub']]['next_certification_date'] = __('next verification',true).' '.$firstCertification;
				$_data[$model['sub']]['valid_class'] = 'certification_time_reached';
				$_data[$model['sub']]['valid'] = 0;

				return $_data;
			}

			if($_data[$model['sub']]['certified_date'] == NULL && $_data[$model['sub']]['certified'] > 0){

				$_data[$model['sub']]['first_certification_date'] = null;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = null;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = null;
				$_data[$model['sub']]['certification_requested'] = __('This verification is valide but there is no verification date. Please open the verification and enter a valid date.',true);
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['valid_class'] = 'certification_no_date';
				$_data[$model['sub']]['valid'] = 0;
				return $_data;
			}

			// Die Spalte recertification_in_year soll decimal sein
			// dadurch sind Kommawerte zugelassen, damit strtotime funktioniert
			// werden die Tage ausgerechnet weil strtotime nur Int akzeptiert
			//$period = floor($_data[$model['sub']]['period'] * 365);

			//$nextCertification = date('Y-m-d',strtotime('+' . $period . ' days', strtotime($_data[$model['sub']]['certified_date'])));
			$nextCertification = $_data[$model['sub']]['expiration_date'];


			$nextCertificationHorizon = date('Y-m-d', strtotime('-' . $_data[$model['sub']]['horizon'] . ' months', strtotime($nextCertification)));

			$datediffCertificatione = $this->_controller->Qualification->dateDiff($nextCertification,date('Y-m-d',time()));
			$datediffHorizon = $this->_controller->Qualification->dateDiff($nextCertificationHorizon,date('Y-m-d',time()));

			$_data[$model['sub']]['next_certification'] = $nextCertification;
			$_data[$model['sub']]['next_certification_date'] = __('next verification',true).': '.$nextCertification;
			$_data[$model['sub']]['next_certification_horizon'] = $nextCertificationHorizon;
			$_data[$model['sub']]['time_to_next_certification'] = __('This verification is still valid for',true).': '.$datediffCertificatione;
			$_data[$model['sub']]['time_to_next_horizon'] = __('For the next verification they are reminded in',true).': '.$datediffHorizon;

			if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
				$_data[$model['sub']]['next_certification_date'] = __('next renewal',true).': '.$nextCertification;
				$_data[$model['sub']]['time_to_next_horizon'] = __('To renewal they are reminded in',true).': '.$datediffHorizon;
			}

			if(strtotime($nextCertification) > strtotime(date('Y-m-d',time()))){
				$_data[$model['sub']]['valid'] = 1;
				$_data[$model['sub']]['valid_class'] = 'certification_valid';
			}
			else {
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
				$_data[$model['sub']]['time_to_next_certification'] = __('verification time is exceeded',true) . ' ' . $nextCertification.'; '.$datediffCertificatione.' '.__('ago',true);

				if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
					$_data[$model['sub']]['time_to_next_certification'] = __('renewal time is exceeded',true) . ' ' . $nextCertification.'; '.$datediffCertificatione.' '.__('ago',true);

				}
			}
			if(strtotime($nextCertificationHorizon) < strtotime(date('Y-m-d',time()))){
				$_data[$model['sub']]['time_to_next_horizon'] = __('They were reminded on',true) . ' ' . $nextCertificationHorizon . '; ' . $datediffHorizon . ' ' . __('ago',true);
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid_soon';
			}
			if(strtotime($nextCertification) < strtotime(date('Y-m-d',time()))){
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}
			if($_data[$model['sub']]['certified'] == 2){
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}
			if($_data[$model['sub']]['certified'] == 0){
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}

			$_data[$model['sub']]['certification_requested'] = null;

			if($_data[$model['sub']]['active'] == 0){
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_deactive';
			}

			if($_data[$model['sub']]['certified'] == 2){
				$_data[$model['sub']]['certification_requested'] = __('This verification has the status',true) . ' "' . __('not valid') . '"';
			}

			if($_data[$model['sub']]['certified_file'] != ''){

                                $_folder = 'device_folder';
                                $modeltop = 'Device';

                               if($model['sub'] == 'ExaminerMonitoringData') {
                                   $_folder = 'monitoring_folder';
                                   $modeltop ='Examiner';

                               }

                               if($model['sub'] == 'WelderMonitoringData') {
                                   $_folder = 'welder_monitoring_folder';
                                   $modeltop ='Welder';


                               }

				$path = Configure::read($_folder) . $_data[$modeltop]['id']. DS . $_data[$model['main']]['id'] . DS . $_data[$model['sub']]['id'] . DS;

				if(file_exists($path . $_data[$model['sub']]['certified_file'])){
					$_data[$model['sub']]['certified_file_pfath'] = $path;
				}
				if(!file_exists($path . $_data[$model['sub']]['certified_file'])){
					if($_data[$model['main']]['file'] == 1){
						$_data[$model['sub']]['certified_file'] = '';
						$_data[$model['sub']]['certified_file_error'] = __('The uploadet file could not be found, pleace upload a new file.');
						$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
					}
				}
			}
			if(empty($_data[$model['sub']]['certified_file']) && $_data[$model['main']]['file'] == 1){
				$_data[$model['sub']]['certified_file_error'] = __('Please upload a file.');
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}

		return $_data;
	}

	public function SingleMonitoringSummary($data,$model) {

		$summary = array('future' => array(),'futurenow' => array(),'errors' => array(),'warnings' => array(),'hints' => array());
		$data['Summary'] = array();

		foreach($summary as $_key => $_summary){
			switch($_key){
				case 'futurenow':
				break;
				case 'future':
				break;
				case 'errors':

					if($data[$model['sub']]['valid_class'] == 'certification_not_valid'){
						$data['Summary'][$_key][] = $data[$model['sub']]['time_to_next_certification'];

						if(isset($data[$model['sub']]['certified_file_error'])){
							$data['Summary'][$_key][] = $data[$model['sub']]['certified_file_error'];
						}
					}

				break;

				case 'warnings':
					if($data[$model['sub']]['valid_class'] == 'certification_not_valid_soon'){
						$data['Summary'][$_key][] = $data[$model['sub']]['time_to_next_certification'];
					}
				break;

				case 'hints':
					if($data[$model['sub']]['valid_class'] == 'certification_valid'){
						$data['Summary'][$_key][] = $data[$model['sub']]['time_to_next_certification'].';   ' .$data[$model['sub']]['next_certification_date'];
                                                //$data['Summary'][$_key][] = $data['DeviceCertificateData']['next_certification_date'];
					}
				break;
				case 'deactive':
				break;

			}
		}

		return $data;
	}

	public function DeviceCertificationSummary($summary,$data_input,$model) {

		foreach($summary as $_key => $_summary){
			switch($_key){
				case 'futurenow':
				break;
				case 'future':
				break;
				case 'errors':
					if($data_input['DeviceCertificateData']['valid_class'] == 'certification_not_valid'){
						$summary[$_key][$data_input['DeviceCertificate']['certificat']][$data_input['Device']['id']][$data_input['DeviceCertificate']['id']] = $data_input;
						$summary[$_key][$data_input['DeviceCertificate']['certificat']][$data_input['Device']['id']][$data_input['DeviceCertificate']['id']]['summary'][] = $data_input['DeviceCertificateData']['time_to_next_certification'];

						if(isset($data_input['DeviceCertificateData']['certified_file_error'])){
							$summary[$_key][$data_input['DeviceCertificate']['certificat']][$data_input['Device']['id']][$data_input['DeviceCertificate']['id']]['summary'][] = $data_input['DeviceCertificateData']['certified_file_error'];
						}
					}
				break;
				case 'warnings':
					if($data_input['DeviceCertificateData']['valid_class'] == 'certification_not_valid_soon'){
						$summary[$_key][$data_input['DeviceCertificate']['certificat']][$data_input['Device']['id']][$data_input['DeviceCertificate']['id']] = $data_input;
						$summary[$_key][$data_input['DeviceCertificate']['certificat']][$data_input['Device']['id']][$data_input['DeviceCertificate']['id']]['summary'][] = $data_input['DeviceCertificateData']['time_to_next_certification'];
					}
				break;
				case 'hints':
					if($data_input['DeviceCertificateData']['valid_class'] == 'certification_valid'){
						$summary[$_key][$data_input['DeviceCertificate']['certificat']][$data_input['Device']['id']][$data_input['DeviceCertificate']['id']] = $data_input;
						$summary[$_key][$data_input['DeviceCertificate']['certificat']][$data_input['Device']['id']][$data_input['DeviceCertificate']['id']]['summary'][] = $data_input['DeviceCertificateData']['time_to_next_certification'];
					}
				break;
				case 'deactive':
				break;
			}
		}
		return $summary;
	}

public function WelderTimeHorizons($_data,$model/*,$period*/) {

			//$_data[$model['sub']]['period'] = $period;

			// Testen ob der Zeitpunkt zur ersten Zertifizierung erreicht wurde
			$firstCertification = date('Y-m-d', strtotime('+' . $_data[$model['sub']]['first_certification'] . ' months', strtotime($_data[$model['sub']]['first_registration'])));

			if($firstCertification >= date('Y-m-d',time()) && $_data[$model['sub']]['certified'] == 0){

				$_data[$model['sub']]['first_certification_date'] = $firstCertification;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = $firstCertification;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->_controller->Qualification->dateDiff($firstCertification,date('Y-m-d',time()));
				$_data[$model['sub']]['certification_requested'] = __('The time for certification will be reached in:',true).' '.$_data[$model['sub']]['time_to_first_certification_date'];
				$_data[$model['sub']]['next_certification'] = $firstCertification;
				$_data[$model['sub']]['next_certification_date'] = __('next qualification',true).' '.$firstCertification;
				$_data[$model['sub']]['valid_class'] = 'first_certification_valid_soon';
				$_data[$model['sub']]['valid'] = 0;

				return $_data;
			}

			if($firstCertification < date('Y-m-d',time()) && $_data[$model['sub']]['certified_date'] == NULL && $_data[$model['sub']]['certified'] == 0){

				$_data[$model['sub']]['first_certification_date'] = $firstCertification;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = $firstCertification;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->_controller->Qualification->dateDiff($firstCertification,date('Y-m-d',time()));
				$_data[$model['sub']]['certification_requested'] = __('The time for the certification was reached.',true);
				$_data[$model['sub']]['next_certification'] = $firstCertification;
				$_data[$model['sub']]['next_certification_date'] = __('next certification',true).' '.$firstCertification;
				$_data[$model['sub']]['valid_class'] = 'certification_time_reached';
				$_data[$model['sub']]['valid'] = 0;

				return $_data;
			}

			if($_data[$model['sub']]['certified_date'] == NULL && $_data[$model['sub']]['certified'] > 0){

				$_data[$model['sub']]['first_certification_date'] = null;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = null;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = null;
				$_data[$model['sub']]['certification_requested'] = __('This certificat has the status CERTIFICATED but there is no certification date. Please open the certification and enter a valid date.',true);
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['valid_class'] = 'certification_no_date';
				$_data[$model['sub']]['valid'] = 0;
				return $_data;
			}

			$nextCertification = $_data[$model['sub']]['expiration_date'];
			$nextCertificationHorizon = date('Y-m-d', strtotime('-' . $_data[$model['sub']]['horizon'] . ' months', strtotime($nextCertification)));


			$datediffCertificatione = $this->_controller->Qualification->dateDiff($nextCertification,date('Y-m-d',time()));
			$datediffHorizon = $this->_controller->Qualification->dateDiff($nextCertificationHorizon,date('Y-m-d',time()));

			$_data[$model['sub']]['next_certification'] = $nextCertification;
			$_data[$model['sub']]['next_certification_date'] = __('next qualification',true).': '.$nextCertification;
			$_data[$model['sub']]['next_certification_horizon'] = $nextCertificationHorizon;
			$_data[$model['sub']]['time_to_next_certification'] = __('This certificate is still valid for',true).': '.$datediffCertificatione;
			$_data[$model['sub']]['time_to_next_horizon'] = __('To requalificate they are reminded in',true).': '.$datediffHorizon;

			if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
				$_data[$model['sub']]['next_certification_date'] = __('next renewal',true).': '.$nextCertification;
				$_data[$model['sub']]['time_to_next_horizon'] = __('To renewal they are reminded in',true).': '.$datediffHorizon;
			}

			if(strtotime($nextCertification) > strtotime(date('Y-m-d',time()))){
				$_data[$model['sub']]['valid'] = 1;
				$_data[$model['sub']]['valid_class'] = 'certification_valid';
			}
			else {
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
				if($model['sub'] == 'WelderEyecheckData'){
					$_data[$model['sub']]['time_to_next_certification'] = __('renewal time is exceeded',true) . ' ' . $nextCertification.'; '.$datediffCertificatione.' '.__('ago',true);
				}
				if($model['sub'] == 'WelderCertificateData'){
					$_data[$model['sub']]['time_to_next_certification'] = __('requalification time is exceeded',true) . ' ' . $nextCertification.'; '.$datediffCertificatione.' '.__('ago',true);
				}
				if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
					$_data[$model['sub']]['time_to_next_certification'] = __('renewal time is exceeded',true) . ' ' . $nextCertification.'; '.$datediffCertificatione.' '.__('ago',true);

				}
			}
			if(strtotime($nextCertificationHorizon) < strtotime(date('Y-m-d',time()))){
				$_data[$model['sub']]['time_to_next_horizon'] = __('They were reminded on',true) . ' ' . $nextCertificationHorizon . '; ' . $datediffHorizon . ' ' . __('ago',true);
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid_soon';
			}
			if(strtotime($nextCertification) < strtotime(date('Y-m-d',time()))){
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}
			if($_data[$model['sub']]['certified'] == 2){
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}
			if($_data[$model['sub']]['certified'] == 0){
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}

			if(
				$_data[$model['sub']]['valid_class'] == 'certification_not_valid_soon' ||
				$_data[$model['sub']]['valid_class'] == 'certification_not_valid'
				){
				if($_data[$model['sub']]['apply_for_recertification'] == 0){
					$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
					$_data[$model['sub']]['certification_requested'] = __('Recertification is not requested',true);
					$_data[$model['sub']]['next_certification_date'] = __('next qualification',true).' '.$_data[$model['sub']]['next_certification'];
					if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
						$_data[$model['sub']]['certification_requested'] = __('Renewal is not requested',true);
						$_data[$model['sub']]['next_certification_date'] = __('next renewal',true).': '.$_data[$model['sub']]['next_certification'];
					}
				}
				if($_data[$model['sub']]['apply_for_recertification'] == 1){
					$_data[$model['sub']]['certification_requested_class'] = 'is_requested';
					$_data[$model['sub']]['certification_requested'] = __('Recertification is requested',true);

					if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
						$_data[$model['sub']]['certification_requested'] = __('Renewal is requested',true);
					}
				}
			}
			else {
				$_data[$model['sub']]['certification_requested_class'] = '';
				$_data[$model['sub']]['certification_requested'] = __('The request for requalification can be submitted earliest on',true) . ' ' . $nextCertificationHorizon;
				if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
					$_data[$model['sub']]['certification_requested'] = __('The request for renewal can be submitted earliest on',true) . ' ' . $nextCertificationHorizon;
				}
			}
			if($_data[$model['sub']]['active'] == 0){
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_deactive';
			}

			if($_data[$model['sub']]['certified'] == 2){
				$_data[$model['sub']]['certification_requested'] = __('This certificate has the status',true) . ' "' . __('not certificated') . '"';
			}

			if($_data[$model['sub']]['apply_for_recertification'] == 1){
				$_data[$model['sub']]['certification_requested'] = __('The request for renewal is submitted.',true);
			}

			if($_data[$model['sub']]['certified_file'] != ''){

				if($model['sub'] == 'WelderCertificateData'){
					$_folder = 'welder_certificate_folder';
				}
				if($model['sub'] == 'WelderEyecheckData'){
					$_folder = 'welder_eyecheck_folder';
				}

				$path = Configure::read($_folder) . $_data['Welder']['id']. DS . $_data[$model['main']]['id'] . DS . $_data[$model['sub']]['id'] . DS;

				if(file_exists($path . $_data[$model['sub']]['certified_file'])){
					$_data[$model['sub']]['certified_file_pfath'] = $path;
				}
				if(!file_exists($path . $_data[$model['sub']]['certified_file'])){
					$_data[$model['sub']]['certified_file'] = '';
					$_data[$model['sub']]['certified_file_error'] = __('The certificate file could not be found, pleace upload a new file.');
					$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
				}
			}
			if(empty($_data[$model['sub']]['certified_file'])){
				$_data[$model['sub']]['certified_file_error'] = __('Please upload a certificate file.');
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}

		return $_data;
	}

public function TimeHorizons($_data,$model) {
			// Testen ob der Zeitpunkt zur ersten Zertifizierung erreicht wurde
			$firstCertification = date('Y-m-d', strtotime('+' . $_data[$model['sub']]['first_certification'] . ' months', strtotime($_data[$model['sub']]['first_registration'])));

			if($firstCertification >= date('Y-m-d',time()) && $_data[$model['sub']]['certified'] == 0){

				$_data[$model['sub']]['first_certification_date'] = $firstCertification;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = $firstCertification;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->_controller->Qualification->dateDiff($firstCertification,date('Y-m-d',time()));
				$_data[$model['sub']]['certification_requested'] = __('The time for certification will be reached in:',true).' '.$_data[$model['sub']]['time_to_first_certification_date'];
				$_data[$model['sub']]['next_certification'] = $firstCertification;
				$_data[$model['sub']]['next_certification_date'] = __('next certification',true).' '.$firstCertification;
				$_data[$model['sub']]['valid_class'] = 'first_certification_valid_soon';
				$_data[$model['sub']]['valid'] = 0;

				return $_data;
			}

			if($firstCertification < date('Y-m-d',time()) && $_data[$model['sub']]['certified_date'] == NULL && $_data[$model['sub']]['certified'] == 0){

				$_data[$model['sub']]['first_certification_date'] = $firstCertification;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = $firstCertification;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->_controller->Qualification->dateDiff($firstCertification,date('Y-m-d',time()));
				$_data[$model['sub']]['certification_requested'] = __('The time for the certification was reached.',true);
				$_data[$model['sub']]['next_certification'] = $firstCertification;
				$_data[$model['sub']]['next_certification_date'] = __('next certification',true).' '.$firstCertification;
				$_data[$model['sub']]['valid_class'] = 'certification_time_reached';
				$_data[$model['sub']]['valid'] = 0;

				return $_data;
			}

			if($_data[$model['sub']]['certified_date'] == NULL && $_data[$model['sub']]['certified'] > 0){

				$_data[$model['sub']]['first_certification_date'] = null;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = null;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = null;
				$_data[$model['sub']]['certification_requested'] = __('This certificat has the status CERTIFICATED but there is no certification date. Please open the certification and enter a valid date.',true);
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['valid_class'] = 'certification_no_date';
				$_data[$model['sub']]['valid'] = 0;
				return $_data;
			}
//nextCertification (datum)
		//	$nextCertification = date('Y-m-d',strtotime('+' . $_data[$model['sub']]['expiration_date'] . ' year', strtotime($_data[$model['sub']]['certified_date'])));
			$nextCertification = $_data[$model['sub']]['expiration_date'];

			//Erinnerungsdatum
			$nextCertificationHorizon = date('Y-m-d', strtotime('-' . $_data[$model['sub']]['horizon'] . ' months', strtotime($nextCertification)));

			$datediffCertificatione = $this->_controller->Qualification->dateDiff($nextCertification,date('Y-m-d',time()));
			$datediffHorizon = $this->_controller->Qualification->dateDiff($nextCertificationHorizon,date('Y-m-d',time()));
			$_data[$model['sub']]['next_certification'] = $nextCertification;
			$_data[$model['sub']]['next_certification_date'] = __('next certification',true).': '.$nextCertification;
			$_data[$model['sub']]['next_certification_horizon'] = $nextCertificationHorizon;
			$_data[$model['sub']]['time_to_next_certification'] = __('This certificate is still valid for',true).': '.$datediffCertificatione;
			$_data[$model['sub']]['time_to_next_horizon'] = __('To recertificate they are reminded in',true).': '.$datediffHorizon;

			if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
				$_data[$model['sub']]['next_certification_date'] = __('next renewal',true).': '.$nextCertification;
				$_data[$model['sub']]['time_to_next_horizon'] = __('To renewal they are reminded in',true).': '.$datediffHorizon;
			}

			if(strtotime($nextCertification) > strtotime(date('Y-m-d',time()))){
				$_data[$model['sub']]['valid'] = 1;
				$_data[$model['sub']]['valid_class'] = 'certification_valid';
			}
			else {
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
				if($model['sub'] == 'EyecheckData'){
					$_data[$model['sub']]['time_to_next_certification'] = __('renewal time is exceeded',true) . ' ' . $nextCertification;
				}
				if($model['sub'] == 'CertificateData'){
					$_data[$model['sub']]['time_to_next_certification'] = __('recertification time is exceeded',true) . ' ' . $nextCertification;
				}
				if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
					$_data[$model['sub']]['time_to_next_certification'] = __('renewal time is exceeded',true) . ' ' . $nextCertification;

				}
			}
			if(strtotime($nextCertificationHorizon) < strtotime(date('Y-m-d',time()))){
				$_data[$model['sub']]['time_to_next_horizon'] = __('They were reminded on',true) . ' ' . $nextCertificationHorizon;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid_soon';
			}
			if(strtotime($nextCertification) < strtotime(date('Y-m-d',time()))){
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}
			if($_data[$model['sub']]['certified'] == 2){
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}
			if($_data[$model['sub']]['certified'] == 0){
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}

			if(
				$_data[$model['sub']]['valid_class'] == 'certification_not_valid_soon' ||
				$_data[$model['sub']]['valid_class'] == 'certification_not_valid'
				){
				if($_data[$model['sub']]['apply_for_recertification'] == 0){
					$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
					$_data[$model['sub']]['certification_requested'] = __('Recertification is not requested',true);
					$_data[$model['sub']]['next_certification_date'] = __('next certification',true).' '.$_data[$model['sub']]['next_certification'];
					if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
						$_data[$model['sub']]['certification_requested'] = __('Renewal is not requested',true);
						$_data[$model['sub']]['next_certification_date'] = __('next renewal',true).': '.$_data[$model['sub']]['next_certification'];
					}
				}
				if($_data[$model['sub']]['apply_for_recertification'] == 1){
					$_data[$model['sub']]['certification_requested_class'] = 'is_requested';
					$_data[$model['sub']]['certification_requested'] = __('Recertification is requested',true);

					if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
						$_data[$model['sub']]['certification_requested'] = __('Renewal is requested',true);
					}
				}
			}
			else {
				$_data[$model['sub']]['certification_requested_class'] = '';
				$_data[$model['sub']]['certification_requested'] = __('The request for recertification can be submitted earliest on',true) . ' ' . $nextCertificationHorizon;
				if(isset($_data[$model['sub']]['renewal']) && $_data[$model['sub']]['renewal'] == true){
					$_data[$model['sub']]['certification_requested'] = __('The request for renewal can be submitted earliest on',true) . ' ' . $nextCertificationHorizon;
				}
			}
			if($_data[$model['sub']]['active'] == 0){
				$_data[$model['sub']]['valid'] = 0;
				$_data[$model['sub']]['valid_class'] = 'certification_deactive';
			}

			if($_data[$model['sub']]['certified'] == 2){
				$_data[$model['sub']]['certification_requested'] = __('This certificate has the status',true) . ' "' . __('not certificated') . '"';
			}

			if($_data[$model['sub']]['apply_for_recertification'] == 1){
				$_data[$model['sub']]['certification_requested'] = __('The request for renewal is submitted.',true);
			}

			if($_data[$model['sub']]['certified_file'] != ''){

				if($model['sub'] == 'CertificateData'){
					$_folder = 'certificate_folder';
				}
				if($model['sub'] == 'EyecheckData'){
					$_folder = 'eyecheck_folder';
				}

				$path = Configure::read($_folder) . $_data['Examiner']['id']. DS . $_data[$model['main']]['id'] . DS . $_data[$model['sub']]['id'] . DS;

				if(file_exists($path . $_data[$model['sub']]['certified_file'])){
					$_data[$model['sub']]['certified_file_pfath'] = $path;
				}
				if(!file_exists($path . $_data[$model['sub']]['certified_file'])){
					$_data[$model['sub']]['certified_file'] = '';
					$_data[$model['sub']]['certified_file_error'] = __('The certificate file could not be found, pleace upload a new file.');
					$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
				}
			}
			if(empty($_data[$model['sub']]['certified_file'])){
				$_data[$model['sub']]['certified_file_error'] = __('Please upload a certificate file.');
				$_data[$model['sub']]['valid_class'] = 'certification_not_valid';
			}

		return $_data;
	}

	public function MonitoringSummary($_data,$model) {

		$monitoring = array();

		$top = $model['top'];
                $main = $model['main'];
                $sub = $model['sub'];
		$options = array('conditions' =>
							array(
								$model['main'].'.device_id' => $_data[$model['top']]['id'],
								$model['main'].'.deleted' => 0,
								$model['main'].'.active' => 1,

							)
						);

		$this->_controller->$top->$main->recursive = -1;
		$this->_controller->$top->$main->$sub->recursive = -1;

		$device_certificate = $this->_controller->$top->$main->find('all',$options);

		$_data['Monitorings'] = array();

		if(count($device_certificate) > 0){

			$_data[$model['main']] = $device_certificate;

			foreach($_data[$model['main']] as $__key => $__data){

				$options = array(
								'order' => array($model['sub'].'.id DESC'),
								'conditions' =>
									array(
										$model['sub'].'.device_id' => $_data[$model['top']]['id'],
										$model['sub'].'.device_certificate_id' => $__data[$model['main']]['id'],
										$model['sub'].'.deleted' => 0,
										$model['sub'].'.active' => 1
									)
								);

				$device_certificate_data = $this->_controller->$top->$main->$sub->find('first',$options);

				if(count($device_certificate_data) > 0){
					$_data[$model['main']][$__key][$model['sub']] = $device_certificate_data[$model['sub']];
					$_data[$model['main']][$__key][$model['top']] = $_data[$model['top']];
					$_data[$model['main']][$__key] = $this->_controller->Qualification->DeviceCertification($_data[$model['main']][$__key],$model);
					$_data[$model['main']][$__key] = $this->_controller->Qualification->SingleMonitoringSummary($_data[$model['main']][$__key],$model);

				}

				$_data['Monitorings'][$__data[$model['main']]['certificat']] = array();
			}
		}

		unset($_data[$model['sub']]);

		if(!isset($_data[$model['main']])) return $monitoring;

		foreach($_data[$model['main']] as $_key => $_data){

			if(!isset($_data['Summary'])) continue;

			if(count($_data['Summary']) > 0){

				foreach($_data['Summary'] as $__key => $__data){
					if(count($__data) == 0) continue;

					$monitoring['summary']['monitoring'][$__key][$_data[$model['main']]['certificat']][$_data[$model['top']]['id']]['info'] = $_data[$model['sub']];

					foreach($__data as $___key => $___data){
						$monitoring['summary']['monitoring'][$__key][$_data[$model['main']]['certificat']][$_data[$model['top']]['id']][$_data[$model['sub']]['id']] = $___data;
					}
				}
			}
		}
		return $monitoring;
	}

	public function ExaminerMonitoringSummary($_data,$model) {

		$monitoring = array();
		$examiner_certificate = array();
		$options = array('conditions' =>
							array(
								$model['main'].'.examiner_id' => $_data[$model['top']]['id'],
								$model['main'].'.deleted' => 0,
								$model['main'].'.active' => 1,

							)
						);
		$top = $model['top'];
                $main = $model['main'];
                $sub = $model['sub'];
		$this->_controller->$top->$main->recursive = -1;
		$this->_controller->$top->$main->$sub->recursive = -1;
		$examiner_certificate = $this->_controller->$top->$main->find('all',$options);

		$_data['Monitorings'] = array();

		if(count($examiner_certificate) > 0){

			$_data[$main] = $examiner_certificate;

			foreach($_data[$main] as $__key => $__data){

				$options = array(
								'order' => array($model['sub'].'.id DESC'),
								'conditions' =>
									array(
										$model['sub'].'.examiner_id' => $_data[$model['top']]['id'],
										$model['sub'].'.examiner_monitoring_id' => $__data[$main]['id'],
										$model['sub'].'.deleted' => 0,
										$model['sub'].'.active' => 1
									)
								);

				$examiner_certificate_data = $this->_controller->$top->$main->$sub->find('first',$options);

				if(count($examiner_certificate_data) > 0){
					$_data[$main][$__key][$model['sub']] = $examiner_certificate_data[$model['sub']];
					$_data[$main][$__key][$model['top']] = $_data[$model['top']];
					$_data[$main][$__key] = $this->_controller->Qualification->DeviceCertification($_data[$main][$__key],$model);
					$_data[$main][$__key] = $this->_controller->Qualification->SingleMonitoringSummary($_data[$main][$__key],$model);

				}

				$_data['Monitorings'][$__data[$main]['certificat']] = array();
			}
		}

		unset($_data[$model['sub']]);
/*
		foreach($_data[$main] as $_key => $_data){
			if(count($_data['Summary']) > 0){

				foreach($_data['Summary'] as $__key => $__data){
					if(count($__data) == 0) continue;

					$monitoring['summary']['monitoring'][$__key][$_data[$main]['certificat']][$_data[$model['top']]['id']]['info'] = $_data[$model['sub']];

					foreach($__data as $___key => $___data){
						$monitoring['summary']['monitoring'][$__key][$_data[$main]['certificat']][$_data[$model['top']]['id']][$_data[$model['sub']]['id']] = $___data;
					}
				}
			}
		}
*/
		foreach($_data[$main] as $__key => $__data){
			if(!isset($__data['Summary'])) continue;
			if(count($__data['Summary']) == 0) continue;
			foreach($__data['Summary'] as $___key => $___data){
				if(count($___data) == 0) continue;
				$monitoring['summary']['monitoring'][$___key][$__data[$main]['certificat']][$__data[$model['top']]['id']]['info'] = $__data[$model['sub']];
				foreach($___data as $____key => $____data){
					$monitoring['summary']['monitoring'][$___key][$__data[$main]['certificat']][$__data[$model['top']]['id']][$__data[$model['sub']]['id']] = $____data;
				}
			}
		}

		return $monitoring;
	}







		public function MonitoringSearchSummary($_data,$model,$MonitoringKind = null) {
		$summary = array('future' => array(),'futurenow' => array(),'errors' => array(),'warnings' => array(),'hints' => array());
		$monitoring['summary']['monitoring'] = array();
                $MonitoringKindCond = array($model['main'].'.certificat' => array());
                count($MonitoringKind) > 0 ?  $MonitoringKindCond = array($model['main'].'.certificat' => $MonitoringKind) : 0;

		$options = array('conditions' =>
							array(
								$model['main'].'.device_id' => $_data[$model['top']]['id'],
								$model['main'].'.deleted' => 0,
								$model['main'].'.active' => 1,
								$MonitoringKindCond
							)
						);

		$this->_controller->$top->$main->recursive = -1;
		$this->_controller->$top->$sub->recursive = -1;

		$device_certificate = $this->_controller->$top->$main->find('all',$options);

		$_data['Monitorings'] = array();

		if(count($device_certificate) > 0){

			$_data[$model['main']] = $device_certificate;

			foreach($_data[$model['main']] as $__key => $__data){

				$options = array(
								'order' => array($model['sub'].'.id DESC'),
								'conditions' =>
									array(
										$model['sub'].'.device_id' => $_data[$model['top']]['id'],
										$model['sub'].'.device_certificate_id' => $__data[$model['main']]['id'],
										$model['sub'].'.deleted' => 0,
										$model['sub'].'.active' => 1
									)
								);

				$device_certificate_data = $this->_controller->$top->$main->$sub->find('first',$options);

				if(count($device_certificate_data) > 0){
					$_data[$model['main']][$__key][$model['sub']] = $device_certificate_data[$model['sub']];
					$_data[$model['main']][$__key][$model['top']] = $_data[$model['top']];
					$_data[$model['main']][$__key] = $this->_controller->Qualification->DeviceCertification($_data[$model['main']][$__key],$model);
					$_data[$model['main']][$__key] = $this->_controller->Qualification->SingleMonitoringSummary($_data[$model['main']][$__key],$model);

				}

				$_data['Monitorings'][$__data[$model['main']]['certificat']] = array();
			}
		}

		unset($_data[$model['sub']]);

		foreach($_data[$model['main']] as $_key => $_data){
			if(count($_data['Summary']) > 0){

				foreach($_data['Summary'] as $__key => $__data){
					if(count($__data) == 0) continue;

					$monitoring['summary']['monitoring'][$__key][$_data[$model['main']]['certificat']][$_data[$model['top']]['id']]['info'] = $_data[$model['sub']];

					foreach($__data as $___key => $___data){
						$monitoring['summary']['monitoring'][$__key][$_data[$model['main']]['certificat']][$_data[$model['top']]['id']][$_data[$model['sub']]['id']] = $___data;
					}
				}
			}
		}
		return $monitoring;
	}
	public function WelderMonitoringSummary($_data,$model) {

		$monitoring = array();

		$options = array('conditions' =>
							array(
								$model['main'].'.welder_id' => $_data[$model['top']]['id'],
								$model['main'].'.deleted' => 0,
								$model['main'].'.active' => 1,

							)
						);

                $this->_controller->{$model['top']}->{$model['main']}->recursive = -1;
                $this->_controller->{$model['top']}->{$model['main']}->{$model['sub']}->recursive = -1;

        $welder_certificate = $this->_controller->{$model['top']}->{$model['main']}->find('all',$options);

		$_data['Monitorings'] = array();

		if(count($welder_certificate) > 0){

			$_data[$model['main']] = $welder_certificate;

			foreach($_data[$model['main']] as $__key => $__data){

				$options = array(
								'order' => array($model['sub'].'.id DESC'),
								'conditions' =>
									array(
										$model['sub'].'.welder_id' => $_data[$model['top']]['id'],
										$model['sub'].'.welder_monitoring_id' => $__data[$model['main']]['id'],
										$model['sub'].'.deleted' => 0,
										$model['sub'].'.active' => 1
									)
								);

				$welder_certificate_data = $this->_controller->{$model['top']}->{$model['main']}->{$model['sub']}->find('first',$options);

				if(count($welder_certificate_data) > 0){
					$_data[$model['main']][$__key][$model['sub']] = $welder_certificate_data[$model['sub']];
					$_data[$model['main']][$__key][$model['top']] = $_data[$model['top']];
					$_data[$model['main']][$__key] = $this->_controller->Qualification->DeviceCertification($_data[$model['main']][$__key],$model);
					$_data[$model['main']][$__key] = $this->_controller->Qualification->SingleMonitoringSummary($_data[$model['main']][$__key],$model);

				}

				$_data['Monitorings'][$__data[$model['main']]['certificat']] = array();
			}
		}

		//unset($_data[$model['sub']]);

		foreach($_data[$model['main']] as $_key => $_data){
			if(isset($_data['Summary']) && is_countable($_data['Summary']) && count($_data['Summary']) > 0){

				foreach($_data['Summary'] as $__key => $__data){

					if(count($__data) == 0) continue;

					$monitoring['summary']['monitoring'][$__key][$_data[$model['main']]['certificat']][$_data[$model['top']]['id']]['info'] = $_data[$model['sub']];

                                        $monitoring['summary']['monitoring'][$__key][$_data[$model['main']]['certificat']][$_data[$model['top']]['id']]['info']['certificat'] = $_data[$model['main']]['certificat'];
					foreach($__data as $___key => $___data){
						$monitoring['summary']['monitoring'][$__key][$_data[$model['main']]['certificat']][$_data[$model['top']]['id']][$_data[$model['sub']]['id']] = $___data;
					}
				}
			}
		}

		return $monitoring;
	}







	public function CertificateSummary($_data,$model) {


		$summary = array('future' => array(),'futurenow' => array(),'errors' => array(),'warnings' => array(),'hints' => array());

		$qualifications = array();
	 	$certificat_no = array();

		foreach($_data as $__key => $__data){

			if(count($_data) == 0) continue;

			foreach($__data as $___key => $___data){

				if($___data[$model['main']]['certificate_data_active'] == 0){

					if(!isset($___data['Examiner'])) continue;

					$this->_controller->request->projectvars['VarsArray'][15] = $___data['Examiner']['id'];
					$this->_controller->request->projectvars['VarsArray'][16] = $___data[$model['main']]['id'];
					$this->_controller->request->projectvars['VarsArray'][17] = 0;

					$qualifications[$___data[$model['main']]['id']]['certificat'] = '-';
					$qualifications[$___data[$model['main']]['id']]['active'] = $___data[$model['main']]['active'];
					$qualifications[$___data[$model['main']]['id']]['class'] = strtolower($___data[$model['main']]['sector']).'_'.strtolower($___data[$model['main']]['testingmethod']).'_'.$___data[$model['main']]['level'].' no_certificat';
					$qualifications[$___data[$model['main']]['id']]['sector'] = $___data[$model['main']]['sector'];
					$qualifications[$___data[$model['main']]['id']]['testingmethod'] = $___data[$model['main']]['testingmethod'];
					$qualifications[$___data[$model['main']]['id']]['testingmethod_id'] = $___data[$model['main']]['testingmethod_id'];
					$qualifications[$___data[$model['main']]['id']]['level'] = $___data[$model['main']]['level'];
					$qualifications[$___data[$model['main']]['id']]['term'] = $this->_controller->request->projectvars['VarsArray'];
					$qualifications[$___data[$model['main']]['id']]['termlink'] = implode('/',$this->_controller->request->projectvars['VarsArray']);
					$qualifications[$___data[$model['main']]['id']]['certificate_id'] = 0;
					$qualifications[$___data[$model['main']]['id']]['certificate_data_id'] = 0;

				}
				
				if(!isset($___data[$model['sub']]['id'])) continue;
				
				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['active'] == 1 && $___data[$model['sub']]['deleted'] == 0){

					$certificat_no[$___data['Certificate']['certificat']] = $___data['Certificate']['certificat'];


					$this->_controller->request->projectvars['VarsArray'][15] = $___data['Examiner']['id'];
					$this->_controller->request->projectvars['VarsArray'][16] = $___data[$model['main']]['id'];
					$this->_controller->request->projectvars['VarsArray'][17] = $___data[$model['sub']]['id'];

					$qualifications[$___data[$model['main']]['id']]['certificat'] = $___data[$model['main']]['certificat'];
					$qualifications[$___data[$model['main']]['id']]['active'] = $___data[$model['main']]['active'];
					$qualifications[$___data[$model['main']]['id']]['class'] = strtolower($___data[$model['main']]['sector']).'_'.strtolower($___data[$model['sub']]['testingmethod']).'_'.$___data[$model['main']]['level'];
					$qualifications[$___data[$model['main']]['id']]['sector'] = $___data[$model['main']]['sector'];
					$qualifications[$___data[$model['main']]['id']]['testingmethod'] = $___data[$model['main']]['testingmethod'];
					$qualifications[$___data[$model['main']]['id']]['level'] = $___data[$model['main']]['level'];
					$qualifications[$___data[$model['main']]['id']]['term'] = $this->_controller->request->projectvars['VarsArray'];
					$qualifications[$___data[$model['main']]['id']]['termlink'] = implode('/',$this->_controller->request->projectvars['VarsArray']);
					$qualifications[$___data[$model['main']]['id']]['certificate_id'] = $___data[$model['main']]['id'];
					$qualifications[$___data[$model['main']]['id']]['certificate_data_id'] = $___data[$model['sub']]['id'];

				}

				if($___data[$model['sub']]['valid_class'] == 'certification_time_reached'){
					$summary['futurenow'][$___data[$model['sub']]['id']]['recertified_soon_date'] = __('The time for certification was reached on',true). ' ' . $___data[$model['sub']]['next_certification'];
					$summary['futurenow'][$___data[$model['sub']]['id']][] = $summary['futurenow'][$___data[$model['sub']]['id']]['recertified_soon_date'];
					$summary['futurenow'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['futurenow'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['futurenow'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					$qualifications[$___data[$model['main']]['id']]['status_class'] = 'certification_time_reached';

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
					}
					continue;
				}

				if($___data[$model['sub']]['valid_class'] == 'first_certification_valid_soon'){
					$summary['future'][$___data[$model['sub']]['id']]['recertified_soon_date'] =
						__('The time for certification is reached on',true). ' ' . $___data[$model['sub']]['next_certification'].' (' . __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification'],date('Y-m-d',time())).')';

					$summary['future'][$___data[$model['sub']]['id']][] = $summary['future'][$___data[$model['sub']]['id']]['recertified_soon_date'];
					$summary['future'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['future'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['future'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					$qualifications[$___data[$model['main']]['id']]['status_class'] = 'first_certification_valid_soon';

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_future') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_future';
					}
					continue;
				}

				if($___data[$model['sub']]['valid_class'] == 'certification_no_date'){

					$summary['errors'][$___data[$model['sub']]['id']][] = __('This certificat has the status CERTIFICATED but there is no certification date. Please open the certification and enter a valid date.',true);
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}

				if($___data[$model['sub']]['valid_class'] == 'certification_time_exceeded'){
					$summary['errors'][$___data[$model['sub']]['id']]['recertified_soon_date'] =  __('The time for certification was exceeded.',true);

					$summary['errors'][$___data[$model['sub']]['id']][] = __('The time for certification was exceeded.',true);
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}

				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['certified'] == 2){

					$summary['errors'][$___data[$model['sub']]['id']]['recertified_soon_date'] = __('This certificate has the status',true) . ' "' . __('not certificated') . '"';

					$summary['errors'][$___data[$model['sub']]['id']][] =  __('This certificate has the status',true) . ' "' . __('not certificated') . '"';
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
					continue;
				}

				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['valid'] == 0 && $___data[$model['sub']]['active'] == 1){

					$summary['errors'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}
				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['certified_file'] == '' && $___data[$model['sub']]['active'] == 1){
					$summary['errors'][$___data[$model['sub']]['id']][] = __('Certificate file not found.',true);
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}
				if(
				$___data[$model['sub']]['valid'] == 1 &&
				$___data[$model['sub']]['valid_class'] == 'certification_not_valid_soon' &&
				$___data[$model['sub']]['apply_for_recertification'] == 0
				){
					$summary['warnings'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['warnings'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['certification_requested'];
					$summary['warnings'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['warnings'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['warnings'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}
				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['certified'] == 0){

					if($___data[$model['sub']]['active'] > 0){
						$summary['warnings'][$___data[$model['sub']]['id']][] = __('Certificate deative',true);
						$summary['warnings'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
						$summary['warnings'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
						$summary['warnings'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

						$qualifications[$___data[$model['main']]['id']]['status_class'] = 'certificate_deactive';

						if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
							$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
						}
					}
				}
				if(
					$___data[$model['sub']]['valid'] == 1 &&
					$___data[$model['sub']]['certified'] == 1 &&
					$___data[$model['sub']]['valid_class'] == 'certification_valid' &&
					strtotime('+'.Configure::read('NextZertificationsMonths').' month') >= strtotime($___data[$model['sub']]['next_certification_horizon'])
				)
				{

						$summary['hints'][$___data[$model['sub']]['id']]['recertified_soon_date'] =
							__('This certification will expire on',true). ' ' . $___data[$model['sub']]['next_certification'].' (' . __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification'],date('Y-m-d',time())).')';
						$summary['hints'][$___data[$model['sub']]['id']]['horizon_soon_date'] =
							__('They will be remembered',true). ' ' . $___data[$model['sub']]['next_certification_horizon'].' ('. __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification_horizon'],date('Y-m-d',time())).')';


						$summary['hints'][$___data[$model['sub']]['id']][] = __('This certification will expire on',true). ' ' . $___data[$model['sub']]['next_certification'].' (' . __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification'],date('Y-m-d',time())).')';
						$summary['hints'][$___data[$model['sub']]['id']][] = __('They will be remembered',true). ' ' . $___data[$model['sub']]['next_certification_horizon'].' ('. __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification_horizon'],date('Y-m-d',time())).')';

						$summary['hints'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
						$summary['hints'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
						$summary['hints'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

						$qualifications[$___data[$model['main']]['id']]['status_class'] = 'recertified_soon';

						if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
							$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
						}
				}
				if(
				$___data[$model['sub']]['valid'] == 1 &&
				$___data[$model['sub']]['valid_class'] == 'certification_not_valid_soon' &&
				$___data[$model['sub']]['apply_for_recertification'] == 1
				){
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['certification_requested'];
					$summary['hints'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['hints'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['hints'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					$qualifications[$___data[$model['main']]['id']]['status_class'] = 'certification_not_valid_soon';

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
					}
				}
				/*
				if($___data[$model['sub']]['active'] == 0){
					$summary['deactive'][$___data[$model['sub']]['id']][] = __('This certificate has the status deaktive.',true);
					$summary['deactive'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['deactive'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['deactive'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];
				}
				*/
				if($___data[$model['sub']]['valid_class'] == 'certification_valid'){
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['certification_requested'];
					$summary['hints'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['hints'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['hints'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];
				}
			}
		}

		foreach($qualifications as $_key => $_data){
			if(isset($summary['future'][$_data['certificate_data_id']])) $qualifications[$_key]['status_class'] = $summary['future'][$_data['certificate_data_id']]['info']['valid_class'];
			if(isset($summary['futurenow'][$_data['certificate_data_id']])) $qualifications[$_key]['status_class'] = $summary['futurenow'][$_data['certificate_data_id']]['info']['valid_class'];
			if(isset($summary['errors'][$_data['certificate_data_id']])) $qualifications[$_key]['status_class'] = $summary['errors'][$_data['certificate_data_id']]['info']['valid_class'];
			if(isset($summary['warnings'][$_data['certificate_data_id']])) $qualifications[$_key]['status_class'] = $summary['warnings'][$_data['certificate_data_id']]['info']['valid_class'];
			if(isset($summary['hints'][$_data['certificate_data_id']])) $qualifications[$_key]['status_class'] = $summary['hints'][$_data['certificate_data_id']]['info']['valid_class'];
		}

		$testingmethods = array();
		$this->_controller->Examiner->Certificate->recursive = 1;

		foreach($qualifications as $_key => $_data){

			$Testingmethods = $this->_controller->Examiner->Certificate->find('first',array('conditions' => array('Certificate.id' => $_key)));
			
			if(count($Testingmethods) == 0){
				$testingmethods[$_key] = array();
				continue;
			}

			if(!isset($Testingmethods['CertificatesTestingmethodes'])){
				$testingmethods[$_key] = array();
				continue;
			}

			$Testingmethods = Hash::extract($Testingmethods['CertificatesTestingmethodes'], '{n}.testingmethod_id');
			$testingmethods[$_key] = $Testingmethods;
		}

	 	$output['summary'] = $summary;
	 	$output['certificat_no'] = $certificat_no;
	 	$output['qualifications'] = $qualifications;
	 	$output['testingmethods'] = $testingmethods;

		return $output;
	}

	public function WelderCertificateSummary($_data,$model) {
/*
pr($___data[$model['main']]['id']);
pr($qualifications[$___data[$model['main']]['id']]['certificat']);
			if(!empty($qualifications[$___data[$model['main']]['id']]['certificat'])){
				$certificat_no[] = $qualifications[$___data[$model['main']]['id']]['certificat'];
			}
*/
		$summary = array('future' => array(),'futurenow' => array(),'errors' => array(),'warnings' => array(),'hints' => array());

		$qualifications = array();
	 	$certificat_no = array();

		foreach($_data as $__key => $__data){
			if(count($_data) == 0) continue;

			foreach($__data as $___key => $___data){

				if($___data[$model['main']]['certificate_data_active'] == 0){

					$this->_controller->request->projectvars['VarsArray'][15] = $___data['Welder']['id'];
					$this->_controller->request->projectvars['VarsArray'][16] = $___data[$model['main']]['id'];
					$this->_controller->request->projectvars['VarsArray'][17] = 0;

					$qualifications[$___data[$model['main']]['id']]['certificat'] = '-';
					$qualifications[$___data[$model['main']]['id']]['active'] = $___data[$model['main']]['active'];
					$qualifications[$___data[$model['main']]['id']]['class'] = 'w_'.strtolower($___data[$model['main']]['weldingmethod']).'_no_certificat';
					$qualifications[$___data[$model['main']]['id']]['sector'] = $___data[$model['main']]['sector'];
					$qualifications[$___data[$model['main']]['id']]['weldingmethod'] = $___data[$model['main']]['weldingmethod'];
					$qualifications[$___data[$model['main']]['id']]['level'] = $___data[$model['main']]['level'];
					$qualifications[$___data[$model['main']]['id']]['term'] = $this->_controller->request->projectvars['VarsArray'];
					$qualifications[$___data[$model['main']]['id']]['termlink'] = implode('/',$this->_controller->request->projectvars['VarsArray']);
					$qualifications[$___data[$model['main']]['id']]['certificate_id'] = 0;
					$qualifications[$___data[$model['main']]['id']]['certificate_data_id'] = 0;

				}

				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['active'] == 1 && $___data[$model['sub']]['deleted'] == 0){

					$certificat_no[$___data['WelderCertificate']['certificat']] = $___data['WelderCertificate']['certificat'];


					$this->_controller->request->projectvars['VarsArray'][15] = $___data['Welder']['id'];
					$this->_controller->request->projectvars['VarsArray'][16] = $___data[$model['main']]['id'];
					$this->_controller->request->projectvars['VarsArray'][17] = $___data[$model['sub']]['id'];

					$qualifications[$___data[$model['main']]['id']]['certificat'] = $___data[$model['main']]['certificat'];
					$qualifications[$___data[$model['main']]['id']]['active'] = $___data[$model['main']]['active'];
					$qualifications[$___data[$model['main']]['id']]['class'] =  'w_'.strtolower($___data[$model['main']]['weldingmethod']);
					$qualifications[$___data[$model['main']]['id']]['sector'] = $___data[$model['main']]['sector'];
					$qualifications[$___data[$model['main']]['id']]['weldingmethod'] = $___data[$model['main']]['weldingmethod'];
					$qualifications[$___data[$model['main']]['id']]['level'] = $___data[$model['main']]['level'];
					$qualifications[$___data[$model['main']]['id']]['term'] = $this->_controller->request->projectvars['VarsArray'];
					$qualifications[$___data[$model['main']]['id']]['termlink'] = implode('/',$this->_controller->request->projectvars['VarsArray']);
					$qualifications[$___data[$model['main']]['id']]['certificate_id'] = $___data[$model['main']]['id'];
					$qualifications[$___data[$model['main']]['id']]['certificate_data_id'] = $___data[$model['sub']]['id'];

				}

				if($___data[$model['sub']]['valid_class'] == 'certification_time_reached'){
					$summary['futurenow'][$___data[$model['sub']]['id']]['recertified_soon_date'] =
__('The time for qualification was reached on',true). ' ' . $___data[$model['sub']]['next_certification'];

					$summary['futurenow'][$___data[$model['sub']]['id']][] = $summary['futurenow'][$___data[$model['sub']]['id']]['recertified_soon_date'];
					$summary['futurenow'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['futurenow'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['futurenow'][$___data[$model['sub']]['id']]['examiner'] = $___data['Welder'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
					}
					continue;
				}

				if($___data[$model['sub']]['valid_class'] == 'first_certification_valid_soon'){
					$summary['future'][$___data[$model['sub']]['id']]['recertified_soon_date'] =
__('The time for qualification is reached on',true). ' ' . $___data[$model['sub']]['next_certification'].' (' . __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification'],date('Y-m-d',time())).')';

					$summary['future'][$___data[$model['sub']]['id']][] = $summary['future'][$___data[$model['sub']]['id']]['recertified_soon_date'];
					$summary['future'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['future'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['future'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];
					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_future') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_future';
					}
					continue;
				}
				if($___data[$model['sub']]['valid_class'] == 'certification_no_date'){

					$summary['errors'][$___data[$model['sub']]['id']][] = __('This qualification has the status QUALIFICATED but there is no qualification date. Please open the qualification and enter a valid date.',true);
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];

						if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
							$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
						}
					}

				if($___data[$model['sub']]['valid_class'] == 'certification_time_exceeded'){
					$summary['errors'][$___data[$model['sub']]['id']]['recertified_soon_date'] =  __('The time for qualification was exceeded.',true);

					$summary['errors'][$___data[$model['sub']]['id']][] = __('The time for qualification was exceeded.',true);
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}

				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['certified'] == 2){

					$summary['errors'][$___data[$model['sub']]['id']]['recertified_soon_date'] = __('This qualification has the status',true) . ' "' . __('not qualified') . '"';

					$summary['errors'][$___data[$model['sub']]['id']][] =  __('This qualification has the status',true) . ' "' . __('not qualified') . '"';
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
					continue;
				}

				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['valid'] == 0 && $___data[$model['sub']]['active'] == 1){
					$summary['errors'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}
				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['certified_file'] == '' && $___data[$model['sub']]['active'] == 1){
					$summary['errors'][$___data[$model['sub']]['id']][] = __('Certificate file not found.',true);
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}
				if(
				$___data[$model['sub']]['valid'] == 1 &&
				$___data[$model['sub']]['valid_class'] == 'certification_not_valid_soon' &&
				$___data[$model['sub']]['apply_for_recertification'] == 0
				){
					$summary['warnings'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['warnings'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['certification_requested'];
					$summary['warnings'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['warnings'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['warnings'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}
				if($___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['certified'] == 0){

					if($___data[$model['sub']]['active'] > 0){
						$summary['warnings'][$___data[$model['sub']]['id']][] = __('Certificate deative',true);
						$summary['warnings'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
						$summary['warnings'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
						$summary['warnings'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];

						if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
							$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
						}
					}
				}
				if(
					$___data[$model['sub']]['valid'] == 1 &&
					$___data[$model['sub']]['certified'] == 1 &&
					$___data[$model['sub']]['valid_class'] == 'certification_valid' &&
					strtotime('+'.Configure::read('NextZertificationsMonths').' month') >= strtotime($___data[$model['sub']]['next_certification_horizon'])
				)
				{

						$summary['hints'][$___data[$model['sub']]['id']]['recertified_soon_date'] =
__('This qualification will expire on',true). ' ' . $___data[$model['sub']]['next_certification'].' (' . __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification'],date('Y-m-d',time())).')';
						$summary['hints'][$___data[$model['sub']]['id']]['horizon_soon_date'] =
__('They will be remembered',true). ' ' . $___data[$model['sub']]['next_certification_horizon'].' ('. __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification_horizon'],date('Y-m-d',time())).')';


						$summary['hints'][$___data[$model['sub']]['id']][] = __('This qualification will expire on',true). ' ' . $___data[$model['sub']]['next_certification'].' (' . __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification'],date('Y-m-d',time())).')';
						$summary['hints'][$___data[$model['sub']]['id']][] = __('They will be remembered',true). ' ' . $___data[$model['sub']]['next_certification_horizon'].' ('. __('in',true) . ' ' . $this->_controller->Qualification->dateDiff($___data[$model['sub']]['next_certification_horizon'],date('Y-m-d',time())).')';

						$summary['hints'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
						$summary['hints'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
						$summary['hints'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];
						if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
							$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
						}
				}
				if(
				$___data[$model['sub']]['valid'] == 1 &&
				$___data[$model['sub']]['valid_class'] == 'certification_not_valid_soon' &&
				$___data[$model['sub']]['apply_for_recertification'] == 1
				){
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['certification_requested'];
					$summary['hints'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['hints'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['hints'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
					}
				}
				/*
				if($___data[$model['sub']]['active'] == 0){
					$summary['deactive'][$___data[$model['sub']]['id']][] = __('This certificate has the status deaktive.',true);
					$summary['deactive'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['deactive'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['deactive'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];
				}
				*/
				if($___data[$model['sub']]['valid_class'] == 'certification_valid'){
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['certification_requested'];
					$summary['hints'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['hints'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['hints'][$___data[$model['sub']]['id']]['welder'] = $___data['Welder'];
				}
			}
		}

	 	$output['summary'] = $summary;
	 	$output['certificat_no'] = $certificat_no;
	 	$output['qualifications'] = $qualifications;

		return $output;
	}

	public function EyecheckSummary($_data,$model) {

		if(count($_data) == 0){
			return array('summary' => array(),'qualifications' => array());
		}

		$summary = array('future' => array(),'futurenow' => array(),'errors' => array(),'warnings' => array(),'hints' => array());
		$qualifications = array();

		if($_data[$model['sub']]['active'] == 1 && $_data[$model['sub']]['deleted'] == 0){

			$this->_controller->request->projectvars['VarsArray'][15] = $_data['Examiner']['id'];
			$this->_controller->request->projectvars['VarsArray'][16] = $_data[$model['main']]['id'];
			$this->_controller->request->projectvars['VarsArray'][17] = $_data[$model['sub']]['id'];

			$qualifications[$_data[$model['main']]['id']]['certificat'] = $_data[$model['main']]['certificat'];
			$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_valide';
			$qualifications[$_data[$model['main']]['id']]['term'] = $this->_controller->request->projectvars['VarsArray'];
			$qualifications[$_data[$model['main']]['id']]['termlink'] = implode('/',$this->_controller->request->projectvars['VarsArray']);
			$qualifications[$_data[$model['main']]['id']]['certificate_id'] = $_data[$model['main']]['id'];
			$qualifications[$_data[$model['main']]['id']]['certificate_data_id'] = $_data[$model['sub']]['id'];

			if(date('Y-m-d',time()) >= $_data[$model['sub']]['next_certification']){
				$qualifications[$_data[$model['main']]['id']]['class'] = 'error';
				$summary['errors'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_certification'];
				$summary['errors'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_horizon'];
				$summary['errors'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
				$summary['errors'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
				$summary['errors'][$_data[$model['sub']]['id']]['examiner'] = $_data['Examiner'];
			}
			if(isset($_data[$model['sub']]['certified_file_error'])){
				$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_error';
				$summary['errors'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['certified_file_error'];
				$summary['errors'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
				$summary['errors'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
				$summary['errors'][$_data[$model['sub']]['id']]['examiner'] = $_data['Examiner'];
			}
			if($_data[$model['sub']]['valid_class'] == 'certification_not_valid_soon'){
				$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_warning';
				$summary['warnings'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_certification'];
				$summary['warnings'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_horizon'];
				$summary['warnings'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
				$summary['warnings'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
				$summary['warnings'][$_data[$model['sub']]['id']]['examiner'] = $_data['Examiner'];
			}
		}
		if($_data[$model['sub']]['active'] == 0){

//			$this->_controller->request->projectvars['VarsArray'][15] = $_data['Examiner']['id'];
//			$this->_controller->request->projectvars['VarsArray'][16] = $_data[$model['main']]['id'];
//			$this->_controller->request->projectvars['VarsArray'][17] = $_data[$model['sub']]['id'];

			$qualifications[$_data[$model['main']]['id']]['certificat'] = $_data[$model['main']]['certificat'];
			$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_deactive';
			$qualifications[$_data[$model['main']]['id']]['term'] = $this->_controller->request->projectvars['VarsArray'];
			$qualifications[$_data[$model['main']]['id']]['certificate_id'] = $_data[$model['main']]['id'];
			$qualifications[$_data[$model['main']]['id']]['certificate_data_id'] = $_data[$model['sub']]['id'];

			$summary['errors'][$_data[$model['sub']]['id']][] = __('There is no valide vision test.',true);
			$summary['errors'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
			$summary['errors'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
			$summary['errors'][$_data[$model['sub']]['id']]['examiner'] = $_data['Examiner'];

		}
		if($_data[$model['sub']]['valid_class'] == 'certification_valid'){

			$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_valide';
			$summary['hints'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_certification'];
			$summary['hints'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_horizon'];
			$summary['hints'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
			$summary['hints'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
			$summary['hints'][$_data[$model['sub']]['id']]['examiner'] = $_data['Examiner'];

		}

	 	$output['summary'] = $summary;
	 	$output['qualifications'] = $qualifications;

		return $output;
	}

        public function WelderEyecheckSummary($_data,$model) {

		if(count($_data) == 0){
			return array('summary' => array(),'qualifications' => array());
		}

		$summary = array('future' => array(),'futurenow' => array(),'errors' => array(),'warnings' => array(),'hints' => array());
		$qualifications = array();

		if($_data[$model['sub']]['active'] == 1 && $_data[$model['sub']]['deleted'] == 0){

			$this->_controller->request->projectvars['VarsArray'][15] = $_data['Welder']['id'];
			$this->_controller->request->projectvars['VarsArray'][16] = $_data[$model['main']]['id'];
			$this->_controller->request->projectvars['VarsArray'][17] = $_data[$model['sub']]['id'];

			$qualifications[$_data[$model['main']]['id']]['certificat'] = $_data[$model['main']]['certificat'];
			$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_valide';
			$qualifications[$_data[$model['main']]['id']]['term'] = $this->_controller->request->projectvars['VarsArray'];
			$qualifications[$_data[$model['main']]['id']]['termlink'] = implode('/',$this->_controller->request->projectvars['VarsArray']);
			$qualifications[$_data[$model['main']]['id']]['certificate_id'] = $_data[$model['main']]['id'];
			$qualifications[$_data[$model['main']]['id']]['certificate_data_id'] = $_data[$model['sub']]['id'];

			if(date('Y-m-d',time()) >= $_data[$model['sub']]['next_certification']){
				$qualifications[$_data[$model['main']]['id']]['class'] = 'error';
				$summary['errors'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_certification'];
				$summary['errors'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_horizon'];
				$summary['errors'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
				$summary['errors'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
				$summary['errors'][$_data[$model['sub']]['id']]['welder'] = $_data['Welder'];
			}
			if(isset($_data[$model['sub']]['certified_file_error'])){
				$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_error';
				$summary['errors'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['certified_file_error'];
				$summary['errors'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
				$summary['errors'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
				$summary['errors'][$_data[$model['sub']]['id']]['welder'] = $_data['Welder'];
			}
			if($_data[$model['sub']]['valid_class'] == 'certification_not_valid_soon'){
				$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_warning';
				$summary['warnings'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_certification'];
				$summary['warnings'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_horizon'];
				$summary['warnings'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
				$summary['warnings'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
				$summary['warnings'][$_data[$model['sub']]['id']]['welder'] = $_data['Welder'];
			}
		}
		if($_data[$model['sub']]['active'] == 0){

//			$this->_controller->request->projectvars['VarsArray'][15] = $_data['Welder']['id'];
//			$this->_controller->request->projectvars['VarsArray'][16] = $_data[$model['main']]['id'];
//			$this->_controller->request->projectvars['VarsArray'][17] = $_data[$model['sub']]['id'];

			$qualifications[$_data[$model['main']]['id']]['certificat'] = $_data[$model['main']]['certificat'];
			$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_deactive';
			$qualifications[$_data[$model['main']]['id']]['term'] = $this->_controller->request->projectvars['VarsArray'];
			$qualifications[$_data[$model['main']]['id']]['certificate_id'] = $_data[$model['main']]['id'];
			$qualifications[$_data[$model['main']]['id']]['certificate_data_id'] = $_data[$model['sub']]['id'];

			$summary['errors'][$_data[$model['sub']]['id']][] = __('There is no valide vision test.',true);
			$summary['errors'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
			$summary['errors'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
			$summary['errors'][$_data[$model['sub']]['id']]['welder'] = $_data['Welder'];

		}
		if($_data[$model['sub']]['valid_class'] == 'certification_valid'){

			$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_valide';
			$summary['hints'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_certification'];
			$summary['hints'][$_data[$model['sub']]['id']][] = $_data[$model['sub']]['time_to_next_horizon'];
			$summary['hints'][$_data[$model['sub']]['id']]['info'] = $_data[$model['sub']];
			$summary['hints'][$_data[$model['sub']]['id']]['certificate'] = $_data[$model['main']];
			$summary['hints'][$_data[$model['sub']]['id']]['welder'] = $_data['Welder'];

		}

	 	$output['summary'] = $summary;
	 	$output['qualifications'] = $qualifications;

		return $output;
	}

	public function SingleCertificateSummary($_data,$model) {

		if($model['main'] == 'DeviceCertificate' ||$model['main'] == 'ExaminerMonitoring'||$model['main'] == 'WelderMonitoring'  ){
			$_certificate_data_old_infos = $this->_controller->Qualification->DeviceCertification($_data,$model);
			$this->_controller->set('certificate_data_old_infos',$_certificate_data_old_infos);
		}

		if($model['main'] == 'Eyecheck'){
			$_certificate_data_old_infos = $this->_controller->Qualification->Eyechecks($_data);
			$this->_controller->set('certificate_data_old_infos',$_certificate_data_old_infos);
		}
                if($model['main'] == 'WelderCertificate' ){
 			$certificate_data_old_infos = $this->_controller->Qualification->WelderCertificatesSectors($_data);
                }
		if($model['main'] == 'Certificate'){

			$certificate_data_old_infos = $this->_controller->Qualification->CertificatesSectors($_data);

			foreach($certificate_data_old_infos[$_data[$model['main']]['sector']] as $_certificate_data_old_infos){

				if(!isset($_certificate_data_old_infos[$model['sub']]['certificate_id'] )) continue;

				if($_certificate_data_old_infos[$model['sub']]['certificate_id'] == $_data[$model['sub']]['certificate_id']){
					$this->_controller->set('certificate_data_old_infos',$_certificate_data_old_infos);
					break;
				}
			}
		}

		return $_certificate_data_old_infos;
	}

	public function DokuFiles($path,$examiner_id,$id,$files_id,$models) {

		if(!file_exists($path)) $dir = new Folder($path, true, 0755);

		$top= $models['top'];
                $main= $models['main'];
                $sub =$models['sub'];
                $file = $models['file'];

//		if(isset($this->_controller->request['data'][$models['top']]['file'])){
		if(isset($_FILES) && count($_FILES) > 0){

			App::uses('Sanitize', 'Utility');

			// Infos zur Datei
			$fileinfo = pathinfo($_FILES['file']['name']);

			$Microtime = microtime();
			$Microtime = str_replace(array('.',' '),'_',$Microtime);

			$filename_new = $examiner_id.'_'.$id.'_'.$Microtime.'_'.uniqid().'.'.$fileinfo['extension'];
			$this->_controller->Session->write('FileUploadErrors',$_FILES['file']['error']);
			$description = explode('.',$_FILES['file']['name']);
			$description = Sanitize::paranoid($description[0]);
			$orginal_name = Sanitize::escape($_FILES['file']['name']);

			move_uploaded_file($_FILES['file']['tmp_name'],$path.$filename_new);

			$dataFiles = array(
				'examiner_id' => $examiner_id,
				'parent_id' => $id,
				'name' => $filename_new,
				'originally_filename' => $orginal_name,
				'file_size' => filesize($path.$filename_new),
				'basename' => $path,
				'description' => $description,
				'user_id' => $this->_controller->Auth->user('id'),
				'testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
			);

			if(file_exists($path.$filename_new)){
				$this->_controller->$file->save($dataFiles);
			}

		}

		$certificate_data = array();

		if($models['top'] != NULL && $models['main'] != NULL && $models['sub'] != NULL){

			$certificate_option = array(
									'order' => array($models['main'].'.id DESC'),
									'conditions' => array(
										$models['main'].'.id' => $id,
										$models['main'].'.deleted' => 0,
										)
									);

			$certificate_data = $this->_controller->$top->$main->find('first',$certificate_option);
		}
		if($models['top'] != NULL && $models['main'] == NULL && $models['sub'] == NULL){

			$certificate_data_option = array(
									'order' => array($models['top'].'.id DESC'),
									'conditions' => array(
										$models['top'].'.id' => $id,
										$models['top'].'.deleted' => 0,
										)
									);

			$certificate_data = $this->_controller->$top->find('first',$certificate_data_option);
		}

		$files_id_array = array();

		if($files_id > 0){
			$this->_controller->request->projectvars['VarsArray']['15'] = $examiner_id;
			$this->_controller->request->projectvars['VarsArray']['16'] = $id;

			$here_array = explode('/',$this->_controller->request->here);
			$this->_controller->request->here = implode('/',array_merge($here_array,$this->_controller->request->projectvars['VarsArray']));

			$files_id_array = array($models['file'].'.id' => $files_id);

		}

		$certificate_files = $this->_controller->$file->find('all', array(
																'conditions' => array(
																	$files_id_array,
																	$models['file'].'.deleted' => 0,
																	$models['file'].'.parent_id' => $id,
																	$models['file'].'.testingcomp_id' => $this->_controller->Autorisierung->ConditionsTestinccomps(),
																	)
																)
															);


		if(count($certificate_files) > 0){
			foreach($certificate_files as $_key => $_certificate_files){

				if($_certificate_files[$models['file']]['file_size'] / 1024 < 100){
					$certificate_files[$_key][$models['file']]['file_size'] = round($_certificate_files[$models['file']]['file_size'] / 1024,3)  . ' ' . 'Kb';
				}
				else {
					$certificate_files[$_key][$models['file']]['file_size'] = round($_certificate_files[$models['file']]['file_size'] / 1024 / 1024,3)  . ' ' . 'Mb';
				}

				if(!file_exists($path.$_certificate_files[$models['file']]['name'])){
					$certificate_files[$_key][$models['file']]['error'] = __('File not found.',true);
				} else {
					$certificate_files[$_key][$models['file']]['realpath'] = $path . $certificate_files[$_key][$models['file']]['name'];
				}
			}
		}

		$outputarray['_data'] = $certificate_data;
		$outputarray['_files'] = $certificate_files;
		$outputarray['models'] = $models;

		return $outputarray;
	}

	public function DokuFilesWeldercomp($path,$weldingcomp_id,$id,$files_id,$models) {

		if(!file_exists($path)){
			$dir = new Folder($path, true, 0755);
		}

		if(isset($_FILES) && !empty($_FILES)){

			App::uses('Sanitize', 'Utility');

			// Infos zur Datei
			$fileinfo = pathinfo($_FILES['file']['name']);
			$filename_new = $weldingcomp_id.'_'.$id.'_'.time().'.'.$fileinfo['extension'];

			$this->_controller->Session->write('FileUploadErrors',$_FILES['file']['error']);
			$description = explode('.',$_FILES['file']['name']);

			$description = Sanitize::paranoid($description[0]);
			$orginal_name = Sanitize::escape($_FILES['file']['name']);

			move_uploaded_file($_FILES['file']['tmp_name'],$path.$filename_new);

			$dataFiles = array(
				'weldingcomp_id' => $weldingcomp_id,
				'parent_id' => $id,
				'name' => $filename_new,
				'originally_filename' => $orginal_name,
				'file_size' => $_FILES['file']['size'],
				'basename' => $path,
				'description' => $description,
				'user_id' => $this->_controller->Auth->user('id'),
				'testingcomp_id' => $weldingcomp_id,
			);

			if(file_exists($path.$filename_new)){
				$this->_controller->{$models['file']}->save($dataFiles);
			}

		}

		$certificate_data = array();
		$files_id_array = array();

		$certificate_files = $this->_controller->{$models['file']}->find('all', array(
			'conditions' => array(
				$models['file'].'.deleted' => 0,
				$models['file'].'.parent_id' => $id,
				$models['file'].'.testingcomp_id' => $this->_controller->Autorisierung->ConditionsTestinccomps(),
				)
			)
		);


		if(count($certificate_files) > 0){
			foreach($certificate_files as $_key => $_certificate_files){

				if($_certificate_files[$models['file']]['file_size'] / 1024 < 100){
					$certificate_files[$_key][$models['file']]['file_size'] = round($_certificate_files[$models['file']]['file_size'] / 1024,3)  . ' ' . 'Kb';
				}
				else {
					$certificate_files[$_key][$models['file']]['file_size'] = round($_certificate_files[$models['file']]['file_size'] / 1024 / 1024,3)  . ' ' . 'Mb';
				}

				if(!file_exists($path.$_certificate_files[$models['file']]['name'])){
					$certificate_files[$_key][$models['file']]['error'] = __('File not found.',true);
				} else {
					$certificate_files[$_key][$models['file']]['realpath'] = $path . $certificate_files[$_key][$models['file']]['name'];
				}
			}
		}

		$outputarray['_data'] = $certificate_data;
		$outputarray['_files'] = $certificate_files;
		$outputarray['models'] = $models;

		return $outputarray;
	}
        public function WeldingcompFileValidity ($Weldingcompfile){
            $summary = array('future' => array(),'futurenow' => array(),'errors' => array(),'warnings' => array(),'hints' => array());

            if (empty($Weldingcompfile)){
                $summary ['errors'] =  __('No file available',true);;
                $Weldingcompfile ['Weldingcompfile']['valid_class'] = 'certification_not_valid';
            }
            else{
                $nextCertification = $Weldingcompfile['Weldingcompfile']['date_of_expiry'];
               // $nextCertification = $this->datediff($expired,$Weldingcompfile['Weldingcompfile']['created'],3);
               // $Weldingcompfile['Weldingcompfile']['next_certification']= __('date of expiry').': '.$expired.'('.$nextCertification.')';


			$nextCertificationHorizon = date('Y-m-d', strtotime('-' . $Weldingcompfile['Weldingcompfile']['horizon'] . ' months', strtotime($nextCertification)));

			$datediffCertificatione = $this->_controller->Qualification->dateDiff($nextCertification,date('Y-m-d',time()));
			$datediffHorizon = $this->_controller->Qualification->dateDiff($nextCertificationHorizon,date('Y-m-d',time()));

			 $Weldingcompfile['Weldingcompfile']['next_certification'] = $nextCertification;
			 $Weldingcompfile['Weldingcompfile']['next_certification_date'] = __('next verification',true).': '.$nextCertification;
			 $Weldingcompfile['Weldingcompfile']['next_certification_horizon'] = $nextCertificationHorizon;
			 $Weldingcompfile['Weldingcompfile']['time_to_next_certification'] = __('This document is still valid for',true).': '.$datediffCertificatione;
			 $Weldingcompfile['Weldingcompfile']['time_to_next_horizon'] = __('For the next verification they are reminded in',true).': '.$datediffHorizon;



			if(strtotime($nextCertification) > strtotime(date('Y-m-d',time()))){
				 $Weldingcompfile['Weldingcompfile']['valid'] = 1;
				 $Weldingcompfile['Weldingcompfile']['valid_class'] = 'certification_valid';
			}
			else {
				 $Weldingcompfile['Weldingcompfile']['valid'] = 0;
				 $Weldingcompfile['Weldingcompfile']['valid_class'] = 'certification_not_valid';
				 $Weldingcompfile['Weldingcompfile']['time_to_next_certification'] = __('validty is exceeded',true) . ' ' . $nextCertification.'; '.$datediffCertificatione.' '.__('ago',true);


			}
			if(strtotime($nextCertificationHorizon) < strtotime(date('Y-m-d',time()))){
				 $Weldingcompfile['Weldingcompfile']['time_to_next_horizon'] = __('They were reminded on',true) . ' ' . $nextCertificationHorizon . '; ' . $datediffHorizon . ' ' . __('ago',true);
				 $Weldingcompfile['Weldingcompfile']['valid_class'] = 'certification_not_valid_soon';
			}
			if(strtotime($nextCertification) < strtotime(date('Y-m-d',time()))){
				 $Weldingcompfile['Weldingcompfile']['valid_class'] = 'certification_not_valid';
			}



			 $Weldingcompfile['Weldingcompfile']['certification_requested'] = null;

			if( !empty($Weldingcompfile)){

                                $_folder = 'weldingcomp_folder';
                                $modeltop = 'Weldingcompfile';

                               }

				$path = Configure::read($_folder) . $Weldingcompfile['Weldingcompfile']['testingcomp_id']. DS. 'documents' . DS;

				if(file_exists($path .  $Weldingcompfile['Weldingcompfile']['name'])){
					 $Weldingcompfile['Weldingcompfile']['certified_file_pfath'] = $path;
				}
				if(!file_exists($path .  $Weldingcompfile['Weldingcompfile']['name'])){

						 $Weldingcompfile['Weldingcompfile']['name'] = '';
						 $Weldingcompfile['Weldingcompfile']['certified_file_error'] = __('The uploadet file could not be found, pleace upload a new file.');
						 $Weldingcompfile['Weldingcompfile']['valid_class'] = 'certification_not_valid';

				}
			}
			if(empty( $Weldingcompfile['Weldingcompfile']['originally_filename'])){

				 $Weldingcompfile['Weldingcompfile']['certified_file_error'] = __('Please upload a file.');
				 $Weldingcompfile['Weldingcompfile']['valid_class'] = 'certification_not_valid';
			}

            return $Weldingcompfile;
        }


}
