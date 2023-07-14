<script>
$(document).ready(function () {

		var modalheight = $(window).height();
		var modalwidth = $(window).width();

    $(".fancybox").fancybox({
			'transitionIn'  :   'fade',
      'transitionOut' :   'fade',
			'speedIn'       :   600,
      'speedOut'      :   200,
      'overlayShow'   :   true,
			height: modalheight,
	    width: modalwidth,
			autoScale: true,
			autoDimensions: true,
			type: 'ajax',
			afterClose: function(){
 				$(".fancybox").css("display","block");
			}
		});
});
</script>
