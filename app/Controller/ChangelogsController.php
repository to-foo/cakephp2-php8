<?php
App::uses('AppController', 'Controller');
App::uses('ConnectionManager', 'Model');

/**
 * Examiners Controller
 *
 * @property Changelog $Examiner
 * @property AutorisierungComponent $Autorisierung
 * @property LangComponent $Lang
 * @property NavigationComponent $Navigation
 * @property SicherheitComponent $Sicherheit
 */
class ChangelogsController extends AppController {

    public $components = array(
        'Drops',
        'Formating',
        'Qualification',
        'Data',
        'Session',
        'Auth',
        'Acl',
        'Autorisierung',
        'Cookie',
        'Navigation',
        'Lang',
        'Sicherheit',
        'SelectValue',
        'Image',
        'Paginator',
        'Xml',
        'Search',
        'Drops'
    );
    protected $writeprotection = false;

    public $helpers = array(
        'Lang',
        'Navigation',
        'JqueryScripte',
        'Pdf',
        'Quality'
    );
    public $layout = 'ajax';

    public function _validateDate($date) {
        if (!is_string($date)) return false;
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    function beforeFilter() {
        //		if(Configure::read('ChangelogManager') == false){
        //	die();
        //	}
        App::import('Vendor', 'Authorize');
        $this->loadModel('User');
        //$this->Autorisierung->Protect();
        $noAjaxIs = 0;
        $noAjax = array();

        // Test ob die aktuelle Funktion per Ajax oder direkt aufgerufen werden soll
        foreach ($noAjax as $_noAjax) {
            if ($_noAjax == $this->request->params['action']) {
                $noAjaxIs++;
                break;
            }
        }

        if ($noAjaxIs == 0) {
            $this->Navigation->ajaxURL();
        }

        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');

        $this->Lang->Choice();
        $this->Lang->Change();
        $this->Navigation->ReportVars();

        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('menues', $this->Navigation->Menue());
        //		$this->set('breadcrumbs', $this->Navigation->Breadcrumb());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('lang_discription', $this->Lang->Discription());
        $this->set('previous_url', $this->base . '/' . $this->Session->read('lastURL'));
        $this->set('locale', $this->Lang->Discription());

    }

    function afterFilter() {
        $this->Navigation->lastURL();
    }

    public function index() {
        $this->layout = 'ajax';
    }

    public function dataupload() {
       $model = 'Changelogfile';
        $this->loadmodel('ChangelogData');
        //$this->loadModel('Changelogfile');
        $this->Sicherheit->ClamavScan();
        $ApplicationType = array(
            'image/jpeg',
            'image/png'
        );

        $uuid = $this->request->data["uuid"];
        $logData = $this->ChangelogData->find('first', array(
            'conditions' => array(
                'identifier' => $uuid,
            )
        ));
        if (!empty($logData)) {
          $logID = $logData['ChangelogData']['id'];
          $changelogID = $logData['ChangelogData']['changelog_id'];
          $fileName = $_FILES['file']['name'];
          $filePath = $_FILES['file']['tmp_name'];
          $fileTypeArray = explode('/',$_FILES['file']['type']);
          $fileType = $fileTypeArray[1];

          $fileNameNew = microtime(true);
          $fileNameNew = str_replace(',', '-', $fileNameNew);
          $fileNameNew .= $uuid . '.' . $fileType;

          $this->loadModel('Changelogfile');

          $filesave = array(
            'changelog_data_id' => intval($logID) ,
            'changelog_id' => intval($changelogID) ,
            'base_filename' => $fileName,
            'filename' => $fileNameNew
          );

          if($this->Changelogfile->save($filesave)){
            $querysave = array(
              $this->Changelogfile->name => $filesave
            );

            $changelog_data_id = $logData['ChangelogData']['id'];
            $changelog_data_category = $logData['ChangelogData']['category'];
            foreach($querysave as $querysavekey => $query){
              if($querysave[$querysavekey]['changelog_data_id'] == $changelog_data_id){
                if($changelog_data_category == 'test'){
                  unset($querysave[$querysavekey]);
                }
              }
            }

            if(!empty($querysave)){
              $this->generateSQLInsertQuery($querysave, false, true,$model);
            }
          }

        }

        //If Fehlschlag
        $savePath =  APP . 'update' . DS .'changelogsfiles' . DS . 'changelogs' . DS . $logID . DS;

        if (isset($_FILES['file']) && count($_FILES['file']) > 0) {

            $this->autoRender = false;

            $ExtensionGrant = false;

            foreach ($ApplicationType as $key => $value) {
                if ($value == $_FILES['file']['type']) {
                    $ExtensionGrant = true;
                    break;
                }
            }

            if ($ExtensionGrant === false) die();

            $this->request->data['Order'] = $_FILES;
        }
        //-----------
        if (isset($this->request['data']['Order'])) {
            $info = getimagesize($this->request['data']['Order']['file']["tmp_name"]);
            $basename = $this->request['data']['Order']['file']['name'];
            $suffix = false;

            if ($info['mime'] == 'image/jpeg') $suffix = 'jpg';
            if ($info['mime'] == 'image/png') $suffix = 'png';

            if ($suffix == false) {

                /*$this->Flash->error($_FILES['file']['name'] . ' ' . __('Wrong file format', true) , array(
                    'key' => 'error'
                ));*/
                unset($_FILES);
                unset($this->request->data['Order']);

            }
        }
        if (isset($this->request['data']['Order'])) {

            if ($_FILES['file']['error'] > 0) {
                $max_upload = (int)(ini_get('upload_max_filesize'));
              /*  $this->Flash->error(Configure::read('FileUploadErrors.' . $_FILES['file']['error']) . ' ' . __('Your file is bigger than', true) . ' ' . $max_upload . 'MB' . ' ' . $dataImage['basename'], array(
                    'key' => 'error'
                ));*/
                unset($_FILES);
                unset($this->request->data['Order']);
            }

            $errortest = $this->Image->CheckFileSize($this->request['data']['Order']['file']["tmp_name"]);

            if ($errortest === false) {
              /*  $this->Flash->error(__('Internal error, file too big.', true) , array(
                    'key' => 'error'
                ));*/
                unset($_FILES);
                unset($this->request->data['Order']);
                return;
            }

            // Wenn nicht vorhanden Ordner erzeugen
            if (!file_exists($savePath)) {
                $dir = new Folder($savePath, true, 0755);
            }

            move_uploaded_file($this->request['data']['Order']['file']["tmp_name"], $savePath . '/' . $fileNameNew);

            $dataImage['basename'] = $this->request['data']['Order']['file']['name'];
          /*  $this->Flash->success(__('The following image was saved successfully:', true) . ' ' . $dataImage['basename'], array(
                'key' => 'success'
            ));*/
        }
    }

    public function view() {
        $this->layout = 'modal';
        $locale = $this->Lang->Discription();
        $this->loadModel('Changelog');

        $id = $this->request->projectvars['VarsArray'][0];
        $options = array(
          'order' => array('log_date' => 'DESC')
        );
        if(isset($id) && $id > 0){
          $options = array('conditions' => array(
            'Changelog.id '=> $id),
          );
        }
        $changelogs = $this->Changelog->find('all', $options);

        foreach ($changelogs as $logkey => $log) {

          $log_date = $log['Changelog']['log_date'];
          $dateStr = date_create($log_date)->format('d.m.Y');
          $changelogs [$logkey]['Changelog']['log_date_de'] = $dateStr;

          if(empty($log['ChangelogData'])) unset($changelogs[$logkey]);

          if(!isset($log["Changelogfile"])) continue;
          if(count($log["Changelogfile"]) == 0) continue;

          foreach ($log["Changelogfile"] as $fileskey => $files) {

            $savePath =  APP . 'update' . DS .'changelogsfiles' . DS . 'changelogs' . DS . $files["changelog_data_id"] . DS;

            if(file_exists($savePath.$files["filename"])){
              list($width, $height) = getImageSize($savePath . DS . $files["filename"]);
              $img_b64 = $this->Image->ImageToBase64($savePath . DS . $files["filename"]);
              if(!empty($img_b64)) {
                $log["Changelogfile"][$fileskey]['imagedata'] = 'data:png;base64,'.$img_b64;
              }
            }

            $changelogs[$logkey]["Changelogfile"] = $log["Changelogfile"];
          }
        }

        $this->set('Changelogs', $changelogs);

        $FormName['controller'] = 'topprojects';
        $FormName['action'] = 'start';
        $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function add() {

      $this->layout = 'modal';
      $locale = $this->Lang->Discription();
      $this->loadModel('Changelog');

      if (isset($this->request->data['Changelog']) && ($this->request->is('post') || $this->request->is('put'))) {

        $this->Changelog->set($this->request->data);
        $errors = array();

        $this->Changelog->create();

        $savechangelog = array(
          'Changelog' => $this->request->data['Changelog'],
        );

        if ($this->Changelog->save($savechangelog)) {
          $savechangelog['Changelog']['id'] = $this->Changelog->getLastInsertID();

          $dataIndex = explode(',', $this->request->data['changelog_data_child_index']);
          $count = 0;

          foreach ($dataIndex as $idx) {
            $this->request->data['ChangelogData' . $idx]['changelog_id'] = $this->Changelog->getLastInsertID();
            $savechangelogdata['ChangelogData'] = $this->request->data['ChangelogData' . $idx];
            $savechangelogdata['ChangelogData']["id"] = 0;

            if($result = $this->Changelog->ChangelogData->save($savechangelogdata)) {
              $model = 'ChangelogData';
              if($result[$model]['category'] !== "test") {
                $this->generateSQLInsertQuery($result, false, true, $model);
                $count++;
              }
            }else{
              $this->Flash->error(__('Die Changelog Daten konnten nicht gespeichert werden.') , array(
                'key' => 'error'
              ));
            }
          }

          //Wenn nur Testeinträge vorhanden sind Changelog nicht hinzufügen zu changelogs.sql
          if($count > 0) {
            $model = 'Changelog';
            $this->generateSQLInsertQuery($savechangelog, false, true, $model);
          }

          $this->Flash->success(__('The Changelog has been saved') , array(
            'key' => 'success'
          ));
          $FormName['controller'] = 'topprojects';
          $FormName['action'] = 'start';
          $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);
        }
        else {
          $this->Flash->error(__('Der Changelog konnte nicht gespeichert werden.') , array(
            'key' => 'error'
          ));
        }
      }

      $this->Changelog->recursive = -1;
      $schema = $this->Changelog->schema();

      $changelog = array(
        'Changelog' => array()
      );

      foreach ($schema as $key => $value) {
        $device['Changelog'][$key] = null;
      }

      $this->request->data = $changelog;

      $SettingsArray = array();
      $SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);
      $this->set('SettingsArray', $SettingsArray);
    }
}
