<script type="text/javascript">
$(document).ready(function(){

	json_request_load_animation = function(){

		$("#JsonSvgLoader").show();

	}

	json_request_stop_animation = function(){

		$("#JsonSvgLoader").hide();

	}

	json_request_animation = async function(data){

		if(data == "Success"){

      $("#SuccessSVGAnimation").show();
      $(".circle2").addClass("animation4");
      $(".line3").addClass("animation5");
      $(".line4").addClass("animation6");

      var x = document.getElementById("circle2");

      x.addEventListener("animationend", function(){
        $(".circle2").removeClass("animation4");
        $(".line3").removeClass("animation5");
        $(".line4").removeClass("animation6");
        $("#SuccessSVGAnimation").hide();
				return Promise.resolve(1);
      });

		} else if(data == "Error") {

			$("#ErrorSVGAnimation").show();
			$(".circle1").addClass("animation1");
			$(".line1").addClass("animation2");
			$(".line2").addClass("animation3");

			var x = document.getElementById("circle1");

			x.addEventListener("animationend", function(){
				$(".circle1").removeClass("animation1");
				$(".line1").removeClass("animation2");
				$(".line2").removeClass("animation3");
				$("#ErrorSVGAnimation").hide();
			});

		}
  }

	json_stop_animation = function(){

		$(".circle2").removeClass("animation4");
		$(".line3").removeClass("animation5");
		$(".line3").removeClass("animation6");
		$("#SuccessSVGAnimation").hide();

		$(".circle1").removeClass("animation1");
		$(".line1").removeClass("animation2");
		$(".line2").removeClass("animation3");
		$("#ErrorSVGAnimation").hide();

	}
});
</script>
