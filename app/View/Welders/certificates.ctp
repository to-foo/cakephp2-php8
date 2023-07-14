<div class="">
<h2><?php  echo __('Welder') . ' ' . $welder['Welder']['name'] . ' - ' . __('Welder qualifications');?></h2>
<div id="message_wrapper">
<?php
echo $this->Session->flash();
?>
</div>
<div class="infos">
<?php // echo $this->Quality->CertificatSummary($summary,$welder['Welder']['id'],$welder['Welder']['name']);?>
<div class="clear"></div>
<div class="current_content hide_infos_div">
<h3><?php echo __('Welder infos',true);?></h3>
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Welder','ul');?>

<div class="clear"></div>
</div>
<div id="container_summary" class="container_summary" ></div>
<div class="current_content">
<?php echo $this->Html->link(__('Hide welder infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_hide_infos','title' => __('Hide welder infos',true)));?>
<?php echo $this->Html->link(__('Edit welder',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit_welder','title' => __('Edit welder',true)));?>
<?php echo $this->Html->link(__('Show qualifications',true), array_merge(array('action' => 'qualifications'), $this->request->projectvars['VarsArray']), array('class' => 'ajax icon icon_show_qualifications','title' => __('Show qualifications',true)));?>
<div class="clear"></div>
<ul class="listemax certificates">
<?php
foreach($certificates as $_key => $_certificates){
	echo '<li>';
	$disc = $_key . ' (' . $_certificates[0]['WelderCertificate']['third_part'] . ') - ';
	$disc .= count($_certificates) . ' ';

	if(count($_certificates) == 1){
		$disc .= __('Welder test',true);
	}
	elseif(count($_certificates) > 1){
		$disc .= __('Welder qualifications',true);
	}
	echo '<b class="head">';
	echo $this->Html->link($disc, 'javascript:',array('class' => 'nolink'));
	echo '</b>';
	echo '<ul>';
	foreach($_certificates as $__key => $__certificates){
/*
print '<pre>';
print_r($__certificates['WelderCertificateData']);
print '</pre>';
*/
		unset($this->request->projectvars['VarsArray'][17]);

		// Wenn ein spezielles Zertifikat hervorgehoben werden soll
		// wir das Element hier markiert
		$QualiMarkingClass = null;

		if($QualiMarking == $__certificates['WelderCertificate']['id']){
			$QualiMarkingClass = 'mark_this mark_this_' . $QualiMarking;
		}

		echo '<li class="'.$QualiMarkingClass.'">';
		$this->request->projectvars['VarsArray'][16] = $__certificates['WelderCertificate']['id'];
		$disc_cert  = ucfirst($__certificates['WelderCertificate']['weldingmethod']) . ' ';
		$disc_cert .= $__certificates['WelderCertificate']['certificat'] . ' ';
		$disc_cert .= $__certificates['WelderCertificate']['third_part'] . ' ';
		$disc_cert .= __('level',true) . ' ' . $__certificates['WelderCertificate']['level'];

		$deactive_class = null;
		$deactive_title = array();


		if($__certificates['WelderCertificateData']['valid_class'] == 'certification_deactive'){
			$deactive_class = 'is_deactive';
			$deactive_title = __('This qualification has the status deaktive.',true);
		}

		if($__certificates['WelderCertificateData']['valid_class'] == 'certification_not_planned') $disc_cert .= ' (' . __('qualification not planned',true) . ')';

		echo $this->Html->link($disc_cert, 'javascript:', array('class' => 'nolink ' . $deactive_class,'title' => $deactive_title));

		if($__certificates['WelderCertificateData']['valid_class'] == 'certification_not_planned') continue;

		if($__certificates['WelderCertificateData']['valid_class'] != 'certification_deactive'){
			echo '<ul class="';
			echo $__certificates['WelderCertificateData']['valid_class'];
			echo '">';

			echo '<li>' . $__certificates['WelderCertificateData']['next_certification_date'] . '</li>';
			echo '<li>' . __('reminder',true) . ' ' . $__certificates['WelderCertificateData']['next_certification_horizon'] . '</li>';

			echo '<li>' . $__certificates['WelderCertificateData']['time_to_next_certification'] . '</li>';
			echo '<li>' . $__certificates['WelderCertificateData']['time_to_next_horizon'] . '</li>';
			echo '<li><b class="';
			echo $__certificates['WelderCertificateData']['certification_requested_class'];
			echo '">' . $__certificates['WelderCertificateData']['certification_requested'] . '</b></li>';

			if($__certificates['WelderCertificateData']['valid'] == 1 && !isset($__certificates['WelderCertificateData']['certified_file_pfath'])){
				echo '<li><strong class="error">' . __('There is no qualification file available.',true) . '</strong></li>';
			}

			echo '<li><p class="show_file">';
			$this->request->projectvars['VarsArray'][17] = $__certificates['WelderCertificateData']['id'];
			echo $this->Html->link(__('Edit certification',true), array_merge(array('action' => 'editcertificate'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit','title' => __('Edit certification',true)));

			if(isset($__certificates['WelderCertificateData']['certified_file_pfath'])){
				echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('target' => '_blank','class' => 'icon icon_certificate','title' => __('Show certificate file',true)));
			}
//			elseif($__certificates['WelderCertificateData']['valid'] == 1) {
			else {
				echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('title' => __('Upload',true),'class' => 'modal icon icon_upload_red'));
			}

			echo $this->Html->link(__('Show qualification',true), array_merge(array('action' => 'qualifications'), $this->request->projectvars['VarsArray']), array('class' => 'ajax icon icon_show_qualifications','title' => __('Show qualification',true)));

			if($__certificates['WelderCertificateData']['apply_for_recertification'] == 1){
				$apply_for_recertification_desc = __('Beantragung auf Requalifizierung oder Erneuerung zurückziehen',true);
			}
			elseif($__certificates['WelderCertificateData']['valid'] == 1){
				$apply_for_recertification_desc = __('Beantragung auf Requalifizierung oder Erneuerung stellen',true);
			}

			if(isset($apply_for_recertification_desc)){
				echo $this->Html->link($apply_for_recertification_desc, array_merge(array('action' => 'askcertification'), $this->request->projectvars['VarsArray']), array('title' => $apply_for_recertification_desc,'class' => 'modal icon icon_ask_replace'));
			}

			if($__certificates['WelderCertificateData']['apply_for_recertification'] == 1){

//				$VarsArray_17 = $this->request->projectvars['VarsArray'][17];
//				$this->request->projectvars['VarsArray'][17] = 0;

				$replace_link_desc = __('Replace this information',true);

				if(isset($__certificates['WelderCertificateData']['renewal']) && $__certificates['WelderCertificateData']['renewal'] == true){
					$replace_link_desc = __('Renewal this information',true);
				}

				echo $this->Html->link($replace_link_desc, array_merge(array('action' => 'replacecertificate'), $this->request->projectvars['VarsArray']), array('title' => $replace_link_desc,'class' => 'modal icon icon_replace'));
//				$this->request->projectvars['VarsArray'][17] = $VarsArray_17;
			}

			echo $this->Html->link(__('Show history',true), array_merge(array('action' => 'history'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_history','title' => __('Show history',true)));
			echo $this->Html->link(__('Delete certification',true),array_merge(array('action' =>'removecertificate'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_del','title' => __('Delete certification',true)));
			 echo $this->Html->link($welder['Welder']['name'], array('controller' => 'reportnumbers', 'action' => 'pdf', $welder['Welder']['id']), array('class'=>'mymodal'));
			echo '<div class="clear"></div></p></li>';
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
</div>
<?php
// Wenn eine Zertifizierung hervorgehoben werden soll, wird zu diese gescrollt
if($QualiMarking > 0)echo $this->JqueryScripte->ScrollToElement('.mark_this_' . $QualiMarking,1000);
?>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
