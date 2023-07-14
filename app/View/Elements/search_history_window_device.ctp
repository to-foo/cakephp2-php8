<div class="search_history">
<h3><?php echo __('Search history',true);?></h3>
<ol>
<?php
//pr($HistorySearch);
foreach($HistorySearch['link'] as $_key => $_data){
	echo '<li id="history_' . $_key . '">';
	echo $_data;
	echo '<br>';
//	echo $this->Html->link(__('Overload search form',true), array_merge(array('action' => 'update'),$this->request->projectvars['VarsArray']),array('rev' => $_key,'class' => 'round search_history'));
	if($HistorySearch['devices'][$_key] > 0) echo $this->Html->link(__('Show foundet devices',true), '',array('rev' => $_key,'class' => 'round search_history_result'));
	echo '</li>';
}
?>
</ol>
</div>
