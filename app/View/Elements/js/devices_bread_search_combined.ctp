<script type="text/javascript">
$(() => {
  let hideElements = <?php if(isset($hideElements)){ echo $hideElements;} else {echo 0;}?>;

  if(hideElements == 1){
    $('.addsearchingmonitoring').hide();
    $('.addsearching').hide();
  } else {
    $(document).ready(function() {
      $('.addsearching').hide();
      let searchControl = $('.settingslink').append('<div class="searchcontrol"  style="float:left;"></div>');
      
      $('.searchcontrol').empty();
      $('.searchcontrol').append(`<div class="elasticsearch">
      <div class="elasticsearch-content">
      </div>
      </div>`);
      $('.addsearching').appendTo('.elasticsearch-content');
      $('.addsearchingmonitoring').appendTo('.elasticsearch-content');
      $('.elasticsearch').append('<a class="addsearching"></a>');
      $('.addsearching').show();

    });
  }
});
</script>
