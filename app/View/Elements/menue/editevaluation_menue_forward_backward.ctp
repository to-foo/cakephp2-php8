<?php
return;
$Description = isset($this->request->data['paginationOverview']['current_weld'])? key($this->request->data['paginationOverview']['current_weld']) : '';

if ($hasPositionColum === true) {
    $Position = $this->request->data['paginationOverview']['current_position'][$this->request->projectvars['evalId']];
    $this_first_weld_id = $this->request->data['paginationOverview']['weld_struktur_id'][$Description];
}

if ($this->request->projectvars['VarsArray'][6] == 1) {
    $naht_dupli_desc = __('Naht').' "'.$Description.'" '.__('duplizieren');
    $naht_del_desc = __('Naht').' "'.$Description.'" '.__('löschen');
} else {
    $naht_dupli_desc = __('Nahtbereich').' "'.isset($Position) && !empty($Position)? $Position : ''.'" '.__('von').' '.__('Naht').' "'.$Description.'" '.__('duplizieren');
    $naht_del_desc = __('Nahtbereich').' "'.isset($Position) && !empty($Position)? $Position : ''.'" '.__('von').' '.__('Naht').' "'.$Description.'" '.__('löschen');
}

if (isset($this->request->projectvars['evalId'])&& $this->request->projectvars['evalId'] > 0) {

    if ($this->request->projectvars['weldedit'] == 0 && $hasPositionColum == true) {
        if (isset($this->request->data['paginationOverview']['paging_position'][$this->request->projectvars['evalId']]['prev_position'])) {
            $key_prev_position = key($this->request->data['paginationOverview']['paging_position'][$this->request->projectvars['evalId']]['prev_position']);
            $desc_prev_position = __('Go to', true) . ' ' . $this->request->data['paginationOverview']['position_struktur_weld'][$key_prev_position][0].'/'.$this->request->data['paginationOverview']['position_struktur_weld'][$key_prev_position][1];

            echo $this->Html->Link(
                $desc_prev_position,
                array('action' => 'editevalution',
              $this->request->projectvars['projectID'],
              $this->request->projectvars['cascadeID'],
              $this->request->projectvars['orderID'],
              $this->request->projectvars['reportID'],
              $this->request->projectvars['reportnumberID'],
              $key_prev_position,
              $this->request->projectvars['VarsArray'][6]
            ),
                array(
              'class' => 'icon icon_prev ajax',
              'title' => $desc_prev_position
            )
            );
        }

        if (isset($this->request->data['paginationOverview']['paging_position'][$this->request->projectvars['evalId']]['next_position'])) {
            $key_next_position = key($this->request->data['paginationOverview']['paging_position'][$this->request->projectvars['evalId']]['next_position']);
            $desc_next_position = __('Go to', true) . ' ' . $this->request->data['paginationOverview']['position_struktur_weld'][$key_next_position][0].'/'.$this->request->data['paginationOverview']['position_struktur_weld'][$key_next_position][1];

            echo $this->Html->Link(
          $desc_next_position,
          array('action' => 'editevalution',
            $this->request->projectvars['projectID'],
            $this->request->projectvars['cascadeID'],
            $this->request->projectvars['orderID'],
            $this->request->projectvars['reportID'],
            $this->request->projectvars['reportnumberID'],
            $key_next_position,
            $this->request->projectvars['VarsArray'][6]
          ),
          array(
            'class' => 'icon icon_next ajax',
            'title' => $desc_next_position
          )
      );
        }
    }
    if ($this->request->projectvars['weldedit'] == 1) {
        if ($hasPositionColum == true) {
            if (isset($this->request->data['paginationOverview']['paging_weld'][$this_first_weld_id]['prev_weld'])) {
                $key_prev_position = key($this->request->data['paginationOverview']['paging_weld'][$this_first_weld_id]['prev_weld']);
                $desc_prev_position = __('Go to') . ' ' . $this->request->data['paginationOverview']['paging_weld'][$this_first_weld_id]['prev_weld'][key($this->request->data['paginationOverview']['paging_weld'][$this_first_weld_id]['prev_weld'])];

                echo $this->Html->Link(
                    $desc_prev_position,
                    array('action' => 'editevalution',
            $this->request->projectvars['projectID'],
            $this->request->projectvars['cascadeID'],
            $this->request->projectvars['orderID'],
            $this->request->projectvars['reportID'],
            $this->request->projectvars['reportnumberID'],
            $key_prev_position,
            $this->request->projectvars['VarsArray'][6]
          ),
                    array(
            'class' => 'icon icon_prev ajax',
            'title' => $desc_prev_position
          )
                );
            }
        } elseif ($hasPositionColum == false) {
            if (isset($this->request->data['paginationOverview']['paging_weld'][$this->request->projectvars['evalId']]['prev_weld'])) {
                $key_prev_position = key($this->request->data['paginationOverview']['paging_weld'][$this->request->projectvars['evalId']]['prev_weld']);
                $desc_prev_position = __('Go to') . ' ' . $this->request->data['paginationOverview']['paging_weld'][$this->request->projectvars['evalId']]['prev_weld'][key($this->request->data['paginationOverview']['paging_weld'][$this->request->projectvars['evalId']]['prev_weld'])];

                echo $this->Html->Link(
                    $desc_prev_position,
                    array('action' => 'editevalution',
            $this->request->projectvars['projectID'],
            $this->request->projectvars['cascadeID'],
            $this->request->projectvars['orderID'],
            $this->request->projectvars['reportID'],
            $this->request->projectvars['reportnumberID'],
            $key_prev_position,
            $this->request->projectvars['VarsArray'][6]
          ),
                    array(
            'class' => 'icon icon_prev ajax',
            'title' => $desc_prev_position
          )
                );
            }
        }
        if ($hasPositionColum == true) {
            if (isset($this->request->data['paginationOverview']['paging_weld'][$this_first_weld_id]['next_weld'])) {
                $key_next_position = key($this->request->data['paginationOverview']['paging_weld'][$this_first_weld_id]['next_weld']);
                $desc_next_position = __('Go to') . ' ' . $this->request->data['paginationOverview']['paging_weld'][$this_first_weld_id]['next_weld'][key($this->request->data['paginationOverview']['paging_weld'][$this_first_weld_id]['next_weld'])];

                echo $this->Html->Link(
                    $desc_next_position,
                    array('action' => 'editevalution',
            $this->request->projectvars['projectID'],
            $this->request->projectvars['cascadeID'],
            $this->request->projectvars['orderID'],
            $this->request->projectvars['reportID'],
            $this->request->projectvars['reportnumberID'],
            $key_next_position,
            $this->request->projectvars['VarsArray'][6]
          ),
                    array(
            'class' => 'icon icon_next ajax',
            'title' => $desc_next_position
          )
                );
            }
        } elseif ($hasPositionColum == false) {
            if (isset($this->request->data['paginationOverview']['paging_weld'][$this->request->projectvars['evalId']]['next_weld'])) {
                $key_next_position = key($this->request->data['paginationOverview']['paging_weld'][$this->request->projectvars['evalId']]['next_weld']);
                $desc_next_position = __('Go to') . ' ' . $this->request->data['paginationOverview']['paging_weld'][$this->request->projectvars['evalId']]['next_weld'][key($this->request->data['paginationOverview']['paging_weld'][$this->request->projectvars['evalId']]['next_weld'])];

                echo $this->Html->Link(
                    $desc_next_position,
                    array('action' => 'editevalution',
            $this->request->projectvars['projectID'],
            $this->request->projectvars['cascadeID'],
            $this->request->projectvars['orderID'],
            $this->request->projectvars['reportID'],
            $this->request->projectvars['reportnumberID'],
            $key_next_position,
            $this->request->projectvars['VarsArray'][6]
          ),
                    array(
            'class' => 'icon icon_next ajax',
            'title' => $desc_next_position
          )
                );
            }
        }
    }
}
