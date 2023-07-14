<?php
App::import('Vendor', 'tcpdf/tcpdf');

class XTCPDF extends TCPDF
{
//    var $xheadertext  = 'PDF creado using CakePHP y TCPDF';
    public $xheadercolor = array(255, 255, 255);
//    var $xfootertext  = 'Copyright © %d XXXXXXXXXXX. All rights reserved.';
    public $xdata = array();
    public $xchanges = array();
    public $xsettings = array();
    public $Verfahren = null;
    public $Examiner_Stamp_path = null;
    public $dataArray = array();
    public $xfooterfon;
    public $xfooterfontsize = 8;
    public $reportDeleted = false;
    public $defs = null;
    public $reportnumber;
    public $qr = null;
    public $bodyStart = array();
    public $forcePrintHeaders = false;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        TCPDF_FONTS::addTTFfont(APP . 'Config' . DS . 'Fonts' . DS . 'calibri.ttf', 'TrueTypeUnicode', '', 32);
        TCPDF_FONTS::addTTFfont(APP . 'Config' . DS . 'Fonts' . DS . 'calibrib.ttf', 'TrueTypeUnicode', '', 32);
        $this->AddFont('calibri', 'B', 'calibrib.php');

        TCPDF_FONTS::addTTFfont(APP . 'Config' . DS . 'Fonts' . DS . 'calibrii.ttf', 'TrueTypeUnicode', '', 96);
        $this->AddFont('calibri', 'I', 'calibrii.php');

        TCPDF_FONTS::addTTFfont(APP . 'Config' . DS . 'Fonts' . DS . 'calibriz.ttf', 'TrueTypeUnicode', '', 96);
        $this->AddFont('calibri', 'BI', 'calibriz.php');

    }

    public function __call($name, $arguments)
    {
        $method = null;
        $_margins = $this->getMargins();
        if (preg_match('/^get(left|right|top|bottom|header|footer)(padding|margin)$/', strtolower($name), $method)) {
            if (strtolower($method[2]) == 'margin') {
                return $_margins[strtolower($method[1])];
            } elseif ($method[2] == 'padding') {
                if (array_search(strtolower($method[1]), array('left', 'right', 'top', 'bottom')) !== false) {
                    return $_margins['padding_' . strtolower($method[1])];
                }
            }
        }

        return parent::_call($name, $arguments);
    }

    public function getFontFormatting($item, $fonts = array())
    {
        $fonts = array_merge(array(
            'title' => array(
                'n' => 'calibri',
                'b' => 'calibrib',
                'i' => 'calibrii',
                'bi' => 'calibriz',
            ),
            'data' => array(
                'n' => 'calibri',
                'b' => 'calibrib',
                'i' => 'calibrii',
                'bi' => 'calibriz',
            ),
        ), $fonts);

        $style = array('', '');
        $font = array('', '');
        if (isset($item['formatting']['bold']) && !empty($item['formatting']['bold'])) {
            $bold = explode(' ', $item['formatting']['bold']);
            if (count($bold) == 1) {
                $bold[1] = $bold[0];
            }

            $style[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'B' : '';
            $font[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'b' : '';
            $style[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'B' : '';
            $font[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'b' : '';
        }

        if (isset($item['formatting']['italic']) && !empty($item['formatting']['italic'])) {
            $bold = explode(' ', $item['formatting']['italic']);
            if (count($bold) == 1) {
                $bold[1] = $bold[0];
            }

            $style[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'I' : '';
            $font[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'i' : '';
            $style[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'I' : '';
            $font[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'i' : '';
        }

        if (isset($item['formatting']['underline']) && !empty($item['formatting']['underline'])) {
            $bold = explode(' ', $item['formatting']['underline']);
            if (count($bold) == 1) {
                $bold[1] = $bold[0];
            }

            $style[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'U' : '';
            $style[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'U' : '';
        }

        $fonts = array_values($fonts);

        foreach ($font as $num => $fontstyle) {
            if (empty($fontstyle)) {
                $fontstyle = 'n';
            }

            $font[$num] = $fonts[$num][$fontstyle];
        }

        return array($style, $font);
    }

    public function writeSettings($defs)
    {
        $this->defs = $defs;
        $this->qr = null;
        $this->xfooterfon = $defs['PDF_FONT_NAME_MAIN'];
    }

    public function insertPageNumbers()
    {
    }

    public function insertSearchBarcode()
    {

        if (Configure::check('BarcodeSearchScanner') == false) {
            return false;
        }

        if (Configure::read('BarcodeSearchScanner') == false) {
            return false;
        }

        if (empty($this->SecID)) {
            return false;
        }

        $style = array(
            'border' => false,
            'hpadding' => '0',
            'vpadding' => '0',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4,
        );

        $this->StartTransform();
        $this->Rotate(90, $this->defs['QM_CELL_LAYOUT_CLEAR'] + 2, $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM']);
        $this->write1DBarcode($this->SecID, 'C39', $this->defs['QM_CELL_LAYOUT_CLEAR'] + 2, $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], 85, 5, 0.4, $style, 'T');
        $this->StopTransform();
    }

    public function Header()
    {

        $this->reportDeleted = $this->xdata->Reportnumber->delete == 'true' || $this->xdata->Reportnumber->delete == '1';
        list($r, $b, $g) = $this->xheadercolor;
        // Daten vorbereiten
        // Daten vorbereiten
        $Verfahren = $this->Verfahren;

        $ReportPDfSettings = 'Report' . $Verfahren . 'Pdf';
        $Report[1] = 'Report' . $Verfahren . 'Generally';
        $Report[2] = 'Report' . $Verfahren . 'Specific';
        $ReportEvaluation = 'Report' . $Verfahren . 'Evaluation';
        $ReportPdf = 'Report' . $Verfahren . 'Pdf';
        $ReportArchiv = 'Report' . $Verfahren . 'Archiv';
        $width = array();
        $imageArray = array();
        $imageArrayEvaluation = array();
        $lastImageHeight = 0;
        $StandartWidthLabel = 25;
        $StandartWidthData = 50;
        $cellheight = 4.5;
        $SignatorySettings = array();

        // Die Werte aus den Daten und den Setting weden zusammengeführt
        // in einem neuen Array, es wird die Höhe der Zellen berechnet
        foreach ($Report as $Reports) {
            foreach ($this->xsettings->$Reports as $_Report) {
                foreach ($_Report as $__Report) {

                    if (Configure::read('WriteSignatory') == true) {
                        if (isset($__Report->pdf->signatory->show) && $__Report->pdf->signatory->show == 1) {
                            $SignatorySettings[] = $__Report->pdf->signatory;
                        }
                    }

                    // Wenn die Bezeichnung ein Array ist, bei Zeilenumbrüchen
                    if (isset($__Report->pdf->description->p) && $__Report->pdf->description->p != '') {
                        $discriptionArray = null;
                        $xxx = 0;
                        foreach ($__Report->pdf->description->p as $_p) {
                            // um das XML-Objekt loszuwerden
                            if ($xxx > 0) {$discriptionArray .= "\n";}
                            $discriptionArray .= $_p;
                            $xxx++;
                        }
                    } else {
                        $discriptionArray = trim($__Report->pdf->description);
                    }

                    // Test ob Objekt an mehreren Stellen angezeigt werden soll
//                        if($__Report->pdf->output){}
                    $place = explode(' ', $__Report->pdf->output);
                    foreach ($place as $_place) {
                        if ($_place == 1) {
                            if ($__Report->pdf->output == 1) {
                                $key = trim($__Report->key);

                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['description'] = $discriptionArray;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = trim($this->xdata->$Reports->$key);

                                // die Breite von Label- und Datenzelle wird ermittelt
                                $width = explode(' ', trim($__Report->pdf->positioning->width));
                                $fontcolorlabel = $__Report->pdf->fontcolorlabel;
                                $bgcolorlabel = $__Report->pdf->bgcolorlabel;
                                $fontcolordata = $__Report->pdf->fontcolordata;
                                $bgcolordata = $__Report->pdf->bgcolordata;
                                $cellheight = trim($__Report->pdf->positioning->height);

                                // Wenn ein Zelleninhalt aus der XML-Datei kommt
                                if (isset($__Report->pdf->alternativvalue)) {
                                    $alternativvalue_array = explode('|br|', trim($__Report->pdf->alternativvalue));
                                    if (count($alternativvalue_array) > 1) {
                                        $alternativvalue = implode("\n", $alternativvalue_array);
                                    } else {
                                        $alternativvalue = trim($__Report->pdf->alternativvalue);
                                    }

                                    $dataArray[$Reports][trim($__Report->pdf->sorting)]['alternativvalue'] = $alternativvalue;
                                }

                                if (!isset($width[0])) {$width[0] = $StandartWidthLabel;}
                                if (!isset($width[1])) {$width[1] = $StandartWidthData;}

                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['revision'] = 0;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['clear'] = 0;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['headline'] = trim($__Report->pdf->headline);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['subheader'] = trim($__Report->pdf->subheader);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['break'] = trim($__Report->pdf->positioning->break);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['measure'] = trim($__Report->pdf->measure);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['fontsize'] = trim($__Report->pdf->fontsize);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdescription'] = trim($__Report->pdf->lines->description);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['textalign'] = trim($__Report->pdf->textalign);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdata'] = trim($__Report->pdf->lines->data);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdata_right'] = trim($__Report->pdf->lines->position->right);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdata_bottom'] = trim($__Report->pdf->lines->position->bottom);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['fieldtype'] = trim($__Report->fieldtype);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['mother'] = trim($__Report->pdf->mother);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['child'] = trim($__Report->pdf->child);
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['barcode'] = $__Report->pdf->barcode;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['radiooption'] = $__Report->radiooption;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['width_label'] = $width[0];
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['width_data'] = $width[1];
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['fontcolorlabel'] = $fontcolorlabel;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['bgcolorlabel'] = $bgcolorlabel;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['fontcolordata'] = $fontcolordata;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['bgcolordata'] = $bgcolordata;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['cellheight'] = $cellheight;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['hidden'] = false;
                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['formatting'] = array(
                                    'bold' => trim($__Report->pdf->fontbold),
                                    'italic' => trim($__Report->pdf->fontitalic),
                                    'underline' => trim($__Report->pdf->fontunderline),
                                );

                                if (!empty($this->RevisionsList)) {
                                    if (isset($this->RevisionsList['overview'][trim($__Report->model)][trim($__Report->key)]) && $this->RevisionsList['overview'][trim($__Report->model)][trim($__Report->key)] == true) {
                                        $dataArray[$Reports][trim($__Report->pdf->sorting)]['revision'] = 1;
                                    }
                                }

                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = preg_replace('/\s+/', ' ', $dataArray[$Reports][trim($__Report->pdf->sorting)]['data']);

                                if ($__Report->key == 'auftraggeber_adress') {
                                    $dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = preg_replace('#[,|;]+#', PHP_EOL, $dataArray[$Reports][trim($__Report->pdf->sorting)]['data']);
                                    $dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = join(PHP_EOL, array_map('trim', explode(PHP_EOL, $dataArray[$Reports][trim($__Report->pdf->sorting)]['data'])));
                                }

                                // Testen, ob das Feld ausgeblendet werden soll
                                if (isset($__Report->pdf->hidable) && (trim($__Report->pdf->hidable) == '1' || trim($__Report->pdf->hidable) == 'x' || trim($__Report->pdf->hidable) == 'true')) {
                                    foreach ($this->hiddenFields as $field) {
                                        if ($field['HiddenField']['model'] == trim($__Report->model) && $field['HiddenField']['field'] == trim($__Report->key)) {
                                            $dataArray[$Reports][trim($__Report->pdf->sorting)]['hidden'] = true;
                                            break;
                                        }
                                    }
                                }

                                // Wenn mehrere Felder zusammengefasst werden sollen
                                if ($dataArray[$Reports][trim($__Report->pdf->sorting)]['child'] == 1) {
                                    foreach ($this->xsettings->$Reports as $_xsettingsMother) {
                                        foreach ($_xsettingsMother as $__xsettingsMother) {
                                            if (trim($__Report->key) == trim($__xsettingsMother->pdf->mother)) {
                                                $motherKey = trim($__xsettingsMother->key);
                                                $dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = trim(
                                                    $dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] . '/' .
                                                    trim($this->xdata->$Reports->$motherKey)
                                                    , '/');
                                                $motherKey = null;
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }

            if (isset($this->xsettings->$ReportPDfSettings->images) && !empty($this->xsettings->$ReportPDfSettings->images)) {
                foreach ($this->xsettings->$ReportPDfSettings->images->children() as $_images) {

                    if (trim($_images->position->area) == $Reports) {

                        $images = array();

                        if (!empty($_images->type)) {
                            $images['type'] = trim($_images->type);
                        } else {
                            continue;
                        }

                        if (empty($_images->file)) {
                            continue;
                        }

                        if (!file_exists(Configure::read('root_folder') . 'pdf_images' . DS . trim($this->xdata->Reportnumber->testingmethod_id) . DS . trim($this->xdata->Reportnumber->version) . DS . trim($_images->file) . '.' . $images['type'])) {
                            continue;
                        } else {
                            $images['file'] = Configure::read('root_folder') . 'pdf_images' . DS . trim($this->xdata->Reportnumber->testingmethod_id) . DS . trim($this->xdata->Reportnumber->version) . DS . trim($_images->file) . '.' . $images['type'];
                        }

                        if (!empty($_images->dimension)) {
                            $dimension = explode(' ', $_images->dimension);
                        } else {
                            continue;
                        }

                        if (!empty($_images->position->float)) {
                            $position = explode(' ', $_images->position->float);
                        } else {
                            continue;
                        }

                        if (!empty($_images->position->absolut)) {
                            $absolut = explode(' ', $_images->position->absolut);
                        } else {
                            continue;
                        }

                        if (isset($dimension)) {
                            $images['width'] = $dimension[0];
                            $images['height'] = $dimension[1];
                        } else {
                            continue;
                        }

                        if (isset($position)) {
                            $images['vertikal'] = $position[0];
                            $images['horizontal'] = $position[1];
                        } else {
                            continue;
                        }

                        if (isset($absolut)) {
                            $images['x'] = $absolut[0];
                            $images['y'] = $absolut[1];
                        } else {
                            continue;
                        }

                        $imageArray[$Reports][] = $images;
                    }
                }
            }
        }

        // Das neue Array nach der vorgegeben Reihenfolge sortieren
        if (isset($Report[1])) {@ksort($dataArray[$Report[1]]);}
        if (isset($Report[2])) {@ksort($dataArray[$Report[2]]);}

        $datenArraySort = array();
        // Die Felder werden nach ihrer Reihenfolge sortiert
        foreach ($Report as $_Report) {
            if ($dataArray[$_Report] > 0) {
                foreach ($dataArray[$_Report] as $_dataArray) {
                    $datenArraySort[$_Report][] = $_dataArray;
                }
            }
        }

        $current_clear = 0;
        $xx = 0;

        // Die Daten werden in Zeilen und Spalten aufgeteilt
        foreach ($Report as $_Report) {
            if (isset($datenArraySort[$_Report])) {
                for ($x = 0; $x < count($datenArraySort[$_Report]); $x++) {
                    // Wenn die Gesamtbreite zu groß wird erfolgt ein Umbruch
                    $current_clear = $current_clear + $datenArraySort[$_Report][$x]['width_label'] + $datenArraySort[$_Report][$x]['width_data'];
                    $datenArraySort[$_Report][$x]['clear'] = $x;

                    $datenArraySortRow[$_Report][$xx][] = $datenArraySort[$_Report][$x];

                    if ($datenArraySort[$_Report][$x]['break'] == 1) {
                        $xx++;
                        $maxCellheigth = 0;
                        $current_clear = $this->defs['PDF_MARGIN_LEFT'];
                    }
                }
                $xx = 0;
            }
        }

        $y = 0;
        $sectionHeadline = array(
            0 => trim($this->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_1->VALUE),
            1 => trim($this->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_2->VALUE),
        );

        foreach ($datenArraySortRow as $_key => $_datenArraySort) {
            $sectionHeadline[$_key] = $sectionHeadline[$y];
            $y++;
        }

        if (isset($this->QrCodeUrl)) {

            $QRstyle = array(
                'border' => 0,
                'vpadding' => 0,
                'hpadding' => 0,
                'fgcolor' => array(0, 0, 0),
                'bgcolor' => false,
                'module_width' => 1,
                'module_height' => 1,
            );

            $this->write2DBarcode(
                $this->QrCodeUrl,
                $this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->quality,
                $this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->x,
                $this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->y,
                $this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->width,
                $this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->height,
                $QRstyle,
                'N');
        }

        // Überschrift 1
        if (trim($this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->VALUE) != '') {
            $this->SetFont('calibri', 'n', 14);
            $this->MultiCell(
                0,
                0,
                trim($this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->VALUE),
                0,
                'L',
                0,
                1,
                $this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->POS_X,
                $this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y + $this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->POS_Y,
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
        if (trim($this->xsettings->$ReportPdf->settings->QM_HEADLINE_02->VALUE) != '') {
            $this->SetFont('calibri', 'n', 10);
            $this->MultiCell(
                0,
                0,
                trim($this->xsettings->$ReportPdf->settings->QM_HEADLINE_02->VALUE),
                0,
                'L',
                0,
                1,
                $this->xsettings->$ReportPdf->settings->QM_HEADLINE_02->POS_X,
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

        if (Configure::check('show_orginal_duplicat_string') && Configure::read('show_orginal_duplicat_string') == true) {
            $this->SetFont('calibri', 'n', 10);
            $this->MultiCell(
                0,
                0,
                trim($this->print_version),
                0,
                'L',
                0,
                1,
                $this->xsettings->$ReportPdf->settings->QM_HEADLINE_02->POS_X,
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

        // Firmenlogo
        if ($this->printversion < 3) {
            if (file_exists(Configure::read('company_logo_folder') . trim($this->xdata->Testingcomp->id) . DS . 'logo.png') && $this->xdata->Testingcomp->id != 0) {

                $this->Image(Configure::read('company_logo_folder') . trim($this->xdata->Testingcomp->id) . DS . 'logo.png',
                    intval($this->xsettings->$ReportPdf->settings->QM_LOGO_X),
                    intval($this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y),
                    intval($this->xsettings->$ReportPdf->settings->QM_LOGO_HEIGHT),
                    intval($this->xsettings->$ReportPdf->settings->QM_LOGO_WIDTH),
                    'PNG', false, '', false, 300, '', false, false, 0, false, false, false);

            } else {

                $CompanyLogoFolder = Configure::read('company_logo_folder') . trim($this->xdata->Testingcomp->id) . DS;

                if ($handle = opendir($CompanyLogoFolder)) {

                    while (false !== ($entry = readdir($handle))) {
                        if (file_exists($CompanyLogoFolder . $entry) && filetype($CompanyLogoFolder . $entry) == 'file') {

                            $LogoInfo = mime_content_type($CompanyLogoFolder . $entry);

                            switch ($LogoInfo) {
                                case 'image/svg+xml':

                                    $this->ImageSVG(
                                        $CompanyLogoFolder . $entry,
                                        $this->xsettings->$ReportPdf->settings->QM_LOGO_X,
                                        $this->xsettings->$ReportPdf->settings->QM_LOGO_Y,
                                        $this->xsettings->$ReportPdf->settings->QM_LOGO_WIDTH,
                                        $this->xsettings->$ReportPdf->settings->QM_LOGO_HEIGHT,
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
            }
//1679561017
            // Zusätzliche Logos einfügen
            if (isset($this->xsettings->$ReportPdf->settings->QM_ADDITIONAL_LOGOS->LOGO)) {

                $CompanyLogoFolder = Configure::read('company_logo_folder') . trim($this->xdata->Testingcomp->id) . DS . 'additional' . DS;

                $additionaly_logos = $this->xdata->$ReportArchiv->additionaly_logos;

                $additionaly_logos = json_encode($additionaly_logos);
                $additionaly_logos = json_decode($additionaly_logos,true);

                foreach ($this->xsettings->$ReportPdf->settings->QM_ADDITIONAL_LOGOS->LOGO as $_additional_logos) {

                    $StopForEach = 0;

                    if(count($additionaly_logos) > 0){
                        if(isset($additionaly_logos['LOGO']['LOGO_NAME'])){

                            if($additionaly_logos['LOGO']['LOGO_NAME'] == trim($_additional_logos->LOGO_NAME)){
    
                                $StopForEach = 1;
    
                            }
                        } else if(!isset($additionaly_logos['LOGO']['LOGO_NAME']) && count($additionaly_logos['LOGO']) > 1){
    
                            foreach($additionaly_logos['LOGO'] as $search_logo){
    
                                if($search_logo['LOGO_NAME'] == trim($_additional_logos->LOGO_NAME)){
    
                                    $StopForEach = 1;
                                    break;
                                }
    
                            }
    
                        }

                    }

                    if($StopForEach == 1) continue;
    
                    $AdditionalLogoPath = $CompanyLogoFolder . trim($_additional_logos->LOGO_NAME);

                    if (!file_exists($AdditionalLogoPath)) {
                        continue;
                    }

                    $LogoInfo = mime_content_type($AdditionalLogoPath);

                    switch ($LogoInfo) {
                        case 'image/svg+xml':
                            $this->ImageSVG(
                                $AdditionalLogoPath,
                                intval($_additional_logos->POS_X),
                                intval($_additional_logos->POS_Y),
                                intval($_additional_logos->POS_W),
                                intval($_additional_logos->POS_H),
                                '',
                                '',
                                '',
                                0,
                                false);
                            break;

                        case 'image/png':
                            $this->Image(
                                $AdditionalLogoPath,
                                intval($_additional_logos->POS_X),
                                intval($_additional_logos->POS_Y),
                                intval($_additional_logos->POS_H),
                                intval($_additional_logos->POS_W),
                                'PNG', false, '', false, 300, '', false, false, 0, false, false, false);
                            break;
                    }
                }
            }
        }

        if ($this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->SHOW_ADRESS == 1) {
            $Firmenadresse = null;
            $Firmenadresse .= trim($this->xdata->Testingcomp->firmenname) . PHP_EOL;
            $Firmenadresse .= trim($this->xdata->Testingcomp->plz) . " ";
            $Firmenadresse .= trim($this->xdata->Testingcomp->ort) . PHP_EOL;
            $Firmenadresse .= trim($this->xdata->Testingcomp->strasse) . PHP_EOL;
            if (trim($this->xdata->Testingcomp->telefon) != '') {
                $Firmenadresse .= 'Tel. ' . trim($this->xdata->Testingcomp->telefon) . PHP_EOL;
            }

            if (trim($this->xdata->Testingcomp->telefax) != '') {
                $Firmenadresse .= 'Fax. ' . trim($this->xdata->Testingcomp->telefax) . PHP_EOL;
            }

            if (trim($this->xdata->Testingcomp->email) != '') {
                $Firmenadresse .= trim($this->xdata->Testingcomp->email);
            }

            if (trim($this->xdata->Testingcomp->internet) != '') {
                $Firmenadresse .= ', ' . trim($this->xdata->Testingcomp->internet);
            }

            $Firmenadresse = preg_replace('/[\s,]+$/', '', $Firmenadresse);

            if ($this->printversion == 3) {
                $Firmenadresse = null;
            }

            $this->SetFont('calibri', 'n', 7);
            $this->MultiCell(
                55,
                0,
                $Firmenadresse,
                0,
                'L',
                0,
                1,
                intval($this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_X),
                intval($this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y),
                true,
                0,
                false,
                true,
                0,
                'T',
                true
            );
        }

        $this->setCellPaddings($this->defs['QM_CELL_PADDING_L'], $this->defs['QM_CELL_PADDING_T'], $this->defs['QM_CELL_PADDING_R'], $this->defs['QM_CELL_PADDING_B']);

        // Umrandungen
        //oben horizontal
        $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->style1);
        //unten horizontal
        $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style1);
        //links vertikal
        $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style1);
        //rechts vertikal
        $this->Line($this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style1);

        $this->insertSearchBarcode();

        $this->SetY($this->defs['QM_CELL_LAYOUT_LINE_TOP']);

        $this->SetLeftMargin($this->defs['PDF_MARGIN_LEFT']);

        $AreaStartY = array();

        // General oder Specific
        foreach ($datenArraySortRow as $_key => $_datenArraySortRow) {

            $AreaStartY[$_key] = $this->GetY();

            // Ab Seite 2 keine Footerdaten mehr anzeigen
            $printHeader = true;
            if (Configure::read('HeaderOnlyFirstPage') == true && $this->numpages > 1) {
                $printHeader = false;
            }

            if ($this->forcePrintHeaders) {
                $printHeader = true;
            }

            // Wenn die Ausgabe von Infos ab der zweiten Seite unterdrückt werden soll
            if (!$this->forcePrintHeaders && Configure::check('HeaderOnlyFirstPage') && Configure::read('HeaderOnlyFirstPage') == true && $this->numpages > 1) {
                continue;
            }

            if (!$this->forcePrintHeaders && preg_match('/Generally$/', $_key) && Configure::check('HeaderOnlyFirstPageGenerally') && Configure::read('HeaderOnlyFirstPageGenerally') == true && $this->numpages > 1) {
                continue;
            }

            if (!$this->forcePrintHeaders && preg_match('/Specific$/', $_key) && Configure::check('HeaderOnlyFirstPageSpecific') && Configure::read('HeaderOnlyFirstPageSpecific') == true && $this->numpages > 1) {
                continue;
            }

            //if($this->getPage() > 1 && !preg_match('/Generally$/', $_key)) continue;

            $this->SetCellPadding(1);

            if (preg_match('/Generally$/', $_key)) {
                $reportnumberY = $this->GetY();
                $reportnumberX = $this->GetX();

                $this->SetFont('calibri', 'n', 8);
                $this->SetFillColor(180, 180, 180);
                $page_report_no = $this->defs['QM_PAGE_DESCRIPTION'] . ' ' . $this->getPage() . '/' . $this->getAliasNbPages() . ' ' . trim($this->reportnumber);
                if ($this->printversion == 3) {
                    $page_report_no = $this->defs['QM_PAGE_DESCRIPTION'] . ' ' . $this->getPage() . '/' . $this->getAliasNbPages();
                }

                //$page_report_no = 'Seite '.$this->getPage().' '.trim($this->reportnumber),

                $width = $this->GetStringWidth($page_report_no);
//                    CakeLog::write('error', $width);
                $this->MultiCell(
                    $width,
                    5,
                    $page_report_no,
                    0,
                    'R',
                    false,
                    1,
                    $this->defs['QM_CELL_LAYOUT_CLEAR'] - $width,
                    $reportnumberY + 1,
                    true,
                    0,
                    false,
                    false,
                    5,
                    'M',
                    true
                );

                $this->SetY($reportnumberY);
                $this->SetX($reportnumberX);

                // Zusätzliche Kundendaten Anfang
                if (!empty($this->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_0->VALUE)) {

                    $this->SetFont('calibri', 'n', 10);
                    $this->SetFillColor(180, 180, 180);
                    $this->MultiCell(
                        $this->defs['QM_CELL_LAYOUT_CLEAR'] - $this->defs['PDF_MARGIN_LEFT'],
                        0,
                        trim($this->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_0->VALUE),
                        0,
                        'L',
                        1,
                        1,
                        $this->GetX(),
                        $this->GetY(),
                        true,
                        0,
                        false,
                        true,
                        0,
                        'T',
                        true
                    );

                    $this->Line(
                        $this->defs['PDF_MARGIN_LEFT'],
                        $this->GetY(),
                        $this->defs['QM_CELL_LAYOUT_CLEAR'],
                        $this->GetY(),
                        $this->style2
                    );
                }
// Zusätzlich Kundendaten Ende
            }

            if (empty($this->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_0->VALUE) || preg_match('/Generally$/', $_key) == 0) {

                $this->SetFont('calibri', 'n', 10);
                $this->SetFillColor(180, 180, 180);
                $this->MultiCell(
                    $this->defs['QM_CELL_LAYOUT_CLEAR'] - $this->defs['PDF_MARGIN_LEFT'],
                    0,
                    $sectionHeadline[$_key],
                    0,
                    'L',
                    1,
                    1,
                    $this->GetX(),
                    $this->GetY(),
                    true,
                    0,
                    false,
                    true,
                    0,
                    'T',
                    true
                );

                // horizontale Linie

                $SectionStartArray[$_key] = $this->GetY();

                $this->Line(
                    $this->defs['PDF_MARGIN_LEFT'],
                    $this->GetY(),
                    $this->defs['QM_CELL_LAYOUT_CLEAR'],
                    $this->GetY(),
                    $this->style2
                );
            }

//                // Zeilen
            foreach ($_datenArraySortRow as $__key => $__datenArraySortRow) {

                $this_y_zeile_top = $this->GetY();
                $line_vertical = array();
                $this_y_zeile = $this->GetY();
                $this_y_zeile_max = $this->GetY();
                $this_x_zeile = $this->GetX();

                // Daten auslesen
                foreach ($__datenArraySortRow as $___key => $___datenArraySortRow) {

                    if (!empty($___datenArraySortRow['subheader'])) {

                        $Subheader = trim($___datenArraySortRow['subheader']);

                        if (!empty($this->xsettings->$ReportPdf->settings->{$Subheader}->VALUE)) {

                            $this->SetFont('calibri', 'n', 10);
                            $this->SetFillColor(180, 180, 180);
                            $this->MultiCell(
                                $this->defs['QM_CELL_LAYOUT_CLEAR'] - $this->defs['PDF_MARGIN_LEFT'],
                                0,
                                trim($this->xsettings->$ReportPdf->settings->{$Subheader}->VALUE),
                                0,
                                'L',
                                1,
                                1,
                                $this->GetX(),
                                $this->GetY(),
                                true,
                                0,
                                false,
                                true,
                                0,
                                'T',
                                true
                            );

                            // horizontale Linie

                            $SectionStartArray[$_key] = $this->GetY();

                            $this->Line(
                                $this->defs['PDF_MARGIN_LEFT'],
                                $this->GetY(),
                                $this->defs['QM_CELL_LAYOUT_CLEAR'],
                                $this->GetY(),
                                $this->style2
                            );

                            $this_y_zeile_top = $this->GetY();
                            $line_vertical = array();
                            $this_y_zeile = $this->GetY();
                            $this_y_zeile_max = $this->GetY();
                            $this_x_zeile = $this->GetX();

                        }
                    }

                    if (isset($___datenArraySortRow['headline']) && $___datenArraySortRow['headline'] == 1) {
//                            pr($___datenArraySortRow['description']);

                        $this->SetFont('calibri', 'n', 10);
                        $this->SetFillColor(180, 180, 180);
                        $this->MultiCell(
                            $this->defs['QM_CELL_LAYOUT_CLEAR'] - $this->defs['PDF_MARGIN_LEFT'],
                            0,
                            $___datenArraySortRow['description'],
                            0,
                            'L',
                            1,
                            1,
                            $this->GetX(),
                            $this->GetY(),
                            true,
                            0,
                            false,
                            true,
                            0,
                            'T',
                            true
                        );

                        // horizontale Linie
                        $this->Line(
                            $this->defs['PDF_MARGIN_LEFT'],
                            $this->GetY(),
                            $this->defs['QM_CELL_LAYOUT_CLEAR'],
                            $this->GetY(),
                            $this->style2
                        );

                        $this_y_zeile_top = $this->GetY();
                        $this_y_zeile = $this->GetY();
                        $this_y_zeile_max = $this->GetY();
                        $this_x_zeile = $this->GetX();

                        continue;
                    }

                    $Data = $___datenArraySortRow['hidden'] ? null : $___datenArraySortRow['data'];

                    // Wenn Daten vorhanden sind und eine Maßeinheit da ist, wird dies angefügt
                    if ($Data != '' && $___datenArraySortRow['measure'] != '') {
                        // Wenn die Masseinheit per Hand eingetragen wurde, wird Sie hier entfernt
                        $Data = str_replace($___datenArraySortRow['measure'], '', $Data);
                        $Data .= ' ' . $___datenArraySortRow['measure'];
                    }

                    // bei einer Checkbox muss der Wert umgewandelt werden
                    if (
                        isset($___datenArraySortRow['fieldtype']) &&
                        trim($___datenArraySortRow['fieldtype']) == 'checkbox'
                    ) {

                        if ($Data == 'false') {
                            $Data = '';
                        }
                        if ($Data == '0') {
                            $Data = '';
                        }
                        if ($Data == 'true') {
                            $Data = 'X';
                        }
                        if ($Data == '1') {
                            $Data = 'X';
                        }
                    }

                    if (
                        isset($___datenArraySortRow['fieldtype']) &&
                        trim($___datenArraySortRow['fieldtype']) == 'radio'
                    ) {
                        if (
                            isset($___datenArraySortRow['radiooption']) &&
                            count($___datenArraySortRow['radiooption']) > 0
                        ) {
                            if ($Data != '') {

                                foreach ($___datenArraySortRow['radiooption'] as $_radiokey => $_radiovalue) {
                                    $radio_x = 0;
                                    foreach ($_radiovalue as $__radiokey => $__radiovalue) {
                                        if ($radio_x == $Data) {
//                                            pr(trim($__radiovalue));
                                            $Data = trim($__radiovalue);
                                            break;
                                        }
                                        $radio_x++;
                                    }
                                }
                            }
                        } else {
                            $Data = $this->defs['radiodefault'][(int)$Data];
                        }

                    }

                    if (isset($___datenArraySortRow['alternativvalue']) && $___datenArraySortRow['alternativvalue'] != '') {
                        $Data = $___datenArraySortRow['alternativvalue'];
                    }

                    if (
                        isset($___datenArraySortRow['fieldtype']) &&
                        trim($___datenArraySortRow['fieldtype']) == 'multiselect'
                    ) {
                        $Data = str_replace(" ", ", ", $Data);
                    }

                    $font_size = 8;

                    if ($___datenArraySortRow['fontsize'] != '') {
                        $font_size = $___datenArraySortRow['fontsize'];
                    }

                    if ($___datenArraySortRow['width_data'] > 0) {
                        $_font_style = '';
                        if (isset($___datenArraySortRow['formatting']['bold']) && !!intval($___datenArraySortRow['formatting']['bold'])) {
                            $_font_style .= 'B';
                        }

                        if (isset($___datenArraySortRow['formatting']['italic']) && !!intval($___datenArraySortRow['formatting']['italic'])) {
                            $_font_style .= 'I';
                        }

                        $this->SetFont('calibri' . (empty($_font_style) ? '' : strtolower($_font_style)), empty($_font_style) ? 'n' : $_font_style, $font_size);
                        $this->SetCellPadding(1);

//                            $Data = setUTF8($Data);
                        //    $Data = (iconv('UTF-8', 'UTF-16BE', $Data));

                        if ($___datenArraySortRow['revision'] == true) {
                            $Data .= ' (R)';
                            $this->setTextColor(255, 0, 0);
                        }

                        if (!empty($___datenArraySortRow['barcode']) && trim($___datenArraySortRow['barcode']->show) == 1) {

                            $style = array(
                                'border' => false,
                                'hpadding' => '0',
                                'vpadding' => '0',
                                'fgcolor' => array(0, 0, 0),
                                'bgcolor' => false, //array(255,255,255),
                                'text' => false,
                                'font' => 'helvetica',
                                'fontsize' => 8,
                                'stretchtext' => 4,
                            );

                            $this->write1DBarcode($Data, 'C39', $this_x_zeile + 1, $this_y_zeile - 5, $___datenArraySortRow['width_data'] - 2, 10, 0.4, $style, 'T');
                        }

                        if (empty($___datenArraySortRow['barcode'])) {
                            $this->MultiCell(
                                $___datenArraySortRow['width_data'],
                                0,
                                $Data,
                                0,
                                'L',
                                0,
                                1,
                                $this_x_zeile,
                                $this_y_zeile,
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

                    $this->setTextColor(0, 0, 0);

                    // aktuelle Zeilenhöhe speichern wenn diese höher ist als max
                    if ($this->GetY() > $this_y_zeile_max) {
                        $this_y_zeile_max = $this->GetY();
                    }
                    $this_x = $this_x_zeile;
                    $this_x_zeile = $this_x_zeile + $___datenArraySortRow['width_data'];

                    $line_vertical[] = array(
                        'x_1' => $this_x,
                        'x_2' => $this_x + $___datenArraySortRow['width_data'],
                        'right' => trim($___datenArraySortRow['linesdata_right']),
                        'bottom' => trim($___datenArraySortRow['linesdata_bottom']),
                    );
                }

                $this->Ln();

                $this->SetY($this_y_zeile_max - (intval($cellheight) / 4));
                $this->SetX($this->defs['PDF_MARGIN_LEFT']);

                $this_y_zeile = $this->GetY();
                $this_y_zeile_max = $this->GetY();
                $this_x_zeile = $this->GetX();

                // Bezeichnungen auslesen
                foreach ($__datenArraySortRow as $___key => $___datenArraySortRow) {

                    if (isset($___datenArraySortRow['headline']) && $___datenArraySortRow['headline'] == 1) {
                        continue;
                    }

                    $description = $___datenArraySortRow['description'];
                    $this->SetFont('calibri', 'I', 6);
                    $this->SetCellPadding(1);
                    $description = str_replace('|br|', PHP_EOL, $description);

                    if ($description != '') {
                        $this->MultiCell(
                            $___datenArraySortRow['width_data'],
                            0,
                            $___datenArraySortRow['hidden'] ? null : $description,
                            0,
                            'L',
                            0,
                            1,
                            $this_x_zeile,
                            $this_y_zeile,
                            true,
                            0,
                            false,
                            true,
                            0,
                            'T',
                            true
                        );
                    }
                    // aktuelle Zeilenhöhe speichern wenn diese höher ist als max
                    if ($this->GetY() > $this_y_zeile_max) {
                        $this_y_zeile_max = $this->GetY();
                    }
                    $this_x_zeile = $this_x_zeile + $___datenArraySortRow['width_data'];
                }

                // Linien
                foreach ($line_vertical as $_line_vertical) {

                    // horizontale Linie
                    if ($_line_vertical['bottom'] == 1) {
                        $this->Line(
                            $_line_vertical['x_1'],
                            $this_y_zeile_max,
                            $_line_vertical['x_2'],
                            $this_y_zeile_max,
                            $this->style2
                        );
                    }
                    // vertikale Linie
                    if ($_line_vertical['right'] == 1) {
                        $this->Line(
                            $_line_vertical['x_2'],
                            $this_y_zeile_top,
                            $_line_vertical['x_2'],
                            $this_y_zeile_max,
                            $this->style2
                        );
                    }
                }

                $this->Ln();
                $this->SetY($this_y_zeile_max);
            }
        }

        $this->Line(
            $this->defs['PDF_MARGIN_LEFT'],
            $this->GetY(),
            $this->defs['QM_CELL_LAYOUT_CLEAR'],
            $this->GetY(),
            $this->style1
        );

        if (!empty($this->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_3->VALUE) && !empty($this->xdata->$ReportEvaluation)) {
            $this->SetCellPadding(1);
            $this->SetFont('calibri', 'n', 10);
            $this->SetFillColor(180, 180, 180);
            $this->MultiCell(
                $this->defs['QM_CELL_LAYOUT_CLEAR'] - $this->defs['PDF_MARGIN_LEFT'],
                0,
                trim($this->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_3->VALUE),
                0,
                'L',
                1,
                1,
                $this->GetX(),
                $this->GetY(),
                true,
                0,
                false,
                true,
                0,
                'T',
                true
            );
            // horizontale Linie

            $this->Line(
                $this->defs['PDF_MARGIN_LEFT'],
                $this->GetY(),
                $this->defs['QM_CELL_LAYOUT_CLEAR'],
                $this->GetY(),
                $this->style2
            );
        }

        // mögliche Bilder einlesen
        $GetYBeforeImage = $this->GetY();

        if (isset($imageArray[$_Report])) {
            foreach ($imageArray[$_Report] as $_imageArray) {

                // Wenn die Ausgabe von Infos ab der zweiten Seite unterdrückt werden soll
                if (
                    !$this->forcePrintHeaders &&
                    Configure::check('HeaderOnlyFirstPage') &&
                    Configure::read('HeaderOnlyFirstPage') == true && $this->numpages > 1
                ) {
                    continue;
                }

                if (
                    !$this->forcePrintHeaders &&
                    preg_match('/Generally$/', $_key) &&
                    Configure::check('HeaderOnlyFirstPageGenerally') &&
                    Configure::read('HeaderOnlyFirstPageGenerally') == true && $this->numpages > 1
                ) {
                    continue;
                }

                if (
                    !$this->forcePrintHeaders && preg_match('/Specific$/', $_key) &&
                    Configure::check('HeaderOnlyFirstPageSpecific') &&
                    Configure::read('HeaderOnlyFirstPageSpecific') == true && $this->numpages > 1
                ) {
                    continue;
                }

                if ($_imageArray['type'] == 'eps') {

                    $this_x = 0;
                    $this_y = 0;

                    // Wenn das Bild unterhalb eingefügt wird, muss der Y-Punkt gesetzt werden
                    if ($_imageArray['x'] > 0) {
                        $this_x = $_imageArray['x'];
                    }
                    if ($_imageArray['y'] > 0) {
                        $this_y = $_imageArray['y'];
                    }
                    if ($_imageArray['x'] == 0 && $_imageArray['y'] == 0) {
                        if ($_imageArray['vertikal'] == 'T') {
                            $this_y = $AreaStartY[$_Report] + 8;
                        }

                        if ($_imageArray['horizontal'] == 'R') {
                            $this_x = $this->defs['QM_CELL_LAYOUT_WIDTH'] - $_imageArray['width'];
                        }

                    }

                    $this->ImageEps(
                        $_imageArray['file'],
                        $this_x,
                        $this_y,
                        $_imageArray['height'],
                        $_imageArray['width'],
                        '',
                        true,
                        '',
                        '',
                        0,
                        true
                    );

                }
            }

        }
//pr($this->defs);
//die();
        $this->SetY($GetYBeforeImage);

        $this->bodyStart[$this->getPage()] = $this->HaederEnd = $this->GetY();

    }

    public function Footer()
    {

        if (trim($this->xsettings->{'Report' . ($this->Verfahren) . 'Pdf'}->settings->QM_SHOW_FOOTER) == 'false' || trim($this->xsettings->{'Report' . ($this->Verfahren) . 'Pdf'}->settings->QM_SHOW_FOOTER) == '0') {
            return;
        }

        // Ab Seite 2 keine Footerdaten mehr anzeigen
        $printFooter = true;
        if (Configure::read('FooterOnlyFirstPage') == true && $this->numpages > 1) {
            $printFooter = false;
        }

        if ($this->forcePrintHeaders) {
            $printFooter = true;
        }

        if (!$printFooter) {
            return;
        }

        $this->forcePrintHeaders = false;

        $Verfahren = $this->Verfahren;
        if (isset($this->Examiner_Stamp_Path)) {
            if (isset($this->Examiner_Stamp_Path['examiner']['examiner_stamp_path'])) {
                $Examiner_Stamp_Path = $this->Examiner_Stamp_Path['examiner']['examiner_stamp_path'];
            }

            if (isset($this->Examiner_Stamp_Path['supervision']['supervisor_stamp_path'])) {
                $Supervisor_Stamp_Path = $this->Examiner_Stamp_Path['supervision']['supervisor_stamp_path'];
            }
        }

        $Report[1] = 'Report' . $Verfahren . 'Generally';
        $dataArray = array();
        $width = array();
        $StandartWidthLabel = 25;
        $StandartWidthData = 50;
        $StandartCellHeight = 6;
        $cellheight = 4;
        $_ReportsFooterheight = 0;

        $this->SetY($this->defs['QM_START_FOOTER']);
        $this->SetLeftMargin($this->defs['PDF_MARGIN_LEFT']);
        $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_START_FOOTER'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_START_FOOTER'], $this->style1);

        $Reports[0] = 'Report' . $this->Verfahren . 'Generally';
        $Reports[1] = 'Report' . $this->Verfahren . 'Specific';
        $Reports[2] = 'Report' . $this->Verfahren . 'Evaluation';

        $ReportsFooter = array();
        $Signatory = array();

        foreach ($Reports as $_Reports) {

            foreach ($this->xsettings->$_Reports as $_xsettings) {
                foreach ($_xsettings as $__xsettings) {
                    // Digitale Unterschriften sammeln
                    if (isset($__xsettings->pdf->signatory->show) && $__xsettings->pdf->signatory->show == 1) {
                        $Signatory[] = $__xsettings->pdf->signatory;
                    }

                    if ($__xsettings->pdf->output == 5) {

                        // Wenn die Bezeichnung ein Array ist, bei Zeilenumbrüchen
                        if ($__xsettings->pdf->description->p != '') {
                            $discriptionArray = array();
                            $row = null;
                            foreach ($__xsettings->pdf->description->p as $_p) {
                                // um das XML-Objekt loszuwerden
                                $row .= $_p;
                                $discriptionArray[] = $row;
                                $row = null;
                            }
                        } else {
                            $discriptionArray = trim($__xsettings->pdf->description);
                        }

                        // Die Daten anhand des Keys aus dem Datenarray holen
                        if (isset($__xsettings->key)) {

                            $DataKey = trim($__xsettings->key);

                            if ($__xsettings->pdf->positioning->height != '') {
                                $CellHeight = intval($__xsettings->pdf->positioning->height);
                            } else {
                                $CellHeight = $StandartCellHeight;
                            }
                            // hier werden die Ausgabedaten gesammelt, die im Fluss angeordnet sind
                            if ($__xsettings->pdf->positioning->x[0] == '') {

                                $width = explode(' ', trim($__xsettings->pdf->positioning->width));

                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['revision'] = 0;
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['data'] = $this->xdata->$_Reports->$DataKey;
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['key'] = $DataKey;
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['sorting'] = trim($__xsettings->pdf->sorting);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['description'] = $discriptionArray;
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['widthLabel'] = intval($width[0]);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['widthData'] = intval($width[1]);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['height'] = $CellHeight;
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['x'] = trim($__xsettings->pdf->positioning->x);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['rotation'] = trim($__xsettings->pdf->positioning->rotation);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['mother'] = trim($__xsettings->pdf->mother);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['child'] = trim($__xsettings->pdf->child);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['fontsize'] = trim($__xsettings->pdf->fontsize);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['break'] = trim($__xsettings->pdf->positioning->break);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['linesdata'] = trim($__xsettings->pdf->lines->data);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['linesdata_right'] = trim($__xsettings->pdf->lines->position->right);
                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['linesdata_bottom'] = trim($__xsettings->pdf->lines->position->bottom);

                                if (!empty($this->RevisionsList)) {
                                    if (isset($this->RevisionsList['overview'][$_Reports][$DataKey]) && $this->RevisionsList['overview'][$_Reports][$DataKey] == true) {
                                        $ReportsFooter[trim($__xsettings->pdf->sorting)]['revision'] = 1;
                                    }
                                }

                                // Wenn mehrere Felder zusammengefasst werden sollen
                                if ($ReportsFooter[trim($__xsettings->pdf->sorting)]['child'] == 1) {
                                    foreach ($this->xsettings->$_Reports as $_xsettingsMother) {
                                        foreach ($_xsettingsMother as $__xsettingsMother) {
                                            if (trim($__xsettingsMother->pdf->mother) == $DataKey) {
                                                $DataKeyMother = trim($__xsettingsMother->key);
                                                $ReportsFooter[trim($__xsettings->pdf->sorting)]['data'][0] = trim($ReportsFooter[trim($__xsettings->pdf->sorting)]['data'] . '; ' . trim($this->xdata->$_Reports->$DataKeyMother));
                                            }
                                        }
                                    }
                                }
                            }
                            // hier werden die Ausgabedaten gesammelt, die einen festen X-Punkt haben
                            if ($__xsettings->pdf->positioning->x[0] != '') {
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['data'] = $this->xdata->$_Reports->$DataKey;
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['key'] = $DataKey;
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['description'] = $discriptionArray;
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['width'] = trim($__xsettings->pdf->positioning->width);
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['height'] = trim($__xsettings->pdf->positioning->height);
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['x'] = trim($__xsettings->pdf->positioning->x);
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['fontsize'] = trim($__xsettings->pdf->fontsize);
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['textalign'] = trim($__xsettings->pdf->textalign);
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['fontbold'] = trim($__xsettings->pdf->fontbold);
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['fontitalic'] = trim($__xsettings->pdf->fontitalic);
                                $ReportsOverFooter[trim($__xsettings->pdf->sorting)]['fontunderline'] = trim($__xsettings->pdf->fontunderline);

                                if (!empty($this->RevisionsList)) {
                                    if (isset($this->RevisionsList['overview'][$_Reports][$DataKey]) && $this->RevisionsList['overview'][$_Reports][$DataKey] == true) {
                                        $ReportsFooter[trim($__xsettings->pdf->sorting)]['revision'] = 1;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Das Array nach der Ausgabereihenfolge sortieren
        @ksort($ReportsFooter);
        @ksort($ReportsOverFooter);
        $testx = 0;

        // nochmal den Y-Startpuntk festlegen
        $this->SetY($this->defs['QM_START_FOOTER']);

        // feste Daten ausgeben
        if (isset($ReportsOverFooter)) {

            foreach ($ReportsOverFooter as $_ReportsOverFooter) {
                $_output = null;

                // Wenn der Zelleninhalt mehrere Zeilen (Arrays) umfasst, werden diese in Zeilenumbrüche gewandelt
                if (is_array($_ReportsOverFooter['description'])) {
                    $x = 0;
                    foreach ($_ReportsOverFooter['description'] as $__description) {
                        if ($x > 0) {
                            $_output .= "\n";
                        }
                        $_output .= $__description;
                        $x++;
                    }
                } else {
                    $_output = $_ReportsOverFooter['description'];
                }

                // Überschrift und Daten werden zusammengefasst
                if ($_ReportsOverFooter['data'][0] != '') {
                    $_output .= "\n" . $_ReportsOverFooter['data'][0];
                }

                // Schriftgröße festlegen wenn angegeben
                if ($_ReportsOverFooter['fontsize'] != '') {
                    $this->SetFontSize($_ReportsOverFooter['fontsize']);
                } else {
                    $this->SetFontSize(8);
                }

                // Daten ausgeben
                //while(mb_detect_encoding(utf8_decode($_output)) != 'ASCII') {
                //    $_output = utf8_decode(ConnectionManager::getDataSource('default')->setUTF8Encoding($_output));
                // }
                $_output = htmlspecialchars_decode($_output);

                $this->SetCellPadding(1);
                $this->MultiCell(
                    intval($_ReportsOverFooter['width']),
                    intval($_ReportsOverFooter['height']),
                    $_output,
                    'R',
                    $_ReportsOverFooter['textalign'],
                    false,
                    2,
                    $this->defs['PDF_MARGIN_LEFT'] + $_ReportsOverFooter['x'],
                    $this->defs['QM_START_FOOTER'],
                    true,
                    0,
                    false,
                    true,
                    intval($_ReportsOverFooter['height']),
                    'T',
                    true
                );
            }

            $this->SetX($this->defs['PDF_MARGIN_LEFT']);
        }

        $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->GetY(), $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->GetY(), $this->style1);
        // Zeilenumbrücke einfügen
        $rows = array();
        foreach ($ReportsFooter as $_ReportsFooter) {
            $row[] = $_ReportsFooter;
            if ($_ReportsFooter['break'] == 1) {
                $rows[] = $row;
                $row = array();
            }
        }

        // Flussdaten ausgeben
        foreach ($rows as $_rows) {
            $this_y_zeile_top = $this->GetY();
            $line_vertical = array();
            $this_y_zeile = $this->GetY();
            $this_y_zeile_max = $this->GetY();
            $this_x_zeile = $this->GetX();

            foreach ($_rows as $__rows) {

                $_data = null;

                if (trim($__rows['data'][0]) == '') {
                    $_data = ' ';
                } else {
                    $_data = trim($__rows['data'][0]);
                }
                if ($_data == '0000-00-00') {
                    $_data = ' ';
                }

                if ($__rows['revision'] == true) {
                    $_data .= ' (R)';
                    $this->setTextColor(255, 0, 0);
                }

                if ($__rows['key'] == 'examiner' || $__rows['key'] == 'supervision' || $__rows['key'] == 'third_part') {
                    $SignatoryPosition[$__rows['key']]['x'] = $this_x_zeile;
                    $SignatoryPosition[$__rows['key']]['y'] = $this_y_zeile;

                }

                if ($__rows['key'] == 'examiner') {

                  if (isset($Examiner_Stamp_Path) && !empty($Examiner_Stamp_Path) && $this->printversion < 3) {
                      $this->ImageSVG(
                        $file = $Examiner_Stamp_Path,
                        $x = $this_x_zeile * 2.22 + intval($this->Examiner_Stamp_Path['examiner']['examiner_stamp_offset_x']),
                        $y = $this_y_zeile * 1.03 + intval($this->Examiner_Stamp_Path['examiner']['examiner_stamp_offset_y']),
                        $w = intval($this->Examiner_Stamp_Path['examiner']['examiner_stamp_width']),
                        $h = intval($this->Examiner_Stamp_Path['examiner']['examiner_stamp_height']),
                        $link = '',
                        $align = '',
                        $palign = '',
                        $border = 0,
                        $fitonpage = false
                      );
                  }
                }

                if ($__rows['key'] == 'supervision') {
                    if (isset($Supervisor_Stamp_Path) && !empty($Supervisor_Stamp_Path) && $this->printversion < 3)  {

                      $this->ImageSVG(
                        $file = $Supervisor_Stamp_Path,
                        $x = $this_x_zeile * 1.30 + intval($this->Examiner_Stamp_Path['supervision']['supervisor_stamp_offset_x']),
                        $y = $this_y_zeile * 1.04 + intval($this->Examiner_Stamp_Path['supervision']['supervisor_stamp_offset_y']),
                        $w = intval($this->Examiner_Stamp_Path['supervision']['supervisor_stamp_width']),
                        $h = intval($this->Examiner_Stamp_Path['supervision']['supervisor_stamp_height']),
                        $link = '',
                        $align = '',
                        $palign = '',
                        $border = 0,
                        $fitonpage = false
                      );
                    }
                }

                if ($__rows['widthData'] > 0) {
                    $this->SetFont('calibri', 'n', 8);
                    $this->SetCellPadding(1);
                    $this->MultiCell(
                        $__rows['widthData'],
                        $__rows['height'],
                        $_data,
                        0,
                        'L',
                        0,
                        1,
                        $this_x_zeile,
                        $this_y_zeile,
                        true,
                        0,
                        false,
                        true,
                        0,
                        'T',
                        true
                    );
                }

                $this->setTextColor(0, 0, 0);

                // aktuelle Zeilenhöhe speichern wenn diese höher ist als max
                if ($this->GetY() > $this_y_zeile_max) {
                    $this_y_zeile_max = $this->GetY();
                }

                $this_x = $this_x_zeile;
                $this_x_zeile = $this_x_zeile + $__rows['widthData'];

                $line_vertical[] = array(
                    'x_1' => $this_x,
                    'x_2' => $this_x + intval($__rows['widthData']),
                    'right' => trim($__rows['linesdata_right']),
                    'bottom' => trim($__rows['linesdata_bottom']),
                );

            }

            $this->Ln();
            $this->SetY($this_y_zeile_max - ($cellheight / 2));

            $this->SetX($this->defs['PDF_MARGIN_LEFT']);

            $this_y_zeile = $this->GetY();
            $this_y_zeile_max = $this->GetY();
            $this_x_zeile = $this->GetX();

            // Bezeichnungen auslesen
            foreach ($_rows as $__rows) {
                $description = $__rows['description'];
                $this->SetFont('calibri', 'I', 6);
                $this->SetCellPadding(1);
                $description = str_replace('|br|', PHP_EOL, $description);
                $this->MultiCell(
                    $__rows['widthData'],
                    0,
                    $description,
                    0,
                    'L',
                    0,
                    1,
                    $this_x_zeile,
                    $this_y_zeile,
                    true,
                    0,
                    false,
                    true,
                    0,
                    'T',
                    true
                );

                // aktuelle Zeilenhöhe speichern wenn diese höher ist als max
                if ($this->GetY() > $this_y_zeile_max) {
                    $this_y_zeile_max = $this->GetY();
                }

                $this_x_zeile = $this_x_zeile + $__rows['widthData'];
            }

            // Linien
            foreach ($line_vertical as $_line_vertical) {

                // horizontale Linie
                if ($_line_vertical['bottom'] == 1) {
                    $this->Line(
                        $_line_vertical['x_1'],
                        $this_y_zeile_max,
                        $_line_vertical['x_2'],
                        $this_y_zeile_max,
                        $this->style2
                    );
                }

                // vertikale Linie
                if ($_line_vertical['right'] == 1) {
                    $this->Line(
                        $_line_vertical['x_2'],
                        $this_y_zeile_top,
                        $_line_vertical['x_2'],
                        $this_y_zeile_max,
                        $this->style2
                    );
                }
            }

            $this->Ln();
            $this->SetY($this_y_zeile_max);

        }

        // Zusätzliche Textzeilen unter dem Footer aus XML lesen und ausgeben
        $datafooter = $this->xsettings->{'Report' . $Verfahren . 'Pdf'}->additional->datafooter;
        if (isset($datafooter) && is_object($datafooter) && count($datafooter->children()) > 0) {
            $this->setFillColor(127, 0, 0);
            foreach ($datafooter->xpath('element') as $element) {

                $ElementText = trim($element->text);
                $ElementText = str_replace("&amp;amp", "&", $ElementText);

                $data = array(
                    'text' => $ElementText,
                    'size' => intval($element->fontsize),
                    'style' => preg_replace('/[^IBU]+/', '', strtoupper(trim($element->fontstyle))),
                    'width' => floatval(str_replace(',', '.', trim($element->position->w))),
                    'x' => floatval(str_replace(',', '.', trim($element->position->x))),
                    'y' => floatval(str_replace(',', '.', trim($element->position->y))),
                    'w' => floatval(str_replace(',', '.', trim($element->position->w))),
                    'h' => floatval(str_replace(',', '.', trim($element->position->h))),
                    'break' => (bool)(preg_match('/(1|true|x)/i', trim($element->position->ln))),
                );

                $this->setFont('calibri', $data['style'], $data['size']);
                $this->Multicell($data['w'], $data['h'], $data['text'], 0, 'L', false, $data['break'] ? 1 : 0, $data['x'], $data['y'], true, 0, false, true, $data['h'], 'T', true);
                //pr($data);
            }
        }

//        $this->Line($this->defs['PDF_MARGIN_LEFT'], 263.205, $this->defs['QM_CELL_LAYOUT_CLEAR'], 263.205, $this->style1);
        if (isset($this->Signature) && !empty($this->Signature)&& $this->printversion < 3) {
            foreach ($this->Signature as $_key => $_signature) {

                if (!isset($_signature['Sign']['signatory_name'])) {
                    continue;
                }

                $signatory_x = 5;
                if (isset($SignatoryPosition[$_signature['Sign']['signatory_name']]['x'])) {
                    $signatory_x = $SignatoryPosition[$_signature['Sign']['signatory_name']]['x'];
                } elseif (isset($_signature['Sign']['signatory_x'])) {
                    $signatory_x = $_signature['Sign']['signatory_x'];
                }

                if (isset($_signature['Sign']['offset_x'])) {
                    $signatory_x += $_signature['Sign']['offset_x'];
                }

                $sinatory_offset_y = 0;
                $signatory_y = 0;
                if (isset($SignatoryPosition[$_signature['Sign']['signatory_name']]['y'])) {
                    $signatory_y = $SignatoryPosition[$_signature['Sign']['signatory_name']]['y'] - ($_signature['Sign']['height'] / 2);
                }

                if (isset($_signature['Sign']['offset_y'])) {
                    $signatory_y += $_signature['Sign']['offset_y'];
                }

                $this->Image(
                    '@' . base64_decode($_signature['Sign']['sign_output']['image']),
                    $signatory_x, // X
                    $signatory_y, // Y
                    $_signature['Sign']['width'], // Breite
                    $_signature['Sign']['height'], // Höhe
                    'png',
                    $signatory_x . ' ' . $signatory_y . ' ' . $_signature['Sign']['height'], // Link
                    '', // Ausrichtung
                    false, // resize
                    $_signature['Sign']['dpi'], //  dpi
                    '', // 'L', // Ausrichtung auf Seite
                    false, // Bild als Maske
                    false, // Maske oder Testumfluss glaube ich
                    false, // der Rand
                    true, // passt das Bild an Dimensionen bleiben
                    1, // wenn das Bild nicht angezeit werden soll
                    false, // Bild an Seitengröße anpassen
                    false, // Alternativtext
                    0// altervatives Bild
                );
            }
        }
    }
}
