<?php
echo $this->Form->create('Searching',array('class' => 'open_close_order','id' => 'ReportsOrdersCase'));
$options = array(
'1' => __('Testing Reports') . ' <span class="count">' . count($SearchFormData['Result']['Reports']) . '</span>',
'3' => __('Statistics') . ' <span class="count">' . count($SearchFormData['Result']['Reports']) . '</span>',
//'2' => __('Orders') . ' <span class="count">' . count($SearchFormData['Result']['Orders']) . '</span>',
);
$attributes = array('legend' => false,'default' => $this->request->data['search_typ']);

echo '<div class="input radio">';
echo $this->Form->radio('search_typ', $options, $attributes);
echo '</div>';

if(isset($this->request->data['history']) && $this->request->data['history'] > 0){
	echo $this->Form->input('history',array(
			'id' => 'history',
			'name' => 'history',
			'type' => 'hidden',
			'value' => $this->request->data['history']
		)
	);
} else {
	echo $this->Form->input('history',array(
			'id' => 'history',
			'name' => 'history',
			'type' => 'hidden',
		)
	);
}
echo $this->Form->input('history',array(
		'id' => 'search_type',
		'name' => 'search_type',
		'type' => 'hidden',
		'value' => $this->request->data['search_typ']
	)
);
?>
<?php echo $this->Form->end(); ?>
<script type="text/javascript">
$(document).ready(function(){

$("#ReportsOrdersCase input").change(function() {
//var data = new Array();
var data = $(this).parents("form").serializeArray();
data.push({name: "search_typ", value: $(this).val()});
data.push({name: "ajax_true", value: 1});
data.push({name: "dialog", value: 1});
url = "<?php echo $this->request->url;?>";
$.ajax({
	type	: "POST",
	cache	: false,
	url		: url,
	data	: data,
	success: function(data) {
		$("#container").html(data);
		$("#container").show();
		}
	});
});
return false;
});
</script>
