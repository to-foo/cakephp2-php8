<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
//header("X-Frame-Options: DENY");
$cakeDescription = __d('cake_dev', '');
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
<!--
	<meta http-equiv="Content-Security-Policy" content="frame-src 'self'">
-->
	<meta name="robots" content="noindex" />
	<meta name="robots" content="nofollow" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta http-equiv="cache-control" content="max-age=0, no-cache, no-store, must-revalidate" />
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title><?php echo $cakeDescription ?></title>

	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('jquery-ui.min');
		echo $this->Html->css('jquery.datetimepicker');
		echo $this->Html->css('multi-select');
		echo $this->Html->css('spectrum');
		echo $this->Html->css('fancybox/jquery.fancybox');
		echo $this->Html->css('dropzone/basic');
		echo $this->Html->css('dropzone/dropzone');
		echo $this->Html->css('select2/select2.min');

		echo $this->Html->css($this->Navigation->PathToCSS('default'). 'default.css?'.microtime(),null);
		echo $this->Navigation->PathToSpecificCSS();

		echo $this->Html->script('jquery-3-6-0-min');
		echo $this->Html->script('jquery-ui.min');
		echo $this->Html->script('dropzone');
		echo $this->Html->script('jquery.datetimepicker');
		echo $this->Html->script('jquery.jeditable.mini');
		echo $this->Html->script('jquery.jeditable.datepicker.min');
		echo $this->Html->script('jquery.ui-contextmenu');
		echo $this->Html->script('jquery.multi-select');
		echo $this->Html->script('taphold');
		echo $this->Html->script('fancybox/jquery.fancybox.min');
		echo $this->Html->script('spectrum');
		echo $this->Html->script('pdfobject.min');
		echo $this->Html->script('bluebird.min');
		echo $this->Html->script('STPadServerLib-3.2.0');
		echo $this->Html->script('browser_history');
		echo $this->Html->script('jquery.select2.min');
		echo $this->Html->script('zxing');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
  </head>
<body>

<noscript><?php echo __('Sorry, this Application will run only with JavaScript, please activate JavaScript in your browser, and try again.', true);?></noscript>

<div id="wrapper_pdf_container" class="wrapper_pdf_container">
<div id="show_pdf_contaniner" class="show_pdf_contaniner"></div>
<div id="show_pdf_container_navi" class="show_pdf_container_navi">
	<?php	echo $this->Html->link(__('Close window'),'javascript:',array('class' => 'show_pdf_contaniner_button','id' => 'show_pdf_contaniner_button', 'title' => __('Close window')));?>
</div>
</div>

<div id="wrapper_pdf_container_assigned_report" class="wrapper_pdf_container">
<div id="show_pdf_contaniner_assigned_report" class="show_pdf_contaniner"></div>
<div id="show_pdf_container_navi_assigned_report" class="show_pdf_container_navi">
<?php
echo $this->Html->link(__('Add report and close window'),'javascript:',
	array(
		'class' => 'show_pdf_contaniner_button',
		'id' => 'show_pdf_contaniner_button_assigned_report_add',
		'title' => __('Add report and close window')
	)
);
echo $this->Html->link(__('Cancel process and close window'),'javascript:',
	array(
		'class' => 'show_pdf_contaniner_button',
		'id' => 'show_pdf_contaniner_button_assigned_report_cancle',
		'title' => __('Cancel process and close window')
	)
);
?>
</div>
</div>

<div id="testdeviceforms"></div>

<div id="container" class="container">
		<?php echo $this->fetch('content'); ?>
</div>
<div id="footer" class="footer"><a href="https://www.mbq-gmbh.info" target="_blank">&copy; MBQ Qualitätssicherung 2022</a></div>

<div class="memory_navigation">
	<?php

	echo $this->Html->link(' ','javascript:',array('class' => 'memory_link maximizemodal','id' => 'maximizethismodal', 'title' => __('Maximize Window')));
	echo $this->Html->link(' ','javascript:',array('class' => 'memory_link searchresultlink','id' => 'searchresultlink', 'title' => __('Show Search results')));
	echo $this->Html->link(' ','javascript:',array('class' => 'memory_link searchresultlinkdevices','id' => 'searchresultlinkdevices', 'title' => __('Show Search results')));
	echo $this->Html->link(' ','javascript:',array('class' => 'memory_link resultlinktickets','id' => 'resultlinktickets', 'title' => __('Show Tickets')));

	echo $this->Html->link(' ','javascript:',array('class' => 'memory_link weldermanagment','id' => 'weldermanagment', 'title' => __('Back to welder managment')));
	echo $this->Html->link(' ','javascript:',array('class' => 'memory_link masterdropdown','id' => 'masterdropdownmanagment', 'title' => __('Back to report')));
	echo $this->element('js/tickets_link_back_to_list');
	echo $this->element('js/searchresult_link_back_to_list');
	echo $this->element('js/searchresultdevice_link_back_to_list');
	echo $this->element('advance/js/link_back_to_list');


	?>
</div>

<div id="dialog"></div>

<svg id="ErrorSVGAnimation" class="svg_request svg_request_1">
    <circle class="circle1" cx="50" cy="50" r="40" fill="none" id="circle1"></circle>
    <path class="line1" d="M30 30 L70 70" fill="none"></path>
    <path class="line2" d="M70 30 L30 70" fill="none"></path>
</svg>

<svg id="SuccessSVGAnimation"  class="svg_request svg_request_2">
    <circle class="circle2" cx="50" cy="50" r="40" fill="none" id="circle2"></circle>
    <path class="line3" d="M25 45 L45 70" fill="none"></path>
    <path class="line4" d="M43 70 L75 30" fill="none"></path>
</svg>

<div id="JsonSvgLoader" class="loader_ani"><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><div class="bar4"></div><div class="bar5"></div><div class="bar6"></div></div>
<svg id="AjaxSvgLoader" class="ajax_loader" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
    <path fill="#333333" d="M25,5A20.14,20.14,0,0,1,45,22.88a2.51,2.51,0,0,0,2.49,2.26h0A2.52,2.52,0,0,0,50,22.33a25.14,25.14,0,0,0-50,0,2.52,2.52,0,0,0,2.5,2.81h0A2.51,2.51,0,0,0,5,22.88,20.14,20.14,0,0,1,25,5Z">
     <animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.5s" repeatCount="indefinite"/>
    </path>
</svg>
</body>
</html>
