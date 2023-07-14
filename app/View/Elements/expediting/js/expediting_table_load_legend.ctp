<script type="text/javascript">
$(document).ready(function(){

  var load_legend = function(data) {

    var url = $("<?php echo $loc;?>").last().attr("<?php echo $attr;?>");

    $("table.advancetool tr").find("td[class='suppliere_legend']").append($("#JsonSvgLoader").clone());
    $("table.advancetool tr td.suppliere_legend #JsonSvgLoader").removeClass("loader_ani");
    $("table.advancetool tr td.suppliere_legend #JsonSvgLoader").addClass("loader_ani_tablerow");
    $("div.loader_ani_tablerow").show();

    $("table.advancetool tbody tr td.suppliere_legend").each(function(){

      data = new Array();

      data.push({name: "json_true", value: 1});
      data.push({name: "ajax_true", value: 1});
      data.push({name: "CascadeId", value: $(this).attr("data-id")});

      $.ajax({
        type	: "POST",
        cache	: false,
        url		: url,
        data	: data,
        dataType: "json",
        success: function(data) {
          update_legend(data);
        }
      });
    });

  }

  var update_legend = function(data) {

    let el = '<ul class="expediting_legend">';

    el += '<li class="critical tooltip" title="' + data.StatusMessages.PriorityStatus.critical.text + '">';
    el += '<span>' + data.StatusMessages.PriorityStatus.critical.count + '</span>';
    el += '<li>';

    el += '<li class="delayed tooltip" title="' + data.StatusMessages.PriorityStatus.delayed.text + '">';
    el += '<span>' + data.StatusMessages.PriorityStatus.delayed.count + '</span>';
    el += '<li>';

    el += '<li class="plan tooltip" title="' + data.StatusMessages.PriorityStatus.plan.text + '">';
    el += '<span>' + data.StatusMessages.PriorityStatus.plan.count + '</span>';
    el += '<li>';

    el += '<li class="future tooltip" title="' + data.StatusMessages.PriorityStatus.future.text + '">';
    el += '<span>' + data.StatusMessages.PriorityStatus.future.count + '</span>';
    el += '<li>';

    el += '<li class="finished tooltip" title="' + data.StatusMessages.PriorityStatus.finished.text + '">';
    el += '<span>' + data.StatusMessages.PriorityStatus.finished.count + '</span>';
    el += '<li>';

    el += '</ul>';

    $("table.advancetool tr").find("td[class='suppliere_legend'][data-id='" + data.DataId + "']").html("");
    $("table.advancetool tr").find("td[class='suppliere_legend'][data-id='" + data.DataId + "']").append(el);

  }

  load_legend();

});
</script>
