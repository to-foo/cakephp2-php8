<?php

// Test
App::uses('AppShell', 'Console/Command');
App::uses('Controller', 'Controller');
App::uses('ComponentCollection', 'Controller');
App::uses('AclComponent', 'Controller/Component');
App::uses('DbAcl', 'Model');
App::uses('Hash', 'Utility');

class UpdateShell extends AppShell
{
    public $uses = array('Topprojects', 'Acl');

    public function main()
    {
        $this->out('Hello world.');
    }

    public function AddCertificateData()
    {

        App::uses('Certificate', 'Model');
        $Certificate = ClassRegistry::init('Certificate');

        $Conditions = array(
            'conditions' => array(
                'Certificate.certificate_data_active' => 1,
            ),
        );

        $Certificates = $Certificate->find('list', $Conditions);

        foreach ($Certificates as $value) {

            $this->_AddCertificateData($value);

        }

    }

    protected function _AddCertificateData($value)
    {

        App::uses('CertificateData', 'Model');
        $CertificateData = ClassRegistry::init('CertificateData');

        $Conditions = array(
            'conditions' => array(
                'CertificateData.certificate_id' => $value,
            ),
        );

        $Certificates = $CertificateData->find('first', $Conditions);

        if (count($Certificates) > 0)
            return;

        App::uses('Certificate', 'Model');
        $Certificate = ClassRegistry::init('Certificate');

        $Conditions = array(
            'conditions' => array(
                'Certificate.id' => $value,
            ),
        );

        $Certificates = $Certificate->find('first', $Conditions);

        $Save = array(
            'certificate_id'            => $Certificates['Certificate']['id'],
            'examiner_id'               => $Certificates['Certificate']['examiner_id'],
            'testingmethod'             => $Certificates['Certificate']['testingmethod'],
            'first_certification'       => 0,
            'certified'                 => 0,
            'recertification_in_year'   => 10,
            'renewal_in_year'           => 5,
            'horizon'                   => 9,
            'first_registration'        => $Certificates['Certificate']['first_registration'],
            'certified_date'            => $Certificates['Certificate']['first_registration'],
            'apply_for_recertification' => 0,
            'deleted'                   => 0,
            'active'                    => 1,
            'user_id'                   => $Certificates['Certificate']['user_id'],
        );

        $CertificateData->create();
        $CertificateData->save($Save);

    }

    public function CorrectCertificateTableTestingmethodId()
    {


        App::uses('Certificate', 'Model');
        $Certificate = ClassRegistry::init('Certificate');

        $Conditions = array(
            'conditions' => array(
                'Certificate.testingmethod_id' => 0,
            ),
        );

        $Certificates = $Certificate->find('list', $Conditions);

        foreach ($Certificates as $value) {

            $this->_CorrectCertificateTableTestingmethodId($value);

        }


    }

    protected function _CorrectCertificateTableTestingmethodId($id)
    {

        App::uses('Testingmethod', 'Model');
        $Testingmethod = ClassRegistry::init('Testingmethod');

        App::uses('Certificate', 'Model');
        $Certificate = ClassRegistry::init('Certificate');

        $Conditions = array(
            'conditions' => array(
                'Certificate.id' => $id,
            ),
        );

        $Certificates = $Certificate->find('first', $Conditions);

        $testingmethod = strtolower($Certificates['Certificate']['testingmethod']);

        $Conditions = array(
            'conditions' => array(
                'Testingmethod.value' => $testingmethod,
            ),
        );

        $Testingmethods = $Testingmethod->find('first', $Conditions);

        if (count($Testingmethods) == 0)
            return;

        $Certificates['Certificate']['testingmethod_id'] = $Testingmethods['Testingmethod']['id'];

        $Save = array(
            'id'               => intval($Certificates['Certificate']['id']),
            'testingmethod_id' => intval($Certificates['Certificate']['testingmethod_id'])
        );

        $Certificate->save($Save);

        pr($Save);

    }

    public function UpdateExpeditingUser()
    {

        App::uses('UserlistExpediting', 'Model');
        $UserlistExpediting = ClassRegistry::init('UserlistExpediting');

        App::uses('User', 'Model');
        $User = ClassRegistry::init('User');

        $Conditions = array(
            'conditions' => array(
                //             'DropdownsMaster.id' => $MasterDropdownId,
            ),
        );

        $Userlist = $UserlistExpediting->find('all', $Conditions);

        $Users = array();

        foreach ($Userlist as $key => $value) {

            $Users[$key] = $this->_Username($value['UserlistExpediting']);
            $Users[$key] = $this->_Rollname($Users[$key]);
            //           $Users[$key] = $this->_SaveMailAdress($Users[$key]);
            $Users[$key] = $this->_SaveUsers($Users[$key]);
            $Users[$key] = $this->_MailMessage($Users[$key]);
        }

        //         pr($Users);


    }

    protected function _MailMessage($data)
    {

        App::uses('User', 'Model');
        $Model = ClassRegistry::init('User');

        $Conditions = array(
            'conditions' => array(
                'User.email' => $data['email'],
            ),
        );

        $User = $Model->find('first', $Conditions);


        if ($User['User']['id'] < 36)
            return $data;

        $this->out(h('Zugangsdaten Expeditingtool für ' . $data['first_name'] . ' ' . $data['last_name']));
        $this->out($data['email']);
        $this->out(' ');
        $this->out(h('https://total.docu-dynamics.cloud'));
        $this->out('Username: ' . $data['username']);
        $this->out('Passwort: ' . $data['pass']);
        $this->out(' ');
        $this->out(' ');
        $this->out(' ');

    }

    protected function _SaveMailAdress($data)
    {

        App::uses('Emailadress', 'Model');
        $Model = ClassRegistry::init('Emailadress');

        $TopprojectId   = 13;
        $EmailsettingId = 1;

        $Conditions = array(
            'conditions' => array(
                'Emailadress.email' => $data['email'],
            ),
        );

        $Emailadress = $Model->find('first', $Conditions);

        if (!empty($Emailadress))
            return $data;

        $Insert = array(
            'topproject_id'   => $TopprojectId,
            'emailsetting_id' => $EmailsettingId,
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'email'           => $data['email'],
            'roll'            => $data['roll_id'],
        );

        $Model->create();
        $Model->save($Insert);

        return $data;

    }

    protected function _SaveUsers($data)
    {

        if (!isset($data['roll_id']))
            return $data;

        App::uses('User', 'Model');
        $Model = ClassRegistry::init('User');

        $TopprojectId  = 13;
        $TestingcompId = 34;

        $Insert = array(
            'name'           => $data['first_name'] . ' ' . $data['last_name'],
            'username'       => $data['username'],
            'password'       => $data['pass'],
            'passwd'         => $data['pass'],
            'lastlogin'      => time(),
            'enabled'        => 1,
            'roll_id'        => $data['roll_id'],
            'testingcomp_id' => $TestingcompId,
            'email'          => $data['email'],
            'hidden'         => 0,
            'counter_fine'   => 1,
            'counter_fail'   => 1,
            'examiner_id'    => 0,
        );

        //        pr($Insert);

        $Conditions = array(
            'conditions' => array(
                'User.email' => $data['email'],
            ),
        );

        $User = $Model->find('first', $Conditions);

        if (!empty($User))
            return $data;



        $Model->create();
        $Test = $Model->save($Insert);

        $data['sendmail'] = 1;

        return $data;

    }

    protected function _Username($data)
    {

        $FirstN   = str_split(trim($data['first_name']));
        $LastN    = strtolower(trim($data['last_name']));
        $Email    = trim($data['email']);
        $UserName = strtolower($FirstN[0]) . $LastN;

        $data['username'] = $UserName;

        return $data;
    }

    protected function _Rollname($data)
    {

        App::uses('Roll', 'Model');
        $Model            = ClassRegistry::init('Roll');
        $Model->recursive = -1;
        $Roll             = $Model->find('list', array('fields' => array('id', 'name')));

        $MatchArray = array(
            'Projektleiter'  => 'Projektleiter/Bedarfsträger',
            'Einkauf'        => 'Einkauf/Vertragswesen',
            'Instandhaltung' => 'Instandhaltung',
            'Instandhaltung' => 'Instandhaltung/Bedarfsträger',
            'Inspektion'     => 'Inspektion',
            'Projektleiter'  => 'Projektleiter/Bedarfsträger',
        );

        $Key = array_search(trim($data['first_roll']), $MatchArray);

        if ($Key === false)
            return $data;

        $data['roll_name'] = $Key;

        $Key = array_search($Key, $Roll);

        if ($Key === false)
            return $data;

        $data['roll_id'] = $Key;

        $Pass = 'PassTotal' . $data['id'] . '!';

        $data['pass_hash'] = Security::hash($Pass, null, true);

        $data['pass'] = $Pass;

        return $data;
    }

    public function UpdateTest()
    {
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');

        $time = time();

        $archiv_rows = new Folder(APP . 'update' . DS . $time, true, 0700);
    }

    /**
     * setDefaultValues
     * Setzt Standardwerte
     * @return void
     */
    public function setDefaultValues()
    {
        $isTestRun = false;

        if (!isset($this->args[0])) {
            $this->out(h('Kein Model übergeben.'));
            return;
        }

        if (isset($this->args[1])) {
            if (strtolower($this->args[1]) == 'test') {
                $this->out(h('Probelauf'));

                $isTestRun = true;
            }
        }

        $model = $this->args[0];

        if (in_array($model, App::objects('Model'))) {
            $currentModel = ClassRegistry::init($model);
            $tableName    = $currentModel->table;

            $schema  = $currentModel->schema();
            $columns = array_keys($schema);

            $this->_setDefaultValuesForTable($currentModel, $schema, $columns, $tableName, $isTestRun);
        } else {
            $this->out(h('Model nicht gefunden.'));
        }
    }

    /**
     * _setDefaultValuesForTable
     * Setzt die Defaultwerte für das angegebene Model
     * @param  mixed $model . Modelobjekt
     * @param  mixed $schema . Schema
     * @param  mixed $columns die Spalten
     * @param  mixed $tableName der echte Tabellenname
     * @param  mixed $isTestRun Testlauf
     * @return void
     */
    private function _setDefaultValuesForTable($model, $schema, $columns, $tableName, $isTestRun)
    {
        $db = ConnectionManager::getDataSource('default');

        foreach ($columns as $column) {
            if (!$this->_isInFieldBlacklist($column)) {
                $currentColumnInfo = $schema[$column];
                $currentDataType   = $currentColumnInfo['type'];
                $schemaDefault     = $currentColumnInfo['default'];

                if (!$this->_isInTypeBlacklist($currentDataType)) {
                    //Felder welche schon einen Standardwert besitzen überspringen
                    if ($schemaDefault === null) {
                        $currentDefaultValue = $this->_getDefaultValueForDatatype($currentDataType);

                        $qry = 'ALTER TABLE `' . $tableName . '` ALTER COLUMN `' . $column . '` SET DEFAULT \'' . $currentDefaultValue . '\';';
                        $this->out($qry);

                        if (!$isTestRun) {
                            $this->out($db->query($qry));
                        }
                    }
                } else {
                    if ($currentDataType == 'binary') {
                        $currentDataType = 'LONGBLOB';
                    }

                    $qry = 'ALTER TABLE `' . $tableName . '` CHANGE `' . $column . '` `' . $column . '` ' . $currentDataType . ' NULL;';
                    $this->out($qry);

                    if (!$isTestRun) {
                        $this->out($db->query($qry));
                    }
                }
            }
        }
    }

    /**
     * _isInTypeBlacklist
     * Typen für Extrabehandlung
     * @param  mixed $type
     * @return void
     */
    private function _isInTypeBlacklist($type)
    {
        $isInBlacklist = false;

        switch ($type) {
            case 'text':
            case 'binary':
                $isInBlacklist = true;
                break;
            case 'date':
            case 'datetime':
                $isInBlacklist = true;
                break;
        }

        return $isInBlacklist;
    }


    /**
     * _getDefaultValueForDatatype
     * Setzt den Standardwert für übergebene Datentypen
     * @param  mixed $dataType Datentyp eines Datenbankfeldes
     * @return void
     */
    private function _getDefaultValueForDatatype($dataType)
    {
        $defaultValue = null;
        switch ($dataType) {
            case 'string':
                $dataType = 'string';
                $defaultValue = '';
                break;
            case 'date':
            case 'datetime':
                $defaultValue = '0000-00-00 00:00:00';
                break;
            case 'integer':
            case 'tinyinteger':
                $defaultValue = 0;
                break;
        }

        return $defaultValue;
    }


    /**
     * _isInFieldBlacklist
     * Blacklist für Felder
     * @param  mixed $columnName
     * @return void
     */
    private function _isInFieldBlacklist($columnName)
    {
        $isCake = false;

        switch ($columnName) {
            case 'id':
            case 'created':
            case 'modified':
                $isCake = true;
                break;
            default:
                $isCake = false;
        }

        return $isCake;
    }

    public function DropdownTestningcompCorretur()
    {

        $this->loadModel('DropdownsMaster');
        $this->loadModel('DropdownsMastersTestingcomp');

        $DropdownsMaster = $this->DropdownsMaster->find('list', array('fields' => array('id', 'id')));

        $x = 0;

        foreach ($DropdownsMaster as $key => $value) {

            $x += $this->_DropdownTestningcompCorretur($value);

        }

        pr($x);

    }

    protected function _DropdownTestningcompCorretur($id)
    {

        $Test = $this->DropdownsMastersTestingcomp->find(
            'first',
            array(
                'conditions' => array(
                    'DropdownsMastersTestingcomp.dropdowns_masters_id' => $id,
                )
            )
        );

        if (count($Test) > 0)
            return 0;

        $Testingcomp = $this->DropdownsMaster->find(
            'first',
            array(
                'conditions' => array(
                    'DropdownsMaster.id' => $id,
                )
            )
        );

        if ($Testingcomp['DropdownsMaster']['testingcomp_id'] == 0)
            return 1;

        pr($Testingcomp['DropdownsMaster']['testingcomp_id']);

        $save = array(
            'id'                   => 0,
            'testingcomp_id'       => $Testingcomp['DropdownsMaster']['testingcomp_id'],
            'dropdowns_masters_id' => $id
        );

        $this->DropdownsMastersTestingcomp->create();

        $Testingcomp = $this->DropdownsMastersTestingcomp->save($save);

        return 1;

    }

    public function CopyDropdownMaster()
    {
        if (!isset($this->args[0])) {
            $this->out(h('Kein DropdownId Übergeben.'));
            return;
        }

        if (!isset($this->args[1])) {
            $this->out(h('Kein Modul Übergeben.'));
            return;
        }

        if (!isset($this->args[2])) {
            $this->out(h('Kein Feld Übergeben.'));
            return;
        }

        if (!isset($this->args[3])) {
            $this->out(h('Keinen neuen Namen Übergeben.'));
            return;
        }

        $Test = $this->_CopyCheckMasterDropdownId();

        if ($Test === false) {
            $this->out(h('Keine gültige MasterDropdownId Übergeben.'));
            return;
        }

        $Data = $this->_CopyMasterDropdown();
        $Data = $this->_CopyMasterDropdownDependencyField($Data);
        $Data = $this->_CopyMasterDropdownDependency($Data);
        $Data = $this->_CopyMasterDropdownSave($Data);
        $Data = $this->_CopyMasterDropdownRights($Data);

    }

    protected function _CopyMasterDropdownRights($data)
    {

        $data = $this->_SaveDropdownsMastersReports($data);
        $data = $this->_SaveDropdownsMastersTestingcomps($data);
        $data = $this->_SaveDropdownsMastersTestingmethods($data);
        $data = $this->_SaveDropdownsMastersTopprojects($data);


        return $data;
    }

    protected function _SaveRigths($data, $RightsTable, $RightsField)
    {

        $NewId = $data['DropdownsMaster']['id'];
        $OldId = $this->args[0];

        App::uses($RightsTable, 'Model');
        $posts = ClassRegistry::init($RightsTable);

        $Conditions = array(
            'conditions' => array(
                $RightsTable . '.dropdowns_masters_id' => $OldId,
            ),
        );

        $Table = $posts->find('all', $Conditions);

        if (count($Table) == 0)
            return $data;

        $Table = Hash::extract($Table, '{n}.' . $RightsTable . '.' . $RightsField);

        $InsertAll = array();

        foreach ($Table as $key => $value) {

            $Insert = array(
                $RightsField           => $value,
                'dropdowns_masters_id' => $NewId,
            );

            $Conditions = array('conditions' => $Insert);

            $Check = $posts->find('all', $Conditions);

            if (count($Check) > 0)
                continue;

            $InsertAll[] = $Insert;

        }

        if (count($InsertAll) > 0) {
            $posts->saveMany($InsertAll, array('deep' => true));
        }

        return $data;
    }

    protected function _SaveDropdownsMastersReports($data)
    {

        $RightsTable = 'DropdownsMastersReport';
        $RightsField = 'report_id';

        $data = $this->_SaveRigths($data, $RightsTable, $RightsField);

        return $data;
    }

    protected function _SaveDropdownsMastersTestingcomps($data)
    {

        $RightsTable = 'DropdownsMastersTestingcomp';
        $RightsField = 'testingcomp_id';

        $data = $this->_SaveRigths($data, $RightsTable, $RightsField);

        return $data;
    }

    protected function _SaveDropdownsMastersTestingmethods($data)
    {

        $RightsTable = 'DropdownsMastersTestingmethod';
        $RightsField = 'testingmethod_id';

        $data = $this->_SaveRigths($data, $RightsTable, $RightsField);

        return $data;
    }

    protected function _SaveDropdownsMastersTopprojects($data)
    {

        $RightsTable = 'DropdownsMastersTopproject';
        $RightsField = 'topproject_id';

        $data = $this->_SaveRigths($data, $RightsTable, $RightsField);

        return $data;
    }

    protected function _CopyCheckMasterDropdownId()
    {
        $MasterDropdownId = $this->args[0];

        App::uses('DropdownsMaster', 'Model');
        $posts = ClassRegistry::init('DropdownsMaster');

        $Conditions = array(
            'conditions' => array(
                'DropdownsMaster.id' => $MasterDropdownId,
            ),
        );

        $DropdownsMaster = $posts->find('first', $Conditions);

        if (count($DropdownsMaster) == 0) {
            return false;
        }
    }

    protected function _CopyMasterDropdown()
    {
        App::uses('DropdownsMaster', 'Model');
        $posts = ClassRegistry::init('DropdownsMaster');

        $MoveId = $this->args[0];

        $Conditions = array(
            'conditions' => array(
                'DropdownsMaster.id' => $MoveId,
            ),
        );

        $Dropdown = $posts->find('first', $Conditions);

        if (count($Dropdown) == 0) {
            return 0;
        }

        return $Dropdown;
    }

    protected function _CopyMasterDropdownDependencyField($data)
    {

        App::uses('DropdownsMastersDependenciesField', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersDependenciesField');

        $posts->recursive = -1;

        $Conditions = array(
            'conditions' => array(
                'DropdownsMastersDependenciesField.dropdowns_masters_id' => $data['DropdownsMaster']['id'],
            ),
        );

        $DropdownsMastersDependenciesField = $posts->find('all', $Conditions);

        if (count($DropdownsMastersDependenciesField) == 0)
            return false;

        $data['DropdownsMastersDependenciesField'] = $DropdownsMastersDependenciesField;

        return $data;
    }

    protected function _CopyMasterDropdownDependency($data)
    {

        if (!isset($data['DropdownsMastersDependenciesField']))
            return false;
        if (count($data['DropdownsMastersDependenciesField']) == 0)
            return false;

        App::uses('DropdownsMastersDependency', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersDependency');

        $output = array();

        foreach ($data['DropdownsMastersData'] as $key => $value) {

            $input = $value;

            $input['id']                   = 0;
            $input['dropdowns_masters_id'] = 0;

            $output[$key]['DropdownsMastersData'] = $input;

            foreach ($data['DropdownsMastersDependenciesField'] as $_key => $_value) {

                $Conditions = array(
                    'conditions' => array(
                        'DropdownsMastersDependency.dropdowns_masters_id'      => $data['DropdownsMaster']['id'],
                        'DropdownsMastersDependency.dropdowns_masters_data_id' => $value['id'],
                        'DropdownsMastersDependency.field'                     => $_value['DropdownsMastersDependenciesField']['field'],
                    ),
                );

                $DropdownsMastersDependency = $posts->find('all', $Conditions);

                if (count($DropdownsMastersDependency) == 0)
                    continue;

                $DropdownsMastersDependency = Hash::extract($DropdownsMastersDependency, '{n}.DropdownsMastersDependency');

                $output[$key]['DropdownsMastersDependency'][$_value['DropdownsMastersDependenciesField']['field']] = $DropdownsMastersDependency;


            }
        }

        unset($data['Topproject']);
        unset($data['Report']);
        unset($data['Testingmethod']);
        unset($data['Testingcomp']);

        $data['DropdownsMastersData'] = $output;

        return $data;
    }

    protected function _CopyMasterDropdownSave($data)
    {

        $data = $this->_CopyMasterDropdownSaveMaster($data);
        $data = $this->_CopyMasterDropdownSaveDependencyFields($data);
        $data = $this->_CopyMasterDropdownSaveMasterData($data);
        $data = $this->_CopyMasterDropdownSaveDependencies($data);

        return $data;
    }

    protected function _CopyMasterDropdownSaveMaster($data)
    {

        $modul = $this->args[1];
        $feld  = $this->args[2];
        $name  = $this->args[3];

        $data['DropdownsMaster']['id']    = 0;
        $data['DropdownsMaster']['name']  = $name;
        $data['DropdownsMaster']['modul'] = $modul;
        $data['DropdownsMaster']['field'] = $feld;

        unset($data['DropdownsMaster']['created']);
        unset($data['DropdownsMaster']['modified']);

        App::uses('DropdownsMaster', 'Model');
        $posts = ClassRegistry::init('DropdownsMaster');

        $Conditions = array(
            'conditions' => array(
                'DropdownsMaster.name'  => $data['DropdownsMaster']['name'],
                'DropdownsMaster.modul' => $data['DropdownsMaster']['modul'],
                'DropdownsMaster.field' => $data['DropdownsMaster']['field'],
            ),
        );

        $Check = $posts->find('first', $Conditions);

        if (count($Check) > 0) {

            $data['DropdownsMaster'] = $Check['DropdownsMaster'];
            return $data;

        }

        $posts->save($data['DropdownsMaster']);

        $Conditions = array(
            'conditions' => array(
                'DropdownsMaster.id' => $posts->getLastInsertID(),
            ),
        );

        $posts->recursive = -1;

        $Dropdown = $posts->find('first', $Conditions);

        $data['DropdownsMaster'] = $Dropdown['DropdownsMaster'];

        return $data;

    }

    protected function _CopyMasterDropdownSaveDependencyFields($data)
    {

        if (!isset($this->args[4]))
            return $data;

        $fields = explode(',', $this->args[4]);
        $Id     = $data['DropdownsMaster']['id'];


        if (count($fields) > count($data['DropdownsMastersDependenciesField'])) {

            $this->out(h('Es wurden zuviele untergeordnete Felder für das zu kopierende Feld angegeben.'));
            return $data;

        }

        App::uses('DropdownsMastersDependenciesField', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersDependenciesField');

        foreach ($fields as $key => $value) {

            $Conditions = array(
                'conditions' => array(
                    'DropdownsMastersDependenciesField.dropdowns_masters_id' => $Id,
                    'DropdownsMastersDependenciesField.field'                => $value,
                ),
            );

            $Check = $posts->find('first', $Conditions);

            if (count($Check) > 0)
                continue;

            $Insert['id']                   = 0;
            $Insert['testingcomp_id']       = $data['DropdownsMastersDependenciesField'][$key]['DropdownsMastersDependenciesField']['testingcomp_id'];
            $Insert['dropdowns_masters_id'] = $Id;
            $Insert['user_id']              = $data['DropdownsMastersDependenciesField'][$key]['DropdownsMastersDependenciesField']['user_id'];
            $Insert['field']                = $value;

            $posts->save($Insert);

        }

        $posts->recursive = -1;

        $Conditions = array(
            'conditions' => array(
                'DropdownsMastersDependenciesField.dropdowns_masters_id' => $Id,
            ),
        );

        $DropdownsMastersDependenciesField = $posts->find('all', $Conditions);

        $data['DropdownsMastersDependenciesField'] = $DropdownsMastersDependenciesField;

        return $data;
    }

    protected function _CopyMasterDropdownSaveMasterData($data)
    {

        $modul = $this->args[1];
        $feld  = $this->args[2];
        $name  = $this->args[3];

        $Id = $data['DropdownsMaster']['id'];

        App::uses('DropdownsMastersData', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersData');

        foreach ($data['DropdownsMastersData'] as $key => $value) {

            $Conditions = array(
                'conditions' => array(
                    'DropdownsMastersData.dropdowns_masters_id' => $Id,
                    'DropdownsMastersData.value'                => $value['DropdownsMastersData']['value'],
                ),
            );

            $Check = $posts->find('first', $Conditions);

            if (count($Check) > 0) {
                $data['DropdownsMastersData'][$key]['DropdownsMastersData'] = $Check['DropdownsMastersData'];
                continue;
            }

            $value['DropdownsMastersData']['dropdowns_masters_id'] = $Id;

            $posts->save($value['DropdownsMastersData']);

            $data['DropdownsMastersData'][$key]['DropdownsMastersData']['dropdowns_masters_id'] = $Id;
            $data['DropdownsMastersData'][$key]['DropdownsMastersData']['id']                   = $posts->getLastInsertID();

            if (!isset($value['DropdownsMastersDependency']))
                continue;
            if (count($value['DropdownsMastersDependency']) == 0)
                continue;

        }

        return $data;

    }

    protected function _CopyMasterDropdownSaveDependencies($data)
    {

        if (!isset($this->args[4]))
            return $data;

        $Id = $data['DropdownsMaster']['id'];

        App::uses('DropdownsMastersDependency', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersDependency');


        foreach ($data['DropdownsMastersData'] as $key => $value) {

            if (!isset($value['DropdownsMastersDependency']))
                continue;

            $x = 0;

            foreach ($value['DropdownsMastersDependency'] as $_key => $_value) {

                foreach ($_value as $__key => $__value) {

                    $__value['id']                        = 0;
                    $__value['dropdowns_masters_data_id'] = $value['DropdownsMastersData']['id'];
                    $__value['dropdowns_masters_id']      = $Id;
                    $__value['field']                     = $data['DropdownsMastersDependenciesField'][$x]['DropdownsMastersDependenciesField']['field'];

                    $Conditions = array(
                        'conditions' => array(
                            'DropdownsMastersDependency.dropdowns_masters_id'      => $__value['dropdowns_masters_id'],
                            'DropdownsMastersDependency.dropdowns_masters_data_id' => $__value['dropdowns_masters_data_id'],
                            'DropdownsMastersDependency.value'                     => $__value['value'],
                            'DropdownsMastersDependency.field'                     => $__value['field'],
                        ),
                    );

                    $Check = $posts->find('first', $Conditions);

                    if (count($Check) > 0)
                        continue;

                    $posts->save($__value);

                }

                $x++;
            }

        }

        return $data;
    }

    public function MoveDropdownToMasterDropdown()
    {
        if (!isset($this->args[0])) {
            $this->out(h('Kein Model Übergeben.'));
            return;
        }

        if (!isset($this->args[1])) {
            $this->out(h('Kein Feld Übergeben.'));
            return;
        }

        if (!isset($this->args[2])) {
            $this->out(h('Keine MasterDropdownId Übergeben.'));
            return;
        }

        $Test = $this->_CheckMasterDropdownId();

        if ($Test === false) {
            $this->out(h('Keine gültige MasterDropdownId Übergeben.'));
            return;
        }

        $Data['DropdownMasterId'] = $this->_GetMasterDropdown();

        $Data = $this->_GetMasterDropdownData($Data);
        $Data = $this->_GetDropdownDependencies($Data);
        $Data = $this->_GetMasterDropdownDataDependencies($Data);
        $Data = $this->_InsertMasterDropdownData($Data);
        $Data = $this->_InsertMasterDropdownDependencies($Data);

        //        pr($Data);
    }

    protected function _CheckMasterDropdownId()
    {
        $MasterDropdownId = $this->args[2];

        App::uses('DropdownsMaster', 'Model');
        $posts = ClassRegistry::init('DropdownsMaster');

        $Conditions = array(
            'conditions' => array(
                'DropdownsMaster.id' => $MasterDropdownId,
            ),
        );

        $DropdownsMaster = $posts->find('first', $Conditions);

        if (count($DropdownsMaster) == 0) {
            return false;
        }
    }

    protected function _GetMasterDropdown()
    {
        App::uses('Dropdown', 'Model');
        $posts = ClassRegistry::init('Dropdown');

        $MoveModel = $this->args[0];
        $MoveField = $this->args[1];

        $Conditions = array(
            'conditions' => array(
                'Dropdown.model' => $MoveModel,
                'Dropdown.field' => $MoveField,
            ),
        );

        $Dropdown = $posts->find('first', $Conditions);

        if (count($Dropdown) == 0) {
            return 0;
        }

        return $Dropdown['Dropdown']['id'];
    }

    protected function _GetMasterDropdownData($Data)
    {
        if ($Data['DropdownMasterId'] == 0) {
            return $Data;
        }

        App::uses('DropdownsValue', 'Model');
        $posts = ClassRegistry::init('DropdownsValue');

        $Conditions = array(
            'fields'     => array('id', 'discription'),
            'conditions' => array(
                'DropdownsValue.dropdown_id' => $Data['DropdownMasterId'],
            ),
        );

        $posts->recursive = -1;

        $DropdownsValue = $posts->find('list', $Conditions);

        $Data['DropdownsValue'] = $DropdownsValue;

        return $Data;
    }

    protected function _GetDropdownDependencies($Data)
    {
        if ($Data['DropdownMasterId'] == 0) {
            return $Data;
        }
        if (count($Data['DropdownsValue']) == 0) {
            return $Data;
        }

        $MoveField = $this->args[1];

        App::uses('Dependency', 'Model');
        $posts = ClassRegistry::init('Dependency');

        $posts->recursive = -1;

        foreach ($Data['DropdownsValue'] as $key => $value) {
            $Conditions = array(
                //            'fields' => array('id','discription'),
                'conditions' => array(
                    'Dependency.dropdowns_value_id' => $key,
                    //            'Dependency.field' => $MoveField,
                ),
            );

            $DropdownsValue = $posts->find('all', $Conditions);

            if (count($DropdownsValue) == 0) {
                continue;
            }

            $Data['Dependency'][$key] = $DropdownsValue;
        }

        return $Data;
    }

    protected function _GetMasterDropdownDataDependencies($Data)
    {
        if ($Data['DropdownMasterId'] == 0) {
            return $Data;
        }
        if (count($Data['DropdownsValue']) == 0) {
            return $Data;
        }

        $MoveField = $this->args[1];

        App::uses('DropdownsMastersDependency', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersDependency');

        foreach ($Data['DropdownsValue'] as $key => $value) {
            $Conditions = array(
                //            'fields' => array('id','discription'),
                'conditions' => array(
                    'DropdownsMastersDependency.dropdowns_masters_id'      => $Data['DropdownMasterId'],
                    'DropdownsMastersDependency.dropdowns_masters_data_id' => $key,
                    'DropdownsMastersDependency.field'                     => $MoveField,
                ),
            );

            $DropdownsMastersDependency = $posts->find('all', $Conditions);

            if (count($DropdownsMastersDependency) == 0) {
                continue;
            }

            $Data['DropdownsMastersDependency'][$key] = $DropdownsMastersDependency;
        }


        return $Data;
    }

    protected function _InsertMasterDropdownData($Data)
    {
        if (count($Data['DropdownsValue']) == 0) {
            return $Data;
        }

        $MasterDropdownId = $this->args[2];

        App::uses('DropdownsMastersData', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersData');

        foreach ($Data['DropdownsValue'] as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $Insert = array();

            $Insert = array(
                'id'                   => 0,
                'dropdowns_masters_id' => intval($MasterDropdownId),
                'status'               => 0,
                'value'                => $value,
            );

            $Conditions = array(
                'conditions' => array(
                    'DropdownsMastersData.dropdowns_masters_id' => $Insert['dropdowns_masters_id'],
                    'DropdownsMastersData.value'                => $Insert['value'],
                ),
            );

            $Check = $posts->find('first', $Conditions);

            if (count($Check) > 0) {
                continue;
            }

            if ($posts->save($Insert)) {
                pr($Insert);
                pr($posts->getLastInsertID());
                $this->out(h('Insert Okay.'));
            } else {
                $this->out(h('Keine Insert.'));
            }
        }

        return $Data;
    }

    protected function _InsertMasterDropdownDependencies($Data)
    {
        $Data = $this->_UpdateMasterDropdown($Data);
        $Data = $this->_InsertMastersDependenciesFields($Data);
        $Data = $this->_InsertMastersDependencies($Data);

        return $Data;
    }

    protected function _UpdateMasterDropdown($Data)
    {
        if (!isset($Data['Dependency'])) {
            return $Data;
        }
        if (count($Data['Dependency']) == 0) {
            return $Data;
        }

        $Id = $this->args[2];

        App::uses('DropdownsMaster', 'Model');
        $posts = ClassRegistry::init('DropdownsMaster');

        $Update = array(
            'id'           => $Id,
            'dependencies' => 1,
        );

        $posts->save($Update);


        return $Data;
    }

    protected function _InsertMastersDependenciesFields($Data)
    {
        if (!isset($Data['Dependency'])) {
            return $Data;
        }
        if (count($Data['Dependency']) == 0) {
            return $Data;
        }

        $Id    = $this->args[2];
        $Field = $this->args[1];

        App::uses('DropdownsMastersDependenciesField', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersDependenciesField');

        foreach ($Data['Dependency'] as $key => $value) {
            if (!isset($value[0]['Dependency'])) {
                continue;
            }

            $DependensyField = $value[0]['Dependency']['field'];

            break;
        }

        if (!isset($DependensyField)) {
            return $Data;
        }

        $Conditions = array(
            'conditions' => array(
                'DropdownsMastersDependenciesField.dropdowns_masters_id' => $Id,
                'DropdownsMastersDependenciesField.field'                => $DependensyField,
            ),
        );

        $Check = $posts->find('first', $Conditions);

        $Data['DropdownDependencyField'] = $DependensyField;

        if (count($Check) > 0) {
            return $Data;
        }

        $Insert = array(
            'id'                   => 0,
            'testingcomp_id'       => 1,
            'dropdowns_masters_id' => $Id,
            'user_id'              => 1,
            'field'                => $DependensyField,
        );

        $posts->save($Insert);

        return $Data;
    }

    protected function _InsertMastersDependencies($Data)
    {
        if (!isset($Data['Dependency'])) {
            return $Data;
        }
        if (count($Data['Dependency']) == 0) {
            return $Data;
        }

        $Id    = $this->args[2];
        $Field = $this->args[1];

        App::uses('DropdownsMastersDependency', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersDependency');

        foreach ($Data['Dependency'] as $key => $value) {
            if (count($value) == 0) {
                continue;
            }

            foreach ($value as $_key => $_value) {
                //            pr($key);
//            pr($_key);

                $DropdownValue         = $this->_GetDropdownsValueValue($_value['Dependency']);
                $MasterDropdownValueId = $this->_GetMasterDropdownsValueValue($DropdownValue);

                $Conditions = array(
                    'conditions' => array(
                        'DropdownsMastersDependency.dropdowns_masters_id' => $Id,
                        'DropdownsMastersDependency.field'                => $_value['Dependency']['field'],
                        'DropdownsMastersDependency.value'                => $_value['Dependency']['value'],
                    ),
                );

                $Check = $posts->find('first', $Conditions);

                if (count($Check) == 1) {
                    continue;
                }

                $Insert = array(
                    'id'                        => 0,
                    'testingcomp_id'            => 1,
                    'dropdowns_masters_id'      => $Id,
                    'dropdowns_masters_data_id' => $MasterDropdownValueId,
                    'field'                     => $_value['Dependency']['field'],
                    'value'                     => $_value['Dependency']['value'],
                    'global'                    => 0,
                );

                $posts->save($Insert);
            }
        }


        return $Data;
    }

    protected function _GetMasterDropdownsValueValue($Data)
    {
        $Id = $this->args[2];

        App::uses('DropdownsMastersData', 'Model');
        $posts = ClassRegistry::init('DropdownsMastersData');

        $Conditions = array(
            'conditions' => array(
                'DropdownsMastersData.dropdowns_masters_id' => $Id,
                'DropdownsMastersData.value'                => $Data,
            ),
        );

        $DropdownsMastersData = $posts->find('first', $Conditions);

        if (count($DropdownsMastersData) == 0) {
            return 0;
        }

        return $DropdownsMastersData['DropdownsMastersData']['id'];
    }

    protected function _GetDropdownsValueValue($Data)
    {
        App::uses('DropdownsValue', 'Model');
        $posts = ClassRegistry::init('DropdownsValue');

        $Conditions = array(
            //          'fields' => array('id','discription'),
            'conditions' => array(
                'DropdownsValue.id' => $Data['dropdowns_value_id'],
            ),
        );

        $posts->recursive = -1;

        $DropdownsValue = $posts->find('first', $Conditions);

        return $DropdownsValue['DropdownsValue']['discription'];
    }

    public function AlstomExaminerImport()
    {
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');

        $Model = 'AlstomExaminier';

        App::uses($Model, 'Model');
        $AlstomExaminier = ClassRegistry::init($Model);

        $Shema = $AlstomExaminier->schema();
        unset($Shema['id']);
        $Rows = array_keys($Shema);

        $dir   = new Folder(Configure::read('document_folder') . 'examiner' . DS);
        $files = $dir->find('.*\.csv');

        if (count($files) == 0) {
            $this->out(h('Keine Datei gefunden.'));
            return false;
        }

        foreach ($files as $file) {
            $file = new File($dir->pwd() . DS . $file);
            $Row  = 0;

            $this->out(h('Importdatei gefunden.'));
            $this->out(h('Datenimport gestartet...'));

            if (($handle = fopen($file->path, "r")) !== false) {
                while (($data = fgetcsv($handle, 0, ";")) !== false) {
                    $Insert = array();

                    foreach ($Rows as $key => $value) {
                        $Insert[$value] = $data[$key];
                    }

                    if (empty($Insert['testingmethod'])) {
                        continue;
                    }
                    if ($Insert['testingmethod'] == 'Autorisierung') {
                        continue;
                    }

                    $AlstomExaminier->create();
                    $AlstomExaminier->save($Insert);
                }

                fclose($handle);
            }

            $file->close();

            $this->out(h('... Datenimport beendet.'));

            break;
        }
    }

    public function AlstomExaminerDelete()
    {
        $Query =
            '
        TRUNCATE examiners;
        TRUNCATE examinermonitoringfiles;
        TRUNCATE certificates;
        TRUNCATE certificate_datas;
        TRUNCATE certifcatefiles;
        TRUNCATE eyecheckfiles;
        TRUNCATE eyechecks;
        TRUNCATE eyecheck_datas;
        TRUNCATE examiner_monitorings;
        TRUNCATE examiner_monitoring_datas;
        TRUNCATE examinerfiles;
        ';

        $test = $this->Topprojects->query($Query);

        if ($test == true) {
            $this->out(h('Tabellen geleert.'));
        } else {
            $this->out(h('Fehler beim leeren der Tabellen'));
        }
    }

    public function AlstomExaminer()
    {
        $Examiner        = ClassRegistry::init('Examiner');
        $AlstomExaminier = ClassRegistry::init('AlstomExaminier');
        $Data            = $AlstomExaminier->find('all', array('conditions' => array('AlstomExaminier.name !=' => '')));

        $TestingcompId = 1;

        $Examiner->recursive = -1;

        foreach ($Data as $key => $value) {
            $value = $this->DateSql($value);

            $Insert = array();

            $Insert['name']           = $value['AlstomExaminier']['name'];
            $Insert['first_name']     = $value['AlstomExaminier']['first_name'];
            $Insert['working_place']  = $value['AlstomExaminier']['working_place'];
            $Insert['testingcomp_id'] = $TestingcompId;
            $Insert['da_no']          = $value['AlstomExaminier']['id_no'];
            $Insert['date_of_birth']  = $value['AlstomExaminier']['date_of_birth'];
            $Insert['date_of_birth']  = $value['AlstomExaminier']['date_of_birth'];

            if (empty($Insert['da_no'])) {
                $Insert['da_no'] = '-';
            }

            $ModelName = get_class($Examiner);

            $Conditions = array(
                'conditions' => array(
                    $ModelName . '.name' => $Insert['name'],
                    $ModelName . '.first_name' => $Insert['first_name'],
                    $ModelName . '.date_of_birth' => $Insert['date_of_birth']
                )
            );

            $Test = $Examiner->find('first', $Conditions);

            if (count($Test) > 0) {
                continue;
            }
            $Insert['date_of_birth'] = $value['AlstomExaminier']['date_of_birth'];

            $Examiner->create();
            $Examiner->save($Insert);
        }

        $this->AlstomQualifications();
    }

    public function AlstomQualifications()
    {
        $AlstomExaminier = ClassRegistry::init('AlstomExaminier');
        $Data            = $AlstomExaminier->find('all');

        $Exam = $this->_CollectExaminerData($Data);
        $Test = $this->_Save($Exam);
    }

    protected function _Save($Data)
    {
        $Certificate     = ClassRegistry::init('Certificate');
        $CertificateData = ClassRegistry::init('CertificateData');

        $Eyecheck     = ClassRegistry::init('Eyecheck');
        $EyecheckData = ClassRegistry::init('EyecheckData');

        foreach ($Data as $key => $value) {
            $test = $this->_SaveCertfications($Certificate, $CertificateData, $value['ZfP']);
            $test = $this->_SaveEyeCheck($Eyecheck, $EyecheckData, $value['Sehtest']);
        }

        $Test = true;

        return $Test;
    }

    protected function _SaveCertfications($Model, $SubModel, $Data)
    {
        $ModelName = get_class($Model);

        foreach ($Data as $key => $value) {
            if (empty($value['recertification'])) {
                continue;
            }

            if (empty($value['certificat'])) {
                $value['certificat'] = '-';
            }

            $Test = $Model->find(
                'first',
                array(
                    'conditions' => array(
                        $ModelName . '.examiner_id' => $value['examiner_id'],
                        $ModelName . '.certificat' => $value['certificat'],
                        $ModelName . '.testingmethod' => $value['testingmethod'],
                        $ModelName . '.level' => $value['level']
                    )
                )
            );

            // von der Rezertifizierung 10 Jahre und ein Tag zurückrechnen
            // um den Startpunkt festzulegen
            $dt = new DateTime($value['erneuerung']);
            $dt->modify('-5 years');
            $dtn = $dt->format('Y-m-d');
            /*
            $dt = new DateTime($dtn);
            $dt->modify('-1 day');
            $dtn = $dt->format('Y-m-d');
            */
            $value['first_registration'] = $dtn;

            if (count($Test) > 0) {
                continue;
            }

            $value['sector'] = Inflector::camelize(strtolower($value['sector']));

            if ($value['sector'] == 'Is') {
                $value['recertification_in_year'] = 10;
                $value['renewal_in_year']         = 5;
            }
            if ($value['sector'] == 'Ir') {
                $value['recertification_in_year'] = 5;
                $value['renewal_in_year']         = 5;
            }

            $Model->create();
            $Test = $Model->save($value);

            $value['certificate_id']        = $Model->getLastInsertID();
            $value['certified']             = 1;
            $value['certified_datied_date'] = $value['first_registration'];

            $SubModel->create();
            $Test = $SubModel->save($value);
        }

        $Test = true;

        return $Test;
    }

    protected function _SaveEyeCheck($Model, $SubModel, $Data)
    {
        $ModelName = get_class($Model);

        foreach ($Data as $key => $value) {
            $Test = $Model->find(
                'first',
                array(
                    'conditions' => array(
                        $ModelName . '.examiner_id' => $value['examiner_id'],
                    )
                )
            );

            if (count($Test) > 0) {
                continue;
            }

            $Model->create();
            $Test = $Model->save($value);

            $value['certificate_id']      = $Model->getLastInsertID();
            $value['certified']           = 1;
            $value['certified_date']      = $value['first_registration'];
            $value['first_certification'] = 1;

            $SubModel->create();
            $Test = $SubModel->save($value);
        }

        $Test = true;

        return $Test;
    }

    protected function _CollectExaminerData($Data)
    {
        $Examiner  = ClassRegistry::init('Examiner');
        $ThirdPart = 'DGZfP';

        $Examiner->recursive = -1;

        $Exam = array();

        foreach ($Data as $key => $value) {
            if (!empty($value['AlstomExaminier']['name'])) {
                $ThisExam = $Examiner->find(
                    'first',
                    array(
                        'conditions' => array(
                            'Examiner.name'       => $value['AlstomExaminier']['name'],
                            'Examiner.first_name' => $value['AlstomExaminier']['first_name'],
                        )
                    )
                );

                if (count($ThisExam) == 0) {
                    continue;
                }
                $IdNo = $value['AlstomExaminier']['id_no'];
            }

            if (!isset($ThisExam['Examiner'])) {
                continue;
            }
            if (trim($value['AlstomExaminier']['testingmethod']) == 'Sehtest') {
                $value['AlstomExaminier']['examiner_id']             = $ThisExam['Examiner']['id'];
                $value['AlstomExaminier']['certified_date']          = $value['AlstomExaminier']['certificate'];
                $value['AlstomExaminier']['first_registration']      = $value['AlstomExaminier']['certificate'];
                $value['AlstomExaminier']['recertification_in_year'] = 1;
                $value['AlstomExaminier']['certified']               = 1;
                $value['AlstomExaminier']['horizon']                 = 1;

                $value = $this->DateSql($value);

                $Exam[$ThisExam['Examiner']['id']]['Sehtest'][$value['AlstomExaminier']['id']] = $value['AlstomExaminier'];
            } else {
                if ($value['AlstomExaminier']['sector'] == 'Is') {
                    $value['AlstomExaminier']['recertification_in_year'] = 10;
                    $value['AlstomExaminier']['renewal_in_year']         = 5;
                }
                if ($value['AlstomExaminier']['sector'] == 'Ir') {
                    $value['AlstomExaminier']['recertification_in_year'] = 5;
                    $value['AlstomExaminier']['renewal_in_year']         = 5;
                }

                $value['AlstomExaminier']['horizon'] = 6;

                $testingmethod = $value['AlstomExaminier']['testingmethod'];

                $testingmethod = str_replace(' ', '', $testingmethod);

                $testingmethod = str_split(strtolower($testingmethod));

                $Level  = array_pop($testingmethod);
                $Method = Inflector::camelize(implode($testingmethod));

                $value['AlstomExaminier']['testingmethod'] = $Method;
                $value['AlstomExaminier']['level']         = $Level;

                $value['AlstomExaminier']['supervisor']              = 0;
                $value['AlstomExaminier']['deleted']                 = 0;
                $value['AlstomExaminier']['active']                  = 1;
                $value['AlstomExaminier']['user_id']                 = 1;
                $value['AlstomExaminier']['certificate_data_active'] = 1;
                $value['AlstomExaminier']['first_registration']      = $value['AlstomExaminier']['certificate'];
                $value['AlstomExaminier']['exam_date']               = $value['AlstomExaminier']['quali_date'];
                $value['AlstomExaminier']['examiner_id']             = $ThisExam['Examiner']['id'];
                $value['AlstomExaminier']['certificat']              = $IdNo;
                $value['AlstomExaminier']['third_part']              = $ThirdPart;

                $value = $this->DateSql($value);


                $Exam[$ThisExam['Examiner']['id']]['ZfP'][$value['AlstomExaminier']['id']] = $value['AlstomExaminier'];

                unset($Exam[$ThisExam['Examiner']['id']]['ZfP'][$value['AlstomExaminier']['id']]['id']);
            }
        }

        return $Exam;
    }

    public function DateSql($Data)
    {
        $DateArray = array('certified_date' => true, 'date_of_birth' => true, 'first_registration' => true, 'exam_date' => true, 'erneuerung' => true);

        foreach ($Data['AlstomExaminier'] as $key => $value) {
            if (!isset($DateArray[$key])) {
                continue;
            }

            $date = new DateTime($value);

            $Data['AlstomExaminier'][$key] = $date->format('Y-m-d');
        }

        return $Data;
    }

    public function MeasuringPoints()
    {
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');
        App::uses('MbqMeasuringPoint', 'Model');
        App::import('Vendor', 'measuring_points');

        pr(APP);
        return;
        $dir = new Folder(APP . 'measurings' . DS);

        $MbqMeasuringPoint = ClassRegistry::init('MbqMeasuringPoint');

        $Data = $MbqMeasuringPoint->find('all');

        $custom_layout = array(76.2, 50.8);
        $tcpdf         = new TCPDF('L', PDF_UNIT, $custom_layout, true, 'UTF-8', false);

        $tcpdf->setPrintHeader(false);
        $tcpdf->setPrintFooter(false);
        $tcpdf->SetAutoPageBreak(false, 0);

        $tcpdf->SetFont('calibri', '', 12);
        $tcpdf->SetFillColor(210, 210, 210);

        foreach ($Data as $key => $value) {
            $tcpdf->setY(0);

            $tcpdf->AddPage();

            $l = 5;
            $y = $l;

            $tcpdf->MultiCell(
                0,
                0,
                trim($value['MbqMeasuringPoint']['ausruestung']),
                0,
                'L',
                0,
                0,
                5,
                $y,
                true,
                0,
                false,
                true,
                0,
                'T',
                true
            );

            $y += $l;

            $tcpdf->MultiCell(
                0,
                0,
                trim($value['MbqMeasuringPoint']['detail']),
                0,
                'L',
                0,
                0,
                5,
                $y,
                true,
                0,
                false,
                true,
                0,
                'T',
                true
            );

            $y += $l;

            $tcpdf->MultiCell(
                0,
                0,
                "Abmessung: " . trim($value['MbqMeasuringPoint']['abmessung']),
                0,
                'L',
                0,
                0,
                5,
                $y,
                true,
                0,
                false,
                true,
                0,
                'T',
                true
            );

            $y += $l;

            $tcpdf->MultiCell(
                0,
                0,
                "Messstelle/Lage: " . trim($value['MbqMeasuringPoint']['messtelle']) . " / " . trim($value['MbqMeasuringPoint']['lage']),
                0,
                'L',
                0,
                0,
                5,
                $y,
                true,
                0,
                false,
                true,
                0,
                'T',
                true
            );

            $y += $l;

            $tcpdf->MultiCell(
                0,
                0,
                "Zugang: " . trim($value['MbqMeasuringPoint']['zugang']),
                0,
                'L',
                0,
                0,
                5,
                $y,
                true,
                0,
                false,
                true,
                0,
                'T',
                true
            );

            $y += $l;

            $tcpdf->MultiCell(
                0,
                0,
                "Fenster: " . trim($value['MbqMeasuringPoint']['fenster']),
                0,
                'L',
                0,
                0,
                5,
                $y,
                true,
                0,
                false,
                true,
                0,
                'T',
                true
            );
        }

        $tcpdf->Output($dir->path . 'kuitti.pdf', 'F');
    }

    public function UpdateSql()
    {
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');
        App::uses('ClassRegistry', 'Utility');

        $this->_Sql();
        $this->_Rows();
        $this->_CreateAcl();
        $this->_CreateAclAco();
        $this->_DelFiles();
        $this->DelCache();

        $this->out('Ende');
    }

    protected function _Sql()
    {
        $dir   = new Folder(APP . 'update' . DS . 'sql' . DS);
        $files = $dir->find('.*\.sql');

        if (count($files) == 0) {
            return;
        }

        foreach ($files as $key => $file) {
            if (!file_exists($dir->path . $file)) {
                continue;
            }

            $Query = file_get_contents($dir->path . $file);

            $test = $this->Topprojects->query($Query);

            $this->out('File ' . $file);
        }
    }

    protected function _Rows()
    {
        $dir   = new Folder(APP . 'update' . DS . 'rows' . DS);
        $files = $dir->find('.*\.json');

        if (count($files) == 0) {
            return;
        }

        foreach ($files as $key => $file) {
            if (!file_exists($dir->path . $file)) {
                continue;
            }

            $json  = file_get_contents($dir->path . $file);
            $array = json_decode($json);

            $test = $this->_AddRow($array);

            if ($test == false) {
                $this->out('File ' . $file . ' keine Aktion');
            } else {
                $this->out('File ' . $file . ' Spalte angelegt');
            }
        }
    }

    protected function _AddRow($data)
    {
        $test = false;

        if (isset($data->modus)) {
            $test = $this->_AddRowSingle($data);
            return $test;
        } else {
            $test = $this->_AddRowMany($data);
        }


        return $test;
    }

    protected function _AddRowMany($data)
    {
        if (count($data) == 0) {
            return false;
        }

        $test = false;

        foreach ($data as $key => $value) {

            $test = $this->_AddRowSingle($value);
            if ($test == false) {
                return false;
            }
        }

        return $test;
    }

    protected function _AddRowSingletest($data)
    {
        pr($data);

        if ($data->modus != 'insert') {
            return false;
        }

        $Model = $data->model;
        $Field = $data->field;
        $Query = $data->string;

        App::uses($Model, 'Model');
        $posts = ClassRegistry::init($Model);

        $test = $posts->schema();

        if (!is_array($test)) {
            return false;
        }
        if (count($test) == 0) {
            return false;
        }

        if (isset($test[$Field])) {
            return;
        }

        $test = $posts->query($Query);

        return $test;
    }

    protected function _AddRowSingle($data)
    {
        //        if($data->modus != 'insert') return false;

        $Model = $data->model;
        $Field = $data->field;
        $Query = $data->string;

        App::uses('Topproject', 'Model');
        App::uses($Model, 'Model');

        $posts = ClassRegistry::init($Model);
        $table = $posts->table;

        $topproject = ClassRegistry::init('Topproject');
        $check      = $topproject->query("SHOW TABLES LIKE '" . $table . "'");

        if (count($check) == 0) {
            return false;
        }

        $test = $posts->schema();

        if (!is_array($test)) {
            return false;
        }
        if (count($test) == 0) {
            return false;
        }

        if (isset($test[$Field])) {
            return;
        }

        $test = $posts->query($Query);

        return $test;
    }

    protected function _CreateAclAco()
    {
        $AclModel              = ClassRegistry::init('Aco');
        $collection            = new ComponentCollection();
        $this->MyCakeComponent = $collection->load('Acl');

        $dir   = new Folder(APP . 'update' . DS . 'acos' . DS);
        $files = $dir->findRecursive('.*\.json');

        if (count($files) == 0) {
            return;
        }

        foreach ($files as $key => $file) {
            $json  = file_get_contents($file);
            $array = json_decode($json);

            if (count($array) == 0) {
                return;
            }

            foreach ($array as $key => $value) {
                if ($value->modus != 'grant') {
                    continue;
                }

                $aro    = array('model' => 'Roll', 'foreign_key' => $value->aro);
                $aco    = $value->aco;
                $action = $value->action;

                $test = $this->MyCakeComponent->allow($aro, $aco, $action);
            }
        }
    }

    protected function _CreateAcl()
    {
        $AclModel              = ClassRegistry::init('Aco');
        $collection            = new ComponentCollection();
        $this->MyCakeComponent = $collection->load('Acl');

        $dir   = new Folder(APP . 'update' . DS . 'acls' . DS);
        $files = $dir->findRecursive('.*\.json');

        if (count($files) == 0) {
            return;
        }

        foreach ($files as $key => $file) {
            $json  = file_get_contents($file);
            $array = json_decode($json);
            if (is_countable($array)) {
                if (count($array) == 0) {
                    continue;
                }
            } else {
                continue;
            }

            foreach ($array as $key => $value) {
                if ($value->modus != "create") {
                    continue;
                }

                $args = array($value->type, $value->model, $value->field);

                $class  = ucfirst($args[0]);
                $parent = $this->parseIdentifier($args[1]);

                if (!empty($parent) && $parent !== '/' && $parent !== 'root') {
                    $parent = $this->_getNodeId($class, $parent);
                } else {
                    $parent = null;
                }

                $data = $this->parseIdentifier($args[2]);

                if (is_string($data) && $data !== '/') {
                    $data = array('alias' => $data);
                } elseif (is_string($data)) {
                    $this->error(__d('cake_console', '/ can not be used as an alias!') . __d('cake_console', "	/ is the root, please supply a sub alias"));
                }

                $data['parent_id'] = $parent;

                $Query = "SELECT * FROM acos WHERE alias LIKE '" . $data['alias'] . "' AND parent_id = " . $data['parent_id'];

                $test = $this->Topprojects->query($Query);

                if (!empty($test)) {
                    continue;
                }

                $AclModel->create();

                if ($AclModel->save($data)) {
                    $aro    = array('model' => 'Roll', 'foreign_key' => 1);
                    $aco    = $args[1] . '/' . $args[2];
                    $action = '*';

                    $test = $this->MyCakeComponent->allow($aro, $aco, $action);

                    $this->out(__d('cake_console', "<success>New %s</success> '%s' created.", $class, $args[2]), 2);
                } else {
                    $this->err(__d('cake_console', "There was a problem creating a new %s '%s'.", $class, $args[2]));
                }
            }
        }
    }

    public function DelCache()
    {
        if (Cache::clear(true, '_cake_model_')) {
            $this->out('Cake cache model clear complete');
        } else {
            $this->out("Error : Cake model cache clear failed");
        }

        if (Cache::clear(true, '_cake_core_')) {
            $this->out('Cake persistent cache clear complete');
        } else {
            $this->out("Error : Cake persistent cache clear failed");
        }

        /*
        Cache::clear(true, 'default');
        Cache::clear(true, '_cake_core_');
        Cache::clear(true, '_cake_model_');
        $this->out('<info>The</info> "<error>deployer clear_cache</error>" <info>command has been executed successfully !</info>', 2);
        */
    }

    protected function _DelFiles()
    {
        if (!isset($this->args[0])) {
            return;
        }
        if ($this->args[0] != 'delete') {
            return;
        }

        $time = time();

        $rows = new Folder(APP . 'update' . DS . 'rows' . DS);
        $sql  = new Folder(APP . 'update' . DS . 'sql' . DS);
        $acl  = new Folder(APP . 'update' . DS . 'acls' . DS);
        $aco  = new Folder(APP . 'update' . DS . 'acos' . DS);

        $archiv_rows = new Folder(APP . 'update' . DS . 'archiv' . DS . $time . DS . 'rows' . DS, true, 0700);
        $archiv_sql  = new Folder(APP . 'update' . DS . 'archiv' . DS . $time . DS . 'sql' . DS, true, 0700);
        $archiv_acl  = new Folder(APP . 'update' . DS . 'archiv' . DS . $time . DS . 'acl' . DS, true, 0700);
        $archiv_aco  = new Folder(APP . 'update' . DS . 'archiv' . DS . $time . DS . 'aco' . DS, true, 0700);

        $acl->copy($archiv_acl->path);
        $aco->copy($archiv_aco->path);
        $rows->copy($archiv_rows->path);
        $sql->copy($archiv_sql->path);
        /*
        $rows->delete();
        $sql->delete();
        $acl->delete();
        $aco->delete();
        $rows = new Folder(APP . 'update' . DS . 'rows' . DS, true, 0700);
        $sql = new Folder(APP . 'update' . DS . 'sql' . DS, true, 0700);
        $acl = new Folder(APP . 'update' . DS . 'acls' . DS, true, 0700);
        $aco = new Folder(APP . 'update' . DS . 'acos' . DS, true, 0700);
        */
    }

    public function parseIdentifier($identifier)
    {
        if (preg_match('/^([\w]+)\.(.*)$/', $identifier, $matches)) {
            return array(
                'model'       => $matches[1],
                'foreign_key' => $matches[2],
            );
        }
        return $identifier;
    }

    protected function _getNodeId($class, $identifier)
    {
        $AcoModel = ClassRegistry::init('Aco');
        $node     = $AcoModel->node($identifier);

        if (empty($node)) {
            if (is_array($identifier)) {
                $identifier = var_export($identifier, true);
            }
            $this->error(__d('cake_console', 'Could not find node using reference "%s"', $identifier));
            return null;
        }
        return Hash::get($node, "0.{$class}.id");
    }

    protected function _dataVars($type = null)
    {
        if (!$type) {
            $type = $this->args[0];
        }
        $vars                 = array();
        $class                = ucwords($type);
        $vars['secondary_id'] = (strtolower($class) === 'aro') ? 'foreign_key' : 'object_id';
        $vars['data_name']    = $type;
        $vars['table_name']   = $type . 's';
        $vars['class']        = $class;
        return $vars;
    }

    protected function _UpdateDropdownMasters()
    {
        $this->loadModel('DropdownsMaster');
        $this->loadModel('Testingcomp');
        $this->loadModel('User');

        $this->DropdownsMaster->recursive = -1;
        $this->Testingcomp->recursive     = -1;
        $this->User->recursive            = -1;

        $DropdownsMaster = $this->DropdownsMaster->find('all');

        foreach ($DropdownsMaster as $key => $value) {
            $user = $this->User->find('first', array('conditions' => array('User.id' => $value['DropdownsMaster']['user_id'])));

            if (!empty($user)) {
                if (empty($value['DropdownsMaster']['field'])) {
                    $value['DropdownsMaster']['field'] = '-';
                }
                $value['DropdownsMaster']['testingcomp_id'] = intval($user['User']['testingcomp_id']);
                $this->DropdownsMaster->save($value['DropdownsMaster']);
            } else {
                echo 'Benutzer konnte nicht gefunden werden';
            }
        }
    }


    public function wackerdates()
    {
        $this->loadModel('Reportnumber');
        $this->loadModel('ReportRtGenerally');
        $this->loadModel('Sign');

        $this->Reportnumber->recursive = -1;
        $reportnumbers                 = $this->Reportnumber->find('all', array('conditions' => array('Reportnumber.created >' => '2022-03-01 00:58:25', 'Reportnumber.topproject_id' => 14, 'Reportnumber.testingmethod_id' => 1, 'delete' => 0, 'status >' => 0)));

        foreach ($reportnumbers as $key => $value) {
            $Generally = $this->ReportRtGenerally->find('first', array('conditions' => array('reportnumber_id' => $value['Reportnumber']['id'])));

            if ($Generally['ReportRtGenerally']['examiner_date'] <= $Generally['ReportRtGenerally']['date_of_test']) {
                $Sign1 = $this->Sign->find('first', array('conditions' => array('reportnumber_id' => $value['Reportnumber']['id'], 'Signatory' => 1)));
                if ($Generally['ReportRtGenerally']['examiner_date'] < $Generally['ReportRtGenerally']['date_of_test']) {
                    //  var_dump($value['Reportnumber']['number']);

                    if (!empty($Sign1)) {
                        $timestamp = '';
                        $timestamp = strtotime($Sign1['Sign']['created']);
                        $date1     = date('Y-m-d', $timestamp);

                        $Generally['ReportRtGenerally']['examiner_date'] = $date1;
                        //$value['Reportnumber']['status']= 0;
                        $this->ReportRtGenerally->save($Generally);
                        $this->Reportnumber->save($value);
                    }
                }

                $Sign2 = $this->Sign->find('first', array('conditions' => array('reportnumber_id' => $value['Reportnumber']['id'], 'Signatory' => 2)));
                if (!empty($Sign2) && empty($Generally['ReportRtGenerally']['supervisor_date'])) {
                    $timestamp = '';
                    $timestamp = strtotime($Sign2['Sign']['created']);
                    $date2     = date('Y-m-d', $timestamp);

                    $Generally['ReportRtGenerally']['supervisor_date'] = $date2;
                    //    $value['Reportnumber']['status']= 0;
                    $this->ReportRtGenerally->save($Generally);
                    $this->Reportnumber->save($value);
                }

                $Sign3 = $this->Sign->find('first', array('conditions' => array('reportnumber_id' => $value['Reportnumber']['id'], 'Signatory' => 3)));
                if (!empty($Sign3) && empty($Generally['ReportRtGenerally']['supervisor_company_date'])) {
                    $timestamp = '';
                    $timestamp = strtotime($Sign3['Sign']['created']);
                    $date3     = date('Y-m-d', $timestamp);

                    $Generally['ReportRtGenerally']['supervisor_company_date'] = $date3;
                    //      $value['Reportnumber']['status']= 0;
                    $this->ReportRtGenerally->save($Generally);
                    $this->Reportnumber->save($value);
                }

                $Sign4 = $this->Sign->find('first', array('conditions' => array('reportnumber_id' => $value['Reportnumber']['id'], 'Signatory' => 4)));
                if (!empty($Sign4) && empty($Generally['ReportRtGenerally']['third_part_date'])) {
                    $timestamp = '';
                    $timestamp = strtotime($Sign4['Sign']['created']);
                    $date4     = date('Y-m-d', $timestamp);

                    $Generally['ReportRtGenerally']['third_part_date'] = $date4;
                    //      $value['Reportnumber']['status']= 0;
                    $this->ReportRtGenerally->save($Generally);
                    $this->Reportnumber->save($value);
                }
            }
        }
    }

    /**
     * MoveDropdownToMasterDropdownAuto
     *
     * @return void
     */
    public function MoveDropdownToMasterDropdownAuto()
    {
        App::uses('Dropdowns', 'Model');
        $dropdownsModel       = ClassRegistry::init('Dropdowns');
        $dropdownsValuesModel = ClassRegistry::init('DropdownsValues');

        $progress = $this->helper('Progress');

        $this->out('Hole Dropdowns...');

        $dropdowns = $dropdownsModel->find('all');

        $this->out('Wandle um...');

        if (is_countable($dropdowns)) {
            $dropdownCount = count($dropdowns);
        }

        $progress->init(
            array(
                'total' => $dropdownCount,
                'width' => 100,
            )
        );

        foreach ($dropdowns as &$dropdown) {

            $currentDropdownId = $dropdown['Dropdowns']['id'];

            $DropdownsValues = $dropdownsValuesModel->find(
                'all',
                [
                    'conditions' => [
                        'dropdown_id' => $currentDropdownId,
                    ],
                ]
            );

            $dropdown['DropdownsValues'] = $DropdownsValues;

            if (strpos($dropdown['Dropdowns']['model'], 'Generally') !== false) {
                $dropdown['Dropdowns']['modul'] = 'generally_area';
            } elseif (strpos($dropdown['Dropdowns']['model'], 'Specific') !== false) {
                $dropdown['Dropdowns']['modul'] = 'specific_area';
            } elseif (strpos($dropdown['Dropdowns']['model'], 'Evaluation') !== false) {
                $dropdown['Dropdowns']['modul'] = 'evaluation_area';
            } else {
                $dropdown['Dropdowns']['modul'] = '%unkowwn%';
            }

            //Prüfen ob Abhängigkeiten vorhanden sind
            if (self::_checkDropdownHasDependencies($dropdown)) {
                $dropdown['Dropdowns']['dependencies'] = 1;
            } else {
                $dropdown['Dropdowns']['dependencies'] = 0;
            }

            if (self::_insertDropdownMasters($dropdown)) {
                if (!empty($dropdown['DropdownsValues'])) {
                    self::_insertDropdownMastersData($dropdown);
                    self::_insertDropdownMastersDependencies($dropdown);
                    self::_insertDropdownMastersReports($dropdown);
                    self::_insertDropdownMastersTestingcomps($dropdown);
                    self::_insertDropdownMastersTestingmethods($dropdown);
                    self::_insertDropdownMastersTopprojects($dropdown);
                }
            }

            $progress->increment(1);
            $progress->draw();
        }
    }

    private function _checkDropdownHasDependencies($dropdown)
    {
        $hasDependency = false;

        if (!empty($dropdown['DropdownsValues'])) {
            $dependenciesModel = ClassRegistry::init('Dependency');

            foreach ($dropdown['DropdownsValues'] as $DropdownValue) {
                $dependenciesCount = $dependenciesModel->find(
                    'count',
                    [
                        'conditions' => [
                            'Dependency.dropdown_id'        => $dropdown['Dropdowns']['id'],
                            'Dependency.dropdowns_value_id' => $DropdownValue['DropdownsValues']['id'],
                        ]
                    ],
                );

                if ($dependenciesCount > 0) {
                    $hasDependency = true;
                    break;
                }
            }
        }

        return $hasDependency;
    }

    /**
     * _insertDropdownMasters
     *
     * @param  mixed $Dropdown
     * @return void
     */
    private function _insertDropdownMasters(&$Dropdown)
    {
        $proceed = true;

        $dropdownMastersModel = ClassRegistry::init('DropdownsMasters');
        if ($Dropdown['Dropdowns']['modul'] !== '%unkowwn%') {
            $dropdownMastersCount = $dropdownMastersModel->find(
                'count',
                [
                    'conditions' => [
                        'name'              => $Dropdown['Dropdowns']['deu'],
                        'modul'             => $Dropdown['Dropdowns']['modul'],
                        'field'             => $Dropdown['Dropdowns']['field'],
                        'testingcomp_id IN' => [$Dropdown['Dropdowns']['testingcomp_id'], 0],
                    ],
                ],
            );


            if ($dropdownMastersCount > 0) {
                $proceed = false;
            }

            if ($proceed) {
                $saveData = [
                    'DropdownsMasters' => [
                        'id'             => 0,
                        'name'           => $Dropdown['Dropdowns']['deu'],
                        'user_id'        => 1,
                        'description'    => '',
                        'modul'          => $Dropdown['Dropdowns']['modul'],
                        'field'          => $Dropdown['Dropdowns']['field'],
                        'dependencies'   => $Dropdown['Dropdowns']['dependencies'],
                        'status'         => 0,
                        'deleted'        => 0,
                        'testingcomp_id' => $Dropdown['Dropdowns']['testingcomp_id'],
                        'imported'       => 1,
                    ]
                ];


                if ($dropdownMastersModel->save($saveData)) {
                    $Dropdown['Dropdowns']['dropdowns_masters_id'] = $dropdownMastersModel->getInsertID();
                }
            }
        }

        return $proceed;
    }

    private function _insertDropdownMastersData(&$Dropdown)
    {
        if (isset($Dropdown['Dropdowns']['dropdowns_masters_id']) && $Dropdown['Dropdowns']['dropdowns_masters_id'] != -1) {
            $dropdownMastersDataModel = ClassRegistry::init('DropdownsMastersData');

            foreach ($Dropdown['DropdownsValues'] as &$DropdownValue) {
                $dropdownValueCount = $dropdownMastersDataModel->find(
                    'count',
                    [
                        'conditions' => [
                            'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                            'value'                => $DropdownValue['DropdownsValues']['discription'],
                        ],
                    ],
                );

                if ($dropdownValueCount == 0) {
                    $insertData = [
                        'id'                   => 0,
                        'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                        'status'               => 0,
                        'value'                => $DropdownValue['DropdownsValues']['discription'],
                        'imported'             => 1,
                    ];

                    if ($insertData['value'] == '') {
                        $insertData['value'] = '-';
                    }

                    if (!$dropdownMastersDataModel->save($insertData)) {
                        $this->out('Error: Failed to save on DropdownMastersData');
                        debug($dropdownMastersDataModel->validationErrors);
                        debug($insertData);
                        debug($DropdownValue);

                        $DropdownValue['DropdownsValues']['dropdowns_masters_data_id'] = -1;
                    } else {
                        $DropdownValue['DropdownsValues']['dropdowns_masters_data_id'] = $dropdownMastersDataModel->getLastInsertID();
                    }
                }
            }
        }
    }

    /**
     * _insertDropdownMastersDependencies
     *
     * @param  mixed $Dropdown
     * @return void
     */
    private function _insertDropdownMastersDependencies($Dropdown)
    {
        if (isset($Dropdown['Dropdowns']['dropdowns_masters_id']) && $Dropdown['Dropdowns']['dropdowns_masters_id'] != -1) {
            $dependenciesModel                       = ClassRegistry::init('Dependency');
            $dropdownsMastersDependenciesModel       = ClassRegistry::init('DropdownsMastersDependency');
            $dropdownsMastersDependenciesFieldsModel = ClassRegistry::init('DropdownsMastersDependenciesFields');


            //Entfernen falls vorher schon Werte für diese Felder vorhanden sind
            $dropdownsMastersDependenciesModel->deleteAll(
                array(
                    'field IN' => array(
                        'examiner_certificate',
                        'examiner_certificat_level',
                        'supervisor_certificat_level',
                        'supervisor_certificat',
                        'supervisor_certificat_level'
                    ),
                ),
                false
            );

            foreach ($Dropdown['DropdownsValues'] as $DropdownValue) {
                if (isset($DropdownValue['DropdownsValues']['dropdowns_masters_data_id'])) {
                    if ($DropdownValue['DropdownsValues']['dropdowns_masters_data_id'] != -1) {
                        $currentID = $DropdownValue['DropdownsValues']['id'];

                        $conditions = [
                            'Dependency.dropdown_id'        => $Dropdown['Dropdowns']['id'],
                            'Dependency.dropdowns_value_id' => $DropdownValue['DropdownsValues']['id'],
                        ];



                        $dependencies = $dependenciesModel->find(
                            'all',
                            [
                                'conditions' => $conditions,
                            ]
                        );

                        if (!empty($dependencies)) {
                            foreach ($dependencies as $dependency) {
                                $dropdownMastersDependenciesCount = $dropdownsMastersDependenciesModel->find(
                                    'count',
                                    [
                                        'conditions' => [
                                            'testingcomp_id'            => $dependency['Dependency']['testingcomp_id'],
                                            'dropdowns_masters_id'      => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                                            'dropdowns_masters_data_id' => $DropdownValue['DropdownsValues']['dropdowns_masters_data_id'],
                                        ],
                                    ]
                                );

                                if ($dropdownMastersDependenciesCount == 0) {
                                    $insertData = [
                                        'id'                        => 0,
                                        'testingcomp_id'            => $dependency['Dependency']['testingcomp_id'],
                                        'dropdowns_masters_id'      => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                                        'dropdowns_masters_data_id' => $DropdownValue['DropdownsValues']['dropdowns_masters_data_id'],
                                        'field'                     => $dependency['Dependency']['field'],
                                        'value'                     => $dependency['Dependency']['value'],
                                        'global'                    => $dependency['Dependency']['global'],
                                        'imported'                  => 1,
                                    ];

                                    if (!$dropdownsMastersDependenciesModel->save($insertData)) {
                                        $this->out('Error: Failed to save DropdownMastersDependencies');
                                        debug($insertData);
                                        debug($dependency);
                                    } else {
                                        $dropdownMastersDependencyFieldsCount = $dropdownsMastersDependenciesFieldsModel->find(
                                            'count',
                                            [
                                                'conditions' => [
                                                    'testingcomp_id'       => $dependency['Dependency']['testingcomp_id'],
                                                    'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                                                    'user_id'              => 1,
                                                    'field'                => $dependency['Dependency']['field'],
                                                ],
                                            ]
                                        );

                                        if ($dropdownMastersDependencyFieldsCount == 0) {
                                            $saveData = [
                                                'id'                   => 0,
                                                'testingcomp_id'       => $dependency['Dependency']['testingcomp_id'],
                                                'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                                                'user_id'              => 1,
                                                'field'                => $dependency['Dependency']['field'],
                                                'imported'             => 1,
                                            ];

                                            if (!$dropdownsMastersDependenciesFieldsModel->save($saveData)) {
                                                $this->out('Error: Failed to save DropdownsMastersDependenciesFields');
                                                debug($saveData);
                                                debug($dependency);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * _insertDropdownMastersReports
     *
     * @param  mixed $Dropdown
     * @return void
     */
    private function _insertDropdownMastersReports($Dropdown)
    {
        if (isset($Dropdown['Dropdowns']['dropdowns_masters_id']) && $Dropdown['Dropdowns']['dropdowns_masters_id'] != -1) {
            $dropdownsMastersReportsModel = ClassRegistry::init('DropdownsMastersReports');

            $dropdownMastersReportsCount = $dropdownsMastersReportsModel->find(
                'count',
                [
                    'conditions' => [
                        'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                        'report_id'            => $Dropdown['Dropdowns']['report_id'],
                    ],
                ]
            );

            if ($dropdownMastersReportsCount == 0) {
                $saveData = [
                    'id'                   => 0,
                    'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                    'report_id'            => $Dropdown['Dropdowns']['report_id'],
                    'imported'             => 1,
                ];

                if (!$dropdownsMastersReportsModel->save($saveData)) {
                    $this->out('Error: Failed to save DropdownsMastersReports');
                    debug($Dropdown);
                }
            }
        }
    }

    /**
     * _insertDropdownMastersTestingcomps
     *
     * @param  mixed $Dropdown
     * @return void
     */
    private function _insertDropdownMastersTestingcomps($Dropdown)
    {
        if (isset($Dropdown['Dropdowns']['dropdowns_masters_id']) && $Dropdown['Dropdowns']['dropdowns_masters_id'] != -1) {
            $dropdownMastersTestingcompsModel = ClassRegistry::init('DropdownsMastersTestingcomps');

            $dropdownMastersTestingcompsCount = $dropdownMastersTestingcompsModel->find(
                'count',
                [
                    'conditions' => [
                        'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                        'testingcomp_id'       => $Dropdown['Dropdowns']['testingcomp_id'],
                    ],
                ]
            );

            if ($dropdownMastersTestingcompsCount == 0) {
                $saveData = [
                    'id'                   => 0,
                    'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                    'testingcomp_id'       => $Dropdown['Dropdowns']['testingcomp_id'],
                    'imported'             => 1,
                ];

                if (!$dropdownMastersTestingcompsModel->save($saveData)) {
                    $this->out('Error failed to save DropdownsMastersTestingcomps');
                    var_dump($saveData);
                    var_dump($Dropdown);
                }
            }
        }
    }

    /**
     * _insertDropdownMastersTestingmethods
     *
     * @param  mixed $Dropdown
     * @return void
     */
    private function _insertDropdownMastersTestingmethods($Dropdown)
    {
        if (isset($Dropdown['Dropdowns']['dropdowns_masters_id']) && $Dropdown['Dropdowns']['dropdowns_masters_id'] != -1) {
            $dropdownMastersTestingmethodsModel = ClassRegistry::init('DropdownsMastersTestingmethods');
            $oldDropdownsModel                  = ClassRegistry::init('Dropdowns');


            //Hier alle testingmethods holen für field 

            $testingMethodIdsForField = $oldDropdownsModel->find(
                'all',
                [
                    'conditions' => [
                        'field'          => $Dropdown['Dropdowns']['field'],
                        'testingcomp_id' => $Dropdown['Dropdowns']['testingcomp_id'],
                    ],
                ]
            );

            foreach ($testingMethodIdsForField as $testingmethodField) {
                $dropdownMastersTestingmethodsCount = $dropdownMastersTestingmethodsModel->find(
                    'count',
                    [
                        'conditions' => [
                            'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                            'testingmethod_id'     => $testingmethodField['Dropdowns']['testingmethod_id'],
                        ],
                    ]
                );

                if ($dropdownMastersTestingmethodsCount == 0) {
                    $saveData = [
                        'id'                   => 0,
                        'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                        'testingmethod_id'     => $testingmethodField['Dropdowns']['testingmethod_id'],
                        'imported'             => 1,
                    ];

                    if (!$dropdownMastersTestingmethodsModel->save($saveData)) {
                        $this->out('Error: Failed to save DropdownsMastersTestingmethods');
                        debug($saveData);
                        debug($Dropdown);
                    }
                }
            }
        }
    }

    /**
     * _insertDropdownMastersTopprojects
     *
     * @param  mixed $Dropdown
     * @return void
     */
    private function _insertDropdownMastersTopprojects($Dropdown)
    {
        if (isset($Dropdown['Dropdowns']['dropdowns_masters_id']) && $Dropdown['Dropdowns']['dropdowns_masters_id'] != -1) {
            $dropdownMastersTopprojectsModel = ClassRegistry::init('DropdownsMastersTopprojects');
            $topprojectsModel                = ClassRegistry::init('Topprojects');

            $topprojects = $topprojectsModel->find('all');

            foreach ($topprojects as $topproject) {
                $dropdownMastersTopprojectsCount = $dropdownMastersTopprojectsModel->find(
                    'count',
                    [
                        'conditions' => [
                            'topproject_id'        => $topproject['Topprojects']['id'],
                            'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                        ],
                    ]
                );


                if ($dropdownMastersTopprojectsCount == 0) {
                    $saveData = [
                        'id'                   => 0,
                        'topproject_id'        => $topproject['Topprojects']['id'],
                        'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                        'imported'             => 1,
                    ];

                    if (!$dropdownMastersTopprojectsModel->save($saveData)) {
                        $this->out('Error failed to save DropdownsMastersTopprojects');

                        debug($saveData);
                        debug($Dropdown);
                    }
                }
            }
        }
    }

    public function DependencyLinkByName(){

        if (!isset($this->args[0])) {
            $this->out(h('Keine Ursprungsid übergeben.'));
            return;
        }

        if (!isset($this->args[1])) {
            $this->out(h('Keine Zielid übergeben.'));
            return;
        }

        $UrsprungId = $this->args[0];
        $ZielId = $this->args[1];
        $TestingCompId = 1;

        App::uses('Dropdowns', 'Model');

        $data = array();

        $dropdownsModel                              = ClassRegistry::init('Dropdowns');
        $dropdownsValueModel                    = ClassRegistry::init('DropdownsValue');
        $dependenciesModel                      = ClassRegistry::init('Dependencies');
        $dropdownMastersDependenciesModel       = ClassRegistry::init('DropdownsMastersDependency');
        $dropdownMastersDependenciesFieldsModel = ClassRegistry::init('DropdownsMastersDependenciesField');
        $dropdownsMastersModel                  = ClassRegistry::init('DropdownsMaster');
        $dropdownMastersDataModel               = ClassRegistry::init('DropdownsMastersData');

        if (isset($this->args[2]) && $this->args[2] == 1) {
            // vorhandene Daten löschen
            $dropdownMastersDataModel->deleteAll(array('DropdownsMastersData.dropdowns_masters_id' => $ZielId), false);
            $dropdownMastersDependenciesModel->deleteAll(array('DropdownsMastersDependency.dropdowns_masters_id' => $ZielId), false);
        }

        $dropdown = $dropdownsModel->find(
            'first',
            array(
                'conditions' => array(
                    'Dropdowns.id' => $UrsprungId
                )
            )
        );

        if(count($dropdown) == 0){
            $this->out(h('Keine Ursprungsdaten vorhanden.'));
            return;
        }

        $data['Settings']['From']['field'] = $dropdown['Dropdowns']['field'];

        $data = array_merge($data,$dropdown);

        $dropdownsValueModel->recursive = -1;

        $dropdownsValue['DropdownsValue'] = $dropdownsValueModel->find(
            'all',
            array(
               // 'limit' => 1,
                'conditions' => array(
                    'DropdownsValue.dropdown_id' => $UrsprungId,
                    'DropdownsValue.testingcomp_id' => $TestingCompId
                )
            )
        );

        if(count($dropdownsValue['DropdownsValue']) == 0){
            $this->out(h('Keine Ursprungsdropdowneinträge vorhanden.'));
            return;
        }


        foreach($dropdownsValue['DropdownsValue'] as $key => $value){

            $dependencies = $dependenciesModel->find(
                'all',
                array(
                    'conditions' => array(
                        'Dependencies.dropdown_id' => $UrsprungId,
                        'Dependencies.testingcomp_id' => $TestingCompId,
                        'Dependencies.dropdowns_value_id' => $value['DropdownsValue']['id']
                    )
                )
            );
    
            if(count($dependencies) > 0){
                $data['Settings']['From']['dependendy_field'] = $dependencies[0]['Dependencies']['field'];
            }

            $dropdownsValue['DropdownsValue'][$key]['Dependencies'] =  $dependencies;
            
        }

        $data = array_merge($data,$dropdownsValue);

        $test = $dropdownsMastersModel->find(
            'first',
            array(
                'conditions' => array(
                    'DropdownsMaster.id' => $ZielId
                )
            )
        );

        if(count($test) == 0){
            $this->out(h('Keine Zieldaten vorhanden.'));
            return;
        }

        $test = $dropdownMastersDependenciesFieldsModel->find(
            'first',
            array(
                'conditions' => array(
                    'DropdownsMastersDependenciesField.testingcomp_id' => $TestingCompId,
                    'DropdownsMastersDependenciesField.dropdowns_masters_id' => $ZielId,
                )
            )
        );

        if(count($test) == 0){
            $this->out(h('Für das neuen Dropdownfeld wurde kein untergeordnetes Feld gefunden, bitte erstellen.'));
            return;
        }

        $data = array_merge($data,$test);

        foreach($data['DropdownsValue'] as $key => $value){

            $test = $dropdownMastersDataModel->find(
                'first',
                array(
                    'conditions' => array(
                        'DropdownsMastersData.dropdowns_masters_id' => $ZielId,
                        'DropdownsMastersData.value' => $value['DropdownsValue']['discription'],
                    )
                )
            );
    
    	    if (count($test) == 0) {
                
                $Insert = array(
                    'id' => 0,
                    'dropdowns_masters_id' => $ZielId,
                    'status' => 0,
                    'value' => $value['DropdownsValue']['discription'],
                    'imported' => 1
                );
    
                $dropdownMastersDataModel->create();
                $dropdownMastersDataModel->save($Insert);
    
                $InsertID = $dropdownMastersDataModel->getLastInsertID();
    
                $test = $dropdownMastersDataModel->find(
                    'first',
                    array(
                        'conditions' => array(
                            'DropdownsMastersData.id' => $InsertID,
                        )
                    )
                );    
    
            }

            if (count($value['Dependencies']) == 0) continue;

            foreach ($value['Dependencies'] as $_key => $_value){

                $test2 = $dropdownMastersDependenciesModel->find(
                    'first',
                    array(
                        'conditions' => array(
                            'DropdownsMastersDependency.dropdowns_masters_id' => $ZielId,
                            'DropdownsMastersDependency.dropdowns_masters_data_id' => $test['DropdownsMastersData']['id'],
                            'DropdownsMastersDependency.field' => $data['DropdownsMastersDependenciesField']['field'],
                            'DropdownsMastersDependency.value' => $_value['Dependencies']['value'],
                            )
                    )
                );

                if(count($test2) == 1) continue;

                $Insert = array(
                    'id' => 0,
                    'testingcomp_id' => $TestingCompId,
                    'dropdowns_masters_id' => $ZielId,
                    'dropdowns_masters_data_id' => $test['DropdownsMastersData']['id'],
                    'field' => $data['DropdownsMastersDependenciesField']['field'],
                    'value' => $_value['Dependencies']['value'],
                    'imported' => 1
                );

                $dropdownMastersDependenciesModel->create();
                $dropdownMastersDependenciesModel->save($Insert);

            }

        }

    }

        public function DependencyLinkByNameDelete(){

        if (!isset($this->args[0])) {
            $this->out(h('Keine Ursprungsid übergeben.'));
            return;
        }

        if (!isset($this->args[1])) {
            $this->out(h('Keine Zielid übergeben.'));
            return;
        }

        $UrsprungId = $this->args[0];
        $ZielId = $this->args[1];

        App::uses('Dropdowns', 'Model');

        $dropdownMastersDependenciesModel       = ClassRegistry::init('DropdownsMastersDependency');
        $dropdownMastersDependenciesFieldsModel = ClassRegistry::init('DropdownsMastersDependenciesField');
        $dropdownMastersDataModel               = ClassRegistry::init('DropdownsMastersData');


        if (isset($this->args[2]) && $this->args[2] == 1) {
            // vorhandene Daten löschen
            $dropdownMastersDataModel->deleteAll(array('DropdownsMastersData.dropdowns_masters_id' => $ZielId), false);
            $dropdownMastersDependenciesModel->deleteAll(array('DropdownsMastersDependency.dropdowns_masters_id' => $ZielId), false);
            $dropdownMastersDependenciesFieldsModel->deleteAll(array('DropdownsMastersDependenciesField.dropdowns_masters_id' => $ZielId), false);
        }

    }

    public function DependencyLink()
    {
        App::uses('Dropdowns', 'Model');

        $dependenciesModel                      = ClassRegistry::init('Dependencies');
        $dropdownMastersDependenciesModel       = ClassRegistry::init('DropdownsMastersDependency');
        $dropdownMastersDependenciesFieldsModel = ClassRegistry::init('DropdownsMastersDependenciesField');
        $dropdownsValueModel                    = ClassRegistry::init('DropdownsValue');
        $dropdownsMastersModel                  = ClassRegistry::init('DropdownsMaster');
        $dropdownMastersDataModel               = ClassRegistry::init('DropdownsMastersData');

        //Dependencies holen
        $dependencies = $dependenciesModel->find(
            'list',
            array(
                'fields' => array(

                    'field',
                    'value',
                    'dropdowns_value_id',
                )
            )
        );

        $this->out('Daten werden verarbeitet...');

        //Dependencies
        foreach ($dependencies as $key => $dependenciesModelvalue) {
            $dropdowns_value_id = $key;

            $dropdownsValue = $dropdownsValueModel->find(
                'all',
                array(
                    'conditions' => array(
                        'DropdownsValue.id' => $dropdowns_value_id
                    )
                )
            );

            //DropdownValues
            foreach ($dropdownsValue as $value) {
                $description = $value['DropdownsValue']['discription'];

                $dropdownMastersData = $dropdownMastersDataModel->find(
                    'all',
                    array(
                        'conditions' => array(
                            'DropdownsMastersData.value' => $description
                        )
                    )
                );

                foreach ($dropdownMastersData as $masterData) {
                    $dropdown_master_id       = $masterData['DropdownsMastersData']['dropdowns_masters_id'];
                    $dropdown_masters_data_id = $masterData['DropdownsMastersData']['id'];
                    $dropdownMaster           = $dropdownsMastersModel->find(
                        'all',
                        array(
                            'conditions' => array(
                                'DropdownsMaster.id' => $dropdown_master_id
                            )
                        )
                    );


                    $testingcomp_id = 1;




                    $field = key($dependenciesModelvalue);
                    $model = $value['DropdownsValue']['model'];

                    $model_to_module = '';



                    $keywords = array("Generally", "Specific", "Evaluation");

                    foreach ($keywords as $keyword) {
                        switch ($keyword) {
                            case "Generally":
                                if (strpos($model, $keyword) !== false) {
                                    $model_to_module = 'generally_area';
                                }
                                break;
                            case "Specific":
                                if (strpos($model, $keyword) !== false) {
                                    $model_to_module = 'specific_area';
                                }
                                break;
                            case "Evaluation":
                                if (strpos($model, $keyword) !== false) {
                                    $model_to_module = 'evaluation_area';
                                }
                                break;
                            default:
                                continue;
                                break;
                        }
                    }

                    //Hier Daten einfügen

                    /*debug($dropdown_master_id);
                    debug($dropdown_masters_data_id);*/
                    //     debug($field);
                    //    debug($description);
                    $masterDependencies = $dropdownMastersDependenciesModel->find(
                        'all',
                        array(
                            'conditions' => array(
                                'DropdownsMastersDependency.field' => $field,
                                'DropdownsMastersDependency.value' => $description,
                            )
                        )
                    );

                    $testingcomp_id = $value['Testingcomp']['id'];



                    if (empty($masterDependencies)) {

                        $saveData = [
                            'id'                        => 0,
                            'testingcomp_id'            => $testingcomp_id,
                            'dropdowns_masters_id'      => $dropdown_master_id,
                            'dropdowns_masters_data_id' => $dropdown_masters_data_id,
                            'field'                     => $field,
                            'value'                     => $description,
                            'global'                    => 0,
                            'imported'                  => 1
                        ];

                        $saveDataFields = [
                            'id'                   => 0,
                            'testingcomp_id'       => $testingcomp_id,
                            'dropdowns_masters_id' => $dropdown_master_id,
                            'user_id'              => 1,
                            'field'                => $field,
                            'field_type'           => '',
                            'imported'             => 1
                        ];


                        $masterDependenciesField = $dropdownMastersDependenciesFieldsModel->find(
                            'all',
                            array(
                                'conditions' => $saveDataFields
                            )
                        );

                        if (empty($masterDependenciesField)) {
 //                           $dropdownMastersDependenciesFieldsModel->save($saveDataFields);
                        }

                        $dropdownMastersDependenciesModel->save($saveData);

                    }



                    /*     $saveData = [
                             'id'                   => 0,
                             'dropdowns_masters_id' => $Dropdown['Dropdowns']['dropdowns_masters_id'],
                             'testingcomp_id'       => $Dropdown['Dropdowns']['testingcomp_id'],
                             'imported'             => 1,
                         ];

                         if (!$dropdownMastersTestingcompsModel->save($saveData)) {
                             $this->out('Error failed to save DropdownsMastersTestingcomps');
                             var_dump($saveData);
                             var_dump($Dropdown);
                         }*/


                }

            }

        }

        $this->out('Daten verarbeitet!');
    }
}