<?php
echo $this->Form->input('NumCharacters',array('type' => 'hidden','value' => $PasswordConditions['NumCharacters']));
echo $this->Form->input('UseLowercase',array('type' => 'hidden','value' => $PasswordConditions['UseLowercase']));
echo $this->Form->input('UseUppercase',array('type' => 'hidden','value' => $PasswordConditions['UseUppercase']));
echo $this->Form->input('UseNumbers',array('type' => 'hidden','value' => $PasswordConditions['UseNumbers']));
echo $this->Form->input('UseSpecial',array('type' => 'hidden','value' => $PasswordConditions['UseSpecial']));
?>
<script type="text/javascript">
$(document).ready(function(){

	$('form.login input[type="submit"]').prop("disabled", "disabled");
	$('form.login input.pass_new').prop("disabled", "disabled");

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

	function responsemessage(data){

		if(data.Type == "error"){
			$("#UserPassword").next("span").removeAttr("class");
			$("#UserPassword").next("span").addClass("error");
			$("form.login input, form.login select").addClass("disabled");
			$('form.login input[type="submit"]').prop("disabled", "disabled");
			$('form.login input.pass_new').prop("disabled", "disabled");
			$("#UserPassword").removeClass("disabled");
			$(".pass_new").val("");
		}
		if(data.Type == "success"){
			$("#UserPassword").next("span").removeAttr("class");
			$("#UserPassword").next("span").addClass("success");
			$("form.login input, form.login select").prop("disabled", false);
			$("form.login input, form.login select").removeClass("disabled");
			$('form.login input[type="submit"]').prop("disabled", "disabled");
			$(".pass_new").val("");
		}
	}

	$("#UserPasswdConfirm").focus(function() {

	});

	$("#UserPasswd").focus(function() {

		userpass = $("#UserPasswd").val();
		confirmpass = $("#UserPasswdConfirm").val();

		if(confirmpass.length == 0){

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("error");
			$('form.login input[type="submit"]').prop("disabled", "disabled");

			return false;

		}

		if(userpass == confirmpass){

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("success");
			$("form.login input, form.login select").prop("disabled", false);

		} else {

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("error");
			$('form.login input[type="submit"]').prop("disabled", "disabled");

		}

	});

	$("#UserPasswd").keyup(function() {

		userpass = $("#UserPasswd").val();
		confirmpass = $("#UserPasswdConfirm").val();

		if(confirmpass.length == 0){

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("error");
			$('form.login input[type="submit"]').prop("disabled", "disabled");

			return false;

		}

		if(userpass == confirmpass){

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("success");
			$("form.login input, form.login select").prop("disabled", false);

		} else {

			$("#UserPasswdConfirm").next("span").removeAttr("class");
			$("#UserPasswdConfirm").next("span").addClass("error");
			$('form.login input[type="submit"]').prop("disabled", "disabled");

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
			$('form.login input[type="submit"]').prop("disabled", "disabled");

		}

	});

	$("input.old_password").on("change", function() {

		url = $(this).closest("form").attr("action");
		data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "json_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "data[User][password]", value: $(this).val()});
		data.push({name: "data[User][password_request]", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			dataType: "json",
			success: function(data) {
					responsemessage(data);
				}
		});
	});

	$("button#CancelResetForm").click(function() {

		$("#UserPassword").val("");
		$("#UserPasswd").val("");
		$("#UserPasswdConfirm").val("");
		$('form.login input[type="submit"]').prop("disabled", "disabled");
		$("#UserPasswd").prop("disabled", true);
		$("#UserPasswdConfirm").prop("disabled", true);
		$("#UserPasswd").addClass("disabled");
		$("#UserPasswdConfirm").addClass("disabled");
	});
});
</script>
