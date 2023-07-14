<div class="modalarea">
<h2>test</h2>
<?php echo $this->element('Flash/_messages');?>

<?php
//pr($summaryeyecheck);
$output_messages = array();
//echo $this->Html->link(__('Send this vision test informations per email',true),array_merge(array('action' => 'email_eyecheck'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal_live icon icon_email_eyecheck','title' => __('Send this vision test informations per email',true)));
//echo $this->Html->link(__('Send this certificate informations per email',true),array_merge(array('action' => 'email_certificate'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal_live icon icon_email_certificate','title' => __('Send this certificate informations per email',true)));
echo '<ul class="summary">';
//echo '<li class="back" title="'.__('Back',true).'"><span>back</span></li>';

foreach($summary as $_key => $_summary){

	if($_key == 'hints') continue;

	if(count($_summary) > 0 && isset($_key)){

		$output_messages[$_key] = '<div class="container_inner_summary summary_all_'.$_key.'">';
		$output_messages[$_key] .= '<h4 class="';
		$output_messages[$_key] .= $_key;
		$output_messages[$_key] .= '">';
		$output_messages[$_key] .= __('Qualifications',true) . ' - ' .  $summary_desc[$_key][1];
		$output_messages[$_key] .= '</h4>';
		$output_messages[$_key] .= '<ul class="summary_all summary_all_'.$_key.'">';

		foreach($_summary as $__key => $__summary){
			if(count($__summary)> 0){
				foreach($__summary as $___key => $___summary){

					$this->request->projectvars['VarsArray'][15] = $___summary['examiner']['id'];
					$this->request->projectvars['VarsArray'][16] = $___summary['certificate']['id'];

					$output_messages[$_key] .= '<li>';
					$output_messages[$_key] .= $___summary['certificate']['sector'].'/';
					$output_messages[$_key] .= $___summary['certificate']['third_part'].'/';
					$output_messages[$_key] .= $___summary['certificate']['certificat'].'/';
					$output_messages[$_key] .= $___summary['certificate']['testingmethod'].'/';
					$output_messages[$_key] .= $___summary['certificate']['level'].', ';
					$output_messages[$_key] .= __('Examiner',true). ': ' . $___summary['examiner']['name'].' ';

					$output_messages[$_key] .= '<ul>';

					foreach($___summary as $____key => $____summary){
						if(is_numeric($____key)){
							$output_messages[$_key] .= '<li>';
							$output_messages[$_key] .= $____summary.' ';
							$output_messages[$_key] .= '</li>';
						}
					}

//					$output_messages[$_key] .= $___summary[0].' ';
					$output_messages[$_key] .= '<li>';
					$output_messages[$_key] .= $this->Html->link(__('Show certificate',true),array_merge(array('action' => 'certificates'),$this->request->projectvars['VarsArray']),array('title' => __('Show certificate',true),'class' => 'ajax icon icon_certificate'));
//					$output_messages[$_key] .= $this->Html->link(__('Edit Examiner',true),array_merge(array('action' => 'edit'),$this->request->projectvars['VarsArray']),array('title' =>__('Edit Examiner',true),'class' => 'mymodal_live icon icon_examiner'));

					$output_messages[$_key] .= '<div class="clear"></div>';
					$output_messages[$_key] .= '</li>';
					$output_messages[$_key] .= '</ul>';
					$output_messages[$_key] .= '</li>';
				}
			}
		}

		$output_messages[$_key] .= '</ul>';
		$output_messages[$_key] .= '</div>';

		echo '<li class="';
		echo 'summary_all ';
		echo 'summary_'.$_key;
		echo '" ';
		echo 'id="';
		echo 'summary_all_'.$_key;
		echo '"';
		echo 'title ="';
		echo $summary_desc[$_key][1];
		echo '"';
		echo '>';
		echo '<span>';
		echo '</span>';
		echo '</li>';
	}
}

echo '</ul>';

foreach($output_messages as $_output_messages){
	echo $_output_messages;
}

//pr($summaryeyecheck);
$output_messages = array();
echo '<ul class="summary">';

foreach($summaryeyecheck as $_key => $_summary){

	if($_key == 'hints') continue;

	if(count($_summary) > 0 && isset($_key)){

		$output_messages[$_key] = '<div class="container_inner_summaryeyecheck summaryeyecheck_all_'.$_key.'">';
		$output_messages[$_key] .= '<h4 class="';
		$output_messages[$_key] .= $_key;
		$output_messages[$_key] .= '">';
		$output_messages[$_key] .= __('Eye checks',true) . ' - ' . $summary_desc[$_key][1];
		$output_messages[$_key] .= '</h4>';
		$output_messages[$_key] .= '<ul class="summaryeyecheck_all summary_all_'.$_key.'">';

		foreach($_summary as $__key => $__summary){
			if(count($__summary)> 0){
				foreach($__summary as $___key => $___summary){

					$this->request->projectvars['VarsArray'][15] = $___summary['examiner']['id'];
					$this->request->projectvars['VarsArray'][16] = $___summary['certificate']['id'];

					$output_messages[$_key] .= '<li>';
					$output_messages[$_key] .= $___summary['certificate']['certificat'].' ';
					$output_messages[$_key] .= __('Examiner',true). ': ' . $___summary['examiner']['name'].' ';

					$output_messages[$_key] .= '<ul>';

					foreach($___summary as $____key => $____summary){
						if(is_numeric($____key)){
							$output_messages[$_key] .= '<li>';
							$output_messages[$_key] .= $____summary.' ';
							$output_messages[$_key] .= '</li>';
						}
					}

//					$output_messages[$_key] .= $___summary[0].' ';
					$output_messages[$_key] .= '<li>';
					$output_messages[$_key] .= $this->Html->link(__('Show vision test',true),array_merge(array('action' => 'eyechecks'),$this->request->projectvars['VarsArray']),array('class' => 'ajax icon icon_eyecheck'));
//					$output_messages[$_key] .= $this->Html->link(__('Edit Examiner',true),array_merge(array('action' => 'edit'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal_live  icon icon_examiner'));
					$output_messages[$_key] .= '<div class="clear"></div>';
					$output_messages[$_key] .= '</li>';
					$output_messages[$_key] .= '</ul>';
					$output_messages[$_key] .= '</li>';
				}
			}
		}

		$output_messages[$_key] .= '</ul>';
		$output_messages[$_key] .= '</div>';

		echo '<li class="';
		echo 'summary_all ';
		echo 'summaryeyecheck_'.$_key;
		echo '" ';
		echo 'id="';
		echo 'summaryeyecheck_all_'.$_key;
		echo '"';
		echo 'title ="';
		echo $summary_desc[$_key][1];
		echo '"';
		echo '>';
		echo '<span>';
		echo '</span>';
		echo '</li>';
	}
}

echo '</ul>';

foreach($output_messages as $_output_messages){
	echo $_output_messages;
}

?>
<div id="container_summary" class="container_summary" ></div>
</div>
<script type="text/javascript">
$(document).ready(function(){

	$("div.summary_output").css("background","none");
	$("div.container_inner_summary").hide();
	$("div.container_inner_summaryeyecheck").hide();
	$("ul.summary li.back").hide();

	$("a.mymodal_live").die();
	$("a.back").die();

	var html = $("div." + $("div.modalarea li:first").attr("id")).html();
	$("#container_summary").empty();
	$("#container_summary").append(html);
	$("ul.summary li.back").show();

	$("a.mymodal_live").live("click", function(){

//		$("#dialog").dialog(dialogOpts);

		$("#dialog").load($(this).attr("href"), {
			"ajax_true": 1
		});

//		$("#dialog").dialog("open");

		return false;
	});
/*
	$("ul.summary li.back, h4 a.back").click(function() {
		$("#container_summary").empty();
		$(".current_content").show();
		$("ul.summary li.back").hide();
	});

	$("a.back").live("click", function(){
		$("#container_summary").empty();
		$(".current_content").show();
		$("ul.summary li.back").hide();
	});
*/
	$("ul.summary li.summary_all").click(function() {
		$("div.container_inner_summary").hide();
		$("div.container_inner_summaryeyecheck").hide();
		var html = $("div." + $(this).attr("id")).html();
		$("#container_summary").empty();
		$("#container_summary").append(html);
		$("ul.summary li.back").show();
		return false;
	});
});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
