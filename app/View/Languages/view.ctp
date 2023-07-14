<div class="languages view">
<h2><?php  echo __('Language'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($language['Language']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Locale'); ?></dt>
		<dd>
			<?php echo h($language['Language']['locale']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Discription'); ?></dt>
		<dd>
			<?php echo h($language['Language']['beschreibung']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit language'), array('action' => 'edit', $language['Language']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete language'), array('action' => 'delete', $language['Language']['id']), null, __('Are you sure you want to delete # %s?', $language['Language']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List languages'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New language'), array('action' => 'add')); ?> </li>
	</ul>
</div>
