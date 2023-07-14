<div class="changelog">
<div class="header"><h2><?php echo __('Changelog')?></h2></div>
<?php echo $this->element("Flash/_messages"); ?>
  <div class='log-wrapper'>
      <?php

      foreach ($Changelogs as $logkey => $log) {

        if(!isset($log['Changelog'])) continue;

        echo "<section class='log'>";
        echo "<h3>". $log['Changelog']["log_date_de"] ."</h3>";
        $logID = $log['Changelog']['id'];
        echo "<ul>";


        foreach ($log['ChangelogData'] as $key => $value) {
          echo "<li>";
          echo "<div class='label-wrapper'>";
          echo "<span class='label-improved ".$value["category"]."' title='" . __($value["category"]) . "'>";
          echo __($value["category"]);
          echo "</span>";
          echo "</div>";
          echo "<div class='detail-wrapper'>";
          echo "<div class='log headline'>";
          echo __($value["category"]) . ': ' . $value["title"];
          echo "</div>";
          echo "<div class='log innertext'>";
          echo $value["content"];
          echo "</div>";
          echo "<div class='image-wrapper'>";

          if(isset($log['Changelogfile'])){
            foreach ($log['Changelogfile'] as $_key => $_value) {

              if(empty($_value['imagedata'])) continue;
              if($_value['changelog_data_id'] == $value['id']){
                echo '<a href="#image_container_' . $_value['id'] . '"  class="fancybox_changelog"><img src="' . $_value['imagedata'] . '"/></a>';
                echo '<span style="display: none;"  id="image_container_' . $_value['id'] . '">';
                echo '<img src="' . $_value['imagedata'] . '"/>';
                echo '</span>';
              }
            }
          }

          echo "</div>";
          echo "</div>";
          echo "</li>";

        }
        echo "</ul>";
        echo "</section>";
      }
      ?>
    </div>
  </div>
</div>
<script>
$(function() {
  $(".fancybox_changelog").fancybox();
});
</script>
<?php
echo $this->element('image/fancybox');
echo $this->element("js/form_button_set");
echo $this->element("js/ajax_mymodal_link");
echo $this->element("js/close_modal");
echo $this->element("js/minimize_modal");
echo $this->element("js/maximize_modal");
echo $this->element('js/scroll_modal_top');
?>
