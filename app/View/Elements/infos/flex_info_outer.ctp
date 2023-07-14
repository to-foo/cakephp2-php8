<div class=" flex_info">
<?php
echo $this->element('infos/flex_info',array('model' => 'Ticket','field' => 'technical_place','label' => __('Techical Place',true)));
echo $this->element('infos/flex_info',array('model' => 'Ticket','field' => 'count_welds','label' => __('Count Welds',true)));
echo $this->element('infos/flex_info',array('model' => 'Ticket','field' => 'spools','label' => __('Spools',true)));
echo $this->element('infos/flex_info',array('model' => 'Ticket','field' => 'created','label' => __('Created',true)));
?>
</div>
