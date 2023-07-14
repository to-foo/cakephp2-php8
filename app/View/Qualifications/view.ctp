<div class="qualifications view modalarea">
<h2><?php  echo __('Qualification'); ?></h2>
	<dl>
		<dt><?php echo __('Examiner-id'); ?></dt>
		<dd>
			<?php echo h($qualification['Qualification']['examiner-id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Certification-number'); ?></dt>
		<dd>
			<?php echo h($qualification['Qualification']['certification-number']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Testingmethod'); ?></dt>
		<dd>
			<?php echo h($qualification['Testingmethod']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Level'); ?></dt>
		<dd>
			<?php echo h($qualification['Qualification']['level']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Timeperiod'); ?></dt>
		<dd>
			<?php echo h($qualification['Qualification']['timeperiod']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($qualification['Qualification']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($qualification['Qualification']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($qualification['User']['name'], array('controller' => 'users', 'action' => 'view', $qualification['User']['id']),array('class' => 'mymodal')); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Testingcomp'); ?></dt>
		<dd>
			<?php echo $this->Html->link($qualification['Testingcomp']['name'], array('controller' => 'testingcomps', 'action' => 'view', $qualification['Testingcomp']['id']),array('class' => 'mymodal')); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Supervisors'); ?></dt>
		<dd>
			<?php echo h($qualification['Qualification']['supervisors']); ?>
			&nbsp;
		</dd>
		<?php 		
		echo $this->Html->link(__('Edit this qualification', true), array('action' => 'edit', $qualification['Qualification']['id']), array('class'=>'round mymodal')); 
		?>
		<div class="clear edit"></div>			
	</dl>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>