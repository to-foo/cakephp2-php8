<div class="testingcomps view modalarea">
<h2><?php  echo __('Testingcomp'); ?></h2>
<!-- Beginn Message -->
<?php echo $this->element('Flash/_messages');?>
<!-- Ende Message -->
<div class="current_content">
<dl><?php echo __('Name'); ?> <strong><?php echo h($testingcomp['Testingcomp']['name']); ?></strong></dl>
<dl><?php echo __('highest valid roll'); ?> <strong><?php echo h($testingcomp['Roll']['name']); ?></strong></dl>
<dl><?php echo __('Company name'); ?> <strong><?php echo h($testingcomp['Testingcomp']['firmenname']); ?></strong></dl>
<dl><?php echo __('Company name addition'); ?> <strong><?php echo h($testingcomp['Testingcomp']['firmenzusatz']); ?></strong></dl>
<dl><?php echo __('Street'); ?> <strong><?php echo h($testingcomp['Testingcomp']['strasse']); ?></strong></dl>
<dl><?php echo __('Postcode'); ?> <strong><?php echo h($testingcomp['Testingcomp']['plz']); ?></strong></dl>
<dl><?php echo __('City'); ?> <strong><?php echo h($testingcomp['Testingcomp']['ort']); ?></strong></dl>
<dl><?php echo __('Phone'); ?> <strong><?php echo h($testingcomp['Testingcomp']['telefon']); ?></strong></dl>
<dl><?php echo __('Fax'); ?> <strong><?php echo h($testingcomp['Testingcomp']['telefax']); ?></strong></dl>
<dl><?php echo __('Internet'); ?> <strong><?php echo h($testingcomp['Testingcomp']['internet']); ?></strong></dl>
<dl><?php echo __('Email'); ?> <strong><?php echo h($testingcomp['Testingcomp']['email']); ?></strong></dl>
<dl>
	<?php echo $this->Html->link(__('Edit testingcomp',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal icon icon_edit','title' => __('Edit testingcomp',true)));?>
	<?php echo $this->Html->link(__('Add user',true), array_merge(array('controller' => 'users','action' => 'add'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal icon icon_examiners_add','title' => __('Add user',true)));?>
</dl>
</div>
<h3><?php echo __('Related users'); ?></h3>
	<?php if (!empty($testingcomp['User'])): ?>

	<div class="quicksearch">
	<?php
	echo $this->element('navigation/quicksearch_user');
	//echo $this->Navigation->quickComponentSearchingModal('quicksearch',$ControllerQuickSearch,false);
	?>
	</div>

	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Name'); ?></th>
		<th></th>
		<th><?php echo __('Username'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Roll Id'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($testingcomp['User'] as $user):
//		 print '<pre>';print_r($user);print '</pre>';
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}

		?>
		<tr<?php echo $class;?>>
			<td>
			<span class="for_hasmenu1 weldhead">
			<?php
			$this->request->projectvars['VarsArray'][1] = $user['User']['id'];
			echo $this->Html->link($user['User']['name'], array_merge(array('controller' => 'users', 'action' => 'edit'),$this->request->projectvars['VarsArray']), array('class'=>'icon_edit round mymodal'));
			$this->request->projectvars['VarsArray'][1] = 0;
			?>
      </span>
      </td>
			<td><?php $this->ViewData->UserInfos($user); ?></td>
			<td><?php echo $user['User']['username']; ?></td>
			<td><?php echo $user['User']['created']; ?></td>
			<td><?php echo $user['User']['modified']; ?></td>
			<td><?php echo $Rolls[$user['User']['roll_id']]; ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
	<?php if (!empty($testingcomp['Topproject'])): ?>
	<h3><?php echo __('Related topprojects'); ?></h3>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Project name'); ?></th>
		<th><?php echo __('Project discription'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($testingcomp['Topproject'] as $topproject):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}

		?>
		<tr<?php echo $class;?>>
			<td>
			<span class="for_hasmenu1 weldhead">
			<?php
			echo $this->Html->link($topproject['projektname'], array('controller' => 'topprojects', 'action' => 'edit', $topproject['id']), array('class'=>'icon_edit round mymodal'));
			?>
            </span>
            </td>
			<td><?php echo $topproject['projektbeschreibung']; ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
