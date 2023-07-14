<?php
class MySoapClient extends SoapClient {

  public function __doRequest($request, $location, $action, $version, $one_way = 0) {
    $response = parent::__doRequest($request, $location, $action, $version, $one_way);

    $start = strpos($response,'<?xml');
    $end = strrpos($response,'>');
    return substr($response,$start,$end-$start+1);
  }

}
class SoapComponent extends Component
{
  protected $_controller = null;




  public function initialize(Controller $controller)
  {
    $this->_controller = $controller;
  }


  public function OdreFindRequest($logindata, $location,$wsdl,$input){

    $context = stream_context_create([
      'ssl' => [
        // set some SSL/TLS specific options
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      ]
    ]);
    $options = array('features'=>SOAP_SINGLE_ELEMENT_ARRAYS,'cache_wsdl' => WSDL_CACHE_NONE,'soap_version' => SOAP_1_1,'stream_context'=>$context,'trace'=>1);
    $token = $this->LoginRequest($options,$location,$wsdl,$logindata);
    $token = json_decode(json_encode($token), true);

    if(!empty($token)) {
      $order = $this->FindRequest($options,$location,$wsdl,$token,$input);

      //$this->LogoutRequest($options,$location,$wsdl,$logindata);
      return $order;
    }else{
      //Flashmessage einbauen Falsche Logindaten
      return;
    }
    //var_dump($token); die();
  }


  public function AdressFindRequest($logindata, $location,$wsdl,$input){

    $context = stream_context_create([
      'ssl' => [
        // set some SSL/TLS specific options
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      ]
    ]);
    $options = array('features'=>SOAP_SINGLE_ELEMENT_ARRAYS,'cache_wsdl' => WSDL_CACHE_NONE,'soap_version' => SOAP_1_1,'stream_context'=>$context,'trace'=>1);
    $token = $this->LoginRequest($options,$location,$wsdl,$logindata);
    $token = json_decode(json_encode($token), true);

    if(!empty($token)) {
      $adress = $this->FindRequestforAdress($options,$location,$wsdl,$token,$input);

      return $adress;
    }else{
      //Flashmessage einbauen Falsche Logindaten
      return;
    }
    //var_dump($token); die();
  }


  public function LoginRequest($options,$location,$wsdl,$logindata){

    try{
      $proxy = new MySoapClient($wsdl,$options);



      //  $myClass->UserLogin = new \stdClass;
      //  $myClass->UserLogin->UserName = 'MBQ';
      //  $myClass->UserLogin->Password = 'mbq-A99';
      //  $myClass->UserLogin->Mandant = '3500';

      $proxy->__setLocation($location);

      $myClass['UserLogin'] = $logindata;

      $token = $proxy->login($myClass);

      $token = (array) $token;

      return $token;
    }catch(SoapFault $ex){
      //	 //var_dump($ex);
      //	 trigger_error("Error SOAP: (faultcode: {$ex->faultcode}, faultstring: {$ex->faultstring})", E_USER_ERROR);
//      echo'<pre>'	;print_r( $proxy->__getLastRequest());echo'</pre>'	;
//      echo'<pre>'	;print_r( $proxy->__getLastResponse());echo'</pre>';
    //  echo "<pre>Exception: ".print_r($ex, true)."</pre>\n";
    }

  }

  public function LogoutRequest($options,$location,$wsdl,$token){

    try{
      $logoutclient = new MySoapClient($wsdl,$options);

      $logoutclient->__setLocation($location);

      $logoutArray['LoginToken'] = $token['LoginToken'];
      $logout = $logoutclient->logout($logoutArray);

      //  $token = (array) $token;
      //return $token;

      return;
    }catch(SoapFault $ex){
      //	 //var_dump($ex);
      //	 trigger_error("Error SOAP: (faultcode: {$ex->faultcode}, faultstring: {$ex->faultstring})", E_USER_ERROR);
  //    echo'<pre>'	;print_r( $logoutclient->__getLastRequest());echo'</pre>'	;
  //    echo'<pre>'	;print_r( $logoutclient->__getLastResponse());echo'</pre>';
      echo "<pre>Exception: ".print_r($ex, true)."</pre>\n";
    }

  }
  public function FindRequest($options,$location,$wsdl,$token,$input){

    try{
      $findclient = new MySoapClient($wsdl,$options);


      $findclient->__setLocation($location);

      $findarray ['AbaConnectParam']['Login']['LoginToken'] =  $token['LoginToken'];
      $findarray ['AbaConnectParam']['Revision'] = "0";
      $findarray ['FindParam']['Index'] = "1";
      $findarray ['FindParam']['Operation'] = "EQUAL";
      $findarray ['FindParam'] ['KeyFields']['LongData'] = array('_' => $input, 'Name'=>'OrderNumber');

      $response = $findclient->find($findarray);
      $response = json_decode(json_encode($response), true);
      if(isset($response['ResponseMessage'])){
        if(isset($response['ResponseMessage']['RequestID'])){
          while ($response['ResponseMessage']['IsFinished'] == false) {
            // code...
            $finish ['RequestID'] = $response['ResponseMessage']['RequestID'];
            $finished =  $findclient->isFinished($finish);
            $response = $finished;

            $response = json_decode(json_encode($response), true);
          }
        }
      }

      if(isset($response['ResponseMessage']) && isset($response['ResponseMessage']['IsFinished'] )&& $response['ResponseMessage']['IsFinished'] == true){
        $this->LogoutRequest($options,$location,$wsdl,$token);
      }
      if(isset($response['DataContainer'])) {
        $response = $response['DataContainer']['Data'];
      }else return;

      return $response;
    }catch(SoapFault $ex){
      //	 //var_dump($ex);
      //	 trigger_error("Error SOAP: (faultcode: {$ex->faultcode}, faultstring: {$ex->faultstring})", E_USER_ERROR);
//      echo'<pre>'	;print_r( $findclient->__getLastRequest());echo'</pre>'	;
//      echo'<pre>'	;print_r( $findclient->__getLastResponse());echo'</pre>';
      echo "<pre>Exception: ".print_r($ex, true)."</pre>\n";
    }

  }

  public function FindRequestforAdress($options,$location,$wsdl,$token,$inputadress){

    try{
      $findclientadress = new MySoapClient($wsdl,$options);


      $findclientadress->__setLocation($location);

      $findadress ['AbaConnectParam']['Login']['LoginToken'] =  $token['LoginToken'];
      $findadress ['AbaConnectParam']['Revision'] = "0";
      $findadress ['FindParam']['Index'] = "1";
      $findadress ['FindParam']['Operation'] = "EQUAL";
      $findadress ['FindParam'] ['KeyFields']['LongData'] = array('_' => $inputadress, 'Name'=>'CustomerNumber');

      $response = $findclientadress->find($findadress);


      $response = json_decode(json_encode($response), true);
      if(isset($response['ResponseMessage'])){
        if(isset($response['ResponseMessage']['RequestID'])){
          while ($response['ResponseMessage']['IsFinished'] == false) {
            $finisharray ['RequestID'] = $response['ResponseMessage']['RequestID'];
            $finished =  $findclientadress->isFinished($finisharray);
            $response = $finished;
            $response = json_decode(json_encode($response), true);
          }
        }
      }

      if(isset($response['ResponseMessage']) && isset($response['ResponseMessage']['IsFinished'] ) && $response['ResponseMessage']['IsFinished'] == true){
        $this->LogoutRequest($options,$location,$wsdl,$token);
      }
      $response = $response['DataContainer']['Data'];


      return $response;
    }catch(SoapFault $ex){
      //	 //var_dump($ex);
      //	 trigger_error("Error SOAP: (faultcode: {$ex->faultcode}, faultstring: {$ex->faultstring})", E_USER_ERROR);
//      echo'<pre>'	;print_r( $findclientadress->__getLastRequest());echo'</pre>'	;
//      echo'<pre>'	;print_r( $findclientadress->__getLastResponse());echo'</pre>';
      echo "<pre>Exception: ".print_r($ex, true)."</pre>\n";
    }

  }


  public function OdreSaverequest($logindata, $location,$wsdl,$input){

    $context = stream_context_create([
      'ssl' => [
        // set some SSL/TLS specific options
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      ]
    ]);
    $options = array('features'=>SOAP_SINGLE_ELEMENT_ARRAYS,'cache_wsdl' => WSDL_CACHE_NONE,'soap_version' => SOAP_1_1,'stream_context'=>$context,'trace'=>1);
    $token = $this->LoginRequest($options,$location,$wsdl,$logindata);
    $token = json_decode(json_encode($token), true);

    if(!empty($token)) {
      $order = $this->SaveRequest($options,$location,$wsdl,$token,$input);

      //$this->LogoutRequest($options,$location,$wsdl,$logindata);
      return;
    }else{
      //Flashmessage einbauen Falsche Logindaten
      return;
    }
    //var_dump($token); die();
  }

  public function SaveRequest($options,$location,$wsdl,$token,$input){

    try{
      $saveclient = new MySoapClient($wsdl,$options);


      $saveclient->__setLocation($location);

      $savearray ['AbaConnectParam']['Login']['LoginToken'] =  $token['LoginToken'];
      $savearray ['AbaConnectParam']['Revision'] = "1";
      $savearray ['Data'] = array();
      $savearray ['Data']['SalesOrderHeader']['SalesOrderHeaderFields']['OrderNumber'] = $input['OrderNumber'];

      $savearray ['Data']['SalesOrderHeader']['Item']['ItemFields'] = array('Itemtype'=>$input['ItemType'],'ProductNumber'=>$input['ProductNumber'],'QuantityOrdered'=>$input['QuantityOrdered'],'QuantityInitial'=>$input['QuantityInitial']);


      $response = $saveclient->save($savearray);
      $response = json_decode(json_encode($response), true);

      if(isset($response['ResponseMessage'])){
        if(isset($response['ResponseMessage']['RequestID'])){
          while ($response['ResponseMessage']['IsFinished'] == false) {
            // code...
            $finish ['RequestID'] = $response['ResponseMessage']['RequestID'];
            $finished =  $saveclient->isFinished($finish);
            $response = $finished;

            $response = json_decode(json_encode($response), true);
          }
        }
      }

      if(isset($response['ResponseMessage']) && isset($response['ResponseMessage']['IsFinished'] )&& $response['ResponseMessage']['IsFinished'] == true){
        $this->LogoutRequest($options,$location,$wsdl,$token);
      }

      return ;
    }catch(SoapFault $ex){
      //	 //var_dump($ex);
      //	 trigger_error("Error SOAP: (faultcode: {$ex->faultcode}, faultstring: {$ex->faultstring})", E_USER_ERROR);
//      echo'<pre>'	;print_r( $saveclient->__getLastRequest());echo'</pre>'	;
//      echo'<pre>'	;print_r( $saveclient->__getLastResponse());echo'</pre>';
      echo "<pre>Exception: ".print_r($ex, true)."</pre>\n";
    }

  }


  public function SendFromReport($reportnumber,$verfahren){

    $this->_controller->loadModel('Soapservice');

    $Service = $this->_controller->Soapservice->find('first',array(
      'conditions' => array(
        'testingcomp_id' => $this->_controller->Auth->user('testingcomp_id')
        )
      )
    );

    if(count($Service) == 0) return;

    $wsdl = Configure::read('xml_folder') . 'soap' . DS . $this->_controller->Auth->user('testingcomp_id').DS.'order'.DS.'ordesalesorder.wsdl';

    $logindata = array(
      'UserName' => $Service['Soapservice']['username'], 
      'Password' => $Service['Soapservice']['password'],
      'Mandant'=>$Service['Soapservice']['mandant']
    );

    $xml = $this->_controller->Xml->XmltoArray($verfahren,'file',ucfirst($verfahren));

    $Generally = 'Report'.ucfirst($verfahren).'Generally';

    $this->_controller->loadmodel($Generally);
    $GenerallyData=$this->_controller->$Generally->find('first',array('conditions' =>array('reportnumber_id'=> $reportnumber['Reportnumber']['id'])));

    $PDF = 'Report'.ucfirst($verfahren).'Pdf';

    foreach ($xml->$PDF->settings->children() as $key => $value) {

      if ($key <> 'WEBSERVICE') continue;
      $dataarray = array();
      foreach ($value->children() as $ws_key => $ws_value) {
        if(isset($ws_value->value) && !empty( trim($ws_value->value))) {
          $dataarray[trim($ws_key)] = trim($ws_value->value);
        }elseif (isset($ws_value->model) && isset($ws_value->field) && !empty($GenerallyData[$Generally][trim($ws_value->field)])) {
        //  if(isset($ws_value->format)&& trim($ws_value->format) == 'decimal') {
          //  $GenerallyData[$Generally][trim($ws_value->field)] = number_format($GenerallyData[$Generally][trim($ws_value->field)], 2, '.', '');

        //  }
          $dataarray[trim($ws_key)] =$GenerallyData[$Generally][trim($ws_value->field)];
        }

      }
    }

    $this->OdreSaverequest($logindata, $Service['Soapservice']['webservice_url'],$wsdl,$dataarray);
    
    return;
  }
}
?>
