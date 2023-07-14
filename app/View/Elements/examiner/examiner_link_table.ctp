<td class="small_cell">
<span class="weldhead">
<?php
echo $this->Html->link(__('Edit',true),
  array_merge(array('action' => 'overview'),
  $_examiner['Examiner']['_examiner_link']),
  array(
    'class' => 'icon icon_edit examiner_link_' . $_examiner['Examiner']['id'],
    'title' => __('Edit',true),
  )
);

echo $this->Html->link(__('Certificates',true),
  array_merge(array('action' => 'certificates'),
  $_examiner['Examiner']['_examiner_link']),
  array(
    'class' => 'icon icon_show_certificates examiner_link_' . $_examiner['Examiner']['id'],
    'title' => __('Certificates',true),
  )
);

echo $this->Html->link(__('Eyechecks',true),
  array_merge(array('action' => 'eyechecks'),
  $_examiner['Examiner']['_examiner_link']),
  array(
    'class' => 'icon icon_eyecheck examiner_link_' . $_examiner['Examiner']['id'],
    'title' => __('Eyechecks',true),
  )
);

echo $this->Html->link(__('Print examiner infos',true),
  array_merge(array('action' => 'pdf'),
  $this->request->projectvars['VarsArray']),
  array(
    'class' => 'icon icon_print showpdflink',
    'title' => __('Print examiner infos',true)
  )
);

if(isset($paging['page'])) $this_page = $paging['page'];
else $this_page = null;
?>
</span>
</td>
<script type="text/javascript">
$(document).ready(function(){

$("a.examiner_link_<?php echo $_examiner['Examiner']['id'];?>").on( "click", function() {

	$("#AjaxSvgLoader").show();

  var named = "<?php echo $this_page?>";

	var data = new Array();

	data.push({name: "ajax_true", value: 1});
  data.push({name: "named", value: named});

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $(this).attr("href"),
		data	: data,
		success: function(data) {
			$("#container").html(data);
			$("#container").show();
			$("#AjaxSvgLoader").hide();
		},
		statusCode: {
	    404: function() {
	      alert( "page not found" );
				location.reload();
	    }
	  },
		statusCode: {
	    403: function() {
	      alert( "page blocked" );
				location.reload();
	    }
	  }
		});

		return false;
	});
});
</script>
