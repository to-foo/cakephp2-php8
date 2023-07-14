<div class="quicksearch">
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
</div>
<div class="reportnumbers detail">
<h2><?php echo $this->Pdf->ConstructReportName($reportnumber) ?></h2>
<div class="clear edit">
<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>
</div>
<h3><?php echo __('Signatur Examiner');?></h3>
<div class="clear"></div>
<div id="sign_container">
<div class="current_content hide_infos_div">
<dl><span><?php echo __('Created',true);?>:</span> <?php echo $Sign['Sign']['modified'];?></dl>
<dl><span><?php echo __('Logged in user',true);?>:</span> <?php echo $User;?></dl>
<dl><span><?php echo __('Testingcompany',true);?>:</span> <?php echo $Testingcomp;?></dl>
<?php if($openImage['image'] != null):?>
<dl><img src="data:image/png;base64,<?php echo $openImage['image'];?>" /></dl>
<?php endif;?>
<?php if($openImage['image'] == null):?>
<dl><?php echo __('No sign found.',true);?></dl>
<?php endif;?>
</div>
</div>
</div>
<?php echo $this->JqueryScripte->LeftMenueHeight($reportnumber); ?>
