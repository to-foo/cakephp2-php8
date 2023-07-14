<div class="modalarea">
	<h2><?php echo __('Create report').' '.$reportName; ?></h2>
	<?php if(isset($this->request->data['linked']) && $this->request->data['linked'] == 1) { ?>
	<div class="hint">
		<?php
			echo $this->Form->input('setGenerally', array('type'=>'radio', 'options'=>array(__('no'), __('yes')), 'value'=>1, 'legend'=>__('Copy general data from source report')));
		?>
	</div>
	<?php } ?>
<ul class="listemax">
<?php foreach ($testingmethods as $testingmethod): ?>
<li class="icon_discription icon_add"><span></span>
<?php
	$data = array('class' => 'ajax');
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
<script type="text/javascript">
$(document).ready(function() {
	$('.modalarea a.assignlink').on('click', function() {
		$(this).data('setGenerally', $('.modalarea .hint input:checked').val());
	});
});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>