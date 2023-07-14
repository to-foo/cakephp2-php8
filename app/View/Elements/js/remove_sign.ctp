<script type="text/javascript">
$(() => {
	const closeModalClick = _ => {
		$("a#closethismodal").click(e => {
				$("#dialog").dialog("close");
				return false;
		});
	}

	const removeSignsClick = _ => {
		$('a#remove_signs').click(e => {
			var data = $(".fakeform").serializeArray();

			data.push({name: "ajax_true", value: 1});
			data.push({name: "remove_sign", value: 1});

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: $(e.target).attr("href"),
				data	: data,
				success: data => {
					$("#dialog").html(data);
					$("#dialog").show();
				}
			});
			return false;
		});
	}

	closeModalClick();
	removeSignsClick();

});
</script>
