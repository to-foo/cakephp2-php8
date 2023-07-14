<?php
App::uses('AppController', 'Controller');
// App::import('Vendor', 'OAuth/OAuthClient');
/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController
{
    public $components = array(
//		'Security',
        'Auth',
        'Acl',
        'Autorisierung',
        'Cookie',
        'Navigation',
        'Lang',
        'Sicherheit',
        'Xml',
        'Pdf',
        'Search',
        'Csv',
        'Data'
        );

    public $helpers = array('Lang','Navigation','JqueryScripte');
    public $layout = 'ajax';

    public function beforeFilter()
    {
        App::import('Vendor', 'Authorize');
        App::uses('Sanitize', 'Utility');

        $this->Autorisierung->Protect();
        if ($this->request->action != 'login') {
            $this->Lang->Choice();
            $this->Lang->Change();
        }

        $noAjaxIs = 0;
        $noAjax = array('login','quicksearch');

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

        $this->Navigation->ReportVars();

        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('menues', $this->Navigation->Menue());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));

        // Werte für die Schnellsuche
        $ControllerQuickSearch =
        array(
                'Model' => 'User',
                'Field' => 'name',
                'description' => __('User name', true),
                'minLength' => 2,
                'targetcontroller' => 'users',
                'target_id' => 'id',
                'targetation' => 'edit',
                'Quicksearch' => 'QuickUsersearch',
                'QuicksearchForm' => 'QuickUsersearchForm',
                'QuicksearchSearchingAutocomplet' => 'QuickUsersearchSearchingAutocomplet',
        );

        $this->set('ControllerQuickSearch', $ControllerQuickSearch);

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }
    }

    public function afterFilter()
    {
        $this->Navigation->lastURL();
    }

    public function forceSSL()
    {
        //       return $this->redirect('https://' . env('SERVER_NAME') . $this->here);
//		return $this->redirect($this->Auth->logout());
    }

    private function createClient()
    {
        //		return new OAuthClient('YOUR_CONSUMER_KEY', 'YOUR_CONSUMER_SECRET');
        return new OAuthClient('u5e5di3-xJa3U41v5NX-dw', '');
    }

    public function base64url_encode($data)
    {
//    	return base64_encode($data);
        return strtr(base64_encode($data), '+/=', '-_,');
    }

    public function base64url_decode($data)
    {
        //	    return base64_decode($data);
        return base64_decode(strtr($data, '-_,', '+/='));
    }

    public function oauth()
    {
        $this->layout = 'blank';
        $this->render('oauth');

        if (isset($this->request->data['Login'])) {
            $this->autoRender = false;

            // Produktivsystem
            $SecretKey = 'B366PAO1V3622WQTX3WHJ1Q';
            // Testsystem
            //			$SecretKey = '5E86PAO1V3622WQTX3WKPSD5';
            //			$SecretKey = 'u5e5di3-xJa3U41v5NX-dw';

            parse_str($this->request->data['Login']['string'][1], $output);

            unset($this->request->data['Login']);

            $AccessTokenParts = explode('.', $output['access_token']);
            $IdTokenParts = explode('.', $output['id_token']);

            $JWTToken['id_token']['encode']['header'] = $IdTokenParts[0];
            $JWTToken['id_token']['encode']['body'] = $IdTokenParts[1];
            $JWTToken['id_token']['encode']['signature'] = $IdTokenParts[2];

            $JWTToken['id_token']['decode']['header'] = $this->base64url_decode($JWTToken['id_token']['encode']['header']);
            $JWTToken['id_token']['decode']['body'] = $this->base64url_decode($JWTToken['id_token']['encode']['body']);
            $JWTToken['id_token']['decode']['signature'] = $this->base64url_decode($JWTToken['id_token']['encode']['signature']);

            $JWTToken['id_token']['json']['header'] = json_decode($JWTToken['id_token']['decode']['header']);
            $JWTToken['id_token']['json']['body'] = json_decode($JWTToken['id_token']['decode']['body']);
            $JWTToken['id_token']['json']['signature'] = $JWTToken['id_token']['decode']['signature'];

            $JWTToken['access_token']['encode']['header'] = $AccessTokenParts[0];
            $JWTToken['access_token']['encode']['body'] = $AccessTokenParts[1];
            $JWTToken['access_token']['encode']['signature'] = $AccessTokenParts[2];

            $JWTToken['access_token']['decode']['header'] = $this->base64url_decode($JWTToken['access_token']['encode']['header']);
            $JWTToken['access_token']['decode']['body'] = $this->base64url_decode($JWTToken['access_token']['encode']['body']);
            $JWTToken['access_token']['decode']['signature'] = $this->base64url_decode($JWTToken['access_token']['encode']['signature']);

            $JWTToken['access_token']['json']['header'] = json_decode($JWTToken['access_token']['decode']['header']);
            $JWTToken['access_token']['json']['body'] = json_decode($JWTToken['access_token']['decode']['body']);
            $JWTToken['access_token']['json']['signature'] = $JWTToken['access_token']['decode']['signature'];

            /*
            print '<pre>';
            print_r($JWTToken);
            print '</pre>';
            */
            $AccessToken = $JWTToken['access_token']['encode']['signature'];
            $AccessTokenSecred = rtrim($this->base64url_encode(hash_hmac('sha256', ($JWTToken['access_token']['encode']['header'].'.'.$JWTToken['access_token']['encode']['body']), $SecretKey, true)), ',');
            $IdToken = $JWTToken['id_token']['encode']['signature'];
            $IDTokenSecred = rtrim($this->base64url_encode(hash_hmac('sha256', ($JWTToken['id_token']['encode']['header'].'.'.$JWTToken['id_token']['encode']['body']), $SecretKey, true)), ',');

            $Error['Count'] = 0;

            if ($AccessToken !== $AccessTokenSecred || $IdToken !== $IDTokenSecred) {
                $Error['Count']++;
                $Error['Message'][$Error['Count']] = __('Login stopped, certification failed', true);
            }
            if (trim($JWTToken['id_token']['json']['body']->iss) != 'trm.digitale-baustelle.com') {
                $Error['Count']++;
                $Error['Message'][$Error['Count']] = __('Login stopped, wrong location', true);
            }
            if (time() >= ($JWTToken['id_token']['json']['body']->exp)) {
                $Error['Count']++;
                $Error['Message'][$Error['Count']] = __('Login stopped, timeout', true);
            }
            if ($Error['Count'] > 0) {
                $this->set('Error', $Error);
                $this->render('oauth');
                return;
            } elseif ($Error['Count'] == 0) {
                $this->User->unbindModel(array('hasMany' => array('UserData')));
                $Testingcomp = $this->User->Testingcomp->find('first', array('conditions' => array('Testingcomp.name' => trim($JWTToken['id_token']['json']['body']->com))));

                if (count($Testingcomp) == 0) {
                    $Error['Count']++;
                    $Error['Message'][$Error['Count']] = __('Login stopped, Company name not found in database', true);
                }

                if ($Error['Count'] > 0) {
                    $this->set('Error', $Error);
                    $this->render('oauth');
                    return;
                }

                $User = $this->User->find('first', array('conditions' => array('User.username' => trim($JWTToken['id_token']['json']['body']->sub))));

                if (count($User) > 0) {
                    $password = Security::hash($SecretKey, null, $User['User']['username']);

                    $this->request->data['User']['username'] = $User['User']['username'];
                    $this->request->data['User']['password'] = $password ;
                    $this->request->data['Language']['beschreibung'] = 2;

                    $Error['Count']++;
                    $Error['Message'][$Error['Count']] = __('Login', true);
                    $this->set('Error', $Error);
                    $this->render('oauth');
                    return;
                } elseif (count($User == 0)) {
                    $RollArray = array(
                                    'LocalAdmin' => 1,
                                    'ex_ad' => 1,
                                    'ex_ex' => 2,
                                    'ex_ei' => 3,
                                    'ex_in' => 4,
                                    'ex_pr' => 5,
                                    'ex_pl' => 6,
                                    'ex_li' => 7,
                    );

                    $RowArray = array(
                                    'iss' => 'location',
                                    'sub' => 'username',
                                    'aud' => 'aud',
                                    'iat' => 'iat',
                                    'exp' => 'exp',
                                    'auth_time' => 'auth_time',
                                    'nonce' => 'nonce',
                                    'com' => 'testingcomp',
                                    'pos' => 'roll',
                                    'fnm' => 'name',
                                    'lnm' => 'lnm',
                                    'add' => 'email',
                                    'at_hash' => 'password',
                                );
                    $AddLoginData = array();

                    foreach ($JWTToken['id_token']['json']['body'] as $_key => $_body) {
                        $AddLoginData[$RowArray[$_key]] = $_body;
                    }

                    $AddLoginData['name'] = $AddLoginData['name'] . ' ' . $AddLoginData['lnm'];
                    $AddLoginData['lastlogin'] = time();
                    $AddLoginData['enabled'] = 1;
                    $AddLoginData['counter_fail'] = 1;
                    $AddLoginData['roll_id'] = $RollArray[trim($JWTToken['id_token']['json']['body']->pos)];
                    $AddLoginData['testingcomp_id'] = $Testingcomp['Testingcomp']['id'];
                    $AddLoginData['password'] = Security::hash($SecretKey, null, trim($JWTToken['id_token']['json']['body']->sub));

                    $Data = array();

                    foreach ($this->User->schema() as $_key => $_schema) {
                        if ($_key == 'id') {
                            continue;
                        }
                        if (isset($AddLoginData[$_key])) {
                            $Data[$_key] = $AddLoginData[$_key];
                        }
                    }

                    $Data['passwd'] = Security::hash($SecretKey, null, trim($JWTToken['id_token']['json']['body']->sub));

                    $this->request->data['User'] = $Data;

                    $this->User->create();

                    if ($this->User->save($this->request->data)) {
                        $User = $this->User->find('first', array('conditions'=>array('User.id'=>$this->User->getLastInsertID())));

                        $password = Security::hash($SecretKey, null, $User['User']['username']);

                        $this->request->data['User']['username'] = $User['User']['username'];
                        $this->request->data['User']['password'] = $password ;
                        $this->request->data['Language']['beschreibung'] = 2;

                        $Error['Count']++;
                        $Error['Message'][$Error['Count']] = __('Login', true);
                        $this->set('Error', $Error);
                        $this->render('oauth');
                        return;
                    } else {
                        $Error['Count']++;
                        $Error['Message'][$Error['Count']] = __('Login stopped, the new user could not be added', true);
                    }

                    if ($Error['Count'] > 0) {
                        $this->set('Error', $Error);
                        $this->render('oauth');
                        return;
                    }
                }
            }
        }
    }


    public function login()
    {
        //  phpinfo(); die ();
        if (isset($this->request->data['Login']['string'][1])) {
            $this->oauth();
            $this->render('oauth');
            return;
        }

        $max_fail_login = 3; // Anzahl ungültiger Loginversuche
        $max_unlogged_time = 7776000; // maximale Zeit zwischen zwei Logins (in diesem Fall 90 Tage)

        if (Configure::check('MaxFailLogin') == true) {
            $max_fail_login = Configure::read('MaxFailLogin');
        }
        if (Configure::check('MaxUnloggedTime')) {
            $max_unlogged_time = Configure::read('MaxUnloggedTime');
        }

        if (isset($this->request->data['User']) && $this->request->is('post')) {

            $username = Sanitize::paranoid($this->request->data['User']['username']);

            $conditions = array('User.username' => $username);

            $user = $this->User->find('first', array('conditions' => array('User.username' => $username)));

            if (isset($this->request->data['Language']['beschreibung'])) {
                $this->loadModel('Language');
                $lang = $this->Language->find('first', array('conditions'=>array('id'=>intval($this->request->data['Language']['beschreibung']))));
                if (count($lang)) {
                    $this->Cookie->write('lang', intval($lang['Language']['id']), false, '+365 day');
                    Configure::write('Config.language', $lang['Language']['locale']);
                    setlocale(LC_ALL, $lang['Language']['iso']);
                }

                if (isset($this->request->data['Language']['lastpath'])) {
                    $this->set('afterEdit', $this->Navigation->afterEDIT($this->request->data['Language']['lastpath'], 50));
                    $this->render('/closemodal', 'modal');

                    return;
                }
            }

            if ($this->Auth->login()) {
                $login_fail = 1;
                $login_counter = $this->Auth->user('counter_fine');
                $login_counter++;
                $UserLoginData = array();
                $UserLoginData['User']['id'] = $this->Auth->user('id');
                $UserLoginData['User']['counter_fine'] = $login_counter;
                $UserLoginData['User']['counter_fail'] = $login_fail;
                $UserLoginData['User']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
                $UserLoginData['User']['roll_id'] = $this->Auth->user('roll_id');
                $UserLoginData['User']['enabled'] = $this->Auth->user('enabled');
                $UserLoginData['User']['lastlogin'] = time();

                $last_login = $this->Auth->user('lastlogin');
                $last_login_current = time() - $max_unlogged_time;

                App::import('Vendor', 'clientinfo');
                $ClientInfo = new clientinfo();
                $SystemInfo = array();

                $SystemInfo['browser_clear'] = $ClientInfo->showInfo("browser");
                $SystemInfo['browser_version_clear'] = $ClientInfo->showInfo("version");
                $SystemInfo['os_clear'] = $ClientInfo->showInfo("os");

                $ClientIp = $this->Autorisierung->GetClientIp();
                //				$ClientIp = '195.243.81.122';

                //				$UserInfo = array_merge(array('ip' =>$ClientIp),get_browser(null, true),$this->Autorisierung->IpInfo($ClientIp,'Location'));
                $AllClientInfo = array_merge($SystemInfo, array());

                $AllClientInfo['user_id'] = $this->Auth->user('id');
                $AllClientInfo['testingcomp_id'] = $this->Auth->user('testingcomp_id');

                $this->loadModel('UserData');

                $this->UserData->create();
                $this->UserData->save($AllClientInfo);

                // Wenn sich der User zu lange nicht eingeloggt hat, wird das Konto deaktiviert
                if ($last_login < $last_login_current) {
                    $this->Session->setFlash(__('This account has been suspended due to inactivity', true));
                    $this->redirect($this->Auth->logout());
                }

                if ($this->Auth->user('counter_fail') > $max_fail_login) {
                    $this->Session->setFlash(__('This account is blocked, to many invalid login attempts', true));
                    $this->redirect($this->Auth->logout());
                }

                if ($this->User->save($UserLoginData)) {
                    $this->Autorisierung->Logger($this->Auth->user('id'), $this->Auth->user());

                    $Redirect['controller'] = 'topprojects';
                    $Redirect['action'] = 'start';
                    $Redirect['term'] = null;

                    $this->redirect(array('controller' => $Redirect['controller'], 'action' => $Redirect['action'], $Redirect['term']));

                } else {
                    $this->redirect($this->Auth->logout());
                }
            } else {
                if(isset($user['User'])) {
                  $login_counter = $user['User']['counter_fail'];
                  $login_counter++;

                  $UserLoginData = array();
                  $UserLoginData['User']['id'] = $user['User']['id'];
                  $UserLoginData['User']['counter_fail'] = $login_counter;
                  $UserLoginData['User']['testingcomp_id'] =$user['User']['testingcomp_id'];
                  $UserLoginData['User']['roll_id'] = $user['User']['roll_id'];
                  $UserLoginData['User']['enabled'] = $user['User']['enabled'];
                }

                if (count($user) > 0) {
                    $this->User->save($UserLoginData);
                }

                if ($UserLoginData['User']['counter_fail'] > $max_fail_login) {
                    $this->Session->setFlash(__('This account is blocked, to many invalid login attempts'));
                    $this->redirect($this->Auth->logout());
                }
            }

            if (isset($this->request->data['User']['username']) && !empty($this->request->data['User']['username'])) {
                $this->Session->setFlash(__('Invalid username or password, try again'));
            }
            $this->redirect(array('controller' => 'users', 'action' => 'logout'));
        }

        if ($this->Auth->user('id')) {
            $this->redirect(array('controller' => 'users', 'action' => 'loggedin'));
        }

        $this->render(null, 'login');
    }

    public function redirectafterlogin()
    {
        $this->layout = 'redirectafterlogin';
        $redirectto = Router::url(array('action'=>'loggedin'));//FULL_BASE_URL . $this->webroot . 'users/loggedin';
        $this->set('redirectto', $redirectto);
    }

    public function loggedin()
    {
        $this->Navigation->ReportVars();

        if ($this->Auth->user('id')) {

            $this->layout = 'blank';

            $this->Autorisierung->ConditionsStart();
            $lastURL = $this->request->webroot.'topprojects/start';
            if ($this->Session->read('ajaxURL')) {
                $lastURL = $this->Session->read('ajaxURL');
                $this->Session->delete('ajaxURL');
            }

            // Druckbefehl abfangen
            $url_test = explode('/', $lastURL);

                // bei fehlender Autorisierung für die Projkte kommt der Benutzer hier an
                if (count($url_test) == 1 && $url_test[0] == 'topprojects') {
                    $this->Session->setFlash(__('No rights for this project.'));
                    $lastURL = $this->request->webroot;

                    $this->redirect('topprojects/start');
                } else {
                    if ($this->Session->read('AclError') && $this->Session->read('AclError') != '') {
                        $this->Session->setFlash($this->Session->read('AclError'));
                        $this->set('ajax_url', $this->Session->read('AclErrorUrl'));
                        $this->set('timeout', 3000);
                        $this->Session->delete('AclError');
                        $this->Session->delete('AclErrorUrl');
                    } else {
                        $this->set('ajax_url', $lastURL);
                    }
                    $this->render(null, 'default');
                }
        } else {
            $this->redirect(array('controller' => 'users', 'action' => 'login'));
        }

    }

    public function logout()
    {
        if ($this->Auth->user('id') == '') {
            $this->redirect($this->Auth->logout());
        }

        $this->Autorisierung->Logger($this->Auth->user('id'), $this->Auth->user());
        $this->redirect($this->Auth->logout());
    }

    /**
     * index method
     *
     * @return void
     */
    public function index()
    {
        $this->include_index();
    }

    protected function include_index()
    {
        $this->Navigation->GetSessionForPaging();
        $this->layout = 'modal';
        // Der Root soll nicht angezeigt werden
        $rootArray = array('User.id !=' => 1);

        $this->paginate = array(
            'conditions' => array(
                'User.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'User.hidden' => 0,
                $rootArray,
                $this->Autorisierung->ConditionsUserRoll()
                ),
            'limit' => 10,
            'order' => array('name' => 'asc')
        );

        $this->User->recursive = 0;
        $users = $this->paginate('User');
        $users = $this->Data->GetUserInfos($users);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'users','action' => 'add', 'terms' => null);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('users', $users);
        $this->set('breads', $this->Navigation->Breads(null));
        $this->render('index', 'modal');
        $this->Navigation->SetSessionForPaging();
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view()
    {
        $this->layout = 'modal';

        $testingcomp_id = $this->request->projectvars['VarsArray'][0];
        $id = $this->request->projectvars['VarsArray'][1];

        if (!$this->User->exists($id)) {
            throw new NotFoundException(__('Invalid user'));
        }

        $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
        $_user = $this->User->find('first', $options);
        $this->Autorisierung->ConditionsTestinccompsTest($_user['Testingcomp']['id']);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'users','action' => 'index', 'terms' => null);
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'users','action' => 'add', 'terms' => null);
        //		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
        //		$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);

        $this->set('SettingsArray', $SettingsArray);

        $this->set('user', $_user);
        $this->loadModel('Testingmethod');
        $this->loadModel('Testingcomp');
        $testingmethods = $this->Testingmethod->find('list');
        $testingcomps = $this->Testingcomp->find('list');
        $this->set('breads', $this->Navigation->Breads(null));
        $this->set(compact('testingmethods'));
        $this->set(compact('testingcomps'));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add()
    {
        $this->layout = 'modal';

        $count = $this->User->find('count', array('conditions' => array('User.id !=' => 1, 'User.hidden' => 0,'User.enabled >' => 0)));

        $MaxCount = 55;

        if(Configure::check('MaxUsers') == true) $MaxCount = Configure::check('MaxUsers');

        if ($count > $MaxCount) {
            $this->Flash->error(__('Die Höchstanzahl an aktiven Benutzern wurde erreicht.',true), array('key' => 'error'));
            $this->set('max_user', true);
        }

        $PasswordConditions = $this->Autorisierung->PasswordConditions();
        $this->set('PasswordConditions',$PasswordConditions);

        if (isset($this->request->data['User'])) {

          $insert = $this->request->data['User'];
            
          $insert['roll_id'] = $this->request->data['Roll']['id'];
          $insert['lastlogin'] = time();

          $this->User->create();

          if ($this->User->save($insert)) { 

            $id = $this->User->getInsertID();
            $this->Autorisierung->Logger($id, $insert); 
            $this->Flash->success(__('User was saved.',true), array('key' => 'success'));

            $FormName['controller'] = 'testingcomps';
            $FormName['action'] = 'view';
            $FormName['terms'] = $this->request->projectvars['VarsArray'];

            $this->set('FormName',$FormName);

          } else {
            $this->Flash->error(__('User could not be saved. Please, try again.',true), array('key' => 'error'));
          }
        }

        $roll = $this->Auth->user('Roll');
        $rolls = $this->User->Roll->find('list', array('conditions' => array('id >=' => $roll['id'])));

        $testingcomps = $this->User->Testingcomp->find('list', array(
            'conditions' => array(
                //'id' => $this->Auth->user('testingcomp_id')
            )
        ));

        $testingcomprolls = $this->User->Testingcomp->find('list', array(
            'fields'=>array('Testingcomp.id', 'Testingcomp.roll_id'),
            'conditions' => array(
                'id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id')
            )
        ));

        $this->request->data['User']['testingcomp_id'] = $this->request->projectvars['VarsArray'][0];
        $this->request->data['User']['enabled'] = 1;

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'testingcomps','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

        $this->set(compact('rolls', 'testingcomps', 'testingcomprolls'));
    }

    //Holt die Examiner welche zugewiesen werden können
    private function getAssignableExaminers()
    {
        $users = $this->User->Examiner->recursive = -1;
        $users = $this->User->Examiner->find('all', array('fields' => array('Examiner.id', 'Examiner.first_name', 'Examiner.name'),
                                                        'conditions' => array(
                                                        )));
        $users = Hash::combine($users,'{n}.Examiner.id',array('%s %s', '{n}.Examiner.first_name', '{n}.Examiner.name'), '{n}.Examiner.working_place');

        return $users;
    }

    //Holt die bereits zugewiesenen Examiner
    private function getAssignedExaminers($user_id)
    {
        $users = $this->User->Examiner->recursive = -1;
        $users = $this->User->Examiner->find('all', array('conditions' => array(
                                                            'user_id' => $user_id,
                                                        )));
        $users = Hash::combine($users,'{n}.Examiner.id',array('%s', '{n}.Examiner.id'));
        $users = array_values($users);
        return $users;
    }

    private function assignExaminers($user_id, $widget_data)
    {
        if(!empty($user_id)) {
            if(!empty($widget_data) && is_countable($widget_data) && count($widget_data) > 0) {
                foreach($widget_data as $examiner_id){
                $this->User->Examiner->id=$examiner_id;
                $this->User->Examiner->set(array('user_id'=>$user_id));
                $this->User->Examiner->save();
                }

                $examiners = $this->User->Examiner->find('list', array('fields' => array('Examiner.id'),
                                                                    'conditions' => array('Examiner.user_id'=>$user_id)));
                $notFound = array();
                foreach($examiners as $examiner => $x){
                $bFound = false;
                for($y = 0; $y < count($widget_data); $y++) {
                    if($x == $widget_data[$y]) {
                    $bFound = true;
                    }
                }
                if(!$bFound) {
                    array_push($notFound, $x);
                }
                }

                //user_id für entferne Examiner auf null setzen
                foreach($notFound as $examiner_id => $value){
                $this->User->Examiner->id=$value;
                $this->User->Examiner->set(array('user_id'=>null));
                $this->User->Examiner->save();
                }

            }
        }
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
        $this->loadModel('LandingpagesTemplate');
        $this->loadModel('Landingpage');

        $locale = $this->Lang->Discription();

        $testingcomp_id = $this->request->projectvars['VarsArray'][0];
        $id = $this->request->projectvars['VarsArray'][1];

        if ($id == null && isset($this->request->data['QuickUsersearch']) && isset($this->request->data['id'])) {
            $id = $this->request->data['id'];
        }
        if ($id == null && isset($this->request->data['User']['id'])) {
            $id = $this->request->data['User']['id'];
        }

        if (!$this->User->exists($id)) {
            throw new NotFoundException(__('Invalid user'));
        }

        if (isset($this->request->data['User']) || $this->request->is('put')) {
            $data = array();
            $this->request->data['User']['roll_id'] = $this->request->data['Roll']['id'];
            $this->request->data['User']['testingcomp_id'] = $this->request->data['Testingcomp']['id'];
            $data = $this->request->data['User'];

            if (isset($this->request->data['User']['counter_blocked']) && $this->request->data['User']['counter_blocked'] == 0) {
                $this->request->data['User']['counter_fail'] = 1;
            }
            if (isset($this->request->data['User']['time_blocked']) && $this->request->data['User']['time_blocked'] == 0) {
                $this->request->data['User']['lastlogin'] = time();
            }

            if(isset($this->request->data['User']['password'])) unset($this->request->data['User']['password']);
            if(isset($this->request->data['User']['passwd'])) unset($this->request->data['User']['passwd']);
            if(isset($this->request->data['User']['passwd_confirm'])) unset($this->request->data['User']['passwd_confirm']);

            if ($this->User->save($this->request->data) && $this->Auth->user('Roll.id') <= $this->request->data['Roll'] ['id']) {

                $LandingPageValue = $this->Navigation->SaveLandingPageValue();

                $this->Autorisierung->Logger($id, $this->request->data);
                $this->Flash->success('User was saved.', array('key' => 'success'));

                if(isset($this->request->data['AssignedExaminers']['widget'])){
                    $this->assignExaminers($id, $this->request->data['AssignedExaminers']['widget']);
                }

                $FormName['controller'] = 'testingcomps';
                $FormName['action'] = 'view';
                $FormName['terms'] = $this->request->projectvars['VarsArray'];
    
                $this->set('FormName',$FormName);

            } else {
                $this->Flash->error('User could not be saved. Please, try again.', array('key' => 'error'));
            }
        }

        $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
        $user = $this->User->find('first', $options);
        unset($user['Testingcomp']['roll_id']);

        $user = $this->Data->GetUserInfos($user);
        $this->request->data = $user;

        $assignableExaminers = $this->getAssignableExaminers();
        $assignedExaminers = $this->getAssignedExaminers($id);

        $this->set('assignableExaminers', $assignableExaminers);
        $this->set('assignedExaminers', $assignedExaminers);

        $this->request->projectvars['VarsArray'][0] = $user['Testingcomp']['id'];

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'testingcomps','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['dellink'] = array('discription' => __('Back', true), 'controller' => 'users','action' => 'delete', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

        $UserData = $this->request->data('User');

        $roll = $this->Auth->user('Roll');

        $rolls = $this->User->Roll->find('list', array('conditions' => array('id >=' => $roll['id'])));

        $testingcompsOptions = null;

        if ($roll['id'] < 5) {
            $testingcompsOptions = array('id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id'));
        } else {
            $testingcompsOptions = array('id' => $this->Auth->user('testingcomp_id'));
        }

        if(count($testingcompsOptions['id']) == 0){
          $testingcompsOptions = array('id' => $this->Auth->user('testingcomp_id'));
        }

        $testingcomps = $this->User->Testingcomp->find(
            'list',
            array(
            'conditions' => $testingcompsOptions
            )
        );

        $testingcomprolls = $this->User->Testingcomp->find('list', array(
            'fields'=>array('Testingcomp.id', 'Testingcomp.roll_id'),
            'conditions' => array(
                'id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id')
            )
        ));

        $LandingPageValue = $this->Navigation->SelectLandingPageValue();
        $landingpageswidget = $this->Navigation->SelectLandingPageWidgets();
        $landingpageslarge = $this->Navigation->SelectLandingPageLarge();

        $this->set('breads', $this->Navigation->Breads(null));
        $this->set('LandingPageValue', $LandingPageValue);
        $this->set(compact('rolls', 'testingcomps', 'testingcomprolls','landingpageswidget','landingpageslarge'));
    }

    public function password() {

      if(!empty($this->request->data['landig_page_large'])) $this->__landingpage();
      else $this->__password();

    }

    protected function __landingpage()
    {

        $this->layout = 'blank';
        $this->loadModel('LandingpagesTemplate');
        $this->loadModel('Landingpage');

        $locale = $this->Lang->Discription();

        $testingcomp_id = $this->request->projectvars['VarsArray'][0];
        $id = $this->request->projectvars['VarsArray'][1];

        if ($id == null && isset($this->request->data['QuickUsersearch']) && isset($this->request->data['id'])) {
            $id = $this->request->data['id'];
        }
        if ($id == null && isset($this->request->data['User']['id'])) {
            $id = $this->request->data['User']['id'];
        }

        if (!$this->User->exists($id)) {
            return;
        }

        if (isset($this->request->data['User']) || $this->request->is('put')) {
            $data = array();
            $this->request->data['User']['roll_id'] = $this->request->data['Roll']['id'];
            $this->request->data['User']['testingcomp_id'] = $this->request->data['Testingcomp']['id'];
            $data = $this->request->data['User'];

            $this->Autorisierung->ConditionsTest('TestingcompsTopprojects', 'testingcomp_id', $this->request->data['User']['testingcomp_id']);

            if ($this->User->save($this->request->data) && $this->Auth->user('Roll.id') <= $this->request->data['Roll'] ['id']) {

                $LandingPageValue = $this->Navigation->SaveLandingPageValue();

                $FormName['controller'] = 'users';
                $FormName['action'] = 'edit';
                $FormName['terms'] = implode('/',array($this->request->data['User']['testingcomp_id'],$this->request->data['User']['id']));
                $this->set('FormName', $FormName);

                $this->Flash->success('Settings was saved.', array('key' => 'success'));

            } else {
                $this->Flash->error('Settings could not be saved. Please, try again.', array('key' => 'error'));
            }
        }

        $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
        $user = $this->User->find('first', $options);
        unset($user['Testingcomp']['roll_id']);

        $user = $this->Data->GetUserInfos($user);
        $this->request->data = $user;

        $UserData = $this->request->data('User');

        $roll = $this->Auth->user('Roll');

        $rolls = $this->User->Roll->find('list', array('conditions' => array('id >=' => $roll['id'])));

        $testingcompsOptions = array();

        if ($roll['id'] < 5) {
            $testingcompsOptions = array('id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id'));
        } else {
            $testingcompsOptions = array('id' => $this->Auth->user('testingcomp_id'));
        }

        if(count($testingcompsOptions) == 0){
          $testingcompsOptions = array('id' => $this->Auth->user('testingcomp_id'));
        }

        $testingcomps = $this->User->Testingcomp->find(
            'list',
            array(
            'conditions' => $testingcompsOptions
            )
        );

        $testingcomprolls = $this->User->Testingcomp->find('list', array(
            'fields'=>array('Testingcomp.id', 'Testingcomp.roll_id'),
            'conditions' => array(
                'id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id')
            )
        ));

        $LandingPageValue = $this->Navigation->SelectLandingPageValue();
        $landingpageswidget = $this->Navigation->SelectLandingPageWidgets();
        $landingpageslarge = $this->Navigation->SelectLandingPageLarge();

        $this->set('LandingPageValue', $LandingPageValue);
        $this->set(compact('rolls', 'testingcomps', 'testingcomprolls','landingpageswidget','landingpageslarge'));

        $this->render('landingpagesettings');
        return;

    }

    protected function __password() {

      App::uses('Sanitize', 'Utility');

      $this->loadModel('LandingpagesTemplate');
      $this->loadModel('Landingpage');

      $locale = $this->Lang->Discription();
      $tetingcomp_id  = $this->request->projectvars['VarsArray'][0];
      $id  = $this->request->projectvars['VarsArray'][1];

      $PasswordConditions = $this->Autorisierung->PasswordConditions();
      $this->set('PasswordConditions',$PasswordConditions);

      $this->layout = 'modal';

      $SettingsArray = array();

      if($this->request->projectvars['VarsArray'][2] == 1){
          $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'users','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);
      }

      if (isset($this->request->data['User']) || $this->request->is('put')) {

        if(isset($this->request->data['User']['password_request']) && $this->request->data['User']['password_request'] == 1){


          $this->layout = 'json';

          $this->User->recursive = -1;
          $user = $this->User->find('first', array('conditions' => array('User.id' => $id)));

          $password = Security::hash($this->request->data['User']['password'], null, true);

          $adminuser = array();
          if($user['User']['id'] != $this->Auth->user('id') && $this->Auth->user('roll_id') == 1) {
			       $adminuser = $this->User->find('first',array('conditions'=>array('User.id' => $this->Auth->user('id')),'fields' => array('roll_id','password')));
          }

          if($user['User']['password'] != $password && empty($adminuser)){

            $Message = array('Type' => 'error','Message' => __('Wrong passwort',true));
            $this->set('response',json_encode($Message));
            $this->render('password_json');
            return false;

          }else{
            if(!empty($adminuser) && $adminuser['User']['password'] != $password ){
              $Message = array('Type' => 'error','Message' => __('Wrong admin passwort',true));
              $this->set('response',json_encode($Message));
              $this->render('password_json');
              return false;
            }
          }

         if(!empty($adminuser) && $adminuser['User']['password'] == $password ){
            $Message = array('Type' => 'success','Message' => __('Passwort okay',true));
            $this->set('response',json_encode($Message));
            $this->render('password_json');
            return false;
         }
         else{
          if($user['User']['password'] == $password){

            $Message = array('Type' => 'success','Message' => __('Passwort okay',true));
            $this->set('response',json_encode($Message));
            $this->render('password_json');
            return false;
          }
        }


          $this->set('response',json_decode(array()));
          $this->render('password_json');
          return false;
        }

        $this->User->recursive = -1;
        $user = $this->User->find('first', array('conditions' => array('User.id' => $id)));

        $password = Security::hash($this->request->data['User']['password'], null, true);

        $PasswordCheck = $this->Autorisierung->PasswordCheck();

        if($PasswordCheck === false){
          $this->request->data = $user;
          $this->set('SettingsArray', $SettingsArray);
          $this->render();
          return false;
        }

        $this->User->recursive = 0;
        $user = $this->User->find('first', array('conditions' => array('User.id' => $id)));
        $this->request->data['User']['testingcomp_id'] = $user['Testingcomp']['id'];
        $this->request->data['User']['roll_id'] = $user['Roll']['id'];
        $this->request->data['User']['enabled'] = 1;

        if($this->User->save($this->request->data)) {
          $this->Autorisierung->Logger($id, $this->request->data);
          $this->Flash->success('Your password has been changed.', array('key' => 'success'));

          if($this->request->projectvars['VarsArray'][2] == 1){
            $FormName['controller'] = 'users';
            $FormName['action'] = 'edit';
            $FormName['terms'] = implode('/',array($this->request->data['User']['testingcomp_id'],$this->request->data['User']['id']));
            $this->set('FormName', $FormName);

          }

          $this->set('SettingsArray', $SettingsArray);
          $this->render();
          return false;
        } else {
          $this->Flash->error('Password could not be saved. Please, try again.', array('key' => 'error'));
        }
      }

      $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
      $this->request->data = $this->User->find('first', $options);

      $this->set('SettingsArray', $SettingsArray);

      $this->Flash->warning(__('Please enter the password of the currently logged in user.',true), array('key' => 'warning'));
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

        $testingcomp_id = $this->request->projectvars['VarsArray'][0];
        $id = $this->request->projectvars['VarsArray'][1];

        if (isset($this->request->data['User']) || $this->request->is('put')) {

          $this->User->id = $this->request->data['User']['id'];

          $FormName['controller'] = 'testingcomps';
          $FormName['action'] = 'view';
          $FormName['terms'] = $this->request->projectvars['VarsArray'];

          if ($id == 1) {
              $this->Flash->error(__('This user can not be deleted',true), array('key' => 'error'));
            } else {
              if (!$this->User->exists()) {
                  $this->Flash->error(__('Invalid user',true), array('key' => 'error'));
                }
              if ($this->User->delete($this->request->data['User']['id'], false)) {
                  $this->Flash->success(__('User was deleted',true), array('key' => 'success'));
                }
          }

          $this->set('FormName',$FormName);
          $this->render();
          return;
        }

        $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
        $user = $this->User->find('first', $options);
        $this->request->data = $user;

        $this->Flash->warning(__('Do you want to delete user',true), array('key' => 'warning'));

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'users','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function quicksearch()
    {
        App::uses('Sanitize', 'Utility');

        $this->_quicksearch();
    }

    protected function _quicksearch()
    {
        $this->layout = 'json';

        $testingcomp_id = $this->request->projectvars['VarsArray'][0];

        $model = Sanitize::stripAll($this->request->data['model']);
        $field = Sanitize::stripAll($this->request->data['field']);
        $term = Sanitize::escape($this->request->data['term']);

        $conditions =  array(
        				$model.'.testingcomp_id' => $testingcomp_id,
                $model.'.'.$field.' LIKE' => '%'.$term.'%',
                $model.'.hidden' => 0,
        );

        if (isset($this->request->data['Conditions'])) {
            foreach ($this->request->data['Conditions'] as $_key => $_conditions) {
                $conditions[$model.'.'.Sanitize::escape($_key)] = Sanitize::escape($_conditions);
            }
        }

        if (!is_object($model)) {
            $this->loadModel($model);
        }

        $options = array(
                'fields' => array('id',$field),
                'limit' => 5,
                'conditions' => $conditions
        );

        $this->$model->recursive = -1;
        $data = $this->$model->find('all', $options);

        $response = array();

        foreach ($data as $_key => $_data) {
            array_push($response, array('key' => $_data[$model]['id'],'value' => $_data[$model][$field]));
        }

        $this->set('response', json_encode($response));
    }
}
