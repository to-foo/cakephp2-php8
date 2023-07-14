<?php
echo $this->Form->create('Examiner',array('class' => 'open_close_order','id' => 'SortExamierTable'));

$sorting_icon = array(
  'asc' => '<span class="small_icon icon_asc"></span>',
  'desc' => '<span class="small_icon icon_desc"></span>'
);

$options = array();
$options[0] ='-';
$options[1] = __('Working place') . ' ' . $sorting_icon['asc'];
$options[2] = __('Working place') . ' ' . $sorting_icon['desc'];

if(!isset($this->request->params['paging']['Examiner']['order']['Examiner.working_place']))$default = 0;
if(isset($this->request->params['paging']['Examiner']['order']['Examiner.working_place']) && $this->request->params['paging']['Examiner']['order']['Examiner.working_place'] == 'asc') $default = 1;
if(isset($this->request->params['paging']['Examiner']['order']['Examiner.working_place']) && $this->request->params['paging']['Examiner']['order']['Examiner.working_place'] == 'desc') $default = 2;

$attributes = array('legend' => false,'default' => $default);

echo '<div class="input radio">';
echo $this->Form->radio('working_place', $options, $attributes);
echo '</div>';

$options = array();
$options[0] ='-';
$options[1] = __('Name') . ' ' . $sorting_icon['asc'];
$options[2] = __('Name') . ' ' . $sorting_icon['desc'];

unset($default);

if(!isset($this->request->params['paging']['Examiner']['order']['Examiner.name']))$default = 0;
if(isset($this->request->params['paging']['Examiner']['order']['Examiner.name']) && $this->request->params['paging']['Examiner']['order']['Examiner.name'] == 'asc') $default = 1;
if(isset($this->request->params['paging']['Examiner']['order']['Examiner.name']) && $this->request->params['paging']['Examiner']['order']['Examiner.name'] == 'desc') $default = 2;

$attributes = array('legend' => false,'default' => $default);
echo '<div class="input radio">';
echo $this->Form->radio('name', $options, $attributes);
echo '</div>';

$options = array();
$options[0] ='-';
$options[1] = __('Date of birth') . ' ' . $sorting_icon['asc'];
$options[2] = __('Date of birth') . ' ' . $sorting_icon['desc'];

$default = 0;

$attributes = array('legend' => false,'default' => $default);
echo '<div class="input radio">';
echo $this->Form->radio('date_of_birth', $options, $attributes);
echo '</div>';

$options = array();
$options[1] = __('active') . ' ' . $sorting_icon['asc'];
$options[0] = __('deactive') . ' ' . $sorting_icon['desc'];

if(!isset($this->request->params['paging']['Examiner']['options']['Examiner.active']))$default = 1;
if(isset($this->request->params['paging']['Examiner']['options']['Examiner.active']) && $this->request->params['paging']['Examiner']['options']['Examiner.active'] == 1) $default = 1;
if(isset($this->request->params['paging']['Examiner']['options']['Examiner.active']) && $this->request->params['paging']['Examiner']['options']['Examiner.active'] == 0) $default = 0;

$attributes = array('legend' => false,'default' => $default);
echo '<div class="input radio">';
echo $this->Form->radio('active', $options, $attributes);
echo '</div>';

echo $this->element('js/examiner_testingcomp');
echo $this->Form->end('Anzeigen');
echo $this->element('js/form_button_set');

echo $this->element('js/form_send_sort_examiner',array('FormId' => 'SortExamierTable'));

?>
