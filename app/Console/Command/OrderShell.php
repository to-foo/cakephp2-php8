<?php

    App::uses('ComponentCollection', 'Controller');
    App::uses('NavigationComponent', 'Controller/Component');
    App::uses('XmlComponent', 'Controller/Component');

    class OrderShell extends AppShell
    {
        public $uses = array(
          'Topprojects',
          'ReportsTopprojects',
          'TestingmethodsReports',
          'Testingmethod',
          'Cascade',
          'CascadesOrder',
          'Deliverynumber',
          'Order',
          'OrdersDevelopments',
          'Development',
          'OrdersTestingcomps',
          'ReportsTopprojects',
          'TestingcompsTopprojects',
          'ImportSchreiber',
          'TestingcompsCascades',
          'Supplier',
          'Expediting',
          'ExpeditingEvent',
          'Reportnumber'
      );



        public function main()
        {
            $this->out('Hello world.');
        }

        public function reduce()
        {
          $this->loadModel('Cascade');
          $this->loadModel('AdvancesCascade');
          $this->loadModel('AdvancesCascadesTestingcomp');
          $this->loadModel('AdvancesData');
          $this->loadModel('AdvancesDataDependency');
          $this->loadModel('AdvancesHistory');
          $this->loadModel('AdvancesOrder');
         // $this->loadModel('CascadesOrders');
          $this->loadModel('ExpeditingEvent');
          $this->loadModel('Order');
          $this->loadModel('TestingcompsCascades');
           $this->loadModel('Supplier');
          $ids = $this->Cascade->find('list',array('fields'=>array('Cascade.id'),'conditions'=>array('Cascade.id <>' => 14,'Cascade.id <>' => 1,'Cascade.id <>' => 3, 'Cascade.parent <>' => 14)));
       //   $ids['14'] = 14;
        //  $ids['3'] = 3;
        //  $ids['1'] = 1;
          $this->AdvancesCascade->deleteAll(array('AdvancesCascade.cascade_id' => $ids), false);
          $this->AdvancesCascadesTestingcomp->deleteAll(array('AdvancesCascadesTestingcomp.cascade_id' => $ids), false);
          $this->AdvancesData->deleteAll(array('AdvancesData.cascade_id' => $ids), false);
          $this->AdvancesDataDependency->deleteAll(array('AdvancesDataDependency.cascade_id' => $ids), false);
          $this->AdvancesHistory->deleteAll(array('AdvancesHistory.cascade_id' => $ids), false);
          $this->AdvancesOrder->deleteAll(array('AdvancesOrder.cascade_id' => $ids), false);
          $this->CascadesOrder->deleteAll(array('CascadesOrder.cascade_id' => $ids), false);
          $this->Cascade->deleteAll(array('Cascade.id' => $ids), false);
          $this->ExpeditingEvent->deleteAll(array('ExpeditingEvent.cascade_id' => $ids), false);
          $this->Order->deleteAll(array('Order.cascade_id' => $ids), false);
          $this->TestingcompsCascades->deleteAll(array('TestingcompsCascades.cascade_id' => $ids), false);
          $this->Supplier->deleteAll(array('Supplier.cascade_id' => $ids), false);

          var_dump($ids);
          var_dump('Erfolg');
        }
        public function CreateOrdersFromReport()
        {
        //  if(count($this->args) < 4) return;

          $mailconfig = 'gmail';
//          $mailconfig = 'default';
          $mailadresses = array('torsten.foth@mbq-gmbh.de');

          $projectID  = intval($this->args[0]); // Projekt aus dem die Berichte kommen
          $cascadeID = intval($this->args[1]); //cascade wo wie Auträge generiert werden sollen
          $year = $testingcompID = intval($this->args[2]);
          $testingcompID = intval($this->args[3]); // Alle involvierten Firmen ausser id 1

          if($projectID == 0) return;
        //  if($cascadeID == 0) return;
      //    if($testingcompID == 0) return;

          $reportnumbers = $this->Reportnumber->find('all',array('conditions'=>array('Reportnumber.topproject_id'=>$projectID,'Reportnumber.year'=> $year)));
          foreach ($reportnumbers as $key => $value) {
            $Verfahren = ucfirst($value['Testingmethod']['value']);
            $Generally = 'Report'.$Verfahren.'Generally';
            $this->loadmodel($Generally);
            $GenData =  $this->$Generally->find('first',array('conditions'=>array('reportnumber_id'=>$value['Reportnumber']['id'])));
            $orderdata ['auftrags_nr'] = $GenData[$Generally]['auftrags_nr'];
            $orderdata ['auftraggeber']= $GenData[$Generally]['auftraggeber'];
            $orderdata ['auftraggeber_adress']= $GenData[$Generally]['auftraggeber_adress'];
            $orderdata ['auftrags_nr'] = preg_replace('![^0-9]!', '/', $orderdata ['auftrags_nr']);
            if(empty($orderdata['auftrags_nr'])) $orderdata['auftrags_nr'] = 'nicht vergeben';
            $order = $this->Order->find('first',array('conditions'=>array('Order.topproject_id'=>$projectID,'Order.auftrags_nr'=> $orderdata ['auftrags_nr'],'Order.cascade_id'=>$cascadeID)));
            if(count($order) > 0 ) {
              $reportnumbersnew = array();
              $reportnumbersnew['Reportnumber'] = $value['Reportnumber'];
              $reportnumbersnew ['Reportnumber']['order_id'] = $order['Order']['id'];
              $reportnumbersnew ['Reportnumber']['cascade_id'] = $order['Order']['cascade_id'];
              $this->Reportnumber->save($reportnumbersnew );
            }else{
              $this->Order->create();
              if($cascadeID == 0){
                 $Order ['cascade_id'] =  $value['Reportnumber'] ['cascade_id'];
              }else{
                $Order ['cascade_id'] = $cascadeID;
              }
              $Order['topproject_id']= $projectID;
              $Order['auftrags_nr'] = $orderdata ['auftrags_nr'];
              $Order['auftrags_nr'] =  preg_replace('![^0-9]!', '/', $orderdata ['auftrags_nr']);
              $Order['auftraggeber'] = $orderdata ['auftraggeber'];
              $Order['auftraggeber_adress'] = $orderdata ['auftraggeber_adress'];
              if(empty($Order['auftrags_nr'])) $Order['auftrags_nr'] = 'nicht vergeben';
              $Order['testingcomp_id'] = 1;
              $Order['development_id'] = 1;
              $Order['status'] = 0;

              $this->Order->save($Order);
              $Insert = array();
              $Insert['cascade_id'] = $Order ['cascade_id'];
              $Insert['order_id'] = $this->Order->getLastInsertId();

              $this->CascadesOrder->create();

              if($this->CascadesOrder->save($Insert)){
                pr('CascadesOrder eingefügt');
              } else {
                $emailcontent .= "\nBeim Datenimport ist ein Fehler aufgetreten, Fehlercode 001.\n";
              }
              $InsertTC['order_id'] = $this->Order->getLastInsertId();
              $InsertTC['testingcomp_id'] = 1;
                            if($this->OrdersTestingcomps->save($InsertTC)){
                              pr('CascadesOrder eingefügt');
                            } else {
                              $emailcontent .= "\nBeim Datenimport ist ein Fehler aufgetreten, Fehlercode 001.\n";
                            }
                $reportnumbersnew = array();
                $reportnumbersnew['Reportnumber'] = $value['Reportnumber'];
                $reportnumbersnew ['Reportnumber']['order_id'] = $this->Order->getLastInsertId();
                $reportnumbersnew ['Reportnumber']['cascade_id'] =   $cascadeID;
                $this->Reportnumber->save($reportnumbersnew );

            }

          }



        }

    }
