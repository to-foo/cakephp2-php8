<div class="inhalt">
<h2><?php echo __('Ticket Manager',true); ?></h2>
<?php echo $this->element('rest/ticket_menue'); ?>
<?php echo '<div class="areas" id="main_area">'; ?>
<div class="rest_container">
<?php
echo $this->Form->input('TicketVarsArray',array('type' => 'hidden','value' => implode('/',$this->request->projectvars['VarsArray'])));
echo $this->Form->create('Ticketscan');
echo $this->element('rest/barcode_scanner');
echo $this->Form->end();
?>
</div>
</div>
<?php echo $this->element('rest/ticket_data_area');?>

<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
echo $this->element('js/show_pdf_link');
?>
