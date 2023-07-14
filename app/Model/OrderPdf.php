<?php
App::uses('AppModel', 'Model');
App::import('Vendor', 'pdf_parser');
class OrderPdf extends AppModel {
	public $useTable = false;
	
	protected function _getObjectOptions($data)
	{
		if(!preg_match('/<<(.*)>>/', $data, $options)) { $options = array('1'=>null); }
		
		return $options[1];		
	}
	
	protected function _getObjectStream($obj)
	{
		if(preg_match('/stream(.*)endstream/ismU', $obj, $stream))
		{
			return ltrim($stream[1]);
		}
		else
		{
			return '';
		}
	}
	
	protected function _uncompress($data, $filter=null)
	{
		switch(strtolower($filter))
		{
			case 'flatedecode':
				return gzuncompress($data);
				
			default:
				return $data;
		}
	}
	
	public function getText($file = null) {
		if(!file_exists($file)) return null;
		
		$file = file_get_contents($file, FILE_BINARY);
		if(empty($file)) return null;
		
		$parser = new PdfParser($file);
		
		/*
		//pr($parser);
		
		$texts = array();
		if(preg_match_all('/([\d]+ [\d]+) obj(.*?)endobj/s', $file, $objects))
		{
			$objects = array_combine($objects[1], array_map('trim', $objects[2]));

			foreach($objects as $key=>$content)
			{
				if(!preg_match('/>>\s(.*)/s', $content, $body)) { $body = array('1'=>null); }
				$options = $this->_getObjectOptions($content);
				$stream = $this->_getObjectStream($content);
								
				if(preg_match('/\/Filter\/([a-zA-Z]+)/', $options, $filter)) {
					$stream = $this->_uncompress($stream, $filter[1]);
				}
				
				foreach(explode(PHP_EOL, $stream) as $line) {
					if(strpos($stream, 'TJ') !== false && preg_match_all('/\(([^\(\)]+)\)/iU', $line, $text))
						$texts[] = join('', $text[1]); 
				}
			}
			$texts = array_values(array_filter($texts, function($elem) { $elem = trim($elem); return $elem != null && !empty($elem) && $elem != 'x-none';}));
			$texts = array_map(function($elem) {return trim(trim($elem), '.:'); }, $texts);
			//pr($texts);
		}
		*/
		
		// get text with numbered lines
		return $parser->getParsedText();
		
		// use line-positions in units instead of line-numbers
		return $parser->getParsedTextLines();
	}
}
