<div id="header">
<h1><?php echo __('Qualitätssicherung',true);?></h1>
<span class="right">
<?php if ($authUser){ echo $this->Html->link(__('Logout', true), array('controller' => 'users', 'action' => 'logout'),array('class' => 'logout'));}?>
<?php if ($authUser){ echo $login_info['discription'];}?>
<?php if ($authUser){
echo $this->Html->link(__('Change password', true), array('controller' => $login_info['link']['conroller'], 'action' => $login_info['link']['action'],$login_info['link']['term']),$login_info['link']['options']);
}
?>
<?php if ($authUser){ echo $this->Lang->changeLang($lang_choise,$selected);} ?>
</span>
</div>
<hr class="clear dotted" />
<div id="content" class="content clear"><?php echo $this->fetch('content'); ?></div>
<div id="footer" class="footer"><a href="https://www.mbq-gmbh.info" target="_blank">&copy; MBQ Qualitätssicherung 2020</a>
</div>
