<?php
if(isset($this->request->data['Supplier'])) return;
if(empty($this->request->data['Cascades'])) return;
?>
<table id="" class="advancetool">
<tr>
<th><?php echo __('Description');?> </th>
<th><?php echo __('Infos');?> </th>
</tr>
<tbody>
<?php
foreach ($this->request->data['Cascades'] as $key => $value) {
  echo '<tr>';
  echo '<td >';

  echo $this->Html->link($value['Cascade']['discription'],
    array_merge(
      array(
        'action' => 'index'
      ),
      array($value['Cascade']['topproject_id'],$value['Cascade']['id'])
    ),
    array(
      'title' => $value['Cascade']['discription'],
      'class' => 'round ajax'
    )
  );

  echo '</td>';
  echo '<td class="suppliere_legend" data-id="' . $value['Cascade']['id'] . '">';
  echo '</td>';
  echo '</tr>';
}
?>

</tbody>
</table>
<?php echo $this->element('expediting/js/expediting_table_load_legend',array('loc' => 'div.subbreadcrump h5 a','attr' => 'href'));?>
