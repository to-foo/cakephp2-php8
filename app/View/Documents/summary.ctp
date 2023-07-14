<?php
//pr($summaryeyecheck);
$output_messages = array();
echo $this->Html->link(__('Send this monitoring informations per email',true),array_merge(array('action' => 'email_certificate'),$this->request->projectvars['VarsArray']),array('class' => 'mymodal_live icon icon_email_certificate','title' => __('Send this monitoring informations per email',true)));
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
			
					if($____summary['Document']['id'] == 0) continue;

					$this->request->projectvars['VarsArray'][15] = $____summary['DocumentTestingmethod']['id'];
					$this->request->projectvars['VarsArray'][16] = $____summary['Document']['id'];
					$this->request->projectvars['VarsArray'][17] = $____summary['DocumentCertificate']['id'];
					$this->request->projectvars['VarsArray'][18] = $____summary['DocumentCertificateData']['id'];

					$output_messages[$_key] .= '<li>';
					$output_messages[$_key] .= $____summary['Document']['name'].' (';
					$output_messages[$_key] .= $____summary['Document']['registration_no']. ') /';
					$output_messages[$_key] .= $____summary['DocumentCertificate']['certificat'];
					
					if(count($____summary['summary']) > 0){
						
						$output_messages[$_key] .= '<ul>';
						
						foreach($____summary['summary'] as $_____summary){
							$output_messages[$_key] .= '<li>';
							$output_messages[$_key] .= $_____summary;
							$output_messages[$_key] .= '</li>';
						}
						
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
<script type="text/javascript">
$(document).ready(function(){

	$("div.summary_output").css("background","none");
	$("div.container_inner_summary").hide();
	$("div.container_inner_summaryeyecheck").hide();
	$("ul.summary li.back").hide();

	$("a.mymodal_live").die();
	$("a.back").die();

	var modalheight = Math.ceil(($(window).height() * 90) / 100);
	var modalwidth = Math.ceil(($(window).width() * 90) / 100);

	var dialogOpts = {
		modal: true,
		width: modalwidth,
		height: modalheight,
		autoOpen: false,
		draggable: true,
		resizeable: true
		};

	$("a.mymodal_live").live("click", function(){
		
		$("#dialog").dialog(dialogOpts);
		
		$("#dialog").load($(this).attr("href"), {
			"ajax_true": 1
		});
		
		$("#dialog").dialog("open");
		
		return false;
	});
	
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

	$("ul.summary li.summary_all").click(function() {
		$("div.container_inner_summary").hide();
		var html = $("div." + $(this).attr("id")).html();

		$("#container_summary").empty();
		$("#container_summary").append(html);
		$("#container_summary").width($("#content").width());
		$("ul.summary li.back").show();

		$(".current_content").hide();
		return false;
	});
});
</script>
		
