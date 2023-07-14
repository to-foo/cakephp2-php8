<script>
$(document).ready(function(){
	$("input.checkbox").button();
});
</script>
<?php
if($attribut_disabled === true) return;
?>
<script>
$(document).ready(function(){

	$('.image input[type="checkbox"]').on('change', function() {
		$('.images li.image input[type="checkbox"]').prop("disabled", true);
		$.ajax({
			'url': '<?php echo $this->Html->url(array_merge(array('controller'=>$this->request->controller, 'action'=>'images'), $VarsArray)); ?>',
			'data': [{'name':'ajax_true', 'value':1}, {'name':'reportnumber_id', 'value':<?php echo $this->request->reportnumberID; ?>}, {'name':'image_id', 'value':$(this).attr('rel')}, {'name':'image_value','value':$(this).prop('checked')?1:0}],
			'type': 'post',
			'dataType':'json',
			'success':function(data) {
				$('.images li.image input[type="checkbox"][rel="'+data.Reportimage.id+'"]').prop('checked',parseInt(data.Reportimage.print));
			},
			'complete': function() {
				$('.images li.image input[type="checkbox"]').prop("disabled", false);
			}
		});
	});
});
</script>
