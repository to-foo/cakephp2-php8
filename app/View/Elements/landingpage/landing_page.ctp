<?php
if(!isset($SettingsStartArray)) return;
if(count($SettingsStartArray) == 0) return;

echo '<div class="module" id="modul_container">';
foreach ($SettingsStartArray as $key => $value) {

  echo '<div class="item div_' . $key  . '">';

  echo $this->Html->link(' ',
    array(
      'controller' => $value['controller'],
      'action' => $value['action']
    ),
    array(
      'class' => ' landing_icon ' . $key,
      'title' => $value['discription']
    )
  );

  echo '</div>';

}
echo '</div>';

echo '<div id="large_window" class="infos large_window"></div>';

echo '<div class="infos last_infos">';

if(isset($LandingPageUrl['widget'])) foreach ($LandingPageUrl['widget'] as $key => $value) echo '<div id="' . $value['place'] . '" class="info_item"></div>';

echo $this->Html->link(__('Edit widget'),
  array_merge(
    array(
      'controller' => 'users',
      'action' => 'password'
    ),
    array(
      AuthComponent::user('testingcomp_id'),
      AuthComponent::user('id'),
    )
  ),
  array(
    'title' => __('Edit widget'),
    'class' => 'settinglink icon editlink'
  )
);
echo '</div>';

echo $this->element('js/ajax_link_landingpage_global',array('name' => 'a.settinglink'));
echo $this->element('js/json_request_animation');
echo $this->element('landingpage/js/js');
?>
