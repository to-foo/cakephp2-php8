<script type="text/javascript">
$(() => {
    const showElements = _ => {
        $("div#wrapper_pdf_container").show();
        $("div#show_pdf_contaniner").show();
        $("div#show_pdf_container_navi").show();
    };

    const showPdfContainerButton = _ => {
        $("a#show_pdf_contaniner_button").click(_ => {
            $("div#wrapper_pdf_container").hide();
            $("div#show_pdf_contaniner").hide();
            $("div#show_pdf_container_navi").hide();
        });
    };

    const sendMassActionClick = _ => {
        $("#send_mass_action").click(_ => {
            var data = $("#tmpForm").serializeArray();

            data.push({
                name: "ajax_true",
                value: 1
            });
            data.push({
                name: "dialog",
                value: 1
            });

            if ($("#MassSelectType").val() == "weldlabel") {

                json_request_load_animation();

                data.push({
                    name: "showpdf",
                    value: 1
                });

                $.ajax({
                    type: "POST",
                    cache: false,
                    url: $("#tmpForm").attr("action"),
                    data: data,
                    dataType: "json",
                    success: data => {
                        EmbedPDF(data);
                    },
                    complete: data => {
                        json_request_stop_animation();
                        $("#dialog").dialog("close");
                    },
                    statusCode: {
                        404: _ => {
                            alert("page not found");
                            location.reload();
                        }
                    },
                    statusCode: {
                        403: _ => {
                            alert("page blocked");
                            location.reload();
                        }
                    }
                });

            } else {
                $.ajax({
                    type: "POST",
                    cache: false,
                    url: $("#tmpForm").attr("action"),
                    data: data,
                    success: data => {
                        $("#dialog").html(data);
                        $("#dialog").dialog("open");
                        $("#dialog").show();
                        $("#dialog").css('overflow', 'scroll');
                        $("#AjaxSvgLoader").hide();
                    },
                    statusCode: {
                        404: _ => {
                            alert("page not found");
                            location.reload();
                        }
                    },
                    statusCode: {
                        403: _ => {
                            alert("page blocked");
                            location.reload();
                        }
                    }
                });
            }

            return false;
        });
    };

    const sendMassActionQRCodeClick = _ => {
        $("#send_mass_action_qr_code").click( _ => {
            let requrl = '<?php echo $requrl;?>';
            requrl = requrl.replace('reportnumbers/massActions/', '');

            var data = $("#tmpForm").serializeArray();

            data.push({
                name: "ajax_true",
                value: 1
            });
            data.push({
                name: "dialog",
                value: 1
            });
            data.push({
                name: "qrcode",
                value: 1
            });
            data.push({
                name: "requrl",
                value: requrl
            });

            if ($("#MassSelectType").val() == "weldlabel") {

                json_request_load_animation();

                data.push({
                    name: "showpdf",
                    value: 1
                });

                $.ajax({
                    type: "POST",
                    cache: false,
                    url: $("#tmpForm").attr("action"),
                    data: data,
                    dataType: "json",
                    success: data => {
                        EmbedPDF(data);
                    },
                    complete: data => {
                        json_request_stop_animation();
                        $("#dialog").dialog("close");
                    },
                    statusCode: {
                        404: _ => {
                            alert("page not found");
                            location.reload();
                        }
                    },
                    statusCode: {
                        403: _ => {
                            alert("page blocked");
                            location.reload();
                        }
                    }
                });

            } else {

                $.ajax({
                    type: "POST",
                    cache: false,
                    url: $("#tmpForm").attr("action"),
                    data: data,
                    success: data => {
                        $("#dialog").html(data);
                        $("#dialog").dialog("open");
                        $("#dialog").show();
                        $("#dialog").css('overflow', 'scroll');
                        $("#AjaxSvgLoader").hide();
                    },
                    statusCode: {
                        404: _ => {
                            alert("page not found");
                            location.reload();
                        }
                    },
                    statusCode: {
                        403: _ => {
                            alert("page blocked");
                            location.reload();
                        }
                    }
                });

            }

            return false;

        });
    };

    const EmbedPDF = data => {
        var pdf_b64 = "data:application/pdf;base64," + data.string;

        showElements();
        PDFObject.embed(pdf_b64, "div#show_pdf_contaniner");
        showPdfContainerButton();
    };

    sendMassActionClick();
    sendMassActionQRCodeClick();

});
</script>
