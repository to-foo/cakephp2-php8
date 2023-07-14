<?php
    if (Configure::check('ProofPrinting') && Configure::check('ProofPrinting') == true )  {

        $this->request->projectvars['VarsArray'][8] = 3;

        echo $this->Html->link(
            __('Proof print', true),
            array_merge(
                array('action' => 'pdf'),
                $this->request->projectvars['VarsArray']
            ),
            array(
              'class'=>'round close_after_printing',
              'title' => __('Print this report'),
              'target' => '_blank',
              'disabled'=>(isset($this->request->data['prevent']) && intval($this->request->data['prevent'])==1)
            )
        );
/*
        echo $this->Html->link(
            __('Proof print', true),
            array_merge(
                array('action' => 'pdf'),
                $this->request->projectvars['VarsArray']
            ),
            array('class'=>'round showpdflink', 'title' => __('Print this report'), 'disabled'=>(isset($this->request->data['prevent']) && intval($this->request->data['prevent'])==1))
        );
*/
        $this->request->projectvars['VarsArray'][8] = 0;
    }
?>
