<div class="quicksearch">
<?php echo $this->element('searching/search_quick_reportnumber', array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
</div>
<div class="reportnumbers detail">
<h2><?php echo $this->Pdf->ConstructReportName($reportnumber, 3) ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if (isset($writeprotection) && $writeprotection) {
  echo $this->Html->tag('p', __('Report is writeprotected - changes will not be saved.'), array('class'=>'error'));
  echo $this->Html->tag('p', '&nbsp;');
}
?>
<div class="clear edit">
<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>
<div id="refresh_revision_container"></div>
</div>
<div class="revisionsform">
<?php
if($reportnumber['Reportnumber'] ['revision'] > 0 && !empty($reportnumber['RevisionValues'])) {
  $field = 'all';
  $modelpart = 'Reportfile';
/*
  echo $this->Html->link('Showrevisions',array(
          'controller' => 'reportnumbers',
          'action' => 'showrevisions',
          $this->request->projectvars['VarsArray'][0],
          $this->request->projectvars['VarsArray'][1],
          $this->request->projectvars['VarsArray'][2],
          $this->request->projectvars['VarsArray'][3],
          $this->request->projectvars['VarsArray'][4],
          $this->request->projectvars['VarsArray'][5],
          $this->request->projectvars['VarsArray'][6],
          $this->request->projectvars['VarsArray'][7],
        ),
        array(
          'id' => $modelpart.'/'.$field,
          'class'=> 'tooltip_ajax_revision',
          'title'=> __('Content will load...', true)
        )
      );
*/
    }
?>
</div>
<h3><?php echo __('Fileupload', true);?></h3>

<?php
echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "application/pdf"));
echo $this->element('form_upload_report',array('writeprotection' => $writeprotection));
?>
<div class="uploadform">

<div class="clear"></div>

		<div class="files">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<th><?php echo __('Filename', true); ?></th>
          <th></th>
					<th><?php echo __('Uploaded from', true); ?></th>
					<th><?php echo __('Uploaded time', true); ?></th>
				</tr>
				<?php foreach ($reportfiles as $_reportfiles): ?>
				<tr>
					<td>
					<span class="for_hasmenu1 weldhead">
					<?php
          $this->request->projectvars['VarsArray'][6] = $_reportfiles['Reportfile']['id'];

          if($_reportfiles['Reportfile']['file_exists'] == 1){
          echo $this->Html->link(
              $_reportfiles['Reportfile']['basename'],
              array_merge(array('action' => 'getfile'), $this->request->projectvars['VarsArray']),
              array(
                'class'=>'round icon_file filelink hasmenu1',
                'rev' => implode('/', $this->request->projectvars['VarsArray']),
                )
              );
          } elseif($_reportfiles['Reportfile']['file_exists'] == 0){
            echo $this->Html->link(
                $_reportfiles['Reportfile']['basename'],
                array_merge(array('action' => 'delfile'), $this->request->projectvars['VarsArray']),
                array(
                  'class'=>'round icon_file modal ',
                  )
                );
            echo ' ' . __('File not found.',true);
          }
          ?>
			    </span>
					</td>
          <td>
          <?php
          if($_reportfiles['Reportfile']['file_exists'] == 1){
          echo $this->Html->link(
              $_reportfiles['Reportfile']['basename'],
              array_merge(array('action' => 'delfile'), $this->request->projectvars['VarsArray']),
              array(
                'class'=>'icon icon_delete modal',
                )
              );
              echo $this->Html->link(
                  $_reportfiles['Reportfile']['basename'],
                  array_merge(array('action' => 'filediscription'), $this->request->projectvars['VarsArray']),
                  array(
                    'class'=>'icon icon_edit modal',
                    )
                  );
          }
          ?>
          </td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Examiner ID'); ?>:
					</span>
					<?php echo $_reportfiles['Reportfile']['user_id'];?>
                    </td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Created'); ?>:
					</span>
					<?php echo $_reportfiles['Reportfile']['created'];?>
                    </td>
				</tr>
				<?php endforeach; ?>
			</table>
			<span class="clear"></span>
		</div>
	</div>
<div class="clear" id="testdiv"></div>
<?php
$url = $this->Html->url(
    array('controller' => 'reportnumbers', 'action' => 'files',
     $reportnumber['Reportnumber']['topproject_id'],
     $reportnumber['Reportnumber']['cascade_id'],
     $reportnumber['Reportnumber']['order_id'],
     $reportnumber['Reportnumber']['report_id'],
     $reportnumber['Reportnumber']['id']
    )
  );
?>

<?php
$CurrentUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=> $this->request->params['action']),$this->request->projectvars['VarsArray']));
echo $this->Form->input('CurrentUrl',array('value' => $CurrentUrl,'type' => 'hidden'));

$attribut_disabled = false;

if ($reportnumber['Reportnumber']['status'] > 0) $attribut_disabled = true;
if (isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;
if (Configure::check('FileUploadAfterClosing') && Configure::read('FileUploadAfterClosing') == true) $attribut_disabled = false;
if ($attribut_disabled === true) echo $this->element('js/form_hide_upload_buttons');

if (Configure::read('RefreshReport') == true && $reportnumber['Reportnumber']['status'] == 0) echo $this->element('refresh_report');
if (isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) echo $this->element('refresh_revision');

echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/revisions_link');
echo $this->element('js/form_upload_report',array('container' => '#container','url' => $url,'FileLabel' => __('Select files', true),'Extension' => 'pdf'));
echo $this->element('js/tooltip_revision');
?>
