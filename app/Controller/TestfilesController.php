<?php
if (!defined('ELFINDER_DIR')) {
  // According to your server path and setup        
  define ('ELFINDER_DIR', ROOT . DS . 'files' . DS);
}
if (!defined('ELFINDER_URL')) {
  // According to your domain and setup     
  define ('ELFINDER_URL', 'http://localhost/dekra/' . 'files/');
}

App::uses('AppController', 'Controller');
require_once APP . 'Lib'. DS. 'Elfinder'. DS. 'elFinderConnector.class.php';
require_once APP . 'Lib'. DS. 'Elfinder'. DS. 'elFinder.class.php';
require_once APP . 'Lib'. DS. 'Elfinder'. DS. 'elFinderVolumeDriver.class.php';
require_once APP . 'Lib'. DS. 'Elfinder'. DS. 'elFinderVolumeLocalFileSystem.class.php';


class TestfilesController extends AppController {
    public $name = 'Testfiles';
    public $uses = array();
    public $components = array('RequestHandler');
	public $helpers = array('Js', 'Html');
	
 
    public function beforeFilter() {
//        parent::beforeFilter();
 //       $this->Security->csrfCheck = false;
  //      $this->Security->validatePost = false;
    }
 
    public function index() {
		
	$pfad = ROOT . DS . 'app' .DS . 'files' . DS;

	$opts = array(
            'debug' => true,
            'roots' => array(
                array(
                    'driver'        => 'LocalFileSystem',    // driver for accessing file system (REQUIRED)
                    'path'          => '../webroot/fileser/',             // path to files (REQUIRED)
					'URL'           => 'http://localhost/projekt/files/',             // URL to files (REQUIRED)
                    'tmbBgColor'    => 'transparent'
                )
            )
        );

        $title_for_layout = 'Media Library';
        $this->set(compact('title_for_layout'));
		$connector = new ElFinderConnector(new ElFinder($opts));
		$connector->run();
		
/* 
        if($this->RequestHandler->isAjax() || $this->RequestHandler->isPost()) {
            $connector = new ElFinderConnector(new ElFinder($this->opts));
            $connector->run();
        }
*/ 
    }
}