<?php
if(isset($this->request->data['data_area'])) echo $this->Form->input('DataAreaModal',array('value' => $this->request->data['data_area'],'type' => 'hidden'));
if(isset($this->request->data['Expediting']['expediting_type'])) echo $this->Form->input('ExpeditingTypeHidden',array('value' => $this->request->data['Expediting']['expediting_type'],'type' => 'hidden'));
if(isset($ExpeditingAddId))echo $this->Form->input('ExpeditingAddId',array('value' => $ExpeditingAddId,'type' => 'hidden'));


$thisURL = str_replace("%2C", "/", $this->Html->url(array('controller' => $FormName['controller'], 'action' => $FormName['action'].'/'.$FormName['terms'])));
echo $this->Form->input('ReloadContainerID',array('value' => $thisURL,'type' => 'hidden'));
?>
<script type="text/javascript">
$(document).ready(function(){

  var data = new Array();
  data.push({name: "ajax_true", value: 1});

  if(($("#DataAreaModal").length > 0)) data.push({name: "data_area", value: $("#DataAreaModal").val()});
  if(($("#ExpeditingAddId").length > 0)) data.push({name: "expediting_add_id", value: $("#ExpeditingAddId").val()});
  if(($("#ExpeditingTypeHidden").length > 0)) data.push({name: "expediting_type", value: $("#ExpeditingTypeHidden").val()});

  $.ajax({
    type	: "POST",
    cache	: false,
    url		: $("#ReloadContainerID").val(),
    data	: data,
    success: function(data) {
      $("#container").html(data);
      $("#container").show();
      $("#AjaxSvgLoader").hide();
      }
  });
});
</script>
