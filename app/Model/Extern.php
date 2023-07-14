<?php
App::uses('AppModel', 'Model');
/**
 * Roll Model
 *
 * @property Testingcomp $Testingcomp
 * @property User $User
 */
class Extern extends AppModel {

	public $hasMany = array(
		'ExternData' => array(
			'className' => 'ExternData',
			'foreignKey' => 'extern_id',
		),
	);

	public $validate = array(
		'username' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'password' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);


	function beforeValidate($options = array()) {
/*
 *             'passwd' => array
            	(
					'empty' => array(
						'required'		=> true,
						'allowEmpty'	=> false,
                	    'message'       => __('You must submit a password', true)
					),
					'match' => array(
						'rule'          => 'validatePasswdConfirm',
						'message'       => __('Passwords do not match', true)
					)
				),
 */

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
}
