<script type="text/javascript">
$(document).ready(function(){

  $("#AjaxSvgLoader").show();

  var url = $("#CurrentEvaluationUrl").val();
  var data = new Array();

  data.push({name: "ajax_true", value: 1});

  $.ajax({
    type	: "POST",
    cache	: false,
    url		: url,
    data	: data,
    success: function(data) {

        $("#EvaluationArea").html(data);
        $("#EvaluationArea").show();
        $("#AjaxSvgLoader").hide();
        $("#dialog").dialog().dialog("close");
  			$("#dialog").empty();
  			$("#dialog").css("overflow","inherit");
  			$("#dialog").dialog("destroy");

    }
  });

});
</script>
