<?php
$TicketsActiveAreas = SessionComponent::read('TicketsActiveAreas');
echo $this->Form->input('TicketDataArea',array('value' => $TicketsActiveAreas,'type' => 'hidden'));
?>
<script type="text/javascript">
$(document).ready(function(){

        let current_area = $("#TicketDataArea").val();
        let current_area_link = "link_" + $("#TicketDataArea").val();

        $("div.areas").hide();
        $("div#" + current_area).show();
        $("ul.editmenue li#" + current_area_link).removeClass('deactive').addClass('active');

        $("ul.editmenue li a").click(function() {

          let id = $(this).attr('rel');
          let data = new Array();
          let url = $("div.breadcrumbs").find("a:last").attr("href");

          data.push({name: "ajax_true", value: 1});
          data.push({name: "json_true", value: 1});
          data.push({name: "active_area", value: id});

          $("ul.editmenue li").removeClass("active").addClass("deactive");
          $(this).closest("li").removeClass("deactive").addClass("active");
          $("div.areas").hide();
          $("div#" + id).show();

          $.ajax({
        		type	: "POST",
        		cache	: false,
        		url		: url,
        		data	: data,
            dataType: "json",
      		  success: function(data) {
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


        });

});
</script>
