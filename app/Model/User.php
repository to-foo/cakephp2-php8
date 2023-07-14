<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Roll $Roll
 * @property Testingcomp $Testingcomp
 * @property Qualification $Qualification
 */
class User extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Roll' => array(
			'className' => 'Roll',
			'foreignKey' => 'roll_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'foreignKey' => 'testingcomp_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public $hasMany = array(
		'Qualification' => array(
			'className' => 'Qualification',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'HiddenField' => array(
			'className' => 'HiddenField',
			'foreignKey' => 'reportnumber_id',
			'dependent' => true
		),
		'Examiner' => array(
				'className' => 'Examiner',
				'foreignKey' => 'user_id',
				'dependent' => true,
			),
	);

	public function bindNode($user){
		if(empty($user['User']['roll_id'])){
			return null;
		}
		else {
			return array(
					'model' => 'Roll',
					'foreign_key' => $user['User']['roll_id']
				);
		}
	}

    function __construct() {

        parent::__construct();

		/*
		 * Validate on the password field not the password confirmation field
		 * This ensures that if we enter blank in both then nothing is triggered
		 * or blank on the confirmation field only then it is rejected using validatePasswdConfirm
		 */
        $this->validate = array
        (
			'username' => array(
				'rule'    => 'isUnique',
				'message' => 'This username has already been taken.'
				),

			);
		}

	public function beforeDelete($cascade = true) {
		if($this->id === 1){
			die();
		}
	}

   function validateHashedPassword($data)
    {
		return $data['password'] != Security::hash('', null, true);
    }

   function validatePasswdConfirm()
    {
		if (isset($this->data['User']['passwd_confirm']) && trim($this->data['User']['passwd']) != '')
        {
            return $this->data['User']['passwd_confirm'] === $this->data['User']['passwd'];
        }

        return true;
    }

	function beforeValidate($options = array()) {

		if(!isset($this->data[$this->alias][$this->primaryKey]) || empty($this->data[$this->alias][$this->primaryKey])) {
			// Falls ein neuer Benutzer angelegt wird, ist ein PAsswort Pflicht - bei Aktualisierung wird das alte beibehalten
	 		if(!isset($this->data[$this->alias]['passwd']) || trim($this->data[$this->alias]['passwd']) == '') {
	 			$this->invalidate('passwd', __('You must submit a password'));
				return false;
	 		}
		}

		if(!$this->validatePasswdConfirm()) {
			foreach(array('passwd', 'passwdConfirm') as $field) { $this->invalidate($field, __('Passwords do not match')); }
			return false;
		}

		return true;
	}

	function beforeSave($options = array()) {
		/*
		* Ensure that there is a value for the password,
		* field it should be ignored if they are not
		* providing a value (i.e. no update should take place)
		*/

		if (isset($this->data['User']['passwd']) && trim($this->data['User']['passwd']) != '')
		{
			$this->data['User']['password'] = Security::hash($this->data['User']['passwd'], null, true);
		}


		// Prüfen, ob eine höhere Benutzerrolle gewählt wurde, als für dessen Prüffirma erlaubt und notfalls auf höchste erlaubte korregieren
		$this->Testingcomp->recursive = -1;
		$roll_id = $this->Testingcomp->find('list', array('fields' => array('roll_id'), 'conditions' => array('Testingcomp.id'=>$this->data['User']['testingcomp_id'])));

		$this->data['User']['roll_id'] = max(
			$this->data['User']['roll_id'],
			empty($roll_id) ? -1 : reset($roll_id)
		);


		// leere Felder herausfiltern, damit beim bearbeiten keine Passwörter gelöscht werden
		$this->data[$this->alias] = array_filter($this->data[$this->alias]);
		if(!isset($this->data[$this->alias]['enabled'])) $this->data[$this->alias]['enabled'] = 0;

		return true;
	}

}
