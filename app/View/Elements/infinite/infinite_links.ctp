<script type="text/javascript">

$(document).ready(function(){

	$("#container a.ajax, #container div.breadcrumbs a, #container .paging a").off('click');

	$("#container a.ajax, #container div.breadcrumbs a, #container .paging a").on("click",function() {
		$("#container").load($(this).attr("href"), {
			"ajax_true": 1
		})
		return false;
	});
});
</script>

<?php
if(isset($paging['next_page']) && $paging['next_page'] > 0 && isset($paging['pages'][$paging['next_page']]['value']) && $paging['pages'][$paging['next_page']]['value'] > 0){
	echo '<script type="text/javascript">';
	echo 'var next_page = ' . $paging['pages'][$paging['next_page']]['value'] . ';';
	echo 'var url_next = "' . $paging['pages'][$paging['next_page']]['url'] . '";';
	echo '</script>';
} else {
	echo '<script type="text/javascript">';
	echo 'var next_page = false;';
	echo 'var url_next = false;';
	echo '</script>';
}
if(isset($paging['prev_page']) && $paging['prev_page'] > 0 && isset($paging['pages'][$paging['prev_page']]['value']) && $paging['pages'][$paging['prev_page']]['value'] > 0){
	echo '<script type="text/javascript">';
	echo 'var prev_page = ' . $paging['pages'][$paging['prev_page']]['value'] . ';';
	echo 'var url_prev = "' . $paging['pages'][$paging['prev_page']]['url'] . '";';
	echo '</script>';
} else {
	echo '<script type="text/javascript">';
	echo 'var prev_page = false;';
	echo 'var url_prev = false;';
	echo '</script>';
}if(isset($paging['current_page']) && $paging['current_page'] > 0 && isset($paging['pages'][$paging['current_page']]['value']) && $paging['pages'][$paging['current_page']]['value'] > 0){
	echo '<script type="text/javascript">';
	echo 'var current_page = ' . $paging['pages'][$paging['current_page']]['value'] . ';';
	echo 'var url_current = "' . $paging['pages'][$paging['current_page']]['url'] . '";';
	echo '</script>';
} else {
	echo '<script type="text/javascript">';
	echo 'var current_page = false;';
	echo 'var url_current = false;';
	echo '</script>';
}
?>
