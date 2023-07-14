<?php
echo '<div class="areas" id="main_area">';
echo $this->Form->create('Advance');
echo '<fieldset>';
echo '<fieldset>';
echo $this->Form->input('id');
echo $this->Form->input('name');
echo $this->Form->input('status',array('options' => array('0' => __('active',true), '1' => __('deactive',true)),'type' => 'radio'));
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('description');
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('Topproject',array('multiple' => false,'label' => __('Involved projects',true),'selected' => $Advance['Topproject']['selected']));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingcomp',array('multiple' => true,'label' => __('Involved companies',true),'selected' => $Advance['Testingcomp']['selected']));
echo '</fieldset>';
echo $this->Form->end(__('Submit'));
echo '</div>';
?>
</div>
