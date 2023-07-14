<div class="tooltip detail">
<h3><?php echo __('Revision History')?></h3>
<h4><?php echo __('Number of Revisions').': '. $revision [0] ['Reportnumber'] ['revision']?></h4>
<div class="current_content">
<?php



foreach ($revision as $_key => $_revision) {

  $revmodel = $_revision ['Revision'] ['model'];
  $revrow = $_revision['Revision']['row'];

  if (isset($_revision ['Revision']['row']) && !empty($_revision ['Revision']['row'])) $revrow = $_revision ['Revision']['row'];

  if (trim($settings->$revmodel->$revrow->fieldtype == 'radio')) {
      $_revision ['Revision']['last_value'] = trim($settings->$revmodel->$revrow->radiooption->value[intval($_revision ['Revision']['last_value'])]);
      $_revision ['Revision']['this_value'] = trim($settings->$revmodel->$revrow->radiooption->value[intval($_revision ['Revision']['this_value'])]);
  }

  if(!isset($field)) continue;

  if($field == 'all'){

    if(!isset($_revision['Revision']['action'])) continue;

    $arraylabel = array(
      __('added', true),
      __('edited', true),
      __('deleted', true),
      __('duplicated', true),
      __('added file', true),
      __('deleted file', true),
      __('file description changed', true)
    );

    $output = null;
    $output .= __('Revision', true).': ' .$_revision ['Revision']['revision'].'<br>';
//pr($_revision);


//    if($_revision['Revision']['action'] == 'addevaluation') $output .= $arraylabel[0] . ': '. trim($settings->$revmodel->$revrow->discription->$locale) . ' = ' . $_revision['Revision']['this_value'] . '<br>';
//    if($_revision['Revision']['action'] == 'editevaluation') $output .= $arraylabel[1] . ': ' . trim($settings->$revmodel->$revrow->discription->$locale) . ' = ' . $_revision['Revision']['this_value'] . '<br>';

    if($_revision['Revision']['action'] == 'addevaluation'){
      $output .= $arraylabel[0] . ': ';
      $output .= trim($settings->$revmodel->$revrow->discription->$locale) . ' = ' . $_revision['Revision']['this_value'] . '<br>';
    }
    if($_revision['Revision']['action'] == 'editevaluation'){
      $output .= $arraylabel[1] . ': ';
      $output .= trim($settings->$revmodel->$revrow->discription->$locale) . '<br>';
      $output .= __('Last value',true) .': ' . $_revision ['Revision']['last_value'] . '<br>';
      $output .= __('New value',true) .': ' . $_revision ['Revision']['this_value'] . '<br>';
//      $output .= trim($settings->$revmodel->$revrow->discription->$locale) . ' = ' . $_revision['Revision']['this_value'] . '<br>';
    }
    if($_revision ['Revision']['action'] == 'deleteevalution/weld' || $_revision ['Revision']['action'] == 'mass_action/deleteevalution'){
      $output .= $arraylabel[2] . ': ' . $_revision ['Revision']['last_value'];
    }
    if($_revision ['Revision']['action'] == 'duplicatevalution/weld' || $_revision ['Revision']['action'] == 'mass_action/duplicatevalution'){
      $output .= $arraylabel[3]. ': ' . $_revision ['Revision']['last_value'];
    }
    if($_revision ['Revision']['action'] == 'images/addimage' || $_revision ['Revision']['action'] == 'files/addfile'){
      $output .= $arraylabel[4]. ': ' . $_revision ['Revision']['last_value'];
    }
    if($_revision ['Revision']['action'] == 'images/delimage' ||  $_revision ['Revision']['action'] == 'files/delfile'){
      $output .= $arraylabel[5].': ' . $_revision ['Revision']['last_value'];
    }
    if($_revision ['Revision']['action'] == 'images/discription'){
      $output .= $arraylabel[6]. ' from '.$_revision ['Revision']['last_value']. 'to '.$_revision ['Revision']['this_value'].'<br>';
    }


    $output .= __('Date', true).': '.$_revision ['Revision']['created'].'<br>';
    $output .= __('User', true).': '.$_revision ['Revision']['user'];

    echo '<dl>';
    echo $output;
    echo '<br>';
    echo '</dl>';

  } else {

    $output = null;

    if (isset($_revision ['Revision']['revision'])) {
        $output .= __('Revision', true).': ' .$_revision ['Revision']['revision'].'<br>';
    }
    if (isset($_revision ['Revision']['modified'])) {
        $output .= __('Date', true).': ' .$_revision ['Revision']['modified'].'<br>';
    }
    if (isset($_revision ['Revision']['last_value'])) {
        $output .= __('Last Value', true).': '.$_revision ['Revision']['last_value'].'<br>';
    }
    if (isset($_revision ['Revision']['this_value'])) {
        $output .= __('New Value', true).': '.$_revision ['Revision']['this_value'].'<br>';
    }
    if (isset($_revision ['Revision']['user'])) {
        $output .= __('User', true).': '.$_revision ['Revision']['user'];
    }

    echo '<dl>';
    echo $output;
    echo '<br>';
    echo '</dl>';

  }
}

?>
</div>
</div>
