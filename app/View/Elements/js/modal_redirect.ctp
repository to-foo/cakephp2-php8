<?php
if (is_array($FormName)) {

  $thisURL = $this->Html->url(array_merge(array('controller' => $FormName['controller'],'action' => $FormName['action']),$FormName['terms']));

} else  {

    $thisURL = $FormName;

}
echo $this->Form->input('AjaxModalRedirect',array('type' => 'hidden','value' => $thisURL));
echo $this->Form->input('ThisRedirectModalTime',array('type' => 'hidden','value' => 1000));
?>
<script type="text/javascript">
$(document).ready(function(){

  function redirectmodal(){
    $("#dialog").load($("#AjaxModalRedirect").val(), {"ajax_true": 1,"dialog": 1})
  }

  setTimeout(redirectmodal, $('#ThisRedirectModalTime').val());

});
</script>
