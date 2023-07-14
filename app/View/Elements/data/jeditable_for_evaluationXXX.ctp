
<script type="text/javascript">
$(document).ready(function(){
$("#editable_'.$_setting->key.'_'.$weld['id'].'").editable(" ' . Router::url(array('action'=>'editableUp')) . '", {
  indicator : "<img src=\'img/indicator.gif\'>",
  tooltip   : "'.__('Click to edit').'",
  onblur : "submit",
  placeholder : "&nbsp;",
  submitdata : {
    ajax_true: 1,
    report_number: ' . $weld['reportnumber_id'] . ',
  },
  cssclass : "editables",
  '.$select_for_jeditable.'
  method : "POST",
  callback : function($select_for_jeditable) {

    var select_for_jeditable = $select_for_jeditable;
    var select_array = $(this).attr("id").split("_");

    if(select_array[1] == "result" && select_for_jeditable == "ne"){
      $(this).closest("tr").removeClass();
      $(this).closest("tr").addClass("error");
    }
    if(select_array[1] == "result" && select_for_jeditable != "ne"){
      $(this).closest("tr").removeClass();
    }
  }
});
});
</script>
