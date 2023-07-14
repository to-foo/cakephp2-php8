<div class="actions" id="top-menue">
	<?php
	echo $this->Navigation->quickSearch();
	?>
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showNavi($menues); ?></ul>
</div>

<div class="orders index inhalt">
	<h2><?php echo __('Orders');?></h2>
	<ul class="listemax">
		<li>
			<ul>
				<li>
				<?php
				echo $this->Html->link(__('Ongoing process', true),
						array('controller' => 'orders', 'action' => 'index', $projectID, 1),
						array('class'=>'ajax')
					);
				?>
				</li>
				<li>
				<?php
				echo $this->Html->link(__('Revision', true),
						array('controller' => 'orders', 'action' => 'index', $projectID, 2),
						array('class'=>'ajax')
					);
				?>
				</li>
			</ul>
		</li>
	</ul>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
