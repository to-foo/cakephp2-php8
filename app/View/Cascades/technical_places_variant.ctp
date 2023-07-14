
<div class="equipments index inhalt">
<h2></h2>
 <ul class="listemax">
<?php
foreach($tpv as $_key => $_tpv){
	echo '<li>';
	$this->request->projectvars['VarsArray'][2] = $_tpv['Technicalplacevariant']['id'];
	echo $this->Html->link($_tpv['Technicalplacevariant']['name'],array_merge(array('controller'=>'technicalplaces','action' => 'index'),$this->request->projectvars['VarsArray']),array('class' => 'ajax'));
	echo '</li>';
}
?>
</ul>
</div>

<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
