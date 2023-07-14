<div class="modalarea detail">
<h2><?php echo __('Add new Technical Place here'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($RequestUrl)){
  echo $this->element('js/ajax_redirect',array('url' => $RequestUrl));
	echo '</div>';
	return;
	}
?>
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
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingAddexpeditingobjectForm'));
?>
