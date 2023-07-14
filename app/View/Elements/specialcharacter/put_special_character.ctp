<script type="text/javascript">
$(document).ready(function(){

	function copyStringToClipboard (str) {

	   var el = document.createElement('textarea');
	   el.value = str;
	   el.setAttribute('readonly', '');
	   el.style = {position: 'absolute', left: '-9999px'};
	   document.body.appendChild(el);
	   el.select();
	   document.execCommand('copy');
	   document.body.removeChild(el);
	}

	$(".specialcharacter a").tooltip({});

	$(".specialcharacter a").click(function(){

		copyStringToClipboard($(this).text());
		$(this).tooltip("option","content",$(this).text() + " " + "copied to the clipboard." + " " + "Paste with Strg+V");

		return false;

	});
});
</script>
