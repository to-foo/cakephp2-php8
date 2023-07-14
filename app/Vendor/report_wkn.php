<?php
App::import('Vendor','tcpdf/tcpdf');
App::import('Vendor','report');

class WKNTCPDF  extends XTCPDF {
	function Header() {
		if(trim($this->xdata->Testingmethod->value) != 'wkn') parent::Header();
		else {

			// Logo Wacker
			if(file_exists(Configure::read('company_logo_folder').DS.'1'.DS.'logo.png')){
				$size = getimagesize(Configure::read('company_logo_folder').DS.'1'.DS.'logo.png');

				$this->Image(Configure::read('company_logo_folder').DS.'1'.DS.'logo.png',
				$this->defs['PDF_MARGIN_LEFT'] + 12,
				$this->defs['PDF_MARGIN_TOP']-7,
				30,
				0,
				'PNG', false, '', false, '', '', false, false, 0, false, false, false);
			}
		}
		$this->bodyStart[$this->getPage()] = $this->HaederEnd = $this->GetY();

	}

	function Footer() {
		if(trim($this->xdata->Testingmethod->value) != 'wkn') parent::Footer();
	}
}
