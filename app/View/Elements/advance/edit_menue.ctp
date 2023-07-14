<ul class="editmenue">
<?php
echo '<li class="active" id="link_advance_tablecontent">';
echo $this->Html->link(__('Advances',true),'javascript:',array('title' => __('Advances data',true),'rel' => 'advance_tablecontent'));
echo '</li>';
echo '<li class="deactive" id="link_advance_diagrammcontent">';
echo $this->Html->link(__('Statistics',true),'javascript:',array('title' => __('Statistics',true),'rel' => 'advance_diagrammcontent'));
echo '</li>';
echo '<li class="deactive" id="link_advance_reportcontent">';
echo $this->Html->link(__('Test reports',true),'javascript:',array('title' => __('Reports',true),'rel' => 'advance_reportcontent'));
echo '</li>';
?>
</ul>
<?php echo $this->element('advance/js/edit_menue_js');?>
