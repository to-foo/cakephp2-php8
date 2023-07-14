<div class="welders index inhalt">
<h2><?php echo __('Overview') . ' ' . $welder_comp['name'];?></h2>
<div class="quicksearch">
<?php
echo $this->element('searching/search_quick_welder',
	array(
		'target_id' => 'id',
		'targedaction' => 'overview',
		'action' => 'quicksearch',
		'minLength' => 2,
		'discription' => __('Name', true)
		)
	);
?>
</div>
<?php
?>
<div class="current_content">
<?php
echo $this->Html->link(__('Weldingcomp info'),array_merge(array('action' => 'weldingcompinfo'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_edit','title' => __('Weldingcomp info',true)));
echo $this->Html->link(__('Add welder',true), array_merge(array('action' => 'add'),$this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_examiners_add','title' => __('Add welder',true)));
echo $this->Html->link(__('Show or upload documents',true),array_merge(array('action' =>'filesweldingcomp'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_file_approval','title' => __('Show or upload documents',true)));
?>
<ul class="listemax">
<?php

    $this->request->projectvars['VarsArray'][14] = $compid;

    $paramssupervisor =  $this->request->projectvars['VarsArray'];
    $paramssupervisor [17] = 1;
        if($welderok == 1){
            echo '<li>';
            echo $this->Html->link(__('Welder'),array_merge(array('action' => 'index'),$this->request->projectvars['VarsArray']),array('class' => 'ajax'));
            echo '</li>';
        }
        if($supervisor == 1){
            echo '<li>';
           echo $this->Html->link(__('Welder supervisor'),array_merge(array('action' => 'index'),$paramssupervisor),array('class' => 'ajax'));
            echo '</li>';
        };
         echo '<li>';
	 echo '</li>';

?>
</ul>



<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/maximize_modal');
?>
</div>
</div>
