<?php
App::import('Vendor','waiting');

// Cellpadding definieren
define('QM_CELL_PADDING_T', 1);
define('QM_CELL_PADDING_R', 1);
define('QM_CELL_PADDING_B', 1);
define('QM_CELL_PADDING_L', 1);

define('QM_CELL_LAYOUT_LINE_TOP', 10);
define('QM_CELL_LAYOUT_LINE_BOTTOM', 280);
define('QM_CELL_LAYOUT_WIDTH', 180);
define('QM_CELL_LAYOUT_CLEAR', 200);

// Seitenumbruch
define('QM_PAGE_BREAKE', 235);
define('QM_START_FOOTER', 255);
define('QM_LINE_HEIGHT', 3);

// neues Objekt anlegen 
$tcpdf = new XTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


// Einstellungen und Daten aus dem Controller importieren
$tcpdf->projectID = $projectID;
$tcpdf->orderID = $orderID;
$tcpdf->thisOrder = $thisOrder;
$tcpdf->Waitings = $Waitings;

$tcpdf->SetCreator(PDF_CREATOR);
$tcpdf->SetAuthor('DEKRA Duisburg');
$tcpdf->SetTitle('ZfP Prüfbericht');
$tcpdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$tcpdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$tcpdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$tcpdf->setCellPaddings(QM_CELL_PADDING_L, QM_CELL_PADDING_T, QM_CELL_PADDING_R, QM_CELL_PADDING_B);

// Linien
$tcpdf->style1 = array('width' => 0.25, 'color' => array(0, 0, 0));			
$tcpdf->style2 = array('B' => array('width' => 0.15, 'color' => array(0, 0, 0)));			
$tcpdf->style3 = array('width' => 0.15, 'color' => array(0, 0, 0));			

// set font
$tcpdf->SetFont('helvetica', 'B', 7);
// add a page
$tcpdf->AddPage();
$tcpdf->SetY($tcpdf->HaederEnd);
/*
		$tcpdf->MultiCell(
			20,
			7,
			"Prüfberichts-Nr.",
			1,
			'L',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);
*/		
		$tcpdf->MultiCell(
			30,
			7,
			"Wartezeit von",
			1,
			'L',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);
		$tcpdf->MultiCell(
			30,
			7,
			"Wartezeit bis",
			1,
			'L',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);
		$tcpdf->MultiCell(
			12,
			7,
			"Stunden",
			1,
			'R',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);
		
		$tcpdf->MultiCell(
			73,
			7,
			"Grund der Wartezeit",
			1,
			'L',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);								
		$tcpdf->MultiCell(
			35,
			7,
			"Prüfer/F-Nr.",
			1,
			'L',
			false,
			1,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);

// set font
$tcpdf->SetFont('helvetica', 'N', 7);

$tcpdf->TotalHours = 0;

// Kleine Korrektur wenn der Arbeitsgrund nicht ausgefüllt wurde
if($tcpdf->Waitings['generally']['ordered_work'] = null){
	$tcpdf->Waitings['generally']['ordered_work'] = "-"; 
}

foreach($tcpdf->Waitings as $_waitings){

if(isset($_waitings['ordered_work'])){
	pr($_waitings['ordered_work']);
}

if(isset($_waitings['data']) && is_array($_waitings['data'])){

	foreach($_waitings['data'] as $_data){

		if($_data['active'] == 1){
		
		// Gesamtstunden
		$tcpdf->TotalHours = $tcpdf->TotalHours + number_format($_data['waiting_time'], 2, '.', ',');
		/*
		$tcpdf->MultiCell(
			20,
			7,
			$_data['verfahren']."-".$_data['number'],
			1,
			'L',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);
		*/
		$tcpdf->MultiCell(
			30,
			7,
			$_data['waiting_start'],
			1,
			'L',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);
		$tcpdf->MultiCell(
			30,
			7,
			$_data['waiting_stop'],
			1,
			'L',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);
		$tcpdf->MultiCell(
			12,
			7,
			number_format($_data['waiting_time'], 2, '.', ','),
			1,
			'R',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);
		
		$tcpdf->MultiCell(
			73,
			7,
			$_data['reason'],
			1,
			'L',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);								
		$tcpdf->MultiCell(
			35,
			7,
			$_waitings['name']." - ".$_waitings['number'],
			1,
			'L',
			false,
			1,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			7,
			'T',
			true
		);
		}
	}		
}		
}

$tcpdf->Ln(5,false);

//pr($tcpdf->TotalHours);

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
						'width' => '70', 
						'border' => 'LTRB',
						'content' => 'Gesamtstunden: '
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
						'width' => '110', 
						'border' => 'B',
						'content' => $tcpdf->TotalHours.' h'
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
						'width' => '70', 
						'border' => 'LTRB',
						'content' => 'Veranlassende Abteilung: '
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
						'width' => '110', 
						'border' => 'B',
						'content' => ' '
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
						'width' => '70', 
						'border' => 'LTRB',
						'content' => 'Unterschriftsberechtigte Person: '
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
						'break' => '1',
						'width' => '110', 
						'border' => 'B',
						'content' => ' '
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
						'width' => '70', 
						'border' => 'LTRB',
						'content' => 'DEKRA Incos Unterschriftsberechtigte Person: '
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
						'width' => '110', 
						'border' => 'B',
						'content' => ' '
					),														
				);

			foreach ($WaitingOrderArray as $_WaitingOrderArray){
				
				// Start Auftragsdaten
				$tcpdf->SetFont(
						$_WaitingOrderArray['fonts']['font'], 
						$_WaitingOrderArray['fonts']['style'], 
						$_WaitingOrderArray['fonts']['size']
					);
				
				$tcpdf->setCellPaddings(
						$_WaitingOrderArray['padding']['l'],
						$_WaitingOrderArray['padding']['t'],
						$_WaitingOrderArray['padding']['r'],
						$_WaitingOrderArray['padding']['b']
					); 
					
				$tcpdf->MultiCell(
					$_WaitingOrderArray['width'],
					$tcpdf->CellHeight,
					$_WaitingOrderArray['content'],
					$_WaitingOrderArray['border'],
					'L',
					false,
					$_WaitingOrderArray['break'],
					$tcpdf->GetX(),
					$tcpdf->GetY(),
					true,
					0,
					false,
					false,
					$tcpdf->CellHeight,
					'M',
					true
				);
				// Ende Auftragsdaten
			}

echo $tcpdf->Output('watingreport_'.$tcpdf->projectID.'_'.$tcpdf->orderID.'.pdf', 'D');