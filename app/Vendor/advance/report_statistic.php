<?php
App::import('Vendor', '/tcpdf/tcpdf');

class XTCPDF extends TCPDF {

    public $xheadercolor = array(255,255,255);
    public $xdata  = array();
    public $xchanges = array();
    public $xsettings  = array();
    public $Verfahren  = null;
    public $dataArray = array();
    public $xfooterfon;
    public $xfooterfontsize = 8 ;
    public $reportDeleted = false;
    public $defs = null;
    public $reportnumber;
    public $qr = null;
    public $bodyStart = array();
    public $forcePrintHeaders = false;

    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false){

        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

    }

    public function __call($name, $arguments){
        $method = null;
        $_margins = $this->getMargins();
        if (preg_match('/^get(left|right|top|bottom|header|footer)(padding|margin)$/', strtolower($name), $method)) {
            if (strtolower($method[2])=='margin') {
                return $_margins[strtolower($method[1])];
            } elseif ($method[2] == 'padding') {
                if (array_search(strtolower($method[1]), array('left','right','top','bottom')) !== false) {
                    return $_margins['padding_'.strtolower($method[1])];
                }
            }
        }

        return parent::_call($name, $arguments);
    }

    protected function _getFontFormatting($item, $fonts=array()){
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

        foreach ($font as $num=>$fontstyle) {
            if (empty($fontstyle)) {
                $fontstyle = 'n';
            }

            $font[$num] = $fonts[$num][$fontstyle];
        }

        return array($style, $font);
    }


    public function writeSettings($defs){
        $this->defs = $defs;
        $this->qr = null;
        $this->xfooterfon = $defs['PDF_FONT_NAME_MAIN'];
    }

    public function insertPageNumbers(){
    }

    public function Header(){


      //LOGOS
 if(!empty($this->logosvg))$this->ImageSVG($this->logosvg,$this->defs['QM_LOGO_X'],$this->defs['QM_LOGO_Y'],'',$this->defs['QM_LOGO_HEIGHT'],'','','',0,false);

 if(!empty($this->logoadditional))$this->Image($this->logoadditional,$this->defs['QM_LOGO_ADDITIONAL_X'],$this->defs['QM_LOGO_ADDITIONAL_Y'],'',$this->defs['QM_LOGO_ADDITIONAL_HEIGHT'],'','','',0,false);

      //Headline
      $this->setCellPaddings(1, 1, 1, 1);
      $this->SetFillColor(219, 238, 241);
      $this->SetFont('calibri', 'b', 15);
      $this->MultiCell(
              $this->defs['QM_REPORT_HEADLINE_WIDTH'],
              10,
              $this->defs['QM_REPORT_HEADLINE'],
              0,
              'L',
              1,
              1,
              $this->defs['PDF_MARGIN_LEFT'],
              $this->defs['QM_REPORT_HEADLINE_Y'],
              true,
                0,
              false,
              true,
              0,
              'T',
              true
            );

            $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_REPORT_HEADLINE_Y'] + 10, $this->defs['QM_REPORT_HEADLINE_WIDTH']+ $this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_REPORT_HEADLINE_Y']+10, $this->style1);

            $Subheadline = $this->Advance['Scheme']['AdvancesOrder']['AdvancesOrder']['cascade_cat_parent_name'] . ' ' . $this->Advance['Scheme']['AdvancesOrder']['AdvancesOrder']['cascade_cat'] . ' ' . $this->Advance['Scheme']['AdvancesOrder']['AdvancesOrder']['name'];

            //Subheadline
            $this->SetFont('calibri', 'n', 19);
            $this->MultiCell(
                    0,
                    0,
                    'R-TI '.__('Checkliste',true) . " " . $Subheadline,
                    0,
                    'L',
                    0,
                    1,
                    $this->defs['PDF_MARGIN_LEFT'],
                    $this->defs['QM_REPORT_HEADLINE_Y'] + 10,
                    true,
                      0,
                    false,
                    true,
                    0,
                    'T',
                    true
                  );

       $this->Line($this->defs['QM_REPORT_HEADLINE_WIDTH']+ $this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_REPORT_HEADLINE_Y'], $this->defs['QM_REPORT_HEADLINE_WIDTH']+ $this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->style1);

      //headerlinien
      $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_REPORT_HEADLINE_Y'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_REPORT_HEADLINE_Y'], $this->style1);
      $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_REPORT_HEADLINE_Y'], $this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->style1);
      $this->Line($this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_REPORT_HEADLINE_Y'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->style1);

    }

    public function Footer(){

      // Umrandungen
      //oben horizontal
      $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->style1);
      //unten horizontal
      $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style1);
      //links vertikal
      $this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style1);
      //rechts vertikal
      $this->Line($this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style1);
      $this->setCellPaddings(1, 1, 1, 1);
      $this->SetFont('calibri', 'n', 10);
      $this->SetFillColor(219, 238, 241);
      $this->MultiCell(
               $this->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
               7,
               __('Inspektion abgeschlossen'),
               1,
               'L',
               1,
               1,
               $this->defs['PDF_MARGIN_LEFT'],
               $this->defs['QM_START_FOOTER'],
               true,
                 0,
               false,
               true,
               0,
               'T',
               true
             );

 $this->MultiCell(
          $this->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
          14,
          "Inspektion",
          1,
          'L',
          0,
          1,
          $this->defs['PDF_MARGIN_LEFT'],
          $this->defs['QM_START_FOOTER']+7,
          true,
            0,
          false,
          true,
          0,
          'T',
          true
        );



       $this->MultiCell(
               $this->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
               7,
               __('Datum'),
               1,
               'L',
               1,
               1,
               $this->defs['PDF_MARGIN_LEFT']+$this->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
               $this->defs['QM_START_FOOTER'],
               true,
                 0,
               false,
               true,
               0,
               'T',
               true
             );

      $this->MultiCell(
              $this->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
              14,
              "",
              1,
              'L',
              0,
              1,
              $this->defs['PDF_MARGIN_LEFT']+$this->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
              $this->defs['QM_START_FOOTER']+7,
              true,
                0,
              false,
              true,
              0,
              'T',
              true
            );

       $this->MultiCell(
               $this->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
               7,
               __('Unterschrift'),
               1,
               'L',
               1,
               1,
               $this->defs['PDF_MARGIN_LEFT']+($this->defs['QM_CELL_LAYOUT_WIDTH'] / 3)+($this->defs['QM_CELL_LAYOUT_WIDTH'] / 3),
               $this->defs['QM_START_FOOTER'],
               true,
                 0,
               false,
               true,
               0,
               'T',
               true
             );
       $this->MultiCell(
               $this->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
               14,
               "",
               1,
               'L',
               0,
               1,
               $this->defs['PDF_MARGIN_LEFT']+($this->defs['QM_CELL_LAYOUT_WIDTH'] / 3)+($this->defs['QM_CELL_LAYOUT_WIDTH'] / 3),
               $this->defs['QM_START_FOOTER']+7,
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
