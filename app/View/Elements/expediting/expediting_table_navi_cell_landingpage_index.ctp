<td class="">
<?php
//pr($_examiner);
$OrderLink = $this->request->projectvars['VarsArray'];
$OrderLink[0] = $_examiner['Supplier']['topproject_id'];
$OrderLink[1] = $_examiner['Supplier']['cascade_id'];
$OrderLink[2] = $_examiner['Supplier']['id'];

$status_icon = 'icon_epediting';
//pr($this->request->data['ExpeditingLinks']);
if(isset($Priority[$_examiner['Supplier']['priority']])) $status_icon = $Priority[$_examiner['Supplier']['priority']];

echo $this->Html->link($_examiner['Supplier']['unit'] . '-' . $_examiner['Supplier']['equipment'],
			array_merge(array('controller'=>'suppliers','action' => 'overview'),$OrderLink),
			array('title' => __('Goto',true) . ' ' . $_examiner['Supplier']['unit'] . '-' . $_examiner['Supplier']['equipment'],'class'=>'round  ajax')
		);
?>
</td>
<?php
echo '<td class="suppliere_legend" data-id="' . $_examiner['Supplier']['cascade_id'] . '"></td>';
?>
