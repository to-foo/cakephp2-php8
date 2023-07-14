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

$cakeDescription = __d('cake_dev', '');
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<meta http-equiv="Content-Security-Policy" content="frame-src 'self'">
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta http-equiv="cache-control" content="max-age=0, no-cache, no-store, must-revalidate" />
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title><?php echo $cakeDescription ?></title>

	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('jquery-ui.min.css?'.microtime());
		echo $this->Html->css($this->Navigation->PathToCSS('default'). 'default.css?'.microtime(),null);
//		echo $this->Html->css($this->Navigation->PathToCSS('small'). 'cake.small.css?'.microtime(),null,array('media' => 'screen and (max-width: 1099px)'));

		echo $this->Navigation->PathToSpecificCSS();
		echo $this->Html->script('jquery-3-6-0-min');
		echo $this->Html->script('jquery-ui.min');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
<?php echo $this->fetch('content');?>
<?php echo $this->element('sql_dump'); ?>
</body>
</html>
