<?php
if(!isset($displaychangelog)) return false;
if($displaychangelog != 1) return false;

echo $this->Html->link('changelog_link',
	array_merge(
		array(
			'controller' => 'changelogs',
			'action' => 'view' ),
		array(
			$changelog['Changelog']['id']
		)
	),
	array(
		'id' => 'changelog_link',
		'class' => 'hidden modal icon icon_view',
		'title' => __('',true)
		)
	);
?>
<script type="text/javascript">
$(document).ready(function(){
	$('#changelog_link')[0].click();
});
</script>
