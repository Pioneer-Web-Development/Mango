//this is my virtual time setting script
var timeopened = false;
var timesource=null;
var timesetDiv=null;
var timepos=[];
       
function timeInit(source,fCal,sid,ares)
{
     var shour='';
     var sminute='';
     timeopened = !timeopened;
     timesource=document.getElementById(source);
     timesetDiv=document.getElementById('timePopup');
     //lets see if there is an existing value
     var exist=timesource.value
     if (exist!='')
     {
        var pieces=exist.split(":");
        shour=pieces[0];
        sminute=pieces[1];
     }
     
     var pX=findPosX(timesource);
     var pY=findPosY(timesource);
     timesetDiv.style.top=pY+25+'px';
     timesetDiv.style.left=pX+'px';
     var timeHtml=createTimePopup(shour,sminute,fCal,sid,ares);
     timesetDiv.innerHTML=timeHtml;
     timesetDiv.style.display='block';
     timesource = document.getElementById(source);
     timesource.focus();

     if(document.attachEvent)
       timesource.attachEvent("onblur", timeFocus);
     
}

function timeFocus()
{
 if(timeopened)
 {
   timesource.focus();
 }
}

function createTimePopup(shour,sminute,fCal,sid,ares)
{
    var val='';
    var html="<div style='background-color:#FFFFFF;border:1px solid black;padding:5px;'>";
    html+="<span style='font-size:14px;'>Select time</span><br>";
    html+="<select id='timePopup_hour' name='timePopup_hour' style='font-size:28px;'>";
    for (var i=0;i<24;i++)
    {
        if (i<10)
        {
            val='0'+i;
        } else {
            val=i;
        }
        if (val==shour)
        {
            html+="<option value='"+val+"' selected>"+val+"</option>";
        } else {
            html+="<option value='"+val+"'>"+val+"</option>";
        }
    }
    html+="</select>";
    html+="<span style='font-size:28px;'>:</span>";
    html+="<select id='timePopup_minute' name='timePopup_minute' style='font-size:28px;'>";
    for (var j=0;j<60;j++)
    {
        if (j<10)
        {
            val='0'+j;
        } else {
            val=j;
        }
        if (val==sminute)
        {
            html+="<option value='"+val+"' selected>"+val+"</option>";
        } else {
            html+="<option value='"+val+"'>"+val+"</option>";
        }
    }
    html+="</select>";
    html+="<br><br>";
    html+="<input type='button' id='popupTime_submit' value='Set Time' onclick='popupSetTime(\""+fCal+"\",\""+sid+"\",\""+ares+"\");'>";
    return html;
}

function popupSetTime(fCal,sid,ares)
{
    var tHour=document.getElementById('timePopup_hour').value;
    var tMinute=document.getElementById('timePopup_minute').value;
    timesource.value=tHour+":"+tMinute;
    timesetDiv.style.display='none';
    timeopened=false;
    fCal=fCal+"('"+sid+"','"+ares+"');";
    eval(fCal);
    //uncomment to handle the database timesetting in this function
    //var timevalue=tHour+":"+tMinute;
    //var url='includes/savePressData.php?value='+timevalue+'&source='+timesource.id;
    //var tajax=[];
    //var index = tajax.length;
    //tajax[index] = new sack();
    //tajax[index].requestFile = url;    // Specifying which file to get
    //tajax[index].runAJAX();  
}