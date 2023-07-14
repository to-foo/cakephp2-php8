<script type="text/javascript">
$(document).ready(function(){
$("#dialog").css("background-image","none");
});
</script>
<?php  echo $this->fetch('script'); ?>
<?php if(isset($SettingsArray)) echo $this->Navigation->sysLinksModal($SettingsArray);?>
<div class="modalarea">
<h2><?php echo __('Overview delivering dates', true);?></h2>
<div class="current_content"><ul>
<li>
<?php
echo $this->Html->link(__('Export data',true),
    array_merge(array('controller'=>'suppliers','action' => 'export'),$this->request->projectvars['VarsArray']),
    array(
      'title' => __('Export data',true),
      'class'=>'icon icon_export',
      )
  );
?>

<?php
  $DownloadVars = $this->request->projectvars['VarsArray'];
  $DownloadVars[] = $this->request->data['case'];

  echo $this->Html->link(__('Download image',true),
      array_merge(array('controller'=>'suppliers','action' => 'download'),$DownloadVars),
      array(
        'title' => __('Download image',true),
        'class'=>'icon icon_download',
        )
    );
?>
</li>
<li>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'suppliers','action' => 'diagramm'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('ThisRequstUrl',array('type' => 'hidden','value' => $url));
echo $this->Form->input('change_diagramm_view',array(
	'class' => 'filter',
	'div' => true,
	'label' => false,
  'options' => array(2 => 'Gantt',3 => 'Line'),
  'selected' => $this->request->data['case']
  )
);
?>
</li>
</ul></div>
<div class="diagramm_image">
<?php
$img = $this->Image->get($this->fetch('content'));
if(!$img) echo $this->fetch('content');
else echo $img;
?>
</div>
</div>
<div id="footer" class="clear"><?php  echo $this->element('sql_dump'); ?></div>
<?php
echo $this->element('expediting/ajax_modal_change_delivery_diagram');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
