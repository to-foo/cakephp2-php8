<?php
class MoveDataComponent extends Component {
	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}
	/**
	* Starts up ExportComponent for use in the controller
	*
	* @param Controller $controller A reference to the instantiating controller object
	* @return void
	*/

	public function NatSortAndStringAndUrl($data){

		krsort($data['array']);

		$href = Router::url(array_merge(array('action' => 'move'),$this->_controller->request->projectvars['VarsArray']));

		$data['bread_array'][] = '<a href="' . $href . '" class="move_navigation" data-cascade="0">'.__('Start',true).'</a>';

		foreach ($data['array'] as $key => $value) {

			$data['bread_array'][] = '<a href="' . $href . '" class="move_navigation" data-cascade="'.$value['Cascade']['id'].'">'.$value['Cascade']['discription'].'</a>';

		}

		$Last = $data['array'];
		$Last = array_pop($Last); 

		if($Last['Cascade']['orders']);

		$results = Hash::extract($data['array'], '{n}.Cascade.discription');

		$data['string'] = implode(' > ',$results);
		$data['bread'] = implode(' > ',$data['bread_array']);

		return $data;
	}

	public function CurrentCascadeBreadcrump($data){

		if(!isset($this->_controller->request->data['Reportnumber'])) return $data;
		if(!isset($this->_controller->request->data['Reportnumber']['cascade_id'])) return $data;

		if(empty($this->_controller->request->data['Reportnumber']['cascade_id'])){
			$data['TargedCascadeTree']['bread'] = '';
			return $data;
		}

		$CascadeId = $this->_controller->request->data['Reportnumber']['cascade_id'];

		$Cascade = $this->_controller->Cascade->find('first',array(
				'conditions' => array(
					'Cascade.id' => $CascadeId
				)
			)
		);

		$data['TargedCascadeTree']['array'] = $this->_controller->Navigation->CascadeGetBreads($Cascade['Cascade']['id']);
		$data['TargedCascadeTree'] = $this->NatSortAndStringAndUrl($data['TargedCascadeTree']);

		return $data;
	}

	public function CurrentCascade($data){

		if(!isset($this->_controller->request->data['Reportnumber']['cascade_id'])){

			//erst alle cascade auf level 0 holen
			$cascadefirst = $this->_controller->Cascade->find('list',array(
				'fields'=> array('id','discription'),
				'order'=> array('discription'),
				'conditions' => array(
					'Cascade.level' => 0
					)
				)
			);

			$data['CascadeList'] = $cascadefirst;
			$data['Cascade'] = array();

			return $data;

		}

		if($this->_controller->request->data['Reportnumber']['cascade_id'] == 0){

			//erst alle cascade auf level 0 holen
			$Cascade = $this->_controller->Cascade->find('list',array(
				'fields'=> array('id','discription'),
				'order'=> array('discription'),
				'conditions' => array(
					'Cascade.level' => 0
					)
				)
			);

			// Wird im Json sonst nach dem Key sortiert
			foreach ($Cascade as $key => $value) {
				$Liste[] = array('key' => $key,'value' => $value);
			}

			$data['CascadeList'] = $Liste;

			$data['Cascade'] = array();

			return $data;

		}

		$CascadeId = $this->_controller->request->data['Reportnumber']['cascade_id'];

		//erst alle cascade auf level 0 holen
		$Cascade = $this->_controller->Cascade->find('list',array(
			'fields'=> array('id','discription'),
			'order'=> array('discription'),
			'conditions' => array(
				'Cascade.parent' => $CascadeId
				)
			)
		);

		// Wenn die letzte Kaskade erreicht ist
		// wird die Übergeordnete für das Dropdown geladen
		if(count($Cascade) == 0){
			$CascadeLast = $this->_controller->Cascade->find('first',array(
				'conditions' => array(
					'Cascade.id' => $CascadeId
					)
				)
			);

			if($CascadeLast['Cascade']['orders'] == 1){

				$Cascade = $this->_controller->Cascade->find('list',array(
					'fields'=> array('id','discription'),
					'order'=> array('discription'),
					'conditions' => array(
						'Cascade.parent' => $CascadeLast['Cascade']['parent']
						)
					)
				);
			} elseif($CascadeLast['Cascade']['orders'] == 0){

				$Cascade = $this->_controller->Cascade->find('list',array(
					'fields'=> array('id','discription'),
					'order'=> array('discription'),
					'conditions' => array(
						'Cascade.id' => $CascadeLast['Cascade']['id']
						)
					)
				);
			}
		}

		$Liste = array();

		// Wird im Json sonst nach dem Key sortiert
		foreach ($Cascade as $key => $value) {
			$Liste[] = array('key' => $key,'value' => $value);
		}


		$data['CascadeList'] = $Liste;

		$Cascade = $this->_controller->Cascade->find('first',array(
			'conditions' => array(
				'Cascade.id' => $CascadeId
				)
			)
		);

		$data['Cascade'] = $Cascade['Cascade'];

		return $data;

	}

	public function CurrentHiddenOrder($data){

		if(!isset($this->_controller->request->data['Reportnumber'])) return $data;
		if(!isset($this->_controller->request->data['Reportnumber']['cascade_id'])) return $data;
		if(empty($this->_controller->request->data['Reportnumber']['cascade_id'])) return $data;
		if(!isset($data['Cascade'])) return $data;
		if(count($data['Cascade']) == 0) return $data;
		if(isset($data['Order'])) return $data;
		if(isset($data['Orders'])) return $data;

		$CascadeId = $data['Cascade']['id'];

		$Cascade = $this->_controller->Cascade->find('first',array(
				'conditions' => array(
					'Cascade.parent' => $CascadeId
				)
			)
		);

		if(count($Cascade) == 1) return $data;

		$options = array(
			'conditions' => array(
				'Order.cascade_id'
			),
		);

		$this->_controller->Order->recursive = -1;

		$Order  = $this->_controller->Order->find('first',$options);

		if(count($Order) == 0){

			$Cascade = $this->_controller->Cascade->find('first',array(
					'conditions' => array(
						'Cascade.parent' => $data['Cascade']['id']
					)
				)
			);

			if(count($Cascade) == 0) $data['order_deactive'] = 1;

			return $data;
		}

		$data['Order'] = $Order['Order'];

		$Liste = array();

		// Wird im Json sonst nach dem Key sortiert
		$Liste[] = array('key' => $Order['Order']['id'],'value' => $Order['Order']['auftrags_nr']);
		$data['Orders'] = $Liste;

		$data['order_deactive'] = 1;

		return $data;
	}

	public function CurrentOrder($data){

		if(!isset($this->_controller->request->data['Reportnumber'])) return $data;
		if(!isset($this->_controller->request->data['Reportnumber']['cascade_id'])) return $data;
		if(empty($this->_controller->request->data['Reportnumber']['cascade_id'])) return $data;
		if(!isset($data['Cascade'])) return $data;
		if(count($data['Cascade']) == 0) return $data;
		if($data['Cascade']['orders'] == 0) return $data;

 	 	$Orders = $this->_controller->Order->find('list',array(
		 'fields'=>array('id','auftrags_nr'),
		 'conditions' => array(
			 'Order.cascade_id' =>$data['Cascade']['id']
		 		)
	 		)
 		);

		$Liste = array();

		// Wird im Json sonst nach dem Key sortiert
		foreach ($Orders as $key => $value) {
			$Liste[] = array('key' => $key,'value' => $value);
		}

		$data['Orders'] = $Liste;

		if(empty($this->_controller->request->data['Reportnumber']['order_id'])) return $data;
		if($this->_controller->request->data['Reportnumber']['order_id'] == 0) return $data;

		$OrderId = $this->_controller->request->data['Reportnumber']['order_id'];

		$this->_controller->Order->recursive = -1;

		$Order = $this->_controller->Order->find('first',array(
		 'conditions' => array(
			 'Order.id' =>$OrderId
		 		)
	 		)
 		);

		$data['Order'] = $Order['Order'];

		$data['TargedCascadeTree']['bread'] .= ' > ' . $data['Order']['auftrags_nr'];
		$data['TargedCascadeTree']['string'] .= ' > ' . $data['Order']['auftrags_nr'];

		return $data;
	}

	public function CheckReportnumberHandling($data){

		if(Configure::check('GlobalReportNumbers') != false && $data['Reportnumber']['Reportnumber']['status'] > 0){

			if(!Configure::check('MoveCloseReports')){

				$this->_controller->Flash->error(__('Der Prüfbericht ist geschlossen und kann nicht mehr verschoben werden.',true), array('key' => 'error'));
				$data['stop_moving'] = 1;
	
				return $data;	

			}
		}

		if(Configure::check('GlobalReportNumbers') == false) return $data;

		switch(strtolower(Configure::read('GlobalReportNumbers'))) {

			case 'topproject':

			$data['global_report_numbers'] = 1;
			$this->_controller->Flash->error(__('ACHTUNG! Beim Verschieben außerhalb eines Projektes, erhält dieser Prüfbericht eine neue Prüfberichtsnummer.',true), array('key' => 'error'));

			break;

			case 'order':

			$data['global_report_numbers'] = 1;
			$this->_controller->Flash->error(__('ACHTUNG! Beim Verschieben außerhalb eines Auftrages, erhält dieser Prüfbericht eine neue Prüfberichtsnummer.',true), array('key' => 'error'));

			break;

			case 'report':

			$data['global_report_numbers'] = 1;
			$this->_controller->Flash->error(__('ACHTUNG! Beim Verschieben erhält dieser Prüfbericht eine neue Prüfberichtsnummer.',true), array('key' => 'error'));

			break;

		}

		return $data;
	}

	public function CurrentReportsNoOrders($data){

		if(isset($data['Report'])) return $data;
		if(!isset($data['order_deactive'])) return $data;
		if($data['order_deactive'] != 1) return $data;

		$data = $this->__CurrentReports($data,$data['Cascade']['topproject_id']);

		$data['Order']['id'] = 0;

		return $data;
	}

	public function CurrentReports($data){

		if(!isset($data['Order'])) return $data;
		if(count($data['Order']) == 0) return $data;

		$data = $this->__CurrentReports($data,$data['Order']['topproject_id']);

		return $data;
	}

	protected function __CurrentReports($data,$TopprojectId){

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];


		$reportsid = $this->_controller->ReportsTopprojects->find('all',array(
			'fields'=>array(
				'ReportsTopprojects.topproject_id',
				'ReportsTopprojects.report_id'
			),
			'conditions' => array(
					'topproject_id' => $TopprojectId
				)
			)
		);

		$reportsid = Hash::extract($reportsid, '{n}.ReportsTopprojects.report_id');

		$reports =  $this->_controller->Report->find('list',array(
			'fields'=>array(
				'Report.id',
				'Report.name'),
			'conditions' => array(
				'Report.id'=>$reportsid
				)
			)
		);

		$options = array('conditions' => array('Reportnumber.id' => $id));

		$this->_controller->Reportnumber->recursive = -1;

		$Reportnumber = $this->_controller->Reportnumber->find('first',$options);




		$this->_controller->loadModel('TestingmethodsReports');

		$Liste = array();

		// Wird im Json sonst nach dem Key sortiert
		foreach ($reports as $key => $value) {

			$options = array(
				'conditions' => array(
					'TestingmethodsReports.report_id' => $key,
					'TestingmethodsReports.testingmethod_id' => $Reportnumber['Reportnumber']['testingmethod_id']
				)
			);

			$TestingmethodsReports = $this->_controller->TestingmethodsReports->find('first',$options);

			if(count($TestingmethodsReports) == 0) continue;

			$Liste[] = array('key' => $key,'value' => $value);
		}

		if(count($Liste) == 0) $data['Messages']['Reports'] = array(
			'message' => __('Es existiert keine passende Prüfberichtsmappe für den zu verschiebenden Prüfbericht.',true),
			'type' => 'error'
		);

		$data['Reports'] = $Liste;

		if(empty($this->_controller->request->data['Reportnumber']['report_id'])) return $data;
		if($this->_controller->request->data['Reportnumber']['report_id'] == 0) return $data;

		$ReportId = $this->_controller->request->data['Reportnumber']['report_id'];

		$this->_controller->Report->recursive = -1;

		$Report =  $this->_controller->Report->find('first',array(
			'conditions' => array(
				'Report.id' => $ReportId
				)
			)
		);

		$data['Report'] = $Report['Report'];

		$data['TargedCascadeTree']['bread'] .= ' > ' . $data['Report']['name'];
		$data['TargedCascadeTree']['string'] .= ' > ' . $data['Report']['name'];

		return $data;

	}

	public function MoveReport($data){
		if(!isset($this->_controller->request->data['save'])) return $data;
		if($this->_controller->request->data['save'] != 1) return $data;
		if(!isset($data['Report'])) return $data;
		if(!isset($data['Order'])) return $data;
		if(!isset($data['Cascade'])) return $data;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];

		$GlobalReportNumbers = array();

		$data['savearray']['id'] = $id;
		$data['savearray']['report_id'] = $data['Report']['id'];
		$data['savearray']['cascade_id'] = $data['Cascade']['id'];
		$data['savearray']['topproject_id'] = $data['Cascade']['topproject_id'];

		if(isset($data['Order']['id'])) $data['savearray']['order_id'] = $data['Order']['id'];
		else $data['savearray']['order_id'] = 0;


		if(Configure::check('GlobalReportNumbers')) {

			$newnumber = 1;

			switch(strtolower(Configure::read('GlobalReportNumbers'))) {

				case 'topproject':
				$GlobalReportNumbers['Reportnumber.topproject_id'] = $data['savearray']['topproject_id'];
				break;

				case 'report':
				$GlobalReportNumbers['Reportnumber.report_id'] = $data['savearray']['report_id'];
				$GlobalReportNumbers['Reportnumber.topproject_id'] = $data['savearray']['topproject_id'];
				break;
	
				case 'order':
				$GlobalReportNumbers['Reportnumber.report_id'] = $data['savearray']['report_id'];
				$GlobalReportNumbers['Reportnumber.topproject_id'] = $data['savearray']['topproject_id'];
				$GlobalReportNumbers['Reportnumber.order_id'] = $data['savearray']['order_id'];
				break;

				default:
				$newnumber = 0;
				break;
			}

			$options = array(
				'conditions' => $GlobalReportNumbers,
				'order' => 'Reportnumber.id DESC',
				'limit' => 5
			);

			if($newnumber == 1) {
				$data =	$this->_MoveReportChangeNumber($data,$options);
			}else{
				$data = $this->_MoveReportGlobal($data);
			}
		}

		if(isset($data['order_deactive']) && $data['order_deactive'] == 1) $data['savearray']['order_id'] = 0;

		$Url = implode('/',array(
			$data['savearray']['topproject_id'],
			$data['savearray']['cascade_id'],
			$data['savearray']['order_id'],
			$data['savearray']['report_id'],
			$data['savearray']['id']
			)
		);

		if($this->_controller->Reportnumber->save($data['savearray'])){

			$this->_controller->Autorisierung->Logger($data['savearray']['id'],$data['savearray']);

			$Url = implode('/',array(
				$data['savearray']['topproject_id'],
				$data['savearray']['cascade_id'],
				$data['savearray']['order_id'],
				$data['savearray']['report_id'],
				$data['savearray']['id']
				)
			);

			$data['FormName']['controller'] = 'reportnumbers';
			$data['FormName']['action'] = 'show';
			$data['FormName']['terms'] = $Url;
			$data['FormName']['url'] = 'reportnumbers/view/' .$Url;

			$this->_controller->Flash->success(__('The Report was moved from',true) . ' ' . $data['CascadeTree']['string'], array('key' => 'success'));

			return $data;

		} else {

			return $data;

		}

		return $data;

	}

	public function MoveSigns($data){

		if(!isset($this->_controller->request->data['save'])) return $data;
		if($this->_controller->request->data['save'] != 1) return $data;
		if(!isset($data['Report'])) return $data;
		if(!isset($data['Order'])) return $data;
		if(!isset($data['Cascade'])) return $data;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];

		$this->_controller->loadModel('Sign');

		$Signs = array();
		$Signs = $this->_controller->Sign->find('all',array('conditions'=>array('reportnumber_id' => $id)));

		if(count($Signs) == 0) return $data;

		foreach ($Signs as $skey => $svalue) {

			if(empty($svalue['Sign'])) continue;
			if(!isset($svalue['Sign']['Signatory'])) continue;

			$typ = $svalue['Sign']['Signatory'];
			$colors = array('orginal','colored');

			foreach ($colors as $ckey => $color) {

				if((Configure::check('SignatorySaveMethode') && Configure::read('SignatorySaveMethode') == 'file') || !Configure::check('SignatorySaveMethode')){

					// Daten kommen aus Datein
					$report_id_chiper = bin2hex(Security::cipher($id, Configure::read('SignatoryHash')));
					$project_id_chiper = bin2hex(Security::cipher($projectID, Configure::read('SignatoryHash')));
					$path = Configure::read('SignatoryPfad') . $project_id_chiper . DS . $report_id_chiper . DS . $typ . DS . $color . DS . $report_id_chiper;

					if(!file_exists($path)) continue;

					$filecontent = file_get_contents($path);
					$newreport_id_chiper = bin2hex(Security::cipher($id, Configure::read('SignatoryHash')));
					$newproject_id_chiper = bin2hex(Security::cipher($data['Order']['topproject_id'], Configure::read('SignatoryHash')));

					$newpath =  Configure::read('SignatoryPfad') . $newproject_id_chiper . DS . $newreport_id_chiper . DS . $typ . DS;

					if(!file_exists($newpath)){

						$dir_orginal = new Folder($newpath . $color, true, 0755);

						$file_orginal = new File($newpath .  $color . DS . $newreport_id_chiper);

						$file_orginal->write($filecontent);
						$file_orginal->close();

					}

					$svalue['Sign']['topproject_id'] = $data['Order']['topproject_id'];
					$svalue['Sign']['report_id'] = $data['Report']['id'];

					if(isset($data['savearray']['order_id']) && !empty($data['savearray']['order_id'])) $svalue['Sign']['order_id'] = $savearray['order_id'];

					$this->_controller->Sign->save($svalue['Sign']);

				}
			}
		}

		return $data;
	}

	public function MoveFiles($data){

		if(!isset($this->_controller->request->data['save'])) return $data;
		if($this->_controller->request->data['save'] != 1) return $data;
		if(!isset($data['Report'])) return $data;
		if(!isset($data['Order'])) return $data;
		if(!isset($data['Cascade'])) return $data;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$id = $this->_controller->request->projectvars['VarsArray'][4];

		$datatype = array('Reportfile'=>'files','Reportimage'=>'images');

		foreach ($datatype as $dkey => $dvalue) {

			$this->_controller->loadModel($dkey);

			$datafiles = $this->_controller->$dkey->find('all',array('conditions'=>array($dkey.'.reportnumber_id' => $id)));

			if(count($datafiles) == 0) continue;

			foreach ($datafiles as $datas_key => $datas_value) {

				$savePath = Configure::read('report_folder') . $projectID . DS . $dvalue . DS . $id . DS . $datas_value[$key]['name'];

				if(file_exists($savePath)){

					$newpath = Configure::read('report_folder').$tpid.DS.$dvalue;

					if(!is_dir($newpath)) mkdir($newpath, 0744);

					rename($savePath,$newpath.DS.$id.DS.$datas_value[$key]['name']);
				}
			}
		}

		return $data;
	}

	protected function _MoveReportGlobal($data){
		//alles bleibt wie es ist
		return $data;
	}

	protected function _MoveReportChangeNumber($data,$options){

		$options['conditions']['year'] = date('Y');
		$options['conditions']['cascade_id'] = $data['Cascade']['id'];

		$reportnumberlast  = $this->_controller->Reportnumber->find('first',$options);

		if(!empty($reportnumberlast)){
			$data['savearray']['number'] = $reportnumberlast['Reportnumber']['number']+1;
			$data['savearray']['year'] = $reportnumberlast['Reportnumber']['year'];
		} else {
			$data['savearray']['number'] = 1;
			$data['savearray']['year'] = date('Y');
		}

		return $data;
	}
}
