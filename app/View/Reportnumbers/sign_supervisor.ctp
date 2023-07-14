<div class="quicksearch">
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
</div>
<div class="reportnumbers detail">
<h2><?php echo $this->Pdf->ConstructReportName($reportnumber) ?></h2>
<div class="clear edit">
<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>

</div>

	<?php
		echo $this->Form->create('Reportnumber', array('class' => 'signform', 'url' => array('action' => 'sign',
			$this->request->projectvars['VarsArray'][0],
			$this->request->projectvars['VarsArray'][1],
			$this->request->projectvars['VarsArray'][2],
			$this->request->projectvars['VarsArray'][3],
			$this->request->projectvars['VarsArray'][4],
			$this->request->projectvars['VarsArray'][5],
			$this->request->projectvars['VarsArray'][6],
			$this->request->projectvars['VarsArray'][7],
		)));
	?>

	<h3><?php echo __('Signatur Supervisor');?></h3>
    	<div id="ctlSignature_Container" style="width:100%;height:80%">

    	<script language="javascript" type="text/javascript">

            var ieVer = getInternetExplorerVersion();
            if (isIE) {
                if (ieVer >= 9.0)
                    isIE = false;
            }

            if (isIE)
            {
 				$('form.signform div.canvas').append('<div ID="ctlSignature" style="width:400px;height:200px;z-index:50000"></div>');
            }
            else
            {
 				$('form.signform div.canvas').append('<canvas ID="ctlSignature" width="400" height="200"></canvas>');
            }

         </script>
         <?php
		 echo $this->Form->input('ctlSignature_examiner_file',array('value' => uniqid() . '.png','type' => 'hidden','id' => 'ctlSignature_examiner_file','name' => 'ctlSignature_examiner_file'));
		 echo $this->Form->input('signatur',array('value' => 'supervisor','type' => 'hidden','id' => 'signatur','name' => 'signatur'));

		 ?>
		<div class="canvas"></div>
	</div>
 <script type="text/javascript">
  	var signObjects = new Array('ctlSignature');

	var objctlSignature = new SuperSignature({
			SignObject:"ctlSignature",
			SignWidth: "400",
			TransparentSign:"false",
			SignHeight: "200",
			IeModalFix: false,
			PenColor: "#0000FF",
			BorderStyle: "Dashed",
			BorderWidth: "2px",
			BorderColor: "#DDDDDD",
			RequiredPoints: "15",
			ClearImage:"img/icon_blue_negativ_refresh_single.png",
			PenCursor:"img/cursor.cur",
			SuccessMessage: "<?php __('Save Signature?');?>",
			Visible: "true"
	});

	$(document).ready(function()
	{
	  objctlSignature.Init();
//	  $('#ctlSignature').width($('#ctlSignature_Container').width());
//	  $('#ctlSignature').height($('#ctlSignature_Container').height());
//	  ResizeSignature("ctlSignature", $('#ctlSignature_Container').width(), $('#ctlSignature_Container').height());
	});


   </script>

<?php
echo '<div class="buttons">';
echo $this->Form->end(__('Submit', true));
echo '</div>';
?>
</div>

	   <script language="javascript" type="text/javascript">
	    var signW = 0;
            var signH = 0;

	    function Resize()
            {
		var ppi = dpi.get(false);
          	signW  = parseInt(window.innerWidth) - parseInt((40 * ppi / 100));
          	signH  = parseInt(window.innerHeight) - parseInt((55 * ppi / 100));

          	ResizeSignature("ctlSignature", signW, signH);
            }

        var dpi = {
           v: 0,
           get: function (noCache) {
              if (noCache || dpi.v == 0) {
                 e = document.body.appendChild(document.createElement('DIV'));
                 e.style.width = '1in';
                 e.style.padding = '0';
                 dpi.v = e.offsetWidth;
                 e.parentNode.removeChild(e);
            }
             return dpi.v;
           }
        }

        $(document).ready(function () {

                window.addEventListener("resize", function () {
                var signData = Base64.decode($('#ctlSignature_data').val());

		var tmpW = signW;
		var tmpH = signH;

                Resize();
                ClearSignature("ctlSignature");

                if(signData.length > 0)
                {
		   signData = signData.replace(tmpW + ',' + tmpH, signW + ',' + signH);
                   LoadSignature("ctlSignature", signData, 1);
                }

            }, false);


            $("ctlSignature_Container").css('margin', '0px');
            $('body').css('height', '100%');
            $('body').css('width', '100%');


           dpi.get(true);

        });

         $(window).load(function () { Resize();});

    </script>

<?php echo $this->JqueryScripte->LeftMenueHeight($reportnumber); ?>
