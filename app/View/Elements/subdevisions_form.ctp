<?php 
return;

if($this->request->TopprojectSubdivisionStandard > 1) {
	$url = $this->Html->url(array(
					'controller' => 'topprojects',
					'action' => 'subdevisions',
					$this->request->projectvars['VarsArray'][0],
					$this->request->projectvars['VarsArray'][1],
					$this->request->projectvars['VarsArray'][2],
					$this->request->projectvars['VarsArray'][3],
					$this->request->projectvars['VarsArray'][4],
					$this->request->projectvars['VarsArray'][5]
					)
				);
	
	echo $this->Form->create('Topproject',array(
											'action' => $url,
											'class' => 'open_close_order subdevisions',
											'id' => 'form_subdivisions'
										)
									);
	echo '<label class="discription">';
	echo __('Aktuelle Aufteilung des Projekts');
	echo '</label>';
	$subdevision_options = Configure::read('SubdivisionValues');
	$attributes = array('legend' => false,'default' => $this->request->TopprojectSubdivision);
	echo '<div class="input radio">';
	echo $this->Form->radio('subdivision', $subdevision_options, $attributes);
	echo '</div>';
	echo '<div class="change_subdevisions" id="change_subdevisions"></div>';
	echo $this->Form->end();
?>
<script>
$(function() {

$("#form_subdivisions input").change(function() {

	var data = $(this).serializeArray();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "data[Topproject][controller]", value: "<?php echo $subdevision_link['controller'];?>"});
	data.push({name: "data[Topproject][action]", value: "<?php echo $subdevision_link['action'];?>"});
	
	$.ajax({
		type	: "POST",
		cache	: true,
		url		: "<?php echo $url;?>",
		data	: data,
		success: function(data) {
			$("#change_subdevisions").html(data);
			$("#change_subdevisions").show();
		}
	});
	return false;
});

});
</script>
<?php
}