<script type="text/javascript">
$(document).ready(function() {

  function LogoOnOff(data){

    if(data.status == "off") $("#ReportnumberPrintingErrorsForm div.flex_info").find("div[data-id='" + data.DataId + "']").addClass("is_off");
    if(data.status == "on") $("#ReportnumberPrintingErrorsForm div.flex_info").find("div[data-id='" + data.DataId + "']").removeClass("is_off");

  }

  $("div.logo_on_off").click(function() {

    data = new Array();

    data.push({name: "ajax_true", value: 1});
    data.push({name: "json_true", value: 1});
    data.push({name: "logo_on_off", value: $(this).attr("data-id")});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#ReportnumberPrintingErrorsForm").attr("action"),
			data	: data,
      dataType: "json",
			success: function(data) {
          LogoOnOff(data);
				}
			});

		return false;

	});
});
</script>
