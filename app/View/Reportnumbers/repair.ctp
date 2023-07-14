<div class="modalarea">
<h2><?php echo h(__('Weld repair tracking')); ?> <?php echo $this->Pdf->ConstructReportNamePdf($reportnumbers,3,true);?></h2>
<div class="message_wrapper">
<?php
echo $this->Session->flash('good');
echo $this->Session->flash('bad');
?>
</div>
<div class="hint"><p>
<?php
if(count($reportnumbers['Repairs']) == 0){
echo __('Alle Reparatuen wurden ausgeführt oder sind in Arbeit.',true);
echo '</div></div>';
return;
}

if(isset($FormName)){
	echo $this->Html->Link(__('Show repair report',true),
		array_merge(
			array(
			'controller' => $FormName['controller'],
			'action' => $FormName['action']
			),explode('/',$FormName['terms'])
			),
			array(
				'class' => 'round ajax',
				'title' => __('Show repair report',true)
				)
			);

	echo '</div></div>';
	return;
}
?>
<?php echo __('Soll von diesem Prüfbericht ein Reparaturbericht erstellt werden:') . ' ' . $this->Pdf->ConstructReportName($reportnumbers);?>
<?php echo '<br>' . __('Der Reparaturbericht enthält die folgenden Prüfbereiche.',true);?>
</p>
<ul>
<?php
foreach($reportnumbers['Repairs'] as $_key => $_data){
	echo '<li>';
	echo $_data['mistake'][$this->request->tablenames[2]]['description'] . ' ';
	echo $_data['mistake'][$this->request->tablenames[2]]['position'] . ' ';

	if(isset($_data['history'])){
		foreach($_data['history'] as $__key => $__data){
			echo ' > ' . $__data[$this->request->tablenames[2]]['description'] . ' ';
			echo $__data[$this->request->tablenames[2]]['position'];
		}
	}

	echo '</li>';
}
?>
</ul>
</div>
<?php
if(isset($saveOK) && $saveOK  == 1){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo '</div>';
	return;
	}

echo $this->Form->create('Reportnumber', array('class' => 'dialogform'));
echo '<fieldset>';
echo $this->Form->input('is_repair',array('type' => 'hidden', 'value' => 1));
echo '</fieldset>';
echo $this->Form->end(__('Reparaturbericht erstellen'));?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ReportnumberRepairForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
?>
