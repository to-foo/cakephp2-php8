<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/8/18
 * Time: 9:00 AM
 */

//namespace Advancedideasmechanics\Antivirus;

//use Advancedideasmechanics\Antivirus\Adapter\ClamavSocket as ClamavSocket;
//use Advancedideasmechanics\Antivirus\Adapter\ClamavScan as ClamavScan;

//require_once(dirname(__FILE__).'/ClamavServiceInterface.php');
interface ClamavServiceInterface {

    public function sendToScanner($file);

    public function hello();
}

interface ClamavSocketInterface
{
    public function openSocket($options);

    public function closeSocket($socket);

    public function checkSocket($options);

    public function send($socket, $chunk, $end);
}

interface ClamavScanInterface {
    public function scan($fileHandle, $fileSize, $options);
}

class ClamavService implements ClamavServiceInterface {

    /*
     * $this->option['clamavScanMode'] = 'local' || 'server' || 'cli'
     * local is the default behaviour
     * This tells the socket to use ether the server settings or
     * just connect to local daemon running via socket pid and not a port.
     */
    public $option = [
        'clamavScanMode' => 'local',
        'clamavMaxFileSize' => 25000000,
        'clamavServerHost' => 'localhost',
        'clamavServerPort' => 3310,
        'clamavServerTimeout' => 30,
        'clamavServerSocketMode' => TRUE,
        'clamavLocalSocket' => '/var/run/clamav/clamd.ctl',
        'clamavCliScanner' => '/usr/bin/clamscan',
        'clamavChunkSize' => 2048,
    ];

    public function __construct($options = null) {

        if(!extension_loaded('sockets')) {
            return ['message' => "Sockets not enabled"];
        }
            if(is_array($options)) {
                if(isset($options['clamavScanMode'])){
                    $this->option['clamavScanMode'] = $options['clamavScanMode'];
                }

                if(isset($options['clamavMaxFileSize'])){
                    $this->option['clamavMaxFileSize'] = $options['clamavMaxFileSize'];
                }

                if(isset($options['clamavServerHost'])){
                    $this->option['clamavServerHost'] = $options['clamavServerHost'];
                }

                if(isset($options['clamavServerPort'])){
                    $this->option['clamavServerPort'] = $options['clamavServerPort'];
                }

                if(isset($options['clamavServerTimeout'])){
                    $this->option['clamavServerTimeout'] = $options['clamavServerTimeout'];
                }

                if(isset($options['clamavServerSocketMode'])){
                    $this->option['clamavServerSocketMode'] = $options['clamavServerSocketMode'];
                }

                if(isset($options['clamavLocalSocket'])){
                    $this->option['clamavLocalSocket'] = $options['clamavLocalSocket'];
                }

                if(isset($options['clamavCliScanner'])) {
                    $this->option['clamavCliScanner'] = $options['clamavCliScanner'];
                }

                if(isset($options['clamavChuckSize'])){
                    $this->option['clamavChunkSize'] = $options['clamavChunkSize'];
                }
        }

    }

    public function sendToScanner($file)
    {
        $response = null;
        $openedFile = null;
        $checkClamav = $this->checkClamav();

        if($checkClamav['message'] == "ClamAV is Alive!") {
            $openedFile = fopen($file, "rb");
            /*
             * Check is file exists or opens
             */
            if(!$openedFile) {
                return ['message' => 'File not found or unable to open'];
            }

            $openedFilesize = filesize($file);

            if($openedFilesize <= $this->option['clamavMaxFileSize']) {
                $clamavScan = new ClamavScan();
                switch($this->option['clamavScanMode']) {
                    case 'cli':
                        $response = $clamavScan->scan($file, $openedFilesize, $this->option);
                        break;
                    default:
                        $response = $clamavScan->scan($openedFile, $openedFilesize, $this->option);
                }

            } else {
                $response =  ['message' => 'File is to large for clamav\'s ' . $this->options['clamavMaxFilesize'] . '. This file is: ' . $openedFilesize];
            }
            fclose($openedFile);
            return $response;


        } else {
            return ['message' => 'ClamAV is not available.'];
        }
    }

    public function checkClamav() {
        $response = null;
        /*
         * Send Ping to ClamAV Service
         * Want a better way to handle this
         */
        switch($this->option['clamavScanMode']){
            case "cli":
                if(is_file($this->option['clamavCliScanner'])) {
                    $response['message'] = "ClamAV is Alive!";
                } else {
                    $response['message'] = "ClamAV is not available or not found";
                }
                break;
            default:
                $socket = new ClamavSocket();
                $response = $socket->checkSocket($this->option);
        }
        return $response;
    }

    public function hello() {
        return ["message" => "hello"];
    }

}

class ClamavSocket implements ClamavSocketInterface{

    public function __construct($options = null) {

    }

    public function openSocket($options) {
        /*
         * Socket should be opened as non-blocking
         * stream_socket_client()
         * stream_set_blocking($stream, FALSE)
         */

        $socket = null;
        $message = null;
        $errorno = null;
        $errorstr = null;

        if($options['clamavScanMode'] != 'cli') {

            $clamavServer = $options['clamavServerHost'];
            $clamavServerPort = $options['clamavServerPort'];

            switch($options['clamavScanMode']) {
                case 'server':
                    $socket = stream_socket_client("tcp://$clamavServer:$clamavServerPort", $errorno, $errorstr, $options['clamavServerTimeout']);
                    break;
                default:
                    $socket = stream_socket_client("unix://".$options['clamavLocalSocket'], $errorno, $errorstr, $options['clamavServerTimeout']);
            }

            if(!$socket) {
                $message = "$errorstr ($errorno)";
                return ['message' => $message];
            }
                if ($options['clamavServerSocketMode'] === false && $options['clamavScanMode'] == 'server') {
                    stream_set_blocking($socket, FALSE);
                }
            return $socket;
        }
    }

    public function closeSocket($socket) {
            fclose($socket);
    }

    public function checkSocket($options)
    {
        $options['clamavServerSocketMode'] = TRUE;
        $socket = $this->openSocket($options);

        $pingResponse = null;

        if ($options['clamavServerSocketMode'] === false && $options['clamavScanMode'] == 'server') {
            /*
             * Turn off blocking till the PING happens. Not sure this a great option.
             * May probably need to open a new Socket to test PING.
             * Currently this may send screw a scan. Hopefully because IDSESSION or INSTREAM is sent
             * It will ignore.
             */
            stream_set_blocking($socket, TRUE);
            fwrite($socket, "PING", 4);
            $pingResponse = fread($socket,4);
            stream_set_blocking($socket, FALSE);

        } else {
            fwrite($socket, "PING", 4);
            $pingResponse = fread($socket,4);
        }

        $this->closeSocket($socket);

        if ($pingResponse == "PONG") {
            return ['message' => 'ClamAV is Alive!'];
        } else {
            return ['message' => 'ClamAV is NOT Alive!'];
        }

    }

    public function send($socket, $chunk, $end = 0) {

        $response = [];
        $sentData = 0;
        $cmdLength = strlen($chunk);

        /*
         * If a fwrite does not write the full length because socket gets another packet
         * Track the amount written and continue to try and write the rest.
         * May need to include this with stream_select if statement. or move stream_select into while loop.
         */
        while ($sentData< $cmdLength) {
            $fwrite = fwrite($socket, substr($chunk, $sentData));
            if($end == 1) {

                $response['message'] = trim(substr(strstr(stream_get_contents($socket, 255), ':'), 1));
            }
            $sentData += $fwrite;
            $response['written'] = $sentData;

        }

        return $response;

    }
}

class ClamavQueue {
    /*
     * Current unused. This will support send files to ClamAV and then coming back to check.
     */
    public function __construct($options = null) {

    }
}

class ClamavScan implements ClamavScanInterface
{

    /*
     * Connecting to clamav requires zINSTREAM '<length><data>'
     * 4 byte unsigned integer network byte order
     * Possible use of zIDSESSION to build a Queue system for larger files and higher traffic servers.
     */

    public function __construct($options = null)
    {

    }

    public function scan($fileHandle, $fileSize, $options)
    {

        $response = null;

        switch ($options['clamavScanMode']) {
            case 'cli':
                exec($options['clamavCliScanner'] . ' ' . escapeshellarg($fileHandle), $execResponse);
                $response['message'] = trim(substr(strstr($execResponse[0], ':'), 1));
                break;
            default:
                $zInstream = "zINSTREAM\0";

                $socket = new ClamavSocket();
                $openSocket = $socket->openSocket($options);
                /*
                     * Check if clamav is available if not return message
                     */

                $checkSocket = $socket->checkSocket($options);
                if ($checkSocket['message'] != "ClamAV is Alive!") {
                    return $checkSocket;
                }

                $sendResponse['instream'] = $socket->send($openSocket, $zInstream);

                $chunkDataSent = 0;
                $chunkDataLength = $fileSize;

                while ($chunkDataSent < $chunkDataLength) {
                    fseek($fileHandle, $chunkDataSent);
                    $chunk = fread($fileHandle, $options['clamavChunkSize']);
                    $chunkLength = pack("N", strlen($chunk));
                    /*
                     * Check if clamav is available if not return message
                     */
                    if ($checkSocket['message'] != "ClamAV is Alive!") {
                        return $checkSocket;
                    }
                    $chunkLengthResponse = $socket->send($openSocket, $chunkLength);

                    $chunkDataResponse = $socket->send($openSocket, $chunk);
                    $chunkDataSent += $chunkDataResponse['written'];

                }
                /*
                     * Currently do not need to send zero string to Clamav with this code.
                     * Leaving it here for the time being for update to how a file is sent to clamvav host socket.
                     */
                $endInstream = pack("N", strlen("")) . "";
                /*
                 * Check if clamav is available if not return message
                */
                $checkSocket = $socket->checkSocket($options);
                if ($checkSocket['message'] != "ClamAV is Alive!") {
                    return $checkSocket;
                }
                $response = $socket->send($openSocket, $endInstream, 1);
                $socket->closeSocket($openSocket);
                return $response;

        }
    }
}
