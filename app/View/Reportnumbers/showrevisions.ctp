<div class="revisionhistory modalarea detail">
<h2><?php echo __('Revision History')?></h2>

<div class="current_content">
<?php    
echo $this->Html->link(
  __('print revisions'),
  array_merge(array('controller' => 'reportnumbers','action' => 'printrevisions'), $this->request->projectvars['VarsArray']),
    array_merge(
      array(
        'class'=> 'print round',
        'target'=> '_blank'
      )
    )
);

foreach ($revision as $key => $value) {

    echo' <h3>'.__('Revision').' '.$key.'</h3>';

    echo '<ul class="listemax ">';

    foreach ($value as $_key => $_value) {

        echo $this->element('revision/show_revision',array('data' => $_value));
        echo $this->element('revision/show_revision_row',array('data' => $_value));

/*
        $arraylabel = array(
            __('added weld', true),
            __('edited weld', true),
            __('deleted weld', true),
            __('duplicated weld', true),
            __('added file', true),
            __('deleted file', true),
            __('file description changed', true)
        );


        $revmodel = $_value['Revision']['model'];
        $revrow = $_value['Revision']['row'];
        $fielddiscript = '';

        if(isset($settings->$revmodel->$revrow->discription->$locale)) $fielddiscript = trim($settings->$revmodel->$revrow->discription->$locale);

        echo __('Field') . ': '.$fielddiscript.'<br>';
*/ 

    }

    echo '</ul>';
}
?>
</div>
</div>
