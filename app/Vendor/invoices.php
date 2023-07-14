<?php
App::import('Vendor','tcpdf/tcpdf');

class XTCPDF  extends TCPDF
{
	var $xheadertext  = 'PDF creado using CakePHP y TCPDF';
	var $xheadercolor = array(255,255,255);
	var $xfootertext  = 'Copyright © %d XXXXXXXXXXX. All rights reserved.';
	var $xdata  = array();
	var $xsettings  = array();
	var $Verfahren  = null;
	var $dataArray = array();
	var $xfooterfont  = PDF_FONT_NAME_MAIN ;
	var $xfooterfontsize = 8 ;
	
function Header() { 
			list($r, $b, $g) = $this->xheadercolor;
			
			// die Außenlinien zeichen horizontal
			$this->Line(QM_X_L,QM_Y_O,QM_X_R,QM_Y_O,$this->style4); 	
			$this->Line(QM_X_L,QM_Y_U,QM_X_R,QM_Y_U,$this->style4); 	

			// die Außenlinien zeichen vertikal
			$this->Line(QM_X_L,QM_Y_O,QM_X_L,QM_Y_U,$this->style4); 	
			$this->Line(QM_X_R,QM_Y_O,QM_X_R,QM_Y_U,$this->style4); 	

			$this->SetFont('helveticaB', 'B', 12);
			$this->MultiCell(
					275,
					8,
					"Aufmaßblatt ZfP",
					1,
					'C',
					false,
					1,
					QM_X_L,
					QM_Y_O,
					true,
					0,
					false,
					true,
					8,
					'M',
					true
				);
				
			$line = $this->InvoicesLine;

			$this->SetX(QM_X_L);
			$this->SetFont('helveticaB', 'N', 10);
			$cellheight = 6;

			// Seitenzahlen einfügen
			$line[1][4]['text'] = $this->getAliasNumPage();
			$line[4][5]['text'] = $this->getAliasNbPages();

			foreach($line as $_line){
				foreach($_line as $__line){
				$this->SetTextColor($__line['textcolor'][0],$__line['textcolor'][1],$__line['textcolor'][2]);	
				$this->MultiCell(
					$__line['width'],
					$__line['height'],
					$__line['text'],
					$__line['border'],
					'C',
					$__line['fill'],
					0,
					$this->GetX(),
					$this->GetY(),
					true,
					0,
					false,
					true,
					$__line['height'],
					'M',
					true
				);
				}
			$this->Ln();
			$this->SetX(QM_X_L);
			}

		// die Trennlinie zum Footer zeichen horizontal
		$this->Line(QM_X_L,$this->GetY(),QM_X_R,$this->GetY(),$this->style4); 	

		$this->HaederEnd = $this->GetY();
		}
		
function Footer() {

		// die Trennlinie zum Footer zeichen horizontal
		$this->Line(QM_X_L,QM_PAGE_BREAKE,QM_X_R,QM_PAGE_BREAKE,$this->style4); 	
		$this->SetX(QM_X_L);
		$this->SetY(QM_PAGE_BREAKE);

		$this->SetFont('helvetica', 'N', 8);
		$this->SetLineStyle($this->style3);
		$this->Rect(48,180,4,4,array(),$this->style5,array()); 	
		$this->Text(54, 180, 'Vollkontrolle');
		$this->Arrow(100, 175.5, 100, 179, 2, 2, 14);
		$this->Line(100,175.5,145,175.5,$this->style5); 	
		$this->Line(200,175.5,280,175.5,$this->style5); 	
		$this->Arrow(280, 175.5, 280, 172, 2, 2, 14);
		$this->Rect(98,180,4,4,array(),$this->style5,array()); 	
		$this->Text(104, 180, 'Teil-geprüft');
		$this->Rect(137,180,4,4,array(),$this->style5,array()); 	
		$this->Text(144, 180, 'nicht geprüft');

		$this->Text(249, 180, 'Summe');
		$this->Text(260, 180, $this->totalprice);

		$this->SetFont('helvetica', 'N', 7);
		$this->Line(47,190,97,190,$this->style5); 	
		$this->Text(47, 190, 'Datum');
		$this->Line(47,196,97,196,$this->style5); 	
		$this->Text(47, 196, 'Unterschrift Bauleiter');

		$this->Line(164,190,217,190,$this->style5); 	
		$this->Text(164, 190, 'Datum');
		$this->Line(164,196,217,196,$this->style5); 	
		$this->Text(164, 196, 'Unterschrift Fachbereich');

		$this->MultiCell(
			50,
			0,
			"bitte bei Teilprüfung, geprüfte Position ankreuzen",
			0,
			'C',
			false,
			0,
			147,
			173,
			true,
			0,
			false,
			true,
			5,
			'M',
			true
		);
	}
}