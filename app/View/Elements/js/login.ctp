<script type="text/javascript">
$(document).ready(function() {

    var hash = window.location.hash;
    var hashsplit = hash.split('#');
    var nonce = Math.floor(Math.random() * 101) * Math.floor(Math.random() * 202);

		$(".container").css("background-color", "transparent");

    if (hashsplit.length == 1) {}

    if (hashsplit.length == 2) {

        var data = new Array();

        $.ajax({
            type: "POST",
            cache: true,
            url: "login",
            data: {
                "data[Login][string]": hashsplit,
            },
            success: function(data) {
                $("#loginformuser").html(data);
                $("#loginformuser").show();
            },
            done: function() {}
        });
    }
    $('#UserUsername').focus();

    $("form").bind("submit", function() {
        if ($("#UserUsername").val() == "") {
            $("#UserUsername").css("background-color", "#ecb5a2");
            return false;
        }
        if ($("#UserPassword").val() == "") {
            $("#UserPassword").css("background-color", "#ecb5a2");
            return false;
        }
    });
});
</script>
