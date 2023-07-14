<?php
    class CronJobShell extends AppShell {

    public $uses = array('Examiner','MailMonitoring','Device','DeviceTestingmethod','ExaminersEmailCronjob');


    public function CronJobCertifcates() {

      App::import('Core', 'L10n');
      App::uses('CakeEmail', 'Network/Email');

      Configure::write('Config.language',$this->args[0]);

      /*//
      // Arg 0 Sprache
      // Arg 1 = 1 Alle Zertifikatinfos an bestimmte Mail-Adressen senden
      // Arg 2 = 1 Alle Zertifikatinfos an Mail-Adressen der Workplaces senden
      // Arg 3 = 1 Persönliche Zertifikatinfos an Personal senden
      /*///

      if($this->args[1] == 1) $this->__SendAllWorkingPlaces();
      if($this->args[2] == 1) $this->__SendThisWorkingPlaces();
      if($this->args[3] == 1) $this->__SendExaminerWorkingPlaces();

    }

    protected function __SendExaminerWorkingPlaces(){

      $Emailtitel = __('Personenbezogene Zertifikate');

      $Options = array(
        'conditions' => array(
          'Examiner.email !=' => '',
          'Examiner.send_infos' => 1,
        ),
      );

      $this->Examiner->recursive = -1;

      $Examiner = $this->Examiner->find('all',$Options);

      if(count($Examiner) == 0) return array();

      foreach ($Examiner as $key => $value) {

        $Titel = $Emailtitel . ' ' . $value['Examiner']['name'] . ' ' . $value['Examiner']['first_name'];

        $Certifcates = $this->__CollectCertificateInfos(array('value' => $value['Examiner']['id'],'row' => 'id'));
        $Emails = array($value['Examiner']['email']);
        $Mails = $this->__SendCertificateMails($Emails,array('Certifcates' => $Certifcates,'Examiners' => $value),true,$Titel);

      }
    }

    protected function __SendAllWorkingPlaces(){

      $Emailtitel = __('Alle Zertifikate');
      $Emails = $this->__CollectEmails(NULL);
      $Certifcates = $this->__CollectCertificateInfos(NULL);
      $Mails = $this->__SendCertificateMails($Emails,array('Certifcates' => $Certifcates),false,$Emailtitel);

    }

    protected function __SendThisWorkingPlaces(){

      $Emailtitel = __('Dienststellenbezogene Zertifikate');

      $Options = array(
        'fields' => array('DISTINCT working_place'),
      );

      $WorkingPlaces = $this->ExaminersEmailCronjob->find('all',$Options);

      if(count($WorkingPlaces) == 0) return array();

      $WorkingPlaces = Hash::extract($WorkingPlaces, '{n}.ExaminersEmailCronjob.working_place');

      foreach ($WorkingPlaces as $key => $value) {

        if(empty($value)) continue;

        $Emailtitel .= ' ' . $value;

        $Emails = $this->__CollectEmails($value);
        $Certifcates = $this->__CollectCertificateInfos(array('value' => $value,'row' => 'working_place'));
        $Mails = $this->__SendCertificateMails($Emails,array('Certifcates' => $Certifcates),false,$Emailtitel);

      }

    }

    protected function __CollectEmails($WorkingPlaces){

      if($WorkingPlaces == NULL){

        $Options = array(
          'fields' => array('DISTINCT email'),
          'conditions' => array(
            'ExaminersEmailCronjob.working_place' => '',
          )
        );

      } else {

        $Options = array(
          'fields' => array('DISTINCT email'),
          'conditions' => array(
            'ExaminersEmailCronjob.working_place' => $WorkingPlaces,
          )
        );

      }


      $Mails = $this->ExaminersEmailCronjob->find('all',$Options);

      if(count($Mails) == 0) return array();

      $Mails = Hash::extract($Mails, '{n}.ExaminersEmailCronjob.email');

      return $Mails;

    }

    protected function __CollectCertificateInfos($WorkingPlaces){

      if(!is_array($WorkingPlaces)){

        $certificates_options = array(
          'order' => array('CertificateData.id DESC'),
          'conditions' => array(
            'Examiner.deleted' => 0,
            'Examiner.active' => 1,
            'CertificateData.active' => 1,
            'CertificateData.deleted' => 0,

          )
        );

      } else {

        if(count($WorkingPlaces) != 2) return false;

        $certificates_options = array(
          'order' => array('CertificateData.id DESC'),
          'conditions' => array(
            'Examiner.' . $WorkingPlaces['row'] => $WorkingPlaces['value'],
            'Examiner.deleted' => 0,
            'Examiner.active' => 1,
            'CertificateData.active' => 1,
            'CertificateData.deleted' => 0,

          )
        );

      }


      $certificate = $this->Examiner->Certificate->CertificateData->find('all',$certificates_options);
      $certficates = array();

      $summary = array(
        'errors' => array(),
        'warnings' => array(),
        'futurenow' => array(),
        'future' => array(),
        'hints' => array(),
        'deactive' => array()
      );

      $summary_desc = array(
        'futurenow' => array(
          0 =>__('First-time certification reached',true),
          1 =>__('First-time certifications reached',true),
        ),

        'future' => array(
          0 =>__('First-time certification',true),
          1 =>__('First-time certifications',true),
        ),
        'errors' => array(
          0 =>__('Irregularity',true),
          1 =>__('Irregularities',true),
        ),
        'warnings' => array(
          0 => __('Warning',true),
          1 => __('Warnings',true)
        ),
        'hints' => array(
          0 => __('Hint',true),
          1 => __('Hints',true)
        ),
        'deactive' => array(
          0 => __('Deactive',true),
          1 => __('Deactive',true)
        )
      );

      foreach($certificate as $_key => $_certificates){

        if(!isset($_certificates ['CertificateData'])) continue;

        $sendingonetime = Configure::read('monitoring.email.sendonetime');

        if ($sendingonetime == true) {

          $mailedtrue = '';
          $mailedtrue = $this->MailMonitoring->find('first',array(

            'conditions' => array('MailMonitoring.id_cert_data' =>$_certificates['CertificateData'] ['id'],
            'MailMonitoring.model' => 'CertificateData'
          )));

          if(!empty($mailedtrue)) continue;

          $__certificates = $this->CertificatesSectors($_certificates);
          $___certificates = $this->CertificateSummary($__certificates,array('main'=>'Certificate','sub'=>'CertificateData'));

          $certificates[$_certificates['Examiner']['id']]['summary'] = $___certificates;
          $send = 0;

          // Nach Fehler/Hinweisarten sortieren
          foreach($summary as $__key => $_summary){

            if($__key == 'hints'||$__key == 'future' ) continue;

            if(isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0){
              $summary[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];
              $send = 1;
            }
          }

          if($send == 1 ){
            $sent_monitoring ['model'] ='CertificateData';
            $sent_monitoring['id_cert_data'] = $_certificates['CertificateData'] ['id'];
            $this->MailMonitoring->create();
            $this->MailMonitoring->save($sent_monitoring);
          }

        } else {
          $__certificates = $this->CertificatesSectors($_certificates);
          $___certificates = $this->CertificateSummary($__certificates,array('main'=>'Certificate','sub'=>'CertificateData'));
          $certificates[$_certificates['Examiner']['id']]['summary'] = $___certificates;
          foreach($summary as $__key => $_summary){
            if(isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0){
              $summary[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];
            }
          }
        }
      }

      $Data = array('summary' => $summary,'summary_desc' => $summary_desc);

      return $Data;

    }

    protected function __SendCertificateMails($Emails,$Certificates,$SendHints,$Title){

      if(count($Emails) == 0) return false;
      if(count($Certificates) == 0) return false;
      if(!isset($Certificates['Certifcates']['summary_desc'])) return false;
      if(!isset($Certificates['Certifcates']['summary'])) return false;

      $mailview ='summary_certificat';
      $email = new CakeEmail();
      $email->config('default');
      $email->from('certificats@docu-dynamics.cloud');
      $email->to($Emails);
      $email->subject(__('Zertifizierungsinfos') . ' ' . $Title);
      $email->template($mailview,'summary');
      $email->emailFormat('both');
      $email->viewVars(array(
        'summary_desc' => $Certificates['Certifcates']['summary_desc'],
        'summary' => $Certificates['Certifcates']['summary'],
        'examiner' => (isset($Certificates['Examiners'])) ?  $Certificates['Examiners'] : array(),
        'commentar' => array(),
        'title' => $Title,
        'send_hints' => $SendHints
        )
      );

      $Send = $email->send();

      $Data = array();
      return $Data;

    }

  public function main() {
  }

	public function CertificatesSectors($examiner) {

		$examiner_id = $examiner['Examiner']['id'];

		$filter_certifcation = array();

		// wenn die Funktion Ã¼ber die Certifcation-Aktion aufgerufen wird
		// werden nur zertifizierte Qualifikationen angezeigt

			$filter_certifcation = array('certificate_data_active' => 1);


		$certificate_options = array(
								'conditions' => array(
										'Certificate.examiner_id' => $examiner_id,
										'Certificate.deleted' => 0,
										'Certificate.active' => 1,
										$filter_certifcation
									),
								'fields' => array(
										'Certificate.sector'
									),
								'group' => array(
										'Certificate.sector'
									)
								);

		$certificate = $this->Examiner->Certificate->find('list',$certificate_options);

		$certificates = array();
		$certificatesbyid = array();

		foreach($certificate as $_key => $_certificate){

			$certificate_options = array(
								'order' => array('testingmethod ASC','level DESC'),
								'conditions' => array(
										'Certificate.examiner_id' => $examiner_id,
										'Certificate.sector' => $_certificate,
										'Certificate.deleted' => 0,
										'Certificate.active' => 1,
										$filter_certifcation
									),
								);

			$data = $this->Examiner->Certificate->find('all',$certificate_options);

			if(count($data) > 0){

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
													'CertificateData.active' => 1
												),
												'order' => array('CertificateData.id DESC')
											);
					$certificate_date = $this->Examiner->Certificate->CertificateData->find('first',$certificate_date_options);
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

					// Wenn das Zertifikat deaktiviert wurde
					if($certificates[$_certificate][$__key]['Certificate']['certificate_data_active'] == 1 && isset($certificates[$_certificate][$__key]['CertificateData']['active']) && $certificates[$_certificate][$__key]['CertificateData']['active'] == 0){
						$certificates[$_certificate][$__key]['CertificateData']['valid'] = 0;
						$certificates[$_certificate][$__key]['CertificateData']['valid_class'] = 'certification_deactive';
					}
				}
			}
		}
		return $certificates;
	}


	public function IsRenewal($certificate) {
		// Test ob Erneuert oder rezertifiziert wird



    $certified_differentiation = $certificate['CertificateData']['certified_differentiation'];

    switch ($certified_differentiation) {
      case 0:
        $certificate['CertificateData']['renewal'] = false;
      break;
      case 1:
        $certificate['CertificateData']['renewal'] = true;
      break;
    }

		return $certificate;
	}



	public function TimeHorizons($_data,$model) {

		//	$_data[$model['sub']]['period'] = $period;

			// Testen ob der Zeitpunkt zur ersten Zertifizierung erreicht wurde
	     $_data[$model['sub']]['valid_class'] = "";
       $_data[$model['sub']]['valid'] = "";
			$firstCertification = date('Y-m-d', strtotime('+' . $_data[$model['sub']]['first_certification'] . ' '.'months', strtotime($_data[$model['sub']]['first_registration'])));

			if($firstCertification >= date('Y-m-d',time()) && $_data[$model['sub']]['certified'] == 0){

				$_data[$model['sub']]['first_certification_date'] = $firstCertification;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = $firstCertification;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->dateDiff($firstCertification,date('Y-m-d',time()));
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
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->dateDiff($firstCertification,date('Y-m-d',time()));
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
			$nextCertificationHorizon = date('Y-m-d', strtotime('-' . $_data[$model['sub']]['horizon'] . ' '.'months', strtotime($nextCertification)));


			$datediffCertificatione = $this->dateDiff($nextCertification,date('Y-m-d',time()));
			$datediffHorizon = $this->dateDiff($nextCertificationHorizon,date('Y-m-d',time()));

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
					$_data[$model['sub']]['time_to_next_certification'] = __('renewal time is exceeded',true) . ' ' . $nextCertification.'; '.$datediffCertificatione.' '.__('ago',true);
				}
				if($model['sub'] == 'CertificateData'){
					$_data[$model['sub']]['time_to_next_certification'] = __('recertification time is exceeded',true) . ' ' . $nextCertification.'; '.$datediffCertificatione.' '.__('ago',true);
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
          $interval .= "(s)";
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
			$time[$desciption[$_key]] = intval($_times);
		}
	}

	$output['timestamp']['start'] = $timestamp1;
	$output['timestamp']['end'] = $timestamp2;
	$output['timestamp']['diff'] = $timestamp_diff;
	$output['array'] = $time;
	$output['string'] = implode(", ", $times);


	return implode(", ", $times);
  }

 	public function CertificateSummary($_data,$model) {

		$summary = array('future' => array(),'futurenow' => array(),'errors' => array(),'warnings' => array(),'hints' => array());

		$qualifications = array();
	 	$certificat_no = array();

		foreach($_data as $__key => $__data){
			if(count($_data) == 0) continue;

			foreach($__data as $___key => $___data){
      if(!isset($___data[$model['main']])) continue;
        if(!isset($___data[$model['sub']])) continue;
				if($___data[$model['main']]['certificate_data_active'] == 0){

					$vars['VarsArray'][15] = $___data['Examiner']['id'];
					$vars['VarsArray'][16] = $___data[$model['main']]['id'];
					$vars['VarsArray'][17] = 0;

					$qualifications[$___data[$model['main']]['id']]['certificat'] = '-';
					$qualifications[$___data[$model['main']]['id']]['active'] = $___data[$model['main']]['active'];
					$qualifications[$___data[$model['main']]['id']]['class'] = strtolower($___data[$model['main']]['sector']).'_'.strtolower($___data[$model['main']]['testingmethod']).'_'.$___data[$model['main']]['level'].'_no_certificat';
					$qualifications[$___data[$model['main']]['id']]['sector'] = $___data[$model['main']]['sector'];
					$qualifications[$___data[$model['main']]['id']]['testingmethod'] = $___data[$model['main']]['testingmethod'];
					$qualifications[$___data[$model['main']]['id']]['level'] = $___data[$model['main']]['level'];
					$qualifications[$___data[$model['main']]['id']]['term'] = $vars['VarsArray'];
					$qualifications[$___data[$model['main']]['id']]['termlink'] = implode('/',$vars['VarsArray']);
					$qualifications[$___data[$model['main']]['id']]['certificate_id'] = 0;
					$qualifications[$___data[$model['main']]['id']]['certificate_data_id'] = 0;

				}

				if($___data[$model['main']]['certificate_data_active'] == 1 && isset($___data[$model['sub']]['active'])&& $___data[$model['sub']]['active'] == 1 && $___data[$model['sub']]['deleted'] == 0){

					$certificat_no[$___data['Certificate']['certificat']] = $___data['Certificate']['certificat'];


					$vars['VarsArray'][15] = $___data['Examiner']['id'];
					$vars['VarsArray'][16] = $___data[$model['main']]['id'];
					$vars['VarsArray'][17] = $___data[$model['sub']]['id'];

					$qualifications[$___data[$model['main']]['id']]['certificat'] = $___data[$model['main']]['certificat'];
					$qualifications[$___data[$model['main']]['id']]['active'] = $___data[$model['main']]['active'];
					$qualifications[$___data[$model['main']]['id']]['class'] = strtolower($___data[$model['main']]['sector']).'_'.strtolower($___data[$model['sub']]['testingmethod']).'_'.$___data[$model['main']]['level'];
					$qualifications[$___data[$model['main']]['id']]['sector'] = $___data[$model['main']]['sector'];
					$qualifications[$___data[$model['main']]['id']]['testingmethod'] = $___data[$model['main']]['testingmethod'];
					$qualifications[$___data[$model['main']]['id']]['level'] = $___data[$model['main']]['level'];
					$qualifications[$___data[$model['main']]['id']]['term'] = $vars['VarsArray'];
					$qualifications[$___data[$model['main']]['id']]['termlink'] = implode('/',$vars['VarsArray']);
					$qualifications[$___data[$model['main']]['id']]['certificate_id'] = $___data[$model['main']]['id'];
					$qualifications[$___data[$model['main']]['id']]['certificate_data_id'] = $___data[$model['sub']]['id'];

				}

				if(isset($___data[$model['sub']]['valid_class']) && $___data[$model['sub']]['valid_class'] == 'certification_time_reached'){
					$summary['futurenow'][$___data[$model['sub']]['id']]['recertified_soon_date'] =
__('The time for certification was reached on',true). ' ' . $___data[$model['sub']]['next_certification'];

					$summary['futurenow'][$___data[$model['sub']]['id']][] = $summary['futurenow'][$___data[$model['sub']]['id']]['recertified_soon_date'];
					$summary['futurenow'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['futurenow'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['futurenow'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
					}
					continue;
				}

				if(isset($___data[$model['sub']]['valid_class']) && $___data[$model['sub']]['valid_class'] == 'first_certification_valid_soon'){
					$summary['future'][$___data[$model['sub']]['id']]['recertified_soon_date'] =
__('The time for certification is reached on',true). ' ' . $___data[$model['sub']]['next_certification'].' (' . __('in',true) . ' ' . $this->dateDiff($___data[$model['sub']]['next_certification'],date('Y-m-d',time())).')';

					$summary['future'][$___data[$model['sub']]['id']][] = $summary['future'][$___data[$model['sub']]['id']]['recertified_soon_date'];
					$summary['future'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['future'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['future'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];
					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_future') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_future';
					}
					continue;
				}
				if(isset($___data[$model['sub']]['valid_class']) && $___data[$model['sub']]['valid_class'] == 'certification_no_date'){

					$summary['errors'][$___data[$model['sub']]['id']][] = __('This certificat has the status CERTIFICATED but there is no certification date. Please open the certification and enter a valid date.',true);
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

						if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
							$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
						}
					}

				if(isset($___data[$model['sub']]['valid_class']) && $___data[$model['sub']]['valid_class'] == 'certification_time_exceeded'){
					$summary['errors'][$___data[$model['sub']]['id']]['recertified_soon_date'] =  __('The time for certification was exceeded.',true);

					$summary['errors'][$___data[$model['sub']]['id']][] = __('The time for certification was exceeded.',true);
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}

				if($___data[$model['main']]['certificate_data_active'] == 1 && isset($___data[$model['sub']]['certified']) && $___data[$model['sub']]['certified'] == 2){

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

				if(isset($___data[$model['sub']]['valid']) && $___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['valid'] == 0 && isset($___data[$model['sub']]['active']) && $___data[$model['sub']]['active'] == 1){
					$summary['errors'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}
				if(isset($___data[$model['sub']]['certified_file']) && $___data[$model['main']]['certificate_data_active'] == 1 && $___data[$model['sub']]['certified_file'] == '' && isset($___data[$model['sub']]['active'] ) && $___data[$model['sub']]['active'] == 1){
					$summary['errors'][$___data[$model['sub']]['id']][] = __('Certificate file not found.',true);
					$summary['errors'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['errors'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['errors'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_error') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_error';
					}
				}
				if(
        isset($___data[$model['sub']]['valid']) &&
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
				if($___data[$model['main']]['certificate_data_active'] == 1 && isset($___data[$model['sub']]['certified'] ) &&$___data[$model['sub']]['certified'] == 0){

					if(isset($___data[$model['sub']]['active']) && $___data[$model['sub']]['active'] > 0){
						$summary['warnings'][$___data[$model['sub']]['id']][] = __('Certificate deative',true);
						$summary['warnings'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
						$summary['warnings'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
						$summary['warnings'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

						if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
							$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
						}
					}
				}
				if(
          isset($___data[$model['sub']]['valid']) &&
					$___data[$model['sub']]['valid'] == 1 &&
					$___data[$model['sub']]['certified'] == 1 &&
					$___data[$model['sub']]['valid_class'] == 'certification_valid' &&
					strtotime('+'.Configure::read('NextZertificationsMonths').' month') >= strtotime($___data[$model['sub']]['next_certification_horizon'])
				)
				{

						$summary['hints'][$___data[$model['sub']]['id']]['recertified_soon_date'] =
__('This certification will expire on',true). ' ' . $___data[$model['sub']]['next_certification'].' (' . __('in',true) . ' ' . $this->dateDiff($___data[$model['sub']]['next_certification'],date('Y-m-d',time())).')';
						$summary['hints'][$___data[$model['sub']]['id']]['horizon_soon_date'] =
__('They will be remembered',true). ' ' . $___data[$model['sub']]['next_certification_horizon'].' ('. __('in',true) . ' ' . $this->dateDiff($___data[$model['sub']]['next_certification_horizon'],date('Y-m-d',time())).')';


						$summary['hints'][$___data[$model['sub']]['id']][] = __('This certification will expire on',true). ' ' . $___data[$model['sub']]['next_certification'].' (' . __('in',true) . ' ' . $this->dateDiff($___data[$model['sub']]['next_certification'],date('Y-m-d',time())).')';
						$summary['hints'][$___data[$model['sub']]['id']][] = __('They will be remembered',true). ' ' . $___data[$model['sub']]['next_certification_horizon'].' ('. __('in',true) . ' ' . $this->dateDiff($___data[$model['sub']]['next_certification_horizon'],date('Y-m-d',time())).')';

						$summary['hints'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
						$summary['hints'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
						$summary['hints'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];
						if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
							$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
						}
				}
				if(
        isset($___data[$model['sub']]['valid']) &&
				$___data[$model['sub']]['valid'] == 1 &&
				$___data[$model['sub']]['valid_class'] == 'certification_not_valid_soon' &&
				$___data[$model['sub']]['apply_for_recertification'] == 1
				){
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['certification_requested'];
					$summary['hints'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['hints'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['hints'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];

					if (strpos($qualifications[$___data[$model['main']]['id']]['class'], '_warning') === false) {
						$qualifications[$___data[$model['main']]['id']]['class'] = $qualifications[$___data[$model['main']]['id']]['class'].'_warning';
					}
				}

				if(isset($___data[$model['sub']]['valid_class']) && $___data[$model['sub']]['valid_class'] == 'certification_valid'){
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['time_to_next_certification'];
					$summary['hints'][$___data[$model['sub']]['id']][] = $___data[$model['sub']]['certification_requested'];
					$summary['hints'][$___data[$model['sub']]['id']]['info'] = $___data[$model['sub']];
					$summary['hints'][$___data[$model['sub']]['id']]['certificate'] = $___data[$model['main']];
					$summary['hints'][$___data[$model['sub']]['id']]['examiner'] = $___data['Examiner'];
				}
			}
		}

	 	$output['summary'] = $summary;
	 	$output['certificat_no'] = $certificat_no;
	 	$output['qualifications'] = $qualifications;

		return $output;
	}



  public function emaileyechecks() {
        App::import('Core', 'L10n');
        Configure::write('Config.language',$this->args[0]);
        App::uses('CakeEmail', 'Network/Email');







		$certificates = array();

		$eyechecks_options = array(
									'order' => array('EyecheckData.id DESC'),
									'conditions' => array(
													//'Examiner.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
													'Examiner.deleted' => 0,
                                                                                                        'EyecheckData.active' => 1,
                                                                                                        'EyecheckData.deleted' => 0,

												)
											);

		$eyechecks = $this->Examiner->Eyecheck->EyecheckData->find('all',$eyechecks_options);

		$summary = array(
						'errors' => array(),
						'warnings' => array(),
						'futurenow' => array(),
						'future' => array(),
						'hints' => array(),
						'deactive' => array()
					);

		$summary_desc = array(
					'futurenow' => array(
							0 =>__('First-time eyecheck reached',true),
							1 =>__('First-time eyecheck reached',true),
							),

					'future' => array(
							0 =>__('First-time eyecheck',true),
							1 =>__('First-time eyecheck',true),
							),
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);
                $summaryeyecheck = '';
                $mailed = 0;
		foreach($eyechecks as $_key => $_certificates){
                    if(isset($_certificates['EyecheckData'])) {
                        $sendingonetime = Configure::read('monitoring.email.sendonetime');
                        if ($sendingonetime == true) {

                        $mailedtrue = '';
                        $mailedtrue = $this->MailMonitoring->find('first',array(

									'conditions' => array('MailMonitoring.id_cert_data' =>$_certificates['EyecheckData'] ['id'],
                                                                                               'MailMonitoring.model' => 'EyecheckData'
                                                                            )));
                        if(!empty($mailedtrue)){
                           // $summary = '';
                             $mailed ++;
                            continue;
                        }
                        else{
			$__certificates = $this->Eyechecks($_certificates);
			$___certificates = $this->EyecheckSummary($__certificates,array('main'=>'Eyecheck','sub'=>'EyecheckData'));

			$certificates[$_certificates['Examiner']['id']]['summary'] = $___certificates;

			// Nach Fehler/Hinweisarten sortieren
			foreach($summary as $__key => $_summary){
                                if($__key == 'hints'||$__key == 'future'  ) continue;
				if(isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0){
                                    $summaryeyecheck[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];
                                    $sent_monitoring ['model'] ='EyecheckData';
                                    $sent_monitoring['id_cert_data'] = $_certificates['EyecheckData'] ['id'];
                                    $this->MailMonitoring->create();
                                    $this->MailMonitoring->save($sent_monitoring);
				}

			}
                        }
                    }else{$__certificates = $this->Eyechecks($_certificates);
			$___certificates = $this->EyecheckSummary($__certificates,array('main'=>'Eyecheck','sub'=>'EyecheckData'));

			$certificates[$_certificates['Examiner']['id']]['summary'] = $___certificates;}
                        foreach($summary as $__key => $_summary){
                        if(isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0){
                        $summaryeyecheck[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];}
                        }
                  }

                }

                $emptysummary = 1;
               //
                if(!empty($summaryeyecheck)) {
                foreach ($summaryeyecheck as $s_key => $s_value) {
                    // pr($s_value);
                if($s_key == 'hints') continue;
                if(!empty($s_value)){
                    $emptysummary = 0;
                    break;
                }else $emptysummary = 1;
              }
              }
        if($emptysummary == 0) {
        $email = new CakeEmail();
        $email->config('default');


        $email->from('ines.hoffner@mbq-gmbh.de');

        $email->to('phillip.schmidt@mbq-gmbh.de');
        !empty($mailed)?$betreff = 'Sehtestinformationen (noch offen: '.$mailed.')' : $betreff = 'Sehtestinformationen';
        $email->subject($betreff);
        $mailview ='summary_eyecheck';
        $email->template($mailview,'summary');
				$email->emailFormat('both');
				$email->viewVars(array(
							'summary_desc' => $summary_desc,
							'summary' => $summaryeyecheck,
							'commentar' => '',
    						)
						);

        $email->send();

        }
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

		$data = $this->Examiner->Eyecheck->EyecheckData->find('first',$certificate_options);

		if(count($data) > 0){

			$data = $this->TimeHorizons($data,array('main'=>'Eyecheck','sub'=>'EyecheckData'),$data['EyecheckData']['recertification_in_year']);

			// Wenn das Zertifikat deaktiviert wurde
			if($data['EyecheckData']['active'] == 0){
				$data['EyecheckData']['valid'] = 0;
				$data['EyecheckData']['valid_class'] = 'certification_deactive';
			}
		}
		return $data;
	}



	public function EyecheckSummary($_data,$model) {

		if(count($_data) == 0){
			return array('summary' => array(),'qualifications' => array());
		}

		$summary = array('future' => array(),'futurenow' => array(),'errors' => array(),'warnings' => array(),'hints' => array());
		$qualifications = array();

		if($_data[$model['sub']]['active'] == 1 && $_data[$model['sub']]['deleted'] == 0){

			$vars['VarsArray'][15] = $_data['Examiner']['id'];
			$vars['VarsArray'][16] = $_data[$model['main']]['id'];
			$vars['VarsArray'][17] = $_data[$model['sub']]['id'];

			$qualifications[$_data[$model['main']]['id']]['certificat'] = $_data[$model['main']]['certificat'];
			$qualifications[$_data[$model['main']]['id']]['class'] = 'eyecheck_valide';
			$qualifications[$_data[$model['main']]['id']]['term'] = $vars['VarsArray'];
			$qualifications[$_data[$model['main']]['id']]['termlink'] = implode('/',$vars['VarsArray']);
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
			$qualifications[$_data[$model['main']]['id']]['term'] = $vars['VarsArray'];
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



       public function devicemonitoring() {
        App::import('Core', 'L10n');
        Configure::write('Config.language',$this->args[0]);
        App::uses('CakeEmail', 'Network/Email');

      //  $testingmethod =$this->args[1];
        $_ids = array();
        $this->DeviceTestingmethod->recursive = 1;
	$testingmethods = $this->DeviceTestingmethod->find('all');
        if (isset($this->args[1])) {
            		$this->Device->DeviceCertificate->recursive = -1;
			$MonitoringKind = $this->Device->DeviceCertificate->find('all',array('order' => array('certificat'),'fields' => array('certificat'),'group' => array('certificat')));


                        foreach ($MonitoringKind as $mkey => $mvalue) {
                              $certificate = array();
                            $certificates_options = array(

									'conditions' => array(
													//'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),

													'Device.deleted' => 0,
                                                                                                        'Device.active' => 1,
													'DeviceCertificateData.active' => 1,
													'DeviceCertificateData.deleted' => 0,
                                                                                                        'DeviceCertificateData.certificat' => $mvalue['DeviceCertificate']['certificat'],
													$_ids
												),

											);
                            $certificate = $this->Device->DeviceCertificate->DeviceCertificateData->find('all',$certificates_options);


                            		$certficates = array();


                                      	$summary = array(
						'errors' => array(),
						'warnings' => array(),
						'futurenow' => array(),
						'future' => array(),
						'hints' => array(),
						'deactive' => array()
					);

		$summary_desc = array(
					'futurenow' => array(
							0 =>__('First-time certification reached',true),
							1 =>__('First-time certifications reached',true),
							),

					'future' => array(
							0 =>__('First-time certification',true),
							1 =>__('First-time certifications',true),
							),
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);
                $mailed = 0;
               foreach($certificate as $_key => $_certificates){


                    if(isset($_certificates['DeviceCertificateData'])) {
                     $sendingonetime = Configure::read('monitoring.email.sendonetime');
                     if ($sendingonetime == true) {

                        $mailedtrue = '';
                        $mailedtrue = $this->MailMonitoring->find('first',array(

									'conditions' => array('MailMonitoring.id_cert_data' =>$_certificates['DeviceCertificateData'] ['id'],
                                                                                               'MailMonitoring.model' => 'DeviceCertificateData'
                                                                            )));
                        if(!empty($mailedtrue)){
                           // $summary = '';
                            $mailed++;
                            continue;
                        }
                        else{

			//pr($_certificates['DeviceCertificateData']);
                        $certificates[$_key] = $this->DeviceCertification($_certificates,array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'));

                        $summary = $this->DeviceCertificationSummary($summary,$certificates[$_key],array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'));
                     //   pr($summary);
                        foreach ($summary as $skey => $svalue) {
                            if($skey == 'hints') continue;
                            if(!empty($svalue[$mvalue['DeviceCertificate']['certificat']][$_certificates['DeviceCertificateData']['device_id']])) {
                                //pr($svalue[$mvalue['DeviceCertificate']['certificat']][$_certificates['DeviceCertificateData']['device_id']]);
                                //;
                                $sent_monitoring ['model'] ='DeviceCertificateData';
                                $sent_monitoring['id_cert_data'] = $_certificates['DeviceCertificateData'] ['id'];
                                $this->MailMonitoring->create();
                                $this->MailMonitoring->save($sent_monitoring);

                               // if(!empty($svalue[$mvalue['DeviceCertificate']['certificat']])) {$emptysummary = 0; break;} else {$emptysummary = 1;pr('test');}
                            }
                        }

                        }
                    }else{$certificates[$_key] = $this->DeviceCertification($_certificates,array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'));

                        $summary = $this->DeviceCertificationSummary($summary,$certificates[$_key],array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'));}
                    }
		}

              foreach ($summary as $s_key => $s_value) {
                if($s_key == 'hints') continue;
                if(!empty($s_value)){
                    $emptysummary = 0;
                    break;
                }else $emptysummary = 1;
              }


            //$emails = array('phillip.schmidt@mbq-gmbh.de','karsten.gatz@mbq-gmbh.de', 'ines.hoffner@mbq-gmbh.de');

            if($emptysummary == 0) {

               // foreach ($emails as $ekey => $evalue) {
                $emails = array('phillip.schmidt@mbq-gmbh.de');

                foreach ($emails as $ekey => $evalue) {

                $email = new CakeEmail();
                $email->config('default');


                $email->from('petra.jeckel@mbq-gmbh.de');

                $email->to($evalue);
!empty($mailed)?$betreff = utf8_encode('Geräteüberwachung').' '.$mvalue['DeviceCertificate']['certificat'].' noch offen: '.$mailed:$betreff = utf8_encode('Geräteüberwachung').' '.$mvalue['DeviceCertificate']['certificat'];

                $email->subject($betreff);
                $mailview ='summary_monitoring';
                 $email->template($mailview,'summary');
				$email->emailFormat('both');
				$email->viewVars(array(
							'summary_desc' => $summary_desc,
							'summary' => $summary,
							'commentar' => '',
    						)
						);

        $email->send();
                }
        $certificates = array();
        $summary = '';

                    //    }
        }
        }
        }
      /*  else{
        foreach ($testingmethods as $tm => $tmval) {
            $certificate = array();
            $this->DeviceTestingmethod->recursive = 1;
            $dev = $this->DeviceTestingmethod->find('all');
            foreach ($tmval['Device'] as $key => $value) {
                 		$certificates_options = array(

									'conditions' => array(
													//'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                                                                        'Device.id' => $value['id'],
													'Device.deleted' => 0,
													'DeviceCertificateData.active' => 1,
													'DeviceCertificateData.deleted' => 0,
													$_ids
												),

											);

		$certificate[] = $this->Device->DeviceCertificate->DeviceCertificateData->find('first',$certificates_options);


            }

		$certficates = array();

		$summary = array(
						'errors' => array(),
						'warnings' => array(),
						'futurenow' => array(),
						'future' => array(),
						'hints' => array(),
						'deactive' => array()
					);

		$summary_desc = array(
					'futurenow' => array(
							0 =>__('First-time certification reached',true),
							1 =>__('First-time certifications reached',true),
							),

					'future' => array(
							0 =>__('First-time certification',true),
							1 =>__('First-time certifications',true),
							),
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);

  		foreach($certificate as $_key => $_certificates){
               if(isset($_certificates['DeviceCertificateData'])) {
			$certificates[$_key] = $this->DeviceCertification($_certificates,array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'));

                        $summary = $this->DeviceCertificationSummary($summary,$certificates[$_key],array('top'=>'Device','main'=>'DeviceCertificate','sub'=>'DeviceCertificateData'));

                }
		}
               // pr($summary);


            $email = new CakeEmail();
            $email->config('default');


            $email->from('phillip.schmidt@mbq-gmbh.de');

            $email->to('phillip.schmidt@mbq-gmbh.de');

            $email->subject('Geraete'.' '.$tmval['DeviceTestingmethod']['verfahren']);
             $mailview ='summary_monitoring';
              $email->template($mailview,'summary');
				$email->emailFormat('both');
				$email->viewVars(array(
							'summary_desc' => $summary_desc,
							'summary' => $summary,
							'commentar' => '',
    						)
						);

        $email->send();
        $certificates = array();
        $summary = '';
        }}*/	//$SettingsArray = array();
//	}	$SettingsArray['devicelink'] = array('discription' => __('Back to overview',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
//		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devices','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);

		//$this->set('SettingsArray', $SettingsArray);

		//$this->__email($summary,$summary_desc,'summary_monitoring','Monitoringinfos');
	}


	public function DeviceCertification($data,$model) {

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
				$data = $this->TimeHorizonsForDevices($data,array('main'=>$model['main'],'sub'=>$model['sub']),$data[$model['sub']]['recertification_in_year']);

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

	public function TimeHorizonsForDevices($_data,$model) {
		//	$_data[$model['sub']]['period'] = $period;

			// Testen ob der Zeitpunkt zur ersten Zertifizierung erreicht wurde
			$firstCertification = date('Y-m-d', strtotime('+' . $_data[$model['sub']]['first_certification'] . ' '.__('months'), strtotime($_data[$model['sub']]['first_registration'])));

			if($firstCertification > date('Y-m-d',time())){

				$_data[$model['sub']]['first_certification_date'] = $firstCertification;
				$_data[$model['sub']]['certification_requested_class'] = 'is_not_requested';
				$_data[$model['sub']]['next_certification'] = null;
				$_data[$model['sub']]['next_certification_date'] = null;
				$_data[$model['sub']]['next_certification_horizon'] = $firstCertification;
				$_data[$model['sub']]['time_to_next_certification'] = null;
				$_data[$model['sub']]['time_to_next_horizon'] = null;
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->dateDiff($firstCertification,date('Y-m-d',time()));
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
				$_data[$model['sub']]['time_to_first_certification_date'] = $this->dateDiff($firstCertification,date('Y-m-d',time()));
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

      $nextCertification = $_data[$model['sub']]['expiration_date'];


			$nextCertificationHorizon = date('Y-m-d', strtotime('-' . $_data[$model['sub']]['horizon'] . ' months', strtotime($nextCertification)));

    	$datediffCertificatione = $this->dateDiff($nextCertification,date('Y-m-d',time()));
			$datediffHorizon = $this->dateDiff($nextCertificationHorizon,date('Y-m-d',time()));

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



}
