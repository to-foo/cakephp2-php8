<?php
echo '<div class="error"><p>';
echo __('This function is not available.', true);
echo ' ';
echo __('A revision is in progress.', true);
echo '</p></div>';
echo '</div>';
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
