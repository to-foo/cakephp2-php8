<script type="text/javascript">
$(document).ready(function(){

	$("#duplicatevalution").click(function() {
		$("#container").load("<?php echo Router::url(array('action' => 'duplicatevalution',
        $this->request->projectvars['VarsArray'][0],
        $this->request->projectvars['VarsArray'][1],
        $this->request->projectvars['VarsArray'][2],
        $this->request->projectvars['VarsArray'][3],
        $this->request->projectvars['VarsArray'][4],
        $this->request->projectvars['VarsArray'][5],
        $this->request->projectvars['VarsArray'][6],
        $this->request->projectvars['VarsArray'][7],
        $evalId
        ));?>", {"ajax_true": 1});
		});
	});
</script>
