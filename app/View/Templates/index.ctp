<h3><?php echo __('Report Template',true);?></h3>
<div class="quicksearch">
<?php echo $this->element('searching/search_quick_project',array('action' => 'quicksearch','minLength' => 1,'discription' => __('Template Name', true)));
?>

</div>
<div class="flex_horizontal">
<div class="item"><?php echo $this->Html->link(__('General Template',true), array('controller' => 'templates', 'action' => 'index',1), array('title' => __('Template',true),'class' => 'icon_start_landingpage'));?></div>
<div class="item"><?php echo $this->Html->link(__('Evaluation Template',true), array('controller' => 'templates', 'action' => 'index',2), array('title' => __('Evaluation template',true),'class' => 'icon_start_landingpage'));?></div>
</div>
<?php
echo $this->element('js/ajax_link_global',array('name' => 'a.icon_template'));
?>
