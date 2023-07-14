<div class="modalarea changelogs form">
<h2 class="changelog_header"><?php echo __('Changelog')?></h2>
<a id="btnNewLogEntry" class="changelog_add addlink" title="<?php echo __('New entry for %s', __('Changelog'))?>" onclick="createLogEntry()">Hinzufügen</a>
<?php echo $this->element("Flash/_messages"); ?>
<?php
  if (isset($FormName) && count($FormName) > 0) {
      echo $this->element("js/reload_container", [
          "FormName" => $FormName,
      ]);
      echo $this->element("js/ajax_stop_loader");
      echo $this->element("js/close_modal_auto");
      //echo "</div>";
      return;
  }
?>
<?php echo $this->Form->create("Changelog", ["class" => "login"]); ?>
<fieldset>
<?php echo $this->Form->input('log_date',  ['label' => __('Date'), "class" => "date"]); ?>
</fieldset>
<?php echo $this->Form->end(__("Submit", true)); ?>
<div id="contents" class="content clear"></div>
</div>
<?php
//echo $this->element("js/form_send_modal", ["FormId" => "ChangelogAddForm"]);
echo $this->element('image/fancybox');
echo $this->element("js/changelog_add");
echo $this->element("js/form_button_set");
echo $this->element("js/form_datefield");
echo $this->element("js/ajax_mymodal_link");
echo $this->element("js/close_modal");
echo $this->element("js/minimize_modal");
echo $this->element("js/maximize_modal");
 ?>
