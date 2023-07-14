<?php
App::uses('AppController', 'Controller');
App::uses('ConnectionManager', 'Model');
/**
 * Welders Controller
 *
 * @property Welder $Welder
 * @property AutorisierungComponent $Autorisierung
 * @property LangComponent $Lang
 * @property NavigationComponent $Navigation
 * @property SicherheitComponent $Sicherheit
 */
class WeldersController extends AppController
{
    public $components = array('Qualification','Data','Session','Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','SelectValue', 'Paginator','Xml','Drops');
    protected $writeprotection = false;

    public $helpers = array('Lang','Navigation','JqueryScripte','Pdf','Quality');
    public $layout = 'ajax';
    public $uses = array('Welder', 'WelderTime');

    public function _validateDate($date)
    {
        if (!is_string($date)) {
            return false;
        }
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    public function beforeFilter()
    {
        $abort = true;
        $abort &= !Configure::read('WelderManager');
        //$abort &= !Configure::read('WorkloadManager');

        if ($abort) {
            die();
        }

        if ($this->request->params['action'] == 'testgraph') {
            return;
        }
        App::import('Vendor', 'Authorize');
        $this->loadModel('User');
        $this->Autorisierung->Protect();

        $noAjaxIs = 0;
        $noAjax = array('printworkload_xxx','certificatesfiles','certificatefile','getfiles','getcertificatefiles','getmonitoringfile','monitoringfile','getcertificatefile','eyecheckfile','eyecheckfiles','geteyecheckfile','geteyecheckfiles','pdf','pdfinv','files','quicksearch','email','autocomplete','filesweldingcomp','getweldercompfiles');

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

        $this->Welder->Testingcomp->Topproject->Report->Reportlock->recursive = -1;
        $this->writeprotection = 0 < $this->Welder->Testingcomp->Topproject->Report->Reportlock->find('count', array('conditions'=>array('Reportlock.topproject_id'=>$this->request->projectID, 'Reportlock.report_id'=>$this->request->reportID)));
        $this->set('writeprotection', $this->writeprotection);

        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('menues', $this->Navigation->Menue());
        //		$this->set('breadcrumbs', $this->Navigation->Breadcrumb());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('lang_discription', $this->Lang->Discription());
        $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
        $this->set('locale', $this->Lang->Discription());

        // Werte fÃƒÂ¼r die Schnellsuche
        $ControllerQuickSearch =
            array(
                'Model' => 'Welder',
                'Field' => 'name',
                'description' => __('Name', true),
                'minLength' => 2,
                'targetcontroller' => 'welders',
                'target_id' => 'welder_id',
                'targetation' => 'overview',
                'Quicksearch' => 'QuickWeldersearch',
                'QuicksearchForm' => 'QuickWeldersearchForm',
                'QuicksearchSearchingAutocomplet' => 'QuickWeldersearchSearchingAutocomplet',
        );

        $this->set('ControllerQuickSearch', $ControllerQuickSearch);

        $WelderManagerAutocomplete = null;

        if (Configure::check('WelderManagerSearchAutocomplete') && Configure::read('WelderManagerSearchAutocomplete') == true) {
            $WelderManagerAutocomplete =
                array(
                    'Model' => 'Welder',
                );
        }

        $this->request->data['WelderManagerAutocomplete'] = $WelderManagerAutocomplete;

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }
    }

    public function afterFilter()
    {
        $this->Navigation->lastURL();
    }

    public function quicksearch()
    {
        App::uses('Sanitize', 'Utility');

        $this->__quicksearch();
    }

    protected function __quicksearch()
    {
      $this->layout = 'json';

      $model = 'Welder';
      $field = 'name';
      $term = Sanitize::escape($this->request->data['term']);

      $options = array(
        'limit' => 5,
        'conditions' => array(
          $model.'.'.$field.' LIKE' => '%'.$term.'%',
          $model.'.deleted' => 0,
          $model.'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
        )
      );

//      $this->$model->recursive = -1;
      $data = $this->$model->find('all', $options);

      $response = array();

      foreach ($data as $_key => $_data) {
        array_push($response,
          array(
            'key' => $_data[$model]['id'],
            'value' => $_data[$model]['name'] . ' ' . $_data[$model]['first_name'] . ' (' . $_data['Testingcomp']['name'] . ')'
            )
        );
      }

      $this->set('response', json_encode($response));
    }

    public function save($id = null)
    {
        // Die IDs des Projektes und des Auftrages werden getestet

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        $this->layout = 'blank';


        if (key($this->request->data['orginal_data']) == 'Welder') {
            $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
            $id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

            if (!$this->Welder->exists($welder_id)) {
                throw new NotFoundException(__('Invalid welder'));
            }

            $Model = key($this->request->data['orginal_data']);
        }

        if (key($this->request->data['orginal_data']) == 'WelderCertificate') {
            $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
            $id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

            if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid certificate'));
            }

            $Model = key($this->request->data['orginal_data']);
            $this->loadModel($Model);
        }

        if (key($this->request->data['orginal_data']) == 'WelderEyecheck') {
            $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
            $id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

            if (!$this->Welder->WelderEyecheck->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid vision test'));
            }

            $Model = key($this->request->data['orginal_data']);
            $this->loadModel($Model);
        }

        $this_id = $this->request->data['this_id'];
        $orginal_data = $this->request->data['orginal_data'];
        unset($this->request->data['orginal_data']);

        foreach ($this->request->data as $_key => $_data) {
            if (!is_array($_data)) {
                unset($this->request->data[$_key]);
            }
        }

        $orginalData = $this->$Model->find('first', array('conditions' => array($Model.'.id' => $id)));

        $field_key = key($this->request->data[$Model]);

        if ($field_key == '0') {
            $field_key = key($this->request->data[$Model][0]);
            $this_value = $this->request->data[$Model][0][$field_key];
        } else {
            $this_value = $this->request->data[$Model][$field_key];
        }

        $last_value = $orginalData[$Model][$field_key];
        $this_field = key($this->request->data[$Model]);
        $last_id_for_radio = $Model . Inflector::camelize($field_key);

        $this->set('this_id', $this_id);
        $this->set('last_value', $last_value);
        $this->set('this_value', $this_value);
        $this->set('last_id_for_radio', $last_id_for_radio);

        if ($orginalData[$Model]['active'] == 0 && $this_field != 'active') {
            $this->set('closed', __('The value could not be saved. The record is deactive!'));
            $this->render();
            return;
        }

        if ($orginalData[$Model]['deleted'] == 1) {
            $this->set('closed', __('The value could not be saved. The record is deleted!'));
            $this->render();
            return;
        }

        $option = array(
                        'id' => $id,
                        $field_key => $this_value
                    );

        if ($Model == 'WelderCertificate') {

            // Qulifikationen mit diesen Werten dÃƒÂ¼rfen nicht doppelt vorhanden sein
            if (isset($option['level']) || isset($option['weldingmethod']) || isset($option['sector'])) {
                $this->$Model->recursive = -1;
                $orginal_data = $this->$Model->find(
                    'first',
                    array(
                                                    'fields' => array('id','sector','level','weldingmethod','welder_id'),
                                                    'conditions' => array(
                                                        $Model.'.id'=>$id
                                                        )
                                                    )
                );

                $orginal_data_test_option = array_merge($orginal_data['WelderCertificate'], $option);
                $orginal_data_test_option['deleted'] = 0;
                unset($orginal_data_test_option['id']);
                $orginal_data_test = $this->$Model->find('first', array('conditions' => $orginal_data_test_option));

                if (count($orginal_data_test) > 0) {
                    $this->set('closed', __('The value could not be saved. There is a qualification with the following values: '.$orginal_data_test['WelderCertificate']['sector'].'/'.$orginal_data_test['WelderCertificate']['weldingmethod'].'/'.$orginal_data_test['WelderCertificate']['level']));
                    $this->render();
                    return;
                }
            }
        }

        if ($this->$Model->save($option)) {

            // Falls eine Qualifikation mit geringerem Level vorhanden ist,
            // wird diese deaktiviert
            if ($Model == 'WelderCertificate' && isset($option['level'])) {
                unset($orginal_data_test_option['level']);
                $orginal_data_test_option['id !='] = $id;
                $orginal_data_test_option['level <'] = $option['level'];
                $orginal_data_test = $this->$Model->find(
                    'all',
                    array(
                                                            'fields' => array('id','sector','weldingmethod','level'),
                                                            'conditions' => $orginal_data_test_option
                                                            )
                );

                $hint_quilification = null;
                if (count($orginal_data_test) > 0) {
                    foreach ($orginal_data_test as $_orginal_data_test) {
                        $_orginal_data_test['WelderCertificate']['active'] = 0;
                        $this->WelderCertificate->WelderCertificateData->updateAll(array('active' => 0), array('certificate_id' => $_orginal_data_test['WelderCertificate']['id']));
                        $hint_quilification .= $_orginal_data_test['WelderCertificate']['sector'].'/'.$_orginal_data_test['WelderCertificate']['weldingmethod'].'/'.$_orginal_data_test['WelderCertificate']['level'];
                    }

                    $this->set('hint', __('The Qualification with lower level has been disabled' . ': '.$hint_quilification));
                }
            }

            $this->Autorisierung->Logger($id, $this->request->data[$Model]);
            $this->set('success', __('The value has been saved.'));
        } else {
            $this->set('error', __('The value could not be saved. Please try again!'));
        }
    }

    public function testgraph()
    {
        $this->layout = 'jpgraph';

        $this->loadModel('WelderTime');
        $this->WelderTime->recursive = -1;
        $this->set('welderTimes', $this->WelderTime->find('all', array('conditions'=>array('WelderTime.welder_id'=>14))));
        $this->render('list_welder_workload_month_gantt');
    }
    /**
     * index method
     *
     * @return void
     */
    public function indexcomp()
    {
        $this->Session->delete('searchwelder');

        $this->Navigation->ResetSessionForPaging();

        $methods = array();

        if ($this->Auth->user('roll_id') > 4) {
            //   pr($this->Auth->user('testingcomp_id'));
            $welder_comps =$this->Welder->find('all', array('conditions' => array('testingcomp_id' =>$this->Auth->user('testingcomp_id'))));
        } else {
            $welder_comps = array();
        }



        !empty($welder_comps)?$welder_comps = Hash::extract($welder_comps, '{n}.Testingcomp'):'';

        $welder_comp = array_unique($welder_comps, SORT_REGULAR);



        if (empty($welder_comps)) {
            $this->loadmodel('Testingcompcat');
            $categoryid = $this->Testingcompcat->find('first', array('fields'=>'id','conditions' => array('identification' => 'wd')));
            $this->loadmodel('TestingcompcatsTestingcomps');
            $this->loadmodel('Testingcomp');
            $this->Testingcomp->recursive = -1;
            $Testingcompsid = $this->TestingcompcatsTestingcomps->find('all', array('fields'=>'testingcomp_id','conditions' => array('testingcomp_category_id' => $categoryid['Testingcompcat']['id'] )));
            $Testingcompsids= Hash::extract($Testingcompsid, '{n}.TestingcompcatsTestingcomps.testingcomp_id');
            $welder_comps = $this->Testingcomp->find('all', array('conditions'=>array('Testingcomp.id' =>$Testingcompsids)));
            $welder_comp = Hash::extract($welder_comps, '{n}.Testingcomp');
        }

        foreach ($welder_comp as $_key => $_welder_comp) {
            if (($this->Auth->user('testingcomp_id') <> $_welder_comp ['id'])&& ($this->Auth->user('roll_id') > 4)) {
                unset($welder_comp[$_key]);
            };
            //			pr($welder_comp);
        }
        $SettingsArray = array();

        if (Configure::read('CertifcateManager') == true) {
            $SettingsArray['examinerlink'] = array('discription' => __('Examiners administration', true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        }


        //$SettingsArray['addsearching'] = array('discription' => __('Search welder', true), 'controller' => 'welders', 'action'=>'search', 'terms'=>null, 'rel'=>'devices');



        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Welding Companies', true);
        $breads[1]['controller'] = 'welders';
        $breads[1]['action'] = 'indexcomp';
        $breads[1]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('welder_comp', $welder_comp);

        if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) 		$this->render('indexcomp_landingpage','blank');

    }

    public function indexpart()
    {
        $weldingcompid = $this->request->projectvars['VarsArray'][14];
        $welders = $this->Welder->find('first', array('conditions' => array('Welder.testingcomp_id' => $weldingcompid,'Welder.supervisor' => 0)));
        !empty($welders)?$this->set('welderok', 1):$this->set('welderok', 0);
        $supervisor = $this->Welder->find('first', array('conditions' => array('Welder.testingcomp_id' => $this->Autorisierung->CheckTestingcompsLinks($weldingcompid) ,'Welder.supervisor' => 1)));
        !empty($supervisor) ? $this->set('supervisor', 1):$this->set('supervisor', 0);
        $welder_comps = $this->Welder->Testingcomp->find('first', array('conditions' => array('Testingcomp.id' => $this->Autorisierung->CheckTestingcompsLinks($weldingcompid))));

        $welder_comps = $welder_comps ['Testingcomp'];

        if(is_array($welder_comps)) {
          $welder_comp = array_unique($welder_comps, SORT_REGULAR);
        }

        $this->set('compid', $weldingcompid);
        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Welding Companies', true);
        $breads[1]['controller'] = 'welders';
        $breads[1]['action'] = 'indexcomp';
        $breads[1]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[2]['discription'] = $welder_comps ['name'];
        $breads[2]['controller'] = 'welders';
        $breads[2]['action'] = 'indexpart';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];


        $SettingsArray = array();
        $this->set('breads', $breads);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('welder_comp', $welder_comp);
    }

    public function index()
    {
        $this->include_index();
    }

    protected function include_index()
    {


        $this->Navigation->GetSessionForPaging();

        $this->Welder->recursive = 1;
        $this->Session->delete('searchwelder');
        $testingcomp_id = $this->request->projectvars['VarsArray'][14];

        $backlink= $this->request->projectvars['VarsArray'];

        $this->Welder->Testingcomp->recursive = 1;
        $comp = $this->Welder->Testingcomp->find('first', array('conditions'=>array('Testingcomp.id' => $this->Autorisierung->CheckTestingcompsLinks($testingcomp_id) )));
        $weldingcompcheck = 0;

        if (isset($comp['Testingcompcat'])) {
            foreach ($comp['Testingcompcat'] as $catkey => $catval) {
                $catval['identification'] == 'wd' ? $weldingcompcheck = 1 : '';
            }
        }
        if ($weldingcompcheck  > 1) {
            return;
        }

        $xml = $this->Xml->DatafromXml('display_welder', 'file', null);

        if (!is_object($xml['settings'])) {
            pr('Einstellungen konnten nicht geladen werden.');
            return;
        } else {
            $this->set('xml', $xml['settings']);
        }

        $summary = array();

        $options['Testingcomp.id'] =$this->Autorisierung->CheckTestingcompsLinks($testingcomp_id);
        $options['Welder.deleted'] = 0;


        if (isset($this->request->projectvars['VarsArray'][17]) && $this->request->projectvars['VarsArray'] [17] == 1) {
            $breaddesc = __('Welder Supervisor');
            $options['Welder.supervisor'] = 1;
            $this->request->projectvars['VarsArray'][17] = 0;
        } else {
            $options['Welder.supervisor >='] = 0;
        }
        if (isset($this->request->data['welder_id']) && $this->request->data['welder_id'] > 0) {
            $welder_id = $this->Sicherheit->Numeric($this->request->data['welder_id']);
            $options['Welder.id'] = $welder_id;
        }

        $SettingsArray = array();
        //		$SettingsArray['addlink'] = array('discription' => __('Add welder',true), 'controller' => 'welders','action' => 'add', 'terms' => null);

        $this->paginate = array(
            'conditions' => array($options),
            'order' => array('Welder.name' => 'asc','Welder.first_name' => 'asc'),
            'limit' => 10
        );
        $welder = $this->paginate('Welder');

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] =   __('Welding Companies', true);
        $breads[1]['controller'] = 'welders';
        $breads[1]['action'] = 'indexcomp';
        $breads[1]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[2]['discription'] = $comp['Testingcomp']['name'];
        $breads[2]['controller'] = 'welders';
        $breads[2]['action'] = 'indexpart';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = __('Welder Parts', true);
        $breads[3]['controller'] = 'welders';
        $breads[3]['action'] = 'indexpart';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        isset($breaddesc)? $breads[4]['discription'] = $breaddesc:$breads[4]['discription'] =__('Welders', true);
        $breads[4]['controller'] = 'welders';
        $breads[4]['action'] = 'index';
        $breads[4]['pass'] = $backlink;
        foreach ($welder as $_key => $_welder) {

            $welder_link = $this->request->projectvars['VarsArray'];
            $summary[$_welder['Welder']['id']]['summary'] = $this->Qualification->WelderCertificateSummary($this->Qualification->WelderCertificatesSectors($_welder), array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));
            $summary[$_welder['Welder']['id']]['eyecheck'] = $this->Qualification->WelderEyecheckSummary($this->Qualification->WelderEyechecks($_welder), array('main' => 'WelderEyecheck','sub' => 'WelderEyecheckData'));
            $welder_link[13] = 0;
            $welder_link[14] = $testingcomp_id;
            $welder_link[15] = $_welder['Welder']['id'];

            unset($welder_link[16]);
            unset($welder_link[17]);

            foreach ($summary[$_welder['Welder']['id']]['summary']['qualifications'] as $__key => $__qualifications) {
                $welder[$_key]['Welder']['_welder_qualification'][] = array(
                                                                                'certificate' => $__qualifications['certificat'],
                                                                                'qualification' => $__qualifications,
                                                                                'term' => $__qualifications['term'],
                                                                                'termlink' => 'welder/certificate/' . implode('/', $__qualifications['term']),
                                                                                'certificate_id' => $__qualifications['certificate_id'],
                                                                                'class' => $__qualifications['class']
                                                                            );
            }

            $welder[$_key]['Welder']['_welder_link'] = $welder_link;

            // alle Zertifikatsnummer sammeln
            // Foth
            $all_certificats_no = array();
            if (isset($welder[$_key]['Welder']['_welder_qualification'])) {
                foreach ($welder[$_key]['Welder']['_welder_qualification'] as $__key => $_welder_qualification) {
                    if ($_welder_qualification['certificate'] == '-') {
                        continue;
                    } else {
                        $all_certificats_no[] = $_welder_qualification['certificate'];
                    }
                }

                $all_certificats_no = array_unique($all_certificats_no);
            }

            if (count($all_certificats_no) > 0) {
                $welder[$_key]['WelderCertificate']['certificat'] = implode('/', $all_certificats_no);
            } else {
                $welder[$_key]['WelderCertificate']['certificat'] = '-';
            }

            $this->loadmodel('ReportVtstGenerally');
            $this->loadmodel('ReportVtstEvaluation');
            $this->loadmodel('Reportnumber');
            if (!empty($_welder['Welder']['identification'])) {
                $reportids = $this->ReportVtstGenerally->find('all', array('limit'=> 10,'fields'=>array('reportnumber_id','date_of_test'),'order'=>array('ReportVtstGenerally.reportnumber_id DESC'),'conditions'=>array('ReportVtstGenerally.welder'=> $_welder['Welder']['identification'])));
                $i = 0;
                $weldertests =array() ;

                foreach ($reportids as $rkey => $rvalue) {
                    $Report = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.delete'=> 0,'Reportnumber.id'=>$rvalue['ReportVtstGenerally']['reportnumber_id'])));

                    if (count($Report) == 0) {
                        continue;
                    }

                    if ($Report['Reportnumber']['delete'] == 0) {
                        $evaluations =  $this->ReportVtstEvaluation->find('all', array('fields'=>array('material','dimension','result_vt','result'),'conditions'=> array('reportnumber_id'=>$rvalue['ReportVtstGenerally']['reportnumber_id'],'deleted'=>0)));

                        $datetest['Reportnumber'] = $Report['Reportnumber'];
                        $datetest['Reportnumber']['date_of_test'] = $rvalue['ReportVtstGenerally']['date_of_test'];
                        $eval = array();
                        $resarray = array('-','e','ne');
                        $weldertests = array();

                        foreach ($evaluations as $evkey => $evalue) {

                            if (!empty($evalue['ReportVtstEvaluation']['result_vt'])) {
                                $resvt = $resarray[$evalue['ReportVtstEvaluation']['result_vt']];
                            } else {
                                $resvt = $resarray[0];
                            }
                            if (!empty($evalue['ReportVtstEvaluation']['result'])) {
                                $resrt = $resarray[$evalue['ReportVtstEvaluation']['result']];
                            } else {
                                $resrt = $resarray[0];
                            }

                            $eval['material'] = $evalue['ReportVtstEvaluation']['material'];
                            $eval['dimension'] = $evalue['ReportVtstEvaluation']['dimension'];
                            $eval['resvt'] = $resvt;
                            $eval['resrt'] = $resrt;
                        }


                        if(isset($eval['resrt']) && isset($eval['resvt'])) {
                          if (!$eval['resrt'] && $eval['resvt'] == 'e' && $eval['resrt'] == 'e') {
                              $datetest['evaluation_class'] = 'welderlink_okay';
                          } elseif (!$eval['resrt'] && $eval['resvt'] == 'ne' || $eval['resrt'] == 'ne') {
                              $datetest['evaluation_class'] = 'welderlink_error';
                          } else {
                              $datetest['evaluation_class'] = 'welderlink_open';
                          }
                        } else {
                          $datetest['evaluation_class'] = 'welderlink_open';
                        }

                        $datetest['evaluation'][] = $eval;
                        $weldertests= $datetest;
                        if (!empty($weldertests)) {
                            $welder[$_key]['WelderTest'][] = $weldertests;
                        } else {
                            $welder[$_key]['WelderTest'][] = array();
                        }
                    }
                }
            }
        }


        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        }

        if (Configure::read('search.welder') == true) {
            $SettingsArray['addsearching'] = array('discription' => __('Search welder', true), 'controller' => 'welders', 'action'=>'search', 'terms'=>null, 'rel'=>'welders');
        }
        foreach ($welder as $_key => $_welders) {
            // pr($_welders);
            $monitorings[$_welders['Welder']['id']] = $this->Qualification->WelderMonitoringSummary($_welders, array('top'=>'Welder','main'=>'WelderMonitoring','sub'=>'WelderMonitoringData'));
        }

        $this->set('SettingsArray', $SettingsArray);
        $this->set('comp', $comp);

        $this->set('breads', $breads);
        $this->set('welders', $welder);
        $this->set('summary', $summary);
        if(isset($monitorings)) $this->set('monitorings', $monitorings);

        $this->Navigation->SetSessionForPaging();

        // Infinite scroll
        if (Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true) {
            $this->Navigation->InfiniteScrollPaging('index_infinite');
        }
        if (isset($options['Welder.supervisor'])&&   $options['Welder.supervisor'] == 1) {
            $this->render('index_superv');
        }
    }

    public function email_eyecheck()
    {
        $this->layout = 'modal';

        $message  = null;
        $message .= __('Eine Zusammenfassung von anstehenden und fehlerhaften Sehtests, wird an die eingegebene E-Mail-Adresse versand.');
        $message .= '<br>';
        $message .= __('Sie kÃƒÂ¶nnen Meldungen abwÃƒÂ¤hlen in dem Sie die Checkbox der Meldung deaktivieren.');

        $this->set('message', $message);

        $_ids = array();

        if (isset($this->request->data['Welder']) && ($this->request->is('post') || $this->request->is('put'))) {
            $emailto = $this->request->data['Welder']['email'];
            $isValid = Validation::email($emailto);

            if ($isValid === true) {
                $_id = array();
                foreach ($this->request->data['Welder'] as $_key => $_data) {
                    if (is_numeric($_key)) {
                        if ($_data == 1) {
                            $_id[] = $_key;
                        }
                    }
                }

                $_ids = array('WelderEyecheckData.id' => $_id);
            }
        }


        $certificates = array();

        $eyechecks_options = array(
                                    'order' => array('WelderEyecheckData.id DESC'),
                                    'conditions' => array(
                                                    'Welder.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                    'Welder.deleted' => 0,
                                                    $_ids
                                                )
                                            );

        $eyechecks = $this->Welder->WelderEyecheck->WelderEyecheckData->find('all', $eyechecks_options);

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
                            0 =>__('First-time eyecheck reached', true),
                            1 =>__('First-time eyecheck reached', true),
                            ),

                    'future' => array(
                            0 =>__('First-time eyecheck', true),
                            1 =>__('First-time eyecheck', true),
                            ),
                    'errors' => array(
                            0 =>__('Irregularity', true),
                            1 =>__('Irregularities', true),
                            ),
                    'warnings' => array(
                            0 => __('Warning', true),
                            1 => __('Warnings', true)
                            ),
                    'hints' => array(
                            0 => __('Hint', true),
                            1 => __('Hints', true)
                            ),
                    'deactive' => array(
                            0 => __('Deactive', true),
                            1 => __('Deactive', true)
                            )
                        );

        foreach ($eyechecks as $_key => $_certificates) {
            $__certificates = $this->Qualification->WelderEyechecks($_certificates);
            $___certificates = $this->Qualification->WelderEyecheckSummary($__certificates, array('main'=>'WelderEyecheck','sub'=>'WelderEyecheckData'));

            $certificates[$_certificates['Welder']['id']]['summary'] = $___certificates;

            // Nach Fehler/Hinweisarten sortieren
            foreach ($summary as $__key => $_summary) {
                if (isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0) {
                    $summaryeyecheck[$__key][$_certificates['Welder']['id']] = $___certificates['summary'][$__key];
                }
            }
        }

        $this->__email($summaryeyecheck, $summary_desc, 'summary_eyecheck', 'Sehtestinfos');
    }

    public function email_certificate()
    {
        $welderid = $this->request->projectvars['VarsArray'][15];
        $lastModalURLArray['controller'] = 'welders';
        $lastModalURLArray['action'] = 'index';
        $lastModalURLArray['pass'] = array();
        $lastModal = $this->Session->read('lastModalURLArray');

        if (is_array($lastModal) && count($lastModal) == 3) {
            $lastModalURLArray['controller'] = $this->Session->read('lastModalURLArray.controller');
            $lastModalURLArray['action'] = $this->Session->read('lastModalURLArray.action');
            $lastModalURLArray['pass'] = $this->Session->read('lastModalURLArray.pass');
        }

        $SettingsArray = array();

        $this->set('lastModalURLArray', $lastModalURLArray);

        $this->set('SettingsArray', $SettingsArray);
        $this->layout = 'modal';


        $emailto = Configure::read('EmailWelderInfos');
        $emailto = explode(',', $emailto);

        $message  = null;
        $message .= __('Möchten Sie eine Zusammenfassung des Schweißers an die folgenden Stellen versenden?.');
        $message .= '<br>';
        foreach ($emailto as $et =>$ev) {
            $message .= '- '.$ev.'<br>';
        }

        $this->set('message', $message);

        $_ids = array();
        $welder = $this->Welder->find('first', array('conditions' =>array('Welder.id' =>$welderid)));





        $certificates_options = array(
                                    'order' => array('WelderMonitoring.id DESC'),
                                    'conditions' => array(
                                                    'Welder.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                    'Welder.deleted' => 0,
                                                    'WelderMonitoring.active' => 1,
                                                    'WelderMonitoring.deleted' => 0,
                                                    'Welder.id' => $welderid,												)
                                            );

        $certificate = $this->Welder->WelderMonitoring->find('all', $certificates_options);


        $certificates = '';
        foreach ($certificate as $key => $value) {
            $certificates.= $value['WelderMonitoring'] ['certificat']."\n";
        }



        if (isset($this->request->data['Welder']) && $this->request->is('post') || $this->request->is('put')) {
            foreach ($emailto as $ekey => $evalue) {
                $comment = $this->request->data['Welder']['comment'];

                $isValid = Validation::email($evalue);


                if (!isset($this->request->data['url']['pass'])) {
                    $url['pass'] = array();
                }

                if ($isValid === true) {
                    App::uses('CakeEmail', 'Network/Email');

                    $commentar = null;


                    $subject = 'Information Schweißer';
                    $email = new CakeEmail('default');
                    $email->domain(FULL_BASE_URL);
                    $email->from(array('reminder@mbq-gmbh.info' => 'Schweißerdatenbank'));
                    $email->sender(array('reminder@mbq-gmbh.info' => 'Schweißerdatenbank'));
                    $email->to($evalue);
                    $email->subject($subject);
                    $email->template('certificate', 'certificate');
                    $email->emailFormat('text');
                    $email->viewVars(
                        array(
                            'welder' => $welder,
                            'certificates' => $certificates,
                                                        'comment' => $comment,
                            )
                    );
                    $email->send();

                    $this->set('lastModalURLArray', $url);
                    $this->set('message', __('E-Mail versendet', true));
                    $this->render('email_successful_send', 'modal');
                } elseif ($isValid === false) {
                    $this->set('message', __('E-Mail konte nicht versendet werden. Sie haben keine gültige E-Mail-Adresse angegeben.', true));
                    $this->set('lastModalURLArray', $url);
                }
            }
        }
    }



    protected function __email($summary, $summary_desc, $mailview, $subject)
    {
        $lastModalURLArray['controller'] = 'welders';
        $lastModalURLArray['action'] = 'index';
        $lastModalURLArray['pass'] = array();
        $lastModal = $this->Session->read('lastModalURLArray');

        if (is_array($lastModal) && count($lastModal) == 3) {
            $lastModalURLArray['controller'] = $this->Session->read('lastModalURLArray.controller');
            $lastModalURLArray['action'] = $this->Session->read('lastModalURLArray.action');
            $lastModalURLArray['pass'] = $this->Session->read('lastModalURLArray.pass');
        }

        $SettingsArray = array();

        $this->set('lastModalURLArray', $lastModalURLArray);
        $this->set('summary_desc', $summary_desc);
        $this->set('summary', $summary);
        $this->set('SettingsArray', $SettingsArray);

        if (isset($this->request->data['Welder']) && ($this->request->is('post') || $this->request->is('put'))) {
            $emailto = $this->request->data['Welder']['email'];
            $isValid = Validation::email($emailto);
            $url['controller'] = $this->request->data['url']['controller'];
            $url['action'] = $this->request->data['url']['action'];
            $url['pass'] = $this->request->data['url']['pass'];

            if (!isset($this->request->data['url']['pass'])) {
                $url['pass'] = array();
            }

            if ($isValid === true) {
                App::uses('CakeEmail', 'Network/Email');

                $commentar = null;

                if ($this->request->data['Welder']['comment'] != '') {
                    App::uses('Sanitize', 'Utility');
                    $commentar = Sanitize::stripScripts($this->request->data['Welder']['comment']);
                    $this->set('commentar', $commentar);
                }

                $email = new CakeEmail('default');
                $email->domain(FULL_BASE_URL);
                $email->from(array('reminder@mbq-gmbh.info' => 'SchweiÃ¯Â¿Â½erdatenbank'));
                $email->sender(array('reminder@mbq-gmbh.info' => 'SchweiÃ¯Â¿Â½erdatenbank'));
                $email->to($emailto);
                $email->subject($subject);
                $email->template($mailview, 'summary');
                $email->emailFormat('both');
                $email->viewVars(
                    array(
                            'summary_desc' => $summary_desc,
                            'summary' => $summary,
                            'commentar' => $commentar,
                            )
                );
                $email->send();

                $this->set('lastModalURLArray', $url);
                $this->set('message', __('E-Mail versendet an '.$emailto, true));
                $this->render('email_successful_send', 'modal');
            } elseif ($isValid === false) {
                $this->set('message', __('E-Mail konte nicht versendet werden. Sie haben keine gÃƒÂ¼ltige E-Mail-Adresse angegeben.', true));
                $this->set('lastModalURLArray', $url);
            }
        }
    }

    public function single_eyecheck_summary($id)
    {
        $welder_id = $id;

        $this->__singlesummary($welder_id);
    }

    public function singlesummary($welder_id)
    {
        $this->layout = 'modalsummary';

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        $arrayData = $this->Xml->DatafromXml('Welder', 'file', null);
        $welder = $this->Welder->find('first', array('conditions' => array('Welder.id' => $welder_id)));

        $certificates = $this->Qualification->WelderCertificatesSectors($welder);
        $summary = $this->Qualification->WelderCertificateSummary($certificates, array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));

        $eyechecks = $this->Qualification->WelderEyechecks($welder);
        $eyechecksummary = $this->Qualification->WelderEyecheckSummary($eyechecks, array('main'=>'WelderEyecheck','sub'=>'WelderEyecheckData'));

        $this->set('welder', $welder);
        $this->set('eyechecks', $eyechecks);
        $this->set('eyechecksummary', $eyechecksummary);
        $this->set('certificates', $certificates);
        $this->set('summary', $summary);

        $this->render('singlesummary');
    }

    public function summary()
    {
        $this->layout = 'modal';
        $certificates = array();

        $certificates_options = array(
                                    'order' => array('WelderCertificateData.id DESC'),
                                    'conditions' => array(
                                                    'Welder.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                    'Welder.deleted' => 0,
                                                    'WelderCertificateData.active' => 1,
                                                    'WelderCertificateData.deleted' => 0
                                                )
                                            );

        $certificate = $this->Welder->WelderCertificate->WelderCertificateData->find('all', $certificates_options);

        $certficates = array();
        $summary = array(
                        'futurenow' => array(),
                        'future' => array(),
                        'errors' => array(),
                        'warnings' => array(),
                        'hints' => array(),
                        'deactive' => array()
                    );

        $summaryeyecheck = array(
                        'futurenow' => array(),
                        'future' => array(),
                        'errors' => array(),
                        'warnings' => array(),
                        'hints' => array(),
                        'deactive' => array()
                    );

        foreach ($certificate as $_key => $_certificates) {
            $__certificates = $this->Qualification->WelderCertificatesSectors($_certificates);
            $___certificates = $this->Qualification->WelderCertificateSummary($__certificates, array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));

            $certificates[$_certificates['Welder']['id']]['summary'] = $___certificates;

            // Nach Fehler/Hinweisarten sortieren
            foreach ($summary as $__key => $_summary) {
                if (isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0) {
                    $summary[$__key][$_certificates['Welder']['id']] = $___certificates['summary'][$__key];
                }
            }
        }

        $this->set('certificates', $certificates);

        $certificates = array();

        $eyechecks_options = array(
                                    'order' => array('WelderEyecheckData.id DESC'),
                                    'conditions' => array(
                                                    'Welder.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                    'Welder.deleted' => 0
                                                )
                                            );

        $eyechecks = $this->Welder->WelderEyecheck->WelderEyecheckData->find('all', $eyechecks_options);
        foreach ($eyechecks as $_key => $_certificates) {
            $__certificates = $this->Qualification->WelderEyechecks($_certificates);
            $___certificates = $this->Qualification->WelderEyecheckSummary($__certificates, array('main'=>'WelderEyecheck','sub'=>'WelderEyecheckData'));

            $certificates[$_certificates['Welder']['id']]['summary'] = $___certificates;

            // Nach Fehler/Hinweisarten sortieren
            foreach ($summary as $__key => $_summary) {
                if (isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0) {
                    $summaryeyecheck[$__key][$_certificates['Welder']['id']] = $___certificates['summary'][$__key];
                }
            }
        }

        $summary_desc = array(
                    'futurenow' => array(
                            0 =>__('First-time certification reached', true),
                            1 =>__('First-time certifications reached', true),
                            ),

                    'future' => array(
                            0 =>__('First-time certification', true),
                            1 =>__('First-time certifications', true),
                            ),
                    'errors' => array(
                            0 =>__('Irregularity', true),
                            1 =>__('Irregularities', true),
                            ),
                    'warnings' => array(
                            0 => __('Warning', true),
                            1 => __('Warnings', true)
                            ),
                    'hints' => array(
                            0 => __('Hint', true),
                            1 => __('Hints', true)
                            ),
                    'deactive' => array(
                            0 => __('Deactive', true),
                            1 => __('Deactive', true)
                            )
                        );

        $SettingsArray = array();

        $this->set('summary_desc', $summary_desc);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('summary', $summary);
        $this->set('summaryeyecheck', $summaryeyecheck);
    }
    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function overview()
    {
        $this->Session->delete('Welder.form');

        // Wenn der PrÃƒÂ¼fer ÃƒÂ¼ber die Schnellsuche aufgerufen wird
        if ($this->request->projectvars['VarsArray'][15] == 0 && isset($this->request->data['welder_id']) && $this->request->data['welder_id'] > 0) {
            $this->request->projectvars['VarsArray'][15] = $this->Sicherheit->Numeric($this->request->data['welder_id']);
        }

        $id = $this->request->projectvars['VarsArray'][15];
        //		$this->request->projectvars['VarsArray'][15] = $id;


        if (!$this->Welder->exists($id)) {
            throw new NotFoundException(__('Invalid welder'));
        }


        $arrayData = $this->Xml->DatafromXml('Welder', 'file', null);
        $this->Welder->recursive = -1;
        $welder = $this->Welder->find('first', array('conditions' => array('Welder.id' => $id)));

        $imagePath = Configure::read('welder_picture_folder') . $id . DS.$welder['Welder']['profile_picture'];

        if (file_exists($imagePath)) {
            $imageinfo = getimagesize($imagePath);
            $imageContent = file_get_contents($imagePath);
            $imData = base64_encode($imageContent);
            $this->set('mime', $imageinfo['mime']);
            $this->set('imagePath', $imagePath);
            $this->set('imData', $imData);
        }
        $this->request->data = $welder;


        $options = array(
                        'conditions' => array(
                                            'WelderMonitoring.deleted' => 0,
                                            'WelderMonitoring.welder_id' => $id,
                                            'WelderMonitoring.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );

        $this->Welder->WelderMonitoring->recursive = -1;
        $WelderMonitoring = $this->Welder->WelderMonitoring->find('first', $options);

        if (count($WelderMonitoring) == 1) {
            $welder['WelderMonitoring'] =
            $WelderMonitoring['WelderMonitoring'];
        }

        $this->request->data = $this->Data->GeneralDropdownData(array('Welder'), $arrayData, $this->request->data);

        $certificates = $this->Qualification->WelderCertificatesSectors($welder);
        $summary = $this->Qualification->WelderCertificateSummary($certificates, array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));
        $this->set('summary', $summary['summary']);

        $eyechecks = $this->Qualification->WelderEyechecks($welder);
        $eyechecksummary = $this->Qualification->WelderEyecheckSummary($eyechecks, array('main'=>'WelderEyecheck','sub'=>'WelderEyecheckData'));
        $this->set('eyechecksummary', $eyechecksummary['summary']);

        $SettingsArray = array();

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        }

        $breads = $this->Navigation->Breads(null);


        unset($this->request->projectvars['VarsArray'][16]);
        unset($this->request->projectvars['VarsArray'][17]);

        $breads[1]['discription'] =   __('Welding Companies', true);
        $breads[1]['controller'] = 'welders';
        $breads[1]['action'] = 'indexcomp';
        $breads[1]['pass'] = null;

        $breads[2]['discription'] = $welder['Testingcomp']['name'];
        $breads[2]['controller'] = 'welders';
        $breads[2]['action'] = 'indexpart';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = __('Welders', true);
        $breads[3]['controller'] = 'welders';
        $breads[3]['action'] = 'index';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[4]['discription'] = $welder['Welder']['first_name'] . ' ' . $welder['Welder']['name'];
        $breads[4]['controller'] = 'welders';
        $breads[4]['action'] = 'overview';
        $breads[4]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);
        $this->set('eyechecks', $eyechecks);
        $this->set('arrayData', $arrayData);
        $this->set('welder', $welder);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function eyechecks()
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        $arrayData = $this->Xml->DatafromXml('Welder', 'file', null);

        $this->Welder->recursive = -1;
        $welder = $this->Welder->find('first', array('conditions' => array('Welder.id' => $welder_id)));

        $this->Welder->WelderEyecheck->recursive = -1;
        $eyecheck_options = array(
                                'order' => array('WelderEyecheckData.id DESC'),
                                'conditions' => array(
                                        'WelderEyecheckData.welder_id' => $welder_id
                                    ),
                                );

        $eyechecks = $this->Welder->WelderEyecheck->WelderEyecheckData->find('first', $eyecheck_options);
        if (count($eyechecks) > 0) {
            $this->request->data = $eyechecks;
            $this->request->data = $this->Data->GeneralDropdownData(array('Welder'), $arrayData, $this->request->data);

            $eyechecks['WelderEyecheckData']['period'] = $eyechecks['WelderEyecheckData']['recertification_in_year'];
            $eyechecks = $this->Qualification->WelderTimeHorizons($eyechecks, array('main'=>'WelderEyecheck','sub'=>'WelderEyecheckData'), $eyechecks['WelderEyecheckData']['period']);

            $eyechecks = $this->Qualification->WelderEyechecks($welder);
            $eyechecksummary = $this->Qualification->WelderEyecheckSummary($eyechecks, array('main'=>'WelderEyecheck','sub'=>'WelderEyecheckData'));

            $this->set('eyechecksummary', $eyechecksummary['summary']);
        } else {
            $this->request->data = $welder;
        }

        $this->request->projectvars['VarsArray'][15] = $welder_id;
        $SettingsArray = array();

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        }

        unset($this->request->projectvars['VarsArray'][16]);
        unset($this->request->projectvars['VarsArray'][17]);

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Welders', true);
        $breads[1]['controller'] = 'welders';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        $breads[2]['discription'] = $welder['Welder']['first_name'] . ' ' . $welder['Welder']['name'];
        $breads[2]['controller'] = 'welders';
        $breads[2]['action'] = 'overview';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = __('eye checks');
        $breads[3]['controller'] = 'welders';
        $breads[3]['action'] = 'eyechecks';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $this->request->projectvars['VarsArray'][16] = isset($eyechecks['WelderEyecheck']['id']) ? $eyechecks['WelderEyecheck']['id'] : '' ;
        $this->request->projectvars['VarsArray'][17] = isset($eyechecks['WelderEyecheckData']['id']) ? $eyechecks['WelderEyecheckData']['id'] : '';

        $this->set('breads', $breads);
        $this->set('arrayData', $arrayData);
        $this->set('welder', $welder);
        $this->set('eyechecks', $eyechecks);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function eyecheck()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $modus = $this->Sicherheit->Numeric($this->request->data['modus']);
        $this->set('modus', $modus);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderEyecheck->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid eyecheck'));
        }

        if (isset($this->request->data['WelderEyecheck']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            if ($this->Welder->WelderEyecheck->save($this->request->data)) {
                $this->Session->setFlash(__('The eye check has been saved'));
                $this->Autorisierung->Logger($certificate_id, $this->request->data['WelderEyecheck']);
            } else {
                $this->Session->setFlash(__('The eye check could not be saved. Please, try again.'));
            }
        }

        $eyecheck_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderEyecheckData.certificate_id' => $certificate_id,
                                        'WelderEyecheckData.deleted' => 0,
                                        'WelderEyecheckData.active' => 1
                                        )
                                    );

        $eyecheck_data = $this->Welder->WelderEyecheck->WelderEyecheckData->find('first', $eyecheck_data_option);

        if (empty($eyecheck_data)) {
            $eyecheck_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderEyecheckData.certificate_id' => $certificate_id,
                                        'WelderEyecheckData.deleted' => 0,
                                        'WelderEyecheckData.active' => 0
                                        )
                                    );
            $eyecheck_data = $this->Welder->WelderEyecheck->WelderEyecheckData->find('first', $eyecheck_data_option);
        }

        $arrayData = $this->Xml->DatafromXml('WelderEyecheck', 'file', null);

        $eyecheck_data['WelderEyecheckData']['period'] = $eyecheck_data['WelderEyecheck']['recertification_in_year'];

        $eyecheck_data = $this->Qualification->WelderTimeHorizons($eyecheck_data, array('main'=>'WelderEyecheck','sub'=>'WelderEyecheckData'), $eyecheck_data['WelderEyecheckData']['period']);

        $this->request->data = $eyecheck_data;

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to overview',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        $this->request->projectvars['VarsArray'][15] = $welder_id;
        //		$SettingsArray['eyechecklink'] = array('discription' => __('Welder eyechecks',true), 'controller' => 'welders','action' => 'eyechecks', 'terms' => $this->request->projectvars['VarsArray']);
        $this->request->projectvars['VarsArray'][16] = $certificate_id;
        //		$SettingsArray['dellink'] = array('discription' => __('Delete this certification',true), 'controller' => 'welders','action' => 'removeeyecheck', 'terms' => $this->request->projectvars['VarsArray']);
        //		$SettingsArray['filelink'] = array('discription' => __('Show document',true), 'controller' => 'welders','action' => 'eyecheckfiles', 'terms' => $this->request->projectvars['VarsArray']);
        //		$SettingsArray['userlink'] = array('discription' => __('Welder overview',true), 'controller' => 'welders','action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);

        $FileUploadErrors = $this->Session->read('FileUploadErrors');

        if (isset($FileUploadErrors)) {
            $this->set('FileUploadErrors', $FileUploadErrors);
            $this->set('message', Configure::read('FileUploadErrors.' . $FileUploadErrors));
        }

        $this->set('SettingsArray', $SettingsArray);
        $this->set('eyecheck_data', $eyecheck_data);
        $this->set('settings', $arrayData['headoutput']);
    }

    public function neweyecheck()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        $certificate_data_option = array(
                                    'conditions' => array(
                                        'Welder.id' => $welder_id,
                                        )
                                    );

        $certificate_data = $this->Welder->find('first', $certificate_data_option);
        $this->set('certificate_data', $certificate_data);

        if (isset($this->request->data['WelderEyecheck']) && ($this->request->is('post') || $this->request->is('put'))) {
            $message = null;

            // muss noch im Helper korrigiert werden
            foreach ($this->request->data['WelderEyecheck'] as $_key => $_certificate) {
                if ($_key == '0') {
                    foreach ($this->request->data['WelderEyecheck'][0] as $__key => $__certificate) {
                        $this->request->data['WelderEyecheck'][$__key] = $__certificate;
                    }
                }
            }

            unset($this->request->data['WelderEyecheck'][0]);
            unset($this->request->data['Order']);

            $this->request->data['WelderEyecheck']['welder_id'] = $certificate_data['Welder']['id'];
            $this->request->data['WelderEyecheck']['user_id'] = $this->Auth->user('id');

            if ($this->Welder->WelderEyecheck->save($this->request->data)) {
                $message .= __('The vision test has been saved');
                $this->Session->setFlash($message);

                $certificate_id = $this->Welder->WelderEyecheck->getLastInsertId();

                $this->Autorisierung->Logger($certificate_id, $this->request->data['WelderEyecheck']);

                $certificate_data = $this->Welder->WelderEyecheck->find('first', array('conditions' => array('WelderEyecheck.id' => $certificate_id)));

                $certificate_data_data = array(
                                            'certificate_id' => $certificate_id,
                                            'welder_id' => $this->request->data['WelderEyecheck']['welder_id'],
                                            'certified' => 1,
                                            'first_registration' => $certificate_data['WelderEyecheck']['first_registration'],
                                            'first_certification' => 0,
                                            'certified_date' => $certificate_data['WelderEyecheck']['first_registration'],
                                            'horizon' => $this->request->data['WelderEyecheck']['horizon'],
                                            'recertification_in_year' => $certificate_data['WelderEyecheck']['recertification_in_year'],
                                            'active' => 1,
                                            'deleted' => 0,
                                            'user_id' => $this->Auth->user('id')
                                            );

                $this->Welder->WelderEyecheck->WelderEyecheckData->save($certificate_data_data);
                $certificate_data_id = $this->Welder->WelderEyecheck->WelderEyecheckData->getLastInsertId();

                $certificate_data_option = array(
                                    'conditions' => array(
                                        'WelderEyecheckData.id' => $certificate_data_id,
                                        )
                                    );

                $certificate_data = $this->Welder->WelderEyecheck->WelderEyecheckData->find('first', $certificate_data_option);

                $this->request->projectvars['VarsArray'][15] = $certificate_data['Welder']['id'];
                $this->request->projectvars['VarsArray'][16] = $certificate_data['WelderEyecheck']['id'];
                $this->request->projectvars['VarsArray'][17] = $certificate_data['WelderEyecheckData']['id'];

                $this->set('certificate_data', $certificate_data);
            } else {
                $this->Session->setFlash(__('The vision test could not be saved. Please, try again.'));
            }
        }

        $arrayData = $this->Xml->DatafromXml('WelderEyecheck', 'file', null);

        $certificate_data_option = array('conditions' => array('Welder.id' => $welder_id));
        $certificate_data = $this->Welder->WelderEyecheck->WelderEyecheckData->find('first', $certificate_data_option);

        $this->request->data = $certificate_data;
        $this->request->data = $this->Data->GeneralDropdownData(array('WelderCertificate'), $arrayData, $this->request->data);

        unset($this->request->data['WelderEyecheck']);
        unset($this->request->data['WelderEyecheckData']);

        $this->request->data['WelderEyecheck']['level'] = 0;
        $this->request->data['WelderEyecheck']['supervisor'] = 0;
        $this->request->data['WelderEyecheck']['vt'] = 0;
        $this->request->data['WelderEyecheck']['vt_requirement_s1'] = 0;
        $this->request->data['WelderEyecheck']['vt_requirement_f1'] = 0;
        $this->request->data['WelderEyecheck']['vt_requirement_f2'] = 0;
        $this->request->data['WelderEyecheck']['vt_requirement_b'] = 0;
        $this->request->data['WelderEyecheck']['vt_requirement_sb'] = 0;
        $this->request->data['WelderEyecheck']['vt_requirement_g1'] = 0;
        $this->request->data['WelderEyecheck']['pt'] = 0;
        $this->request->data['WelderEyecheck']['pt_requirement_s1'] = 0;
        $this->request->data['WelderEyecheck']['pt_requirement_f1'] = 0;
        $this->request->data['WelderEyecheck']['pt_requirement_f2'] = 0;
        $this->request->data['WelderEyecheck']['pt_requirement_b'] = 0;
        $this->request->data['WelderEyecheck']['pt_requirement_sb'] = 0;
        $this->request->data['WelderEyecheck']['pt_requirement_g1'] = 0;
        $this->request->data['WelderEyecheck']['mt'] = 0;
        $this->request->data['WelderEyecheck']['mt_requirement_s1'] = 0;
        $this->request->data['WelderEyecheck']['mt_requirement_f1'] = 0;
        $this->request->data['WelderEyecheck']['mt_requirement_f2'] = 0;
        $this->request->data['WelderEyecheck']['mt_requirement_b'] = 0;
        $this->request->data['WelderEyecheck']['mt_requirement_sb'] = 0;
        $this->request->data['WelderEyecheck']['mt_requirement_g1'] = 0;
        $this->request->data['WelderEyecheck']['ut'] = 0;
        $this->request->data['WelderEyecheck']['ut_requirement_s1'] = 0;
        $this->request->data['WelderEyecheck']['ut_requirement_f1'] = 0;
        $this->request->data['WelderEyecheck']['ut_requirement_f2'] = 0;
        $this->request->data['WelderEyecheck']['ut_requirement_b'] = 0;
        $this->request->data['WelderEyecheck']['ut_requirement_sb'] = 0;
        $this->request->data['WelderEyecheck']['ut_requirement_g1'] = 0;
        $this->request->data['WelderEyecheck']['sv'] = 0;
        $this->request->data['WelderEyecheck']['sv_requirement_s1'] = 0;
        $this->request->data['WelderEyecheck']['sv_requirement_f1'] = 0;
        $this->request->data['WelderEyecheck']['sv_requirement_f2'] = 0;
        $this->request->data['WelderEyecheck']['sv_requirement_b'] = 0;
        $this->request->data['WelderEyecheck']['sv_requirement_sb'] = 0;
        $this->request->data['WelderEyecheck']['sv_requirement_g1'] = 0;

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $welder_id;
        //		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'welders','action' => 'eyechecks', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['headoutput']);
    }

    public function editeyecheck()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderEyecheck->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Welder->WelderEyecheck->WelderEyecheckData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        if (isset($this->request->data['WelderEyecheckData']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            if ($this->Welder->WelderEyecheck->WelderEyecheckData->save($this->request->data)) {
                $this->Session->setFlash(__('The eye check infos has been saved'));
                $this->Autorisierung->Logger($certificate_data_id, $this->request->data['WelderEyecheckData']);
            } else {
                $this->Session->setFlash(__('The eye check infos could not be saved. Please, try again.'));
            }

            $FormName['controller'] = 'welders';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $eyecheck_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderEyecheckData.id' => $certificate_data_id,
                                        'WelderEyecheckData.deleted' => 0,
                                        'WelderEyecheckData.active' => 1
                                        )
                                    );

        $eyecheck_data = $this->Welder->WelderEyecheck->WelderEyecheckData->find('first', $eyecheck_data_option);

        if (empty($eyecheck_data)) {
            $eyecheck_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderEyecheckData.certificate_id' => $certificate_id,
                                        'WelderEyecheckData.deleted' => 0,
                                        'WelderEyecheckData.active' => 0
                                        )
                                    );

            $eyecheck_data = $this->Welder->WelderEyecheck->WelderEyecheckData->find('first', $eyecheck_data_option);
        }

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $eyecheck_data;

        $arrayData = $this->Xml->DatafromXml('WelderEyecheckData', 'file', null);

        $this->request->projectvars['VarsArray'][17] = $certificate_data_id;

        $SettingsArray = array();
        //		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'welders','action' => 'eyecheck', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('eyecheck_data', $eyecheck_data);
        $this->set('settings', $arrayData['orderoutput']);
    }

    public function qualifications()
    {
        $id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        if (!$this->Welder->exists($id)) {
            throw new NotFoundException(__('Invalid welder'));
        }
        $testingcompid = $this->request->projectvars['VarsArray'][14];
        $testingcomp =  $this->Welder->Testingcomp->find('first', array('conditions' => array('Testingcomp.id' => $testingcompid)));
        // Wenn qualifications mit einer Variablen fÃƒÂ¼r eine spezielle Qualifikation aufgerufen wird
        // wird dies mit der Variable QualiMarking ÃƒÂ¼bergeben, um diese Qualifikation im View zu markieren
        // Foth, 14.08.2017
        if (isset($this->request->projectvars['VarsArray'][16]) && $this->request->projectvars['VarsArray'][16] > 0) {
            $this->set('QualiMarking', $this->request->projectvars['VarsArray'][16]);
        } else {
            $this->set('QualiMarking', 0);
        }

        $arrayData = $this->Xml->DatafromXml('Welder', 'file', null);

        $this->Welder->recursive = -1;
        $welder = $this->Welder->find('first', array('conditions' => array('Welder.id' => $id)));
        $certificates = $this->Qualification->WelderCertificatesSectors($welder);

        $this->request->data = $welder;
        $this->request->data = $this->Data->GeneralDropdownData(array('Welder'), $arrayData, $this->request->data);

        $summary = $this->Qualification->WelderCertificateSummary($certificates, array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));

        $this->request->projectvars['VarsArray'][15] = $id;
        $SettingsArray = array();

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        }

        unset($this->request->projectvars['VarsArray'][16]);
        unset($this->request->projectvars['VarsArray'][17]);

        $breads = $this->Navigation->Breads(null);



        $breads[1]['discription'] =   __('Welding Companies', true);
        $breads[1]['controller'] = 'welders';
        $breads[1]['action'] = 'indexcomp';
        $breads[1]['pass'] = null;

        $breads[2]['discription'] = $testingcomp['Testingcomp']['name'];
        $breads[2]['controller'] = 'welders';
        $breads[2]['action'] = 'indexpart';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];


        $breads[3]['discription'] = __('Welders', true);
        $breads[3]['controller'] = 'welders';
        $breads[3]['action'] = 'index';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[4]['discription'] = $welder['Welder']['first_name'] . ' ' . $welder['Welder']['name'];
        $breads[4]['controller'] = 'welders';
        $breads[4]['action'] = 'overview';
        $breads[4]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[5]['discription'] = __('Welder qualifications');
        $breads[5]['controller'] = 'welders';
        $breads[5]['action'] = 'qualifications';
        $breads[5]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);

        $this->set('arrayData', $arrayData);
        $this->set('summary', $summary['summary']);
        $this->set('welder', $welder);
        $this->set('certificates', $certificates);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function qualification()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $modus = 0;
        if (isset($this->request->data['type'])) {
            $modus = $this->Sicherheit->Numeric($this->request->data['type']);
        }

        $this->set('modus', $modus);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid qualification'));
        }

        if (isset($this->request->data['WelderCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->Welder->WelderCertificate->set($this->request->data);
            $errors = array();

            if ($this->Welder->WelderCertificate->validates()) {
            } else {
                $errors = $this->Welder->WelderCertificate->validationErrors;
            }
        }

        if (isset($errors) && count($errors) == 0 && isset($this->request->data['WelderCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {

            // Test, ob fÃƒÂ¼r die Qualifizierung eine Zertifizierung vorliegt
            $welder_certificate_data_option = array(
                                'order' => array('WelderCertificateData.certified_date DESC'),
                                'conditions' => array(
                                    'WelderCertificateData.certificate_id' => $certificate_id,
                                    'WelderCertificateData.deleted' => 0,
                                )
                            );

            $welder_certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $welder_certificate_data_option);

            $message = null;

            // das gleiche PrÃƒÂ¼fverfahren, das gleiche level
            $certificate_exist_options = array(
                                    'conditions' => array(
                                        'WelderCertificate.id !=' => $certificate_id,
                                        'WelderCertificate.welder_id' => $welder_id,
                                        'WelderCertificate.level' => $this->request->data['WelderCertificate']['level'],
                                        'WelderCertificate.weldingmethod' => $this->request->data['WelderCertificate']['weldingmethod'],
                                        'WelderCertificate.sector' => $this->request->data['WelderCertificate']['sector'],
                                        'WelderCertificate.deleted' => 0,
                                        )
                                    );
            $certificate_exist = $this->Welder->WelderCertificate->find('first', $certificate_exist_options);

            if (count($certificate_exist) > 0) {
                $message = __('Information could not be saved, there is already a qualification', true) . ' ' . $certificate_exist['WelderCertificate']['sector'] . '/' . $certificate_exist['WelderCertificate']['certificat'] . '/' . $certificate_exist['WelderCertificate']['weldingmethod'] . '-' . $certificate_exist['WelderCertificate']['level'];
                $this->Session->setFlash($message);
                $this->render('error_modal', 'modal');
                return false;
            }

            // Wenn ein Zertifikat mit gleichem Sector/PrÃƒÂ¼fverfahren/Zertifikatsnummer existiert
            // wird dies deaktiviert wenn dessen Level kleiner ist als das neu angelegte
            $certificate_exist_options = array(
                                    'fields' => array('id','level'),
                                    'order' => array('WelderCertificate.id DESC'),
                                    'conditions' => array(
                                        'WelderCertificate.id !=' => $certificate_id,
                                        'WelderCertificate.welder_id' => $welder_id,
//										'WelderCertificate.certificat' => $this->request->data['WelderCertificate']['certificat'],
                                        'WelderCertificate.weldingmethod' => $this->request->data['WelderCertificate']['weldingmethod'],
                                        'WelderCertificate.sector' => $this->request->data['WelderCertificate']['sector'],
                                        'WelderCertificate.deleted' => 0,
                                        )
                                    );

            $certificate_exist = $this->Welder->WelderCertificate->find('list', $certificate_exist_options);

            if (count($certificate_exist) > 0) {
                if ($this->request->data['WelderCertificate']['level'] <= max($certificate_exist)) {
                    $message = __('Information could not be saved, there is already a qualification with a higher level for this weldingmethod.');
                    $this->Session->setFlash($message);
                    $this->render('error_modal', 'modal');
                    return false;
                }
                if ($this->request->data['WelderCertificate']['level'] > max($certificate_exist)) {
                    $this->Welder->WelderCertificate->WelderCertificateData->updateAll(
                        array(
                            'WelderCertificateData.active' => 0,
                            ),
                        array(
                            'WelderCertificateData.certificate_id' => array_flip($certificate_exist),
                            )
                    );
                }
            }

            $this->request->data['WelderCertificate']['status'] = 0;
            $this->request->data['WelderCertificate']['user_id'] = $this->Auth->user('id');

            $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);
            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('WelderCertificate'));

            // Wenn eine Zertifizierungsfreigabe zurÃƒÂ¼ck genomen wird,
            // Werden die entsprechenden Werte in der Qualifikation auf null gesetzt

            if ($this->request->data['WelderCertificate']['certificate_data_active'] == 0 && count($welder_certificate_data > 0) && isset($welder_certificate_data['WelderCertificateData']['id'])) {
                $this->request->data['WelderCertificate']['certificat'] = '-';
                $this->request->data['WelderCertificate']['first_certification'] = 0;
                $this->request->data['WelderCertificate']['renewal_in_year'] = 0;
                $this->request->data['WelderCertificate']['recertification_in_year'] = 0;
                $this->request->data['WelderCertificate']['horizon'] = 0;
            }

            if ($this->Welder->WelderCertificate->save($this->request->data)) {
                $message .= __('The qualification has been saved');
                $this->Session->setFlash($message);
                $this->Session->delete('WelderCertificate.form');

                $this->Autorisierung->Logger($certificate_id, $this->request->data['WelderCertificate']);

                $certificate_data = $this->Welder->WelderCertificate->find('first', array('conditions' => array('WelderCertificate.id' => $certificate_id)));

                if ($this->request->data['WelderCertificate']['certificate_data_active'] == 0) {

                    // Wenn eine Zertifizierungsfreigabe zurÃƒÂ¼ck genommen wird
                    // Werden alle Zertifizierungsdaten auf gelÃƒÂ¶scht gestellt
                    if (count($welder_certificate_data > 0) && isset($welder_certificate_data['WelderCertificateData']['id'])) {
                        $this->Welder->WelderCertificate->WelderCertificateData->updateAll(
                            array(
                                'WelderCertificateData.deleted' => 1,
                                'WelderCertificateData.active' => 0,
                                'WelderCertificateData.user_id' => $this->Auth->user('id'),
                            ),
                            array('WelderCertificateData.certificate_id' => $certificate_id)
                        );
                    }

                    $certificate_data = $this->Welder->find('first', array('conditions' => array('Welder.id' => $welder_id)));

                    $FormName['controller'] = 'welders';
                    $FormName['action'] = 'qualifications';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $this->set('SettingsArray', array());
                    $this->set('CloseModal', 1);
                    $this->set('certificate_data', $certificate_data);
                    $this->set('FormName', $FormName);
                    return false;
                }

                if ($this->request->data['WelderCertificate']['certificate_data_active'] == 1) {
                    if (count($welder_certificate_data) > 0) {

                        // Wenn eine vorher zurÃƒÂ¼ck gezogene Zertifizierungsfreigabe wieder erteilt wird
                        // muss diese mit den neuen Daten aktualisiert werden
                        $certificat_data_current =	array(
                                'WelderCertificateData.first_certification' => $this->request->data['WelderCertificate']['first_certification'],
                                'WelderCertificateData.renewal_in_year' => $this->request->data['WelderCertificate']['renewal_in_year'],
                                'WelderCertificateData.recertification_in_year' => $this->request->data['WelderCertificate']['recertification_in_year'],
                                'WelderCertificateData.horizon' => $this->request->data['WelderCertificate']['horizon'],
                                'WelderCertificateData.deleted' => 0,
                                'WelderCertificateData.active' => 1,
                                'WelderCertificateData.user_id' => $this->Auth->user('id'),
                            );

                        $this->Welder->WelderCertificate->WelderCertificateData->updateAll(
                            $certificat_data_current,
                            array('WelderCertificateData.id' => $welder_certificate_data['WelderCertificateData']['id'])
                        );

                        $this->request->projectvars['VarsArray'][15] = $welder_certificate_data['Welder']['id'];
                        $this->request->projectvars['VarsArray'][16] = $welder_certificate_data['WelderCertificate']['id'];
                        $this->request->projectvars['VarsArray'][17] = $welder_certificate_data['WelderCertificateData']['id'];

                        $FormName['controller'] = 'welders';
                        $FormName['action'] = 'qualifications';
                        $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                        if ($welder_certificate_data['WelderCertificate']['certificate_data_active'] != $this->request->data['WelderCertificate']['certificate_data_active']) {
                            $this->set('NextModal', 1);
                        } else {
                            $this->set('CloseModal', 1);
                        }

                        $this->set('SettingsArray', array());
                        $this->set('certificate_data', $certificate_data);
                        $this->set('FormName', $FormName);
                        return false;
                    }

                    $certificate_data_data['WelderCertificateData'] = array(
                                            'certificate_id' => $certificate_id,
                                            'welder_id' => $certificate_data['WelderCertificate']['welder_id'],
                                            'certified' => 0,
                                            'weldingmethod' => $certificate_data['WelderCertificate']['weldingmethod'],
                                            'first_registration' => $certificate_data['WelderCertificate']['first_registration'],
                                            'recertification_in_year' => $certificate_data['WelderCertificate']['recertification_in_year'],
                                            'renewal_in_year' => $certificate_data['WelderCertificate']['renewal_in_year'],
                                            'horizon' => $certificate_data['WelderCertificate']['horizon'],
                                            'first_certification' => $certificate_data['WelderCertificate']['first_certification'],
                                            'active' => 1,
                                            'deleted' => 0,
                                            'user_id' => $this->Auth->user('id')
                                            );

                    if (!$this->Welder->WelderCertificate->WelderCertificateData->save($certificate_data_data)) {
                        $message = __('Information could not be saved.');
                        $this->Session->setFlash($message);
                        $this->render('error_modal', 'modal');
                        return false;
                    }

                    $certificate_data_id = $this->Welder->WelderCertificate->WelderCertificateData->getLastInsertId();
                }

                $certificate_data_option = array(
                                    'conditions' => array(
                                        'WelderCertificateData.id' => $certificate_data_id,
                                        )
                                    );

                $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);

                $this->request->projectvars['VarsArray'][15] = $certificate_data['Welder']['id'];
                $this->request->projectvars['VarsArray'][16] = $certificate_data['WelderCertificate']['id'];
                $this->request->projectvars['VarsArray'][17] = $certificate_data['WelderCertificateData']['id'];

                $FormName['controller'] = 'welders';
                $FormName['action'] = 'qualifications';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('certificate_data', $certificate_data);
                $this->set('NextModal', 1);
                return false;
            } else {
                $this->Session->setFlash(__('The qualification could not be saved. Please, try again.'));
            }
        }

        $certificate_data_option = array(
                                    'conditions' => array(
                                        'WelderCertificate.id' => $certificate_id,
                                        'WelderCertificate.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Welder->WelderCertificate->find('first', $certificate_data_option);

        $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);

        // Wenn Zertifizierung aktiviert oder deaktiviert wird
        if (isset($this->request->data['certificate_data_active'])) {
            if ($this->request->data['certificate_data_active'] == 0) {
                if (empty($certificate_data['WelderCertificate']['first_registration'])) {
                    pr('leer');
                }
                if (!empty($certificate_data['WelderCertificate']['first_registration'])) {
                    pr('voll');
                    $certificate_data['WelderCertificate']['first_registration'] = null;
                    $certificate_data['WelderCertificate']['certificate_data_active'] = 0;
                    $certificate_data['WelderCertificate']['certificat'] = '-';
                    $certificate_data['WelderCertificate']['first_certification'] = 0;
                    $certificate_data['WelderCertificate']['renewal_in_year'] = 0;
                    $certificate_data['WelderCertificate']['recertification_in_year'] = 0;
                    $certificate_data['WelderCertificate']['horizon'] = 0;
                }
            }
            if ($this->request->data['certificate_data_active'] == 1) {
                // Wenn Zertifizierung auf aktiv steht wird first_registration zum Pflichfeld
                $this->Welder->WelderCertificate->validate['first_registration'] = array(
                    'notempty' => array(
                        'rule' => array('notempty'),
                        'message' => 'Your custom message here',
                    ),
                );
                $certificate_data['WelderCertificate']['certificate_data_active'] = 1;
                $certificate_data['WelderCertificate']['certificat'] = '';
                $certificate_data['WelderCertificate']['first_certification'] = '';
                $certificate_data['WelderCertificate']['renewal_in_year'] = '';
                $certificate_data['WelderCertificate']['recertification_in_year'] = '';
                $certificate_data['WelderCertificate']['horizon'] = '';
            }
        }

        $this->request->data = $certificate_data;
        $this->request->data = $this->Data->GeneralDropdownData(array('WelderCertificate'), $arrayData, $this->request->data);
        $this->request->data =  $this->Data->MultiselectData(array('WelderCertificate'), $arrayData, $this->request->data);

        $this->loadModel('Weldingmethod');
        $weldingmethods = $this->Weldingmethod->find('list', array('fields'=>array('value','value')));

        $this->Data->SessionFormload('WelderCertificate');

        $this->request->data['WelderCertificate']['status'] = 0;

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $welder_id;
        $this->request->projectvars['VarsArray'][16] = $certificate_id;

        $this->set('WeldingmethodOption', $weldingmethods);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);
    }

    public function qualificationdel()
    {
        $this->layout = 'modal';

        $welder_id = $this->request->projectvars['VarsArray'][15];
        $certificate_id =$this->request->projectvars['VarsArray'][16];

        $modus = 0;
        if (isset($this->request->data['type'])) {
            $modus = $this->Sicherheit->Numeric($this->request->data['type']);
        }

        $this->set('modus', $modus);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid qualification'));
        }

        if (isset($this->request->data['WelderCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            $message = null;

            //			$this->Welder->WelderCertificate->recursive = -1;
            $certificate_data = $this->Welder->WelderCertificate->find('first', array('conditions' => array('WelderCertificate.id' => $certificate_id)));
            $this->request->data = $certificate_data;
            $this->request->data['WelderCertificate']['active'] = 0;
            $this->request->data['WelderCertificate']['deleted'] = 1;
            $this->request->data['WelderCertificate']['user_id'] = $this->Auth->user('id');

            $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);

            $certificate_data['WelderCertificate']['deleted'] = 1;
            unset($this->request->data['WelderCertificateData']);
            pr($this->request->data);
            if ($this->Welder->WelderCertificate->save($this->request->data)) {
                $message .= __('The qualification has been deleted');
                $this->Session->setFlash($message);

                $this->Autorisierung->Logger($certificate_id, $this->request->data['WelderCertificate']);

                $certificate_data = $this->Welder->WelderCertificate->find('first', array('conditions' => array('WelderCertificate.id' => $certificate_id)));

                if ($certificate_data['WelderCertificate']['certificate_data_active'] == 0) {
                    $this->request->projectvars['VarsArray'][15] = $welder_id;
                    $this->request->projectvars['VarsArray'][16] = 0;
                    $this->request->projectvars['VarsArray'][17] = 0;

                    $FormName['controller'] = 'welders';
                    $FormName['action'] = 'qualifications';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $this->set('CloseModal', 1);

                    $this->set('SettingsArray', array());
                    $this->set('certificate_data', $certificate_data);
                    $this->set('FormName', $FormName);
                    $this->Session->setFlash($message);
                    return false;
                }

                if ($certificate_data['WelderCertificate']['certificate_data_active'] == 1) {
                    $certificat_data_current =	array(
                            'WelderCertificateData.deleted' => 1,
                            'WelderCertificateData.user_id' => $this->Auth->user('id'),
                        );

                    if ($this->Welder->WelderCertificate->WelderCertificateData->updateAll(
                        $certificat_data_current,
                        array('WelderCertificateData.certificate_id' => $certificate_id)
                    )) {
                        $this->request->projectvars['VarsArray'][15] = $welder_id;
                        $this->request->projectvars['VarsArray'][16] = 0;
                        $this->request->projectvars['VarsArray'][17] = 0;

                        $FormName['controller'] = 'welders';
                        $FormName['action'] = 'qualifications';
                        $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                        $this->set('CloseModal', 1);

                        $this->set('SettingsArray', array());
                        $this->set('certificate_data', $certificate_data);
                        $this->set('FormName', $FormName);
                        $this->Session->setFlash($message);
                        return false;
                    } else {
                        $this->Session->setFlash(__('The qualification could not be deleted. Please, try again.'));
                    }
                }
            } else {
                $this->Session->setFlash(__('The qualification could not be deleted. Please, try again.'));
            }
        }

        $certificate_data_option = array(
                                    'conditions' => array(
                                        'WelderCertificate.id' => $certificate_id,
                                        'WelderCertificate.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Welder->WelderCertificate->find('first', $certificate_data_option);

        $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);

        // Wenn Zertifizierung aktiviert oder deaktiviert wird
        if (isset($this->request->data['certificate_data_active'])) {
            if ($this->request->data['certificate_data_active'] == 0) {
                if (empty($certificate_data['WelderCertificate']['first_registration'])) {
                    pr('leer');
                }
                if (!empty($certificate_data['WelderCertificate']['first_registration'])) {
                    pr('voll');
                    $certificate_data['WelderCertificate']['first_registration'] = null;
                    $certificate_data['WelderCertificate']['certificate_data_active'] = 0;
                    $certificate_data['WelderCertificate']['certificat'] = '-';
                    $certificate_data['WelderCertificate']['first_certification'] = 0;
                    $certificate_data['WelderCertificate']['renewal_in_year'] = 0;
                    $certificate_data['WelderCertificate']['recertification_in_year'] = 0;
                    $certificate_data['WelderCertificate']['horizon'] = 0;
                }
            }
            if ($this->request->data['certificate_data_active'] == 1) {
                // Wenn Zertifizierung auf aktiv steht wird first_registration zum Pflichfeld
                $this->Welder->WelderCertificate->validate['first_registration'] = array(
                    'notempty' => array(
                        'rule' => array('notempty'),
                        'message' => 'Your custom message here',
                    ),
                );
                $certificate_data['WelderCertificate']['certificate_data_active'] = 1;
                $certificate_data['WelderCertificate']['certificat'] = '';
                $certificate_data['WelderCertificate']['first_certification'] = '';
                $certificate_data['WelderCertificate']['renewal_in_year'] = '';
                $certificate_data['WelderCertificate']['recertification_in_year'] = '';
                $certificate_data['WelderCertificate']['horizon'] = '';
            }
        }

        $this->request->data = $certificate_data;
        $this->request->data = $this->Data->GeneralDropdownData(array('WelderCertificate'), $arrayData, $this->request->data);
        $this->request->data['WelderCertificate']['status'] = 0;

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $welder_id;
        $this->request->projectvars['VarsArray'][16] = $certificate_id;

        $message  = 	__('Do you really want to delete this qualification?');
        $message .= '<br>';
        $message .= $this->request->data['WelderCertificate']['third_part'].' '.$this->request->data['WelderCertificate']['sector'].' '.$this->request->data['WelderCertificate']['weldingmethod'].' '.$this->request->data['WelderCertificate']['level'] . ' ' . $this->request->data['WelderCertificate']['certificat'];

        $this->Session->setFlash($message);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);
    }

    public function qualificationnew()
    {
        $this->layout = 'modal';

        $welder_id = $this->request->projectvars['VarsArray'][15];
        $id_reportnumbers = $this->request->projectvars['VarsArray'][0];



        $certificate_data_option = array(
                                    'conditions' => array(
                                        'Welder.id' => $welder_id,
                                        )
                                    );

        $certificate_data = $this->Welder->find('first', $certificate_data_option);
        $this->set('certificate_data', $certificate_data);

        if (isset($this->request->data['WelderCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {

            // muss noch im Helper korrigiert werden
            foreach ($this->request->data['WelderCertificate'] as $_key => $_certificate) {
                if ($_key == '0') {
                    foreach ($this->request->data['WelderCertificate'][0] as $__key => $__certificate) {
                        $this->request->data['WelderCertificate'][$__key] = $__certificate;
                    }
                }
            }

            unset($this->request->data['WelderCertificate'][0]);
            unset($this->request->data['Order']);

            $this->Welder->WelderCertificate->set($this->request->data);
            $errors = array();

            if ($this->Welder->WelderCertificate->validates()) {
            } else {
                $errors = $this->Welder->WelderCertificate->validationErrors;
            }
        }

        if (isset($errors) && count($errors) == 0 && isset($this->request->data['WelderCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            $message = null;

            // das gleiche PrÃƒÂ¼fverfahren, das gleiche level
            $certificate_exist_options = array(
                                    'conditions' => array(
                                        'WelderCertificate.welder_id' => $welder_id,
                                        'WelderCertificate.level' => $this->request->data['WelderCertificate']['level'],
                                        'WelderCertificate.weldingmethod' => $this->request->data['WelderCertificate']['weldingmethod'],
                                        'WelderCertificate.sector' => $this->request->data['WelderCertificate']['sector'],
                                        'WelderCertificate.deleted' => 0,
                                        )
                                    );
            $certificate_exist = $this->Welder->WelderCertificate->find('first', $certificate_exist_options);

            if (count($certificate_exist) > 0) {
                $message = __('Information could not be saved, there is already a qualification', true) . ' ' . $certificate_exist['WelderCertificate']['sector'] . '/' . $certificate_exist['WelderCertificate']['certificat'] . '/' . $certificate_exist['WelderCertificate']['weldingmethod'] . '-' . $certificate_exist['WelderCertificate']['level'];
                $this->Session->setFlash($message);
                $this->render('error_modal', 'modal');
                return false;
            }

            // Wenn ein Zertifikat mit gleichem Sector/PrÃƒÂ¼fverfahren/Zertifikatsnummer existiert
            // wird dies deaktiviert wenn dessen Level kleiner ist als das neu angelegte
            $certificate_exist_options = array(
                                    'fields' => array('id','level'),
                                    'order' => array('WelderCertificate.id DESC'),
                                    'conditions' => array(
                                        'WelderCertificate.welder_id' => $welder_id,
//										'WelderCertificate.certificat' => $this->request->data['WelderCertificate']['certificat'],
                                        'WelderCertificate.weldingmethod' => $this->request->data['WelderCertificate']['weldingmethod'],
                                        'WelderCertificate.sector' => $this->request->data['WelderCertificate']['sector'],
                                        'WelderCertificate.deleted' => 0,
                                        )
                                    );

            $certificate_exist = $this->Welder->WelderCertificate->find('list', $certificate_exist_options);

            if (count($certificate_exist) > 0) {

                // Wenn eine Qualifikation im gleichen Sektor/Verfahren mit hÃƒÂ¶herem oder gleichem Level
                // gefunden wird, wird der Vorgang abgebrochen
            //	if($this->request->data['WelderCertificate']['level'] <= max($certificate_exist)){

                //	$message = __('Information could not be saved, there is already a qualification with a higher level for this weldingmethod.');
                //	$this->Session->setFlash($message);
                //	$this->render('error_modal','modal');
                //	return false;

                //}

                // Wenn eine Qualifikation im gleichen Sektor/Verfahren mit niedrigeren Level
                // gefunden wird, wird diese deaktivert
                /*if($this->request->data['WelderCertificate']['level'] > max($certificate_exist)){

                    $this->Welder->WelderCertificate->updateAll(
                        array(
                            'WelderCertificate.active' => 0,
                            ),
                        $certificate_exist_options['conditions']
                    );

                    $this->Welder->WelderCertificate->WelderCertificateData->updateAll(
                        array(
                            'WelderCertificateData.active' => 0,
                            ),
                        array(
                            'WelderCertificateData.certificate_id' => array_flip($certificate_exist),
                            )
                    );

                }*/
            }

            $this->request->data['WelderCertificate']['status'] = 0;
            $this->request->data['WelderCertificate']['welder_id'] = $certificate_data['Welder']['id'];
            $this->request->data['WelderCertificate']['user_id'] = $this->Auth->user('id');
            if (isset($id_reportnumbers)) {
                $this->request->data ['WelderCertificate'] ['number_vt'] = $id_reportnumbers;
            }

            $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);
            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('WelderCertificate'));

            if ($this->Welder->WelderCertificate->save($this->request->data)) {
                $message .= __('The qualification has been saved');
                $this->Session->delete('WelderCertificate.form');
                $this->Session->setFlash($message);

                $certificate_id = $this->Welder->WelderCertificate->getLastInsertId();

                $this->Autorisierung->Logger($certificate_id, $this->request->data['WelderCertificate']);

                $certificate_data = $this->Welder->WelderCertificate->find('first', array('conditions' => array('WelderCertificate.id' => $certificate_id)));

                $certificate_data_data['WelderCertificateData'] = array(
                                            'certificate_id' => $certificate_id,
                                            'welder_id' => $this->request->data['WelderCertificate']['welder_id'],
                                            'certified' => 0,
                                            'weldingmethod' => $certificate_data['WelderCertificate']['weldingmethod'],
                                            'first_registration' => $certificate_data['WelderCertificate']['first_registration'],
                                            'recertification_in_year' => $this->request->data['WelderCertificate']['recertification_in_year'],
                                            //'renewal_in_year' => $this->request->data['WelderCertificate']['renewal_in_year'],
                                            'horizon' => $this->request->data['WelderCertificate']['horizon'],
                                            'first_certification' => $certificate_data['WelderCertificate']['first_certification'],
                                            'active' => 1,
                                            'deleted' => 0,
                                            'user_id' => $this->Auth->user('id'),



                                            );


                if ($this->request->data['WelderCertificate']['certificate_data_active'] == 0) {
                    $certificate_data = $this->Welder->find('first', array('conditions' => array('Welder.id' => $welder_id)));

                    $FormName['controller'] = 'welders';
                    $FormName['action'] = 'qualifications';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $this->set('SettingsArray', array());
                    $this->set('CloseModal', 1);
                    $this->set('certificate_data', $certificate_data);
                    $this->set('FormName', $FormName);
                    return false;
                }

                if ($this->request->data['WelderCertificate']['certificate_data_active'] == 1) {
                    $this->Welder->WelderCertificate->WelderCertificateData->create();

                    if (!$this->Welder->WelderCertificate->WelderCertificateData->save($certificate_data_data)) {
                        $this->Welder->WelderCertificate->delete($certificate_id);
                        $message = __('Information could not be saved.');
                        pr($certificate_data_data);
                        $this->Session->setFlash($message);
                        $this->render('error_modal', 'modal');
                        return false;
                    }
                }

                $certificate_data_id = $this->Welder->WelderCertificate->WelderCertificateData->getLastInsertId();

                // Wenn der Datensatz fÃƒÂ¼r die Zertifizierung nicht gespeichert wurde
                // Wird der Hauptdatensatz wieder gelÃƒÂ¶scht
                if ($certificate_data_id == null) {
                    $this->Welder->WelderCertificate->delete($certificate_id);
                    $message = __('Information could not be saved.');
                    $this->Session->setFlash($message);
                    $this->render('error_modal', 'modal');
                    return false;
                }

                $certificate_data_option = array(
                                    'conditions' => array(
                                        'WelderCertificateData.id' => $certificate_data_id,
                                        )
                                    );

                $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);

                $this->request->projectvars['VarsArray'][15] = $certificate_data['Welder']['id'];
                $this->request->projectvars['VarsArray'][16] = $certificate_data['WelderCertificate']['id'];
                $this->request->projectvars['VarsArray'][17] = $certificate_data['WelderCertificateData']['id'];

                $FormName['controller'] = 'welders';
                $FormName['action'] = 'qualifications';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('SettingsArray', array());
                $this->set('NextModal', 1);
                $this->set('certificate_data', $certificate_data);
                $this->set('FormName', $FormName);
                return false;
            } else {
                $this->Session->setFlash(__('The qualification could not be saved. Please, try again.'));
            }
        }


        $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);

        $certificate_data_option = array('conditions' => array('Welder.id' => $welder_id));
        $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);

        if (!isset($this->request->data['WelderCertificate'])) {
            $this->request->data = $certificate_data;

            unset($this->request->data['WelderCertificate']);
            unset($this->request->data['WelderCertificateData']);

            $this->request->data['WelderCertificate']['certificat'] = '-';
            $this->request->data['WelderCertificate']['certificate_data_active'] = 0;
            $this->request->data['WelderCertificate']['level'] = 0;
            $this->request->data['WelderCertificate']['supervisor'] = 0;
            $this->request->data['WelderCertificate']['first_certification'] = 0;
            $this->request->data['WelderCertificate']['renewal_in_year'] = 0;
            $this->request->data['WelderCertificate']['recertification_in_year'] = 0;
            $this->request->data['WelderCertificate']['horizon'] = 0;
        }

        //Alle untergeordneten Berichte holen

        $this->loadModel('ReportRtstGenerally');
        $this->loadModel('ReportVtstGenerally');
        $this->loadModel('ReportVtstSpecific');
        $this->loadModel('ReportRtstSpecific');
        $this->loadModel('ReportVtstEvaluation');
        $this->loadModel('ReportRtstEvaluation');
        $this->loadModel('Reportnumber');

        if ($id_reportnumbers == 0) {
            $vtst = $this->ReportVtstGenerally->find('all', array('condition' => array('welder' => $welder_id)));

            foreach ($vtst as $v_key => $v_value) {
                $reportlist [$v_key] ['VT'] = $v_value;
                $repnmbrvt = $this->Reportnumber->find('first', array('conditions' =>array('Reportnumber.id' => $v_value['ReportVtstGenerally']['reportnumber_id'])));
                $reportlist [$v_key] ['VT'] ['Reportnumber'] = $repnmbrvt['Reportnumber'];

                $vtstspec = $this->ReportVtstSpecific->find('all', array('condition' => array('reportnumber_id' => $v_value['ReportVtstGenerally'] ['reportnumber_id'])));
                isset($vtstspec) ? $vtstspec = $vtstspec [0]:'';
                $reportlist [$v_key] ['VT'] ['ReportVtstSpecific'] = $vtstspec ['ReportVtstSpecific'];
                $vtstev= $this->ReportVtstEvaluation->find('all', array('condition' => array('reportnumber_id' => $v_value['ReportVtstGenerally'] ['reportnumber_id'])));
                $reportlist [$v_key] ['VT'] ['ReportVtstEvaluation'] = $vtstev;


                $child_id = $this->Reportnumber->find('first', array('fields'=> array('id'),'conditions' =>array('parent_id' => $v_value['ReportVtstGenerally']['reportnumber_id'])));
                $rtst = $this->ReportRtstGenerally->find('first', array('condition' => array('welder' => $welder_id, 'reportnumber_id' => $child_id ['Reportnumber'] ['id'])));
                $reportlist [$v_key] ['RT'] = $rtst;

                $repnmbrRT = $this->Reportnumber->find('first', array('conditions' =>array('Reportnumber.id' => $child_id['Reportnumber'] ['id'])));
                $reportlist [$v_key] ['RT'] ['Reportnumber'] = $repnmbrRT['Reportnumber'];

                $rtstspec = $this->ReportRtstSpecific->find('all', array('condition' => array('reportnumber_id' => $child_id ['Reportnumber'] ['id'])));
                isset($rtstspec) ? $rtstspec = $rtstspec [0]:'';
                $reportlist [$v_key] ['RT'] ['ReportRtstSpecific'] = $rtstspec ['ReportRtstSpecific'];

                $rtstev= $this->ReportRtstEvaluation->find('all', array('condition' => array('reportnumber_id' => $child_id ['Reportnumber'] ['id'])));

                $reportlist [$v_key] ['RT'] ['ReportRtstEvaluation'] = $rtstev;
            }
            $this->set('reportlist', $reportlist);
            $this->render('qualificationnew_list');
        } else {
            $ReportVT = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$id_reportnumbers )));
            $GenerallyVT = $this->ReportVtstGenerally->find('first', array('conditions'=>array('reportnumber_id'=>$id_reportnumbers)));
            $EvaluationVT = $this->ReportVtstEvaluation->find('all', array('conditions'=>array('reportnumber_id'=>$id_reportnumbers)));
            $SpecificVT = $this->ReportVtstSpecific->find('first', array('conditions'=>array('reportnumber_id'=>$id_reportnumbers)));

            $Report = array();
            $Report['ReportnumberVT'] [0] = $ReportVT ['Reportnumber'];
            $Report['ReportVtstGenerally'] [0] = $GenerallyVT  ['ReportVtstGenerally'];
            $Report['ReportVtstSpecific'][0] = $SpecificVT ['ReportVtstSpecific'];

            foreach ($EvaluationVT as $ev_key => $ev_value) {
                $Report['ReportVtstEvaluation'][] =  $ev_value ['ReportVtstEvaluation'];
            }


            $ReportRT = $this->Reportnumber->find('first', array('conditions'=>array('parent_id' => $id_reportnumbers )));
            $GenerallyRT = $this->ReportRtstGenerally->find('first', array('conditions'=>array('reportnumber_id'=>$ReportRT['Reportnumber']['id'])));
            $EvaluationRT = $this->ReportRtstEvaluation->find('all', array('conditions'=>array('reportnumber_id'=>$ReportRT['Reportnumber']['id'])));
            $SpecificRT = $this->ReportRtstSpecific->find('first', array('conditions'=>array('reportnumber_id'=>$ReportRT['Reportnumber']['id'])));

            $Report['ReportnumberRT'] [0] = $ReportRT ['Reportnumber'];
            $Report['ReportRtstGenerally'] [0] = $GenerallyRT  ['ReportRtstGenerally'];
            $Report['ReportRtstSpecific'][0] = $SpecificRT ['ReportRtstSpecific'];

            foreach ($EvaluationRT as $ev_key => $ev_value) {
                $Report['ReportRtstEvaluation'][] =  $ev_value ['ReportRtstEvaluation'];
            }
            // $reportdata = $this->Data->InsertFromReportData($Report,$arrayData['settings']->WelderCertificate);
            if (isset($Report)) {
                $reportdata = $this->Data->InsertFromReportData($Report, $arrayData['settings']->WelderCertificate);

                foreach ($reportdata as $d_key => $d_value) {
                    $this->request->data['WelderCertificate'][$d_key] =  $d_value;
                }
            }

            //Schweismethoden
            $this->loadModel('Weldingmethod');
            $weldingmethods = $this->Weldingmethod->find('list', array('fields'=>array('value','value')));
            $this->set('WeldingmethodOption', $weldingmethods);

            //Fehler fÃƒÂ¼r Multiselect
            $this->loadModel('ErrorNumber');
            $errornumbers =  $this->ErrorNumber->find('list', array('fields'=>array('number','text')));
            $this->set('errornumbers', $errornumbers);
            $this->request->data =  $this->Data->MultiselectData(array('WelderCertificate'), $arrayData, $this->request->data);

            $this->Data->SessionFormload('WelderCertificate');
            $this->request->data = $this->Data->GeneralDropdownData(array('WelderCertificate'), $arrayData, $this->request->data);
            $this->request->data['WelderCertificate']['status'] = 0;


            $SettingsArray = array();
            $this->request->projectvars['VarsArray'][15] = $welder_id;
            $this->set('SettingsArray', $SettingsArray);
            $this->set('settings', $arrayData['headoutput']);
        }
    }

    public function certificates()
    {
        $id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $testingcompid = $this->request->projectvars['VarsArray'][14];
        $testingcomp =  $this->Welder->Testingcomp->find('first', array('conditions' => array('Testingcomp.id' => $testingcompid)));
        if (!$this->Welder->exists($id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        $arrayData = $this->Xml->DatafromXml('Welder', 'file', null);

        // Wenn certificates mit einer Variablen fÃƒÂ¼r ein spezielles Zertifikat aufgerufen wird
        // wird dies mit der Variable QualiMarking ÃƒÂ¼bergeben, um dieses Zertifikat im View zu markieren
        // Foth, 14.08.2017
        if (isset($this->request->projectvars['VarsArray'][16]) && $this->request->projectvars['VarsArray'][16] > 0) {
            $this->set('QualiMarking', $this->request->projectvars['VarsArray'][16]);
        } else {
            $this->set('QualiMarking', 0);
        }

        $this->Welder->recursive = -1;
        $welder = $this->Welder->find('first', array('conditions' => array('Welder.id' => $id)));
        $certificates = $this->Qualification->WelderCertificatesSectors($welder);

        $this->request->data = $welder;
        $this->request->data = $this->Data->GeneralDropdownData(array('Welder'), $arrayData, $this->request->data);

        $summary = $this->Qualification->WelderCertificateSummary($certificates, array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));

        $this->request->projectvars['VarsArray'][15] = $id;
        $SettingsArray = array();

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        }

        unset($this->request->projectvars['VarsArray'][16]);
        unset($this->request->projectvars['VarsArray'][17]);

        $breads = $this->Navigation->Breads(null);



        $breads[1]['discription'] =   __('Welding Companies', true);
        $breads[1]['controller'] = 'welders';
        $breads[1]['action'] = 'indexcomp';
        $breads[1]['pass'] = null;


        $breads[2]['discription'] = $testingcomp ['Testingcomp'] ['name'];
        $breads[2]['controller'] = 'welders';
        $breads[2]['action'] = 'indexpart';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = __('Welders', true);
        $breads[3]['controller'] = 'welders';
        $breads[3]['action'] = 'index';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[4]['discription'] = $welder['Welder']['first_name'] . ' ' . $welder['Welder']['name'];
        $breads[4]['controller'] = 'welders';
        $breads[4]['action'] = 'overview';
        $breads[4]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[5]['discription'] = __('Welder qualifications');
        $breads[5]['controller'] = 'welders';
        $breads[5]['action'] = 'qualifications';
        $breads[5]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[6]['discription'] = __('Welder qualifications data');
        $breads[6]['controller'] = 'welders';
        $breads[6]['action'] = 'certificates';
        $breads[6]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);

        $this->set('arrayData', $arrayData);
        $this->set('summary', $summary['summary']);
        $this->set('welder', $welder);
        $this->set('certificates', $certificates);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function certificate()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $modus = 0;
        if (isset($this->request->data['type'])) {
            $modus = $this->Sicherheit->Numeric($this->request->data['type']);
        }

        $this->set('modus', $modus);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (isset($this->request->data['WelderCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->Welder->WelderCertificate->set($this->request->data);
            $errors = array();

            if ($this->Welder->WelderCertificate->validates()) {
            } else {
                $errors = $this->Welder->WelderCertificate->validationErrors;
            }
        }

        if (isset($errors) && count($errors) == 0 && isset($this->request->data['WelderCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {

            // Test, ob fÃƒÂ¼r die Qualifizierung eine Zertifizierung vorliegt
            $welder_certificate_data_option = array(
                                'order' => array('WelderCertificateData.certified_date DESC'),
                                'contain' => array(
                                    'WelderCertificateData' => array(
                                        'conditions' => array(
                                            'WelderCertificateData.certificate_id' => $certificate_id,
                                            'WelderCertificateData.deleted' => 0,
                                        )
                                    ),
                                    'Welder' => array(
                                        'conditions' => array(
                                            'Welder.id' => $welder_id,
                                            'Welder.deleted' => 0,
                                        )
                                    )
                                )
                            );

            $welder_certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $welder_certificate_data_option);

            $message = null;

            // das gleiche PrÃƒÂ¼fverfahren, das gleiche level
            $certificate_exist_options = array(
                                    'conditions' => array(
                                        'WelderCertificate.id !=' => $certificate_id,
                                        'WelderCertificate.welder_id' => $welder_id,
                                        'WelderCertificate.level' => $this->request->data['WelderCertificate']['level'],
                                        'WelderCertificate.weldingmethod' => $this->request->data['WelderCertificate']['weldingmethod'],
                                        'WelderCertificate.sector' => $this->request->data['WelderCertificate']['sector'],
                                        'WelderCertificate.deleted' => 0,
                                        )
                                    );
            $certificate_exist = $this->Welder->WelderCertificate->find('first', $certificate_exist_options);

            if (count($certificate_exist) > 0) {
                $message = __('Information could not be saved, there is already a certificate', true) . ' ' . $certificate_exist['WelderCertificate']['sector'] . '/' . $certificate_exist['WelderCertificate']['certificat'] . '/' . $certificate_exist['WelderCertificate']['weldingmethod'] . '-' . $certificate_exist['WelderCertificate']['level'];
                $this->Session->setFlash($message);
                $this->render('error_modal', 'modal');
                return false;
            }

            // Wenn ein Zertifikat mit gleichem Sector/PrÃƒÂ¼fverfahren/Zertifikatsnummer existiert
            // wird dies deaktiviert wenn dessen Level kleiner ist als das neu angelegte
            $certificate_exist_options = array(
                                    'fields' => array('id','level'),
                                    'order' => array('WelderCertificate.id DESC'),
                                    'conditions' => array(
                                        'WelderCertificate.id !=' => $certificate_id,
                                        'WelderCertificate.welder_id' => $welder_id,
//										'WelderCertificate.certificat' => $this->request->data['WelderCertificate']['certificat'],
                                        'WelderCertificate.weldingmethod' => $this->request->data['WelderCertificate']['weldingmethod'],
                                        'WelderCertificate.sector' => $this->request->data['WelderCertificate']['sector'],
                                        'WelderCertificate.deleted' => 0,
                                        )
                                    );

            $certificate_exist = $this->Welder->WelderCertificate->find('list', $certificate_exist_options);

            if (count($certificate_exist) > 0) {
                if ($this->request->data['WelderCertificate']['level'] <= max($certificate_exist)) {
                    $message = __('Information could not be saved, there is already a certificate with a higher level for this weldingmethod.');
                    $this->Session->setFlash($message);
                    $this->render('error_modal', 'modal');
                    return false;
                }
                if ($this->request->data['WelderCertificate']['level'] > max($certificate_exist)) {
                    $this->Welder->WelderCertificate->WelderCertificateData->updateAll(
                        array(
                            'WelderCertificateData.active' => 0,
                            ),
                        array(
                            'WelderCertificateData.certificate_id' => array_flip($certificate_exist),
                            )
                    );
                }
            }

            $this->request->data['WelderCertificate']['status'] = 0;
            $this->request->data['WelderCertificate']['user_id'] = $this->Auth->user('id');

            $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);
            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('WelderCertificate'));

            // Wenn eine Zertifizierungsfreigabe zurÃƒÂ¼ck genomen wird,
            // Werden die entsprechenden Werte in der Qualifikation auf null gesetzt

            if ($this->request->data['WelderCertificate']['certificate_data_active'] == 0 && count($welder_certificate_data > 0) && isset($welder_certificate_data['WelderCertificateData']['id'])) {
                $this->request->data['WelderCertificate']['certificat'] = '-';
                $this->request->data['WelderCertificate']['first_certification'] = 0;
                $this->request->data['WelderCertificate']['renewal_in_year'] = 0;
                $this->request->data['WelderCertificate']['recertification_in_year'] = 0;
                $this->request->data['WelderCertificate']['horizon'] = 0;
            }

            if ($this->Welder->WelderCertificate->save($this->request->data)) {
                $message .= __('The qualification has been saved');
                $this->Session->setFlash($message);

                $this->Autorisierung->Logger($certificate_id, $this->request->data['WelderCertificate']);

                $certificate_data = $this->Welder->WelderCertificate->find('first', array('conditions' => array('WelderCertificate.id' => $certificate_id)));

                if ($this->request->data['WelderCertificate']['certificate_data_active'] == 0) {

                    // Wenn eine Zertifizierungsfreigabe zurÃƒÂ¼ck genommen wird
                    // Werden alle Zertifizierungsdaten auf gelÃƒÂ¶scht gestellt
                    if (count($welder_certificate_data > 0) && isset($welder_certificate_data['WelderCertificateData']['id'])) {
                        $this->Welder->WelderCertificate->WelderCertificateData->updateAll(
                            array(
                                'WelderCertificateData.deleted' => 1,
                                'WelderCertificateData.active' => 0,
                                'WelderCertificateData.user_id' => $this->Auth->user('id'),
                            ),
                            array('WelderCertificateData.certificate_id' => $certificate_id)
                        );
                    }

                    $certificate_data = $this->Welder->find('first', array('conditions' => array('Welder.id' => $welder_id)));

                    $FormName['controller'] = 'welders';
                    $FormName['action'] = 'certificates';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $this->set('SettingsArray', array());
                    $this->set('CloseModal', 1);
                    $this->set('certificate_data', $certificate_data);
                    $this->set('FormName', $FormName);
                    return false;
                }

                if ($this->request->data['WelderCertificate']['certificate_data_active'] == 1) {
                    if (count($welder_certificate_data) > 0) {

                        // Wenn eine vorher zurÃƒÂ¼ck gezogene Zertifizierungsfreigabe wieder erteilt wird
                        // muss diese mit den neuen Daten aktualisiert werden
                        $certificat_data_current =	array(
                                'WelderCertificateData.first_certification' => $this->request->data['WelderCertificate']['first_certification'],
                                'WelderCertificateData.renewal_in_year' => $this->request->data['WelderCertificate']['renewal_in_year'],
                                'WelderCertificateData.recertification_in_year' => $this->request->data['WelderCertificate']['recertification_in_year'],
                                'WelderCertificateData.horizon' => $this->request->data['WelderCertificate']['horizon'],
                                'WelderCertificateData.deleted' => 0,
                                'WelderCertificateData.active' => 1,
                                'WelderCertificateData.user_id' => $this->Auth->user('id'),
                            );

                        $this->Welder->WelderCertificate->WelderCertificateData->updateAll(
                            $certificat_data_current,
                            array('WelderCertificateData.id' => $welder_certificate_data['WelderCertificateData']['id'])
                        );

                        $this->request->projectvars['VarsArray'][15] = $welder_certificate_data['Welder']['id'];
                        $this->request->projectvars['VarsArray'][16] = $welder_certificate_data['WelderCertificate']['id'];
                        $this->request->projectvars['VarsArray'][17] = $welder_certificate_data['WelderCertificateData']['id'];

                        $FormName['controller'] = 'welders';
                        $FormName['action'] = 'certificates';
                        $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                        $this->set('NextModal', 1);

                        $this->set('SettingsArray', array());
                        $this->set('certificate_data', $certificate_data);
                        $this->set('FormName', $FormName);
                        return false;
                    }

                    $certificate_data_data['WelderCertificateData'] = array(
                                            'certificate_id' => $certificate_id,
                                            'welder_id' => $certificate_data['WelderCertificate']['welder_id'],
                                            'certified' => 0,
                                            'weldingmethod' => $certificate_data['WelderCertificate']['weldingmethod'],
                                            'first_registration' => $certificate_data['WelderCertificate']['first_registration'],
                                            'recertification_in_year' => $certificate_data['WelderCertificate']['recertification_in_year'],
                                            'renewal_in_year' => $certificate_data['WelderCertificate']['renewal_in_year'],
                                            'horizon' => $certificate_data['WelderCertificate']['horizon'],
                                            'first_certification' => $certificate_data['WelderCertificate']['first_certification'],
                                            'active' => 1,
                                            'deleted' => 0,
                                            'user_id' => $this->Auth->user('id')
                                            );

                    if (!$this->Welder->WelderCertificate->WelderCertificateData->save($certificate_data_data)) {
                        $message = __('Information could not be saved.');
                        $this->Session->setFlash($message);
                        $this->render('error_modal', 'modal');
                        return false;
                    }

                    $certificate_data_id = $this->Welder->WelderCertificate->WelderCertificateData->getLastInsertId();
                }

                $certificate_data_option = array(
                                    'conditions' => array(
                                        'WelderCertificateData.id' => $certificate_data_id,
                                        )
                                    );

                $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);

                $this->request->projectvars['VarsArray'][15] = $certificate_data['Welder']['id'];
                $this->request->projectvars['VarsArray'][16] = $certificate_data['WelderCertificate']['id'];
                $this->request->projectvars['VarsArray'][17] = $certificate_data['WelderCertificateData']['id'];

                $FormName['controller'] = 'welders';
                $FormName['action'] = 'certificates';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('certificate_data', $certificate_data);
                $this->set('NextModal', 1);
                return false;
            } else {
                $this->Session->setFlash(__('The qualification could not be saved. Please, try again.'));
            }
        }


        $certificate_data_option = array(
                                    'conditions' => array(
                                        'WelderCertificate.id' => $certificate_id,
                                        'WelderCertificate.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Welder->WelderCertificate->find('first', $certificate_data_option);

        $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);

        $this->request->data = $certificate_data;
        $this->request->data = $this->Data->GeneralDropdownData(array('WelderCertificate'), $arrayData, $this->request->data);
        $this->request->data['WelderCertificate']['status'] = 0;

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $welder_id;
        $this->request->projectvars['VarsArray'][16] = $certificate_id;

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);
    }

    protected function __files($path, $welder_id, $certificate_id, $certificatefiles_id, $models)
    {
        $outputarray = $this->Qualification->DokuFiles($path, $welder_id, $certificate_id, $certificatefiles_id, $models);

        $this->set('_data', $outputarray['_data']);
        $this->set('_files', $outputarray['_files']);
        $this->set('models', $outputarray['models']);
    }
    protected function __weldercompfiles($path, $weldingcomp_id, $certificate_id, $certificatefiles_id, $models)
    {
        $outputarray = $this->Qualification->DokuFilesWeldercomp($path, $weldingcomp_id, $certificate_id, $certificatefiles_id, $models);

        $this->set('_data', $outputarray['_data']);
        $this->set('_files', $outputarray['_files']);
        $this->set('models', $outputarray['models']);
    }

    public function certificatesfiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Welder';
        $models['main'] = 'WelderCertificate';
        $models['sub'] = 'WelderCertificateData';
        $models['file'] = 'WelderCertificatefile';

        $certificatefile_id = 0;
        $this->loadModel($models['file']);

        if (isset($this->request->data['QuickWelderCertificatefilesearch']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['id'])) {
                $certificatefile_id = $this->Sicherheit->Numeric($this->request->data['id']);

                if (!$this->{$models['file']}->exists($certificatefile_id)) {
                    throw new NotFoundException(__('Invalid value'));
                }

                $certificate_files = $this->{$models['file']}->find(
                    'first',
                    array(
                                                                'conditions' => array(
                                                                    $models['file'].'.deleted' => 0,
                                                                    $models['file'].'.id' => $certificatefile_id,
                                                                    $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                                    )
                                                                )
                );

                $certificatefile_id = $certificate_files[$models['file']]['id'];
                $welder_id = $certificate_files[$models['file']]['welder_id'];
                $certificate_id = $certificate_files[$models['file']]['certificate_id'];
            }
        } else {
            $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
            $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

            if (!$this->Welder->exists($welder_id)) {
                throw new NotFoundException(__('Invalid welder'));
            }

            if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid certificate'));
            }
        }

        $path = Configure::read('welder_certificate_folder') . $welder_id. DS . $certificate_id . DS . 'documents' . DS;

        $this->__files($path, $welder_id, $certificate_id, $certificatefile_id, $models);

        // Werte fÃƒÂ¼r die Schnellsuche
        $ControllerQuickSearch =
            array(
                'Model' => 'WelderCertificatefile',
                'Field' => 'description',
                'Conditions' => array(
                                'parent_id' => $certificate_id,
                                ),
                'description' => __('Dokument description', true),
                'minLength' => 2,
                'targetcontroller' => 'welders',
                'target_id' => 'id',
                'targetation' => 'certificatesfiles',
                'Quicksearch' => 'QuickWelderCertificatefilesearch',
                'QuicksearchForm' => 'QuickWelderCertificatefilesearchForm',
                'QuicksearchSearchingAutocomplet' => 'QuickWelderCertificatefilesearchSearchingAutocomplet',
        );

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('ControllerQuickSearch', $ControllerQuickSearch);
    }

    public function eyecheckfiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Welder';
        $models['main'] = 'WelderEyecheck';
        $models['sub'] = 'WelderEyecheckData';
        $models['file'] = 'WelderEyecheckfile';
        $certificatefile_id = 0;
        $this->loadModel($models['file']);

        if (isset($this->request->data['QuickWelderEyecheckfilesearch']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['id'])) {
                $certificatefile_id = $this->Sicherheit->Numeric($this->request->data['id']);

                if (!$this->{$models['file']}->exists($certificatefile_id)) {
                    throw new NotFoundException(__('Invalid value'));
                }

                $certificate_files = $this->{$models['file']}->find(
                    'first',
                    array(
                                                                'conditions' => array(
                                                                    $models['file'].'.deleted' => 0,
                                                                    $models['file'].'.id' => $certificatefile_id,
                                                                    $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                                    )
                                                                )
                );

                $certificatefile_id = $certificate_files[$models['file']]['id'];
                $welder_id = $certificate_files[$models['file']]['welder_id'];
                $certificate_id = $certificate_files[$models['file']]['parent_id'];
            }
        } else {
            $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
            $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
            $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

            if (!$this->Welder->exists($welder_id)) {
                throw new NotFoundException(__('Invalid welder'));
            }

            if (!$this->Welder->WelderEyecheck->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid certificate'));
            }
        }

        $path = Configure::read('welder_eyecheck_folder') . $welder_id. DS . $certificate_id . DS . 'documents' . DS;

        $this->__files($path, $welder_id, $certificate_id, $certificatefile_id, $models);

        $lastModal = array(
                        'controller' => 'welders',
                        'action' => 'eyechecks',
                        'terms' => $this->request->projectvars['VarsArray']
                        );

        // Werte fÃƒÂ¼r die Schnellsuche
        $ControllerQuickSearch =
            array(
                'Model' => 'WelderEyecheckfile',
                'Field' => 'description',
                'Conditions' => array(
                                'parent_id' => $certificate_id,
                                ),
                'description' => __('Dokument description', true),
                'minLength' => 2,
                'targetcontroller' => 'welders',
                'target_id' => 'id',
                'targetation' => 'eyecheckfiles',
                'Quicksearch' => 'QuickWelderEyecheckfilesearch',
                'QuicksearchForm' => 'QuickWelderEyecheckfilesearchForm',
                'QuicksearchSearchingAutocomplet' => 'QuickWelderEyecheckfilesearchSearchingAutocomplet',
        );

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to overview',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        //		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => $lastModal['controller'],'action' => $lastModal['action'], 'terms' => $lastModal['terms']);
        //		$SettingsArray['eyechecklink'] = array('discription' => __('Welder eyechecks',true), 'controller' => 'welders','action' => 'eyechecks', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('ControllerQuickSearch', $ControllerQuickSearch);
    }

    protected function __filesdescription($models, $successtarget)
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = 0;

        if (isset($this->request->params['pass']['17']) && $this->Sicherheit->Numeric($this->request->params['pass']['17']) > 0) {
            $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        }

        if (!$this->{$models['top']}->exists($welder_id)) {
            throw new NotFoundException(__('Invalid value'));
        }

        if (!$this->{$models['top']}->{$models['main']}->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid value'));
        }

        $this->loadModel($models['file']);

        $certificate_option = array(
                                    'conditions' => array(
                                        $models['main'].'.id' => $certificate_id,
                                        $models['main'].'.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->{$models['top']}->{$models['main']}->find('first', $certificate_option);

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($this->request->data[$models['file']]['description'] == '') {
                $file_id = $this->Sicherheit->Numeric($this->request->data[$models['file']]['id']);

                $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['file'].'.id' => $file_id,
                                        $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        )
                                    );

                $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);

                if ($certificate_files[$models['file']]['description'] == '') {
                    $this->request->data = $certificate_files;
                    $this->set('certificate_files', $certificate_files);
                    $this->set('certificate_data', $certificate_data);
                    $this->set('models', $models);
                    return;
                }
            }

            if ($this->{$models['file']}->save($this->request->data)) {
                $this->Session->setFlash(__('The value has been saved'));
                $this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);


                if ($models['file'] == 'WelderCertificatefile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'certificatesfiles';
                    $this->request->here = implode('/', $_here);
                    $this->certificatesfiles();
                }
                if ($models['file'] == 'WelderEyecheckfile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'eyecheckfiles';
                    $this->request->here = implode('/', $_here);
                    $this->eyecheckfiles();
                }


                $this->render($successtarget);
            } else {
                $this->Session->setFlash(__('The value could not be saved. Please, try again.'));
            }
        }

        if ($file_id == 0) {
            $certificate_data_option = array(
                                    'order' => array($models['file'].'.id DESC'),
                                    'conditions' => array(
                                        $models['file'].'.description' => '',
                                        $models['file'].'.deleted' => 0,
                                        $models['file'].'.welder_id' => $welder_id,
                                        $models['file'].'.parent_id' => $certificate_id,
                                        $models['file'].'.user_id' => $this->Auth->user('id'),
                                        $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        )
                                    );
        } elseif ($file_id > 0) {
            $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['file'].'.id' => $file_id,
                                        $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        )
                                    );
        }

        $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);

        $this->request->data = $certificate_files;

        $FileUploadErrors = $this->Session->read('FileUploadErrors');
        $this->Session->delete('FileUploadErrors');

        $this->set('certificate_files', $certificate_files);
        $this->set('certificate_data', $certificate_data);
        $this->set('models', $models);
        $this->set('message', Configure::read('FileUploadErrors.' . $FileUploadErrors));
        $this->set('uploaderror', $FileUploadErrors);
    }

    public function eyecheckfilesdescription()
    {
        $this->layout = 'modal';

        $models['top'] = 'Welder';
        $models['main'] = 'WelderEyecheck';
        $models['sub'] = 'WelderEyecheckData';
        $models['file'] = 'WelderEyecheckfile';

        $this->__filesdescription($models, 'eyecheckfiles');

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to overview',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'eyecheckfiles', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function certificatefilesdescription()
    {
        $this->layout = 'modal';

        $models['top'] = 'Welder';
        $models['main'] = 'WelderCertificate';
        $models['sub'] = 'WelderCertificateData';
        $models['file'] = 'WelderCertificatefile';

        $this->__filesdescription($models, 'certificatesfiles');

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to overview',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'certificatesfiles', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    protected function __delfiles($models, $successtarget)
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->{$models['top']}->exists($welder_id)) {
            throw new NotFoundException(__('Invalid value'));
        }
        if (!empty($models['main'])) {
            if (!$this->{$models['top']}->{$models['main']}->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid value'));
            }
        }
        $this->loadModel($models['file']);

        if (!$this->{$models['file']}->exists($file_id)) {
            throw new NotFoundException(__('Invalid file'));
        }

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data[$models['file']]['deleted'] = 1;

            if ($this->{$models['file']}->save($this->request->data)) {
                $this->Session->setFlash(__('The file has been deleted'));
                $this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);


                $_here = explode('/', $this->request->here);
                $_here[3] = 'certificatesfiles';
                $this->request->here = implode('/', $_here);

                if ($models['file'] == 'WelderCertificatefile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'certificatesfiles';
                    $this->request->here = implode('/', $_here);
                    $this->certificatesfiles();
                }
                if ($models['file'] == 'WelderEyecheckfile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'eyecheckfiles';
                    $this->request->here = implode('/', $_here);
                    $this->eyecheckfiles();
                }
                if ($models['file'] == 'Welderfile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'files';
                    $this->request->here = implode('/', $_here);
                    $this->files();
                }

                $this->render($successtarget);
            } else {
                $this->Session->setFlash(__('The file could not be deleted. Please, try again.'));
            }
        }
        if (!empty($models['sub'])) {
            $certificate_data_option = array(
                                    'order' => array($models['sub'].'.id DESC'),
                                    'conditions' => array(
                                        $models['sub'].'.certificate_id' => $certificate_id,
                                        $models['sub'].'.deleted' => 0,
                                        )
                                    );

            $certificate_data = $this->{$models['top']}->{$models['main']}->{$models['sub']}->find('first', $certificate_data_option);
        } else {
            $certificate_data_option = array(
                                'conditions' => array(
                                    $models['file'].'.id' => $file_id,
                                    $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                    )
                                );
        }
        $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);

        $this->request->data = $certificate_files;

        $this->set('certificate_files', $certificate_files);
        isset($certificate_data)?$this->set('certificate_data', $certificate_data) : '';
        $this->set('models', $models);
    }

    public function delcertificatefiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Welder';
        $models['main'] = 'WelderCertificate';
        $models['sub'] = 'WelderCertificateData';
        $models['file'] = 'WelderCertificatefile';

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'welders';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $this->__delfiles($models, 'certificatesfiles');

        $lastModal = array(
                        'controller' => 'welders',
                        'action' => 'certificatesfiles',
                        'terms' => $this->request->projectvars['VarsArray']
                        );

        if ($this->Session->check('lastModalURLArray')) {
            $lastModal = array(
                        'controller' => $this->Session->read('lastModalURLArray.controller'),
                        'action' => $this->Session->read('lastModalURLArray.action'),
                        'terms' => $this->Session->read('lastModalURLArray.pass')
                        );
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => $lastModal['controller'],'action' => $lastModal['action'], 'terms' => $lastModal['terms']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function deleyecheckfiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Welder';
        $models['main'] = 'WelderEyecheck';
        $models['sub'] = 'WelderEyecheckData';
        $models['file'] = 'WelderEyecheckfile';

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'welders';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $this->__delfiles($models, 'eyecheckfiles');

        $lastModal = array(
                        'controller' => 'welders',
                        'action' => 'eyecheckfiles',
                        'terms' => $this->request->projectvars['VarsArray']
                        );

        if ($this->Session->check('lastModalURLArray')) {
            $lastModal = array(
                        'controller' => $this->Session->read('lastModalURLArray.controller'),
                        'action' => $this->Session->read('lastModalURLArray.action'),
                        'terms' => $this->Session->read('lastModalURLArray.pass')
                        );
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => $lastModal['controller'],'action' => $lastModal['action'], 'terms' => $lastModal['terms']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function getcertificatefiles()
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $models = array('main' => 'WelderCertificatefile','sub' => null);
        $path = Configure::read('welder_certificate_folder') . $welder_id. DS . $certificate_id . DS . 'documents' . DS;

        $this->loadModel($models['main']);

        $this->__getfiles($path, $models);
    }

    public function geteyecheckfiles()
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $models = array('main' => 'WelderEyecheckfile','sub' => null);
        $path = Configure::read('welder_eyecheck_folder') . $welder_id. DS . $certificate_id . DS . 'documents' . DS;

        $this->loadModel($models['main']);

        $this->__getfiles($path, $models);
    }

    protected function __getfiles($path, $models)
    {
        $this->layout = 'blank';
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['main'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        $models['main'].'.id' => $file_id
                                        )
                                    );

        $certificate_data = $this->{$models['main']}->find('first', $certificate_data_option);

        if (count($certificate_data) == 0) {
            $this->Session->setFlash(__('The file does not exist.'));
            $this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')), $this->request->projectvars['VarsArray']));
        } elseif (count($certificate_data) == 1) {
            if (file_exists($path . $certificate_data[$models['main']]['name'])) {
                $this->set('path', $path . $certificate_data[$models['main']]['name']);
            } else {
                $this->Session->setFlash(__('The file cannot be found.'));
                $this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')), $this->request->projectvars['VarsArray']));
            }
        }
    }

    public function removecertificate()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        $this->set('hint', __('Will you delete this qualification?', true));
        $this->set('del_button', __('Delete', true));

        if (isset($this->request->data['WelderCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->request->data['WelderCertificate']['deleted'] = 1;
            $this->request->data['WelderCertificate']['active'] = 0;

            if ($this->Welder->WelderCertificate->save($this->request->data)) {
                $this->Welder->WelderCertificate->WelderCertificateData->updateAll(
                    array(
                            'WelderCertificateData.deleted' => 1,
                            'WelderCertificateData.active' => 0,
                            'WelderCertificateData.certified_file' => null,
                            'WelderCertificateData.user_id' => $this->Auth->user('id')
                            ),
                    array(
                            'WelderCertificateData.certificate_id' => $certificate_id,
                            )
                );

                $path = Configure::read('welder_certificate_folder') . $welder_id. DS . $certificate_id . DS;
                $folder = new Folder($path);
                $folder->delete();

                $this->request->projectvars['VarsArray'][15] = $certificate_data['Welder']['id'];
                $this->request->projectvars['VarsArray'][16] = 0;

                $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderCertificateData.certificate_id' => $certificate_id,
                                        )

                                    );

                $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);
                $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);
                $this->request->data = $certificate_data;

                $SettingsArray = array();
                $this->request->projectvars['VarsArray'][15] = $welder_id;
                //				$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'welders','action' => 'certificate', 'terms' => $this->request->projectvars['VarsArray']);

                $FormName['controller'] = 'welders';
                $FormName['action'] = 'certificates';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);

                $this->set('SettingsArray', $SettingsArray);
                $this->set('certificate_data', $certificate_data);
                $this->set('settings', $arrayData['headoutput']);

                $this->Session->setFlash(__('The certificate has been deleted'));
                $this->Autorisierung->Logger($certificate_id, $this->request->data['WelderCertificate']);
            } else {
                $this->Session->setFlash(__('The certificate could not be deleted. Please, try again.'));
            }
        } else {
            $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderCertificateData.certificate_id' => $certificate_id,
                                        'WelderCertificateData.deleted' => 0,
//										'WelderCertificateData.active' => 1
                                        )
                                    );

            $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);
            $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);
            $this->request->data = $certificate_data;

            $SettingsArray = array();
            $this->request->projectvars['VarsArray'][15] = $welder_id;
            $this->request->projectvars['VarsArray'][16] = $certificate_id;
            //			$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'welders','action' => 'certificate', 'terms' => $this->request->projectvars['VarsArray']);

            $this->set('SettingsArray', $SettingsArray);
            $this->set('certificate_data', $certificate_data);
            $this->set('settings', $arrayData['headoutput']);
        }
    }

    public function editcertificate()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Welder->WelderCertificate->WelderCertificateData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        if (isset($this->request->data['WelderCertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {
            $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderCertificateData.id' => $certificate_data_id,
                                        'WelderCertificateData.deleted' => 0,
//										'WelderCertificateData.active' => 1
                                        )
                                    );

            $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);

            // Test ob es diese Qualifikation mit hÃƒÂ¶herem Level schon gibt
            $qualification_test = $this->Welder->WelderCertificate->find(
                'first',
                array('conditions'=>array(
                                                                'WelderCertificate.welder_id' => $certificate_data['WelderCertificate']['welder_id'],
                                                                'WelderCertificate.sector' => $certificate_data['WelderCertificate']['sector'],
                                                                'WelderCertificate.weldingmethod' => $certificate_data['WelderCertificate']['weldingmethod'],
                                                                'WelderCertificate.level >=' => $certificate_data['WelderCertificate']['level'],
                                                                'WelderCertificate.id !=' => $certificate_data['WelderCertificate']['id'],
                                                                'WelderCertificate.active' => 1,
                                                                )
                                                            )
            );
            if (count($qualification_test) > 0) {
                $qualification_data_test = $this->Welder->WelderCertificate->WelderCertificateData->find(
                    'first',
                    array(
                                                                        'order' => array('WelderCertificateData.certified_date DESC'),
                                                                        'conditions'=>array(
                                                                            'WelderCertificateData.certificate_id'=>$qualification_test['WelderCertificate']['id']
                                                                            )
                                                                        )
                );
            } else {
                $qualification_data_test = array();
            }


            if (count($qualification_test) > 0 && $this->request->data['WelderCertificateData']['active'] > 0 && count($qualification_data_test) > 0 && $qualification_data_test['WelderCertificateData']['active'] > 0) {
                $this->Session->setFlash(__('The certificate infos could not be saved.').' '.__('There is a qualification with the same weldingmethod and a higher level.'));
            } else {
                unset($this->request->data['Order']);

                $this->request->data['WelderCertificateData']['user_id'] = $this->Auth->user('id');

                if ($this->Welder->WelderCertificate->WelderCertificateData->save($this->request->data)) {
                    $message = __('The certificate infos has been saved');

                    // Test ob es diese Qualifikation mit niedrigerem Level gibt
                    // diese wird deaktiviert wenn die Aktuelle aktiv ist
                    $qualification_test = $this->Welder->WelderCertificate->find(
                        'first',
                        array('conditions'=>array(
                                                                'WelderCertificate.welder_id' => $certificate_data['WelderCertificate']['welder_id'],
                                                                'WelderCertificate.sector' => $certificate_data['WelderCertificate']['sector'],
                                                                'WelderCertificate.weldingmethod' => $certificate_data['WelderCertificate']['weldingmethod'],
                                                                'WelderCertificate.level <' => $certificate_data['WelderCertificate']['level'],
                                                                'WelderCertificate.id !=' => $certificate_data['WelderCertificate']['id'],
                                                                'WelderCertificate.active' => 1,
                                                                )
                                                            )
                    );
                    if (count($qualification_test) > 0) {
                        $this->Welder->WelderCertificate->WelderCertificateData->recursive = -1;

                        $qualification_data_test = $this->Welder->WelderCertificate->WelderCertificateData->find(
                            'first',
                            array(
                                                                        'order' => array('WelderCertificateData.certified_date DESC'),
                                                                        'conditions'=>array(
                                                                            'WelderCertificateData.certificate_id'=>$qualification_test['WelderCertificate']['id']
                                                                            )
                                                                        )
                        );
                        if ($this->request->data['WelderCertificateData']['active'] > 0 && $qualification_data_test['WelderCertificateData']['active'] > 0) {
                            $qualification_data_test['WelderCertificateData']['active'] = 0;
                            if ($this->Welder->WelderCertificate->WelderCertificateData->save($qualification_data_test)) {
                                $message .= ' '.__('A qualification with the same weldingmethod and a lower level was deactivated.');
                            } else {
                                $message .= ' '.__('A qualification with the same weldingmethod and a lower level could not be disabled.');
                            }
                        }

                        $this->Welder->WelderCertificate->WelderCertificateData->recursive = 1;
                    }

                    $this->Session->setFlash($message);
                    $this->Autorisierung->Logger($certificate_data_id, $this->request->data['WelderCertificateData']);

                    $FormName['controller'] = 'welders';
                    $FormName['action'] = 'certificates';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $this->set('FormName', $FormName);
                } else {
                    $this->Session->setFlash(__('The certificate infos could not be saved. Please, try again.'));
                }
            }
        }

        $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderCertificateData.id' => $certificate_data_id,
                                        'WelderCertificateData.deleted' => 0,
//										'WelderCertificateData.active' => 1
                                        )
                                    );

        $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);

        $this->Qualification->SingleCertificateSummary($certificate_data, array('main' => 'WelderCertificate','sub' => 'WelderCertificateData'));

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        if (!isset($this->request->data['WelderCertificateData'])) {
            $this->request->data = $certificate_data;
        }

        $arrayData = $this->Xml->DatafromXml('WelderCertificateData', 'file', null);

        $certificate_data = $this->Qualification->WelderIsRenewal($certificate_data);

        $certificate_data = $this->Qualification->WelderTimeHorizons($certificate_data, array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'), $certificate_data['WelderCertificateData']['period']);

        $SettingsArray = array();
        //		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'welders','action' => 'certificate', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['orderoutput']);
    }

    public function askcertification()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Welder->WelderCertificate->WelderCertificateData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        if (isset($this->request->data['WelderCertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->request->data['WelderCertificateData']['user_id'] = $this->Auth->user('id');

            if ($this->Welder->WelderCertificate->WelderCertificateData->save($this->request->data)) {
                $this->Session->setFlash(__('The certificate infos has been saved'));
                $this->Autorisierung->Logger($certificate_data_id, $this->request->data['WelderCertificateData']);
            } else {
                $this->Session->setFlash(__('The certificate infos could not be saved. Please, try again.'));
            }

            $FormName['controller'] = 'welders';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderCertificateData.id' => $certificate_data_id,
                                        'WelderCertificateData.deleted' => 0,
//										'WelderCertificateData.active' => 1
                                        )
                                    );

        $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);

        $this->Qualification->SingleCertificateSummary($certificate_data, array('main' => 'WelderCertificate','sub' => 'WelderCertificateData'));

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml('WelderCertificateDataAsk', 'file', null);

        $certificate_data = $this->Qualification->WelderIsRenewal($certificate_data);

        $certificate_data = $this->Qualification->WelderTimeHorizons($certificate_data, array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'), $certificate_data['WelderCertificateData']['period']);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['orderoutput']);

        if (isset($certificateinfo) && $certificateinfo == 1) {
            $this->set('certificate_data', $certificate_data);
            $this->render('certificateinfo', 'blank');
        }
    }

    public function replacecertificate()
    {
        $model = array('main' => 'WelderCertificate','sub' => 'WelderCertificateData');

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'welders';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $this->_replacecertificate($model, 'welder_certificate_folder', 'certificateinfo', 'certificate_data');
    }

    public function replaceeyecheck()
    {
        $model = array('main' => 'WelderEyecheck','sub' => 'WelderEyecheckData');

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'welders';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $this->_replacecertificate($model, 'welder_eyecheck_folder', 'eyecheckinfo', 'eyecheck_data');
    }

    protected function _replacecertificate($model, $folder, $target, $varname)
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->$model['main']->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $certificat_test = $this->Welder->$model['main']->$model['sub']->find('first', array('conditions'=>array($model['sub'].'.id' => $certificate_data_id)));

            if ($this->request->data[$model['sub']]['certified_date'] <= $certificat_test[$model['sub']]['certified_date']) {
                $message = __('Information could not be saved.');
                $message .= ' ' . __('The date is too far in the past.');
                $this->Session->setFlash($message);
                $this->set('certificate_data', $certificat_test);
                $this->render('error_modal', 'modal');
                return false;
            }

            $this->Welder->$model['main']->$model['sub']->updateAll(
                array(
                        $model['sub'].'.active' => 0,
                        ),
                array(
                        $model['sub'].'.certificate_id' => $certificate_id,
                        )
            );

            unset($this->request->data['Order']);

            $certificat = $this->Welder->$model['main']->find('first', array('conditions' => array($model['main'].'.id' => $certificate_id)));

            if (isset($certificat[$model['main']]['weldingmethod'])) {
                $this->request->data[$model['sub']]['weldingmethod'] = $certificat[$model['main']]['weldingmethod'];
            }

            $this->request->data[$model['sub']]['first_certification'] = $certificat[$model['main']]['first_certification'];
            $this->request->data[$model['sub']]['first_registration'] = $certificat[$model['main']]['first_registration'];
            $this->request->data[$model['sub']]['renewal_in_year'] = $certificat[$model['main']]['renewal_in_year'];
            $this->request->data[$model['sub']]['recertification_in_year'] = $certificat[$model['main']]['recertification_in_year'];
            $this->request->data[$model['sub']]['user_id'] = $this->Auth->user('id');

            unset($this->request->data[$model['sub']]['id']);

            if ($this->Welder->$model['main']->$model['sub']->save($this->request->data)) {
                $certificate_data_id = $this->Welder->$model['main']->$model['sub']->getLastInsertId();
                $this->Session->setFlash(__('The infos has been saved'));
                $this->Autorisierung->Logger($certificate_data_id, $this->request->data[$model['sub']]);
            } else {
                $this->Session->setFlash(__('The infos could not be saved. Please, try again.'));
            }
        }

        $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        $model['sub'].'.id' => $certificate_data_id,
                                        $model['sub'].'.deleted' => 0,
                                        $model['sub'].'.active' => 1
                                        )
                                    );

        $certificate_old_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        $model['sub'].'.certificate_id' => $certificate_id,
                                        $model['sub'].'.deleted' => 0,
                                        $model['sub'].'.active' => 1
                                        )
                                    );

        $certificate_data = $this->Welder->$model['main']->$model['sub']->find('first', $certificate_data_option);
        $certificate_data_old = $this->Welder->$model['main']->$model['sub']->find('first', $certificate_old_data_option);

        if (count($certificate_data) == 0) {
            $this->request->data[$model['sub']]['welder_id'] = $welder_id;
            $this->request->data[$model['sub']]['certificate_id'] = $certificate_id;
            $this->request->data[$model['sub']]['recertification_in_year'] = $certificate_data_old[$model['main']]['recertification_in_year'];
            $this->request->data[$model['sub']]['renewal_in_year'] = $certificate_data_old[$model['main']]['renewal_in_year'];
            $this->request->data[$model['sub']]['horizon'] = $certificate_data_old[$model['main']]['horizon'];
            $this->request->data[$model['sub']]['apply_for_recertification'] = 0;
            $this->request->data[$model['sub']]['active'] = 1;
            $this->request->data[$model['sub']]['certified'] = 1;

            if ($this->request->data[$model['sub']]['recertification_in_year'] == 0) {
                $this->request->data[$model['sub']]['recertification_in_year'] = $certificate_data_old[$model['main']]['recertification_in_year'];
            }
        } else {
            $this->request->data = $certificate_data;
        }

        if (isset($this->request->data['WelderCertificateData']['certified_date'])) {
            unset($this->request->data['WelderCertificateData']['certified_date']);
        }

        $this->request->data['WelderEyecheckData']['vt'] = 0;
        $this->request->data['WelderEyecheckData']['pt'] = 0;
        $this->request->data['WelderEyecheckData']['mt'] = 0;
        $this->request->data['WelderEyecheckData']['ut'] = 0;
        $this->request->data['WelderEyecheckData']['sv'] = 0;
        $this->request->data['WelderEyecheckData']['near_vision'] = 0;
        $this->request->data['WelderEyecheckData']['color_contrast'] = 0;
        $this->request->data['WelderEyecheckData']['anomalies'] = 0;
        $this->request->data['WelderEyecheckData']['face'] = 0;
        $this->request->data['WelderEyecheckData']['distance_vision'] = 0;
        $this->request->data['WelderEyecheckData']['landolt'] = 0;
        $this->request->data['WelderEyecheckData']['one_eye'] = 0;
        $this->request->data['WelderEyecheckData']['different_way'] = 0;

        $this->request->data['WelderEyecheckData']['glasses'] = 0;

        $arrayData = $this->Xml->DatafromXml($model['sub'].'Refresh', 'file', null);

        $this->Qualification->SingleCertificateSummary($certificate_data_old, $model);
        $this->set('certificate_data', $certificate_data);
        $this->set('certificate_data_old', $certificate_data_old);
        $this->set('settings', $arrayData['orderoutput']);
    }

    public function delcertificate()
    {
        $this->layout = 'blank';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Welder->WelderCertificate->WelderCertificateData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        $this->set('hint', __('Will you delete the certificate info from this certifikate? The certificate will then be invalid.', true));
        $this->set('del_button', __('Delete', true));

        $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderCertificateData.id' => $certificate_data_id,
                                        'WelderCertificateData.deleted' => 0,
//										'WelderCertificateData.active' => 1
                                        )
                                    );

        if (isset($this->request->data['WelderCertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->request->data['WelderCertificateData']['deleted'] = 1;
            $this->request->data['WelderCertificateData']['active'] = 0;
            $this->request->data['WelderCertificateData']['certified'] = 0;
            $this->request->data['WelderCertificateData']['certified_file'] = '';
            $this->request->data['WelderCertificateData']['user_id'] = $this->Auth->user('id');

            if ($this->Welder->WelderCertificate->WelderCertificateData->save($this->request->data)) {
                $this->Session->setFlash(__('The certificate infos has been delete'));

                $path = Configure::read('welder_certificate_folder') . $welder_id. DS . $certificate_id . DS . $certificate_data_id . DS;
                $folder = new Folder($path);
                $folder->delete();

                $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderCertificateData.certificate_id' => $certificate_id,
                                        'WelderCertificateData.deleted' => 0,
//										'WelderCertificateData.active' => 1
                                        )
                                    );

                $this->Autorisierung->Logger($certificate_data_id, $this->request->data['WelderCertificateData']);

                $this->request->projectvars['VarsArray'][15] = $welder_id;
                $this->request->projectvars['VarsArray'][16] = $certificate_id;
                $this->request->projectvars['VarsArray'][17] = 0;
                unset($this->request->data['back']);
                unset($this->request->data['WelderCertificateData']);


                $this->_include_replacecertificate();
                $this->render('replacecertificate');
                return;
            } else {
                $this->Session->setFlash(__('The certificate infos could not be delete. Please, try again.'));
            }
        }

        $certificate_data = $this->Welder->WelderCertificate->WelderCertificateData->find('first', $certificate_data_option);

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml('WelderCertificateData', 'file', null);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['orderoutput']);

        if (isset($certificateinfo) && $certificateinfo == 1) {
            $certificate_data = $this->Qualification->WelderIsRenewal($certificate_data);

            $certificate_data = $this->Qualification->WelderTimeHorizons($certificate_data, array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'), $certificate_data['WelderCertificateData']['period']);

            $this->set('certificate_data', $certificate_data);
            $this->render('certificateinfo', 'blank');
        }
    }

    public function eyecheckfile()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $this->request->projectvars['VarsArray'][17] = $certificate_data_id ;

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'editeyecheck', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

        if (isset($this->request['data']['Welder']['file'])) {
            $FormName['controller'] = 'welders';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $hint = __('Eine vorhandene Datei wird mit dem Upload ÃƒÂ¼berschrieben.', true);
        $this->set('hint', $hint);

        $models = array('main' => 'WelderEyecheck','sub' => 'WelderEyecheckData');
        $path = Configure::read('welder_eyecheck_folder') . $welder_id. DS . $certificate_id . DS . $certificate_data_id . DS;
        $this->fileupload($path, $models);
    }

    public function certificatefile()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'editcertificate', 'terms' => $this->request->params['pass']);

        $this->set('SettingsArray', $SettingsArray);

        if (isset($this->request['data']['Welder']['file'])) {
            $FormName['controller'] = 'welders';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $models = array('main' => 'WelderCertificate','sub' => 'WelderCertificateData');
        $path = Configure::read('welder_certificate_folder') . $welder_id. DS . $certificate_id . DS . $certificate_data_id . DS;

        $this->fileupload($path, $models);
    }

    protected function fileupload($path, $models)
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $_data_option = array(
                    'conditions' => array(
                        $models['sub'] . '.id' => $certificate_data_id,
                        $models['sub'] . '.certificate_id' => $certificate_id,
                        $models['sub'] . '.deleted' => 0,
//						$models['sub'] . '.active' => 1
                        )
                    );

        $_data = $this->Welder->{$models['main']}->{$models['sub']}->find('first', $_data_option);

        if ($_data[$models['sub']]['certified_file'] != '' && file_exists($path . $_data[$models['sub']]['certified_file'])) {
            $this->set('hint', __('Do you want to overwrite the existing file?', true));
        }

        $this->set('data', $_data);
        $this->set('models', $models);

        $Extension = 'pdf';

        if (isset($this->request['data']['Welder']['file'])) {

            // Infos zur Datei
            $fileinfo = pathinfo($this->request['data']['Welder']['file']['name']);
            $filename_new = $welder_id.'_'.$certificate_id.'_'.$certificate_data_id.'_'.time().'.'.$fileinfo['extension'];

            if ($Extension == strtolower($fileinfo['extension'])) {
                if (isset($_FILES['data']['error'][$models['top']]['file'])) {
                    $this->Session->write('FileUploadErrors', $_FILES['data']['error'][$models['top']]['file']);
                }

                // Vorhandene Dateien werden gelÃƒÂ¶scht
                if ($_FILES['data']['error']['Welder']['file'] == 0) {
                    $folder = new Folder($path);
                    $folder->delete();

                    if (!file_exists($path)) {
                        $dir = new Folder($path, true, 0755);
                    }
                }

                $this->Session->write('FileUploadErrors', $_FILES['data']['error']['Welder']['file']);
                move_uploaded_file($this->request['data']['Welder']['file']["tmp_name"], $path. DS .$filename_new);

                if ($_FILES['data']['error']['Welder']['file'] == 0) {
                    $dataFiles = array(
                        'id' => $certificate_data_id,
                        'certified_file' => $filename_new,
                        'user_id' => $this->Auth->user('id')
                    );

                    $this->Welder->{$models['main']}->{$models['sub']}->save($dataFiles);
                }
            }
        }
    }

    public function getcertificatefile()
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $models = array('main' => 'WelderCertificate','sub' => 'WelderCertificateData');
        $path = Configure::read('welder_certificate_folder') . $welder_id. DS . $certificate_id . DS . $certificate_data_id . DS;
        $this->getfile($path, $models);
    }

    public function geteyecheckfile()
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $models = array('main' => 'WelderEyecheck','sub' => 'WelderEyecheckData');
        $path = Configure::read('welder_eyecheck_folder') . $welder_id. DS . $certificate_id . DS . $certificate_data_id . DS;
        $this->getfile($path, $models);
    }

    protected function getfile($path, $models)
    {
        $this->layout = 'blank';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        $models['sub'].'.id' => $certificate_data_id
                                        )
                                    );

        $certificate_data = $this->Welder->{$models['main']}->{$models['sub']}->find('first', $certificate_data_option);

        $this->set('path', $path . $certificate_data[$models['sub']]['certified_file']);
    }

    public function delcertificatefile()
    {
        $model = array('main' => 'WelderCertificate','sub' => 'WelderCertificateData');

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'welders';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->params['pass']);

            $this->set('FormName', $FormName);
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'editcertificate', 'terms' => $this->request->params['pass']);

        $this->set('SettingsArray', $SettingsArray);
        $this->_delfile($model, 'welder_certificate_folder', 'certificateinfo');
    }

    public function deleyecheckfile()
    {
        $model = array('main' => 'WelderEyecheck','sub' => 'WelderEyecheckData');

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'welders';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'eyecheck', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

        $this->_delfile($model, 'welder_eyecheck_folder', 'eyecheckinfo');
    }

    protected function _delfile($model, $folder, $target)
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->$model['main']->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Welder->$model['main']->$model['sub']->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        $this->set('hint', __('Will you delete this file?', true));
        $this->set('del_button', __('Delete', true));

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->request->data[$model['sub']]['user_id'] = $this->Auth->user('id');

            if ($this->Welder->$model['main']->$model['sub']->save($this->request->data)) {
                $path = Configure::read($folder) . $welder_id. DS . $certificate_id . DS . $certificate_data_id . DS;
                $folder = new Folder($path);
                $folder->delete();

                $this->set('hint', __('The file has been delete', true));
                $this->Autorisierung->Logger($certificate_data_id, $this->request->data[$model['sub']]);
                $this->set('del_button', null);
            } else {
                $this->set('hint', __('The file could not be delete. Please, try again.', true));
            }
            /*
                        $FormName['controller'] = 'welders';
                        $FormName['action'] = 'certificates';
                        $FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

                        $this->set('FormName',$FormName);
            */
        }

        $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        $model['sub'].'.id' => $certificate_data_id
                                        )
                                    );

        $certificate_data = $this->Welder->$model['main']->$model['sub']->find('first', $certificate_data_option);

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml($model['sub'], 'file', null);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['orderoutput']);

        if (isset($certificateinfo) && $certificateinfo == 1) {
            $certificate_data = $this->Qualification->WelderTimeHorizons($certificate_data);

            $certificate_data = $this->Qualification->WelderTimeHorizons($certificate_data, $model, $certificate_data[$model['sub']]['period']);

            $this->set('certificate_data', $certificate_data);
            $this->render($target, 'blank');
        }
    }

    public function historyeyecheck()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $path = Configure::read('welder_eyecheck_folder');

        if (!$this->Welder->exists($welder_id)) {
            return;
        }

        if (!$this->Welder->WelderEyecheck->exists($certificate_id)) {
            return;
        }

        $certificate_data_option = array(
                                    'order' => array('WelderEyecheckData.id ASC'),
                                    'conditions' => array(
                                        'WelderEyecheckData.certificate_id' => $certificate_id,
                                        )
                                    );

        $certificate_datas = $this->Welder->WelderEyecheck->WelderEyecheckData->find('all', $certificate_data_option);


        if (count($certificate_datas) > 0) {
            $certificate_data = $certificate_datas[0];

            foreach ($certificate_datas as $_key => $_certificate_datas) {
                if ($_certificate_datas['WelderEyecheckData']['certified_file'] != '') {
                    $filepath = $path . $_certificate_datas['Welder']['id']. DS . $_certificate_datas['WelderEyecheck']['id']. DS . $_certificate_datas['WelderEyecheckData']['id'] . DS . $_certificate_datas['WelderEyecheckData']['certified_file'];
                    if (file_exists($filepath) == true) {
                        $certificate_datas[$_key]['WelderEyecheckData']['file'] = true;
                    }
                }
            }
        }

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to list',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        //		$SettingsArray['certificatelink'] = array('discription' => __('Welder qualifications',true), 'controller' => 'welders','action' => 'certificates', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('certificate_datas', $certificate_datas);
        $this->set('path', $path);
    }

    public function history()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $path = Configure::read('welder_certificate_folder');

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (!$this->Welder->WelderCertificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        $certificate_data_option = array(
                                    'order' => array('WelderCertificateData.id ASC'),
                                    'conditions' => array(
                                        'WelderCertificateData.certificate_id' => $certificate_id,
                                        )
                                    );

        $certificate_datas = $this->Welder->WelderCertificate->WelderCertificateData->find('all', $certificate_data_option);
        $certificate_datas_sectors = $certificate_datas;

        if (count($certificate_datas) > 0) {
            $certificate_data = $certificate_datas[0];
        }

        foreach ($certificate_datas_sectors as $_key => $_certificate_datas_sectors) {
            $certificate_datas_sectors[$_key] = $this->Qualification->WelderCertificatesSectors($_certificate_datas_sectors);

            if ($_certificate_datas_sectors['WelderCertificateData']['certified_file'] != '') {
                $filepath = $path . $_certificate_datas_sectors['Welder']['id']. DS . $_certificate_datas_sectors['WelderCertificate']['id']. DS . $_certificate_datas_sectors['WelderCertificateData']['id'] . DS . $_certificate_datas_sectors['WelderCertificateData']['certified_file'];
                if (file_exists($filepath) == true) {
                    $certificate_datas[$_key]['WelderCertificateData']['file'] = true;
                }
            }
        }

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to list',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        //		$SettingsArray['certificatelink'] = array('discription' => __('Welder qualifications',true), 'controller' => 'welders','action' => 'certificates', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('certificate_datas', $certificate_datas);
        $this->set('certificate_datas_sectors', $certificate_datas_sectors);
        $this->set('path', $path);
    }

    // kann wohl gelÃƒÂ¶scht werden
    public function visiontests()
    {
        $id = $this->request->projectvars['VarsArray'][13];

        if (!$this->Welder->exists($id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        $welder = $this->Welder->find('first', array('conditions' => array('Welder.id' => $id)));

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);
        $this->set('welder', $welder);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function view()
    {
        $this->loadModel('Reportnumber');

        $order = $this->WelderTime->Order->find('first', array('conditions' => array('Order.id' => $this->request->projectvars['orderID'])));
        $this->set('order', $order);

        $this->set('welder', array());

        if (isset($this->request->params['pass'][15]) && $this->request->params['pass'][15] > 0) {
            $id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
            $this->request->projectvars['VarsArray'][15] = $id;

            if (!$this->Welder->exists($id)) {
                throw new NotFoundException(__('Invalid welder'));
            }

            $options = array('conditions' => array(
                                'Welder.' . $this->Welder->primaryKey => $id
                                )
                            );
            $this->WelderTime->recursive = 0;

            $welder = $this->Welder->find('first', $options);

            $this->set('welder', $welder);

            $id_condition = array('WelderTime.welder_id' => $id);
        }

        if ($this->request->projectvars['reportID'] > 0) {
            $reportnumberID_condition = array('WelderTime.reportnumber_id' => $this->request->projectvars['reportnumberID']);
        }
        if ($this->request->projectvars['orderID'] > 0) {
            $orderID_condition = array('WelderTime.order_id' => $this->request->projectvars['orderID']);
        }

        if (isset($id_condition)) {
            $options['conditions'][] = $id_condition;
        }
        if (isset($reportnumberID_condition)) {
            $options['conditions'][] = $reportnumberID_condition;
        }
        if (isset($orderID_condition)) {
            $options['conditions'][] = $orderID_condition;
        }

        $weldertime = $this->WelderTime->find('all', array_merge($options, array('order'=>array('date(WelderTime.testing_time_start) ASC', 'time(WelderTime.testing_time_start) ASC'))));

        $times = array();

        foreach ($weldertime as $_key => $_weldertime) {
            if (isset($this->request->projectvars['VarsArray'][15]) && $this->request->projectvars['VarsArray'][15] != 0) {
                $_welder_id = $_weldertime['WelderTime']['welder_id'];
            } else {
                $_welder_id = 'all';
            }

            $timestamp_waitingtime =  strtotime($_weldertime['WelderTime']['waiting_time_start']);
            $timestamp_testingtime =  strtotime($_weldertime['WelderTime']['testing_time_start']);

            if (date('Y', $timestamp_testingtime) != '1970') {
                $times[$_welder_id]['testing'][date('Y', $timestamp_testingtime)][date('m', $timestamp_testingtime)][date('d', $timestamp_testingtime)]['id'] = $_weldertime['WelderTime']['id'];
                if ($_weldertime['WelderTime']['collision'] != null) {
                    $times[$_welder_id]['testing'][date('Y', $timestamp_testingtime)][date('m', $timestamp_testingtime)][date('d', $timestamp_testingtime)]['collision'] = 1;
                }
                if ($_weldertime['WelderTime']['collision'] == null) {
                    $times[$_welder_id]['testing'][date('Y', $timestamp_testingtime)][date('m', $timestamp_testingtime)][date('d', $timestamp_testingtime)]['collision'] = 0;
                }
            }
            if (date('Y', $timestamp_waitingtime) != '1970') {
                $times[$_welder_id]['waiting'][date('Y', $timestamp_waitingtime)][date('m', $timestamp_waitingtime)][date('d', $timestamp_waitingtime)]['id'] = $_weldertime['WelderTime']['id'];
            }
        }

        $this->set('times', $times);
        $this->set('welderTime', $weldertime);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'welders','action' => 'add', 'terms' => null);
        //		$SettingsArray['printlink'] = array('discription' => __('Print workload'), 'controller'=>'welders', 'action'=>'printworkload', 'terms' => array_merge($this->request->projectvars['VarsArray'], array($id)), 'class'=>'printlink');

        $this->set('SettingsArray', $SettingsArray);
    }

    /**
     * add method
     *
     * @return void
     */
     public function add()
     {

       $testingcompid = $this->request->projectvars['VarsArray'][14];
       $this->layout = 'modal';

       $afterEdit = null;
       $arrayData['settings'] = $this->Xml->CollectXMLFromFile('Welder');

       if (isset($this->request->data['Welder']) && ($this->request->is('post') || $this->request->is('put'))) {
         $this->Welder->create();

         // muss noch in der xml angepasst werden
         $this->request->data['Welder']['testingcomp_id'] = $testingcompid;

         $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('Welder'));

         if ($this->Welder->save($this->request->data)) {



           $id = $this->Welder->getLastInsertId();
           $emailto = Configure::read('EmailWelderInfos');
           $emailto = explode(',', $emailto);
           $welder = $this->Welder->find('first',array('conditions'=> array('Welder.id'=>$id)));
           foreach ($emailto as $ekey => $evalue) {
             $isValid = Validation::email($evalue);

             if ($isValid === true && isset($welder['Welder'])) {
               App::uses('CakeEmail', 'Network/Email');

               $subject = 'Information Schweißer'.' '. $welder['Welder']['identification'].' '. $welder['Welder']['first_name'].' '. $welder['Welder']['name'];
               $content = 'Der Schweißer wurde angelegt';
               $email = new CakeEmail('default');
               $email->domain(FULL_BASE_URL);
               $email->from(array('reminder@mbq-gmbh.info' => 'Schweißerdatenbank'));
               $email->sender(array('reminder@mbq-gmbh.info' => 'Schweißerdatenbank'));
               $email->to($evalue);
               $email->subject($subject);
               $email->template('welderstate', 'default');
               $email->emailFormat('text');
               $email->viewVars(
                 array(
                   'state' => $content,
                 )
               );
               $email->send();
             }
           }
           $this->Flash->success(__('The welder has been saved'), array('key' => 'success'));

           $this->Session->delete('Welder.form');

           $this->Autorisierung->Logger($this->Welder->getLastInsertID(), $this->request->data['Welder']);

           $this->request->projectvars['VarsArray'][15] = $this->Welder->getLastInsertID();

           $FormName['controller'] = 'welders';
           $FormName['action'] = 'overview';
           $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

           $this->set('FormName', $FormName);
         } else {
           $this->Flash->warning(__('The welder could not be saved. Please, try again.'), array('key' => 'warning'));

         }
       }

       $testingcomps = $this->Welder->Testingcomp->find('list', array('conditions' => array('Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps())));
       $this->set(compact('testingcomps'));

       //		$this->request->data = array();
       $this->request->data = $this->Data->GeneralDropdownData(array('Welder'), $arrayData, $this->request->data);
       $this->request->data['WelderActive']['active'] = 1;
       $this->Data->SessionFormload();   //wenn Sessiondatenvorhanden diese setzen P.Schmidt 4.9.2017
       $SettingsArray = array();
       //		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'welders','action' => 'index', 'terms' => null);

       $this->set('SettingsArray', $SettingsArray);
       $this->set('arrayData', $arrayData);
       $this->set('settings', $arrayData['settings']);
     }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit()
    {
        $this->layout = 'modal';
        $id = $this->request->projectvars['VarsArray'][15];
        $afterEdit = null;

        if (!$this->Welder->exists($id)) {
            throw new NotFoundException(__('Invalid welder'));
        }

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('Welder');
        $status= $this->Welder->find('first', array('fields' => array('active'),'conditions' => array('Welder.id'=> $id)));

        $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('Welder'));
        if (isset($this->request->data['Welder']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data['id'] = $id;

            if ($this->Welder->save($this->request->data)) {


                $this->Flash->success(__('The welder has been saved'), array('key' => 'success'));
                $this->Autorisierung->Logger($id, $this->request->data['Welder']);
                $this->Session->delete('Welder.form');
                $FormName['controller'] = 'welders';
                $FormName['action'] = 'overview';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
            } else {
                $this->Flash->warning(__('The welder could not be saved. Please, try again.'), array('key' => 'warning'));

            }
        } else {
            $options = array('conditions' => array('Welder.' . $this->Welder->primaryKey => $id));
            $this->request->data = $this->Welder->find('first', $options);
        }

        $testingcomps = $this->Welder->Testingcomp->find('list', array('conditions' => array('Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps())));
        $this->set(compact('testingcomps'));

        $this->request->data = $this->Data->GeneralDropdownData(array('Welder'), $arrayData, $this->request->data);

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to list',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        $SettingsArray['dellink'] = array('discription' => __('Delete welder', true), 'controller' => 'welders','action' => 'delete', 'terms' => $this->request->params['pass']);
        //		$SettingsArray['addlink'] = array('discription' => __('Add welder',true), 'controller' => 'welders','action' => 'add', 'terms' => null);
        //		$SettingsArray['toplink'] = array('discription' => __('Back to overview',true), 'controller' => 'welders','action' => 'overview', 'terms' => $this->request->params['pass']);
        //		$SettingsArray['userlink'] = array('discription' => __('Welder overview',true), 'controller' => 'welders','action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);


        $this->Data->SessionFormload();   //wenn Sessiondatenvorhanden diese setzen P.Schmidt 4.9.2017
        $this->set('SettingsArray', $SettingsArray);
        $this->set('arrayData', $arrayData);
        $this->set('settings', $arrayData['settings']);

        if ($this->Session->read('lastModalURLArray.action') == 'certificate') {
            $this->layout = 'blank';
            $this->set('certificate_id', $this->request->params['pass'][16]);
            $this->render('edit_fast');
        }
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete()
    {
        $this->layout = 'modal';

        $id = $this->request->projectvars['VarsArray'][15];

        $this->Welder->id = $id;

        if (!$this->Welder->exists()) {
            throw new NotFoundException(__('Invalid welder'));
        }

        if (isset($this->request->data['Welder']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data['Welder']['deleted'] = 1;
            $this->request->data['Welder']['active'] = 0;
            if ($this->Welder->save($this->request->data)) {
                $this->Flash->success(__('Welder deleted'), array('key' => 'success'));
                $this->Autorisierung->Logger($id, $this->request->data['Welder']);
                //$this->Autorisierung->Logger($this->Device->getLastInsertID(), $device['Device']);

                $FormName['controller'] = 'welders';
                $FormName['action'] = 'index';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
            //$this->index();
                                //$this->render('index', 'ajax');
            } else {
                $this->Flash->warning(__('Welder was not deleted. Please, try again.'), array('key' => 'warning'));
            }
        }

        $options = array('conditions' => array('Welder.' . $this->Welder->primaryKey => $id));
        $this->request->data = $this->Welder->find('first', $options);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'edit', 'terms' =>  $this->request->params['pass']);

        $this->set('SettingsArray', $SettingsArray);

        //		$this->set('afterEDIT', $afterEdit);
    }

    public function list_welder_workload()
    {
        if (isset($this->request->data['year']) && isset($this->request->data['month'])) {
            if ($this->request->data['month'] > 0) {
                $this->_list_welder_workload_complett_month();
            }
            if ($this->request->data['month'] == 0) {
                $this->_list_welder_workload_complett_year();
            }
            return;
        }

        if (!is_array($this->request->data['day'])) {
            $this->request->data['day'] = array($this->request->data['day']);
        }
        sort($this->request->data['day']);
        $that = $this;
        if (!array_reduce($this->request->data['day'], function ($prev, $curr) use ($that) {
            return $prev && $that->_validateDate($curr);
        }, true)) {
            throw new BadRequestException(__('Invalid Date'));
        }

        if (count($this->request->data['day']) == 1) {
            $this->_list_welder_workload_by_day();
        } else {
            $this->_list_welder_workload_by_month();
        }
    }

    protected function _list_welder_workload_complett_year()
    {
        $this->loadModel('Reportnumber');

        $welderID_condition = array();

        if ($this->request->projectvars['reportnumberID'] == 0) {
            $options = array('fields'=>array('id'), 'conditions'=>array(
                'Reportnumber.topproject_id'	=> $this->request->projectvars['projectID'],
                'Reportnumber.order_id'			=> $this->request->projectvars['orderID'],
                'Reportnumber.report_id'		=> $this->request->projectvars['reportID'],
                'Reportnumber.testingcomp_id'	=> $this->Autorisierung->ConditionsTestinccomps(),
                'Reportnumber.delete'			=> 0,
            ));
            $options['conditions'] = array_filter($options['conditions']);
            $reportnumbers = $this->Reportnumber->find('list', $options);
        } else {
            if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }
            $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

            $reportnumbers = array($this->request->projectvars['reportnumberID']);
        }

        $options = array('fields'=>array('Testingcomp_id'), 'conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers));
        $this->Reportnumber->recursive = 0;
        $testingcomps = array_unique($this->Reportnumber->find('list', $options));

        if (isset($this->request->projectvars['examiniererID']) && $this->request->projectvars['examiniererID'] > 0) {
            $welderid = $this->Welder->find('first', array('fields'=>array('name'),
                'conditions' => array(
                    'Welder.testingcomp_id' => $testingcomps,
                    'Welder.id' => $this->request->projectvars['examiniererID']
            )));

            $welderID_condition = array('WelderTime.welder_id'=> $this->request->projectvars['examiniererID']);

            $this->set('welder', $welderid);
        }

        $this->WelderTime->recursive = -1;

        $order = array('order' => array(
                'YEAR(WelderTime.testing_time_start)',
            )
        );

        $reportnumberID_condition = array();

        if ($this->request->projectvars['reportID'] > 0) {
            $reportnumberID_condition = array('WelderTime.reportnumber_id' => $reportnumbers);
        }

        $options = array(
        'conditions' => array(
            $welderID_condition,
            $reportnumberID_condition,
            'YEAR(WelderTime.testing_time_start)' => $this->request->data['year'],
            'WelderTime.waiting_time_start' => null,
            ),
            $order
        );

        $weldertime1 = $this->WelderTime->find('all', $options);

        $options = array('conditions' => array(
            $welderID_condition,
            $reportnumberID_condition,
            'YEAR(WelderTime.waiting_time_start)' => $this->request->data['year'],
            'WelderTime.testing_time_start' => null,
            ),
            $order
        );
        $weldertime2 = $this->WelderTime->find('all', $options);

        $options = array('conditions' => array(
            $welderID_condition,
            $reportnumberID_condition,
            'YEAR(WelderTime.waiting_time_start)' => $this->request->data['year'],
            'YEAR(WelderTime.waiting_time_start)' => $this->request->data['year'],
            ),
            $order
        );
        $weldertime3 = $this->WelderTime->find('all', $options);

        $weldertime = array_merge($weldertime1, $weldertime2, $weldertime3);

        $SettingsArray = array();
        $this->request->projectvars['VarsArray']['15'] = $this->request->projectvars['examiniererID'];
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
        if ($this->request->projectvars['reportnumberID'] != 0) {
            $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'welders','action' => 'add_workload', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $this->set('SettingsArray', $SettingsArray);

        if (count($weldertime) == 0) {
            $this->render('no_data', 'modal');
            return;
        }

        $order = ($this->WelderTime->Order->find('first', array('conditions' => array('Order.id' => $weldertime[0]['WelderTime']['order_id']))));

        $this->set('welderTimes', $weldertime);
        $this->set('order', $order);
        $this->set('width', $this->request->data['width']);
        $this->set('limits', count($weldertime));

        $this->render('list_welder_workload_complett_year_gantt', 'modal');
    }

    protected function _list_welder_workload_complett_month()
    {
        $this->loadModel('Reportnumber');

        $welderID_condition = array();

        if ($this->request->projectvars['reportnumberID'] == 0) {
            $options = array('fields'=>array('id'), 'conditions'=>array(
                'Reportnumber.topproject_id'	=> $this->request->projectvars['projectID'],
                'Reportnumber.order_id'			=> $this->request->projectvars['orderID'],
                'Reportnumber.report_id'		=> $this->request->projectvars['reportID'],
                'Reportnumber.testingcomp_id'	=> $this->Autorisierung->ConditionsTestinccomps(),
                'Reportnumber.delete'			=> 0,
            ));
            $options['conditions'] = array_filter($options['conditions']);
            $reportnumbers = $this->Reportnumber->find('list', $options);
        } else {
            if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }
            $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

            $reportnumbers = array($this->request->projectvars['reportnumberID']);
        }

        $options = array('fields'=>array('Testingcomp_id'), 'conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers));
        $this->Reportnumber->recursive = 0;
        $testingcomps = array_unique($this->Reportnumber->find('list', $options));

        if (isset($this->request->projectvars['examiniererID']) && $this->request->projectvars['examiniererID'] > 0) {
            $welderid = $this->Welder->find('first', array('fields'=>array('name'),
                'conditions' => array(
                    'Welder.testingcomp_id' => $testingcomps,
                    'Welder.id' => $this->request->projectvars['examiniererID']
            )));

            $welderID_condition = array('WelderTime.welder_id'=> $this->request->projectvars['examiniererID']);

            $this->set('welder', $welderid);
        }

        $this->WelderTime->recursive = -1;

        $order = array('order' => array(
                'YEAR(WelderTime.testing_time_start)',
                'MONTH(WelderTime.testing_time_start)'
            )
        );

        $reportnumberID_condition = array();

        if ($this->request->projectvars['reportID'] > 0) {
            $reportnumberID_condition = array('WelderTime.reportnumber_id' => $reportnumbers);
        }

        $options = array(
        'conditions' => array(
            $welderID_condition,
            $reportnumberID_condition,
            'YEAR(WelderTime.testing_time_start)' => $this->request->data['year'],
            'MONTH(WelderTime.testing_time_start)' => $this->request->data['month'],
            'WelderTime.waiting_time_start' => null,
            ),
            $order
        );

        $weldertime1 = $this->WelderTime->find('all', $options);

        $options = array('conditions' => array(
            $welderID_condition,
            $reportnumberID_condition,
            'YEAR(WelderTime.waiting_time_start)' => $this->request->data['year'],
            'MONTH(WelderTime.waiting_time_start)' => $this->request->data['month'],
            'WelderTime.testing_time_start' => null,
            ),
            $order
        );
        $weldertime2 = $this->WelderTime->find('all', $options);

        $options = array('conditions' => array(
            $welderID_condition,
            $reportnumberID_condition,
            'YEAR(WelderTime.waiting_time_start)' => $this->request->data['year'],
            'MONTH(WelderTime.waiting_time_start)' => $this->request->data['month'],
            'YEAR(WelderTime.waiting_time_start)' => $this->request->data['year'],
            'MONTH(WelderTime.waiting_time_start)' => $this->request->data['month'],
            ),
            $order
        );
        $weldertime3 = $this->WelderTime->find('all', $options);

        $weldertime = array_merge($weldertime1, $weldertime2, $weldertime3);

        $SettingsArray = array();
        $this->request->projectvars['VarsArray']['15'] = $this->request->projectvars['examiniererID'];
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
        if ($this->request->projectvars['reportnumberID'] != 0) {
            $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'welders','action' => 'add_workload', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $this->set('SettingsArray', $SettingsArray);

        if (count($weldertime) == 0) {
            $this->render('no_data', 'modal');
            return;
        }

        $order = ($this->WelderTime->Order->find('first', array('conditions' => array('Order.id' => $weldertime[0]['WelderTime']['order_id']))));

        $this->set('welderTimes', $weldertime);
        $this->set('order', $order);
        $this->set('width', $this->request->data['width']);
        $this->set('limits', count($weldertime));

        $this->render('list_welder_workload_complett_month_gantt', 'modal');
    }

    protected function _list_welder_workload_by_month()
    {
        $this->loadModel('Reportnumber');
        if ($this->request->projectvars['reportnumberID'] == 0) {
            $options = array('fields'=>array('id'), 'conditions'=>array(
                'Reportnumber.topproject_id'	=> $this->request->projectvars['projectID'],
                'Reportnumber.order_id'			=> $this->request->projectvars['orderID'],
                'Reportnumber.report_id'		=> $this->request->projectvars['reportID'],
                'Reportnumber.testingcomp_id'	=> $this->Autorisierung->ConditionsTestinccomps(),
                'Reportnumber.delete'			=> 0,
            ));
            $options['conditions'] = array_filter($options['conditions']);
            $reportnumbers = $this->Reportnumber->find('list', $options);
        } else {
            if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }
            $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

            $reportnumbers = array($this->request->projectvars['reportnumberID']);
        }

        $options = array('fields'=>array('Testingcomp_id'), 'conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers));
        $this->Reportnumber->recursive = 0;
        $testingcomps = array_unique($this->Reportnumber->find('list', $options));

        $welderid = $this->Welder->find('list', array('fields'=>array('id'), 'conditions'=>array(
            'Welder.id'=>$this->request->data['welder'],
            'Welder.testingcomp_id' => $testingcomps
        )));

        $options = array('conditions' => array(
            'WelderTime.welder_id'=>$welderid,
            'WelderTime.reportnumber_id'=>$reportnumbers,
            'date(WelderTime.testing_time_start) >=' => reset($this->request->data['day']),
            'date(WelderTime.testing_time_end) <=' => end($this->request->data['day'])
        ));

        $this->WelderTime->recursive = -1;
        $this->set('limits', $this->request->data['day']);
        //$this->set('welderTimes', $this->WelderTime->find('all', $options));
        $this->paginate = array(
            'limit' => 25
        );

        $this->set('welderTimes', $this->paginate('WelderTime', $options['conditions']));
        $this->set('width', $this->request->data['width']);

        $SettingsArray = array();

        if ($this->request->projectvars['reportnumberID'] != 0) {
            $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'welders','action' => 'add_workload', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $this->set('SettingsArray', $SettingsArray);

        $diff = array_diff(explode('-', $this->request->data['day'][0]), explode('-', $this->request->data['day'][1]));
        if ($this->WelderTime->find('count', $options) == 0) {
            echo '<p>'.__('No data to display').'</p>';
            $this->render(null, 'blank');
        } elseif (isset($diff[1])) { // Monat unterschiedlich - JahresÃƒÂ¼bersicht
            $this->render('list_welder_workload_year_gantt', 'csv');
        } else {
            $this->render('list_welder_workload_month_gantt', 'csv');
        }
    }

    protected function _list_welder_workload_by_day()
    {
        $this->loadModel('Reportnumber');
        if ($this->request->projectvars['reportnumberID'] == 0) {
            $options = array(
                'fields'=>array('id'),
                'conditions'=>array(
                    'Reportnumber.topproject_id'	=> $this->request->projectvars['projectID'],
                    'Reportnumber.order_id'			=> $this->request->projectvars['orderID'],
                    'Reportnumber.report_id'		=> $this->request->projectvars['reportID'],
                    'Reportnumber.testingcomp_id'	=> $this->Autorisierung->ConditionsTestinccomps(),
                    'Reportnumber.delete'			=> 0,
                )
            );
            $options['conditions'] = array_filter($options['conditions']);
            $reportnumbers = $this->Reportnumber->find('list', $options);
        } else {
            if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }
            $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

            $reportnumbers = array($this->request->projectvars['reportnumberID']);
        }

        $options = array('fields'=>array('Testingcomp_id'), 'conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers));
        $this->Reportnumber->recursive = 0;
        $testingcomps = array_unique($this->Reportnumber->find('list', $options));

        $this->WelderTime->recursive = 2;
        $options = array(
            'Welder.id'=>$this->request->data['welder'],
            'Welder.testingcomp_id' => $testingcomps,
            'WelderTime.reportnumber_id'=>$reportnumbers,
            'date(WelderTime.testing_time_start) >=' => reset($this->request->data['day']),
            'date(WelderTime.testing_time_end) <=' => end($this->request->data['day'])
        );

        //pr(preg_grep_key('/WelderTime\.testing/', $options));
        $this->set('welders', $this->paginate('WelderTime', $options));

        $SettingsArray = array();
        if ($this->request->projectvars['reportnumberID'] != 0) {
            $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'welders','action' => 'add_workload', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $this->set('SettingsArray', $SettingsArray);

        if ($this->WelderTime->find('count', array('conditions'=>$options)) == 0) {
            $this->render(null, 'blank');
        }

        $this->render('list_welder_workload', 'csv');
    }

    public function add_workload()
    {
        $afterEdit = null;
        $this->loadModel('Reportnumber');
        if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
            throw new NotFoundException(__('Invalid reportnumber'));
        }

        $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

        $options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $this->request->projectvars['reportnumberID']));
        $this->Reportnumber->recursive = 0;
        $reportnumbers = $this->Reportnumber->find('first', $options);

        $this->set('collision', array());

        $welders = $this->Welder->find('all', array('order'=>array('Welder.name ASC'),'conditions'=>array('Welder.testingcomp_id'=>$reportnumbers['Testingcomp']['id'])));
        $exam = array();

        foreach ($welders as $welder) {
            $exam[$welder['Welder']['id']] = $welder['Welder']['name'];
        }

        if (isset($this->request->data['WelderTime']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['caption'])) {
                unset($this->request->data['caption']);
            }
            $this->request->data['WelderTime']['reportnumber_id'] = $reportnumbers['Reportnumber']['id'];
            $this->request->data['WelderTime']['order_id'] = $reportnumbers['Reportnumber']['order_id'];
            if (isset($this->request->data['force_save'])) {
                $this->request->data['WelderTime']['force_save'] = $this->request->data['force_save'];
            }
            $this->WelderTime->create();
            if ($this->WelderTime->save($this->request->data)) {
                // Erneut abspeichern, um die Fehler-IDs richtig einzufÃƒÂ¼gen
                $this->WelderTime->recursive = -1;
                $this->request->data = $this->WelderTime->find('first', array('conditions'=>array('WelderTime.'.$this->WelderTime->primaryKey=>$this->WelderTime->getLastInsertID())));

                if (isset($this->request->data['WelderTime']['collision'])) {
                    $this->request->data['WelderTime']['collision'] = str_replace('"id":null', '"id":'.$this->request->data['WelderTime']['id'], $this->request->data['WelderTime']['collision']);
                    $this->request->data['WelderTime']['force_save'] = 1;
                    $this->WelderTime->save($this->request->data);
                }

                $this->Session->setFlash(__('Welder workload saved'));
                $afterEdit = $this->Navigation->afterEditModal(Router::url(array_merge(array('action'=>'list_workload'), $this->request->projectvars['VarsArray'])), 0);

                $logdata = $this->request->data['WelderTime'];
                $logdata['welder']=$exam[$logdata['welder_id']];
                $this->Autorisierung->Logger($this->WelderTime->getLastInsertID(), $logdata);
            } else {
                $this->set('collision', $this->WelderTime->data['WelderTime']['collision']);
                if (isset($this->WelderTime->data['WelderTime']['collision']['waiting_time'])) {
                    $this->WelderTime->data['WelderTime']['collision']['waiting_time'] = array_map(function ($elem) {
                        return $elem['Reportnumber']['number'].'-'.$elem['WelderTime']['id'];
                    }, $this->WelderTime->data['WelderTime']['collision']['waiting_time']);
                }
                if (isset($this->WelderTime->data['WelderTime']['collision']['testing_time'])) {
                    $this->WelderTime->data['WelderTime']['collision']['testing_time'] = array_map(function ($elem) {
                        return $elem['Reportnumber']['number'].'-'.$elem['WelderTime']['id'];
                    }, $this->WelderTime->data['WelderTime']['collision']['testing_time']);
                }
                $this->request->data = $this->WelderTime->data;

                $this->Session->setFlash(__('Welder workload was not saved'));
                $this->set('ask_force', 1);
            }
        }

        $this->set('welders', $exam);
        $this->set('reportnumber', $reportnumbers);
        $this->set('afterEDIT', $afterEdit);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Add', true), 'controller' => 'welders','action' => 'list_workload', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function edit_workload()
    {
        $afterEdit = null;
        $id = $this->request->params['pass'][15];

        if (!$this->WelderTime->exists($id)) {
            throw new NotFoundException(__('Invalid workload id'));
        }

        $this->loadModel('Reportnumber');
        if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
            //			throw new NotFoundException(__('Invalid reportnumber'));
        }

        $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

        $options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $this->request->projectvars['reportnumberID']));
        $this->Reportnumber->recursive = 0;
        $reportnumbers = $this->Reportnumber->find('first', $options);


        $this->set('collision', array());

        if (isset($this->request->data['WelderTime']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['caption'])) {
                unset($this->request->data['caption']);
            }
            $this->request->data['WelderTime'][$this->WelderTime->primaryKey] = $id;
            $this->request->data['WelderTime']['reportnumber_id'] = $reportnumbers['Reportnumber']['id'];
            $this->request->data['WelderTime']['order_id'] = $reportnumbers['Reportnumber']['order_id'];
            if (isset($this->request->data['force_save'])) {
                $this->request->data['WelderTime']['force_save'] = $this->request->data['force_save'];
            }
            if ($this->WelderTime->save($this->request->data)) {
                $this->Session->setFlash(__('Welder workload saved'));
                $afterEdit = $this->Navigation->afterEditModal(Router::url(array_merge(array('action'=>'list_workload'), $this->request->projectvars['VarsArray'])), 0);

                // neue Werte aus der Datenbank holen, mum automatisch ergÃƒÂ¤nzte Felder zu bekommen
                $logData = $this->request->data['WelderTime'];
                if (isset($logData['collision'])) {
                    unset($logData['collision']);
                }

                $welder = $this->WelderTime->Welder->find('first', array('conditions'=>array('Welder.'.$this->WelderTime->Welder->primaryKey=>$logData['welder_id'])));
                $logData['welder'] = $welder['Welder']['display'];
                $this->Autorisierung->Logger($id, $logData);
            } else {
                $this->set('collision', $this->WelderTime->data['WelderTime']['collision']);
                if (isset($this->WelderTime->data['WelderTime']['collision']['waiting_time'])) {
                    $this->WelderTime->data['WelderTime']['collision']['waiting_time'] = array_map(function ($elem) {
                        return $elem['Reportnumber']['number'].'-'.$elem['WelderTime']['id'];
                    }, $this->WelderTime->data['WelderTime']['collision']['waiting_time']);
                }
                if (isset($this->WelderTime->data['WelderTime']['collision']['testing_time'])) {
                    $this->WelderTime->data['WelderTime']['collision']['testing_time'] = array_map(function ($elem) {
                        return $elem['Reportnumber']['number'].'-'.$elem['WelderTime']['id'];
                    }, $this->WelderTime->data['WelderTime']['collision']['testing_time']);
                }
                $this->request->data = $this->WelderTime->data;

                $this->Session->setFlash(__('Welder workload was not saved'));
                $this->set('ask_force', 1);
            }
        } else {
            $this->request->data = $this->WelderTime->find('first', array('conditions'=>array('WelderTime.'.$this->WelderTime->primaryKey => $id)));
        }

        $welders = $this->Welder->find('all', array('order'=>array('Welder.name ASC'),'conditions'=>array('Welder.testingcomp_id'=>$reportnumbers['Testingcomp']['id'])));
        $exam = array();
        foreach ($welders as $welder) {
            $exam[$welder['Welder']['id']] = $welder['Welder']['name'];
        }
        $this->set('welders', $exam);
        $this->Reportnumber->recursive = 0;
        $this->set('reportnumber', $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.'.$this->Reportnumber->primaryKey=>$reportnumbers['Reportnumber']['id']))));
        $this->set('afterEDIT', $afterEdit);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Add', true), 'controller' => 'welders','action' => 'list_workload', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function delete_workload()
    {
        $id = $this->request->params['pass'][15];

        if (!$this->WelderTime->exists($id)) {
            throw new NotFoundException(__('Invalid workload id'));
        }

        $this->loadModel('Reportnumber');
        if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
            throw new NotFoundException(__('Invalid reportnumber'));
        }

        $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

        $this->request->onlyAllow('post', 'delete_workload');

        $afterEdit = $this->Navigation->afterEditModal(Router::url(array_merge(array('action'=>'list_workload'), $this->request->projectvars['VarsArray'])), 0);
        $this->WelderTime->recursive = -1;
        $logData = $this->WelderTime->find('first', array('conditions'=>array('WelderTime.'.$this->WelderTime->primaryKey=>$id)));

        if ($this->WelderTime->delete($id)) {
            $this->WelderTime->recursive = -1;
            $workloads = $this->WelderTime->find('all', array('conditions'=>array('collision REGEXP'=>'"workload":'.$id)));
            $workloads = array_map(function ($elem) {
                unset($elem['WelderTime']['collision']);
                $elem['WelderTime']['force_save'] = 1;
                return $elem;
            }, $workloads);

            $this->WelderTime->saveMany($workloads);

            $this->Session->setFlash(__('Welder workload deleted'));
            $this->Autorisierung->Logger($id, $logData['WelderTime']);
        } else {
            $this->Session->setFlash(__('Welder workload was not deleted'));
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'index', 'terms' => null);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('afterEDIT', $afterEdit);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Add', true), 'controller' => 'welders','action' => 'list_workload', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    //14.08.2017 Schmidt
    public function pdf()
    {
        $this->layout = 'pdf';
        $locale = $this->Lang->Discription();
        if ($this->request->projectvars['VarsArray'][15] == 0 && isset($this->request->data['welder_id']) && $this->request->data['welder_id'] > 0) {
            $this->request->projectvars['VarsArray'][15] = $this->Sicherheit->Numeric($this->request->data['welder_id']);
        }

        if ($this->request->projectvars['VarsArray'][15] > 0) {
            $welder_id = $this->request->projectvars['VarsArray'][15];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $arrayData = $this->Xml->DatafromXml('WelderTest', 'file', null);
        if (!is_array($arrayData)) {
            return;
        }

        $this->set('xml_welder', $arrayData['settings']);

        $options = array(
                        'conditions' => array(
                                            'Welder.deleted' => 0,
                                            'Welder.id' => $welder_id,
                                            //'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );
        $this->Welder->recursive = 1;
        $Welder = $this->Welder->find('first', $options);

        if ($Welder['Welder']['id'] == 0) {
            unset($Welder['Welder']);
        }

        if (count($Welder) == 0) {
            return;
        }
        $data = array();
        $data ['Welder'] = $Welder ['Welder'];

        $this->loadModel('Testingcomp');
        $options = array(
                        'conditions' => array(
                                            'Testingcomp.id' => $Welder['Welder']['testingcomp_id'],
                                        )
                        );
        $this->Testingcomp->recursive = -1;
        $Testingcomp = $this->Testingcomp->find('first', $options);
        $Welder = array_merge($Welder, $Testingcomp);

        $adress = null;
        $testingcomp = '';
        $contact = null;
        $imagePath = Configure::read('welder_picture_folder') . $id . DS.$Welder['Welder']['profile_picture'];

        if (!empty($Welder['Testingcomp']['firmenname'])) {
            $testingcomp .= $Welder['Testingcomp']['firmenname'] . "\n";
        }
        if (!empty($Welder['Testingcomp']['firmenzusatz'])) {
            $testingcomp .= $Welder['Testingcomp']['firmenzusatz'] . "\n";
        }
        if (!empty($Welder['Testingcomp']['strasse'])) {
            $testingcomp .= $Welder['Testingcomp']['strasse'] . "\n";
        }
        if (!empty($Welder['Testingcomp']['plz'])) {
            $testingcomp .= $Welder['Testingcomp']['plz'] . " ";
        }
        if (!empty($Welder['Testingcomp']['ort'])) {
            $testingcomp .= $Welder['Testingcomp']['ort'] . "\n";
        }
        if (!empty($Welder['Testingcomp']['telefon'])) {
            $testingcomp .= __('Tel', true) . ': ' . $Welder['Testingcomp']['telefon'] . "\n";
        }
        if (!empty($Welder['Testingcomp']['telefax'])) {
            $testingcomp .= __('Fax', true) . ': ' . $Welder['Testingcomp']['telefax'] . "\n";
        }
        if (!empty($Welder['Testingcomp']['internet'])) {
            $testingcomp .= $Welder['Testingcomp']['internet'] . "\n";
        }
        if (!empty($Welder['Testingcomp']['email'])) {
            $testingcomp .= $Welder['Testingcomp']['email'] . "\n";
        }

        $Welder['Testingcomp']['summary'] = $testingcomp;

        $CurrentTestingcomp = $this->Auth->user('Testingcomp');

        if (!empty($CurrentTestingcomp['firmenname'])) {
            $adress .= $this->Auth->user('Testingcomp.firmenname') . "\n";
        }
        if (!empty($CurrentTestingcomp['firmenzusatz'])) {
            $adress .= $this->Auth->user('Testingcomp.firmenzusatz') . "\n";
        }
        if (!empty($CurrentTestingcomp['strasse'])) {
            $adress .= $this->Auth->user('Testingcomp.strasse') . "\n";
        }
        if (!empty($CurrentTestingcomp['plz'])) {
            $adress .= $this->Auth->user('Testingcomp.plz') . " ";
        }
        if (!empty($CurrentTestingcomp['ort'])) {
            $adress .= $this->Auth->user('Testingcomp.ort') . " ";
        }

        if (!empty($CurrentTestingcomp['telefon'])) {
            $contact .= __('Tel', true) . ': ' . $this->Auth->user('Testingcomp.telefon') . "\n";
        }
        if (!empty($CurrentTestingcomp['telefax'])) {
            $contact .= __('Fax', true) . ': ' . $this->Auth->user('Testingcomp.telefax') . "\n";
        }
        if (!empty($CurrentTestingcomp['internet'])) {
            $contact .= $this->Auth->user('Testingcomp.internet') . "\n";
        }
        if (!empty($CurrentTestingcomp['email'])) {
            $contact .= $this->Auth->user('Testingcomp.email') . " ";
        }

        /*if(file_exists($this->Auth->user('Testingcomp.logopfad'))){
            $this->request->data['welderinfo']['logo'] = $this->Auth->user('Testingcomp.logopfad');
        }*/


        if ($this->request->projectvars['VarsArray'][0] <> 0 || !empty($this->request->projectvars['VarsArray'][0])) {
            $id_vt = $this->request->projectvars['VarsArray'][0];
            if (isset($this->request->projectvars['VarsArray'][16])) {
                $certificate_id = $this->request->projectvars['VarsArray'][16];
            }

            if (isset($this->request->projectvars['VarsArray'][17])) {
                $certificate_data_id = $this->request->projectvars['VarsArray'][17];
            }


            $certificate_option = array(

                                    'conditions' => array(
                                        'WelderCertificate.welder_id' => $welder_id,
                                        'WelderCertificate.deleted' => 0,
                                        'WelderCertificate.active' => 1,
                                                                                'WelderCertificate.id' => $certificate_id
                                        )
                                    );

            $arrayData = array();
            $arrayData = $this->Xml->DatafromXml('WelderTest', 'file', null);
            $certificate = $this->Welder->WelderCertificate->find('first', $certificate_option);

            $data ['WelderCertificate'] = $certificate['WelderCertificate'];
            $this->loadModel('ReportVtstEvaluation');
            $this->loadModel('ReportRtstEvaluation');
            $this->loadModel('Reportnumber');

            $reports = array();
            $reports['ReportVtstEvaluation'] = $this->ReportVtstEvaluation->find('all', array(
                                                                                'conditions' => array(
                                        'reportnumber_id' => $id_vt,
                                                                                'deleted' => 0,
                                        )
                                    ));

            $reportnumberRtst = $this->Reportnumber->find('first', array('conditions' => array('parent_id' =>$id_vt),'fields' => array('id')));
            $reportnumberRtst  =  $reportnumberRtst ['Reportnumber'] ['id'];

            $reports['ReportRtstEvaluation'] = $this->ReportRtstEvaluation->find('all', array(
                                                                                'conditions' => array(
                                        'reportnumber_id' => $reportnumberRtst,
                                        )
                                    ));

            $data ['ReportRtstEvaluation'] =   $reports['ReportRtstEvaluation'];
            $data ['ReportVtstEvaluation'] =   $reports['ReportVtstEvaluation'];
            $this->request->data = $data;


            $this->request->data['welderinfo']['adress'] = $testingcomp;
            $this->request->data['welderinfo']['profile_picture'] = $imagePath;
            if (file_exists(Configure::read('company_logo_folder') .$Welder['Testingcomp']['id'] . DS . 'logo.png')) {
                $this->request->data['welderinfo']['logo'] = Configure::read('company_logo_folder') . $Welder['Testingcomp']['id'] . DS . 'logo.png';
            }

            //   pr($data); die();
            $this->set('locale', $locale);
            $this->render('print_test');
        } else {




        //$this->set('xml_device_certificate',$arrayData['settings']);




            //Sehtests
            $eyecheck_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        'WelderEyecheckData.welder_id' => $welder_id,
                                        'WelderEyecheckData.deleted' => 0,
                                        'WelderEyecheckData.active' => 1
                                        )
                                    );








            $arrayData = array();
            $arrayData = $this->Xml->DatafromXml('WelderEyecheckData', 'file', null);
            if (!is_array($arrayData)) {
                return;
            }

            $this->set('xml_eyecheckdata', $arrayData['settings']);
            $this->Welder->WelderEyecheck->WelderEyecheckData->recursive = 0;
            $eyecheck_data = $this->Welder->WelderEyecheck->WelderEyecheckData->find('first', $eyecheck_data_option);


            $arrayData = array();
            $arrayData = $this->Xml->DatafromXml('WelderEyecheck', 'file', null);
            if (!is_array($arrayData)) {
                return;
            }
            $this->set('xml_eyecheck', $arrayData['settings']);


            /*
                          $certificate_option = array(

                                  'conditions' => array(
                                      'WelderCertificate.welder_id' => $welder_id,
                                      'WelderCertificate.deleted' => 0,
                                      'WelderCertificate.active' => 1
                                      )
                                  );

            $arrayData = array();
             $arrayData = $this->Xml->DatafromXml('WelderCertificate','file',null);
             if(!is_array($arrayData)) return;

             $this->set('xml_certificate',$arrayData['settings']);
             $this->Welder->WelderEyecheck->WelderEyecheckData->recursive = 0;
             $certificate_data = $this->Welder->WelderCertificate->find('first',$certificate_option);
             */


            $certificates = $this->Qualification->WelderCertificatesSectors($Welder);
            if (isset($certificates)&& count($certificates) > 0) {
                $this->request->data['welderinfo']['WelderCertificate'] = $certificates;
            }


            //$summary = $this->Qualification->WelderCertificateSummary($certificates,array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));
            $arrayData = $this->Xml->DatafromXml('WelderCertificate', 'file', null);
            if (!is_array($arrayData)) {
                return;
            }
            $this->set('xml_certificate', $arrayData['settings']);

            $arrayData = $this->Xml->DatafromXml('WelderCertificateData', 'file', null);
            if (!is_array($arrayData)) {
                return;
            }
            $this->set('xml_certificatedata', $arrayData['settings']);

            $SettingsArray = array();
            $this->request->projectvars['VarsArray'][15] = $welder_id;

            $this->request->data['welderinfo']['info'] = $Welder;
            $this->request->data['welderinfo']['contact'] = $contact;
            $this->request->data['welderinfo']['adress'] = $testingcomp;
            $this->request->data['welderinfo']['WelderEyecheck'] = $eyecheck_data;
        }
    } //14.08.2017 Schmidt

    public function sessionstorage()
    {
        if ($this->request->data['req_controller'] == 'welders') {
            $act = $this->request->data['req_action'];
            switch ($act) {
                case 'qualification':
                    $this->Data->Sessionstoraging('WelderCertificate');
                    break;
                case 'qualificationnew':
                    $this->Data->Sessionstoraging('WelderCertificate');
                    break;
                default:
                    $this->Data->Sessionstoraging();
            }
            // $this->Data->Sessionstoraging();
        }
    }


    public function search()
    {
        isset($this->request->data['delsession']) && $this->request->data['delsession'] > 0 ? $this->Session->delete('searchwelder'):'';
        unset($this->Welder->validate);
        $this->layout = 'modal';
        $arrayData = $this->Xml->DatafromXml('searchwelder', 'file', null);
        $settings = $arrayData['settings'];

        $locale = $this->Lang->Discription();

        $this->Welder->recursive = 1;

        //$period = array(0,1,2,3);

        /*	// Alle ÃƒÅ“berwachungsarten aus der Tabelle holen
            $this->Welder->WelderWelderCertificate->recursive = -1;
            $MonitoringKind = $this->Welder->WelderWelderCertificate->find('all',array('order' => array('certificat'),'fields' => array('certificat'),'group' => array('certificat')));
            $MonitoringKind = Hash::insert($MonitoringKind, '{n}.WelderWelderCertificate.checked', true);

            $this->Welder->WelderWelderCertificate->recursive = 1; */

        foreach ($settings->Welder->children() as $_settings => $value) {
            if ($value->fieldtype == 'select' && (!empty($value->key))) {
                $selects[$_settings] ['discription'] = trim($value->discription->$locale);
                $selects[$_settings] ['values'] = $this->Welder->find(
                    'list',
                    array(
                'fields' => array($_settings,$_settings),
                                'order'  => array($_settings => 'asc'),
                                    'conditions' => array(
                                        'Welder.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        'Welder.deleted' => 0
                                    )
                )
                );
                $selects[$_settings] ['values'] = array_unique($selects[$_settings] ['values']);
            }
        }

        if (isset($this->request->data['Welder']) && !empty($this->request->data)&& ($this->request->is('post'))) {

            /*// Je nach dem, welche ÃƒÅ“berwachungen angezeigt werden sollen, werden im ÃƒÅ“berwachungenArray mit true oder false gekennzeichnet
            if(isset($this->request->data['Monitoring'])){
                foreach($MonitoringKind as $_key => $_MonitoringKind){
                    if(isset($this->request->data['Monitoring'][$_MonitoringKind['WelderWelderCertificate']['certificat']])){
                    } else {
                        $MonitoringKind[$_key]['WelderWelderCertificate']['checked'] = false;
                    }
                }
            } else {
                $MonitoringKind = Hash::insert($MonitoringKind, '{n}.WelderWelderCertificate.checked', false);
            }*______________noch zu gebrauchen______________*/

            $this->request->data['User']['user_id'] = $this->Auth->user('id');
            $this->request->data['User']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, 'Welder');

            $this->Session->write('searchwelder.params', $this->request->data);
            $this->Session->write('searchwelder.fields', $this->request->data);
            //pr($this->Session->read('searchwelder.params'));
            $searchparams = '';
            //$weldingmethod_id = $this->request->data['WelderWeldingmethod'] ['WelderWeldingmethod'];

            //  isset($weldingmethod_id) ? $weldingmethod = $this->Welder->WelderWeldingmethod->find('first',array('conditions' => array('WelderWeldingmethod.id' => $weldingmethod_id))): '';
            foreach ($settings->Welder->children() as $_settings => $value) {

                 //Sortierung aus der XML holen---------------------------------
                if (isset($value->order)) {
                    $i= intval($value->order->priority);
                    $dir = $value->order->direction;
                    $OrderBy[$i]['Welder.'.$_settings] = trim($dir);
                }

                //Suchkriterien erzeugen -----------------------------------

                empty($this->request->data['WelderWelderCertificateData']['next_certification_in']) ? $this->request->data['WelderWelderCertificateData']['period'] = '' : '';
                $searchoutput = Hash::extract($this->request->data, '{s}.'.$_settings);
                /* if(!empty($weldingmethod)&& ($value->key == 'weldingmethod')) {
                     $searchparams = $searchparams.' '.$value->discription->$locale.': '.$weldingmethod ['WelderWeldingmethod'] ['verfahren'].' | ';
                 }*/

                if (isset($value->fieldtype) && ($value->fieldtype == 'select') && (empty($value->key))) {
                    if (!empty($searchoutput[0])) {
                        $opt = intval($searchoutput[0]);
                        $searchparams = $searchparams.' '.$value->discription->$locale.': '. $value->options->value[$opt]->$locale.' | ';
                    }
                } else {
                    !empty($searchoutput[0])? $searchparams = $searchparams.' '.$value->discription->$locale.': '. $searchoutput[0].' | ' : '';
                }
            }

            //Sortierung fÃƒÂ¼r die Ausgabe----------------------------------------
            ksort($OrderBy);
            $nOrderBy = array();
            foreach ($OrderBy as $ob_key => $ob_val) {
                $nOrderBy [key($ob_val)] =  $OrderBy [$ob_key] [key($ob_val)];
            }

            //Conditions aus den abschickten Daten erzeugen------------------------
            $weldersearch = $this->request->data['Welder'] ;
            foreach ($weldersearch as $ds_key => $value) {
                if (isset($value)&& $value <> '') {
                    if ($ds_key == 'active') {
                        $conditions['Welder.'.$ds_key] = $value;
                    } else {
                        $conditions['Welder.'.$ds_key.' LIKE'] = '%'.$value.'%';
                    }
                }
            }


            if (!empty($conditions) || !empty($this->request->data['WelderWelderCertificateData']['next_certification_in'])) {
                $conditions ['Welder.deleted'] = 0;
                $conditions['Welder.testingcomp_id'] = $this->Auth->user('testingcomp_id');

                //Daten holen----------------------------------------------


                $welders = $this->Welder->find('all', array(
                'order' => $nOrderBy,
                'conditions' =>  isset($conditions) ? $conditions : '',
                            ));


                $xml = $this->Xml->DatafromXml('display_welder', 'file', null);

                if (!is_object($xml['settings'])) {
                    pr('Einstellungen konnten nicht geladen werden.');
                    return;
                } else {
                    $this->set('xml', $xml['settings']);
                }
                $weldercertificatesearch = $this->request->data['WelderWelderCertificateData'];

                //Zeitraum setzen
                if (!empty($weldercertificatesearch['period'])) {
                    switch ($weldercertificatesearch['period']) {
                                case 1: $period = 'day';
                                    break;
                                case 2: $period = 'week';
                                    break;
                                case 3: $period = 'month';
                                    break;
                                case 4: $period = 'year';
                                    break;

                                default: $period = '';
                            }
                }

                if ($weldercertificatesearch['next_certification_in'] <> 0 && !empty($period)) {
                    if ($this->request->data['Monitorings']['OnOff'] > 0) {
                        foreach ($welders as $_key => $_welders) {
                            $all_certificats_no = '';
                            switch ($this->request->data['Monitorings']['OnOff']) {
                                         case 1:$monitorings[$_welders['Welder']['id']] = $this->Qualification->WelderEyecheckSummary($this->Qualification->WelderEyechecks($_welders), array('main' => 'WelderEyecheck','sub' => 'WelderEyecheckData'));
                                         break;
                                         case 2: $monitorings[$_welders['Welder']['id']] = $this->Qualification->WelderCertificateSummary($this->Qualification->WelderCertificatesSectors($_welders), array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));
                                         break;

                                     }
                            $countmonitoring= 0;
                            foreach ($monitorings[$_welders['Welder']['id']] ['summary'] as $_monitoringkey => $_value) {
                                foreach ($_value as $m_key => $m_value) {
                                    $nextcert = Hash::extract($m_value, '{s}.next_certification');
                                    $nextcert = $nextcert[0];

                                    if (!empty($period) && $weldercertificatesearch ['next_certification_in'] <> 0 && !empty($nextcert)) {
                                        if (($nextcert >= date('Y-m-d') && $nextcert <= date('Y-m-d', strtotime('+'.$weldercertificatesearch ['next_certification_in']. ' '.$period))) || ($nextcert < date('Y-m-d')&& $weldercertificatesearch ['next_certification_in'] == 1)) { // nÃƒÂ¤chste ÃƒÂ¼berwachung muss >= akt. Datum und <= als festgelgter Zeitraum

                                            $resultmonitorings[$_welders['Welder']['id']]['summary'] = $this->Qualification->WelderCertificateSummary($this->Qualification->WelderCertificatesSectors($_welders), array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));
                                            $resultmonitorings[$_welders['Welder']['id']]['eyecheck'] = $this->Qualification->WelderEyecheckSummary($this->Qualification->WelderEyechecks($_welders), array('main' => 'WelderEyecheck','sub' => 'WelderEyecheckData'));
                                            !empty($resultmonitorings[$_welders['Welder']['id']]['summary'] ['certificat_no']) ? $all_certificats_no = $resultmonitorings[$_welders['Welder']['id']]['summary'] ['certificat_no']:$all_certificats_no= '-';
                                            $welders [$_key] ['WelderCertificate'] ['certificat'] =  implode('/', $all_certificats_no);
                                            $result [] = $welders [$_key] ;
                                            $countmonitoring = 1;
                                            break;
                                        }
                                    }
                                }
                                if ($countmonitoring > 0) {
                                    break;
                                }// Schleifen werden abgebrochen wenn mindestens eine ÃƒÅ“berwachung im Zeitraum fÃƒÂ¤llig wird
                            }
                        }
                    }
                } else {
                    $result = $welders;
                    foreach ($result as $_resultkey => $_resultvalue) {
                        $all_certificats_no = '';
                        //$resultmonitorings[$_resultvalue['Welder']['id']] = $this->Qualification->MonitoringSummary($_resultvalue,array('top'=>'Welder','main'=>'WelderWelderCertificate','sub'=>'WelderWelderCertificateData'));
                        $resultmonitorings[$_resultvalue['Welder']['id']]['summary'] = $this->Qualification->WelderCertificateSummary($this->Qualification->WelderCertificatesSectors($_resultvalue), array('main'=>'WelderCertificate','sub'=>'WelderCertificateData'));
                        $resultmonitorings[$_resultvalue['Welder']['id']]['eyecheck'] = $this->Qualification->WelderEyecheckSummary($this->Qualification->WelderEyechecks($_resultvalue), array('main' => 'WelderEyecheck','sub' => 'WelderEyecheckData'));
                        !empty($resultmonitorings[$_resultvalue['Welder']['id']]['summary'] ['certificat_no']) ? $all_certificats_no = $resultmonitorings[$_resultvalue['Welder']['id']]['summary'] ['certificat_no']:'';
                        !empty($all_certificats_no)? $result [$_resultkey] ['WelderCertificate'] ['certificat'] =  implode('/', $all_certificats_no):$result [$_resultkey] ['WelderCertificate'] ['certificat']='';
                    }
                }
                if (isset($result)) {
                    $this->set('welders', $result);
                }
                if (!empty($resultmonitorings)) {
                    $this->set('summary', $resultmonitorings);
                }

                if (isset($result)) {
                    $countresult = count($result);
                } else {
                    $countresult = 0;
                }
                $this->Session->write('searchwelder.countresult', $countresult);
                if (!isset($this->request->data['shownoresults'])) {
                    $lastUrl = $this->Session->read('lastmodalArrayURL');

                    isset($result) ? $this->Session->write('searchwelder.welders', $result) : '';
                    $this->Session->write('searchwelder.params', $searchparams);
                    $this->Session->write('searchwelder.searching', 1);
                    $this->Session->write('searchwelder.monitoringon', $this->request->data['Monitorings']['OnOff']);

                    $SettingsArray = array();
                    $SettingsArray['backlink'] = array('discription' => __('back to search', true), 'controller' => 'welders','action' => 'search', 'terms' => null);
                    $SettingsArray['printlink'] = array('discription' => __('Print welder list', true), 'controller' => 'welders','action' => 'pdfinv', 'terms' => $this->request->projectvars['VarsArray']);

                    $this->set('SettingsArray', $SettingsArray);
                    $this->render('result', 'modal');
                }
            }
        }

        if ($this->Session->check('searchwelder.fields')) {
            $this->request->data = $this->Session->read('searchwelder.fields');
            if ($this->Session->check('searchwelder.countresult')) {
                $countresult = $this->Session->read('searchwelder.countresult');
            }
            if ($this->Session->check('searchwelder.searching')) {
                $this->request->data['searching'] = $this->Session->read('searchwelder.searching');
            }
            if ($this->Session->check('searchwelder.monitoringon')) {
                $this->request->data['Monitorings']['OnOff'] = $this->Session->read('searchwelder.monitoringon');
            }
        }

        if (!isset($this->request->data['Monitorings']['OnOff'])) {
            $this->request->data['Monitorings']['OnOff'] = 0;
        }
        $this->set('selects', $selects);
        $this->set('settings', $arrayData['headoutput']);
        $this->set('locale', $locale);
        //     $this->set('testingcomps', $testingcomps);
        isset($countresult) ? $this->set('countresult', $countresult): '';
        //$this->set('weldingmethods', $weldingmethods);
    }
    public function autocomplete()
    {
        App::uses('Sanitize', 'Utility');

        $this->layout = 'json';

        $fields = explode('_', Inflector::underscore($this->request->data['field']));

        if (count($fields) < 2) {
            $this->set('test', json_encode(array()));
            return;
        }

        unset($fields[0]);

        $field = Sanitize::clean(implode('_', $fields));

        $data = Sanitize::escape($this->request->data['term']);

        $Source = $this->Welder->getDataSource('columns');
        $table = $Source->describe('welders');

        if (!isset($table[$field])) {
            $this->set('test', json_encode(array()));
            return;
        }

        $options = array(
                        'limit' => 20,
                        'fields' => array($field),
                        'group' => array($field),
                        'conditions' => array(
                            'Welder.deleted' => 0,
                            'Welder.active' => 1,
                            'Welder.' . $field . ' LIKE' => '%' . $data . '%'
                            )
                        );

        $result = $this->Welder->find('list', $options);

        //$this->Autorisierung->WriteToLog($result);

        $this->set('test', json_encode($result));
    }
    public function addmonitoring()
    {
        $this->layout = 'modal';

        if ($this->request->projectvars['VarsArray'][15] > 0) {
            $welder_id = $this->request->projectvars['VarsArray'][15];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $options = array(
                        'conditions' => array(
                                            'Welder.deleted' => 0,
                                            'Welder.id' => $welder_id,
                                            'Welder.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('WelderMonitoring');
        if (isset($this->request->data['WelderMonitoring']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data['WelderMonitoring']['welder_id'] = $welder_id;
            $this->request->data['WelderMonitoring']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            $this->request->data['WelderMonitoring']['user_id'] = $this->Auth->user('id');


            //$this->request->data = $this->Data->GeneralDropdownData(array('WelderMonitoring'), $arrayData, $this->request->data);
            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('WelderMonitoring'));

            $WelderMonitoringExist = $this->Welder->WelderMonitoring->find(
                                                                          'first',
                                                                          array(
                                                                            'conditions'=>array(
                                                                                'WelderMonitoring.deleted' => 0,
                                                                                'WelderMonitoring.welder_id' => $welder_id,
                                                                                'WelderMonitoring.certificat' => $this->request->data['WelderMonitoring']['certificat'],

                                                                                )
                                                                            )
            );

            if (count($WelderMonitoringExist) > 0) {
                $this->Flash->warning(__('Information could not be saved, there is already a certificate').': '.$this->request->data['WelderMonitoring']['certificat'], array('key' => 'warning'));
            }

            if (count($WelderMonitoringExist) == 0) {
                $this->Welder->WelderMonitoring->create();

                if(isset($this->request->data['WelderMonitoring'])) {
                  if(isset($this->request->data['WelderMonitoring']['expiration_date'])) {
                    $this->request->data['WelderMonitoringData']['expiration_date'] = $this->request->data['WelderMonitoring']['expiration_date'];
                  }
                }

                if ($this->Welder->WelderMonitoring->save($this->request->data['WelderMonitoring'])) {

                    foreach ($this->request->data['WelderMonitoring'] as $_key => $_WelderMonitoring) {
                        $this->request->data['WelderMonitoringData'][$_key] = $_WelderMonitoring;
                    }

                    $this->request->data['WelderMonitoringData']['welder_monitoring_id'] = $this->Welder->WelderMonitoring->getLastInsertId();
                    $this->request->data['WelderMonitoringData']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
                    $this->request->data['WelderMonitoringData']['user_id'] = $this->Auth->user('id');
                    $this->request->data['WelderMonitoringData']['welder_id'] = $welder_id;
                    $this->request->data['WelderMonitoringData']['first_certification'] = 0;
                    $this->request->data['WelderMonitoringData']['certified'] = 1;
                    $this->request->data['WelderMonitoringData']['certified_date'] = $this->request->data['WelderMonitoring']['first_registration'];
                    $this->request->data['WelderMonitoringData']['renewal_in_year'] = 0;
                    $this->request->data['WelderMonitoringData']['apply_for_recertification'] = 0;
                    $this->request->data['WelderMonitoringData']['deleted'] = 0;
                    $this->request->data['WelderMonitoringData']['active'] = 1;

                    $this->Welder->WelderMonitoring->WelderMonitoringData->create();

                    if ($this->Welder->WelderMonitoring->WelderMonitoringData->save($this->request->data['WelderMonitoringData'])) {
                        $this->Flash->success(__('The certificate infos has been saved.'), array('key' => 'success'));

                        $this->Session->delete('WelderMonitoring.form');
                        $this->request->params['pass']['17'] = $this->Welder->WelderMonitoring->getLastInsertId();
                        $this->request->params['pass']['18'] = $this->Welder->WelderMonitoringData->getLastInsertId();

                        $this->request->params['action'] = 'monitoring';
                        $request_url = explode('/', $this->request->url);
                        $request_here = explode('/', $this->request->here);
                        $request_url[1] = 'monitoring';
                        $request_here[3] = 'monitoring';
                        $this->request->url = implode('/', $request_url);
                        $this->request->here = implode('/', $request_here);

                        $FormName['controller'] = 'welders';
                        $FormName['action'] = 'monitorings';
                        $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                        $this->set('saveOK', 1);
                        $this->set('FormName', $FormName);
                    } else {
                        $this->Welder->WelderMonitoring->delete($this->Welder->WelderMonitoring->getLastInsertId());
                        $this->Flash->warning(__('Information could not be saved.'), array('key' => 'warning'));
                    }
                } else {
                    $this->Flash->warning(__('Information could not be saved.'), array('key' => 'warning'));
                }
            }
        }

        $welder = $this->Welder->find('first', $options);

        $SettingsArray = array();

        if (!empty($arrayData['settings']->Welder)) {
            foreach ($arrayData['settings']->Welder->children() as $_key => $_settings) {
                $this->request->data[trim($_settings->model)][trim($_settings->key)] = null;
            }
        }
        $this->Data->SessionFormload('WelderMonitoring');
        $this->request->data = $this->Data->GeneralDropdownData(array('WelderMonitoring'), $arrayData, $this->request->data);
        $this->request->data['WelderMonitoring']['file'] = 1;
        $this->request->data['WelderMonitoring']['extern_intern'] = 0;
        $this->request->data['WelderMonitoring']['active'] = 1;
        $this->request->data['WelderMonitoring']['status'] = 0;
        //pr($arrayData);
        $this->set('arrayData', $arrayData);
        $this->set('welder', $welder);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['settings']);
    }

    public function monitorings()
    {

        //if($this->request->projectvars['VarsArray'][15] > 0){
        //	$weldingmethod_id = $this->request->projectvars['VarsArray'][15];
        //	}
        //	else {
        //throw new NotFoundException(__('Invalid value 1'));
        //	}
        if ($this->request->projectvars['VarsArray'][15] > 0) {
            $welder_id = $this->request->projectvars['VarsArray'][15];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $arrayData = $this->Xml->DatafromXml('Welder', 'file', null);
        $xmlcert = $this->Xml->DatafromXml('WelderMonitoring', 'file', null);

        $optionswelder = array(
                        'conditions' => array(
                                            'Welder.id' => $welder_id,
                                            'Welder.deleted' => 0,
                                            'Welder.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );

        $model = array('top' => 'Welder','main' => 'WelderMonitoring','sub' => 'WelderMonitoringData');
        $this->Welder->recursive = 0;
        $welder = $this->Welder->find('first', $optionswelder);

        $testingcomp = $welder ['Testingcomp'];


        $this->request->data = $welder;

        $this->request->data = $this->Data->GeneralDropdownData(array('Welder'), $arrayData, $this->request->data);

        //$weldingmethodes = $device['DeviceWeldingmethod'];

        $options = array(
                        //'order' => array('WelderMonitoring.certificat ASC'),
                        'conditions' => array(
                                            'WelderMonitoring.welder_id' => $welder_id,
                                            'WelderMonitoring.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                            'WelderMonitoring.deleted' => 0
                                        )
                        );
        $this->Welder->WelderMonitoring->recursive = 2;
        $welder = $this->Welder->WelderMonitoring->find('all', $options);

        //$this->Device->DeviceWeldingmethod->recursive = -1;
        $this->Welder->WelderMonitoring->WelderMonitoringData->recursive = -1;
        //$weldingmethod = $this->Device->DeviceWeldingmethod->find('first',array('conditions' => array('DeviceWeldingmethod.id' => $weldingmethod_id)));

        $options = array(
                        'order' => array('WelderMonitoringData.welder_id ASC'),
                        'conditions' => array(
                                            'WelderMonitoringData.welder_id' => $welder_id,
                                            'WelderMonitoringData.deleted' => 0,
                                            'WelderMonitoringData.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );
        $option['order'] = 'WelderMonitoringData.id ASC';
        $option['conditions']['WelderMonitoringData.testingcomp_id'] = $this->Autorisierung->ConditionsTestinccomps();

        //$weldingmethods = $this->Device->find('first',array('conditions'=>array('Device.id' => $device_id,'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps())));
        if (count($welder) > 0) {
            foreach ($welder as $_key => $_welder) {

                $option['conditions']['WelderMonitoringData.welder_monitoring_id'] = $_welder['WelderMonitoring']['id'];
                //				$option['conditions']['DeviceWelderCertificateData.active'] = 1;
                $option['conditions']['WelderMonitoringData.deleted'] = 0;
                $option['order'] = array('WelderMonitoringData.id DESC');
                $WelderMonitoringData = $this->Welder->WelderMonitoring->WelderMonitoringData->find('first', $option);
                //$welder[$_key] = array_merge($welder[$_key],$weldingmethod);
                $welder[$_key]['WelderMonitoringData'] = array_merge($welder[$_key]['WelderMonitoringData'], $WelderMonitoringData['WelderMonitoringData']);

                //$welder[$_key]['Weldingmethods'] = $weldingmethods['DeviceWeldingmethod'];
                $welder[$_key] = $this->Qualification->DeviceCertification($welder[$_key], $model); //->WelderMonitoring wird die Funktion heissen.!
            }
        }
        if (empty($welder)) {
            $welder  = $this->Welder->find('first', $optionswelder);
        }
        $SettingsArray = array();
        //		$SettingsArray['devicelink'] = array('discription' => __('Back to start',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        //		$SettingsArray['backlink'] = array('discription' => __('Back to device',true) . ' ' . $device[0]['Device']['name'], 'controller' => 'devices','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
        //		$SettingsArray['addlink'] = array('discription' => __('Add monitoring',true), 'controller' => 'devices','action' => 'addmonitoring', 'terms' => $this->request->projectvars['VarsArray']);
        if (Configure::read('CertifcateManager') == true) {
            $SettingsArray['welderlink'] = array('discription' => __('Welders administration', true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DeviceManager') == true) {
            //			$SettingsArray['devicelink'] = array('discription' => __('Devices administration',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        }


        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] =   __('Welding Companies', true);
        $breads[1]['controller'] = 'welders';
        $breads[1]['action'] = 'indexcomp';
        $breads[1]['pass'] = null;
        $breads[2]['discription'] = $testingcomp['name'];
        $breads[2]['controller'] = 'welders';
        $breads[2]['action'] = 'indexpart';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = __('Welders', true);
        $breads[3]['controller'] = 'welders';
        $breads[3]['action'] = 'index';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[4]['discription'] = $welder[0]['Welder']['first_name'] . ' ' . $welder[0]['Welder']['name'];
        $breads[4]['controller'] = 'welders';
        $breads[4]['action'] = 'overview';
        $breads[4]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);

        $this->set('arrayData', $arrayData);
        $this->set('xmlcert', $xmlcert['settings']);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('welder', $welder);
        //$this->set('weldingmethod', $weldingmethod);
    }

    public function replacemonitoring()
    {
        $this->layout = 'modal';

        //$weldingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
        $welder_certifificate_id = $this->Sicherheit->Numeric($this->request->params['pass'][16]);
        $welder_monitoring_data_id = $this->Sicherheit->Numeric($this->request->params['pass'][17]);

        $this->request->projectvars['VarsArray'][16] = $welder_certifificate_id;
        $this->request->projectvars['VarsArray'][17] = $welder_monitoring_data_id;

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid id 1'));
        }

        if (!$this->Welder->WelderMonitoring->exists($welder_certifificate_id)) {
            throw new NotFoundException(__('Invalid id 2'));
        }

        if (!$this->Welder->WelderMonitoring->WelderMonitoringData->exists($welder_monitoring_data_id)) {
            throw new NotFoundException(__('Invalid id 3'));
        }

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('WelderMonitoringRefresh', 'file', null);

        $certificate_data_option = array(
                                    'order' => array('WelderMonitoringData.id DESC'),
                                    'conditions' => array(
                                        'WelderMonitoringData.id' => $welder_monitoring_data_id,
                                        'WelderMonitoringData.deleted' => 0,
                                        )
                                    );

        //		$this->Welder->WelderMonitoring->WelderMonitoringData->recursive = -1;
        $certificate_data = $this->Welder->WelderMonitoring->WelderMonitoringData->find('first', $certificate_data_option);

        if (isset($this->request->data['WelderMonitoringData']) && ($this->request->is('post') || $this->request->is('put'))) {

            // Die Daten aus dem Formular kommen in unterschiedlichen Arrays und werden vereiningt
            $replace_data = $this->request->data['WelderMonitoringData'];

            // Der alte Datensatz aus der Datenbank wird mit den neuen Werten aus dem Formular aktualisierst
            foreach ($certificate_data['WelderMonitoringData'] as $_key => $_certificate_data) {
                if (isset($replace_data[$_key])) {
                    $certificate_data['WelderMonitoringData'][$_key] = $replace_data[$_key];
                }
            }

            // Die ID wird entfernt, und noch ein paar Werte aktualisiert
            unset($certificate_data['WelderMonitoringData']['id']);
            $certificate_data['WelderMonitoringData']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            $certificate_data['WelderMonitoringData']['user_id'] = $this->Auth->user('id');

            // Die veralteten DatensÃƒÂ¤tze werden auf inaktiv gestellt
            $this->Welder->WelderMonitoring->WelderMonitoringData->updateAll(
                array(
                        'WelderMonitoringData.active' => 0,
                        ),
                array(
                        'WelderMonitoringData.welder_monitoring_id' => $certificate_data['WelderMonitoring']['id'],
                        'WelderMonitoringData.deleted' => 0,
                        )
            );

            // neuer Datensatz und speichern
            $this->Welder->WelderMonitoring->WelderMonitoringData->create();

            if ($this->Welder->WelderMonitoring->WelderMonitoringData->save($certificate_data)) {
                $certificate_data_option = array(
                                    'order' => array('WelderMonitoringData.id DESC'),
                                    'conditions' => array(
                                        'WelderMonitoringData.id' => $this->Welder->WelderMonitoring->WelderMonitoringData->getLastInsertId(),
                                        'WelderMonitoringData.deleted' => 0,
                                        )
                                    );

                $certificate_data = $this->Welder->WelderMonitoring->WelderMonitoringData->find('first', $certificate_data_option);

                $this->Session->setFlash(__('The monitoring infos has been saved.'));

                $this->request->params['pass']['18'] = $this->Welder->WelderMonitoring->WelderMonitoringData->getLastInsertId();

                $this->request->params['controller'] = 'monitoring';

                $FormName['controller'] = 'welders';
                $FormName['action'] = 'monitorings';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('settings', array());
                $this->set('certificate_data_old_infos', $certificate_data);
                $this->set('FormName', $FormName);

                return;
            } else {
                $this->Session->setFlash(__('The monitoring infos could not be saved. Please, try again.'));
            }
        }

        $this->request->data = $certificate_data;

        $this->request->data['WelderMonitoringData']['certified_date'] = null;
        $this->request->data['WelderMonitoringData']['status'] = 0;

        $model = array('top' => 'Welder','main' => 'WelderMonitoring','sub' => 'WelderMonitoringData');
        $this->Qualification->SingleCertificateSummary($certificate_data, $model);

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['settings']);
    }

    public function editmonitoring()
    {
        $this->layout = 'modal';
        $welder_id =$this->request->projectvars['VarsArray'][15];
        $welder_monitoring_id = $this->request->projectvars['VarsArray'][16];
        $welder_monitoring_data_id = $this->request->projectvars['VarsArray'][17];



        // ÃƒÅ“berprÃƒÂ¼fung ob GerÃƒÂ¤t, Benutzer, Firma zusammenpassen

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        if (!$this->Welder->WelderMonitoring->WelderMonitoringData->exists($welder_monitoring_data_id)) {
            throw new NotFoundException(__('Invalid id'));
        }



        if(isset($this->request->data['WelderMonitoring'])) {

          if(isset($this->request->data['WelderMonitoring']['expiration_date'])) {
            $this->request->data['WelderMonitoringData']['expiration_date'] = $this->request->data['WelderMonitoring']['expiration_date'];
          }
        }

        if (isset($this->request->data['WelderMonitoringData']) && ($this->request->is('post') || $this->request->is('put'))) {
          $arrayData['settings'] = $this->Xml->CollectXMLFromFile('WelderMonitoring');
          $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('WelderMonitoring'));
          $this->request->data['WelderMonitoringData'] = $this->request->data['WelderMonitoring'];
            $this->request->data['WelderMonitoring'] = $this->request->data['WelderMonitoringData'];
            $this->request->data['WelderMonitoring']['id'] = $welder_monitoring_id;
            $this->request->data['WelderMonitoring']['user_id'] = $this->Auth->user('id');

            $this->request->data['WelderMonitoringData']['id'] = $welder_monitoring_data_id;
            $this->request->data['WelderMonitoringData']['user_id'] = $this->Auth->user('id');


            //       $this->request->data = $this->Data->GeneralDropdownData(array('WelderMonitoringData'), $arrayDatax, $this->request->data);

            unset($this->request->data['Order']);
            //unset($this->request->data['WelderMonitoring']);

            if ($this->Welder->WelderMonitoring->WelderMonitoringData->save($this->request->data['WelderMonitoring'])) {
                $this->Welder->WelderMonitoring->save($this->request->data['WelderMonitoring']);
                $this->Flash->success(__('The monitoring infos has been saved'), array('key' => 'success'));

                $this->Autorisierung->Logger($welder_monitoring_data_id, $this->request->data['WelderMonitoringData']);
            } else {
                $this->Flash->warning(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'warning'));

            }

            $FormName['controller'] = 'welders';
            $FormName['action'] = 'monitorings';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $certificate_data_option = array(
                                    'order' => array('WelderMonitoringData.id DESC'),
                                    'conditions' => array(
                                        'WelderMonitoringData.id' => $welder_monitoring_data_id,
                                        'WelderMonitoringData.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Welder->WelderMonitoring->WelderMonitoringData->find('first', $certificate_data_option);

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;
        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('WelderMonitoring');


        $model = array('top' => 'Welder','main' => 'WelderMonitoring','sub' => 'WelderMonitoringData');
        $this->Qualification->SingleCertificateSummary($certificate_data, $model);

        $SettingsArray = array();

        //		$SettingsArray['backlink'] = array('discription' => __('Back',true) . ' ' . $certificate_data['Welder']['name'], 'controller' => 'welders','action' => 'monitoring', 'terms' => $this->request->projectvars['VarsArray']);
        $this->request->data = $this->Data->GeneralDropdownData(array('WelderMonitoring'), $arrayData, $this->request->data);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['settings']);

        if (isset($certificateinfo) && $certificateinfo == 1) {
            //			$this->set('certificate_data', $certificate_data);
//			$this->render('certificateinfo','blank');
        }
    }
    public function delmonitoring()
    {
        $this->layout = 'modal';

        $welder_id =$this->request->projectvars['VarsArray'][15];
        $welder_monitoring_id = $this->request->projectvars['VarsArray'][16];
        $welder_monitoring_data_id = $this->request->projectvars['VarsArray'][17];

        if (isset($this->request->data['WelderManagerAutocomplete'])) {
            unset($this->request->data['WelderManagerAutocomplete']);
        }

        // ÃƒÅ“berprÃƒÂ¼fung ob GerÃƒÂ¤t, Benutzer, Firma zusammenpassen

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        if (!$this->Welder->WelderMonitoring->WelderMonitoringData->exists($welder_monitoring_data_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        $certificate_data_option = array(
                                    'order' => array('WelderMonitoringData.id DESC'),
                                    'conditions' => array(
                                        'WelderMonitoringData.id' => $welder_monitoring_data_id,
                                        'WelderMonitoringData.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Welder->WelderMonitoring->WelderMonitoringData->find('first', $certificate_data_option);


        if (isset($this->request->data['WelderMonitoring']) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($welder_monitoring_id != $this->request->data['WelderMonitoring']['id']) {
                $this->Flash->warning(__('The certificate infos could not be deleted.'), array('key' => 'warning'));

                return;
            }

            unset($this->request->data['Order']);
            $this->request->data['WelderMonitoring']['id'] = $welder_monitoring_id;
            $this->request->data['WelderMonitoring']['user_id'] = $this->Auth->user('id');
            $this->request->data['WelderMonitoring']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            //$this->request->data['WelderMonitoring']['recertification_in_year'] = $certificate_data['WelderMonitoring']['recertification_in_year'];
            //$this->request->data['WelderMonitoring']['horizon'] = $certificate_data['WelderMonitoring']['horizon'];
            $this->request->data['WelderMonitoring']['deleted'] = 1;

            if ($this->Welder->WelderMonitoring->save($this->request->data)) {
                $this->Welder->WelderMonitoring->WelderMonitoringData->updateAll(
                    array(
                        'WelderMonitoringData.deleted' => 1,
                        ),
                    array(
                        'WelderMonitoringData.Welder_monitoring_id' => $welder_monitoring_id,
                        )
                );

                $this->Flash->success(__('The certificate has been deleted.'), array('key' => 'success'));

                $this->Autorisierung->Logger($welder_monitoring_data_id, $this->request->data['WelderMonitoring']);



                unset($this->request->projectvars['VarsArray']['16']);
                unset($this->request->projectvars['VarsArray']['17']);
                /*
                                $this->request->params['action'] = 'monitorings';

                                $request_url = explode('/',$this->request->url);
                                $request_here = explode('/',$this->request->here);

                                $request_url[1] = 'monitoring';
                                $request_here[3] = 'monitoring';

                                $this->request->url = implode('/',$request_url);
                                $this->request->here = implode('/',$request_here);
                */
                $FormName['controller'] = 'welders';
                $FormName['action'] = 'overview';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('saveOK', 1);
                $this->set('FormName', $FormName);
                return;
            } else {
                $this->Flash->warning(__('The certificate infos could not be deleted. Please, try again.'), array('key' => 'warning'));
            }
        }

        $certificate_data_option = array(     'order' => array('WelderMonitoringData.id DESC'),
                                    'conditions' => array(
                                        'WelderMonitoringData.id' => $welder_monitoring_data_id,
                                        'WelderMonitoringData.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Welder->WelderMonitoring->WelderMonitoringData->find('first', $certificate_data_option);

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml('WelderMonitoringEdit', 'file', null);

        $model = array('top' => 'Welder','main' => 'WelderMonitoring','sub' => 'WelderMonitoringData');
        $this->Qualification->SingleCertificateSummary($certificate_data, $model);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);

        $this->Flash->warning(__('Are you sure you want to delete this certificate?'), array('key' => 'warning'));
    }




    public function pdfinv()
    {
        $this->layout = 'pdf';
        if ($this->Session->check('searchwelder.welders')) {
            $sessionwelder = $this->Session->read('searchwelder.welders');
        }

        if (isset($sessionwelder) && !empty($sessionwelder)) {
            $welders = $this->Session->read('searchwelder.welders');
        } else {
            $this->Welder->recursive = 1;
            $welders = $this->Welder->find('all', array(
                //'order' => $OrderBy,
                'conditions' => array(
                        'Welder.deleted' => 0,
                                                'WelderActive.active' => $this->request->projectvars['VarsArray'][17],
                                               'Welder.testingcomp_id' => $this->request->projectvars['VarsArray'][14],
                    )

            ));
        }

        $arrayData = null;
        $arrayData = $this->Xml->DatafromXml('Welder_Inv', 'file', null);
        if (!is_array($arrayData)) {
            return;
        }

        $this->set('xml_welderinv', $arrayData["settings"]);
        if (Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png') {
            $this->request->data['Testingcomp'] = $this->Auth->user('Testingcomp');
            $this->request->data['Testingcomp']['logo'] = Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png';
        }




        $Welderdata = array();
        $model = array('top' => 'Welder','main' => 'WelderMonitoring','sub' => 'WelderMonitoringData');
        foreach ($welders as $key => $value) {
            $welderid = $value ['Welder'] ['id'];
            $Welderdata [$key] = $value ['Welder'];


            $this->loadmodel('ReportVtstGenerally');
            $this->loadmodel('ReportVtstEvaluation');
            $this->loadmodel('Reportnumber');
            $reportids = $this->ReportVtstGenerally->find('all', array('fields'=>array('reportnumber_id','date_of_test'),'conditions'=>array('ReportVtstGenerally.welder'=> $value['Welder']['identification'])));
            $i = 0;
            $weldertests = array() ;
            foreach ($reportids as $rkey => $rvalue) {
                $Report = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.delete'=> 0,'Reportnumber.id'=>$rvalue['ReportVtstGenerally']['reportnumber_id'])));

                if ($Report['Reportnumber']['delete'] ==0) {
                    $evaluations =  $this->ReportVtstEvaluation->find('all', array('fields'=>array('material','dimension','result_vt','result'),'conditions'=> array('reportnumber_id'=>$rvalue['ReportVtstGenerally']['reportnumber_id'],'deleted'=>0)));
                    // pr($evaluations);
                    $datetest = $rvalue['ReportVtstGenerally']['date_of_test']."\n";
                    $eval= '';
                    $resarray = array('-','e','ne');

                    foreach ($evaluations as $evkey => $evalue) {
                        !empty($evalue['ReportVtstEvaluation']['result_vt']) ? $resvt = $resarray[$evalue['ReportVtstEvaluation']['result_vt']]: $resvt = $resarray[0];
                        !empty($evalue['ReportVtstEvaluation']['result']) ? $resrt = $resarray[$evalue['ReportVtstEvaluation']['result']]: $resrt = $resarray[0];
                        $eval.= '-'.$evalue['ReportVtstEvaluation']['material'].' '. $evalue['ReportVtstEvaluation']['dimension'].' '.$resvt.' '.$resrt."\n";
                    }
                    $weldertests .= $datetest.$eval;
                }
            }

            $Welderdata [$key] ['weldertest'] = $weldertests;

            $monitorings = $this->Qualification->WelderMonitoringSummary($value, array('top'=>'Welder','main'=>'WelderMonitoring','sub'=>'WelderMonitoringData'));
            $monitorings = isset($monitorings ['summary'] ['monitoring']) ? $monitorings ['summary'] ['monitoring'] : '';
            $uberwachungen = '';

            if (!empty($monitorings)) {
                foreach ($monitorings as $key2 => $_monitorings) {
                    foreach ($_monitorings as $key3 => $value2) {
                        if (!isset($value2[$welderid]['info']['certificat'])) {
                            continue;
                        }
                        $uberwachungen .= $value2 [$welderid] ['info']['certificat'] . ', '.$value2 [$welderid] ['info'] ['next_certification_date']."\n";
                    }
                    $Welderdata [$key]  ['monitorings'] = $uberwachungen;
                }
            } else {
                $Welderdata [$key]  ['monitorings'] ='' ;
            }
        }

        $welders = $Welderdata;

        $this->set('Welders', $welders);
    }

    public function files()
    {
        $this->layout = 'modal';

        $models['top'] = 'Welder';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Welderfile';
        $certificatefile_id = 0;
        $this->loadModel($models['file']);

        //$weldingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        if (!$this->Welder->exists($welder_id)) {
            throw new NotFoundException(__('Invalid welder'));
        }


        $path = Configure::read('welder_folder') . $welder_id. DS . 'documents' . DS;
        $this->__files($path, 0, $welder_id, $certificatefile_id, $models);

        $SettingsArray = array();
        //		$SettingsArray['devicelink'] = array('discription' => __('Back to overview',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        //		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devices','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function getfiles()
    {
        //$weldingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $models = array('main' => 'Welderfile','sub' => null);
        $path = Configure::read('welder_folder') . $welder_id. DS . 'documents' . DS;

        $this->loadModel($models['main']);
        $this->__getfiles($path, $models);
    }

    public function getweldercompfiles()
    {
        //$weldingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        $weldingcomp_id = $this->Sicherheit->Numeric($this->request->params['pass']['14']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        $models = array('main' => 'Weldingcompfile','sub' => null);
        $path = Configure::read('weldingcomp_folder') . $weldingcomp_id. DS . 'documents' . DS;

        $this->loadModel($models['main']);
        $options = array(
                                    'conditions' => array(
                                        $models['main'].'.testingcomp_id' => $weldingcomp_id,
                                        $models['main'].'.id' => $file_id
                                        )
                                    );

        $data = $this->{$models['main']}->find('first', $options);

        if (count($data) == 0) {
            $this->Session->setFlash(__('The file does not exist.'));
            $this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')), $this->request->projectvars['VarsArray']));
        } elseif (count($data) == 1) {
            if (file_exists($path .$data[$models['main']]['name'])) {
                $this->set('path', $path .$data[$models['main']]['name']);
            } else {
                $this->Session->setFlash(__('The file cannot be found.'));
                $this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')), $this->request->projectvars['VarsArray']));
            }
        }
    }

    public function monitoringfile()
    {
        $this->layout = 'modal';

        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'editmonitoring', 'terms' => $this->request->params['pass']);

        $this->set('SettingsArray', $SettingsArray);

        if (isset($_FILES) && count($_FILES) > 0) {
            $FormName['controller'] = 'welders';
            $FormName['action'] = 'monitorings';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $models = array('top'=>'Welder','main' => 'WelderMonitoring','sub' => 'WelderMonitoringData');
        $path = Configure::read('welder_monitoring_folder') . $welder_id. DS . $certificate_id . DS . $certificate_data_id . DS;

        $this->_monitoringfileupload($path, $models);
    }




    protected function _monitoringfileupload($path, $models)
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $_data_option = array(
                    'conditions' => array(
                        $models['sub'] . '.id' => $certificate_data_id,
                        $models['sub'] . '.welder_monitoring_id' => $certificate_id,
                        $models['sub'] . '.deleted' => 0,
//						$models['sub'] . '.active' => 1
                        )
                    );

        $_data = $this->Welder->{$models['main']}->{$models['sub']}->find('first', $_data_option);

        if ($_data[$models['sub']]['certified_file'] != '' && file_exists($path . $_data[$models['sub']]['certified_file'])) {
            $this->set('hint', __('Do you want to overwrite the existing file?', true));
        }

        $this->set('data', $_data);
        $this->set('models', $models);

        $Extension = 'pdf';

        if (isset($_FILES) && count($_FILES) > 0) {

            // Infos zur Datei
            $fileinfo = pathinfo($_FILES['file']['name']);
            $filename_new = $welder_id.'_'.$certificate_id.'_'.$certificate_data_id.'_'.time().'.'.$fileinfo['extension'];

            if ($Extension == strtolower($fileinfo['extension'])) {
                if ($_FILES['file']['error'] != 0) {
                    $this->Session->write('FileUploadErrors', $_FILES['file']['error']);
                }

                // Vorhandene Dateien werden gelöscht
                if ($_FILES['file']['error'] == 0) {
                    $folder = new Folder($path);
                    $folder->delete();

                    if (!file_exists($path)) {
                        $dir = new Folder($path, true, 0755);
                    }
                }

                $this->Session->write('FileUploadErrors', $_FILES['data']['error']['Welder']['file']);
                move_uploaded_file($_FILES['file']["tmp_name"], $path. DS .$filename_new);

                if ($_FILES['file']['error'] == 0) {
                    $dataFiles = array(
                        'id' => $certificate_data_id,
                        'certified_file' => $filename_new,
                        'user_id' => $this->Auth->user('id')
                    );

                    $this->Welder->{$models['main']}->{$models['sub']}->save($dataFiles);
                }
            }
        }
    }


    public function getmonitoringfile()
    {
        $welder_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $models = array('main' => 'WelderMonitoring','sub' => 'WelderMonitoringData');
        $path = Configure::read('welder_monitoring_folder') . $welder_id. DS . $certificate_id . DS . $certificate_data_id . DS;
        $this->getfile($path, $models);
    }

    public function delwelderfiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Welder';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Welderfile';
        if (isset($this->request->data['WelderManagerAutocomplete'])) {
            unset($this->request->data['WelderManagerAutocomplete']);
        }

        $this->__delfiles($models, 'files');

        $lastModal = array(
                        'controller' => 'welder',
                        'action' => 'overview',
                        'terms' => $this->request->projectvars['VarsArray']
                        );



        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'files', 'terms' => $lastModal['terms']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function weldingcompinfo()
    {
        $this->layout = 'modal';
        $testingcomp_id = $this->request->projectvars['VarsArray'][14];
        $arrayData = null;
        $arrayData = $this->Xml->DatafromXml('Testingcomp_info', 'file', null);

        $this->Welder->Testingcomp->recursive = 1;
        $comp = $this->Welder->Testingcomp->find('first', array('conditions'=>array('Testingcomp.id' =>$testingcomp_id)));
        $this->loadmodel('Weldingcompfile');
        $file = array();
        $file = $this->Weldingcompfile->find('first', array('conditions'=>array('weldingcomp_id' =>$testingcomp_id)));


        $file = $this->Qualification->WeldingcompFileValidity($file);
        $SettingsArray = array();
        $SettingsArray['editlink'] = array('discription' => __('edit', true), 'controller' => 'testingcomps','action' => 'edit', 'terms' => array($testingcomp_id));

        $this->set('SettingsArray', $SettingsArray);
        $this->request->data = $comp;
        $this->set('weldingcompfile', $file['Weldingcompfile']);
        $this->set('comp', $comp);
        $this->set('arrayData', $arrayData);
    }

    public function filesweldingcomp()
    {
        $this->layout = 'modal';

        $models['top'] = 'Testingcomp';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Weldingcompfile';
        $certificatefile_id = 0;
        $this->loadModel($models['file']);

        //$weldingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $welder_id = 0;

        $testingcomp_id = $this->request->projectvars['VarsArray'][14];
        $this->loadModel('Testingcomp');
        if (!$this->Testingcomp->exists($testingcomp_id)) {
            throw new NotFoundException(__('Invalid welding comp'));
        }


        $path = Configure::read('weldingcomp_folder') . $testingcomp_id. DS . 'documents' . DS;
        $this->__weldercompfiles($path, $testingcomp_id, $welder_id, 0, $models);

        $SettingsArray = array();
        //		$SettingsArray['devicelink'] = array('discription' => __('Back to overview',true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'weldingcompinfo', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function welderfilesdescription()
    {
        $this->layout = 'modal';

        $models['top'] = 'Welder';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Welderfile';

        $welder_id = $this->request->projectvars['VarsArray'][15];
        $file_id = 0;

        if (isset($this->request->projectvars['VarsArray'][17]) && $this->request->projectvars['VarsArray'][17] > 0) {
            $file_id = $this->request->projectvars['VarsArray'][17];
        }





        $this->loadModel($models['file']);





        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($this->request->data[$models['file']]['description'] == '') {
                $file_id = $this->Sicherheit->Numeric($this->request->data[$models['file']]['id']);

                $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['file'].'.id' => $file_id,
                                        $models['file'].'.testingcomp_id' => $testingcomp_id,
                                        )
                                    );

                $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);

                if ($certificate_files[$models['file']]['description'] == '') {
                    $this->request->data = $certificate_files;
                    $this->set('certificate_files', $certificate_files);
                    //$this->set('certificate_data', $certificate_data);
                    $this->set('models', $models);
                    return;
                }
            }

            if ($this->{$models['file']}->save($this->request->data)) {
                $this->Session->setFlash(__('The value has been saved'));
                $this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);


                if ($models['file'] == 'Welderfile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'files';
                    $this->request->here = implode('/', $_here);
                    $this->filesweldingcomp();
                }



                $this->render('files');
            } else {
                $this->Session->setFlash(__('The value could not be saved. Please, try again.'));
            }
        }


        if ($file_id > 0) {
            $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['file'].'.id' => $file_id,
                                        $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        )
                                    );
        }

        $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);

        $this->request->data = $certificate_files;

        $FileUploadErrors = $this->Session->read('FileUploadErrors');
        $this->Session->delete('FileUploadErrors');

        $this->set('certificate_files', $certificate_files);
        //	$this->set('certificate_data', $certificate_data);
        $this->set('models', $models);
        $this->set('message', Configure::read('FileUploadErrors.' . $FileUploadErrors));
        $this->set('uploaderror', $FileUploadErrors);

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to overview',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'weldingcompfiles', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }
    public function weldingcompfilesdescription()
    {
        $this->layout = 'modal';

        $models['top'] = 'Testingcomp';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Weldingcompfile';

        $weldingcomp_id = $this->Sicherheit->Numeric($this->request->params['pass']['14']);
        $file_id = 0;

        if (isset($this->request->params['pass']['15']) && $this->Sicherheit->Numeric($this->request->params['pass']['15']) > 0) {
            $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        }





        $this->loadModel($models['file']);





        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($this->request->data[$models['file']]['description'] == '') {
                $file_id = $this->Sicherheit->Numeric($this->request->data[$models['file']]['id']);

                $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['file'].'.id' => $file_id,
                                        $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        )
                                    );

                $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);

                if ($certificate_files[$models['file']]['description'] == '') {
                    $this->request->data = $certificate_files;
                    $this->set('certificate_files', $certificate_files);
                    //$this->set('certificate_data', $certificate_data);
                    $this->set('models', $models);
                    return;
                }
            }

            if ($this->{$models['file']}->save($this->request->data)) {
                $this->Session->setFlash(__('The value has been saved'));
                $this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);


                if ($models['file'] == 'Weldingcompfile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'filesweldingcomp';
                    $this->request->here = implode('/', $_here);
                    $this->filesweldingcomp();
                }



                $this->render('filesweldingcomp');
            } else {
                $this->Session->setFlash(__('The value could not be saved. Please, try again.'));
            }
        }


        if ($file_id > 0) {
            $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['file'].'.id' => $file_id,
                                        $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        )
                                    );
        }

        $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);

        $this->request->data = $certificate_files;

        $FileUploadErrors = $this->Session->read('FileUploadErrors');
        $this->Session->delete('FileUploadErrors');

        $this->set('certificate_files', $certificate_files);
        //	$this->set('certificate_data', $certificate_data);
        $this->set('models', $models);
        $this->set('message', Configure::read('FileUploadErrors.' . $FileUploadErrors));
        $this->set('uploaderror', $FileUploadErrors);

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to overview',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'filesweldingcomp', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function setpicture($id = null)
    {
        $this->layout = 'modal';
        $welder_id = $this->request->projectvars['VarsArray'][15];

        $savePath = Configure::read('welder_picture_folder') . $welder_id . DS;

        if(isset($this->request->data['close_reload']) && $this->request->data['close_reload'] == 1) {

          $FormName['controller'] = 'welders';
          $FormName['action'] = 'overview';
          $FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

          $this->set('FormName',$FormName);
        }

        if (isset($_FILES) && count($_FILES) > 0) {

            $attribut_disabled = false;

            if ($_FILES['file']['error'] > 0) {
                $max_upload = (int)(ini_get('upload_max_filesize'));
                $this->Flash->error(Configure::read('FileUploadErrors.' . $_FILES['file']['error']), array('key' => 'error'));
                return;
            }

            $info = getimagesize($_FILES['file']["tmp_name"]);
            $basename = $_FILES['file']['name'];

            if ($info['mime'] == 'image/jpeg') $suffix = 'jpg';
            if ($info['mime'] == 'image/png') $suffix = 'png';

            $saveFile = 'foto'.'.'.$suffix;

            // Wenn nicht vorhanden Ordner erzeugen
            if (!file_exists($savePath)) {
                $dir = new Folder($savePath, true, 0755);
            }

            move_uploaded_file($_FILES['file']["tmp_name"], $savePath. DS .$saveFile);

            $dataImage = array(
              'id' => $welder_id,
              'profile_picture' => $saveFile,
            );

            $this->Welder->save($dataImage);

        }

        $SettingsArray= array();
        $this->set('SettingsArray', $SettingsArray);

    }

    public function createweldertest()
    {
        $this->layout = 'modal';

        $ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

        $id = $this->request->projectvars['VarsArray'][15];
        $welder = $this->Welder->find('first', array('conditions' => array('Welder.id' => $id)));
        $Topproject = $this->Data->BelongsToManySelected($welder, 'Testingcomp', 'Topproject', array('TestingcompsTopprojects','topproject_id','testingcomp_id'));
        $ConditionsTopprojects = $Topproject['Topproject']['selected'];

        $WeldertestTethods = array('vtst');
        $ReportIDs = array();
        $TopprojectIDs = array();

        $this->loadmodel('Reportnumber');
        $this->loadmodel('Testingmethod');
        $this->loadmodel('Report');
        $this->loadmodel('Topproject');
        $this->loadmodel('Cascade');

        $this->Cascade->recursive = -1;
        $this->Report->recursive = -1;

        $Testingmethods = $this->Testingmethod->find('all', array('conditions' => array('Testingmethod.value' => $WeldertestTethods)));
        $Topprojects = $this->Topproject->find('all', array('conditions' => array('Topproject.id' => $ConditionsTopprojects)));

        $NaviForWelderTest = array();

        foreach ($Topprojects as $_key => $_data) {
            $Topprojects[$_key] = $this->Data->BelongsToManySelected($_data, 'Topproject', 'Report', array('ReportsTopprojects','report_id','topproject_id'));

            $NaviForWelderTest[$_data['Topproject']['id']]['Topproject'] = $_data;

            $option = array();
            $options['Cascade.topproject_id'] = $_data['Topproject']['id'];
            $options['Cascade.level'] = 0;

            $Cascade = $this->Cascade->find('first', array('conditions' => $options));

            $CascadeGetChildrenList = $this->Navigation->CascadeGetChildrenList($Cascade['Cascade']['id']);

            $CascadeLastChild = $this->Navigation->CascadeGetLastChilds($CascadeGetChildrenList);

            $NaviForWelderTest[$_data['Topproject']['id']]['Cascade'] = $CascadeLastChild;

            if (count($Topprojects[$_key]['Report']['selected']) > 0) {
                $Topprojects[$_key]['Topproject']['Report'] = $this->Report->find('all', array('conditions' => array('Report.id' => $Topprojects[$_key]['Report']['selected'])));

                foreach ($Topprojects[$_key]['Topproject']['Report'] as $__key => $__data) {
                    $_testingmethodIds = $this->Data->BelongsToManySelected($__data, 'Report', 'Testingmethod', array('TestingmethodsReports','testingmethod_id','report_id'));

                    if (count($_testingmethodIds['Testingmethod']['selected']) > 0) {
                        $_testingmethod = $this->Testingmethod->find('all', array('conditions' => array('Testingmethod.value' => $WeldertestTethods,'Testingmethod.id' => $_testingmethodIds['Testingmethod']['selected'])));

                        if (count($_testingmethod) > 0) {
                            $Topprojects[$_key]['Topproject']['Report'][$__key]['Testingmethod'] = $_testingmethod;
                        }
                    }
                }
            }

            unset($Topprojects[$_key]['Report']);

            if (!isset($Topprojects[$_key]['Topproject']['Report'])) {
                unset($Topprojects[$_key]);
            }
        }

        $WelderTestMenue = array();
        $MenueDescription = array();

        foreach ($Topprojects as $_key => $_data) {
            $MenueDescription['Topproject'][$_data['Topproject']['id']] = $_data['Topproject']['projektname'];

            foreach ($_data['Topproject']['Report'] as $__key => $__data) {
                if (!isset($__data['Testingmethod'])) {
                    unset($Topprojects[$_key]['Topproject']['Report'][$__key]);
                } else {
                    $MenueDescription['Report'][$__data['Report']['id']] = $__data['Report']['name'];
                    foreach ($NaviForWelderTest[$_data['Topproject']['id']]['Cascade'] as $___key => $___data) {
                        $MenueDescription['Cascade'][$___data['Cascade']['id']] = $___data['Cascade']['discription'];
                        $WelderTestMenue[$___data['Cascade']['topproject_id']][$___data['Cascade']['id']][$__data['Report']['id']] = $__data['Testingmethod'];
                    }
                }
            }
        }

        $this->set('welder', $welder);
        $this->set('MenueDescription', $MenueDescription);
        $this->set('WelderTestMenue', $WelderTestMenue);
        $this->set('Topprojects', $Topprojects);
        $this->set('SettingsArray', array());
    }

    public function getlasttests()
    {
        $xml = $this->Xml->DatafromXml('display_weldertest', 'file', null);
        $this->set('xml', $xml['settings']);
        $this->layout = 'modal';
        $id = $this->request->projectvars['VarsArray'][15];
        $welder = $this->Welder->find('first', array('conditions' => array('Welder.id' => $id)));
        $this->loadmodel('ReportVtstGenerally');
        $this->loadmodel('ReportVtstEvaluation');
        $this->loadmodel('Reportnumber');
        $reportids = $this->ReportVtstGenerally->find('list', array('fields'=>array('reportnumber_id','reportnumber_id'),'conditions'=>array('ReportVtstGenerally.welder'=> $welder['Welder']['identification'])));
        $i = 0;
        $this->paginate = array(
            'conditions' => array('Reportnumber.id'=>$reportids,'Reportnumber.delete' => 0 ),
            //'order' => array('Welder.name' => 'asc','Welder.first_name' => 'asc'),
            'limit' => 10
        );
        $reports = $this->paginate('Reportnumber');

        foreach ($reports as $rkey => $rvalue) {
            $ReportGenerally = $this->ReportVtstGenerally->find('first', array('conditions'=>array('ReportVtstGenerally.reportnumber_id'=> $rvalue['Reportnumber'] ['id'])));
            $reports[$rkey]['Reportnumber']['date'] = $ReportGenerally['ReportVtstGenerally']['date_of_test'];
            $evaluations = $this->ReportVtstEvaluation->find('all', array('conditions'=>array('ReportVtstEvaluation.deleted' => 0,'ReportVtstEvaluation.reportnumber_id' => $rvalue['Reportnumber']['id'])));
            $reports[$rkey]['ReportVtstEvaluation'] = $evaluations;
            $i++;
        }

        if (empty($reports)) {
            $this->Session->setFlash(__('There are no Tests for this Welder'));
        }

        $SettingsArray = array();
        $this->set('Reports', $reports);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function deleteweldingcompfile()
    {
        $this->layout = 'modal';

        $models['top'] = 'Testingcomp';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Weldingcompfile';

        $weldingcomp_id = $this->request->projectvars['VarsArray'][14];
        $file_id = 0;

        if (isset($this->request->projectvars['VarsArray'][15]) && $this->request->projectvars['VarsArray'][15] > 0) {
            $file_id = $this->request->projectvars['VarsArray'][15];
            ;
        }

        $this->loadModel($models['file']);

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($this->{$models['file']}->delete($file_id)) {
                $this->Session->setFlash(__('The value has been deleted'));
                $this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);


                if ($models['file'] == 'Weldingcompfile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'filesweldingcomp';
                    $this->request->here = implode('/', $_here);
                    $this->filesweldingcomp();
                }



                $this->render('filesweldingcomp');
            } else {
                $this->Session->setFlash(__('The value could not be deleted. Please, try again.'));
            }
        }

        $FileUploadErrors = $this->Session->read('FileUploadErrors');
        $this->Session->delete('FileUploadErrors');

        //$this->set('certificate_files', $certificate_files);
        //	$this->set('certificate_data', $certificate_data);
        $this->set('models', $models);
        $this->set('message', Configure::read('FileUploadErrors.' . $FileUploadErrors));
        $this->set('uploaderror', $FileUploadErrors);

        $SettingsArray = array();
        //		$SettingsArray['welderlink'] = array('discription' => __('Back to overview',true), 'controller' => 'welders','action' => 'index', 'terms' => null);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'welders','action' => 'filesweldingcomp', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function welderstate() {

      $this->layout = 'json';
      $id = $this->request->data['welder_id'];

      $status = array();
      $this->loadModel('WelderActive');

      $status = $this->WelderActive->find('first',array('conditions' => array('WelderActive.welder_id' => $id)));
      if(!empty($status)){

         if($status['WelderActive']['active'] == 1)  {
            $status['WelderActive']['active'] = 0;
         }else{
          if($status['WelderActive']['active'] == 0) {
            $status['WelderActive']['active'] = 1;
          }
         }
        $this->WelderActive->save($status);
      }else{

        $status['WelderActive']['welder_id'] = $id;
        $status['WelderActive']['active'] = 1;

        $this->WelderActive->create();
        $this->WelderActive->save($status);
      }


      $states = array(0=> __('passiv'),1=>__('aktiv'));

      $emailto = Configure::read('EmailWelderInfos');
      $emailto = explode(',', $emailto);
      $welder = $this->Welder->find('first',array('conditions'=> array('Welder.id'=>$id)));
      foreach ($emailto as $ekey => $evalue) {
        $isValid = Validation::email($evalue);

        if ($isValid === true) {
          App::uses('CakeEmail', 'Network/Email');

          $subject = 'Information Schweißer'.' '. $welder['Welder']['identification'].' '. $welder['Welder']['first_name'].' '. $welder['Welder']['name'];
          $content = 'Der Status des Schweißers wurde auf '. $states[$status['WelderActive']['active']].' gesetzt.';
          $email = new CakeEmail('default');
          $email->domain(FULL_BASE_URL);
          $email->from(array('reminder@mbq-gmbh.info' => 'Schweißerdatenbank'));
          $email->sender(array('reminder@mbq-gmbh.info' => 'Schweißerdatenbank'));
          $email->to($evalue);
          $email->subject($subject);
          $email->template('welderstate', 'default');
          $email->emailFormat('text');
          $email->viewVars(
            array(
              'state' => $content,
            )
          );
          $email->send();
        }
      }
      $this->set('test',json_encode($status));
      $this->render();

    }
  }
