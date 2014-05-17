$(document).ready(function() {
    if(calendarSchedulePress)
    {
        var disableDrag=false;
    } else {
        var disableDrag=true;
    }
    var currentJobID=0;
        $('#calendar').fullCalendar({
        
            defaultView: 'agendaWeek',
            allDaySlot: false,
            firstHour: calendarStartPress,
            slotMinutes: calendarPressSlots,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            overlap: false,
            theme: true,
            editable: true,
            height: 700,
            disableDragging: disableDrag,
            dataType: 'json', 
            events: "includes/ajax_handlers/fetchCalendarPress.php",
            eventDrop: function(event,dayDelta,minuteDelta,revertFunc) {
                if(calendarSchedulePress && event.mypub)
                { 
                    if(event.eventtype=='maintenance')
                    {
                        $.ajax({
                           url: 'includes/ajax_handlers/maintenanceScheduledTicketHandler.php',
                           type: "POST",
                           data: {type:'move',scheduleid:event.id,dayDelta:dayDelta,minuteDelta:minuteDelta},
                           dataType: 'json',
                           error: function()
                           {
                               revertFunc();
                           },
                           success: function(response) {
                               if(response.status=='success')
                               {
                                  //don't do anything to annoy the user with a dialog or somethign :) 
                               } else {
                                  alert("Update failed\n"+response.message);
                                  revertFunc(); 
                               }
                           }
                        });    
                    } else {
                        $.ajax({
                           url: 'includes/ajax_handlers/updateCalendarPress.php',
                           type: "POST",
                           data: {type:'move',jobid:event.id,dayDelta:dayDelta,minuteDelta:minuteDelta},
                           dataType: 'json',
                           error: function()
                           {
                               revertFunc();
                           },
                           success: function(response) {
                               if(response.status=='success')
                               {
                                  //don't do anything to annoy the user with a dialog or somethign :) 
                               } else {
                                  alert("Update failed\n"+response.message);
                                  revertFunc(); 
                               }
                           }
                        });
                    }
                }
            },
            eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
                if(calendarSchedulePress && event.mypub)
                { 
                    if(event.eventtype=='maintenance')
                    {
                        $.ajax({
                           url: 'includes/ajax_handlers/maintenanceScheduledTicketHandler.php',
                           type: "POST",
                           data: {type:'resize',scheduleid:event.id,dayDelta:dayDelta,minuteDelta:minuteDelta},
                           dataType: 'json',
                           error: function()
                           {
                               revertFunc();
                           },
                           success: function(response) {
                               if(response.status=='success')
                               {
                                  //don't do anything to annoy the user with a dialog or somethign :) 
                               } else {
                                  alert("Resize failed\n"+response.message);
                                  revertFunc(); 
                               }
                           }
                        });    
                    } else {
                        $.ajax({
                           url: 'includes/ajax_handlers/updateCalendarPress.php',
                           type: "POST",
                           data: {type:'resize',jobid:event.id,dayDelta:dayDelta,minuteDelta:minuteDelta},
                           dataType: 'json',
                           error: function()
                           {
                               revertFunc();
                           },
                           success: function(response) {
                               if(response.status=='success')
                               {
                                  //don't do anything to annoy the user with a dialog or somethign :) 
                               } else {
                                  alert("Update failed\n"+response.message);
                                  revertFunc(); 
                               }
                           }
                        });
                    }
                }
            },
            
            loading: function(bool) {
                if (bool) $('#loading').show();
                else $('#loading').hide();
            },
            dblclick: function(event, jsEvent) {
                /*
                alert('You doubleclicked! Event: ' + event.title+' and id='+event.id);
                alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
                alert('View: ' + view.name);
                */
                if(calendarSchedulePress && event.mypub) {
                    if(event.eventtype=='maintenance')
                    {
                        window.open('maintenanceSchedulePopup.php?popup=true&ticketid='+event.id,'Scheduled Maintenance',"scrollbars=0, resizeable=1, width=740, height=750");
                    } else {
                        window.open('jobPressPopup.php?id='+event.id,'Press Job Editor',"scrollbars=0, resizeable=1, width=940, height=850");
                    }
                } else {
                    if(event.eventtype=='maintenance')
                    {
                        window.open('maintenanceSchedulePopup.php?popup=true&ne=true&ticketid='+event.id,'Scheduled Maintenance',"scrollbars=0, resizeable=1, width=740, height=750");
                    } else {
                        window.open('jobPressPopup.php?id='+event.id+'&ne=true','Press Job Editor',"scrollbars=0, resizeable=1, width=940, height=850");
                    }
                }
            },
            eventClick: function(event, jsEvent, view) {
                currentJobID=event.id;
                return false;
                
                //sample of coloring an event after click
                //event.backgroundColor = 'yellow';
                //$('#calendar').fullCalendar('rerenderEvents');
                
            },
            eventMouseover: function( event, jsEvent, view ) { 
                $(this).css('border-color','red');
                
            },
            eventMouseout: function( event, jsEvent, view ) { 
                $(this).css('border-color',event.borderColor);
            },
            
            dayClick: function(date, allDay, jsEvent, view) {

                /*
                if (allDay) {
                    alert('Clicked on the entire day: ' + date);
                }else{
                    alert('Clicked on the slot: ' + date);
                }

                alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);

                alert('Current view: ' + view.name);
                */
                // change the day's background color just for fun
               if(view.name=='month')
               {
                   $('#calendar').fullCalendar(
                       'changeView','agendaDay' 
                   );
                    $('#calendar').fullCalendar(
                       'gotoDate', date 
                   );
                    
               } else {
                   if(calendarSchedulePress)
                   {
                       var a = this; 
                       var $dialog = $('<div id="jConfirm"></div>')
                        .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This will create a new press job. Are you sure?</p>')
                        .dialog({
                            autoOpen: false,
                            title: 'Are you sure you want to create a new job??',
                            modal: true,
                            draggable: false,
                            buttons: {
                                Cancel: function() {
                                    $( this ).dialog( "close" );
                                    return false;
                                },
                                'Create Job': function() {
                                    $( this ).dialog( "close" );
                                    $.ajax({
                                       url: 'includes/ajax_handlers/updateCalendarPress.php',
                                       type: "POST",
                                       data: {type:'add',jobid:0,dayDelta:0,minuteDelta:0,date:date},
                                       dataType: 'json',
                                       success: function(response) {
                                           if(response.status=='success')
                                           {
                                              window.open('jobPressPopup.php?id='+response.jobid,'Press Job Editor',"scrollbars=1, resizeable=1, width=940, height=900");
                                              
                                           } else {
                                              alertMessage("Job creation failed<br />"+response.message,'error');
                                           }
                                       }
                                    });
                                }
                            },
                            open: function() {
                                $('.ui-dialog-buttonpane > button:last').focus();
                            }
                       
                        });
                        $dialog.dialog("open");
                   } else {
                       noPerms('add');
                   }
               } 
                
            },

            eventRender: function(event, element) {
                if(event.eventtype=='maintenance')
                {
                    element.find('.fc-event-title').append("<br/>" + event.description);
                } else {
                    element.find('.fc-event-time').prepend('F-'+event.folder+' '+event.tags+"<span id='details"+event.id+"' style='float:right;'><img src='/artwork/icons/magnifying-glass.png' border=0 height=20 /></span><br />"),
                    element.find('.fc-event-title').append("<br/>" + event.description),
                    
                    $('#details'+event.id).qtip({
                        content: {
                            text: event.tooltip,
                            title: {
                              text: 'Job Details',
                              button: '<span onclick="return false;">Close</span>'
                            }
                        }, 
                        position: {
                                target: $('#details'+event.id),
                                my: 'left center',
                                at: 'right center'
                            },
                        style: {
                            classes: 'ui-tooltip-shadow', // Optional shadow...
                            tip: 'left center' // Tips work nicely with the styles too!
                        },
                        show: {
                            event: 'click',
                            solo: true // Only show one tooltip at a time
                        },
                        hide: 'unfocus'
                    }),
                    
                    element.contextMenu('jobCmenu_'+event.id,{
                        'Print Job Ticket': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.open('jobPressTicket.php?action=print&jobid='+event.id,'Press Job Ticket',"scrollbars=0, resizeable=1, width=750, height=640");
                                         
                            }
                        },
                        'Edit Job': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                if(calendarSchedulePress && event.mypub)
                                {
                                    window.open('jobPressPopup.php?id='+event.id,'Press Job Editor',"scrollbars=0, resizeable=1, width=900, height=850");
                                } else {
                                    window.open('jobPressPopup.php?id='+event.id+'&ne=true','Press Job Editor',"scrollbars=0, resizeable=1, width=900, height=850");
                                }
                            }
                        },
                        'Print Stacker Ticket': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.open('printouts/pressStackerTicket.php?jobid='+event.id,'Press Stacker Ticket',"scrollbars=0, resizeable=1, width=750, height=640");
                                         
                            }
                        },
                        'Edit Recurrence': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                if(calendarSchedulePress && event.mypub)
                                {
                                   window.open('jobRecurring.php?id='+event.id,'Recurring Job Editor',"scrollbars=0, resizeable=1, width=750, height=640");
                                } else {
                                   noPerms('edit the recurrence of');
                                }
                            }
                        },
                        'View in Press Monitor': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.location='jobMonitor_press.php?jobid='+event.id;
                            }
                        },
                        'View in Pagination Monitor': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.location='jobMonitor_pagination.php?jobid='+event.id;
                            }
                        },
                        'View in Plateroom Monitor': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.location='jobMonitor_plate.php?jobid='+event.id;
                            }
                        },
                        'Un-schedule Job': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                if(calendarSchedulePress && event.mypub)
                                {   
                                    var a = this;
                                    var d= new Date();
                                    var month=d.getMonth()+1;
                                    var date=d.getDate();
                                    var year=d.getFullYear();
                                    var today=month+"/"+date+"/"+year;
                                    var diagHtml="<p>This job will be removed from the schedule if you click ok. Please enter a new requested print date:<br>";
                                    diagHtml+="<input type='text' id='rdate_"+event.id+"' value='' placeholder='"+today+"'/></p>";
                                    
                                    var $dialog = $('<div id="jConfirm"></div>')
                                    .html(diagHtml)
                                    .dialog({
                                        autoOpen: true,
                                        title: 'Unschedule Job',
                                        draggable: false,
                                        modal: true,
                                        buttons: {
                                            Cancel: function() {
                                                $( this ).dialog( "destroy" );
                                                return false;
                                            },
                                            'Unschedule': function() {
                                                var requestdate=$('#rdate_'+event.id).val();
                                                $( this ).dialog( "destroy" );
                                                $.ajax({
                                                   url: 'includes/ajax_handlers/updateCalendarPress.php',
                                                   type: "POST",
                                                   data: {type:'unschedule',jobid:event.id,dayDelta:0,minuteDelta:0,rdate:requestdate},
                                                   dataType: 'json',
                                                   success: function(response) {
                                                       if(response.status=='success')
                                                       {
                                                            $('#calendar').fullCalendar( 'removeEvents',event.id );
                                                            var view = $('#calendar').fullCalendar('getView');
                                                            var d= new Date();
                                                            var curDate = Date.parse(view.start);
                                                            d.setTime(curDate);
                                                            var curMonth = d.getMonth()+1;
                                                            var curDay   = d.getDate();
                                                            var curYear  = d.getFullYear();
                                                            getUnscheduled(curYear,curMonth,curDay);
                                                       } else {
                                                          alertMessage("Job unscheduling failed<br />"+response.status+'<br>'+response.message,'error');
                                                       }
                                                   }
                                               });
                                            }
                                        },
                                        open: function() {
                                            $('#rdate_'+event.id).datepicker({ dateFormat: 'mm/dd/yy' });
                                            $('.ui-dialog-buttonpane > button:last').focus();
                                            $('#rdate_'+event.id).click(function(){$('#rdate_'+event.id).datepicker( "show" );})
                                        }
                                   
                                    });
                                    //a.dialog("open");
                               } else {
                                   noPerms('edit');
                               }  
                            }
                        },
                        'Delete Job': {
                           click: function(element){ // element is the jquery obj clicked on when context menu launched
                                if(calendarSchedulePress && event.mypub)
                                {   
                                    var a = this; 
                                    var $dialog = $('<div id="jConfirm"></div>')
                                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure?</p>')
                                    .dialog({
                                        autoOpen: true,
                                        title: 'Are you sure you want to Delete?',
                                        draggable: false,
                                        modal: true,
                                        buttons: {
                                            Cancel: function() {
                                                $( this ).dialog( "close" );
                                                return false;
                                            },
                                            'Delete': function() {
                                              $( this ).dialog( "close" );
                                                $.ajax({
                                                   url: 'includes/ajax_handlers/updateCalendarPress.php',
                                                   type: "POST",
                                                   data: {type:'delete',jobid:event.id,dayDelta:0,minuteDelta:0},
                                                   dataType: 'json',
                                                   success: function(response) {
                                                       if(response.status=='success')
                                                       {
                                                            $('#calendar').fullCalendar( 'removeEvents',event.id );
                                                            
                                                       } else {
                                                            alertMessage("Job deletion failed<br />"+response.status+'<br>'+response.sql,'error');
                                                       }
                                                   }
                                               });
                                            }
                                        },
                                        open: function() {
                                            $('.ui-dialog-buttonpane > button:last').focus();
                                        }
                                   
                                    });
                                    //a.dialog("open");
                               } else {
                                   noPerms('delete');
                                }    
                            }
                        }
                    });
                } 
            },
            droppable: true,
            drop: function(date, allDay) {
                //we'll update the record via ajax, then refresh the calendar...
                var jobid=$(this).attr('id');
                $.ajax({
                   url: 'includes/ajax_handlers/updateCalendarPress.php',
                   type: "POST",
                   data: {type:'drop',jobid:jobid,date:date,},
                   dataType: 'json',
                   success: function(response) {
                       if(response.status=='success')
                       {
                          $('#calendar').fullCalendar('refetchEvents');
                       } else {
                          alertMessage("Job sheduling failed<br />"+response.message,'error');
                       }
                   }
                });
                $(this).remove();
            },
            viewDisplay: function(view) {
                var d= new Date();
                var curDate = Date.parse(view.start);
                d.setTime(curDate);
                var curMonth = d.getMonth()+1;
                var curDay   = d.getDate();
                var curYear  = d.getFullYear();
                getUnscheduled(curYear,curMonth,curDay)
                //console.log('The new title of the view is ' + view.title+' and the start date is '+curMonth+'/'+curDay+'/'+curYear);
            }
                        
        });
        
    });

    
function refreshCalendar()
{
   $('#calendar').fullCalendar('refetchEvents');
}
function jumpDate(cdate)
{
    var temp=cdate.split("-");
    var year=temp[0];
    var month=temp[1];
    var date=temp[2];
    month=parseInt(month)-1;
    $('#calendar').fullCalendar('gotoDate', year, month, date);
    //getUnscheduled(year,month,date);  // this is now integrated into the viewDisplay event in full calendar   
}


function getUnscheduled(year,month,date)
{
    //console.log('getting unscheduled events with date='+month+'/'+date+'/'+year);
    $.ajax({
       url: 'includes/ajax_handlers/updateCalendarPress.php',
       type: "POST",
       data: "type=unscheduled&year="+year+"&month="+month+"&date="+date,
       dataType:'json',
       success: function(response) {
           if(response.status=='success')
           {
               $('.unscheduledHolder').empty();  
               $.each(response.jobs, function (j,job){
                  var newDiv=$('<div id="'+job.id+'"><span id="pop'+job.id+'" style="float:right;"><img src="/artwork/icons/magnifying-glass.png" border=0 height=20 /></span>'+job.title+'</div>').addClass('ui-widget ui-draggable unscheduledJob');
                  var eventObject = {
                        title: job.title // use the element's text as the event title
                  };
                  // store the Event Object in the DOM element so we can get to it later
                  $(newDiv).data('eventObject', eventObject);
                  var dHolder=$('#'+job.dateholder);
                  
                  $('#'+job.dateholder).append(newDiv);  
                  $('#pop'+job.id).qtip({
                        content: {
                            text: job.tooltip,
                            title: {
                              text: 'Job Details',
                              button: '<span onclick="return false;">Close</span>'
                            }
                        }, 
                        position: {
                                target: $('#pop'+job.id),
                                my: 'left center',
                                at: 'right center'
                            },
                        style: {
                            classes: 'ui-tooltip-shadow', // Optional shadow...
                            tip: 'left center' // Tips work nicely with the styles too!
                        },
                        show: {
                            event: 'click',
                            solo: true // Only show one tooltip at a time
                        },
                        hide: 'unfocus'
                    });
                  
                  //console.log('added new div '+job.id);
                  // make the event draggable using jQuery UI
                  $(newDiv).draggable({
                      zIndex: 999,
                      revert: true,      // will cause the event to go back to its
                      revertDuration: 0  //  original position after the drag
                  });
                    
              });
              
              
           } else {
              alertMessage("Job sheduling failed<br />"+res,'error');
           }
       }
    });
    
}