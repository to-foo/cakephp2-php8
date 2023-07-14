<h3><?php echo __('Overview'); ?></h3>
<?php echo $this->element('Flash/_messages');?>
<div class="quicksearch">
<?php
echo $this->Html->link(__('Back',true),
  array(
    'controller' => 'expeditings',
    'action' => 'start'
  ),
  array(
    'class' => 'icon backlink',
    'title' => __('Back',true)
  )
);
?>
</div>

<div class="users index inhalt">

  <?php echo $this->Form->create('Expediting', array('class' => 'login')); ?>
  	<fieldset>
  	<?php echo $this->Form->input('description');?>
    <?php echo $this->Form->input('cascade_id',array('type' => 'hidden'));?>
    <?php echo $this->Form->input('topproject_id',array('type' => 'hidden'));?>
  	</fieldset>
  <?php echo $this->Form->end(__('Submit')); ?>

</div>

<?php

echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_link_landingpage_global',array('name' => 'a.backlink'));
echo $this->element('js/ajax_link_landingpage_global',array('name' => 'a.addexpeditingobject'));
echo $this->element('js/form_send_landing_large_window',array('FormId' => 'ExpeditingAddexpeditingobjectForm'));
?>
