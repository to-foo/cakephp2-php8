<?php
class AdditionsHelper extends AppHelper
{

    public $helpers = array('Html', 'Form', 'Ajax', 'Javascript', 'Navigation');

    // Diese Funktion funktioniert ähnlich der Autoload-Funktion von php
    public function Show($xml)
    {

        $verfahren = $this->request->verfahren;
        $Verfahren = ucfirst($verfahren);

        if (Configure::check('Additions') === false) {
            return false;
        } else {
            $Additions = Configure::read('Additions');
        }

        $ReportAdditions = 'Report' . $Verfahren . 'Additions';

        if (empty($xml->$ReportAdditions)) {
            return false;
        } else {
            $AdditionsXml = $xml->$ReportAdditions;
        }

        if (count($AdditionsXml->children()) == 0) {
            return false;
        }

        if (!is_array($Additions)) {
            return false;
        }

        foreach ($Additions as $_key => $_Additions) {
            if (!method_exists($this, $_key)) {
                continue;
            }

            if (empty($AdditionsXml->$_key)) {
                continue;
            }

            if (!is_object($AdditionsXml->$_key)) {
                continue;
            }

            echo call_user_func(array($this, $_key), $AdditionsXml->$_key, $_key);

        }
    }

    public function ImageSelection($AdditionsXml, $name)
    {

        if (!isset($this->request->data['Reportnumber'])) {
            return false;
        }

        if (!isset($this->_View->viewVars[$name])) {
            return false;
        }

        echo '<div class="ImageSelectionArea" id="ImageSelectionArea" style="display: none;">';
        echo '<div class="svg_view">';
        foreach ($this->_View->viewVars[$name] as $_key => $_name) {
            echo '<div class="svg">';
            echo $_name['file_content'];
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';

        echo '
		<script type="text/javascript">
			$(document).ready(function(){
//				$("div.svg_view div.svg svg").attr("width","auto");
//				$("div.svg_view div.svg svg").attr("height","100px");

				$("div.svg_view div.svg svg line").attr("stroke","red");
				$("div.svg_view div.svg svg line").attr("stroke-width","2");
				$("div.svg_view div.svg svg path").attr("stroke","red");
				$("div.svg_view div.svg svg path").attr("stroke-width","2");
				$("div.svg_view div.svg svg polygon").attr("stroke","red");
				$("div.svg_view div.svg svg polygon").attr("stroke-width","2");
				$("div.svg_view div.svg svg g polygon").attr("fill","red");

			});
		</script>';

    }

    public function PositioningTable($AdditionsXml, $name)
    {
        if (!isset($this->request->data['Reportnumber'])) {
            return false;
        }

        if (!isset($this->_View->viewVars['PositioningTable'])) {
            return false;
        }

        $Model = trim($AdditionsXml->model);
        $position_table = 0;
        $checked = false;
        $description = __('printing deactive', true);

        if (isset($this->request->data[$Model]['position_table'])) {
            if ($this->request->data[$Model]['position_table'] == 1) {
                $checked = true;
                $position_table = 1;
                $description = __('printing active', true);
            } else {
                $checked = false;
                $position_table = 0;
                $description = __('printing deactive', true);
            }
        }

        echo '<div class="PositionTableArea addition" id="PositionTableArea" style="display: none;">';

        $options = array(
            0 => __('printing deactive', true),
            1 => __('printing active', true),
        );
        $attributes = array(
            'legend' => false,
            'value' => $position_table,
            'id' => $Model . 'PositionTable',
            'name' => 'data[' . $Model . '][position_table]',
        );

        echo $this->Form->radio('printing', $options, $attributes);
/*
echo $this->Form->input($description,
array(
'id' => $Model . 'PositionTable',
'name'=>'data['.$Model.'][position_table]',
'label' => false,
'selected' => $position_table,
'options' =>
array(
0 => __('printing deactive',true),
1 => __('printing active',true)
)
)
);
 */
        echo '<table class="editable">';
        echo $this->Html->tableHeaders($this->_View->viewVars['PositioningTable']['head']);
        echo $this->Html->tableCells($this->_View->viewVars['PositioningTable']['body']);
        echo '</table>';

        echo '
    		<script type="text/javascript">
    			$(document).ready(function(){
    				$("span.edit_in_table").editable(" ' . Router::url(array('action' => 'editableUp')) . '", {
    					indicator : "<img src=\'img/indicator.gif\'>",
    					tooltip   : "' . __('Click to edit') . '",
    					onblur : "submit",
    					placeholder : "&nbsp;",
    					cssclass : "editables",
    					method : "POST",
    					callback : function(data) {
    					},
    					submitdata : {
    						ajax_true: 1,
    						additional: "' . $name . '",
    						model: "' . $Model . '",
    						report_number: ' . $this->request->data['Reportnumber']['id'] . ',
    						},
    						callback : function(value, settings) {
    						}
    					});
    				});
    		</script>';

        echo '</div>';
    }

    public function ShowExpediting($Orders)
    {

        if (!Configure::check('ExpeditingManager')) {
            return false;
        }

        if (Configure::read('ExpeditingManager') != true) {
            return false;
        }

        if (Configure::read('ExpeditingManager') == false) {
            return false;
        }

        if (!isset($Orders['Supplier'])) {
            return false;
        }

        $Priority = $this->_View->viewVars['Priority'];
        $status_icon = 'icon_epediting';
        $Vars = $this->request->projectvars['VarsArray'];
        $Vars[2] = $Orders['Supplier']['id'];

        if (isset($Priority[$Orders['Supplier']['priority']])) {
            $status_icon = $Priority[$Orders['Supplier']['priority']];
        }

//pr(Router::url(array_merge(array('controller'=>'expeditings','action'=>'shortview'), $Vars)));
        echo $this->Html->link($Orders['Supplier']['unit'] . '-' . $Orders['Supplier']['equipment'],
            array_merge(array('controller' => 'expeditings', 'action' => 'detail'), $Vars),
            array(
                'title' => '',
                'class' => 'short_view icon modal summary_expediting_' . $Orders['Supplier']['id'] . ' ' . $status_icon,
                'rev' => Router::url(array_merge(array('controller' => 'expeditings', 'action' => 'shortview'), $Vars)),
            )
        );

        echo '
		<script type="text/javascript">
		$(document).ready(function(){
		$("a.summary_expediting_' . $Orders['Supplier']['id'] . '").tooltip({
		content:function(callback) {
			var url = $(this).attr("rev");
			$.ajax({
				type	: "POST",
				cache	: true,
				url		: url,
				data	: null,
				dataType: "html",
				success: function(data) {
					callback(data);
				},
			});
			},
			});
		});
		</script>
		';
    }
}
