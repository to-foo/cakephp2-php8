<div class="infinite_scroll_loader"></div>
<div class="paging">
<?php
if(!isset($paging['tr_marker'])) return;
$countertext  =  __('Page');
$countertext .= ' <span class="infinite_current_page">' . $paging['page'] . '</span>';
$countertext .= ' ' . __('of');
$countertext .= ' <span class="infinite_all_pages">' . $paging['pageCount']. '</span>';
$countertext .= ', ' . __('showing');
$countertext .= ' <span class="infinite_current_record">' . $paging['current'] . '</span>';
$countertext .= ' ' . __('records out of');
$countertext .= ' <span class="infinite_all_records">' . $paging['count'] . '</span>';
$countertext .= ' ' . __('total, starting on record');
$countertext .= ' <span class="infinite_from_record">' . $paging['start'] . '</span>';
$countertext .= ', ' . __('ending on');
$countertext .= ' <span class="infinite_to_record">' . $paging['end'] . '</span>';
?>
</div>
<?php // pr($paging)?>
<p class="paging_query"><?php // echo $countertext;?></p>
<div class="clear"></div>
<script type="text/javascript">

	$(document).ready(function(){

		var stopscroll = false;
		var page = false;

		var el = $("table.table_infinite_sroll");
		var bottom = el.height() + el.offset().top;
		var loadpage = false;

		if($("#CurrentLoadetPage").val() > 1 && $(window).height() > bottom){

			first_page = $("table.table_infinite_sroll tr.infinite_sroll_item:first").attr("data-page");
			last_page = $("table.table_infinite_sroll tr.infinite_sroll_item:last").attr("data-page");
			default_page_url = $("#DefaultPageUrl").val();
			page_count = $("#PageCount").val();

			if(first_page == 1) return;

			first_page--;
			page_exists = "page_" + first_page;

			if($("table.table_infinite_sroll tr").hasClass(page_exists) == true){
				return false;
			}

			var data = new Array();

			data.push({name: "ajax_true", value: 1});
			data.push({name: "is_infinite", value: 1});

			$.ajax({
				cache: false,
				url: default_page_url + first_page,
				data: data,
				success: function(html){
					if(html){

						$(html).insertBefore("table.table_infinite_sroll tr.infinite_sroll_item:first");
						$("div.infinite_scroll_loader").hide();
//						$("html, body").animate({ scrollTop: $(document).height() }, 800);

						stopscroll = false;

						if(url_next == false) {
							stopscroll = true;
						}
						return false;
					} else {
						$("div#loadmoreajaxloader").html("<center>No more posts to show.</center>");
					}
					return;
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

		if($(window).height() > (bottom + 50)){

			stopscroll = true;

			page_exists_number = $("#CurrentLoadetPage").val();
			page_exists_number++;
			page_exists = "page_" + page_exists_number;

			first_page = $("table.table_infinite_sroll tr.infinite_sroll_item:first").attr("data-page");
			last_page = $("table.table_infinite_sroll tr.infinite_sroll_item:last").attr("data-page");
			default_page_url = $("#DefaultPageUrl").val();
			page_count = $("#PageCount").val();

			last_page++;

			if(page_count < last_page){
				return false;
			}

			page_exists = "page_" + last_page;

			$("div.infinite_scroll_loader").show();

			var data = new Array();

			data.push({name: "ajax_true", value: 1});
			data.push({name: "is_infinite", value: 1});

			$.ajax({
				cache: false,
				url: default_page_url + last_page,
				data: data,
				success: function(html){
					if(html){

						$(html).insertAfter("table.table_infinite_sroll tr:last");
						$("div.infinite_scroll_loader").hide();
//						$('html, body').animate({scrollTop: '+=150px'}, 800);

						stopscroll = false;

						if(url_next == false) {
							stopscroll = true;
						}
						return false;
					} else {
						$("div#loadmoreajaxloader").html("<center>No more posts to show.</center>");
					}
					return;
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

		$(window).off().scroll(function() {

			if($(window).scrollTop() == 0){

				first_page = $("table.table_infinite_sroll tr.infinite_sroll_item:first").attr("data-page");
				last_page = $("table.table_infinite_sroll tr.infinite_sroll_item:last").attr("data-page");
				default_page_url = $("#DefaultPageUrl").val();
				page_count = $("#PageCount").val();

				if(first_page == 1) return;

				first_page--;
				page_exists = "page_" + first_page;

				if($("table.table_infinite_sroll tr").hasClass(page_exists) == true){
					return false;
				}

				var data = new Array();

				data.push({name: "ajax_true", value: 1});
				data.push({name: "is_infinite", value: 1});

				$.ajax({
					cache: false,
					url: default_page_url + first_page,
					data: data,
					success: function(html){
						if(html){

							$(html).insertBefore("table.table_infinite_sroll tr.infinite_sroll_item:first");
							$("div.infinite_scroll_loader").hide();

							stopscroll = false;

							if(url_next == false) {
								stopscroll = true;
							}
							return false;
						} else {
							$("div#loadmoreajaxloader").html("<center>No more posts to show.</center>");
						}
						return;
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

			if(stopscroll == true){
				return false;
			}

			if($(window).scrollTop() + $(window).height() + 100 >= $(document).height()) {

				stopscroll = true;

				page_exists_number = $("#CurrentLoadetPage").val();
				page_exists_number++;
				page_exists = "page_" + page_exists_number;

				first_page = $("table.table_infinite_sroll tr.infinite_sroll_item:first").attr("data-page");
				last_page = $("table.table_infinite_sroll tr.infinite_sroll_item:last").attr("data-page");
				default_page_url = $("#DefaultPageUrl").val();
				page_count = $("#PageCount").val();

				last_page++;

				if(page_count < last_page){
					return false;
				}

				page_exists = "page_" + last_page;

				if($("table.table_infinite_sroll tr").hasClass(page_exists) == true){
					return false;
				}

				if($("div.infinite_scroll_loader").show().length == 0){
					return false;
				}

				$("div.infinite_scroll_loader").show();

				var data = new Array();

				data.push({name: "ajax_true", value: 1});
				data.push({name: "is_infinite", value: 1});

				$.ajax({
					cache: false,
					url: default_page_url + last_page,
					data: data,
					success: function(html){
						if(html){
							$(html).insertAfter("table.table_infinite_sroll tr:last");


							$("div.infinite_scroll_loader").hide();
							stopscroll = false;

							if(url_next == false) {
								stopscroll = true;
							}

							return false;
						} else {
							$("div#loadmoreajaxloader").html("<center>No more posts to show.</center>");
						}
						return false;
					}
				});

				return false;

			} else {
				$("div.infinite_scroll_loader").hide();
			}
		});

	});

</script>
</div>
