<script>
$( function() {

    $("div.accordion").hide();
    $("table.advancetool h2").css('cursor','pointer');
    $("table.advancetool h2").css('border','none');

    $("table.advancetool h2").click(function() {

        if($("div." + $(this).attr("class")).is(":visible") == true){
           $("div." + $(this).attr("class")).hide("slow");
        }
        if($("div." + $(this).attr("class")).is(":hidden") == true){
            $("div.accordion").hide();
            $("div." + $(this).attr("class")).show("slow");
        }

//        $("div.accordion").hide();
 //       $("div." + $(this).attr("class")).show("slow");

    });

	$( ".accordion" ).accordion({
		heightStyle: "content",
		collapsible: true,
		active: 100000000
	});

});
</script>