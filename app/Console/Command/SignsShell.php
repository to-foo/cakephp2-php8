<?php
App::uses('ComponentCollection', 'Controller');
App::uses('XmlComponent', 'Controller/Component');

class SignsShell extends AppShell
{
    public function DecryptSigns()
    {
        $path = Configure::read('SignatoryPfad');

        $files = self::_getDirectoryList($path);
        self::_decryptFilename($files);
        self::_decryptTopprojectDirectories($path);
        self::_decryptReportDirectories($path);
    }

    private function _decryptReportDirectories($path)
    {
        $directories = glob($path . '*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $subDirectories = glob($dir . '/*', GLOB_ONLYDIR);
            foreach ($subDirectories as $subDir) {
                $dirname = basename($subDir);
                $decrypted_dir = Security::cipher(hex2bin(basename($dirname)), Configure::read('SignatoryHash'));

                if (rename($subDir, realpath(dirname($subDir)) . DS . $decrypted_dir)) {
                    $this->out($subDir . ' erfolgreich');
                } else {
                    $this->out($subDir . ' fehlgeschlagen');
                }
            }
        }
    }

    private function _decryptTopprojectDirectories($path)
    {
        $directories = glob($path . '*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $dirname = basename($dir);
            $decrypted_dir = Security::cipher(hex2bin(basename($dirname)), Configure::read('SignatoryHash'));

            if (rename($dir, realpath(dirname($dir)) . DS . $decrypted_dir)) {
                $this->out($dir . ' erfolgreich');
            } else {
                $this->out($dir . ' fehlgeschlagen');
            }
        }
    }

    private function _decryptFilename($files)
    {
        if (isset($files['file'])) {
            foreach ($files['file'] as $file) {
                $dirname = pathinfo($file, PATHINFO_DIRNAME);

                $dirname = pathinfo($file, PATHINFO_DIRNAME);
                $newFile =  $dirname . DS . Security::cipher(hex2bin(basename($file)), Configure::read('SignatoryHash'));
                if (rename($file, $newFile)) {
                    $this->out($newFile . ' erfolgreich');
                } else {
                    $this->out($newFile . ' fehlgeschlagen');
                }
            }
        }
    }

    //Security::cipher(hex2bin($file->getFilename()), Configure::read('SignatoryHash'));
    private function _getDirectoryList($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results['file'][] = $path;
            } elseif ($value != "." && $value != "..") {
                self::_getDirectoryList($path, $results);
                $results['dir'][] = $path;
            }
        }

        return $results;
    }
}
