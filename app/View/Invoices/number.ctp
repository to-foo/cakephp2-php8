<?php 
echo $numbers;
?>
<script>
$(function() {
	$("#number<?php echo $number;?> .number_hide input").val("<?php echo $numbers;?>");	
	$("#number<?php echo $number;?> .price").text("<?php echo $pricetotal;?>");	
});
</script>
