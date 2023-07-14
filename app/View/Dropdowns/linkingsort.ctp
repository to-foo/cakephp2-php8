<div class="modalarea">

	<h2><?php echo __('Please select the dropdown for transfering values to the chosen dropdown'); ?></h2>

	<ul class="listemax cascade" id="">
    <?php 
	foreach($all_order_testingmethods as $_key => $_all_order_testingmethods){
		echo '<li>';
		echo $this->Html->Link($all_id_testingmethods[$_key],array(''));
		echo '<ul>';
		$this->request->projectvars['VarsArray'][8] = $_key;
		foreach($_all_order_testingmethods as $__key => $__all_order_testingmethods){
			$this->request->projectvars['VarsArray'][9] = $__key;
			echo '<li>';
			echo $this->Html->Link($__all_order_testingmethods,array_merge(array('action'=>'linking'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal'));
			echo '</li>';
		}
		echo '</ul>';
		echo '</li>';
	}
	?>
    </ul>
</div>
<div class="clear" id="testdiv"></div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#dialog').scrollTop(0);
		
		$('.modalarea .cascade > li > a').click(function(e) {
			e.stopImmediatePropagation();
			e.stopPropagation();
			e.preventDefault();
		});
	});
</script>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>