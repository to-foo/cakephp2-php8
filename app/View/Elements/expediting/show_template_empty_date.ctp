<?php if(!isset($EditUrl)) return;?>
<div class="hint">
<?php
echo $this->Html->link(
    __('Edit technical Place'),
    $EditUrl,
    array(
        'class' => 'mymodal round'
    )
);
?>
</div>