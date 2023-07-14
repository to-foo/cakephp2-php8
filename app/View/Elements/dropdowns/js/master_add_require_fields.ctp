<script type="text/javascript">
$(document).ready(function(){

  if($("#ErrorFields").length){

  var data = jQuery.parseJSON($("#ErrorFields").val());

  $.each(data,function(key,value) {
      console.log($("#" + key).attr("class"));
//      console.log(value.val);
      $("fieldset.multiple_field div.select div#" + key).addClass(value.val);
    });

  }
});
</script>
