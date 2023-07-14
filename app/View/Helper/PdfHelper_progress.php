<?php
class PdfHelper extends AppHelper {

public $helpers = array('Image');

public function MakeHaederData($tcpdf,$position) {

$Reports = 'Report'.$tcpdf->Verfahren.'Evaluation';
$ReportsHaed = array();

foreach($tcpdf->xsettings->$Reports as $_Report){
	foreach($_Report as $__Report){
		// Wenn die Bezeichnung ein Array ist, bei Zeilenumbrüchen
		if($__Report->pdf->description->p != ''){
			$discriptionArray = array();
			$row = null;
			foreach($__Report->pdf->description->p as $_p){
				// um das XML-Objekt loszuwerden
				$row .= $_p;
			 	$discriptionArray[] = $row;
				$row = null;
			}
		}
		else {
			$discriptionArray = trim($__Report->pdf->description);
		}

		// Test ob Objekt an mehreren Stellen angezeigt werden soll
			$place = explode(' ',$__Report->pdf->output);
			foreach($place as $_place){
				if($_place == $position){
					$ReportsHaed[trim($__Report->pdf->sorting)]['key'] = trim($__Report->key);
					$ReportsHaed[trim($__Report->pdf->sorting)]['sorting'] = trim($__Report->pdf->sorting);
					$ReportsHaed[trim($__Report->pdf->sorting)]['description'] = $discriptionArray;
					$ReportsHaed[trim($__Report->pdf->sorting)]['width'] = trim($__Report->pdf->positioning->width);
					$ReportsHaed[trim($__Report->pdf->sorting)]['height'] = trim($__Report->pdf->positioning->height);
					$ReportsHaed[trim($__Report->pdf->sorting)]['rotation'] = trim($__Report->pdf->positioning->rotation);
					$ReportsHaed[trim($__Report->pdf->sorting)]['mother'] = trim($__Report->pdf->mother);
					$ReportsHaed[trim($__Report->pdf->sorting)]['child'] = trim($__Report->pdf->child);
					$ReportsHaed[trim($__Report->pdf->sorting)]['fontsize'] = trim($__Report->pdf->fontsize);
					$ReportsHaed[trim($__Report->pdf->sorting)]['fontweight'] = trim($__Report->pdf->fontweight);
					$ReportsHaed[trim($__Report->pdf->sorting)]['fontcolordata'] = $__Report->pdf->fontcolordata;
					$ReportsHaed[trim($__Report->pdf->sorting)]['linesdescription'] = $__Report->pdf->lines->description;
					$ReportsHaed[trim($__Report->pdf->sorting)]['lines'] = $__Report->pdf->lines->description;
					$ReportsHaed[trim($__Report->pdf->sorting)]['measure'] = isset($__Report->pdf->messure) ? trim($__Report->pdf->messure) : '';
					$ReportsHaed[trim($__Report->pdf->sorting)]['measure'] = isset($__Report->pdf->measure) ? trim($__Report->pdf->measure) : $ReportsHaed[trim($__Report->pdf->sorting)]['measure'];
					$ReportsHaed[trim($__Report->pdf->sorting)]['formatting'] = array(
						'bold' => trim($__Report->pdf->fontbold),
						'italic' => trim($__Report->pdf->fontitalic),
						'underline' => trim($__Report->pdf->fontunderline)
					);
				}
				break;
			}
		}
	}
	// Arrays nach der Reihenfolge sortieren
	ksort($ReportsHaed);

	// falls Felder zusammengefasst werden sollen
	foreach($ReportsHaed as $key => $value){
		if($value['child'] == 1){
			$ChildArray = array();
			foreach($_Report as $_ReportChild){
				if($_ReportChild->pdf->mother == $value['key']){
					$ChildArray[] = $_ReportChild->key;
				}
			}

		$ReportsHaed[$key]['childKeys'] = $ChildArray;
		}
	}
	return $ReportsHaed;
}

public function MakeBodyData($tcpdf) {

	$viewVars = $this->_View->viewVars;

	$Reports[0] = 'Report'.$tcpdf->Verfahren.'Generally';
	$Reports[1] = 'Report'.$tcpdf->Verfahren.'Specific';
	$Reports[2] = 'Report'.$tcpdf->Verfahren.'Evaluation';

	$ReportsPDF = 'Report'.$tcpdf->Verfahren.'Pdf';

	$ReportsFooter = array();

	foreach($Reports as $_Reports){
		foreach($tcpdf->xsettings->$_Reports as $_xsettings){
			if($_Reports == $Reports[2]) {
				for($id=0; $id<$tcpdf->xdata->$_Reports->count(); ++$id) {
					$footerData = array();
					foreach($_xsettings as $__xsettings){
						if($__xsettings->pdf->output == 4){

							$evalData = ($tcpdf->xdata->xpath($_Reports));
							$evalData = $evalData[$id];
							$key = trim($__xsettings->key);
							if(trim($evalData->$key) == '') continue;

							if(trim($evalData->$key) == '' && empty($__xsettings->pdf->headline)) continue;

							// Wenn die Bezeichnung ein Array ist, bei Zeilenumbrüchen
							if($__xsettings->pdf->description->p != ''){
								$discriptionArray = array();
								$row = null;
								foreach($__xsettings->pdf->description->p as $_p){
									// um das XML-Objekt loszuwerden
									$row .= $_p;
						 			$discriptionArray[] = $row;
									$row = null;
								}
							}
							else {
								$discriptionArray = trim($__xsettings->pdf->description);
								$placeholder = array();
								if(preg_match('/\%(.+?)\%/',$discriptionArray, $placeholder)) {
									foreach($placeholder as $_place) {
										$replace = null;
										if(isset($tcpdf->xdata->$_Reports->$_place)) {
											$replace = trim($evalData->$_place);
										}
										$discriptionArray = str_replace('%'.$_place.'%', $replace, $discriptionArray);
									}
								}
							}

							if($evalData->$key != ''){
								$footerData['data'] = $evalData->$key;
							}

							$footerData['headline'] = trim($__xsettings->pdf->headline);
							$footerData['key'] = trim($__xsettings->key);
							$footerData['sorting'] = trim($__xsettings->pdf->sorting);
							$footerData['description'] = $discriptionArray;
							$footerData['width'] = trim($__xsettings->pdf->positioning->width);
							$footerData['height'] = trim($__xsettings->pdf->positioning->height);
							$footerData['break'] = intval(trim($__xsettings->pdf->positioning->break));
							$footerData['rotation'] = trim($__xsettings->pdf->positioning->rotation);
							$footerData['fontsize'] = trim($__xsettings->pdf->fontsize);
							$footerData['fontbold'] = trim($__xsettings->pdf->fontbold);
							$footerData['fontcolor'][0] = trim($__xsettings->pdf->fontcolordata->color[0]);
							$footerData['fontcolor'][1] = trim($__xsettings->pdf->fontcolordata->color[1]);
							$footerData['fontcolor'][2] = trim($__xsettings->pdf->fontcolordata->color[2]);
							$footerData['mother'] = trim($__xsettings->pdf->mother);
							$footerData['child'] = trim($__xsettings->pdf->child);

							// Wenn kein Bereich aus den Evaluations in den Body soll, darf natürlich nichts eingefügt werden
							if(!empty($footerData)) $ReportsFooter[] = $footerData;
						}
					}
				}
			} else {
				foreach($_xsettings as $__xsettings){
					if($__xsettings->pdf->output == 4){
						if(trim($tcpdf->xdata->$_Reports->{$__xsettings->key}) == '') continue;
						if(isset($__xsettings->pdf->showif->field)) {
							$field = explode('.',trim($__xsettings->pdf->showif->field));
							if(count($field) == 1) array_unshift($field, $_Reports);
							$value = trim($__xsettings->pdf->showif->value);

							$xml = $tcpdf->xsettings->xpath($field[0].'/*[key="'.$field[1].'"]');

							// Neben dem eigentlichen Wert noch Testen, ob evtl. ein Radiobutton angegeben ist (sollte nicht vorkommen)
							if(isset($xml[0]->radiooption->value)) {
								$valueText = $xml[0]->xpath('radiooption/value['.(intval($value)+1).']');
								$valueText = trim($valueText[0]);
							}

							if(trim($tcpdf->xdata->{$field[0]}->{$field[1]}) != $value && (!isset($valueText) || trim($tcpdf->xdata->{$field[0]}->{$field[1]}) != $valueText)) continue;
						}

						// Wenn die Bezeichnung ein Array ist, bei Zeilenumbrüchen
						if($__xsettings->pdf->description->p != ''){
							$discriptionArray = array();
							$row = null;
							foreach($__xsettings->pdf->description->p as $_p){
								// um das XML-Objekt loszuwerden
								$row .= $_p;
					 			$discriptionArray[] = $row;
								$row = null;
							}
						}
						else {
							$discriptionArray = trim($__xsettings->pdf->description);
							$placeholder = array();
							if(preg_match('/\%(.+?)\%/',$discriptionArray, $placeholder)) {
								foreach($placeholder as $_place) {
									$replace = null;
									if(isset($tcpdf->xdata->$_Reports->$_place)) {
										$replace = trim($tcpdf->xdata->$_Reports->$_place);
									}
									$discriptionArray = str_replace('%'.$_place.'%', $replace, $discriptionArray);
								}
							}
						}

						$key = trim($__xsettings->key);
						if($tcpdf->xdata->$_Reports->$key != ''){
							$_data_string = preg_replace(array('/(\r\n|\n\r)/', '/[\n]+/', '/\|br\|/','/\|(\/{0,1})([ibu])\|/'), array(PHP_EOL, '<br>', '<br>','<$1$2>'), trim($tcpdf->xdata->$_Reports->$key));
							$_data_array =  explode('<br>',$_data_string);
							$ReportsFooter[trim($__xsettings->pdf->sorting)]['data'] = $tcpdf->xdata->$_Reports->$key;
							$ReportsFooter[trim($__xsettings->pdf->sorting)]['data_array'] = $_data_array;
							unset($_data_array);
						}

						$ReportsFooter[trim($__xsettings->pdf->sorting)]['key'] = trim($__xsettings->key);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['sorting'] = trim($__xsettings->pdf->sorting);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['description'] = $discriptionArray;
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['width'] = trim($__xsettings->pdf->positioning->width);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['height'] = trim($__xsettings->pdf->positioning->height);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['break'] = intval(trim($__xsettings->pdf->positioning->break));
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['lines'] =	array(
							trim($__xsettings->pdf->lines->description),
							trim($__xsettings->pdf->lines->data),
						);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['bgcolorlabel'][0] = trim($__xsettings->pdf->bgcolorlabel->color[0]);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['bgcolorlabel'][1] = trim($__xsettings->pdf->bgcolorlabel->color[1]);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['bgcolorlabel'][2] = trim($__xsettings->pdf->bgcolorlabel->color[2]);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['bgcolorlabel'][3] = trim($__xsettings->pdf->bgcolorlabel->color[3]);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['customwidth']	=  trim($__xsettings->pdf->positioning->customwidth);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['rotation'] = trim($__xsettings->pdf->positioning->rotation);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['fontsize'] = trim($__xsettings->pdf->fontsize);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['fontcolor'][0] = trim($__xsettings->pdf->fontcolordata->color[0]);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['fontcolor'][1] = trim($__xsettings->pdf->fontcolordata->color[1]);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['fontcolor'][2] = trim($__xsettings->pdf->fontcolordata->color[2]);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['mother'] = trim($__xsettings->pdf->mother);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['child'] = trim($__xsettings->pdf->child);
						$ReportsFooter[trim($__xsettings->pdf->sorting)]['fontbold'] = trim($__xsettings->pdf->fontbold);
					}
				}
			}
		}
	}

	// wenn in XML aktiviert, Änderungen im Revisionsbericht ausweisen
	if(!isset($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION) || preg_match('/(true|[1-9x])/', strtolower(trim($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->output)))) {
		if(isset($tcpdf->xchanges) && !empty($tcpdf->xchanges)) {
			$txtData = array(
				'sorting' => isset($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->sorting) ? intval($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->sorting) : null,
				'description' => isset($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->description) ? trim($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->description) : __('Changes to previous version'),
				'width' => isset($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->positioning->width) ? trim($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->positioning->width) : null,
				'height' => isset($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->positioning->height) ? trim($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->positioning->height) : null,
				'break' => isset($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->positioning->break) ? intval($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->positioning->break) : null,
				'fontsize' => isset($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->fontsize) ? intval($tcpdf->xsettings->{'Report'.$tcpdf->Verfahren.'Pdf'}->settings->QM_REVISION->pdf->fontsize) : null,
				'data'=>null
			);
			// Foth 19.09.2017
			// Die Revisionsdaten nochmals als Array übergeben
			$x = 0;

			foreach($tcpdf->xchanges as $_model => $changes) {
				if(preg_match('/Evaluation$/', $_model)) {
					foreach($changes as $change) {

						// Foth 20.09.2017, wenn ein Prüfabschnitt gelöscht wurde
						if(isset($change[3]['deleted_in_revision']) && $change[3]['deleted_in_revision'] == true){
						}

						if(empty($change[0])) {
							// Foth 19.09.2017
							$txtData['data_array'][$x]['dscription'] = __('New Evaluation Area').': '.$change[1]['description'];
							$txtData['data'] .= __('New Evaluation Area').': '.$change[1]['description'];
							if(isset($change[1]['position'])) $txtData['data'] .= ' '.$change[1]['position'];
							$txtData['data'] .= '|br|';
						} else {
							$txtData['data_array'][$x]['dscription'] = __('Evaluation edited').': '.$change[0]['description'];
							$txtData['data'] .= __('Evaluation edited').': |i|'.$change[0]['description'];
							if(isset($change[0]['position'])){
								$txtData['data'] .= ' '.$change[0]['position'];
								$txtData['data_array'][$x]['dscription'] = $txtData['data_array'][$x]['dscription'] . ' ' . $change[0]['position'];

							}
							$txtData['data'] .= '|/i||br|';

							foreach(array_keys($change[2]) as $field) {

								// Foth 19.09.2017
								if(isset($tcpdf->xsettings->$_model->$field->fieldtype) && trim($tcpdf->xsettings->$_model->$field->fieldtype) == 'radio'){
									if(!empty($tcpdf->xsettings->$_model->$field->radiooption->value)){
										if(!empty($tcpdf->xsettings->$_model->$field->radiooption->value[intval($change[0][$field])])){
											$change[0][$field] = trim($tcpdf->xsettings->$_model->$field->radiooption->value[intval($change[0][$field])]);
										}
										if(!empty($tcpdf->xsettings->$_model->$field->radiooption->value[intval($change[1][$field])])){
											$change[1][$field] = trim($tcpdf->xsettings->$_model->$field->radiooption->value[intval($change[1][$field])]);
										}
									} else {
										$change[0][$field] = 'no/nein';
										$change[1][$field] = 'yes/ja';
									}
								}

								$txtData['data_array'][$x]['dscription'] = $txtData['data_array'][$x]['dscription'] . ': ' . str_replace('|br|', ' ',trim($tcpdf->xsettings->$_model->$field->pdf->description)) . ' ' . $change[0][$field].' => '.$change[1][$field];

								$txtData['data'] .= '|b|'.str_replace('|br|', ' ', trim($tcpdf->xsettings->$_model->$field->discription->deu)).'|/b|: '
									.$change[0][$field].' => '
									.$change[1][$field]
									.'|br|';

							}
						}
						$x++;
					}
				} else {
					foreach($changes as $field => $values) {

						// nochmaliger Test ob der Knoten im aktuellen Dokument noch vorhanden ist
						if(!isset($tcpdf->xsettings->$_model)) continue;
						if(!isset($tcpdf->xsettings->$_model->$field)) continue;

						// Foth 19.09.2017
						if(isset($tcpdf->xsettings->$_model->$field->fieldtype) && trim($tcpdf->xsettings->$_model->$field->fieldtype) == 'radio'){
							if(!empty($tcpdf->xsettings->$_model->$field->radiooption->value)){
								if(!empty($tcpdf->xsettings->$_model->$field->radiooption->value[intval($values[0])])){
									$values[0] = trim($tcpdf->xsettings->$_model->$field->radiooption->value[intval($values[0])]);
								}
								if(!empty($tcpdf->xsettings->$_model->$field->radiooption->value[intval($values[1])])){
									$values[1] = trim($tcpdf->xsettings->$_model->$field->radiooption->value[intval($values[1])]);
								}
							} else {
								$values[0] = 'no/nein';
								$values[1] = 'yes/ja';
							}
						}

						$txtData['data_array'][$x]['dscription'] = str_replace('|br|','',trim($tcpdf->xsettings->$_model->$field->discription->deu)).': '.$values[0].' => '.$values[1];
						$txtData['data'] .= '|b|'.trim($tcpdf->xsettings->$_model->$field->pdf->description).'|/b|: '.$values[0].' => '.$values[1].'|br|';
						$x++;
					}
				}
			}

			$txtData['description'] = $txtData['description'] . '|br|' . 'Prüfbericht' . ' ' . $tcpdf->Verfahren . ' ' . $this->request->RevisionOldReport['Reportnumber']['year'].'-'.$this->request->RevisionOldReport['Reportnumber']['number'] . ' ' . 'wird ersetzt durch den Prüfbericht' . ' ' . $tcpdf->Verfahren . ' ' . $this->request->RevisionNewReport['Reportnumber']['year'].'-'.$this->request->RevisionNewReport['Reportnumber']['number'];

			$txtData['data'] = preg_replace(array('/\|br\|/','/\|(\/{0,1})([ibu])\|/'), array('<br>','<$1$2>'), is_array($txtData['data']) ? reset($txtData['data']) : $txtData['data']);
			$ReportsFooter[$txtData['sorting']] = $txtData;
		}
	}

	// Wenn eine Revison vorliegt
	if(trim($tcpdf->xdata->Reportnumber->revision) > 0){

		if(isset($tcpdf->xsettings->$ReportsPDF->settings->QM_REVISION_TEXT)){

			$rev_version = intval(trim($tcpdf->xdata->Reportnumber->revision)) - 1;
			$rev_version_txt = null;

			if($rev_version > 0) $rev_version_txt = ' R' . $rev_version;
			if(trim($tcpdf->xsettings->$ReportsPDF->settings->QM_REVISION_TEXT->SHOW) == 1){
				$revision = array(
					'data' => array(),
					'data_array' => array(),
					'key' => trim($tcpdf->xsettings->$ReportsPDF->settings->QM_REVISION_TEXT->KEY),
					'sorting' => trim($tcpdf->xsettings->$ReportsPDF->settings->QM_REVISION_TEXT->SORTING),
					'description' => trim($tcpdf->xsettings->$ReportsPDF->settings->QM_REVISION_TEXT->DESCRIPTION) . ' ' . $this->ConstructReportNamePdf($tcpdf->xdata,null,false) . $rev_version_txt,
				  'width' => trim($tcpdf->xsettings->$ReportsPDF->settings->QM_REVISION_TEXT->WIDTH),
				  'height' => trim($tcpdf->xsettings->$ReportsPDF->settings->QM_REVISION_TEXT->HEIGHT),
				  'break' => trim($tcpdf->xsettings->$ReportsPDF->settings->QM_REVISION_TEXT->BREAK),
				  'fontsize' => trim($tcpdf->xsettings->$ReportsPDF->settings->QM_REVISION_TEXT->FONTSIZE),
				  'lines' => array(),
				  'rotation' => null,
				  'mother' => null,
				  'child' => null,

				);
				array_push($ReportsFooter,$revision);
			}
		}
	};

	return $ReportsFooter;
}

public function MakeEvolutionData($tcpdf,$ReportsHaed) {
	$x = 0;
	$Reports = 'Report'.$tcpdf->Verfahren.'Evaluation';
	$Evaluation = $tcpdf->xdata->$Reports;
	$ReportsData = array();

	foreach($Evaluation as $_ReportsData){
		foreach($_ReportsData as $key => $value){
			foreach($ReportsHaed as $_ReportsHaed){
				if($key == $_ReportsHaed['key']){
//					$value = preg_replace('/\xc3\x83/', "\xc3\x9f", $value);		// ß
//					$value = preg_replace('/\xc3\x9f/', "\xc3\xb8", $value);		// Ø
					$value = preg_replace('/\xc2\xb0/',"\xe2\x81\xb0", $value);		// superscript 0
					//$value = preg_replace('/\xc2\xb9/',"\xc2\xb9", $value);		// superscript 1
					//$value = preg_replace('/\xc2\xb2/',"\xc2\xb2", $value);		// superscript 2
					//$value = preg_replace('/\xc2\xb3/',"\xc2\xb3", $value);		// superscript 3
					$value = preg_replace('/\xc3\xa2\xc2/',"\xe2\x81", $value);	// superscript 4-9,+-()=
					//$value = preg_replace('/\xc3\xa2\xc2/',"\xe2\x81", $value);	// superscript 4-9,+-()=
					$value = preg_replace('/\xe2\x81\xb0/',"\xc2\xb0", $value);	// °

					//pr($value);
					//pr(array_map(function($char){return $char.'('.dechex(ord($char)).')';}, str_split($value)));

					$ReportsData[$x][$_ReportsHaed['sorting']]['id'] = trim($_ReportsData->id);
					$ReportsData[$x][$_ReportsHaed['sorting']]['key'] = $key;
					$ReportsData[$x][$_ReportsHaed['sorting']]['width'] = trim($_ReportsHaed['width']);
					$ReportsData[$x][$_ReportsHaed['sorting']]['measure'] = '';
					if(isset($_ReportsHaed['measure'])) $ReportsData[$x][$_ReportsHaed['sorting']]['measure'] = trim($_ReportsHaed['measure']);
					$ReportsData[$x][$_ReportsHaed['sorting']]['data'] = $value;


					// Wenn Daten aus anderen Felder hinzugefügt werden sollen
					if($value != '' && isset($_ReportsHaed['childKeys']) && is_array($_ReportsHaed['childKeys'])){
						foreach($tcpdf->xdata->$Reports as $_cildeReports){
							if(trim($_cildeReports->id) == trim($_ReportsData->id)){
								foreach($_ReportsHaed['childKeys'] as $_childKeys){
									$childElement = trim($_childKeys);
									if(trim($_cildeReports->$childElement) != ''){
										$ReportsData[$x][$_ReportsHaed['sorting']]['data'] .= " ".$_cildeReports->$childElement;
									}
								}
							}
						}
					}
				}
			}
		}
		$x++;
	}
	for($x = 0; $x < count($ReportsData); $x++){
		ksort($ReportsData[$x]);
	}
	return $ReportsData;
}

public function WeldSorting($data) {

	$weld_descriptions = Hash::extract($data, '{n}.{n}.{n}[key=description]');
	$weld_positions = Hash::extract($data, '{n}.{n}.{n}[key=position]');

	if(count($weld_descriptions) == 0) return $data;
	if(count($weld_positions) == 0) return $data;

	$weld_descriptions_sorting = array();




     foreach($weld_descriptions as $_key => $_weld_descriptions){
		unset($weld_descriptions[$_key]);
		$weld_descriptions[$_weld_descriptions['data']] = array();
		$weld_descriptions_sorting[] = $_weld_descriptions['data'];
	}

    natsort($weld_descriptions_sorting);

	$weld_descriptions_sorting = array_flip($weld_descriptions_sorting);

	$weldDiscription = array();
	$_weldDiscription = array();
	$welds = array();

	foreach($data as $_key => $_data){
		foreach($_data as $__key => $__data){
			$weld_description = Hash::extract($__data, '{n}[key=description].data');
			$weld_position = Hash::extract($__data, '{n}[key=position].data');

			$__data['inter_weld_count_one'] = $_key;

			if(isset($weld_description[0])){
				$weld_descriptions[$weld_description[0]][$weld_position[0]] = $__data;
			}
		}
	}


	foreach($weld_descriptions_sorting as $_key => $_value){
		$weld_descriptions_sorting[$_key] = $weld_descriptions[$_key];
	}

	$weld_descriptions = array();
	$data = array();

	foreach($weld_descriptions_sorting as $_key => $_weld_descriptions){

		ksort($_weld_descriptions);
		$weld_descriptions[$_key] = $_weld_descriptions;

		foreach($_weld_descriptions as $__key => $__weld_descriptions){
			$inter_weld_count_one = $__weld_descriptions['inter_weld_count_one'];
			unset($__weld_descriptions['inter_weld_count_one']);
			$data[$inter_weld_count_one][] = $__weld_descriptions;
		}
	}

        if (Configure::read('SortSheetNumber') == true) {
            $datasheet = array();
            foreach ($data as $k_d => $v_d) {
                foreach($v_d as $_k => $_d) {
                    $weld_sheet = Hash::extract($_d, '{n}[key=sheet_no].data');
                     $weld_desc = Hash::extract($_d, '{n}[key=description].data');
                      $weld_position = Hash::extract($_d, '{n}[key=position].data');
                   if (!empty($weld_sheet)) {
                      // $datasheet [$weld_sheet[0]][$_k] = $_d;
                       $datasheet [$weld_sheet[0]][$weld_desc[0]][$weld_position[0]] = $_d;
                   }
                   else break;
                 }

            }


           if( !empty($datasheet)){
               $data =array();

           }else{return $data;}


             ksort($datasheet);
            foreach ($datasheet as $tkey => $rvalue) {
                foreach ($rvalue as $rkey => $r_value) {
                    uksort($r_value, 'strnatcmp');
                    $datasheet [$tkey][$rkey] = $r_value;
                }
            }
           $testdata = Hash::extract($datasheet, '{*}.{*}.{*}');
            $i= 1;
             $data = array();
             foreach ($testdata as $tdkey => $tdvalue) {
                $data[1][$i] = $tdvalue;
                $i++;
             }

        }

	return $data;

}

public function PrintEvolutionHaeder($tcpdf,$ReportsHaedSort,$z) {

	if($tcpdf->GetY() > $tcpdf->defs['QM_PAGE_BREAKE']) return;

	$ReportPdf = $tcpdf->ReportPdf;
	$ReportEvaluation = $tcpdf->ReportEvaluation;
	$imageArrayEvaluation = array();
	$ReportSpecific= 'Report'.ucfirst($tcpdf->xdata->Testingmethod->value).'Specific';

	$MarginLeftPlus = 0;

	$tcpdf->Line($tcpdf->defs['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $tcpdf->defs['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY(), $tcpdf->style1);

	// Nach Bilder im Auswertebereich schauen
	foreach($tcpdf->xsettings->$ReportPdf->images as $_images){
		foreach($_images as $__images){
			if($__images->position->area == $ReportEvaluation){
				$___images = array();
				$___images['type'] = $__images->type;
				$___images['file'] = Configure::read('pdf_images_folder').$tcpdf->xdata->Testingmethod->id.'/'.$__images->file.'.'.$__images->type;
				$dimension = explode(' ',$__images->dimension);
				$position = explode(' ',$__images->position->float);
				$___images['width'] = $dimension[0];
				$___images['height'] = $dimension[1];
				$___images['vertikal'] = $position[0];
				$___images['horizontal'] = $position[1];
				$imageArrayEvaluation[$ReportEvaluation][] = $___images;
			}
		}
	}

	if(isset($tcpdf->xsettings->$ReportPdf->additional->dataheader->element->text)) {

	$AdditionalHaederText =	null;

	$tcpdf->Line($tcpdf->defs['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $tcpdf->defs['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY(), $tcpdf->style2);

	$tcpdf->xsettings->EvolutionImageHeight = 0;

	if(count($imageArrayEvaluation) > 0){
		// mögliche Bilderposition einlesen
		if(isset($imageArrayEvaluation[$ReportEvaluation])){

			foreach($imageArrayEvaluation[$ReportEvaluation] as $_imageArray){
				if($_imageArray['type'][0] == 'eps'){
//					$tcpdf->Ln();
					// Wenn das Bild unterhalb eingefügt wird, muss der Y-Punkt gesetzt werden

					$tcpdf->ImageEPS(
						realpath($_imageArray['file']),
						$tcpdf->defs['PDF_MARGIN_LEFT']+$tcpdf->defs['QM_CELL_PADDING_L'],
						$tcpdf->GetY(),
						$_imageArray['height'],
						$_imageArray['width'],
						null,
						true,
						$_imageArray['vertikal'],
						$_imageArray['horizontal'],
						false,
						false,
						true
					);

				}
			}
			$MarginLeftPlus = $MarginLeftPlus + $_imageArray['height'] + 1;
			$tcpdf->xsettings->EvolutionImageHeight = ($tcpdf->getImageRBY());
		}
	}

	// Bei einem eingefügten Bild wird der X-Einrückwert gespeichert
	$tcpdf->MarginLeftPlus = $MarginLeftPlus;

	foreach($tcpdf->xsettings->$ReportPdf->additional->dataheader->element as $_element){
		$tcpdf->SetFont(
				'helvetica',
				$_element->fontstyle,
				$_element->fontsize
			);

		$AdditionalLineStyle = 'style' . trim($_element->line);

		$tcpdf->MultiCell(
				$_element->position->w,
				5,
				$_element->text,
				$tcpdf->$AdditionalLineStyle,
				'L',
				false,
				$_element->position->ln,
				$_element->position->x,
				$tcpdf->GetY(),
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

	$StandartFontForHaeder = 'helveticaB';
	$StandartFontweightForHaeder = 'B';
	$StandartFontsizeForHaeder = 6;

	$tcpdf->SetFont($StandartFontForHaeder,$StandartFontweightForHaeder, $StandartFontsizeForHaeder);

	$tcpdf->SetX($tcpdf->defs['PDF_MARGIN_LEFT'] + $MarginLeftPlus);
	$startY = $tcpdf->GetY();
	$StartX = $tcpdf->GetX() + $MarginLeftPlus;
	$StartXCell = $tcpdf->GetX();
	$StartYCell = $tcpdf->GetY();
	$this->StartEvolutionHaeder = $tcpdf->GetY();
	$LinieVertikalHaeder = $tcpdf->GetY();

	if($z > 0){
		$this->StartEvolutionHaeder2 = $tcpdf->GetY();
	}

	$nextY = 0;
	$lineArray = array();
	$x = 0;

	// erst testen ob auch vertikale Texte vorhanden sind
	$heightesCell = 0;
	$RotationTrue = 0;

	if(isset($ReportsHaedSort)){
		foreach($ReportsHaedSort as $_ReportsHaedSort){
			if($_ReportsHaedSort['height'] > $heightesCell){
				$heightesCell = $_ReportsHaedSort['height'];
				}
			if($_ReportsHaedSort['rotation'] != ''){
				$RotationTrue = $_ReportsHaedSort['rotation'];
			}
		}
	}

	//Wenn keine Rotation des Textes vorgesehen ist
	if($RotationTrue == 0){
		foreach($ReportsHaedSort as $_ReportsHaedSort){
					$_data = $_ReportsHaedSort['description'];
			if($_data == ''){
				$_data = ' ';
			}

			$NumLines = $tcpdf->getNumLines($_data,$_ReportsHaedSort['width'],true,false,false,0 );
			$StringHeight = $tcpdf->getStringHeight($_data,$_ReportsHaedSort['width'],true,false,false,0 );
			// die Höhe der Zelle wird überprüft, so das die nächste Zeile entsprechend weiter unten beginnt
		 	if($NumLines * $StringHeight - $StringHeight > $nextY){
				$nextY = $NumLines * $StringHeight - $StringHeight;
			}
			else{
	 			$nextY = $nextY;
			}
			// die Zelle ausgeben
			$__data = explode('|br|',$_data);
			$____data = null;

			if(count($__data) > 1){
				foreach($__data as $___data){
					$____data .= $___data;
					$____data .= "\n";
				}
			}
			else {
				$____data = $__data[0];
			}

			if(trim($_ReportsHaedSort['lines']) != ''){
				$linestyle = 'style'.trim($_ReportsHaedSort['lines']);
				$theLineHorizontal = $tcpdf->$linestyle;
			}
			else {
				$theLineHorizontal = $tcpdf->style6;
			}



			// Ausnahme, wenn in PDF Einstellungen festgelegt wird, dass erste Datenzeile der Auswertungen als Titel benutzt werden soll.
			//if($tcpdf->Verfahren == 'Rfa'){
			if(

						isset($tcpdf->xsettings->$ReportPdf->settings->use_first_evaluation_as_header)
						&& (
							trim($tcpdf->xsettings->$ReportPdf->settings->use_first_evaluation_as_header) == '1'
							|| strtolower(trim($tcpdf->xsettings->$ReportPdf->settings->use_first_evaluation_as_header)) == 'true'
						)
					) {
						$field = $_ReportsHaedSort['key'];
						$_thisKey = trim($tcpdf->xsettings->$ReportEvaluation->$field->key);
						foreach($tcpdf->xdata->$ReportEvaluation as $_key => $_ReportRfaEvaluation){
							if(trim($_ReportRfaEvaluation->$_thisKey) != null){
								$____data = (trim($_ReportRfaEvaluation->$_thisKey));

							}
							break;
						}

	 				}

						if(
									isset($tcpdf->xsettings->$ReportPdf->settings->use_data_for_header)
									&& (
										trim($tcpdf->xsettings->$ReportPdf->settings->use_data_for_header) == '1'
										|| strtolower(trim($tcpdf->xsettings->$ReportPdf->settings->use_data_for_header)) == 'true'
									)
								) {
									$datamodel = '';
									$datakey = '';
									$field = $_ReportsHaedSort['key'];
									if (isset($tcpdf->xsettings->$ReportEvaluation->$field->headerfrom->model))	$datamodel = trim($tcpdf->xsettings->$ReportEvaluation->$field->headerfrom->model);
									if (isset($tcpdf->xsettings->$ReportEvaluation->$field->headerfrom->key))$datakey	= trim($tcpdf->xsettings->$ReportEvaluation->$field->headerfrom->key);
									if (!empty($datakey) && !empty($datamodel)) {
										foreach ($tcpdf->xdata->$datamodel->children() as $ddkey => $ddvalue) {
											if($datakey == $ddkey && !empty($ddvalue) ) {
												$____data = $ddvalue;
											break;
											}
									 }
								 }
				 				}

			if(isset($_ReportsHaedSort['measure']) && !empty($_ReportsHaedSort['measure'])) $____data .= ' (' . $_ReportsHaedSort['measure'] . ')';

			$tcpdf->SetCellPadding(1);
			$tcpdf->MultiCell(
				$_ReportsHaedSort['width'],
				$heightesCell,
				$____data,
				$theLineHorizontal,
				'L',
				false,
				0,
				$tcpdf->GetX(),
				$tcpdf->GetY(),
				true,
				0,
				false,
				true,
				$heightesCell,
				'T',
				true
			);

			$StartXCell = $StartXCell + $_ReportsHaedSort['width'];

			//X-Positionen aufzeichen für die vertikalen Linien
			$lineArray[] = $tcpdf->GetX();
			$x++;
		}
//	$tcpdf->SetY($tcpdf->GetY() + 10,true,false);
	$tcpdf->SetY($tcpdf->GetY() + $heightesCell,true,false);

	if($tcpdf->Verfahren != 'Ovwd'){
		$tcpdf->Line($tcpdf->defs['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $tcpdf->defs['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY(), $tcpdf->style1);
		}
	if($tcpdf->Verfahren == 'Ovwd'){
		$tcpdf->Line($tcpdf->defs['PDF_MARGIN_LEFT'] + 35, $tcpdf->GetY(), $tcpdf->defs['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY(), $tcpdf->style1);
		}
	}

	elseif($RotationTrue > 0){

		$XStart = $tcpdf->GetX();
		$YLineStart = $tcpdf->GetY();
		$YStart = $tcpdf->GetY() + $heightesCell;

		foreach($ReportsHaedSort as $_ReportsHaedSort){
			// Bezeichnung in eine Variable schreiben
			$_data = $_ReportsHaedSort['description'];
			$__data = explode('|br|',$_data);
			$Xkorrektur = 0;


			if($_ReportsHaedSort['rotation'] == 90){
				$tcpdf->StartTransform();
				$tcpdf->Rotate(90, $XStart,$YStart);
				$Xkorrektur = $Xkorrektur + 2.5;
			}

			$tcpdf->Text(
					$XStart - $Xkorrektur, // X-Achse
					$YStart, // Y-Achse
					$__data[0], // Text
					false, // Outline in Users
					false // Clipping Mode aktivieren
				);

			// bei einem vertikalen Zeilenumbruch
			if(count($__data) > 1){
				$YKorrekturZeilenumbruch = 0;
				foreach($__data as $___key => $___data){
					$YKorrekturZeilenumbruch = $YKorrekturZeilenumbruch + 1;
					if($___key  > 0){
						$tcpdf->Text(
						$XStart - $Xkorrektur, // X-Achse
						$YStart + $YKorrekturZeilenumbruch, // Y-Achse
						$___data, // Text
						false, // Outline in Users
						false // Clipping Mode aktivieren
						);
					}
				}
			}

			if($_ReportsHaedSort['rotation'] == 90){
				$tcpdf->StopTransform();
			}

			$XStart  = $XStart + $_ReportsHaedSort['width'];

			// die Linien
			if($_ReportsHaedSort['lines'] == 8){
				$linestyle = 0.3;
			}
			else {
				$linestyle = 0.2;
			}

			$tcpdf->SetLineWidth($linestyle);
			$tcpdf->SetDrawColor(0,0,0);

			$tcpdf->Line(
				$XStart,
				$YStart - $_ReportsHaedSort['height'],
				$XStart,
				$YStart + 3
			);
		}
	$tcpdf->Ln();
	$tcpdf->Line($tcpdf->defs['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $tcpdf->defs['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY(), $tcpdf->style1);
	}
}


public function PrintEvolutionData($tcpdf,$ReportsData,$ReportsHaedSort,$version,$z) {
}

public function RateOfFilmConsumption($Data) {

	$films = array();

	foreach($Data->ReportRtEvaluation as $_ReportRtEvaluation){
		if(trim($_ReportRtEvaluation->film_dimension) != ''){
			$films[trim($_ReportRtEvaluation->film_dimension)][] = trim($_ReportRtEvaluation->description);
		}
		else {
			$films[__('keine Angaben')][] = trim($_ReportRtEvaluation->description);
		}
	}

	return $films;

}

public function WeldDimensions($Data) {

	$films = array();

	foreach($Data->ReportRtEvaluation as $_ReportRtEvaluation){
		if(trim($_ReportRtEvaluation->dimension) != ''){
			$films[trim($_ReportRtEvaluation->dimension)][] = trim($_ReportRtEvaluation->description);
		}
		else {
			$films[__('keine Angaben')][] = trim($_ReportRtEvaluation->description);
		}
	}

	return $films;

}

public function GetFontFormatting($item, $fonts=array()) {
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
}

public function PrintBodyData($tcpdf,$ReportsHaedSort) {

		$tcpdf->SetFont('calibri');
		$startY = $tcpdf->GetY();
		$StartX = $tcpdf->GetX();
		$StartXCell = $tcpdf->GetX();
		$StartYCell = $tcpdf->GetY();
		$maxY = 0;

		$page = $tcpdf->getPage();

		if(isset($ReportsHaedSort)){
			foreach($ReportsHaedSort as $_ReportsHaedSort){

				if(isset($_ReportsHaedSort['headline']) && $_ReportsHaedSort['headline'] == 1){
					$headline_line_vertical_top = $tcpdf->GetY();

					// horizontale Linie
					$tcpdf->Line(
						$tcpdf->defs['PDF_MARGIN_LEFT'],
						$tcpdf->GetY(),
						$tcpdf->defs['QM_CELL_LAYOUT_CLEAR'],
						$tcpdf->GetY(),
						$tcpdf->style2
					);

					//$tcpdf->SetCellPadding(1);
					$tcpdf->SetFont('calibri', 'n', 8);
					$tcpdf->SetFillColor(180,180,180);
					$tcpdf->MultiCell(
								$tcpdf->defs['QM_CELL_LAYOUT_CLEAR'] - $tcpdf->defs['PDF_MARGIN_LEFT'],
								0,
								$_ReportsHaedSort['description'],
								0,
								'L',
								1,
								1,
								$tcpdf->GetX(),
								$tcpdf->GetY(),
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);

					// horizontale Linie
					$tcpdf->Line(
						$tcpdf->defs['PDF_MARGIN_LEFT'],
						$tcpdf->GetY(),
						$tcpdf->defs['QM_CELL_LAYOUT_CLEAR'],
						$tcpdf->GetY(),
						$tcpdf->style2
					);
					// vertikale Linie links
					$tcpdf->Line(
						$tcpdf->defs['PDF_MARGIN_LEFT'],
						$headline_line_vertical_top,
						$tcpdf->defs['PDF_MARGIN_LEFT'],
						$tcpdf->GetY(),
						$tcpdf->style2
					);
					// vertikale Linie rechts
					$tcpdf->Line(
						$tcpdf->defs['QM_CELL_LAYOUT_CLEAR'],
						$headline_line_vertical_top,
						$tcpdf->defs['QM_CELL_LAYOUT_CLEAR'],
						$tcpdf->GetY(),
						$tcpdf->style2
					);

					continue;
				}

				$_data = trim(isset($_ReportsHaedSort['data']) ? is_array($_ReportsHaedSort['data']) ? reset($_ReportsHaedSort['data']) : $_ReportsHaedSort['data'] : '');

//				$_data = setUTF8($_data);
				$tcpdf->setFontSize($size = isset($_ReportsHaedSort['fontsize']) && !empty($_ReportsHaedSort['fontsize']) ? $_ReportsHaedSort['fontsize'] : 9);

				if(
					$tcpdf->GetY()
					+ $tcpdf->getStringHeight($tcpdf->defs['QM_CELL_LAYOUT_WIDTH']-$tcpdf->defs['PDF_MARGIN_LEFT']-$tcpdf->defs['QM_CELL_PADDING_L']-$tcpdf->defs['QM_CELL_PADDING_R'], $_ReportsHaedSort['description'].(empty($_data) ? '' : PHP_EOL.'Testzeile'), true, true, 0, 0)
					+ $tcpdf->defs['QM_CELL_PADDING_T']
					>= $tcpdf->defs['QM_PAGE_BREAKE']
				){
					$tcpdf->AddPage();
					$tcpdf->SetY($tcpdf->bodyStart[$tcpdf->getPage()]);
				}

				if(!empty($_ReportsHaedSort['break'])) $_ReportsHaedSort['break'] = 1;
				if(empty($_ReportsHaedSort['width'])) $_ReportsHaedSort['width'] = $tcpdf->defs['QM_CELL_LAYOUT_WIDTH']-$tcpdf->defs['QM_CELL_PADDING_R'];
				$_ReportsHaedSort['width'] = explode(' ', $_ReportsHaedSort['width']);
				if(is_string($_ReportsHaedSort['width']) || count($_ReportsHaedSort['width'])==1) $_ReportsHaedSort['width'] = array($_ReportsHaedSort['width'][0], $_ReportsHaedSort['width'][0]);
				if(isset($_ReportsHaedSort['bgcolorlabel'])){
					$bg = implode(',',$_ReportsHaedSort['bgcolorlabel']);
					$tcpdf->SetFillColor($_ReportsHaedSort['bgcolorlabel'][0],$_ReportsHaedSort['bgcolorlabel'][1],$_ReportsHaedSort['bgcolorlabel'][2]);
				}
				else{$tcpdf->SetFillColor(255,255,255);}
				$_ReportsHaedSort['description'] = preg_replace(array('/\|br\|/','/\|(\/{0,1})([ibu])\|/'), array('<br>','<$1$2>'), $_ReportsHaedSort['description']);
				if(isset($_ReportsHaedSort['customwidth']) && $_ReportsHaedSort['customwidth'] == 1) {
				$widthlabel =	$_ReportsHaedSort['width'][1];
				} else{
					$widthlabel = $tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] - $tcpdf->defs['PDF_MARGIN_LEFT'] - 1;
				}


				if(!empty($_ReportsHaedSort['description'])){
					$tcpdf->setFont(null, 'B', $size);

					$tcpdf->MultiCell(
						$widthlabel,
						0,
						$_ReportsHaedSort['description'],
						0,
						'L',
						1,
						0,
						$tcpdf->defs['PDF_MARGIN_LEFT']+$tcpdf->defs['QM_CELL_PADDING_L'],
						$tcpdf->GetY(),
						true,
						0,
						true,
						true,
						0,
						'T',
						true
					);
					$tcpdf->SetFontSize(2);
					$tcpdf->Ln();

				if(
					$tcpdf->GetY() + 1
					+ $tcpdf->getStringHeight($tcpdf->defs['QM_CELL_LAYOUT_WIDTH']-$tcpdf->defs['PDF_MARGIN_LEFT']-$tcpdf->defs['QM_CELL_PADDING_L']-$tcpdf->defs['QM_CELL_PADDING_R'], $_ReportsHaedSort['description'].(empty($_data) ? '' : PHP_EOL.'Testzeile'), true, true, 0, 0)
					+ $tcpdf->defs['QM_CELL_PADDING_T']
					>= $tcpdf->defs['QM_PAGE_BREAKE']
				){

					$tcpdf->AddPage();
					$tcpdf->SetY($tcpdf->bodyStart[$tcpdf->getPage()]);
					$tcpdf->setFont(null, 'B', $size);
					$tcpdf->MultiCell(
						$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] - $tcpdf->defs['PDF_MARGIN_LEFT'] - 1,//$_ReportsHaedSort['width'][0],
						0,
						$_ReportsHaedSort['description'],
						0,
						'L',
						false,
						0,
						$tcpdf->defs['PDF_MARGIN_LEFT']+$tcpdf->defs['QM_CELL_PADDING_L']+0.5,
						$tcpdf->GetY(),
						true,
						0,
						true,
						true,
						0,
						'T',
						true
					);
					$tcpdf->SetFontSize(2);
					$tcpdf->Ln();
				}

				}

				$_data = preg_replace(array('/(\r\n|\n\r)/', '/[\n]+/', '/\|br\|/','/\|(\/{0,1})([ibu])\|/'), array(PHP_EOL, '<br>', '<br>','<$1$2>'), $_data);

				$tcpdf->SetFont(null,'', $size);
				if(
					$tcpdf->GetY()
					//+ $tcpdf->getStringHeight($tcpdf->defs['QM_CELL_LAYOUT_WIDTH']-$tcpdf->defs['PDF_MARGIN_LEFT']-$tcpdf->defs['QM_CELL_PADDING_L']-$tcpdf->defs['QM_CELL_PADDING_R'], $_data, true, true, 0, 0)
					+ $tcpdf->getStringHeight($_ReportsHaedSort['width'][1], str_replace('<br>', PHP_EOL, $_data), true, true, 0, 0)
					> $tcpdf->defs['QM_PAGE_BREAKE']
				)
				{
					//$_data = $this->_splitTextByRamainingSize($tcpdf, $_data, $tcpdf->defs['QM_PAGE_BREAKE'] - $tcpdf->GetY(), $_ReportsHaedSort['width'][0], $_ReportsHaedSort['width'][1]);
//					$_data = $this->_splitTextByRamainingSize($tcpdf, $_data, $tcpdf->defs['QM_PAGE_BREAKE'] - $tcpdf->GetY(), $tcpdf->defs['PDF_MARGIN_LEFT'], $tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] - $tcpdf->defs['PDF_MARGIN_LEFT'] - 1);

					// Foth 19.09.2017
					if(isset($_ReportsHaedSort['data_array']) && count($_ReportsHaedSort['data_array']) > 0){



						foreach($_ReportsHaedSort['data_array'] as $_key => $_data_array){

						if(is_array($_data_array)){
							$_data_array = $_data_array['dscription'];
						}

						$tcpdf->setFont(null, 'N', $size);
/*
if(AuthComponent::user('id') == 1){
	print '<pre>';
	print_r($_data_array);
	print '</pre>';
}
*/
						if(isset($_ReportsHaedSort['fontcolor']) && is_array($_ReportsHaedSort['fontcolor'])){
							$tcpdf->SetTextColor($_ReportsHaedSort['fontcolor'][0],$_ReportsHaedSort['fontcolor'][1],$_ReportsHaedSort['fontcolor'][2]);

						}

						$tcpdf->MultiCell(
							$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] - $tcpdf->defs['PDF_MARGIN_LEFT'] - 1,//$_ReportsHaedSort['width'][0],
							2,
							$_data_array,
							0,
							'L',
							false,
							2,
							$tcpdf->defs['PDF_MARGIN_LEFT']+$tcpdf->defs['QM_CELL_PADDING_L']+0.5,
							$tcpdf->GetY(),
							true,
							0,
							true,
							true,
							0,
							'T',
							true
							);

						$tcpdf->SetTextColor(0,0,0);

							if($tcpdf->GetY() > $tcpdf->defs['QM_PAGE_BREAKE']){
								$tcpdf->AddPage();
								$tcpdf->SetY($tcpdf->bodyStart[$tcpdf->getPage()]);

					$tcpdf->setFont(null, 'B', $size);
					$tcpdf->MultiCell(
						$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] - $tcpdf->defs['PDF_MARGIN_LEFT'] - 1,//$_ReportsHaedSort['width'][0],
						0,
						$_ReportsHaedSort['description'],
						0,
						'L',
						false,
						0,
						$tcpdf->defs['PDF_MARGIN_LEFT']+$tcpdf->defs['QM_CELL_PADDING_L']+0.5,
						$tcpdf->GetY(),
						true,
						0,
						true,
						true,
						0,
						'T',
						true
					);
					$tcpdf->SetFontSize(2);
					$tcpdf->Ln();

							}

						}
						if(isset($_ReportsHaedSort['lines'][1])&& $_ReportsHaedSort['lines'][1] == 1){
							$tcpdf->Line(
								$tcpdf->defs['PDF_MARGIN_LEFT'],
								$tcpdf->GetY(),
								$tcpdf->defs['QM_CELL_LAYOUT_CLEAR'],
								$tcpdf->GetY(),
								$tcpdf->style2
							);
						}
					}

					$tcpdf->SetX($tcpdf->defs['PDF_MARGIN_LEFT']);
				} else {
					$maxY = max($maxY, $tcpdf->GetY()
						+ $tcpdf->getStringHeight($_ReportsHaedSort['width'][1], (empty($_data) ? 'Testzeile' : PHP_EOL.str_replace('<br>', PHP_EOL, $_data)), true, true, 0, 0)
						+ $tcpdf->defs['QM_CELL_PADDING_T']
					);
/*
				if(
					$tcpdf->GetY()
					+ $tcpdf->getStringHeight($tcpdf->defs['QM_CELL_LAYOUT_WIDTH']-$tcpdf->defs['PDF_MARGIN_LEFT']-$tcpdf->defs['QM_CELL_PADDING_L']-$tcpdf->defs['QM_CELL_PADDING_R'], $_ReportsHaedSort['description'].(empty($_data) ? '' : PHP_EOL.'Testzeile'), true, true, 0, 0)
					+ $tcpdf->defs['QM_CELL_PADDING_T']
					>= $tcpdf->defs['QM_PAGE_BREAKE']
				){
					$tcpdf->AddPage();
					$tcpdf->SetY($tcpdf->bodyStart[$tcpdf->getPage()]);
				}
*/
					$tcpdf->SetFillColor(0,255,0);

					// Foth 19.09.2017
					if(isset($_ReportsHaedSort['data_array']) && count($_ReportsHaedSort['data_array']) > 0){

//pr($_ReportsHaedSort['data_array']);

						foreach($_ReportsHaedSort['data_array'] as $_key => $_data_array){

						if(is_array($_data_array)){
							$_data_array = $_data_array['dscription'];
						}

						$tcpdf->setFont(null, 'N', $size);

						if(isset($_ReportsHaedSort['fontcolor']) && is_array($_ReportsHaedSort['fontcolor'])){
							$tcpdf->SetTextColor($_ReportsHaedSort['fontcolor'][0],$_ReportsHaedSort['fontcolor'][1],$_ReportsHaedSort['fontcolor'][2]);
						}

						if(isset($_ReportsHaedSort['fontbold']) && trim($_ReportsHaedSort['fontbold'] == 1)){

							$tcpdf->setFont(null, 'B', $size);
						}


						$tcpdf->MultiCell(
							$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] - $tcpdf->defs['PDF_MARGIN_LEFT'] - 1,//$_ReportsHaedSort['width'][0],
							2,
							$_data_array,
							0,
							'L',
							false,
							2,
							$tcpdf->defs['PDF_MARGIN_LEFT']+$tcpdf->defs['QM_CELL_PADDING_L']+0.5,
							$tcpdf->GetY(),
							true,
							0,
							true,
							true,
							0,
							'T',
							true
							);

						//	horizontallinie nach Bemerkung

							if($tcpdf->GetY() > $tcpdf->defs['QM_PAGE_BREAKE']){
								$tcpdf->AddPage();
								$tcpdf->SetY($tcpdf->bodyStart[$tcpdf->getPage()]);

								$tcpdf->setFont(null, 'B', $size);
								$tcpdf->MultiCell(
								$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] - $tcpdf->defs['PDF_MARGIN_LEFT'] - 1,//$_ReportsHaedSort['width'][0],
									0,
									$_ReportsHaedSort['description'],
									0,
									'L',
									false,
									0,
									$tcpdf->defs['PDF_MARGIN_LEFT']+$tcpdf->defs['QM_CELL_PADDING_L']+0.5,
									$tcpdf->GetY(),
									true,
									0,
									true,
									true,
									0,
									'T',
									true
								);
								$tcpdf->SetFontSize(2);
								$tcpdf->Ln();
							}
						}

						if(isset($_ReportsHaedSort['lines'][1])&& $_ReportsHaedSort['lines'][1] == 1){
							$tcpdf->Line(
								$tcpdf->defs['PDF_MARGIN_LEFT'],
								$tcpdf->GetY(),
								$tcpdf->defs['QM_CELL_LAYOUT_CLEAR'],
								$tcpdf->GetY(),
								$tcpdf->style2
							);
						}
						} else {

						if(isset($_ReportsHaedSort['fontcolor']) && is_array($_ReportsHaedSort['fontcolor'])){
							$tcpdf->SetTextColor($_ReportsHaedSort['fontcolor'][0],$_ReportsHaedSort['fontcolor'][1],$_ReportsHaedSort['fontcolor'][2]);
						}
						$tcpdf->setFont(null, 'B', $size);
						$tcpdf->MultiCell(
							$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] - $tcpdf->defs['PDF_MARGIN_LEFT'] - 1,//$_ReportsHaedSort['width'][1],
							0,
							$_data,
							0,
							'L',
							false,
							1,
							$tcpdf->defs['PDF_MARGIN_LEFT']+$tcpdf->defs['QM_CELL_PADDING_L']+0.5,
							$tcpdf->GetY(),
							true,
							0,
							true,
							true,
							0,
							'T',
							true
						);
						if(isset($_ReportsHaedSort['lines'][1])&& $_ReportsHaedSort['lines'][1] == 1){
							$tcpdf->Line(
								$tcpdf->defs['PDF_MARGIN_LEFT'],
								$tcpdf->GetY(),
								$tcpdf->defs['QM_CELL_LAYOUT_CLEAR'],
								$tcpdf->GetY(),
								$tcpdf->style2
							);
						}
					}
					
					$tcpdf->SetTextColor(0,0,0);
				}
			}
		}
/*
if(AuthComponent::user('id') == 1){
	die('toto');
}
*/

	}

	protected function _splitTextByRamainingSize($tcpdf, $texts, $size=0, $Xoffset, $width=0) {
		if(empty($width)) $width = $tcpdf->defs['QM_CELL_LAYOUT_WIDTH']-$tcpdf->defs['QM_CELL_PADDING_R'];
		if(is_object($texts) || is_array($texts)) $texts = reset($texts);
		if(!is_string($texts) || !preg_match_all('/([^\s]+)([\s]+)*/', $texts, $matches)) return;

		$matches = $matches[0];
		$line = '';
		while(!empty($matches) && $tcpdf->getStringHeight($width, $line, true, true, 0, 0) <= $size)
		{
			$line .= array_shift($matches);
		}

		$tcpdf->MultiCell(
			$width,
			0,
			$line,
			0,
			'L',
			false,
			2,
			$tcpdf->defs['PDF_MARGIN_LEFT']+$tcpdf->defs['QM_CELL_PADDING_L']+0.5,
			$tcpdf->GetY(),
			true,
			0,
			true,
			true,
			0,
			'T',
			true
		);

		if(!empty($matches))
		{
			$size = $tcpdf->getFontSizePt();
			$tcpdf->AddPage();
			$tcpdf->SetXY($Xoffset, $tcpdf->bodyStart[$tcpdf->getPage()]);
			$tcpdf->setFontSize($size);
			$this->_splitTextByRamainingSize($tcpdf, join('', $matches), $tcpdf->defs['QM_PAGE_BREAKE'] - $tcpdf->bodyStart[$tcpdf->getPage()], $Xoffset, $width);
		}

	}

	public function PrintAttachedReports($view, $tcpdf, $reports) {
		$startY = $tcpdf->GetY();
		$StartX = $tcpdf->GetX();
		$StartXCell = $tcpdf->GetX();
		$StartYCell = $tcpdf->GetY();
		if(isset($reports)){
			$_title = 'zugeordnete Berichte:';
			$_data = array();

			foreach($reports as $_reports){
				if($_reports['Verfahren'] == 'Alg') continue;

				$_data[] = $_reports['data']->Testingmethod->verfahren.' - '.$_reports['data']->Report->name.' '.$_reports['data']->Testingmethod->name.' '.$_reports['data']->Reportnumber->year.'/'.$_reports['data']->Reportnumber->id;
			}

			if($tcpdf->GetY() >= $tcpdf->defs['QM_PAGE_BREAKE'] && !empty($_data)){
				$tcpdf->AddPage();
				$tcpdf->SetY($tcpdf->HaederEnd);
			}

			$tcpdf->SetFont('helvetica', '', 6);
			$tcpdf->MultiCell(200,0,$_title,0,'L',false,2,$tcpdf->defs['PDF_MARGIN_LEFT'],$tcpdf->GetY(),true,0,false,true,0,'T',true);
			$tcpdf->SetFont('calibri');
			$tcpdf->MultiCell(200,0,join(PHP_EOL, $_data),0,'L',false,2,$tcpdf->defs['PDF_MARGIN_LEFT'],$tcpdf->GetY(),true,0,false,true,0,'T',true);
		}
	}

	public function ListLinkedReports($view, $id, $method, $childReports = array(), $unusedReports = array())
	{
		$output = '';
		$title = '';

		$unused = array();
		foreach($unusedReports as $unusedReport) {$unused[$unusedReport['id']] = $unusedReport['name'];}
		$unusedReports = $unused;

		$unused = array_keys($unused);
		$unused = reset($unused);

		if($method == 'alg')
		{
			$title = 'zugeordnete Berichte';

			if(count($childReports) > 0)
			{
				$output .= $view->Html->nestedList(
					array_map(
						function($elem, $view, $id) {
							return $view->Html->link($elem['name'], array('controller'=>'reportnumbers','action'=>'view',$elem['id'])).
							$view->Html->link(
								$view->Html->image('/img/remove.png', array('width'=>12)),
								array('action'=>'removechild',$id, $elem['id']), array('class'=>'ajax plain', 'escape'=>false)
							);
						},
						$childReports,
						array_fill(0, count($childReports), $view),
						array_fill(0, count($childReports), $id)
					)
				);
			}

			if(count($unusedReports) > 0)
			{
				$output .= $view->Form->select(
					'addUnusedReport',
					$unusedReports,
					array(
						'empty'=>false,
						'onchange'=>'$(\'#btnAddUnusedReport\').attr(\'href\',\''.$view->Html->url(array('action'=>'addchild',$id)).'/'.'\'+$(this).val());'
					)
				).
				' '.
				$view->Html->link(
					$view->Html->image('/img/add.png', array('width'=>12)),
					array('action'=>'addchild',$id, $unused),
					array('id'=>'btnAddUnusedReport', 'class'=>'ajax plain', 'escape'=>false)
				);
			}
		}
		else
		{
			$title = 'zugeordneter ALG-Bericht';

//			pr($childReports);
			if(count($childReports) > 0)
			{
				$output .= $view->Html->nestedList(
					array_map(
						function($elem, $view, $id) {
							return $view->Html->link($elem['name'], array('controller'=>'reportnumbers','action'=>'view',$elem['id'])).
							$view->Html->link(
								$view->Html->image('/img/remove.png', array('width'=>12)),
								array('action'=>'removechild', $elem['id'], $id, $id), array('class'=>'ajax plain', 'escape'=>false)
							);
						},
						$childReports,
						array_fill(0, count($childReports), $view),
						array_fill(0, count($childReports), $id)
					)
				);
			}

			//pr($unusedReports);
			if(count($unusedReports) > 0)
			{
				$output .= $view->Form->select(
					'setParentReport',
					$unusedReports,
					array(
						'empty'=>false,
						'onchange'=>'$(\'#btnSetParentReport\').attr(\'href\',\''.$view->Html->url(array('action'=>'setparent',$id)).'/\'+$(this).val()+\'/'.$id.'\');'
					)
				).
				' '.
				$view->Html->link(
					$view->Html->image('/img/apply.png', array('width'=>12)),
					array('action'=>'setparent', $id, $unused, $id),
					array('id'=>'btnSetParentReport', 'class'=>'ajax plain', 'escape'=>false)
				);
			}
		}

		return !empty($output) ? $view->Html->tag('fieldset', $view->Html->tag('legend',$title).$output, null) : null;
	}

    public function ConstructReportName($report, $format = null) {

            if($format == null){
                $format = Configure::read('Format');
            }

            $separator = Configure::read('Separator');
            switch ($format) {
                case 1:

                  $format = array('Report.identification./', 'Reportnumber.year.'.$separator,'Reportnumber.number');
//                    $format = array('Testingmethod.verfahren. ', 'Reportnumber.year.'.$separator,'Reportnumber.number');
                    break;

                case 2:
                    $format = array('Reportnumber.year.'.$separator,'Reportnumber.number');
                    break;

                 case 3:
                    $format = array('Testingmethod.verfahren. ', 'Reportnumber.year.'.$separator,'Reportnumber.number');
                    break;

                default:
                    $format = array('Topproject.projektname. ' ,'Report.name.'.$separator,'Testingmethod.verfahren. ', 'Reportnumber.number.'.$separator,'Reportnumber.year');
            }

	$report = array_map(function($elem) { return ((array)$elem); }, (array)$report);
		$year = $report['Reportnumber']['year'];
		if($year < 2000) $year += 2000;
                    $return = '';
                    foreach ($format as $key ) {
                        $var = explode('.', $key);
                         if(isset($report [$var[0]] [$var[1]])) {
                              $return = $return.$report [$var[0]] [$var[1]].(isset($var[2]) ? $var[2] : null);
                         }
                    }

		return $return;

	}

	public function WeldStatistic($ReportsData) {

		$Statistics = array();
		$Welds = array();

		foreach($ReportsData as $_key => $_ReportsData){
			foreach($_ReportsData as $__key => $__ReportsData){
				if($__ReportsData['key'] == 'dimension' && $__ReportsData['data'] != ''){
					$dimension = $__ReportsData['data'];
				}
				if($__ReportsData['key'] == 'description' && $__ReportsData['data'] != '' && isset($dimension)){
					$description = $__ReportsData['data'];
					$Welds[$description] = $dimension;
				}
			}
		}
		foreach($Welds as $_key => $_Welds){
			if(!isset($Statistics[$_Welds])){
				$Statistics[$_Welds] = 1;
			}
			else {
				$Statistics[$_Welds]++;
			}
		}

		return $Statistics;
	}

 public function ConstructReportNamePdf($report, $format = null,$rev = true) {
            if($format == null){
                $format = Configure::read('FormatPdf');
            }

            $separator = Configure::read('Separator');
            switch ($format) {
                case 1:

                  $format = array('Report.identification./', 'Reportnumber.year.'.$separator,'Reportnumber.number');
//                    $format = array('Testingmethod.verfahren. ', 'Reportnumber.year.'.$separator,'Reportnumber.number');
                    break;

                case 2:
                    $format = array('Reportnumber.year.'.$separator,'Reportnumber.number');
                    break;

                case 3:
                    $format = array('Topproject.projektname. ' ,'Report.name.'.$separator,'Testingmethod.verfahren. ', 'Reportnumber.year.'.$separator,'Reportnumber.number');
            }

	$report = array_map(function($elem) { return ((array)$elem); }, (array)$report);
		$year = $report['Reportnumber']['year'];
		if($year < 2000) $year += 2000;
                    $return = '';
                    foreach ($format as $key ) {
                        $var = explode('.', $key);
                         if(isset($report [$var[0]] [$var[1]])) {
                              $return = $return.$report [$var[0]] [$var[1]].(isset($var[2]) ? $var[2] : null);
                         }
                    }

                    if($rev == true && isset($report['Reportnumber'] ['revision']) && $report['Reportnumber'] ['revision'] > 0){
						$return .= $separator.'Rev.'.$report['Reportnumber'] ['revision'];
					}

		return $return;
	}

  public function SendPdfEmail($pdfData,$model, $emails,$checkfield= null,$checkorganisationmessage,$report,$signature,$savePath){
            if(!empty($emails)) {
                $emailbefore = '';

                foreach ($emails as $k_email => $v_email) {
                   if(empty($v_email) || $v_email== '-') continue;
                    $email = new CakeEmail();
                    $email->config('default');
                    Configure::check('EmailFrom')? $from = Configure::read('EmailFrom'):'';
                    $email->from($from);
                    $extra = '';
                    if(Configure::check('requiredsign')&& Configure::read('requiredsign') == true  ) {
			$tuevsigntrue = 0;
                        if(isset($signature) && !empty($signature)) {
                            foreach ($signature as $k_sign => $v_sign) {
                                $v_sign ['Sign']['signatory'] == Configure::read('requiredsignval')? $tuevsigntrue = 1:'';
                            }
                        }
			$pdfData[0]['data']->$model->$checkfield == 1 && $tuevsigntrue == 0? $extra = $checkorganisationmessage:$extra = '';
                    }


                    $email->to($v_email);

                    $email->subject(__('Report').' '.$report);
                    $mailview ='reportmail';
                    $email->template($mailview,'reportmail');
                    $email->emailFormat('text');
                    $email->viewVars(array(
							'extra' => $extra,

    						)
						);
                    $email->attachments( $savePath.'/'.'report_'.$report.'.pdf');

                    $v_email !== $emailbefore && !empty($v_email)? $email->send():'';
                    $emailbefore =  $v_email;

                }
            }
        }
        public function SendPdfEmailManually($pdfData,$model, $emails,$checkfield= null,$checkorganisationmessage,$report,$signature,$savePath,$text=null){
            $emailbefore = '';
            $extra = '';

            if(Configure::check('requiredsign')&& Configure::read('requiredsign') == true  ) {
                $tuevsigntrue = 0;
                if(isset($signature) && !empty($signature)) {
                    foreach ($signature as $k_sign => $v_sign) {

                        $v_sign ['Sign']['signatory'] == Configure::read('requiredsignval')? $tuevsigntrue = 1:'';

                    }
                }
                $pdfData[0]['data']->$model->$checkfield == 1 && $tuevsigntrue == 0? $extra = $checkorganisationmessage:$extra = '';

            }
         //   $extra.= PHP_EOL.$text;

            !empty($text)?$extra.= "\n". $text:'';
            foreach ($emails as $k_email => $v_email) {
               if(empty($v_email) || $v_email== '-') continue;
                $email = new CakeEmail();
                $email->config('default');
                Configure::check('EmailFrom')? $from = Configure::read('EmailFrom'):'';
                $email->from($from);
                $email->to($v_email);
                $email->subject(__('Report').' '.$report);
                $mailview ='reportmail';
                $email->template($mailview,'reportmail');
                $email->emailFormat('text');
                $email->viewVars(array(
                                                'extra' => $extra,

                                        )
                                        );
                $email->attachments( $savePath.'/'.'report_'.$report.'.pdf');

                $v_email !== $emailbefore && !empty($v_email)? $email->send():'';
                $emailbefore =  $v_email;

        }

    }

    public function PositioningTable($tcpdf,$define,$additionsdata,$ReportPdf){

		$Verfahren = 'Report' . $tcpdf->Verfahren . 'Specific';

		if(trim($tcpdf->xdata->$Verfahren->position_table) == 0) return;

        $tcpdf->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_3->VALUE =$additionsdata['title'];
        $tcpdf->AddPage($define['PDF_PAGE_ORIENTATION'], $define['PDF_PAGE_FORMAT']);
				$tcpdf->SetY($tcpdf->HaederEnd);
        $width = ($define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT']) / count($additionsdata['head']);
        $startx = $define['PDF_MARGIN_LEFT'];
        $starty = $tcpdf->GetY();
        $tcpdf->SetX($startx);

        $head = $additionsdata['head'];
        $body = $additionsdata['body'];

        foreach ($head as $headkey => $headvalue) {

		// quick and diry
		$tcpdf->SetFontSize(8);

        $datahead = strip_tags($headvalue);
        $tcpdf->SetFillColor ( 255,255,255);
        $tcpdf->MultiCell(
                                $width,
                                7,
                                $datahead,
                                // die Linien werden in die Mulitcell gezeichnet, da alle Spalten immeer gleich groß sind
                                1,//$tcpdf->$LineStyle,
                                'L',
                                1,
                                1,
                                $startx,
                                $starty,
                                true,
                                0,
                                false,
                                true,
                                7,
                                'T',
                                true
                            );
        $startx = $startx +$width;

        }
        $tcpdf->SetY($tcpdf->GetY());
        //pr($body); die();


        foreach ($body as $b_key => $b_value) {
             $starty =  $tcpdf->GetY();
             $startx = $define['PDF_MARGIN_LEFT'];

            foreach ($b_value as $bv_key => $bv_value) {

                if(is_array($bv_value) && count($bv_value)> 1) { $data = $bv_value[0];} else {$data = $bv_value;}
                $data = strip_tags($data);
                $tcpdf->SetFillColor ( 255,255,255);
                $tcpdf->MultiCell(
                                $width,
                                7,
                                $data,
                                // die Linien werden in die Mulitcell gezeichnet, da alle Spalten immeer gleich groß sind
                                1,//$tcpdf->$LineStyle,
                                'R',
                                1,
                                1,
                                $startx,
                                $starty,
                                true,
                                0,
                                false,
                                true,
                                7,
                                'T',
                                true
                            );

              $startx = $startx +$width;
            }
        }
    }

    public function PrintImages($tcpdf,$define){

		if($this->request->PDFImageCount == 0) return;

		if(count($tcpdf->ReportImages) > 0){

			if($this->request->PDFImageCount == 1) $this->__PrintSingleImages($tcpdf,$define);
			elseif($this->request->PDFImageCount > 1) $this->__PrintManyImages($tcpdf,$define);

		} else {
			return;
		}
	}

    protected function __PrintManyImages($tcpdf,$define){

		$tcpdf->AddPage();
		$tcpdf->SetY($tcpdf->HaederEnd);

		$LayoutWidth = $define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - 5;

		$DPI = 150;
		$Inch = 25.4;
		$MaxImageHeight = 0;

		$ImageArray = array();

		switch($this->request->PDFImageCount){
			case 1:
				$ImagePerLine = 1;
			break;

			case 2;
				$ImagePerLine = 4;
				if(count($tcpdf->ReportImages) == 1) $ImagePerLine = 1;
				elseif(count($tcpdf->ReportImages) == 2) $ImagePerLine = 2;
				else $ImagePerLine = ceil(count($tcpdf->ReportImages) / 2);
			break;

			case 4;
				$ImagePerLine = 4;
				if(count($tcpdf->ReportImages) == 1) $ImagePerLine = 1;
				if(count($tcpdf->ReportImages) == 2) $ImagePerLine = 2;
				if(count($tcpdf->ReportImages) > 2 && $ImagePerLine > count($tcpdf->ReportImages)) $ImagePerLine = ceil(count($tcpdf->ReportImages) / 2);
			break;
		}

		if($this->request->PDFImageCount == 2) $ImagePerLine = 2;

		$x_row = 0;
		$x_column = 0;
		$x_pos = $define['PDF_MARGIN_LEFT'];
		$x_margin = 2.5;
		$text_height = 10;

		foreach($tcpdf->ReportImages as $_key => $_ReportImages){

			if($x_column == $ImagePerLine){
				$x_column = 0;
				++$x_row;
				$x_pos = $define['PDF_MARGIN_LEFT'];
			}

			$imagepfad = Configure::read('report_folder') . $tcpdf->xdata->Reportnumber->topproject_id . DS . 'images' . DS . $tcpdf->xdata->Reportnumber->id;

			$img = $this->__GetImage($tcpdf,$_ReportImages,$imagepfad);
			if($img === false) continue;

			$output = $this->__GetImageInfo($tcpdf,$_ReportImages,$imagepfad);
			if(count($output) != 2) continue;

			$output['imageinfo'] = $this->__ScaleImage($output['imageinfo'],$DPI,$Inch);

			$Images[$_key]['image'] = $img;

			$Images[$_key]['imageinfo'] = $output['imageinfo'];

			$Images[$_key]['imageinfo'] = $this->__SetImageFitToLayout($Images[$_key]['imageinfo'],$LayoutWidth,$ImagePerLine);

			$Mime = explode('/',$Images[$_key]['imageinfo']['mime']);

			$Images[$_key]['imageinfo']['filetype'] = $Mime[1];

			$Images[$_key]['imageinfo']['x'] = $x_pos  + $x_margin;

			if($Images[$_key]['imageinfo'][1] > $MaxImageHeight) $MaxImageHeight = $Images[$_key]['imageinfo'][1];

			if(!empty($_ReportImages['Reportimage']['discription'])) $Images[$_key]['imageinfo']['description'] = $_ReportImages['Reportimage']['discription'];
			else $Images[$_key]['imageinfo']['description'] = ' ';

			$ImageArray[$x_row][$x_column] = $Images[$_key];

			$x_pos += $Images[$_key]['imageinfo'][0];

			++$x_column;

		}

		$y_pos = $tcpdf->GetY() + $x_margin;

		foreach($ImageArray as $_key => $_data){

			if($y_pos + $MaxImageHeight > $define['QM_PAGE_BREAKE']){
				$tcpdf->AddPage($define['PDF_PAGE_ORIENTATION'], $define['PDF_PAGE_FORMAT']);
				$tcpdf->SetY($tcpdf->HaederEnd);
				$y_pos = $tcpdf->GetY() + $x_margin;
			}

			foreach($_data as $_key => $__data){

				$tcpdf->Image(
				'@'.$__data['image'],
				$__data['imageinfo']['x'],
				$y_pos,
				$__data['imageinfo'][0], // Breite
				$__data['imageinfo'][1], // H�he
				$__data['imageinfo']['filetype'],
				'', // Link
				'L', // Ausrichtung
				false, // resize
				$DPI, //  dpi
				null, // 'L', // Ausrichtung auf Seite
				false, // Bild als Maske
				false, // Maske oder Textumfluss glaube ich
				'LTRB', // der Rand
				true, // passt das Bild an Dimensionen bleiben
				false, // wenn das Bild nicht angezeit werden soll
				false, // Bild an Seitengr��e anpassen
				false, // Alternativtext
				0 // altervatives Bild
				);

				$tcpdf->MultiCell(
						$__data['imageinfo'][0],
						$text_height,
						$__data['imageinfo']['description'],
						0,
						'L',
						false,
						0,
						$__data['imageinfo']['x'],
						$y_pos + $__data['imageinfo'][1],
						true,
						0,
						false,
						true,
						false,
						'T',
						$text_height
					);

			}

			$y_pos += $MaxImageHeight + $x_margin + $text_height;
		}

		$tcpdf->SetY($y_pos);
	}

    protected function __PrintSingleImages($tcpdf,$define){

		foreach($tcpdf->ReportImages as $_ReportImages){

			$tcpdf->AddPage();
			$tcpdf->SetY($tcpdf->HaederEnd);

			$imagepfad = Configure::read('report_folder') . $tcpdf->xdata->Reportnumber->topproject_id . DS . 'images' . DS . $tcpdf->xdata->Reportnumber->id;

			$img = $this->__GetImage($tcpdf,$_ReportImages,$imagepfad);
			if($img === false) continue;

			$output = $this->__GetImageInfo($tcpdf,$_ReportImages,$imagepfad);
			if(count($output) != 2) continue;

			$filetype = $output['filetype'];
			$imageinfo = $output['imageinfo'];

			$imageW = $define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - 5;
			$imageh = $define['QM_PAGE_BREAKE'] - $tcpdf->GetY() - 5;

			if(round($imageinfo[0] * $tcpdf->QM_INCH / $tcpdf->QM_DPI,2) < $imageW){
				$imageW = round($imageinfo[0] * $tcpdf->QM_INCH / $tcpdf->QM_DPI,2);
			}
			if(round($imageinfo[1] * $tcpdf->QM_INCH / $tcpdf->QM_DPI,2) < $imageh){
				$imageh = round($imageinfo[1] * $tcpdf->QM_INCH / $tcpdf->QM_DPI,2);
			}

			if($tcpdf->GetY()>= $define['QM_PAGE_BREAKE']){
				$tcpdf->AddPage($define['PDF_PAGE_ORIENTATION'], $define['PDF_PAGE_FORMAT']);
				$tcpdf->SetY($tcpdf->HaederEnd + 5);
			}

			$tcpdf->Image(
				'@'.$img,
				$define['PDF_MARGIN_LEFT'] + $define['QM_CELL_PADDING_L'] + 0.5,
				$tcpdf->GetY() + $define['QM_CELL_PADDING_T'] + 0.5,
				$imageW , // Breite
				$imageh, // H�he
				$filetype,
				'', // Link
				'M', // Ausrichtung
				false, // resize
				$tcpdf->QM_DPI, //  dpi
				null, // 'L', // Ausrichtung auf Seite
				false, // Bild als Maske
				false, // Maske oder Testumfluss glaube ich
				'LTRB', // der Rand
				true, // passt das Bild an Dimensionen bleiben
				false, // wenn das Bild nicht angezeit werden soll
				true, // Bild an Seitengr��e anpassen
				false, // Alternativtext
				0 // altervatives Bild
			);

			// Die H�he des Bildes holen um den Text darunter zusetzen
			$lastImageHeight = ($tcpdf->getImageRBY());

			if($_ReportImages['Reportimage']['discription'] == ''){
				$tcpdf->SetY($lastImageHeight);
				$tcpdf->Ln();
			}

			if($_ReportImages['Reportimage']['discription'] != ''){
				$startX = $define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'];

				$tcpdf->MultiCell(
						$startX,
						0,
						$_ReportImages['Reportimage']['discription'],
						0,
						'L',
						false,
						0,
						$define['PDF_MARGIN_LEFT'] + 2,
						$lastImageHeight,
						true,
						0,
						false,
						true,
						0,
						'T',
						true
					);
				$tcpdf->Ln();
				$tcpdf->SetY($tcpdf->GetY());
			}
		}
	}

    protected function __GetImage($tcpdf,$_ReportImages,$imagepfad){

		if(!((bool)$img = $this->Image->get($imagepfad. DS .$_ReportImages['Reportimage']['name'], false, array('quality' => 50,'width' => 2000)))) {
			$this->log('Bild '.$imagepfad. DS .$_ReportImages['Reportimage']['name'].' nicht gefunden.');
			return false;
		}

		return $img;
	}

    protected function __GetImageInfo($tcpdf,$_ReportImages,$imagepfad){

		$filetype = 'JPEG';

		$imageinfo = getimagesize($imagepfad. DS .$_ReportImages['Reportimage']['name']);

		if($imageinfo['mime'] == 'image/png')$filetype = 'PNG';
		if($imageinfo['mime'] == 'image/jpeg')$filetype = 'JPEG';

		return array('imageinfo' => $imageinfo,'filetype' => $filetype);
	}

    protected function __ScaleImage($ImageInfo,$DPI,$Inch){

		$ImageInfo[0] = ceil($ImageInfo[0] * $Inch / $DPI);
		$ImageInfo[1] = ceil($ImageInfo[1] * $Inch / $DPI);

		return $ImageInfo;

	}

    protected function __SetImageFitToLayout($ImageInfo,$LayoutWidth,$ImagePerLine){

		$ImageWidthPerLine = $LayoutWidth / $ImagePerLine;

		$ImageInfo[1] = round($ImageWidthPerLine * $ImageInfo[1] / $ImageInfo[0],2);
		$ImageInfo[0] = round($ImageWidthPerLine,2);

		return $ImageInfo;

	}


}
