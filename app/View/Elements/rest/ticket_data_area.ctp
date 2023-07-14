<?php
if(empty($ticket)) return;

echo '<div id="ticket_table_content">';
echo $this->element('rest/ticket_data_area_table');
echo '</div>';
?>
