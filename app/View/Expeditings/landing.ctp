<h3><?php echo __('Overview'); ?></h3>
<?php echo $this->element('Flash/_messages');?>
<div class="quicksearch">
<?php
//TODO: Die Startcascade muss noch anderst gelöst werden

$StartCascade = 0;
$StartExpeditingType = 1;

if(Configure::check('ExpeditingStartCascade') && Configure::read('ExpeditingStartCascade') != 0) $StartCascade = Configure::read('ExpeditingStartCascade');
if(Configure::check('ExpeditingStartExpeditingType') && Configure::read('ExpeditingStartExpeditingType') != 0) $StartExpeditingType = Configure::read('ExpeditingStartExpeditingType');

echo $this->Html->link(__('Open Expediting',true),
  array(
    'controller' => 'suppliers',
    'action' => 'index',
    $StartCascade ,$StartExpeditingType
  ),
  array(
    'class' => 'round open_expediting',
    'title' => __('Open Expediting',true)
  )
);
?>
<?php
echo $this->Html->link(__('Back',true),
  array(
    'controller' => 'expeditings',
    'action' => 'start'
  ),
  array(
    'class' => 'icon backlink',
    'title' => __('Back',true)
  )
);
/*
echo $this->Html->link(__('Add',true),
  array(
    'controller' => 'expeditings',
    'action' => 'addexpeditingobject',
    1
  ),
  array(
    'class' => 'icon addlink',
    'title' => __('Add',true)
  )
);
*/
?>
<?php echo $this->element('expediting/quicksearch');?>
</div>

<?php // echo $this->element('expediting/expediting_legend');?>

<div class="users index inhalt">
<?php echo $this->element('expediting/expediting_table_landingpage_index');?>
<?php // echo $this->element('page_navigation');?>
</div>

<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_link_landingpage_global',array('name' => 'div.quicksearch a'));
echo $this->element('js/ajax_link_global',array('name' => 'a.open_expediting'));

?>
