<?php
class PdfComponent extends Component
{

    protected $_controller = null;

    public function initialize(Controller $controller)
    {
        $this->_controller = $controller;
    }

    public function ArraytoPdf($va1)
    {
        App::import('Vendor', 'TCPDF', array('file' => 'tcpdf/tcpdf.php'));
    }

    public function CreatePdfDataName($data = null, $Verfahren)
    {
        $output = $data['Topproject']['identification'] . '_' . $data['Reportnumber']['year'] . '_' . $data['Reportnumber']['number'];
        $GenerallyModel = 'Report' . $Verfahren . 'Generally';
        $reportNameEmail = $data['Reportnumber']['year'] . '_' . $data['Reportnumber']['number'] . '_' . $data['Report']['identification'];

        isset($data['$GenerallyModel']['factory_no']) && !empty($data['$GenerallyModel']['factory_no']) ? $reportNameEmail .= '_' . $data['$GenerallyModel']['factory_no'] : '';
        isset($data['$GenerallyModel']['technical_place']) && !empty($data['$GenerallyModel']['technical_place']) ? $reportNameEmail .= '_' . $data['$GenerallyModel']['technical_place'] : '';

        if (Configure::check('PdfNameFields') == true && !empty(Configure::read('PdfNameFields'))) {

            $entry = explode(',', Configure::read('PdfNameFields'));
            $output = '';
            $Separator = '_';
            
            if(Configure::check('Separator') == true && !empty(Configure::read('Separator'))) $Separator = Configure::read('Separator');

            foreach ($entry as $key => $value) {

                $arr_value = explode('.', $value);

                $model = $arr_value[0];
                $field = $arr_value[1];
    
                if ($arr_value[0] == 'Generally') {
                    $model = 'Report' . $Verfahren . 'Generally';
                }

                if ($arr_value[0] == 'Specific') {
                    $model = 'Report' . $Verfahren . 'Specific';
                }

                if ($arr_value[0] == 'Evaluation') {
                    $model = 'Report' . $Verfahren . 'Evaluation';
                }

                if (isset($data[$model][$field]) && !empty($data[$model][$field])) {
                    
                    if (!empty($output)) {
                        $output .= $Separator;
                    }

                    $data[$model][$field] = preg_replace('/[^a-z0-9 ]/i', '', $data[$model][$field]);

                    $output .= $data[$model][$field];
                }
            }
        }

        return $output;
    }

    public function EvaluationStatistik($Evaluation, $ReportEvaluation)
    {

        $x = 0;

        $EvaluationStatistik = array();

        // Nähte und Nahtbereiche zählen
        if (count($Evaluation) > 0) {

            $EvaluationStatistik['CountAreas'] = count($Evaluation);
            $WeldCount = 0;

            foreach ($Evaluation as $_key => $_Evaluation) {

                if ($_Evaluation[$ReportEvaluation]['film_dimension'] != '') {
                    $EvaluationStatistik['FilmDimension'][$_Evaluation[$ReportEvaluation]['film_dimension']][] = $_Evaluation[$ReportEvaluation]['film_dimension'];
                } else {
                    $EvaluationStatistik['FilmDimension']['keine Angaben'][] = '';
                }

                if ($WeldCount > 0 && $_Evaluation[$ReportEvaluation]['description'] != $LastWeld) {
                    $WeldCount = 0;
                }

                $WeldCount++;
                $EvaluationStatistik['CountWelds'][$_Evaluation[$ReportEvaluation]['description']] = $WeldCount;
                $LastWeld = $_Evaluation[$ReportEvaluation]['description'];
            }
        }

        return $EvaluationStatistik;
    }

    public function CreatePdf($var)
    {
        App::import('Vendor', 'tcpdf/xtcpdf');
        $tcpdf = new XTCPDF();
        $textfont = 'freesans';
        $tcpdf->SetAuthor("");
        $tcpdf->SetAutoPageBreak(false);
        $tcpdf->setHeaderFont(array($textfont, '', 10));

        $tcpdf->AddPage();

        header("Content-type: application/pdf");
        ob_end_clean();
        return $tcpdf->Output('mi_archivo.pdf', 'D'); //D or I
    }

    public function FilmConsumption($arrayEvaluation, $ReportTableNames)
    {

        $output = null;
        $EvaluationStatistik = $this->_controller->Pdf->EvaluationStatistik($this->_controller->Data->WeldSortingDiscription($arrayEvaluation, $ReportTableNames['Evaluation'], 'description'), $ReportTableNames['Evaluation']);

        foreach ($EvaluationStatistik['FilmDimension'] as $_key => $_EvaluationStatistik) {
            $output .= $_key . ": " . count($_EvaluationStatistik) . ", ";
        }

        return $output;
    }

    public function WeldCount($arrayEvaluation, $ReportTableNames)
    {

        $output = null;
        $EvaluationStatistik = $this->_controller->Pdf->EvaluationStatistik($this->_controller->Data->WeldSortingDiscription($arrayEvaluation, $ReportTableNames['Evaluation'], 'description'), $ReportTableNames['Evaluation']);

        if (count($EvaluationStatistik['CountWelds']) == 0) {
            $output .= count($EvaluationStatistik['CountWelds']) . " Nähte, ";
        } elseif (count($EvaluationStatistik['CountWelds']) == 1) {
            $output .= count($EvaluationStatistik['CountWelds']) . " Naht, ";
        } else {
            $output .= count($EvaluationStatistik['CountWelds']) . " Nähte, ";
        }

        if ($EvaluationStatistik['CountAreas'] == 0) {
            $output .= ($EvaluationStatistik['CountAreas']) . " Nahtbereiche";
        } elseif ($EvaluationStatistik['CountAreas'] == 1) {
            $output .= ($EvaluationStatistik['CountAreas']) . " Nahtbereich";
        } else {
            $output .= ($EvaluationStatistik['CountAreas']) . " Nahtbereiche";
        }

        return $output;
    }

    public function ReplaceData($data, $settings, $arrayEvaluation, $ReportTableNames)
    {

        foreach ($ReportTableNames as $_key => $_ReportTableNames) {
            if ($_key == 'Generally' || $_key == 'Specific' || $_key == 'Evaluation') {
                foreach ($settings->$_ReportTableNames as $__key => $__ReportTableNames) {
                    foreach ($__ReportTableNames as $___key => $___ReportTableNames) {
                        if ($___ReportTableNames->pdf->data_array != '') {

                            $thisKey = trim($___ReportTableNames->key);
                            $thisFunction = trim($___ReportTableNames->pdf->data_array->funktion);

                            $data->$_ReportTableNames->$thisKey = $this->_controller->Pdf->$thisFunction($arrayEvaluation, $ReportTableNames);
//                            $data[$_ReportTableNames][$thisKey] = $this->_controller->Pdf->$thisFunction($arrayEvaluation,$ReportTableNames);

                        }
                    }
                }
            }
        }
    }

    public function InsertDataFromChildReports($reports, $data)
    {
        foreach ($reports as $childReport) {
            if ($childReport['Testingmethod']['value'] == 'alg') {
                continue;
            }

            foreach (array('Report' . ucfirst($childReport['Testingmethod']['value']) . 'Generally', 'Report' . ucfirst($childReport['Testingmethod']['value']) . 'Specific') as $childPart) {
                $this->_controller->loadModel($childPart);
                $algPart = preg_replace('/' . ucfirst($childReport['Testingmethod']['value']) . '/', 'Alg', $childPart);
                foreach ($this->_controller->$childPart->find('first', array('conditions' => array($childPart . '.reportnumber_id' => $childReport['Reportnumber']['id']))) as $childPart) {
                    //pr($data->$algPart);
                    foreach ($childPart as $childKey => $childValue) {
                        if ($data->xpath($algPart . '/' . $childKey) && empty($data->$algPart->$childKey)) {
                            $data->$algPart->$childKey = $childValue;
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function GetStampPaths($reportnumber,$xml)
    {

        $reportnumberID = $reportnumber['Reportnumber']['id'];
        $model = 'Report' . ucfirst($reportnumber['Testingmethod']['value']) . 'Generally';

 //       if(empty($xml['settings']->{$model}->supervision->pdf->stamp) || empty($xml['settings']->{$model}->examiner->pdf->stamp)) return null;

        $this->_controller->loadModel('Reportnumber');
        $this->_controller->loadModel('ExaminersStamp');
        $examiner_id = $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $reportnumberID), 'fields' => array('examiner_id', 'supervisor_id')));

        $stamps = array();
        $stamps = $this->_GetStampPathsExaminer($stamps,$examiner_id,$model,$xml);
        $stamps = $this->_GetStampPathsSupervisor($stamps,$examiner_id,$model,$xml);
        $stamps = $this->_ShowStampOnlyAfterSignature($stamps,$examiner_id,$model,$xml);

        return $stamps;
    }

    protected function _ShowStampOnlyAfterSignature($stamps,$examiner_id,$model,$xml){

        if(Configure::check('ShowStampOnlyAfterSignature') === false) return $stamps;
        if(Configure::read('ShowStampOnlyAfterSignature') != true) return $stamps;

        // geleistete Unterschriften holen
        $Check = $this->_controller->Data->SignReport();

        foreach($Check['src'] as $key => $value){

            // existiert für diese Unterschritt kein Stempel
            // passiert nix
            if(!isset($stamps[$key])) continue;

            if(!isset($value['Data'])){
                // ist die Unterschrift noch nicht geleistet
                // wird der vorhandene Stempel gelöscht, so das er nicht auf dem Pdf erscheint
                unset($stamps[$key]);

            } elseif(isset($value['Data'])){
                // wenn Unterschrift und Stempel vorhanden sind 
                // bleibt alles wie es ist
            }
        }

        return $stamps;

    }

    protected function _GetStampPathsSupervisor($stamps,$examiner_id,$model,$xml){

        if(empty($xml['settings']->{$model}->supervision->pdf->stamp)) return $stamps;   
        if(!isset($examiner_id['Reportnumber']['supervisor_id'])) return $stamps;
        if(empty($examiner_id['Reportnumber']['supervisor_id'])) return $stamps;
        if($examiner_id['Reportnumber']['supervisor_id'] == 0) return $stamps;

        $stamp = $this->_controller->ExaminersStamp->find('first', array('conditions' => array('examiner_id' => $examiner_id['Reportnumber']['supervisor_id'])));
        
        if(count($stamp) == 0) return $stamps;

        $stamp_file_name = $stamp['ExaminersStamp']['file_name'];
        $stamp_path = Configure::read('examiner_folder') . 'stamps' . DS . $examiner_id['Reportnumber']['supervisor_id'] . DS;
        $stamp_path = $stamp_path . $stamp_file_name;

        if(empty($stamp_path)) return $stamps;
        if(!file_exists($stamp_path)) return $stamps;

        $data['supervisor_stamp_path'] = $stamp_path;
        $data['supervisor_stamp_offset_x'] = 0;
        $data['supervisor_stamp_offset_y'] = 0;
        $data['supervisor_stamp_height'] = 0;
        $data['supervisor_stamp_width'] = 0;

        if(!empty($xml['settings']->{$model}->supervision->pdf->stamp->offset_x)) $data['supervisor_stamp_offset_x'] = trim($xml['settings']->{$model}->supervision->pdf->stamp->offset_x);
        if(!empty($xml['settings']->{$model}->supervision->pdf->stamp->offset_y)) $data['supervisor_stamp_offset_y'] = trim($xml['settings']->{$model}->supervision->pdf->stamp->offset_y);
        if(!empty($xml['settings']->{$model}->supervision->pdf->stamp->height)) $data['supervisor_stamp_height'] = trim($xml['settings']->{$model}->supervision->pdf->stamp->height);
        if(!empty($xml['settings']->{$model}->supervision->pdf->stamp->width)) $data['supervisor_stamp_width'] = trim($xml['settings']->{$model}->supervision->pdf->stamp->width);

        $stamps['supervision'] = $data;

        return $stamps;
    }

    protected function _GetStampPathsExaminer($stamps,$examiner_id,$model,$xml){

        if(empty($xml['settings']->{$model}->examiner->pdf->stamp)) return $stamps;   
        if(!isset($examiner_id['Reportnumber']['examiner_id'])) return $stamps;
        if(empty($examiner_id['Reportnumber']['examiner_id'])) return $stamps;
        if($examiner_id['Reportnumber']['examiner_id'] == 0) return $stamps;

        $stamp = $this->_controller->ExaminersStamp->find('first', array('conditions' => array('examiner_id' => $examiner_id['Reportnumber']['examiner_id'])));

        if(count($stamp) == 0) return $stamps;

        $stamp_file_name = $stamp['ExaminersStamp']['file_name'];
        $stamp_path = Configure::read('examiner_folder') . 'stamps' . DS . $examiner_id['Reportnumber']['examiner_id'] . DS;
        $stamp_path = $stamp_path . $stamp_file_name;

        if(empty($stamp_path)) return $stamps;
        if(!file_exists($stamp_path)) return $stamps;

        $data['examiner_stamp_path'] = $stamp_path;
        $data['examiner_stamp_offset_x'] = 0;
        $data['examiner_stamp_offset_y'] = 0;
        $data['examiner_stamp_height'] = 0;
        $data['examiner_stamp_width'] = 0;

        if(!empty($xml['settings']->{$model}->examiner->pdf->stamp->offset_x)) $data['examiner_stamp_offset_x'] = trim($xml['settings']->{$model}->examiner->pdf->stamp->offset_x);
        if(!empty($xml['settings']->{$model}->examiner->pdf->stamp->offset_y)) $data['examiner_stamp_offset_y'] = trim($xml['settings']->{$model}->examiner->pdf->stamp->offset_y);
        if(!empty($xml['settings']->{$model}->examiner->pdf->stamp->height)) $data['examiner_stamp_height'] = trim($xml['settings']->{$model}->examiner->pdf->stamp->height);
        if(!empty($xml['settings']->{$model}->examiner->pdf->stamp->width)) $data['examiner_stamp_width'] = trim($xml['settings']->{$model}->examiner->pdf->stamp->width);

        $stamps['examiner'] = $data;

        return $stamps;
    }


    public function FetchLinkedReports($id)
    {
        return $this->_controller->Reportnumber->find('list', array(
            'conditions' => array(
                'Reportnumber.parent_id' => $id,
            ),
            'order' => array(
                'Reportnumber.subindex' => 'asc',
                'Reportnumber.id' => 'asc',
            ),
        ));
    }

    public function MakeSignatory($ReportGenerally, $report, $settingsData)
    {

        if (!Configure::check('WriteSignatory')) {
            return array();
        }

        if (!Configure::check('SignatoryPdfOutput')) {
            return array();
        }

        if (Configure::read('WriteSignatory') == false) {
            return array();
        }

        if (Configure::read('SignatoryPdfOutput') == false) {
            return array();
        }

        $this->_controller->loadModel('Sign');
        $sign = $this->_controller->Sign->find('all', array('conditions' => array('Sign.reportnumber_id' => $report['Reportnumber']['id'])));
        $xmp = null;
        $daten = array();
        $dpi = 300;
        $scaling = 0.6;

        if (count($sign) > 0) {

            foreach ($sign as $_key => $_sign) {

                $output = $this->_controller->Image->setSignImage($report, $_sign, $_sign['Sign']['signatory']);

                $sign[$_key]['Sign']['sign_output'] = $output;

                if ($sign[$_key]['Sign']['signatory'] == 1) {

                    $sign[$_key] = $this->_controller->Pdf->MakeSignatoryInfos($ReportGenerally, $report, $sign[$_key], $settingsData, 'examiner', $dpi, $scaling);
                    $xmp .= $this->_controller->Pdf->MakeXmpInfos($output, 'examiner', $_key);
                }
                if ($sign[$_key]['Sign']['signatory'] == 2) {

                    $sign[$_key] = $this->_controller->Pdf->MakeSignatoryInfos($ReportGenerally, $report, $sign[$_key], $settingsData, 'supervision', $dpi, $scaling);
                    $xmp .= $this->_controller->Pdf->MakeXmpInfos($output, 'supervision', $_key);

                }

                if ($sign[$_key]['Sign']['signatory'] == 3) {

                    $sign[$_key] = $this->_controller->Pdf->MakeSignatoryInfos($ReportGenerally, $report, $sign[$_key], $settingsData, 'supervisor_company', $dpi, $scaling);
                    $xmp .= $this->_controller->Pdf->MakeXmpInfos($output, 'supervisor_company', $_key);

                }

                if ($sign[$_key]['Sign']['signatory'] == 4) {

                    $sign[$_key] = $this->_controller->Pdf->MakeSignatoryInfos($ReportGenerally, $report, $sign[$_key], $settingsData, 'third_part', $dpi, $scaling);
                    $xmp .= $this->_controller->Pdf->MakeXmpInfos($output, 'third_part', $_key);

                }

            }

            $xmp_first = "\t\t" . '<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
//            $xmp_first = "\t\t".'<rdf:Description rdf:about="">'."\n";
            $xmp_last = "\t\t" . '</rdf:Description>' . "\n";

            return array('xmp' => $xmp_first . $xmp . $xmp_last, 'signature' => $sign);

        } else {
            return array();
        }
    }

    public function MakeXmpInfos($output, $type, $_key)
    {

        $xmp = null;

        $user_id_chiper = bin2hex(Security::cipher($output['data']['SingsHistory']['user_id'], Configure::read('SignatoryHash')));
        $report_id_chiper = bin2hex(Security::cipher($output['data']['SingsHistory']['reportnumber_id'], Configure::read('SignatoryHash')));
        $signs_id_chiper = bin2hex(Security::cipher($output['data']['SingsHistory']['signs_id'], Configure::read('SignatoryHash')));

        $daten[$_key][0] = array('key' => 'tEXt', 'keyword' => 'Author', 'content' => $user_id_chiper);
        $daten[$_key][1] = array('key' => 'tEXt', 'keyword' => 'Title', 'content' => $report_id_chiper);
        $daten[$_key][2] = array('key' => 'tEXt', 'keyword' => 'Disclaimer', 'content' => $signs_id_chiper);
        $daten[$_key][3] = array('key' => 'tEXt', 'keyword' => 'Description', 'content' => $output['data']['SingsHistory']['rand']);
        $daten[$_key][4] = array('key' => 'tEXt', 'keyword' => 'Creation Time', 'content' => $output['data']['SingsHistory']['created']);
        $daten[$_key][5] = array('key' => 'tEXt', 'keyword' => 'Location', 'content' => $output['data']['SingsHistory']['geoplugin_latitude'] . ' ' . $output['data']['SingsHistory']['geoplugin_longitude']);
        $daten[$_key][6] = array('key' => 'tEXt', 'keyword' => 'Country', 'content' => $output['data']['SingsHistory']['continent_code']);
        $daten[$_key][7] = array('key' => 'tEXt', 'keyword' => 'Ip', 'content' => $output['data']['SingsHistory']['ip_adress']);
        $daten[$_key][8] = array('key' => 'tEXt', 'keyword' => 'Signature', 'content' => $output['data']['SingsHistory']['signature']);
        $daten[$_key][9] = array('key' => 'tEXt', 'keyword' => 'Source', 'content' => $_SERVER['HTTP_HOST'] . $this->_controller->base);
//pr($output['data']['SingsHistory']['signature']);

/*
$xmp .= "\t\t\t".'<rdf:' . Inflector::camelize($type) . '>'."\n";
$xmp .= '666';
$xmp .= "\t\t\t".'</rdf:' . Inflector::camelize($type) . '>'."\n";
 */

        $xmp .= "\t\t\t" . '<dc:' . Inflector::camelize($type) . '>' . "\n";
        $xmp .= "\t\t\t\t" . '<rdf:Seq>' . "\n";

        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][0]['content'] . '</rdf:li>' . "\n";
        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][1]['content'] . '</rdf:li>' . "\n";
        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][2]['content'] . '</rdf:li>' . "\n";
        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][3]['content'] . '</rdf:li>' . "\n";
        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][4]['content'] . '</rdf:li>' . "\n";
        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][5]['content'] . '</rdf:li>' . "\n";
        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][6]['content'] . '</rdf:li>' . "\n";
        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][7]['content'] . '</rdf:li>' . "\n";
        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][8]['content'] . '</rdf:li>' . "\n";
        $xmp .= "\t\t\t\t\t" . '<rdf:li xml:lang="x-default">' . $daten[$_key][9]['content'] . '</rdf:li>' . "\n";

        $xmp .= "\t\t\t\t" . '</rdf:Seq>' . "\n";
        $xmp .= "\t\t\t" . '</dc:' . Inflector::camelize($type) . '>' . "\n";

        return $xmp;
    }

    public function MakeSignatoryInfos($Model, $report, $sign, $settingsData, $typ, $dpi, $scaling)
    {

        if (empty($settingsData['settings']->$Model->$typ->pdf->signatory->show)) {
            die();
           return $sign;
        }
        if (trim($settingsData['settings']->$Model->$typ->pdf->signatory->show) != 1) {
            return $sign;
        }

        $sign['Sign']['signatory_name'] = $typ;
        $sign['Sign']['dpi'] = $dpi;

        $sign['Sign']['width'] = $sign['Sign']['width'] * floatval($scaling);
        $sign['Sign']['height'] = $sign['Sign']['height'] * floatval($scaling);

        $_width = round($sign['Sign']['width'] * 25.4 / $dpi, 2);

        if (trim($settingsData['settings']->$Model->$typ->pdf->signatory->max_width) < $_width && trim($settingsData['settings']->$Model->$typ->pdf->signatory->max_width) > 0) {
            $_width = trim($settingsData['settings']->$Model->$typ->pdf->signatory->max_width);
        }

        $sign['Sign']['height'] = round($sign['Sign']['height'] * $_width / $sign['Sign']['width'], 2);
        $sign['Sign']['width'] = $_width;

        $field_width = explode(' ', trim($settingsData['settings']->$Model->$typ->pdf->positioning->width));
        $sign['Sign']['signatory_x'] = trim($settingsData['settings']->$Model->$typ->pdf->signatory->x);
        $sign['Sign']['signatory_y'] = trim($settingsData['settings']->$Model->$typ->pdf->signatory->y);
        $sign['Sign']['offset_x'] = trim($settingsData['settings']->$Model->$typ->pdf->signatory->offset_x);
        $sign['Sign']['offset_y'] = trim($settingsData['settings']->$Model->$typ->pdf->signatory->offset_y);

        return $sign;
    }

    public function ConstructReportName($report, $format = null)
    {
        if ($format == null) {
            $format = Configure::read('Format');
        }

        $separator = Configure::read('Separator');
        switch ($format) {
            case 1:

                $format = array('Report.identification./', 'Reportnumber.year.' . $separator, 'Reportnumber.number');
//                    $format = array('Testingmethod.verfahren. ', 'Reportnumber.year.'.$separator,'Reportnumber.number');
                break;

            case 2:
                $format = array('Reportnumber.year.' . $separator, 'Reportnumber.number');
                break;

            default:
                $format = array('Topproject.projektname. ', 'Report.name.' . $separator, 'Testingmethod.verfahren. ', 'Reportnumber.number.' . $separator, 'Reportnumber.year');
        }

        $report = array_map(function ($elem) {return ((array)$elem);}, (array)$report);
        $year = $report['Reportnumber']['year'];
        if ($year < 2000) {
            $year += 2000;
        }

        $return = '';
        foreach ($format as $key) {
            $var = explode('.', $key);
            if (isset($report[$var[0]][$var[1]])) {
                $return = $return . $report[$var[0]][$var[1]] . (isset($var[2]) ? $var[2] : null);
            }
        }
        return $return;
    }

    public function RateOfFilmConsumptionCsv($Data)
    {
        $testingmethods = array('ReportRtEvaluation', 'ReportRtnunEvaluation');
        $testingmethod = ucfirst($Data['Testingmethod']['value']);
        $films = array();
        $countfilms = 0;

        foreach ($testingmethods as $key => $value) {

            if (!isset($Data[$value])) {
                continue;
            }

            foreach ($Data[$value] as $_key => $_value) {

                if ($_value[$value]['deleted'] == 1) {
                    continue;
                }

                if (empty($_value[$value]['film_dimension'])) {
                    continue;
                }

                $films[trim($_value[$value]['film_dimension'])][] = trim($_value[$value]['description']);

            }
        }

        foreach ($films as $_films => $value) {
            $countfilms .= $_films . ' ' . count($value) . ' ' . __('Films', true) . '  ';
            //$countfilms = $countfilms + count($value);

        }
        // pr($countfilms);
        return $countfilms;

    }

/* funktioniert nicht
public function RateOfFilmConsumptionCsv($head,$Csv,$Data,$FilmeDimension) {

$testingmethods = array('ReportRtEvaluation','ReportRtnunEvaluation');
$testingmethod = ucfirst($Data['Testingmethod']['value']);
$films = array();
$countfilms = 0;

foreach ($testingmethods as $key => $value) {

if(!isset($Data[$value])) continue;

foreach ($Data[$value] as $_key => $_value) {

if($_value[$value]['deleted'] == 1) continue;
if(empty($_value[$value]['film_dimension'])) continue;

$films[trim($_value[$value]['film_dimension'])][] = trim($_value[$value]['description']);

}
}

$head_flip = array_flip($head);
$FilmeDimensionId = array();

foreach ($FilmeDimension as $key => $value) {

$FilmeDimensionId[$head_flip[$key]] = $value;

if(isset($films[$value])) $Csv[$head_flip[$key]] = count($films[$value]);
else $Csv[$head_flip[$key]] = 0;

}

return $Csv;

}*/

    public function WeldCountWithDimension($arrayEvaluation, $ReportTableNames)
    {

        $output = null;
        $output = $this->_controller->Pdf->EvaluationStatistikDimension($this->_controller->Data->WeldSortingDiscription($arrayEvaluation, $ReportTableNames['Evaluation'], 'description'), $ReportTableNames['Evaluation']);

        return $output;
    }

    public function EvaluationStatistikDimension($Evaluation, $ReportEvaluation)
    {

        $output = '';

        // Nähte und Nahtbereiche zählen
        if (count($Evaluation) > 0) {

            $WeldCount = array();
            $dimensions = Hash::extract($Evaluation, '{n}.{s}.dimension');
            $dimensions = array_unique($dimensions);

            foreach ($dimensions as $_dimensionkey => $_dimension) {
                $WeldCount[$_dimension] = 0;
                $LastWeld = '';
                foreach ($Evaluation as $_key => $_Evaluation) {

                    if ($_Evaluation[$ReportEvaluation]['dimension'] == $_dimension && $_Evaluation[$ReportEvaluation]['description'] != $LastWeld) {

                        $WeldCount[$_dimension] = $WeldCount[$_dimension] + 1;
                    }
                    $LastWeld = $_Evaluation[$ReportEvaluation]['description'];
                }

            }

            foreach ($WeldCount as $_wckey => $_wcvalue) {
                $_wckey == '' ? $_wckey = 'keine Angabe' : '';
                $_wcvalue > 1 ? $text = 'welds' : $text = 'weld';
                if (count($WeldCount) > 0) {
                    $output .= $_wckey . ' (' . $_wcvalue . ' ' . __($text, true) . ') ';

                }
            }
            return $output;
        }

    }

    public function ErrorDescriptionOutput($Evaluation, $verfahren, $data)
    {
        //    echo '<pre>'; var_dump($data); echo '</pre>';var_dump($verfahren); die();

        $descriptions = Hash::extract($Evaluation, '{n}.{s}.error');
        $errornumbersarray = array();

        foreach ($descriptions as $dskey => $dsvalue) {
            $arraydesc = array();
            $arraydesc = explode(",", $dsvalue);

            foreach ($arraydesc as $adkey => $advalue) {

                !empty($advalue) ? $errornumbersarray[] = trim($advalue) : '';

                // code...
            }

        }
        $errorsoutput = '';
        $this->_controller->loadModel('ErrorNumber');
        array_unique($errornumbersarray);
        $decarray = $this->_controller->ErrorNumber->find('all', array('conditions' => array('ErrorNumber.number' => $errornumbersarray)));
        $count = count($decarray);
        $i = 1;

        foreach ($decarray as $dskey => $davalue) {
            $errorsoutput .= $davalue['ErrorNumber']['text'];
            if (isset($davalue['ErrorNumber']['text_eng'])) {
                $errorsoutput .= '/' . $davalue['ErrorNumber']['text_eng'];
            }

            if ($count > $i) {
                $errorsoutput .= ', ';
            }

            // code...
            $i++;
        }
        $Generally = $verfahren . 'Generally';
        $data->$Generally->errordescriptions = $errorsoutput;

        return $data;
    }

    public function MakeSignatoryChilds($ReportGenerally, $reports, $settingsData)
    {

        if (!Configure::check('WriteSignatory')) {
            return array();
        }

        if (!Configure::check('SignatoryPdfOutput')) {
            return array();
        }

        if (Configure::read('WriteSignatory') == false) {
            return array();
        }

        if (Configure::read('SignatoryPdfOutput') == false) {
            return array();
        }

        foreach ($reports as $rkey => $report) {
            // code...

            $this->_controller->loadModel('Sign');
            $sign = $this->_controller->Sign->find('all', array('conditions' => array('Sign.reportnumber_id' => $report['Reportnumber']['id'])));
            $xmp = null;
            $daten = array();
            $dpi = 300;
            $scaling = 0.6;

            if (count($sign) > 0) {

                foreach ($sign as $_key => $_sign) {

                    $output = $this->_controller->Image->setSignImage($report, $_sign, $_sign['Sign']['signatory']);

                    $sign[$_key]['Sign']['sign_output'] = $output;

                    if ($sign[$_key]['Sign']['signatory'] == 1) {

                        $sign[$_key] = $this->_controller->Pdf->MakeSignatoryInfos($ReportGenerally, $report, $sign[$_key], $settingsData, 'examiner', $dpi, $scaling);
                        $xmp .= $this->_controller->Pdf->MakeXmpInfos($output, 'examiner', $_key);
                    }
                    if ($sign[$_key]['Sign']['signatory'] == 2) {

                        $sign[$_key] = $this->_controller->Pdf->MakeSignatoryInfos($ReportGenerally, $report, $sign[$_key], $settingsData, 'supervision', $dpi, $scaling);
                        $xmp .= $this->_controller->Pdf->MakeXmpInfos($output, 'supervision', $_key);

                    }

                    if ($sign[$_key]['Sign']['signatory'] == 3) {

                        $sign[$_key] = $this->_controller->Pdf->MakeSignatoryInfos($ReportGenerally, $report, $sign[$_key], $settingsData, 'supervisor_company', $dpi, $scaling);
                        $xmp .= $this->_controller->Pdf->MakeXmpInfos($output, 'supervisor_company', $_key);

                    }

                    if ($sign[$_key]['Sign']['signatory'] == 4) {

                        $sign[$_key] = $this->_controller->Pdf->MakeSignatoryInfos($ReportGenerally, $report, $sign[$_key], $settingsData, 'third_part', $dpi, $scaling);
                        $xmp .= $this->_controller->Pdf->MakeXmpInfos($output, 'third_part', $_key);

                    }

                }
                $xmp_first = "\t\t" . '<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
//            $xmp_first = "\t\t".'<rdf:Description rdf:about="">'."\n";
                $xmp_last = "\t\t" . '</rdf:Description>' . "\n";

                return array('xmp' => $xmp_first . $xmp . $xmp_last, 'signature' => $sign);

            } else {
                return array();
            }
        }
    }

}
