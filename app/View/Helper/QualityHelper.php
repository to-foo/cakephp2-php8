<?php
class QualityHelper extends AppHelper {

	var $helpers = array('Html','Pdf');

	public function EyecheckDateSingle($data,$certificate_id,$certificate_data_id) {

		$output = NULL;

		foreach($data['eyecheck']['summary'] as $_key => $_summary){

			if(count($_summary) == 0) continue;
			if(!isset($_summary[$certificate_data_id])) continue;

			if(!isset($output_messages[$_key])) $output_messages[$_key] = array();

					foreach($_summary[$certificate_data_id] as $__key => $__summary){

						if(!isset($__summary['certified_date'])) continue;

						return $__summary['certified_date'];
						break;

					}
		}

		if($output != NULL) return NULL;
		else return false;
	}

	public function EyecheckSummarySingle($data,$certificate_id,$certificate_data_id) {

		$output = NULL;
		$output_messages = array();

		$summary_desc = array(
					'futurenow' => array(
							0 =>__('First-time certification reached',true),
							1 =>__('First-time certifications reached',true),
							),
					'future' => array(
							0 =>__('First-time certification',true),
							1 =>__('First-time certifications',true),
							),
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);

		foreach($data['eyecheck']['summary'] as $_key => $_summary){
			if(count($_summary) > 0){
				if(isset($_summary[$certificate_data_id])){
					if(!isset($output_messages[$_key])){
							$output_messages[$_key] = array();
						}
					foreach($_summary[$certificate_data_id] as $__key => $__summary){
						if(is_int($__key)){
							$output_messages[$_key]['headline'] = '<h4 class="'.$_key.'">'.$summary_desc[$_key][0].'</h4>';
							$output_messages[$_key]['messages'][$__key] = '<li>'.$__summary.'</li>';
						}
					}
				}
			}
		}

		if(count($output_messages) > 0){
			foreach($output_messages as $_key => $_output_messages){
				$output .= $_output_messages['headline'];
				$output .= '<ul>';
				foreach($_output_messages['messages'] as $_messages){
					$output .= $_messages;
				}
				$output .= '</ul>';
			}
		}

		if($output != NULL) return $output;
		else return false;
	}

	public function CertificatSummarySingle($data,$certificate_id,$certificate_data_id) {

		$output = NULL;
		$output_messages = array();

		$summary_desc = array(
					'futurenow' => array(
							0 =>__('First-time certification reached',true),
							1 =>__('First-time certifications reached',true),
							),
					'future' => array(
							0 =>__('First-time certification',true),
							1 =>__('First-time certifications',true),
							),
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);

		foreach($data['summary']['summary'] as $_key => $_summary){
			if(count($_summary) > 0){
				if(isset($_summary[$certificate_data_id])){

					if(!isset($output_messages[$_key])){
							$output_messages[$_key] = array();
						}


					$headline_desc = null;

					if(isset($_summary[$certificate_data_id]['certificate'])){
						$headline_desc .= $_summary[$certificate_data_id]['certificate']['sector'].'/';
						$headline_desc .= $_summary[$certificate_data_id]['certificate']['third_part'].'/';
						$headline_desc .= $_summary[$certificate_data_id]['certificate']['certificat'].'/';
						$headline_desc .= $_summary[$certificate_data_id]['certificate']['testingmethod'].'/';
						$headline_desc .= $_summary[$certificate_data_id]['certificate']['level'];
					}

					foreach($_summary[$certificate_data_id] as $__key => $__summary){
						if(is_int($__key)){
							$output_messages[$_key]['headline'] = '<h4 class="'.$_key.'">'.$summary_desc[$_key][0].' - '.$headline_desc.'</h4>';
							$output_messages[$_key]['messages'][$__key] = '<li>'.$__summary.'</li>';
//							$output_messages['info'] = $data['summary']['qualifications'][$certificate_id];
						}
					}
				}
			}
		}

		if(count($output_messages) > 0){
			foreach($output_messages as $_key => $_output_messages){
				$output .= $_output_messages['headline'];
				$output .= '<ul>';
				foreach($_output_messages['messages'] as $_messages){
					$output .= $_messages;
				}
				$output .= '</ul>';
			}
		}

		if($output != NULL) return $output;
		else return false;
	}

	public function CollectWelderTestInfo($data) {

		$infolink = array();
		$infolinks = NULL;
		$output = '';
		$output = '<div class="welder_test_tooltip">';

		$welderid = $data ['Welder']['id'];

		foreach ($data['WelderTest'] as $_key => $_data) {
			$ThisReportName = $this->Pdf->ConstructReportName($_data,2);

			$output .= '<div class="' .$welderid.'_'. $_key . '_weldtestinfo">';
			$output .= '<p><strong>';
                        $output .= $ThisReportName . " / " . $_data['Reportnumber']['date_of_test'] . "\n";
			$output .= '</strong></p>';

			if(isset($_data['evaluation']) && count($_data['evaluation']) > 0){



			foreach($_data['evaluation'] as $__key => $__data){

                            $output .= '<ul class="">';
                        foreach ($__data as $d_k => $d_vv){

					$output .= '<li>';
					$output .= $d_k . ': ' . $d_vv;
					$output .= '</li>';
				}
                                $output .= '</ul>';

                        }
                        $infolink[] = $this->Html->link(__('Show welder test info',true),array_merge(array('controller' => 'welders','action' => 'getlasttests'),$this->request->projectvars['VarsArray']),array('id' =>$_key,'rev' => $welderid .'_'.$_key . '_weldtestinfo','title' => $ThisReportName,'class' => 'modal weldertest_tooltip icon ' . $_data['evaluation_class'] . ' ' .$welderid.'_'. $_key . '_weldtestinfo'));


			}

			$output .= '</div>';
		}

		$output .= '<div>';

		foreach($infolink as $_key => $_data){
			$infolinks .= $_data;
		}

		return $infolinks . $output;
	}

	public function MonitoringSummarySingle($key,$data) {

		$output = NULL;
		$output_messages = array();

		$summary_desc = array(
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);

		foreach($data as $_key => $_data){
			if(count($_data) == 0) continue;

			$headline_desc = null;

			$headline_desc .= '<strong>' . $_key . '</strong> ';

			foreach($_data as $__key => $__data){
				foreach($__data as $___key => $___data){
					if(is_int($___key)){
						$output_messages[$key]['headline'] = '<h4 class="'.$key.'">'.$summary_desc[$key][0].'</h4>';
						$output_messages[$key]['messages'][$___key] = '<li><strong>'.$_key. '</strong> ' . $___data.'</li>';
					}
				}
			}
		}

		if(count($output_messages) > 0){
			foreach($output_messages as $_key => $_output_messages){
				$output .= $_output_messages['headline'];
				$output .= '<ul>';
				foreach($_output_messages['messages'] as $_messages){
					$output .= $_messages;
				}
				$output .= '</ul>';
			}
		}

		if($output != NULL) return $output;
		else return false;

	}

	public function CertificatSummary($summary,$examiner_id,$examiner) {

		$output = NULL;
		$output_messages = array();

		$summary_desc = array(
					'futurenow' => array(
							0 =>__('First-time certification reached',true),
							1 =>__('First-time certifications reached',true),
							),
					'future' => array(
							0 =>__('First-time certification',true),
							1 =>__('First-time certifications',true),
							),
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);

		$output .= '<ul class="summary">';

		foreach($summary as $_key => $_summary){

		if(count($_summary) > 0){

		if(count($_summary) == 1) $singular_plural = 0;
		else $singular_plural = 1;

		$output .= '<li class="';
		$output .= 'summary ';
		$output .= 'summary_'.$_key;
		$output .= '" ';
		$output .= 'title="' . count($_summary). ' ' . $summary_desc[$_key][$singular_plural] . '"';
		$output .= '>';
		$output .= '<span class="';
		$output .= 'summary_dialog_'.$examiner_id.'_'.$_key;
		$output .= '">';
		$output .= '</span>';

		$output_messages[$_key]  = '<div id="summary_dialog_'.$examiner_id.'_'.$_key.'" class="summary_dialog summary_dialog_'.$_key.'">';
		$output_messages[$_key] .= '<h4 class="';
		$output_messages[$_key] .= $_key;
		$output_messages[$_key] .= '">';
		$output_messages[$_key] .= $summary_desc[$_key][$singular_plural];
		$output_messages[$_key] .= ' - ';
		$output_messages[$_key] .= $examiner;
		$output_messages[$_key] .= '</h4>';
		$output_messages[$_key] .'<span class="">';
		$output_messages[$_key] .'</span>';

		$output_messages[$_key] .= '<ul>';
		foreach($_summary as $__key => $__summary){

			$certificate_disc  = $__summary['certificate']['sector'] . '/';
			$certificate_disc .= $__summary['certificate']['third_part'] . '/';
			$certificate_disc .= $__summary['certificate']['certificat'] . '-';
			$certificate_disc .= $__summary['certificate']['level'] . '/';
			$certificate_disc .= $__summary['certificate']['testingmethod'];
			$this->request->projectvars['VarsArray'][16] = $__summary['certificate']['id'];

			$output_messages[$_key] .= '<li>';
			$output_messages[$_key] .= $this->Html->link($certificate_disc, array_merge(array('action' => 'certificate'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal','title' => __('show zertificate',true)));

			$output_messages[$_key] .= '<ul>';

			foreach($__summary as $___key => $___summary){
				if(!is_array($___summary)){
					$output_messages[$_key] .= '<li>';
					$output_messages[$_key] .= $___summary;
					$output_messages[$_key] .= '</li>';
				}
			}

			$output_messages[$_key] .= '</ul>';

			$output_messages[$_key] .= '</li>';
		}
		$output_messages[$_key] .= '</ul>';
		$output_messages[$_key] .= '</div>';

		$output .= '</li>';
	}
}

		$output .= '</ul>';

		foreach($output_messages as $_key => $_output_messages){
			$output .= $_output_messages;
		}

		$output .= '
<script type="text/javascript">
$(document).ready(function(){

	$("ul.summary li").click(function() {
		$(".summary_dialog").hide();
		$("#" + $(this).children("span").attr("class")).show();
		$("#" + $(this).children("span").attr("class")).width($(".content").width());
		return false;
	});

	$(window).click(function() {
		$(".summary_dialog").hide();
	});
});
</script>
';

	if(count($output_messages)== 0){
		return false;
	}

	return $output;
	}

	public function EyecheckSummary($summary,$examiner_id,$examiner) {

		$output = NULL;
		$output_messages = array();

		$summary_desc = array(
					'futurenow' => array(
							0 =>__('First-time certification reached',true),
							1 =>__('First-time certifications reached',true),
							),
					'future' => array(
							0 =>__('First-time certification',true),
							1 =>__('First-time certifications',true),
							),
					'errors' => array(
							0 =>__('Irregularity',true),
							1 =>__('Irregularities',true),
							),
					'warnings' => array(
							0 => __('Warning',true),
							1 => __('Warnings',true)
							),
					'hints' => array(
							0 => __('Hint',true),
							1 => __('Hints',true)
							),
					'deactive' => array(
							0 => __('Deactive',true),
							1 => __('Deactive',true)
							)
						);

		$output .= '<ul class="summary_eyecheck">';
		foreach($summary as $_key => $_summary){

			if(count($_summary) > 0){

				if(count($_summary) == 1) $singular_plural = 0;
				else $singular_plural = 1;

				$output .= '<li class="';
				$output .= 'summary ';
				$output .= 'summary_'.$_key;
				$output .= '" ';
				$output .= 'title="' . count($_summary). ' ' . $summary_desc[$_key][$singular_plural] . '"';
				$output .= '>';
				$output .= '<span class="';
				$output .= 'summaryeyecheck_dialog_'.$examiner_id.'_'.$_key;
				$output .= '">';
				$output .= '</span>';
				$output .= '</li>';

				$output_messages[$_key]  = '<div id="summaryeyecheck_dialog_'.$examiner_id.'_'.$_key.'" class="summary_dialog summary_dialog_'.$_key.'">';
				$output_messages[$_key] .= '<h4 class="';
				$output_messages[$_key] .= $_key;
				$output_messages[$_key] .= '">';
				$output_messages[$_key] .= $summary_desc[$_key][$singular_plural];
				$output_messages[$_key] .= ' - ';
				$output_messages[$_key] .= $examiner;
				$output_messages[$_key] .= '</h4>';
				$output_messages[$_key] .'<span class="">';
				$output_messages[$_key] .'</span>';

				$output_messages[$_key] .= '<ul>';
				foreach($_summary as $__key => $__summary){

					$certificate_disc = $__summary['certificate']['certificat'];
					$this->request->projectvars['VarsArray'][16] = $__summary['certificate']['id'];

					$output_messages[$_key] .= '<li>';
					$output_messages[$_key] .= $this->Html->link($certificate_disc, array_merge(array('action' => 'eyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal','title' => __('show vision test',true)));

					$output_messages[$_key] .= '<ul>';

					foreach($__summary as $___key => $___summary){
						if(!is_array($___summary)){
							$output_messages[$_key] .= '<li>';
							$output_messages[$_key] .= $___summary;
							$output_messages[$_key] .= '</li>';
						}
					}

					$output_messages[$_key] .= '</ul>';
					$output_messages[$_key] .= '</li>';
				}

				$output_messages[$_key] .= '</ul>';
				$output_messages[$_key] .= '</div>';


			}
		}
		$output .= '</ul>';

		$output .= '
<script type="text/javascript">
$(document).ready(function(){

	$("ul.summary_eyecheck li").click(function() {
		$(".summary_dialog").hide();
		$("#" + $(this).children("span").attr("class")).show();
		$("#" + $(this).children("span").attr("class")).width($(".modalarea").width());
		return false;
	});

	$(window).click(function() {
		$(".summary_dialog").hide();
	});
});
</script>
';

		if(count($output_messages) == 0){
			return false;
		}

		foreach($output_messages as $_key => $_output_messages){
			$output .= $_output_messages;
		}

		return $output;
	}
}
?>
