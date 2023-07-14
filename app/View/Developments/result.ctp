<?php
//if($success == false){
//pr($this->request->data ['DevelopmentData']['result']);	
//pr($this->request->data ['DevelopmentData']['evaluation_id']);	

$option = array('0' => '-', '1' => 'Rep', '2' => 'Ok'); 
echo $this->Form->input('DevelopmentData.result',
							array(
								'options' => $option,
								'data' => $this->request->data['DevelopmentData']['id'], 
								'label' => false, 
								'default' => $this->request->data['DevelopmentData']['result']
							)
						);
//}
/*
if($success == true){
	echo $message;
}
*/
?>
<?php 
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	} 

if($this->request->data ['DevelopmentData']['result'] == 0){$dev_class = 'development_open';}	
if($this->request->data ['DevelopmentData']['result'] == 1){$dev_class = 'development_rep';}	
if($this->request->data ['DevelopmentData']['result'] == 2){$dev_class = 'development_ok';}	
	
?>

<script type="text/javascript">
	$(document).ready(function(){

		$("#this_development_<?php echo $this->request->data ['DevelopmentData']['evaluation_id'];?>").removeClass("development_open development_ok development_rep").addClass("<?php echo $dev_class;?>");
		$("span#count_all").text("<?php echo $OrdersStatus['all'];?>");
		$("span#count_open").text("<?php echo $OrdersStatus['open'];?>");
		$("span#count_repairs").text("<?php echo $OrdersStatus['repairs'];?>");
		$("span#count_closet").text("<?php echo $OrdersStatus['closet'];?>");

		$("td.progress_result select").change(function() {
			
			var url = "<?php echo $this->Html->url(array('controller'=>'developments','action'=>'result',
			$this->request->projectvars['VarsArray'][0],
			$this->request->projectvars['VarsArray'][1],
			$this->request->projectvars['VarsArray'][2],
			$this->request->projectvars['VarsArray'][3],
			$this->request->projectvars['VarsArray'][4],
			$this->request->projectvars['VarsArray'][5],
			$this->request->projectvars['VarsArray'][6]
			));?>";
			var data = $("fakeform").serializeArray();
			var target = "td#cell_" + $(this).attr("data");
			data.push({name: "ajax_true", value: 1});
			data.push({name: "data[DevelopmentData][id]", value: $(this).attr("data")});
			data.push({name: "data[DevelopmentData][result]", value: $(this).val()});

			$.ajax({
					type	: "POST",
					cache	: true,
					url		: url,
					data	: data,
					success: function(data) {
		    			$(target).html(data);
		    			$(target).show();
					}
				});
				return false;
			});
		
	});
</script> 						


