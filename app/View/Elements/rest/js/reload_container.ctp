<?php
$thisURL = str_replace("%2C", "/", $this->Html->url(array('controller' => $FormName['controller'], 'action' => $FormName['action'].'/'.$FormName['terms'])));
echo $this->Form->input('ReloadContainerID',array('value' => $thisURL,'type' => 'hidden'));
echo $this->Form->input('ReloadContainerTicketId',array('value' => $TicketId,'type' => 'hidden'));

?>
<script type="text/javascript">
$(document).ready(function(){

  $("#AjaxSvgLoader").show();

  var data = new Array();
  data.push({name: "ajax_true", value: 1});
  data.push({name: "ticket_id", value: $("#ReloadContainerTicketId").val()});

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
