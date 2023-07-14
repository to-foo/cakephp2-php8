<h3><?php echo __('Expeditings overview'); ?></h3>
<div class="quicksearch">
<?php

$StartCascade = 0;
$StartExpeditingType = 1;

if(Configure::check('ExpeditingStartCascade') && Configure::read('ExpeditingStartCascade') != 0) $StartCascade = Configure::read('ExpeditingStartCascade');
if(Configure::check('ExpeditingStartExpeditingType') && Configure::read('ExpeditingStartExpeditingType') != 0) $StartExpeditingType = Configure::read('ExpeditingStartExpeditingType');

	echo $this->Html->link(__('Open Expediting',true),
		array(
			'controller' => 'suppliers',
			'action' => 'index',
      $StartCascade,$StartExpeditingType
		),
		array(
			'class' => 'round open_expediting',
			'title' => __('Open Expediting',true)
		)
	);
?>
<?php echo $this->element('expediting/quicksearch');?>
</div>
<div class="flex_horizontal">
<?php
foreach ($SettingsStartArray as $key => $value) {

  echo '<div class="item">';

  echo $this->Html->link($value['discription'],
    array(
      'controller' => $value['controller'],
      'action' => $value['action'],
      $value['terms']
    ),
    array(
      'class' => 'landing_icon expediting_landing_icon ' . $key,
      'title' => $value['discription']
    )
  );

  echo '</div>';

}
?>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_link_landingpage_global',array('name' => 'a.expediting_landing_icon'));
echo $this->element('js/ajax_link_global',array('name' => 'a.open_expediting'));

?>
