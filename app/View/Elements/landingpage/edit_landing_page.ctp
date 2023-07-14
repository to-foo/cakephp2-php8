<?php
echo '<fieldset>';

echo $this->Form->input('Landingpage.large', array(
      'label' => __('Display main window',true),
      'options' => $landingpageslarge,
      'value' => $LandingPageValue['large'] ?? '',
      'empty' => '(choose one)'
));

echo '</fieldset><fieldset class="multiple_field">';

echo $this->Form->input('Landingpage.widget', array(
      'label' => __('Selection widgets',true),
      'multiple' => true,
      'options' => $landingpageswidget,
      'value' => $LandingPageValue['widget'] ?? '',
));

echo '</fieldset><fieldset>';
?>
