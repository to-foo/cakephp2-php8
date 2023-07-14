<?php
if(count($errors) > 0) return;
if(!isset($this->request->tablenames[0])) return;
if(!isset($EmailAdresses)) return;

if ($reportnumber['Reportnumber']['print'] == 0) $print_description = __('Send orginal report to the following email addresses:', true);
if ($reportnumber['Reportnumber']['print'] > 0) $print_description = __('Send duplicate of report to the following email addresses:', true);

$this->request->data['Reportnumber']['attachments'] = 1;

$Url =  $this->Html->url(array_merge(array('action' => 'collectpdf'),$this->request->projectvars['VarsArray']));

echo '<div class="hint_box">';
echo $this->Form->create('Reportnumber', array('url' => $Url,'class' => 'modalform'));
echo '<h4>' . __('Email sending',true) . '</h4>';

//$PrintMessages = $this->ViewData->CollectPrintMessages($reportnumber,$errors);
//echo $this->element('reports/collect_print_unevaluated');
//echo $this->element('reports/collect_print_messages',array('PrintMessages' => $PrintMessages));

echo '<p>' . $print_description . '</p>';
echo '<p>' . implode(', ',$EmailAdresses) . '</p>';

echo '<fieldset>';
echo $this->Form->input('attachments', array(
    'options' => array('1' => __('Send with attachments'), '0' => __('Send without attachments')),
    'type' => 'radio',
    'legend' => false,
));
echo '</fieldset>';

if(count($EmailAdresses) == 0){
  echo '<div id="SendReportMailResponse">';
  echo '<div id="" class="message_info">';
  echo '<span class="warning">' . __('No email adresses available.',true) . '</span>';
  echo '</div>';

  echo '</div>';
  echo $this->Form->end();
  echo '</div>';
  echo $this->element('js/form_button_set');
  return;
}

echo '<div id="SendReportMailResponse">';
echo $this->element('Flash/_messages');
echo '</div>';

echo $this->Html->link(
        __('Send',true),
        array_merge(
            array('action' => 'collectpdf'),
            $this->request->projectvars['VarsArray']
        ),
        array('class'=>'round sendmail', 'title' => $print_description, 'disabled'=>(isset($this->request->data['prevent']) && intval($this->request->data['prevent'])==1))
    );

echo $this->Form->end();
echo '</div>';

echo $this->element('js/form_button_set');
echo $this->element('reports/js/send_report_mail');
?>
