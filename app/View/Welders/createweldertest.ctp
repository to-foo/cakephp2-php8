<div class="modalarea welders index inhalt">
<h2><?php echo __('Add welder test'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="hint"><p>
<?php echo __('Bitte wählen, in welchen Projekt/Cascade der Schweißertest gespeichert werden soll.',true);?>
</p></div>
<ul class="listecascade">
<?php
foreach($WelderTestMenue as $_key => $_data){

	echo '<li>';

	echo $MenueDescription['Topproject'][$_key];

	echo '<ul>';

	foreach($_data as $__key => $__data){

		echo '<li>';

		echo $MenueDescription['Cascade'][$__key];

		echo '<ul>';

		foreach($__data as $___key => $___data){

			echo '<li>';

			echo $MenueDescription['Report'][$___key];

			echo '<ul>';

			foreach($___data as $____key => $____data){

				echo '<li>';

				echo $this->Html->link($____data['Testingmethod']['verfahren'], array('controller' => 'reportnumbers','action' => 'add', $_key,$__key,0,$___key,0,$____data['Testingmethod']['id']), array('id' => '_infos_link','class' => 'mymodal create_welder_test round','title' => $____data['Testingmethod']['verfahren']));

				echo '</li>';
			}

			echo '</ul>';
			echo '</li>';
		}

		echo '</ul>';
		echo '</li>';

	}

	echo '</ul>';
	echo '</li>';

}
?>
</ul>
</div>

<?php
echo $this->element('js/welder_test_link',array('WelderId' => $welder['Welder']['id']));
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
?>
