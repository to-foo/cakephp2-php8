<div class="quicksearch"><?php if(isset($ControllerQuickSearch)) echo $this->element('searching/search_quick_examiner',array('target_id' => 'examiner_id','targedaction' => 'overview','action' => 'quicksearch','minLength' => 2,'discription' => __('Examiner last name', true)));?></div>
<h2><?php  echo __('Examiner') . ' ' . $examiner['Examiner']['name'] . ' - ' . __('qualifications');?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="infos">
<div class="current_content hide_infos_div">
<h3><?php echo __('Examiner infos',true);?></h3>
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Examiner','ul');?>
</div>
<div class="current_content">
<?php  echo $this->Html->link(__('Hide examiner infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_toggle icon_hide_infos','title' => __('Hide examiner infos',true)));?>
<?php echo $this->Html->link(__('Edit examiner',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit_examiner','title' => __('Edit examiner',true)));?>
<?php echo $this->Html->link(__('Add qualification',true), array_merge(array('action' => 'qualificationnew'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_add_qualification','title' => __('Add qualification',true)));?>
<?php if(count($certificates) > 0) echo $this->Html->link(__('Show certifacations',true), array_merge(array('action' => 'certificates'), $this->request->projectvars['VarsArray']), array('class' => 'ajax icon icon_show_certificates','title' => __('Show certifacations',true)));?>
<?php echo $this->Html->link(__('Print examiner infos',true), array_merge(array('action' => 'pdf'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf showpdflink','title' => __('Print examiner infos',true)));?>
<ul class="listemax certificates">
<?php

foreach($certificates as $_key => $_certificates){


	echo '<li>';

	$disc = $_key . ' (' . $_certificates[0]['Certificate']['third_part'] . ') - ';
	$disc .= count($_certificates) . ' ';

	if(count($_certificates) == 1) $disc .= __('qualification',true);
	elseif(count($_certificates) > 1) $disc .= __('qualifications',true);

	echo '<b class="head">';
	echo $this->Html->link($disc, 'javascript:',array('class' => 'nolink'));
	echo '</b>';
	echo '<ul>';

	foreach($_certificates as $__key => $__certificates){

		unset($this->request->projectvars['VarsArray'][17]);

		// Wenn ein spezielles Zertifikat hervorgehoben werden soll
		// wir das Element hier markiert
		$QualiMarkingClass = null;

		$is_deactive = null;
		$deactive_class = null;
		$deactive_title = array();

		if($QualiMarking == $__certificates['Certificate']['id']) $QualiMarkingClass .= 'mark_this mark_this_' . $QualiMarking;

		echo '<li class="'.$QualiMarkingClass.'">';

		$this->request->projectvars['VarsArray'][16] = $__certificates['Certificate']['id'];

		if(isset($__certificates['CertificateData']['id'])) $this->request->projectvars['VarsArray'][17] = $__certificates['CertificateData']['id'];

		$disc_cert  = strtoupper($__certificates['Certificate']['testingmethod']) . ' ';
		$disc_cert .= $__certificates['Certificate']['certificat'] . ' ';
		$disc_cert .= $__certificates['Certificate']['third_part'] . ' ';
		$disc_cert .= __('level',true) . ' ' . $__certificates['Certificate']['level'];
		$disc_cert .= ' ';

		if($__certificates['CertificateStatus']['has_higher_certificate']['status'] == 1){
//			pr($__certificates['CertificateStatus']['has_higher_certificate']['status']);
//			$is_deactive = 'is_deactive';
			$disc_cert .= '<br>' . __('There exist a qualification with higher level for these test methods.',true);
		}

		if($__certificates['CertificateData']['valid_class'] == 'certification_deactive'){
			$deactive_class = 'is_deactive';
			$deactive_title = __('This certificate has the status deaktive.',true);
		}

		if($__certificates['Certificate']['certificate_data_active'] > 0) $disc_cert .=  '(' . __('Zertifiziert',true) . ')';
		else $disc_cert .=  '(' . __('Certification is not planned',true) . ')';

		$valid_class = null;
		$valid_class = $__certificates['CertificateData']['valid_class'];

		echo '<p class="'.$valid_class.'">';
		echo $this->Html->link($disc_cert, 'javascript:', array('title' => $disc_cert, 'escape' => false, 'class' => $is_deactive.' nolink'));
		echo '</p>';
		//	pr($__certificates['Certificate']['active']);	

		if(isset($__certificates['Certificate']['active'])) {
			echo '<p class="'.$valid_class.'">';
			echo __('Qualification Status') . ': ';
			if($__certificates['Certificate']['active'] == 1) echo '<b>' . __('active') . '</b>';
			if($__certificates['Certificate']['active'] == 0) echo '<b>' . __('deactive') . '</b>';
			echo '</p>';
		}

		if(isset($__certificates['CertificateData']['active'])) {
			echo '<p class="'.$valid_class.'">';
			echo __('Certificate Status') . ': ';
			if($__certificates['CertificateData']['active'] == 1) echo '<b>' . __('active') . '</b>';
			if($__certificates['CertificateData']['active'] == 0) echo '<b>' . __('deactive') . '</b>';
			echo '</p>';
		}

		echo '</p>';

		if(isset($__certificates['CertificatesTestingmethodes'])){
			echo '<ul>';

			foreach($__certificates['CertificatesTestingmethodes'] as $_testingmethods){

				echo '<li class="disc_style">';
				echo $_testingmethods['verfahren'];
				echo '</li>';

			}

			echo '</ul>';
		}

		if($is_deactive == null){

			echo $this->Html->link($disc_cert, array_merge(array('action' => 'qualification'), $this->request->projectvars['VarsArray']), array('title' => __('Edit Qualification',true), 'escape' => false, 'class' => 'modal symbol_link_small icon_small_single_edit_qualification'));
			echo $this->Html->link(__('Additional documents',true), array_merge(array('action' => 'certificatesfiles'), $this->request->projectvars['VarsArray']), array('class' => 'modal symbol_link_small icon_small_single_file','title' => __('Additional documents',true)));
			echo $this->Html->link(__('Delete qualification',true), array_merge(array('action' => 'qualificationdel'), $this->request->projectvars['VarsArray']), array('class' => 'modal symbol_link_small icon_small_delete','title' => __('Delete qualification',true)));

			if($__certificates['CertificateData']['valid_class'] == 'certification_not_planned'){
				echo $this->Html->link(__('certification not planned',true), 'javascript:', array('class' => 'nolink symbol_link_small '.$__certificates['CertificateData']['valid_class'],'title' => __('certification not planned',true)));
			}

			if($__certificates['CertificateData']['valid_class'] == 'certification_not_planned') continue;

			if($__certificates['CertificateData']['valid_class'] != 'certification_deactive'){
				echo $this->Html->link($__certificates['CertificateData']['next_certification_date'], array_merge(array('action' => 'certificates'), $this->request->projectvars['VarsArray']), array('class' => 'ajax symbol_link_small '.$__certificates['CertificateData']['valid_class'],'title' => __('Go to this certification',true) . ' (' . $__certificates['CertificateData']['next_certification_date'] . ')'));
			}
		} else {
			echo $this->Html->link($disc_cert, array_merge(array('action' => 'qualification'), $this->request->projectvars['VarsArray']), array('title' => __('Edit Qualification',true), 'escape' => false, 'class' => 'modal symbol_link_small icon_small_single_edit_qualification'));
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
echo $this->element('js/toggle_element',array('Button' => '.icon_toggle','Element' => '.hide_infos_div'));
echo $this->element('js/show_pdf_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
if($QualiMarking > 0) echo $this->element('js/scroll_to_element',array('element' => '.mark_this_' . $QualiMarking, 'time' => 1000));
?>
