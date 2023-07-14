<div class="paging">
<?php echo $this->Paginator->next(__('Weitere Einträge werden geladen.',true));?>
</p>
<div class="clear"></div>
<script type="text/javascript">

	$(document).ready(function(){

		$("#table_infinite_sroll").infinitescroll({
			navSelector  	: ".next", 
			nextSelector 	: ".next a",
			itemSelector	: ".infinite_sroll_item", 
			bufferPX		: 50,
			debug		 	: true,
			dataType	 	: "html",
			loading: {
				msgText		: "<?php echo __('Es werden weitere Einträge geladen',true);?>",
				finishedMsg	: "<?php echo __('Keine weiteren Einträge.',true);?>",
				img			: "<?php echo $this->webroot; ?>img/indicator.gif",
				}
			},
			function(arrayOfNewElems){
				setscrollfunctions();
		});

	});
</script>
</div>