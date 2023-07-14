<div class="error"><ul>
<?php
foreach ($validationErrors as $key => $value) {
	foreach ($value as $_key => $_value) {
		echo '<li>' . $_value . '</li>';
	}
}
?>
</ul></div>
