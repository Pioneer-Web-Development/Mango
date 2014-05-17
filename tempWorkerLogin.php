<?php
  $standalone=true;
  include("includes/mainmenu.php");
  
  $sql="SELECT * FROM temp_workers WHERE status=1 ORDER BY last_name, first_name";
  $dbWorkers=dbselectmulti($sql);
  $workers[0]="Select your name";
  if($dbWorkers['numrows']>0)
  {
      foreach($dbWorkers['data'] as $worker)
      {
          $workers[$worker['id']]=stripslashes($worker['first_name'].' '.$worker['middle_name'].' '.$worker['last_name']);
      }
  }
  
  print "<div style='background-color: #feeebd;border: 1px solid black;padding:20px;width:400px;height:300px;margin-top:200px;margin-left:auto;margin-right:auto;'>\n";
  print "<h3>Temp Labor Sign-in</h3>\n";
      make_select('workerid',$workers[0],$workers,'Name');
      make_text('pin','','Pin','Enter your pin number',20);
      print "<div class='label'></div>
      <div class='input'>
        <span id='error' style='color:red;font-weight:bold;display:none'></span>
      </div>
      <div class='clear'></div>\n";
      make_button('signin','Sign In','','','tempSignIn()');
      make_button('start','Start Clock','','',"tempShift('start')");
      make_button('stop','Stop Clock','','',"tempShift('stop')");
      make_button('hours','Show My Hours (last 30 days)','','',"tempShiftReport()");
  print "</form>\n";
  ?>
  <script type='text/javascript'>
    $(document).ready(function(){
        $('#start').hide();
        $('#stop').hide();
        $('#hours').hide();
    })
    function tempSignIn()
    {
        var workerid=$('#workerid').val();
        var pin=$('#pin').val();
        $.ajax({
          url: "includes/ajax_handlers/tempWorkers.php",
          type: "POST",
          data: ({action:'login',workerid:workerid,pin:pin}),
          dataType: "json",
          success: function(response){
            if(response.status=='success')
            {
               $('#error').hide(); 
               $('#signin').hide(); 
               if(response.shifter=='start')
               {
                 $('#start').show(); 
                 $('#hours').show(); 
               } else {
                 $('#hours').show();  
                 $('#stop').show();  
               } 
            } else {
               $('#error').html(response.message); 
               $('#error').show(); 
            } 
          }
        })
    }
    function tempShift(shifter)
    {
        var workerid=$('#workerid').val();
        var pin=$('#pin').val();
        $.ajax({
          url: "includes/ajax_handlers/tempWorkers.php",
          type: "POST",
          data: ({action:shifter,workerid:workerid,pin:pin}),
          dataType: "json",
          success: function(response){
            if(response.status=='success')
            {
               var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Shift updated successfully.</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'Shift updated!',
                    buttons:[
                    {
                        text: 'Ok',
                        click: function() { 
                            location.reload();
                        }
                    }]
                })
            } else {
               $('#error').html(response.message); 
               $('#error').show(); 
            } 
          }
        })
    }
    
    function tempShiftReport()
    {
        var workerid=$('#workerid').val();
        var pin=$('#pin').val();
        $.ajax({
          url: "includes/ajax_handlers/tempWorkers.php",
          type: "POST",
          data: ({action:'hours',workerid:workerid,pin:pin}),
          dataType: "html",
          success: function(response){
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span></p>'+response)
                .dialog({
                    autoOpen: true,
                    width: 500,
                    height: 500,
                    modal: true,
                    title: 'Shifts for past 30 days',
                    buttons:[
                    {
                        text: 'Ok',
                        click: function() { 
                            $( this ).dialog( "destroy" );
                    
                        }
                    }]
                })
             
          }
        })
    }
    
  </script>
  <?php
      
  footer();
?>