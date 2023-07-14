<div class="modalarea">
	<h2><?php echo __('Create report').' '.$reportName; ?></h2>
	<?php if(isset($this->request->data['linked']) && $this->request->data['linked'] == 1) { ?>
	<div class="hint">
		<form class="login">
		<fieldset>
		<p><?php echo __('Copy general data from source report');?></p>
		<?php echo $this->Form->input('setGenerally', array('type'=>'radio', 'options'=>array(__('no'), __('yes')), 'value'=> $setGenerally, 'legend'=>' '));?>
		</fieldset>
	</form>
	</div>
	<?php } ?>
<ul class="listemax">
<?php foreach ($testingmethods as $testingmethod): ?>
<li class="icon_discription icon_add"><span></span>
<?php
	$data = array('class' => 'ajax_add_report');
	if(isset($this->request->data['linked']) && $this->request->data['linked'] == 1) $data['class'] .= ' assignlink';

	echo $this->Html->link(h($testingmethod['Testingmethod']['verfahren']),
		array(
			'controller' => 'reportnumbers',
			'action' => 'add',
			$this->request->projectvars['VarsArray'][0],
			$this->request->projectvars['VarsArray'][1],
			$this->request->projectvars['VarsArray'][2],
			$this->request->projectvars['VarsArray'][3],
			$this->request->projectvars['VarsArray'][4],
			$testingmethod['Testingmethod']['id'],
			),
		$data
		);
?></li>
<?php endforeach; ?>
</ul>
</div>
<div class="clear" id="testdiv"></div>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/ajax_link_add_report');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
