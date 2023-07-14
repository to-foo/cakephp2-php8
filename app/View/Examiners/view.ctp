<div class="modalarea examiners view">
<h2><?php  echo __('Examiner workload'); ?></h2>
	<dl>
		<?php if(isset($examiner['Examiner']['name'])):?>
        <dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($examiner['Examiner']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Testingcomp'); ?></dt>
		<dd>
			<?php echo $this->Html->link($examiner['Testingcomp']['name'], array('controller' => 'testingcomps', 'action' => 'view', $examiner['Testingcomp']['id']), array('class'=>'mymodal')); ?>
			&nbsp;
		</dd>
        <?php endif;?>
		<?php if(isset($order)):?>
        <dt><?php echo __('Order'); ?></dt>
		<dd>
			<?php echo h($order['Order']['auftrags_nr']); ?>
			&nbsp;
		</dd>
        <?php endif;?>
		<div class="clear"></div>
	</dl>
	<div class="clear"></div>
	<div class="related">
		<?php if (!empty($examinerTime)): ?>
		<?php

			$tests = array();
			foreach ($examinerTime as $time):
				$begin = new DateTime($time['ExaminerTime']['testing_time_start']);
				$end = new Datetime($time['ExaminerTime']['testing_time_end']);

				$daterange = new DatePeriod($begin, new DateInterval('PT5M'), $end);

				$t = array();
				$day = '';
				foreach($daterange as $date):
					$day = $date->format('d.m.Y');
					$t[$date->format('H:i')] = $date->format('H') * 3600+$date->format('i')*60;
				endforeach;
				// Endzeit muss manuell eingefügt werden
				$t[$end->format('H:i')] = $end->format('H') * 3600+$end->format('i')*60;

				$tests[$day][] = $t;
			endforeach;
		?>
		<div class="clear"></div>
        <div class="datearchiv">
        <?php
		if(isset($this->request->projectvars['VarsArray'][15]) && $this->request->projectvars['VarsArray'][15] > 0){
			$this->request->projectvars['VarsArray'][13] = $this->request->projectvars['VarsArray'][15];
			$_examiner_id = $examiner['Examiner']['id'];
			$_examiner_id_for_ajax = $examiner['Examiner']['id'];
		}
		else {
			$_examiner_id = 'all';
			$_examiner_id_for_ajax = 0;
		}
			if(isset($times[$_examiner_id]['testing'])){$val = 'testing';}
			elseif(!isset($times[$_examiner_id]['testing'])){$val = 'waiting';}

			foreach($times[$_examiner_id][$val] as $_key => $_times){
				echo '<ul>';
				echo '<li><span>';
				echo $this->Html->Link($_key,array_merge(array('controller'=>'examiners', 'action'=>'list_examiner_workload'),$this->request->projectvars['VarsArray']),array('rel' => $_key,'rev' => 0));
				echo '</span><ul>';
				foreach($_times as $__key => $__times){
					echo '<li><span>';
					echo $this->Html->Link($__key,array_merge(array('controller'=>'examiners', 'action'=>'list_examiner_workload'),$this->request->projectvars['VarsArray']),array('rel' => $_key,'rev' => $__key));
					echo '</span></li>';
				}
				echo '</ul>';
				echo '</li>';
				echo '</ul>';
			}
		?>
        </div>
	<?php endif; ?>
	</div>

</div>
<script type="text/javascript">
$(function(){
	$("div.datearchiv ul li a").click(function() {
		$("#dialog").load($(this).attr("href"), {
			"ajax_true": 1,
			"examiner": "<?php echo $_examiner_id_for_ajax;?>",
			"width": $(".datearchiv").width(),
			"month": $(this).attr("rev"),
			"year": $(this).attr("rel")
		})
		return false;
	});
});
</script>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
