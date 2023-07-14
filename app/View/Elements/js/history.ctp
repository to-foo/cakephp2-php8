<script type="text/javascript">
$(function(){

	var cache = {
		"": $("#container")
	};

	$(window).bind( 'hashchange', function(e) {

    // Get the hash (fragment) as a string, with any leading # removed. Note that
    // in jQuery 1.4, you should use e.fragment instead of $.param.fragment().
    var url = $.param.fragment();

    // Hide any visible ajax content.
    $( '#container' ).children( ':visible' ).hide();

    if ( cache[ url ] ) {
      // Since the element is already in the cache, it doesn't need to be
      // created, so instead of creating it again, let's just show it!
      cache[ url ].show();

    } else {
      // Show "loading" content while AJAX content loads.

      // Create container for this url's content and store a reference to it in
      // the cache.
      cache[ url ] = $( '<div class="bbq-item"/>' )

        // Append the content container to the parent container.
        .appendTo( '#container' )

        // Load external content via AJAX. Note that in order to keep this
        // example streamlined, only the content in .infobox is shown. You'll
        // want to change this based on your needs.
        .load( url, function(){
          // Content loaded, hide "loading" content.
        });
    }
  })

	$(window).trigger( 'hashchange' );	
});
</script>
