<div class="">
<h2><?php  echo __('Welder details') . ' ' . $welder['Welder']['name'] . ' ' . $welder['Welder']['first_name'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<div id="image_wrapper" class="user_image">
<?php if(!empty( $imData)) echo '<img class="image" src="data:image/jpeg;base64, ' . $imData . ' " />';?>
</div>
</div>
<div class="current_content hide_infos_div">
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Welder','ul');?>
<div class="clear"></div>
</div>
<div class="current_content">
<?php echo $this->Html->link(__('Hide welder infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_hide_infos','title' => __('Hide welder infos',true)));?>
<?php echo $this->Html->link(__('Edit welder',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit_welder','title' => __('Edit welder',true)));?>
<?php echo $this->Html->link(__('Upload welder image',true), array_merge(array('action' => 'setpicture'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon userimage','title' => __('Upload welder image',true)));?>
<?php echo $this->Html->link(__('Show or upload documents',true),array_merge(array('action' =>'files'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_file','title' => __('Show or upload documents',true)));?>
<?php echo $this->Html->link(__('Create welder test',true),array_merge(array('action' =>'createweldertest'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_weldertest_add','title' => __('Create welder test',true)));?>
<?php echo $this->Html->link(__('Show last tests',true),array_merge(array('action' =>'getlasttests'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_tests','title' => __('Show last tests',true)));?>
<?php echo $this->Html->link(__('Send this certificate informations per email',true),array_merge(array('action' => 'email_certificate'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_email_welder','title' => __('Send welder informations',true)));?>



<div class="clear"></div>

<ul class="listemax current_content">
<li>
<li>
<?php
if($welder['Welder']['active'] > 0){
	if(isset($welder['WelderMonitoring']) && count($welder['WelderMonitoring']) > 0){
		echo $this->Html->link(__('Certificates',true),array_merge(array('action' =>'monitorings'),$this->request->projectvars['VarsArray']),array('class' => 'ajax'));
	}
	elseif(!isset($welder['WelderMonitoring']) || count($welder['WelderMonitoring']) == 0){
		echo $this->Html->link(__('Add monitoring',true),array_merge(array('action' =>'addmonitoring'),$this->request->projectvars['VarsArray']),array('class' => 'modal'));
	}
}
elseif(isset($device) && $device['Welder']['active'] == 0){
	echo '<div class="hint"><p>';
	echo __('Der Schweisser wurde deaktiviert, Überwachungen sind nicht verfügbar.',true);
	echo '</p></div>';
}
?>
</li>
</ul>
</div>
<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
//echo $this->element('js/welder_picture_upload');
?>
<script type="text/javascript">
	$(document).ready(function(){
		$("#image_wrapper").css('overflow','hidden');
		$("#dialog").css('overflow','hidden');
		$(".draggable").draggable({cursor: "crosshair"});
                $(".image").css("height","200px");
	});
    </script>
