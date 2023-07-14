<?php
App::import('Vendor','tcpdf/tcpdf');
class XTCPDF extends TCPDF
{
        var $xsettings  = array();
	var $xdata  = array();
	var $xfooterfon;
	var $xfooterfontsize = 8 ;
	public $defs = null;

	function __construct($orientation='L', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibri.ttf', 'TrueTypeUnicode', '', 32);
	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrib.ttf', 'TrueTypeUnicode', '', 32);
	$this->AddFont('calibri', 'B', 'calibrib.php');

	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrii.ttf', 'TrueTypeUnicode', '', 96);
	$this->AddFont('calibri', 'I', 'calibrii.php');

	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibriz.ttf', 'TrueTypeUnicode', '', 96);
	$this->AddFont('calibri', 'BI', 'calibriz.php');
	}

    function writeSettings($defs) {
	$this->defs = $defs;

	//$this->qr = null;
	$this->xfooterfon = $defs['PDF_FONT_NAME_MAIN'];
    }

   function Header() {
    $ExaminerPdf = 'WelderPdf';

    //pr($this->xsettings); die();
    // Überschrift 1
		/*	if(trim($this->xsettings->$DevicePdf->settings->QM_HEADLINE_01->VALUE) != ''){
				$this->SetFont('calibri', 'n', 14);
				$this->MultiCell(
								0,
								0,
								trim($this->xsettings->$DevicePdf->settings->QM_HEADLINE_01->VALUE),
								0,
								'L',
								0,
								1,
								$this->xsettings->$DevicePdf->settings->QM_HEADLINE_01->POS_X,
								$this->xsettings->$DevicePdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y,
								true,
                                                                0,
								false,
								true,
								0,
								'T',
								true
							);

                        }
// Firmenlogo
			if(file_exists(Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS.'logo.png') && $this->xdata->Testingcomp->id != 0){
				$this->Image(Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS.'logo.png',
				$this->xsettings->$DevicePdf->settings->QM_LOGO_X,
				$this->xsettings->$DevicePdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y,
				$this->xsettings->$DevicePdf->settings->QM_LOGO_HEIGHT,
				$this->xsettings->$DevicePdf->settings->QM_LOGO_WIDTH,
				'PNG', false, '', false, '', '', false, false, 0, false, false, false);
                        }
    */
//*Überschrift


	if(trim($this->xsettings->$ExaminerPdf->settings->QM_HEADLINE_01->VALUE) != ''){
				$this->SetFont('calibri', 'n', 14);
				$this->MultiCell(
								0,
								0,
								trim($this->xsettings->$ExaminerPdf->settings->QM_HEADLINE_01->VALUE),
								0,
								'L',
								0,
								1,
								$this->xsettings->$ExaminerPdf->settings->QM_HEADLINE_01->POS_X,
								$this->xsettings->$ExaminerPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y,
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
			if(trim($this->xsettings->$ExaminerPdf->settings->QM_HEADLINE_02->VALUE) != ''){
				$this->SetFont('calibri', 'n', 10);
				$this->MultiCell(
								0,
								0,
								trim($this->xsettings->$ExaminerPdf->settings->QM_HEADLINE_02->VALUE),
								0,
								'L',
								0,
								1,
								$this->xsettings->$ExaminerPdf->settings->QM_HEADLINE_02->POS_X,
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


        if(file_exists($this->xdata['Testingcomp']['logo'])){
	$this->Image($this->xdata['Testingcomp']['logo'],
	$this->xsettings->$ExaminerPdf->settings->QM_LOGO_X,
	$this->xsettings->$ExaminerPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y,
	$this->xsettings->$ExaminerPdf->settings->QM_LOGO_HEIGHT,
	$this->xsettings->$ExaminerPdf->settings->QM_LOGO_WIDTH,
	'PNG', false, '', false, '', '', false, false, 0, false, false, false);
        }
        $this->Ln();

        			if($this->xsettings->$ExaminerPdf->settings->QM_FIRM_ADRESS_FORMAT->SHOW_ADRESS == 1){
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
								$this->xsettings->$ExaminerPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_X,
                                                                $this->xsettings->$ExaminerPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y,
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);


                                }
                                $this->Ln();


    }



}
