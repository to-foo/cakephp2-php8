<div class="modalarea welders index inhalt">


<h2><?php  echo __('Weldingcomp info') . ' ' .$comp['Testingcomp']['name']?></h2>
<!--
<div class="quicksearch">
</div>
-->
<?php echo $this->element('Flash/_messages');?>
<div class="current_content hide_infos_div">
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Testingcomp','ul');?>
<ul class="listemax certificates">
<li><b class="head">
<?php
echo $this->Html->link(__('Weldingcomp approval',true), 'javascript:');
?>
</b>


    <ul>
    <?php

    if(!empty($weldingcompfile)){
var_dump($weldingcompfile['valid_class']);
                echo '<li>';
//		echo $this->Html->link($_device['DeviceCertificate']['certificat'],array_merge(array('action' => 'monitoring'),$this->request->projectvars['VarsArray']),array('class'=>'modal'));
		echo '<ul class="';


		echo $weldingcompfile['valid_class'];
		echo '">';
		echo '<li>';
                 echo '<p class="show_file"><strong>';
		echo $weldingcompfile['description'];
		echo '</strong></p>';
                echo'<br></br>';
		echo '<p class="show_file">';
		echo $weldingcompfile['certification_requested'];
		echo '</p>';
		echo '<p class="show_file">';
		echo $weldingcompfile['time_to_next_certification'];
		echo '</p>';
		echo '<p class="show_file">';
		echo $weldingcompfile['time_to_next_horizon'];
		echo '</p>';
		echo '<p class="show_file">';
		echo $weldingcompfile['next_certification_date'];
		echo '</p>';
                echo '<p class="show_file">';
		echo $weldingcompfile['certified_file_error'];
		echo '</p>';
     echo '</li>';
    }
   ?>
    </ul> </ul>
 <?php echo $this->Html->link(__('Show or upload documents',true),array_merge(array('action' =>'filesweldingcomp'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_file_approval','title' => __('Show or upload documents',true)));?>





</div>
</div>
<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
