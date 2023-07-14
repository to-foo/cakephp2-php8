<?php
class LangHelper extends AppHelper {

	var $helpers = array('Html', 'Form');
	public function changeLang($lang_choise,$selected) {
	$output  = null;
	$output  = $this->Form->create('Language', array('url' => array('controller'=>'users', 'action'=>'login'),'class' => 'change_lag','label' => false,'div' => false,));
	$output .= '<fieldset>';
	$output .= $this->Form->input('beschreibung', array('label' => false, 'div' => false, 'selected' => $selected, 'options' => $lang_choise));
	$output .= $this->Form->hidden('lastpath', array('value' => CakeSession::read('here')));
	$output .= $this->Form->end();
	$output .= '</fieldset>';
	$output .= '<script type="text/javascript">
					$(document).ready(function(){
						$("form.change_lag").on("submit", function() {
							data = $(this).serializeArray();
							data.push({"name": "ajax_true", "value":1});

							$("#dialog").empty().load($(this).attr("action"), data).dialog("show");
							return false;
						});

   						$("#LanguageBeschreibung").change(function() {
     						$("form.change_lag").submit();
  							});
						});
				</script>';
	return $output;
	}
}