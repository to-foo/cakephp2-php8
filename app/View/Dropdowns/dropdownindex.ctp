<?php
$activ_deactiv = array(0 => __('no'),1 => __('yes'));
?>
<div class="modalarea">
	<h2><?php if(isset($dropdowns[0]['Dropdown'][$select_lang])) echo __($dropdowns[0]['Dropdown'][$select_lang]); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('discription'); ?></th>
			<th>
			<?php
			if(isset($has_dependencies) && $has_dependencies == true){
				echo $this->Paginator->sort('dependencies');
			}
			?>
            </th>
			<th><?php echo $this->Paginator->sort('testingcomp_id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id'); ?></th>
			<th><?php echo $this->Paginator->sort('global'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($modelDatas as $modelData):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td>
        <span class="contexmenu_weldposition for_hasmenu1">
		<?php
		$varsArray = $VarsArray;
		$varsArray[9] = $modelData[$Model]['id'];

		echo $this->Html->link(
			h($modelData[$Model]['discription']),
			array_merge(array('action' => 'dropdownedit'), $varsArray),
			array(
				'class' => 'round icon_edit mymodal hasmenu1',
				'rev' => join('/', $varsArray),
				'disabled' => $protected
			)
		);

		?>
        </span>
        </td>
        <td>
        <span class="contexmenu_weldposition">
		<?php
		if(isset($has_dependencies) && $has_dependencies == true){
			$this->request->projectvars['VarsArray'][9] = $modelData[$Model]['id'];
			echo $this->Html->link(__('Dependencies'),array_merge(array(
									'controller' => 'dependencies',
									'action' => 'index'
									),$this->request->projectvars['VarsArray']),
									array('class' => 'mymodal round icon_edit')
								);
		}
		?>
		</span>
       </td>
		<td>
		<span class="discription_mobil">
		<?php echo __('Testingcomp');?>:
		</span>
		<?php
		echo h($modelData['Testingcomp']['name']);
		?>&nbsp;
        </td>
		<td>
		<span class="discription_mobil">
		<?php echo __('User');?>:
		</span>
		<?php
		echo h($modelData['User']['name']); ?>
        &nbsp;
        </td>
		<td>
		<span class="discription_mobil">
		<?php echo __('global');?>:
		</span>
		<?php
		echo $activ_deactiv[$modelData[$Model]['global']]; ?>
        &nbsp;
        </td>
		<td>
		<span class="discription_mobil">
		<?php echo __('modified');?>:
		</span>
		<?php
		echo h($modelData[$Model]['modified']); ?>
        &nbsp;
        </td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging mymodal">
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
$(document).ready(function() {

    $("span.for_hasmenu1").contextmenu({
        delegate: ".hasmenu1",
        autoFocus: true,
        preventContextMenuForPopup: true,
        preventSelect: true,
        taphold: true,
        menu: [{
                title: "<?php echo __('Bearbeiten');?>",
                disabled: <?php echo $protected ? 'true' : 'false'; ?> ,
                cmd : "editevalution",
                action: function(event, ui) {
                    $("#dialog").load("dropdowns/dropdownedit/" + ui.target.attr("rev"), {
                        "ajax_true": 1
                    })
                },
                uiIcon: "qm_edit"
            },
            {
                title: "----"
            },
            {
                title: "<?php echo __('Löschen');?>",
                disabled: <?php echo $protected ? 'true' : 'false'; ?> ,
                cmd : "duplicatevalution",
                action: function(event, ui) {
                    $("#dialog").load("dropdowns/dropdowndelete/" + ui.target.attr("rev"), {
                        "ajax_true": 1
                    })
                },
                uiIcon: "qm_delete"
            },
        ],

        select: function(event, ui) {},
    });
});
</script>
<?php

if(isset($FormName)){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	}

echo $this->JqueryScripte->ModalFunctions();
?>
