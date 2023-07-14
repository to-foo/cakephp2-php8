<div class="monitoring_content monitoring_infocontent">


  <div class="index inhalt">
    <h2>Maßnahmen</h2>
    <table class="advancetool" cellpadding="0" cellspacing="0">
      <tr>
        <th></th>
        <th></th>
        <th><?php echo __('Datum', true); ?></th>
        <th><?php echo __('Karenz', true); ?></th>



        <th><?php echo __('Discription', true); ?></th>
      </tr>
      <?php
      foreach ($tasks as $key => $task):



        echo '<tr>';
        ?>



        <td>
          <?php
          $class = 'round modal cascades';
          $action = 'edit';



          echo $this->Html->link(
              key($task['description']),
              array_merge(array('controller'=>'cascades','action' => $action), $task['link']),
              array(
            'class' => $class,
            'rev' => implode('/', $task['link']))
          );
          ?>




        </td>
        <td><span class="icon empty <?php echo $task['class'];?> "></span></td>
        <td>
          <?php echo($key); ?>
          &nbsp;
        </td>
        <td>
          <?php if (isset($task['karenz'])) {
              echo $task['karenz'];
          } ?>
        </td>
        <td>
          <?php



          foreach ($task['description'] as $_key => $_task):



            $taskstring = implode(', ', $_task);



            echo $taskstring; echo "<br>";
          endforeach;
          ?>



        </td>







        <?php
        echo '</tr>';
      endforeach;



      ?>



    </table>
    <h2>Kontake</h2>
    <table class="advancetool" cellpadding="0" cellspacing="0">
      <tr>
        <th><?php echo __('Name', true); ?></th>
        <th><?php echo __('Firma', true); ?></th>



        <th><?php echo __('Zuständigkeit', true); ?></th>
        <th><?php echo __('Tel.', true); ?></th>
      </tr>
      <?php
      $contacts = array(array('Peter Krause','Kesselbau','Kesselbauer','0162-0000-0000'),array('Torsten Plaue','Qualitätssicherungsfirma','Sicherheitsbeauftragter','0151-0000-0000'),array('Thomas Bach','Reinigungsfirma','Facility Manager','0151-0000-0000'),array('Joachim Meyer','NDT Firma','ZFP Prüfer Stufe 3','0151-0000-0000'));
      foreach ($contacts as $ckey => $c_vlaue):



        ?>
        <tr>
          <?php
          foreach ($c_vlaue as $ck => $cv) {
              echo '<td>'.$cv.'</td>';
          }
          ?>




        </tr>
      <?php endforeach;?>
    </table>



  </div>



</div>
