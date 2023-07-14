<div class="quicksearch"><?php if(isset($ControllerQuickSearch)) echo $this->element('searching/search_quick_examiner',array('target_id' => 'examiner_id','targedaction' => 'overview','action' => 'quicksearch','minLength' => 2,'discription' => __('Examiner last name', true)));?></div>
<h2><?php  echo __('Examiner') . ' ' . $examiner['Examiner']['name'] . ' - ' . __('certificates');?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="infos">
<div class="current_content hide_infos_div">
<h3><?php echo __('Examiner infos',true);?></h3>
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Examiner','ul');?>
</div>
<div class="current_content">
<?php echo $this->Html->link(__('Hide examiner infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_toggle icon_hide_infos','title' => __('Hide examiner infos',true)));?>
<?php echo $this->Html->link(__('Edit examiner',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit_examiner','title' => __('Edit examiner',true)));?>
<?php echo $this->Html->link(__('Show qualifications',true), array_merge(array('action' => 'qualifications'), $this->request->projectvars['VarsArray']), array('class' => 'ajax icon icon_show_qualifications','title' => __('Show qualifications',true)));?>
<?php echo $this->Html->link(__('Print examiner infos',true), array_merge(array('action' => 'pdf'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf showpdflink','title' => __('Print examiner infos',true)));?>
<ul class="listemax certificates">
<?php
foreach($certificates as $_key => $_certificates){
	echo '<li>';
	$disc = $_key . ' (' . $_certificates[0]['Certificate']['third_part'] . ') - ';
	$disc .= count($_certificates) . ' ';

	if(count($_certificates) == 1) $disc .= __('certification',true);
	elseif(count($_certificates) > 1) $disc .= __('certifications',true);

	echo '<b class="head">';
	echo $this->Html->link($disc, 'javascript:',array('class' => 'nolink'));
	echo '</b>';
	echo '<ul>';

	foreach($_certificates as $__key => $__certificates){

		unset($this->request->projectvars['VarsArray'][17]);

		$QualiMarkingClass = null;

		if($QualiMarking == $__certificates['Certificate']['id']) $QualiMarkingClass = 'mark_this mark_this_' . $QualiMarking;

		echo '<li class="'.$QualiMarkingClass.'">';

		$this->request->projectvars['VarsArray'][16] = $__certificates['Certificate']['id'];
		$disc_cert  = strtoupper($__certificates['Certificate']['testingmethod']) . ' ';
		$disc_cert .= $__certificates['Certificate']['certificat'] . ' ';
		$disc_cert .= $__certificates['Certificate']['third_part'] . ' ';
		$disc_cert .= __('level',true) . ' ' . $__certificates['Certificate']['level'];

		$deactive_class = null;
		$deactive_title = array();

		if($__certificates['CertificateData']['valid_class'] == 'certification_deactive'){
			$deactive_class = 'is_deactive';
			$deactive_title = __('This certificate has the status deaktive.',true);
		}

		if($__certificates['CertificateData']['valid_class'] == 'certification_not_planned') $disc_cert .= ' (' . __('certification not planned',true) . ')';

		echo $this->Html->link($disc_cert, 'javascript:', array('class' => 'nolink ' . $deactive_class,'title' => $deactive_title));

		if($deactive_class == 'is_deactive'){
			$this->request->projectvars['VarsArray'][17] = $__certificates['CertificateData']['id'];
			echo $this->Html->link(__('Edit certification',true), array_merge(array('action' => 'editcertificate'), $this->request->projectvars['VarsArray']), array('class' => 'modal','title' => __('Edit certification',true)));
			unset($this->request->projectvars['VarsArray'][17]);
		}

		if($__certificates['CertificateData']['valid_class'] == 'certification_not_planned') continue;

		if($__certificates['CertificateData']['valid_class'] != 'certification_deactive'){
			echo '<ul class="';
			echo $__certificates['CertificateData']['valid_class'];
			echo '">';

			if(isset($__certificates['Certificate']['rules'])) echo '<li>' . $__certificates['Certificate']['rules'] . '</li>';
			if(isset($__certificates['Certificate']['rules'])) echo '<li>' . $__certificates['Certificate']['description'] . '</li>';

			echo '<li>' . $__certificates['CertificateData']['next_certification_date'] . '</li>';
			echo '<li>' . __('reminder',true) . ' ' . $__certificates['CertificateData']['next_certification_horizon'] . '</li>';

			echo '<li>' . $__certificates['CertificateData']['time_to_next_certification'] . '</li>';
			echo '<li>' . $__certificates['CertificateData']['time_to_next_horizon'] . '</li>';
			echo '<li><b class="';
			echo $__certificates['CertificateData']['certification_requested_class'];
			echo '">' . $__certificates['CertificateData']['certification_requested'] . '</b></li>';

			if($__certificates['CertificateData']['valid'] == 1 && !isset($__certificates['CertificateData']['certified_file_pfath'])){
				echo '<li><strong class="error">' . __('There is no certifcate file available.',true) . '</strong></li>';
			}
			echo '<li><p class="show_file">';
			$this->request->projectvars['VarsArray'][17] = $__certificates['CertificateData']['id'];
			echo $this->Html->link(__('Edit certification',true), array_merge(array('action' => 'editcertificate'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit','title' => __('Edit certification',true)));

			if(isset($__certificates['CertificateData']['certified_file_pfath'])) echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('target' => '_blank','class' => 'icon icon_certificate','title' => __('Show certificate file',true)));
			else if(!empty($__certificates['CertificateData']['certified_date'])) echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('title' => __('Upload',true),'class' => 'modal icon icon_upload_red'));

			echo $this->Html->link(__('Show qualification',true), array_merge(array('action' => 'qualifications'), $this->request->projectvars['VarsArray']), array('class' => 'ajax icon icon_show_qualifications','title' => __('Show qualification',true)));

			if($__certificates['CertificateData']['apply_for_recertification'] == 1) $apply_for_recertification_desc = __('Beantragung auf Rezertifizierung oder Erneuerung zurückziehen',true);
			elseif($__certificates['CertificateData']['valid'] == 1) $apply_for_recertification_desc = __('Beantragung auf Rezertifizierung oder Erneuerung stellen',true);

			if($__certificates['CertificateData']['valid'] == 1) echo $this->Html->link($apply_for_recertification_desc, array_merge(array('action' => 'askcertification'), $this->request->projectvars['VarsArray']), array('title' => $apply_for_recertification_desc,'class' => 'modal icon icon_ask_replace'));

			if($__certificates['CertificateData']['apply_for_recertification'] == 1){

				$replace_link_desc = __('Replace this information',true);

				if(isset($__certificates['CertificateData']['renewal']) && $__certificates['CertificateData']['renewal'] == true){
					$replace_link_desc = __('Renewal this information',true);
				}

				echo $this->Html->link($replace_link_desc, array_merge(array('action' => 'replacecertificate'), $this->request->projectvars['VarsArray']), array('title' => $replace_link_desc,'class' => 'modal icon icon_replace'));

			}

			echo $this->Html->link(__('Show history',true), array_merge(array('action' => 'history'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_history','title' => __('Show history',true)));
			echo $this->Html->link(__('Delete certification',true),array_merge(array('action' =>'removecertificate'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_del','title' => __('Delete certification',true)));

			echo '<div class="clear"></div></p></li>';

			echo '<li class="box">';
			echo $__certificates['CertificateData']['remark'];
			echo '</li>';

			unset($this->request->projectvars['VarsArray'][17]);
			echo '</ul>';
		}
		echo '</li>';
	}
	echo '</ul>';
	echo '</li>';
}
?>
</ul>
</div>
</div>
<?php
if($QualiMarking > 0) echo $this->element('js/scroll_to_element',array('element' => '.mark_this_' . $QualiMarking, 'time' => 1000));
echo $this->element('js/toggle_element',array('Button' => '.icon_toggle','Element' => '.hide_infos_div'));
echo $this->element('js/show_pdf_link');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
