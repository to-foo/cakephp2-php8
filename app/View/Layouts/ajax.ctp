<div id="header">
<h1><?php echo __('Qualitätssicherung',true);?></h1>
<span class="right">
<?php if ($authUser){ echo $this->Html->link(__('Logout', true), array('controller' => 'users', 'action' => 'logout'),array('class' => 'logout'));}?>
<?php if ($authUser){ echo $login_info['discription'];}?>
<?php if ($authUser){
echo $this->Html->link(__('Change password', true),
  array_merge(
    array(
      'controller' => $login_info['link']['conroller'],
      'action' => $login_info['link']['action'],
    ),$login_info['link']['term']),
    $login_info['link']['options']
  );
}
?>
<?php if ($authUser){ echo $this->Lang->changeLang($lang_choise,$selected);} ?>
</span>
</div>
<hr class="clear dotted" />
<div class="top_navigation">
<?php if ($authUser){ echo  $this->Navigation->showBreads(isset($breads)? $breads : '');} ?>
<?php echo $this->Navigation->sysLinks(isset($SettingsArray) ? $SettingsArray : '');?>
<div class="clear"></div>
</div>
<div class="content_wrapper">
<div id="content" class="content clear"><?php echo $this->fetch('content'); ?></div>
<div id="right_site_area" class="right_site_area"></div>
</div>
<div id="bottom_hint_area" class="bottom_hint_area"></div>

<?php

if($this->request->params['controller'] <> 'dropdowns') {
  echo $this->element('js/modal_close_hide_auto');
}
?>
