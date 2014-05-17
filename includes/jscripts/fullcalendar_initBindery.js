$(document).ready(function() {
    
    var calendarScheduling=calendarScheduleBindery;
    if(calendarScheduling)
    {
        var disableDrag=false;
    } else {
        var disableDrag=true;
    }
    
    
    var currentJobID=0;
        $('#calendar').fullCalendar({
        
            defaultView: 'agendaWeek',
            allDaySlot: false,
            firstHour: calendarStartBindery,
            slotMinutes: calendarBinderySlots,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            theme: true,
            editable: true,
            height: 700,
            disableDragging: disableDrag,
            events: "includes/ajax_handlers/fetchCalendarBindery.php",
            
            eventDrop: function(event,dayDelta,minuteDelta,revertFunc) {
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
                       url: 'includes/ajax_handlers/updateCalendarBindery.php',
                       type: "POST",
                       data: {type:'move',jobid:event.id,dayDelta:dayDelta,minuteDelta:minuteDelta},
                       dataType: 'json',
                       success: function(response) {
                           if(response.status=='success')
                           {
                              //don't do anything to annoy the user with a dialog :)
                           } else {
                              alert("Update failed\n"+response.message);
                              revertFunc(); 
                           }
                       },
                       error: function()
                       {
                           revertFunc();
                       }
                    });
                }
            },
            eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
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
                       url: 'includes/ajax_handlers/updateCalendarBindery.php',
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

            },
            
            loading: function(bool) {
                if (bool) $('#loading').show();
                else $('#loading').hide();
            },
            dblclick: function(event, jsEvent, view) {
                /*
                alert('You doubleclicked! Event: ' + event.title+' and id='+event.id);
                alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
                alert('View: ' + view.name);
                */
                //console.log(event);
                window.open('binderyJobs.php?popup=true&action=edit&id='+event.id,'Bindery Job Editor',"scrollbars=0, resizeable=1, width=940, height=850");
            },
            eventClick: function(event, jsEvent, view) {
                currentJobID=event.id;
                return false;
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
                   if(calendarScheduling)
                   {
                       var a = this; 
                       var $dialog = $('<div id="jConfirm"></div>')
                        .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This will create a new bindery job. Are you sure?</p>')
                        .dialog({
                            autoOpen: false,
                            title: 'Are you sure you want to create a new job??',
                            modal: true,
                            buttons: {
                                Cancel: function() {
                                    $( this ).dialog( "close" );
                                    return false;
                                },
                                'Create Job': function() {
                                    $( this ).dialog( "close" );
                                    $.ajax({
                                       url: 'includes/ajax_handlers/updateCalendarBindery.php',
                                       type: "POST",
                                       data: "type=add&jobid=0&dayDelta=0&minuteDelta=0&date="+date,
                                       dataType: 'json',
                                       success: function(response) {
                                           if(response.status=='success')
                                           {
                                              window.open('binderyJobs.php?popup=true&action=edit&id='+response.jobid,'Bindery Job Editor',"scrollbars=0, resizeable=1, width=940, height=850");
                                              
                                           } else {
                                              alertMessage("Job creation failed<br />"+res[1],'error');
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
                       noPerms('create');
                   }
               } 
            },

            eventRender: function(event, element) {
                if(event.eventtype=='maintenance')
                {
                    element.find('.fc-event-title').append("<br/>" + event.description);
                } else {
                    element.find('.fc-event-time').prepend(event.stitcher+' '+event.tags+"<span id='details"+event.id+"' style='float:right;'><img src='/artwork/icons/magnifying-glass.png' border=0 height=20 /></span><br />"),
                    element.find('.fc-event-title').append("<br/>" + event.description),
                    //element.find('.fc-event-bg').append("<br/>" + event.description),
                    $('#details'+event.id).qtip({
                        content: {
                            text: event.fulldetails,
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
                            tip: 'left center', // Tips work nicely with the styles too!
                            widget: true
                        },
                        show: {
                            event: 'click',
                            solo: true // Only show one tooltip at a time
                        },
                        hide: 'unfocus'
                    }),
                    
                    element.contextMenu('jobCmenu',{
                        'Print Job Ticket': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.open('binderyJobs.php?action=print&jobid='+event.id,'Press Job Ticket',"scrollbars=0, resizeable=1, width=750, height=640");
                            }
                        },
                        'Edit Job': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.open('binderyJobs.php?popup=true&action=edit&id='+event.id,'Bindery Job Editor',"scrollbars=0, resizeable=1, width=900, height=850");
                            }
                        },
                        'Delete Job': {
                           click: function(element){ // element is the jquery obj clicked on when context menu launched
                               if(calendarScheduling)
                               {        
                                   var a = this; 
                                   var $dialog = $('<div id="jConfirm"></div>')
                                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure?</p>')
                                    .dialog({
                                        autoOpen: true,
                                        title: 'Are you sure you want to Delete?',
                                        modal: true,
                                        buttons: {
                                            Cancel: function() {
                                                $( this ).dialog( "close" );
                                                return false;
                                            },
                                            'Delete': function() {
                                                $( this ).dialog( "close" );
                                                $.ajax({
                                               url: 'includes/ajax_handlers/updateCalendarBindery.php',
                                               type: "POST",
                                               data: "type=delete&jobid="+event.id+"&dayDelta=0&minuteDelta=0",
                                               dataType: 'json',
                                               success: function(response) {
                                                   if(response.status=='success')
                                                   {
                                                      $('#calendar').fullCalendar( 'removeEvents',event.id );
                                                   } else {
                                                      alertMessage("Job deletion failed<br />"+response.message,'error');
                                                   }
                                               }
                                               });
                                            }
                                        },
                                        open: function() {
                                            $('.ui-dialog-buttonpane > button:last').focus();
                                        }
                                   
                                    });
                                    a.dialog("open");
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
                   url: 'includes/ajax_handlers/updateCalendarBindery.php',
                   type: "POST",
                   data: "type=drop&jobid="+jobid+"&date="+date,
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
    getUnscheduled(year,month,date);       
}

function getUnscheduled(year,month,date)
{
    $.ajax({
       url: 'includes/ajax_handlers/updateCalendarBindery.php',
       type: "POST",
       data: "type=unscheduled&year="+year+"&month="+month+"&date="+date,
       dataType:'json',
       success: function(response) {
           if(response.status=='success')
           {
               $('.unscheduledHolder').empty();  
               $.each(response.jobs, function (j,job){
                  var newDiv=$('<div id="'+job.id+'">'+job.title+'</div>').addClass('ui-widget ui-draggable unscheduledJob');
                  
                  var eventObject = {
                        title: job.title // use the element's text as the event title
                  };
                  // store the Event Object in the DOM element so we can get to it later
                  $(newDiv).data('eventObject', eventObject);
                  var dHolder=$('#'+job.dateholder);
                  
                  $('#'+job.dateholder).append(newDiv);  
                  //console.log('added new div '+job.id);
                  // make the event draggable using jQuery UI
                  $(newDiv).draggable({
                      zIndex: 999,
                      revert: true,      // will cause the event to go back to its
                      revertDuration: 0  //  original position after the drag
                  });
                    
              });
           } else {
              alertMessage("Unable to find any bindery jobs<br />"+res,'error');
           }
       }
    });
    
}