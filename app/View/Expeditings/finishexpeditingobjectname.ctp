<h3><?php echo __('Overview'); ?></h3>
<?php echo $this->element('Flash/_messages');?>
<div class="quicksearch">
</div>

<div class="users index inhalt">

</div>

<?php
if(isset($RequestUrl)) echo $this->element('js/ajax_redirect',array('url' => $RequestUrl));

echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
