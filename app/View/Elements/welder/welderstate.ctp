<?php
echo $this->Form->input('WelderStateUrl',
	array(
		'type' =>'hidden',
		'label' => false,
		'div' => false,
		'value' => $this->Html->url(array_merge(array('controller' => 'welders','action' => 'welderstate'),$this->request->projectvars['VarsArray'])),
	)
);
echo $this->Form->input('WelderIndexeUrl',
	array(
		'type' =>'hidden',
		'label' => false,
		'div' => false,
		'value' => $this->Html->url(array_merge(array('controller' => 'welders','action' => 'index'),$this->request->projectvars['VarsArray'])),
	)
);
?>
<script type="text/javascript">

function reload() {

    data = new Array();
    data.push({
        name: "ajax_true",
        value: 1
    });
    //   data.push({name: "table_only", value: 1});
		
    $.ajax({
        type: "POST",
        cache: false,
        url: $("#WelderIndexeUrl").val(),
        data: data,
        success: function(data) {
            container = $(data).find("#container_table_summary");
            $("#container_table_summary").html(container);

        }
    });
}

$(document).ready(function() {
    $(".WelderStateLink").click(function() {
        switch ($(this).text()) {
            case "passiv":
                $(this).text("aktiv");
                break;
            case "aktiv":
                $(this).text("passiv");
                break;
        }

        var data = new Array();

        data.push({
            name: "ajax_true",
            value: 1
        });
        data.push({
            name: "welder_id",
            value: $(this).attr('id')
        });


        $.ajax({
            type: "POST",
            cache: false,
            url: $("#WelderStateUrl").val(),
            data: data,
            dataType: "json",
            success: function(data) {
                reload();
                console.debug("success");
                return;
            }

        });
        return false;
    });
});
</script>
