<script type="text/javascript">
$(document).ready(function(){

	$("#CertificateCertificateDataActive1").change(function() {
		$("#dialog form input,#dialog form select,#dialog form textarea").attr("disabled","disabled");
		$("#dialog").load("<?php echo $url;?>", {"ajax_true": 1,"certificate_data_active": $(this).val()});
	});
		
	$("#CertificateCertificateDataActive0").change(function() {		
		$("#dialog form input,#dialog form select,#dialog form textarea").attr("disabled","disabled");
		$("#dialog").load("<?php echo $url;?>", {"ajax_true": 1,"certificate_data_active": $(this).val()});
	});	
	
});
</script>
