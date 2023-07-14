<div class="modalarea detail">
<h2><?php echo __('Edit'); ?></h2>
<?php $mailadressen = array('torsten.foth@mbq-gmbh.de','chritian.pick@mbq-gmbh.de', 'ralf.naumann@mbq-gmbh.de');?>
<?php echo $this->Form->create('Expediting', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php echo $this->Form->input(__('Equipment',true),array('value'=>'W 6151'));?>
	<?php echo $this->Form->input(__('Description',true),array('value'=>'Schwimmkopf + Materialwechsel'));?>
	<?php echo $this->Form->input(__('Equipment Typ',true),array('value'=>'Schwimmkopf'));?>
	<?php echo $this->Form->input(__('Count',true),array('value'=> 1));?>
	<?php echo $this->Form->input(__('Material',true),array('value'=>''));?>
	<?php echo $this->Form->input(__('Projekt',true),array('value'=>'Projekt'));?> 
	<?php echo $this->Form->input(__('Anfrage fertig',true));?> 
	<?php echo $this->Form->input(__('Bestellnummer',true));?> 
	<?php echo $this->Form->input(__('Lieferant',true));?> 
	<?php echo $this->Form->input(__('Projektnr. Lieferant',true));?> 
	<?php echo $this->Form->input(__('Bestelldatum',true));?> 
	<?php echo $this->Form->input(__('Liefertermin',true));?> 
	<?php echo $this->Form->input(__('Solldatum',true));?> 
	<?php echo $this->Form->input(__('Istdatum',true));?> 
	<?php
	$Points = array(0 => '-','1' => __('Witness Point',true),2 => __('Hold Point',true));	
	echo $this->Form->input('point_', array(
										'legend' => false, 
										'options' => $Points,
										'type' => 'radio',
										'value' => 0
										)
									);	
									
    ?>
	</fieldset>
	<fieldset>
	<?php echo $this->Form->input(__('Verteiler',true),array('type' => 'select', 'multiple' => true, 'options' => $mailadressen));?> 
	</fieldset>
	<fieldset>
	<?php echo $this->Form->textarea(__('Bemerkung',true));?> 
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?> 
