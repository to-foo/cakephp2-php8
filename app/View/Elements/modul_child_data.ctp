<div class="clear" id="savediv"></div>
<?php //$url = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'save'),$this->request->projectvars['VarsArray']));?>
<script type="text/javascript">
$(document).ready(function(){
    $(".modulselect").on("change", function() {

      var data = $(this).serializeArray();
      data.push({name: "ajax_true", value: 1});
      var fieldname = $(this).prop("name");
         data.push({name: "field", value: fieldname});
     	$.ajax({
			type	: 'POST',
			cache	: false,
			url		: "<?php echo Router::url(array_merge(array('action'=>'modulchilddata'), $this->request->projectvars['VarsArray']));?>",
			data	: data,
			dataType: 'json',
			success: function(data) {
          ChangeChildFields(data);
				},
		});

    });
return false;

});
function ChangeChildFields(data){

        $.each(data,function(key,value) {
            $("#"+"ReportVtstGenerally"+key).val(value['value']);

            $("#"+"ReportVtstGenerally"+key).trigger($.Event('change'));
            $("#"+"ReportVtstGenerally"+key).prop( "disabled", true );


   });


}
</script>
