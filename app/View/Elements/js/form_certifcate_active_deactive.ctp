<script type="text/javascript">
$(document).ready(function(){

	$("#CertificateCertificateDataActive1").click(function() {
		$("#CertificateCertificat").val(null);
		$("#CertificateFirstCertification").val(null);
		$("#CertificateRenewalInYear").val(null);
		$("#CertificateRecertificationInYear").val(null);
		$("#CertificateHorizon").val(null);
	});	
	$("#CertificateCertificateDataActive0").click(function() {
		$("#CertificateCertificat").val("-");
		$("#CertificateFirstCertification").val(0);
		$("#CertificateRenewalInYear").val(0);
		$("#CertificateRecertificationInYear").val(0);
		$("#CertificateHorizon").val(0);
	});	
});
</script>
