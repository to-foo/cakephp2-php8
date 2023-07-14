<?php
// Kommentar für GitHub
class ViewDataHelper extends AppHelper
{
  public $helpers = array('Html', 'Form', 'Paginator');

  public function CreateDropdownEditLink($data,$_setting,$setting,$disabled,$x){

    if(!isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)])) return null;

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
          $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'],
          $x
        ), array_merge(
                array(
                    'class'=>'modal dropdown',
                    'disabled' => $disabled,
                    'title'=> __('Edit dropdown', true)
                ),
                Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
            ));

      return $output;
    }

    public function CreateDependenciesEditLink($data,$_setting,$setting,$disabled,$x){

      if(!isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0) return null;

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
          $x
        ),
        array_merge(
          array(
            'class'=>'modal dependency',
            'disabled' =>$disabled,
            'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
          ),
          Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
        )
      );

      return $output;

    }

    public function RadioDefault(){

      return $this->radiodefault;

    }

    public function CreateRadioForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x){

      if(empty($_setting->fieldtype)) return null;
      if(!empty($_setting->fieldtype) && trim($_setting->fieldtype) != 'radio') return null;

      $model = trim($_setting->model);

      $radiooptions = $this->radiodefault;

      if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {

        $radiooptions = array();

        foreach ($_setting->radiooption->value as $_radiooptions) {
          array_push($radiooptions, trim($_radiooptions));
        }
      }

      if (isset($_setting->validate->error)) $attribut_array['class'] = ' error';

      $attribut_array['legend'] = $attribut_array['label'];
      $attribut_array['options'] = $radiooptions;

      foreach ($radiooptions as $radkey => $radvalue) {
        if ($radvalue == $data[$model][trim($_setting->key)]) $attribut_array['value'] = $radkey;
      }

      if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) $attribut_array['tabindex'] = '-1';

      $attribut_array['type'] = 'radio';

      return $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);

    }

    public function CreateDateInputForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x){

      $attribut_array['type'] = 'text';
      return $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);

    }

    public function CreateInputForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x){

      if(!empty($_setting->select->model)) return null;
      if(!empty($_setting->fieldtype) && trim($_setting->fieldtype) == 'radio') return null;
      if(!empty($_setting->format) && trim($_setting->format) == 'date') return null;
      if(isset($_setting->select->moduldata)) return false;


      return $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);

    }

    public function CreateModulInputForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x){

      if (!isset($_setting->select->moduldata)) return false;

      $options = array();
      $optionsarray = array();

      if (isset($data['ModulData'])) $optionsarray = json_decode($data['ModulData'], true);

      if (!empty($optionsarray) && isset($optionsarray [trim($_setting->model)][trim($_setting->key)])) {

        $options = Hash::combine($optionsarray [trim($_setting->model)][trim($_setting->key)], '{n}.id', '{n}.'.trim($_setting->select->moduldata->field));

        if (isset($data [trim($_setting->model)][trim($_setting->key)])) $thisselected = array_search($data [trim($_setting->model)][trim($_setting->key)], $options);

        $input = $this->Form->input(
          trim($_setting->model).'.'.trim($_setting->key),
          array(
            'label' => $attribut_array['label'],
            'class' => 'modulselect',
            'disabled' => $disabled,
            'options' => $options,
            'selected' => isset($thisselected)? $thisselected : '',
            'empty' => ' ',
            'before' => $attribut_array['before'],
            'between' => $hidable ? $hideBox : null,
          )
        );
      }
    }

    public function CreateDropdownForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x){

      if(empty($_setting->select->model)) return null;
      if(!empty($_setting->fieldtype) && trim($_setting->fieldtype) == 'radio') return null;
      if(!empty($_setting->format) && trim($_setting->format) == 'date') return null;
      if(isset($_setting->select->moduldata)) return false;

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
              asort($options);
          }

          $EditSelectLink = null;
          $EditSelectLink .= $this->CreateDropdownEditLink($data,$_setting,$setting,$disabled,$x);
          $EditSelectLink .= $this->CreateDependenciesEditLink($data,$_setting,$setting,$disabled,$x);

          if (!empty($_setting->select->roll->edit)) {
              foreach ($_setting->select->roll->edit->children() as $_children) {
                  if (trim($_children) == AuthComponent::user('Roll.id')) {
                      $test = $EditSelectLink;
                      break;
                  } else {
                      // do nothing
                      $test = null;
                  }
              }
          } else {
              $test = $EditSelectLink;
          }

          $Class = isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null;
          if (isset($_setting->validate->error)) {
              $Class .= ' error';
          }

          $input = $this->Form->input(
              trim($_setting->model).'.'.trim($_setting->key),
              array(
                  'label' => $attribut_array['label'],
                  'disabled' => $disabled,
                  'options' => $options,
                  'selected' => isset($thisselected)? $thisselected : '',
                  'class' => $Class,
                  'empty' => ' ',
                  'before' => $attribut_array['before'],
                  'between' => $hidable ? $hideBox : null,
                  'after' => empty($data['LinkedDropdownData'][trim($_setting->model)] [trim($_setting->key)])? $test : ''
              )
          );

          return $input;

      }
/*
          if (isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) && count($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) > 0) {
              //__________________________29.01.2018______________________________
              if (!empty($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)])) {
                  $modul = trim($_setting->select->roll->modul);
                  $mfield = trim($_setting->select->roll->field);
                  $options = Hash::combine($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)], '{n}.'.$modul.'.id', '{n}.'.$modul.'.'.$mfield);
                  asort($options);
                  foreach ($options as $option => $o_value) {
                      $o_value == $data[$model][trim($_setting->key)] ? $thisselected = $option : '';
                  }


                  if (isset($_setting->select->roll->dependencies) && isset($_setting->dependencies->child)) {
                      foreach ($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)] as $linkeddatas => $linkedvalues) {
                          $linkedvalues [$modul] ['id'] == $thisselected ?
                                                       $data[$model][trim($_setting->dependencies->child)] = $linkedvalues [$modul] [trim($_setting->select->roll->dependencies->value)]:$data[$model][trim($_setting->dependencies->child)]= '';
                      }
                  }
              }


              //__________________________29.01.2018______________________________
              else {


              $EditSelectLink =
                      $this->Html->link(__('Edit'), array(
                          'controller' => 'dropdowns',
                          'action' => 'dropdownindex',
                          $this->request->projectvars['VarsArray'][0],
                          $this->request->projectvars['VarsArray'][1],
                          $this->request->projectvars['VarsArray'][2],
                          $this->request->projectvars['VarsArray'][3],
                          $this->request->projectvars['VarsArray'][4],
                          $this->request->projectvars['VarsArray'][5],

                          $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'],
                          $x
                      ), array_merge(
                          array(
                              'class'=>'modal dropdown',
                              'disabled' => $disabled,
                              'title'=> __('Edit dropdown', true)
                          ),
                          Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                      )).
                      (
                          !isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0
                          ? null
                          : $this->Html->link(
                              __('Edit dependent fields'),
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
                                  $x

                              ),
                              array_merge(
                                  array(
                                          'class'=>'modal dependency',
                                          'disabled' =>$disabled,
                                          'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
                                  ),
                                  Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                              )
                          )
                      );

              if (!empty($_setting->select->roll->edit)) {
                  foreach ($_setting->select->roll->edit->children() as $_children) {
                      if (trim($_children) == AuthComponent::user('Roll.id')) {
                          $test = $EditSelectLink;
                          break;
                      } else {
                          // do nothing
                          $test = null;
                      }
                  }
              } else {
                  $test = $EditSelectLink;
              }

              $Class = isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null;
              if (isset($_setting->validate->error)) {
                  $Class .= ' error';
              }

              $input = $this->Form->input(
                  trim($_setting->model).'.'.trim($_setting->key),
                  array(
                      'label' => $attribut_array['label'],
                      'disabled' => $disabled,
                      'options' => $options,
                      'selected' => isset($thisselected)? $thisselected : '',
                      'class' => $Class,
                      'empty' => ' ',
                      'before' => $revisionlink,
                      'between' => $hidable ? $hideBox : null,
                      'after' => empty($data['LinkedDropdownData'][trim($_setting->model)] [trim($_setting->key)])? $test : ''
                  )
              );
          } else {
              $options = array();

              $attribut_array['after'] =
                      $this->Html->link(
                          __('Edit'),
                          array(
                                  'controller' => 'dropdowns',
                                  'action' => 'dropdownindex',
                                      $this->request->projectvars['VarsArray'][0],
                                      $this->request->projectvars['VarsArray'][1],
                                      $this->request->projectvars['VarsArray'][2],
                                      $this->request->projectvars['VarsArray'][3],
                                      $this->request->projectvars['VarsArray'][4],
                                      $this->request->projectvars['VarsArray'][5],

                                      0,
                                      $x
                              ),
                          array_merge(
                              array(
                                      'class'=>'modal dropdown',
                                      'disabled' => $disabled,
                                      'title'=> __('Add dropdown', true)
                                  ),
                              Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                          )
                      );

              if ($hidable) {
                  $attribut_array['between'] = $hideBox;
              }

              if (!empty($_setting->select->roll->edit)) {
                  foreach ($_setting->select->roll->edit->children() as $_children) {
                      if (trim($_children) == AuthComponent::user('Roll.id')) {
                          //								$test = $EditSelectLink;
                          break;
                      } else {
                          // do nothing
                          unset($attribut_array['after']);
                          //								$test = null;
                      }
                  }
              } else {
                  //						$test = $EditSelectLink;
              }
              if (isset($_setting->validate->error)) {
                  $attribut_array['class'] = 'error';
              }
              $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
          }
*/
    }

    public function CollectPrintMessages($reportnumber,$errors){

      $Messages = array('hint' => array(),'success' => array(),'error' => array());

      if (Configure::check('CloseMethode') && Configure::read('CloseMethode') == 'showReportVerificationByTime') {
        //FIXME Übersetzung
          $d = array(Configure::read('CloseMethodeTime') / 60 . ' Minuten');

          if ($reportnumber['Reportnumber']['status'] == 0) {

              if (Configure::read('CloseMethodeTime') > 0) {
                  $Messages['hint'][] =  __d('validation', 'Attention, this report is after printing for %d minutes editable.', $d) . '<br>' . __('After this time this report will be closed automatically.', true);
              }
              if (Configure::read('CloseMethodeTime') == 0) {
                  $Messages['hint'][] = __('This report will be closed automatically after printing.', true);
              }

              if ($reportnumber['Reportnumber']['print'] > 0) {
                  $format = '%d.%m.%Y %H:%M:%S';
                  $Messages['hint'][] = __('This version of the report was first printed on', true) . ': ' . strftime($format, $reportnumber['Reportnumber']['print']);
              }
          }
      }

      if (Configure::check('SignatoryAfterPrinting') && Configure::check('SignatoryAfterPrinting') == true) {
          $Messages['hint'][] = __('No signature can be added after printing.');
      }
      if (isset($deverror) && $deverror == 1) {
          $Messages['hint'][] = __('For this report are available progress', true);
      }

      if ($reportnumber['Reportnumber']['status'] > 0 && $reportnumber['Reportnumber']['print'] > 0) {
          $format = '%d.%m.%Y %H:%M:%S';
          $Messages['hint'][] = __('This version of the report was first printed on', true) . ': ' . strftime($format, $reportnumber['Reportnumber']['print']);
      }

      if (isset($unevaluated) && count($unevaluated) > 0) {
          $list = array_filter($unevaluated);
          $Messages['hint'][] = '<p>' . __('Welds without results found') . (count($list) > 0 ? ':' : null) . '</p>' . $this->Html->nestedList(array_values($list));
      }

      if (isset($errors) && is_array($errors) && count($errors) == 0) {
      //    echo '<div class="success"><div class="success">';
          if (isset($this->request->data['prevent']) && intval($this->request->data['prevent'])==1) {
              $Messages['error'][] = __('Some values are still saving. Please try again in a few moments.');
          } else {
              $Messages['success'][] = __('All required fields have been filled out.', true);
          }
      }

      if (isset($errors) && is_array($errors) && count($errors) > 0) {
          $Messages['error'][] = __('Required fields were not filled. The report can not be printed.', true);
      }

      return $Messages;
    }

    public function showValidationErrors($errors = array())
    {
        if (!isset($errors)) {
            return;
        }
        if (count($errors) == 0) {
            return;
        }

        $Verfahren = $this->request->Verfahren;
        $lang = $this->request->lang;

        echo '<div class="error">';
        echo '<ul>';

        foreach ($errors as $_key => $_errors) {
            if (!is_array($_errors)) {
                continue;
            }
            if (count($_errors) == 0) {
                continue;
            }
            foreach ($_errors as $__key => $__errors) {
                if (!is_array($__errors)) {
                    continue;
                }
                if (count($__errors) == 0) {
                    continue;
                }
                foreach ($__errors as $___key => $___errors) {
                    echo '<li>';
                    echo __($_key, true);
                    echo ' -> ';
                    if (isset($___errors['position'])) {
                        echo $___errors['position'] . ' -> ';
                    }

                    $Action = 'edit';
                    if ($_key == 'Evaluation') {
                        $Action = 'editevalution';
                    }

                    echo $this->_View->Html->link(
                        trim($___errors['description']->$lang),
                        array_merge(
                                array(
                            'controller' => 'reportnumbers',
                            'action' => $Action,
                            ),
                                $___errors['reportnumber']
                            ),
                        array(
                                'name' => 'error[Report'.$Verfahren.$_key.']['.$__key.']',
                                'title' => trim($___errors['description']->$lang),
                                'class' => 'error_fields',
                                'rel' => $_key,
                                'rev' => 'Report'.$Verfahren.$_key.Inflector::camelize($__key),
                            )
                    );

                    echo ' -> ';
                    echo trim($___errors['message']);
                    echo '</li>';
                }
            }
        }

        echo '</ul>';
        echo '</div>';
    }

    public function FastSave($url, $formname)
    {
        $output = '
	<div class="clear" id="savediv"></div>
	<script type="text/javascript">
	$(document).ready(function(){

		$("form.'.$formname.'").on("keyup keypress", function(e) {
			var keyCode = e.keyCode || e.which;
			if(keyCode === 13) {
				e.preventDefault();
				return false;
			}
		});

		var data = $("#fakeform").serializeArray();
		var orginal_data = {};

		$("form.'.$formname.' input, form.'.$formname.' select, form.'.$formname.' textarea").each(function(){
			if($(this).closest("div").hasClass("radio")){
				if($(this).attr("checked") == "checked"){
					orginal_data["orginal_" + $(this).attr("name")] = $(this).val();
				}
			}
			if($(this).closest("div").hasClass("text") || $(this).closest("div").hasClass("select") || $(this).closest("div").hasClass("textarea")) {
				orginal_data["orginal_" + $(this).attr("name")] = $(this).val();
			}
			if($(this).closest("div").hasClass("checkbox")){
				if($(this).attr("checked") === undefined){
					orginal_data["orginal_" + $(this).attr("name")] = 0;
				} else {
					orginal_data["orginal_" + $(this).attr("name")] = 1;
				}
			}
		});

		$("#container").off("change", ".dependency, .customValue");

		$("#container").on("change", ".dependency, .customValue", function(event) {
			$(this).closest("div").find("label").css("padding-right","1.5em");
			$(this).closest("div").find("label").css("background-image","url(img/indicator.gif)");
			$(this).closest("div").find("label").css("background-repeat","no-repeat");
			$(this).closest("div").find("label").css("background-position","center right");
			$(this).closest("div").find("label").css("background-size","auto 0.9em");


			var val = $(this).val();
			var this_id = $(this).attr("id");
			var this_submit_id = $(this).attr("id") + "_submit";

			$("<span id=\"" + this_submit_id + "\"></span>").insertAfter("#savediv");

			var url = "'.$url.'";
			var data = $("#fakeform").serializeArray();
			data.push({name: "ajax_true", value: 1});
			data.push({name: "this_id", value: this_id});
			data.push({name: $(this).attr("name"), value: val});

			$.each(orginal_data, function(key, value) {
				data.push({name: key, value: value});
			});


			$.ajax({
				type	: "POST",
				cache	: false,
				url		: url,
				data	: data,
				success: function(data) {
			    	$("#" + this_submit_id).html(data);
			    	$("#" + this_submit_id).show();
					}
				});

			$(this).closest("div").find("label").css("background-image","none");
			return false;
		});

		$("form.'.$formname.' input, form.'.$formname.' select, form.'.$formname.' textarea").change(function() {

			if(!$(this).closest("div").hasClass("radio")){
				$(this).closest("div").css("background-image","url(img/indicator.gif)");
				$(this).closest("div").css("background-repeat","no-repeat");
				$(this).closest("div").css("background-position","95% 10%");
				$(this).closest("div").css("background-size","auto 0.9em");
			}
			if($(this).closest("div").hasClass("radio")){
				$(this).closest("div").find("legend").css("padding-right","2em");
				$(this).closest("div").find("legend").css("background-image","url(img/indicator.gif)");
				$(this).closest("div").find("legend").css("background-repeat","no-repeat");
				$(this).closest("div").find("legend").css("background-position","center right");
				$(this).closest("div").find("legend").css("background-size","auto 0.9em");
			}

			if(!$(this).closest("div").hasClass("radio")){
				$("label.ui-button").css("background-image","none");
			}

			var val = $(this).val();
			var this_id = $(this).attr("id");
			var this_submit_id = $(this).attr("id") + "_submit";
			var name = $(this).attr("name");

			$("<span id=\"" + this_submit_id + "\"></span>").insertAfter("#" + this_id);

			if($(this).closest("div").hasClass("select")){
				if($(this).find("option:selected").attr("rel") == "custom"){
					$(this).closest("div").find("label").css("background-image","none");
					return false;
				}
				val = $(this).find("option:selected").text();
			}
			if($(this).closest("div").hasClass("checkbox")){
				if($(this).attr("checked") === undefined){
					val = 0;
				} else {
					val = 1;
				}
			}

			if($(this).attr("multiple") != undefined){
				$(this).parent().find("input").val($(this).val() == null ? "" : $(this).val().join(", "));
				val = $(this).parent().find("input").val();
				name = $(this).parent().find("input").attr("name");
			}

			var url = "'.$url.'";
			var data = $("#fakeform").serializeArray();
			data.push({name: "ajax_true", value: 1});
			data.push({name: "this_id", value: this_id});
			data.push({name: name, value: val});

			$.each(orginal_data, function(key, value) {
				data.push({name: key, value: value});
			});

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: url,
				data	: data,
				success: function(data) {
			    	$("#" + this_submit_id).html(data);
			    	$("#" + this_submit_id).show();
					}
				});

			$(this).closest("div").css("background-image","none");
			$(this).closest("div").find("label").css("background-image","none");
			return false;
		});
	});


	</script>
	';

        return $output;
    }

    public function ShowInfo($report)
    {
        //pr($report);
        $output = null;
//pr('foo');
        if ($report['Reportnumber']['delete'] == 1) {
            if ($report['Reportnumber']['moved_id'] != 0) {
                return __('moved to %s', $this->_View->Html->link(
                    $this->_View->Pdf->ConstructReportName($report['Moved']),
                    array(
                    'controller'=>'reportnumbers',
                    'action'=>'view',
                    $report['Moved']['Reportnumber']['topproject_id'],
                    $report['Moved']['Reportnumber']['cascade_id'],
                    $report['Moved']['Reportnumber']['order_id'],
                    $report['Moved']['Reportnumber']['report_id'],
                    $report['Moved']['Reportnumber']['id']
                ),
                    array('class'=>'ajax')
                ));
            }
            if ($report['Reportnumber']['new_version_id'] != 0) {
                $output = null;

                if (isset($report['OldVersion'])) {
                    $output .= '<span class="" title="' . __('revision', true) . '">';
                    $OldVersioDesc = null;

                    if (isset($report['OldVersion']['info'])) {
                        $OldVersioDesc .=  $report['OldVersion']['info'];
                        echo $OldVersioDesc;
                    } else {
                        $OldVersioDesc .= __('revision', true) . ' ';
                        $OldVersioDesc .= __('from', true) . ' ';
                        $OldVersioDesc .=  $this->_View->Pdf->ConstructReportName($report['OldVersion']);

                        $output .= $this->_View->Html->link(
                            $OldVersioDesc,
                            array(
                            'controller'=>'reportnumbers',
                            'action'=>'view',
                            $report['OldVersion']['Reportnumber']['topproject_id'],
                            $report['OldVersion']['Reportnumber']['cascade_id'],
                            $report['OldVersion']['Reportnumber']['order_id'],
                            $report['OldVersion']['Reportnumber']['report_id'],
                            $report['OldVersion']['Reportnumber']['id']
                        ),
                            array('title' => $OldVersioDesc, 'class'=>'icon icon_rev_in ajax')
                        );
                    }

                    $output .= '</span>';
                }

                $OldVersioDesc = null;

                return $output;
            }
        }

        if ($report['Reportnumber']['delete'] == 1 && $report['Reportnumber']['new_version_id'] == 0 && $report['Reportnumber']['moved_id'] == 0) {
            return '<span class="new_version_link">'.__('Report deleted', true).'</span>';
        }

        if (isset($report['RepairReport']) && $report['RepairReport'] == true) {
            $RepairReportName = $this->_View->Pdf->ConstructReportName($report);

            if(isset($report['MistakeReport'])) {
              $MistakeReportName = $this->_View->Pdf->ConstructReportName($report['MistakeReport']);
            } else {
              $MistakeReportName = '-';
            }

            $output .= '<div class="repair_info">';
            $TooltipRepair  = '<div class="content_for_repairreport" style="display:none">';
            $RepairButtons  = '<div class="button_container">';
            $TooltipRepair .= __('This report (%1$s)', $RepairReportName) . ' ';
            $TooltipRepair .= __('is a repair of report (%1$s) and contain', $MistakeReportName) . ' ';
            //			$TooltipRepair .= __('This repair report (%1$s) contains',$RepairReportName) . ' ';
            if(isset($report['ResultOverview'][3])) {
              if ($report['ResultOverview'][3] == 1) {
                $TooltipRepair .= $report['ResultOverview'][3] . ' ' . __('test area', true) . '<br/>';
              } elseif ($report['ResultOverview'][3] > 1) {
                $TooltipRepair .= $report['ResultOverview'][3] . ' ' . __('test areas', true) . '<br/>';
              }
            }
            
            $TooltipRepair .= '<ul class="repairs">';
            if(isset($report['ResultOverview'])){
              if ($report['ResultOverview'][0] > 0) {
                $RepairButtons .= '<span class="button hint_button" title=""></span>';
                $TooltipRepair .= '<li class="hint">';
                if ($report['ResultOverview'][0] == 1) {
                  $TooltipRepair .= $report['ResultOverview'][0] . ' ' . __('test area', true) . ' ' . __('not evaluated', true) . '<br/>';
                } elseif ($report['ResultOverview'][0] > 1) {
                  $TooltipRepair .= $report['ResultOverview'][0] . ' ' . __('test areas', true) . ' ' . __('not evaluated', true) . '<br/>';
                }
                $TooltipRepair .= '</li>';
              }
              if ($report['ResultOverview'][1] > 0) {
                $RepairButtons .= '<span class="button success_button" title=""></span>';
                $TooltipRepair .= '<li class="success">';
                if ($report['ResultOverview'][1] == 1) {
                  $TooltipRepair .= $report['ResultOverview'][1] . ' ' . __('test area', true) . ' ' . __('successfully passed', true) . '<br/>';
                } elseif ($report['ResultOverview'][1] > 1) {
                  $TooltipRepair .= $report['ResultOverview'][1] . ' ' . __('test areas', true) . ' ' .  __('successfully passed', true) . '<br/>';
                }
                $TooltipRepair .= '</li>';
              }
              if ($report['ResultOverview'][2] > 0) {
                $RepairButtons .= '<span class="button error_button" title=""></span>';
                $TooltipRepair .= '<li class="error">';
                if ($report['ResultOverview'][2] == 1) {
                  $TooltipRepair .= $report['ResultOverview'][2] . ' ' . __('test area', true) . ' ' .  __('defect', true) . '<br/>';
                } elseif ($report['ResultOverview'][2] > 1) {
                  $TooltipRepair .= $report['ResultOverview'][2] . ' ' . __('test areas', true) .  ' ' . __('defect', true) . '<br/>';
                }
                $TooltipRepair .= '</li>';
              }
            }
            //var_dump($TooltipRepair);
            //			$RepairButtons .= $TooltipRepair;
            $RepairButtons .= '</div>';
            $TooltipRepair .= '</ul>';
            $TooltipRepair  .= '</div>';

            $output .= '<span class="tooltip_repair" title="test">' .  __('Repair report', true) . '</span>';
            $output .= $TooltipRepair;
            $output .= $RepairButtons;
            $output .= '</div>';

            $output .= '<script>
					$(function() {
						$("span.tooltip_repair").tooltip({
							content: function() {
								return $(this).next("div.content_for_repairreport").html();
							}
						});
					});
				</script>';
        }
        if (isset($report['OldVersion'])) {
            $output .= '<span class="" title="' . __('revision', true) . '">';
            $OldVersioDesc = null;
            if (isset($report['OldVersion']['info'])) {
                $OldVersioDesc .=  $report['OldVersion']['info'];
                echo $OldVersioDesc;
            } else {
                $OldVersioDesc .= __('revision', true) . ' ';
                $OldVersioDesc .= __('from', true) . ' ';
                $OldVersioDesc .=  $this->_View->Pdf->ConstructReportName($report['OldVersion']);

                $output .= $this->_View->Html->link(
                    $OldVersioDesc,
                    array(
                    'controller'=>'reportnumbers',
                    'action'=>'view',
                    $report['OldVersion']['Reportnumber']['topproject_id'],
                    $report['OldVersion']['Reportnumber']['cascade_id'],
                    $report['OldVersion']['Reportnumber']['order_id'],
                    $report['OldVersion']['Reportnumber']['report_id'],
                    $report['OldVersion']['Reportnumber']['id']
                ),
                    array('title' => $OldVersioDesc, 'class'=>'icon icon_rev_in ajax')
                );
            }

            $output .= '</span>';
        }

        if (isset($report['Reportnumber']['revision'])&& $report['Reportnumber']['revision'] > 0) {
            //			$output .= '<span class="icon_revision tooltip_revision" title="' . __('revision', true) . ' ' . $report['Reportnumber']['revision'] .'">';
            //			$output .= $report['Reportnumber']['revision'];
            //			$output .= '</span>';
            $output .= $this->_View->Html->link(
                $report['Reportnumber']['revision'],
                array(
                'controller'=>'reportnumbers',
                'action'=>'showrevisions',
                $report['Reportnumber']['topproject_id'],
                $report['Reportnumber']['cascade_id'],
                $report['Reportnumber']['order_id'],
                $report['Reportnumber']['report_id'],
                $report['Reportnumber']['id']
            ),
                array(
                    'title' => __('revision', true) . ' ' . $report['Reportnumber']['revision'],
                    'class'=>'tooltip_revision icon icon_revision modal')
            );


            $output .= '<script>
					$(function() {
						$("a.tooltip_revision").tooltip();
					});
				</script>';
        }

        if (isset($report['Children'])) {
            if ($report['Children'] == 1) {
                $desc = $report['Children'] . ' ' . __('assigned report', true);
            }
            if ($report['Children'] > 1) {
                $desc = $report['Children'] . ' ' . __('assigned reports', true);
            }

            $output .= $this->_View->Html->link($desc, array_merge(array('controller' => 'reportnumbers','action' => 'assignedReports'), $this->request->projectvars['VarsArray']), array('title' => $desc,'class' => 'modal icon icon_assigned'));
        }

        $output .= '<script>
					$(function() {

					});
				</script>';

        return $output;
    }

    public function ShowStatus($report)
    {
        $output = null;

        if ($report['Reportnumber']['status'] == 0 && $report['Reportnumber']['delete'] == 0) {
            $output = '<span class="close_0" title="' . __('open', true) . '">';
            $output .= __('open', true);
            $output .= '</span>';
        }
        if ($report['Reportnumber']['status'] == 1 && $report['Reportnumber']['delete'] == 0) {
            $output = '<span class="close_1" title="' . __('closed by examiner', true) . '">';
            $output .= __('closed by examiner', true);
            $output .= '</span>';
        }
        if ($report['Reportnumber']['status'] == 2 && $report['Reportnumber']['delete'] == 0) {
            $output = '<span class="close_2" title="' . __('closed by supervisor', true) . '">';
            $output .= __('closed by supervisor', true);
            $output .= '</span>';
        }
        if ($report['Reportnumber']['status'] == 3 && $report['Reportnumber']['delete'] == 0) {
            $output = '<span class="close_3" title="' . __('settled', true) . '">';
            $output .= __('settled', true);
            $output .= '</span>';
        }
        if ($report['Reportnumber']['settled'] == 3 && $report['Reportnumber']['delete'] == 0) {
            $output .= '<span class="settled" title="' . __('abgerechnet', true) . '">';
            $output .= __('abgerechnet', true);
            $output .= '</span>';
        }
        if (Configure::check('ReportStatusShowHandlingTime') && Configure::read('ReportStatusShowHandlingTime') == true && isset($report['Reportnumber']['handling_time']) && $report['Reportnumber']['handling_time'] > 0) {
            $output .= '<span class="handling_time" title="' . __('Handling time from creating to first printing', true) . ' ' . $report['Reportnumber']['handling_time'] . '&nbsp;' . $report['Reportnumber']['handling_time_measure'] . '">';
            $output .= $report['Reportnumber']['handling_time'] . '&nbsp;' . $report['Reportnumber']['handling_time_measure'];
            $output .= '</span>';
        }


        if (isset($report['RepairReporting']) && $report['RepairReporting'] == true) {
            //pr(count($report['Repair']));

            $RepairReportName = $this->_View->Pdf->ConstructReportName($report);

            if ($report['Repair']['repair_status'] == 0 && count($report['Repair']) == 1) {
                $Attributes = 				array(
                    'title' => __('There is no repair yet', true),
                    'class'=>'icon icon_repair_status_ modal'
                    );
            }
            if ($report['Repair']['repair_status'] == 0 && count($report['Repair']) > 1) {
                $Attributes = 				array(
                    'title' => __('This repair of this report (%1$s) is in progress', $RepairReportName),
                    'class'=>'icon icon_repair_status_0 modal'
                    );
            }
            if ($report['Repair']['repair_status'] == 1) {
                $Attributes = 				array(
                    'title' => __('This repair of this report (%1$s) was successful', $RepairReportName),
                    'class'=>'icon icon_repair_status_1 modal'
                    );
            }
            if ($report['Repair']['repair_status'] == 2) {
                $Attributes = 				array(
                    'title' => __('This repair of this report (%1$s) was unsuccessful', $RepairReportName),
                    'class'=>'icon icon_repair_status_2 modal'
                    );
            }

            $output .= $this->_View->Html->link(
                __('Show repair', true),
                array(
                    'controller'=>'reportnumbers',
                    'action'=>'repairs',
                    $report['Reportnumber']['topproject_id'],
                    $report['Reportnumber']['cascade_id'],
                    $report['Reportnumber']['order_id'],
                    $report['Reportnumber']['report_id'],
                    $report['Reportnumber']['id']
                ),
                $Attributes
            );
        }

        $output .= '<script>
					$(function() {


					});
				</script>';

        return $output;
    }

    public function ShowHeadData($data, $setting, $lang)
    {
        $output  = null;
        $output .= '<dl>';
        foreach ($setting as $_setting) {
            $output .= '<div class="dl">';
            $output .= '<dt>'.$_setting->discription->$lang.':</dt>';
            $output .= '<dd>'.$data[trim($_setting->model)][trim($_setting->key)].'&nbsp;</dd>';
            $output .= '</div>';
        }

        $output .= '<div class="clear"></div>';
        $output .= '</dl>';
        return $output;
    }

    public function ShowOrderData($data, $setting, $lang)
    {
        $output  = null;
        echo '<div class="infos" >';
        foreach ($setting as $_key => $_setting) {
            if (trim($_setting->key) == 'id') {
                continue;
            }
            if ($_setting->output->screen != 1) {
                continue;
            }

            $value = $data[trim($_setting->model)][trim($_setting->key)];

            if ($_setting->fieldtype == 'radio' && $_setting->radiooption) {
                $value = trim($_setting->radiooption->value[$value]);
            }

            if (!$value) {
                $value = '-';
            }

            if (trim($_setting->fieldset) == 1) {
                echo '<span class="clear"></span>';
            }

            echo '<p class="output">';
            echo $value;
            echo '<span class="title">';
            echo trim($_setting->discription->$lang);
            echo '</span>';
            echo '</p>';
        }

        echo '<span class="clear"></span></div>';

        return $output;
    }

    public function ShowOrderEvaluationData($data)
    {
        $output  = null;
        $output .= '<div><table cellpadding = "0" cellspacing = "0">';
        $output .= '<tr>';
        $output .= '<th>'.__('Reportnumber', true).'</th>';
        $output .= '<th>'.__('Reports', true).'</th>';
        $output .= '<th>'.__('Testingmethod', true).'</th>';
        $output .= '<th>'.__('Testingcompany', true).'</th>';
        $output .= '<th>'.__('Created', true).'</th>';
        $output .= '<th>'.__('Modified', true).'</th>';
        $output .= '<th>&nbsp;</th>';
        $output .= '</tr>';
        $i = 0;
        foreach ($data as $_data) {
            $class = null;

            if ($i++ % 2 == 0) {
                $class = ' altrow';
            }

            if ($_data['Reportnumber']['status'] == 1) {
                $class = ' closed';
            }
            if ($_data['Reportnumber']['status'] == 2) {
                $class = ' settled';
            }
            if ($_data['Reportnumber']['delete'] == 1) {
                $class = ' delete';
            }


            $output .= '<tr class="'.$class.'">';
            $output .= '<td>'.$_data['Reportnumber']['year'].'-'.$_data['Reportnumber']['number'].'</td>';
            $output .= '<td>'.$_data['Report']['name'].'</td>';
            $output .= '<td>'.$_data['Testingmethod']['verfahren'].'</td>';
            $output .= '<td>'.$_data['Testingcomp']['name'].'</td>';
            $output .= '<td>'.$_data['Reportnumber']['created'].'</td>';
            $output .= '<td>'.$_data['Reportnumber']['modified'].'</td>';

            $output .= '<td class="actions">';

            if ($_data['Reportnumber']['delete'] != 1) {
                $output .= $this->Html->link(__('Edit'), array('controller' => 'reportnumbers', 'action' => 'edit',
                $_data['Reportnumber']['topproject_id'],
                $_data['Reportnumber']['equipment_type_id'],
                $_data['Reportnumber']['equipment_id'],
                $_data['Reportnumber']['order_id'],
                $_data['Reportnumber']['report_id'],
                $_data['Reportnumber']['id']
                ), array('class'=>'icon icon_edit ajax'));
                $output .= $this->Html->link(__('View'), array('controller' => 'reportnumbers', 'action' => 'view',
                $_data['Reportnumber']['topproject_id'],
                $_data['Reportnumber']['equipment_type_id'],
                $_data['Reportnumber']['equipment_id'],
                $_data['Reportnumber']['order_id'],
                $_data['Reportnumber']['report_id'],
                $_data['Reportnumber']['id']
            ), array('class'=>'icon icon_view ajax'));
            }

            $output .= '</td>';
            $output .= '</tr>';
        }

        $output .= '</table><div class="clear"></div></div>';
        $output .= '<div class="paging">';
        $output .= $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
        $output .= $this->Paginator->numbers(array('separator' => ''));
        $output .= $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
        $output .= '</div>';
        $output .= '<p class="paging_query">';
        $output .= $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
        $output .= '</p>';

        return $output;
    }

    public function ShowGeneralyData($data, $setting, $lang, $table)
    {
        $ReportTable = 'Report' . $this->request->verfahren . $table;

        echo '<div class="view">';

        foreach ($setting as $_setting) {

            $output_value = $data[trim($_setting->model)][trim($_setting->key)];

            if (trim($_setting->fieldtype) == 'radio') {
                $radiooptions = $this->radiodefault;

                if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {
                    $radiooptions = array();

                    foreach ($_setting->radiooption->value as $_radiooptions) {
                        array_push($radiooptions, trim($_radiooptions));
                    }
                }

                $output_value = $radiooptions[(int)$data[trim($_setting->model)][trim($_setting->key)]];
            } elseif (trim($_setting->fieldtype) == 'checkbox') {
                if ((int)$data[trim($_setting->model)][trim($_setting->key)] == 1) {
                    $output_value = 'X';
                }
            }

            echo '<dl class="view ';

            //			echo $data['Tableshema'][$ReportTable][trim($_setting->key)]['type'] . ' ';

            if (!empty($_setting->validate->notempty) && trim($_setting->validate->notempty) == 1 && strlen($output_value) == 0) {
                echo 'notempty ';
                $output_value = '&nbsp;';
            }

            echo '" ';
            echo 'id="' . $ReportTable . '0' . Inflector::camelize(trim($_setting->key)) . '"';
            echo '>';

            echo '<dd>';
            echo $output_value;
            echo '</dd>';

            echo '<dt>';
            echo $_setting->discription->$lang;
            echo '</dt>';

            echo '</dl>';
        }

        echo '</div>';
        echo '<div class="clear"></div>';

        echo '<script>
					$(function() {

					var OutputHeight = 0;

					$("dl.view").each(function(){

						if($(this).hasClass("text")){
							return;
						}

						if($(this).height() > OutputHeight) {
							OutputHeight = $(this).height() + 2;
						}
					});


//					$("dl.view").css("height",OutputHeight+"px");

					});
				</script>';
    }

    public function ShowEvaluationData($reportnumber, $data, $setting, $lang, $dropdowns, $urlvariablen, $settings)
    {
        $ReportPdf  = 'Report' . ucfirst($this->request->verfahren) . 'Pdf';
        $output  = null;
        $output .= '<div>';


        if ($reportnumber['Reportnumber']['repair_for'] == 0 || Configure::read('RepairAddNewEvaluation') == true) {
            $output .= $this->Form->create(
                'Reportnumber',
                array(
                                                'id' => 'ReportnumberMassFunktion',
                                                'class' => 'mass_function'
                                                )
            );

            $attribut_disabled = false;

            if (
            ($this->data['Reportnumber']['status'] > 0 && $this->data['Reportnumber']['revision_progress'] == 0) ||
            $this->data['Reportnumber']['deactive'] > 0 ||
            $this->data['Reportnumber']['settled'] > 0 ||
            $this->data['Reportnumber']['delete'] > 0
        ) {
                $attribut_disabled = true;
            }

            if (isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) {
                $attribut_disabled = false;
            }

            if ($attribut_disabled == true) {
                $optionsMassSelect = array(
                0 => __('Massenaktion wählen'));
                $optionsMassSelect['weldlabel'] = __('Print weld label');

                $output .= $this->Form->input(
                    'MassSelect',
                    array(
                                                'class' => 'mass_funktion_select',
                                                'options' => $optionsMassSelect,
                                                'defaut' => 0,
                                                'label' => false,
                                                'div' => false
                                                )
                );
            } elseif ($attribut_disabled == false) {
                if (isset($this->request->data['has_result'])) {
                    $errors_remove = array();

                    if (isset($this->request->data['has_error'])) {
                        $errors_remove = array('errors_remove' => __('Fehler entfernen'));
                    }

                    $optionsMassSelect = array(
                0 => __('Massenaktion wählen'),
                'duplicatevalution' => __('Duplizieren'),
                'deleteevalution' => __('Löschen'),
                'result_e' => __('Mit e bewerten'),
                'result_ne' => __('Mit ne bewerten'),
                'result_no' => __('Bewertungen entfernen')
            );
                    $optionsMassSelect = array_merge($optionsMassSelect, $errors_remove);
                } else {
                    $optionsMassSelect = array(
                0 => __('Massenaktion wählen'),
                'duplicatevalution' => __('Duplizieren'),
                'deleteevalution' => __('Löschen')
            );
                }

             ;
                if (isset($settings->$ReportPdf->settings->QM_WELDLABEL->items) && count($settings->$ReportPdf->settings->QM_WELDLABEL->items) > 0) {
                    $optionsMassSelect['weldlabel'] = __('Print weld label');
                }

                if ($attribut_disabled == false) {
                    $output .= $this->Form->input(
                        'MassSelect',
                        array(
                                                'class' => 'mass_funktion_select',
                                                'options' => $optionsMassSelect,
                                                'defaut' => 0,
                                                'label' => false,
                                                'div' => false
                                                )
                    );

                    $output .= $this->Form->input(
                        'okay',
                        array(
                                                'type' => 'hidden',
                                                'value' => 0
                                                )
                    );
                }
                if (count($setting) > 0) {
                    $output .= $this->Html->link(
                        __('Reset evaluation sorting', true),
                        array_merge(
                    array('controller'=>'reportnumbers', 'action'=>'resetevaluation'),
                    $this->_View->viewVars['VarsArray']
                ),
                        array('class' => 'modal round', 'title'=>__('Reset evaluation sorting', true))
                    );

                    $output .= $this->Html->link(
                        __('New Evaluation Area', true),
                        array("action" => 'editevalution',
                    $this->request->projectvars['projectID'],
                    $this->request->projectvars['cascadeID'],
                    $this->request->projectvars['orderID'],
                    $this->request->projectvars['reportID'],
                    $this->request->projectvars['reportnumberID'],

                    ),
                        array(
                    'class' => 'ajax round',
                    'title' => __('New Evaluation Area', true)
                )
                    );
                }
                /* bis hier her */
            }
        }
        $output .= '<table cellpadding = "0" cellspacing = "0" class="editable sortable evaluation_table">';
        $output .= '<thead>';

        $evalHeaders = '';
        $evalDiscription = array();
        foreach ($setting as $_setting) {
            if (trim($_setting->showintable) == 1) {
                  if (isset($this->_View->viewVars['replaceheaderdata'])&&($this->_View->viewVars['replaceheaderdata'] == '1' || $this->_View->viewVars['replaceheaderdata'] == 'true') && isset($_setting->headerfrom) &&  isset($_setting->headerfrom->key)&&isset($_setting->headerfrom->model)) {
                    $headmodel = trim($_setting->headerfrom->model);
                    $headkey =  trim($_setting->headerfrom->key);
                    if(!empty($reportnumber[$headmodel][$headkey])) {
                      $evalHeaders.='<th>'.$reportnumber[$headmodel][$headkey].($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null).'</th>';
                    } else{
                      $evalHeaders .= '<th>'.$_setting->discription->$lang.($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null).'</th>';
                    }
                  }else{
                    $evalHeaders .= '<th>'.$_setting->discription->$lang.($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null).'</th>';
                  }
                if (trim($_setting->key) == 'description') {
                    $evalHeaders .= '<th class="no_padding"></th>';
                }

                $evalDiscription[] = $_setting->discription->$lang.($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null);
            }
        }
        //		$evalHeaders .= '<th>&nbsp;</th>';
        if ($this->_View->viewVars['replaceHeaderSetting'] == '1' || $this->_View->viewVars['replaceHeaderSetting'] == 'true') {
            $output .= '<tr><th colspan="'.(count($evalDiscription)+3).'" class="hint">'.__('Attention: Entries in first row will be used for titles.').'</th></tr>';
        }

        $output .= '<tr>';
        $output .= '<th>';
        $output .= 	$this->Form->input('all_welds', array('type' => 'checkbox','label' => '&nbsp;'));
        $output .= '</th>';
        $output .= $evalHeaders;
        $output .= '<th></th>';
        $output .= '</tr>';
        $output .= '</thead>';

        $i = 0;

        $modelpart  = 'Report' . $this->request->verfahren.'Evaluation';
        $Modelpart  = 'Report' . $this->request->Verfahren.'Evaluation';

        foreach ($data as $dataArray) {
            if (isset($reportnumber['RevisionValues'][$modelpart][$dataArray[$Modelpart]['weld'][0]['id']])) {
                $revtrue = 1;
            } else {
                $revtrue = 0;
            }
            $comp_weld = '1';

            $output .= '<tbody class="weld weld_'.$dataArray[$Modelpart]['discription'].'">';
            // Zuerst kommt die Nahtbezeichnung, wenn mehr als ein Nahtbereich vorhanden sind
            if (count($dataArray[$Modelpart]['weld']) > 0) {
                $comp_weld = 0;
                $x = 0 ;

                $output .= '<tr>';
                $output .= '<td class="weldhead">';
                $output .= 	$this->Form->input(
                    'weldhead_' . $dataArray[$Modelpart]['id'],
                    array(
                                        'type' => 'checkbox',
                                        'class' => 'check_weld '.$dataArray[$Modelpart]['id'],
                                        'label' => '&nbsp;',
                                        'value' => $dataArray[$Modelpart]['id'],
                                        'weld-id' => $dataArray[$Modelpart]['id']
                                    )
                );
                $output .= '</td>';

                $output .= '<td class="weldhead" >';

                $output .= '<span class="for_hasmenu1">';

                $output .= $this->Html->link(
                    $dataArray[$Modelpart]['discription'],
                    array('action' => 'editevalution',
                    $this->request->projectvars['projectID'],
                    $this->request->projectvars['cascadeID'],
                    $this->request->projectvars['orderID'],
                    $this->request->projectvars['reportID'],
                    $this->request->projectvars['reportnumberID'],
                    $dataArray[$Modelpart]['id'],
                    '1'
                ),
                    array(
                    'class'=>'round icon_edit ajax hasmenu1',
                    'title' => __('Edit') . ' ' . $dataArray[$Modelpart]['discription'],
                    'rev' =>
                        $this->request->projectvars['VarsArray'][0] . '/' .
                        $this->request->projectvars['VarsArray'][1] . '/' .
                        $this->request->projectvars['VarsArray'][2] . '/' .
                        $this->request->projectvars['VarsArray'][3] . '/' .
                        $this->request->projectvars['VarsArray'][4] . '/' .
                        $dataArray[$Modelpart]['id'] . '/' .
                        '1'
                        )
                );

                $output .= '</span>';
                $output .= '<div class="clear"></div>';
                $output .= '</td>';

                $evalDiscriptionCount = count($evalDiscription) + 1;
                $output .= '<td class="no_padding" colspan="'. $evalDiscriptionCount .'">';

                if ($revtrue == 1) {
                    $revisionlink  = $this->Html->link('Showrevisions', array('controller' => 'reportnumbers',
                        'action' => 'showrevisions',$this->request->projectvars['VarsArray'][0],
                                $this->request->projectvars['VarsArray'][1],
                                $this->request->projectvars['VarsArray'][2],
                                $this->request->projectvars['VarsArray'][3],
                                $this->request->projectvars['VarsArray'][4],
                                $dataArray[$Modelpart]['id'],
                                $comp_weld,

                                                                ), array_merge(
                                                                    array(
                                    'class'=> 'tooltip_ajax_revision',
                                    'title'=> __('Content will load...', true),
                                                                        'id' => $modelpart.'/all',
                                    )
                                                                ));
                } else {
                    $revisionlink = '';
                }
                $output.= $revisionlink;

                if (Configure::read('DevelopmentsEnabled') == true) {
                    if (isset($dataArray[$Modelpart]['development'])) {
                        if ($dataArray[$Modelpart]['development'] == 0) {
                            $developmentClass = array('development_open',__('marked as not processed'));
                        }
                        if ($dataArray[$Modelpart]['development'] == 1) {
                            $developmentClass = array('development_rep',__('marked as error'));

                            $output .= $this->Html->link(__('add progress', true), array('controller' => 'developments', 'action' => 'progressadd',
                            $this->request->projectvars['VarsArray'][0],
                            $this->request->projectvars['VarsArray'][1],
                            $this->request->projectvars['VarsArray'][2],
                            $this->request->projectvars['VarsArray'][3],
                            $this->request->projectvars['VarsArray'][4],

                            $dataArray[$Modelpart]['id'],
                            '1'
                          ), array('class'=>'addlink modal icon','title' => __('add progress', true)));
                        }
                        if ($dataArray[$Modelpart]['development'] == 2) {
                            $developmentClass = array('development_ok',__('marked as processed'));
                        }

                        $output .= $this->Html->link(__('examination development', true), array('controller' => 'developments', 'action' => 'change',
                            $this->request->projectvars['VarsArray'][0],
                            $this->request->projectvars['VarsArray'][1],
                            $this->request->projectvars['VarsArray'][2],
                            $this->request->projectvars['VarsArray'][3],
                            $this->request->projectvars['VarsArray'][4],
                            $dataArray[$Modelpart]['id'],
                            '1'
                        ), array('class'=>'development_evalution icon modal '.$developmentClass[0], 'id'=>'this_development_'.$dataArray[$Modelpart]['id'],'title' => $developmentClass[1]));
                    }
                }

                $output .= '</td>';
                $output .= '</tr>';
            }
            foreach ($dataArray[$Modelpart]['weld'] as $weld) {

//				if(count($dataArray[$Modelpart]['weld']) == 1) continue;

                if (isset($reportnumber['RevisionValues'][$modelpart][$weld['id']])) {
                    $revtrue = 1;
                } else {
                    $revtrue = 0;
                }

                $class = null;
                if ($i++ % 2 == 0) {
                    $class = ' class="altrow"';
                }
                if (isset($this->request->data['has_result']) && $weld['result'] == 2) {
                    $class = ' class="error"';
                }

                $output .= '<tr rel="'.$weld['id'].'" '.$class.'>';
                $output .= '<td>';
                // Checkbox für Massenaktion
                $output .= 	$this->Form->input(
                    'check_' . $weld['id'],
                    array(
                                                            'type' => 'checkbox',
                                                            'label' => '&nbsp;',
                                                            'class' => 'check_weld_position',
                                                            'weld-id' => $dataArray[$Modelpart]['id'],
                                                            'value' => $weld['id']
                                                        )
                );
                $output .= '</td>';


                $xx = 0;
                foreach ($setting as $_key => $_setting) {

                    if (trim($_setting->showintable) == 1) {
                        $output .= '<td class="col_'.$_setting->key.'">';
                        //						pr($evalDiscription);
                        $output .= '<span class="discription_mobil">';
                        $output .= $evalDiscription[$xx] . ': ';
                        $output .= '</span>';

                        $output .= '<span';

                        // Wenn der PRüfbericht geschlossen ist, keine Bearbeitung mehr ermöglichen
                        //						if($this->_View->viewVars['reportnumber']['Reportnumber']['status'] != 0) $_setting->editable = 0;
                        if (isset($attribut_disabled) && $attribut_disabled == true) {
                            $_setting->editable = 0;
                        }

                        //						if($_setting->editable == 1 && !$replaceResult){
                        if ($_setting->editable == 1 && $reportnumber['Reportnumber']['revision_progress'] < 1) {
                            $output .= ' class="edita"';
                            $output .= ' id="editable_'.$_setting->key.'_'.$weld['id'].'"';
                        }

                        $output .= '>';

                        if (trim($_setting->fieldtype) == 'radio') {
                            $radiooptions = $this->radiodefault;


                            if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {
                                $radiooptions = array();

                                foreach ($_setting->radiooption->value as $_radiooptions) {
                                    array_push($radiooptions, trim($_radiooptions));
                                }
                            }

                            //							if($replaceResult) {
                            //								$output .= join(', ', $errors);
                            //							} else {
                            $output .= $radiooptions[(int)$weld[trim($_setting->key)]];
                        //							}
                        } elseif (trim($_setting->fieldtype) == 'checkbox') {
                            if ($weld[trim($_setting->key)] == 1) {
                                $output .= 'X';
                            }
                        } else {
                            if ($xx == 0) {
                                //								$weld_pos = $weld[trim($_setting->key)];
                                $weld_pos = null;
                                $weld_title = null;
                                if (isset($weld['position'])) {
                                    $weld_pos .= $weld['position'];
                                    $weld_title = __('Edit') . ' ' . $weld['description']. '/' . $weld['position'];
                                }

                                $hasmenu = 'hasmenu2';

                                if (count($dataArray[$Modelpart]['weld']) == 1) {
                                    if (count($dataArray[$Modelpart]['weld']) > 1) {
                                        $weld_pos = ' '.$weld['description'];
                                    } elseif (count($dataArray[$Modelpart]['weld']) == 1 && isset($weld['position'])) {
                                        $weld_pos = ' '.$weld['position'];
                                    } else {
                                        $weld_pos = ' -----';
                                    }
                                    $weld_title = __('Edit') . ' ' . $weld['description'];
                                    $hasmenu = 'hasmenu1';
                                }

                                $output .= '<span class="';

                                if ($reportnumber['Reportnumber']['repair_for'] == 0) {
                                    $output .= 'for_' . $hasmenu;
                                }

                                $output .=	' contexmenu_weldposition">';
                                $output .= $this->Html->link(
                                    $weld_pos,
                                    array('action' => 'editevalution',
                    $this->request->projectvars['projectID'],
                    $this->request->projectvars['cascadeID'],
                    $this->request->projectvars['orderID'],
                    $this->request->projectvars['reportID'],
                    $this->request->projectvars['reportnumberID'],
                                    $weld['id'],
                                    $comp_weld
                                ),
                                    array(
                                    'title' => $weld_title,
                                    'class' => 'round_white icon_edit ajax ' . $hasmenu,
                                    'rev' =>
                                        $this->request->projectvars['VarsArray'][0] . '/' .
                                        $this->request->projectvars['VarsArray'][1] . '/' .
                                        $this->request->projectvars['VarsArray'][2] . '/' .
                                        $this->request->projectvars['VarsArray'][3] . '/' .
                                        $this->request->projectvars['VarsArray'][4] . '/' .

                                        $weld['id'] . '/' .
                                        $comp_weld
                                    )
                                );

                                if ($revtrue == 1) {
                                    $revisionlink  = $this->Html->link(
                                        'Showrevisions',
                                        array(
                                                            'controller' => 'reportnumbers',

                                                                'action' => 'showrevisions',$this->request->projectvars['VarsArray'][0],
                                $this->request->projectvars['VarsArray'][1],
                                $this->request->projectvars['VarsArray'][2],
                                $this->request->projectvars['VarsArray'][3],
                                $this->request->projectvars['VarsArray'][4],

                                $weld['id'],
                                $comp_weld,

                                                                ),
                                        array_merge(
                                                                array(
                                    'class'=> 'tooltip_ajax_revision',
                                    'title'=> __('Content will load...', true),
                                                                        'id' => $modelpart.'/all',
                                    )
                                                            )
                                    );
                                } else {
                                    $revisionlink = '';
                                }
                                $output.= $revisionlink;

                                $output .= '<span class="clear"></span>';
                                $output .= '</span>';
                            } else {
                                $output .= $weld[trim($_setting->key)];
                            }
                        }

                        $output .= '</span></td>';


                        if ($xx == 0) {
                            $output .= '<td class="no_padding">';


                            if (count($dataArray[$Modelpart]['weld']) == 1) {
                                if (Configure::read('DevelopmentsEnabled') == true) {
                                    if (isset($dataArray[$Modelpart]['development'])) {
                                        if ($dataArray[$Modelpart]['development'] == 0) {
                                            $developmentClass = array('development_open',__('marked as not processed'));
                                        }
                                        if ($dataArray['development'] == 1) {
                                            $developmentClass = array('development_rep',__('marked as error'));
                                            $this->request->data['Test'] = 1;
                                            $output .= $this->Html->link(__('add progress', true), array('controller' => 'developments', 'action' => 'progressadd',
                                                                                $this->request->projectvars['VarsArray'][0],
                                                                                $this->request->projectvars['VarsArray'][1],
                                                                                $this->request->projectvars['VarsArray'][2],
                                                                                $this->request->projectvars['VarsArray'][3],
                                                                                $this->request->projectvars['VarsArray'][4],

                                                                                $dataArray[$Modelpart]['id'],
                                                                                '1'
                                                                                ), array('class'=>'addlink modal','title' =>''));
                                        }
                                        if ($dataArray['development'] == 2) {
                                            $developmentClass = array('development_ok',__('marked as processed'));
                                        }

                                        $output .= $this->Html->link(__('examination development', true), array('controller' => 'developments', 'action' => 'change',
                                                $this->request->projectvars['VarsArray'][0],
                                                $this->request->projectvars['VarsArray'][1],
                                                $this->request->projectvars['VarsArray'][2],
                                                $this->request->projectvars['VarsArray'][3],
                                                $this->request->projectvars['VarsArray'][4],

                                                $dataArray[$Modelpart]['id'],
                                                '1'
                                            ), array('class'=>'development_evalution icon modal '.$developmentClass[0], 'id'=>'this_development_'.$dataArray[$Modelpart]['id'],'title' => $developmentClass[1]));
                                    }
                                }
                            }

                            $output .= '</td>';
                        }

                        if ($_setting->editable == 1) {
                            $new = array();
                            if ($_setting->select->model[0] != '' && isset($dropdowns[trim($_setting->model)][trim($_setting->key)])) {
                                $decode=json_decode($dropdowns[trim($_setting->model)][trim($_setting->key)]);
                                foreach ($decode as $dc => $dcval) {
                                    $new[$dcval] = $dcval;
                                }


                                $select_for_jeditable = 'data: \''.json_encode($new).'\',type:"select",';
                            } else {
                                $select_for_jeditable = null;
                            }

                            if ($_setting->fieldtype == 'radio' && isset($dropdowns[trim($_setting->model)][trim($_setting->key)])) {
                                $radiooptions_editable = $this->radiodefault;
                                if ($_setting->radiooption) {
                                    $radiooptions_editable = array();
                                    if (count($_setting->radiooption->value) > 0) {
                                        $radiooptions_editable = array();
                                        foreach ($_setting->radiooption->value as $_radiokey => $_radiovalue) {
                                            $radiooptions_editable[] = trim($_radiovalue);
                                        }
                                        $select_for_jeditable = 'data: \''.json_encode($radiooptions_editable).'\',type:"select",';
                                    }
                                }
                            }

                            $output .= '
								<script type="text/javascript">
									$(document).ready(function(){
										$("#editable_'.$_setting->key.'_'.$weld['id'].'").editable(" ' . Router::url(array('action'=>'editableUp')) . '", {
											indicator : "<img src=\'img/indicator.gif\'>",
											tooltip   : "'.__('Click to edit').'",
											onblur : "submit",
											placeholder : "&nbsp;",
											submitdata : {
												ajax_true: 1,
												report_number: ' . $weld['reportnumber_id'] . ',
											},
											cssclass : "editables",
											'.$select_for_jeditable.'
											method : "POST",
											callback : function($select_for_jeditable) {

												var select_for_jeditable = $select_for_jeditable;
												var select_array = $(this).attr("id").split("_");

												if(select_array[1] == "result" && select_for_jeditable == "ne"){
													$(this).closest("tr").removeClass();
													$(this).closest("tr").addClass("error");
												}
												if(select_array[1] == "result" && select_for_jeditable != "ne"){
													$(this).closest("tr").removeClass();
												}
											}
										});
									});
								</script>
								';
                        }
                        $xx++;
                    }
                }

                $output .= '<td>';
                if (isset($weld['RepairReport'])) {
                    $output .= '<span class="repair '.$weld['class_for_repair_view'].'"></span>';
                }
                $output .= '</td>';

                $output .= '</tr>';
            }
            $output .= '</tbody>';
        }

        $output .= '</table>';
        $output .= $this->Form->end();
        $output .= '<div class="clear"></div></div>';

        $url = $this->Html->url(array('controller' => 'reportnumbers','action' => 'massActions',$this->request->projectvars['VarsArray'][0],$this->request->projectvars['VarsArray'][1],$this->request->projectvars['VarsArray'][2],$this->request->projectvars['VarsArray'][3],$this->request->projectvars['VarsArray'][4],$this->request->projectvars['VarsArray'][5]));

        $output .= '<script type="text/javascript">
		$(document).ready(function(){

		var modalheight = Math.ceil(($(window).height() * 90) / 100);
		var modalwidth = Math.ceil(($(window).width() * 90) / 100);

		var dialogOpts = {
			modal: false,
			width: modalwidth,
			height: modalheight,
			autoOpen: false,
			draggable: true,
			resizeable: true
		};

		$("#ReportnumberAllWelds").click(function() {
			if($(this).is(":checked")){
				$("#ReportnumberMassFunktion input.check_weld,#ReportnumberMassFunktion input.check_weld_position").attr("checked", true);
				$("#ReportnumberMassFunktion input.check_weld,#ReportnumberMassFunktion input.check_weld_position").button("refresh");
			}
			else {
				$("#ReportnumberMassFunktion input.check_weld,#ReportnumberMassFunktion input.check_weld_position").attr("checked", false);
				$("#ReportnumberMassFunktion input.check_weld,#ReportnumberMassFunktion input.check_weld_position").button("refresh");
			}
		});

		$("a#printWeldLabels").click(function() {
			form = "<form id=\"tmpForm\" action=\""+$(this).attr("href")+"\" method=\"POST\" target=\"_blank\">";

			data = $("#ReportnumberMassFunktion").serializeArray();
			print = 0;
			for(id in data) {
				if(data[id]["name"].lastIndexOf("data[Reportnumber]", 0) === 0 && data[id]["value"] != 0){
					print = 1;
					form += "<input type=\"text\" name=\""+data[id]["name"]+"\" value=\""+data[id]["value"]+"\" />";
				}
			}

			form += "</form>";
			if(print == 1) {
				$("body").append(form);
				$("body > #tmpForm").submit().remove();
			} else {
				// Alert mit Hinweis, dass keine Nähte ausgewählt sind
				alert("'.__('No welds selected').'");
			}

			return false;
		});

		$(".check_weld").click(function() {

			var weld_id = $(this).attr("id");
			var weld_val = $(this).val();

			if($(this).is(":checked")){
				$("#ReportnumberMassFunktion input.check_weld_position").each(function(){
					if($(this).attr("weld-id") == weld_val){
						$(this).attr("checked", true);
						$(this).button("refresh");
					}
				});
			}
			else {
				$("#ReportnumberMassFunktion input.check_weld_position").each(function(){
					if($(this).attr("weld-id") == weld_val){
						$(this).attr("checked", false);
						$(this).button("refresh");
					}
				});
			}

		});

		$(".check_weld_position").click(function() {

			var weld_id = $(this).attr("weld-id");
			var pos_count = 0;
			var pos_count_checked = 0;

			$("#ReportnumberMassFunktion input.check_weld_position").each(function(){
				if($(this).attr("weld-id") == weld_id){

					pos_count++;

					if($(this).attr("checked") == "checked"){
						pos_count_checked++;
					}
				}
			});

			if(pos_count_checked == 0){
				$("#ReportnumberWeldhead" + weld_id).attr("checked", false);
				$("#ReportnumberWeldhead" + weld_id).button("refresh");
			}

			if(pos_count_checked == pos_count){
				$("#ReportnumberWeldhead" + weld_id).attr("checked", true);
				$("#ReportnumberWeldhead" + weld_id).button("refresh");
			}
		});

		$("#ReportnumberMassSelect").change(function() {

			if($("#ReportnumberMassSelect option:selected").val() != 0){

				$("#dialog").dialog(dialogOpts);
				var data = $("#ReportnumberMassFunktion").serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: "' . $url . '",
					data	: data,
					success: function(data) {
						$("#dialog").html(data);
						$("#dialog").show();
					}
				});
				$("#dialog").dialog("open");
				$("#ReportnumberMassFunktion input#ReportnumberAllWelds,#ReportnumberMassFunktion input.check_weld,#ReportnumberMassFunktion input.check_weld_position").attr("checked", false);
				$("#ReportnumberMassFunktion input#ReportnumberAllWelds,#ReportnumberMassFunktion input.check_weld,#ReportnumberMassFunktion input.check_weld_position").button("refresh");
				$("#ReportnumberMassSelect option[value=\'0\']").attr("selected",true);
				return false;
			}
		});

		updateRows = function(event, ui) {
			$(".sortable").addClass("is_sorting").sortable().sortable( "option", "disabled", true);
			data = {ajax_true: 1};
			$(this).find("tr[rel]").each(function(i, e) {
				data[i] = {
					value: i,
					id: "editable_sorting_"+$(e).attr("rel"),
					report_number: '.$this->_View->viewVars['reportnumber']['Reportnumber']['id'].'
				};

				$(e).find(".col_sorting span").text(i);
			});

			$.post("'.Router::url(array('action'=>'editableUp')).'", data, function(e) {
				$(".sortable").removeClass("is_sorting").sortable().sortable( "option", "disabled", false);
			});
		}';


        if (
        ($this->data['Reportnumber']['status'] > 0 && $this->data['Reportnumber']['revision_progress'] == 0 ) ||
        $this->data['Reportnumber']['deactive'] > 0 ||
        $this->data['Reportnumber']['settled'] > 0 ||
        $this->data['Reportnumber']['delete'] > 0) {
            $attribut_array['disabled'] = "disabled";
        } else {
            $output .= '
			if($(".sortable").length != 0) {
				$(".sortable").sortable({
					items: "> tbody",
					appendTo: "parent",
					helper: "clone",
					cursor: "move",
					update: updateRows
				}).children("tbody").sortable({
					items: "tr:not(tr:first-child)",
					cursor: "move",
					update: updateRows
				});
			}';
        }

        $output .= '

		var modalheight = Math.ceil(($(window).height() * 90) / 100);
		var modalwidth = Math.ceil(($(window).width() * 90) / 100);

		var dialogOpts = {
			modal: false,
			width: modalwidth,
			height: modalheight,
			autoOpen: false,
			draggable: true,
			resizeable: true
			};

		$("#dialog").dialog(dialogOpts);

		$("span.for_hasmenu1").contextmenu({
			delegate: ".hasmenu1",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: " ' . __('Gesamte Naht bearbeiten') . ' ",
				cmd: "editevalution",
				action :	function(event, ui) {
							$("#container").load("reportnumbers/editevalution/" + ui.target.attr("rev"), {
									"ajax_true": 1
								})
							},
				uiIcon: "qm_edit"
				},
				{
				title: "----"
				},
				{
				title: " ' . __('Gesamte Naht duplizieren') . ' ",
				cmd: "duplicatevalution",
				action :	function(event, ui) {
								checkDuplicate = confirm(" ' . __('Soll die gesamte Naht dupliziert werden?') . '");
								if (checkDuplicate == false) {
									return false;
								}
								$("#container").load("reportnumbers/duplicatevalution/" + ui.target.attr("rev"), {
									"ajax_true": 1
								})
							},
				uiIcon: "qm_duplicate"
				},';

        if (!empty($settings->$ReportPdf->settings->QM_QRCODE_REPORT)) {
            $output .='
				{
				title: "----"
				},
				{
				title: " ' . __('Print Label') . ' ",
				cmd: "printweldlabel",
				action :	function(event, ui) {
								window.open("reportnumbers/printweldlabel/" + ui.target.attr("rev"));
							},
				uiIcon: "qm_label",
				disabled: false
				},';
        }

        if (!empty($settings->$ReportPdf->settings->QM_QRCODE_REPORT)) {
            $output .= '
				{
				title: "----"
				},
				{
				title: " ' . __('Show QR-Code') . ' ",
				cmd: "showqrcode",
				action :	function(event, ui) {
							$("#dialog").load("reportnumbers/showqrcode/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_qr_code",
				disabled: false
				},
				';
        }

        $output .= '
				{
				title: "----"
				},
				{
				title: " ' . __('Gesamte Naht löschen') . ' ",
				cmd: "deleteevalution",
				action :	function(event, ui) {
								checkDuplicate = confirm("' . __('Soll die gesamte Naht gelöscht werden?') . '");
								if (checkDuplicate == false) {
									return false;
								}
								$("#container").load("reportnumbers/deleteevalution/" + ui.target.attr("rev"), {
									"ajax_true": 1
								})
							},
				uiIcon: "qm_delete"
				},

				],

			select: function(event, ui) {},
			});

		$("span.for_hasmenu2").contextmenu({
			delegate: ".hasmenu2",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: " ' . __('Diesen Nahtabschnitt bearbeiten') . ' ",
				cmd: "editevalution",
				action :	function(event, ui) {
							$("#container").load("reportnumbers/editevalution/" + ui.target.attr("rev"), {
									"ajax_true": 1
								})
							},
				uiIcon: "qm_edit"
				},
				{
				title: "----"
				},
				{
				title: " ' . __('Diesen Nahtabschnitt duplizieren') . ' ",
				cmd: "duplicatevalution",
				action :	function(event, ui) {
								checkDuplicate = confirm(" ' . __('Soll dieser Nahtabschnitt dupliziert werden?') . '");
								if (checkDuplicate == false) {
									return false;
								}
								$("#container").load("reportnumbers/duplicatevalution/" + ui.target.attr("rev"), {
									"ajax_true": 1
								})
							},
				uiIcon: "qm_duplicate"
				},
				{
				title: "----"
				},
				{
				title: " ' . __('Diesen Nahtabschnitt löschen') . ' ",
				cmd: "deleteevalution",
				action :	function(event, ui) {
								checkDuplicate = confirm("' . __('Soll dieser Nahtabschnitt gelöscht werden?') . '");
								if (checkDuplicate == false) {
									return false;
								}
								$("#container").load("reportnumbers/deleteevalution/" + ui.target.attr("rev"), {
									"ajax_true": 1
								})
							},
				uiIcon: "qm_delete"
				},

				],

			select: function(event, ui) {},
		});

			$(".TestingArea a.ajax").click(function() {
				$("#container").load($(this).attr("href"), {"ajax_true": 1});
				return false;
			});

			$("a.modal:not(.specialchars), div.images ul li a, button.modal").click(function() {

				$("#dialog").dialog(dialogOpts);

				$("#dialog").load($(this).attr("href"), {"ajax_true": 1});

				$("#dialog").dialog("open");
				return false;
			});

			$("div.checkbox input[type=checkbox]").checkboxradio();

		});
		</script>';
        return $output;
    }

    public function ViewEvaluationData($data, $setting, $lang)
    {
        $output  = null;
        $output .= '<div><table cellpadding = "0" cellspacing = "0">';
        $output .= '<tr>';

        $x = 0;

        foreach ($setting->children() as $_key => $_setting) {
            if (trim($_setting->showintable) == 1) {
                $colspan = $x == 0 ? ' colspan="2"' : null;
                $output .= '<th'.$colspan.'>'.$_setting->discription->$lang.'</th>';
                $x++;
            }
        }
        $output .= '</tr>';

        $x = $x + 1;
        $i = 0;

        foreach ($data as $dataArray) {

            $dataArray = $dataArray[key($dataArray)];

            if (count($dataArray['weld']) > 1) {
                $output .= '<tr>';
                $output .= '<td class="weldhead" colspan="'. ($x + 1) .'">';
                $output .=  $this->Html->link(
                    $dataArray['weld'][0]['description'],
                    array(
                                'action' => 'editevalution',
                                $this->request->projectvars['projectID'],
                                                                $this->request->projectvars['cascadeID'],
                                                                $this->request->projectvars['orderID'],
                                                                $this->request->projectvars['reportID'],
                                                                $this->request->projectvars['reportnumberID'],
                                $dataArray['weld'][0]['id'],
                                1
                                ),
                    array(
                                    'class' => 'round icon_edit ajax testingreportlink',
                                    )
                );

                if (Configure::read('DevelopmentsEnabled') == true) {
                    if (isset($dataArray['development'])) {
                        if ($dataArray['development'] == 0) {
                            $developmentClass = array('development_open',__('marked as not processed'));
                        }
                        if ($dataArray['development'] == 1) {
                            $developmentClass = array('development_rep',__('marked as error'));
                            $output .= $this->Html->link(__('add progress', true), array('controller' => 'developments', 'action' => 'progressadd',
                        $this->request->projectvars['VarsArray'][0],
                        $this->request->projectvars['VarsArray'][1],
                        $this->request->projectvars['VarsArray'][2],
                        $this->request->projectvars['VarsArray'][3],
                        $this->request->projectvars['VarsArray'][4],

                        $dataArray['id'],
                        '1'
                         ), array('class'=>'addlink modal','title' =>''));
                        }
                        if ($dataArray['development'] == 2) {
                            $developmentClass = array('development_ok',__('marked as processed'));
                        }

                        $output .= $this->Html->link(__('examination development', true), array('controller' => 'developments', 'action' => 'change',
                        $this->request->projectvars['VarsArray'][0],
                        $this->request->projectvars['VarsArray'][1],
                        $this->request->projectvars['VarsArray'][2],
                        $this->request->projectvars['VarsArray'][3],
                        $this->request->projectvars['VarsArray'][4],

                        $dataArray['id'],
                        '1'
                        ), array('class'=>'development_evalution icon modal '.$developmentClass[0], 'id'=>'this_development_'.$dataArray['id'],'title' => $developmentClass[1]));
                    }
                }


                $output .= '</td>';
                $output .= '</tr>';
            }

            foreach ($dataArray['weld'] as $weld) {
                $class = array();
                if ($i++ % 2 == 0) {
                    $class[] = 'altrow';
                }

                if (isset($weld['result']) && isset($setting->result->radiooption->value[(int)$weld['result']]) && strtolower($setting->result->radiooption->value[(int)$weld['result']]) == 'ne') {
                    $class[] = 'error';
                }
                //if(isset($setting->result->radiooption->(int)$weld['result']]) && $radiooptions[(int)$weld['result']]=='ne') $class[] = 'error';

                $output .= '<tr '.(!empty($class) ? ' class="'.join(' ', $class).'"' : null).'>';
                $colspan = count($dataArray['weld']) == 1 ? ' colspan="2"' : null;

                foreach ($setting->children() as $_key => $_setting) {
                    if (trim($_setting->showintable) == 1) {
                        if (trim($_setting->key) == 'description') {
                            if (count($dataArray['weld']) > 1) {
                                $output .= '<td></td><td>';
                            } else {
                                $output .= '<td'.$colspan.'>';
                            }
                        } else {
                            $output .= '<td>';
                        }

                        $output .= '<span class="discription_mobil">';
                        $output .= trim($_setting->discription->$lang) . ': ';
                        $output .= '</span>';

                        if (trim($_setting->fieldtype) == 'radio') {
                            $radiooptions = $this->radiodefault;

                            if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {
                                $radiooptions = array();

                                foreach ($_setting->radiooption->value as $_radiooptions) {
                                    array_push($radiooptions, trim($_radiooptions));
                                }
                            }

                            $output .= $radiooptions[(int)$weld[trim($_setting->key)]];
                        } else {
                            $value = null;
                            $value = utf8_decode($weld[trim($_setting->key)]);

                            if (trim($_setting->fieldtype) == 'checkbox') {
                                if ($weld[trim($_setting->key)] == 1 || $weld[trim($_setting->key)] == 'true') {
                                    $value = 'X';
                                } else {
                                    $value = '-';
                                }
                            }

                            if (trim($_setting->key) == 'description') {
                                //								$output .= ($weld['id'] . ' ' . $weld['reportnumber_id']);
                                $hasmenu = null;

                                $linkclass = 'contexmenu_weldposition';

                                $value  = utf8_decode($weld[trim($_setting->key)]);

                                if (count($dataArray['weld']) == 1) {
                                    if (Configure::read('DevelopmentsEnabled') == true) {
                                        if (isset($dataArray['development'])) {
                                            if ($dataArray['development'] == 0) {
                                                $developmentClass = array('development_open',__('marked as not processed'));
                                            }
                                            if ($dataArray['development'] == 1) {
                                                $developmentClass = array('development_rep',__('marked as error'));
                                            }
                                            if ($dataArray['development'] == 2) {
                                                $developmentClass = array('development_ok',__('marked as processed'));
                                            }

                                            $output .= $this->Html->link(__('examination development', true), array('controller' => 'developments', 'action' => 'change',
                                            $this->request->projectvars['VarsArray'][0],
                                            $this->request->projectvars['VarsArray'][1],
                                            $this->request->projectvars['VarsArray'][2],
                                            $this->request->projectvars['VarsArray'][3],
                                            $this->request->projectvars['VarsArray'][4],

                                            $dataArray['id'],
                                            '1'
                                            ), array('class'=>'icon mymodal '.$developmentClass[0], 'id'=>'development_evalution','title' => $developmentClass[1]));
                                        }
                                    }

                                    $linkclass = 'hasmenu1';
                                }

                                $output .= '<span class="'.$linkclass.'">';

                                if (count($dataArray['weld']) > 1) {
                                    $value .=  isset($setting->children()->position->key) ? '/' . $weld[trim($setting->children()->position->key)] : ' ';
                                }

                                $output .=  $this->Html->link(
                                    $value,
                                    array(
                                                    'action' => 'editevalution',
                                                    $this->request->projectvars['projectID'],
                                                                                                        $this->request->projectvars['cascadeID'],
                                                                                                        $this->request->projectvars['orderID'],
                                                                                                        $this->request->projectvars['reportID'],
                                                                                                        $this->request->projectvars['reportnumberID'],
//													$this->request->data['Reportnumber']['topproject_id'],
//													$this->request->data['Reportnumber']['testingmethod_id'],
                                                    $weld['id']
                                                    ),
                                    array(
                                                        'class' => 'round icon_edit ajax testingreportlink',
                                                        )
                                );

                                $output .= '</span>';
                            } else {
                                $output .= $value;
                            }
                        }

                        $output .= '</td>';
                    }
                }

                $output .= '</tr>';
            }
        }

        $output .= '</table><div class="clear"></div></div>';
        return $output;
    }

    public function ViewEvaluation($data, $setting, $lang)
    {
        $ReportEvaluation = $this->request->tablenames[2];
        $ReportPdf  = 'Report' . $this->request->verfahren . 'Pdf';
        $output  = null;

        $output .= '<table cellpadding = "0" cellspacing = "0" class="view_evalution">';
        $output .= '<thead>';

        $x = 0;

        $evalHeaders = '';
        $evalDiscription = array();

        foreach ($setting as $_setting) {
            if (trim($_setting->showintable) == 1) {
                $evalHeaders .= '<th>'.$_setting->discription->$lang.($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null).'</th>';

                if (trim($_setting->key) == 'description') {
                    $evalHeaders .= '<th class="no_padding"></th>';
                    $x--;
                }

                $evalDiscription[] = $_setting->discription->$lang.($this->request->action == 'edit' && isset($_setting->validate->notempty) ? '&nbsp;*' : null);
                $x++;
            }
        }

        $x++;

        if ($this->_View->viewVars['replaceHeaderSetting'] == '1' || $this->_View->viewVars['replaceHeaderSetting'] == 'true') {
            $output .= '<tr><th colspan="'.$x.'" class="hint">'.__('Attention: Entries in first row will be used for titles.').'</th></tr>';
        }

        $output .= '<tr>';
        $output .= $evalHeaders;
        $output .= '</tr>';
        $output .= '</thead>';
        $i = 0;

        foreach ($data as $dataArray) {
            $comp_weld = '1';

            $output .= '<tbody class="weld weld_'.$dataArray[$ReportEvaluation]['discription'].'">';

            foreach ($dataArray[$ReportEvaluation]['weld'] as $weld) {
                $class = null;
                if ($i++ % 2 == 0) {
                    $class = ' class="altrow"';
                }
                if (isset($this->request->data['has_result']) && $weld['result'] == 2) {
                    $class = ' class="error"';
                }

                $output .= '<tr rel="'.$weld['id'].'" '.$class.'>';

                $xx = 0;
                foreach ($setting as $_key => $_setting) {
                    if (trim($_setting->showintable) == 1) {
                        $output .= '<td class="col_'.$_setting->key.'">';
                        $output .= '<span class="discription_mobil">';
                        $output .= $evalDiscription[$xx] . ': ';
                        $output .= '</span>';
                        $output .= '<span>';

                        if (trim($_setting->fieldtype) == 'radio') {
                            $radiooptions = $this->radiodefault;


                            if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {
                                $radiooptions = array();

                                foreach ($_setting->radiooption->value as $_radiooptions) {
                                    array_push($radiooptions, trim($_radiooptions));
                                }
                            }

                            //							if($replaceResult) {
                            //								$output .= join(', ', $errors);
                            //							} else {
                            $output .= $radiooptions[(int)$weld[trim($_setting->key)]];
                        //							}
                        } elseif (trim($_setting->fieldtype) == 'checkbox') {
                            if ($weld[trim($_setting->key)] == 1) {
                                $output .= 'X';
                            }
                        } else {
                            //							$output .= utf8_decode($weld[trim($_setting->key)]);
                            $output .= ($weld[trim($_setting->key)]);
                        }

                        $output .= '</span></td>';


                        if ($xx == 0) {
                            $output .= '<td class="no_padding">';


                            if (count($dataArray[$ReportEvaluation]['weld']) == 1) {
                                if (Configure::read('DevelopmentsEnabled') == true) {
                                    if (isset($dataArray['development'])) {
                                        if ($dataArray['development'] == 0) {
                                            $developmentClass = array('development_open',__('marked as not processed'));
                                        }
                                        if ($dataArray['development'] == 1) {
                                            $developmentClass = array('development_rep',__('marked as error'));
                                        }
                                        if ($dataArray['development'] == 2) {
                                            $developmentClass = array('development_ok',__('marked as processed'));
                                        }

                                        $output .= $this->Html->link(__('examination development', true), array('controller' => 'developments', 'action' => 'change',
                                                $this->request->projectvars['VarsArray'][0],
                                                $this->request->projectvars['VarsArray'][1],
                                                $this->request->projectvars['VarsArray'][2],
                                                $this->request->projectvars['VarsArray'][3],
                                                $this->request->projectvars['VarsArray'][4],

                                                $dataArray['id'],
                                                '1'
                                            ), array('class'=>'icon modal '.$developmentClass[0], 'id'=>'development_evalution','title' => $developmentClass[1]));
                                    }
                                }
                            }



                            $output .= '</td>';
                        }
                        $xx++;
                    }
                }
                $output .= '</tr>';
            }
            $output .= '</tbody>';
        }

        $output .= '</table>';



        return $output;
    }

    public function ShowDlData($arrayData, $locale, $model)
    {
        if (is_object($arrayData['settings']->$model)) {
            foreach ($arrayData['settings']->$model->children() as $_key => $_xml) {
                if (trim($_xml->output->screen) != 1) {
                    continue;
                }
                if ($_xml->key == 'id') {
                    continue;
                }

                $value = $this->request->data[trim($_xml->model)][trim($_xml->key)];

                if ($_xml->fieldtype == 'radio' && $_xml->radiooption) {
                    $value = trim($_xml->radiooption->value->$value);
                }

                if (is_object($_xml->select->model) &&  trim($_xml->select->model) == 1) {
                    if (!empty($this->request->data['Dropdowns'][trim($_xml->model)][trim($_xml->key)][$value])) {
                        $value = $this->request->data['Dropdowns'][trim($_xml->model)][trim($_xml->key)][$value];
                    }
                }

                if (!$value) {
                    $value = '-';
                }

                echo '<dl>';
                echo trim($_xml->discription->$locale);
                echo ': <strong>';
                echo $value;
                echo '</strong></dl>';
            }
        }
    }

    public function ShowDataList($arrayData, $locale, $model, $tag, $data = null)
    {
        if (is_object($arrayData['settings']->$model)) {
            if ($data == null) {
                $data = $this->request->data;
            }

            foreach ($arrayData['settings']->$model->children() as $_key => $_xml) {
                if (trim($_xml->output->screen) != 1) {
                    continue;
                }
                if ($_xml->key == 'id') {
                    continue;
                }

                $value = $data[trim($_xml->model)][trim($_xml->key)];

                if ($_xml->fieldtype == 'radio' && $_xml->radiooption) {
                    $value = trim($_xml->radiooption->value->$value);
                }

                if (is_object($_xml->select->model) &&  trim($_xml->select->model) == 1) {
                    if (!empty($data['Dropdowns'][trim($_xml->model)][trim($_xml->key)][$value])) {
                        $value = $data['Dropdowns'][trim($_xml->model)][trim($_xml->key)][$value];
                    }
                }

                if (!$value) {
                    $value = '-';
                }

                switch ($tag) {
                    case 'dl':

                        echo '<dl>';
                        echo trim($_xml->discription->$locale);
                        echo ': <strong>';
                        echo $value;
                        echo '</strong></dl>';

                    break;

                    case 'p':

                        echo '<p class="output">';
                        echo $value;
                        echo '<span class="title">';
                        echo trim($_xml->discription->$locale);
                        echo '</span>';
                        echo '</p>';

                    break;

                    default:
                        echo '<dl>';
                        echo trim($_xml->discription->$locale);
                        echo ': <strong>';
                        echo $value;
                        echo '</strong></dl>';
                }
            }
        }
    }

    public function EditModulData($data, $setting, $lang, $testingmethods, $step='Order')
    {

        // wenn keine Daten vorhanden sind
        if ($setting == null) {
            return false;
        }

        // Falls ein PDF eingelesen wurde, die Daten hier bereitstellen
        if (isset($this->_View->viewVars['texts'])) {
            $texts = $this->_View->viewVars['texts'];
        }

        $fieldset_count = 0;
        $x = 0;
        $output  = null;
        $output .= '<fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';
        $output .= $this->Form->input('id');

        if (is_array($testingmethods) && isset($testingmethods['testingcomp_id']) && isset($testingcomps)) {
            $output .= $this->Form->input(
                'testingcomp_id',
                array(
                'options' => $testingcomps['testingcomp_id']
                )
            );
        }

        if (is_array($testingmethods) && isset($testingmethods['Testingmethod'])) {
            $output .= $this->Form->input(
                $step.'Testingmethod',
                array(
                'label' => __('choose category', true),
                                'empty' => ' ',
                'multiple' => false,
                'options' => $testingmethods['Testingmethod'],
                                'selected' => $this->request->projectvars['VarsArray'][15]
                )
            );
        }

        foreach ($setting as $_key => $_setting) {
            if (trim($_setting->output->screen) != 1) {
                continue;
            }

            $x++;
            $model = trim($_setting->model);
            if (isset($texts) && !empty($texts)) {
                $source = array('x'=>array(), 'y'=>array(), 'previous'=>null, 'next'=>null);

                if (isset($_setting->source->x)) {
                    $source['x'] = array_filter(explode(' ', trim($_setting->source->x)));
                    if (count($source['x']) == 1) {
                        $source['x'][1] = $source['x'][0];
                    }
                    sort($source['x']);
                }
                if (isset($_setting->source->y)) {
                    $source['y'] = array_filter(explode(' ', trim($_setting->source->y)));
                    if (count($source['y']) == 1) {
                        $source['y'][1] = $source['y'][0];
                    }
                    $source['y'] = array_map(function ($elem) use ($texts) {
                        return $elem < 0 ? $elem+count($texts) : $elem;
                    }, $source['y']);
                    sort($source['y']);
                }
                if (isset($_setting->source->previous)) {
                    $source['previous'] = array_filter(explode(' ', trim($_setting->source->previous)));
                }
                if (isset($_setting->source->next)) {
                    $source['next'] = array_filter(explode(' ', trim($_setting->source->next)));
                }
            }
            $discription = null;
            $input = null;

            if ($_setting->fieldset != '' && $_setting->fieldset == 1) {
                $fieldset_count++;
                $input = '</fieldset><fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';
                $output .= $input;
            }

            if ($_setting->legend->$lang != '' && $_setting->legend->$lang != '') {
                $input = '<legend class="headline">'.$_setting->legend->$lang.'</legend>';
                $output .= $input;
            }

            $attribut_array = array();
            if (isset($_setting->autocomplete)) {
              $attribut_array ['autocomplete'] = 'autocomplete';
              $attribut_array ['autocompletemodel'] = $_setting->autocomplete->model;
              $attribut_array ['autocompletefield'] = $_setting->autocomplete->field;

            }
            // Die Werte aus der Datenbank in das Formularfeld eintragen
            //			$attribut_array['value'] = $data[$model][trim($_setting->key)];
            //			$attribut_array['title'] = __('Double click to insert specialchars', true);

            // Leerzeichen im Labeltag müssen gegen geschützte Leerzeichen getauscht werden
            $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);

            if ($discription != null) {
                $attribut_array['label'] = $discription;
            }

            // falls ein spezielles Format für das Inputfeld angegeben ist
            if (trim($_setting->fieldtype) > '0') {
                $attribut_array['type'] = trim($_setting->fieldtype);

                if (trim($_setting->fieldtype) == 'radio') {
                    $radiooptions = $this->radiodefault;

                    if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {
                        $radiooptions = array();

                        foreach ($_setting->radiooption->value as $_radiooptions) {
                            array_push($radiooptions, trim($_radiooptions));
                        }
                    }
                    $attribut_array['legend'] = $discription;
                    $attribut_array['options'] = $radiooptions;
                }
            }

            // bei einer speziellen Datenformatierung
            if (trim($_setting->format) > '0') {
                $attribut_array['class'] = trim($_setting->format);
            }

            //			pr(trim($_setting->key));
            // Falls Daten aus einem eingelesenen PDF vorhanden sind, testen, ob ein Wert übernommen werden soll
            if (!empty($texts)) {
                $txtLines = $texts;
                $attribut_array['value'] = '';
                // Wenn Y-Werte angegeben sind, dann nur in diesen Zeilen suchen
                if (!empty($source['y'])) {
                    $txtLines = array_intersect_key($texts, array_flip(array_filter(array_keys($texts), function ($line) use ($source) {
                        return $source['y'][0] <= $line && $line <= $source['y'][1];
                    })));
                }

                if (!empty($source['x'])) {
                    if (!empty($txtLines)) {
                        foreach ($txtLines as $txtLine) {
                            foreach ($txtLine as $txtPos=>$txtValue) {
                                if ($source['x'][0] <= $txtPos && $txtPos <= $source['x'][1]) {
                                    $attribut_array['value'] = trim($attribut_array['value'].' '.$txtValue);
                                }
                            }
                        }
                    }
                } elseif (!empty($source['previous']) || !empty($source['next'])) {
                    $insert = false;
                    if (!empty($txtLines)) {
                        foreach ($txtLines as $txtLine) {
                            foreach ($txtLine as $txtValue) {
                                if (!empty($source['next'])) {
                                    foreach ($source['next'] as $srcNext) {
                                        if (strpos($txtValue, $srcNext) !== false) {
                                            if ($insert) {
                                                $insert = false;
                                                break;
                                            }

                                            if (empty($source['prev'])) {
                                                break 3;
                                            }
                                        }
                                    }
                                }

                                if ($insert || empty($source['previous'])) {
                                    $attribut_array['value'] = trim($attribut_array['value'].' '.$txtValue);
                                }
                                // Wenn Vorheriger Wert nicht angegeben ist, dann alles bis zum next-Treffer mitnehmen
                                if (empty($source['previous'])) {
                                    $insert = true;
                                }
                                // Ansonsten einen der angegebenen Begrenzer suchen
                                else {
                                    foreach ($source['previous'] as $srcPrev) {
                                        if (strpos($txtValue, $srcPrev) !== false) {
                                            $insert = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif (!empty($source['y'])) {
                    $attribut_array['value'] = trim(join(PHP_EOL, array_map(function ($line) {
                        return join(' ', array_map('trim', $line));
                    }, $txtLines)));
                }
            }

            // Formularfeld plazieren
            if (trim($_setting->select->model == '')) {
                $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
            }

            // Wenn ein Dropdownfeld anliegt
            if (trim($_setting->select->model) != '') {
                $VarsArray = $this->_View->viewVars['VarsArray'];

                $VarsArray[6] = isset($this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['Dropdown']['id'])
                ? $this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['Dropdown']['id']
                : (
                    isset($this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'])
                        ? $this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id']
                        : 0
                );
                $VarsArray[7] = $x;
                /*
                if(isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) && count($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) > 0){
                $options = Hash::combine($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)], '{n}.DropdownsValue.id', '{n}.DropdownsValue.discription');
                }
                */

                if (isset($data['Dropdowns'][$step][trim($_setting->key)]) && count($data['Dropdowns'][$step][trim($_setting->key)]) > 0) {
                    $options = array();
                    if (!empty($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)])) {
//                      mit diesem Code bleibt das OptionArray leer, Phillip, deshalb nochmal die if-Abfrage
                        $options = Hash::combine($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)], '{n}.DropdownsValue.id', '{n}.DropdownsValue.discription');

                        if (isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]['DropdownsValue'])) {
                            $options = Hash::combine($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]['DropdownsValue'], '{n}.DropdownsValue.id', '{n}.DropdownsValue.discription');
                        }
                    }
                    /*
                                        $options = $data['Dropdowns'][$step][trim($_setting->key)];

                                        if(isset($this->request->data[$step]) && isset($this->request->data[$step][trim($_setting->key)])){
                                            if(array_search($this->request->data[$step][trim($_setting->key)], $options) === false) {
                                                $options += array($this->request->data[$step][trim($_setting->key)]=>$this->request->data[$step][trim($_setting->key)]);
                                            }
                                        }
                    */
                    $thisselected = null;

                    // Selected suchen
                    if (isset($data[$model][trim($_setting->key)])) {
                        foreach ($options as $__key => $__options) {
                            if ($__options == $data[$model][trim($_setting->key)]) {
                                $thisselected = $__key;
                                break;
                            }
                        }
                    }

                    if (isset($_setting->dependencies->child)) {
                        $children = (array)($_setting->dependencies->children());
                        $locale = $this->_View->viewVars['locale'];
                        $dependencies = array_map(function ($key) use ($setting, $locale) {
                            $key = array_filter($setting, function ($__setting) use ($key) {
                                return trim($__setting->key) == $key;
                            });

                            return trim(reset($key)->discription->$locale);
                        }, (array)$children['child']);
                    }

                    if (empty($options)) {
                        $attribut_array['after'] = $this->Html->link(
                            __('Edit'),
                            array_merge(
                                array(
                                    'controller' => 'dropdowns',
                                    'action' => 'dropdownindex'
                                ),
                                $VarsArray
                            ),
                            array_merge(
                                array(
                                    'class'=>'mymodal dropdown',
                                    'disabled' => isset($this->request->data[$step]['status'])&& $this->request->data[$step]['status'] != 0,
                                    'title'=> __('Edit dropdown', true)
                                ),
                                Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                            )
                        );

                        $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                    } else {
                        //						$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),

                        $input = $this->Form->input(
                            trim($_setting->model).'.'.trim($_setting->key),
                            array(
                                    'label' => $attribut_array['label'],
                                    'options' => $options,
                                    'selected' => $thisselected,
                                    'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                                    'empty' => ' ',
                                    'after' => $this->Html->link(
                                        __('Edit'),
                                        array_merge(
                                            array(
                                                    'controller' => 'dropdowns',
                                                    'action' => 'dropdownindex'
                                                ),
                                            $VarsArray
                                        ),
                                        array_merge(
                                            array(
                                                            'class'=>'mymodal dropdown',
                                                            'disabled' => isset($this->request->data[$step]['status'])&& $this->request->data[$step]['status'] != 0,
                                                            'title'=> __('Edit dropdown', true)
                                                    ),
                                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                        )
                                    ).(
                                        !isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0
                                                    ? null
                                                    : $this->Html->link(
                                                        __('Edit dependent fields'),
                                                        array_merge(
                                                            array(
                                                                    'controller' => 'dependencies',
                                                                    'action' => 'index'
                                                                ),
                                                            $VarsArray
                                                        ),
                                                        array_merge(
                                                            array(
                                                                            'class'=>'mymodal dependency',
                                                                            'disabled' => isset($this->_View->viewVars['reportnumber']['Reportnumber']['status']) && $this->_View->viewVars['reportnumber']['Reportnumber']['status'] != 0,
                                                                            'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
                                                                    ),
                                                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                                        )
                                                    )
                                    )
                            )
                        );
                    }
                } else {
                    $options = array();
                    //if($this->request->data)
                    if (isset($this->request->data[$step][trim($_setting->key)])) {
                        $attribut_array['value'] = $value = preg_split('/[,|\r\n]+/', $this->request->data[$step][trim($_setting->key)]);
                        foreach ($value as $_value) {
                            if (array_search(trim($_value), $options) === false) {
                                $options[trim($_value)] = trim($_value);
                            }
                        }
                    }

                    if (!empty($options)) {
                        $attribut_array['options'] = $options;
                        $attribut_array['empty'] = ' ';
                        $attribut_array['type'] = 'text';
                        $attribut_array['after'] = $this->Html->link(
                            __('Edit'),
                            array_merge(
                                array(
                                    'controller' => 'dropdowns',
                                    'action' => 'dropdownindex'
                                ),
                                $VarsArray
                            ),
                            array_merge(
                                array(
                                            'class'=>'mymodal dropdown',
                                            'disabled' => isset($this->request->data[$step]['status'])&& $this->request->data[$step]['status'] != 0,
                                            'title'=> __('Edit dropdown', true)
                                    ),
                                Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                            )
                        );
                    }

                    $attribut_array['after'] = $this->Html->link(
                        __('Edit'),
                        array_merge(
                            array(
                                    'controller' => 'dropdowns',
                                    'action' => 'dropdownindex'
                                ),
                            $this->request->projectvars['VarsArray']
                        ),
                        array_merge(
                            array(
                                            'class'=>'mymodal dropdown',
                                            'disabled' => isset($this->_View->viewVars['reportnumber']['Reportnumber']['status']) && $this->_View->viewVars['reportnumber']['Reportnumber']['status']!= 0,
                                            'title'=> __('Add dropdown', true)
                                    ),
                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                        )
                    );
                    ///					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),$attribut_array);
                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                }
            }

            $output .= $input;
            if (isset($_setting->defaultval)&& $_setting->fieldtype == 'radio') {
                $radiodefaultval [trim($_setting->model). ucfirst(trim($_setting->key))] = trim($_setting->defaultval);
            }
        }

        $output .= '<input id="Ordertopproject_id" type="hidden" name="data[Order][topproject_id]" value="'.$this->request->projectID.'">';
        $output .= '</fieldset>';
        $output .= '<script>
					$(function() {

					var OutputHeight = 0;
					var OutputWidth = 0;
					var OutputDateWidth = 0;
					var OutputHeightBS = 0;

					$("#dialog div.textarea").width($("form.dialogform").width() - 30);
					$("#dialog div.textarea textarea").css("height","2em");
					$("#dialog div.textarea textarea").css("width","99%");

					// beim Datumsformat muss noch die Dynamik eingebaut werden
          /*
					$(".time").datetimepicker({ format: "H:i", datepicker: false, scrollInput: false});
					$(".date").datetimepicker({ format: "Y-m-d", timepicker:false, lang:"de", scrollInput: false});
					$(".datetime").datetimepicker({
						lang:"de",
						format: "Y-m-d H:i",
						scrollInput: false
					});
          */
					$("form fieldset.fieldset'.trim($setting[0]->model).'").each(function(index, value){

						$("#" + $(this).attr("id") + " div.input").each(function(){
							if($(this).height() > OutputHeight) {
								OutputHeight = $(this).height();
							}

							if($(this).width() > OutputWidth) {
								OutputWidth = $(this).width();
							}

						});

						OutputWidth = OutputWidth + 10;
						$("#" + $(this).attr("id") + " div.input").css("height",OutputHeight+"px");

						OutputHeight = 0;
						OutputWidth = 0;

					});
				});
				</script>';

        return $output;
    }

    public function EditOrderData($data, $setting, $lang, $step='Order')
    {
        // wenn keine Daten vorhanden sind
        if ($setting == null) {
            return false;
        }

        // Falls ein PDF eingelesen wurde, die Daten hier bereitstellen
        if (isset($this->_View->viewVars['texts'])) {
            $texts = $this->_View->viewVars['texts'];
        }

        $fieldset_count = 0;
        $x = 0;

        $output  = null;
        $output .= '<fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';

        foreach ($setting as $_key => $_setting) {
            //pr($_setting);
            if (trim($_setting->output->screen) != 1) {
                continue;
            }

            $x++;
            $model = trim($_setting->model);
            if (isset($texts) && !empty($texts)) {
                $source = array('x'=>array(), 'y'=>array(), 'previous'=>null, 'next'=>null);

                if (isset($_setting->source->x)) {
                    $source['x'] = array_filter(explode(' ', trim($_setting->source->x)));
                    if (count($source['x']) == 1) {
                        $source['x'][1] = $source['x'][0];
                    }
                    sort($source['x']);
                }
                if (isset($_setting->source->y)) {
                    $source['y'] = array_filter(explode(' ', trim($_setting->source->y)));
                    if (count($source['y']) == 1) {
                        $source['y'][1] = $source['y'][0];
                    }
                    $source['y'] = array_map(function ($elem) use ($texts) {
                        return $elem < 0 ? $elem+count($texts) : $elem;
                    }, $source['y']);
                    sort($source['y']);
                }
                if (isset($_setting->source->previous)) {
                    $source['previous'] = array_filter(explode(' ', trim($_setting->source->previous)));
                }
                if (isset($_setting->source->next)) {
                    $source['next'] = array_filter(explode(' ', trim($_setting->source->next)));
                }
            }
            $discription = null;
            $input = null;

            if ($_setting->fieldset != '' && $_setting->fieldset == 1) {
                $fieldset_count++;
                $input = '</fieldset><fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';
                $output .= $input;
            }

            if ($_setting->legend->$lang != '' && $_setting->legend->$lang != '') {
                $input = '<legend class="headline">'.$_setting->legend->$lang.'</legend>';
                $output .= $input;
            }

            $attribut_array = array();

            // Die Werte aus der Datenbank in das Formularfeld eintragen
            //			$attribut_array['value'] = $data[$model][trim($_setting->key)];
            //			$attribut_array['title'] = __('Double click to insert specialchars', true);

            // Leerzeichen im Labeltag müssen gegen geschützte Leerzeichen getauscht werden
            $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);

            if ($discription != null) {
                $attribut_array['label'] = $discription;
            }

            // falls ein spezielles Format für das Inputfeld angegeben ist
            if (trim($_setting->fieldtype) > '0') {
                $attribut_array['type'] = trim($_setting->fieldtype);

                if (trim($_setting->fieldtype) == 'radio') {
                    $radiooptions = $this->radiodefault;

                    if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {
                        $radiooptions = array();

                        foreach ($_setting->radiooption->value as $_radiooptions) {
                            array_push($radiooptions, trim($_radiooptions));
                        }
                    }
                    $attribut_array['legend'] = $discription;
                    $attribut_array['options'] = $radiooptions;
                }
            }

            // bei einer speziellen Datenformatierung
            if (trim($_setting->format) > '0') {
                $attribut_array['class'] = trim($_setting->format);
            }

            //			pr(trim($_setting->key));
            // Falls Daten aus einem eingelesenen PDF vorhanden sind, testen, ob ein Wert übernommen werden soll
            if (!empty($texts)) {
                $txtLines = $texts;
                $attribut_array['value'] = '';
                // Wenn Y-Werte angegeben sind, dann nur in diesen Zeilen suchen
                if (!empty($source['y'])) {
                    $txtLines = array_intersect_key($texts, array_flip(array_filter(array_keys($texts), function ($line) use ($source) {
                        return $source['y'][0] <= $line && $line <= $source['y'][1];
                    })));
                }

                if (!empty($source['x'])) {
                    if (!empty($txtLines)) {
                        foreach ($txtLines as $txtLine) {
                            foreach ($txtLine as $txtPos=>$txtValue) {
                                if ($source['x'][0] <= $txtPos && $txtPos <= $source['x'][1]) {
                                    $attribut_array['value'] = trim($attribut_array['value'].' '.$txtValue);
                                }
                            }
                        }
                    }
                } elseif (!empty($source['previous']) || !empty($source['next'])) {
                    $insert = false;
                    if (!empty($txtLines)) {
                        foreach ($txtLines as $txtLine) {
                            foreach ($txtLine as $txtValue) {
                                if (!empty($source['next'])) {
                                    foreach ($source['next'] as $srcNext) {
                                        if (strpos($txtValue, $srcNext) !== false) {
                                            if ($insert) {
                                                $insert = false;
                                                break;
                                            }

                                            if (empty($source['prev'])) {
                                                break 3;
                                            }
                                        }
                                    }
                                }

                                if ($insert || empty($source['previous'])) {
                                    $attribut_array['value'] = trim($attribut_array['value'].' '.$txtValue);
                                }
                                // Wenn Vorheriger Wert nicht angegeben ist, dann alles bis zum next-Treffer mitnehmen
                                if (empty($source['previous'])) {
                                    $insert = true;
                                }
                                // Ansonsten einen der angegebenen Begrenzer suchen
                                else {
                                    foreach ($source['previous'] as $srcPrev) {
                                        if (strpos($txtValue, $srcPrev) !== false) {
                                            $insert = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif (!empty($source['y'])) {
                    $attribut_array['value'] = trim(join(PHP_EOL, array_map(function ($line) {
                        return join(' ', array_map('trim', $line));
                    }, $txtLines)));
                }
            }

            // Formularfeld plazieren
            if (trim($_setting->select->model == '')) {
                $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
            }

            // Wenn ein Dropdownfeld anliegt
            if (trim($_setting->select->model) != '') {
                $VarsArray = $this->_View->viewVars['VarsArray'];
                $VarsArray[6] = isset($this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['Dropdown']['id'])
                ? $this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['Dropdown']['id']
                : (
                    isset($this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'])
                        ? $this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id']
                        : 0
                );
                $VarsArray[7] = $x;

                //				pr($data['DropdownInfo']);
                if (isset($data['Dropdowns'][$step][trim($_setting->key)]) && count($data['Dropdowns'][$step][trim($_setting->key)]) > 0) {
                    $options = $data['Dropdowns'][$step][trim($_setting->key)];
                    if (isset($this->request->data[$step][trim($_setting->key)])) {
                        if (array_search($this->request->data[$step][trim($_setting->key)], $options) === false) {
                            $options += array($this->request->data[$step][trim($_setting->key)]=>$this->request->data[$step][trim($_setting->key)]);
                        }
                    }
                    $thisselected = null;

                    // Selected suchen
                    if (isset($data[$model][trim($_setting->key)])) {
                        foreach ($options as $__key => $__options) {
                            if ($__options == $data[$model][trim($_setting->key)]) {
                                $thisselected = $__key;
                                break;
                            }
                        }
                    }

                    if (isset($_setting->dependencies->child)) {
                        $children = (array)($_setting->dependencies->children());
                        $locale = $this->_View->viewVars['locale'];
                        $dependencies = array_map(function ($key) use ($setting, $locale) {
                            $key = array_filter($setting, function ($__setting) use ($key) {
                                return trim($__setting->key) == $key;
                            });

                            return trim(reset($key)->discription->$locale);
                        }, (array)$children['child']);
                    }

                    if (empty($options)) {
                        $attribut_array['after'] = $this->Html->link(
                            __('Edit'),
                            array_merge(
                                array(
                                    'controller' => 'dropdowns',
                                    'action' => 'dropdownindex'
                                ),
                                $VarsArray
                            ),
                            array_merge(
                                array(
                                    'class'=>'mymodal dropdown',
                                    'disabled' => isset($this->request->data[$step]['status']) && $this->request->data[$step]['status'] != 0,
                                    'title'=> __('Edit dropdown', true)
                                ),
                                Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                            )
                        );

                        $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                    } else {
                        $input = $this->Form->input(
                            trim($_setting->model).'.'.trim($_setting->key),
                            array(
                                    'label' => $attribut_array['label'],
                                    'options' => $options,
                                    'selected' => $thisselected,
                                    'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                                    'empty' => ' ',
                                    'after' => $this->Html->link(
                                        __('Edit'),
                                        array_merge(
                                            array(
                                                    'controller' => 'dropdowns',
                                                    'action' => 'dropdownindex'
                                                ),
                                            $VarsArray
                                        ),
                                        array_merge(
                                            array(
                                                            'class'=>'mymodal dropdown',
                                                            'disabled' => isset($this->request->data[$step]['status']) && $this->request->data[$step]['status'] != 0,
                                                            'title'=> __('Edit dropdown', true)
                                                    ),
                                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                        )
                                    ).(
                                        !isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0
                                                    ? null
                                                    : $this->Html->link(
                                                        __('Edit dependent fields'),
                                                        array_merge(
                                                            array(
                                                                    'controller' => 'dependencies',
                                                                    'action' => 'index'
                                                                ),
                                                            $VarsArray
                                                        ),
                                                        array_merge(
                                                            array(
                                                                            'class'=>'mymodal dependency',
                                                                            'disabled' => isset($this->_View->viewVars['reportnumber']['Reportnumber']['status']) && $this->_View->viewVars['reportnumber']['Reportnumber']['status'] != 0,
                                                                            'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
                                                                    ),
                                                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                                        )
                                                    )
                                    )
                            )
                        );
                    }
                } else {
                    $options = array();
                    //if($this->request->data)
                    if (isset($this->request->data[$step][trim($_setting->key)])) {
                        $attribut_array['value'] = $value = preg_split('/[,|\r\n]+/', $this->request->data[$step][trim($_setting->key)]);
                        foreach ($value as $_value) {
                            if (array_search(trim($_value), $options) === false) {
                                $options[trim($_value)] = trim($_value);
                            }
                        }
                    }

                    if (!empty($options)) {
                        $attribut_array['options'] = $options;
                        $attribut_array['empty'] = ' ';
                        $attribut_array['type'] = 'text';
                        $attribut_array['after'] = $this->Html->link(
                            __('Edit'),
                            array_merge(
                                array(
                                    'controller' => 'dropdowns',
                                    'action' => 'dropdownindex'
                                ),
                                $VarsArray
                            ),
                            array_merge(
                                array(
                                            'class'=>'mymodal dropdown',
                                            'disabled' => isset($this->request->data[$step]['status']) && $this->request->data[$step]['status'] != 0,
                                            'title'=> __('Edit dropdown', true)
                                    ),
                                Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                            )
                        );
                    }
                    $attribut_array['after'] = $this->Html->link(
                        __('Edit'),
                        array_merge(
                            array(
                                    'controller' => 'dropdowns',
                                    'action' => 'dropdownindex'
                                ),
                            $this->request->projectvars['VarsArray']
                        ),
                        array_merge(
                            array(
                                            'class'=>'mymodal dropdown',
                                            'disabled' => isset($this->_View->viewVars['reportnumber']['Reportnumber']['status']) && $this->_View->viewVars['reportnumber']['Reportnumber']['status']!= 0,
                                            'title'=> __('Add dropdown', true)
                                    ),
                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                        )
                    );
                    ///					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),$attribut_array);
                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                }
            }

            if (isset($_setting->multiselect) && (trim($_setting->fieldtype) == 'text' || trim($_setting->fieldtype) == '')) {
                if (isset($data['Multiselects'][trim($_setting->model)][trim($_setting->key)])) {
                    $options = array();
                    if (!isset($options) || empty($options)) {
                        pr('test');
                        $options = $data['Multiselects'][trim($_setting->model)][trim($_setting->key)];
                        if (is_array($options)) {
                            $options = array_combine(array_values($options), array_values($options));
                        }
                    } else {
                        //$options = Hash::expand(array_merge(Hash::flatten(array(__('Dynamic values', true)=>$options),'$'), Hash::flatten($data['Multiselects'][trim($_setting->model)][trim($_setting->key)],'$')), '$');
                        $options = array();
                    }

                    foreach (preg_split('/[,|\r\n]+/', $data[trim($_setting->model)][trim($_setting->key)]) as $value) {
                        $options[trim($value)] = trim($value);
                    }

                    $options = array_unique($options);
                    //uksort($options, function($a, $b) { return (empty($a) || $a < $b ? -1 : (empty($b) || $a > $b ? 1 : 0)); });
                    ksort($options);
                }

                if (isset($_setting->dependencies->child)) {
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
                }

                $addLink = null;
                if (trim($_setting->select->model != '')) {
                    $addLink = $this->Html->link(__('Edit'), array(
                        'controller' => 'dropdowns',
                        'action' => 'dropdownindex',

                        $this->request->projectvars['VarsArray'][0],
                                            $this->request->projectvars['VarsArray'][1],
                                            $this->request->projectvars['VarsArray'][2],
                                            $this->request->projectvars['VarsArray'][3],
                                            $this->request->projectvars['VarsArray'][4],
                                            $this->request->projectvars['VarsArray'][5],
                                            $this->request->projectvars['VarsArray'][6],

                        isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0])
                        ? $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id']
                        : 0,
                        $x,
                        $data['Reportnumber']['id'],
                    ), array_merge(
                        array(
                            'class'=>'modal dropdown',
                            'disabled' => $disabled,
                            'title'=> __('Edit dropdown', true)
                        ),
                        Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                    )).
                            (
                                !isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0
                                ? null
                                : $this->Html->link(
                                    __('Edit dependent fields'),
                                    array(
                                        'controller' => 'dependencies',
                                        'action' => 'index',
                                        $this->request->projectvars['VarsArray'][0],
                                        $this->request->projectvars['VarsArray'][1],
                                        $this->request->projectvars['VarsArray'][2],
                                        $this->request->projectvars['VarsArray'][3],
                                        $this->request->projectvars['VarsArray'][4],
                                        $this->request->projectvars['VarsArray'][5],
                                        $this->request->projectvars['VarsArray'][6],
                                        $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'],
                                        $x

                                    ),
                                    array_merge(
                                        array(
                                                'class'=>'modal dependency',
                                            //	'disabled' => $disabled,
                                                'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
                                        ),
                                        Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                    )
                                )
                            );
                }

                if (!empty($_setting->select->roll->edit)) {
                    foreach ($_setting->select->roll->edit->children() as $_children) {
                        if (trim($_children) == AuthComponent::user('Roll.id')) {
                            break;
                        } else {
                            $addLink = null;
                        }
                    }
                } else {
                }

                if (count($options) > 0) {
                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),
                    // quick and dirty, das muss eleganter im Controller gemacht werden
                    //      pr($options[$data[trim($_setting->model)][trim($_setting->key)]]);


                    $input = $this->Form->input(
                        trim($_setting->model).'.'.trim($_setting->key),
                        array(
                            'label' => $attribut_array['label'],
                            'options' => $options,
                                                        'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                            'multiple' => true,
                            //'disabled' => $disabled,
                            'selected' => array_map('trim', preg_split('/[\r\n,;\|]+/', $data[$model][trim($_setting->key)])),
                            'size' => 7,
                            'onchange'=> '$(this).parent().find("textarea").val($(this).val() == null ? "" : $(this).val().join(", ")); $(this).parent().find("input[type=\'text\']").val($(this).val() == null ? "" : $(this).val().join(", "));',
                            'hiddenField' => false,
                        //	'between' => $hidable ? $hideBox : null,
//							'after' =>  $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                            'after' =>  $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                                'label' => false,
                                                         //    'disabled' =>$disabled,
                                'div' => false,
                                'value' => $data[$model][trim($_setting->key)]
                            )).
                            $addLink
                        )
                    );
                } else {
                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key), array(
                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                                'value' => $data[$model][trim($_setting->key)],
                                'label' => $attribut_array['label'],
                                'between' => $hidable ? $hideBox : null,
                                                                'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                                'after' => $addLink
                            ));
                }
            }

            $output .= $input;
        }

        $output .= '<input id="Ordertopproject_id" type="hidden" name="data[Order][topproject_id]" value="'.$this->request->projectID.'">';
        $output .= '</fieldset>';

        $output .= '<script>
					$(function() {

					var OutputHeight = 0;
					var OutputWidth = 0;
					var OutputDateWidth = 0;
					var OutputHeightBS = 0;

					$("form fieldset.fieldset'.trim($setting[0]->model).'").each(function(index, value){

						$("#" + $(this).attr("id") + " div.input").each(function(){

							if($(this).height() > OutputHeight) {
								OutputHeight = $(this).height();
							}

							if($(this).width() > OutputWidth) {
								OutputWidth = $(this).width();
							}

						});

						OutputWidth = OutputWidth + 10;

						$("#" + $(this).attr("id") + " div.input").css("height",OutputHeight+"px");

						OutputHeight = 0;
						OutputWidth = 0;

					});

					$("div.textarea").css("height","inherit");
					$("div.textarea").css("clear","both");
				});
				</script>';

        return $output;
    }

    public function EditGeneralyData($data, $setting, $lang, $step)
    {
        // wenn keine Daten vorhanden sind

        if ($setting == null) {
            return false;
        }

        $fieldset_count = 0;

        $output  = null;
        $output  = '<div class="'.$step.' formcontainer">';
        $output .= '<fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';

        // ID der Reportnummer
        $output .= $this->Form->input(
            trim($setting[0]->model).'.0.reportnumber_id',
            array(
                            'type' => 'hidden',
                            'value' => $data['Reportnumber']['id']
                        )
        );

        // eigene ID
        $output .= $this->Form->input(
            trim($setting[0]->model).'.0.id',
            array(
                            'type' => 'hidden',
                            'value' => $data[trim($setting[0]->model)]['id']
                        )
        );

        $x = 0;
        foreach ($setting as $_setting) {
            if (Configure::check('FieldsSignForm')&& Configure::read('FieldsSignForm')== true) {
                if (isset($_setting->showinsignform) && $_setting->showinsignform == 1) {
                    continue;
                }
            }

            $x++;
            $model = trim($_setting->model);
            $discription = null;
            $input = null;

            if ($_setting->fieldset != '' && $_setting->fieldset == 1) {
                $fieldset_count++;

                $fieldset_class = 'fieldset'.trim($setting[0]->model).' ';

                if(isset($_setting->multiselect)) $fieldset_class .= 'multiple_field';

                $input = '</fieldset><fieldset class="'.$fieldset_class.'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';

                $output .= $input;
            }

            if (isset($_setting->legend->$lang) && $_setting->legend->$lang != '') {
                $input = '<legend class="headline">'.$_setting->legend->$lang.'</legend>';
                $output .= $input;
            }


            if ($data['Reportnumber'] ['revision'] > 0 && !empty($data['RevisionValues'][trim($_setting->model)][$data[trim($_setting->model)]['id']][trim($_setting->key)])) {
                $field = $_setting->key;
                $modelpart = $_setting->model;
                $revisionlink = $this->Html->link(
                    'Showrevisions',
                    array_merge(array('controller' => 'reportnumbers', 'action' => 'showrevisions'), $this->request->projectvars['VarsArray']),
                    array_merge(
                                    array(
                                    'class' => 'tooltip_ajax_revision',
                                    'title' => __('Content will load...', true),
                                    'id' => $modelpart . '/' . $field
                                )
                                )
                );
            } else {
                $revisionlink = '';
            }

            $disabled = false;

            if (isset($this->_View->viewVars['reportnumber']['Reportnumber']['status']) && $this->_View->viewVars['reportnumber']['Reportnumber']['status'] != 0) {
                $disabled = true;
                if (isset($data['Reportnumber']['revision_write']) && $data['Reportnumber']['revision_write'] == 1) {
                    $disabled = false;

                    if (Configure::check('NotAllowRevision')&& $this->_View->viewVars['reportnumber']['Reportnumber']['status'] >= Configure::read('NotAllowRevision')) {
                        $disabled = true;

                        isset($_setting->allowrevision) && trim($_setting->allowrevision) > 0 ? $disabled = false:'';
                    }
                }
            }

            $attribut_array = array();
            $attribut_array['tabindex'] = 0;

            $disabled == true ? $attribut_array['disabled'] = "disabled" :'';
            // Die Werte aus der Datenbank in das Formularfeld eintragen
            if (isset($data[$model][trim($_setting->key)])) {
                $attribut_array['value'] = $data[$model][trim($_setting->key)];
            }
            //			$attribut_array['title'] = __('Double click to insert specialchars', true);

            // Leerzeichen im Labeltag müssen gegen geschützte Leerzeichen getauscht werden
            $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);

            if ($discription != null) {
                $attribut_array['label'] = $discription;
            }

            if (!empty($_setting->pdf->measure)) {
                $attribut_array['label'] .= ' (' . $_setting->pdf->measure . ')';
            }

            if (isset($_setting->validate->notempty)) {
                $attribut_array['div']['required'] = 'required';
                $attribut_array['label'] .= ' *';
            }
            if (isset($_setting->validate->error)) {
                $attribut_array['class'] = 'error';
            }

//            if(isset($_setting->multiselect)) $fieldset_class .= 'multiple_field';

            //$attribut_array['class'] = 'error';

            // Marker, ob das Feld im PDF Ausdruck ausgeblendet werden kann
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
                    'hide-'.trim($_setting->model).'0'.trim($_setting->key),
                    array(
                        'checked'=>$val,
                        'id'=>'HiddenField'.trim($_setting->model).'0'.trim($_setting->key),
                        'name'=>'data[HiddenField]['.trim($_setting->model).'][0]['.trim($_setting->key).']',
                        'type'=>'checkbox',
                        'hiddenField'=>false,
                        'div'=>false,
                        'label'=>false,
                        'class'=>'hide_box',
                        'style'=>'position: absolute; top: 1px; right: 1px; display: inline-block;'
                    )
                );
            }

            // falls ein spezielles Format für das Inputfeld angegeben ist
            if (trim($_setting->fieldtype) > '0') {
                $attribut_array['type'] = trim($_setting->fieldtype);

                if (trim($_setting->fieldtype) == 'radio') {
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
                        if ($radvalue == $data[$model][trim($_setting->key)]) {
                            $attribut_array['value'] = $radkey;
                        }
                    }
                    if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) {
                        $attribut_array['tabindex'] = '-1';
                    }
                }

                if (trim($_setting->fieldtype) == 'checkbox') {
                    if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) {
                        $attribut_array['tabindex'] = '-1';
                    }
                }
            }

            // bei einer speziellen Datenformatierung
            $attribut_array['class'] = null;
            if (trim($_setting->format) > '0') {
                $attribut_array['class'] .= trim($_setting->format) . ' ';
            }
            if (isset($_setting->validate->error)) {
                $attribut_array['class'] .= ' error';
            }

            if (isset($data[$model][trim($_setting->key)]) && $data[$model][trim($_setting->key)] == 1 && trim($_setting->fieldtype) == 'checkbox') {
                $attribut_array['checked'] = 'checked';
                if ($hidable) {
                    $attribut_array['between'] = $hideBox;
                }
            }

            $attribut_array['before']= $revisionlink;
            $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);

            // Wenn ein Dropdownfeld anliegt
            if (trim($_setting->select->model) != '') {
                $options = array();

                if (isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) && count($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) > 0) {
                    //__________________________29.01.2018______________________________
                    if (!empty($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)])) {
                        $modul = trim($_setting->select->roll->modul);
                        $mfield = trim($_setting->select->roll->field);
                        $options = Hash::combine($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)], '{n}.'.$modul.'.id', '{n}.'.$modul.'.'.$mfield);
                        asort($options);
                        foreach ($options as $option => $o_value) {
                            $o_value == $data[$model][trim($_setting->key)] ? $thisselected = $option : '';
                        }


                        if (isset($_setting->select->roll->dependencies) && isset($_setting->dependencies->child)) {
                            foreach ($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)] as $linkeddatas => $linkedvalues) {
                                $linkedvalues [$modul] ['id'] == $thisselected ?
                                                             $data[$model][trim($_setting->dependencies->child)] = $linkedvalues [$modul] [trim($_setting->select->roll->dependencies->value)]:$data[$model][trim($_setting->dependencies->child)]= '';
                            }
                        }
                    }

                    //__________________________29.01.2018______________________________
                    else {
                        $options = Hash::combine($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)], '{n}.DropdownsValue.id', '{n}.DropdownsValue.discription');

                        $thisselected = null;

                        // Selected suchen
                        if (isset($data[$model][trim($_setting->key)])) {
                            $add = true;
                            foreach ($options as $__key => $__options) {
                                if ($__options == $data[$model][trim($_setting->key)]) {
//                                  $thisselected[$__key] = $__options;
                                    $thisselected = $__key;
                                    $add = false;
                                    break;
                                }
                            }

                            if ($add && array_search($data[$model][trim($_setting->key)], $options) === false && !isset($_setting->multiselect)) {
                                $options[$data[$model][trim($_setting->key)]] = $data[$model][trim($_setting->key)];
                                $thisselected = $data[$model][trim($_setting->key)];
                            }
                            if(isset($_setting->multiselect) && !empty($data[$model][trim($_setting->key)])){
//                              pr($options);
                            }

                            if(isset($_setting->multiselect) && !empty($data[$model][trim($_setting->key)])){

                              $thisselected = explode("\n",$data[$model][trim($_setting->key)]);

                              $multiseleced = array();
                              foreach ($options as $__key => $__options) {
                                if (in_array($__options, $thisselected)) {
                                  $multiseleced[$__key] = $__key;
                                }
                              }

                              // quick and dirty Notlösung
                              // Wenn im gespeichertem Wert Einträge stehen,
                              // nicht als Dropdownwert vorhanden sind
/*
                              if(count($thisselected) > count($multiseleced)){
                                foreach ($thisselected as $__key => $__options) {
                                  if(array_search($__options, $options) === false){
                                    $options[] = $__options;
                                    $multiseleced[array_search($__options, $options)] = $__options;
                                  }
                                }
                              }
*/
                              $thisselected = $multiseleced;

                            }

                            /*
                            foreach(preg_split('/[,|]+/', $data[$model][trim($_setting->key)]) as $value) {
                                $options[trim($value)] = trim($value);
                            }
                            */
                            $options = array_unique($options);
                            asort($options);
                        }

                        if (isset($_setting->dependencies->child)) {
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
                        }
                    }
                    $EditSelectLink =
                            $this->Html->link(__('Edit'), array(
                                'controller' => 'dropdowns',
                                'action' => 'dropdownindex',
                                $this->request->projectvars['VarsArray'][0],
                                $this->request->projectvars['VarsArray'][1],
                                $this->request->projectvars['VarsArray'][2],
                                $this->request->projectvars['VarsArray'][3],
                                $this->request->projectvars['VarsArray'][4],
                                $this->request->projectvars['VarsArray'][5],

                                $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'],
                                $x
                            ), array_merge(
                                array(
                                    'class'=>'modal dropdown',
                                    'disabled' => $disabled,
                                    'title'=> __('Edit dropdown', true)
                                ),
                                Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                            )).
                            (
                                !isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0
                                ? null
                                : $this->Html->link(
                                    __('Edit dependent fields'),
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
                                        $x

                                    ),
                                    array_merge(
                                        array(
                                                'class'=>'modal dependency',
                                                'disabled' =>$disabled,
                                                'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
                                        ),
                                        Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                    )
                                )
                            );

                    if (!empty($_setting->select->roll->edit)) {
                        foreach ($_setting->select->roll->edit->children() as $_children) {
                            if (trim($_children) == AuthComponent::user('Roll.id')) {
                                $test = $EditSelectLink;
                                break;
                            } else {
                                // do nothing
                                $test = '<span style="display:none">' .$EditSelectLink . '</span>';
                            }
                        }
                    } else {
                        $test = $EditSelectLink;
                    }

                    $Class = isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null;

                    if (isset($_setting->validate->error)) {
                        $Class .= ' error';
                    }

                    $input = $this->Form->input(
                        trim($_setting->model).'.'.trim($_setting->key),
                        array(
                            'label' => $attribut_array['label'],
                            'disabled' => $disabled,
                            'options' => $options,
                            'multiple' => isset($_setting->multiselect)? 'multiple' : '',
                            'selected' => isset($thisselected) ? $thisselected : '',
                            'class' => $Class,
                            'empty' => ' ',
                            'before' => $revisionlink,
                            'between' => $hidable ? $hideBox : null,
                            'after' => empty($data['LinkedDropdownData'][trim($_setting->model)] [trim($_setting->key)])? $test : ''
                        )
                    );
                } else {
                    $options = array();

                    $attribut_array['after'] =
                            $this->Html->link(
                                __('Edit'),
                                array(
                                        'controller' => 'dropdowns',
                                        'action' => 'dropdownindex',
                                            $this->request->projectvars['VarsArray'][0],
                                            $this->request->projectvars['VarsArray'][1],
                                            $this->request->projectvars['VarsArray'][2],
                                            $this->request->projectvars['VarsArray'][3],
                                            $this->request->projectvars['VarsArray'][4],
                                            $this->request->projectvars['VarsArray'][5],

                                            0,
                                            $x
                                    ),
                                array_merge(
                                    array(
                                            'class'=>'modal dropdown',
                                            'disabled' => $disabled,
                                            'title'=> __('Add dropdown', true)
                                        ),
                                    Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                )
                            );

                    if ($hidable) {
                        $attribut_array['between'] = $hideBox;
                    }

                    if (!empty($_setting->select->roll->edit)) {
                        foreach ($_setting->select->roll->edit->children() as $_children) {
                            if (trim($_children) == AuthComponent::user('Roll.id')) {
                                //								$test = $EditSelectLink;
                                break;
                            } else {
                                // do nothing
                                unset($attribut_array['after']);
                                //								$test = null;
                            }
                        }
                    } else {
                        //						$test = $EditSelectLink;
                    }
                    if (isset($_setting->validate->error)) {
                        $attribut_array['class'] = 'error';
                    }

                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                }
            }


#############################
#############################
##############################
############################

            if (isset($_setting->select->moduldata)) {
                $options = array();
                $optionsarray = array();
                if (isset($data['ModulData'])) {
                    $optionsarray = json_decode($data['ModulData'], true);

                    /*   if (isset($optionsarray[trim($_setting->model)][trim($_setting->key)])) {
                           pr ('test');
                       }*/
                }
                if (!empty($optionsarray) && isset($optionsarray [trim($_setting->model)][trim($_setting->key)])) {
                    $options = Hash::combine($optionsarray [trim($_setting->model)][trim($_setting->key)], '{n}.id', '{n}.'.trim($_setting->select->moduldata->field));
                    if (isset($data [trim($_setting->model)][trim($_setting->key)])) {
                        $thisselected = array_search($data [trim($_setting->model)][trim($_setting->key)], $options);
                    }

                    $input = $this->Form->input(
                        trim($_setting->model).'.'.trim($_setting->key),
                        array(
                            'label' => $attribut_array['label'],
                            'class' => 'modulselect',
                            'disabled' => $disabled,
                            'options' => $options,
                            'selected' => isset($thisselected)? $thisselected : '',
                            //'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                            'empty' => ' ',
                            'before' => $revisionlink,
                            'between' => $hidable ? $hideBox : null,

                        )
                    );
                }
            }

            if (isset($_setting->multiselect) && (trim($_setting->fieldtype) == 'text' || trim($_setting->fieldtype) == '')) {





/*
                if (is_array($options)) {
                    $options = array_combine(array_values($options), array_values($options));
                }

                if (isset($data['Multiselects'][trim($_setting->model)][trim($_setting->key)])) {
                    if (!isset($options) || empty($options)) {
                        $options = $data['Multiselects'][trim($_setting->model)][trim($_setting->key)];
                    } else {
                        //$options = Hash::expand(array_merge(Hash::flatten(array(__('Dynamic values', true)=>$options),'$'), Hash::flatten($data['Multiselects'][trim($_setting->model)][trim($_setting->key)],'$')), '$');
                        $options = array();
                    }

                    foreach (preg_split('/[,|\r\n]+/', $data[trim($_setting->model)][trim($_setting->key)]) as $value) {
                        $options[trim($value)] = trim($value);
                    }

                    $options = array_unique($options);
                    //uksort($options, function($a, $b) { return (empty($a) || $a < $b ? -1 : (empty($b) || $a > $b ? 1 : 0)); });
                    ksort($options);
                }

                if (isset($_setting->dependencies->child)) {
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
                }

                $addLink = null;
                if (trim($_setting->select->model != '')) {
                    $addLink = $this->Html->link(__('Edit'), array(
                        'controller' => 'dropdowns',
                        'action' => 'dropdownindex',

                        $this->request->projectvars['VarsArray'][0],
                                            $this->request->projectvars['VarsArray'][1],
                                            $this->request->projectvars['VarsArray'][2],
                                            $this->request->projectvars['VarsArray'][3],
                                            $this->request->projectvars['VarsArray'][4],
                                            $this->request->projectvars['VarsArray'][5],

                        isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0])
                        ? $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id']
                        : 0,
                        $x,
                        $data['Reportnumber']['id'],
                    ), array_merge(
                        array(
                            'class'=>'modal dropdown',
                            'disabled' => $disabled,
                            'title'=> __('Edit dropdown', true)
                        ),
                        Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                    )).
                            (
                                !isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0
                                ? null
                                : $this->Html->link(
                                    __('Edit dependent fields'),
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
                                        $x

                                    ),
                                    array_merge(
                                        array(
                                                'class'=>'modal dependency',
                                                'disabled' => $disabled,
                                                'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
                                        ),
                                        Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                    )
                                )
                            );
                }

                if (!empty($_setting->select->roll->edit)) {
                    foreach ($_setting->select->roll->edit->children() as $_children) {
                        if (trim($_children) == AuthComponent::user('Roll.id')) {
                            break;
                        } else {
                            $addLink = null;
                        }
                    }
                } else {
                }

                if (count($options) > 0) {
                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),

                    // quick and dirty, das muss eleganter im Controller gemacht werden
                    $part = explode("\n", $options[$data[$model][trim($_setting->key)]]);
                    if (count($part) > 1) {
                        unset($options[$data[$model][trim($_setting->key)]]);
                    }

                    $input = $this->Form->input(
                        trim($_setting->model).'.'.trim($_setting->key),
                        array(
                            'label' => $attribut_array['label'],
                            'options' => $options,
                            'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                            'multiple' => true,
                            'disabled' => $disabled,
                            'selected' => array_map('trim', preg_split('/[\r\n,;\|]+/', $data[$model][trim($_setting->key)])),
                            'size' => 7,
                            'onchange'=> '$(this).parent().find("textarea").val($(this).val() == null ? "" : $(this).val().join("\n")); $(this).parent().find("input[type=\'text\']").val($(this).val() == null ? "" : $(this).val().join(", "));',
                            'hiddenField' => false,
                            'between' => $hidable ? $hideBox : null,
//							'after' =>  $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                            'after' =>  $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                                'label' => false,
                                                             'disabled' =>$disabled,
                                'div' => false,
                                'value' => $data[$model][trim($_setting->key)]
                            )).
                            $addLink
                        )
                    );
                } else {
                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key), array(
                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                                'value' => $data[$model][trim($_setting->key)],
                                'label' => $attribut_array['label'],
                                'between' => $hidable ? $hideBox : null,
                                'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                                'after' => $addLink
                            ));
                }
*/
            }

            $output .= $input;
            $options = null;

        }

        $output .= '<input id="'.$model.'Id" type="hidden" name="data['.$model.'][id]" value="'.$data[$model]['id'].'">';
        $output .= '</fieldset>';
        //		$output .= '<div id="waitingtest"></div>';
        $output .= '</div>';

        $output .= '<script>
				$(function() {

					var OutputHeight = 0;
					var OutputWidth = 0;
					var OutputDateWidth = 0;
					var OutputHeightBS = 0;


					// beim Datumsformat muss noch die Dynamik eingebaut werden
//					$(".time").datetimepicker({ format: "H:i:s", scrollInput: false});
//					$(".date").datetimepicker({ format: "Y-m-d", timepicker:false, lang:"de", scrollInput: false});
//					$(".datetime").datetimepicker({ lang:"de", format: "Y-m-d H:i", scrollInput: false});


					$("form fieldset.fieldset'.trim($setting[0]->model).'").each(function(index, value){

						$("#" + $(this).attr("id") + " div.input").each(function(){
							if($(this).height() > OutputHeight) {
								OutputHeight = $(this).height();
							}

							if($(this).width() > OutputWidth) {
								OutputWidth = $(this).width();
							}

						});

						OutputWidth = OutputWidth + 10;
						$("#" + $(this).attr("id") + " div.input").css("height",OutputHeight+"px");
//						$("#" + $(this).attr("id") + " div.input").css("width",OutputWidth+"px");

						OutputHeight = 0;
						OutputWidth = 0;

					});

					$("#ReportRtGenerally0WaitingStart").blur(function(){

						var data = $("#fakeform").serializeArray();
						var includecontainer = "WaitingTime" + $(this).attr("id");

						$("#" + includecontainer).remove();

						data.push({name: "ajax_true", value: 1});
						data.push({name: "date", value: $(this).val()});
						data.push({name: "examinierer1", value: $("#ReportRtGenerally0Examiner").val()});
						data.push({name: "examinierer2", value: $("#ReportRtGenerally0Examinierer2").val()});
						data.push({name: "id", value: '.$data['Reportnumber']['id'].'});

						$("#" + $(this).attr("id")).after( "<span id=\"" + includecontainer + "\"></span>" )

						$.ajax({
							type	: "POST",
							cache	: false,
							url	: "'.Router::url(array('controller' => 'invoices', 'action' => 'waitingtest')).'",
							data	: data,
							success: function(data) {
		    					$("#" + includecontainer).html(data);
		    					$("#" + includecontainer).show();
							}
						});
						return false;
					});
				});

		</script>';
        $output .= '<script>
                                                    $(document).ready(function(){
                                                        $("select.hasDependencies").on("change", function() {



                                                        });

                                                    });
                                                        </script>';


        return $output;
    }

    public function EditEvaluationData($data, $setting, $lang)
    {
        $legend_desc = __('Edit');

        $attribut_disabled = null;

        if (
            $data['Reportnumber']['status'] > 0 ||
            $data['Reportnumber']['deactive'] > 0 ||
            $data['Reportnumber']['settled'] > 0 ||
            $data['Reportnumber']['delete'] > 0) {
            $attribut_disabled = array('disabled' => 'disabled');
        }

        if (isset($data['Reportnumber']['revision_write']) && $data['Reportnumber']['revision_write'] == 1) {
            $attribut_disabled = null;
        }

        if (isset($this->request->projectvars['weldedit']) && $this->request->projectvars['weldedit'] == 1) {
            $legend_desc = __('Edit the complete weld');
        } else {
            $legend_desc = __('Edit examination area');
        }

        $fieldset_count = 0;

        $output  = null;

        $output .= '<fieldset class="fieldset'.trim(reset($setting)->model).'" id="fieldset'.trim(reset($setting)->model).'_'.$fieldset_count.'">';
        //		$output .= '<legend>'.$legend_desc.'</legend>';
        $output .= '<legend>&nbsp;</legend>';

        $output .= $this->Form->input(
            trim(reset($setting)->model).'.0.id',
            array(
                            'type' => 'hidden',
                            'value' => @$data[trim(reset($setting)->model)]['id']
                        )
        );
        $output .= $this->Form->input(
            trim(reset($setting)->model).'.0.reportnumber_id',
            array(
                            'type' => 'hidden',
                            'value' => @$data[trim(reset($setting)->model)]['reportnumber_id']
                        )
        );

        $x = 0;
        $tbi = 1;
        foreach ($setting as $_setting) {
            $x++;
            $model = trim($_setting->model);
            $discription = null;
            $input = null;

            if ($_setting->fieldset != '' && $_setting->fieldset == 1) {
                $fieldset_count++;
                $input = '</fieldset><fieldset class="fieldset'.trim(reset($setting)->model).'" id="fieldset'.trim(reset($setting)->model).'_'.$fieldset_count.'">';
                $output .= $input;
            }

            if (!empty($_setting->multiselect)) {
                $output .= '</fieldset><fieldset class="multiple_field">';
            }

            if ($_setting->legend->$lang != '' && $_setting->legend->$lang != '') {
                $input = '<legend class="headline">'.$_setting->legend->$lang.'</legend>';
                $output .= $input;
            }

            if (
                        $data['Reportnumber'] ['revision'] > 0 &&
                        isset($data[trim($_setting->model)]) &&
                        !empty($data ['RevisionValues'] [trim($_setting->model)] [$data[trim($_setting->model)] ['id']][trim($_setting->key)]) &&
                        $this->request->projectvars['VarsArray'][5] > 0) {
                $field = $_setting->key;
                $modelpart = $_setting->model;
                $revisionlink  = $this->Html->link(
                    'Showrevisions',
                    array(
                                'controller' => 'reportnumbers',
                                 'action' => 'showrevisions',$this->request->projectvars['VarsArray'][0],
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
                                    'class'=> 'tooltip_ajax_revision',
                                    'title'=> __('Content will load...', true),
                                                                        'id' => $model.'/'.$field,
                                                                )
                                )
                );
            } else {
                $revisionlink = '';
            }


            $attribut_array = array();
            $attribut_array['tabindex'] = ".$tbi.";
            $tbi++;
            // Den Wert in das Eingabefeld eintragen
            $attribut_array['value'] = @$data[$model][trim($_setting->key)];

            $disabled = false;

            if (isset($this->_View->viewVars['reportnumber']['Reportnumber']['status']) && $this->_View->viewVars['reportnumber']['Reportnumber']['status'] != 0) {
                $disabled = true;
                if (isset($data['Reportnumber']['revision_write']) && $data['Reportnumber']['revision_write'] == 1) {
                    $disabled = false;

                    if (Configure::check('NotAllowRevision')&& $this->_View->viewVars['reportnumber']['Reportnumber']['status'] >= Configure::read('NotAllowRevision')) {
                        $disabled = true;

                        isset($_setting->allowrevision) && trim($_setting->allowrevision) > 0 ? $disabled = false:'';
                    }
                }
            }

            $disabled == true ?  $attribut_disabled = array('disabled' => 'disabled') : '';

            if ($attribut_disabled != null) {
                $attribut_array['disabled'] = "disabled";
            }

            // Leerzeichen im Labeltag müssen gegen geschützte Leerzeichen getauscht werden und bei Tabellenkopf ersetzen die Labels auch
            if (isset($this->_View->viewVars['replaceheaderdata'])&&($this->_View->viewVars['replaceheaderdata'] == '1' || $this->_View->viewVars['replaceheaderdata'] == 'true') && isset($_setting->headerfrom) &&  isset($_setting->headerfrom->key)&&isset($_setting->headerfrom->model)) {
              $headmodel = trim($_setting->headerfrom->model);
              $headkey =  trim($_setting->headerfrom->key);
              if(!empty($data[$headmodel][$headkey])) {
                $discription = str_replace(' ', '&nbsp;',$data[$headmodel][$headkey]);
              }else{
                $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);
              }
            }else{
              $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);
            }

            if ($discription != null) {
                $attribut_array['label'] = $discription;
            }

            if (!empty($_setting->pdf->measure)) {
                $attribut_array['label'] .= ' (' . $_setting->pdf->measure . ')';
            }

            if (isset($_setting->validate->notempty)) {
                $attribut_array['div']['required'] = 'required';
                $attribut_array['label'] .= ' *';
            }

            // falls ein spezielles Format für das Inputfeld angegeben ist
            if (trim($_setting->fieldtype) > '0') {
                $attribut_array['type'] = trim($_setting->fieldtype);

                if (trim($_setting->fieldtype) == 'radio') {
                    $radiooptions = $this->radiodefault;

                    if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {
                        $radiooptions = array();

                        foreach ($_setting->radiooption->value as $_radiooptions) {
                            array_push($radiooptions, trim($_radiooptions));
                        }
                    }
                    $attribut_array['legend'] = $attribut_array['label'];
                    $attribut_array['options'] = $radiooptions;
                    if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) {
                        $attribut_array['tabindex'] = '-1';
                    }
                }

                if (trim($_setting->fieldtype) == 'checkbox') {
                    if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) {
                        $attribut_array['tabindex'] = '-1';
                    }
                }
            }

            $attribut_array['class'] = null;
            // bei einer speziellen Datenformatierung
            if (trim($_setting->format) > '0') {
                $attribut_array['class'] .= trim($_setting->format) . ' ';
            }
            if ($_setting->validate->error) {
                $attribut_array['class'] .= 'error ';
            }

            if (isset($data[$model][trim($_setting->key)]) && $data[$model][trim($_setting->key)] == 1 && trim($_setting->fieldtype) == 'checkbox') {
                $attribut_array['checked'] = 'checked';
            }
            $attribut_array['before'] = $revisionlink;
            if (trim($_setting->select->model == '')) {
                //				$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),$attribut_array);
                $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
            }

            if (trim($_setting->select->model != '')) {
                $thisselected = null;

                // Selected suchen
                if (isset($data['Dropdowns'][trim($_setting->model)][trim($_setting->key)])) {
                    $add = true;
                    foreach ($data['Dropdowns'][trim($_setting->model)][trim($_setting->key)] as $__key => $__options) {
                        if (isset($data[$model][trim($_setting->key)]) && $__options == $data[$model][trim($_setting->key)]) {
                            $add = false;
                            $thisselected = $__key;
                            break;
                        }
                    }

                    if (isset($data[$model][trim($_setting->key)]) && $add && array_search($data[$model][trim($_setting->key)], $data['Dropdowns'][trim($_setting->model)][trim($_setting->key)])) {
                        $data['Dropdowns'][trim($_setting->model)][trim($_setting->key)][$data[$model][trim($_setting->key)]] = $data[$model][trim($_setting->key)];
                    }
                }


                if (isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) && count($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) > 0) {
                    $editdropdownlink =
                            $this->Html->link(
                                __('Edit'),
                                array(
                                        'controller' => 'dropdowns',
                                        'action' => 'dropdownindex',
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
                                                'class'=>'modal dropdown',
                                                $attribut_disabled,
                                                'title'=> __('Edit dropdown', true)
                                        ),
                                    (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) ? array('tabindex'=>'-1') : array()
                                )
                            )
                    ;

                    if (!empty($_setting->select->roll->edit)) {
                        foreach ($_setting->select->roll->edit->children() as $_children) {
                            if (trim($_children) == AuthComponent::user('Roll.id')) {
                                $test = $editdropdownlink;
                                break;
                            } else {
                                // do nothing
                                $test = null;
                            }
                        }
                    } else {
                        $test = $editdropdownlink;
                    }

                    if (trim($_setting->key) == 'image_no') {
                        $test .=
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
                                    'class'=>'modal en1435',
                                    $attribut_disabled,
                                    'title'=> __('EN 1435 Bildnummern', true)
                                    ),
                                    Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                )
                            );
                    }

                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),
                    $Class = null;
                    if ($_setting->validate->error) {
                        $Class = 'error ';
                    }

                    $input = $this->Form->input(
                        trim($_setting->model).'.'.trim($_setting->key),
                        array(
                        'label' => $attribut_array['label'],
                        'options' => $data['Dropdowns'][trim($_setting->model)][trim($_setting->key)],
                        'selected' => $thisselected,
                        'empty' => ' ',
                        'class' => $Class,
                        'before' =>$revisionlink,
                        'after' => $test,
                        $attribut_disabled
                        )
                    );

                    $test = null;
                    $editdropdownlink = null;
                } else {
                    $editdropdownlink = null;

                    $editdropdownlink .= $this->Html->link(__('Edit'), array(
                                        'controller' => 'dropdowns',
                                        'action' => 'dropdownindex',
                                        $this->request->projectvars['VarsArray'][0],
                                        $this->request->projectvars['VarsArray'][1],
                                        $this->request->projectvars['VarsArray'][2],
                                        $this->request->projectvars['VarsArray'][3],
                                        $this->request->projectvars['VarsArray'][4],
                                        $this->request->projectvars['VarsArray'][5],
                                        0,
                                        $x,
                                        ), array_merge(
                                            array(
                                                    'class'=>'modal dropdown',
                                                    $attribut_disabled ,
                                                    'title'=> __('Add dropdown', true)
                                            ),
                                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                        ));

                    if (!empty($_setting->select->roll->edit)) {
                        foreach ($_setting->select->roll->edit->children() as $_children) {
                            if (trim($_children) == AuthComponent::user('Roll.id')) {
                                $test = $editdropdownlink;
                                break;
                            } else {
                                // do nothing
                                $test = null;
                            }
                        }
                    } else {
                        $test = $editdropdownlink;
                    }

                    if (trim($_setting->key) == 'image_no') {
                        $test .=
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
                                                    'class'=>'modal en1435',
                                                    $attribut_disabled ,
                                                    'title'=> __('EN 1435 Bildnummern', true)
                                            ),
                                        Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                    )
                                );
                    }

                    $attribut_array['after'] =  $test;

                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),$attribut_array);
                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                }
            }
            if (isset($_setting->select->moduldata)) {
                $options = array();
                $optionsarray = array();
                if (isset($data['ModulData'])) {
                    $optionsarray = json_decode($data['ModulData'], true);

                    /*   if (isset($optionsarray[trim($_setting->model)][trim($_setting->key)])) {
                           pr ('test');
                       }*/
                }
                if (!empty($optionsarray) && isset($optionsarray [trim($_setting->model)][trim($_setting->key)])) {
                    $options = Hash::combine($optionsarray [trim($_setting->model)][trim($_setting->key)], '{n}.id', '{n}.'.trim($_setting->select->moduldata->field));
                    if (isset($data [trim($_setting->model)][trim($_setting->key)])) {
                        $thisselected = array_search($data [trim($_setting->model)][trim($_setting->key)], $options);
                    }

                    $input = $this->Form->input(
                        trim($_setting->model).'.'.trim($_setting->key),
                        array(
                            'label' => $attribut_array['label'],
                                                        'disabled' => $disabled,
                            'options' => $options,
                            'selected' => isset($thisselected)? $thisselected : '',
                            //'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                            'empty' => ' ',
                                                        'before' => $revisionlink,
                            'between' => $hidable ? $hideBox : null,

                        )
                    );
                }
            }
            if (isset($_setting->multiselect)) {
                $options = @$data['Dropdowns'][trim($_setting->model)][trim($_setting->key)];

                if (isset($data['Multiselects'][trim($_setting->model)][trim($_setting->key)])) {
                    if (!isset($options) || empty($options)) {
                        $options = $data['Multiselects'][trim($_setting->model)][trim($_setting->key)];
                    } else {
                        $options = array_merge(array(__('Dropdown values', true)=>$options), $data['Multiselects'][trim($_setting->model)][trim($_setting->key)]);
                    }
                }

                $addLink = null;
                if (trim($_setting->select->model != '')) {
                    $addLink = $this->Html->link(__('Edit'), array(
                        'controller' => 'dropdowns',
                        'action' => 'dropdownindex',
                        $this->request->projectvars['VarsArray'][0],
                        $this->request->projectvars['VarsArray'][1],
                        $this->request->projectvars['VarsArray'][2],
                        $this->request->projectvars['VarsArray'][3],
                        $this->request->projectvars['VarsArray'][4],
                        $this->request->projectvars['VarsArray'][5],
                        $this->request->projectvars['VarsArray'][6],
                        isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0])
                        ? $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id']
                        : 0,
                        $x,
                        $data['Reportnumber']['id'],
                        ), array_merge(
                            array(
                                    'class'=>'modal dropdown',
                                    $attribut_disabled ,
                                    'title'=> __('Edit dropdown', true)
                            ),
                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                        ));

                    if (!empty($_setting->select->roll->edit)) {
                        foreach ($_setting->select->roll->edit->children() as $_children) {
                            if (trim($_children) == AuthComponent::user('Roll.id')) {
                                $test = $addLink;
                                break;
                            } else {
                                // do nothing
                                $test = null;
                            }
                        }
                    } else {
                        $test = $addLink;
                    }
                }

                if (count($options) > 0) {
                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),
                    $input = $this->Form->input(
                        trim($_setting->model).'.'.trim($_setting->key),
                        array(
                            'label' => $attribut_array['label'],
                            'options' => $options,
                            'multiple' => true,
                            $attribut_disabled,
                            'selected' => array_map('trim', preg_split('/[\r\n,;\|]+/', isset($data[$model][trim($_setting->key)])? $data[$model][trim($_setting->key)] : '')),
                            'size' => 7,
                            'onchange'=> '$(this).parent().find("textarea").val($(this).val() == null ? "" : $(this).val().join("\n")); $(this).parent().find("input").val($(this).val() == null ? "" : $(this).val().join(", "));',
                            'hiddenField' => false,
//							'after' =>  $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key), array(
                            'after' =>  $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                                'label' => false,
                                                                'disabled' => $attribut_disabled,
                                'div' => false,
                                //'value' => isset($data[$model][trim($_setting->key)]) ? $data[$model][trim($_setting->key)] : ''
                            )).
                            $test
                        )
                    );
                } else {
                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key), array(
                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                                'value' => $data[$model][trim($_setting->key)],
                                'label' => $attribut_array['label'],
                                           'before'=> $revisionlink,
                                'after' => $test,
                                            $attribut_disabled
                            ));
                }
            }

            if (isset($_setting->dependencies->parent)) {
                $xml = $this->_View->viewVars['arrayData']['settings'];

                $options = array_filter(array_map(function ($val) use ($model, $data, $xml) {
                    $val = explode('.', $val, 2);

                    if (count($val) == 1) {
                        $val = array('model'=>$model, 'field'=>$val[0]);
                    } else {
                        $val = array('model'=>$val[0], 'field'=>$val[1]);
                    }

                    $val['field'] = $xml->xpath($val['model'].'/'.$val['field'].'/key');
                    $val['field'] = trim(reset($val['field']));

                    return isset($data[$val['model']][$val['field']]) ? trim($data[$val['model']][$val['field']]) : null;
                }, $_setting->xpath('dependencies/parent')));

                if (!empty($options)) {
                    $input = $this->Form->input(
//						trim($_setting->model).'.0.'.trim($_setting->key),
                        trim($_setting->model).'.'.trim($_setting->key),
                        array(
                            'empty' => ' ',
                            'label' => $attribut_array['label'],
                            'options' => array_combine($options, $options),
                            'multiple' => false,
                                                        'before' => $revisionlink,
                            'selected' => isset($data[$model][trim($_setting->key)]) ? $data[$model][trim($_setting->key)] : ''
                        )
                    );
                }
            }

            $output .= $input;
            $options = null;
        }

        if (isset($data[$model][0]['id'])) {
            $id = $data[$model][0]['id'];
        } else {
            $id = null;
        }

        $output .= '<input id="'.$model.'Id" type="hidden" name="data['.$model.'][id]" value="'.$id.'">';
        $output .= '</fieldset>';
        $output .= '<script>
					$(function() {

					var OutputHeight = 0;
					var OutputWidth = 0;
					var OutputDateWidth = 0;

					$("fieldset.multiple_field select").multiSelect({ selectableOptgroup: true });

					$("form fieldset.fieldset'.trim(reset($setting)->model).'").each(function(index, value){

						$("#" + $(this).attr("id") + " div.input").each(function(){
							if($(this).height() > OutputHeight) {
								OutputHeight = $(this).height();
							}

							if($(this).width() > OutputWidth) {
								OutputWidth = $(this).width();
							}

						});

						OutputWidth = OutputWidth + 10;

						$("#" + $(this).attr("id") + " div.input").css("height",OutputHeight+"px");
//						$("#" + $(this).attr("id") + " div.input").css("width",OutputWidth+"px");

						OutputHeight = 0;
						OutputWidth = 0;

					});

/*
					$("form fieldset.fieldset'.trim(reset($setting)->model).' div.input").each(function(){
						if($(this).height() > OutputHeight) {
							OutputHeight = $(this).height();
						}
						if($(this).width() > OutputWidth) {
							OutputWidth = $(this).width();
						}
					});
*/

					$("form fieldset.fieldset'.trim(reset($setting)->model).' div.date select").each(function(){
						OutputDateWidth = OutputDateWidth + $(this).width() + 10;
					});

//					$("form fieldset.fieldset'.trim(reset($setting)->model).' div").css("height",OutputHeight+"px");
//					$("form fieldset.fieldset'.trim(reset($setting)->model).' div").css("width",OutputWidth+"px");
					$("form fieldset.fieldset'.trim(reset($setting)->model).' div.date").css("width",OutputDateWidth+"px");

					});

					$("#content .reportnumbers .edit .specialchars")
						.attr("rel", "")
						.removeData("timeout")
						.off("click")
						.unbind("click")
						.on("click", function() {
							var modalheight = Math.ceil(($(window).height() * 90) / 100);
							var modalwidth = Math.ceil(($(window).width() * 90) / 100);

							var dialogOpts = {
								modal: false,
								width: modalwidth,
								height: modalheight,
								autoOpen: false,
								draggable: true,
								resizeable: true
							};

							$("#dialog").dialog(dialogOpts);

							$("#dialog").load($(this).attr("href"), {
								"ajax_true": 1,
								"thisID": $(this).attr("rel"),
							});
							$("#dialog").dialog("open");
							return false;
						});

					$(".reportnumbers input[type=\'text\'], .reportnumbers textarea").on({
						"focus": function() {
							$("#content .reportnumbers .edit .specialchars").attr("rel", $(this).attr("id"));
						}
					});

				</script>';


        return $output;
    }

    public function ShowWeldAssistentData($settings)
    {
        $output = null;
        foreach ($settings as $_settings) {
            foreach ($_settings as $__settings) {
                foreach ($__settings as $___key => $___settings) {
                    $options = array();
                    $options['label'] = str_replace('|br|', '<br />', trim($___settings->discription->{$this->_View->viewVars['locale']}));
                    $options['type'] = trim($___settings->fieldtype);

                    if (trim($___settings->select->value) != '') {
                        $selectOption = array();
                        foreach ($___settings->select->value as $value) {
                            $selectOption[] = trim($value);
                        }
                        $options['options'] = $selectOption;
                        $options['multiple'] = 'multiple';
                    }

                    $output .= $this->Form->input(trim($___settings->key), $options);
                }
            }
        }

        return $output;
    }

    public function UserInfos($user)
    {
        if ($user['User']['enabled'] == 0) {
            echo $this->Html->link(__('User is disabled'), 'javascript:return', array('title' => __('This account is disabled', true), 'class' => 'icon icon_deactive'));
        }
        if ($user['User']['counter_blocked'] == 1) {
            echo $this->Html->link(__('User is blocked'), 'javascript:return', array('title' => __('This account is blocked, to many invalid login attempts', true),'class' => 'icon icon_blocked'));
        }
        if ($user['User']['time_blocked'] == 1) {
            echo $this->Html->link(__('User is blocked'), 'javascript:return', array('title' => __('This account has been suspended due to inactivity', true), 'class' => 'icon icon_time_blocked'));
        }
    }




    public function EditGeneralyDataSign($data, $setting, $lang, $step, $signtype)
    {
        // wenn keine Daten vorhanden sind

        if ($setting == null) {
            return false;
        }

        $fieldset_count = 0;

        $output  = null;
        $output  = '<div class="'.$step.' formcontainer">';
        $output .= '<fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';

        // ID der Reportnummer
        $output .= $this->Form->input(
                trim($setting[0]->model).'.0.reportnumber_id',
                array(
                                'type' => 'hidden',
                                'value' => $data['Reportnumber']['id']
                            )
            );

        // eigene ID
        $output .= $this->Form->input(
                trim($setting[0]->model).'.0.id',
                array(
                                'type' => 'hidden',
                                'value' => $data[trim($setting[0]->model)]['id']
                            )
            );

        $x = 0;
        foreach ($setting as $_setting) {
            if (!isset($_setting->showinsignform) || $_setting->showinsignform <> 1 || $signtype <> trim($_setting->signtype)) {
                continue;
            }
            $x++;
            $model = trim($_setting->model);
            $discription = null;
            $input = null;

            if ($_setting->fieldset != '' && $_setting->fieldset == 1) {
                $fieldset_count++;
                $input = '</fieldset><fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';
                $output .= $input;
            }

            if (isset($_setting->legend->$lang) && $_setting->legend->$lang != '') {
                $input = '<legend class="headline">'.$_setting->legend->$lang.'</legend>';
                $output .= $input;
            }


            if ($data['Reportnumber'] ['revision'] > 0 && !empty($data['RevisionValues'][trim($_setting->model)][$data[trim($_setting->model)]['id']][trim($_setting->key)])) {
                $field = $_setting->key;
                $modelpart = $_setting->model;
                $revisionlink = $this->Html->link(
                        'Showrevisions',
                        array_merge(array('controller' => 'reportnumbers', 'action' => 'showrevisions'), $this->request->projectvars['VarsArray']),
                        array_merge(
                                        array(
                                        'class' => 'tooltip_ajax_revision',
                                        'title' => __('Content will load...', true),
                                        'id' => $modelpart . '/' . $field
                                    )
                                    )
                    );
            } else {
                $revisionlink = '';
            }

            $disabled = false;
            if (isset($this->_View->viewVars['reportnumber']['Reportnumber']['status']) && $this->_View->viewVars['reportnumber']['Reportnumber']['status'] != 0) {
                $disabled = true;
                if (isset($data['Reportnumber']['revision_write']) && $data['Reportnumber']['revision_write'] == 1) {
                    $disabled = false;

                    if (Configure::check('NotAllowRevision')&& $this->_View->viewVars['reportnumber']['Reportnumber']['status'] >= Configure::read('NotAllowRevision')) {
                        $disabled = true;

                        isset($_setting->allowrevision) && trim($_setting->allowrevision) > 0 ? $disabled = false:'';
                    }
                }
            }

            $attribut_array = array();
            $attribut_array['tabindex'] = 0;

            $disabled == true ? $attribut_array['disabled'] = "disabled" :'';
            // Die Werte aus der Datenbank in das Formularfeld eintragen
            if (isset($data[$model][trim($_setting->key)])) {
                $attribut_array['value'] = $data[$model][trim($_setting->key)];
            }
            //			$attribut_array['title'] = __('Double click to insert specialchars', true);

            // Leerzeichen im Labeltag müssen gegen geschützte Leerzeichen getauscht werden
            $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);

            if ($discription != null) {
                $attribut_array['label'] = $discription;
            }

            if (!empty($_setting->pdf->measure)) {
                $attribut_array['label'] .= ' (' . $_setting->pdf->measure . ')';
            }

            if (isset($_setting->validate->notempty)) {
                $attribut_array['div']['required'] = 'required';
                $attribut_array['label'] .= ' *';
            }
            if (isset($_setting->validate->error)) {
                $attribut_array['class'] = 'error';
            }
            //$attribut_array['class'] = 'error';

            // Marker, ob das Feld im PDF Ausdruck ausgeblendet werden kann
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
                        'hide-'.trim($_setting->model).'0'.trim($_setting->key),
                        array(
                            'checked'=>$val,
                            'id'=>'HiddenField'.trim($_setting->model).'0'.trim($_setting->key),
                            'name'=>'data[HiddenField]['.trim($_setting->model).'][0]['.trim($_setting->key).']',
                            'type'=>'checkbox',
                            'hiddenField'=>false,
                            'div'=>false,
                            'label'=>false,
                            'class'=>'hide_box',
                            'style'=>'position: absolute; top: 1px; right: 1px; display: inline-block;'
                        )
                    );
            }

            // falls ein spezielles Format für das Inputfeld angegeben ist
            if (trim($_setting->fieldtype) > '0') {
                $attribut_array['type'] = trim($_setting->fieldtype);

                if (trim($_setting->fieldtype) == 'radio') {
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
                        if ($radvalue == $data[$model][trim($_setting->key)]) {
                            $attribut_array['value'] = $radkey;
                        }
                    }
                    if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) {
                        $attribut_array['tabindex'] = '-1';
                    }
                }

                if (trim($_setting->fieldtype) == 'checkbox') {
                    if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) {
                        $attribut_array['tabindex'] = '-1';
                    }
                }
            }

            // bei einer speziellen Datenformatierung
            $attribut_array['class'] = null;
            if (trim($_setting->format) > '0') {
                $attribut_array['class'] .= trim($_setting->format) . ' ';
            }
            if (isset($_setting->validate->error)) {
                $attribut_array['class'] .= ' error';
            }
            if (isset($data[$model][trim($_setting->key)]) && $data[$model][trim($_setting->key)] == 1 && trim($_setting->fieldtype) == 'checkbox') {
                $attribut_array['checked'] = 'checked';
                if ($hidable) {
                    $attribut_array['between'] = $hideBox;
                }
            }
            $attribut_array['before']= $revisionlink;
            $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);

            // Wenn ein Dropdownfeld anliegt
            if (trim($_setting->select->model) != '') {
                $options = array();

                if (isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) && count($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) > 0) {
                    //__________________________29.01.2018______________________________
                    if (!empty($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)])) {
                        $modul = trim($_setting->select->roll->modul);
                        $mfield = trim($_setting->select->roll->field);
                        $options = Hash::combine($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)], '{n}.'.$modul.'.id', '{n}.'.$modul.'.'.$mfield);
                        asort($options);
                        foreach ($options as $option => $o_value) {
                            $o_value == $data[$model][trim($_setting->key)] ? $thisselected = $option : '';
                        }


                        if (isset($_setting->select->roll->dependencies) && isset($_setting->dependencies->child)) {
                            foreach ($data['LinkedDropdownData'][trim($_setting->model)][trim($_setting->key)] as $linkeddatas => $linkedvalues) {
                                $linkedvalues [$modul] ['id'] == $thisselected ?
                                                                 $data[$model][trim($_setting->dependencies->child)] = $linkedvalues [$modul] [trim($_setting->select->roll->dependencies->value)]:$data[$model][trim($_setting->dependencies->child)]= '';
                            }
                        }
                    }


                    //__________________________29.01.2018______________________________
                    else {
                        $options = Hash::combine($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)], '{n}.DropdownsValue.id', '{n}.DropdownsValue.discription');

                        $thisselected = null;

                        // Selected suchen
                        if (isset($data[$model][trim($_setting->key)])) {
                            $add = true;
                            foreach ($options as $__key => $__options) {
                                if ($__options == $data[$model][trim($_setting->key)]) {
                                    $thisselected = $__key;
                                    $add = false;
                                    break;
                                }
                            }

                            if ($add && array_search($data[$model][trim($_setting->key)], $options) === false) {
                                $options[$data[$model][trim($_setting->key)]] = $data[$model][trim($_setting->key)];
                                $thisselected = $data[$model][trim($_setting->key)];
                            }

                            /*
                            foreach(preg_split('/[,|]+/', $data[$model][trim($_setting->key)]) as $value) {
                                $options[trim($value)] = trim($value);
                            }
                            */
                            $options = array_unique($options);
                            asort($options);
                        }

                        if (isset($_setting->dependencies->child)) {
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
                        }
                    }
                    $EditSelectLink =
                                $this->Html->link(__('Edit'), array(
                                    'controller' => 'dropdowns',
                                    'action' => 'dropdownindex',
                                    $this->request->projectvars['VarsArray'][0],
                                    $this->request->projectvars['VarsArray'][1],
                                    $this->request->projectvars['VarsArray'][2],
                                    $this->request->projectvars['VarsArray'][3],
                                    $this->request->projectvars['VarsArray'][4],
                                    $this->request->projectvars['VarsArray'][5],

                                    $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'],
                                    $x
                                ), array_merge(
                                    array(
                                        'class'=>'modal dropdown',
                                        'disabled' => $disabled,
                                        'title'=> __('Edit dropdown', true)
                                    ),
                                    Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                )).
                                (
                                    !isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0
                                    ? null
                                    : $this->Html->link(
                                        __('Edit dependent fields'),
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
                                            $x

                                        ),
                                        array_merge(
                                            array(
                                                    'class'=>'modal dependency',
                                                    'disabled' =>$disabled,
                                                    'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
                                            ),
                                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                        )
                                    )
                                );

                    if (!empty($_setting->select->roll->edit)) {
                        foreach ($_setting->select->roll->edit->children() as $_children) {
                            if (trim($_children) == AuthComponent::user('Roll.id')) {
                                $test = $EditSelectLink;
                                break;
                            } else {
                              $test = '<span style="display:none">' .$EditSelectLink . '</span>';
                            }
                        }
                    } else {
                        $test = $EditSelectLink;
                    }

                    $Class = isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null;
                    if (isset($_setting->validate->error)) {
                        $Class .= ' error';
                    }

                    $input = $this->Form->input(
                            trim($_setting->model).'.'.trim($_setting->key),
                            array(
                                'label' => $attribut_array['label'],
                                'disabled' => $disabled,
                                'options' => $options,
                                'selected' => isset($thisselected)? $thisselected : '',
                                'class' => $Class,
                                'empty' => ' ',
                                'before' => $revisionlink,
                                'between' => $hidable ? $hideBox : null,
                                'after' => empty($data['LinkedDropdownData'][trim($_setting->model)] [trim($_setting->key)])? $test : ''
                            )
                        );
                } else {
                    $options = array();

                    $attribut_array['after'] =
                                $this->Html->link(
                                    __('Edit'),
                                    array(
                                            'controller' => 'dropdowns',
                                            'action' => 'dropdownindex',
                                                $this->request->projectvars['VarsArray'][0],
                                                $this->request->projectvars['VarsArray'][1],
                                                $this->request->projectvars['VarsArray'][2],
                                                $this->request->projectvars['VarsArray'][3],
                                                $this->request->projectvars['VarsArray'][4],
                                                $this->request->projectvars['VarsArray'][5],

                                                0,
                                                $x
                                        ),
                                    array_merge(
                                        array(
                                                'class'=>'modal dropdown',
                                                'disabled' => $disabled,
                                                'title'=> __('Add dropdown', true)
                                            ),
                                        Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                    )
                                );

                    if ($hidable) {
                        $attribut_array['between'] = $hideBox;
                    }

                    if (!empty($_setting->select->roll->edit)) {
                        foreach ($_setting->select->roll->edit->children() as $_children) {
                            if (trim($_children) == AuthComponent::user('Roll.id')) {
                                //								$test = $EditSelectLink;
                                break;
                            } else {
                                // do nothing
                                unset($attribut_array['after']);
                                //								$test = null;
                            }
                        }
                    } else {
                        //						$test = $EditSelectLink;
                    }
                    if (isset($_setting->validate->error)) {
                        $attribut_array['class'] = 'error';
                    }
                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                }
            }
            if (isset($_setting->select->moduldata)) {
                $options = array();
                $optionsarray = array();
                if (isset($data['ModulData'])) {
                    $optionsarray = json_decode($data['ModulData'], true);

                    /*   if (isset($optionsarray[trim($_setting->model)][trim($_setting->key)])) {
                           pr ('test');
                       }*/
                }
                if (!empty($optionsarray) && isset($optionsarray [trim($_setting->model)][trim($_setting->key)])) {
                    $options = Hash::combine($optionsarray [trim($_setting->model)][trim($_setting->key)], '{n}.id', '{n}.'.trim($_setting->select->moduldata->field));
                    if (isset($data [trim($_setting->model)][trim($_setting->key)])) {
                        $thisselected = array_search($data [trim($_setting->model)][trim($_setting->key)], $options);
                    }

                    $input = $this->Form->input(
                            trim($_setting->model).'.'.trim($_setting->key),
                            array(
                                'label' => $attribut_array['label'],
                                                            'class' => 'modulselect',
                                                            'disabled' => $disabled,
                                'options' => $options,
                                'selected' => isset($thisselected)? $thisselected : '',
                                //'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                                'empty' => ' ',
                                                            'before' => $revisionlink,
                                'between' => $hidable ? $hideBox : null,

                            )
                        );
                }
            }
            if (isset($_setting->multiselect) && (trim($_setting->fieldtype) == 'text' || trim($_setting->fieldtype) == '')) {
                if (is_array($options)) {
                    $options = array_combine(array_values($options), array_values($options));
                }

                if (isset($data['Multiselects'][trim($_setting->model)][trim($_setting->key)])) {
                    if (!isset($options) || empty($options)) {
                        $options = $data['Multiselects'][trim($_setting->model)][trim($_setting->key)];
                    } else {
                        //$options = Hash::expand(array_merge(Hash::flatten(array(__('Dynamic values', true)=>$options),'$'), Hash::flatten($data['Multiselects'][trim($_setting->model)][trim($_setting->key)],'$')), '$');
                        $options = array();
                    }

                    foreach (preg_split('/[,|\r\n]+/', $data[trim($_setting->model)][trim($_setting->key)]) as $value) {
                        $options[trim($value)] = trim($value);
                    }

                    $options = array_unique($options);
                    //uksort($options, function($a, $b) { return (empty($a) || $a < $b ? -1 : (empty($b) || $a > $b ? 1 : 0)); });
                    ksort($options);
                }

                if (isset($_setting->dependencies->child)) {
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
                }

                $addLink = null;
                if (trim($_setting->select->model != '')) {
                    $addLink = $this->Html->link(__('Edit'), array(
                            'controller' => 'dropdowns',
                            'action' => 'dropdownindex',

                            $this->request->projectvars['VarsArray'][0],
                                                $this->request->projectvars['VarsArray'][1],
                                                $this->request->projectvars['VarsArray'][2],
                                                $this->request->projectvars['VarsArray'][3],
                                                $this->request->projectvars['VarsArray'][4],
                                                $this->request->projectvars['VarsArray'][5],

                            isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0])
                            ? $data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id']
                            : 0,
                            $x,
                            $data['Reportnumber']['id'],
                        ), array_merge(
                            array(
                                'class'=>'modal dropdown',
                                'disabled' => $disabled,
                                'title'=> __('Edit dropdown', true)
                            ),
                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                        )).
                                (
                                    !isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0
                                    ? null
                                    : $this->Html->link(
                                        __('Edit dependent fields'),
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
                                            $x

                                        ),
                                        array_merge(
                                            array(
                                                    'class'=>'modal dependency',
                                                    'disabled' => $disabled,
                                                    'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
                                            ),
                                            Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                        )
                                    )
                                );
                }

                if (!empty($_setting->select->roll->edit)) {
                    foreach ($_setting->select->roll->edit->children() as $_children) {
                        if (trim($_children) == AuthComponent::user('Roll.id')) {
                            break;
                        } else {
                            $addLink = null;
                        }
                    }
                } else {
                }

                if (count($options) > 0) {
                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),

                    // quick and dirty, das muss eleganter im Controller gemacht werden
                    $part = explode("\n", $options[$data[$model][trim($_setting->key)]]);
                    if (count($part) > 1) {
                        unset($options[$data[$model][trim($_setting->key)]]);
                    }

                    $input = $this->Form->input(
                            trim($_setting->model).'.'.trim($_setting->key),
                            array(
                                'label' => $attribut_array['label'],
                                'options' => $options,
                                'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                                'multiple' => true,
                                'disabled' => $disabled,
                                'selected' => array_map('trim', preg_split('/[\r\n,;\|]+/', $data[$model][trim($_setting->key)])),
                                'size' => 7,
                                'onchange'=> '$(this).parent().find("textarea").val($(this).val() == null ? "" : $(this).val().join("\n")); $(this).parent().find("input[type=\'text\']").val($(this).val() == null ? "" : $(this).val().join(", "));',
                                'hiddenField' => false,
                                'between' => $hidable ? $hideBox : null,
    //							'after' =>  $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                                'after' =>  $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                                    'label' => false,
                                                                 'disabled' =>$disabled,
                                    'div' => false,
                                    'value' => $data[$model][trim($_setting->key)]
                                )).
                                $addLink
                            )
                        );
                } else {
                    //					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key), array(
                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), array(
                                    'value' => $data[$model][trim($_setting->key)],
                                    'label' => $attribut_array['label'],
                                    'between' => $hidable ? $hideBox : null,
                                    'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                                    'after' => $addLink
                                ));
                }
            }
            /*
                        // Wenn DataMasked zur Formatierung verwendet werden soll
                        if(isset($_setting->datamask) && trim($_setting->datamask) != ''){
                            $input .= '<script>
                                $(function() {
                                     $("#'.trim($_setting->model).'0'.trim(ucfirst($_setting->key)).'").mask("'.trim($_setting->datamask).'");
                                });
                                </script>';
                        }
            */

            $output .= $input;
            $options = null;
        }

        $output .= '<input id="'.$model.'Id" type="hidden" name="data['.$model.'][id]" value="'.$data[$model]['id'].'">';
        $output .= '</fieldset>';
        //		$output .= '<div id="waitingtest"></div>';
        $output .= '</div>';

        $output .= '<script>
    				$(function() {

    					var OutputHeight = 0;
    					var OutputWidth = 0;
    					var OutputDateWidth = 0;
    					var OutputHeightBS = 0;


    					// beim Datumsformat muss noch die Dynamik eingebaut werden
    //					$(".time").datetimepicker({ format: "H:i:s", scrollInput: false});
    					$(".date").datetimepicker({ format: "Y-m-d", timepicker:false, lang:"de", scrollInput: false});
    					$(".datetime").datetimepicker({ lang:"de", format: "Y-m-d H:i", scrollInput: false});


    					$("form fieldset.fieldset'.trim($setting[0]->model).'").each(function(index, value){

    						$("#" + $(this).attr("id") + " div.input").each(function(){
    							if($(this).height() > OutputHeight) {
    								OutputHeight = $(this).height();
    							}

    							if($(this).width() > OutputWidth) {
    								OutputWidth = $(this).width();
    							}

    						});

    						OutputWidth = OutputWidth + 10;
    						$("#" + $(this).attr("id") + " div.input").css("height",OutputHeight+"px");
    //						$("#" + $(this).attr("id") + " div.input").css("width",OutputWidth+"px");

    						OutputHeight = 0;
    						OutputWidth = 0;

    					});

    					$("#ReportRtGenerally0WaitingStart").blur(function(){

    						var data = $("#fakeform").serializeArray();
    						var includecontainer = "WaitingTime" + $(this).attr("id");

    						$("#" + includecontainer).remove();

    						data.push({name: "ajax_true", value: 1});
    						data.push({name: "date", value: $(this).val()});
    						data.push({name: "examinierer1", value: $("#ReportRtGenerally0Examiner").val()});
    						data.push({name: "examinierer2", value: $("#ReportRtGenerally0Examinierer2").val()});
    						data.push({name: "id", value: '.$data['Reportnumber']['id'].'});

    						$("#" + $(this).attr("id")).after( "<span id=\"" + includecontainer + "\"></span>" )

    						$.ajax({
    							type	: "POST",
    							cache	: false,
    							url	: "'.Router::url(array('controller' => 'invoices', 'action' => 'waitingtest')).'",
    							data	: data,
    							success: function(data) {
    		    					$("#" + includecontainer).html(data);
    		    					$("#" + includecontainer).show();
    							}
    						});
    						return false;
    					});
    				});

    		</script>';
        $output .= '<script>
                                                        $(document).ready(function(){
                                                            $("select.hasDependencies").on("change", function() {



                                                            });

                                                        });
                                                            </script>';


        return $output;
    }

        public function EditModulDataSettings($data, $setting, $lang, $testingmethods, $step='Order')
        {

            // wenn keine Daten vorhanden sind
            if ($setting == null) {
                return false;
            }

            // Falls ein PDF eingelesen wurde, die Daten hier bereitstellen
            if (isset($this->_View->viewVars['texts'])) {
                $texts = $this->_View->viewVars['texts'];
            }

            $fieldset_count = 0;
            $x = 0;
            $output  = null;
            $output .= '<fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';
            $output .= $this->Form->input('id');

            if (is_array($testingmethods) && isset($testingmethods['testingcomp_id']) && isset($testingcomps)) {
                $output .= $this->Form->input(
                    'testingcomp_id',
                    array(
                    'options' => $testingcomps['testingcomp_id']
                    )
                );
            }

            if (is_array($testingmethods) && isset($testingmethods['Testingmethod'])) {
                $output .= $this->Form->input(
                    $step.'Testingmethod',
                    array(
                    'label' => __('choose category', true),
                                    'empty' => ' ',
                    'multiple' => false,
                    'options' => $testingmethods['Testingmethod'],
                                    'selected' => $this->request->projectvars['VarsArray'][15]
                    )
                );
            }

            foreach ($setting as $_key => $_setting) {
                if (trim($_setting->output->screen) != 1) {
                    continue;
                }

                $x++;
                $model = trim($_setting->model);
                if (isset($texts) && !empty($texts)) {
                    $source = array('x'=>array(), 'y'=>array(), 'previous'=>null, 'next'=>null);

                    if (isset($_setting->source->x)) {
                        $source['x'] = array_filter(explode(' ', trim($_setting->source->x)));
                        if (count($source['x']) == 1) {
                            $source['x'][1] = $source['x'][0];
                        }
                        sort($source['x']);
                    }
                    if (isset($_setting->source->y)) {
                        $source['y'] = array_filter(explode(' ', trim($_setting->source->y)));
                        if (count($source['y']) == 1) {
                            $source['y'][1] = $source['y'][0];
                        }
                        $source['y'] = array_map(function ($elem) use ($texts) {
                            return $elem < 0 ? $elem+count($texts) : $elem;
                        }, $source['y']);
                        sort($source['y']);
                    }
                    if (isset($_setting->source->previous)) {
                        $source['previous'] = array_filter(explode(' ', trim($_setting->source->previous)));
                    }
                    if (isset($_setting->source->next)) {
                        $source['next'] = array_filter(explode(' ', trim($_setting->source->next)));
                    }
                }
                $discription = null;
                $input = null;

                if ($_setting->fieldset != '' && $_setting->fieldset == 1) {
                    $fieldset_count++;
                    $input = '</fieldset><fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';
                    $output .= $input;
                }

                if ($_setting->legend->$lang != '' && $_setting->legend->$lang != '') {
                    $input = '<legend class="headline">'.$_setting->legend->$lang.'</legend>';
                    $output .= $input;
                }

                $attribut_array = array();
                if (isset($_setting->autocomplete)) {
                  $attribut_array ['autocomplete'] = 'autocomplete';
                  $attribut_array ['autocompletemodel'] = $_setting->autocomplete->model;
                  $attribut_array ['autocompletefield'] = $_setting->autocomplete->field;

                }
                // Die Werte aus der Datenbank in das Formularfeld eintragen
                //			$attribut_array['value'] = $data[$model][trim($_setting->key)];
                //			$attribut_array['title'] = __('Double click to insert specialchars', true);

                // Leerzeichen im Labeltag müssen gegen geschützte Leerzeichen getauscht werden
                $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);

                if ($discription != null) {
                    $attribut_array['label'] = $discription;
                }

                // falls ein spezielles Format für das Inputfeld angegeben ist
                if (trim($_setting->fieldtype) > '0') {
                    $attribut_array['type'] = trim($_setting->fieldtype);

                    if (trim($_setting->fieldtype) == 'radio') {
                        $radiooptions = $this->radiodefault;

                        if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {
                            $radiooptions = array();

                            foreach ($_setting->radiooption->value as $_radiooptions) {
                                array_push($radiooptions, trim($_radiooptions));
                            }
                        }
                        $attribut_array['legend'] = $discription;
                        $attribut_array['options'] = $radiooptions;
                    }
                }

                // bei einer speziellen Datenformatierung
                if (trim($_setting->format) > '0') {
                    $attribut_array['class'] = trim($_setting->format);
                }

                //			pr(trim($_setting->key));
                // Falls Daten aus einem eingelesenen PDF vorhanden sind, testen, ob ein Wert übernommen werden soll
                if (!empty($texts)) {
                    $txtLines = $texts;
                    $attribut_array['value'] = '';
                    // Wenn Y-Werte angegeben sind, dann nur in diesen Zeilen suchen
                    if (!empty($source['y'])) {
                        $txtLines = array_intersect_key($texts, array_flip(array_filter(array_keys($texts), function ($line) use ($source) {
                            return $source['y'][0] <= $line && $line <= $source['y'][1];
                        })));
                    }

                    if (!empty($source['x'])) {
                        if (!empty($txtLines)) {
                            foreach ($txtLines as $txtLine) {
                                foreach ($txtLine as $txtPos=>$txtValue) {
                                    if ($source['x'][0] <= $txtPos && $txtPos <= $source['x'][1]) {
                                        $attribut_array['value'] = trim($attribut_array['value'].' '.$txtValue);
                                    }
                                }
                            }
                        }
                    } elseif (!empty($source['previous']) || !empty($source['next'])) {
                        $insert = false;
                        if (!empty($txtLines)) {
                            foreach ($txtLines as $txtLine) {
                                foreach ($txtLine as $txtValue) {
                                    if (!empty($source['next'])) {
                                        foreach ($source['next'] as $srcNext) {
                                            if (strpos($txtValue, $srcNext) !== false) {
                                                if ($insert) {
                                                    $insert = false;
                                                    break;
                                                }

                                                if (empty($source['prev'])) {
                                                    break 3;
                                                }
                                            }
                                        }
                                    }

                                    if ($insert || empty($source['previous'])) {
                                        $attribut_array['value'] = trim($attribut_array['value'].' '.$txtValue);
                                    }
                                    // Wenn Vorheriger Wert nicht angegeben ist, dann alles bis zum next-Treffer mitnehmen
                                    if (empty($source['previous'])) {
                                        $insert = true;
                                    }
                                    // Ansonsten einen der angegebenen Begrenzer suchen
                                    else {
                                        foreach ($source['previous'] as $srcPrev) {
                                            if (strpos($txtValue, $srcPrev) !== false) {
                                                $insert = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } elseif (!empty($source['y'])) {
                        $attribut_array['value'] = trim(join(PHP_EOL, array_map(function ($line) {
                            return join(' ', array_map('trim', $line));
                        }, $txtLines)));
                    }
                }

                // Formularfeld plazieren
                if (trim($_setting->select->model == '')) {
                    $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                }

                // Wenn ein Dropdownfeld anliegt
                if (trim($_setting->select->model) != '') {
                    $VarsArray = $this->_View->viewVars['VarsArray'];
                    $VarsArray[6] = isset($this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['Dropdown']['id'])
                    ? $this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['Dropdown']['id']
                    : (
                        isset($this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id'])
                            ? $this->request->data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)][0]['DropdownsValue']['dropdown_id']
                            : 0
                    );
                    $VarsArray[7] = $x;
                    /*
                    if(isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) && count($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]) > 0){
                    $options = Hash::combine($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)], '{n}.DropdownsValue.id', '{n}.DropdownsValue.discription');
                    }
                    */

                    if (isset($data['Dropdowns'][$step][trim($_setting->key)]) && count($data['Dropdowns'][$step][trim($_setting->key)]) > 0) {
                        $options = array();
                        if (!empty($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)])) {
    //                      mit diesem Code bleibt das OptionArray leer, Phillip, deshalb nochmal die if-Abfrage
                            $options = Hash::combine($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)], '{n}.DropdownsValue.id', '{n}.DropdownsValue.discription');

                            if (isset($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]['DropdownsValue'])) {
                                $options = Hash::combine($data['DropdownInfo'][trim($_setting->model)][trim($_setting->key)]['DropdownsValue'], '{n}.DropdownsValue.id', '{n}.DropdownsValue.discription');
                            }
                        }
                        /*
                                            $options = $data['Dropdowns'][$step][trim($_setting->key)];

                                            if(isset($this->request->data[$step]) && isset($this->request->data[$step][trim($_setting->key)])){
                                                if(array_search($this->request->data[$step][trim($_setting->key)], $options) === false) {
                                                    $options += array($this->request->data[$step][trim($_setting->key)]=>$this->request->data[$step][trim($_setting->key)]);
                                                }
                                            }
                        */
                        $thisselected = null;

                        // Selected suchen
                        if (isset($data[$model][trim($_setting->key)])) {
                            foreach ($options as $__key => $__options) {
                                if ($__options == $data[$model][trim($_setting->key)]) {
                                    $thisselected = $__key;
                                    break;
                                }
                            }
                        }

                        if (isset($_setting->dependencies->child)) {
                            $children = (array)($_setting->dependencies->children());
                            $locale = $this->_View->viewVars['locale'];
                            $dependencies = array_map(function ($key) use ($setting, $locale) {
                                $key = array_filter($setting, function ($__setting) use ($key) {
                                    return trim($__setting->key) == $key;
                                });

                                return trim(reset($key)->discription->$locale);
                            }, (array)$children['child']);
                        }

                        if (empty($options)) {
                            $attribut_array['after'] = $this->Html->link(
                                __('Edit'),
                                array_merge(
                                    array(
                                        'controller' => 'dropdowns',
                                        'action' => 'dropdownindex'
                                    ),
                                    $VarsArray
                                ),
                                array_merge(
                                    array(
                                        'class'=>'mymodal dropdown',
                                        'disabled' => isset($this->request->data[$step]['status'])&& $this->request->data[$step]['status'] != 0,
                                        'title'=> __('Edit dropdown', true)
                                    ),
                                    Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                )
                            );

                            $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                        } else {
                            //						$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),

                            $input = $this->Form->input(
                                trim($_setting->model).'.'.trim($_setting->key),
                                array(
                                        'label' => $attribut_array['label'],
                                        'options' => $options,
                                        'selected' => $thisselected,
                                        'class' => isset($_setting->dependencies->child) && $_setting->dependencies->child->count() != 0 ? 'hasDependencies' : null,
                                        'empty' => ' ',
                                        'after' => $this->Html->link(
                                            __('Edit'),
                                            array_merge(
                                                array(
                                                        'controller' => 'dropdowns',
                                                        'action' => 'dropdownindex'
                                                    ),
                                                $VarsArray
                                            ),
                                            array_merge(
                                                array(
                                                                'class'=>'mymodal dropdown',
                                                                'disabled' => isset($this->request->data[$step]['status'])&& $this->request->data[$step]['status'] != 0,
                                                                'title'=> __('Edit dropdown', true)
                                                        ),
                                                Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                            )
                                        ).(
                                            !isset($_setting->dependencies->child) || $_setting->dependencies->child->count() == 0
                                                        ? null
                                                        : $this->Html->link(
                                                            __('Edit dependent fields'),
                                                            array_merge(
                                                                array(
                                                                        'controller' => 'dependencies',
                                                                        'action' => 'index'
                                                                    ),
                                                                $VarsArray
                                                            ),
                                                            array_merge(
                                                                array(
                                                                                'class'=>'mymodal dependency',
                                                                                'disabled' => isset($this->_View->viewVars['reportnumber']['Reportnumber']['status']) && $this->_View->viewVars['reportnumber']['Reportnumber']['status'] != 0,
                                                                                'title'=> __('Edit dependent fields').':'.PHP_EOL.join(PHP_EOL, $dependencies)
                                                                        ),
                                                                Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                                            )
                                                        )
                                        )
                                )
                            );
                        }
                    } else {
                        $options = array();
                        //if($this->request->data)
                        if (isset($this->request->data[$step][trim($_setting->key)])) {
                            $attribut_array['value'] = $value = preg_split('/[,|\r\n]+/', $this->request->data[$step][trim($_setting->key)]);
                            foreach ($value as $_value) {
                                if (array_search(trim($_value), $options) === false) {
                                    $options[trim($_value)] = trim($_value);
                                }
                            }
                        }

                        if (!empty($options)) {
                            $attribut_array['options'] = $options;
                            $attribut_array['empty'] = ' ';
                            $attribut_array['type'] = 'text';
                            $attribut_array['after'] = $this->Html->link(
                                __('Edit'),
                                array_merge(
                                    array(
                                        'controller' => 'dropdowns',
                                        'action' => 'dropdownindex'
                                    ),
                                    $VarsArray
                                ),
                                array_merge(
                                    array(
                                                'class'=>'mymodal dropdown',
                                                'disabled' => isset($this->request->data[$step]['status'])&& $this->request->data[$step]['status'] != 0,
                                                'title'=> __('Edit dropdown', true)
                                        ),
                                    Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                                )
                            );
                        }

                        $attribut_array['after'] = $this->Html->link(
                            __('Edit'),
                            array_merge(
                                array(
                                        'controller' => 'dropdowns',
                                        'action' => 'dropdownindex'
                                    ),
                                $this->request->projectvars['VarsArray']
                            ),
                            array_merge(
                                array(
                                                'class'=>'mymodal dropdown',
                                                'disabled' => isset($this->_View->viewVars['reportnumber']['Reportnumber']['status']) && $this->_View->viewVars['reportnumber']['Reportnumber']['status']!= 0,
                                                'title'=> __('Add dropdown', true)
                                        ),
                                Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks') ? array('tabindex'=>'-1') : array()
                            )
                        );
                        ///					$input = $this->Form->input(trim($_setting->model).'.0.'.trim($_setting->key),$attribut_array);
                        $input = $this->Form->input(trim($_setting->model).'.'.trim($_setting->key), $attribut_array);
                    }
                }

                $output .= $input;
                if (isset($_setting->defaultval)&& $_setting->fieldtype == 'radio') {
                    $radiodefaultval [trim($_setting->model). ucfirst(trim($_setting->key))] = trim($_setting->defaultval);
                }
            }

            $output .= '<input id="Ordertopproject_id" type="hidden" name="data[Order][topproject_id]" value="'.$this->request->projectID.'">';
            $output .= '</fieldset>';
            $output .= '<script>
    					$(function() {

    					var OutputHeight = 0;
    					var OutputWidth = 0;
    					var OutputDateWidth = 0;
    					var OutputHeightBS = 0;

    					$("#dialog div.textarea").width($("form.dialogform").width() - 30);
    					$("#dialog div.textarea textarea").css("height","2em");
    					$("#dialog div.textarea textarea").css("width","99%");

    					// beim Datumsformat muss noch die Dynamik eingebaut werden
              /*
    					$(".time").datetimepicker({ format: "H:i", datepicker: false, scrollInput: false});
    					$(".date").datetimepicker({ format: "Y-m-d", timepicker:false, lang:"de", scrollInput: false});
    					$(".datetime").datetimepicker({
    						lang:"de",
    						format: "Y-m-d H:i",
    						scrollInput: false
    					});
              */
    					$("form fieldset.fieldset'.trim($setting[0]->model).'").each(function(index, value){
                $(this).width() = 222;
    						$("#" + $(this).attr("id") + " div.input").each(function(){
    							if($(this).height() > OutputHeight) {
    								OutputHeight = $(this).height();
    							}

    							if($(this).width() > OutputWidth) {
    								OutputWidth = $(this).width();
    							}

    						});

    						OutputWidth = OutputWidth + 10;
    						$("#" + $(this).attr("id") + " div.input").css("height",OutputHeight+"px");

    						OutputHeight = 0;
    						OutputWidth = 0;

    					});
    				});

$("input").css({
    width: "-moz-available",

});


    				</script>';

            return $output;
        }
}
