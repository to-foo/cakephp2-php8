<div class="modalarea welders index">
<h2>
<?php
echo __('Search results'); 
?>
</h2>
   
<div class="quicksearch">
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if (isset($welders)){
if(count($welders) == 0){
	echo '<div class="hint"><p>';
	echo __('No results available.',true);
	echo '</p></div>';
}
}
?>
<div>
<?php
 //   echo $this->Html->link(__('Print welder list',true), array_merge(array('action' => 'pdfinv'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_welders_pdf','title' => __('Print welder list',true)));?>                
</div>
<div>
<?php 
if(($this->Session->check('searchwelder.params'))) { 
	echo '<div class="hint"><p>';
        echo $this->Session->read('searchwelder.params'); 
        
	echo '</p></div>';
}


?>       
</div> 
<div id="container_summary" class="container_summary" ></div>
<?php  echo $this->element('qualification_legend');?>
<div id="container_table_summary" class="container_table_summary" >
<table class="table_resizable table_infinite_sroll">
	<tr> <th class="small_cell">Name</th>
			<?php
			foreach($xml->section->item as $_key => $_xml){
				if(trim($_xml->condition->key) != 'enabled' && empty($_xml->condition->link)) continue;

				$class = null;
				if(!empty($_xml->class)) $class = trim($_xml->class);
				echo '<th class="'.$class.'">';
				echo trim($_xml->description->$locale);
				echo '</th>';
			}
			?>
			<th class="small_cell"><?php echo __('Qualifications',true); ?></th>
                        <th class="small_cell"><?php echo __('Eye checks',true); ?></th>
	</tr>
	<?php 
		$i = 0;
		if (isset($welders))foreach ($welders as $welder):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="infinite_sroll_item altrow"';
		}

		if($welder['Welder']['active'] == 0){
			$class = ' class="infinite_sroll_item deactive" title="'.__('This welder is deactive',true).'"';
		}

		$this->request->projectvars['VarsArray'][13] = 0;
		$this->request->projectvars['VarsArray'][14] = 0;
		$this->request->projectvars['VarsArray'][16] = $welder['Welder']['id'];

		
		echo '<tr '.$class.'>';
                echo '<td class="small_cell">';
		echo'<span class="for_hasmenu1 weldhead">';
		 echo $this->Html->link($welder['Welder']['name'] . ' ' . $welder['Welder']['first_name'], 
			array_merge(array('action' => 'overview'), 
			$this->request->projectvars['VarsArray']), 
			array(
				'class'=>'round icon_show ajax',
				'rev' => implode('/',$this->request->projectvars['VarsArray'])
			)
		); 
                echo '</span>';
                echo   '</td>';            
		foreach($xml->section->item as $_key => $_xml){

//			if(trim($_xml->condition->key) != 'enabled') continue;
			if(trim($_xml->condition->key) != 'enabled' && empty($_xml->condition->link)) continue;
			$class= null;
			if(!empty($_xml->class)) $class = trim($_xml->class);
			echo '<td class="'.$class.'">';
			
			if(!empty($_xml->condition->link)){

			echo '<span class="for_hasmenu1 weldhead">';

			$this->request->projectvars['VarsArray'][15] = isset($welder['WelderTestingmethod'][0]['id']) ?$welder['WelderTestingmethod'][0]['id'] : '';
			
			echo $this->Html->link(!empty($welder[trim($_xml->model)][trim($_xml->key)]) ? $welder[trim($_xml->model)][trim($_xml->key)] : '-', 
				array_merge(array('controller' => trim($_xml->condition->link->controller), 'action' => trim($_xml->condition->link->action)), 
			
				$this->request->projectvars['VarsArray']), 
				array(
					'class'=> trim($_xml->condition->link->class),
					'rev' => implode('/',$this->request->projectvars['VarsArray'])
				)
			);	
	
			echo '</span>';

			} 
			if(empty($_xml->condition->link)){
				echo '<span class="discription_mobil">';
				echo trim($_xml->description->$locale);
				echo '</span>';
				echo h((trim($welder[trim($_xml->model)][trim($_xml->key)])));
			}
			echo '</td>';
		}
        echo '<td><span class="discription_mobil">';
		echo __('Qualifications');
		echo '</span><span class="summary_span">';

		if(isset($summary[$welder['Welder']['id']]['summary']['qualifications'])){

			$thissummary = array();

			foreach($summary[$welder['Welder']['id']]['summary']['qualifications'] as $_key => $_qualifications){

				$thissummary = $this->Quality->CertificatSummarySingle($summary[$welder['Welder']['id']],$_qualifications['certificate_id'],$_qualifications['certificate_data_id']);

				echo '<div class="container_summary_single container_summary_single_'.$_key.'_' . $welder['Welder']['id'] . '">';
				echo $thissummary;
				echo '</div>';
				
				$thissummary = null;
						
				$this_link = $this->request->projectvars['VarsArray'];
				

				echo $this->Html->link($_key,array_merge(array('action' => 'qualifications'),$this_link),array('title' => $_key,'rev'=> $_key,'rel'=> $welder['Welder']['id'], 'class' => 'summary_tooltip ajax icon '.$_qualifications['class']));
			}
		}
		echo '</span></td>';     
                
		echo'<td><span class="discription_mobil">';
                echo __('Eye checks'); 
		
                echo'</span><span class="summary_span">'; 
		
		if(isset($summary[$welder['Welder']['id']]['eyecheck']['qualifications']) && count($summary[$welder['Welder']['id']]['eyecheck']['qualifications']) > 0){

			$thissummary = array();

			foreach($summary[$welder['Welder']['id']]['eyecheck']['qualifications'] as $_key => $_eyecheck){
				foreach($summary[$welder['Welder']['id']]['eyecheck']['summary'] as $__key => $__summary){
					if(count($__summary) > 0){
						$thissummary[$_eyecheck['certificate_id']] = $this->Quality->EyecheckSummarySingle($summary[$welder['Welder']['id']],$_eyecheck['certificate_id'],$_eyecheck['certificate_data_id']);
					}
				}
				if(isset($thissummary[$_eyecheck['certificate_id']]) && $thissummary[$_eyecheck['certificate_id']] != false){
					echo '<div class="container_eyechecksummary_single container_summaryeyecheck_single_'.$_eyecheck['certificate_id'].'">';
					echo $thissummary[$_eyecheck['certificate_id']];
					echo '</div>';
				}
				else {
					echo '<div class="container_eyechecksummary_single container_summaryeyecheck_single_'.$_eyecheck['certificate_id'].'">';
					echo $_eyecheck['certificat'];
					echo '</div>';				
				}
                             
			}
			//$this_link = $this->request->projectvars['VarsArray'];
			echo '<a href="welders/eyechecks/'.$_eyecheck['termlink'].'" title="'.$_eyecheck['certificat'].'" rel="'.$_eyecheck['certificate_id'].'" class="summaryeyecheck_tooltip ajax icon '.$_eyecheck['class'].'">'.$_eyecheck['certificat'].'</a>';

                        
               }
		echo'</span></td></tr>';
    
		endforeach;


                
?>		
</table>
</div>
<?php //echo $this->element('infinite_scroll');?>
<?php // echo $this->element('page_navigation');?>

</div>     





<script type="text/javascript">

	function setscrollfunctions(){

		$("div.container_summary_single").hide();
                $("div.container_eyechecksummary_single").hide();

		$("a.summary_tooltip").tooltip({
			content: function () {
				var output = $(".container_summary_single_" + $(this).prop('rev') + "_" +$(this).prop('rel')).html();
				return output;
			}
		});
                $("a.summaryeyecheck_tooltip").tooltip({
                        content: function () {
                                  var output = $(".container_summaryeyecheck_single_" + $(this).prop('rel')).html();
                                  return output;
                        }
                });                

	    var onSampleResized = function (e) {
	        var columns = $(e.currentTarget).find("td");
	        var rows = $(e.currentTarget).find("tr");
	        var Cloumnsize;
	        var rowsize;
	        columns.each(function () {
	            Cloumnsize += $(this).attr('id') + "" + $(this).width() + "" + $(this).height() + ";";
	        });
	        rows.each(function () {
	            rowsize += $(this).attr('id') + "" + $(this).width() + "" + $(this).height() + ";";
	        });
	        document.getElementById("hf_columndata").value = Cloumnsize;
	        document.getElementById("hf_rowdata").value = rowsize;
	    };

	    $(".table_resizable th").resizable();
	    $(".table_resizable tr").resizable();
	    $(".table_resizable td").resizable();
		      
	}

	$("a.ajax").click(function() {
		$(".ui-dialog").hide();
		$("#maximizethismodal").show();
		$("a#maximizethismodal").attr("title",$("a#maximizethismodal").text() + " - " + $("div#dialog h2").text());
		return;
	});
		
	$(document).ready(function(){
		setscrollfunctions();
	});
</script>

<?php 
echo $this->JqueryScripte->ModalFunctions();
   //$form = '#WelderSearchForm';
     //  echo $this->JqueryScripte->SessionFormData($form); 

?>
