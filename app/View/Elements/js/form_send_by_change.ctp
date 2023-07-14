<script type="text/javascript">
$(function() {
    var data = new Array();



    function ChangeDropdownOptions(data) {
        var searchdata = data;
        searchdata.length = 0;

        if (data.ResultCount > 0) {
            $("#SendThisDeviceForm").text(data.ResultCount + " " + "<?php echo __('devices found',true)?>");
            $("#SendThisDeviceForm").removeAttr("disabled");
            //	$("#SendThisStatisticForm").removeAttr("disabled");
        } else {
            $("#SendThisDeviceForm").text("<?php echo __('Non testing reports found',true);?>");
            $("#SendThisDeviceForm").attr("disabled", "disabled");
            //		$("#SendThisStatisticForm").attr("disabled","disabled");
        }

        if (data.ResultCount == 0) {
            $("button#SendThisDeviceForm").hide();
            //	$("button#SendThisStatisticForm").hide();
        } else {
            $("button#SendThisDeviceForm").show();
            //	$("button#SendThisStatisticForm").show();
        }

        //		CssDropdownSearchStop();
    }

    $("#<?php echo $FormId;?> input,#<?php echo $FormId;?> select,#<?php echo $FormId;?> textarea").change(function() {

        var url = $("#<?php echo $FormId;?>").attr("action");
        var data = $("#<?php echo $FormId;?>").serializeArray();

        data.push({
            name: "ajax_true",
            value: 1
        });
        data.push({
            name: "json",
            value: 1
        });

        $.ajax({
            type: "POST",
            cache: false,
            url: url,
            data: data,
            dataType: "json",
            success: function(data) {
                ChangeDropdownOptions(data);
            }
        });

        return false;
    });
});
</script>
