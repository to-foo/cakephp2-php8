<?php
$setting = $evaluationoutput;
$lang = $locale;

$Modelpart  = $this->request->data['Tablenames']['Evaluation'];
$ReportPdf  = $this->request->data['Tablenames']['Pdf'];

$Radiooptions[0] = 'ja';
$Radiooptions[1] = 'nein';
$i = 0;
//pr($dataArray[$Modelpart]['weld']);
foreach ($dataArray[$Modelpart]['weld'] as $weld) {

  if (isset($reportnumber['RevisionValues'][$Modelpart][$weld['id']])) $revtrue = 1;
  else $revtrue = 0;

  $class = null;

  if ($i++ % 2 == 0) $class = ' class="altrow"';
  if (isset($this->request->data['has_result']) && $weld['result'] == 2) $class = ' class="error"';

  echo '<tr rel="'.$weld['id'].'" '.$class.'>';

  echo'<td>';

  // Checkbox für Massenaktion
  echo 	$this->Form->input('check_' . $weld['id'],array(
    'type' => 'checkbox',
    'label' => false,
    'class' => 'check_weld_position',
    'weld-id' => $dataArray[$Modelpart]['id'],
    'value' => $weld['id']
    )
  );

  echo '</td>';

  $xx = 0;

  foreach ($setting as $_key => $_setting) {

    if(trim($_setting->showintable) != 1) continue;
    if(trim($_setting->key) == 'position') continue;

    if (isset($attribut_disabled) && $attribut_disabled == true) $_setting->editable = 0;

    $Field = trim($_setting->key);

    echo '<td class="col_'.$_setting->key.'">';
    echo '<span class="discription_mobil">';
    echo $evalDiscription[$xx] . ': ';
    echo '</span>';
    echo '<p';

    if ($_setting->editable == 1) {

        $Class = 'editable editpos ';
        $ClassArray = array('editable','editpos');


        if(empty($_setting->select->model) && empty($_setting->fieldtype)){

          $Class .= 'editabletext';
          $ClassArray[2] = 'editabletext';

        }

        if(!empty($_setting->select->model)){

          if(isset($this->request->data['Dropdowns'][$Modelpart][$Field]) && !empty($this->request->data['Dropdowns'][$Modelpart][$Field])){

            $Class .= 'editableselect ';
            $ClassArray[2] = 'editableselect';


          } else {

            $Class .= 'editabletext ';
            $ClassArray[2] = 'editabletext';

          }
        }

        if(trim($_setting->fieldtype) == 'radio'){

          $Class .= 'editableradio';
          $ClassArray[2] = 'editableradio';

        } 

        if($this->request->data['Reportnumber']['status'] > 0 && !isset($this->request->data['Reportnumber']['revision_write'])){

          $Class = '';
          $ClassArray = array();
        } 

        echo ' class="' . implode(' ',$ClassArray) . '"';
        echo ' id="editable_'.$_setting->key.'_'.$weld['id'].'"';
        echo ' data-model="' . trim($_setting->model) . '"';
        echo ' data-field="' . trim($_setting->key) . '"';
        echo ' data-type="editpos"';
        echo ' data-id="' . $weld['id'] . '"';
    }

    echo '>';

    if (trim($_setting->fieldtype) == 'radio') {

      $radiooptions = $Radiooptions;

      if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {

        $radiooptions = array();

        foreach ($_setting->radiooption->value as $_radiooptions) {
          array_push($radiooptions, trim($_radiooptions));
        }
      }

      echo $radiooptions[(int)$weld[trim($_setting->key)]];

    } elseif (trim($_setting->fieldtype) == 'checkbox') {

        if ($weld[trim($_setting->key)] == 1) echo 'X';

    } else {

      if ($xx == 0) {

        $weld_pos = null;
        $weld_title = null;

        if (isset($weld['position'])) {
          $weld_pos .= $weld['position'];
          $weld_title = $weld['description']. '/' . $weld['position'];
        }

        $hasmenu = '';

        if (count($dataArray[$Modelpart]['weld']) == 1) {

            if (count($dataArray[$Modelpart]['weld']) > 1) $weld_pos = ' '.$weld['description'];
            elseif (count($dataArray[$Modelpart]['weld']) == 1 && isset($weld['position'])) $weld_pos = ' '.$weld['position'];
            else $weld_pos = ' -----';

            $weld_title = $weld['description'];
            $hasmenu = 'hasmenu1';
        }

        $Field = trim($_setting->key);

        $Class = 'editable editpos ';
        $ClassArray = array('editable','editpos');

        if(empty($value->select->model)){

          $Class .= 'editabletext';
          $ClassArray[2] = 'editabletext';

        } 

        if(!empty($value->select->model)){

          if(isset($this->request->data['Dropdowns'][$Modelpart][$Field]) && !empty($this->request->data['Dropdowns'][$Modelpart][$Field])){

            $Class .= 'editableselect';
            $ClassArray[2] = 'editableselect';

          } else {

            $Class .= 'editabletext';
            $ClassArray[2] = 'editabletext';
          }
        }

        if($this->request->data['Reportnumber']['status'] > 0 && !isset($this->request->data['Reportnumber']['revision_write'])){

          $Class = '';
          $ClassArray = array();

        } 

        echo '<p class="' . implode(' ',$ClassArray) . '" id="weld_editable_welder_no_' . $weld['id'] . '" data-type="editpos" data-model="' . trim($_setting->model) . '" data-field="position" data-id="' . $weld['id'] . '">';
        echo $weld_pos;
        echo '</p> ';

        echo '<span style="white-space: nowrap;">';

        if ($revtrue == 1) {

          $revisionlink  = $this->Html->link('Showrevisions',array(
            'controller' => 'reportnumbers',
            'action' => 'showrevisions',
            $this->request->projectvars['VarsArray'][0],
            $this->request->projectvars['VarsArray'][1],
            $this->request->projectvars['VarsArray'][2],
            $this->request->projectvars['VarsArray'][3],
            $this->request->projectvars['VarsArray'][4],
            $weld['id'],
            0,
            ),
            array_merge(
              array(
                'class'=> 'tooltip_ajax_revision icon icon_edit_revision',
                'title'=> __('Content will load...', true),
                'id' => $Modelpart.'/all',
              )
            )
          );

        } else {
            $revisionlink = '';
        }

        echo $revisionlink;

        if($this->request->data['Reportnumber']['status'] == 0 || isset($this->request->data['Reportnumber']['revision_write'])){

          echo $this->Html->link($weld_pos,array(
            'action' => 'editevalution',
            $this->request->projectvars['projectID'],
            $this->request->projectvars['cascadeID'],
            $this->request->projectvars['orderID'],
            $this->request->projectvars['reportID'],
            $this->request->projectvars['reportnumberID'],
            $weld['id'],
            0
            ),
            array(
              'title' => __('Edit') . ' ' . $weld_title,
              'class' => 'icon_larger icon_edit ajax ' . $hasmenu,
              'rev' =>
                $this->request->projectvars['VarsArray'][0] . '/' .
                $this->request->projectvars['VarsArray'][1] . '/' .
                $this->request->projectvars['VarsArray'][2] . '/' .
                $this->request->projectvars['VarsArray'][3] . '/' .
                $this->request->projectvars['VarsArray'][4] . '/' .
                $weld['id'] . '/' .
                0
            )
          );

          echo $this->Html->link($dataArray[$Modelpart]['discription'],
            array('action' => 'massActions',
              $this->request->projectvars['projectID'],
              $this->request->projectvars['cascadeID'],
              $this->request->projectvars['orderID'],
              $this->request->projectvars['reportID'],
              $this->request->projectvars['reportnumberID'],
              $weld['id'],
              0
            ),
            array(
              'class'=>'small_icon icon_dupli context',
              'title' => __('Duplicate') . ' ' . $weld_title,
              'rev' => 'ReportnumberCheck' . $weld['id'],
              'rel' => $weld['id'],
              'data-url' => 'duplicatevalution',
              'data-mode' => 'position'
            )
          );

          echo $this->Html->link($dataArray[$Modelpart]['discription'],
            array('action' => 'massActions',
              $this->request->projectvars['projectID'],
              $this->request->projectvars['cascadeID'],
              $this->request->projectvars['orderID'],
              $this->request->projectvars['reportID'],
              $this->request->projectvars['reportnumberID'],
              $weld['id'],
              0
            ),
            array(
              'class'=>'small_icon dellink context',
              'title' => __('Delete') . ' ' . $weld_title,
              'rev' => 'ReportnumberCheck' . $weld['id'],
              'rel' => $weld['id'],
              'data-url' => 'deleteevalution',
              'data-mode' => 'position'
            )
          );

        }

        echo '</p>';

        echo '</td><td>';

      } else {

          echo $weld[trim($_setting->key)];

      }
    }

$xx++;
    echo '</td>';

  }

  echo '</tr>';

  $xx++;

}
?>
