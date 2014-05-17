
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




function getElementsByStyleClass (className) {
  var all = document.all ? document.all :
    document.getElementsByTagName('*');
  var elements = new Array();
  for (var e = 0; e < all.length; e++)
    if (all[e].className == className)
      elements[elements.length] = all[e];
  return elements;
}

function calcTonnage()
{
    //ok, we'll need to figure out which elements we want to count
    var divHolder=getElementsByStyleClass("ton");
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

function addItem()
{
    
    var lastID=document.getElementById('lastID');
    var newI=lastID.value;
    var newHTML="<div class='label'>Item # "+newI+"</div><div class='input'>";
    newHTML=newHTML+"<select name='paper_"+newI+"' id='paper_"+newI+"'>";
    newHTML=newHTML+"<option value='0'>Type</option>";
    newHTML=newHTML+"</select>&nbsp;&nbsp;";
    newHTML=newHTML+"<select name='size_"+newI+"' id='size_"+newI+"'>";
    newHTML=newHTML+"<option value='0'>Size</option>";
    newHTML=newHTML+"</select>";
    newHTML=newHTML+" Tons: <input type=text class='ton' name='tonnage_"+newI+"' id='tonnage_"+newI+"' size=5 value='' onChange='calcTonnage();' onKeyPress='return isNumberKey(event);'>MT";
    newHTML=newHTML+" <input type=button value='Delete' onClick='deleteItem("+newI+");' />";
    newHTML=newHTML+"</div>\n";
    newHTML=newHTML+"<div class='clear'></div>";
    
    var divHolder=document.getElementById("itemHolder");
    var newDiv=document.createElement('div');
    newDiv.id="item_"+newI;
    newDiv.innerHTML=newHTML;
    divHolder.appendChild(newDiv);
    newI++;
    lastID.value=newI;
}

function deleteItem(itemID)
{
    var divHolder=document.getElementById("itemHolder");
    var item=document.getElementById('item_'+itemID);   
    divHolder.removeChild(item);
    calcTonnage();
}

function addRoll()
{
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
    lastIDe.value=lastID+1;   
}


var ajaxObjects = new Array();
function checkRollTag(eid)
{
    var rollTag=document.getElementById('newroll_'+eid).value;
    if (rollTag!="")
    {
    var ajaxIndex = ajaxObjects.length;
    ajaxObjects[ajaxIndex] = new sack();
    ajaxObjects[ajaxIndex].requestFile = 'includes/checkRollTag.php?rolltag='+rollTag;
    ajaxObjects[ajaxIndex].onCompletion = function(){ displayRollTagInfo(eid,ajaxIndex); };    // Specify function that will be executed after file has been found
    ajaxObjects[ajaxIndex].runAJAX();        // Execute AJAX function
    }
}   

function displayRollTagInfo(eid,ajaxIndex)
{
    var rollID=ajaxObjects[ajaxIndex].response;
    var msg=document.getElementById('msg_'+eid);
    if (rollID==0)
    {
        alert ("This roll tag does not exist");
        document.getElementById('newroll_'+eid).value="";
        docuemnt.getElementById('newroll_'+eid).focus();
    } else {
        document.getElementById('rollid_'+eid).value=rollID;
    }
}