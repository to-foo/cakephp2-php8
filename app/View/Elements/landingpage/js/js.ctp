<?php
echo $this->Form->input('ProjectUrl',array('type' => 'hidden','value' => $LandingPageUrl['large']));

if(isset($LandingPageUrl['widget'])){
	foreach ($LandingPageUrl['widget'] as $key => $value) {
		echo $this->Form->input($key,array(
			'class' => 'hidden_widget',
			'type' => 'hidden',
			'value' => $value['url'],
			'data-place' => '#' . $value['place'],
			'data-option' => $value['option'],
			)
		);
	}	
}
?>
<script type="text/javascript">

$(document).ready(function() {

	$("div.module div.item a").tooltip();

	function autoload(handle,place,option){

		if(!$(place)) return false;
		if($(place).length == 0) return false;

		$(place).css("background-image","url(img/indicator.gif)");

	  var data = new Array();

	  data.push({name: "ajax_true", value: 1});
		data.push({name: "landig_page", value: 1});
		data.push({name: "option", value: option});

	  $.ajax({
	    type	: "POST",
	    cache	: false,
	    url		: handle,
	    data	: data,
	    success: function(data) {
				$(place).html(data);
				$(place).show();
				$("#AjaxSvgLoader").hide();
	    },
	    complete: function(data) {
				$(place).css("background-image","none");
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
	}

	function autoloadlarge(handle,place,option){

		if(!$(place)) return false;

		$(place).css("background-image","url(img/indicator.gif)");


		$('div.module div.item').removeClass("active");
		$('div.div_ndt_reporting').addClass("active");

	  var data = new Array();

	  data.push({name: "ajax_true", value: 1});
		data.push({name: "landig_page_large", value: 1});
		data.push({name: "option", value: option});

	  $.ajax({
	    type	: "POST",
	    cache	: false,
	    url		: handle,
	    data	: data,
	    success: function(data) {
				$(place).html(data);
				$(place).show();
				$("#AjaxSvgLoader").hide();
	    },
	    complete: function(data) {
				$(place).css("background-image","none");
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
	}

	autoloadlarge($("#ProjectUrl").val(),"#large_window",null);

	$("input.hidden_widget").each(function(key,value){
		autoload($(this).val(),$(this).attr("data-place"),$(this).attr("data-option"));
	});

	$("div.module a.landing_icon").click(function() {

		$('div.module div.item').removeClass("active");
		$(this).parent('div.item').addClass("active");

		$("#large_window").empty();
		$("#large_window").css("background-image","url(img/indicator.gif)");

		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "landig_page_large", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#large_window").html(data);
				$("#large_window").show();
			},
			complete: function(data) {
				$("#large_window").css("background-image","none");
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
