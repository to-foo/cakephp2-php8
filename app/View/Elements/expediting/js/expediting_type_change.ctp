<?php echo $this->Form->input('ExpeditingTypeInfoUrl',array('type' => 'hidden','value' => Router::url(array('controller' => 'expeditings','action'=>'expeditingstepinfo',$this->request->projectvars['VarsArray'][0],$this->request->projectvars['VarsArray'][1],$this->request->projectvars['VarsArray'][2]))));?>
<script type="text/javascript">
$(document).ready(function(){

  var show_flash_message = function(data) {

    $("div.modalarea div.message_info").remove();

    if(data.length == 0) return false;

    $(data).each(function(index,value){

      if(value.type != "undefined"){

        var elm = '<div id="flash_' + value.type + '" class="message_info"><span class="' + value.type + '">' + value.message + '.</span></div>';
        $(elm).insertAfter("div.modalarea h2").show();

      }
    });
  }

  $("select#OrderExpeditingType").change(function() {

    data = new Array();

    data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: "ExpeditingType", value: $(this).val()});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#ExpeditingTypeInfoUrl").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        show_flash_message(data.FlashMessages);
      }
    });
  });
});
</script>
