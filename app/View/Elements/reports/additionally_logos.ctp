<?php
if(Configure::check('AdditionalLogoPrintControl') == false) return;
if(Configure::read('AdditionalLogoPrintControl') == false) return;

$ReportArchiv = $this->request->tablenames[3];
$ReportPdf = $this->request->tablenames[5];

if(empty($xml['settings']->{$ReportPdf}->settings->QM_ADDITIONAL_LOGOS)) return;
if(empty($xml['settings']->{$ReportPdf}->settings->QM_ADDITIONAL_LOGOS->LOGO)) return;

$CompanyLogoFolder = Configure::read('company_logo_folder') . AuthComponent::user('testingcomp_id') . DS . 'additional' . DS;

$IsOff = array();

if(
  isset($reportnumber[$ReportArchiv]['data'][$ReportArchiv]['additionaly_logos']['id']) && 
  is_string($reportnumber[$ReportArchiv]['data'][$ReportArchiv]['additionaly_logos']['id'])){
  $IsOff[$reportnumber[$ReportArchiv]['data'][$ReportArchiv]['additionaly_logos']['id']] = $reportnumber[$ReportArchiv]['data'][$ReportArchiv]['additionaly_logos']['id'];
 }
if(isset($reportnumber[$ReportArchiv]['data'][$ReportArchiv]['additionaly_logos']['LOGO']) && is_array($reportnumber[$ReportArchiv]['data'][$ReportArchiv]['additionaly_logos']['LOGO'])){
  foreach ($reportnumber[$ReportArchiv]['data'][$ReportArchiv]['additionaly_logos']['LOGO'] as $key => $value) {

    if(!isset($value['LOGO_NAME'])) continue;

    $IsOff[$value['LOGO_NAME']] = $key;

  }
}

if(isset($reportnumber[$ReportArchiv]['data'][$ReportArchiv]['additionaly_logos']['LOGO']['LOGO_NAME'])){

  $IsOff[$reportnumber[$ReportArchiv]['data'][$ReportArchiv]['additionaly_logos']['LOGO']['LOGO_NAME']] = true;

}

echo '<ul class="hint">';
echo '<li>';
echo __('Enthaltene zusäzliche Logos.',true);
echo '</li>';
echo '<li>';
echo __('Durch das Anklicken kann das Mitdrucken eines Logos aktiviert oder deaktiviert werden.',true);
echo '</li>';
echo '</ul>';
// is_off
echo '<div class="flex_info">';

$x = 0;

foreach ($xml['settings']->{$ReportPdf}->settings->QM_ADDITIONAL_LOGOS->LOGO as $key => $value) {

  $AdditionalLogoPath = $CompanyLogoFolder . trim($value->LOGO_NAME);

  if(isset($IsOff[trim($value->LOGO_NAME)])) $IsOffClass = 'is_off';
  else $IsOffClass = '';

  if(!file_exists($AdditionalLogoPath)) continue;

  $LogoInfo = mime_content_type($AdditionalLogoPath);

  switch($LogoInfo){

    case 'image/svg+xml':

    $data = file_get_contents($AdditionalLogoPath);

    echo '<div class="flex_item logo_on_off ' . $IsOffClass . '" data-id="' . $x . '">';
    echo strstr($data,'<svg');
//    echo '<div class="overlay"></div>';
    echo '</div>';

    break;

    case 'image/png':

    $type = pathinfo($AdditionalLogoPath, PATHINFO_EXTENSION);
    $data = file_get_contents($AdditionalLogoPath);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

    echo '<div class="flex_item logo_on_off ' . $IsOffClass . '" data-id="' . $x . '">';
    echo '<img src="' . $base64 . '" height="100px"/>';
//    echo '<div class="overlay"></div>';
    echo '</div>';

    break;
  }

  $x++;

}

echo '</div>';

if($reportnumber['Reportnumber']['status'] > 0) return;

echo $this->element('reports/js/additionally_logos');

?>
