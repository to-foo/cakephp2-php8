<h2>
<?php echo __('Welders'); ?>
<?php echo $welder[0]['Welder']['name'];?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<div class="current_content hide_infos_div">
<h3><?php echo __('Welder infos', true);?></h3>
<?php echo $this->ViewData->ShowDataList($arrayData, $locale, 'Welder', 'dl');?>

</div>
<div class="current_content">
<?php
  echo $this->Html->link(__('Hide welder infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_toggle icon_hide_infos','title' => __('Hide welder infos',true)));
 echo $this->Html->link(__('Edit welder', true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit_welder','title' => __('Edit welder', true)));
 echo $this->Html->link(__('Print welder infos', true), array_merge(array('action' => 'pdf'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf showpdflink','title' => __('Print welder infos', true)));
 echo $this->Html->link(__('Show or upload documents', true), array_merge(array('action' =>'files'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_file','title' => __('Show or upload documents', true)));
 echo $this->Html->link(__('Add monitoring', true), array_merge(array('action' =>'addmonitoring'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_monitoring_add','title' => __('Add monitoring', true)));

?>
<ul class="listemax certificates">
<li><b class="head">
<?php
echo $this->Html->link(__('Monitoring infos', true), 'javascript:');
?>
</b>
<ul>
<?php
if (is_array($welder) && count($welder) > 0) {
    foreach ($welder as $_key => $_welder) {
      if(isset($_welder['WelderMonitoringData'])) {
        $this->request->projectvars['VarsArray'][16] = $_welder['WelderMonitoring']['id'];
        $this->request->projectvars['VarsArray'][17] = $_welder['WelderMonitoringData']['id'];

        echo '<li>';
        echo '<ul class="';
        echo $_welder['WelderMonitoringData']['valid_class'];
        echo '">';
        echo '<li>';

        echo '<p class="show_file"><strong>';
        echo $_welder['WelderMonitoring']['certificat'];
        echo '</strong></p>';

        foreach ($xmlcert->WelderMonitoring->children() as $_xmlkey => $_xml) {
          if ((trim($_xml->showinfo) != 1 || empty($_xml->showinfo))) {
            continue;
          }
          echo '<p class="show_file">';
          if ($_xml->fieldtype == 'radio') {
            echo trim($_xml->discription->$locale). ': ' . $_xml->radiooption->value[$_welder['WelderMonitoringData'] [$_xmlkey]];
          } else {
            echo trim($_xml->discription->$locale). ': ' . $_welder['WelderMonitoringData'] [$_xmlkey];
          }
          echo '</p>';

        }

        echo'<br></br>';
        echo '<p class="show_file">';
        echo $_welder['WelderMonitoringData']['certification_requested'];
        echo '</p>';
        echo '<p class="show_file">';
        echo $_welder['WelderMonitoringData']['time_to_next_certification'];
        echo '</p>';
        echo '<p class="show_file">';
        echo $_welder['WelderMonitoringData']['time_to_next_horizon'];
        echo '</p>';
        echo '<p class="show_file">';
        echo $_welder['WelderMonitoringData']['next_certification_date'];
        echo '</p>';

        if (!isset($_welder['WelderMonitoringData']['certified_file_pfath']) & $_welder['WelderMonitoring']['file'] == 1) {
          echo '<p class="show_file"><strong class="error">' . __('There is no monitoring file available.', true) . '</strong></p>';
        }

        echo '<p class="show_file">';

        echo $this->Html->link(__('Edit monitoring', true), array_merge(array('action' => 'editmonitoring'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit','title' => __('Edit monitoring', true)));
        if ($_welder['WelderMonitoringData']['file'] == 1) {
          if (isset($_welder['WelderMonitoringData']['certified_file_pfath']) && $_welder['WelderMonitoringData']['certified_file_pfath'] != '') {
            echo $this->Html->link(__('Show monitoring file', true), array_merge(array('action' => 'getmonitoringfile'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_monitoring_file ','title' => __('Show monitoring file', true)));
          } else {
            echo $this->Html->link(__('Upload monitoring file', true), array_merge(array('action' => 'monitoringfile'), $this->request->projectvars['VarsArray']), array('title' => __('Upload monitoring file', true),'class' => 'modal icon icon_upload_red'));
          }
        }

        echo $this->Html->link(__('Replace this monitoring', true), array_merge(array('action' => 'replacemonitoring'), $this->request->projectvars['VarsArray']), array('title' => __('Replace this monitoring', true),'class' => 'modal icon icon_replace'));
        echo $this->Html->link(__('Delete monitoring', true), array_merge(array('action' =>'delmonitoring'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_del','title' => __('Delete monitoring', true)));

        echo '<div class="clear"></div>';
        echo '</p>';
        echo '</li></ul></li>';
      }
    }
}
?>
</ul>
</div>
<?php
echo $this->element('js/show_pdf_link');
echo $this->element('js/toggle_element',array('Button' => '.icon_toggle','Element' => '.hide_infos_div'));
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
