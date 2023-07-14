<?php
App::uses('AppModel', 'Model');
class Logo extends AppModel {
   	public $name = 'Logo';
    public $useTable = false;
	public $testingcomp = null;
	public $last = array();
	public $maxImageSize = 200;
	
    public $validate = array(
        'file' => array(
            'extension' => array(
            	'rule' => array('extension', array('jpg', 'jpeg', 'gif', 'png')),
                'message' => 'Invalid file or extention (should be jpg, jpeg, png or gif)'
            ),
            'validFile' => array(
            	'rule' => array('validFile', true),
            	'message' => 'Invalid file'
			)
		)
    ); 

    function get($folderName = null) {
    	if(empty($folderName)) $folderName = Configure::read('company_logo_folder').$this->testingcomp[0];
        $folder = new Folder($folderName);
        $images = $folder->read(
            true,
            array(
                '.',
                '..',
                'Thumbs.db'
            ),
            true
        );
        $images = $images[1]; // We are only interested in files

        // Get more infos about the images
        $retVal = array();
        foreach ($images as $the_image)
        {
            $the_image = new File($the_image);
            $retVal[] = array_merge(
                $the_image->info(),
                array(
                    'size' => $the_image->size(),
                    'last_changed' => $the_image->lastChange()
                )
            );
        }

        return $retVal;
    }

    function upload($data = null) {
    	$folder = Configure::read('company_logo_folder').$this->testingcomp[0].DS;
		if(empty($folder)) $folder = APP.WEBROOT_DIR.DS.'public_uploads'.DS;
        $this->set($data);

        if(empty($this->data)) {
            return false;
        }

        // Validation
        if(!$this->validates()) {
            return false;
        }

        // Move the file to the uploads folder
        if(!is_dir($folder)) mkdir($folder,0777, true);

		// logo vor dem Speichern noch bearbeiten (Maximalgröße etc)        
        $img = imagecreatefromstring(file_get_contents($this->data[$this->name]['file']['tmp_name']));
		list($w, $h) = getimagesize($this->data[$this->name]['file']['tmp_name']);
		unlink($this->data[$this->name]['file']['tmp_name']);

		// Bild auf Maximalgröße verkleinern oder lassen, wenn kleiner
		if($w > $h) {		// Querformat
			$nw = min($w, $this->maxImageSize);
			$nh = $h * $nw / $w;
		} else {			// Hochformat
			$nh = min($w, $this->maxImageSize);
			$nw = $w * $nh / $h;
		}
		
		$logo = imagecreatetruecolor($nw, $nh);
		imagecopyresampled($logo, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

		if(imagepng($logo, $folder.'logo.png'))
		{
			$this->last = array($folder.'logo.png');
			return true;
		} else {
			return false;
		}
    }



    function validFile($check, $required = true) {
        // Remove first level of Array
        $_check = array_shift($check);

        // No file uploaded.
        if(($_check['size'] == 0) && $required) {
            return false;
        }

        // Check for Basic PHP file errors.
        if($_check['error']) {
            return false;
        }

        // Use PHPs own file validation method.
        if(!is_uploaded_file($_check['tmp_name'])) {
            return false;
        }

        // Valid extension
        return true;
    } 
}
?>