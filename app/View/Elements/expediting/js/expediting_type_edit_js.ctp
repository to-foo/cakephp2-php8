<?php echo $this->Form->input('EditExpeditingUrl',array('type' => 'hidden','value' => Router::url(array('controller' => 'expeditings','action'=>'editexpediting',$this->request->projectvars['VarsArray'][0],$this->request->projectvars['VarsArray'][1],$this->request->projectvars['VarsArray'][2]))));?>
<script type="text/javascript">
$(document).ready(function(){

  $('.editabletext').editable(function(value, settings) {

    json_stop_animation();

    data = new Array();

    ThisId = $(this).attr("data-id");
    ThisModel = $(this).attr("data-model");
    ThisField = $(this).attr("data-field");
    Field = "data[" + ThisModel + "][" + ThisField + "]";

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: Field, value: value});
    data.push({name: "data[" + ThisModel + "][id]", value: ThisId});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#EditExpeditingUrl").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        json_request_animation(data.value);
      }
    });

    return(value);

	}, {
    type   : 'textarea',
    submit : 'OK',
    cancel : 'Cancel',
    placeholder : "Edit",

	});
});
</script>
