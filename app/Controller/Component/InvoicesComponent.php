<?php
class InvoicesComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function ArrayBildnummer() {
		$ArrayBildnummer['01'] = array(5);
		$ArrayBildnummer['02'] = array(1,2,3,4,6,7,8,9,10);
		$ArrayBildnummer['03'] = array(11,12,13,14,15,16);
		
		return $ArrayBildnummer;
	}

	public function ArraySoucre() {
		$ArraySoucre = array(
							'Se 75' => '01',
							'Ir 192' => '02',
							'Röntgenröhre' => '03',
						);
		
		return $ArraySoucre;
	}

	public function StatisticsStandardCreateRT($ReportsOrderTestingmethods,$verfahren) {

		$Verfahren = ucfirst($verfahren);
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$InvoiceQuerys = array();
		$dataArray = array();
		$WeldsTotal = 0;
		$WeldsComplet = 0;

		$ArrayBildnummer = $this->_controller->Invoices->ArrayBildnummer();
		$ArraySource = $this->_controller->Invoices->ArraySoucre();

		$VerfahrenArray[$verfahren] = $Verfahren;
		// die einzelnen Nähte pro Verfahren auslesen

		foreach($VerfahrenArray as $key => $value){
			if(isset($ReportsOrderTestingmethods[$key]['Reports']) && count($ReportsOrderTestingmethods[$key]['Reports']) > 0){
				foreach($ReportsOrderTestingmethods[$key]['Reports'] as $_ReportsOrderTestingmethods){
					// Dauer des Prüfberichts ausrechnen
					$TimeInStand = 
					@strtotime($_ReportsOrderTestingmethods[$ReportGenerally][$ReportGenerally]['time_stop']) - 
					@strtotime($_ReportsOrderTestingmethods[$ReportGenerally][$ReportGenerally]['time_start']);

					$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['infos'] = $_ReportsOrderTestingmethods['Reportnumber']; 	
					@$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['personal'] = $_ReportsOrderTestingmethods[$ReportGenerally][$ReportGenerally]['examiner'].'; '.$_ReportsOrderTestingmethods[$ReportGenerally][$ReportGenerally]['examinierer2']; 	
					@$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['beginn'] = $_ReportsOrderTestingmethods[$ReportGenerally][$ReportGenerally]['time_start']; 	
					@$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['end'] = $_ReportsOrderTestingmethods[$ReportGenerally][$ReportGenerally]['time_stop']; 	
				
					@$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['time'] = number_format((($TimeInStand / 60) / 60), 2,'.',','); 	
					@$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['radiation_source'] = $_ReportsOrderTestingmethods[$ReportSpecific][$ReportSpecific]['radiation_source'];
				
					foreach($ArraySource as $_ArraySourceKey => $_ArraySource){
						if(trim($_ReportsOrderTestingmethods[$ReportSpecific][$ReportSpecific]['radiation_source']) == $_ArraySourceKey){
						$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query'] = $_ArraySource;
						}
					}
					
					if(!isset($dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query'])){
						$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query'] = '0';
					}

					// Wartezeit muss noch mit rein 	
					
					// Nähte auflisten
					$weld_change = '';
					$film_number = 1;
					$film_10_24 = 0;
					$film_10_48 = 0;
					$resultENE = 'e';
					$resultENEAll = 0;
					$image_no_query = null;
				
					if(isset($_ReportsOrderTestingmethods[$ReportEvaluation])){
						foreach($_ReportsOrderTestingmethods[$ReportEvaluation] as $_key => $_ReportEvaluation){
					
							$naht = (trim($_ReportEvaluation[$ReportEvaluation]['description']));
							$dimension = (trim($_ReportEvaluation[$ReportEvaluation]['dimension']));
							$film_dimension = (trim($_ReportEvaluation[$ReportEvaluation]['film_dimension']));
							$image_no = (trim($_ReportEvaluation[$ReportEvaluation]['image_no']));
					
							$result_ = (trim($_ReportEvaluation[$ReportEvaluation]['result']));

							if($result_ == 0){$result = '-';}
							if($result_ == 1){$result = 'e';}
							if($result_ == 2){$result = 'ne';}					
					
							// Nahtwechsel überwachen
							if($film_number == 1){
								$weld_change = $naht;
							}
							if($weld_change != $naht){
								$weld_change = $naht;
								$film_number = 1;
								$resultENE = 'e';
							}
					
							// Test ob Reparatur
							if($result == 'ne'){
								$resultENE = 'ne';
								// NE-Nähte zählen
								$resultENEAll++;
							}
					
							foreach($ArrayBildnummer['01'] as $__ArrayBildnummer){
								if($__ArrayBildnummer == $image_no){
									$image_no_query = '01';
									break;
								}
							}
							foreach($ArrayBildnummer['02'] as $__ArrayBildnummer){
								if($__ArrayBildnummer == $image_no){
								$image_no_query = '02';
									break;
								}
							}
							foreach($ArrayBildnummer['03'] as $__ArrayBildnummer){
								if($__ArrayBildnummer == $image_no){
									$image_no_query = '03';
									break;
								}
							}
					
							// hier wird das Array erstellt das später die Nähte in Strahlenquelle, Bildnummer und Abmessung aufteilt
							if(isset($dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query']) && isset($image_no_query)){
								$InvoiceQuerys[$key][$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query']][$image_no_query] = array();
							}
					
							// Das normale Datenarray wird mit den Werten der Auswertungen aufgefüllt
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['query3'] = $image_no_query;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['weld'] = $naht;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['dimension'] = $dimension;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['film_dimension'] = $film_dimension;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['image_no'] = $image_no;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['film'] = $film_number;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['result'] = $resultENE;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['weldnumber'] = count($dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution']);
					
							// Die Filme nach Größe zählen
							// die Werte für die Filmabmessungen, müssen noch ausgelagert werden
							if($film_dimension == '10x24'){$film_10_24++;}
							if($film_dimension == '10x48'){$film_10_48++;}
					
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['film_10_24'] = $film_10_24;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['film_10_48'] = $film_10_48;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['weld_ne'] = $resultENEAll;
							$film_number++;
						}
					}
				}
			}
		}

		// Test ob alle Pflichtfelder ausgefüllt sind
		$dataArrayError = array();

		foreach($dataArray as $_key => $_dataArray){
			foreach($_dataArray as $__key => $__dataArray){
				foreach($__dataArray['evalution'] as $___key => $___dataArray){
					if($___dataArray['image_no'] == '' || $___dataArray['dimension'] == '' || $__dataArray['radiation_source'] == ''){
						$dataArrayError[] = $dataArray[$_key ][$__key];
						unset($dataArray[$_key ][$__key]);
					}
				}
			}
		}

		$output['dataArrayError'] = $dataArrayError;		
		$output['WeldsTotal'] = $WeldsTotal;		
		$output['WeldsComplet'] = $WeldsComplet;		
		$output['dataArray'] = $dataArray;		
		$output['InvoiceQuerys'] = $InvoiceQuerys;		

		return $output;
	}

	public function StatisticsStandardCreatePT($ReportsOrderTestingmethods,$verfahren) {

		$Verfahren = ucfirst($verfahren);
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$InvoiceQuerys = array();
		$dataArray = array();
		$WeldsTotal = 0;
		$WeldsComplet = 0;

		$VerfahrenArray[$verfahren] = $Verfahren;
		// die einzelnen Nähte pro Verfahren auslesen

		foreach($VerfahrenArray as $key => $value){
			if(isset($ReportsOrderTestingmethods[$key]['Reports']) && count($ReportsOrderTestingmethods[$key]['Reports']) > 0){
				foreach($ReportsOrderTestingmethods[$key]['Reports'] as $_ReportsOrderTestingmethods){

					$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['infos'] = $_ReportsOrderTestingmethods['Reportnumber']; 	
									
					if(!isset($dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query'])){
						$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query'] = '0';
					}

					// Wartezeit muss noch mit rein 	
					
					// Nähte auflisten
					$weld_change = '';
					$film_number = 1;
					$film_10_24 = 0;
					$film_10_48 = 0;
					$resultENE = 'e';
					$resultENEAll = 0;
				
					if(isset($_ReportsOrderTestingmethods[$ReportEvaluation])){
						foreach($_ReportsOrderTestingmethods[$ReportEvaluation] as $_key => $_ReportEvaluation){
					
							$naht = (trim($_ReportEvaluation[$ReportEvaluation]['description']));
										
							// Nahtwechsel überwachen
							if($film_number == 1){
								$weld_change = $naht;
							}
							if($weld_change != $naht){
								$weld_change = $naht;
								$film_number = 1;
							}
															
							// hier wird das Array erstellt das später die Nähte in Strahlenquelle, Bildnummer und Abmessung aufteilt
							if(isset($dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query']) && isset($image_no_query)){
								$InvoiceQuerys[$key][$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query']][$image_no_query] = array();
							}
					
							// Das normale Datenarray wird mit den Werten der Auswertungen aufgefüllt
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['weld'] = $naht;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['result'] = $resultENE;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['weldnumber'] = count($dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution']);
						}
					}
				}
			}
		}
		
		$dataArrayError = array();

		$output['dataArrayError'] = $dataArrayError;		
		$output['WeldsTotal'] = $WeldsTotal;		
		$output['WeldsComplet'] = $WeldsComplet;		
		$output['dataArray'] = $dataArray;		
		$output['InvoiceQuerys'] = $InvoiceQuerys;		

		return $output;
	}

	public function StatisticsStandardCreate($ReportsOrderTestingmethods,$verfahren) {

		$Verfahren = ucfirst($verfahren);
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
		$ReportGenerally = 'Report'.$Verfahren.'Generally';
		$ReportSpecific = 'Report'.$Verfahren.'Specific';
		$InvoiceQuerys = array();
		$dataArray = array();
		$WeldsTotal = 0;
		$WeldsComplet = 0;

		$VerfahrenArray[$verfahren] = $Verfahren;
		// die einzelnen Nähte pro Verfahren auslesen

		foreach($VerfahrenArray as $key => $value){
			if(isset($ReportsOrderTestingmethods[$key]['Reports']) && count($ReportsOrderTestingmethods[$key]['Reports']) > 0){
				foreach($ReportsOrderTestingmethods[$key]['Reports'] as $_ReportsOrderTestingmethods){

					$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['infos'] = $_ReportsOrderTestingmethods['Reportnumber']; 	
									
					if(!isset($dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query'])){
						$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query'] = '0';
					}

					// Wartezeit muss noch mit rein 	
					
					// Nähte auflisten
					$weld_change = '';
					$film_number = 1;
					$film_10_24 = 0;
					$film_10_48 = 0;
					$resultENE = 'e';
					$resultENEAll = 0;
				
					if(isset($_ReportsOrderTestingmethods[$ReportEvaluation])){
						foreach($_ReportsOrderTestingmethods[$ReportEvaluation] as $_key => $_ReportEvaluation){
					
							$naht = (trim($_ReportEvaluation[$ReportEvaluation]['description']));
										
							// Nahtwechsel überwachen
							if($film_number == 1){
								$weld_change = $naht;
							}
							if($weld_change != $naht){
								$weld_change = $naht;
								$film_number = 1;
							}
															
							// hier wird das Array erstellt das später die Nähte in Strahlenquelle, Bildnummer und Abmessung aufteilt
							if(isset($dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query']) && isset($image_no_query)){
								$InvoiceQuerys[$key][$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['query']][$image_no_query] = array();
							}
					
							// Das normale Datenarray wird mit den Werten der Auswertungen aufgefüllt
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['weld'] = $naht;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution'][$naht]['result'] = $resultENE;
							$dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['weldnumber'] = count($dataArray[$key][$_ReportsOrderTestingmethods['Reportnumber']['id']]['evalution']);
						}
					}
				}
			}
		}
		
		$dataArrayError = array();

		$output['dataArrayError'] = $dataArrayError;		
		$output['WeldsTotal'] = $WeldsTotal;		
		$output['WeldsComplet'] = $WeldsComplet;		
		$output['dataArray'] = $dataArray;		
		$output['InvoiceQuerys'] = $InvoiceQuerys;		

		return $output;
	}

	public function StatisticsStandardEdit($statisticsstandard,$verfahren) {

		$statisticsstandardArray = array();				
		$statisticsstandardPrice = 0;

		// Die gespeicherten, abgerechneten Nähte in das entsprechende Array schreiben
		foreach($statisticsstandard as $_key => $_statisticsstandard){
				
			// Die KurztextID wird mit dem Kurztext ersetzt
			$kurztext = $this->_controller->Invoice->find('first', array(
														'fields' => array('kurztext'),
														'conditions' => array(
																		'id' => $_statisticsstandard['Statisticsstandard']['position']
																	) 

															));
															
			$_statisticsstandard['Statisticsstandard']['position'] = $kurztext['Invoice']['kurztext'];

			if($_statisticsstandard['Statisticsstandard']['user_id'] != ''){
						$optionUser = array(
									'conditions' => array(
										'User.id' => $_statisticsstandard['Statisticsstandard']['user_id']
										),
									'fields' => array('name')
						);
						
					$this->_controller->User->recursive = -1;
					$user = $this->_controller->User->find('first',$optionUser);
					$_statisticsstandard['Statisticsstandard']['user_id'] = $user['User']['name'];
				}

				
			$statisticsstandardArray[$verfahren][$_key] = $_statisticsstandard['Statisticsstandard'];

			if($_statisticsstandard['Statisticsstandard']['deaktiv'] != 1){
				$statisticsstandardPrice = $statisticsstandardPrice + $_statisticsstandard['Statisticsstandard']['price_total'];				
				$statisticsstandardArray['price_total'] = number_format($statisticsstandardPrice,3);
			}
		}
		$output['data'] = $statisticsstandardArray; 
				
		return $output;
	}
	
	public function StatisticsStandardEditRT($statisticsstandard,$verfahren) {

		$statisticsstandardArray = array();				
		$statisticsstandardPrice = 0;

		// Die Array für die Bildnummern und Strahlenquellen werrden gefüllt
		$ArrayBildnummer = $this->_controller->Invoices->ArrayBildnummer();
		$ArraySoucre = $this->_controller->Invoices->ArraySoucre();

		// Die gespeicherten, abgerechneten Nähte in das entsprechende Array schreiben
		foreach($statisticsstandard as $_statisticsstandard){
				
			// die Anzahl der bisherigen gespeicherten Sets
			foreach($ArrayBildnummer as $_key => $_ArrayBildnummer){
				foreach($_ArrayBildnummer as $__key => $__ArrayBildnummer){
					if($__ArrayBildnummer == $_statisticsstandard['Statisticsstandard']['picture_no']){
						break;
					}
				}
			}
				
			// Das Array wird in einem Array mit den Unterteilungen Strahlenquelle -> Bildnummer geschrieben
//			pr($_statisticsstandard['Statisticsstandard']['source']);
			if(isset($ArraySoucre[$_statisticsstandard['Statisticsstandard']['source']])){
				$sourceKey = $ArraySoucre[$_statisticsstandard['Statisticsstandard']['source']];
			}
			else{
				$sourceKey = $ArraySoucre['Röntgenröhre'];
			}
				
			if($_statisticsstandard['Statisticsstandard']['user_id'] != ''){
						$optionUser = array(
									'conditions' => array(
										'User.id' => $_statisticsstandard['Statisticsstandard']['user_id']
										),
									'fields' => array('name')
						);
						
					$this->_controller->User->recursive = -1;
					$user = $this->_controller->User->find('first',$optionUser);
					$_statisticsstandard['Statisticsstandard']['user_id'] = $user['User']['name'];
			}

				
			$statisticsstandardArray[$verfahren][$sourceKey][$_key][] = $_statisticsstandard['Statisticsstandard'];
			
			if($_statisticsstandard['Statisticsstandard']['deaktiv'] != 1){
				$statisticsstandardPrice = $statisticsstandardPrice + $_statisticsstandard['Statisticsstandard']['price_total'];				
				$statisticsstandardArray['price_total'] = number_format($statisticsstandardPrice,3);
			}
		}

		// Nur für die Beschriftung der Tabelle
		$InvoiceQuerysLabel = array();
		if(isset($statisticsstandardArray['rt'])){
//			$InvoiceQuerysLabel[$verfahren]['label'] = 'RT-Prüfung';
			if(isset($statisticsstandardArray[$verfahren]['03'])){
				$InvoiceQuerysLabel[$verfahren]['03']['label'] = 'Röntgenröhre';
			}
			if(isset($statisticsstandardArray[$verfahren]['02'])){
				$InvoiceQuerysLabel[$verfahren]['02']['label'] = 'Durchstrahlungsprüfungen Iridium 192';
			}
			if(isset($statisticsstandardArray[$verfahren]['01'])){
				$InvoiceQuerysLabel[$verfahren]['01']['label'] = 'Durchstrahlungsprüfungen Selen 75';
			}
		}
		$output['data'] = $statisticsstandardArray; 
		$output['label'] = $InvoiceQuerysLabel; 

		return $output;
	}

	public function WeldArray($InvoiceQuerys,$dataArray,$Dimensions,$WeldsTotal,$WeldsComplet,$verfahren) {
		// Wenn Prüfberichte abgewählt wurden, werden diese jetzt rausgefiltert
		foreach($this->_controller->Session->read('aktiv_deaktiv') as $aktiv_deaktiv){
			unset($dataArray[$verfahren][$aktiv_deaktiv]);
		}

		$invoices = array();
		
		if(count($InvoiceQuerys) == 0){
			foreach($Dimensions as $_key => $_Dimensions){
				$InvoiceQuerys[$verfahren][$_key] = array();
			}
			
		}
		
		// Alle Nähte des Auftrags werden in ein Array geschrieben
		$WeldArray = array();
		$xWeldArray = 1;
		foreach($dataArray as $key => $_dataArray){
			$keyVerfahren = $key;				
			foreach($_dataArray as $__dataArray){
				if(isset($__dataArray['evalution'])){
					foreach($__dataArray['evalution'] as $___dataArray){
					// alle Nähte zählen
					$WeldsTotal++;

					$WeldArray[$xWeldArray] = $___dataArray;
					$WeldArray[$xWeldArray]['query1'] = $key;
					$WeldArray[$xWeldArray]['query2'] = $xWeldArray;
//					$WeldArray[$xWeldArray]['query2'] = array_search($___dataArray['dimension'], $Dimensions);
//					$WeldArray[$xWeldArray]['radiation_source'] = $__dataArray['radiation_source'];
					$WeldArray[$xWeldArray]['infos'] = $__dataArray['infos'];
					$WeldArraForNumbers = $WeldArray[$xWeldArray];
					// hier werden die Nähte mit alle Pflichtangaben gezählt
					$WeldsComplet++;

/*
					foreach($Dimensions as $key => $_Dimension){
						if($_Dimension == $WeldArraForNumbers['dimension']){
							$_keyDimension = $key;
							break;
						}
					}
*/
					// Nähte zählen
					if(isset($InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']]['number'])){
						$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']]['number'] = 
						$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']]['number'] + 1;
					}
					else {
						$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']]['number'] =  1;
					}

					$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']]['infos'] = $WeldArraForNumbers;
					$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']]['ids'][] = $__dataArray['infos']['id'];

					$xWeldArray++;
					}
				}
			}
		}

		// Aufteilung Prüfverfahren / Werte aus Liste
		$InvoiceAufteilung = array(
									'mt' => '05', 
									'pt' => '03', 
									'ht' => '04', 
									'ut' => '02', 
								);
		if(isset($InvoiceAufteilung[$verfahren])){
			$Conditions = $InvoiceAufteilung[$verfahren];
		}
		else {
			$Conditions = '0';
		}
		
		// Die Labels brauchen wir nur bei RT
		$InvoiceQuerysLabel = null;

		// die Abrechnungsdaten laden und in ein Array		
		$invoicesArrays = array();		

		// wir wollen gleiche Abfragen nur einma machen
		// hier werden die Coditionen erstellt
		$invoicesAOption =  array('conditions' => array(
																'testingmethode' => $verfahren,
																'position LIKE' => $Conditions."%"
															) 
														);
		$invoices = $this->_controller->Invoice->find('all', $invoicesAOption);

		// jetzt werden die Abfragewerte aus der Aufmaßtabelle den Nähten zugeordnet
		foreach($InvoiceQuerys as $key1 => $value1){
			foreach($value1 as $key2 => $value2){
				// Die Werte für das Selectfeld herausfiltern
				$_select = array();
				foreach($invoices as $_invoices){
					$_select[trim($_invoices['Invoice']['id'])] = trim($_invoices['Invoice']['kurztext']);
				}
					
				$InvoiceQuerys[$key1][$key2]['select'] = $_select;
			}
		}

		$output['WeldsTotal'] = $WeldsTotal;
		$output['WeldsComplet'] = $WeldsComplet;
		$output['WeldArray'] = $WeldArray;
		$output['InvoiceQuerys'] = $InvoiceQuerys;
		$output['InvoiceQuerysLabel'] = $InvoiceQuerysLabel;
		$output['invoices'] = $invoices;
		
		return $output;
	}

	public function WeldArrayRT($InvoiceQuerys,$dataArray,$Dimensions,$WeldsTotal,$WeldsComplet,$verfahren) {

		// Wenn Prüfberichte abgewählt wurden, werden diese jetzt rausgefiltert
		foreach($this->_controller->Session->read('aktiv_deaktiv') as $aktiv_deaktiv){
			unset($dataArray[$verfahren][$aktiv_deaktiv]);
		}

		$invoices = array();

		// Das Array mit den Abmessungen wird in das Aufteilungsarray eingefügt
		// man erhält ein Array mit den keys des DimensionsArray, hier werde später die Nähte der entsprechenden Abmessungen hinterlegt
		foreach($InvoiceQuerys as $key1 => $_InvoiceQuerys){
			foreach($_InvoiceQuerys as $key2 => $__InvoiceQuerys){
				foreach($__InvoiceQuerys as $key3 => $___InvoiceQuerys){
					foreach($Dimensions as $key4 => $_Dimension){
					$InvoiceQuerys[$key1][$key2][$key3][$key4]['number'] = 0;
					}
				}
			}
		}

		// Alle Nähte des Auftrags werden in ein Array geschrieben
		$WeldArray = array();
		$xWeldArray = 1;
		foreach($dataArray as $key => $_dataArray){
			$keyVerfahren = $key;				
			foreach($_dataArray as $__dataArray){
				if(isset($__dataArray['evalution'])){
					foreach($__dataArray['evalution'] as $___dataArray){
					
					// alle Nähte zählen
					$WeldsTotal++;

					$WeldArray[$xWeldArray] = $___dataArray;
					$WeldArray[$xWeldArray]['query1'] = $key;
					$WeldArray[$xWeldArray]['query2'] = @$__dataArray['query'];
					$WeldArray[$xWeldArray]['personal'] = $__dataArray['personal'];
					$WeldArray[$xWeldArray]['beginn'] = $__dataArray['beginn'];
					$WeldArray[$xWeldArray]['end'] = $__dataArray['end'];
					$WeldArray[$xWeldArray]['time'] = $__dataArray['time'];
					$WeldArray[$xWeldArray]['radiation_source'] = $__dataArray['radiation_source'];
					$WeldArray[$xWeldArray]['infos'] = $__dataArray['infos'];
					$WeldArraForNumbers = $WeldArray[$xWeldArray];

					foreach($Dimensions as $key => $_Dimension){
						if($_Dimension == $WeldArraForNumbers['dimension']){
							$_keyDimension = $key;
							break;
						}
					}


					// Und nochmal die Infos angefügt
					// nur wenn Bildnummer, Strahlenquelle, Bildnummer ausgefüllt ist
					if(
					$WeldArraForNumbers['dimension'] != '' && 
					$WeldArraForNumbers['image_no'] != '' && 
					$WeldArraForNumbers['query2'] != '' && 
					$WeldArray[$xWeldArray]['query2'] != '' && 
					$WeldArray[$xWeldArray]['query3'] != ''
					){
						// hier werden die Nähte mit alle Pflichtangaben gezählt
						$WeldsComplet++;
						$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']][$WeldArray[$xWeldArray]['query3']][$_keyDimension]['number'] = 
						$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']][$WeldArray[$xWeldArray]['query3']][$_keyDimension]['number'] + 1;
						$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']][$WeldArray[$xWeldArray]['query3']][$_keyDimension]['infos'] = $WeldArraForNumbers;
					// Wenn für diese Position schon ein Preis angegeben wurde
						
						if(isset($XmlFromDatabase['invoices'][$keyVerfahren]['x'.$WeldArray[$xWeldArray]['query2']]['x'.$WeldArray[$xWeldArray]['query3']]['x'.$_keyDimension]['selectet'])){
							$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']][$WeldArray[$xWeldArray]['query3']][$_keyDimension]['selectet'] = 
							$XmlFromDatabase['invoices'][$keyVerfahren]['x'.$WeldArray[$xWeldArray]['query2']]['x'.$WeldArray[$xWeldArray]['query3']]['x'.$_keyDimension]['selectet'];
						}
						
						if(isset($XmlFromDatabase['invoices'][$keyVerfahren]['x'.$WeldArray[$xWeldArray]['query2']]['x'.$WeldArray[$xWeldArray]['query3']]['x'.$_keyDimension]['price'])){
							$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']][$WeldArray[$xWeldArray]['query3']][$_keyDimension]['price'] = 
							$XmlFromDatabase['invoices'][$keyVerfahren]['x'.$WeldArray[$xWeldArray]['query2']]['x'.$WeldArray[$xWeldArray]['query3']]['x'.$_keyDimension]['price'];
							$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']][$WeldArray[$xWeldArray]['query3']][$_keyDimension]['singleprice'] = 
							$XmlFromDatabase['invoices'][$keyVerfahren]['x'.$WeldArray[$xWeldArray]['query2']]['x'.$WeldArray[$xWeldArray]['query3']]['x'.$_keyDimension]['singleprice'];							
						}
						$InvoiceQuerys[$keyVerfahren][$WeldArray[$xWeldArray]['query2']][$WeldArray[$xWeldArray]['query3']][$_keyDimension]['ids'][] = $__dataArray['infos']['id'];
					}
					$xWeldArray++;
					}
				}
			}
		}

		// Nur für die Beschriftung der Tabelle
		$InvoiceQuerysLabel = array();
		if(isset($InvoiceQuerys[$verfahren])){
			$InvoiceQuerysLabel[$verfahren]['label'] = 'RT-Prüfung';
			if(isset($InvoiceQuerys[$verfahren]['03'])){
				$InvoiceQuerysLabel[$verfahren]['03']['label'] = 'Durchstrahlungsprüfungen Röntgenröhre';
			}
			if(isset($InvoiceQuerys[$verfahren]['02'])){
				$InvoiceQuerysLabel[$verfahren]['02']['label'] = 'Durchstrahlungsprüfungen Iridium 192';
			}
			if(isset($InvoiceQuerys[$verfahren]['01'])){
				$InvoiceQuerysLabel[$verfahren]['01']['label'] = 'Durchstrahlungsprüfungen Selen 75';
			}
		}

		// die Abrechnungsdaten laden und in ein Array		
		$invoicesArrays = array();		

		// wir wollen gleiche Abfragen nur einma machen
		// hier werden die Coditionen erstellt
		foreach($InvoiceQuerys as $key1 => $value1){
			foreach($value1 as $key2 => $value2){
				foreach($value2 as $key3 => $value3){
					if($key2 == 0){
					$invoicesArrays[$key1][$key2][$key3] =  array('conditions' => array(
																		'testingmethode' => $key1,
																		'position LIKE' => "%.$key3%"
																	) 

															);
					}
					else {
					$invoicesArrays[$key1][$key2][$key3] =  array('conditions' => array(
																		'testingmethode LIKE' => $key1,
																		'position LIKE' => "$key2.$key3%"
																	) 

															);
					}
				}
			}
		}

		// wir wollen gleiche Abfragen nur einma machen
		// für alle vorhandenen Nähte werden die die entsprechenden Daten aus der Abrechnungstabelle geholt
		foreach($invoicesArrays as $key1 => $_invoicesArrays){

			foreach($_invoicesArrays as $key2 => $__invoicesArrays){
				foreach($__invoicesArrays as $key3 => $___invoicesArrays){					
					$invoices[$key1][$key2][$key3] = $this->_controller->Invoice->find('all', $___invoicesArrays);
				}
			}
		}

		// jetzt werden die Abfragewerte aus der Aufmaßtabelle den Nähten zugeordnet
		foreach($InvoiceQuerys as $key1 => $value1){
			foreach($value1 as $key2 => $value2){
				foreach($value2 as $key3 => $value3){
					foreach($value3 as $key4 => $value4){
						
						if($InvoiceQuerys[$key1][$key2][$key3][$key4]['number'] == 0){
							unset($InvoiceQuerys[$key1][$key2][$key3][$key4]);
							continue;
						}

						// Die Werte für das Selectfeld herausfiltern
						$_select = array();
						foreach($invoices[$key1][$key2][$key3] as $_invoices){
							$_select[trim($_invoices['Invoice']['id'])] = trim($_invoices['Invoice']['kurztext']);
						}
					
						$InvoiceQuerys[$key1][$key2][$key3][$key4]['select'] = $_select;
//						$InvoiceQuerys[$key1][$key2][$key3]['invoice'] = $invoices[$key1][$key2][$key3];
					}
				}
			}
		}

		$output['WeldsTotal'] = $WeldsTotal;
		$output['WeldsComplet'] = $WeldsComplet;
		$output['WeldArray'] = $WeldArray;
		$output['InvoiceQuerys'] = $InvoiceQuerys;
		$output['InvoiceQuerysLabel'] = $InvoiceQuerysLabel;
		$output['invoices'] = $invoices;
		
		return $output;
	}
	
	public function NewInvoices($var1,$var2,$var3,$var4,$var5,$var6) {

		// die übermittelten Variablen testen und umbenennen
		$projectID = $var1;
		$equipmentType = $var2;
		$equipment = $var3;
		$orderID = $var4;
		$testingmethodID = $var5;
		$status = $var6;
		
		$InvoiceQuerys = null;
		$WeldsTotal = null;
		$WeldsComplet = null;
		$dataArray = null;
		$InvoiceQuerysLabel = null;
		$InvoiceQuerys = null;		

		$this->_controller->loadModel('Reportnumber');
		
		$ArrayBildnummer = $this->_controller->Invoices->ArrayBildnummer();

		// Infos zum Prüfverfahren holen
		$this->_controller->Reportnumber->Testingmethod->recursive = -1;
		$optionsTestingmethode = array('conditions' => array(
								'id' => $testingmethodID 
							)
						);		

		$testingmethods = $this->_controller->Reportnumber->Testingmethod->find('first', $optionsTestingmethode);

		$verfahren = $testingmethods['Testingmethod']['value'];
		$Verfahren = ucfirst($testingmethods['Testingmethod']['value']);

		$this->_controller->set('verfahren', $verfahren);
		

		// das weiß ich noch nicht, kann wohl weg
		$options = array('conditions' => array(
											'Reportnumber.order_id' => $orderID, 
											'Reportnumber.topproject_id' => $projectID
										)
									);
		

		$ReportsOrderTestingmethods[$verfahren] = $testingmethods;

		// die Suchoptionen für die offenen Prüfberichte
		$optionsAllReportnumners = array('conditions' => array(
											'order_id' => $orderID, 
											'topproject_id' => $projectID,
											'testingmethod_id' => $testingmethodID, 
											'delete' => 0,
											'settled <' => 2,
											'status >' => 1
										)
									);
									
		// Alle Protokollnummern auslesen
		$this->_controller->Reportnumber->recursive = -1;
		
		// alle zu verarbeitenden IDs in ein Array
		$allReportnumners = $this->_controller->Reportnumber->find('list', $optionsAllReportnumners);

		// Die Prüfberiche zu dem aktuellen Prüfverfahren werden ausgelesen
		$options = array('conditions' => array(
											'Reportnumber.delete' => 0,
											'Reportnumber.settled <' => 2,
											'status >' => 1,
											'Reportnumber.topproject_id' => $projectID,
											'Reportnumber.order_id' => $orderID,
											'Reportnumber.testingmethod_id' => $testingmethodID
											)
										);

		$ReportnumberCount = $this->_controller->Reportnumber->find('count', $options);

		if($ReportnumberCount == 0){
		
			$options = array('conditions' => array(
											'Reportnumber.delete' => 0,
											'Reportnumber.settled <' => 2,
											'Reportnumber.topproject_id' => $projectID,
											'Reportnumber.order_id' => $orderID,
											'Reportnumber.testingmethod_id' => $testingmethodID
											)
										);
			$this->_controller->set('massage','Es wurde noch kein Prüfbericht abgeschlossen, bzw. es sind noch keine Prüfbericht vorhanden.');
		}
		elseif($ReportnumberCount > 0){
			$ReportsOrderTestingmethods[$verfahren]['Reports'] = $this->_controller->Reportnumber->find('all', $options);
		}
		$ReportData[0] = 'Report'.$Verfahren.'Generally';	
		$ReportData[1] = 'Report'.$Verfahren.'Specific';	
		$ReportData[2] = 'Report'.$Verfahren.'Evaluation';

		$ReportGenerally = 'Report'.$Verfahren.'Generally';	
		$ReportSpecific = 'Report'.$Verfahren.'Specific';	
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
		
		$this->_controller->loadModel($ReportGenerally);
		$this->_controller->loadModel($ReportSpecific);
		$this->_controller->loadModel($ReportEvaluation);

		// Die Daten aus den zugehörigen Tabellen der Prüfverfahren werden ausgelesen
		if(isset($ReportsOrderTestingmethods[$verfahren]['Reports']) && count($ReportsOrderTestingmethods[$verfahren]['Reports']) > 0){
			foreach($ReportsOrderTestingmethods[$verfahren]['Reports'] as $_key => $_ReportsOrderTestingmethods){
				foreach($ReportData as $__key => $_ReportData){

					$options = array('conditions' => array(
										$_ReportData.'.reportnumber_id' => $_ReportsOrderTestingmethods['Reportnumber']['id']
										)
									);

					// bei der Datentabelle muss mehr als ein Datensatzausgelesen werden
					if($__key == 2){
						$finder = 'all';
					}
					else{
						$finder = 'first';
					}
					$ReportsOrderTestingmethods[$verfahren]['Reports'][$_key][$_ReportData] = $this->_controller->$_ReportData->find($finder, $options);
				}		
			}
		}

		// Wenn Prüfberichte abgewählt wurden, werden die entsprechenden Ids entfernt
//		pr($this->_controller->Session->read('aktiv_deaktiv'));
//		pr($allReportnumners);
		foreach($this->_controller->Session->read('aktiv_deaktiv') as $_aktiv_deaktiv){
			unset($allReportnumners[$_aktiv_deaktiv]);
		}

		// Die abzurechnenten Prüfberichte werden hier zusammengefasst
		switch ($Verfahren) {
		case 'Rt':		
			// alle im Auftrag vorhandenen Abmessungen auslesen
			// wird gebraucht um später das Nahtarray aufzuteilen
			$allDimensions = array();
			if(count($allReportnumners) > 0){
				$allDimensions = $this->_controller->$ReportEvaluation->find('list', array(
							'fields' => array('dimension'),
							'conditions' => array(
								'reportnumber_id' => $allReportnumners, 
								'dimension !=' => '' 
							)
						)
					);
			}

			$Dimensions = array_values((array_unique($allDimensions)));

			$output = $this->StatisticsStandardCreateRT($ReportsOrderTestingmethods,$verfahren);

			$dataArray = $output['dataArray'];
			$InvoiceQuerys = $output['InvoiceQuerys'];
			$WeldsTotal = $output['WeldsTotal'];		
			$WeldsComplet = $output['WeldsComplet'];		
			$dataArrayError = $output['dataArrayError'];	
			// die Nähte aus den Prüfberichten werden nach Strahlenquelle, Bildnummer und Abmessung zusammengefasst
			// es werden die passenden Werte aus der Abrechnungstabelle geladen und den entsprechenden Nähten zugeordnet
			$output = $this->WeldArrayRT($InvoiceQuerys,$dataArray,$Dimensions,$WeldsTotal,$WeldsComplet,$verfahren);

			$WeldsComplet = $output['WeldsComplet'];
			$WeldsTotal = $output['WeldsTotal'];
			$WeldArray = $output['WeldArray'];
			$InvoiceQuerys = $output['InvoiceQuerys'];
			$InvoiceQuerysLabel = $output['InvoiceQuerysLabel'];
			$invoices = $output['invoices'];

		break;
		case 'Pt':		

			$output = $this->StatisticsStandardCreatePT($ReportsOrderTestingmethods,$verfahren);
			$dataArray = $output['dataArray'];
			$InvoiceQuerys = $output['InvoiceQuerys'];
			$WeldsTotal = $output['WeldsTotal'];		
			$WeldsComplet = $output['WeldsComplet'];
			$dataArrayError = $output['dataArrayError'];	

			// die Nähte aus den Prüfberichten werden nach Strahlenquelle, Bildnummer und Abmessung zusammengefasst
			// es werden die passenden Werte aus der Abrechnungstabelle geladen und den entsprechenden Nähten zugeordnet
			$Dimensions = array();
			$output = $this->WeldArray($InvoiceQuerys,$dataArray,$Dimensions,$WeldsTotal,$WeldsComplet,$verfahren);

			$WeldsComplet = $output['WeldsComplet'];
			$WeldsTotal = $output['WeldsTotal'];
			$WeldArray = $output['WeldArray'];
			$InvoiceQuerys = $output['InvoiceQuerys'];
			$InvoiceQuerysLabel = $output['InvoiceQuerysLabel'];
			$invoices = $output['invoices'];

		break;				
		default:

			$output = $this->StatisticsStandardCreate($ReportsOrderTestingmethods,$verfahren);
			$dataArray = $output['dataArray'];
			$InvoiceQuerys = $output['InvoiceQuerys'];
			$WeldsTotal = $output['WeldsTotal'];		
			$WeldsComplet = $output['WeldsComplet'];
			$dataArrayError = $output['dataArrayError'];	

			// die Nähte aus den Prüfberichten werden nach Strahlenquelle, Bildnummer und Abmessung zusammengefasst
			// es werden die passenden Werte aus der Abrechnungstabelle geladen und den entsprechenden Nähten zugeordnet
			$Dimensions = array();
			$output = $this->WeldArray($InvoiceQuerys,$dataArray,$Dimensions,$WeldsTotal,$WeldsComplet,$verfahren);

			$WeldsComplet = $output['WeldsComplet'];
			$WeldsTotal = $output['WeldsTotal'];
			$WeldArray = $output['WeldArray'];
			$InvoiceQuerys = $output['InvoiceQuerys'];
			$InvoiceQuerysLabel = $output['InvoiceQuerysLabel'];
			$invoices = $output['invoices'];

		break;	
		}

		
		// Wenn keinen Ergebnisse vorliegen
		$statusDiscError = null;
		$statusDiscError  = '<div class="clear">';	
	
		if($status == 2){

			$optionReportnumbersOpen = array('conditions' => array(
								'Reportnumber.topproject_id' => $projectID, 
								'Reportnumber.order_id' => $orderID, 
								'Reportnumber.testingmethod_id' => $testingmethodID ,
								'settled' => 3,
								'delete' => 0
							)
						);
			$ReportnumbersClose = $this->_controller->Reportnumber->find('count', $optionReportnumbersOpen);
//				$statusDiscError .= '<p>';
				$statusDiscError .= '<a class="mymodal round" href="';
				$statusDiscError .= Router::url(array('controller' => 'invoices', 'action' => 'invoice', $projectID, $equipmentType, $equipment, $orderID, $testingmethodID, 3));
				$statusDiscError .= '">';
				$statusDiscError .= __('Show settled reports', true) . ' (' . $ReportnumbersClose . ')';
				$statusDiscError .= '</a>';
//				$statusDiscError .= '</p>';

		}
		
		if(count($allReportnumners) == 0){
			// bei offenen Abrechnungen
			if($status == 1){
				$statusDiscError .= __('No settled reports found - only closed reports can be settle. Only additional services can be added.', true);
				//$statusDiscError .= __('Es sind keine abrechenbare Prüfberichte vorhanden, es können nur geschlossene Prüfberichte abgerechnet werden. Es können zusätzliche Leistungen angelegt werden.', true);
			}
		}
		
		// Wenn Prüfberichte wegen fehlende Angaben enfernt wurden
		if(count($dataArrayError) > 0){

			$statusDiscError .= '<p class="error clear">'.__('Following reports were removed due missing data', true).'</p>';
			//$statusDiscError .= 'Folgende Prüfberichte wurden aus der aktuellen Abrechnung entfernt, da nicht alle zur Abrechnung benötigen Angaben gemacht wurden.</p>';
			$statusDiscError .= '<ul class="error clear">';

			foreach($dataArrayError as $_dataArrayError){
				if(count($_dataArrayError) == 0)  continue;	
										
				$statusDiscError .= '<li  class="red">';
				$statusDiscError .= '<a class="ajax red" href="';
				$statusDiscError .= Router::url(array('controller' => 'reportnumbers', 'action' => 'edit', 
									$_dataArrayError['infos']['topproject_id'],
									$_dataArrayError['infos']['equipment_type_id'],
									$_dataArrayError['infos']['equipment_id'],
									$_dataArrayError['infos']['order_id'],
									$_dataArrayError['infos']['report_id'],
									$_dataArrayError['infos']['id']
									
									));
				$statusDiscError .= '">';
				$statusDiscError .= $_dataArrayError['infos']['year'].'-'.$_dataArrayError['infos']['number'];
				$statusDiscError .= '</a> ';
				
				if($_dataArrayError['radiation_source'] == ''){
					$statusDiscError .= '('.__('radiation source missing', true).')';
				}

				foreach($_dataArrayError['evalution'] as $__dataArrayError){
					if($__dataArrayError['dimension'] == ''){
						$statusDiscError .= '('.__('dimensions missing', true).')';
					}
					if($__dataArrayError['image_no'] == ''){
						$statusDiscError .= '('.__('exposure arrangement missing', true).')';
					}
				}
				
				$statusDiscError .= '</li>';
			}
			
			$statusDiscError .= '</ul>';
			$statusDiscError .= '</p>';
		}
		
		$statusDiscError .= '</div>';
		
		$statusDisc = __('Open settlements', true);

		// das alles wird in einer Session gespeichert
		$this->_controller->Session->write('InvoiceQuerys', $InvoiceQuerys);

		$this->_controller->set('status', $status);
		$this->_controller->set('dataArrayError', $dataArrayError);
		$this->_controller->set('statusDiscError', $statusDiscError);
		$this->_controller->set('statusDisc', $statusDisc);
		$this->_controller->set('WeldsTotal', $WeldsTotal);
		$this->_controller->set('WeldsComplet', $WeldsComplet);
		$this->_controller->set('Dimensions', $Dimensions);
		$this->_controller->set('dataArray', $dataArray);
		$this->_controller->set('InvoiceQuerysLabel', $InvoiceQuerysLabel);
		$this->_controller->set('InvoiceQuerys', $InvoiceQuerys);
		$this->_controller->set('menues', null);
	}

	public function EditInvoices($var1,$var2,$var3,$var4,$var5,$var6) {

		// die übermittelten Variablen testen und umbenennen
		$projectID = $var1;
		$equipmentType = $var2;
		$equipment = $var3;
		$orderID = $var4;
		$testingmethodID = $var5;
		$status = $var6;

		$this->_controller->loadModel('Reportnumber');
		$this->_controller->loadModel('Examiner');
				
		// Wenn eine Teilrechnung angezeigt werden soll
		$lf_number = null;
		$LfNumberArray = null;
		$laufendeNrArray = array();

		if(isset($this->_controller->request->data['lf_number'])){
			$lf_number = $this->_controller->Sicherheit->Numeric(trim($this->_controller->request->data['lf_number']));
			$LfNumberArray = array('laufende_nr' => $lf_number);

			if($lf_number == 0){
			$LfNumberArray = null;
			}
		}
		elseif(isset($this->_controller->request->params['pass'][7])){
			$lf_number = $this->_controller->Sicherheit->Numeric(trim($this->_controller->request->params['pass'][7]));
			$LfNumberArray = array('laufende_nr' => $lf_number);
			if($lf_number == 0){
			$LfNumberArray = null;
			}
		}

		// nach dem speichern von vorhandenen zusätzlichen Abrechnungen
		if(isset($this->_controller->request['data']['aditionalsave'])){
			
			$aditionalsave = $this->_controller->Sicherheit->Numeric(trim($this->_controller->request['data']['aditionalsave']));
			
				if($aditionalsave == 3){
					$this->_controller->Session->setFlash(__('Data was saved'));
				}
				if($aditionalsave == 6){
					$this->_controller->Session->setFlash(__('Data was deleted'));
				}
			
			$this->_controller->set('aditionalsave',$aditionalsave);

		}
		
		$invoicesArrays = array();				

		// Prüfverfahren auslesen
		$this->_controller->Reportnumber->Testingmethod->recursive = -1;
		$optionsTestingmethode = array('conditions' => array(
								'id' => $testingmethodID 
							)
						);		
		$testingmethods = $this->_controller->Reportnumber->Testingmethod->find('all', $optionsTestingmethode);
		$verfahren = $testingmethods[0]['Testingmethod']['value'];
		$Verfahren = ucfirst($testingmethods[0]['Testingmethod']['value']);
		$this->_controller->set('verfahren', $verfahren);
		
		$Modelarray = array(
							'cell1' => 'Statisticsgenerall',
							'cell2' => 'Statisticsstandard',
							'cell3' => 'Statisticsadditional',
						);
		
		// Models laden
		foreach($Modelarray as $_Modelarray){
			$this->_controller->loadModel($_Modelarray);
		}

		// die Optionen für die laufenden Nummer
		$optionsStatisticsgenerallLN = array(
								'fields' => array('laufende_nr','id','created'),
								'conditions' => array(
									'topproject_id' => $projectID, 
									'order_id' => $orderID,
									'testingmethod_id' => $testingmethodID,
									),
								'order' => array('laufende_nr','id')	
								);
		$projectID = $var1;
		$equipmentType = $var2;
		$equipment = $var3;
		$orderID = $var4;
		$testingmethodID = $var5;
		$status = $var6;


		// Die gespeicherten Daten der abgeschlossenen Abrechnung werden geladen
		$statisticsgenerallLN = ($this->_controller->Statisticsgenerall->find('all', $optionsStatisticsgenerallLN));
		$statisticsadditionalLN = ($this->_controller->Statisticsadditional->find('all', $optionsStatisticsgenerallLN));
			
		$laufendeNrArray[0] = __('Complete settlement', true);
			
		foreach($statisticsgenerallLN as $_statisticsgenerallLN){
			$laufendeNrArray[$_statisticsgenerallLN['Statisticsgenerall']['laufende_nr']] = 'Set '.$_statisticsgenerallLN['Statisticsgenerall']['laufende_nr'].' vom '.$_statisticsgenerallLN['Statisticsgenerall']['created']; 
		}

		// das ZusatzleistungArray wird nochmal durchlaufen um die laufenden Nummer von unabhängigen Zusatzleitungen hinzuzufügen
		foreach($statisticsadditionalLN as $k_key => $_statisticsadditionalLN){
			foreach($_statisticsadditionalLN['Statisticsadditional'] as $k__key => $__statisticsadditionalLN){
				if($k__key == 'laufende_nr'){
					if(!isset($laufendeNrArray[$__statisticsadditionalLN])){
						$laufendeNrArray[$__statisticsadditionalLN] = 'Set '.$__statisticsadditionalLN.' vom '.$statisticsadditionalLN[$k_key]['Statisticsadditional']['created'];
					}
				}
			}
		}
			
		ksort($laufendeNrArray);

		// Die Optionen für die Abfrage
		$optionsStatisticsgenerall = array(
								'conditions' => array(
									'topproject_id' => $projectID, 
									'order_id' => $orderID,
									'testingmethod_id' => $testingmethodID,
									$LfNumberArray 
									),
								'order' => array('laufende_nr','id')	
								);
			
		// Die gespeicherten Daten der abgeschlossenen Abrechnung werden geladen

		$statisticsgenerall = ($this->_controller->Statisticsgenerall->find('all', $optionsStatisticsgenerall));
		$statisticsstandard = ($this->_controller->Statisticsstandard->find('all', $optionsStatisticsgenerall));
		$statisticsadditional = ($this->_controller->Statisticsadditional->find('all', $optionsStatisticsgenerall));
		
		// die Variablen des Prüfberichts holen
		foreach($statisticsgenerall as $_key => $_statisticsgenerall){
			$Reportnumber = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $_statisticsgenerall['Statisticsgenerall']['reportnumber_id'])));

			$statisticsgenerall[$_key]['Reportnumber'] = $Reportnumber['Reportnumber'];
		}

		// wenn Prüfberichte nach dem Abrechnen wiedereröffnet werden, kann es vorkommen
		// das die Anzahl der Nähte auf Null absinkt, dann wird der Datensatz gelöscht
		foreach($statisticsstandard as $_statisticsstandard){
			if($_statisticsstandard['Statisticsstandard']['number'] == 0){
				$statistikstandardNewLoad = 1;
				$this->_controller->Statisticsstandard->delete($_statisticsstandard['Statisticsstandard']['id']);
			}
		}
			
		// nach eventuellen Löschen müssen die Daten neu geladen werden
		if(isset($statistikstandardNewLoad) && $statistikstandardNewLoad == 1){
			$statisticsstandard = ($this->_controller->Statisticsstandard->find('all', $optionsStatisticsgenerall));
		}

		// die ErstellerID wird mit dem Klarnahmen getauscht
		foreach($statisticsgenerall as $_key => $_statisticsgenerall){
			foreach($_statisticsgenerall as $__key => $__statisticsgenerall){
				foreach($__statisticsgenerall as $___key => $___statisticsgenerall){
					if($___key == 'user_id'){
						$optionUser = array(
								'conditions' => array(
									'User.id' => $___statisticsgenerall
									),
								'fields' => array('name')
						);
		
						$this->_controller->User->recursive = -1;
						$user = $this->_controller->User->find('first',$optionUser);
						$statisticsgenerall[$_key][$__key]['user_id'] = $user['User']['name'];
					}
				}
			}
		}
		// Die Aufteilung der Standartnähte
		if($testingmethodID == 1){
			$output = $this->_controller->Invoices->StatisticsStandardEditRT($statisticsstandard,$verfahren);			
			$statisticsstandardArray = $output['data'];	
			$InvoiceQuerysLabel = $output['label'];
		}
		else {
			$statisticsstandardArray = $this->_controller->Invoices->StatisticsStandardEdit($statisticsstandard,$verfahren);			
			$InvoiceQuerysLabel = array();
			
			if(isset($statisticsstandardArray['data']['price_total'])){
				$statisticsstandardArray['price_total'] = $statisticsstandardArray['data']['price_total'];
			}
			else {
				$statisticsstandardArray['price_total'] = 0;
			}
		}

		$statisticsadditionalPrice = 0;

		// die Zusatzleistunge in dien Array schreiben
		foreach($statisticsadditional as $_key => $_statisticsadditional){
			// Wert für den Bearbeiter
			// die Anzahl der bisherigen gespeicherten Sets

			if($_statisticsadditional['Statisticsadditional']['user_id'] != ''){
					$optionUser = array(
							'conditions' => array(
								'User.id' => $_statisticsadditional['Statisticsadditional']['user_id']
								),
							'fields' => array('name')
					);
					$this->_controller->User->recursive = -1;
					$user = $this->_controller->User->find('first',$optionUser);
					$statisticsadditional[$_key]['Statisticsadditional']['user_id'] = $user['User']['name'];
				}

			$statisticsadditionalPrice = $statisticsadditionalPrice + ereg_replace(',','',$statisticsadditional[$_key]['Statisticsadditional']['price_total']); 	
		}

		$statisticsadditional['price_total']['Statisticsadditional']['price_total'] = number_format($statisticsadditionalPrice,3);
		
		$price_total_total = number_format(ereg_replace(',','',@$statisticsstandardArray['price_total']) + ereg_replace(',','',$statisticsadditional['price_total']['Statisticsadditional']['price_total']),3);



		// Die Daten zum Auftrag holen
		$reportsID = array();
		$this->_controller->Reportnumber->recursive = -1;
		$optionsReports = array('conditions' => array(
								'Reportnumber.order_id' => $orderID, 
								'Reportnumber.topproject_id' => $projectID 
							),
						'fields' => array(
							'DISTINCT testingmethod_id'
							)
						);
		$reports = $this->_controller->Reportnumber->find('all', $optionsReports);
		foreach($reports as $_reports){
			$reportsID[] = $_reports['Reportnumber']['testingmethod_id'];
		}
		
		// nun die Testungmethodes zu den IDs
//		$this->loadModel('Testingmethod');
		$this->_controller->Reportnumber->Testingmethod->recursive = -1;
		$optionsTestingmethods = array(
								'fields' => array('id','verfahren'),
								'conditions' => array(
									'Testingmethod.id' => $reportsID, 
								)
							);

		$testingmethods = $this->_controller->Reportnumber->Testingmethod->find('list', $optionsTestingmethods);

		foreach($testingmethods as $_key => $_testingmethods){
//			$testingmethods[$_key]['Testingmethod']['statistik'] = $StatistikArray[$_key];
		}

		$this->_controller->set('testingmethods', $testingmethods);
		$this->_controller->set('price_total_total', $price_total_total);
		$this->_controller->set('lf_number', $lf_number);
		$this->_controller->set('laufendeNrArray', $laufendeNrArray);
		$this->_controller->set('InvoiceQuerysLabel', $InvoiceQuerysLabel);
		$this->_controller->set('statisticsadditional', $statisticsadditional);
		$this->_controller->set('statisticsgenerall', $statisticsgenerall);
		$this->_controller->set('statisticsstandard', $statisticsstandardArray);
		$this->_controller->set('status', $status);
			
		return $laufendeNrArray;
	}
	
	public function SaveInvoices($Insert,$verfahren) {

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$orderID = $this->_controller->request->projectvars['VarsArray'][3];

		$Modelarray = array(
							1 => 'Statisticsgenerall',
							2 => 'Statisticsstandard',
							3 => 'Statisticsadditional',
						);
		
		// Models laden
		foreach($Modelarray as $_Modelarray){
			$this->_controller->loadModel($_Modelarray);
		}				

		$this->_controller->loadModel('Reportnumber');
		$this->_controller->Reportnumber->recursive = -1;
		
		// Die letzte laufend Nummer holen
		$optionsLaufendeNr = array(
									'fields' => array(
										'id','laufende_nr'
									),
									'conditions' => array( 
										'topproject_id' => $projectID,
										'order_id' => $orderID
									),
									'order' => array(
										'id' => 'DESC'
									)
								);
		
		// Leistungen aus einem Auftrag können Prüfberichtsweise also einzeln abgerechnet werden
		// die Einzelabrechungen werden mit laufenden Nummern versehen
		// einmal aus den normalen Leistungen
		$LaufendeNr1 = $this->_controller->Statisticsgenerall->find('first', $optionsLaufendeNr);
		$LaufendeNrNeu1 = @$LaufendeNr1['Statisticsgenerall']['laufende_nr'];
		$LaufendeNrNeu1++;

		// und nochmal aus den zusätzliche Leistungen
		$LaufendeNr2 = $this->_controller->Statisticsadditional->find('first', $optionsLaufendeNr);
		$LaufendeNrNeu2 = @$LaufendeNr2['Statisticsadditional']['laufende_nr'];
		$LaufendeNrNeu2++;
		
		// je nach dem welcher Wert größer ist
		if($LaufendeNrNeu1 >= $LaufendeNrNeu2){
			$LaufendeNrNeu = $LaufendeNrNeu1;
		}
		elseif($LaufendeNrNeu2 > $LaufendeNrNeu1){
			$LaufendeNrNeu = $LaufendeNrNeu2;
		}

		foreach($Insert as $_key => $_val){
			foreach($_val as $__key => $__val){
				foreach($__val as $___key => $___val){
					$Insert[$_key][$__key][$___key]['laufende_nr'] = $LaufendeNrNeu;
				}
			}
				
			if($this->_controller->$_key->saveAll($Insert[$_key])){

			}
			else {
				pr('Fehler');
				return false;
			}
		}
		
		// betroffene Prüfberichte auf abgerechnet stellen
		$this->_controller->Reportnumber->updateAll(array('Reportnumber.settled'=>3),array('Reportnumber.id'=>$this->_controller->request->data['Report']['Invoice']));

	}	

	// kann wohl gelöscht werden
	public function saveRT($ReportsOrderTestingmethods) {


	}

	public function saveALL($postData,$testingmethodID) {

		$saveArray = array();

		$x1 = 0;
		$x1Conter = 0;
		$x1Break = 10;

		$x2 = 0;
		$x2Conter = 0;
		$x2Break = 6;
		$x2PriceTotal = 0;

		$x3 = 0;
		$x3Conter = 0;
		$x3Break = 12;
		$x3PriceTotal = 0;

		foreach($postData as $key => $_data){
			$_key = explode('t', $key);
			
			if($_key[0] == 1){
				$saveArray[$_key[0]][$x1Conter][] = trim($_data);
				$x1++;
				if($x1 == $x1Break){
					$x1Conter++;
					$x1 = 0;
				}
			}

			if($_key[0] == 2){
				$saveArray[$_key[0]][$x2Conter][] = trim($_data);

				$x2++;
				if($x2 == $x2Break){
					$x2Conter++;
					$x2 = 0;
				}
			}

			if($_key[0] == 3){
				$saveArray[$_key[0]][$x3Conter][] = trim($_data);

				$x3++;
				if($x3 == $x3Break){
					$x3Conter++;
					$x3 = 0;
				}
			}
		}

		//Wenn nix da ist wird nix abgerechnet
		if(count($saveArray) == 0){
			die();
		}

		$ArrayFields = array(
						1 => array(
								'0' => 'reportnumber',
								'1' => 'examinierer',
								'2' => 'examination_start',
								'3' => 'examination_end',
								'4' => 'examination_time',
								'5' => 'welds',
								'6' => 'welds_ne',
								'7' => 'reportnumber_id',
								'8' => 'fake',
								'9' => 'fake',
								),
						2 => array(
								'0' => 'number',
								'1' => 'dimension',
								'2' => 'position',
								'3' => 'price',
								'4' => 'price_total',
								'5' => 'ids',
								),
						3 => array(
								'0' => 'id',
								'1' => 'order_id',
								'2' => 'position',
								'3' => 'kurztext',
								'4' => 'examinierer_1',
								'5' => 'examinierer_2',
								'6' => 'date',
								'7' => 'amount',
								'8' => 'number',
								'9' => 'price',
								'10' => 'price_total',
								)
							);
		// Die Nummernkeys werden mit den Bezeichnungen der Datenabankfelder getauscht
		foreach($saveArray as $_key => $_saveArray){
			foreach($_saveArray as $__key => $__saveArray){
				foreach($__saveArray as $___key => $___saveArray){
					if(isset($ArrayFields[$_key][$___key])){
						$saveArray2[$_key][$__key][$ArrayFields[$_key][$___key]] = $___saveArray;
						$saveArray2[$_key][$__key]['testingmethod_id'] = $testingmethodID;
						$saveArray2[$_key][$__key]['topproject_id'] = $this->_controller->request->projectvars['projectID'];
						$saveArray2[$_key][$__key]['order_id'] = $this->_controller->request->projectvars['orderID'];
					}
				}
			}
		}

		return $saveArray2;
	}	
}