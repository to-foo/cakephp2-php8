<?php
if(Configure::check('SendTicketReport') == false) return;
if(Configure::read('SendTicketReport') == false) return;

echo '<div class="rest_link wacker_link">';
echo $this->Html->link('Open Wacker Digipipe',array_merge(array('controller' => 'rests','action' => 'tickets'),$this->request->projectvars['VarsArray']),array('class' => 'round ajax'));
echo '</div>';
?>
<style type="text/css" data-title="text/css">

div.rest_link {
  margin: 0 10px 0 10px;
}
</style>
