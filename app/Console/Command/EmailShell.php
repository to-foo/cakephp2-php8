<?php
    class MonitoringShell extends AppShell {
    //public $uses = array('Examiner','MailMonitoring','Device','DeviceTestingmethod');    




    public function main() {
        App::import('Core', 'L10n');
        Configure::write('Config.language',$this->args[0]);
        App::uses('CakeEmail', 'Network/Email');

        

        $email = new CakeEmail();
        $email->config('default');


        $email->from('phillip.schmidt@mbq-gmbh.de');

        $email->to('phillip.schmidt@mbq-gmbh.de');
        $test = 'Test für TRM. Bei Erfolg wird dieser Text angezeigt.';
        $email->subject('Testmail für TRM');
        $mailview ='default';
        $email->template($mailview,'default');
				$email->emailFormat('both');
				$email->viewVars(array(
							'content' => $test,
							
    						)
						);		

        $email->send();
       // $this->out(print_r($examiner, true));
    }
    


  
}