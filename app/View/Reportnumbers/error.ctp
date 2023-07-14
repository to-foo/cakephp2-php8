<div class="modalarea detail">
<h2><?php echo __('Error message'); ?></h2>
<div class="message_wrapper">
<?php 
echo $this->Session->flash('good');
echo $this->Session->flash('bad');
?>
</div>
</div>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>