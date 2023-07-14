<?php
App::import('Vendor','tcpdf/tcpdf_parser');

class PdfParser extends TCPDF_PARSER {
	protected $root = null;
	protected $resources = null;
	protected $content = null;
	protected $texts = array();
	
	protected function xpath($array = array(), $path = null, $default = null)
	{
		if(!is_object($array) && !is_array($array)) {
			return $default;
		}

	    // specify the delimiter
	    $delimiter = '/';
	 
	    // fail if the path is empty
	    if (empty($path)) {
	        return $default;
	    }
	 
	    // remove all leading and trailing slashes
	    $path = trim($path, $delimiter);
	 
	    // use current array as the initial value
	    $value = $array;
	 
	    // extract parts of the path
	    $parts = explode($delimiter, $path);
	 
	    // loop through each part and extract its value
	    foreach ($parts as $part) {
	        if (isset($value[$part])) {
	            // replace current value with the child
	            $value = $value[$part];
	        } else {
	            // key doesn't exist, fail
	            return $default;
	        }
	    }
	 
	    return $value;
	}
	
	protected function array_rmsearch($needle, $haystack) {
		if (empty($needle) || empty($haystack) || !(is_array($haystack) || is_object($haystack))) {
			return false;
		}
		
		foreach ($haystack as $key => $value) {
			$exists = true;
			foreach ($needle as $skey => $svalue) {
				$exists = ($exists && IsSet($haystack[$key][$skey]) && $haystack[$key][$skey] == $svalue);
			}
			if($exists) { return $key; }
			elseif(is_array($value)) {
				$search = $this->array_rmsearch($needle, $value);
				if($search !== false) return $key.' '.$search; 
			}
				
		}
		
		return false;
	}
	
	public function __construct($data, $cfg = array()) {
		parent::__construct($data, $cfg);
		
		$this->root = $this->objects[$this->xref['trailer']['root']];
		/*
		$path = explode(' ', $this->array_rmsearch(array('/', 'Pages'), $this->root));
		pr($this->xpath($this->root, join('/', $path)));
		$path[end(array_keys($path))] = end($path)+1;
		$pages = $this->xpath($this->root, join('/', $path));
		$pages = $this->objects[$pages[1]];
		*/
		$path = array_slice(explode(' ', $this->array_rmsearch(array('/','Contents'), $this->objects)), 0, -1);
		$contents = array_slice($this->xpath($this->objects, join('/', $path)), 1);
		$this->resources = ($contents[$this->array_rmsearch(array('/', 'Resources'), $contents)+1]);
		$this->content = array_map(function($elem) { return $elem[1]; }, reset(array_slice(reset($contents), 1, -1)));

		$texts = '';
		// Alle Inhaltsblöcke zu einem Block zusammensetzen
		foreach($this->content as $content) {
			$texts .= reset($this->objects[$content][1][3]).PHP_EOL;
		}
		
		$this->content = $texts;
		
		$texts = array();
		//pr($this->content);
		if(!preg_match_all('/(.*T[Jm])/', $this->content, $cmds)) continue;
		
		$x = 0; $y = 0;
		foreach($cmds[1] as $cmd) {
			if(substr($cmd, -2) == 'Tm') {
				$cmd = explode(' ', $cmd);
				$x = $cmd[4];
				$y = $cmd[5];
			} elseif(substr($cmd, -2) == 'TJ') {
				if(!preg_match_all('/\(([^\(\)]+)\)/iU', $cmd, $text)) continue;
				
				$texts[intval($y)][intval($x)] = utf8_encode(join('', $text[1]));
				ksort($texts[intval($y)]);
			}
		}
		
		// Zeilen trimmen und Leerzeilen entfernen
		$texts = array_map(function($line) { return array_filter(array_map('trim', $line)); }, $texts);

		// TODO: Bildresourcen ($this->resources) einsortieren und Erkennung, ob angehakt oder nicht

		ksort($texts);
		//pr($texts);
		$this->texts = $texts;
	}

	public function getParsedTextLines() {
		return array_reverse($this->texts, true);
	}

	public function getParsedText() {
		return array_reverse(array_map('array_values', $this->texts));
	}
}
