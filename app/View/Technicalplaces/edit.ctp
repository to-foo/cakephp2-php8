<div class="modalarea">
<h2><?php echo __('Edit')?> <?php echo $this->request->data['Technicalplace']['description'];?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if (isset($saveOK) && $saveOK  == 1) {
    echo $this->element('js/ajax_stop_loader');
    //	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
    echo $this->JqueryScripte->DialogClose(1.5);
    //	echo '</div>';
    //	echo $this->JqueryScripte->ModalFunctions();
    return;
}
?>

<?php echo $this->Form->create('Technicalplace', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
        echo $this->ViewData->EditModulData($this->request->data, $settings, $locale, __('Technicalplace'));
    ?>
	</fieldset>
<div class="clear" id="testdiv"></div>
<?php echo $this->Form->end(__('Submit', true));?>
</div>


<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/form_accordion');
echo $this->element('js/form_datefield');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/button_set');
?>
