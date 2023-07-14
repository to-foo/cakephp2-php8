<?php
App::uses('AppController', 'Controller');
/**
 * Reportnumbers Controller
 *
 *
 * @property Reportnumber $Reportnumber
 */
class SignsController extends AppController
{
    public $components = array('Auth','Acl','Csv','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Search','Xml','Data','Drops','RequestHandler','Image','Pdf');
    public $helpers = array('Js','Lang','Navigation','JqueryScripte','ViewData','Html','Additions');
    public $layout = 'ajax';
    protected $writeprotection = false;

    // Das ist ein Testkommentar für GIT

    public function beforeFilter()
    {
        $this->Navigation->GetSessionForPaging();

        if ($this->RequestHandler->isAjax()) {
            if (!$this->Auth->login()) {
                header('Requires-Auth: 1');
            }
        }

        // muss man noch ne Funkton draus machen
        $Auth = $this->Session->read('Auth');

        App::import('Vendor', 'Authorize');
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');
        $this->loadModel('User');
        $this->loadModel('Topproject');
        $this->Autorisierung->Protect();

        $this->Lang->Choice();
        $this->Lang->Change();
        $this->Navigation->ReportVars();

        $noAjaxIs = 0;
        $noAjax = array();

        foreach ($noAjax as $_noAjax) {
            if ($_noAjax == $this->request->params['action']) {
                $noAjaxIs++;
                break;
            }
        }

        if ($noAjaxIs == 0) {
            $this->Navigation->ajaxURL();
        }

        $lang = $this->Lang->Discription();
        $this->request->lang = $lang;

        $this->set('writeprotection', $this->writeprotection);
        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
        $this->set('locale', $lang);
        $this->set('SettingsArray', array());

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }

        $SettingsArray = array();
        $this->set('SettingsArray', $SettingsArray);
    }

    public function afterFilter()
    {
        $this->Navigation->lastURL();
        $this->Navigation->SetSessionForPaging();
    }


    protected function __signMember($type)
    {

        // Die IDs des Projektes und des Auftrages werden getestet
        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $reportID = $this->request->projectvars['VarsArray'][3];
        $id = $this->request->projectvars['VarsArray'][4];

        $this->Reportnumber->recursive = 0;
        $reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));
        $reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);

        $reportnumbers = $this->Data->GetReportData($reportnumbers);

        $verfahren = $this->request->verfahren;
        $Verfahren = $this->request->Verfahren ;

        $ReportGenerally = $this->request->tablenames[0];
        $ReportSpecific = $this->request->tablenames[1];
        $ReportEvaluation = $this->request->tablenames[2];
        $ReportArchiv = $this->request->tablenames[3];
        $ReportSettings = $this->request->tablenames[4];
        $ReportPdf = $this->request->tablenames[5];

        $ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation);

        $arrayData = $this->Xml->DatafromXml($verfahren, 'file', $Verfahren);

        // Daten für mögliche vorhandene Dropdownfelder und Multiselects holen
        $reportnumbers = $this->Data->DropdownData($ReportArray, $arrayData, $reportnumbers);
        $reportnumbers = $this->Data->MultiselectData($ReportArray, $arrayData, $reportnumbers);
        $reportnumbers = $this->Data->ChangeDropdownData($reportnumbers, $arrayData, $ReportArray);
        $reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);

        $this->loadModel($ReportEvaluation);

        $evaluations = $this->$ReportEvaluation->find(
            'all',
            array(
                'conditions' =>
                    array(
                        'reportnumber_id' => $id,
                        'deleted' => 0
                    )
                )
        );

        unset($reportnumbers[$ReportEvaluation]);

        $reportnumbers[$ReportEvaluation] = $evaluations;

        $unevaluated = array();
        foreach ($reportnumbers[$ReportEvaluation] as $eval) {
            if (isset($eval[$ReportEvaluation]['result'])) {
                if ($eval[$ReportEvaluation]['result'] == '-') {
                    $unevaluated[$eval[$ReportEvaluation]['description']] = $eval[$ReportEvaluation]['description'];
                }
            }
        }

        $xml = $this->Xml->DatafromXml($verfahren, 'file', ucfirst($verfahren));

        $errors = $this->Reportnumber->getValidationErrors($xml, $reportnumbers, $verfahren);

        if (count($errors) > 0) {
            $this->set('errors', $errors);
        }

        $allmails = array();
        foreach ($xml['settings']->children() as $skey => $svalue) {
            //     pr($key);
            foreach ($svalue as $sv_key => $_svalue) {
                if ($_svalue->emailfield !=1) {
                    continue;
                }
                $emails = explode(" ", $reportnumbers[$skey][$sv_key]);
                $allmails = array_merge($emails, $allmails);
            }
        }
        $allmails[] = $reportnumbers['Testingcomp']['report_email'];
        $allmails[] = $reportnumbers['Topproject']['email'];
        $allmails = array_unique($allmails);
        $allmails = implode(", ", $allmails);

        $this->set('allmails', $allmails);
        // nacheinander unterschreiben als Standard
        $SignatoryCascading = true;
        $SignatoryAfterPrinting = true;
        $SignatoryClosing = true;

        if (Configure::check('SignatoryCascading')) {
            $SignatoryCascading = Configure::read('SignatoryCascading');
        }
        if (Configure::check('SignatoryAfterPrinting')) {
            $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
        }
        if (Configure::check('SignatoryClosing')) {
            $SignatoryClosing = Configure::read('SignatoryClosing');
        }

        $this->loadModel('Sign');
        $Signtest = $this->Sign->find('all', array('conditions' => array('Sign.reportnumber_id' => $id)));

        switch ($type) {
            case 1:
            $sign_for_action = 'signExaminer';
            $sign_for = __('Examiner', true);
            if (count($Signtest) > 0 && $SignatoryCascading == true) {
                $this->Session->setFlash(__('You can not sign this report again.', true));
                $this->set('stop_sign', true);
            }
            break;
            case 2:
            $sign_for_action = 'signSupervisor';
            $sign_for = __('Supervisor', true);
            if (count($Signtest) != 1 && $SignatoryCascading == true) {
                $this->Session->setFlash(__('You can not sign this report.', true) . ' ' . __('A lower priority signature is missing.', true));
                $this->set('stop_sign', true);
            }
            break;
            case 3:
            $sign_for_action = 'signThirdPart';
            $sign_for = __('Third part', true);
            if (count($Signtest) != 2 && $SignatoryCascading == true) {
                $this->Session->setFlash(__('You can not sign this report.', true) . ' ' . __('A lower priority signature is missing.', true));
                $this->set('stop_sign', true);
            }
            break;
            case 4:
            $sign_for_action = 'signFourPart';

            $sign_for = __('Four part', true);
            if (count($Signtest) != 2 && $SignatoryCascading == true) {
                $this->Session->setFlash(__('You can not sign this report.', true) . ' ' . __('A lower priority signature is missing.', true));
                $this->set('stop_sign', true);
            }
            break;
        }

        $Sign = $this->Sign->find('first', array('conditions' => array('Sign.reportnumber_id' => $id,'Sign.signatory' => $type)));

        $Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
        $this->request->verfahren = $Verfahren;
        $verfahren = $reportnumbers['Testingmethod']['value'];
        $arrayData = $this->Xml->DatafromXml($verfahren, 'file', $Verfahren);

        $ReportSettings = 'Report'.$Verfahren.'Setting';
        $ReportPdf = 'Report'.$Verfahren.'Pdf';

        $this->loadModel($ReportSettings);
        $optionsSettings = array('conditions' => array($ReportSettings.'.reportnumber_id' => $id));
        $Settings = $this->$ReportSettings->find('first', $optionsSettings);

        $menue = $this->Navigation->NaviMenue(null);

        $breads = $this->Navigation->BreadForReport($reportnumbers, 'sign');

        $breads[] = array(
                        'discription' => __('Signature', true) . ' ' . $sign_for,
                        'controller' => 'reportnumbers',
                        'action' => $sign_for_action,
                        'pass' => implode('/', $this->request->projectvars['VarsArray'])
                        );

        $breads = $this->Navigation->SubdivisionBreads($breads);
        $ReportMenue = $this->Navigation->createReportMenue($reportnumbers, $arrayData['settings']);

        $this->Session->delete('Sign');
        $this->Session->write('Sign', null);
        $this->Session->write('Sign.Signatory', $type);

        $WriteSignatoryColor['blue']['r'] = 0;
        $WriteSignatoryColor['blue']['g'] = 0;
        $WriteSignatoryColor['blue']['b'] = 255;

        if (Configure::check('WriteSignatoryColor')) {
            $WriteSignatoryColor = Configure::read('WriteSignatoryColor');
        }

        // Das Array für das Settingsmenü
        $SettingsArray = array();
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
        //	 	$SettingsArray['movelink'] = array('discription' => __('Move',true), 'controller' => 'reportnumbers','action' => 'move', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['addsearching'] = array('discription' => __('Searching', true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

        $SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

        $this->set('SignatoryClosing', $SignatoryClosing);
        $this->set('SignatoryAfterPrinting', $SignatoryAfterPrinting);
        $this->set('SignatoryCascading', $SignatoryCascading);
        $this->set('WriteSignatoryColor', $WriteSignatoryColor);
        $this->set('ReportMenue', $ReportMenue);
        $this->set('settings', $Settings);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('reportnumber', $reportnumbers);
        $this->set('breads', $breads);
        $this->set('menues', $menue);
        $this->set('sign_for', $sign_for);
        $this->set('generaloutput', $arrayData['generaloutput']);
        $this->set('signtype', $type);

        if (count($Sign) == 1) {
            $this->Reportnumber->User->recursive = -1;
            $this->Reportnumber->Testingcomp->recursive = -1;
            $User = $this->Reportnumber->User->find('list', array('fields' => array('name'), 'conditions' => array('User.id' => $Sign['Sign']['user_id'])));
            $Testingcomp = $this->Reportnumber->Testingcomp->find('list', array('fields' => array('firmenname'), 'conditions' => array('Testingcomp.id' => $Sign['Sign']['testingcomp_id'])));

            $openImage = $this->Image->setSignImage($reportnumbers, $Sign, $type);
            $this->set('openImage', $openImage);

            $this->set('User', reset($User));
            $this->set('Testingcomp', reset($Testingcomp));
            $this->set('Sign', $Sign);
            $this->render('sign_show');
        } else {
            $this->render('sign_examiner_socket');
        }
    }

    public function signExaminer()
    {
        $this->__signMember(1);
        return;
    }

    public function signSupervisor()
    {
        $this->__signMember(2);
        return;
    }

    public function signThirdPart()
    {
        $this->__signMember(3);

        return;
    }

    public function signFourPart()
    {
        $this->__signMember(4);
        return;
    }

    public function sign()
    {
        $this->Data->SignReport();
    }

    public function signtransport()
    {
        $this->layout = 'blank';
        $id = $this->request->projectvars['VarsArray'][4];
        $this->Session->write('Sign.Id', $id);

        // nacheinander unterschreiben als Standard
        $SignatoryCascading = true;
        $SignatoryAfterPrinting = true;
        $SignatoryClosing = true;

        if (Configure::check('SignatoryCascading')) {
            $SignatoryCascading = Configure::read('SignatoryCascading');
        }
        if (Configure::check('SignatoryAfterPrinting')) {
            $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
        }
        if (Configure::check('SignatoryClosing')) {
            $SignatoryClosing = Configure::read('SignatoryClosing');
        }

        $color = 'rgb(0, 0, 0)';

        if (isset($this->request->data['signature'])) {
            $this->Session->write('Sign.Signature', $this->request->data['signature']);
        }
        if (isset($this->request->data['image'])) {
            $this->Session->write('Sign.Image', $this->request->data['image']);
        }
        if (isset($this->request->data['color']) && !empty($this->request->data['color'])) {
            $this->Session->write('Sign.Color', $this->request->data['color']);
        }

        $Signatory = $this->Session->read('Sign');

        if (count($Signatory) > 4) {
            $color = $this->Session->read('Sign.Color');

            // Die Grafik wird eingefärbt
            $color = str_replace('rgb(', '', $color);
            $color = str_replace(')', '', $color);
            $color = explode(',', $color);

            if (count($color) == 3) {
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

            imagealphablending($im, false);

            for ($x = imagesx($im); $x--;) {
                for ($y = imagesy($im); $y--;) {
                    $c = imagecolorat($im, $x, $y);
                    $rgb['r'] = ($c >> 16) & 0xFF;
                    $rgb['g'] = ($c >> 8) & 0xFF;
                    $rgb['b'] = ($c & 0xFF);
                    $rgb['t'] = ($c >> 24) & 0x7F;
                    if ($rgb['t'] < 127) {
                        $colorB = imagecolorallocatealpha($im, $ncolor['r'], $ncolor['g'], $ncolor['b'], $rgb['t']);
                        imagesetpixel($im, $x, $y, $colorB);
                    }
                }
            }

            imagesavealpha($im, true);

            ob_start();
            imagepng($im);
            $colored_image =  ob_get_contents();
            ob_end_clean();
            imagedestroy($im);

            $Reportnumber = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $Signatory['Id'])));
            $Reportnumber = $this->Data->RevisionCheckTime($Reportnumber);

            $attribut_disabled = false;
            $currentstatus = $Reportnumber['Reportnumber']['status'];
            if ($Reportnumber['Reportnumber']['status'] > 0) {
                $attribut_disabled = true;
            }

            //			if(isset($Reportnumber['Reportnumber']['revision_write']) && $Reportnumber['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;

            // Wer wann unterschreiben darf, muss noch geklärt werden
            //			if($attribut_disabled == true) die('Sperre ' . $Signatory['Signatory']);

            $this->loadModel('Sign');
            $Sign = $this->Sign->find(
                'first',
                array('conditions' => array(
                  'Sign.reportnumber_id' => $Signatory['Id'],
                  'Sign.signatory' => $Signatory['Signatory']
                )
              )
            );

            $secretImage = Security::cipher($Signatory['Image'], Configure::read('SignatoryHash'));
            $secretColoredImage = Security::cipher(base64_encode($colored_image), Configure::read('SignatoryHash'));

            // Beginn bei speichern in Datei
            if ((Configure::check('SignatorySaveMethode') && Configure::read('SignatorySaveMethode') == 'file') || !Configure::check('SignatorySaveMethode')) {
                $report_id_chiper = bin2hex(Security::cipher($Reportnumber['Reportnumber']['id'], Configure::read('SignatoryHash')));
                $project_id_chiper = bin2hex(Security::cipher($Reportnumber['Reportnumber']['topproject_id'], Configure::read('SignatoryHash')));

                $path = Configure::read('SignatoryPfad') . $project_id_chiper . DS . $report_id_chiper . DS . $Signatory['Signatory'] . DS;

                // Wenn nicht vorhanden Ordner erzeugen
                if (!file_exists($path)) {
                    $dir_orginal = new Folder($path . 'orginal', true, 0755);
                    $dir_colored = new Folder($path . 'colored', true, 0755);

                    $file_orginal = new File($path . 'orginal' . DS . $report_id_chiper);
                    $file_colored = new File($path . 'colored' . DS . $report_id_chiper);

                    $file_orginal->write($secretImage);
                    $file_orginal->close();

                    $file_colored->write($secretColoredImage);
                    $file_colored->close();
                } else {
                    $this->Session->setFlash(__('Die Signatur konnte nicht gespeichert werden. Eine vorhandene Unterschrift konnte nicht gelöscht werden, wenden sie sich an einen Administrator.', true));
                    return;
                }
            }
            // Ende bei speichern in Datei

            $data = $Reportnumber;
            // Beginn bei speichern in Dateinbank
            if (Configure::check('SignatorySaveMethode') && Configure::read('SignatorySaveMethode') == 'data') {
                $data['Reportnumber']['image_orginal'] = $secretImage;
                $data['Reportnumber']['image'] = $secretColoredImage;
            }
            // Ende bei speichern in Dateinbank

            $secretSignatur = $Signatory['Signature'];

            $data['Reportnumber']['width'] = $im_width;
            $data['Reportnumber']['height'] = $im_height;
            $data['Reportnumber']['reportnumber_id'] = $data['Reportnumber']['id'];
            $data['Reportnumber']['signatory'] = $Signatory['Signatory'];
            $data['Reportnumber']['user_id'] = $this->Auth->user('id');

            $data_status['Reportnumber']['id'] = $data['Reportnumber']['id'];
            $Signatory['Signatory'];

            if ($SignatoryClosing == true) {
                $data_status['Reportnumber']['status'] = $Signatory['Signatory'];
                $data_status['Reportnumber']['revision_progress'] = 0;
                $this->Session->delete('revision.' . $Reportnumber['Reportnumber']['id']);
            }

            if (Configure::check('SignatoryKeepOpen') && Configure::read('SignatoryKeepOpen') >= $Signatory['Signatory'] && $Signatory['Signatory'] > $currentstatus) {
                $data_status['Reportnumber']['status'] = 0;
            }
            if (isset($Reportnumber['Reportnumber']['revision_write']) && $Reportnumber['Reportnumber']['revision_write'] == 1) {
                $data_status['Reportnumber']['revision_progress'] = 0;
                $data_status['Reportnumber']['print'] = 0;
                $this->Session->delete('revision.' . $Reportnumber['Reportnumber']['id']);
            }

            if ($SignatoryClosing == false) {
                $data_status['Reportnumber']['status'] = $Reportnumber['Reportnumber']['status'];
            }

            unset($data['Reportnumber']['id']);
            unset($data['Reportnumber']['created']);
            unset($data['Reportnumber']['modified']);

            // Vor dem Speichern der Unterschrift, noch prüfen ob der Bericht damit geschlossen werden soll<br />
            // wenn ja, müssen vorher die Pflichtfelder geprüft werden

            if (count($Sign) > 0) {
                $SignId = $Sign['Sign']['id'];
                $data['Reportnumber']['id'] = $SignId;
                if (!$this->Sign->save($data['Reportnumber'])) {
                    pr('Error 1');
                    return;
                }
            } else {
                $this->Sign->create();
                if (!$this->Sign->save($data['Reportnumber'])) {
                    pr('Error 2');
                    return;
                } else {
                    $this->Autorisierung->Logger($Signatory['Id'], array($Signatory['Signatory']));
                    $SignId = $this->Sign->getInsertID();
                    $Reportnumber = $this->Reportnumber->save($data_status);

                    // diese Array wird der Revison übergeben, wenn nötig
                    $TransportArray['model'] = 'Sign';
                    $TransportArray['row'] = null;
                    $TransportArray['this_value'] = $data['Reportnumber']['signatory'];
                    $TransportArray['last_id_for_radio'] = null;
                    $TransportArray['table_id'] = $SignId;
                    $TransportArray['last_value'] = $data['Reportnumber']['signatory'];

                    $Reportnumber['Reportnumber'] ['revision_progress'] > 0 ? $this->Data->SaveRevision($Reportnumber, $TransportArray, 'sign/add'):'';

                    $this->Reportnumber->recursive= 1;
                    $reportnumberForArchiv =  $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $Signatory['Id'])));
                    $reportsarchiv = $reportnumberForArchiv;


                    $Verfahren = ucfirst($reportsarchiv['Testingmethod']['value']);

                    // solange der Prüfbericht nicht geschlossen ist wird das Archiv aktualisiert

                    $this->Data->Archiv($reportsarchiv['Reportnumber']['id'], $Verfahren);

                    //Unterschriftendatum für das entsprechende Feld
                    if (Configure::check('SetDateFromSignatory') && (Configure::read('SetDateFromSignatory')== true)) {
                        $this->Data->SetDateFromSignatory($id, $Verfahren, $Signatory);
                    }
                }
            }

            $Sign = $this->Sign->find('first', array('conditions' => array('Sign.id' => $SignId)));

            if (count($Sign) > 0) {
                $openImage = $this->Image->setSignImage($Reportnumber, $Sign, $Signatory['Signatory']);

                $FormName = array('controller' => 'reportnumbers', 'action' => 'sign');

                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);
                if (Configure::check('sendmailreport') && Configure::read('sendmailreport') == true & Configure::check('sendreportminstatus')) {
                    $Sign['Sign'] ['signatory'] >= Configure::read('sendreportminstatus') ?   $this->set('sendmail', 1):$this->set('sendmail', 0);
                }
                $this->set('FormName', $FormName);
                $this->set('openImage', $openImage['image']);
            } else {
                $this->Session->setFlash(__('No signature could be generated.'));
            }
        }
    }

    public function removeSign()
    {
        $this->layout = 'modal';

        // Die IDs des Projektes und des Auftrages werden getestet
        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $reportID = $this->request->projectvars['VarsArray'][3];
        $id = $this->request->projectvars['VarsArray'][4];

        // nacheinander unterschreiben als Standard
        $SignatoryCascading = true;
        $SignatoryAfterPrinting = true;
        $SignatoryClosing = true;

        if (Configure::check('SignatoryCascading')) {
            $SignatoryCascading = Configure::read('SignatoryCascading');
        }
        if (Configure::check('SignatoryAfterPrinting')) {
            $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
        }
        if (Configure::check('SignatoryClosing')) {
            $SignatoryClosing = Configure::read('SignatoryClosing');
        }

        if ((isset($this->request['data']['remove_sign'])) && $this->request['data']['remove_sign'] == 1 && $this->request->is('post')) {
            $this->loadModel('Sign');

            if (!$this->Reportnumber->exists($id)) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }

            $this->Autorisierung->IsThisMyReport($id);

            $this->Reportnumber->recursive = 0;
            $reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));
            $reportnumbers = $this->Data->RevisionCheckTime($reportnumbers);

            $attribut_disabled = false;

            //			if($reportnumbers['Reportnumber']['status'] > 0) $attribut_disabled = true;
            if (isset($reportnumbers['Reportnumber']['revision_write']) && $reportnumbers['Reportnumber']['revision_write'] == 1) {
                $attribut_disabled = false;
            }

            if ($attribut_disabled == true) {
                die();
            }

            $report_id_chiper = bin2hex(Security::cipher($reportnumbers['Reportnumber']['id'], Configure::read('SignatoryHash')));
            $project_id_chiper = bin2hex(Security::cipher($reportnumbers['Reportnumber']['topproject_id'], Configure::read('SignatoryHash')));
            $path = Configure::read('SignatoryPfad') . $project_id_chiper . DS . $report_id_chiper . DS;

            if ($this->Sign->deleteAll(array('Sign.reportnumber_id' => $id), false)) {
                unlink($path);
                $folder = new Folder($path);
                if ($folder->delete()) {
                    pr('gelöscht');
                } else {
                    pr('nicht gelöscht');
                }

                $this->Reportnumber->recursive = 0;
                $reportnumbers = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));

                $this->request->data['Reportnumber']['id'] = $id;

                if ($SignatoryClosing == true) {
                    $this->request->data['Reportnumber']['status'] = 0;
                }
                //	 			if($SignatoryAfterPrinting == true) $this->request->data['Reportnumber']['print'] = 0;
                $this->request->data['Reportnumber']['print'] = 0;

                // Wenn Abrechnungen zurückgenommen werden müssem
                $this->Data->RollbackInvoice($reportnumbers);

                $this->Reportnumber->save($this->request->data);

                $this->Autorisierung->Logger($id, $this->request->data);

                // diese Array wird der Revison übergeben, wenn nötig
                $TransportArray['model'] = 'Sign';
                $TransportArray['row'] = null;
                $TransportArray['last_id_for_radio'] = null;
                $TransportArray['table_id'] = $id;
                $reportnumbers['Reportnumber'] ['revision_progress'] > 0 ? $this->Data->SaveRevision($reportnumbers, $TransportArray, 'sign/remove'): '';

                $FormName = array('controller' => 'reportnumbers', 'action' => 'sign');
                $FormName['terms'] = $projectID.'/'.$cascadeID.'/'.$orderID.'/'.$reportID.'/'.$id;
                $this->set('FormName', $FormName);

                return false;
            }
        } else {
            $this->Data->RemoveSign('examiner');
        }
    }
}
