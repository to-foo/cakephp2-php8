<?php
class EmailComponent extends Component {
	protected $_controller = null;

	public function initialize(Controller $controller) {

		App::uses('CakeEmail', 'Network/Email');

		$this->_controller = $controller;
	}
	/**
	* Starts up ExportComponent for use in the controller
	*
	* @param Controller $controller A reference to the instantiating controller object
	* @return void
	*/

	public function StopSendMail(){

		$Check = $this->_StopSendMail();
		return $Check;

	}

	protected function _StopSendMail(){

		if(Configure::check('StopSendingMails') === true) return true;
		else return false;

	}

	public function SendMassCollectPdf($MassSelectetIDs){


		if(Configure::check('sendmailreport') === false) return;
		if(Configure::read('sendmailreport') === false) return;
		if(Configure::check('SendAuto') === false) return;
		if(Configure::read('SendAuto') === false) return;
		$Step = 2;

//		if($MassSelect[0] != 'sign') return;

		// Funktioniert im Augenblick nur für Wacker Nünchritz
		// bei Bericht schließen mit Stufe 2
		$AclCheck = $this->_controller->Autorisierung->AclCheck(array(0 => 'reportnumbers',1 => 'signSupervisor'));

		if($AclCheck === false) return;

		if(count($MassSelectetIDs) == 0) return;
		if(count($MassSelectetIDs) > 25) return;

		$projectvars = $this->_controller->request->projectvars;

		foreach ($MassSelectetIDs as $key => $value) {

			$reportnumber = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $value)));

			if(count($reportnumber) == 0) continue;

			$this->_controller->request->projectvars['VarsArray'][0] = $reportnumber['Reportnumber']['topproject_id'];
			$this->_controller->request->projectvars['VarsArray'][1] = $reportnumber['Reportnumber']['cascade_id'];
			$this->_controller->request->projectvars['VarsArray'][2] = $reportnumber['Reportnumber']['order_id'];
			$this->_controller->request->projectvars['VarsArray'][3] = $reportnumber['Reportnumber']['report_id'];
			$this->_controller->request->projectvars['VarsArray'][4] = $reportnumber['Reportnumber']['id'];
			$this->_controller->request->projectvars['VarsArray'][8] = 2;

			$this->_controller->request->projectvars['projectID'] = $reportnumber['Reportnumber']['topproject_id'];;
			$this->_controller->request->projectvars['cascadeID'] = $reportnumber['Reportnumber']['cascade_id'];;
			$this->_controller->request->projectvars['orderID'] = $reportnumber['Reportnumber']['order_id'];;
			$this->_controller->request->projectvars['reportID'] = $reportnumber['Reportnumber']['report_id'];;
			$this->_controller->request->projectvars['reportnumberID'] = $reportnumber['Reportnumber']['id'];;
			$this->_controller->request->data['projectID'] = 1;
			$this->_controller->request->data['Reportnumber']['attachments'] = 1;

			$this->_controller->collectpdf();

		}

		$this->_controller->request->projectvars = $projectvars;

	}

	public function sendMailWithAttachment($MailData,$Data) {

		if($this->_StopSendMail() === true) return;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$supplierID = $this->_controller->request->projectvars['VarsArray'][2];

		$this->_controller->loadModel('Emailsetting');

		$this->_controller->Emailsetting->recursive = -1;
		$Emailsetting =  $this->_controller->Emailsetting->find('first',array('conditions' => array('Emailsetting.topproject_id' => $projectID)));

		$Message = null;
		$Message .= $MailData['Subject'] . "\n\n";
		$Message .= $MailData['ExpeditingsMessage'];
		$Message .= "\n\n" . $Emailsetting['Emailsetting']['footer_text'];

		$Subject = null;
		$Subject .= $MailData['Subject'];

		$Config = 'default';

		if(!empty($Emailsetting['Emailsetting']['config'])) $Config = $Emailsetting['Emailsetting']['config'];

		$Email = new CakeEmail();
		$Email->config($Config);
		$Email->from($MailData['FromMail']);
		$Email->to($MailData['ToMail']);
		$Email->attachments($MailData['Attachmets']);
		$Email->subject($Subject);

		if($Email->send($Message)) $Data['FlashMessages'][]  = array('type' => 'success','message' => __('The email has been sent to the following addresses:',true) . ' ' . implode(', ',$MailData['ToMail']));
		else $Data['FlashMessages'][]  = array('type' => 'error','message' => __('The email could not be sent, please try again.',true));

		return $Data;

	}

	public function sendStatusExpeditingMail($MailData,$Data) {

		if($this->_StopSendMail() === true) return;

		if(Configure::check('ExpeditingSendMails') == false) return false;
		if(Configure::read('ExpeditingSendMails') == false) return false;

		if(!is_array($MailData['ToMail'])) return false;
		if(count($MailData['ToMail']) == 0) return false;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$supplierID = $this->_controller->request->projectvars['VarsArray'][2];

		$this->_controller->loadModel('Emailsetting');

		$this->_controller->Emailsetting->recursive = -1;
		$Emailsetting =  $this->_controller->Emailsetting->find('first',array('conditions' => array('Emailsetting.topproject_id' => $projectID)));

		$Message = null;
		$Message .= $Emailsetting['Emailsetting']['header_text'] . "\n\n";
		$Message .= $MailData['Subject'] . "\n\n";
		$Message .= $MailData['ExpeditingsMessage'];
		$Message .= "\n\n" . $Emailsetting['Emailsetting']['footer_text'];

		$Subject = null;
		$Subject .= $MailData['Subject'];

		$Config = 'default';

		if(!empty($Emailsetting['Emailsetting']['config'])) $Config = $Emailsetting['Emailsetting']['config'];

		$Email = new CakeEmail();
		$Email->config($Config);

		try {
			$Email->from(array($MailData['FromMail']  => $MailData['FromName']))->to($MailData['ToMail'])->subject($Subject)->send($Message);
			return true;
		}
		catch(Exception $e) {
			return false;
		}

	}

	public function SendReportWithAttachments($AttachmentSend) {

		if($this->_StopSendMail() === true) return;

		unlink (Configure::read('root_folder') . DS . $this->_controller->request->data['PdfName']);
		$MaxFileSizeSingle = 5000000; // 10 MB
		$MaxFileSizeAll = 10000000; // 20 MB
		$FileSizeAll = 0;
		$MailConfig = 'default';
		$MailFrom = 'torsten.foth@mbq-gmbh.de';

		if(Configure::check('EmailConfig') === true) $MailConfig = Configure::read('EmailConfig');
		if(Configure::check('EmailConfigFrom') === true) $MailFrom = Configure::read('EmailConfigFrom');

		$text = "Sehr geehrte Damen und Herren anbei der Prüfbericht zur Übersicht. Diese E-Mail wurde automatisch verfasst.";
		$textwithattach = "Sehr geehrte Damen und Herren anbei der Prüfbericht zur Übersicht. An diesem Prüfbericht sind Anhänge enthalten. Diese E-Mail wurde automatisch verfasst";
		$reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];

	 	$options = array('conditions' => array(
	 			'Reportnumber.id' => $reportnumberID,
	 	));

		$this->_controller->Reportnumber->recursive = 1;
	 	$reportnumber = $this->_controller->Reportnumber->find('first', $options);
	 	$reportnumber = $this->_controller->Data->GetReportData($reportnumber,array('Generally','Specific','Evaluation'));

		$verfahren = $reportnumber['Testingmethod']['value'];
		$Verfahren = ucfirst($verfahren);
		$test = $this->_controller->Xml->DatafromXmlForReport($verfahren,$reportnumber);
		$testmodel = 'Report' . $Verfahren . 'Generally';

		$xml = $this->_controller->Xml->DatafromXml($verfahren,'file',ucfirst($verfahren));

		$EmailAdresses = $this->_controller->Reportnumber->getMailAdresses($xml, $reportnumber);

		if(count($EmailAdresses) == 0) return false;

		$projectID = $reportnumber['Reportnumber']['topproject_id'];
		$id = $reportnumber['Reportnumber']['id'];
		$savePath = Configure::read('report_folder') . $projectID . DS . 'files' . DS . $id . DS;
		$Subject = $this->_controller->request->data['PdfName'];

		$email = new CakeEmail();
		$email->config($MailConfig);

		$email->from($MailFrom);
		$email->to($EmailAdresses);

		$Attachments['Send'][$this->_controller->request->data['PdfName']] = array('data' => $this->_controller->request->data['PdfString'],'mimetype' => '0775application/pdf');

		if($AttachmentSend == 0){

			$email->attachments($Attachments['Send']);
			$email->subject($Subject);

			if(!$email->send($text)){

				CakeLog::write('error', '1 Email not send: '.$reportnumberID.' - '.print_r($EmailAdresses, true));
				return false;
			
			}

			return true;
		}

		$this->_controller->loadModel('Reportfile');
	 	$reportfiles = $this->_controller->Reportfile->find('all', array('conditions' => array('Reportfile.reportnumber_id' => $id)));

		if(count($reportfiles) == 0){

			$email->attachments($Attachments['Send']);
			$email->subject($Subject);

			if(!$email->send($text)){

				CakeLog::write('error', '2 Email not send: '.$reportnumberID.' - '.print_r($EmailAdresses, true));
				return false;
			
			}

			return true;
		}

		$reportfiles = $this->_controller->Data->CheckFileExists($reportfiles,$savePath);

		foreach ($reportfiles as $key => $value) {

			if(file_exists($savePath . $value['Reportfile']['name']) == false) continue;

			$pathinfo = pathinfo($savePath . $value['Reportfile']['name']);
			$filesize = filesize($savePath . $value['Reportfile']['name']);

			if($pathinfo['extension'] != 'pdf') continue;

			if($filesize > $MaxFileSizeSingle){
				$Attachments['ToLarge'][$value['Reportfile']['basename']] = array('size' => $filesize,'message' => __('to large to send',true));
				continue;
			}

			$FileSizeAll += $filesize;

			if($FileSizeAll > $MaxFileSizeAll){
				$Attachments['SendNext'][] = $value;
				continue;
			}

			$Attachments['Send'][$value['Reportfile']['basename']] = array('file' => $savePath . $value['Reportfile']['name'],'mimetype' => '0775application/pdf');
		}

		if(isset($Attachments['SendNext']) && count($Attachments['SendNext']) > 0){
			$Subject .= " " . __('Part 1',true);
		}

		$email->attachments($Attachments['Send']);
		$email->subject($Subject);

		if(!$email->send($textwithattach)){

			CakeLog::write('error', '3 Email not send: '.$reportnumberID.' - '.print_r($EmailAdresses, true));
			return false;
		
		}

		if(isset($Attachments['SendNext']) && count($Attachments['SendNext']) > 0){
			$Return = $this->_SendNextReportWithAttachments($Attachments,1,$EmailAdresses);
			if($Return === false) return false;
		}
		return true;

	}

	protected function _SendNextReportWithAttachments($Data,$Part,$EmailAdresses) {

		if($this->_StopSendMail() === true) return;

		$MaxFileSizeSingle = 5000000; // 10 MB
		$MaxFileSizeAll = 10000000; // 20 MB
		$FileSizeAll = 0;

		$MailConfig = 'gmail';
		$MailFrom = 'torsten.foth@mbq-gmbh.de';

		if(Configure::check('EmailConfig') === true) $MailConfig = Configure::read('EmailConfig');
		if(Configure::check('EmailConfigFrom') === true) $MailFrom = Configure::read('EmailConfigFrom');

		$projectID = $this->_controller->request->data['Reportnumber']['topproject_id'];
		$id = $this->_controller->request->data['Reportnumber']['id'];
		$savePath = Configure::read('report_folder') . $projectID . DS . 'files' . DS . $id . DS;

		$email = new CakeEmail();
		$email->config($MailConfig);

		$email->from($MailFrom);
		$email->to($EmailAdresses);

		foreach ($Data['SendNext'] as $key => $value) {

			$pathinfo = pathinfo($savePath . $value['Reportfile']['name']);
			$filesize = filesize($savePath . $value['Reportfile']['name']);

			if($pathinfo['extension'] != 'pdf') continue;

			$FileSizeAll += $filesize;

			if($FileSizeAll > $MaxFileSizeAll) {
				$Attachments['SendNext'][] = $value;
				continue;
			}

			$Attachments['Send'][$value['Reportfile']['basename']] = array('file' => $savePath . $value['Reportfile']['name'],'mimetype' => '0775application/pdf');

		}

		$Subject = $this->_controller->request->data['PdfName'];
		$Part++;
		$Subject .= ' ' . __('Part',true) . ' ' . $Part;

		$email->attachments($Attachments['Send']);
		$email->subject($Subject);

		if(!$email->send($textwithattach)){

			CakeLog::write('error', '4 Email not send: '.$reportnumberID.' - '.print_r($EmailAdresses, true));
			return false;
			if($Return === false) return false;

		}

		if(isset($Attachments['SendNext']) && count($Attachments['SendNext']) > 0){
			$Return = $this->_SendNextReportWithAttachments($Attachments,$Part,$EmailAdresses);
		}

		return true;
	}
}
