<script type="text/javascript">

var refreshTimer;
clearInterval(refreshTimer);

$(document).ready(function() {

  function loadResponse(data){

    $(data).each(function(key,value){
      $(value).each(function(key1,value1){
        $("#" + value1.id).attr("src","data:image/png;base64, " + value1.diagramm)
      });
    });

  }

  function refreshDiagramm(data){

    var diagramm = data[0][0];
    $("#" + diagramm.id).attr("src","data:image/png;base64, " + diagramm.diagramm)

  }

  function refreshFunction(){

    data = new Array();

    data.push({name: "ajax_true", value: 1});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#RefreshUrl").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        refreshDiagramm(data);
      }
    });
  }

//  refreshTimer = setInterval(refreshFunction, $("#RefreshTime").val());


});
</script>
