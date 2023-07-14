<?php
class ImageComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function PutAdditionalyLogoForArchiv($data,$logos,$model){

		if($logos === false) return $data;

		$data[$model]['additionaly_logos'] = $logos;

		return $data;
	}

	public function SelectAdditionalyLogoForArchiv($data,$model){

		if(!isset($data[$model]['data'])) return false;

		$out = $data[$model]['data'];

		if(empty($out)) return false;

		$out = $this->_controller->Xml->XmltoArray($out,'string',null,null);
		$out =	 json_encode($out);
		$out =	 json_decode($out,true);

		if(!isset($out[$model]['additionaly_logos'])) return false;
		if(!isset($out[$model]['additionaly_logos']['LOGO'])) return false;
		if(empty($out[$model]['additionaly_logos']['LOGO'])) return false;

		return $out[$model]['additionaly_logos'];
	}

	public function AdditionallyLogoOnOff($xml,$reportnumber){

		if(Configure::check('AdditionalLogoPrintControl') == false) return;
		if(Configure::read('AdditionalLogoPrintControl') == false) return;

		if(!isset($this->_controller->request->data['logo_on_off'])) return;
		if($reportnumber['Reportnumber']['status'] > 0) die();

		$ReportPdf = $this->_controller->request->tablenames[5];
		$LogoOnOff = intval($this->_controller->request->data['logo_on_off']);
		$ReportnumberId = $this->_controller->request->projectvars['VarsArray'][4];
		$ReportArchiv = $this->_controller->request->tablenames[3];

		if(empty($xml['settings']->{$ReportPdf}->settings->QM_ADDITIONAL_LOGOS->LOGO)) return;

		$additionaly_logos = json_encode($xml['settings']->{$ReportPdf}->settings->QM_ADDITIONAL_LOGOS);
		$additionaly_logos = json_decode($additionaly_logos,true);

		if(isset($additionaly_logos['LOGO'][$LogoOnOff])) $additionaly_current_logo = $additionaly_logos['LOGO'][$LogoOnOff];
		elseif(isset($additionaly_logos['LOGO']['LOGO_NAME'])) $additionaly_current_logo = $additionaly_logos['LOGO'];
		else return;

		$this->_controller->loadModel($ReportArchiv);

		$ArchivData = $this->_controller->{$ReportArchiv}->find('first',array('conditions' => array($ReportArchiv . '.reportnumber_id' => $ReportnumberId)));
		$Archiv = $this->_controller->Xml->XmltoArray($ArchivData[$ReportArchiv]['data'],'string',null,null);

		$Archiv =	 json_encode($Archiv);
		$Archiv =	 json_decode($Archiv,true);
		$additionaly_logos_is = array();

		if(!isset($Archiv[$ReportArchiv]['additionaly_logos'])) $Archiv[$ReportArchiv]['additionaly_logos'] = array();
		if(!isset($Archiv[$ReportArchiv]['additionaly_logos']['LOGO'])) $Archiv[$ReportArchiv]['additionaly_logos']['LOGO'] = array();

		// Beim ersten Eintrag oder wenn bisher alle Logos gedruckt werden
		if(count($Archiv[$ReportArchiv]['additionaly_logos']['LOGO']) == 0){

			$Archiv[$ReportArchiv]['additionaly_logos']['LOGO'][] = $additionaly_current_logo;
			$this->_controller->set('response',json_encode(array('DataId' => $LogoOnOff,'status' => 'off')));
			$this->_CheckReportStatusSave($ArchivData,$Archiv);
			return;
		}
		
		// Wenn sich ein Logo zum nicht drucken im Array befinden
		if(isset($Archiv[$ReportArchiv]['additionaly_logos']['LOGO']['LOGO_NAME'])){

			// Wenn das Logo vorhanden ist wird das Array geleert
			// und das Logo wieder mitgedruckt
			$diff = array_diff($additionaly_current_logo, $Archiv[$ReportArchiv]['additionaly_logos']['LOGO']);

			// Da das Logo schon da ist wird es entfernt
			if(empty($diff)){

				unset($Archiv[$ReportArchiv]['additionaly_logos']);

				$this->_controller->set('response',json_encode(array('DataId' => $LogoOnOff,'status' => 'on')));
				$this->_CheckReportStatusSave($ArchivData,$Archiv);
				return;

			}

			// Es kommt ein neues Logo zum nicht drucken hinzu
			if(!empty($diff)){

				$additionaly_present_logo = $Archiv[$ReportArchiv]['additionaly_logos']['LOGO'];

				$Archiv[$ReportArchiv]['additionaly_logos']['LOGO'] = array();
				$Archiv[$ReportArchiv]['additionaly_logos']['LOGO'][] = $additionaly_present_logo;
				$Archiv[$ReportArchiv]['additionaly_logos']['LOGO'][] = $additionaly_current_logo;

				$this->_controller->set('response',json_encode(array('DataId' => $LogoOnOff,'status' => 'off')));
				$this->_CheckReportStatusSave($ArchivData,$Archiv);
				return;

			}
		}

		// Wenn sich schon mehr als 1 Logo in NichtdruckenArray befinden
		if(isset($Archiv[$ReportArchiv]['additionaly_logos']['LOGO'][0])){

			foreach ($Archiv[$ReportArchiv]['additionaly_logos']['LOGO'] as $key => $value) {

				$diff = array_diff($value,$additionaly_current_logo);

				// Wenn das Logo gefunden wird wird das Element entfernt
				if(empty($diff)){
					unset($Archiv[$ReportArchiv]['additionaly_logos']['LOGO'][$key]);
					$this->_controller->set('response',json_encode(array('DataId' => $LogoOnOff,'status' => 'on')));
					$this->_CheckReportStatusSave($ArchivData,$Archiv);
					return;
					break;
				}
			}

			// Das Logo ist nicht nicht im Array wird also hinzugefügt
			$Archiv[$ReportArchiv]['additionaly_logos']['LOGO'][] = $additionaly_current_logo;

			$this->_controller->set('response',json_encode(array('DataId' => $LogoOnOff,'status' => 'off')));
			$this->_CheckReportStatusSave($ArchivData,$Archiv);
			return;

		}

	}

	protected function _CheckReportStatusSave($ArchivData,$Archiv) {

		$ReportArchiv = $this->_controller->request->tablenames[3];

		//		unset($Archiv[$ReportArchiv]['additionaly_logos']);
		$data = array('archiv' => $Archiv);
		$xmlObject = Xml::fromArray($data, array('format' => 'tags'));

		$xmlString = $xmlObject->asXML();

		$ArchivData[$ReportArchiv]['data'] = htmlentities($xmlString);

		if($this->_controller->{$ReportArchiv}->save($ArchivData));

		$this->_controller->layout = 'blank';

		$this->_controller->render('json');

	}

	public function CheckReportStatus($data) {

		if($data['Reportnumber']['status'] > 0 && $data['Reportnumber']['revision_progress'] == 0) return true;
		if(isset($data['Reportnumber']['revision_write']) && $data['Reportnumber']['revision_write'] == 1) return false;

		return false;

	}

	public function MaxSortImages($data) {

		if(!is_array($data)) return intval(0);
		if(count($data) == 0) return intval(0);
		if(!isset($data[0]['Reportimage'])) return intval(0);
		if(!isset($data[0]['Reportimage']['sorting'])) return intval(0);

		$sorting = Hash::extract($data, '{n}.Reportimage.sorting');

		if(count($sorting) == 0) return intval(0);

		$sort = max($sorting);

		++$sort;

		return $sort;
	}

	public function SortImages($reportnumber) {

		$data = array();

		$attribut_disabled = $this->CheckReportStatus($reportnumber);

		if($attribut_disabled === true){

			$data['value'] = 'Error';

			return $data;

		}

		if(!isset($this->_controller->request->data['image_sorting'])) return $data;
		if(!isset($this->_controller->request->data['Reportimage'])) return $data;
		if(!isset($this->_controller->request->data['Reportimage']['sorting'])) return $data;
		if(!is_array($this->_controller->request->data['Reportimage']['sorting'])) return $data;
		if(count($this->_controller->request->data['Reportimage']['sorting']) == 0) return $data;

		foreach($this->_controller->request->data['Reportimage']['sorting'] as $key => $value){

			$insert = array('id' => intval($key),'sorting' => intval($value));

			$Test = $this->_controller->Reportimage->save($insert);

			if($Test == false){

				$data['value'] = 'Error';

				return $data;
			}
		}

		$data['value'] = 'Success';

		return $data;
	}

	public function EditImageDescription($data) {

		if(isset($this->_controller->request['data']['Reportimage']['delete_image'])) return;

		$attribut_disabled = $this->CheckReportStatus($data);

		if($attribut_disabled === true){

			$this->_controller->Flash->warning(__('This report has been closed, image properties can no longer be edited.'), array('key' => 'warning'));
			return;

		}

		if(!isset($this->_controller->request['data']['Reportimage'])) return;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
	 	$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
	 	$orderID = $this->_controller->request->projectvars['VarsArray'][2];
	 	$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$testingreportID = $this->_controller->request->projectvars['VarsArray'][4];
	 	$id = $this->_controller->request->projectvars['VarsArray'][6];

	 	$reportimages = $this->_controller->Reportimage->find('first', array('conditions' => array('Reportimage.id' => $id)));

		$Save = $this->_controller->Reportimage->save($this->_controller->request['data']['Reportimage']);

		if($Save == false){

			$this->_controller->Flash->error('Discription was not saved', array('key' => 'error'));

		} else {

			$this->_controller->Autorisierung->Logger($testingreportID,$this->_controller->request['data']['Reportimage']);

			if($data['Reportnumber']['revision_progress'] > 0){

				$TransportArray['model'] = 'Reportimage';
				$TransportArray['row'] = 'discription';
				$TransportArray['last_id_for_radio'] = 0;
				$TransportArray['last_value'] = $reportimages['Reportimage']['discription'];
				$TransportArray['this_value'] = $this->_controller->request['data']['Reportimage']['discription'];
				$TransportArray['table_id'] = $this->_controller->request['data']['Reportimage']['id'];
				$TransportArray['reason'] = $this->_controller->Session->read('revision'.$id.'reason');

				$this->_controller->Data->SaveRevision($data,$TransportArray,'images/discription');

			}

			$FormName = array();

			$FormName['controller'] = 'reportnumbers';
			$FormName['action'] = 'images';
			$FormName['terms'] = implode('/',$this->_controller->request->projectvars['VarsArray']);

			$this->_controller->set('FormName', $FormName);

			$this->_controller->Flash->success('Discription was saved', array('key' => 'success'));

		}
	}

	public function DeleteImage($data) {

		$attribut_disabled = $this->CheckReportStatus($data);

		if($attribut_disabled === true) return;

		if(isset($this->_controller->request->data['delete_image']) && !isset($this->_controller->request['data']['Reportimage'])){

			$this->_controller->Flash->error(__('Will you delete this image?'), array('key' => 'error'));

		}

		if(!isset($this->_controller->request['data']['Reportimage'])) return;
		if(!isset($this->_controller->request['data']['Reportimage']['delete_image'])) return;
		if($this->_controller->request['data']['Reportimage']['delete_image'] != 1) return;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
	 	$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$testingreportID = $this->_controller->request->projectvars['VarsArray'][4];
	 	$id = $this->_controller->request->projectvars['VarsArray'][6];

		$reportimages = $this->_controller->Reportimage->find('first', array('conditions' => array('Reportimage.id' => $id)));

	 	$delPath = Configure::read('report_folder') . $projectID . DS . 'images' . DS . $reportimages['Reportimage']['reportnumber_id'] . DS . $reportimages['Reportimage']['name'];

	 	$deltumbPath = ROOT . DS . 'app' .DS . 'webroot' . DS . 'files'. DS . $projectID . DS . 'images' .DS . $reportimages['Reportimage']['reportnumber_id'] . DS . $reportimages['Reportimage']['name'];

		if(!file_exists($delPath)) {

			$this->_controller->set('reportimages', $reportimages);
			$this->_controller->Flash->error(__('The image does not exist'), array('key' => 'error'));

			return;
		}

	 	if(@unlink($delPath)){

			@unlink($deltumbPath);

			if($this->_controller->Reportimage->delete($id)){

	 			$loggerArray = array('nid' => $this->_controller->request['data']['Reportimage']['id'],'delmessage' => $reportimages);
	 			$this->_controller->Autorisierung->Logger($testingreportID,$loggerArray);

				if($data['Reportnumber']['revision_progress'] > 0){

					$TransportArray['model'] = 'Reportimage';
					$TransportArray['row'] = null;
					$TransportArray['this_value'] = null;
					$TransportArray['last_id_for_radio'] = 0;
					$TransportArray['last_value'] = $reportimages['Reportimage']['name'];
					$TransportArray['table_id'] = $reportimages['Reportimage']['id'];
					$TransportArray['reason'] = $this->_controller->Session->read('revision'.$id.'reason');

					$this->_controller->Data->SaveRevision($data,$TransportArray,'images/delimage');

				}

				$FormName = array();

				$FormName['controller'] = 'reportnumbers';
				$FormName['action'] = 'images';
				$FormName['terms'] = implode('/',$this->_controller->request->projectvars['VarsArray']);

				$this->_controller->set('FormName', $FormName);

				$this->_controller->Flash->success('Image was delete', array('key' => 'success'));
	 			$this->_controller->set('reportimages', $reportimages);
	 			$this->_controller->render('imagedelete', 'modal');

			}
		} else {

				$this->_controller->Flash->error(__('Image was not delete'), array('key' => 'error'));
	 			$this->_controller->set('reportimages', $reportimages);
	 			$this->_controller->render('imagedelete', 'modal');

			}
	}


	public function CheckFileSize($img_phat) {

		$Bn = 100000000;

		$Size = getimagesize($img_phat);

		if(!isset($Size['channels'])) return true;

		$w = $Size[0];
		$h = $Size[1];
		$c = $Size['channels'];
		$M = $Size['mime'];

		$B = $w * $h * $c;

		if($B > $Bn) return false;
		else return true;

	}

	public function CheckResizeFileSize($img_phat) {

		$Bn = 100000000;

		$Size = getimagesize($img_phat);

		$w = $Size[0];
		$h = $Size[1];
		$c = $Size['channels'];
		$M = $Size['mime'];

		$B = $w * $h * $c;

		if($B <= $Bn) return;

		$wn = ceil($w * $Bn / $B);
		$hn = ceil($h * $wn / $w);

		$this->__CheckResizeJepg($img_phat,$hn,$wn,$M);
		$this->__CheckResizePng($img_phat,$hn,$wn,$M);

	}

	private function __CheckResizeJepg($img,$h,$w,$t) {

		if($t != 'image/jpeg') return;
		$image = imagecreatefromjpeg($img);
		$imgResized = imagescale($image,$w,$h);
		imagejpeg($imgResized, $img);

	}

	private function __CheckResizePng($img,$h,$w,$t) {

		if($t != 'image/png') return;

		$image = imagecreatefrompng($img);
		$imgResized = imagescale($image,$w,$h);
		imagepng($imgResized, $img);

	}

	public function ImageToBase64($img_src) {
		$isImage = getimagesize($img_src) ? true : false;
		if($isImage) {
			$imagedata = file_get_contents($img_src);
			if($imagedata !== false and !empty($imagedata)){
				$base64 = base64_encode($imagedata);
				if(!empty($base64)) {
					return $base64;
				} else{
					throw new Exception('Empty base64 string given on ' . $img_src . '!');
					return;
				}
			} else{
				if(!$isImage) {
					throw new Exception('Error on loading image ' . $img_src . '!');
				}
			}
		} else {
			if(!$isImage) {
				throw new Exception('Given file may not be an image ' . $img_src . '!');
			} else {
				throw new Exception('Unknown error while converting image to base64 ' . $img_src . '!');
			}
			return;
		}
	}

	public function Tumbs($img_phat,$img_src,$img_width,$img_height,$des_src) {

  	// Größe und Typ ermitteln
  	list($src_width, $src_height, $src_typ) = @getimagesize($img_phat.$img_src);

		// Wenn EXIF vorhanden, Seitenorientierung bestimmen
		$exif = @exif_read_data($img_phat.$img_src, 0, true);

		$orientation = false;

		if(isset($exif['IFD0']['Orientation']) && $exif['IFD0']['Orientation'] > 1){
			$orientation = $exif['IFD0']['Orientation'];
		}

		// neue Größe bestimmen
		if($src_width >= $src_height){
			$new_image_width = $img_width;
			$new_image_height = @($src_height * $img_width / $src_width);
		}

		if($src_width < $src_height){
			$new_image_height = $img_width;
			$new_image_width = $src_width * $img_height / $src_height;
		}

		if($src_typ == 1){     // GIF
			$image = imagecreatefromgif($img_phat.$img_src);
			$new_image = imagecreate($new_image_width, $new_image_height);
			imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_image_width, $new_image_height, $src_width, $src_height);

			// Wenn eine Seitenorientierung bestimmt wurde
			// wird das Bild gedreht
			switch($orientation) {
				case 3:
				$new_image = imagerotate($new_image, 180, 0);
				break;
				case 6:
				$new_image = imagerotate($new_image, -90, 0);
				break;
				case 8:
				$new_image = imagerotate($new_image, 90, 0);
				break;
    	}

			imagerotate($new_image, 90, 0);
			imagegif($new_image, $des_src.$img_src, 100);
			imagedestroy($image);
			imagedestroy($new_image);
			return true;
		}

		elseif($src_typ == 2){ // JPG
			$image = imagecreatefromjpeg($img_phat.$img_src);
			$new_image = imagecreatetruecolor($new_image_width, $new_image_height);
			imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_image_width,$new_image_height, $src_width, $src_height);

			// Wenn eine Seitenorientierung bestimmt wurde
			// wird das Bild gedreht
			switch($orientation) {
				case 3:
				$new_image = imagerotate($new_image, 180, 0);
				break;
				case 6:
				$new_image = imagerotate($new_image, -90, 0);
				break;
				case 8:
				$new_image = imagerotate($new_image, 90, 0);
				break;
    		}

			ob_start();
			imagepng($new_image, NULL, 0, NULL);
			$imageData_ = (ob_get_contents());
			$imageData = base64_encode(ob_get_contents());
			ob_end_clean();
			imagedestroy($image);
			imagedestroy($new_image);
			return $imageData;
		}

		elseif($src_typ == 3){ // PNG
			$image = imagecreatefrompng($img_phat.$img_src);
			$new_image = imagecreatetruecolor($new_image_width, $new_image_height);
			imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_image_width, $new_image_height, $src_width, $src_height);

			// Wenn eine Seitenorientierung bestimmt wurde
			// wird das Bild gedreht
			switch($orientation) {
				case 3:
				$new_image = imagerotate($new_image, 180, 0);
				break;
				case 6:
				$new_image = imagerotate($new_image, -90, 0);
				break;
				case 8:
				$new_image = imagerotate($new_image, 90, 0);
				break;
    	}

			ob_start();
			imagepng($new_image, NULL, 0, NULL);
			$imageData_ = (ob_get_contents());
			$imageData = base64_encode(ob_get_contents());
			ob_end_clean();
			imagedestroy($image);
			imagedestroy($new_image);
			return $imageData;
		}

		else{
			return false;
		}
	}

  public function setTransparency($picture){

		$img_w = imagesx($picture);
		$img_h = imagesy($picture);

		$newPicture = imagecreatetruecolor( $img_w, $img_h );
		imagesavealpha( $newPicture, true );
		$rgb = imagecolorallocatealpha( $newPicture, 0, 0, 0, 127 );
		imagefill( $newPicture, 0, 0, $rgb );

		$color = imagecolorat( $picture, $img_w-1, 1);

		for( $x = 0; $x < $img_w; $x++ ) {
	    	   for( $y = 0; $y < $img_h; $y++ ) {
	        	$c = imagecolorat( $picture, $x, $y );
				if($color!=$c){
            		imagesetpixel( $newPicture, $x, $y,    $c);
   		     	}
   			   }
			}

		imagedestroy($picture);
		return $newPicture;
	}

	public function TestImageExists($projectID,$Year,$Table,$Order){

		$this->_controller->loadModel($Table);

	 	$reportnumbers = $this->_controller->Reportnumber->find('list', array('fields' => array('id'),'conditions' => array('Reportnumber.topproject_id' => $projectID,'Reportnumber.year' => $Year)));
	 	$reportimages = $this->_controller->$Table->find('all', array('order' => array('created'),'conditions' => array($Table.'.reportnumber_id' => $reportnumbers)));


		foreach($reportimages as $_key => $_data){

			$savePath = Configure::read('report_folder') . $projectID . DS . $Order . DS . $_data[$Table]['reportnumber_id'] . DS;
			$copytoPath = Configure::read('report_folder') . $projectID . DS . $Order . DS;

			if(is_dir($savePath) == false){
				pr($_data[$Table]['reportnumber_id']);
				$Testpfad = 'D:\xampp\htdocs\mps_dev\qmsystems\mps\data_mbq\Img\report_folder' . DS . $projectID . DS . $Order . DS . $_data[$Table]['reportnumber_id'] . DS;
				if(is_dir($Testpfad) == true){
					$files = scandir($Testpfad);
					unset($files[0]);
					unset($files[1]);
					sort($files);
				}
				pr('-----------------');
			}
			elseif(is_dir($savePath) == true){
				$Testpfad = 'D:\xampp\htdocs\mps_dev\qmsystems\mps\data_mbq\Img\report_folder' . DS . $projectID . DS . $Order . DS . $_data[$Table]['reportnumber_id'] . DS;
				$files = scandir($savePath);
				unset($files[0]);
				unset($files[1]);
				sort($files);
				foreach($files as $__key => $__data){
					if($__data == $_data[$Table]['name']){
//						pr('Datei vorhanden');
					}
				}

				$selif = array_flip($files);

				if(!isset($selif[$_data[$Table]['name']])){
					if(is_file($Testpfad . $_data[$Table]['name']) && is_dir($savePath)){
pr($Testpfad);
//						if (!copy($Testpfad . $_data[$Table]['name'], $copytoPath)) pr('nicht kopiert');
//						else pr('kopiert');
					}
					pr('----------------');
				}
			}
		}
	}

	public function setSignImage($data,$sign,$typ,$version = 1){
			if($version == 1) $color = 'colored';
			if($version == 2) $color = 'orginal';

			if((Configure::check('SignatorySaveMethode') && Configure::read('SignatorySaveMethode') == 'file') || !Configure::check('SignatorySaveMethode')){
				// Daten kommen aus Datein
				$report_id_chiper = bin2hex(Security::cipher($data['Reportnumber']['id'], Configure::read('SignatoryHash')));
				$project_id_chiper = bin2hex(Security::cipher($data['Reportnumber']['topproject_id'], Configure::read('SignatoryHash')));
				$path = Configure::read('SignatoryPfad') . $project_id_chiper . DS . $report_id_chiper . DS . $typ . DS . $color . DS . $report_id_chiper;

				if(file_exists($path)){
					$filecontent = file_get_contents($path);
				} else {
					$filecontent = null;
				}
			}

			if(Configure::check('SignatorySaveMethode') && Configure::read('SignatorySaveMethode') == 'data'){
				// Daten kommen aus Datenbank
				$filecontent = $sign['Sign']['image'];
				if($filecontent != null){
					$openImage = Security::cipher($filecontent, Configure::read('SignatoryHash'));
				} else {
					$openImage = null;
				}
			}

			if($filecontent != null){
				$openImage = Security::cipher($filecontent, Configure::read('SignatoryHash'));
			} else {
				$openImage = null;
			}

			// Ausgabe protokollieren
			$this->_controller->loadModel('SingsHistory');
//			$Signatories = $this->_controller->Sign->find('all',array('conditions' => array('Sign.reportnumber_id' => $id)));
			$ClientIp = $this->_controller->Autorisierung->GetClientIp();
			$IpInfo = $this->_controller->Autorisierung->IpInfo($ClientIp,'Location');
//			$browser = get_browser(null, true);
			$browser = array();

			$data = array();
			$data['SingsHistory']['signs_id'] = $sign['Sign']['id'];
			$data['SingsHistory']['user_id'] = $this->_controller->Auth->user('id');
			$data['SingsHistory']['reportnumber_id'] = $sign['Sign']['reportnumber_id'];
			$data['SingsHistory']['ip_adress'] = $ClientIp;
			$data['SingsHistory']['rand'] = 4135131;
//			$data['SingsHistory']['rand'] = rand() * rand();

			(isset($IpInfo['city'])) ? $data['SingsHistory']['city'] = $IpInfo['city'] : null;
			(isset($IpInfo['state'])) ? $data['SingsHistory']['state'] = $IpInfo['state'] : null;
			(isset($IpInfo['country'])) ? $data['SingsHistory']['country'] = $IpInfo['country'] : null;
			(isset($IpInfo['country_code'])) ? $data['SingsHistory']['country_code'] = $IpInfo['country_code'] : null;
			(isset($IpInfo['continent'])) ? $data['SingsHistory']['continent'] = $IpInfo['continent'] : null;
			(isset($IpInfo['continent_code'])) ? $data['SingsHistory']['continent_code'] = $IpInfo['continent_code'] : null;
			(isset($IpInfo['geoplugin_latitude'])) ? $data['SingsHistory']['geoplugin_latitude'] = $IpInfo['geoplugin_latitude'] : null;
			(isset($IpInfo['geoplugin_longitude'])) ? $data['SingsHistory']['geoplugin_longitude'] = $IpInfo['geoplugin_longitude'] : null;

			(isset($browser['browser_name_pattern'])) ? $data['SingsHistory']['browser_name_pattern'] = $browser['browser_name_pattern'] : null;
			(isset($browser['platform'])) ? $data['SingsHistory']['platform'] = $browser['platform'] : null;
			(isset($browser['browser'])) ? $data['SingsHistory']['browser'] = $browser['browser'] : null;
			(isset($browser['device_type'])) ? $data['SingsHistory']['device_type'] = $browser['device_type'] : null;
			(isset($browser['version'])) ? $data['SingsHistory']['version'] = $browser['version'] : null;
			(isset($browser['ismobiledevice'])) ? $data['SingsHistory']['ismobiledevice'] = $browser['ismobiledevice'] : null;
			(isset($browser['istablet'])) ? $data['SingsHistory']['istablet'] = $browser['istablet'] : null;

			$this->_controller->SingsHistory->create();
			$this->_controller->SingsHistory->save($data);
			$SingsHistoryId = $this->_controller->SingsHistory->getInsertID();
			$data = $this->_controller->SingsHistory->find('first',array('conditions' => array('SingsHistory.id' => $SingsHistoryId)));

			if(isset($sign['Sign']['signature'])) $data['SingsHistory']['signature'] = $sign['Sign']['signature'];

			return array('image' => $openImage,'data' => $data);
	}

	function DownloadImage($data) {

		$this->_controller->autoRender = false;
		// Flatten each row of the data array

		if(!empty($maxExecutionSeconds)){
			ini_set('max_execution_time', $maxExecutionSeconds); //increase max_execution_time if data set is very large
		}

		$fileName = "download_image_".date("Y-m-d").".png";

		$Info = getimagesizefromstring($data);

		if($Info['mime'] != 'image/png') return false;

		header('Content-Disposition: attachment;filename="' . $fileName . '"');
		header('Content-Type: application/force-download');
		echo $data;
	}
}
