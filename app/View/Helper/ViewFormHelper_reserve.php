<?php
class ViewFormHelper extends AppHelper
{
  public $helpers = array('Html', 'Form', 'Paginator');

  public function CreateMasterDropdownEditLink($LinkAfter, $data, $_setting, $setting, $disabled, $x)
  {

    $Model = trim($_setting->model);
    $field = trim($_setting->key);
    $Field = Inflector::camelize($field);

    if (!isset($data['MasterDropdowns'][$Model][$field])) {
      return $LinkAfter;
    }

    if (!isset($data['MasterDropdownsUrl'][$Model][$field])) {
      return $LinkAfter;
    }

    $output =
    $this->Html->link(__('Edit'), array_merge(array(
      'controller' => $data['MasterDropdownsUrl'][$Model][$field]['controller'],
      'action' => $data['MasterDropdownsUrl'][$Model][$field]['action'],
    ), $data['MasterDropdownsUrl'][$Model][$field]['parm']), array_merge(
      array(
        'class' => 'modal dropdown',
        'disabled' => $disabled,
        'title' => __('Edit dropdown', true),
      ),
      Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex' => '-1') : array()
    ));

    $LinkAfter = $output;

    $back_link = $this->Form->input($Model . $Field, array('name' => 'data[MasterDropdown][' . $Model . '][' . $field . ']', 'type' => 'hidden', 'value' => $this->request->here));
    $LinkAfter .= $back_link;

    $LinkAfter .= $this->CreateMasterDependenciesEditLink($LinkAfter, $data, $_setting, $setting, $disabled, $x);

    return $LinkAfter;
  }

  public function CreateMasterDependenciesEditLink($LinkAfter, $data, $_setting, $setting, $disabled, $x)
  {

    $Model = trim($_setting->model);
    $field = trim($_setting->key);

    if (!isset($data['MasterDropdownsDependency'])) {
      return null;
    }

    if ($data['MasterDropdownsDependency'][$Model][$field] === false) {
      return null;
    }

    if (key($data['MasterDropdowns'][$Model][$field]) == 0) {
      return false;
    }

    $output = null;

    $output = $this->Html->link(__('Edit dependent fields'),
    array(
      'controller' => 'dependencies',
      'action' => 'index',
      $this->request->projectvars['VarsArray'][0],
      $this->request->projectvars['VarsArray'][1],
      $this->request->projectvars['VarsArray'][2],
      $this->request->projectvars['VarsArray'][3],
      $this->request->projectvars['VarsArray'][4],
      $this->request->projectvars['VarsArray'][5],
      key($data['MasterDropdowns'][$Model][$field]),
      $x,
    ),
    array_merge(
      array(
        'class' => 'modal dependency hide_link',
        'disabled' => $disabled,
        'title' => __('Edit dependent fields'),
      ),
      Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex' => '-1') : array()
      )
    );

    return $output;

  }

  public function CreateModulDropdownEditLink($data, $_setting, $setting, $disabled, $x)
  {

    if (!isset($data['DropdownInfo'])) {
      return;
    }

    if (!isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0])) {
      return;
    }

    if (isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['Dropdown']['id'])) {
      $dropdown_id = $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['Dropdown']['id'];
    } else {
      $dropdown_id = 0;
    }

    $output =
    $this->Html->link(__('Edit'), array(
      'controller' => 'dropdowns',
      'action' => 'dropdownindex',
      $this->request->projectvars['VarsArray'][0],
      $this->request->projectvars['VarsArray'][1],
      $this->request->projectvars['VarsArray'][2],
      $this->request->projectvars['VarsArray'][3],
      $this->request->projectvars['VarsArray'][4],
      $this->request->projectvars['VarsArray'][5],
      $dropdown_id,
      $x,
    ), array_merge(
      array(
        'class' => 'dropdown',
        'disabled' => $disabled,
        'title' => __('Edit dropdown', true),
      ),
      Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex' => '-1') : array()
    ));

    return $output;
  }

  public function CreateDropdownEditLink($data, $_setting, $setting, $disabled, $x)
  {

    if (isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'])) {
      $dropdown_id = $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'];
    } else {
      $dropdown_id = 0;
    }

    $output =
    $this->Html->link(__('Edit'), array(
      'controller' => 'dropdowns',
      'action' => 'dropdownindex',
      $this->request->projectvars['VarsArray'][0],
      $this->request->projectvars['VarsArray'][1],
      $this->request->projectvars['VarsArray'][2],
      $this->request->projectvars['VarsArray'][3],
      $this->request->projectvars['VarsArray'][4],
      $this->request->projectvars['VarsArray'][5],
      $dropdown_id,
      $x,
    ), array_merge(
      array(
        'class' => 'modal dropdown',
        'disabled' => $disabled,
        'title' => __('Edit dropdown', true),
      ),
      Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex' => '-1') : array()
    ));

    return $output;
  }

  public function CreateDependenciesEditLink($data, $_setting, $setting, $disabled, $x)
  {

    if (!isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0) {
      return null;
    }

    $output = null;

    if (!isset($data['DropdownInfo'][trim($_setting->model)])) {
      return $output;
    }

    if (!isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)])) {
      return $output;
    }

    $children = (array)($_setting->dependencies->children());
    $locale = $this->_View->viewVars['locale'];
    $dependencies = array_map(function ($key) use ($setting, $locale) {
      $key = array_filter($setting, function ($__setting) use ($key) {
        return trim($__setting->key) == $key;
      });

      if (!empty(reset($key)->discription->$locale)) {
        return trim(reset($key)->discription->$locale);
      };
    }, (array)$children['child']);

    if (count($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) == 0) {
      return null;
    }

    $output = $this->Html->link(__('Edit dependent fields'),
    array(
      'controller' => 'dependencies',
      'action' => 'index',
      $this->request->projectvars['VarsArray'][0],
      $this->request->projectvars['VarsArray'][1],
      $this->request->projectvars['VarsArray'][2],
      $this->request->projectvars['VarsArray'][3],
      $this->request->projectvars['VarsArray'][4],
      $this->request->projectvars['VarsArray'][5],
      $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'],
      $x,
    ),
    array_merge(
      array(
        'class' => 'modal dependency',
        'disabled' => $disabled,
        'title' => __('Edit dependent fields') . ':' . PHP_EOL . join(PHP_EOL, $dependencies),
      ),
      Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex' => '-1') : array()
      )
    );

    return $output;

  }

  public function CreateRadioForReport($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
  {

    if (empty($_setting->fieldtype)) {
      return null;
    }

    if (!empty($_setting->fieldtype) && trim($_setting->fieldtype) != 'radio') {
      return null;
    }

    $model = trim($_setting->model);

    $radiooptions = $this->radiodefault;

    if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {

      $radiooptions = array();

      foreach ($_setting->radiooption->value as $_radiooptions) {
        array_push($radiooptions, trim($_radiooptions));
      }
    }

    if (isset($_setting->validate->error)) {
      $attribut_array['class'] = ' error';
    }

    $attribut_array['legend'] = $attribut_array['label'];
    $attribut_array['options'] = $radiooptions;

    foreach ($radiooptions as $radkey => $radvalue) {

      if (!isset($data[$model][trim($_setting->key)])) {
        continue;
      }

      if (isset($data[$model]) && $radvalue == $data[$model][trim($_setting->key)]) {
        $attribut_array['value'] = $radkey;
      }

      break;
    }

    if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) {
      $attribut_array['tabindex'] = '-1';
    }

    $attribut_array['type'] = 'radio';

    if (!isset($data[$model][trim($_setting->key)])) {
      $attribut_array['disabled'] = 'disabled';
    }

    return $this->Form->input(trim($_setting->model) . '.' . trim($_setting->key), $attribut_array);

  }

  public function CreateDateInputForReport($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
  {

    $attribut_array['type'] = 'text';
    return $this->Form->input(trim($_setting->model) . '.' . trim($_setting->key), $attribut_array);

  }

  public function CreateInputForReport($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
  {

    if (!empty($_setting->select->model)) {
      return null;
    }

    if (!empty($_setting->multiselect->model)) {
      return null;
    }

    if (!empty($_setting->fieldtype) && trim($_setting->fieldtype) == 'radio') {
      return null;
    }

    if (!empty($_setting->format) && trim($_setting->format) == 'date') {
      return null;
    }

    if (isset($_setting->select->moduldata)) {
      return false;
    }

    $model = trim($_setting->model);
    $field = trim($_setting->key);

    if (!isset($data[$model][$field])) {
      $attribut_array['disabled'] = 'disabled';
    }

    if (isset($data[$model][$field]) && $data[$model][$field] === null && isset($attribut_array['disabled'])) {
      unset($attribut_array['disabled']);
    }

    $attribut_array = $this->EditOnlyBy($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x);
    $attribut_array = $this->EditAfterClosing($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x);

    return $this->Form->input(trim($_setting->model) . '.' . trim($_setting->key), $attribut_array);

  }

  public function CreateModulInputForReport($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
  {

    if (!isset($_setting->select->moduldata)) {
      return false;
    }

    $options = array();
    $optionsarray = array();

    if (isset($data['ModulData'])) {
      $optionsarray = json_decode($data['ModulData'], true);
    }

    if (!empty($optionsarray) && isset($optionsarray[trim($_setting->model)][trim($_setting->key)])) {

      $options = Hash::combine($optionsarray[trim($_setting->model)][trim($_setting->key)], '{n}.id', '{n}.' . trim($_setting->select->moduldata->field));

      if (isset($data[trim($_setting->model)][trim($_setting->key)])) {
        $thisselected = array_search($data[trim($_setting->model)][trim($_setting->key)], $options);
      }

      $input = $this->Form->input(
        trim($_setting->model) . '.' . trim($_setting->key),
        array(
          'label' => $attribut_array['label'],
          'class' => 'modulselect',
          'disabled' => $disabled,
          'options' => $options,
          'selected' => isset($thisselected) ? $thisselected : '',
          'empty' => ' ',
          'before' => $attribut_array['before'],
          'between' => $hidable ? $hideBox : null,
        )
      );
    }
    return $input;
  }

  public function CreateMultiselectForReport($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x, $hideBox)
  {

    //      $thisselected = explode("\n",$data[$model][trim($_setting->key)]);

    if (empty($_setting->multiselect)) {
      return null;
    }

    if (!empty($_setting->fieldtype) && trim($_setting->fieldtype) == 'radio') {
      return null;
    }

    if (!empty($_setting->format) && trim($_setting->format) == 'date') {
      return null;
    }

    if (isset($_setting->select->moduldata)) {
      return false;
    }

    if (trim($_setting->key) == 'error') {
      return null;
    }

    $model = trim($_setting->model);
    $field = trim($_setting->key);

    if (isset($data['Dropdowns'][$model][$field]) && count($data['Dropdowns'][$model][$field]) > 0) {

      $options = $data['Dropdowns'][$model][$field];
      $thisselected = null;

      // Selected suchen
      if (isset($data[$model][$field])) {

        $add = true;

        foreach ($options as $__key => $__options) {
          if ($__options == $data[$model][$field]) {
            $thisselected = $__key;
            $add = false;
            break;
          }
        }

        if ($add && array_search($data[$model][$field], $options) === false) {

          $thisselected = $data[$model][$field];
          $thisselected = explode("\n", $thisselected);
          $multiseleced = array();

          foreach ($options as $__key => $__options) {
            if (in_array($__options, $thisselected)) {
              $multiseleced[$__key] = $__key;
            }

          }

          $thisselected = $multiseleced;
        }

        $options = array_unique($options);
      }
    }

    if (isset($data['Multiselects'][$model][$field])) {
      if (!isset($options) || empty($options)) {
        $options = $data['Multiselects'][$model][$field];
      } else {
        $options = array_merge(array(__('Dropdown values', true) => $options), $data['Multiselects'][$model][$field]);
      }
    }

    $EditSelectLink = null;
    $EditSelectLink .= $this->CreateDropdownEditLink($data, $_setting, $setting, $disabled, $x);
    $EditSelectLink .= $this->CreateDependenciesEditLink($data, $_setting, $setting, $disabled, $x);

    if (!empty($_setting->select->roll->edit) && $_setting->select->roll->edit != null) {

      foreach ($_setting->select->roll->edit->children() as $_children) {
        if (trim($_children) == AuthComponent::user('Roll.id')) {
          $LinkAfter = $EditSelectLink;
          break;
        } else {
          $LinkAfter = null;
        }
      }
    } else {
      $LinkAfter = $EditSelectLink;
    }

    $LinkAfter = $this->CreateMasterDropdownEditLink($LinkAfter, $data, $_setting, $setting, $disabled, $x);

    if (isset($_setting->dependencies->child) && $_setting->dependencies->child->count() > 0) {
      $Class = 'hasDependencies';
    } else {
      $Class = null;
    }

    if (isset($data['MasterDropdownsDependency'][trim($_setting->model)][trim($_setting->key)]) && $data['MasterDropdownsDependency'][trim($_setting->model)][trim($_setting->key)] == true) {
      $Class = 'hasDependencies';
    }

    if (isset($_setting->validate->error)) {
      $Class .= ' error';
    }

    $SelectOptions = array(
      'label' => $attribut_array['label'],
      'disabled' => $disabled,
      'multiple' => 'multiple',
      'class' => $Class,
      'empty' => ' ',
      'before' => $attribut_array['before'],
      'between' => $hidable ? $hideBox : null,
      'after' => empty($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)]) ? $LinkAfter : '',
    );

    $SelectOptions = $this->EditOnlyBy($data, $_setting, $setting, $SelectOptions, $hidable, $disabled, $x);
    $SelectOptions = $this->EditAfterClosing($data, $_setting, $setting, $SelectOptions, $hidable, $disabled, $x);

    if (isset($options) && count($options) > 0) {
      $SelectOptions['options'] = $options;
    }

    if (isset($thisselected)) {
      $SelectOptions['selected'] = $thisselected;
    }

    $input = $this->Form->input(trim($_setting->model) . '.' . trim($_setting->key), $SelectOptions);

    return $input;

  }

  public function CreateMultiErrorselectForReport($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x, $hideBox)
  {

    //      $thisselected = explode("\n",$data[$model][trim($_setting->key)]);

    if (empty($_setting->multiselect)) {
      return null;
    }

    if (!empty($_setting->fieldtype) && trim($_setting->fieldtype) == 'radio') {
      return null;
    }

    if (!empty($_setting->format) && trim($_setting->format) == 'date') {
      return null;
    }

    if (isset($_setting->select->moduldata)) {
      return false;
    }

    if (trim($_setting->key) != 'error') {
      return null;
    }

    $model = trim($_setting->model);
    $field = trim($_setting->key);

    if (isset($data['Multiselects'][$model][$field])) {
      if (!isset($options) || empty($options)) {
        $options = $data['Multiselects'][$model][$field];
      } else {
        $options = array_merge(array(__('Dropdown values', true) => $options), $data['Multiselects'][$model][$field]);
      }
    }

    $EditSelectLink = null;

    $Class = 'multi_errorselect_report';

    if (isset($_setting->validate->error)) {
      $Class .= ' error';
    }

    if (empty($data[$model][trim($_setting->key)])) {
      $selected = null;
    }

    if (!empty($data[$model][trim($_setting->key)])) {

      $search = array('/ /', '/,,/', '/;/', '/\r/', '/\n/');
      $replace = ',';

      $selected = preg_replace($search, $replace, trim($data[$model][trim($_setting->key)]));
      $selected = explode(',', $selected);
    }

    if (!isset($data[$model][trim($_setting->key)])) {
      $attribut_array['disabled'] = 'disabled';
    }

    $SelectOptions = array(
      'label' => $attribut_array['label'],
      'disabled' => $disabled,
      'selected' => $selected,
      'multiple' => 'multiple',
      'class' => $Class,
      'empty' => ' ',
      'hiddenField' => false,
      'after' => $this->Form->input($model . '.' . $field, array(
        'label' => false,
        'div' => false,
        'disabled' => isset($attribut_array['disabled']) ? $attribut_array['disabled'] : false,
        'class' => 'error_data_field',
      )
    ),
  );

  asort($options);

  if (isset($options) && count($options) > 0) {
    $SelectOptions['options'] = $options;
  }

  if (isset($thisselected)) {
    $SelectOptions['selected'] = $thisselected;
  }

  if (!isset($data[$model][trim($_setting->key)])) {
    $SelectOptions['disabled'] = 'disabled';
  }

  $input = $this->Form->input(trim($_setting->model) . '.' . trim($_setting->key), $SelectOptions);

  return $input;

}

public function CreateDropdownForReport($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
{

  if (!empty($_setting->multiselect)) {
    return null;
  }

  if (empty($_setting->select->model)) {
    return null;
  }

  if (!empty($_setting->fieldtype) && trim($_setting->fieldtype) == 'radio') {
    return null;
  }

  if (!empty($_setting->format) && trim($_setting->format) == 'date') {
    return null;
  }

  if (isset($_setting->select->moduldata)) {
    return false;
  }

  $model = trim($_setting->model);
  $field = trim($_setting->key);

  $hidable = false;
  $hideBox = null;

  if (trim($_setting->pdf->hidable) == '1' || strtolower(trim($_setting->pdf->hidable)) == 'x' || strtolower(trim($_setting->pdf->hidable)) == 'true') {
    $hidable = true;
    $val = 1;
    foreach ($data['HiddenField'] as $field) {
      if ($field['model'] == trim($_setting->model) && $field['field'] == trim($_setting->key)) {
        $val = 0;
        break;
      }
    }

    $hideBox = $this->Form->input(
      'hide-' . trim($_setting->model) . '0' . trim($_setting->key),
      array(
        'checked' => $val,
        'id' => 'HiddenField' . trim($_setting->model) . '0' . trim($_setting->key),
        'name' => 'data[HiddenField][' . trim($_setting->model) . '][0][' . trim($_setting->key) . ']',
        'type' => 'checkbox',
        'hiddenField' => false,
        'div' => false,
        'label' => false,
        'class' => 'hide_box',
        'style' => 'position: absolute; top: 1px; right: 1px; display: inline-block;',
      )
    );
  }

  if (isset($data['Dropdowns'][$model][$field]) && count($data['Dropdowns'][$model][$field]) > 0) {

    $options = $data['Dropdowns'][$model][$field];

    $thisselected = null;

    // Selected suchen
    if (isset($data[$model][$field])) {

      $add = true;

      foreach ($options as $__key => $__options) {
        if ($__options == $data[$model][$field]) {
          $thisselected = $__key;
          $add = false;
          break;
        }
      }

      if ($add && array_search($data[$model][$field], $options) === false) {
        $options[$data[$model][$field]] = $data[$model][$field];
        $thisselected = $data[$model][$field];
      }

      $options = array_unique($options);

    }
  }

  $EditSelectLink = null;
  $EditSelectLink .= $this->CreateDropdownEditLink($data, $_setting, $setting, $disabled, $x);
  $EditSelectLink .= $this->CreateDependenciesEditLink($data, $_setting, $setting, $disabled, $x);

  if (!empty($_setting->dependencies->parent)) {
    $EditSelectLink = null;
  }

  if (!empty($_setting->select->roll->edit) && $_setting->select->roll->edit != null) {

    foreach ($_setting->select->roll->edit->children() as $_children) {
      if (trim($_children) == AuthComponent::user('Roll.id')) {
        $LinkAfter = $EditSelectLink;
        break;
      } else {
        $LinkAfter = null;
      }
    }
  } else {
    $LinkAfter = $EditSelectLink;
  }

  $LinkAfter = $this->CreateMasterDropdownEditLink($LinkAfter, $data, $_setting, $setting, $disabled, $x);

  if ($field == 'image_no') {
    $LinkAfter .=
    $this->Html->link(
      __('EN 1435'),
      array(
        'action' => 'en1435',
        $this->request->projectvars['VarsArray'][0],
        $this->request->projectvars['VarsArray'][1],
        $this->request->projectvars['VarsArray'][2],
        $this->request->projectvars['VarsArray'][3],
        $this->request->projectvars['VarsArray'][4],
        $this->request->projectvars['VarsArray'][5],
        $this->request->projectvars['VarsArray'][6],
        $this->request->projectvars['VarsArray'][7],
      ),
      array_merge(
        array(
          'class' => 'modal en1435',
          'disabled' => $disabled,
          'title' => __('EN 1435 Bildnummern', true),
        ),
        Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex' => '-1') : array()
        )
      );
    }

    if (isset($_setting->dependencies->child) && $_setting->dependencies->child->count() > 0) {
      $Class = 'hasDependencies';
    } else {
      $Class = null;
    }

    if (isset($data['MasterDropdownsDependency'][trim($_setting->model)][trim($_setting->key)]) && $data['MasterDropdownsDependency'][trim($_setting->model)][trim($_setting->key)] == true) {
      $Class = 'hasDependencies';
    }

    if (isset($_setting->validate->error)) {
      $Class .= ' error';
    }

    //      if(!isset($data['LinkedDropdownData'][trim($_setting->model)])) $LinkAfter = null;
    if (!isset($attribut_array['before'])) {
      $attribut_array['before'] = null;
    }

    $SelectOptions = array(
      'label' => $attribut_array['label'],
      'disabled' => $disabled,
      'class' => $Class,
      'empty' => ' ',
      'before' => $attribut_array['before'],
      'before' => isset($attribut_array['before']) ? $attribut_array['before'] : null,
      'between' => $hidable ? $hideBox : null,
      'after' => empty($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)]) ? $LinkAfter : '',
    );

    if (isset($options) && count($options) > 0) {
      $SelectOptions['options'] = $options;
    }

    if (isset($thisselected)) {
      $SelectOptions['selected'] = $thisselected;
    }

    $model = trim($_setting->model);
    $field = trim($_setting->key);

    if (isset($data['Reportnumber']) && !isset($data[$model][$field])) {
      $SelectOptions['disabled'] = 'disabled';
    }

    $SelectOptions = $this->EditOnlyBy($data, $_setting, $setting, $SelectOptions, $hidable, $disabled, $x);
    $SelectOptions = $this->EditAfterClosing($data, $_setting, $setting, $SelectOptions, $hidable, $disabled, $x);
    $input = $this->Form->input(trim($_setting->model) . '.' . trim($_setting->key), $SelectOptions);

    return $input;

  }

  public function CreateInputForModul($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
  {

    if (!empty($_setting->select->model)) {
      return null;
    }

    if (!empty($_setting->multiselect->model)) {
      return null;
    }

    if (!empty($_setting->fieldtype) && trim($_setting->fieldtype) == 'radio') {
      return null;
    }

    if (!empty($_setting->format) && trim($_setting->format) == 'date') {
      return null;
    }

    if (isset($_setting->select->moduldata)) {
      return false;
    }

    $model = trim($_setting->model);
    $field = trim($_setting->key);

    return $this->Form->input(trim($_setting->model) . '.' . trim($_setting->key), $attribut_array);

  }

  public function CreateDropdownForModul($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
  {

    if (!empty($_setting->multiselect)) {
      return null;
    }

    if (empty($_setting->select->model)) {
      return null;
    }

    if (!empty($_setting->fieldtype) && trim($_setting->fieldtype) == 'radio') {
      return null;
    }

    if (!empty($_setting->format) && trim($_setting->format) == 'date') {
      return null;
    }

    if (isset($_setting->select->moduldata)) {
      return false;
    }

    $model = trim($_setting->model);
    $field = trim($_setting->key);

    if (isset($data['Dropdowns'][$model][$field]) && count($data['Dropdowns'][$model][$field]) > 0) {

      $options = $data['Dropdowns'][$model][$field];

      $thisselected = null;

      // Selected suchen
      if (isset($data[$model][$field])) {

        $add = true;

        foreach ($options as $__key => $__options) {
          if ($__options == $data[$model][$field]) {
            $thisselected = $__key;
            $add = false;
            break;
          }
        }

        if ($add && array_search($data[$model][$field], $options) === false) {
          $options[$data[$model][$field]] = $data[$model][$field];
          $thisselected = $data[$model][$field];
        }

        $options = array_unique($options);

      }
    }

    $EditSelectLink = null;

    $EditSelectLink .= $this->CreateModulDropdownEditLink($data, $_setting, $setting, $disabled, $x);

    if (!empty($_setting->select->roll->edit) && $_setting->select->roll->edit != null) {

      foreach ($_setting->select->roll->edit->children() as $_children) {
        if (trim($_children) == AuthComponent::user('Roll.id')) {
          $LinkAfter = $EditSelectLink;
          break;
        } else {
          $LinkAfter = null;
        }
      }
    } else {
      $LinkAfter = $EditSelectLink;
    }

    //      $LinkAfter = $this->CreateMasterDropdownEditLink($LinkAfter,$data,$_setting,$setting,$disabled,$x);

    if (isset($_setting->dependencies->child) && $_setting->dependencies->child->count() > 0) {
      $Class = 'hasDependencies';
    } else {
      $Class = null;
    }

    if (isset($data['MasterDropdownsDependency'][trim($_setting->model)][trim($_setting->key)]) && $data['MasterDropdownsDependency'][trim($_setting->model)][trim($_setting->key)] == true) {
      $Class = 'hasDependencies';
    }

    if (isset($_setting->validate->error)) {
      $Class .= ' error';
    }

    //      if(!isset($data['LinkedDropdownData'][trim($_setting->model)])) $LinkAfter = null;
    if (!isset($attribut_array['before'])) {
      $attribut_array['before'] = null;
    }

    $SelectOptions = array(
      'label' => $attribut_array['label'],
      'disabled' => $disabled,
      'class' => $Class,
      'empty' => ' ',
      'before' => $attribut_array['before'],
      'before' => isset($attribut_array['before']) ? $attribut_array['before'] : null,
      'between' => $hidable ? $hideBox : null,
      'after' => empty($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)]) ? $LinkAfter : '',
    );

    if (isset($options) && count($options) > 0) {
      $SelectOptions['options'] = $options;
    }

    if (isset($thisselected)) {
      $SelectOptions['selected'] = $thisselected;
    }

    $model = trim($_setting->model);
    $field = trim($_setting->key);

    $input = $this->Form->input(trim($_setting->model) . '.' . trim($_setting->key), $SelectOptions);

    return $input;

  }

  public function CreateRadioForModul($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
  {

    if (empty($_setting->fieldtype)) {
      return null;
    }

    if (!empty($_setting->fieldtype) && trim($_setting->fieldtype) != 'radio') {
      return null;
    }

    $model = trim($_setting->model);

    $radiooptions = $this->radiodefault;

    if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {

      $radiooptions = array();

      foreach ($_setting->radiooption->value as $_radiooptions) {
        array_push($radiooptions, trim($_radiooptions));
      }
    }

    if (isset($_setting->validate->error)) {
      $attribut_array['class'] = ' error';
    }

    $attribut_array['legend'] = $attribut_array['label'];
    $attribut_array['options'] = $radiooptions;

    foreach ($radiooptions as $radkey => $radvalue) {

      if (!isset($data[$model][trim($_setting->key)])) {
        continue;
      }

      if (isset($data[$model]) && $radvalue == $data[$model][trim($_setting->key)]) {
        $attribut_array['value'] = $radkey;
      }

      break;
    }

    if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) {
      $attribut_array['tabindex'] = '-1';
    }

    $attribut_array['type'] = 'radio';

    return $this->Form->input(trim($_setting->model) . '.' . trim($_setting->key), $attribut_array);

  }

  public function EditOnlyBy($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
  {

    if (empty($_setting->edit_only_by)) {
      return $attribut_array;
    }

    if (empty($_setting->edit_only_by->roll)) {
      return $attribut_array;
    }

    if ($data['Reportnumber']['status'] > 0) {
      return $attribut_array;
    }

    $Access = false;

    foreach ($_setting->edit_only_by->roll->children() as $key => $value) {
      if (AuthComponent::user('roll_id') == trim($value)) {
        $Access = true;
        break;
      }
    }

    if ($Access === false) {
      $attribut_array['disabled'] = 'disabled';
    }

    return $attribut_array;
  }

  public function EditAfterClosing($data, $_setting, $setting, $attribut_array, $hidable, $disabled, $x)
  {

    if (empty($_setting->edit_after_closing)) {
      return $attribut_array;
    }

    if (empty($_setting->edit_after_closing->roll)) {
      return $attribut_array;
    }

    if (empty($_setting->edit_after_closing->status)) {
      return $attribut_array;
    }

    if ($data['Reportnumber']['status'] == 0) {
      return $attribut_array;
    }

    if ($data['Reportnumber']['revision_progress'] > 0) {
      return $attribut_array;
    }

    $Access = false;
    //      pr($_setting->edit_after_closing);

    foreach ($_setting->edit_after_closing->roll->children() as $key => $value) {
      if (AuthComponent::user('roll_id') == trim($value)) {
        $Access = true;
        break;
      }
    }

    if ($Access === false) {
      return $attribut_array;
    }

    $Access = false;

    foreach ($_setting->edit_after_closing->status->children() as $key => $value) {
      if ($data['Reportnumber']['status'] == trim($value)) {
        $Access = true;
        break;
      }
    }

    if ($Access === false) {
      return $attribut_array;
    }

    if (isset($attribut_array['disabled']) && !empty($attribut_array['disabled'])) {
      unset($attribut_array['disabled']);
    }

    $attribut_array['class'] .= ' edit_after_closing';

    return $attribut_array;
  }
}
