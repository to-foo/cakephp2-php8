<?php
class ImageHelper extends AppHelper {
	protected $_defaults = array(
		'format'=>'auto',
		'quality'=>80,
		'width'=>0,
		'height'=>0,
		'force_size'=>false,
		'title'=>true,
		'alt'=>true,
		'use_error_image'=>false,
		'transparency' => 0
	);

	protected function _makeImage($data, $options) {
		
		// Kann vielleicht raus, es wird kein Pfad übergeben
		if(@realpath($data) && @is_readable($data)) {
			list($_, $_, $type) = getimagesize($data);
			$name = basename($data);
			$data = file_get_contents($data);
		}

		// Warnung unterdrücken, da im Fehlerfall sowieso abgebrochen wird
		$img = @imagecreatefromstring($data);
		if(!isset($type)) $type = IMAGETYPE_PNG;
		if(!isset($name)) $name = uniqid(time()).'.'.image_type_to_extension($type);
		
		if($img) {
			return array(
				'res'		=> $img,
				'name'		=> $name,
				'type'		=> $type,
				'mime'		=> image_type_to_mime_type($type), 
				'ext'		=> image_type_to_extension($type), 
				'width'		=> imagesx($img), 
				'height'	=> imagesy($img)
			);
		} else {
			if($options['use_error_image']) {
				$w = empty($options['width']) ? 300 : $options['width'];
				$h = empty($options['height']) ? 300 : $options['height'];

				$img = imagecreatetruecolor($w, $h);
				$txt = __('Image not found', true);
				
				imagestring($img, 2, 150-imagefontwidth(2)*strlen($txt) / 2, 150-imagefontheight(2), $txt, imagecolorallocate($img, 255, 255, 255));

				return array(
					'res'		=> $img,
					'name'		=> 'error_image.png',
					'type'		=> IMAGETYPE_PNG,
					'mime'		=> image_type_to_mime_type(IMAGETYPE_PNG),
					'ext'		=> image_type_to_extension(IMAGETYPE_PNG),
					'width'		=> $w,
					'height'	=> $h
				);
			}
			else {
				return null;
			}
		}
			

		return null;
	}

	protected function _renderImage($img, $type, $html=true, $path=null, $options) {
		if(!is_resource($img)) return null;

		
		// Bildgröße ändern
		if(
			($options['width'] != 0 && $options['height'] != 0)
			&& $options['force_size'] || $options['width']>imagesx($img) || $options['height']>imagesy($img)
		) {
			$w = $options['force_size'] ? $options['width'] : max($options['width'], imagesx($img));
			$h = $options['force_size'] ? $options['height'] : max($options['height'], imagesy($img));
			
			// TODO: Resample des Bildes
		}
		elseif($options['width'] != 0 && $options['height'] == 0 && $options['width'] < imagesx($img)) {
			$w = $options['width'];
			$h = $w * imagesy($img) / imagesx($img);
			$img_r = @imagecreatetruecolor($w, $h);
			$source = imagecopyresized($img_r,$img,0,0,0,0,$w,$h,imagesx($img),imagesy($img));
			$img = $img_r;
			$img_r = null;
		}
		
		if($options['transparency'] > 0) {
			$img_t = @imagecreatetruecolor(imagesx($img), imagesy($img));
			$white = imagecolorallocate($img_t, 255, 255, 255);
			imagecolortransparent($img_t, $white);
			imagefilledrectangle($img_t, 0, 0, imagesx($img), imagesy($img), $white);
			
			imagecopymerge($img_t, $img, 0, 0, 0, 0, imagesx($img), imagesy($img), 100-$options['transparency']);
			imagedestroy($img);
			$img = $img_t;
			$img_t = null;
		}
		
		ob_start();
		
		// Bildtyp auswerten und speichern, wenn nicht als HTML angefordert und Pfad angegeben
		switch($type){
			case IMAGETYPE_PNG:
				$path = empty($path) ? null : dirname($path).DS.basename($path, '.png').'.png';
				$ok = imagepng($img, empty($html) ? $path : null, round((100-$options['quality'])*.08));
				break;
			
			case IMAGETYPE_GIF:
				$path = empty($path) ? null : dirname($path).DS.basename($path, '.gif').'.gif'; 
				$ok = imagegif($img, empty($html) ? $path : null);
				break;
				
			case IMAGETYPE_JPEG:
				$path = empty($path) ? null : dirname($path).DS.preg_replace('/\.jp(e?+)g$/', '', $path).'.jpg'; 
				$ok = imagejpeg($img, empty($html) ? $path : null, $options['quality']);
				break;
				
			default:
				$ok = false;
		}

		$cont = ob_get_contents();
		ob_end_clean();
		imagedestroy($img);
		
		// falls Fehler aufgetreten sind
		if(!$ok) return null;

		// falls das Bild gespeichert werden sollte		
		if(!empty($path) && is_file($path) && is_readable($path)) return true;
		
		// R�ckgabe als HTML oder Bilddatenstrom
		if($html) return '<img src="data:'.image_type_to_mime_type($type).';base64,'.base64_encode($cont).'" title="'.$options['title'].'" alt="'.$options['alt'].'"/>';
		else return $cont;
	}

	public function get($path, $html=true, $options = array()) {


		$options = array_merge($this->_defaults, array_map('strtolower', $options));
		$options['quality'] = min(100, max(1,intval($options['quality'])));
		$options['width'] = intval($options['width']);
		$options['height'] = intval($options['height']);
		$options['transparency'] = min(100, max(0,intval($options['transparency'])));
		
		$img = $this->_makeImage($path, $options);

		if(empty($img)) return null;
		
		//Standardoptionen
		$options = array_merge($options, array('name'=>$img['name']));

		$options['title'] = (string)($options['title'] === true ? $options['name'] : $options['title']);
		$options['alt'] = (string)($options['alt'] === true ? $options['name'] : $options['title']);
		
		if($options['format']=='auto' && $img['type'] == IMAGETYPE_GIF || $options['format']=='gif') return $this->_renderImage($img['res'], IMAGETYPE_GIF, $html, false, $options);
		if($options['format']=='auto' && $img['type'] == IMAGETYPE_JPEG || preg_match('/jp(e?+)g/', $options['format'])) return $this->_renderImage($img['res'], IMAGETYPE_JPEG, $html, false, $options);
		if($options['format']=='auto' && $img['type'] == IMAGETYPE_PNG || $options['format']=='png') return $this->_renderImage($img['res'], IMAGETYPE_PNG, $html, false, $options);
		return null;
	}
	
	public function save($sourcepath, $targetpath=null, $format='auto', $options = array())
	{
		$img = $this->_makeImage($sourcepath);
		if(empty($img)) return false;
		if(empty($targetpath)) $targetpath=(realpath($sourcepath) ? dirname(realpath($sourcepath)).DS : '').$img['name'];
		
		//Standardoptionen
		$options = array_merge($this->_defaults, array_map('strtolower', $options), array('name'=>basename($targetpath)));

		$options['title'] = (string)($options['title'] === true ? $options['name'] : $options['title']);
		$options['alt'] = (string)($options['alt'] === true ? $options['name'] : $options['title']);
		$options['quality'] = min(100, max(1,intval($options['quality'])));
		$options['transparency'] = min(100, max(0,intval($options['transparency'])));
		
		if($options['format']=='auto' && $img['type'] == IMAGETYPE_GIF || $options['format'] == 'gif') return $this->_renderImage($img['res'], IMAGETYPE_GIF, false, strtolower($targetpath));
		if($options['format']=='auto' && $img['type'] == IMAGETYPE_JPEG || preg_match('/jp(e?+)g/', $options['format'])) return $this->_renderImage($img['res'], IMAGETYPE_JPEG, false, strtolower($targetpath));
		if($options['format']=='auto' && $img['type'] == IMAGETYPE_PNG || $$options['format']=='png') return $this->_renderImage($img['res'], IMAGETYPE_PNG, false, strtolower($targetpath));
		return false;
	}
}
	