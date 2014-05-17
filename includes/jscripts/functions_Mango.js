//global variable initialization
             

function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode
    //allow decimal and dollar sign
    //alert (charCode);
    if (charCode==46 || charCode==36){return true;}
    if (charCode>=96 && charCode<=105){return true;}
    if (charCode > 31 && (charCode < 48 || charCode > 57))
    return false;

    return true;
}


function toggleBlock(blockID)
{
    var htmlBlock=document.getElementById(blockID);
    var mode=htmlBlock.style.display;
    mode=(mode=='block')?'none':'block';
    htmlBlock.style.display=mode;       
    
}


function toggleMenu()
{
    var cstat=document.getElementById('menuwrapper');
    cstat.style.display=='block'?cstat.style.display='none':cstat.style.display='block';
}
    
function insertZoneTotal(eid)
{
    //this function totals the number of average inserts required for the selected zones
    //in insert script - zoning function
    var totalcountSpan=document.getElementById('selcount');
    var totalcount=totalcountSpan.innerHTML;
    var insertrequest=document.getElementById('insertrequest').value;
    var insertdifferenceSpan=document.getElementById('requestvariance');
    var insertdifference=insertdifferenceSpan.innerHTML;
    //now split the eid by _ and see if we have a truck or zone
    var eidparts=eid.split("_");
    var countpart='';
    if (eidparts[0]=='zone')
    {
        countpart='zc_';    
    } else {
        countpart='tc_';
    }
    var selcount=document.getElementById(countpart+eidparts[1]).value;
    var ceid=document.getElementById(eid);
    if (ceid.checked)
    {
        totalcount=Number(totalcount)+Number(selcount);    
    } else {
        totalcount=Number(totalcount)-Number(selcount);
    }
    insertdifference=Number(insertrequest)-Number(totalcount);
    if (insertdifference<0)
    {
        insertdifferenceSpan.innerHTML=(', you have overbooked by '+abs(insertdifference)+'.');
    } else if (insertdifference>0) {
        insertdifferenceSpan.innerHTML=(', you have '+insertdifference +' left to book.');
    } else {
        insertdifferenceSpan.innerHTML=(', a perfect fit!');
    }
    totalcountSpan.innerHTML=totalcount;
}



function addRoll()
{
    //this function adds a roll and roll holder to the newsprint receive script
    var divHolder=document.getElementById("rolls");
    var lastIDe=document.getElementById('lastroll');
    var lastID=lastIDe.value;
    var newRoll="Roll tag: <input type='text' name='newroll_"+lastID+"' id='newroll_"+lastID+"' value='' size=20 onKeyPress='newsprintKeyCapture(\"newroll_"+lastID+"\",event,false,\"newweight_"+lastID+"\");return false;'/>";
    newRoll=newRoll+" Weight (kg): <input type='text' name='newweight_"+lastID+"' id='newweight_"+lastID+"' value='' size=5 onKeyPress='newsprintKeyCapture(\"newweight_"+lastID+"\",event,true,\"addbusroll\");return false;'/>";
    newRoll=newRoll+" <input type=checkbox name='delete_"+lastID+"' id='delete_"+lastID+"' /> Check to delete";
    newRoll=newRoll+"<br>\n";
    var roll=document.createElement('div');
    roll.id='rolldiv_'+lastID;
    roll.innerHTML=newRoll;
    divHolder.appendChild(roll);
    lastIDe.value=parseInt(lastID)+1;
    var newtext=document.getElementById('newroll_'+lastID);
    newtext.focus();       
}

function addBusinessRoll()
{
    var divHolder=document.getElementById("rolls");
    var lastIDe=document.getElementById('lastroll');
    var lastID=lastIDe.value;
    var newRoll="Roll tag: <input type='text' name='newroll_"+lastID+"' id='newroll_"+lastID+"' value='' onkeypress='newsprintKeyCapture(\"newroll_"+lastID+"\",event,false,\"addrecroll\");return false;' size=20 />";
    newRoll=newRoll+"<br>\n";
    var roll=document.createElement('div');
    roll.id='rolldiv_'+lastID;
    roll.innerHTML=newRoll;                                                                                                                 
    divHolder.appendChild(roll);
    var newtext=document.getElementById('newroll_'+lastID);
    newtext.focus();
    lastIDe.value=parseInt(lastID)+1;
}

function addJobRoll()
{
    var divHolder=document.getElementById("rolls");
    var lastIDe=document.getElementById('lastroll');
    var lastID=lastIDe.value;
    var newRoll="Roll tag: <input type='text' name='newroll_"+lastID+"' id='newroll_"+lastID+"' value='' size=20 onBlur='checkRollTag("+lastID+");'/>";
    newRoll=newRoll+" Reel: <input type='text' name='newreel_"+lastID+"' id='newreel_"+lastID+"' value='' size=5 />";
    newRoll=newRoll+" <input type=hidden name='rollid_"+lastID+"' id='rollid_"+lastID+"' value=''/>";
    newRoll=newRoll+" <input type=checkbox name='delete_"+lastID+"' id='delete_"+lastID+"' /> Check to delete";
    newRoll=newRoll+" <input type=checkbox name='butt_"+lastID+"' id='butt_"+lastID+"' /> Check to convert to butt roll";
    newRoll=newRoll+" <span id='msg_"+lastID+"'></span>";
    newRoll=newRoll+"<br>\n";
    var roll=document.createElement('div');
    roll.id='rolldiv_'+lastID;
    roll.innerHTML=newRoll;
    divHolder.appendChild(roll);
    lastIDe.value=parseInt(lastID)+1;   
}


function manualDraw()
{
    //function used if someone manually keys in a total draw
    document.getElementById('drawHD').value=0;
    document.getElementById('drawSC').value=0;
    document.getElementById('drawMail').value=0;
    document.getElementById('drawOffice').value=0;
    document.getElementById('drawCustomer').value=0;
    document.getElementById('drawOther').value=0;
}

function calcDraw()
{
    var drawHD=document.getElementById('drawHD').value;
    var drawSC=document.getElementById('drawSC').value;
    var drawMail=document.getElementById('drawMail').value;
    var drawOffice=document.getElementById('drawOffice').value;
    var drawCustomer=document.getElementById('drawCustomer').value;
    var drawOther=document.getElementById('drawOther').value;
    var drawTotal=document.getElementById('drawTotal');
    
    var dtotal=0;
    if (drawHD!=''){dtotal+=parseFloat(drawHD);}
    if (drawSC!=''){dtotal+=parseFloat(drawSC);}
    if (drawMail!=''){dtotal+=parseFloat(drawMail);}
    if (drawOffice!=''){dtotal+=parseFloat(drawOffice);}
    if (drawCustomer!=''){dtotal+=parseFloat(drawCustomer);}
    if (drawOther!=''){dtotal+=parseFloat(drawOther);}
    drawTotal.value=dtotal;
}

function toggleSection(sid)
{
    var sname=document.getElementById('section'+sid+'_name').value;
    var scheck=document.getElementById('section'+sid+'_enable');
    if (sname!='')
    {
        scheck.checked=true;
    }else{
        scheck.checked=false;
    }
}


function pressQuickJump(jobid)
{
    document.location.href="?jobid="+jobid;    
}


function newsprintKeyCapture(objid,e,checknumber,targetid)
{
    //the purpose of this function is to allow the enter key to 
    //point to the correct button to click.
    var key;
    var badkey=false;
    var obj=document.getElementById(objid);
     if(window.event)
          key = window.event.keyCode;     //IE
     else
          key = e.which;     //firefox
    
    if (checknumber) //we are checking to see if the key is a number
    {
        if (key > 31 && (key < 48 || key > 57))
        {
            badkey=true;
        } else {
            badkey=false;
        }
    }
    if (key == 13)
    {
        if (targetid=='addrecroll')
        {
            addBusinessRoll();
        } else if (targetid=='addbusroll')
        {
            addRoll();
        } else {
            var targetobj=document.getElementById(targetid);
            targetobj.focus();
        }
        //need to att buttonName to the input //Get the button the user wants to have clicked
        //var btn = document.getElementById(buttonName);
        //if (btn != null)
        //{ //If we find the button click it

        //    btn.click();
         //   event.keyCode = 0
        //}
     
    } else if (key==8) 
    {
        //delete pressed
        var objval=obj.value;
        objval = objval.substring(0, objval.length - 1);
        obj.value=objval;
    } else if (key==0)
    {
        //tab pressed
        if (targetid=='addrecroll')
        {
            addBusinessRoll();
        } else if (targetid=='addbusroll')
        {
            addRoll();
        } else {
            obj=document.getElementById(targetid);
            obj.focus();
        }
        
    } else {
        var objval=obj.value;
        if (badkey)
        {
            //dont add the key to the field, since we have a non-number and we checked for it.
        } else {
            obj.value=obj.value+String.fromCharCode(key);
        }
    }
}




function calcTonnage()
{
    //ok, we'll need to figure out which elements we want to count
    var divHolder=$(".ton");
    var tonnage=0;
    var current=0;
    
    for (var i=0; i<divHolder.length; i++){
        var subNode=divHolder[i];
        if (subNode.value!=''){current=parseFloat(subNode.value);}else{current=0;}
        tonnage=parseFloat(tonnage)+current;
    }
    
    var tonHolder=document.getElementById('total_weight');
    tonHolder.value=tonnage;
}

function checklistChange(objid)
{
    var tblock=document.getElementById('check_'+objid);
    var tclass=tblock.className;
    var jobid=document.getElementById('jobid').value;
    if (tclass=='checklist_checked')
    {
        //means we're unchecking a checked item
        $.ajax({
          url: "includes/ajax_handlers/jobmonitorPress.php",
          type: "POST",
          data: ({type:'checklist',jobid:jobid,value:'0',source:objid}),
          dataType: "html",
          success: function(response){
              response=response.split("|");
              if($.trim(response[0])=='success')
              {
                //all good!
                tblock.className='checklist_unchecked';
              } else {
                  //error
                  var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
             }
          },
           error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
        })
        
    } else {
        //means we're checking an unchecked item
        $.ajax({
          url: "includes/ajax_handlers/jobmonitorPress.php",
          type: "POST",
          data: ({type:'checklist',jobid:jobid,value:'1',source:objid}),
          dataType: "html",
          success: function(response){
              response=response.split("|");
              if($.trim(response[0])=='success')
              {
                //all good!
                tblock.className='checklist_checked';
              } else {
                  //error
                  var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
             }
          },
           error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
        })
        
    }
}

function benchmarkChange(objid,ares)
{
    var t=objid.split('_');
    if (t[0]=='pagesendtime')
    {
        var ob=document.getElementById(objid);
        objid=t[1];
        var type='page';
    } else if (t[0]=='plateapproval')
    {
        var ob=document.getElementById(objid);
        objid=t[1];
        var type='plateapprove';
    } else if (t[0]=='checklistOperator')
    {
        var ob=document.getElementById(objid);
        objid=t[1];
        var type='checklistOperator';
    } else if (t[0]=='jobOperator')
    {
        var ob=document.getElementById(objid);
        objid=t[1];
        var type='jobOperator';
    } else if (t[0]=='platereceivek')
    {
        var ob=document.getElementById(objid);
        objid=t[1];
        var type='platereceivek';
    } else if (t[0]=='platereceivec')
    {
        var ob=document.getElementById(objid);
        objid=t[1];
        var type='platereceivec';
    } else if (t[0]=='platereceivem')
    {
        var ob=document.getElementById(objid);
        objid=t[1];
        var type='platereceivem';
    } else if (t[0]=='platereceivey')
    {
        var ob=document.getElementById(objid);
        objid=t[1];
        var type='platereceivey';
    } else if (t[0]=='platereceiveall')
    {
        var ob=document.getElementById(objid);
        objid=t[1];
        var type='platereceiveall';
    } else if (t[0]=='colortime')
    {
        var ob=document.getElementById('colortime_'+t[1]);
        objid=t[1];
        var type='colorrelease';
    } else if (t[0]=='setupstart')
    {
        var ob=document.getElementById('benchmark_'+objid);
        objid=t[0];
        var type='stat';
    } else if (t[0]=='setupstop')
    {
        var ob=document.getElementById('benchmark_'+objid);
        objid=t[0];       
        var type='stat';  
    } else {
        var ob=document.getElementById('benchmark_'+objid);
        var type='benchmark';
    }
    var bvalue=ob.value;
    var jobid=document.getElementById('jobid').value;
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPress.php",
      type: "POST",
      data: ({type:type,jobid:jobid,value:bvalue,source:objid}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
            //all good!
          } else {
              //error
              var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
      },
       error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
    })
}

function jobPressStopNotes(jobid)
{
    if ($('#captureStopNotes').val()=='1')
    {
        var $dialog = $('<div id="jNotes"></div>').dialog({
          title: 'Job completion notes',        
          autoOpen: false, 
          height: 400, 
          width: 600,
          modal:true,
          buttons: [
              {
                text: 'Cancel',
                click: function() { 
                    $(this).dialog('destroy');
                }
              },
              {
                text: 'Save Notes',
                click: function() { 
                    $('#jobnotesForm').submit();
                    $(this).dialog('destroy');  
                }
              }
          ]
        })
        $dialog.load('includes/ajax_handlers/pressStopJobNotes.php?jobid='+jobid).dialog('open');
    } 
}

function jobMonitorPressTimeSet(type)
{
    var jobid=document.getElementById('jobid').value;
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPress.php",
      type: "POST",
      data: ({type:'stat',jobid:jobid,source:type}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              $('#benchmark_'+type).val(response[1]);
              if(type=='stoptime')
              {
                 jobPressStopNotes(jobid); 
              }
          } else {
              //error
              var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[0]+" "+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           } 
      },
       error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
       }
    })
}


function changeRollManifest(type,id,value)
{
    if(value=='field')
    {
        value=$('#'+type+"_"+id).val();
    }
    $.ajax({
      url: "includes/ajax_handlers/newsprintManifestUpdates.php",
      type: "POST",
      data: ({type:type,id:id,value:value}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              $('#'+type+"_"+id).css('backgroundColor','#00FF4D'); 
              
          } else if ($.trim(response[0])=='updated') {
              $("#rollstatus_"+id).html(response[1]);  
              $("#rollbatchdate_"+id).html('Batch date updated');  
          } else {
              //error
              var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           } 
      },
       error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
       }
    })
}

function parseFileMonitor()
{
    var delimiter=$('#delimiter').val();
    var sample=$('#sample').val();
    var i=0;
    var sampledisplay='';
    var spiece='';
    if (sample!='')
    {
        if (delimiter!='')
        {
            var spieces=sample.split(delimiter)
            for (i in spieces)
            {
                spiece=spieces[i].split(".");
                sampledisplay+='Position '+i+' is '+spiece[0]+"<br>";    
            }
            $('#sample_display').html(sampledisplay);
        } else {
            //harder :*( need to just worry about it in the piece display area
        }
    }
}

function showFileMonitorPiece(id)
{
    var delimiter=$('#delimiter').val();
    var sample=$('#sample').val();
    var i=0;
    var idval=$('#'+id).val();
    var spiece='';
    var partlength=0;
    if (sample!='')
    {
        if (delimiter!='')
        {
            var spieces=sample.split(delimiter)
            for (i in spieces)
            {
                spieces[i]=spieces[i].split(".");
            }
            if(idval!='')
            {
                spiece=spieces[idval];
            }
        } else {
            //harder :*(
            sample=sample.split(".");
            sample=sample[0];
            var parts=idval.split('-');
            partlength=parseInt(parts[1])-parseInt(parts[0])+1;
            //console.log('length is '+partlength+' part 1 is '+parts[1]+' part 0 is '+parts[0]);
            spiece=sample.substr(parts[0],partlength);
        }
    }
    if(idval!='')
    {
        if (id=='pub_pos')
        {
           $('#pub_sample').html('Sample: '+spiece) 
        } else if (id=='section_pos')
        {
           $('#section_sample').html('Sample: '+spiece) 
        } else if (id=='productcode_pos')
        {
           $('#productcode_sample').html('Sample: '+spiece) 
        } else if (id=='date_pos')
        {
           $('#date_sample').html('Sample: '+spiece) 
        } else if (id=='page_pos')
        {
           $('#page_sample').html('Sample: '+spiece) 
        } else if (id=='color_pos')
        {
           $('#color_sample').html('Sample: '+spiece) 
        }
    }    
}

/*new as of 11/27/10 */
function getLayouts()
{
    //need to build a url based on the selection information provided
    var tosend='';
    var section1=document.getElementById('section1_enable');
    var section2=document.getElementById('section2_enable');
    var section3=document.getElementById('section3_enable');
    if (section1.checked)
    {
        var vs1need=1;
    } else {
        var vs1need=0;
    }
    if (section2.checked)
    {
        var vs2need=1;
    } else {
        var vs2need=0;
    }
    if (section3.checked)
    {
        var vs3need=1;
    } else {
        var vs3need=0;
    }
    var vs1low=document.getElementById('section1_low').value;
    var vs1high=document.getElementById('section1_high').value;
    var vs1format=document.getElementById('section1_format').value;
    var vs1lead=document.getElementById('section1_lead').value;
    var vs1double=document.getElementById('section1_doubletruck').checked?1:0;
    var vs2low=document.getElementById('section2_low').value;
    var vs2high=document.getElementById('section2_high').value;
    var vs2format=document.getElementById('section2_format').value;
    var vs2lead=document.getElementById('section2_lead').value;
    var vs2double=document.getElementById('section2_doubletruck').checked?1:0;
    var vs3low=document.getElementById('section3_low').value;
    var vs3high=document.getElementById('section3_high').value;
    var vs3format=document.getElementById('section3_format').value;
    var vs3lead=document.getElementById('section3_lead').value;
    var vs3double=document.getElementById('section3_doubletruck').checked?1:0;
    
    $.ajax({
      url: 'includes/ajax_handlers/fetchMatchingLayouts.php',
      type: 'get',
      dataType: 'json',
      data: ({s1need:vs1need,s1low:vs1low,s1high:vs1high,s1format:vs1format,s1lead:vs1lead,s1double:vs1double,s2need:vs2need,s2low:vs2low,s2high:vs2high,s2format:vs2format,s2lead:vs2lead,s2double:vs2double,s3need:vs3need,s3low:vs3low,s3high:vs3high,s3format:vs3format,s3lead:vs3lead,s3double:vs3double}),
      success: function(j)
      {
          var options = '';
          for (var i = 0; i < j.length; i++) {
              options += '<option value="' + j[i].id + '">' + j[i].label + '</option>';
          }
          $("#layouts").html(options);  
      },
      error:function (xhr, ajaxOptions, thrownError){
        alert(xhr.status);
        alert(thrownError);
      }
    });
}

/*new as of 11/27/10 */
function getPressDiagram()
{
    if($('#layouts').val()!=0)
    {
        $.ajax({
          url: "includes/layoutGenerator.php",
          type: "GET",
          data: ({layoutid:$('#layouts').val(),mode:'inc',display:true,save:false}),
          dataType: "html",
          success: function(response){
             $('#layout_id').val($('#layouts').val());
             $('#layout_preview').html(response);
             $('#setlayoutBtn').show();
          },
          error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
       });
   }
}


function getMaintenanceHelpTopics(dept)
{
    if($('#keywords').val()!='')
    {
        $.ajax({
          url: "includes/ajax_handlers/findTroubleSolutions.php",
          type: "GET",
          data: ({keywords:$('#keywords').val(),dept:dept}),
          dataType: "html",
          success: function(response){
             $('#search_results').html(response);
          },
          error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+desc+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
       });
   }
}

/*new as of 11/27/10 */
function getInsertRuns()
{
    //alert('function called got pubid of '+$("#pub_id").val());
    var pubid=$("#pub_id").val();
    $.ajax({
    url: "includes/ajax_handlers/fetchInsertRuns.php",
    data: {pub_id:pubid},
    type:  'post',
    dataType: 'json',
    success: function (j) {
        var options = [], i = 0, o = null;
        for (i = 0; i < j.length; i++) {
            // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
            var options = '';
            for (var i = 0; i < j.length; i++) {
                options += '<option value="' + j[i].id + '">' + j[i].label + '</option>';
            }
            $("#run_id").html(options);
        }

        // hand control back to browser for a moment
        setTimeout(function () {
        $("#run_id")
                    .find('option:first')
                    .attr('selected', 'selected')
                    .parent('select')
                    .trigger('change');
        }, 0);
        },
        error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred retrieving insert runs:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           } 
        
    });
}

//inserter planning functions
function getInserterInfo(id)
{
    var inserterfield=$('#inserter_'+id);
    if(inserterfield.val()!=0)
    {
        var inserterid=inserterfield.val();
        $.ajax({
          url: "includes/ajax_handlers/insertPackage.php",
          type: "POST",
          data: ({action:'inserterinfo',inserterid:inserterid,id:id}),
          dataType: "html",
          success: function(response){
              var pieces=response.split("|");
              //alert('getting id of '+id+' and type of '+type+' resp0:'+pieces[0]);
              var checker=$('#doublecheck_'+id);
              if(pieces[0]=='choice')
              {
                $('#hoppers_'+id).val(pieces[1]);
                checker.attr('rel',pieces[1]+'_'+pieces[2]);
                checker.removeAttr('disabled');
              } else {
                $('#hoppers_'+id).val(pieces[1]);
                checker.attr('rel',pieces[1]+'_0');
                checker.attr('disabled','disabled');
              }
          },
           error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred retrieving inserter info:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
       });
   } else {
       $('#zone_holder').html('');
   }
}

function setMaxInserts(id)
{
    //get the rel value of the input checkbox
    var relvalue=$('#doublecheck_'+id).attr('rel');
    var checkbox=$('#doublecheck_'+id+':checked').length;
    //now split it
    var pieces=relvalue.split("_");
    //which piece we want depends on whether or not the checkbox is checked.
    //alert('for '+checkbox.attr('id')+' value is '+checkbox.val());
    if (checkbox>0)
    {
       $('#hoppers_'+id).val(pieces[1]); 
    } else {
       $('#hoppers_'+id).val(pieces[0]); 
    }
}

function showNextPackage(id)
{
    $('#div_'+id).css({'display':'block'});
    $('#addnext'+id).css({'display':'none'});
    $('#package_'+id).sortable("disable");
    if(id<10)
    {
        id++;
        $('#addnext'+id).css({'display':'block'});
    }
}

function showPackageSave(id)
{
    //the idea here is to display a pulsate effect on the 'saving' span element
    var options={};
    $('#saving_'+id).css({'display':'block'});
    $('#saving_'+id).effect( 'pulsate', options, 200, function(){$('#saving_'+id).css({'display':'none'})});
}

function addInsertToPackage(id,insertid,tabpages,pweight)
{
    var planid=$('#plan_id').val();
    var packageid=$('#packageid_'+id).val();
    $.ajax({
          url: "includes/ajax_handlers/insertPackage.php",
          type: "POST",
          data: ({action:'saveinsert',insertid:insertid,planid:planid,packageid:packageid,tabpages:tabpages,pieceweight:pweight}),
          dataType: "html",
          success: function(response){
              var pieces=response.split("|");
              if(pieces[0]=='success')
              {
                //now run out little saving effect even though we are done so the user sees an update 
                showPackageSave(id);
                var pack=$('#p-'+packageid);
                var packrel=pack.attr('rel');
                var packpieces=packrel.split('_');
                
                if(insertid.substr(0,2)=='p-')
                {
                    tabpages=tabpages.split('_');
                    var packcount=parseInt(packpieces[0])+parseInt(tabpages[0]);
                    var packpages=parseInt(packpieces[1])+parseInt(tabpages[1]);
                    var packweight=parseFloat(packpieces[2])+parseFloat(tabpages[2]);
                } else {
                    var packcount=parseInt(packpieces[0])+1;
                    var packpages=parseInt(packpieces[1])+parseInt(tabpages);
                    var packweight=parseFloat(packpieces[2])+parseFloat(pweight);
                }
                
                var packhtml=pack.html();
                packhtml=packhtml.split(':');
                pack.html(packhtml[0]+': <small>'+packpages+'pgs</small>');
                pack.attr({'rel':packcount+'_'+packpages+'_'+packweight}); 
              } else {
                var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+pieces[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'There was a problem saving the insert:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
              }
          },
           error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred saving the insert:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
                
           }
       });
  
}

function removeInsertFromPackage(id,insertid,tabpages,pweight)
{
    var packageid=$('#packageid_'+id).val();
    $.ajax({
          url: "includes/ajax_handlers/insertPackage.php",
          type: "POST",
          data: ({action:'removeinsert',insertid:insertid,packageid:packageid,tabpages:tabpages,pieceweight:pweight}),
          dataType: "html",
          success: function(response){
              var pieces=response.split("|");
              if(pieces[0]=='success')
              {
                //now run out little saving effect even though we are done so the user sees an update 
                showPackageSave(id);
                
                var pack=$('#p-'+packageid);
                var packrel=pack.attr('rel');
                var packpieces=packrel.split('_');
                if(insertid.substr(0,2)=='p-')
                {
                    tabpages=tabpages.split('_');
                    var packcount=parseInt(packpieces[0])-parseInt(tabpages[0]);
                    var packpages=parseInt(packpieces[1])-parseInt(tabpages[1]);
                    var packweight=parseFloat(packpieces[2])-parseFloat(tabpages[2]);
                } else {
                    var packcount=parseInt(packpieces[0])-1;
                    var packpages=parseInt(packpieces[1])-parseInt(tabpages);
                    var packweight=parseFloat(packpieces[2])-parseFloat(pweight);
                }
                var packhtml=pack.html();
                packhtml=packhtml.split(':');
                pack.html(packhtml[0]+': <small>'+packpages+'pgs</small>');
                pack.attr({'rel':packcount+'_'+packpages+'_'+packweight});
              } else {
                var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+pieces[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'There was a problem removing the insert:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
              }
          },
           error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred removing the insert:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                });
           }
       }); 
}

function addJacketToPackage(id,insertid)
{
    var planid=$('#plan_id').val();
    var packageid=$('#packageid_'+id).val();
    $.ajax({
          url: "includes/ajax_handlers/insertPackage.php",
          type: "POST",
          data: ({action:'savejacket',insertid:insertid,planid:planid,packageid:packageid}),
          dataType: "html",
          success: function(response){
              var pieces=response.split("|");
              if(pieces[0]=='success')
              {
                //now run out little saving effect even though we are done so the user sees an update 
                showPackageSave(id);               
              } else {
                var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+pieces[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'There was a problem saving the jacket:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
              }
          },
           error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred saving the jacket:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
       });     
}

function removeJacketFromPackage(id,insertid)
{
    var packageid=$('#packageid_'+id).val();
    $.ajax({
          url: "includes/ajax_handlers/insertPackage.php",
          type: "POST",
          data: ({action:'removejacket',insertid:insertid,packageid:packageid}),
          dataType: "html",
          success: function(response){
              var pieces=response.split("|");
              if(pieces[0]=='success')
              {
                //now run out little saving effect even though we are done so the user sees an update 
                showPackageSave(id);               
              } else {
                var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+pieces[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'There was a problem removing the jacket:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
              }
          },
           error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred removing the jacket:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
       }); 
}

function saveInsertPackage(id)
{
   var action='update';
   var packageid=$('#packageid_'+id).val();
   if(packageid==0){action='insert';}
   var runname=$('#name_'+id).val();
   var runquantity=$('#quantity_'+id).val();
   var rundatetime=$('#date_'+id).val();
   var runinserter=$('#inserter_'+id).val();
   var rundouble=$('#doublecheck_'+id+':checked').length;
   var runhoppers=$('#hoppers_'+id).val();
   var runjacket=$('#jacketid_'+id).val();
   var pubid=$('#pub_id').val();
   var pubdate=$('#pub_date').val();
   var planid=$('#plan_id').val();
   var planrequest=$('#plan_request').val();
   if(runname=='' || rundatetime=='')
   {
      var $dialog = $('<div id="jConfirm"></div>')
        .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You must enter at least a package name and time before saving.</p>')
        .dialog({
            autoOpen: true,
            modal: true,
            title: 'There was a problem saving the package:',
            buttons:[
            {
                text: 'Close',
                click: function() { 
                    $(this).dialog('destroy');
                }
            }]
        }) 
   } else {
   $.ajax({
          url: "includes/ajax_handlers/insertPackage.php",
          type: "POST",
          data: ({action:action,packageid:packageid,id:id,name:runname,datetime:rundatetime,inserterid:runinserter,doubleout:rundouble,hoppers:runhoppers,jacketid:runjacket,pubid:pubid,planid:planid,pubdate:pubdate,planrequest:planrequest,runquantity:runquantity}),
          dataType: "html",
          success: function(response){
              var pieces=response.split("|");
              if(pieces[0]=='success')
              {
                showPackageSave(id);
                if(packageid=='0')
                {
                    var newid=pieces[1];
                    $('#packageid_'+id).val(newid);
                    //now enable the package ul
                    $('#package_'+id).sortable("enable");
                    $('#jacketholder_'+id).css({'display':'block'});
                    //add a new package to the list in the other packages area
                    var newli="<li id='p-"+newid+"' rel='0_0' class='inserts'>"+runname+": <small>0pgs</small></li>\n";
                    $('#packagelist').append(newli);
                } 
                //now run out little saving effect even though we are done so the user sees an update 
                               
              } else {
                var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+pieces[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'There was a problem saving the package:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
              }
          },
           error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred retrieving inserter info:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
       }); 
   }  
}

function deleteInsertPackage(id)
{
    //get number of elements in the package
    var icount=$('#package_'+id).children('li').length;
    var packageid=$('#packageid_'+id).val();
    if(icount==0)
    {
        $.ajax({
          url: "includes/ajax_handlers/insertPackage.php",
          type: "POST",
          data: ({action:'delete',packageid:packageid}),
          dataType: "html",
          success: function(response){
              var pieces=response.split("|");
              if(pieces[0]=='success')
              {
                //now disable the package ul
                $('#package_'+id).sortable("disable");
                $('#packageid_'+id).val(0);
                $('#name_'+id).val('');
                $('#quantity_'+id).val('');
                $('#date_'+id).val('');
                $('#inserter_'+id).val(0);
                $('#inserts_'+id).val(0);
                $('#tabpages_'+id).val(0);
                $('#weight_'+id).val(0);
                $('#packagedisplay_'+id).html('0');
                $('#packagetabpages_'+id).html('0');
                $('#packageweight_'+id).html('0');
                $('#doublecheck_'+id+':checked').length;
                $('#hoppers_'+id).val(0);
                $('#jacketid_'+id).val(0);
                $('#p_'+packageid).remove();
                $('#jacketholder_'+id).css({'display':'none'});
                if(id>0)
                {
                    $('#div_'+id).css({'display':'none'});
                    $('#addnext'+id).css({'display':'none'});
                    var plusone=id+1;
                    $('#addnext'+plusone).css({'display':'block'});
                }
                
              } else {
                var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+pieces[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'There was a problem deleting the package:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
              }
          },
           error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred retrieving inserter info:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
       });
    } else {
        var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Please return all inserts to the insert list before attempting to delete the package.</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'Unable to delete package:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                }) 
    }
}

/*new as of 11/27/10 */
function getInsertRunZones()
{
    if($('#run_id').val()!=0)
    {
        var insertid=$('#insertid').val();
        var schedid=$('#schedid').val();
        $.ajax({
          url: "includes/ajax_handlers/fetchInsertRunZones.php",
          type: "POST",
          data: ({runid:$('#run_id').val(),insertid:insertid,schedid:schedid}),
          dataType: "html",
          success: function(response){
             $('#zone_holder').html($.trim(response));
          },
           error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred retrieving zones.',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
       });
   } else {
       $('#zone_holder').html('');
   }
}

function saveInsertForm()
{
    var pubid=$('#pub_id').val();
    var runid=$('#run_id').val();
    var insertDate=$('#insertDate').val();
    var insertCount=$('#insertCount').val();
    var action=$('#action').val();
    var insertid=$('#insert_id').val();
    var maininsert=$('#main_insert').val();
    var zonetotal=$('#zoned_total').val();
    var zones='';
    var className='insertzones';
    $("."+className).each( function() {
       if($(this).is(':checked')){zones=zones+$(this).attr("id")+"-";}
    });
    //alert(queryString);
    $.ajax({
          url: "includes/ajax_handlers/insertActions.php",
          type: "POST",
          data: ({insertid:insertid,pubid:pubid,runid:runid,insertDate:insertDate,insertCount:insertCount,action:action,maininsert:maininsert,zonetotal:zonetotal,zones:zones}),
          dataType: "html",
          success: function(response){
                var pieces=response.split("|");
                if(pieces[0]=='success')
                {
                    if (pieces[1]=='add')
                    {
                        $('#insertpubs').append(pieces[2]);
                    } else {
                        $(pieces[1]).html(pieces[2]);
                    }
                    $('#addpub_div').slideToggle('fast');
                    
                } else {
                    var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+pieces[1]+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred saving the publication:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('destroy');
                            }
                        }]
                    })
                }
                    
          },
           error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred retrieving zones:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
       }); 
}
                

function insertDelete(insertid,main)
{
    var action='deletepub';
    $.ajax({
          url: "includes/ajax_handlers/insertActions.php",
          type: "POST",
          data: ({action:action,insertid:insertid,main:main}),
          dataType: "html",
          success: function(response){
             var pieces=response.split("|");
             if (pieces[0]=='success')
             {
                 $('#insert'+insertid).remove();
             } else {
                 var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+pieces[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred deleting the publication:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           } 
          },
          error: function (xhr, desc, er) {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
    })
}

function insertEdit(insertid,main)
{
    
    if(insertid!=0){
        var action='editpub';
        $.ajax({
              url: "includes/ajax_handlers/insertActions.php",
              type: "POST",
              data: ({action:action,insertid:insertid,main:main}),
              dataType: "html",
              success: function(response){
                 var pieces=response.split("|");
                 if (pieces[0]=='success')
                 {
                    $('#insert_id').val(pieces[6]);
                    $('#pub_id').val(pieces[1]);
                    getInsertRuns();
                    $('#run_id').val(pieces[2]);
                    getInsertRunZones(insertid);
                    $('#insertDate').val(pieces[3]);
                    $('#insertCount').val(pieces[4]);
                    $('#action').val(pieces[5]);
                    $('#main_insert').val(main);
                 } else {
                     var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+pieces[1]+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred retrieving insert information:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('destroy');
                            }
                        }]
                    })
               } 
              },
              error: function (xhr, desc, er) {
                var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('destroy');
                            }
                        }]
                    })
               }
        })
    } else {
        //new pub, so just defaults
        $('#pub_id').val(0);
        $('#run_id').val(0);
        $('#insertDate').val('');
        $('#insertCount').val(0);
        $('#action').val('saveinsertpub');
        $('#insertid').val(0);
        $('#main_insert').val(main);
    }
}

/*new as of 11/27/10 */
function toggleCheckBoxes(status,className) 
{
    if(className==''){className='checkbox'}
    $("."+className).each( function() {
        $(this).attr("checked",status);
    }
    )
    calcInsertZoneTotal(className);
}

/*new as of 11/27/10 */
function calcInsertZoneTotal(className) 
{
    var ztotal=0;
    if(className==''){className='checkbox'}
    $("."+className).each( function() {
       if($(this).is(':checked')){ztotal=ztotal+parseInt($(this).attr("rel"));}
    })
    $('#zonetotal').html(ztotal);
    $('#zoned_total').val(ztotal);
}

function alertMessage(message,type)
{
    if (type=='error')
    {
        var autoclose=false;
        var time=5000;
        var useescape=true;
        var location=top;
        var msgclass='fail';
    } else {
        var autoclose=true;
        var time=5000;
        var useescape=true;
        var location=top;
        var msgclass='success';
    }
    
    jQuery('body').showMessage({
    thisMessage:      message,
    className:        msgclass,
    position:        location,
    opacity:        90,
    useEsc:            useescape,
    displayNavigation: true,
    autoClose:         autoclose,
    delayTime:         time,
    closeText:         'close',
    escText:      'Esc Key or'
    });

}


/***************************************************************
*
* THESE ARE FUNCTIONS FOR NEWSPRINT ORDER ITEMS
*
***************************************************************/
function newsprintOrderItemAdd(orderid)
{
    $.ajax({
      url: "includes/ajax_handlers/newsprintOrderItems.php",
      type: "POST",
      data: ({action:'addorderitem',orderid:orderid}),
      dataType: "html",
      success: function(response){
          response=response.split("|||");
          if($.trim(response[0])=='success')
          {
             $('#orderitems').append(response[1]);   
          } else {
              var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
          } 
      },
       error:function (xhr, ajaxOptions, thrownError){
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           } 
    })
    return false;
    
}
function newsprintOrderItemDelete(orderitemid,orderid)
{
    var $dialog = $('<div id="jConfirm"></div>')
    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure?</p>')
    .dialog({
        autoOpen: true,
        title: 'Are you sure you want to Delete?',
        modal: true,
        buttons: [
                {
                    text: 'Cancel',
                    click: function() { 
                        $dialog.dialog('destroy');
                    }
                },
                {
                    text: 'Delete Item',
                    click: function() { 
                        if(orderitemid!='new')
                        {
                        $.ajax({
                          url: "includes/ajax_handlers/newsprintOrderItems.php",
                          type: "POST",
                          data: ({action:'deleteorderitem',itemid:orderitemid,orderid:orderid}),
                          dataType: "html",
                          success: function(response){
                              response=response.split("|");
                              if($.trim(response[0])=='success')
                              {
                                 $('#item_'+orderitemid).remove(); 
                                 $dialog.dialog('destroy');    
                              } else {
                                 $dialog.html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                                 $dialog.dialog({
                                    title: 'An error occurred:',
                                    buttons:[
                                    {
                                        text: 'Close',
                                        click: function() { 
                                           $dialog.dialog('destroy');
                                        }
                                    }]
                                    })
                              } 
                          },
                           error:function (xhr, ajaxOptions, thrownError){
                           $dialog.html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                            .dialog({
                                title: 'An error occurred:',
                                buttons:[
                                {
                                    text: 'Close',
                                    click: function() { 
                                        $dialog.dialog('destroy');
                                    }
                                }]
                            })
                            } 
                           
                       });
                        } else {
                            //never saved, just remove the row and close the dialog
                            $('#item_'+orderitemid).remove(); 
                            $(this).dialog('destroy');
                        }   
                    }
                }
                ]
            ,
        open: function() {
            $('.ui-dialog-buttonpane > button:last').focus();
        }
   
    });
    return false;
}


function newsprintOrderItemSave(orderitemid,orderid)
{
    //console.log('orderitemid is '+orderitemid);
    $.ajax({
      url: "includes/ajax_handlers/newsprintOrderItems.php",
      type: "POST",
      data: ({action:'saveorderitem',itemid:orderitemid,orderid:orderid,paper:$('#paper_'+orderitemid).val(),size:$('#size_'+orderitemid).val(),tonnage:$('#tonnage_'+orderitemid).val()}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
             $('#success_'+orderitemid).css("display","block"); 
             //change the id of the existing block if it's new
             if(orderitemid=='new')
             {
                 //the new order id is returned as item 2 in the array
                 $('#paper_new').attr("id",'#paper_'+response[1]);
                 $('#size_new').attr("id",'#paper_'+response[1]);
                 $('#tonnage_new').attr("id",'#paper_'+response[1]);
                 $('#item_new').attr("id",'#paper_'+response[1]);
                 $('#item_new').attr("id",'#paper_'+response[1]);
                 $('#save_new').attr("id",'#paper_'+response[1]);
                 $('#del_new').attr("id",'#paper_'+response[1]);
             }
                
          } else {
              var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
          } 
      },
       error:function (xhr, ajaxOptions, thrownError){
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
           } 
    })
    return false;
}


/*****************************************************************************
*
* THESE SCRIPTS ARE FOR NEWSPRINT BATCH PROCESSING
*
*
******************************************************************************/





/********************************************************************************************
*
*  THESE SCRIPTS ARE FOR THE PAGINATION JOB MONITOR
*
********************************************************************************************/
function viewPageSubs(id)
{
    $('#subpages'+id).slideToggle('fast');
    //get details for that page with an ajax call
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPagination.php",
      type: "POST",
      data: ({action:'getpageversions',id:id}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              $('#subpages'+id).html(response[1]);
          }
      },
      error:function (xhr, ajaxOptions, thrownError){
          var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
           }
    })
    return false;
    
}

function viewPageDetails(id)
{
    $('#pageDetails'+id).slideToggle('fast');
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPagination.php",
      type: "POST",
      data: ({action:'getpagedetails',id:id}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              $('#pageDetails'+id).html(response[1]);
          }
      },
      error:function (xhr, ajaxOptions, thrownError){
          var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
       }
    })
    return false;
}


function viewPlateDetails(id,type)
{
   $('#plateDetails'+id).slideToggle('fast');
   $.ajax({
      url: "includes/ajax_handlers/jobmonitorPagination.php",
      type: "POST",
      data: ({action:'getplatedetails',id:id}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              $('#plateDetails'+id).html(response[1]);
          }
      },
      error:function (xhr, ajaxOptions, thrownError){
          var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                }) 
       }
    })
    return false;
}

function viewPlateSubs(id,type)
{
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPagination.php",
      type: "POST",
      data: ({action:'getplateversions',id:id}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              $('#subplates'+id).html(response[1]);
              $('#toggleplatesub'+id).click(function() {
              $('#subplates'+id).slideToggle('fast', function() {
                // Animation complete.
              });
            });
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
       }
    })
    return false;

}

function setPaginationTime(id, type, value)
{
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPagination.php",
      type: "POST",
      data: ({action:'settime',id:id,type:type,value:value}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              if(type=='pageapprove')
              {
                 $('#pagesendtime_'+id).val(response[1]);  
              } else if (type=='pagecolor')
              {
                 $('#colortime_'+id).val(response[1]); 
              } else if (type=='plateapprove')
              {
                 $('#platesendtime_'+id).val(response[1]); 
              } else if (type=='platecolor')
              {
                 $('#platecolortime_'+id).val(response[1]); 
              } else if (type=='plateapproveall')
              {
                 $('#plateapproveall_'+id).val(response[1]); 
                 $('#plateapprovek_'+id).val(response[1]); 
                 $('#plateapprovec_'+id).val(response[1]); 
                 $('#plateapprovem_'+id).val(response[1]); 
                 $('#plateapprovey_'+id).val(response[1]); 
              } else if (type=='plateapprovek')
              {
                 $('#plateapprovek_'+id).val(response[1]); 
              } else if (type=='plateapprovec')
              {
                 $('#plateapprovec_'+id).val(response[1]); 
              } else if (type=='plateapprovem')
              {
                 $('#plateapprovem_'+id).val(response[1]); 
              } else if (type=='plateapprovey')
              {
                 $('#plateapprovey_'+id).val(response[1]); 
              }
          } else {
              //error
              var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
       }
    })
    return false;

}

function plateMonitorExtra(type,jobid,value)
{
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPagination.php",
      type: "POST",
      data: ({action:'plateextra',type:type,value:value,jobid:jobid}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              
          } else {
             var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
       }
    })
    return false;

}

function remakePage(pid)
{
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPagination.php",
      type: "POST",
      data: ({action:'remakepage',id:pid}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              //alert("response="+response[0]+"\n"+"newpageid="+response[1]+"\n"+"oldplateid="+response[2]+"\n"+"newplateid="+response[3]+"\n"+"platehtml="+response[4]+"\n"+"pagehtml="+response[5]+"\n");
              //ok, we need to change the id of the existing div and stick in the new contents
              $('#page'+pid).html(response[5]);
              $('#page'+pid).attr("id", 'page'+response[1]);
              //update the plate with new plate id and clear times
              $('#plate'+response[2]).html(response[4]);
              $('#plate'+response[2]).attr("id", 'plate'+response[3]);
              //alert('new page id is '+response[1]);
          } else {
              //error
              var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
       }
    })
    return false; 
}

function resetRemakePage(pageid,originalpageid,plateid,originalplateid)
{
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPagination.php",
      type: "POST",
      data: ({action:'undoremake',id:pageid,value:pageid+"|"+originalpageid+"|"+plateid+"|"+originalplateid}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              //alert("response="+response[0]+"\n"+"response1="+response[1]+"\n"+"response2="+response[2]);
              //ok, we need to change the id of the existing div and stick in the new contents
              $('#page'+pageid).html(response[1]);
              $('#page'+pageid).attr("id", 'page'+originalpageid);
              //update the plate with new plate id and clear times
              $('#plate'+plateid).html(response[2]);
              $('#plate'+plateid).attr("id", 'plate'+originalplateid);
          } else {
              //error
              var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
       }
    })
    return false; 
}
 

function removeStopNote(stopid)
{
    ajaxpage('includes/generalAjaxHandler.php?action=&type=deletestopnote&id='+stopid+'&secondid=0');
}

function removeJobStop(jobid,stopid)
{
    var answer=confirm('Are you sure you want to delete this stop?');
    if (answer)
    {
        ajaxpage('includes/generalAjaxHandler.php?action=&type=killstop&id='+jobid+'&secondid='+stopid);
    }
}

function pressmanChange(jobid,pressmanid)
{
    var pblock=document.getElementById('pressman_'+pressmanid);
    var pclass=pblock.className;
    if (pclass=='checklist_checked')
    {
        //means we're unchecking a checked item
        $.ajax({
          url: "includes/ajax_handlers/jobmonitorPress.php",
          type: "POST",
          data: ({type:'crew',jobid:jobid,value:'0',source:pressmanid}),
          dataType: "html",
          success: function(response){
              response=response.split("|");
              if($.trim(response[0])=='success')
              {
                //all good!
                pblock.className='checklist_unchecked';
              } else {
                  //error
                  var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
              }
          },
           error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
        })

    } else {
        //means we're checking an unchecked item
        $.ajax({
          url: "includes/ajax_handlers/jobmonitorPress.php",
          type: "POST",
          data: ({type:'crew',jobid:jobid,value:'1',source:pressmanid}),
          dataType: "html",
          success: function(response){
              response=response.split("|");
              if($.trim(response[0])=='success')
              {
                //all good!
                pblock.className='checklist_checked';
              } else {
                  //error
                  var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
              }
          },
           error:function (xhr, ajaxOptions, thrownError){
           var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
           }
        })
        
    }
    
}


function pressStopInfo(obj,type,towerid,subpart)
{
    var cid="c"+obj.id;
    var cin=document.getElementById(cid);
    var infobox=document.getElementById('stopinfo');
    var cinfo=infobox.value;
    var newinfo=type+"_"+towerid+"_"+subpart+"|";
    if (cin.value=='0')
    {
        cin.value='1';
        obj.className='imgSelected';
        cinfo=cinfo+newinfo;
        infobox.value=cinfo;
    } else if (cin.value=='1')
    {
        cin.value='0';
        obj.className='imgUnselected';
        cinfo=cinfo.replace(newinfo,'');
        infobox.value=cinfo;
    }
}


function pressBoxes()
{
    var tjobid=$('#jobid').val(); 
    $.ajax({
      url: "includes/ajax_handlers/jobmonitorPressBoxes.php",
      type: "POST",
      data: ({jobid:tjobid,type:'missingpages'}),
      dataType: "html",
      success: function(response){
          $('#pageslist').html(response);
          
      }
     });
     $.ajax({
      url: "includes/ajax_handlers/jobmonitorPressBoxes.php",
      type: "POST",
      data: ({jobid:tjobid,type:'missingplates'}),
      dataType: "html",
      success: function(response){
          $('#plateslist').html(response);
      }
     });
     $.ajax({
      url: "includes/ajax_handlers/jobmonitorPressBoxes.php",
      type: "POST",
      data: ({jobid:tjobid,type:'remakes'}),
      dataType: "html",
      success: function(response){
             $('#remakeslist').html(response);
          
      }
     });
}

function getDeadlineDetails(jobid) {
     $.ajax({
      url: "includes/ajax_handlers/jobmonitorPagination.php",
      type: "POST",
      data: ({id:jobid,action:'deadlines'}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
             $('#deadlinedata').html(response[1]);
          } else {
             var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
          }
      }
     });
}

function pressDataCheckZero(id)
{
    if (document.getElementById(id).value=='')
    {
        document.getElementById(id).value=0;
    }
}

function pressCounterCheck()
{
    var counterstart=document.getElementById('counterstart').value;
    var counterstop=document.getElementById('counterstop').value;
    var alertdiv=document.getElementById('counteralert');
    var draw=document.getElementById('draw').value;
    var difference=counterstop-counterstart-draw;
    if (difference>5000)
    {
        alertdiv.innerHTML="<span style='color:red;font-weight:bold'>Large spoilage, please check numbers</span>";
    } else {
        alertdiv.innerHTML="";
    }
}

function checkPressData()
{
    //this script will check start/stop counters and times as well as a few other factors
    var override=document.getElementById('override');
    var saveform=true;
    var alertmessage="FOUND THE FOLLOWING ERROR(S):\n";
    var lead=document.getElementById('pressoperator').value;
    var counterstart=document.getElementById('counterstart').value;
    var counterstop=document.getElementById('counterstop').value;
    var difference=counterstop-counterstart;
    var startdate=document.getElementById('starttime').value;
    var gooddate=document.getElementById('goodtime').value;
    var stopdate=document.getElementById('stoptime').value;
    var startparts=startdate.split(" ");
    var start=startparts[0].split("-");
    var stime=startparts[1].split(":");
    var starthour=stime[0];
    var startminute=stime[1];
    var datestart=new Date();    
    datestart.setFullYear(start[0],start[1]-1,start[2]);
    datestart.setHours(starthour);
    datestart.setMinutes(startminute);
    
    var goodparts=startdate.split(" ");
    var good=goodparts[0].split("-");
    var gtime=goodparts[1].split(":");
    var goodhour=gtime[0];
    var goodminute=gtime[1];
    var dategood=new Date();    
    dategood.setFullYear(good[0],good[1]-1,good[2]);
    dategood.setHours(goodhour);
    dategood.setMinutes(goodminute);
    
    var stopparts=startdate.split(" ");
    var stop=stopparts[0].split("-");
    var stoptime=stopparts[1].split(":");
    var stophour=stoptime[0];
    var stopminute=stoptime[1];
    var datestop=new Date();    
    datestop.setFullYear(stop[0],stop[1]-1,stop[2]);
    datestop.setHours(stophour);
    datestop.setMinutes(stopminute);
    
    var dt=document.getElementById('dataset').value;
    var dtdate=dt.split(' ');
    var dttime=dtdate[1].split(':');
    dtdate=dtdate[0];
    dtdate=dtdate.split('-');
    var dataset=new Date(dtdate[0],dtdate[1],dtdate[2],dttime[0],dttime[1]);
    //ok we have dates, lets start comparing
    if (datestart>dataset)
    {
        saveform=false;
        alertmessage+='You are trying to set a start time later than right now!\n';
    }
    if (datestop>dataset)
    {
        saveform=false;
        alertmessage+='You are trying to set a stop time later than right now!\n';
    }
    if (dategood>dataset)
    {
        saveform=false;
        alertmessage+='You are trying to set a good time later than right now!\n';
    }
    if (datestart>datestop)
    {
        saveform=false;
        alertmessage+='Your start date/time is later than the stop!\n';
    }
    
    if (dategood>datestop)
    {
        saveform=false;
        alertmessage+="Your good date/time is later than the stop!\n";
    }
    if (dategood<datestart)
    {
        saveform=false;
        alertmessage+='Your good date/time is set earlier than the start!\n';
    }
    //now check the minutes alert on anything over 6 hours in time
    var runtime=((((datestop.getTime()-datestart.getTime())/1000)/60)/60)
    if (runtime>pressRunTimeThreshold)
    {
        saveform=false;
        alertmessage+='Looks like a run time over 6 hours, most likely a problem!\n';
    }
    
    if (counterstart==0)
    {
        saveform=false;
        alertmessage+='You missed the counter start number!\n';
    } else if(counterstop==0)
    {
        saveform=false;
        alertmessage+='You missed the counter stop number!\n';
    } else if(difference<0)
    {
        saveform=false;
        alertmessage+='Check your counter numbers, looks like they may be flipped.\n';    
    } else if(difference>pressCounterThreshhold)
    {
        saveform=false;
        alertmessage+='The difference in stop and start is over the threshhold. Please check your number. If they are right, you can change the threshhold in system preferences.\n';    
    }
    if (lead==0)
    {
        saveform=false;
        alertmessage+='Please set lead operator before saving.\n';
    }
    
    
    //if override is checked, alert to that fact, submit the form
    if (override.checked)
    {
        saveform=true;
        alert('You checked override, so we are saving even if there are errors!');
    }
    if (saveform==false)
    {
        alert(alertmessage);   
    } else {
        document.getElementById("pressdata").submit(); 
    }
    
    
}

function checkMailroomData()
{
    var saveform=true;
    var alertmessage='';
    alertmessage='Testing mode';
    if (saveform==false)
    {
        alert(alertmessage);   
    } else {
        document.getElementById("maildata").submit(); 
    }
    
}

function limitText(limitField, limitNum) {
    if (limitField.value.length > limitNum) {
        limitField.value = limitField.value.substring(0, limitNum);
    }
}

function toggleInsertDamage()
{
    var dblock=document.getElementById('insertDamage');
    var dcheck=document.getElementById('damaged');
    if (dcheck.checked)
    {
        dblock.style.display='block';
    } else {
        dblock.style.display='none';
    }
    
}

function checkAllCheckboxes(containDiv,status)
{
    $("#"+containDiv+" input").each( function() {
        $(this).attr("checked",status);
    })
    $("."+containDiv+" input").each( function() {
        $(this).attr("checked",status);
    })
}

function uncheckAllCheckboxes()
{
    var fields=document.forms[0]
    for (i = 0; i < fields.length; i++)
    fields[i].checked = false ;
    
}

var selectDest='';



/*functions for purchase order system*/
function addNewPartFromPO()
{
    var pnameInput=document.getElementById('newpartname');
    var pnumberInput=document.getElementById('newpartnumber');
    var pcostInput=document.getElementById('newpartcost');
    var ptaxableInput=document.getElementById('newparttaxable');
    if (ptaxableInput.checked)
    {
        var taxable=1;
    } else {
        var taxable=0;
    }
    if (pnameInput.value!='')
    {
         $.ajax({
          url: "includes/ajax_handlers/poPartLookup.php",
          type: "POST",
          data: ({action:'addPOPart',partname:pnameInput.value,partnumber:pnumberInput.value,partcost:pcostInput.value,taxable:taxable}),
          dataType: "html",
          success: function(response){
              response=response.split("|");
              if($.trim(response[0])=='success')
              {
                pnameInput.value='';
                pnumberInput.value='';
                pcostInput.value='0.00';
                ptaxableInput.checked=false;
                 var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'Success:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
              } else {
                 var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
              }
          },
           error:function (xhr, ajaxOptions, thrownError){
               var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
           }
         });
        
        
    } else {
        var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You must enter at least a part name.</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
    }
}

function receiveInventoryItem(pid,poid)
{
    var received=document.getElementById('received_'+pid).value
    var ordered=document.getElementById('qty_'+pid).value
    
    $.ajax({
          url: "includes/ajax_handlers/poPartLookup.php",
          type: "POST",
          
          data: ({action:'receivepoitem',partid:pid,poid:poid,received:received,ordered:ordered}),
          dataType: "html",
          success: function(response){
              response=response.split("|");
              if($.trim(response[0])=='success')
              {
                 document.getElementById('ok_'+pid).style.display="block";
              } else {
                 document.getElementById('error_'+pid).style.display="block";
                 var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
              }
          },
           error:function (xhr, ajaxOptions, thrownError){
               var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
           }
    });

}

function addInventoryItem(type)
{
    if (type=='name')
    {
        var pid=document.getElementById('spartname_ID').value;
        document.getElementById('spartname').value='';
        document.getElementById('spartname_ID').value='';
    }else if(type=='number')
    {
        var pid=document.getElementById('spartnumber_ID').value;
        document.getElementById('spartnumber').value='';
        document.getElementById('spartnumber_ID').value='';
    } else if(type='service')
    {
        var pid=document.getElementById('sservicename_ID').value;
        document.getElementById('sservicename').value='';
        document.getElementById('sservicename_ID').value='';
    }
    if (pid!='' && pid!='0')
    {
        $.ajax({
          url: "includes/ajax_handlers/poPartLookup.php",
          type: "POST",
          data: ({action:'addpart',type:type,partid:pid}),
          dataType: "html",
          success: function(response){
              response=response.split("|");
              if($.trim(response[0])=='success')
              {
                    $('#poitems').append(response[1]);
                    calculatePOLine(pid)
              } else {
                 var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
              }
          },
           error:function (xhr, ajaxOptions, thrownError){
               var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
           }
         });
        
        
        
    }
    
}


function calculatePOLine(pid)
{
    var qtyitem='qty_'+pid;
    var costitem='unit_'+pid;
    var lineitem='linetotal_'+pid;
    var qty=Number(document.getElementById(qtyitem).value);
    var costHolder=document.getElementById(costitem);
    var cost=Number(costHolder.value);
    var linecost=Number(cost*qty);
    linecost=linecost.toFixed(2);
    document.getElementById(lineitem).value=linecost;
    cost=cost.toFixed(2);
    costHolder.value=cost;
    
    calculatePO();
}

function deleteInventoryItem(itemID)
{
    var divHolder=document.getElementById("poitems");
    var poitem=document.getElementById('lineitem_'+itemID);   
    divHolder.removeChild(poitem);
    calculatePO();
}

function calculatePO()
{
    var pids=document.getElementById('pids');
    var pidtext='';
    var lines=getElementsByClass('polinetotal');
    var line=new Array();
    var subtotalHolder=document.getElementById('subtotal');
    var taxHolder=document.getElementById('tax');
    var totalHolder=document.getElementById('total');
    var shippingHolder=document.getElementById('shipping');
    var subtotal=0;
    var total=0;
    var tax=0;
    var currentitem=0;
    var curid=0;
    if (shippingHolder.value!='')
    {
        var shipping=Number(shippingHolder.value);
    } else {
        var shipping=0;
    }
    for(i=0;i<lines.length;i++) {
        line=lines[i];
        pidtext=line['id'].split("_");
        curid=pidtext[1];
        currentitem=Number(document.getElementById('linetotal_'+curid).value);
        if (document.getElementById('taxable_'+curid)=='1')
        {
            tax=tax+currentitem*taxRate;
            tax=Number(tax);
            tax=tax.toFixed(2);
        }
        subtotal=subtotal+currentitem;
    }
    total=subtotal+tax+shipping;
    total=Number(total);
    total=total.toFixed(2);
    subtotal=Number(subtotal);
    subtotal=subtotal.toFixed(2);
    subtotalHolder.value=subtotal;
    taxHolder.value=tax;
    totalHolder.value=total;
}

function getElementsByClass(searchClass,node,tag) {
    var classElements = new Array();
    if ( node == null )
        node = document;
    if ( tag == null )
        tag = '*';
    var els = node.getElementsByTagName(tag);
    var elsLen = els.length;
    var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
    for (i = 0, j = 0; i < elsLen; i++) {
        if ( pattern.test(els[i].className) ) {
            classElements[j] = els[i];
            j++;
        }
    }
    return classElements;
}

function updatePressDraw()
{
    var dbtn=document.getElementById('updatedraw');
    var drawfield=document.getElementById('pressdraw');
    var jobid=document.getElementById('jobid').value;
    if (dbtn.value=='Edit')
    {
        dbtn.value='Save';
        drawfield.readOnly=false;
    } else {
         $.ajax({
          url: "includes/ajax_handlers/jobmonitorPress.php",
          type: "POST",
          data: ({type:'updatedraw',draw:drawfield.value,jobid:jobid}),
          dataType: "html",
          success: function(response){
              response=response.split("|");
              if($.trim(response[0])=='success')
              {
                 drawfield.readOnly=true;
                 dbtn.value='Edit';
              } else {
                 var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
              }
          },
           error:function (xhr, ajaxOptions, thrownError){
               var $dialog = $('<div id="jConfirm"></div>')
                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+xhr.status+'<br />'+thrownError+'</p>')
                    .dialog({
                        autoOpen: true,
                        modal: true,
                        title: 'An error occurred:',
                        buttons:[
                        {
                            text: 'Close',
                            click: function() { 
                                $(this).dialog('close');
                            }
                        }]
                    })
           }
         });
    }
}

function pressMaintenanceGeneric()
{
    var equipmentid=$("#equipmentid").val();
    var componentid=$("#componentid").val();
    window.location="?type=generic&equipmentid="+equipmentid+"&componentid="+componentid;
}

function checkForSystemAlerts()
{
    $.ajax({
      url: "includes/ajax_handlers/checkForSystemAlerts.php?cb_="+Math.random(),
      type: "GET",
      data: ({action:'get'}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              $.ctNotify($.trim(response[1]),{type: 'warning', isSticky: true, delay: 5000},'left-bottom')
              /*
              $('body').showMessage({
                thisMessage:      $.trim(response[1]),
                className:        'fail',
                position:        'top',
                opacity:        90,
                useEsc:            true,
                displayNavigation: true,
                autoClose:         false,
                delayTime:         0,
                closeText:         'close',
                escText:      'Esc Key or'
                });
                */
          }
      }
      })
              
}

function clearSystemAlerts(id)
{
     $.ajax({
      url: "includes/ajax_handlers/checkForSystemAlerts.php",
      type: "GET",
      data: ({action:'clear',id:id}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              $('#systemalert_'+id).hide();
          }
      }
})
} 

function checkForFile(checkfield,msgfield,path)
{
   var file=$('#'+checkfield).val();
   file=file.split("?");
   file=file[0];
   if (path=='core')
   {
        path='/';   
   } else if(path=='script') {
       path='/includes/jscripts/';
   } else if(path=='style') {
       path='/styles/';
   } else if(path=='include') {
       path='/includes/';
   } else if(path=='include') {
       path='/includes/ajax_handlers';
   }
   if(file!='')
   {
        $.ajax({
          url: "includes/ajax_handlers/generalAjax.php",
          type: "POST",
          data: ({action:'checkforfile',filename:file,path:path}),
          dataType: "html",
          success: function(response){
            if($.trim(response)=='true')
            {
                $('#'+msgfield).css({'color':'green'});
                $('#'+msgfield).html('File exists');
            } else {
                $('#'+msgfield).css({'color':'red'});
                $('#'+msgfield).html('File was not found');
            }
          }
              
        });
   }
}

function changePartInventory(type,partid)
{
    $.ajax({
      url: "includes/ajax_handlers/changePartInventory.php",
      type: "POST",
      data: ({type:type,partid:partid}),
      dataType: "json",
      success: function(response){
          if(response['status']=='success')
          {
             $('#invcount_'+partid).html(response['count']);
          } else {
             showMessage('error',response['message']);
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
           showMessage('error',xhr.status+'<br />'+thrownError);
       }
     });
}

function showMessage(type,message)
{
     var $dialog = $('<div id="jConfirm"></div>')
        .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+message+'</p>')
        .dialog({
            autoOpen: true,
            modal: true,
            title: 'An error occurred:',
            buttons:[
            {
                text: 'Close',
                click: function() { 
                    $(this).dialog('close');
                }
            }]
        })
}

function createUsername()
{
    var firstname=document.getElementById('firstname');
    var lastname=document.getElementById('lastname');
    var username=document.getElementById('username');
    if (username.value=='')
    {
        var b='';
        var finitial=firstname.value.substr(0,1);
        var lname=lastname.value;
        b=finitial+lname;
        b=b.toLowerCase();
        username.value=b;    
    }   
}

function addAlert()
{
    var pubid=$('#pub_id').val();
    var userid=$('#userid').val();
    var type=$('#alerttype').val();
    if(type=='press')
    {
        var runid=$('#pressrun_id').val();
    } else {
        var runid=$('#insertrun_id').val();
    }
    
    $.ajax({
      url: "includes/ajax_handlers/userAlerts.php",
      type: "POST",
      data: ({action:'add',pubid:pubid,runid:runid,userid:userid,type:type}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
             $('#alerts').append(response[1]);
             $('#alerttype').val('press');
             $('#pub_id').val('0');
             $('#pressrun_id').val('0');
             $('#insertrun_id').val('0');
          } else {
             showMessage('error',response[1]);
             
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
          showMessage('error',xhr.status+'<br />'+thrownError);
       }
     });
    
}
function deleteAlert(alertid)
{
    $.ajax({
      url: "includes/ajax_handlers/userAlerts.php",
      type: "POST",
      data: ({action:'delete',alertid:alertid}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
             $('#alert'+alertid).remove();
          } else {
             showMessage('error',response[1]);
             
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
           showMessage('error',xhr.status+'<br />'+thrownError);
       }
     });
}


function addPartVendor()
{
    var partid=$('#partid').val();
    var vendorid=$('#partvendor').val();
    var number=$('#partnumber').val();
    var cost=$('#partcost').val();
    if(vendorid==0)
    {
       showMessage('error','You need to select at least a vendor before saving.');
       
    } else {
    $.ajax({
      url: "includes/ajax_handlers/partVendors.php",
      type: "POST",
      data: ({action:'add',partid:partid,vendorid:vendorid,number:number,cost:cost}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
             $('#vendors').append(response[1]);
             $('#partvendor').val('0');
             $('#partnumber').val('');
             $('#partcost').val('');
          } else {
             showMessage('error',response[1]);
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
          showMessage('error',xhr.status+'<br />'+thrownError);
       }
     });
    }
}

function updatePartVendor(partvendorid)
{
    var number=$('#part_number_'+partvendorid).val();
    var cost=$('#part_cost_'+partvendorid).val();
    
    $.ajax({
      url: "includes/ajax_handlers/partVendors.php",
      type: "POST",
      data: ({action:'edit',partvendorid:partvendorid,number:number,cost:cost}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
              var options={};
              $('#update_'+partvendorid).css({'display':'block'});
              $('#update_'+partvendorid).effect( 'pulsate', options, 200, function(){$('#update_'+partvendorid).css({'display':'none'})});
          } else {
            showMessage('error',response[1]);
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
           showMessage('error',xhr.status+'<br />'+thrownError);
       }
     });
    
}
function deletePartVendor(vendorid)
{
    $.ajax({
      url: "includes/ajax_handlers/partVendors.php",
      type: "POST",
      data: ({action:'delete',vendorid:vendorid}),
      dataType: "html",
      success: function(response){
          response=response.split("|");
          if($.trim(response[0])=='success')
          {
             $('#vendor_'+vendorid).remove();
          } else {
             showMessage('error',response[1]);
          }
      },
       error:function (xhr, ajaxOptions, thrownError){
          showMessage('error',xhr.status+'<br />'+thrownError);
       }
     });
}

//this function copies data in the "monday" publication run page flow target boxes to the other days of the week
function copyPlateTimeTargets()
{
    var i=1;
    for(i=2;i<=7;i++)
    {
        $('#schedulelead_'+i).val($('#schedulelead_1').val());
        $('#lastcolor_'+i).val($('#lastcolor_1').val());
        $('#lastpage_'+i).val($('#lastpage_1').val());
        $('#lastplate_'+i).val($('#lastplate_1').val());
        $('#last2plate_'+i).val($('#last2plate_1').val());
        $('#last3plate_'+i).val($('#last3plate_1').val());
        $('#last4plate_'+i).val($('#last4plate_1').val());
        $('#last5plate_'+i).val($('#last5plate_1').val());
        $('#last6plate_'+i).val($('#last6plate_1').val());
        $('#chaseplate_'+i).val($('#chaseplate_1').val());
        $('#chasestart_'+i).val($('#chasestart_1').val());
        $('#runlength_'+i).val($('#runlength_1').val());
    }
}
function addslashes(str) {
    if(str==''){return str;}
    str=str.replace(/\\/g,'\\\\');
    str=str.replace(/\'/g,'\\\'');
    str=str.replace(/\"/g,'\\"');
    str=str.replace(/\0/g,'\\0');
    return str;
}
function stripslashes(str) {
    if(str==''){return str;}
    str=str.replace(/\\'/g,'\'');
    str=str.replace(/\\"/g,'"');
    str=str.replace(/\\0/g,'\0');
    str=str.replace(/\\\\/g,'\\');
    return str;
}

function noPerms(type)
{
   var a = this; 
   var $dialog = $('<div id="jConfirm"></div>')
    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>We apologize, but you do not have permission to '+type+' a job on the calendar.</p>')
    .dialog({
        autoOpen: false,
        title: 'Permission denied.',
        modal: true,
        buttons: {
            Cancel: function() {
                $( this ).dialog( "close" );
                return false;
            }
        }
    });
    $dialog.dialog("open");  
}

function toggleTop()
{
    if($('#toggletop').html()=='[-] Hide header and menu')
    {
       $('#topholder').slideUp('fast');
       $('#toggletop').html('[+] Show header and menu');
       $.cookie("mangoMenu", "hidden", "/");     
    } else {
       $('#topholder').slideDown('fast');
       $('#toggletop').html('[-] Hide header and menu');
       $.cookie("mangoMenu", "displayed", "/");  
    }
}
function clearAlert(name)
{
    $.ajax({
      url: "includes/ajax_handlers/alertHandler.php",
      type: "POST",
      data: ({name:name}),
      dataType: "html",
      success: function(response){
        $('#'+name).remove();    
      }
    })
}

function saveMonitorNotes(source)
{
    $.ajax({
      url: "includes/ajax_handlers/monitorNotes.php?cb_="+Math.random(),
      type: "POST",
      data: ({action:'notes',notes:$('#'+source+"notes").val(),jobid:$('#jobid').val(),mode:source}),
      dataType: "json",
      success: function(response){
          if(response.status=='error')
          {
              $.ctNotify($.trim(response.message),{type: 'warning', isSticky: true, delay: 5000},'left-bottom')
          }
      }
    })        
}


function removeLayout(type)
{
    if(type=='recurring')
    {
        var jobid=$('#recurringid').val();
    } else {
        var jobid=$('#job_id').val();
    }
    
    var $dialog = $('<div id="jConfirm"></div>')
    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>If you remove the layout, all associated pages and plates will also be removed. Are you sure you want to proceed?</p>')
    .dialog({
        autoOpen: true,
        title: 'Remove Layout?',
        modal: true,
        buttons: {
            Cancel: function() {
                $( this ).dialog( "close" );
                return false;
            },
            'Delete': function() {
                $( this ).dialog( "close" );
                $.ajax({
                  url: 'includes/ajax_handlers/fetchMatchingLayouts.php',
                  type: 'post',
                  dataType: 'json',
                  data: ({jobid:jobid,type:type}),
                  success: function(response)
                  {
                      if(response.status=='success')
                      {
                          $('#layouts').val(0);
                          $('#layout_id').val(0);
                          $('#layout_preview').html('');
                          $('#laymessage').html('Layout, plates and pages have been succesfully removed.');
                      } else {
                          $('#laymessage').css('color','red');
                          $('#laymessage').html('There was a problem removing the layout.<br />'+response.message);
                      }     
                  },
                  error:function (xhr, ajaxOptions, thrownError){
                    alert(xhr.status);
                    alert(thrownError);
                  }
                });
            }
            
        },
        open: function() {
            $('.ui-dialog-buttonpane > button:last').focus();
        }
   
    });
    return false;

    
}