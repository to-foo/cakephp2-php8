<?php
$setting = $evaluationoutput;
$lang = $locale;

$Modelpart  = $this->request->data['Tablenames']['Evaluation'];
$ReportPdf  = $this->request->data['Tablenames']['Pdf'];

// Zuerst kommt die Nahtbezeichnung, wenn mehr als ein Nahtbereich vorhanden sind
if (count($dataArray[$Modelpart]['weld']) > 0) {

  $comp_weld = 0;
  $x = 0 ;

  echo '<tr><td class="weldhead">';

  echo	$this->Form->input('weldhead_' . $dataArray[$Modelpart]['id'],
  array(
    'type' => 'checkbox',
    'class' => 'check_weld '.$dataArray[$Modelpart]['id'],
    'label' => false,
    'value' => $dataArray[$Modelpart]['id'],
    'weld-id' => $dataArray[$Modelpart]['id']
  )
);

echo '</td><td class="weldhead" ><span style="white-space: nowrap;">';

echo $this->Html->link($dataArray[$Modelpart]['discription'],
array('action' => 'editevalution',
$this->request->projectvars['projectID'],
$this->request->projectvars['cascadeID'],
$this->request->projectvars['orderID'],
$this->request->projectvars['reportID'],
$this->request->projectvars['reportnumberID'],
$dataArray[$Modelpart]['id'],
'1'
),
array(
'class'=>'icon_larger icon_edit ajax',
'title' => __('Edit') . ' ' . $dataArray[$Modelpart]['discription'],
'rev' =>
$this->request->projectvars['VarsArray'][0] . '/' .
$this->request->projectvars['VarsArray'][1] . '/' .
$this->request->projectvars['VarsArray'][2] . '/' .
$this->request->projectvars['VarsArray'][3] . '/' .
$this->request->projectvars['VarsArray'][4] . '/' .
$dataArray[$Modelpart]['id'] . '/' .
'1'
)
);

$Class = 'editable editweld editabletext';

if($this->request->data['Reportnumber']['status'] > 0) $Class = '';

echo '<p class="' . $Class . '" id="weld_editable_welder_no_' . $key . '" data-type="editweld" data-model="' . $Modelpart . '" data-field="description" data-id="' . $dataArray[$Modelpart]['id'] . '">';

echo $dataArray[$Modelpart]['discription'];
echo '</p> ';

if($this->request->data['Reportnumber']['status'] == 0 || isset($this->request->data['Reportnumber']['revision_write'])){

echo $this->Html->link($dataArray[$Modelpart]['discription'],
array('action' => 'massActions',
$this->request->projectvars['projectID'],
$this->request->projectvars['cascadeID'],
$this->request->projectvars['orderID'],
$this->request->projectvars['reportID'],
$this->request->projectvars['reportnumberID'],
$dataArray[$Modelpart]['id'],
'1'
),
array(
  'class'=>'small_icon icon_dupli context',
  'title' => __('Duplicate') . ' ' . $dataArray[$Modelpart]['discription'],
  'rev' => 'ReportnumberWeldhead' . $dataArray[$Modelpart]['id'],
  'rel' => $dataArray[$Modelpart]['id'],
  'data-url' => 'duplicatevalution',
  'data-mode' => 'weld'
)
);

echo $this->Html->link($dataArray[$Modelpart]['discription'],
array('action' => 'massActions',
$this->request->projectvars['projectID'],
$this->request->projectvars['cascadeID'],
$this->request->projectvars['orderID'],
$this->request->projectvars['reportID'],
$this->request->projectvars['reportnumberID'],
$dataArray[$Modelpart]['id'],
'1'
),
array(
  'class'=>'small_icon dellink context',
  'title' => __('Delete') . ' ' . $dataArray[$Modelpart]['discription'],
  'rev' => 'ReportnumberWeldhead' . $dataArray[$Modelpart]['id'],
  'rel' => $dataArray[$Modelpart]['id'],
  'data-url' => 'deleteevalution',
  'data-mode' => 'weld'
)
);

}

echo $this->Html->link($dataArray[$Modelpart]['discription'],
array('action' => 'printweldlabel',
$this->request->projectvars['projectID'],
$this->request->projectvars['cascadeID'],
$this->request->projectvars['orderID'],
$this->request->projectvars['reportID'],
$this->request->projectvars['reportnumberID'],
$dataArray[$Modelpart]['id'],
'1'
),
array(
  'class'=>'small_icon icon_label showpdflink',
  'title' => __('Print Label') . ' ' . $dataArray[$Modelpart]['discription'],
)
);

if (Configure::read('DevelopmentsEnabled') == true) {
  if (isset($dataArray[$Modelpart]['development'])) {

    if ($dataArray[$Modelpart]['development'] == 0) $developmentClass = array('development_open',__('marked as not processed'));

    if ($dataArray[$Modelpart]['development'] == 1) {

      $developmentClass = array('development_rep',__('marked as error'));

      echo $this->Html->link(__('add progress', true), array(
        'controller' => 'developments',
        'action' => 'progressadd',
        $this->request->projectvars['VarsArray'][0],
        $this->request->projectvars['VarsArray'][1],
        $this->request->projectvars['VarsArray'][2],
        $this->request->projectvars['VarsArray'][3],
        $this->request->projectvars['VarsArray'][4],
        $dataArray[$Modelpart]['id'],
        '1'
      ),
      array(
        'class'=>'addlink modal icon',
        'title' => __('add progress', true)
      )
    );
  }

  if ($dataArray[$Modelpart]['development'] == 2) $developmentClass = array('development_ok',__('marked as processed'));

  echo $this->Html->link(__('examination development', true), array(
    'controller' => 'developments',
    'action' => 'change',
    $this->request->projectvars['VarsArray'][0],
    $this->request->projectvars['VarsArray'][1],
    $this->request->projectvars['VarsArray'][2],
    $this->request->projectvars['VarsArray'][3],
    $this->request->projectvars['VarsArray'][4],
    $dataArray[$Modelpart]['id'],
    '1'
  ),
  array(
    'class'=>'development_evalution icon modal '.$developmentClass[0],
    'id'=>'this_development_'.$dataArray[$Modelpart]['id'],
    'title' => $developmentClass[1]
  )
);
}
}

echo '</span>';
echo '<div class="clear"></div></td>';

$evalDiscriptionCount = count($evalDiscription) + 1;

echo '<td class="no_padding">';
echo '</td>';

foreach ($setting as $_key => $value) {

  if(trim($value->key) == 'description') continue;
  if(trim($value->key) == 'position') continue;
  if(empty($value->output->screen)) continue;
  if(empty($value->showintable)) continue;

  $Field = trim($value->key);

  $ClassArray = array('editable','editweld');

  $Class = 'editable editweld ';

  if(empty($value->select->model) && empty($value->fieldtype)){

    $ClassArray[2] = 'editabletext';
    $Class .= 'editabletext';
  }

  if(!empty($value->select->model)){

    if(isset($this->request->data['Dropdowns'][$Modelpart][$Field]) && !empty($this->request->data['Dropdowns'][$Modelpart][$Field])){

      $ClassArray[2] = 'editableselect';
      $Class .= 'editableselect';

    } else {

      $ClassArray[2] = 'editabletext';
      $Class .= 'editabletext';

    }
  }

  if(trim($value->fieldtype) == 'radio'){

    $ClassArray[2] = 'editableradio';
    $Class .= 'editableradio';

  }

  if(trim($value->editable) != 1) $ClassArray = array();
  if($this->request->data['Reportnumber']['status'] > 0 && !isset($this->request->data['Reportnumber']['revision_write'])) $ClassArray = array();

  echo '<td>';

  if(trim($value->key) == 'error'){

  } else {
    echo '<p class="' . implode(' ',$ClassArray) . '" id="weld_editable_welder_no_' . $key . '" data-type="editweld" data-model="' . trim($value->model) . '" data-field="' . trim($value->key) . '" data-id="' . $dataArray[$Modelpart]['id'] . '"></p>';
  }
  
  echo '</td>';

}


echo '</tr>';

}

?>
