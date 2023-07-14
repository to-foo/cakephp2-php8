<div class="modalarea detail">
<h2><?php echo __('send Report'); ?></h2>
    <div class="clear edit ">
</div>
    <div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($close)&& $close == 1) {echo $this->JqueryScripte->DialogClose();}

else{
    echo $this->Form->create('Reportnumber', array('class' => 'dialogform'));


    echo '<fieldset class="multiple_field">';
    
    echo $this->Form->input('Emails',array(
         'label' => __('E-Mail',true),
				'multiple' => 'multiple',
				'empty' => ' ',
                                
                                
    ));   
    
    echo '</fieldset>';
    
    echo '<fieldset>
    <div class="textarea">';
    echo '<label>'.__('Remark').'</label>';
        echo $this->Form->textarea('Remark',array(
         'label' => __('Remarks',true),
         ));  
    echo'</div>';
    echo'</fieldset>';


    echo $this->Form->end(__('send', true));
}
?>
<script>
$(document).ready(function(){
    $('#ReportnumberEmails').multiSelect();
    $('#ReportnumberEmails').multiSelect({ selectableOptgroup: true });
    
    
    $("#dialog div.textarea").width($("form.dialogform").width() - 30);
    $("#dialog div.textarea textarea").css("height","7em");
    $("#dialog div.textarea textarea").css("width","99%");
});


</script>
</div>
<?php echo $this->JqueryScripte->ModalFunctions();

?>