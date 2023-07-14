<?php

$ReportPdf  = $this->request->data['Tablenames']['Pdf'];
$ReportEvaluation  = $this->request->data['Tablenames']['Evaluation'];
$setting = $evaluationoutput;
$lang = $locale;
echo $this->element('data/massfunction_for_evaluation_start');

if(empty($this->request->data['Welds'])) return;

//if ($reportnumber['Reportnumber']['repair_for'] == 0 || Configure::read('RepairAddNewEvaluation') == false) {

  $evalHeaders = '';
  $evalDiscription = array();

  $evalHeadersEdit = array();
  $evalHeadersEdit[] = '<td></td>';
  $evalHeadersEdit[] = '<td></td>';

  foreach ($setting as $_setting) {

    if(trim($_setting->key) == 'position') continue;

    if (trim($_setting->showintable) == 1) {

          if (isset($replaceheaderdata)&&($replaceheaderdata == '1' || $replaceheaderdata == 'true') && isset($_setting->headerfrom) &&  isset($_setting->headerfrom->key)&&isset($_setting->headerfrom->model)) {

            $headmodel = trim($_setting->headerfrom->model);
            $headkey =  trim($_setting->headerfrom->key);

            if(!empty($reportnumber[$headmodel][$headkey])) {

              $evalHeaders.='<th>'.$reportnumber[$headmodel][$headkey].($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null).'</th>';

            } else {

              $evalHeaders .= '<th>'.$_setting->discription->$lang.($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null).'</th>';

            }
          } else {

            $evalHeaders .= '<th>'.$_setting->discription->$lang.($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null).'</th>';

          }
        if (trim($_setting->key) == 'description') {

            $evalHeaders .= '<th class="no_padding"></th>';
            $evalHeadersEdit[] = '<td></td>';

        } else {

          $Field = trim($_setting->key);

          $ClassArray = array('editable','editall');
          $Class = 'editable editall ';

          if(empty($_setting->select->model) && empty($_setting->fieldtype)){
            $ClassArray[2] = 'editabletext';
            $Class .= 'editabletext';
          }

          if(!empty($_setting->select->model)){

            if(isset($this->request->data['Dropdowns'][$ReportEvaluation][$Field]) && !empty($this->request->data['Dropdowns'][$ReportEvaluation][$Field])){

              $Class .= 'editableselect';
              $ClassArray[2] = 'editableselect';

            } else {

              $Class .= 'editabletext';
              $ClassArray[2] = 'editabletext';

            }
          }

          if(trim($_setting->fieldtype) == 'radio'){

            $Class .= 'editableradio';
            $ClassArray[2] = 'editableradio';

          }

          if(trim($_setting->editable) != 1) $ClassArray = array();
          if($this->request->data['Reportnumber']['status'] > 0 && !isset($this->request->data['Reportnumber']['revision_write'])) $ClassArray = array();

          $EvalId =  0;

          if(isset($reportnumber['Welds'][0][$ReportEvaluation]['id'])) $EvalId =  $reportnumber['Welds'][0][$ReportEvaluation]['id'];

          if(trim($_setting->key) == 'error'){
            $evalHeadersEdit[] = '<td><p></p></td>';
          } else {
            $evalHeadersEdit[] = '<td><p class="' .  implode(' ',$ClassArray)  . '" id="editable_'.$_setting->key.'_0" data-model="'.$_setting->model.'" data-field="'.$_setting->key.'" data-id="'.$EvalId.'" data-type="editall"></p></td>';
          }
        }

        $evalDiscription[] = $_setting->discription->$lang.($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null);

    }
  }

  if($this->request->data['Reportnumber']['status'] == 0) $Sortable = 'sortable';
  else $Sortable = '';

  echo '<table cellpadding = "0" cellspacing = "0" class="advancetool editable  evaluation_table '.$Sortable.'">';

  echo $this->element('data/evaluation_data_table_head',array('evalHeaders'  => $evalHeaders,'evalDiscription'  => $evalDiscription));

  // Spalte für die Massenbearbeitung
  foreach ($evalHeadersEdit as $key => $value) echo $value;

  $Modelpart  = $this->request->data['Tablenames']['Evaluation'];;

  if(!empty($settings->$Modelpart->position)) echo $this->element('data/evaluation_data_table_body',array('evalDiscription'  => $evalDiscription));
  if(empty($settings->$Modelpart->position)) echo $this->element('data/evaluation_data_table_body_no_position',array('evalDiscription'  => $evalDiscription));

  echo '</table>';
 
// }

echo $this->element('data/massfunction_for_evaluation_end');

$UrlMassAction = $this->Html->url(array(
  'controller' => 'reportnumbers',
  'action' => 'massActions',
  $this->request->projectvars['VarsArray'][0],
  $this->request->projectvars['VarsArray'][1],
  $this->request->projectvars['VarsArray'][2],
  $this->request->projectvars['VarsArray'][3],
  $this->request->projectvars['VarsArray'][4],
  $this->request->projectvars['VarsArray'][5]
  )
);

echo $this->Form->input('ReportnumberId',array('type' => 'hidden','value' => $this->request->data['Reportnumber']['id']));
echo $this->Form->input('UrlEditTableUp',array('type' => 'hidden','value' => Router::url(array_merge(array('action'=>'editableUp'),$this->request->projectvars['VarsArray']))));
echo $this->Form->input('UrlMassAction',array('type' => 'hidden','value' => Router::url(array_merge(array('action'=>'massActions'),$this->request->projectvars['VarsArray']))));


echo $this->element('data/js/evaluation_edit_functions');
echo $this->element('data/js/evaluation_edit_contexmenue');
echo $this->element('data/js/evaluation_edit_editable');
echo $this->element('data/js/evaluation_edit_revision');
?>
