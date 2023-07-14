<script>
//UID für Uploadzonen
var uniqueId = async _ => {
  var idStrLength = 32;
  var idStr = (Math.floor((Math.random() * 25)) + 10).toString(36) + "_";
  idStr += (new Date()).getTime().toString(36) + "_";
  do {
    idStr += (Math.floor((Math.random() * 35))).toString(36);
  } while (idStr.length < idStrLength);
  return (idStr);
};

//Crypto API Check
var secureConnection = _ => {
  if (typeof(self.crypto.randomUUID) === "function"){
    try {
      return true;
    } catch(e){
      throw Error(e);
      return false;
    }
  } else {
    if (window.location.protocol != "https:"){
      throw Error("Unsecure connection! Can't generate UUID. Try HTTPS instead.");
      return false;
    }
  }
};

var generateForm = async logContainerUUID => {
  let logEntrys = $('#contents');
  let btnNewLogEntry = $('#btnNewLogEntry');
  let currentChildCount = logEntrys.children().length;
  let logContainer = $("<div id='logContainer'></div>");

  logContainer.attr("data-id", currentChildCount);
  logContainer.attr("data-uuid", logContainerUUID);

  let form = $("<form id='ChangelogDataAddForm" + currentChildCount + "' class='logentry login' action='mps/changelogs/add' method='post' accept-charset='utf8'></form>");
  let fieldset = $("<fieldset><fieldset>");

  form.append(fieldset);
  logContainer.append(form);

  let divInputText = $("<div class='input text'></div>");
  let labelTitle = $("<label for='ChangelogDataTitle'><?php echo __('Title') ?></label>");
  let inputTitle = $('<input id="ChangelogDataTitle" name="data[ChangelogData' + currentChildCount + '][title]" maxlength="255" type="text"></input>');

  divInputText.append(labelTitle);
  divInputText.append(inputTitle);
  fieldset.append(divInputText);

  let divInputTextArea = $("<div class='input textarea'></div>");
  let labelContent = $("<label for='ChangelogDataContent'><?php echo __('Content') ?></label>");
  let textAreaContent = $('<textarea id="ChangelogDataContent" name="data[ChangelogData' + currentChildCount + '][content]" cols="30" rows="6" type="text"></textarea>');

  divInputTextArea.append(labelContent);
  divInputTextArea.append(textAreaContent);
  fieldset.append(divInputTextArea);

  let divInputCategory = $("<div class='input text'></div>");
  let labelCategory = $("<label for='ChangelogDataCategory'><?php echo __('Category') ?></label>");
  let inputCategory = $('<select id="ChangelogDataCategory" name="data[ChangelogData' + currentChildCount + '][category]" maxlength="255" required="required"></select>');
  let optionsCategoryHinweis = $('<option value="hint">Hinweis</option>');
  let optionsCategoryBugfix = $('<option value="bugfix">Bugfix</option>');
  let optionsCategoryFunktionsupdate = $('<option value="update">Funktionsupdate</option>');
  let optionsCategoryAenderung = $('<option value="change">Änderung</option>');
  let optionsCategoryTesteintrag = $('<option value="test">Testeintrag</option>');

  inputCategory.append(optionsCategoryHinweis);
  inputCategory.append(optionsCategoryBugfix);
  inputCategory.append(optionsCategoryFunktionsupdate);
  inputCategory.append(optionsCategoryAenderung);
  inputCategory.append(optionsCategoryTesteintrag);

  divInputCategory.append(labelCategory);
  divInputCategory.append(inputCategory);
  fieldset.append(divInputCategory);

  let divImageUpload = $("<div class='uploadform'></div>");
  let uploadURL = '<?php echo $this
  ->Form
  ->input('ThisUploadUrl', array(
    'type' => 'hidden',
    'value' => $this
    ->request
    ->here
  )); ?>';
  let maxFileSize = '<?php $this
  ->Form
  ->input('ThisMaxFileSize', array(
    'type' => 'hidden',
    'value' => (int)(ini_get('upload_max_filesize'))
  )); ?>';
  let acceptedFiles = '<?php $this
  ->Form
  ->input('ThisAcceptedFiles', array(
    'type' => 'hidden',
    'value' => "image/jpeg,image/png"
  )) ?>';

  let formImageUpload = $('<?php echo $this->element('form_upload_changelog') ?>');
  let uid = await uniqueId();
  $(formImageUpload).attr("id", uid);
  $(formImageUpload).find('#OrderUuid').val(logContainerUUID);


  divImageUpload.append(uploadURL);
  divImageUpload.append(maxFileSize);
  divImageUpload.append(acceptedFiles);

  divImageUpload.append(formImageUpload);
  logContainer.append(divImageUpload);

  let btnDeleteEntry = $('<a id="btnDeleteLogEntry" class="changelog_delete dellink" onclick="deleteLogEntry(' + currentChildCount + ')" title="<?php echo __('Delete'); ?>"></a>');

  logContainer.append(btnDeleteEntry);
  logEntrys.append(logContainer);

  addDropzone(uid);
};

var createLogEntry = async _ => {
  if(secureConnection())
    var logContainerUUID = self.crypto.randomUUID();
  else
    return;

  await generateForm(logContainerUUID);
};


var addDropzone = uid => {
  let fieldLabel = '<?php echo  __('Fileupload') ?>';
  let drop = new Dropzone('#' + uid, {
    paramName: "file",
    acceptedFiles: 'image/*',
    autoProcessQueue: false,
    parallelUploads: 10,
    addRemoveLinks: true,
    url: 'changelogs/dataupload',
    dictDefaultMessage: fieldLabel,
    init: function() {
      this.element.querySelector("button[type=submit]").style.visibility = "hidden";
      this.element.querySelector("button[type=submit]").addEventListener("click", function(e)	{
        e.preventDefault();
        e.stopPropagation();
        drop.processQueue();
        return false;
      });

      var totalFiles = 0,
      completeFiles = 0;

      this.on("addedfile", function (file) {
      /*  if (this.files.length > 1) {
          this.removeFile(this.files[0]);
        }*/
      });
      this.on("removed file", function (file) {
        totalFiles -= 1;
      });
      this.on("complete", function (file) {

        var maxsize = 100000000;
        var filesize = file.width * file.height * 3;

        if(maxsize > filesize){
          drop.removeFile(file);
        } else {
          $(file.previewElement).removeClass("dz-success");
          $(file.previewElement).addClass("dz-error").find('.dz-error-message').text("File to big!");
        }

        completeFiles += 1;

        if (completeFiles === totalFiles) {
          $("#AjaxSvgLoader").show();
        }
      });
    }
  })
};

  $("#UploadFormSendButton").click(function(){
    return false;
  });

var deleteLogEntry = dataID => {
  $('#contents').children('div').each(function () {
    if ($(this).attr('data-id') == dataID){
      $(this).remove();
    }
  });
};

$(document).ready(function(){
  //Submit überschreiben und Daten sammeln
  $('input[type="submit"]').click(function() {
    let mainForm = $("#ChangelogAddForm");
    var dataMain = $(mainForm).serializeArray();
    var childs = [];

    dataMain.push({name: "ajax_true", value: 1});
    dataMain.push({name: "dialog", value: 1});
    dataMain.push({name: "show_result", value: 1});

    var childCount = 0;

    $('#contents form.logentry').each(function() {
      let data = $(this).serializeArray();

      let currentID = $(this).attr('id');
      let currentUUID = $(this).parent( ).attr('data-uuid');
      let currentDataID = $(this).parent().attr('data-id');

      dataMain.push.apply(dataMain, data);
      dataMain.push({
        name: 'data[ChangelogData' + currentDataID + "][identifier]",
        value: currentUUID
      });

      childs.push(currentDataID);
      childCount++;
    });

    dataMain.push({name: "changelog_data_child_index", value: childs});

    $.ajax({
      type	: "POST",
      cache	: false,
      url		: $(mainForm).attr("action"),
      data	: dataMain,
      success: function(data) {
        handleDropzones();
        $("#AjaxSvgLoader").hide();
        $("#dialog").html(data);
        $("#dialog").show();
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
    return false;
  });

  var handleDropzones = () => {
    $('.dropzone').each(function() {
      let currentDropzone = $(this).get(0).dropzone;
      currentDropzone.processQueue();
    });
  };
});
</script>
