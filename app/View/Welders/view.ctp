<div class="modalarea welders view">
<h2><?php  echo __('Welder workload'); ?></h2>
	<dl>
		<?php if(isset($welder['Welder']['name'])):?>
        <dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($welder['Welder']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Testingcomp'); ?></dt>
		<dd>
			<?php echo $this->Html->link($welder['Testingcomp']['name'], array('controller' => 'testingcomps', 'action' => 'view', $welder['Testingcomp']['id']), array('class'=>'mymodal')); ?>
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
		<?php if (!empty($welderTime)): ?>
		<?php

			$tests = array();
			foreach ($welderTime as $time):
				$begin = new DateTime($time['WelderTime']['testing_time_start']);
				$end = new Datetime($time['WelderTime']['testing_time_end']);
				
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
			$_welder_id = $welder['Welder']['id'];
			$_welder_id_for_ajax = $welder['Welder']['id'];
		}
		else {
			$_welder_id = 'all';
			$_welder_id_for_ajax = 0;
		}
			if(isset($times[$_welder_id]['testing'])){$val = 'testing';}
			elseif(!isset($times[$_welder_id]['testing'])){$val = 'waiting';}
			
			foreach($times[$_welder_id][$val] as $_key => $_times){
				echo '<ul>';
				echo '<li><span>';
				echo $this->Html->Link($_key,array_merge(array('controller'=>'welders', 'action'=>'list_welder_workload'),$this->request->projectvars['VarsArray']),array('rel' => $_key,'rev' => 0));
				echo '</span><ul>';
				foreach($_times as $__key => $__times){
					echo '<li><span>';
					echo $this->Html->Link($__key,array_merge(array('controller'=>'welders', 'action'=>'list_welder_workload'),$this->request->projectvars['VarsArray']),array('rel' => $_key,'rev' => $__key));
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
			"welder": "<?php echo $_welder_id_for_ajax;?>",
			"width": $(".datearchiv").width(),
			"month": $(this).attr("rev"),
			"year": $(this).attr("rel")
		})
		return false;
	});	
});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>

