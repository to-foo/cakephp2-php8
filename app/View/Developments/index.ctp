<div class="modalarea">
<h2><?php echo __('Progress');?></h2>
<ul class="listemax">
<li><?php echo $this->Html->link(__('Fortschritte auswählen'), array('action' => 'overview'),array('class' => 'mymodal')); ?></li>
<li><?php echo $this->Html->link(__('Fortschritte erstellen'), array('action' => 'add'),array('class' => 'mymodal')); ?></li>
<!--
<li><?php echo $this->Html->link(__('Daten für Fortschritt bereitstellen'), array('action' => 'create'),array('class' => 'mymodal')); ?></li>
-->
</ul>
</div>
<div class="clear" id="testdiv"></div> 
<?php echo $this->JqueryScripte->ModalFunctions(); ?>


