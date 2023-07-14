<?php
echo $this->Form->input('Supplier.unit');
echo $this->Form->input('Supplier.equipment');
?>
</fieldset>
<fieldset>
<?php
//echo $this->Form->input('Rkl.class',array('class' => 'autocompletion'));
//echo $this->element('expediting/rkl_infos');
$autourl = $this->Html->url(array_merge(array('controller' => 'rkls','action' => 'rkls'),$this->request->projectvars['VarsArray']));
//echo $this->Form->input('AutoUrl',array('type' => 'hidden','value' => $autourl));
?>

<script type="text/javascript">
$(document).ready(function(){

  var reset_rkls = function(data){

    if(!data.RklsAnsiPipingClassBasicData) return false;
    if(!data.Xml) return false;

    var lang = data.Locale;

    $("form#SuppliereDuplicateForm div.flex_info").empty();

    $.each(data.Xml.RklsAnsiPipingClassBasicData.fields, function (key, value) {

        var val = data.RklsAnsiPipingClassBasicData[0][key];
        if(val == "") val = "-";
        var item  = '<div class="flex_item"><div class="label">';
        item += value.discription[lang];
        item += '</div><div class="content" data-model="Rkl" data-field="';
        item += key;
        item += '">';
        item += val;
        item += '</div></div>';
        $("form#SuppliereDuplicateForm div.flex_info").append(item);

    })

  }

	var SearchFormName = "#SuppliereDuplicateForm";

	$(SearchFormName + " input.autocompletion").each(function(key,value){

	var name = $(this).prop("name");
	var id = $(this).prop("id");

	$(this).autocomplete({
	minLength: 2,
	delay: 4,
	source:
		function(request,response) {

			$("#" + id).css("background-image","url(img/indicator.gif)");

			value = name + "[" + request.term + "]"

			var data = new Array();

			data.push({name: name, value: request.term});
			data.push({name: "landig_page_large", value: 1});
			data.push({name: "ajax_true", value: 1});

			$.ajax({
				type: "POST",
				url: $("#SuppliereAutoUrl").val(),
				dataType: "json",
				data: data,
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
			  },
				success:
					function(data) {
						response(data);
					},
				});
			},
		close: function(event,ui) {

			var HiddenSearchField = "#Searching" + event.target.attributes.id.nodeValue;
			var SearchField = "#" + event.target.attributes.id.nodeValue;

			$(SearchField).css("background-image","none");

//			if($(HiddenSearchField).val() == 0) $(SearchField).val("");

		},
		select: function(event,ui) {

				var HiddenSearchField = "#" + ui.item.field;
				var HiddenSearchValue = ui.item.key;

				$(HiddenSearchField).val(HiddenSearchValue);

			},
		response: function( event, ui ) {

			var SearchField = "#" + event.target.attributes.id.nodeValue;
			$(SearchField).css("background-image","none");

		},
		change: function( event, ui ) {

				var data = $(SearchFormName).serializeArray();

				data.push({name: "ajax_true", value: 1});
        data.push({name: "json_true", value: 1});
				data.push({name: "show_search_result_dupli", value: 1});

				$.ajax({
					type: "POST",
					url: $("#SuppliereAutoUrl").val(),
					dataType: "json",
					data: data,
					success:
						function(data) {
              reset_rkls(data);
						},
					});
			},
		});
	});
});
</script>
