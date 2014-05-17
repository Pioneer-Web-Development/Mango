$(document).ready(function() {
    var currentJobID=0;
        $('#calendar').fullCalendar({
        
            defaultView: 'agendaWeek',
            allDaySlot: false,
            firstHour: calendarStartPackaging,
            slotMinutes: calendarPackagingSlots,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            theme: true,
            editable: true,
            height: 700,
            eventMouseover: function( event, jsEvent, view ) { 
                $(this).css('border-color','red');
            },
            eventMouseout: function( event, jsEvent, view ) { 
                $(this).css('border-color',event.borderColor);
            },
            events: "includes/ajax_handlers/fetchCalendarPackages.php",
            
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
                       url: 'includes/ajax_handlers/updateCalendarPackages.php',
                       type: "POST",
                       data: {type:'move',jobid:event.id,dayDelta:dayDelta,minuteDelta:minuteDelta},
                       dataType: 'json',
                       error: function()
                       {
                           //revertFunc();
                       },
                       success: function(res) {
                           if(res.status=='success')
                           {
                               
                           } else if (res.status=='error')
                           {
                              alert("Update failed\n"+res.error_message);
                              revertFunc();  
                           } 
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
                       url: 'includes/ajax_handlers/updateCalendarPackages.php',
                       type: "POST",
                       data: {type:'resize',jobid:event.id,dayDelta:dayDelta,minuteDelta:minuteDelta},
                       dataType: 'json',
                       error: function()
                       {
                           revertFunc();
                       },
                       success: function(res) {
                           if(res.status=='success')
                           {
                              
                           } else if (res.status=='error')
                           {
                              alert("Update failed\n"+res.error_message);
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
                window.open('inserterPackages.php?popup=true&action=edit&packageid='+event.id,'Inserter Package Editor',"scrollbars=0, resizeable=1, width=800, height=850");
            },
            eventClick: function(event, jsEvent, view) {
                currentJobID=event.id;
                return false;
            },
            
            dayClick: function(date, allDay, jsEvent, view) {

               if(view.name=='month')
               {
                   $('#calendar').fullCalendar(
                       'changeView','agendaDay' 
                   );
                    $('#calendar').fullCalendar(
                       'gotoDate', date 
                   );
               } 
            },

            eventRender: function(event, element) {
               if(event.eventtype=='maintenance')
                {
                    element.find('.fc-event-title').append("<br/>" + event.description);
                } else {
                    element.find('.fc-event-time').prepend(event.inserter+' '+event.tags+"<span id='details"+event.id+"' style='float:right;'><img src='/artwork/icons/magnifying-glass.png' border=0 height=20 /></span><br />"),
                    element.find('.fc-event-title').append("<br/>" + event.description),
                    
                   $('#details'+event.id).qtip({
                        content: {
                            title: {
                              text: 'Job Details',
                              button: '<span onclick="return false;">Close</span>'
                            },
                            text: "<img src='artwork/icons/ajax-loader.gif' />", // The text to use whilst the AJAX request is loading
                             ajax: {
                                url: 'includes/ajax_handlers/generalAjax.php',
                                data: { action: 'calendarPackageTooltip', id: event.id},
                                type: 'POST',
                                dataType: 'json',
                                once: false,
                                success: function(response) {
                                    if(response.status=='success')
                                    {
                                        this.set('content.text', response.qtip);
                                    }
                                }
                             }
                        }, 
                        position: {
                                target: $('#details'+event.id),
                                my: 'right center',
                                at: 'left center'
                            },
                        style: {
                            classes: 'ui-tooltip-shadow', // Optional shadow...
                            tip: 'right center', // Tips work nicely with the styles too!
                            widget: true
                        },
                        show: {
                            event: 'click',
                            solo: true // Only show one tooltip at a time
                        },
                        hide: 'unfocus'
                    }),
                    
                    element.contextMenu('jobCmenu',{
                        'Edit Package': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.open('inserterPackages.php?popup=true&action=edit&packageid='+event.id,'Inserter Package Editor',"scrollbars=0, resizeable=1, width=800, height=850");
                                }
                        },
                        'View Inserts': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.open('inserterPackages.php?popup=true&action=inserts&packageid='+event.id,'Package Inserts Editor',"scrollbars=0, resizeable=1, width=800, height=850");
                                }
                        },
                        'Print Job Ticket': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.open('inserterPackages.php?action=jobticket&packageid='+event.id,'Job Ticket',"scrollbars=0, resizeable=1, width=800, height=950");
                                }
                        },
                        'Enter Job Data': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.open('inserterPackages.php?popup=true&action=data&packageid='+event.id,'Package Data',"scrollbars=0, resizeable=1, width=800, height=850");
                                }
                        },
                        'Package Settings': {
                            click: function(element){ // element is the jquery obj clicked on when context menu launched
                                window.open('inserterPackages.php?popup=true&action=settings&packageid='+event.id,'Package Settings',"scrollbars=0, resizeable=1, width=800, height=850");
                                }
                        },
                        'Delete Package': {
                           click: function(element){ // element is the jquery obj clicked on when context menu launched
                               var $dialog = $('<div id="jConfirm"></div>')
                                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure?</p>')
                                .dialog({
                                    autoOpen: true,
                                    title: 'Are you sure you want to delete?',
                                    modal: true,
                                    buttons: {
                                        Cancel: function() {
                                            $( this ).dialog( "close" );
                                            return false;
                                        },
                                        'Delete': function() {
                                            $( this ).dialog( "close" );
                                            $.ajax({
                                           url: 'includes/ajax_handlers/updateCalendarPackages.php',
                                           type: "POST",
                                           data: "type=delete&jobid="+event.id+"&dayDelta=0&minuteDelta=0",
                                           success: function(res) {
                                               if(res=='')
                                               {
                                                  $('#calendar').fullCalendar( 'removeEvents',event.id );
                                               } else {
                                                  alertMessage("Package deletion failed<br />"+res,'error');
                                               }
                                           }
                                           });
                                        }
                                    },
                                    open: function() {
                                        $('.ui-dialog-buttonpane > button:last').focus();
                                    }
                               
                                });
                                    
                            }
                        }
                    });
                    
                }
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
}