<table cellpadding="0" cellspacing="0">
<?php
$activ_deactiv = array(0 => __('deactive'),1 => __('active'));
foreach ($Value as $_cascade):
?>

<tr>
  <td>
  <span class="for_hasmenu1 weldhead">
  <?php
  echo $this->Html->link(
    $_cascade['Cascade']['discription'],
      array('controller' => 'cascades', 'action' => 'index', $_cascade['Cascade']['topproject_id'], $_cascade['Cascade']['id'],0),array('class' => 'ajax round hasmenu1','rev' => implode('/',$this->request->projectvars['VarsArray'])));
  ?>
      </span>

      &nbsp;</td>
      <td>
      <span class="for_hasmenu1 weldhead">
      <?php
      echo $this->Html->link(
        __('Edit'),
          array('controller' => 'cascades', 'action' => 'edit', $_cascade['Cascade']['topproject_id'], $_cascade['Cascade']['id'],0),array('class' => 'round hasmenu1 modal','rev' => implode('/',$this->request->projectvars['VarsArray'])));
      ?>
          </span>

          &nbsp;</td>
  <td>
      <span class="discription_mobil">
  <?php echo __('Discription'); ?>:
  </span>
  <?php echo h($_cascade['Cascade']['discription']); ?>
      &nbsp;</td>
  <td>
      <span class="discription_mobil">
  <?php echo __('Status'); ?>:
  </span>
  <?php echo $activ_deactiv [$_cascade['Cascade']['status']]; ?>
      &nbsp;</td>
</tr>
<?php endforeach; ?>
</table>
