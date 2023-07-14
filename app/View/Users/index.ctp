<?php
$activ_deactiv = array(0 => __('deactive'),1 => __('active'));
?>
<div class="modalarea">
	<h2><?php echo __('Users'); ?></h2>
	<div class="quicksearch">
	<?php
	if(isset($ControllerQuickSearch)){
		echo $this->Navigation->quickComponentSearchingModal('quicksearch',$ControllerQuickSearch,false);
	}
	?>
	</div>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th></th>
			<th><?php echo $this->Paginator->sort('username'); ?></th>
			<th><?php echo __('last Login',true); ?></th>
			<th><?php echo $this->Paginator->sort('roll_id'); ?></th>
			<th><?php echo $this->Paginator->sort('testingcomp_id'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($users as $user):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>

		<td>
		<span class="for_hasmenu1 weldhead">
		<?php echo $this->Html->link(h($user['User']['name']),
					array(
						'action' => 'edit',
						$user['Testingcomp']['id'],$user['User']['id']),
					array(
						'class'=>'round icon_edit mymodal hasmenu1',
						'rev' => $user['Testingcomp']['id'] . '/' . $user['User']['id']
						)
					);
		?>
        </span>
        &nbsp;</td>
		<td>
        <span class="discription_mobil"></span>
		<?php $this->ViewData->UserInfos($user); ?>
        &nbsp;</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Username'); ?>:
		</span>
		<?php echo h($user['User']['username']); ?>
        &nbsp;</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('last Login'); ?>:
		</span>
		<?php echo h($user['User']['nice_time']); ?>&nbsp;
		</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Rang'); ?>:
		</span>
		<?php echo h($user['Roll']['name']); ?>&nbsp;
		</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Testingcompany'); ?>:
		</span>
		<?php echo $this->Html->link($user['Testingcomp']['name'], array('controller' => 'testingcomps', 'action' => 'edit', $user['Testingcomp']['id']), array('class' => 'mymodal')); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
	<p class="paging_query">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>
	</p>
</div>
<div class="clear" id="testdiv"></div>
<script type="text/javascript">
	$(document).ready(function(){

		$("a.blocked").click(function() {
			return false;
		});

	});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
