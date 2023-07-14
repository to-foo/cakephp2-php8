<?php
class SicherheitComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function ClamavScan(){

		if(Configure::check('EnabledClamavService') === false) return true;
		if(Configure::read('EnabledClamavService') === false) return true;

		App::import('Vendor','antivirus_fiesta/ClamavService');

		if(!isset($_FILES['file'])) return true;
		if(count($_FILES['file']) == 0) return true;
		if(!isset($_FILES['file']['tmp_name'])) return true;
		if(file_exists($_FILES['file']['tmp_name']) === false) return true;

		$ClamavService = new ClamavService();

		$CheckService = $ClamavService->checkClamav();

		if(isset($CheckService['message']) && $CheckService['message'] == 'ClamAV is NOT Alive!'){
			return true;
		}

		$ReportnumberId = $this->_controller->request->projectvars['VarsArray'][4];

		$testfile = $_FILES['file']['tmp_name'];
		$response = $ClamavService->sendToScanner($testfile);

		if($response['message'] == 'OK') return true;

		if($response['message'] != 'OK'){

			$this->_controller->autoRender = false;

			unlink($_FILES['file']['tmp_name']);
			unset($_FILES);

			$dataFiles = array('message' => $response['message']);

			$this->_controller->Autorisierung->Logger($ReportnumberId,$dataFiles);

			$this->_controller->Flash->error('The file is infected with a virus and has been deleted.', array('key' => 'error'));

		}
	}

	public function SoduimEncrypt($msg){

		$output = array();

		$aliceKeypair = sodium_crypto_box_keypair();
		$alicePublicKey = sodium_crypto_box_publickey($aliceKeypair);
		$aliceSecretKey = sodium_crypto_box_secretkey($aliceKeypair);
		$bobKeypair = sodium_crypto_box_keypair();
		$bobPublicKey = sodium_crypto_box_publickey($bobKeypair); // 32 bytes
		$bobSecretKey = sodium_crypto_box_secretkey($bobKeypair); // 32 bytes
		$nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES); // 24 bytes

		$keyEncrypt = $aliceSecretKey . $bobPublicKey;
		$ciphertext = sodium_crypto_box($msg, $nonce, $keyEncrypt);

		$keyDecrypt = $bobSecretKey . $alicePublicKey;
		$plaintext = sodium_crypto_box_open($ciphertext, $nonce, $keyDecrypt);

		$output['nonce'] = bin2hex($nonce);
		$output['keyDecrypt'] = bin2hex($keyDecrypt);
		$output['ciphertext'] = bin2hex($ciphertext);

		$output = $output['nonce'].$output['keyDecrypt'].$output['ciphertext'];

		if($plaintext == $msg) return $output;
		else return false;
	}

	public function SoduimDecrypt($decrypt){

		if(strlen($decrypt) < 176) return false;

		$nonce = hex2bin((substr($decrypt,0,48)));
		$keyDecrypt = hex2bin((substr($decrypt,48,128)));
		$ciphertext = hex2bin((substr($decrypt,176)));

		$plaintext = sodium_crypto_box_open($ciphertext,$nonce,$keyDecrypt);

		return $plaintext;
	}

	public function Schutz() {
		$this->_controller->Security->requireAuth();
		$this->_controller->Security->requireSecure();
//		$this->_controller->Security->requirePost('view','add','edit','delete');
	}
	public function Numeric($var) {

		// Wenn $var leer ist
		if($var == ''){
			return '0';
		}

		if(is_numeric($var)) {
			return $var;
		}
		else {
			return '0';
		}
	}

	public function OnlyLetterNumber($var) {
		if(preg_match("/[A-Za-z0-9]/", $var) == TRUE){
			return $var;
		}
		else {
			$var = null;
			$this ->_controller->redirect('/');
		}
	}
}
