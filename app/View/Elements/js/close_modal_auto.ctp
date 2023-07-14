<?php echo $this->Form->input('ThisCloseModalTime',array('type' => 'hidden','value' => 3000));?>
<script type="text/javascript">
$(() => {
    closemodal = setTimeout(() => {
        $("#dialog").dialog();
        if ($("#dialog").dialog("isOpen") === true) {
            $("#dialog").dialog("close");
        }
    }, $('#ThisCloseModalTime').val());

    if (closemodal) {
        clearTimeout(closemodal);
    }
});
</script>
