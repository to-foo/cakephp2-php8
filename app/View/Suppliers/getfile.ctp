<?php
$path = $savePath;
$speed = null;
    	if (is_file($path) === true) {
			set_time_limit(0);

			while (ob_get_level() > 0) {
				ob_end_clean();
			}

			$size = sprintf('%u', filesize($path));
			$speed = (is_null($speed) === true) ? $size : intval($speed) * 1024;

			header('Expires: 0');
			header('Pragma: public');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: application/octet-stream');
			header('Content-Length: ' . $size);
			header('Content-Disposition: attachment; filename="' . $file['Supplierfile']['basename'] . '"');
			header('Content-Transfer-Encoding: binary');

			for($i = 0; $i <= $size; $i = $i + $speed)	{
				echo file_get_contents($path, false, null, $i, $speed);

				while (ob_get_level() > 0){
					ob_end_clean();
				}

				flush();
				sleep(1);
			}

			exit();
		}

	return false;