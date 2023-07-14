<div class="inhalt testinginstructions form">
<h2><?php echo __('Report Template'); ?></h2>
<div class="quicksearch">
<?php echo $this->element('searching/search_quick_project',array('action' => 'quicksearch','minLength' => 1,'discription' => __('Template Name', true)));?>
</div>
</div>
<div class="flex_horizontal">
<div class="item"><?php echo $this->Html->link(__('Evaluation Template',true), array('controller' => 'templates', 'action' => 'index',2), array('title' => __('Evaluation template',true),'class' => 'icon_start_landingpage'));?></div>
</div>
<div class="users index inhalt">
	<table cellpadding="0" cellspacing="0" class="advancetool">
	<tr>
    <th class="collaps"></th>
		<th class="collaps"><?php echo __('Name',true); ?></th>
    <th class="collaps"><?php echo __('Testingmethod',true); ?></th>
		<th><?php echo __('Discription'); ?></th>
	</tr>
  <?php foreach ($this->request->data as $key => $value): ?>
    <tr>
      <td>
      <?php
			echo $this->Html->link(__('Edit template',true), array_merge(array('controller' => 'templates', 'action' => 'edit'),array(1,$value['Template']['id'])), array('title' => __('Edit template',true),'class' => 'icon icon_edit ajax'));
			echo $this->Html->link(__('Delete template',true), array_merge(array('controller' => 'templates', 'action' => 'delete'),array(1,$value['Template']['id'])), array('title' => __('Delete template',true),'class' => 'icon icon_delete modal'));
			?>
      </td>
      <td><?php echo $value['Template']['name'];?></td>
      <td><?php echo $value['Testingmethod']['verfahren'];?></td>
      <td><?php echo $value['Template']['description'];?></td>
    </tr>
  <?php endforeach; ?>
	</table>
</div>
</div>
<?php echo $this->element('Flash/_messages');?>
<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
