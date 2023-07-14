<?php
echo $this->Form->input('FastSaveUrl',array('type' => 'hidden','value' => $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'savejson'),$this->request->projectvars['VarsArray']))));
echo $this->Form->input('FastSaveController',array('type' => 'hidden','value' => $this->request->controller));
echo $this->Form->input('FastSaveAction',array('type' => 'hidden','value' => $this->request->action));
?>
<script type="text/javascript">
$(document).ready(function() {

    $('form.editreport').on('keyup keypress', function(e) {

        activeObj = document.activeElement;

        if (activeObj.tagName == "TEXTAREA") {
            // do nothing
        } else {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        }
    });

    CollectFormData = function() {

        var orginal_data = {
            'controller': $("#FastSaveController").val(),
            'action': $("#FastSaveAction").val()
        };

        $("form.editreport input:not(.hide_box), form.editreport select, form.editreport textarea").each(
            function() {


                if ($(this).closest("div").hasClass("radio")) {

                    if ($(this).attr("checked") == "checked") {
                        orginal_data["orginal_" + $(this).attr("name")] = $(this).val();
                    } else {
                        orginal_data["orginal_" + $(this).attr("name")] = 0;
                    }

                }

                if ($(this).closest("div").hasClass("number") || $(this).closest("div").hasClass(
                    "text") || $(this).closest("div").hasClass("select") || $(this).closest("div")
                    .hasClass("textarea")) {

                    var active_div_width = $(this).closest("div").width() + 20;
                    orginal_data["orginal_" + $(this).attr("name")] = $(this).val();

                }

                if ($(this).closest("div").hasClass("checkbox")) {

                    var active_div_width = $(this).closest("div").width() + 20;

                    if ($(this).attr("checked") === undefined) {
                        orginal_data["orginal_" + $(this).attr("name")] = 0;
                    } else {
                        orginal_data["orginal_" + $(this).attr("name")] = 1;
                    }
                }
            });

        return orginal_data;
    }

    $('#container').off('change', '.dependency, .customValue');

    $('#container').on('change', '.dependency, .customValue', function(event) {

        $(this).closest("div").find("label").css("padding-right", "1.5em");
        $(this).closest("div").find("label").css("background-image", "url(img/indicator.gif)");
        $(this).closest("div").find("label").css("background-repeat", "no-repeat");
        $(this).closest("div").find("label").css("background-position", "center right");
        $(this).closest("div").find("label").css("background-size", "auto 0.9em");

        var val = $(this).val();
        var this_id = $(this).attr("id");
        var this_submit_id = $(this).attr("id") + "_submit";

        $("<span id=\"" + this_submit_id + "\"></span>").insertAfter("#savediv");

        var url = $("#FastSaveUrl").val();
        var data = new Array();

        data.push({
            name: "ajax_true",
            value: 1
        });
        data.push({
            name: "this_id",
            value: this_id
        });
        data.push({
            name: $(this).attr("name"),
            value: val
        });

        if ($(this).hasClass("edit_after_closing")) {
            data.push({
                name: "edit_after_closing",
                value: 1
            });
        }

        $.each(orginal_data, function(key, value) {
            data.push({
                name: key,
                value: value
            });
        });

        $.ajax({
            type: "POST",
            cache: false,
            url: url,
            data: data,
            success: function(data) {
                $("#" + this_submit_id).html(data);
                $("#" + this_submit_id).show();
            }
        });

        $(this).closest("div").find("label").css("background-image", "none");
        return false;
    });

    $("form.editreport input:not(.hide_box), form.editreport select, form.editreport textarea").change(
    function() {

        orginal_data = CollectFormData();

        $("#content .edit a.print").data('prevent', 1);

        if (!$(this).closest("div").hasClass("radio")) {
            $(this).attr({
                "disabled": "disabled",
                "rel": "saving"
            });
            $(this).closest("div").css("background-image", "url(img/indicator.gif)");
            $(this).closest("div").css("background-repeat", "no-repeat");
            $(this).closest("div").css("background-position", "95% 10%");
            $(this).closest("div").css("background-size", "auto 0.9em");
        }
        if ($(this).closest("div").hasClass("radio")) {
            $(this).closest("div").find("legend").css("padding-right", "2em");
            $(this).closest("div").find("legend").css("background-image", "url(img/indicator.gif)");
            $(this).closest("div").find("legend").css("background-repeat", "no-repeat");
            $(this).closest("div").find("legend").css("background-position", "center right");
            $(this).closest("div").find("legend").css("background-size", "auto 0.9em");
        }

        if (!$(this).closest("div").hasClass("radio")) {
            $("label.ui-button").css("background-image", "none");
        }

        var val = $(this).val();
        var this_id = $(this).attr("id");
        var this_submit_id = $(this).attr("id") + "_submit";
        var name = $(this).attr("name");

        $("<span id=\"" + this_submit_id + "\"></span>").insertAfter("#" + this_id);

        // Bei Multiselectfeldern nicht das Select als Quelle verwenden, wenn das Event vom Textfeld kommt.
        if ($(this).closest("div").hasClass("select") && $(this).attr("multiple") == undefined) {
            if ($(this).is('select')) {
                if ($(this).find("option:selected").attr("rel") == "custom") {
                    $(this).closest("div").find("label").css("background-image", "none");

                    return false;
                }
                val = $(this).find('option:selected').text();
            } else {
                // In den Eingabefeldern verwendete Inhalte in der Multiselectbox ausw�hlen
                $(this).siblings('select').val($(this).val().split(/[\r\n ,;]+/));
            }
        }

        if ($(this).closest("div").hasClass("checkbox")) {
            if ($(this).attr("checked") === undefined) {
                val = 0;
            } else {
                val = 1;
            }
        }

        if ($(this).attr("multiple") != undefined) {

            if ($(this).attr("id") == "ReportRtEvaluationError") {

            } else {

                if (val.length > 0) {
                    input = "";
                    $(val).each(function(key, value) {
                        if (value.length > 0) {
                            text = $("#" + this_id + " option[value=" + value + "]").text();
                            if (text.length > 0) {
                                input += text + "\n";
                            }
                        }
                    });
                } else {
                    input = null;
                }

                val = input;
            }
        }

        var url = $("#FastSaveUrl").val();
        var data = new Array();

        data.push({
            name: "ajax_true",
            value: 1
        });
        data.push({
            name: "this_id",
            value: this_id
        });
        data.push({
            name: name,
            value: val
        });

        $.each(orginal_data, function(key, value) {
            data.push({
                name: key,
                value: value
            });
        });

        if ($(this).hasClass("edit_after_closing")) {
            data.push({
                name: "edit_after_closing",
                value: 1
            });
        }

        $.ajax({
            type: "POST",
            cache: false,
            url: url,
            data: data,
            dataType: "json",
            success: function(data) {
                console.log(data);
            },
            complete: function(data) {
                $("#content .edit a.print").data('prevent', 0);
            }
        });

        $(this).closest("div").css("background-image", "none");
        $(this).closest("div").find("label").css("background-image", "none");
        $(this).css("background-color", "#fff");
        return false;
    });
});
</script>
