<script type="text/javascript">
$(() => {

    function ChangeTestingcompname(data){

      let name = data.Testingcomp;
      let id = data.Reportnumber.id;

      $("table.advancetool").find("p[data-id='" + id + "']").text(name);

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

    EditableTestingcomp();

});
</script>
