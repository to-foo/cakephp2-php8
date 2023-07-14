<div id="header">
	<span class="right">
		<?php if ($authUser){ echo $this->Html->link(__('Logout', true), array('controller' => 'users', 'action' => 'logout'));}?>
		<?php if ($authUser){ echo $login_info;}?>
		<?php if ($authUser){ echo $this->Lang->changeLang($lang_choise,$selected);} ?>
	</span>
</div>
<hr class="clear dotted" />
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php if ($authUser){ echo $this->Navigation->showBreadcrumb($breadcrumbs);} ?>
<div id="content"><?php echo $this->fetch('content'); ?></div>
<div id="footer"><?php  echo $this->element('sql_dump'); ?></div>