<script type="text/javascript">
$(document).ready(function(){

  $("span.for_hasmenu1").contextmenu({
  delegate: ".hasmenu1",
  autoFocus: true,
  preventContextMenuForPopup: true,
  preventSelect: true,
  taphold: true,
  menu: [
  {
  title: "Gesamte Naht bearbeiten",
  cmd: "editevalution",
  action :	function(event, ui) {
        $("#container").load("reportnumbers/editevalution/" + ui.target.attr("rev"), {
            "ajax_true": 1
          })
        },
  uiIcon: "qm_edit"
  },
  {
  title: "----"
  },
  {
  title: "Gesamte Naht duplizieren",
  cmd: "duplicatevalution",
  action :	function(event, ui) {
          checkDuplicate = confirm("Soll die gesamte Naht dupliziert werden?");
          if (checkDuplicate == false) {
            return false;
          }
          $("#container").load("reportnumbers/duplicatevalution/" + ui.target.attr("rev"), {
            "ajax_true": 1
          })
        },
  uiIcon: "qm_duplicate"
  },

  {
  title: "----"
  },
  {
  title: "Print Label",
  cmd: "printweldlabel",
  action :	function(event, ui) {
          window.open("reportnumbers/printweldlabel/" + ui.target.attr("rev"));
        },
  uiIcon: "qm_label",
  disabled: false
  },
  {
  title: "----"
  },
  {
  title: "Show QR-Code",
  cmd: "showqrcode",
  action :	function(event, ui) {
        $("#dialog").load("reportnumbers/showqrcode/" + ui.target.attr("rev"), {
            "ajax_true": 1
          });
        $("#dialog").dialog("open");
        },
  uiIcon: "qm_qr_code",
  disabled: false
  },
  {
  title: "----"
  },
  {
  title: "Gesamte Naht löschen",
  cmd: "deleteevalution",
  action :	function(event, ui) {
          checkDuplicate = confirm("Soll die gesamte Naht gelöscht werden?");
          if (checkDuplicate == false) {
            return false;
          }
          $("#container").load("reportnumbers/deleteevalution/" + ui.target.attr("rev"), {
            "ajax_true": 1
          })
        },
  uiIcon: "qm_delete"
  },

  ],

  select: function(event, ui) {},
  });

  $("span.for_hasmenu2").contextmenu({
  delegate: ".hasmenu2",
  autoFocus: true,
  preventContextMenuForPopup: true,
  preventSelect: true,
  taphold: true,
  menu: [
  {
  title: "Diesen Nahtabschnitt bearbeiten",
  cmd: "editevalution",
  action :	function(event, ui) {
        $("#container").load("reportnumbers/editevalution/" + ui.target.attr("rev"), {
            "ajax_true": 1
          })
        },
  uiIcon: "qm_edit"
  },
  {
  title: "----"
  },
  {
  title: "Diesen Nahtabschnitt duplizieren",
  cmd: "duplicatevalution",
  action :	function(event, ui) {
          checkDuplicate = confirm(" ' . __('Soll dieser Nahtabschnitt dupliziert werden?') . '");
          if (checkDuplicate == false) {
            return false;
          }
          $("#container").load("reportnumbers/duplicatevalution/" + ui.target.attr("rev"), {
            "ajax_true": 1
          })
        },
  uiIcon: "qm_duplicate"
  },
  {
  title: "----"
  },
  {
  title: "Diesen Nahtabschnitt löschen",
  cmd: "deleteevalution",
  action :	function(event, ui) {
          checkDuplicate = confirm("' . __('Soll dieser Nahtabschnitt gelöscht werden?') . '");
          if (checkDuplicate == false) {
            return false;
          }
          $("#container").load("reportnumbers/deleteevalution/" + ui.target.attr("rev"), {
            "ajax_true": 1
          })
        },
  uiIcon: "qm_delete"
  },

  ],

  select: function(event, ui) {},
  });
});
</script>
