<?php
echo '
<ul class="expediting_legend_modal">
<li class="critical tooltip" title="' . __('Critical (soll date > current date + waiting period)',true) . '"><span> </span></li>
<li class="delayed tooltip" title="' . __('Delayed (soll date > current date and <  current date + waiting period)',true) . '"><span> </span></li>
<li class="plan tooltip" title="' . __('In plan (soll date < current date)',true) . '"><span> </span></li>
<li class="future tooltip" title="' . __('No date informations stored',true) . '"><span> </span></li>
<li class="finished tooltip" title="' . __('Completed',true) . '"><span> </span></li>
<div class="clear"></div>
</ul>';
?>
