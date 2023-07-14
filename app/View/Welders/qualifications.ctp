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
<?php echo $this->Html->link(__('Add qualification',true), array_merge(array('action' => 'qualificationnew'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_add_qualification','title' => __('Add qualification',true)));?>

    <?php
if(count($certificates) > 0){
	echo $this->Html->link(__('Show certifacations',true), array_merge(array('action' => 'certificates'), $this->request->projectvars['VarsArray']), array('class' => 'ajax icon icon_show_certificates','title' => __('Show certifacations',true)));
}
?>
<?php echo $this->Html->link(__('Print welder infos',true), array_merge(array('action' => 'pdf'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf','title' => __('Print welder infos',true)));?>
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

		unset($this->request->projectvars['VarsArray'][17]);

		// Wenn ein spezielles Zertifikat hervorgehoben werden soll
		// wir das Element hier markiert
		$QualiMarkingClass = null;

		$is_deactive = null;
		$deactive_class = null;
		$deactive_title = array();

		if($QualiMarking == $__certificates['WelderCertificate']['id']){
			$QualiMarkingClass .= 'mark_this mark_this_' . $QualiMarking;
		}

		echo '<li class="'.$QualiMarkingClass.'">';
		$this->request->projectvars['VarsArray'][16] = $__certificates['WelderCertificate']['id'];
		if(isset($__certificates['WelderCertificateData']['id']))$this->request->projectvars['VarsArray'][17] = $__certificates['WelderCertificateData']['id'];

		$disc_cert  = ucfirst($__certificates['WelderCertificate']['weldingmethod']) . ' ';
		$disc_cert .= $__certificates['WelderCertificate']['certificat'] . ' ';
		$disc_cert .= $__certificates['WelderCertificate']['third_part'] . ' ';
		$disc_cert .= __('level',true) . ' ' . $__certificates['WelderCertificate']['level'];

		if($__certificates['WelderCertificate']['active'] == 0){
			$is_deactive = 'is_deactive';
			$disc_cert .= ' - ' . __('Es existiert eine Qualifizierung mit höherem Level für dieses Verfahren.',true);
		}

		if($__certificates['WelderCertificateData']['valid_class'] == 'certification_deactive'){
			$deactive_class = 'is_deactive';
			$deactive_title = __('This certificate has the status deaktive.',true);
		}

		$valid_class = null;
		$valid_class = $__certificates['WelderCertificateData']['valid_class'];
		echo '<p class="'.$valid_class.'">';
		echo $this->Html->link($disc_cert, 'javascript:', array('title' => $disc_cert, 'escape' => false, 'class' => $is_deactive.' nolink'));
		echo '</p>';

		if($is_deactive == null){
			echo $this->Html->link($disc_cert, array_merge(array('action' => 'qualification'), $this->request->projectvars['VarsArray']), array('title' => __('Edit Qualification',true), 'escape' => false, 'class' => 'modal symbol_link_small icon_small_single_edit_qualification'));
			echo $this->Html->link(__('Zusätzliche Dokumente anzeigen und speichern',true), array_merge(array('action' => 'certificatesfiles'), $this->request->projectvars['VarsArray']), array('class' => 'modal symbol_link_small icon_small_single_file','title' => __('Zusätzliche Dokumente anzeigen und speichern',true)));
			//echo $this->Html->link(__('Delete qualification',true), array_merge(array('action' => 'qualificationdel'), $this->request->projectvars['VarsArray']), array('class' => 'modal symbol_link_small icon_small_delete','title' => __('Delete qualification',true)));

			if($__certificates['WelderCertificateData']['valid_class'] == 'certification_not_planned'){
				echo $this->Html->link(__('certification not planned',true), 'javascript:', array('class' => 'nolink symbol_link_small '.$__certificates['WelderCertificateData']['valid_class'],'title' => __('certification not planned',true)));
			}

			if($__certificates['WelderCertificateData']['valid_class'] == 'certification_not_planned') continue;

			if($__certificates['WelderCertificateData']['valid_class'] != 'certification_deactive'){
				echo $this->Html->link($__certificates['WelderCertificateData']['next_certification_date'], array_merge(array('action' => 'certificates'), $this->request->projectvars['VarsArray']), array('class' => 'ajax symbol_link_small '.$__certificates['WelderCertificateData']['valid_class'],'title' => __('Go to this certification',true) . ' (' . $__certificates['WelderCertificateData']['next_certification_date'] . ')'));
                               if( isset( $__certificates['WelderCertificate'] ['number_vt'])) {
                                   $this->request->projectvars['VarsArray'] [0] = $__certificates['WelderCertificate'] ['number_vt'];
                                    echo $this->Html->link(__('Print test',true), array_merge(array('action' => 'pdf'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf','title' => __('Print test',true)));
                               }
                        }

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
