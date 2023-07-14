<script type="text/javascript">
$(document).ready(function(){







';


if (
($this->data['Reportnumber']['status'] > 0 && $this->data['Reportnumber']['revision_progress'] == 0 ) ||
$this->data['Reportnumber']['deactive'] > 0 ||
$this->data['Reportnumber']['settled'] > 0 ||
$this->data['Reportnumber']['delete'] > 0) {
    $attribut_array['disabled'] = "disabled";
} else {
    $output .= '
if($(".sortable").length != 0) {
$(".sortable").sortable({
  items: "> tbody",
  appendTo: "parent",
  helper: "clone",
  cursor: "move",
  update: updateRows
}).children("tbody").sortable({
  items: "tr:not(tr:first-child)",
  cursor: "move",
  update: updateRows
});
}';
}

$output .= '



$(".TestingArea a.ajax").click(function() {
$("#container").load($(this).attr("href"), {"ajax_true": 1});
return false;
});

$("a.modal:not(.specialchars), div.images ul li a, button.modal").click(function() {

$("#dialog").dialog(dialogOpts);

$("#dialog").load($(this).attr("href"), {"ajax_true": 1});

$("#dialog").dialog("open");
return false;
});

$("div.checkbox input[type=checkbox]").checkboxradio();

});
</script>
