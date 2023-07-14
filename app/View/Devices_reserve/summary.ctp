<div class="modalarea">
<h2><?php echo __('Summary',true);?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
//pr($summaryeyecheck);
$output_messages = array();
//echo $this->Html->link(__('Send this monitoring informations per email',true),array_merge(array('action' => 'email_certificate'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal_live icon icon_email_certificate','title' => __('Send this monitoring informations per email',true)));
echo '<ul class="summary">';

foreach($summary as $_key => $_summary){

	if($_key == 'hints') continue;
	if(!isset($_summary[key($_summary)]) || count($_summary[key($_summary)]) == 0) continue;
	
	if(!isset($output_messages[$_key])) $output_messages[$_key] = null;
	
	$output_messages[$_key] = '<div class="container_inner_summary summary_all_'.$_key.'">';
	$output_messages[$_key] .= '<h4 class="';
	$output_messages[$_key] .= $_key;
	$output_messages[$_key] .= '">';
	$output_messages[$_key] .=  $summary_desc[$_key][1];
	$output_messages[$_key] .= $this->Html->link('back','javascript:',array('class' => 'back icon icon_close'));		
	$output_messages[$_key] .= '</h4>';		
	$output_messages[$_key] .= '<ul class="summary_all summary_all_'.$_key.'">';

	foreach($_summary as $__key => $__summary){
		
		if(count($__summary) > 0){

			$output_messages[$_key] .= '<li>';
			$output_messages[$_key] .= $__key;
			$output_messages[$_key] .= '<ul>';
		
			foreach($__summary as $___key => $___summary){
					
				foreach($___summary as $____key => $____summary){
			
					if($____summary['Device']['id'] == 0) continue;

					$this->request->projectvars['VarsArray'][15] = $____summary['DeviceTestingmethod']['id'];
					$this->request->projectvars['VarsArray'][16] = $____summary['Device']['id'];
					$this->request->projectvars['VarsArray'][17] = $____summary['DeviceCertificate']['id'];
					$this->request->projectvars['VarsArray'][18] = $____summary['DeviceCertificateData']['id'];

					$output_messages[$_key] .= '<li>';
					$output_messages[$_key] .= $____summary['Device']['name'].' (';
					$output_messages[$_key] .= $____summary['Device']['registration_no']. ') /';
					$output_messages[$_key] .= $____summary['DeviceCertificate']['certificat'];
					
					if(count($____summary['summary']) > 0){
						
						$output_messages[$_key] .= '<ul>';
						
						foreach($____summary['summary'] as $_____summary){
							$output_messages[$_key] .= '<li>';
							$output_messages[$_key] .= $_____summary;
							$output_messages[$_key] .= '</li>';
						}
						
//					$output_messages[$_key] .= $___summary[0].' ';
					$output_messages[$_key] .= '<li>';
					$output_messages[$_key] .= $this->Html->link(__('Show monitoring',true),array_merge(array('action' => 'monitorings'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal_live  icon icon_monitoring'));
//					$output_messages[$_key] .= $this->Html->link(__('Edit Examiner',true),array_merge(array('action' => 'edit'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal_live  icon icon_examiner'));
					$output_messages[$_key] .= '<div class="clear"></div>';
					$output_messages[$_key] .= '</li>';
						$output_messages[$_key] .= '</ul>';
					}
					
					$output_messages[$_key] .= '</li>';
				}
			}
			
			$output_messages[$_key] .= '</ul>';
			$output_messages[$_key] .= '</li>';
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

	$("ul.summary li.summary_all").click(function() {
		$("div.container_inner_summary").hide();
		var html = $("div." + $(this).attr("id")).html();

		$("#container_summary").empty();
		$("#container_summary").append(html);
		$("ul.summary li.back").show();

		$(".current_content").hide();
		return false;
	});
});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
		
