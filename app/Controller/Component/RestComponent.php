<?php
class RestComponent extends Component
{
    protected $_controller = null;

    public function initialize(Controller $controller)
    {

      $this->_controller = $controller;

    }

    public function JsonDecode($data){

      if(!isset($data['TicketData'])) return $data;
      if(empty($data['TicketData'])) return $data;

      foreach ($data['TicketData'] as $key => $value) {

        if(empty($data['TicketData'][$key]['json'])) continue;

        $data['TicketData'][$key]['json_decode'] = json_decode($data['TicketData'][$key]['json'],true);

      }

      return $data;
    }

    public function CollectTicketReports($data){

      $output = array();

      if($data['Ticket']['status'] == 0) $output['data_area'] = $this->CollectTicketOpenReports($data);
      if($data['Ticket']['status'] == 1) $output['data_areaclosed']  = $this->CollectTicketcloseReports($data);

      return $output;
    }

    public function CollectTicketOpenReports($data,$mode = 1){

      $Reportnumbers = $this->_controller->TicketReportnumber->find('all',array('conditions'=>array('ticket_id'=>$data['Ticket']['id'])));

      if(empty($Reportnumbers))  return $data;

      foreach($Reportnumbers as $r_key => $r_value) {
        $current_id = $r_value['TicketReportnumber']['reportnumber_id'];
        $reportnumber = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $current_id)));
        $number = $reportnumber['Reportnumber']['number'];
        $year = $reportnumber['Reportnumber']['year'];
        $testingmethod_name = $reportnumber['Testingmethod']['name'];

        $r_value['TicketReportnumber']['testingmethod_id'] = $reportnumber['Reportnumber']['testingmethod_id'];
        $r_value['TicketReportnumber']['name'] = $testingmethod_name;
        $r_value['TicketReportnumber']['year'] = $year;
        $r_value['TicketReportnumber']['number'] = $number;
        $r_value['TicketReportnumber']['status'] = $reportnumber['Reportnumber']['status'];
        $Reportnumbers[$r_key] = $r_value;
        $reportnumber['Reportnumber']['url'] = array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id']);
        $reportnumber['Reportnumber']['status_class'] = 'icon_close_' . $reportnumber['Reportnumber']['status'];

        $Reportnumbers[$r_key]['Reportnumber'] = $reportnumber['Reportnumber'];

      }

      $data['OpenReports']['Reportnumbers'] = $Reportnumbers;

      if($mode == 2) $data['OpenReports'] = $this->_CollectReportsDeep($data['OpenReports']);

      return $data;
    }

    public function CollectTicketcloseReports($data,$mode = 1){

      $Reportnumbers = $this->_controller->TicketReportnumber->find('all',array('conditions'=>array('ticket_id'=>$data['Ticket']['id'])));

      if(empty($Reportnumbers)) return $data;

      foreach($Reportnumbers as $r_key => $r_value) {

        $current_id = $r_value['TicketReportnumber']['reportnumber_id'];
        $reportnumber = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $current_id)));
        $number = $reportnumber['Reportnumber']['number'];
        $year = $reportnumber['Reportnumber']['year'];
        $testingmethod_name = $reportnumber['Testingmethod']['name'];

        $r_value['TicketReportnumber']['testingmethod_id'] = $reportnumber['Reportnumber']['testingmethod_id'];
        $r_value['TicketReportnumber']['name'] = $testingmethod_name;
        $r_value['TicketReportnumber']['year'] = $year;
        $r_value['TicketReportnumber']['number'] = $number;
        $r_value['TicketReportnumber']['status'] = $reportnumber['Reportnumber']['status'];

        $Reportnumbers[$r_key] = $r_value;
        $reportnumber['Reportnumber']['url'] = array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id']);
        $reportnumber['Reportnumber']['status_class'] = 'icon_close_' . $reportnumber['Reportnumber']['status'];

        $Reportnumbers[$r_key]['Reportnumber'] = $reportnumber['Reportnumber'];

      }

      $data['CloseReports']['Reportnumbers'] = $Reportnumbers;

      if($mode == 2) $data['CloseReports'] = $this->_CollectReportsDeep($data['CloseReports']);

      return $data;
    }

    protected function _CollectReportsDeep($data){

      if(empty($data)) return $data;
      if(empty($data['Reportnumbers'])) return $data;

      foreach ($data['Reportnumbers'] as $key => $value) {

        $verfahren = strtolower($value['TicketReportnumber']['name']);
        $Verfahren = ucfirst($verfahren);

        $EvaluationModel = 'Report'.$Verfahren.'Evaluation';
        $this->_controller->loadModel($EvaluationModel);

        $this->_controller->Testingcomp->recursive = -1;

        $Evaluation = $this->_controller->$EvaluationModel->find('all',
          array(
            'fields' => array('id','description','sheet_no','spool_id','weld_system_uid'),
            'conditions' => array(
              $EvaluationModel . '.reportnumber_id' => $value['TicketReportnumber']['reportnumber_id']
            )
          )
        );

        $Evaluation = Hash::extract($Evaluation, '{n}.' . $EvaluationModel);

        $data['Reportnumbers'][$key]['Evaluation'] = $Evaluation;

        $Testingcomp = $this->_controller->Testingcomp->find('first',array('conditions' => array('Testingcomp.id' => $value['Reportnumber']['testingcomp_id'])));

        $data['Reportnumbers'][$key]['Testingcomp'] = $Testingcomp['Testingcomp'];

      }

      return $data;
    }

    public function CollectTicketWeldMethod($data){

      if(empty($data)) return $data;

      $this->_controller->Testingmethod->recursive = -1;

      $Output = array();

      foreach ($data as $key => $value) {

        $testingmethod = $this->_controller->Testingmethod->find('first',array('conditions'=>array('Testingmethod.id'=>$value['TicketweldsTestingmethods']['testingmethod_id'])));

        if(!isset($Output[$testingmethod['Testingmethod']['name']])) $Output[$testingmethod['Testingmethod']['name']] = array();
        if(!isset($Output[$testingmethod['Testingmethod']['name']][$testingmethod['Testingmethod']['id'] . '_' . $value['TicketweldsTestingmethods']['ticketweld_id']])) $Output[$testingmethod['Testingmethod']['name']][$testingmethod['Testingmethod']['id'] . '_' . $value['TicketweldsTestingmethods']['ticketweld_id']] = $value['TicketweldsTestingmethods']['weld_number'];

        ;

      }

      return $Output;
    }

    public function RestRequest(){

      App::uses('Sanitize', 'Utility');

      $output = array();

      if(empty($this->_controller->request->data['barcode'])){

        $output = array('message' => array('error' => __('data string empty',true)));
        return $output;

      }

      $barcodescan = $this->_controller->request->data['barcode'];
      $barcodescan = Sanitize::paranoid($barcodescan);

      if(strlen($barcodescan) > 10){
        $data_array['message']['error'] = __('The length of barcode exceeds the allowed lenght of 10 characters!');
        return $data_array;
      }

      $tokendata = $this->_GetAccessToken();

      $data = array('spool_id' => $barcodescan);
      $datajsn = $this->_GetData($tokendata,$data);

      if(isset($datajsn['message']['error'])) return $datajsn;

      $output = $this->_SaveTicket($datajsn);

      return $output;

    }

    protected function _SaveTicket($data){

      $barcodescan = $this->_controller->request->data['barcode'];

      if(empty($data)) return array();

      $ticketdata = array();
      $data_array = json_decode($data,true);

      if(isset($data_array['status']) && $data_array['status'] == 404){

        $message = $data_array['message'];
        unset($data_array['message']);
        $data_array['message']['error'] = $message;
        return $data_array;

      }

      if (empty($data_array['data'])){

        $data_array = array();

        $data_array['message']['error'] = __('data recort empty');
        return $data_array;

      }

      if (empty($data_array['data']['akz_data'])){

        $data_array = array();

        $data_array['message']['error'] = __('data recort empty');
        return $data_array;

      }

      $ticket_tp = $this->_controller->Ticket->find('first',array('conditions'=>array('Ticket.technical_place'=>$data_array['data']['akz_data'] ['name'],'Ticket.status'=> 0)));

      if(count($ticket_tp) > 0) {

        $ticket_datas = $this->_controller->Ticket->TicketData->find('first',array('conditions'=>array('TicketData.ticket_id'=>$ticket_tp['Ticket']['id'],'TicketData.spool_id' =>$barcodescan)));

        if(count($ticket_datas) > 0) {
      //    $data_array['message']['error'] = __('It already exists a ticket with this spool name! ('.$data_array['data']['name'].')');
        //  return $data_array;
        }

        $ticket = array();
        $ticked['id'] = $ticket_tp['Ticket']['id'];
        $ticked['spools'] = $ticket_tp['Ticket']['spools']."/".$data_array['data']['name'];
        $ticked['count_welds'] =   $ticket_tp['Ticket']['count_welds'] + count($data_array['data']['welds']);
        $ticked['user_id'] = $this->_controller->Auth->user('id');
        $this->_controller->Ticket->save($ticked);

        $ticketdata['ticket_id'] = $ticket_tp['Ticket']['id'];
        $ticketdata['json'] =  $data;
        $ticketdata['spool_id'] =  $barcodescan;
        $ticketdata['spool'] = $data_array['data']['name'];

        if(isset($data_array['data']['sheet_data'])&&!empty($data_array['data']['sheet_data'])) $ticketdata ['sheet_no'] = $data_array['data']['sheet_data']['name'];
        if(isset($data_array['data']['tasks'])&& !empty($data_array['data']['tasks'])) $ticketdata ['task_id'] = $data_array['data']['tasks']['0']['system_uid'];

        $this->_controller->Ticket->TicketData->save($ticketdata);
        $data_array['ticketid'] = $ticketdata['ticket_id'];

        $data_array['message']['success'] = __('The ticket has been successfully created.');

        return $data_array;
      }

      $ticket['technical_place'] = $data_array['data']['akz_data'] ['name'];
      $ticket['topproject_id'] = $this->_controller->request->projectvars['VarsArray'][0];
      $ticket['cascade_id'] = $this->_controller->request->projectvars['VarsArray'][1];
      $ticket['report_id'] = $this->_controller->request->projectvars['VarsArray'][3]; ;
      $ticket['status'] = 0;
      $ticket['count_welds'] = count($data_array['data']['welds']);
      $ticket['spools'] =  $data_array['data']['name'];
      $ticket['user_id'] = $this->_controller->Auth->user('id');

      if($this->_controller->Ticket->save($ticket)) {

        $ticketdata['ticket_id'] = $this->_controller->Ticket->getLastInsertID();
        $ticketdata['json'] =  $data;
        $ticketdata['spool_id'] =  $data_array['data']['system_uid'];
        $ticketdata ['spool'] = $data_array['data']['name'];
        if(isset($data_array['data']['sheet_data'])&&!empty($data_array['data']['sheet_data'])) $ticketdata ['sheet_no'] = $data_array['data']['sheet_data']['name'];
        if(isset($data_array['data']['tasks'])&& !empty($data_array['data']['tasks'])) $ticketdata ['task_id'] = $data_array['data']['tasks']['0']['system_uid'];

        $this->_controller->Ticket->TicketData->save($ticketdata);

        $data_array['message']['success'] = __('The ticket has been successfully created.');
        $data_array['ticketid'] = $ticketdata['ticket_id'];

      } else {
        $data_array['message']['error'] = __('The ticket could not be created. Please try again.');
      }

      return $data_array;

    }

    protected function _GetAccessToken()
    {

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/

        if(Configure::check('AuthUrl') == false) return;
        if(empty(Configure::read('AuthUrl'))) return;

        $url = Configure::read('AuthUrl');
        $url .= "/api/auth/token/refresh";

        if (Configure::check('TokenGet') && !empty(Configure::read('TokenGet'))) {
            $head = Configure::read('TokenGet');
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $headers = array(
          $head,
           "Content-type: application/json",
           //"Content-Length: 0",
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //for debug only!
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        return json_decode($resp, true);


    }

// Test
//################################################################
    protected function _GetAccessLoginTest(){

      if(Configure::check('AuthUrl') == false) return;
      if(empty(Configure::read('AuthUrl'))) return;

      $url = Configure::read('AuthUrl');
      $url .= "/api/auth/login";

      $post = array('login_name' => 'mbq','password' => 'iW$87JTCZ0vJ');
      $post = json_encode($post);

      $headers = array("Content-type: application/json");

      $ch = curl_init();
  
      curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
      curl_setopt($ch, CURLOPT_URL,$url);
      curl_setopt($ch, CURLOPT_POST,1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

      $response = curl_exec($ch);

 pr(json_decode($response));

    }

    protected function _GetAccessTokenTest()
    {

        if (Configure::check('TokenGet') && !empty(Configure::read('TokenGet'))) {
            $head = Configure::read('TokenGet');
        }

        if(Configure::check('AuthUrl') == false) return;
        if(empty(Configure::read('AuthUrl'))) return;
  
        $url = Configure::read('AuthUrl');
        $url .= "/api/auth/token/refresh";  
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $headers = array(
          $head,
           "Content-type: application/json",
           //"Content-Length: 0",
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //for debug only!
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      //  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);

        print('################################');
        print('Access Token: ');
        print($resp);
        print('End of Access Token');
        print('################################');

        return json_decode($resp, true);


    }
// Ended
//########################################################################    

    protected function _GetData($tokendata= '', $data = '')
    {

        if($tokendata == false){

          $data['message']['error'] = __('Invalid Token. Authentication failed.');
          return $data;

        } 
      
        if(Configure::check('AuthUrl') == false){

          $data['message']['error'] = __('Invalid AuthUrl');
          return $data;

        } 

        if(empty(Configure::read('AuthUrl'))){

          $data['message']['error'] = __('Invalid AuthUrl');
          return $data;

        } 

        $url = Configure::read('AuthUrl');
        $url .= "/api/documentation/spool".'?spool_id='.$data['spool_id'];

        $head = 'Authorization: Bearer '. $tokendata['access_token'];

        //// TODO: Ohne Parameter - Fehlermeldung ???? Muss der reset aufruf nicht nach initialisierung kommen?
        $curl = curl_init($url);
        @curl_reset($curl);
        curl_setopt($curl, CURLOPT_URL, $url);
      //  curl_setopt($curl, CURLOPT_POST, true);
      //  curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $headers = array(
          $head,
           "Content-type: application/json",
           "Content-Length: 0",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //for debug only!
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      //  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;


    }

    protected function _SetTask($spool_id, $taskuid,$taskid, $approved) {

      /*

      if(empty($spool_id)) return;
      if(empty($taskid)) return;
      if(empty($taskuid)) return;

      $tokendata = $this->_GetAccessToken();

      if(empty($tokendata)) return;

      App::uses('HttpSocket', 'Network/Http');
      $head= array('Content-Type'=>'application/json','Authorization' => 'Bearer '.$tokendata['access_token']);
      $options = array('header' => $head);
      $data = array(
        "spool_system_uid" => $spool_id,
        "task_system_uid" => $taskuid,
        "task_isopm_id" => $taskid,
        "task_approved" => $approved
      );

      $data = json_encode($data);

      $url = Configure::read('AuthUrl');
      $url .= "/api/documentation/spool/".$spool_id."/task";

      $HttpSocket = new HttpSocket();
      $responsedata = $HttpSocket->post($url, $data, $options);
      $response = $responsedata->body;

      return ;

      */

      if(empty($spool_id)) return;
      if(empty($taskid)) return;
      if(empty($taskuid)) return;

      $tokendata = $this->_GetAccessToken();

      $Logging = true;

      if(empty($tokendata)) return;

      App::uses('HttpSocket', 'Network/Http');
      $head= array('Content-Type'=>'application/json','Authorization' => 'Bearer '.$tokendata['access_token']);
      $options = array('header' => $head);
      $data = array(
        "spool_system_uid" => $spool_id,
        "task_system_uid" => $taskuid,
        "task_isopm_id" => $taskid,
        "task_approved" => $approved
      );
        
      $json = json_encode($data);

      $url = Configure::read('AuthUrl');
      $url .= "/api/documentation/spool/".$spool_id."/task";

      $this->_LoggingDigipipe($Logging,$data,$url);

      $HttpSocket = new HttpSocket();
      $responsedata = $HttpSocket->post($url, $json, $options);
      $response = $responsedata->body;

      $response_array = json_decode($response,true);
      $this->_LoggingDigipipe($Logging,$response_array,$url);

      return ;
     
    }

    protected function _SetTaskTest($spool_id, $taskuid,$taskid, $approved) {

      if(empty($spool_id)) return;
      if(empty($taskid)) return;
      if(empty($taskuid)) return;

      $tokendata = $this->_GetAccessToken();
      $Logging = true;

      if(empty($tokendata)) return;

      App::uses('HttpSocket', 'Network/Http');
      $head= array('Content-Type'=>'application/json','Authorization' => 'Bearer '.$tokendata['access_token']);
      $options = array('header' => $head);
      $data = array(
        "spool_system_uid" => $spool_id,
        "task_system_uid" => $taskuid,
        "task_isopm_id" => $taskid,
        "task_approved" => $approved
      );

//      $data = json_encode($data);
      $json = json_encode($data);

      $url = Configure::read('AuthUrl');
      $url .= "/api/documentation/spool/".$spool_id."/task";

      $this->_LoggingDigipipe($Logging,$data,$url);

      $HttpSocket = new HttpSocket();
      $responsedata = $HttpSocket->post($url, $json, $options);
      $response = $responsedata->body;

      $response_array = json_decode($response,true);
      $this->_LoggingDigipipe($Logging,$response_array,$url);

      return ;
}

  //TODO das kann dann wohl weg
  protected function _SendPdf($spool_id, $report) {

    $PDF = $this->_controller->collectpdfraw();

    $PDF_name = $this->_controller->request->data['PdfName'];

    $tokendata = $this->_GetAccessToken();

    if(empty($tokendata)) return;

    if(Configure::check('AuthUrl') == false) return;
    if(empty(Configure::read('AuthUrl'))) return;

    $url = Configure::read('AuthUrl');
    $url .= "/api/documentation/zfp-report/".$report['Reportnumber']['number'];


    $PDF_string = $this->_controller->request->data['PdfFile'];
    $EvModel = 'Report'.ucfirst($report['Testingmethod']['value'].'Evaluation');
    $this->_controller->loadModel($EvModel);
    $welduids = $this->_controller->$EvModel->find('list',array('fields'=>array($EvModel.'.id',$EvModel.'.weld_system_uid'),'conditions'=>array('reportnumber_id'=>$report['Reportnumber']['id'])));

    $cFile  = new CURLFile(Configure::read('root_folder') . DS . $PDF_name,'application/pdf' );

    if(!empty($cFile)){
      $post = array('weld_system_uids'=>$welduids,'report'=> $cFile);
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data','Authorization: Bearer '.$tokendata['access_token']));
      curl_setopt($ch, CURLOPT_URL,$url);
      curl_setopt($ch, CURLOPT_POST,1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      $response = curl_exec ($ch);
      if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
      }
      curl_close($ch);

      if (isset($error_msg)) {

        $this->_controller->Flash->error('An error occurred while sending the the report', array('key' => 'error'));
      }else{
        $this->_controller->Flash->success('The report was sent successfully to the external system', array('key' => 'success'));
      }

      unlink (Configure::read('root_folder') . DS . $PDF_name);
    }
    return ;

  }

  protected function _CheckSendPdfStatus($report) {

    if(Configure::check('SendTicketReportSentReport') === false) return false;

    $SendTicketReportSentReport = Configure::read('SendTicketReportSentReport');

    if(empty($SendTicketReportSentReport)) return false;

    $StatusArray = explode(' ', $SendTicketReportSentReport);

    if(count($StatusArray) == 0) return false;

    $this->_controller->loadModel('Sign');

    $Status = $this->_controller->Sign->find('list',array(
        'fields' => array(
          'id',
          'signatory'
        ),
        'conditions' => array(
          'Sign.reportnumber_id' => $report['Reportnumber']['id'],
          'Sign.delete' => 0
        )
      )
    );

    sort($Status);

    // Schnittmenge aus der Config und den vorhandenen Unterschriften
    // Sind die Werte aus der Datenbank in der Config vorhanden
    $Check = array_intersect($StatusArray, $Status);

    if(count($Check) > 0) return true;

    return false;
  }

  protected function _SendPdfString($report) {

    if($this->_CheckSendPdfStatus($report) === false) return;
    
    $tokendata = $this->_GetAccessToken();

    $Logging = true;

    if(empty($tokendata)) return;

    $PDF = $this->_controller->collectpdfraw();

    $PDF_name = $this->_controller->request->data['PdfName'];

    $PDF_string = $this->_controller->request->data['PdfFile'];
    $EvModel = 'Report'.ucfirst($report['Testingmethod']['value'].'Evaluation');
    $this->_controller->loadModel($EvModel);

    $welduids = $this->_controller->$EvModel->find('list',
      array(
        'fields' => array(
          $EvModel.'.id',
          $EvModel.'.weld_system_uid'
        ),
        'conditions' => array(
          'reportnumber_id' => $report['Reportnumber']['id'],
          'weld_system_uid !=' => '',
          )
        )
    );

    if(count($welduids) == 0) return;

    $welduids_string = json_encode(array_values($welduids));

    $spooluids = $this->_controller->$EvModel->find('list',
      array(
        'fields' => array(
          $EvModel.'.id',
          $EvModel.'.spool_id'
        ),
        'conditions' => array(
          'reportnumber_id' => $report['Reportnumber']['id'],
          'spool_id !=' => '',
          'spool_id !=' => 0,
          )
        )
    );

    if(count($spooluids) == 0) return false;

    $spooluids_string = json_encode(array_values($spooluids));
    $spooluid = json_encode(array(array_shift($spooluids)));

    $url = Configure::read('AuthUrl');

    //Git
 
    $url .= "/api/documentation/zfp-report/".$report['Reportnumber']['year'] . '_' . $report['Reportnumber']['number'];
    $url .= "?weld_system_uids=" . $welduids_string;
    $url .= "&spool_system_uids=" . $spooluid;

    $cFile  = new CURLFile(Configure::read('root_folder') . DS . $PDF_name,'application/pdf' );
 
    if(!empty($cFile)){  

      $post = array(
        'spool_system_uids' => $spooluid,
        'weld_system_uids' => $welduids_string,
        'report' => $cFile
      );
      
      $ch = curl_init();
  
      curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data','Authorization: Bearer '.$tokendata['access_token']));
      curl_setopt($ch, CURLOPT_URL,$url);
      curl_setopt($ch, CURLOPT_POST,1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      $response = curl_exec($ch);

      $response = json_decode($response,true);

      if (curl_errno($ch)) {

        $error_msg = curl_error($ch);
        if(isset($error_msg)) $this->_LoggingDigipipe($Logging,$error_msg,$url);
        else $this->_LoggingDigipipe($Logging,array('Keine Response Errors '),$url);

      } else {
      }

      $this->_LoggingDigipipe($Logging,$post,$url);
      $this->_LoggingDigipipe($Logging,$response,$url);

      curl_close($ch);
    
      unlink (Configure::read('root_folder') . DS . $PDF_name);
    }

    return ;

  }

  protected function _SendPdfStringTest($report) {

    //    if($this->_CheckSendPdfStatus($report) === false) return;

    $tokendata = $this->_GetAccessToken();

    $Logging = true;

//    if(empty($tokendata)) return;

    $PDF = $this->_controller->collectpdfraw();

    $PDF_name = $this->_controller->request->data['PdfName'];

    $PDF_string = $this->_controller->request->data['PdfFile'];
    $EvModel = 'Report'.ucfirst($report['Testingmethod']['value'].'Evaluation');
    $this->_controller->loadModel($EvModel);

    $welduids = $this->_controller->$EvModel->find('list',
      array(
        'fields' => array(
          $EvModel.'.id',
          $EvModel.'.weld_system_uid'
        ),
        'conditions' => array(
          'reportnumber_id' => $report['Reportnumber']['id'],
          'weld_system_uid !=' => '',
          )
        )
    );

    if(count($welduids) == 0) return;

    $welduids_string = json_encode(array_values($welduids));

    $spooluids = $this->_controller->$EvModel->find('list',
      array(
        'fields' => array(
          $EvModel.'.id',
          $EvModel.'.spool_id'
        ),
        'conditions' => array(
          'reportnumber_id' => $report['Reportnumber']['id'],
          'spool_id !=' => '',
          'spool_id !=' => 0,
          )
        )
    );

    if(count($spooluids) == 0) return;

    $spooluids_string = json_encode(array_values($spooluids));
    $spooluid = json_encode(array(array_shift($spooluids)));

    $url = Configure::read('AuthUrl');

//    $url .= "/api/documentation/zfp-report/".$report['Reportnumber']['number'];
    $url .= "/api/documentation/zfp-report/".$report['Reportnumber']['year'] . '_' . $report['Reportnumber']['number'];
    $url .= "?weld_system_uids=" . $welduids_string;
    $url .= "&spool_system_uids=" . $spooluid;

    $cFile  = new CURLFile(Configure::read('root_folder') . DS . $PDF_name,'application/pdf' );
 
    if(!empty($cFile)){  

      $post = array(
        'spool_system_uids' => $spooluid,
        'weld_system_uids' => $welduids_string,
        'report' => $cFile
      );
      
      $ch = curl_init();
  
      curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data','Authorization: Bearer '.$tokendata['access_token']));
      curl_setopt($ch, CURLOPT_URL,$url);
      curl_setopt($ch, CURLOPT_POST,1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      $response = curl_exec($ch);

      $response = json_decode($response,true);

      if (curl_errno($ch)) {

        $error_msg = curl_error($ch);
        if(isset($error_msg)) $this->_LoggingDigipipe($Logging,$error_msg,$url);
        else $this->_LoggingDigipipe($Logging,array('Keine Response Errors '),$url);

      } else {
      }

      $this->_LoggingDigipipe($Logging,$post,$url);
      $this->_LoggingDigipipe($Logging,$response,$url);

      curl_close($ch);
    
      unlink (Configure::read('root_folder') . DS . $PDF_name);
    }

    return ;

  }

  protected function _LoggingDigipipe($Logging,$data,$url){

    if($Logging != true) return;

    $log = $data;

    $log ['url'] = $url;

    if(is_array($log)){
      $log = print_r($log, true);
      $log = str_replace(array("\n","\t"), " ", $log);
      $log = preg_replace('/\s+/', ' ',$log);
    }

    CakeLog::write('info', 'UserId ' . $this->_controller->Auth->user('id') . ' Send pdf: post data - ' . $log);

  }

  public function SaveReport($projectID,$cascadeID,$reportID,$ticketid){


    if($ticketid == 0) return false;
    if(!isset($this->_controller->request->data['Ticket'])) return false;
    if(empty($this->_controller->request->data['Ticket'])) return false;
    if(empty($this->_controller->request->data['Ticket']['reportwelds'])) return false;

    $ticketweld_testingmethods = $this->_controller->TicketweldsTestingmethods->find('all',array('conditions'=>array('ticket_id'=>$ticketid)));

    $savetestingmethod = array();

    foreach ($ticketweld_testingmethods as $tw_key => $tw_value) {
      if(empty($ticketweld_testingmethods)) continue;
      $savetestingmethod[$tw_value['TicketweldsTestingmethods']['testingmethod_id']][]= $tw_value['TicketweldsTestingmethods']['ticketweld_id'];
    }

    if(empty($savetestingmethod)) {
        $this->_controller->Flash->error(__('There are no welds selected!'), array('key' => 'error'));
        return false;
    }

    $return = $this->__CheckReport($projectID,$cascadeID,$reportID,$savetestingmethod,$ticketid);

    if($return == false){
      $this->_controller->Flash->error(__('The selected test method is not compatible. No test report will be generated.'), array('key' => 'error'));
      return false;
    }

    $return = $this->_SaveReport($projectID,$cascadeID,$reportID,$savetestingmethod,$ticketid);

    $return = $this->__DeleteTicketweldsTestingmethods($projectID,$cascadeID,$reportID,$savetestingmethod,$ticketid);

    if($return === false){

      $this->_controller->Flash->error(__('An error has occurred'), array('key' => 'error'));
      return false;

    }

    $return = $this->__CloseTicket($ticketid);

    if($return == 0){

      $this->_controller->Session->write('TicketsActiveAreas','data_areaclosed');

      $FormName['controller'] = 'rests';
      $FormName['action'] = 'tickets';
      $FormName['terms'] = implode('/', $this->_controller->request->projectvars['VarsArray']);

      $this->_controller->set('TicketId', $ticketid);
      $this->_controller->set('FormName', $FormName);

    } elseif($return == 1){

    }
  }

  public function __DeleteTicketweldsTestingmethods($projectID,$cascadeID,$reportID,$testingmethodwelds,$ticketid){

    if($projectID == 0) return 0;
    if($cascadeID == 0) return 0;
    if($reportID == 0) return 0;
    if($ticketid == 0) return 0;

    $Ids = array();

    foreach($this->_controller->request->data['Ticket']['reportwelds'] as $key => $value){

      $val = explode('_',$value);

      $Id = $this->_controller->TicketweldsTestingmethods->find('list',array(
         'conditions' => array(
           'TicketweldsTestingmethods.testingmethod_id' => $val[0],
           'TicketweldsTestingmethods.ticketweld_id' => $val[1],
         )
       )
     );

     $Ids = array_merge($Ids, $Id);

    }

    $Check = $this->_controller->TicketweldsTestingmethods->deleteAll(array(
      'TicketweldsTestingmethods.ticket_id' => $ticketid,
      'TicketweldsTestingmethods.id' => $Ids
    ), false);

    if($Check === false){
      return false;
    } else {
      return true;
    }
  }

  public function CheckReportByXml($data){

    $this->_controller->Testingmethod->recursive = -1;

    foreach ($data as $key => $value) {

      $verfahren = strtolower($key);
      $Verfahren = ucfirst($verfahren);

      $testingmethod = $this->_controller->Testingmethod->find('first',array('conditions'=>array('Testingmethod.value' => $verfahren)));

      if(count($testingmethod) == 0){
        unset($data[$key]);
        continue;
      }

      $Model['Generally'] = 'Report'.$Verfahren.'Generally';
      $Model['Specific'] = 'Report'.$Verfahren.'Specific';
      $Model['Evaluation'] = 'Report'.$Verfahren.'Evaluation';

      $testingmethod_xml = $testingmethod;
      $testingmethod_xml['Reportnumber']['version'] = $testingmethod_xml['Testingmethod']['version'];

      $arrayData = $this->_controller->Xml->DatafromXmlForReport($verfahren,$testingmethod_xml);
      $arrayData = $arrayData['settings'];

      if(empty($arrayData)){
        unset($data[$key]);
        continue;
      }

      $HasTicketData = 0;

      foreach ($Model as $_key => $_value) {
     
        if(empty($arrayData->{$_value})) continue;

        foreach ($arrayData->{$_value}->children() as $xml_key => $xml_value) {
          if(!empty($xml_value->ticketdataobject)) $HasTicketData = 1;
        }

      }

      if($HasTicketData == 0){
        unset($data[$key]);
      }
    }

    return $data;

  }

  public function __CheckReport($projectID,$cascadeID,$reportID,$testingmethodwelds,$ticketid)
  {
    if($projectID == 0) return 0;
    if($cascadeID == 0) return 0;
    if($reportID == 0) return 0;
    if($ticketid == 0) return 0;


    $this->_controller->Testingmethod->recursive = -1;

    foreach ($testingmethodwelds as $tw_key => $tw_value) {

      if(empty($tw_value)) continue;

      $testingmethod = '';
      $testingmethod = $this->_controller->Testingmethod->find('first',array('conditions'=>array('Testingmethod.id'=>$tw_key)));
      $verfahren = $testingmethod['Testingmethod'] ['value'];
      $version = $testingmethod['Testingmethod'] ['version'];

      $Verfahren = ucfirst(	$verfahren);

      $GenerallyModel = 'Report'.$Verfahren.'Generally';
      $SpecificModel = 'Report'.$Verfahren.'Specific';
      $EvaluationModel = 'Report'.$Verfahren.'Evaluation';

      $arrayData = array();
      $testingmethod_xml = $testingmethod;
      $testingmethod_xml['Reportnumber']['version'] = $testingmethod_xml['Testingmethod']['version'];

      $arrayData = $this->_controller->Xml->DatafromXmlForReport($verfahren,$testingmethod_xml);

      $arrayData = $arrayData['settings'];

      $HasTicketData = 0;

      //Generelle Daten festlegen
      foreach ($arrayData->$GenerallyModel->children() as $xml_key => $xml_value) {
        if(!empty($xml_value->ticketdataobject)) $HasTicketData = 1;
      }
      foreach ($arrayData->$EvaluationModel->children() as $xml_key => $xml_value) {
        if(!empty($xml_value->ticketdataobject)) $HasTicketData = 1;
      }
    }

    return $HasTicketData;
  }

  public function _SaveReport($projectID,$cascadeID,$reportID,$testingmethodwelds,$ticketid)
  {
    if($projectID == 0) return 0;
    if($cascadeID == 0) return 0;
    if($reportID == 0) return 0;
    if($ticketid == 0) return 0;

    $ticket = $this->_controller->Ticket->find('first',array('conditions' => array('Ticket.id' => $ticketid)));
    $ticketdata = $ticket['TicketData'];
    $datas = array();
    $jsons = Hash::extract($ticketdata, '{n}.json');

    foreach ($jsons as $key => $value) {
      $datas[] = json_decode($value,true);
    }

    $reportwelds = array();

    foreach($this->_controller->request->data['Ticket']['reportwelds'] as $key => $value){

      $val = explode('_',$value);

      $reportwelds[$val[0]][$val[1]] = $val[1];

    }

    foreach ($testingmethodwelds as $key => $value) {

      foreach($value as $_key => $_value) {

        if(!isset($reportwelds[$key][$_value])){
          unset($testingmethodwelds[$key][$_key]);
        }
      }
    }

    $this->_controller->Testingmethod->recursive = -1;

    foreach ($testingmethodwelds as $tw_key => $tw_value) {

      if(empty($tw_value)) continue;

      $testingmethod = '';
      $testingmethod = $this->_controller->Testingmethod->find('first',array('conditions'=>array('Testingmethod.id'=>$tw_key)));
      $verfahren = $testingmethod['Testingmethod'] ['value'];
      $version = $testingmethod['Testingmethod'] ['version'];

      $Verfahren = ucfirst(	$verfahren);

      $GenerallyModel = 'Report'.$Verfahren.'Generally';
      $SpecificModel = 'Report'.$Verfahren.'Specific';
      $EvaluationModel = 'Report'.$Verfahren.'Evaluation';
      $this->_controller->loadModel($GenerallyModel);
      $this->_controller->loadmodel($SpecificModel);
      $this->_controller->loadModel($EvaluationModel);
      $this->_controller->loadmodel('Reportnumber');
      $this->_controller->loadmodel('TicketReportnumber');


      $this->_controller->request->projectvars['reportnumberID'] = 0;
      $arrayData = array();
      $testingmethod_xml = $testingmethod;
      $testingmethod_xml['Reportnumber']['version'] = $testingmethod_xml['Testingmethod']['version'];

      $arrayData = $this->_controller->Xml->DatafromXmlForReport($verfahren,$testingmethod_xml);


      $arrayData = $arrayData['settings'];

      if(!isset($datas)) continue;
      if(empty($datas)) continue;

      $this->_controller->Reportnumber->create();
      $Report = array();
      $akz_datas = Hash::extract($datas,'{n}.{s}.akz_data');
      $akz_data = $akz_datas[0];

      //Generelle Daten festlegen
      foreach ($arrayData->$GenerallyModel->children() as $xml_key => $xml_value) {

        if(empty($xml_value->ticketdata)) continue;

        $ticketdatafield="";
        $key ="";
        $ticketdataobject = trim($xml_value->ticketdataobject);
        $ticketdatafield = trim($xml_value->ticketdata);
        $spooldata = Hash::extract($datas,'{n}.{s}.'.$ticketdataobject);

        if(!isset($spooldata[0])) continue;

        $spooldata = $spooldata[0];

        if ($ticketdataobject== 'welds') $spooldata = $spooldata[0];

        $key = trim($xml_value->key);
        $Report[$GenerallyModel] [$key] = $spooldata[$ticketdatafield];

        if(trim($xml_value->fieldtype) == 'radio'){
          if(isset($xml_value->needtocheck) && trim($xml_value->needtocheck) == $spooldata[$ticketdatafield]){
            $Report[$GenerallyModel] [$key] = 1;
          }else{
            $Report[$GenerallyModel] [$key] = 0;
          }
        }

        if($key == 'technical_place'){
          $tp_parts = explode('=',$spooldata[$ticketdatafield]);
          $Report[$GenerallyModel]['technical_place_level1'] = $tp_parts[0];
          $Report[$GenerallyModel]['technical_place_level2'] = $tp_parts[1];
          $Report[$GenerallyModel]['technical_place_level3'] = $tp_parts[2];
        }

      }

      //Nähte vorbereiten sheet_no noch abfangen
      $weldsArray = array();

      foreach ($datas as $data_key => $data_value) {

        foreach ($data_value['data']['welds'] as $dv_key => $dv_value) {

          if (array_search($dv_value['system_uid'], $tw_value) === false) continue;

          //Daten aus Schweißnahtbuchungen überschreiben
          if(isset($data_value['data']['welds_output_data'])&& !empty($data_value['data']['welds_output_data'])){
            foreach ($data_value['data']['welds_output_data'] as $wopt_key => $wopt_value) {
              if(!isset($wopt_value['system_uid'])) continue;
              if($wopt_value['system_uid'] == $dv_value['system_uid']) {
                $dv_value = array_merge($dv_value,$wopt_value);
              }
            }
          }
          //Daten an den Prüfbericht Übergeben
          foreach ($arrayData->$EvaluationModel->children() as $exml_key => $exml_value) {

            if(isset($exml_value->ticketdata)){

              $ticketdatafield="";
              $key ="";
              $ticketdataobject = trim($exml_value->ticketdataobject);
              $ticketdatafield = trim($exml_value->ticketdata);
              $key = trim($exml_value->key);
              $Report[$EvaluationModel][$key] = $dv_value[$ticketdatafield];

              //Das verstehe ich nicht
              //$Report[$EvaluationModel][$key] = $data_value[$ticketdatafield];
              //Das verstehe ich nicht

              //Nahtnr. mit 0 auffüllen
              while (strlen($dv_value['weld_number']) < 3){
                $dv_value['weld_number'] = '0'.$dv_value['weld_number'];
              }
              
              //nahtarray anlegen
              $weldsArray[$dv_value['system_uid']]= array(
                $key=>$dv_value[$ticketdatafield],
                'description' => $dv_value['weld_number'].'-'.$data_value['data']['sheet_data']['name'],
                'sheet_no' => $data_value['data']['sheet_data']['name'],
                'welder'=>$dv_value['welder_id'],
                'weld_system_uid' => $dv_value['system_uid'],
                'welding_method' => $dv_value['welding_type'],
                'spool_id'=> $data_value['data']['system_uid']
              );
            }
          }
        }
      }

      if(empty($Report)) continue;

      $reportnumber_id = $this->_controller->Data->ResetNumberByYear();
      $reportnumber = array('id'=> $reportnumber_id, 'testingmethod_id'=>$tw_key,'version'=>$version);
      $Test = $this->_controller->Reportnumber->save($reportnumber);

      if(!empty($weldsArray)) {
        foreach ($weldsArray as $wakey => $wavalue) {
          $this->_controller->$EvaluationModel->create();
          $wavalue['reportnumber_id'] = $reportnumber_id;
          $this->_controller->$EvaluationModel->save($wavalue);
        }
      }

      $ReportDataId = '';

      //Bericht erzeugen
      $Report[$GenerallyModel]['reportnumber_id'] = $reportnumber_id;
      $this->_controller->$GenerallyModel->create();
      $this->_controller->$GenerallyModel->save($Report[$GenerallyModel]);
      $ReportDataId .= ' '.$this->_controller->$GenerallyModel->getInsertID();

      $Report[$SpecificModel]['reportnumber_id']= $reportnumber_id;
      $this->_controller->$SpecificModel->create();
      $this->_controller->$SpecificModel->save($Report[$SpecificModel]);
      $ReportDataId .= ' '.$this->_controller->$GenerallyModel->getInsertID();
      //__________________________________________________________________

      //Ticket schließen und reportnumberID setzen
      $ticketreport = array(
        'ticket_id' => intval($ticketid),
        'reportnumber_id' => intval($reportnumber_id)
      );

      $this->_controller->TicketReportnumber->save($ticketreport);

      //status des Tickets ändern
  //    $this->_controller->Ticket->save(array('id'=>$ticketid,'status'=>1));

      //Archiveintrag erzeugen
      $this->_controller->Data->Archiv($reportnumber_id,$Verfahren);

      //return $reportnumber_id;
    }

    $this->_controller->Flash->success(__('Reports successfully created'), array('key' => 'success'));

  }

  public function __CloseTicket($ticketid){

    $Tickets = $this->_controller->TicketweldsTestingmethods->find('list',array('conditions' => array('TicketweldsTestingmethods.id' => $ticketid)));

    if(count($Tickets) == 0){

      $this->_controller->Ticket->save(array('id'=>$ticketid,'status'=>1));
      $this->_controller->Flash->success(__('The ticket has been closed'), array('key' => 'success'));

      return 0;

    } else {

      return 1;

    }
  }

  public function __CloseTicketDeleteTicketwelds($ticketid){

    $this->_controller->TicketweldsTestingmethods->deleteAll(array(
      'TicketweldsTestingmethods.ticket_id' => $ticketid,
    ), false);


  }

    public function SendReport($report) {

      if(empty($report)) return;
      //if($report['Reportnumber']['status'] < 2) return; erst einmal auskommentieren für PEN Test
      //wenn TüvBericht dann abbrechen
      //$checkorganisation = $this->CheckOrganisationState($report);
    //  if($checkorganisation == false) return;

      //Reparatur wird nur dann als nicht komplett makieret wenn vorhanden und "ne"
      $repaircomplete = 1;
      if($report['Reportnumber']['repair_for'] > 0){
        $this->_controller->Reportnumber->recursive = 1;
        $report2repair = $this->_controller->Reportnumber->find('first',array('conditions'=>array('Reportnumber.id'=>$report['Reportnumber']['repair_for'])));
        $repair = $this->_controller->Repairtracking->QuickCheckRepairStatus($report2repair);
        if($repair['Repair']['repair_status']<> 1) {
          $repaircomplete = 0;
          return;
        }else{
          //$report = $repair;
          $repaircomplete = 1;
        }  
      }

      $this->_controller->loadModel('Testingmethod');
      $testingmethod = $this->_controller->Testingmethod->find('first',array('conditions'=>array('Testingmethod.id'=>$report['Reportnumber']['testingmethod_id'])));
      $verfahren = $testingmethod['Testingmethod']['value'];
      $Verfahren = ucfirst($testingmethod['Testingmethod']['value']);
      $Evaluation = 'Report'.$Verfahren.'Evaluation';
      $this->_controller->loadModel($Evaluation);
      $welds = $this->_controller->$Evaluation->find('all',array(
        'conditions'=>array(
            'reportnumber_id' => $report['Reportnumber']['id'],
            'spool_id !=' => '',
            'deleted'=> 0
          )
        )
      );

      //Nöhte den Spools unterordnen
      $weldspools = array();

      if(empty($welds)) return;

      foreach ($welds as $weldkey => $weldvalue) {  

        if(isset($weldvalue[$Evaluation]['spool_id']) && !empty($weldvalue[$Evaluation]['spool_id'])) {
          $spool = $weldvalue[$Evaluation]['spool_id'];
          $weldspools[$spool][] = $weldvalue;
        }
      }

      //Bericht an die Spools schicken
      $this->_SendPdfString($report);

      //Task e-Prüfzeichen setzen wenn Reparatur erfolgt dann werden die ne Nähte ignoeriert
      if(!empty($weldspools)) {
        foreach ($weldspools as $_key => $_value) {
          $ne_welds = array();
          $ne_welds =  Hash::extract($_value, '{n}.{s}[result=2]');
          $ue_welds =  Hash::extract($_value, '{n}.{s}[result=0]');

          if(count($ne_welds) > 0 && $repaircomplete == 0) continue;
          if(count($ue_welds) > 0) continue;

          $this->_controller->loadModel('Ticket');
          $this->_controller->loadModel('TicketReportnumber');

          $TicketID = $this->_controller->TicketReportnumber->find('first',array('conditions' => array('reportnumber_id'=>$report['Reportnumber']['id'])));

          $TicketID = $TicketID['TicketReportnumber']['ticket_id'];
          $reportnumber_id = $report['Reportnumber']['id'];

          if($report['Reportnumber']['repair_for'] > 0) $reportnumber_id = $report['Reportnumber']['repair_for'];

          $ticket = $this->_controller->Ticket->find('first',array('conditions'=>array('Ticket.id'=>$TicketID)));
          $ticketid = $ticket['Ticket']['id'];

          $SpoolTask = Hash::extract($ticket['TicketData'],'{n}[spool_id='.$_key.']');
          $task_uid =  $SpoolTask[0]['task_id'];
          $this->_SetTask($_key, $task_uid,"6631",true);
        }
      }
      return ;

    }

    // ----------------------------------------------------------------
// Testing Methods
// ----------------------------------------------------------------
public function SendReportTest($report) {

  if(empty($report)) return;
  //if($report['Reportnumber']['status'] < 2) return; erst einmal auskommentieren für PEN Test
  //wenn TüvBericht dann abbrechen
  //$checkorganisation = $this->CheckOrganisationState($report);
  //  if($checkorganisation == false) return;

  //Reparatur wird nur dann als nicht komplett makieret wenn vorhanden und "ne"
  $repaircomplete = 1;
  if($report['Reportnumber']['repair_for'] > 0){
    $this->_controller->Reportnumber->recursive = 1;
    $report2repair = $this->_controller->Reportnumber->find('first',array('conditions'=>array('Reportnumber.id'=>$report['Reportnumber']['repair_for'])));
    $repair = $this->_controller->Repairtracking->QuickCheckRepairStatus($report2repair);
    if($repair['Repair']['repair_status']<> 1) {
      $repaircomplete = 0;
      return;
    }else{
      //$report = $repair;
      $repaircomplete = 1;
    }  
  }

  $this->_controller->loadModel('Testingmethod');
  $testingmethod = $this->_controller->Testingmethod->find('first',array('conditions'=>array('Testingmethod.id'=>$report['Reportnumber']['testingmethod_id'])));
  $verfahren = $testingmethod['Testingmethod']['value'];
  $Verfahren = ucfirst($testingmethod['Testingmethod']['value']);
  $Evaluation = 'Report'.$Verfahren.'Evaluation';
  $this->_controller->loadModel($Evaluation);
  $welds = $this->_controller->$Evaluation->find('all',array(
    'conditions'=>array(
        'reportnumber_id' => $report['Reportnumber']['id'],
        'spool_id !=' => '',
        'deleted'=> 0
      )
    )
  );

  //Nöhte den Spools unterordnen
  $weldspools = array();

  if(empty($welds)) return;

  foreach ($welds as $weldkey => $weldvalue) {  

    if(isset($weldvalue[$Evaluation]['spool_id']) && !empty($weldvalue[$Evaluation]['spool_id'])) {
      $spool = $weldvalue[$Evaluation]['spool_id'];
      $weldspools[$spool][] = $weldvalue;
    }
  }

  //Bericht an die Spools schicken
  $this->_SendPdfStringTest($report);

  //Task e-Prüfzeichen setzen wenn Reparatur erfolgt dann werden die ne Nähte ignoeriert

  if(!empty($weldspools)) {
    foreach ($weldspools as $_key => $_value) {

      $ne_welds = array();
      $ne_welds =  Hash::extract($_value, '{n}.{s}[result=2]');
      $ue_welds =  Hash::extract($_value, '{n}.{s}[result=0]');

      if(count($ne_welds) > 0 && $repaircomplete == 0) continue;
      if(count($ue_welds) > 0) continue;

      $this->_controller->loadModel('Ticket');
      $this->_controller->loadModel('TicketReportnumber');

      $TicketID = $this->_controller->TicketReportnumber->find('first',array('conditions' => array('reportnumber_id'=>$report['Reportnumber']['id'])));

      $TicketID = $TicketID['TicketReportnumber']['ticket_id'];
      $reportnumber_id = $report['Reportnumber']['id'];

      if($report['Reportnumber']['repair_for'] > 0) $reportnumber_id = $report['Reportnumber']['repair_for'];

      $ticket = $this->_controller->Ticket->find('first',array('conditions'=>array('Ticket.id'=>$TicketID)));
      $ticketid = $ticket['Ticket']['id'];

      $SpoolTask = Hash::extract($ticket['TicketData'],'{n}[spool_id='.$_key.']');
      $task_uid =  $SpoolTask[0]['task_id'];
      
      $this->_SetTaskTest($_key, $task_uid,"6631",true);
    }
  }
  return ;

}
// END

    public function CheckOrganisationState($report) {

      $reportnumberID = $report['Reportnumber']['id'];
      $status = $report['Reportnumber']['status'];
      $this->_controller->loadmodel('Testingmethod');
      $testingmethod = $this->_controller->Testingmethod->find('first',array('conditions'=>array('Testingmethod.id'=>$report['Reportnumber']['testingmethod_id'])));
      $verfahren = $testingmethod['Testingmethod']['value'];
      $Verfahren = ucfirst($testingmethod['Testingmethod']['value']);
      $GenerallyModel = 'Report'.$Verfahren.'Generally';
      $this->_controller->loadmodel($GenerallyModel);
      $approved = true;
      $GenerallyData = $this->_controller->$GenerallyModel->find('first',array('conditions'=> array('reportnumber_id'=>$reportnumberID)));
      if(isset($GenerallyData[$GenerallyModel]['tuev_required'])) {
        $tuevrequired = $GenerallyData[$GenerallyModel]['tuev_required'];
        if($tuevrequired == 1 && $status < 4 ) $approved = false;
        return $approved;
      }
      return $approved;

    }

    public function SortAndInfoWelds($Ticket,$Optiongroup){
  
      foreach ($Optiongroup as $key => $value) {

          natsort($Optiongroup[$key]);
  
      }
  
      return $Optiongroup;
      
    }

    public function CheckDigipie(){

      $tokendata = $this->_GetAccessTokenTest();

    }
}
?>
