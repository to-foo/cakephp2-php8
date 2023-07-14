<?php
echo '<div class="examiner_stamp">';
echo '<br/>';
echo '<img class="examiner_stamp" src="data:image/svg+xml;utf8,'. $ImageData . '">';
echo $this->Html->link(
  __('Delete Stamp'),
  'javascript:',
  array('class' => 'stamp_delete dellink', 'id' => 'LnkDeleteStamp', 'title' => __('Delete stamp'))
);
echo '</div>';
?>

<script>
$(document).ready(function(e) {
    let editurl = window.location.origin + '/examiners/stamps/' + '<?php echo $projectsvars; ?>';
    $("#LnkDeleteStamp").click(function() {
        var data = $(this).serializeArray();
        data.push({
            name: "ajax_true",
            value: 1
        });
        data.push({
            name: "delete_stamp",
            value: 1
        });

				data.push({
						name: "examiner_id",
						value: '<?php echo $examiner_id; ?>'
				});

        $.ajax({
            type: "POST",
            cache: false,
            url: window.location.origin + '/examiners/delete_stamp',
            data: data,
            success: function(data) {
              var data = new Array();

              data.push({name: "ajax_true", value: 1});
              data.push({name: "dialog", value: 1});

              $.ajax({
                type	: "POST",
                cache	: false,
                url		: editurl,
                data	: data,
                success: function(data) {
                  let new_stamp_container = $(data).find('#stamp_container');
                  $('#stamp_container').html(new_stamp_container);
                  $("#AjaxSvgLoader").hide();
                }
              });
            },
            statusCode: {
                404: function() {
                    alert("page not found");
                    location.reload();
                }
            },
            statusCode: {
                403: function() {
                    alert("page blocked");
                    location.reload();
                }
            }
        });
        return false;
    });
});
</script>
