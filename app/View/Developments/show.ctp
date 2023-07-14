<div class="index inhalt">
<h2>
<?php echo __('Show progress');?> 
</h2>
<ul class="listemax">
<li class="icon_discription icon_view"><span></span>
<?php echo $this->Navigation->makeLink('developments','orders',__('Orders Overview'),'ajax',null,$this->request->projectvars['VarsArray']); ?>
</li>
<li class="icon_discription icon_view"><span></span>
<?php echo $this->Navigation->makeLink('developments','examiners',__('Examiner times Overview'),'ajax',null,$this->request->projectvars['VarsArray']); ?>
</li>
<li class="icon_discription icon_view"><span></span>
<?php echo $this->Navigation->makeLink('developments','statistics',__('Statistics Overview'),'ajax',null,$this->request->projectvars['VarsArray']); ?>
</li>
</ul>
</div>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>



