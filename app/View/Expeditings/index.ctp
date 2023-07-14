<div class="inhalt advances form">
<h2><?php echo __('Overview'); ?></h2>
<table class="advancetool">
<tbody>
  <tr>
    <th><?php echo __('Project',true);?></th>
    <th><?php echo __('Description',true);?></th>
  </tr>

  <?php
    foreach ($Topprojects as $key => $value) {

      echo '<tr>';
      echo '<td>';

      echo $this->Html->link($value['Topproject']['projektname'],
        array(
          'controller' => 'suppliers',
          'action' => 'index',
          $value['Topproject']['id'],
          $value['Cascade']['id'],
        ),
        array(
          'class'=>'ajax round',
          'title' => $value['Topproject']['projektname']
        )
      );

      echo '</td>';
      echo '<td>';
      echo $value['Topproject']['projektbeschreibung'];
      echo '</td>';
      echo '</tr>';

    }
  ?>
</tbody>
</table>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
?>
