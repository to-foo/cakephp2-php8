<script type="text/javascript">
$(document).ready(function(){

	$("a,li,td").tooltip();

	$("#container,table,a").click(function(){
		$("a.short_view").tooltip("close");
	})

	var timeout;
	var finishTimeout = false;

	$(".short_view").tooltip({
		show: null,
		items: 'a',
		hide: {
			effect: "",
		},
		position: {
			my: "left top",
			at: "right center"
		},
		content: function(callback){

			if(!finishTimeout) return false;

			var url = $(this).attr("rev");
			var data = new Array();

			if($(this).hasClass("summary_expediting")){data.push({name: "type", value: 1});}
			if($(this).hasClass("summary_fieles")){data.push({name: "type", value: 2});}

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: url,
				data	: data,
				dataType: "html",
				success: function(data) {
					callback(data);
				},
			});
     	},
		open: function( event, ui ) {
		},

		close: function( event, ui ) {
			ui.tooltip.hover(
			function () {
			$(this).stop(true).fadeTo(400, 1);
 			},
			function () {
				$(this).fadeOut("400", function(){ $(this).remove(); })
        		}
			);
		}
	});

	$(".short_view").mouseover(function(){
		var el = $(this);
		timeout = setTimeout(function(){
			finishTimeout = true;
			el.tooltip( "open" );
			finishTimeout = false;
		},750);
	});

	$(".short_view").mouseout(function(){
		clearTimeout(timeout);
	});

});

</script>
