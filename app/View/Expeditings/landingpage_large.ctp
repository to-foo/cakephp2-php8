<h3><?php echo __('Expeditings'); ?></h3>
<div class="inhalt advances form">
  <div class="quicksearch">
  <?php
  echo $this->Html->link(__('Open expediting modul',true),
  	array(
  		'controller' => 'expeditings',
  		'action' => 'index'
  	),
  	array(
  		'class' => 'round ajax',
  		'title' => __('Open expediting modul',true)
  	)
  );
  ?>
  </div>
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
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
