<script type="text/javascript">
$(() => {
    const reload_time = 300000;

    const reportlink = _ => {

        $("a.modal_post").click(e => {
            let elem = e.currentTarget;

            $("#AjaxSvgLoader").show();

            $(".ui-dialog").show();
            $("#dialog").dialog().dialog("close");
            $("#maximizethismodal").hide();

            let modalheight = Math.ceil(($(window).height() * 90) / 100);
            let modalwidth = Math.ceil(($(window).width() * 90) / 100);

            let dialogOpts = {
                modal: false,
                width: modalwidth,
                height: modalheight,
                autoOpen: false,
                draggable: true,
                resizeable: true
            };

            $("#dialog").dialog(dialogOpts);

            data = new Array();
            data.push({
                name: "ajax_true",
                value: 1
            });
            data.push({
                name: "data_area",
                value: $("ul.editmenue li.active a").attr("rel")
            });

            $("#dialog").empty();

            $.ajax({
                type: "POST",
                cache: false,
                url: $(elem).attr("href"),
                data: data,
                success: data => {
                    $("#dialog").html(data);
                    $("#dialog").dialog("open");
                    $("#dialog").show();
                    $("#AjaxSvgLoader").hide();
                }
            });

            return false;
        });
    };

    const reload = _ => {

        data = new Array();
        data.push({
            name: "ajax_true",
            value: 1
        });
        data.push({
            name: "table_only",
            value: 1
        });

        $.ajax({
            type: "POST",
            cache: false,
            url: $("#TicketscanTicketsUrl").val(),
            data: data,
            success: data => {

                $("#ticket_table_content").html(data);

                $("ul.editmenue li").each((index, e) => {
                    if ($(e).hasClass("active")) {

                        current_area_link = $(e).attr("id");
                        current_area = $("#" + current_area_link + " a").attr("rel");

                        $("div.areas").hide();
                        $("div#" + current_area).show();
                        $("ul.editmenue li").removeClass("active").addClass("deaktive");
                        $("ul.editmenue li#" + current_area_link).addClass("active");

                    }
                });

                reportlink();
            }
        });
    };

    reportlink();

    let interval = null;
  //  let interval = window.setInterval(reload, reload_time);

    $("a").on('click', e => {
        let elem = e.currentTarget;
        if ($(elem).hasClass('notstop')) return false;
        if ($(elem).hasClass('modal_post')) return false;
        clearInterval(interval);
    });

    function ChangeTestingcompname(data){

      let name = data.Testingcomp;
      let id = data.Reportnumber.id;

      $("table.advancetool").find("p[data-id='" + id + "']").text(name);

    }

    function ChangePriority(test){

      $("table.advancetool").find("td.status_td p[ticket-id='" + test.Ticket.id + "']").text(test.Ticket.priority_text);
      $("table.advancetool").find("td.status_td p[ticket-id='" + test.Ticket.id + "']").removeClass("height");
      $("table.advancetool").find("td.status_td p[ticket-id='" + test.Ticket.id + "']").removeClass("low");

      if(test.Ticket.priority == "1") $("table.advancetool").find("td.status_td p[ticket-id='" + test.Ticket.id + "']").addClass("height");
      else $("table.advancetool").find("td.status_td p[ticket-id='" + test.Ticket.id + "']").addClass("low");

    }

    function EditableTestingcomp(){

  		$(".edit_testingcomp").editable($("#EditUrl").val(), {

          type : "select",
          data: $("#TestingcompsForChange").val(),
          submit : 'OK',
      		cancel : 'Cancel',
  	    	cancelcssclass : 'editable_cancel',
  	    	submitcssclass : 'editable_submit',

  				onblur: function(value, settings) {

  				},

  				submitdata : function(data) {

            json_stop_animation();

            let ThisId = $(this).attr("data-id");

            let send = {
              ajax_true: 1,
              json_true: 1,
              testingcomp_id: data,
              reportnumber_id: ThisId,
            };

  					return send;
  	    	},
          callback : function(result, settings, data) {
            let json = jQuery.parseJSON(result);
            ChangeTestingcompname(json);
            json_request_animation(json.Message);
          },

  		});

  	}

    function EditablePriority(){

  		$(".edit_priority").editable($("#EditUrl").val(), {

          type : "select",
          data: '{0:"niedrig",1:"hoch"}',
          submit : 'OK',
      		cancel : 'Cancel',
  	    	cancelcssclass : 'editable_cancel',
  	    	submitcssclass : 'editable_submit',

  				onblur: function(value, settings) {

  				},

  				submitdata : function(data) {

            json_stop_animation();

            let ThisId = $(this).attr("ticket-id");

            let send = {
              ajax_true: 1,
              json_true: 1,
              priority: data,
              ticket_id: ThisId,
            };

  					return send;
  	    	},
          callback : function(result, settings, data) {

            let json_result = jQuery.parseJSON(result);

            ChangePriority(json_result);
            json_request_animation(json_result.Message);
          },

  		});
  	}

    function OpenCloseTicket(){

      $("p.edit_status").click(function() {

        $("#AjaxSvgLoader").show();

        let url = $("#EditUrl").val();
        let ticketid = $(this).attr("ticket-id");
        let ticketurl = $(this).attr("ticket-url");
        let data = new Array();

        data.push({name: "ajax_true", value: 1});
        data.push({name: "json_true", value: 1});
        data.push({name: "status", value: 1});
  			data.push({name: "ticket_id", value: ticketid});
        data.push({name: "ticket_url", value: ticketurl});

  			$.ajax({
  				type	: "POST",
  				cache	: false,
  				url		: url,
  				data	: data,
  				dataType: "json",
  				success: function(data) {

            ReloadTickets();
            LoadTicket(data);
  				}
  			});
  		});
  	}

    function LoadTicket(data){

      if(data.Message != "Success") return false;

      $("#dialog").dialog().dialog("close");
    	$("#maximizethismodal").hide();

      let ticketurl = data.TicketUrl;
    	let modalheight = Math.ceil(($(window).height() * 90) / 100);
    	let modalwidth = Math.ceil(($(window).width() * 90) / 100);

    	let dialogOpts = {
    		modal: false,
    		width: modalwidth,
    		height: modalheight,
    		autoOpen: false,
    		draggable: true,
    		resizeable: true
    	};

    	$("#dialog").dialog(dialogOpts);

      let request = new Array();
    	request.push({name: "ajax_true", value: 1});
    	request.push({name: "dialog", value: 1});

    	$("#dialog").empty();

      $.ajax({
    		type	: "POST",
    		cache	: false,
    		url		: ticketurl,
    		data	: request,
    		success: function(data) {
    			$("#dialog").html(data);
    			$("#dialog").dialog("open");
    			$("#dialog").show();
    			$("#dialog").css('overflow','scroll');
    			$("#AjaxSvgLoader").hide();
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

    function ReloadTickets(){

      let reloadurl = $(".breadcrumbs a").last().attr("href");
      let request = new Array();
      request.push({name: "ajax_true", value: 1});

      $.ajax({
        type	: "POST",
        cache	: false,
        url		: reloadurl,
        data	: request,
        success: function(data) {
          $("#container").html(data);
          $("#container").show();
          $("#AjaxSvgLoader").hide();
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



    OpenCloseTicket();
    EditablePriority();
    EditableTestingcomp();

});
</script>
