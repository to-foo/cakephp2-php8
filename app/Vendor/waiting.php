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
			// muss noch automatisiert werden
			// Logo Betreiber
			$this->Image('../files/1/rwe.png', 22, 15, 50, 0, 'PNG', false, '', false, '', '', false, false, 0, false, false, false);
			// Logo Prüfunternehmen
			$this->Image(Configure::read('company_logo_folder').'1'.DS.'dekra.png', 157, 12, 40, 0, 'PNG', false, '', false, '', '', false, false, 0, false, false, false);
			// Überschrift
			$this->SetFont('helveticaB', 'B', 12);
			$this->MultiCell(
					180,
					14,
					"Bestätigung über betriebsbedingte Wartezeiten durch die Fachmeisterei oder Referent bzw. Auftraggeber",
					1,
					'C',
					false,
					2,
					20,
					30,
					true,
					0,
					false,
					true,
					0,
					'M',
					true
				);
				
			$this->Ln(5,false);
			$this->CellHeight = 7;

//pr($this->thisOrder['Order']);

			$WaitingOrderArray = array(
					1 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'I',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '0',
						'width' => '40', 
						'border' => 'LTRB',
						'content' => 'Datum: '
					),
					2 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'N',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '0',
						'width' => '50', 
						'border' => 'B',
						'content' => $this->Waitings['generally']['FirstStartWaiting'].' - '.$this->Waitings['generally']['LastStopWaiting']
					),
					3 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'I',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '0',
						'width' => '40', 
						'border' => 'LTRB',
						'content' => 'Auftragsnr.:'
					),
					4 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'N',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '1',
						'width' => '50', 
						'border' => 'B',
						'content' => $this->thisOrder['Order']['auftrags_nr']
					),
					5 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'I',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '0',
						'width' => '40', 
						'border' => 'LTRB',
						'content' => 'SAP-Nr.:'
					),
					6 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'N',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '0',
						'width' => '50', 
						'border' => 'B',
						'content' => $this->thisOrder['Order']['auftrags_nr']
					),
					7 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'I',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '0',
						'width' => '40', 
						'border' => 'LTRB',
						'content' => 'Block:'
					),
					8 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'N',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '1',
						'width' => '50', 
						'border' => 'B',
						'content' => $this->thisOrder['Order']['block']
					),
					9 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'I',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '0',
						'width' => '40', 
						'border' => 'LTRB',
						'content' => 'KKS:'
					),
					10 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'N',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '1',
						'width' => '140', 
						'border' => 'B',
						'content' => $this->thisOrder['Order']['kks']
					),																																			
					11 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'I',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '0',
						'width' => '40', 
						'border' => 'LTRB',
						'content' => 'Bauteil:'
					),
					12 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'N',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '1',
						'width' => '140', 
						'border' => 'B',
						'content' => $this->thisOrder['Order']['bauteil']
					),																																			
				);
							
			foreach ($WaitingOrderArray as $_WaitingOrderArray){
				
				// Start Auftragsdaten
				$this->SetFont(
						$_WaitingOrderArray['fonts']['font'], 
						$_WaitingOrderArray['fonts']['style'], 
						$_WaitingOrderArray['fonts']['size']
					);
				
				$this->setCellPaddings(
						$_WaitingOrderArray['padding']['l'],
						$_WaitingOrderArray['padding']['t'],
						$_WaitingOrderArray['padding']['r'],
						$_WaitingOrderArray['padding']['b']
					); 
					
				$this->MultiCell(
					$_WaitingOrderArray['width'],
					$this->CellHeight,
					$_WaitingOrderArray['content'],
					$_WaitingOrderArray['border'],
					'L',
					false,
					$_WaitingOrderArray['break'],
					$this->GetX(),
					$this->GetY(),
					true,
					0,
					false,
					false,
					$this->CellHeight,
					'M',
					true
				);
				// Ende Auftragsdaten
			}
			
			$this->Ln(5,false);
			
			$WaitingOrderArray= array();
			
			$WaitingOrderArray = array(
					1 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'I',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '0',
						'width' => '40', 
						'border' => 'LTRB',
						'content' => 'Bestellte Arbeitsleistung: '
					),
					2 => array(
						'fonts' => array(
							'font' => 'helvetica',
							'style' => 'N',
							'size' => '9',
						),
						'padding' => array(
							't' => 1,
							'r' => 1,
							'b' => 1,
							'l' => 1,
						),
						'break' => '1',
						'width' => '140', 
						'border' => 'B',
						'content' => $this->Waitings['generally']['ordered_work']
					)
				);	

			foreach ($WaitingOrderArray as $_WaitingOrderArray){
				
				// Start Auftragsdaten
				$this->SetFont(
						$_WaitingOrderArray['fonts']['font'], 
						$_WaitingOrderArray['fonts']['style'], 
						$_WaitingOrderArray['fonts']['size']
					);
				
				$this->setCellPaddings(
						$_WaitingOrderArray['padding']['l'],
						$_WaitingOrderArray['padding']['t'],
						$_WaitingOrderArray['padding']['r'],
						$_WaitingOrderArray['padding']['b']
					); 
					
				$this->MultiCell(
					$_WaitingOrderArray['width'],
					$this->CellHeight,
					$_WaitingOrderArray['content'],
					$_WaitingOrderArray['border'],
					'L',
					false,
					$_WaitingOrderArray['break'],
					$this->GetX(),
					$this->GetY(),
					true,
					0,
					false,
					false,
					$this->CellHeight,
					'M',
					true
				);
				// Ende Auftragsdaten
			}			

			$this->Ln(5,false);

			$this->MultiCell(
					180,
					7,
					"Länge der Wartezeiten und Anzahl der Personen",
					1,
					'C',
					false,
					1,
					$this->GetX(),
					$this->GetY(),
					true,
					0,
					false,
					true,
					7,
					'M',
					true
				);

		$this->Ln(5,false);
		$this->HaederEnd = $this->GetY();
		}
		
function Footer() {

	}
}