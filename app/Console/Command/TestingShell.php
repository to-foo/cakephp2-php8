<?php

    App::uses('ComponentCollection', 'Controller');
    App::uses('XmlComponent', 'Controller/Component');

    class SearchingShell extends AppShell
    {
        public $uses = array(
          'Topprojects',
          'ReportsTopprojects',
          'TestingmethodsReports',
          'Testingmethod',
          'Cascade',
          'Deliverynumber',
          'Order',
          'OrdersTestingcomps',
          'ReportsTopprojects',
          'ReportsTopprojects',
          'TestingcompsTopprojects',
          'ImportSchreiber',
      );

        public function main()
        {
            $this->out('Hello world.');
        }

        public function CreateTopprojectAll()
        {




            $this->out('end and okay');
        }
    }
