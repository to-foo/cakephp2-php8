<?php
class SigningComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function UpdateReportnumberData($Data){

		if(isset($Data['error'])) return $Data;

		// nacheinander unterschreiben als Standard
		$SignatoryCascading = true;
		$SignatoryAfterPrinting = true;
		$SignatoryClosing = true;

		if(Configure::check('SignatoryCascading')) $SignatoryCascading = Configure::read('SignatoryCascading');
		if(Configure::check('SignatoryAfterPrinting')) $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
		if(Configure::check('SignatoryClosing')) $SignatoryClosing = Configure::read('SignatoryClosing');

		$data_status['Reportnumber']['id'] = $Data['Reportnumber']['id'];

		if($SignatoryClosing == true) {
			$data_status['Reportnumber']['status'] = $Data['Signatory']['Signatory'];
			$data_status['Reportnumber']['revision_progress'] = 0;
			$this->_controller->Session->delete('revision.' . $Data['Reportnumber']['id']);
		}

		if(Configure::check('SignatoryKeepOpen') && Configure::read('SignatoryKeepOpen') >= $Data['Signatory']['Signatory'] && $Data['Signatory']['Signatory'] > $Data['Reportnumber']['status']) {
			$data_status['Reportnumber']['status'] = 0;
		}

		if(isset($Data['Reportnumber']['revision_write']) && $Data['Reportnumber']['revision_write'] == 1){
			$data_status['Reportnumber']['revision_progress'] = 0;
			$data_status['Reportnumber']['print'] = 0;
			$this->_controller->Session->delete('revision.' . $Data['Reportnumber']['id']);
		}

		if($SignatoryClosing == false) $data_status['Reportnumber']['status'] = $Data['Reportnumber']['status'];

		$this->_controller->Autorisierung->Logger($Data['Signatory']['Id'],array($Data['Signatory']['Signatory']));

		$Reportnumber = $this->_controller->Reportnumber->save($data_status);

		// diese Array wird der Revison übergeben, wenn nötig
		$TransportArray['model'] = 'Sign';
		$TransportArray['row'] = null;
		$TransportArray['this_value'] = $Data['Signatory']['Signatory'];
		$TransportArray['last_id_for_radio'] = null;
		$TransportArray['table_id'] = $Data['Sign']['id'];
		$TransportArray['last_value'] = $Data['Signatory']['Signatory'];

		if($Data['Reportnumber']['revision_progress'] > 0){
			$this->_controller->Data->SaveRevision($Data,$TransportArray,'sign/add');
		}

		$this->_controller->Reportnumber->recursive= 1;
		$reportnumberForArchiv =  $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $Data['Signatory']['Id'])));
		$reportsarchiv = $reportnumberForArchiv;
		$Verfahren = ucfirst($reportsarchiv['Testingmethod']['value']);

		//Unterschriftendatum für das entsprechende Feld
		if(Configure::check('SetDateFromSignatory') && Configure::read('SetDateFromSignatory')== true && isset($Data['Signatory'])){
			$this->_controller->Data->SetDateFromSignatory($Data['Reportnumber']['id'],$Verfahren,$Data['Signatory']);
		}

		// solange der Prüfbericht nicht geschlossen ist wird das Archiv aktualisiert

		$this->_controller->Data->Archiv($reportsarchiv['Reportnumber']['id'],$Verfahren);

		$Data['Reportsarchiv'] = $reportsarchiv;

		if($Data['Signatory']['Signatory'] > 1 &&  Configure::check('WebserviceImport') && Configure::read('WebserviceImport') == true) {
			$this->_controller->Soap->SendFromReport($reportsarchiv,$reportsarchiv['Testingmethod']['value']);
		}

		if(Configure::check('SendTicketReport') && (Configure::read('SendTicketReport')== true)){
			$this->_controller->Rest->SendReport($Data['Reportsarchiv']);
		}
		//für Webservice Daten zurück ans Zielsystem schicken (In diesem Fall Abacus_______________________
		if($Data['Signatory']['Signatory'] > 1 &&  Configure::check('WebserviceImport') && Configure::read('WebserviceImport') == true) {
			$this->_controller->Soap->SendFromReport($reportsarchiv,$reportsarchiv['Testingmethod']['value']);
		}
		//__________________________________________________________________________

		if(Configure::check('sendmailreport') && Configure::read('sendmailreport') == true & Configure::check('sendreportminstatus')){
			if($Data['Signatory']['Signatory'] >= Configure::read('sendreportminstatus')) $this->_controller->collectpdf(1);
		}


		return $Data;
	}

// ----------------------------------------------------------------
// Testing Methods
// ----------------------------------------------------------------
	public function UpdateReportnumberDataTest($Data,$Id){

		if(isset($Data['error'])) return $Data;

		$this->_controller->Reportnumber->recursive= 1;
		$reportnumberForArchiv =  $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $Id)));
		$reportsarchiv = $reportnumberForArchiv;
		$Verfahren = ucfirst($reportsarchiv['Testingmethod']['value']);

		$Data['Reportsarchiv'] = $reportsarchiv;

		if(Configure::check('SendTicketReport') && (Configure::read('SendTicketReport')== true)){
			$this->_controller->Rest->SendReportTest($Data['Reportsarchiv']);
		}

		return $Data;
	}
// END

	public function SaveSign($Data){

		if(isset($Data['error'])) return $Data;
		if(!isset($Data['Sign'])) return $Data;
		if(count($Data['Sign']) == 0) return $Data;

		$data['id'] = $Data['Sign']['id'];
		$data['width'] = $Data['Signatory']['width'];
		$data['height'] = $Data['Signatory']['height'];
		$data['reportnumber_id'] = $Data['Reportnumber']['id'];
		$data['signatory'] = $Data['Signatory']['Signatory'];
		$data['user_id'] = $this->_controller->Auth->user('id');

		if($this->_controller->Sign->save($data)){

			$Sign = $this->_controller->Sign->find('first',array(
				'conditions' => array(
					'Sign.id' => $data['id'],
				)
			)
		);

		$Data['Sign'] = $Sign['Sign'];

	} else {
		$Data['error'] = __('Die Signatur konnte nicht gespeichert werden.',true);
	}

	return $Data;
}

public function CreateSign($Data){

	if(isset($Data['error'])) return $Data;

	$this->_controller->loadModel('Sign');

	$Sign = $this->_controller->Sign->find('first',array(
		'conditions' => array(
			'Sign.reportnumber_id' => $Data['Reportnumber']['id'],
			'Sign.signatory' => $Data['Signatory']['Signatory']
		)
	)
);

if(count($Sign) == 0){

	$this->_controller->Sign->create();
	$this->_controller->Sign->save(array('reportnumber_id' => $Data['Reportnumber']['id'],'signatory' => $Data['Signatory']['Signatory']));
	$SignId = $this->_controller->Sign->getLastInsertID();

	$Sign = $this->_controller->Sign->find('first',array(
		'conditions' => array(
			'Sign.id' => $SignId,
		)
	)
);

$Data['Sign'] = $Sign['Sign'];

} else {

	$Data['Sign'] = $Sign['Sign'];

}

if(!Configure::check('SignatorySaveMethode')){
	$Data = $this->__SaveMethodeFile($Data);
}

if(Configure::check('SignatorySaveMethode') && Configure::read('SignatorySaveMethode') == 'file'){
	$Data = $this->__SaveMethodeFile($Data);
}

if(Configure::check('SignatorySaveMethode') && Configure::read('SignatorySaveMethode') == 'data'){
	$Data = $this->__SaveMethodeSql($Data);
}

return $Data;
}

public function CreateImage($Data){

	if(!is_array($Data['Signatory'])) return $Data;

	$Sign = $Data['Signatory'];

	if(!is_array($Sign)) return $Data;
	if(count($Sign) != 5) return $Data;

	$color = $this->_controller->Session->read('Sign.Color');
	$Signatory = $this->_controller->Session->read('Sign');

	$color = str_replace('rgb(', '', $color);
	$color = str_replace(')', '', $color);
	$color = explode(',',$color);

	if(count($color) == 3){
		$ncolor['r'] = trim($color[0]);
		$ncolor['g'] = trim($color[1]);
		$ncolor['b'] = trim($color[2]);
	} else {
		$ncolor['r'] = 0;
		$ncolor['g'] = 0;
		$ncolor['g'] = 0;
	}

	$data = base64_decode($Signatory['Image']);
	$im = imagecreatefromstring($data);

	$im_width = imagesx($im);
	$im_height = imagesy($im);

	$Data['Signatory']['width'] = $im_width;
	$Data['Signatory']['height'] = $im_height;

	imagealphablending($im, false);

	for ($x = imagesx($im); $x--;) {
		for($y = imagesy($im); $y--;) {
			$c = imagecolorat($im, $x, $y);
			$rgb['r'] = ($c >> 16) & 0xFF;
			$rgb['g'] = ($c >> 8) & 0xFF;
			$rgb['b'] = ($c & 0xFF);
			$rgb['t'] = ($c >> 24) & 0x7F;
			if($rgb['t'] < 127){
				$colorB = imagecolorallocatealpha($im, $ncolor['r'], $ncolor['g'], $ncolor['b'], $rgb['t']);
				imagesetpixel($im, $x, $y, $colorB);
			}
		}
	}

	imagesavealpha($im, true);

	ob_start();
	imagepng($im);
	$colored_image = ob_get_contents();
	ob_end_clean();
	imagedestroy($im);

	$Data['colored_image'] = $colored_image;

	return $Data;
}

public function ConvertBase64($Data){

	if(!isset($Data['colored_image'])) return $Data;

	$Data['colored_image'] = 'data:image/png;base64,' . base64_encode($Data['colored_image']);

	return $Data;

}

private function __SaveMethodeFile($Data){

	$secretImage = Security::cipher($Data['Signatory']['Image'], Configure::read('SignatoryHash'));
	$secretColoredImage = Security::cipher(base64_encode($Data['colored_image']), Configure::read('SignatoryHash'));

	$report_id_chiper = bin2hex(Security::cipher($Data['Reportnumber']['id'], Configure::read('SignatoryHash')));
	$project_id_chiper = bin2hex(Security::cipher($Data['Reportnumber']['topproject_id'], Configure::read('SignatoryHash')));

	$path = Configure::read('SignatoryPfad') . $project_id_chiper . DS . $report_id_chiper . DS . $Data['Signatory']['Signatory'] . DS;

	// Wenn nicht vorhanden Ordner erzeugen
	if(!file_exists($path)){

		$dir_orginal = new Folder($path . 'orginal', true, 0755);
		$dir_colored = new Folder($path . 'colored', true, 0755);

		$file_orginal = new File($path . 'orginal' . DS . $report_id_chiper);
		$file_colored = new File($path . 'colored' . DS . $report_id_chiper);

		$file_orginal->write($secretImage);
		$file_orginal->close();

		$file_colored->write($secretColoredImage);
		$file_colored->close();

	} else {
		$Data['error'] = __('Die Signatur konnte nicht gespeichert werden. Eine vorhandene Unterschrift konnte nicht gelöscht werden, wenden sie sich an einen Administrator.',true);
	}

	return $Data;

}

private function __SaveMethodeSql($Data){

	$secretImage = Security::cipher($Data['Signatory']['Image'], Configure::read('SignatoryHash'));
	$secretColoredImage = Security::cipher(base64_encode($Data['colored_image']), Configure::read('SignatoryHash'));

	$Data['Reportnumber']['image_orginal'] = $secretImage;
	$Data['Reportnumber']['image'] = $secretColoredImage;

	return $Data;

}
public function ValidateSign($step, $errors,$reportnumberID,$reportnumber,$xml,$Generally){

	if($reportnumber['Reportnumber']['status'] > 0) return $errors;

	$this->_controller->loadModel('Sign');
	$Signs = $this->_controller->Sign->find('all',array('conditions'=>array('reportnumber_id'=>$reportnumberID)));

	if(count($Signs) >= $step) {
		return $errors;
	}else{
		$signmembers = array(1=>'examiner',2=>'supervision',3=>'supervisor_company',4=>'third_part');

		foreach ($signmembers as $key => $value) {
			if ($key > $step) continue;
			$sign = $this->_controller->Sign->find('first',array('conditions'=>array('reportnumber_id'=>$reportnumberID,'signatory'=>$key)));

			if(empty($sign)){
				$errors['Sign'][$value][] = array(
					'reportnumber'=> array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id']),
					'model'=>'Sign',
					'field'=>$value,
					'message'=> __('For this field is a signature needed'),
					'description' =>$xml['settings']->$Generally->$value->discription,
				);
			}
		}
		return $errors;
	}
}
}
