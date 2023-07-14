<?php

    echo '<div class="hint_box">';
    echo $this->Form->create('ReportnumberPrinting',array('class' => 'modalform'));
    echo '<h4>' . __('Report printing',true) . '</h4>';

    $PrintMessages = $this->ViewData->CollectPrintMessages($reportnumber,$errors);
    echo $this->element('reports/collect_print_unevaluated');
    echo $this->element('reports/collect_print_messages',array('PrintMessages' => $PrintMessages));

    echo '<p>';


  	if (count($errors) == 0) {
    if ($reportnumber['Reportnumber']['print'] == 0 && count($errors) == 0) {
        $print_description = __('Print orginal report', true);
    }
    if ($reportnumber['Reportnumber']['print'] > 0 && count($errors) == 0) {
        $print_description = __('Print duplicate of report', true);
    }


	 echo $this->Html->link(
        $print_description,
        array_merge(
            array('action' => 'pdf'),
            $this->request->projectvars['VarsArray']
        ),
        array('class'=>'round printlink', 'target'=>'_blank', 'title' => $print_description, 'disabled'=>(isset($this->request->data['prevent']) && intval($this->request->data['prevent'])==1))
    );
	}

    echo $this->element('reports/printing_proof_link');


    echo '</p>';
    echo $this->Form->end();
    echo '</div>';

?>
