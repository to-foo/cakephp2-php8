<?php echo $this->Form->input('ExpeditingTypeInfoUrl',array('type' => 'hidden','value' => Router::url(array('controller' => 'expeditings','action'=>'expeditingstepinfo',$this->request->projectvars['VarsArray'][0],$this->request->projectvars['VarsArray'][1],$this->request->projectvars['VarsArray'][2]))));?>
<script type="text/javascript">
$(document).ready(function(){



  $("#SuppliereTestingcomp").change(function() {

 
    var data = $(this).closest('form').serializeArray();

    data.push({name: "ajax_true", value: 1});
    data.push({name: "not_closing", value: 1});

    $.ajax({
            type: "POST",
            cache: false,
            url		: $(this).closest('form').attr("action"),
            data: data,
            success: function(data) {
                $("#dialog").html(data);
                $("#dialog").show();
                $("#AjaxSvgLoader").hide();
            },
            statusCode: {
                404: function() {
                    alert("page not found");
                    location.reload();
                }
            },
            statusCode: {
                403: function() {
                    alert("page blocked");
                    location.reload();
                }
            }
        });

  });
});
</script>
