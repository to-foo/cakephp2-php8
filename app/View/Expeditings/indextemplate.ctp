<div class="modalarea detail">
<h2><?php echo __('Show ITPs');?> </h2>
<?php echo $this->element('Flash/_messages');?>
<div class="hint">
<?php 
echo $this->Html->link(__('Create ITP',true),
    array_merge(
       array(
        'controller' => 'expeditings',
          'action' => 'createtemplate'
       ),
       $this->request->projectvars['VarsArray']
    ),
    array(
        'class' => 'mymodal round',
    )
);
echo $this->Html->link(__('Edit expediting steps',true),
    array_merge(
        array(
            'controller' => 'expeditings',
            'action' => 'add'
        ),
        $this->request->projectvars['VarsArray']
        ),
    array(
        'class' => 'mymodal round',
    )
);
?>
</div>
<?php echo $this->element('expediting/index_tempate');?>
</div>
