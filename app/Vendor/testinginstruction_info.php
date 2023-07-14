<?php
App::import('Vendor','tcpdf/tcpdf');
class XTCPDF extends TCPDF
{
        var $xsettings  = array();
        var $data  = array();

	function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

	   TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibri.ttf', 'TrueTypeUnicode', '', 32);
	   TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrib.ttf', 'TrueTypeUnicode', '', 32);
	   $this->AddFont('calibri', 'B', 'calibrib.php');

	   TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrii.ttf', 'TrueTypeUnicode', '', 96);
	   $this->AddFont('calibri', 'I', 'calibrii.php');

	   TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibriz.ttf', 'TrueTypeUnicode', '', 96);
	   $this->AddFont('calibri', 'BI', 'calibriz.php');
	}

	/*protected function _getFontFormatting($item, $fonts=array()) {
		$fonts = array_merge(array(
			'title' => array(
				'n'=>'calibri',
				'b'=>'calibrib',
				'i'=>'calibrii',
				'bi'=>'calibriz',
			),
			'data' => array(
				'n'=>'calibri',
				'b'=>'calibrib',
				'i'=>'calibrii',
				'bi'=>'calibriz',
			)
		), $fonts);

		$style = array('', '');
		$font = array('', '');
		if(isset($item['formatting']['bold']) && !empty($item['formatting']['bold']))
		{
			$bold = explode(' ', $item['formatting']['bold']);
			if(count($bold) == 1) $bold[1] = $bold[0];
			$style[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'B' : '';
			$font[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'b' : '';
			$style[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'B' : '';
			$font[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'b' : '';
		}

		if(isset($item['formatting']['italic']) && !empty($item['formatting']['italic']))
		{
			$bold = explode(' ', $item['formatting']['italic']);
			if(count($bold) == 1) $bold[1] = $bold[0];
			$style[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'I' : '';
			$font[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'i' : '';
			$style[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'I' : '';
			$font[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'i' : '';
		}

		if(isset($item['formatting']['underline']) && !empty($item['formatting']['underline']))
		{
			$bold = explode(' ', $item['formatting']['underline']);
			if(count($bold) == 1) $bold[1] = $bold[0];
			$style[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'U' : '';
			$style[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'U' : '';
		}

		$fonts = array_values($fonts);

		foreach($font as $num=>$fontstyle) {
			if(empty($fontstyle)) $fontstyle = 'n';

			$font[$num] = $fonts[$num][$fontstyle];
		}

		return array($style, $font);
	}*/

	public function Header() {
       // pr($this->xdata); die();
      $TestinginstructionsPdf = 'TestinginstructionsDataPdf';

// Überschrift 1
			if(trim($this->xsettings->$TestinginstructionsPdf->settings->QM_HEADLINE_01->VALUE) != ''){
				$this->SetFont('calibri', 'n', 14);
				$this->MultiCell(
								0,
								0,
								$this->xdata['name'] ,
								0,
								'L',
								0,
								1,
								$this->xsettings->$TestinginstructionsPdf->settings->QM_HEADLINE_01->POS_X,
								$this->xsettings->$TestinginstructionsPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y,
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);
			}
			// zweite Überschrift z.B. englisch
			if(trim($this->xsettings->$TestinginstructionsPdf->settings->QM_HEADLINE_02->VALUE) != ''){
				$this->SetFont('calibri', 'n', 10);
				$this->MultiCell(
								0,
								0,
								trim($this->xsettings->$TestinginstructionsPdf->settings->QM_HEADLINE_02->VALUE),
								0,
								'L',
								0,
								1,
								$this->xsettings->$TestinginstructionsPdf->settings->QM_HEADLINE_02->POS_X,
								$this->GetY(),
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);
			}
$CompanyLogoFolder = Configure::read('company_logo_folder').$this->xdata['Testingcomp']['id'].DS;
                        // Firmenlogo

					$CompanyLogoFolder = Configure::read('company_logo_folder').$this->xdata['Testingcomp']['id'].DS;

					if ($handle = opendir($CompanyLogoFolder)) {

					    while (false !== ($entry = readdir($handle))) {
									if(file_exists($CompanyLogoFolder . $entry) && filetype($CompanyLogoFolder . $entry) == 'file'){

										$LogoInfo = mime_content_type($CompanyLogoFolder . $entry);

										switch($LogoInfo){
											case 'image/svg+xml':
											$this->ImageSVG(
												$CompanyLogoFolder . $entry,
											$this->xsettings->$TestinginstructionsPdf->settings->QM_LOGO_X,
											$this->xsettings->$TestinginstructionsPdf->settings->QM_LOGO_Y,
											$this->xsettings->$TestinginstructionsPdf->settings->QM_LOGO_WIDTH,
											$this->xsettings->$TestinginstructionsPdf->settings->QM_LOGO_HEIGHT,
												'',
												'',
												'',
												0,
												false);
											break;
										}
									}
					    }

					    closedir($handle);
					}
        $this->Ln();

			/*if(!empty($this->xsettings->$TestinginstructionsPdf->settings->QM_ADDITIONAL_LOGOS->LOGO)) {
				foreach($this->xsettings->$TestinginstructionsPdf->settings->QM_ADDITIONAL_LOGOS->LOGO as $_additional_logos){
                                if(file_exists(Configure::read('company_logo_folder').trim($this->xdata ['Testingcomp'] ['id']).DS.'additional'.DS.trim($_additional_logos->LOGO_NAME))){
						$this->Image(Configure::read('company_logo_folder').trim($this->xdata ['Testingcomp'] ['id']).DS.'additional'.DS.trim($_additional_logos->LOGO_NAME),
						$_additional_logos->POS_X,
						$_additional_logos->POS_Y,
						$_additional_logos->POS_H,
						$_additional_logos->POS_W,
						'PNG', false, '', false, '', '', false, false, 0, false, false, false);
					}
				}
			} */






   			if($this->xsettings->$TestinginstructionsPdf->settings->QM_FIRM_ADRESS_FORMAT->SHOW_ADRESS == 1){
				$Firmenadresse  = null;
				$Firmenadresse  .=  trim($this->xdata['Testingcomp'] ['firmenname']) . PHP_EOL;
				$Firmenadresse  .= 	trim($this->xdata['Testingcomp'] ['plz']) . " ";
				$Firmenadresse  .= 	trim($this->xdata['Testingcomp'] ['ort']) . PHP_EOL;
				$Firmenadresse  .= 	trim($this->xdata['Testingcomp'] ['strasse']) . PHP_EOL;
				if(trim($this->xdata['Testingcomp'] ['telefon']) != '') $Firmenadresse  .= 'Tel. ' . trim($this->xdata['Testingcomp'] ['telefon']) . ', ';
				if(trim($this->xdata['Testingcomp'] ['telefax']) != '') $Firmenadresse  .= 'Fax. ' . trim($this->xdata['Testingcomp'] ['telefax']) . PHP_EOL;
				if(trim($this->xdata['Testingcomp'] ['email']) != '') $Firmenadresse  .= trim($this->xdata['Testingcomp'] ['email']);
				if(trim($this->xdata['Testingcomp'] ['internet']) != '') $Firmenadresse  .= ', ' . trim($this->xdata['Testingcomp'] ['internet']);

				$Firmenadresse = preg_replace('/[\s,]+$/', '', $Firmenadresse);
				$this->SetFont('calibri', 'n', 7);
				$this->MultiCell(
								0,
								0,
								$Firmenadresse,
								0,
								'L',
								0,
								1,
								$this->xsettings->$TestinginstructionsPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_X,
								$this->xsettings->$TestinginstructionsPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y,
								true,
                0,
								false,
								true,
								0,
								'T',
								true
							);

			}

	}

	function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Seite '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

	}
}
