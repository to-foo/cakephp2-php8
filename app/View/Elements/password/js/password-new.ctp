<?php
echo $this->Form->input('NumCharacters',array('type' => 'hidden','value' => $PasswordConditions['NumCharacters']));
echo $this->Form->input('UseLowercase',array('type' => 'hidden','value' => $PasswordConditions['UseLowercase']));
echo $this->Form->input('UseUppercase',array('type' => 'hidden','value' => $PasswordConditions['UseUppercase']));
echo $this->Form->input('UseNumbers',array('type' => 'hidden','value' => $PasswordConditions['UseNumbers']));
echo $this->Form->input('UseSpecial',array('type' => 'hidden','value' => $PasswordConditions['UseSpecial']));
?>
<script type="text/javascript">
$(document).ready(function(){

	$('form.login input[type="submit"]').attr("disabled", "disabled");

	$(".pr-password").passwordRequirements({
		Message: "<?php echo __('Password strength conditions',true);?>",
		NumbersCountText: "<?php echo __('Number of letters',true);?>",
		LowercaseText: "<?php echo __('Lowercase letter',true);?>",
		UppercaseText: "<?php echo __('Capital letter',true);?>",
		NumbersText: "<?php echo __('Numbers',true);?>",
		SpecialText: "<?php echo __('Special character',true);?>",
		numCharacters: parseInt($("#NumCharacters").val()),
		useLowercase: parseInt($("#UseLowercase").val()),
		useUppercase: parseInt($("#UseUppercase").val()),
		useNumbers: parseInt($("#UseNumbers").val()),
		useSpecial: parseInt($("#UseSpecial").val())
	});


	$("#UserPasswdConfirm").focus(function() {

	});

	$("#UserPasswd").focus(function() {

		userpass = $("#UserPasswd").val();
		confirmpass = $("#UserPasswdConfirm").val();

		if(confirmpass.length == 0){

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("error");
			$('form.login input[type="submit"]').attr("disabled", "disabled");

			return false;

		}

		if(userpass == confirmpass){

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("success");
			$("form.login input, form.login select").prop("disabled", false);

		} else {

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("error");
			$('form.login input[type="submit"]').attr("disabled", "disabled");

		}

	});

	$("#UserPasswd").keyup(function() {

		userpass = $("#UserPasswd").val();
		confirmpass = $("#UserPasswdConfirm").val();

		if(confirmpass.length == 0){

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("error");
			$('form.login input[type="submit"]').attr("disabled", "disabled");

			return false;

		}

		if(userpass == confirmpass){

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("success");
			$("form.login input, form.login select").prop("disabled", false);

		} else {

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("error");
			$('form.login input[type="submit"]').attr("disabled", "disabled");

		}

	});

	$("#UserPasswdConfirm").keyup(function() {

		userpass = $("#UserPasswd").val();
		confirmpass = $("#UserPasswdConfirm").val();

		if(userpass == confirmpass){

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("success");
			$("form.login input, form.login select").prop("disabled", false);

		} else {

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("error");
			$('form.login input[type="submit"]').attr("disabled", "disabled");

		}

	});

	$("button#CancelResetForm").click(function() {

		$("#UserPassword").val("");
		$("#UserPasswd").val("");
		$("#UserPasswdConfirm").val("");
		$('form.login input[type="submit"]').attr("disabled", "disabled");
		$("#UserPasswd").prop("disabled", true);
		$("#UserPasswdConfirm").prop("disabled", true);
		$("#UserPasswd").addClass("disabled");
		$("#UserPasswdConfirm").addClass("disabled");
	});
});
</script>
