<?php echo $this->Form->input('SortUrl',array('type' => 'hidden','value' => Router::url(array('controller' => 'expeditings','action'=>'sortevent',$this->request->projectvars['VarsArray'][0],$this->request->projectvars['VarsArray'][1],$this->request->projectvars['VarsArray'][2]))));?>
<script type="text/javascript">

$(document).ready(function(){

  var order_table_rows = function(data){

    $(data).each(function(index,value) {
      var tr_sort = "#tr_sort_" + value.rev;
      $(tr_sort).attr("rev",value.rev);
      $(tr_sort).attr("rel",value.rel);
    });
  }

  var stopanimation = function(event, ui) {

    $(".sortable tr").removeClass('blinkme');
    $(".sortable a").removeClass('blinkme');
  }

  var updateRows = function(event, ui) {

    $(".sortable tr").removeClass('blinkme');
    $(".sortable a").removeClass('blinkme');

    $(".sortable").addClass("is_sorting").sortable().sortable( "option", "disabled", true);

    var data = [];
    data.push({name: "ajax_true",value: 1});
    data.push({name : "SortableIDs",value : []});
    data.push({name : "Sequence",value : []});
    data.push({name : "Counter",value : []});


    $(this).find("tr[rel]").each(function(i, e) {
      i++;
      data[1].value.push($(e).attr("rev"));
      data[2].value.push($(e).attr("rel"));
      data[3].value.push(i);
    });

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $("#SortUrl").val(),
      data	: data,
      dataType: "json",
      success: function(data) {
        order_table_rows(data.Sequence);
        json_request_animation(data.Message);
      }
    });

  }

  $(".sortable").sortable({
    items: "> tbody",
    appendTo: "parent",
    helper: "clone",
    cursor: "move",
    update: updateRows
  }).children("tbody").sortable({
    items: "tr:not(tr:first-child)",
    cursor: "move",
    start: stopanimation,
    update: updateRows
  });
});
</script>
