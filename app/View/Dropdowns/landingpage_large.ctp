<h3><?php echo __('Dropdown Manager', true); ?></h3>
<div class="users index inhalt">
<div class="quicksearch">
<?php
echo $this->Html->link(__('Open dropdown manager', true),
    array(
        'controller' => 'dropdowns',
        'action' => 'master',
    ),
    array(
        'class' => 'round ajax',
        'title' => __('Open dropdown manager', true),
    )
);

echo $this->element('searching/search_quick_dropdown', array('target_id' => 'id', 'targedaction' => 'masteredit', 'action' => 'quicksearch', 'minLength' => 2, 'discription' => __('Dropdown name', true)));
?>

</div>
<table class="advancetool table_infinite_sroll">
<?php
foreach ($DropdownsMaster as $key => $data) {

    echo '<tr class="">';
    echo '<td>';
    echo '<h2 class="accordion_' . $key . '">' . $data['desc'] . '</h2>';

    echo '<div class="accordion accordion_' . $key . '">';

    foreach ($data['data'] as $_key => $_data) {

        echo '<h3>' . $_data['desc'] . '</h3>';
        echo '<div><ul>';

        foreach ($_data['data'] as $__key => $__data) {

            if ($__data['DropdownsMaster']['status'] == 1) {
                $class = 'deactive';
            } else {
                $class = null;
            }

            $this->request->projectvars['VarsArray'][15] = $__data['DropdownsMaster']['id'];

            echo '<li class="' . $class . '">';
            echo '<span>';
            echo $this->Html->link($__data['DropdownsMaster']['name'], array_merge(array('action' => 'masteredit'), $this->request->projectvars['VarsArray']), array('class' => 'round ajax'));
            echo '</span>';
            echo '<span>' . $__data['DropdownsMaster']['description'] . '</span>';

            if(isset($__data['Testingcomp'])) echo '<br>' . __('Created by') . ' '. $__data['Testingcomp']['name'];

            echo '</li>';

        }

        echo '</ul></div>';

    }

    echo '</div>';
    echo '</td>';
    echo '</tr>';

}
;
?>
</table>
</div>
<?php
echo $this->element('dropdowns/js/accordion');
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
