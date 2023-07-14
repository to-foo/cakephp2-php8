<?php


foreach ($welds as $dataArray) {

  $dataArray = $dataArray[key($dataArray)];

  if(count($dataArray['weld']) == 0) continue;

  echo '<tr>';
  echo '<td class="weldhead" colspan="'. ($x + 1) .'">';

  echo $this->Html->link($dataArray['weld'][0]['description'],
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

    if ($dataArray['development'] == 0) $developmentClass = array('development_open',__('marked as not processed'));

    if ($dataArray['development'] == 1) {

      $developmentClass = array('development_rep',__('marked as error'));

      echo $this->Html->link(__('add progress', true),
        array(
          'controller' => 'developments',
          'action' => 'progressadd',
          $this->request->projectvars['VarsArray'][0],
          $this->request->projectvars['VarsArray'][1],
          $this->request->projectvars['VarsArray'][2],
          $this->request->projectvars['VarsArray'][3],
          $this->request->projectvars['VarsArray'][4],
          $dataArray['id'],
          '1'
        ),
        array(
          'class'=>'addlink modal',
          'title' =>''
        )
      );
  }

  if ($dataArray['development'] == 2) $developmentClass = array('development_ok',__('marked as processed'));

  echo $this->Html->link(__('examination development', true),
    array(
      'controller' => 'developments',
      'action' => 'change',
      $this->request->projectvars['VarsArray'][0],
      $this->request->projectvars['VarsArray'][1],
      $this->request->projectvars['VarsArray'][2],
      $this->request->projectvars['VarsArray'][3],
      $this->request->projectvars['VarsArray'][4],
      $dataArray['id'],
      '1'
    ),
    array(
      'class'=>'development_evalution icon modal '.$developmentClass[0],
      'id'=>'this_development_'.$dataArray['id'],
      'title' => $developmentClass[1]
      )
    );
  }
}
echo '</td>';
echo '</tr>';

foreach ($dataArray['weld'] as $weld) {

  $class = array();

  if ($i++ % 2 == 0) $class[] = 'altrow';
  if (isset($weld['result']) && isset($setting->result->radiooption->value[(int)$weld['result']]) && strtolower($setting->result->radiooption->value[(int)$weld['result']]) == 'ne') $class[] = 'error';

  echo '<tr '.(!empty($class) ? ' class="'.join(' ', $class).'"' : null).'>';

  $colspan = count($dataArray['weld']) == 1 ? ' colspan="2"' : null;

    foreach ($evaluationoutput as $_key => $_setting) {

      if (trim($_setting->showintable) != 1) continue;
//      pr($_setting);

      if (trim($_setting->key) == 'description') {

          if (count($dataArray['weld']) > 1) echo '<td></td><td>';
          else echo '<td'.$colspan.'>';

      } else {
          echo '<td>';
      }

      echo '<span class="discription_mobil">';
      echo trim($_setting->discription->$locale) . ': ';
      echo '</span>';

      if (trim($_setting->fieldtype) == 'radio') {

          if (isset($_setting->radiooption) && count($_setting->radiooption->value) > 0) {

              $radiooptions = array();

              foreach ($_setting->radiooption->value as $_radiooptions) {
                  array_push($radiooptions, trim($_radiooptions));
              }
          }

          echo $radiooptions[(int)$weld[trim($_setting->key)]];

      } else {

        $value = null;
        $value = utf8_decode($weld[trim($_setting->key)]);

        if (trim($_setting->fieldtype) == 'checkbox') {

          if ($weld[trim($_setting->key)] == 1 || $weld[trim($_setting->key)] == 'true') $value = 'X';
          else $value = '-';

        }

        if (trim($_setting->key) == 'description') {

          $hasmenu = null;

          $linkclass = 'contexmenu_weldposition';

          $value  = utf8_decode($weld[trim($_setting->key)]);

          if (count($dataArray['weld']) == 1) {

            if (Configure::read('DevelopmentsEnabled') == true) {

              if (isset($dataArray['development'])) {

                if ($dataArray['development'] == 0) $developmentClass = array('development_open',__('marked as not processed'));
                if ($dataArray['development'] == 1) $developmentClass = array('development_rep',__('marked as error'));
                if ($dataArray['development'] == 2) $developmentClass = array('development_ok',__('marked as processed'));

                echo $this->Html->link(__('examination development', true),
                  array(
                    'controller' => 'developments',
                    'action' => 'change',
                    $this->request->projectvars['VarsArray'][0],
                    $this->request->projectvars['VarsArray'][1],
                    $this->request->projectvars['VarsArray'][2],
                    $this->request->projectvars['VarsArray'][3],
                    $this->request->projectvars['VarsArray'][4],
                    $dataArray['id'],
                    '1'
                  ),
                  array(
                    'class'=>'icon mymodal '.$developmentClass[0],
                    'id'=>'development_evalution',
                    'title' => $developmentClass[1]
                  )
                );
              }
            }

            $linkclass = 'hasmenu1';

          }

          echo '<span class="'.$linkclass.'">';

          foreach ($evaluationoutput as $_key => $_value) {
            if(trim($_value->key) == 'position'){

              if(!empty($weld[trim($_value->key)])) {

                $value .= '/' . $weld[trim($_value->key)];
                echo  $this->Html->link($value,
                array(
                  'action' => 'editevalution',
                  $this->request->projectvars['projectID'],
                  $this->request->projectvars['cascadeID'],
                  $this->request->projectvars['orderID'],
                  $this->request->projectvars['reportID'],
                  $this->request->projectvars['reportnumberID'],
                  $weld['id']
                ),
                array(
                  'class' => 'round icon_edit ajax testingreportlink',
                )
              );
            }
              break;

            }
          }



          echo '</span>';

        } else {

          echo $value;

        }
      }

      echo '</td>';

    }

    echo '</tr>';
}

}

?>
