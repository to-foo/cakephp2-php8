<script>
$(function() {

	$("#container").load("<?php echo Router::url(array('controller'=>'invoices','action'=>'waitings', $this->request->params['pass'][0], $this->request->params['pass'][1]));?>", {
			"ajax_true": 1,
			"mode": 2
		}
	);
});
</script>


