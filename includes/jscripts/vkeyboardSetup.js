   var opened = false, vkb = null, text = null, insertionS = 0, insertionE = 0;
   var pos=[];
   var userstr = navigator.userAgent.toLowerCase();
   var isgecko = (userstr.indexOf('gecko') != -1) && (userstr.indexOf('applewebkit') == -1);
   var numares;         
   function keyb_callback(ch)
   {
     var val = text.value;
     switch(ch)
     {
       case "BackSpace":
         var min = (val.charCodeAt(val.length - 1) == 10) ? 2 : 1;
         text.value = val.substr(0, val.length - min);
         break;

       case "Enter":
         text.value += "\n";
         break;

       case "C":
         text.value = "0";
         break;
  
       default:
         text.value += ch;
     }
   }
   
   function numpad_init(sourceid,ares)
   {
     var paddiv=document.getElementById(sourceid);
     var pX=findPosX(paddiv);
     var pY=findPosY(paddiv);
     var numpadDiv=document.getElementById('numpad');
     numpadDiv.style.top=pY+25+'px';
     numpadDiv.style.left=pX+'px';
    
     numares=ares;
     numpad_change(sourceid);   
   
   }
   
   function numpad_change(sourceid)
   {
     opened = !opened;
     if(opened && !vkb)
     {
       vkb = new VNumpad("numpad",     // container's id
                         pad_callback, // reference to the callback function
                         "",           // font name ("" == system default)
                         "24px",       // font size in px
                         "#000",       // font color
                         "#FFF",       // keyboard base background color
                         "#FFF",       // keys' background color
                         "#777",       // border color
                         true,         // show key flash on click? (false by default)
                         "#666",    // font color for flash event
                         "#CCC",    // key background color for flash event
                         "#000",    // key border color for flash event
                         false,        // embed VNumpad into the page?
                         true);        // use 1-pixel gap between the keys?

     }
     else {
         if (!opened)
         {
            //here is where we save the value in the field
            var sourcediv=document.getElementById(sourceid);
            var bid=sourceid.split("_");
            var bid=bid[1];
            var sourcevalue=sourcediv.value
            var jobid=document.getElementById('jobid').value;
            $.ajax({
              url: "includes/ajax_handlers/jobmonitorPress.php",
              type: "POST",
              data: ({type:'benchmark',jobid:jobid,value:sourcevalue,source:bid}),
              dataType: "html",
              success: function(response){
                  response=response.split("|");
                  if($.trim(response[0])=='success')
                  {
                    //all good!
                  } else {
                      //error
                      if($.trim(response[0])=='error')
                      {
                        alert(response[1]);    
                      } else {
                          alert(response[0]);
                      }
                      
                  }
              },
               error:function (xhr, ajaxOptions, thrownError){
               }
            })
    
         }
         vkb.Show(opened);
     }

     text = document.getElementById(sourceid);
     text.focus();

     //if(document.attachEvent)
     //  text.attachEvent("onblur", backFocus);
   }

   
   function receiveAres(index)
   {
        if (numares!='')
        {
            var eobj=document.getElementById(numares);
            eobj.innerHTML=pajax[index].response
        }
   }
   
   function backFocus()
   {
     if(opened)
     {
       setRange(text, insertionS, insertionE);
       text.focus();
     }
   }

   // Advanced callback function:
   //
   function pad_callback(ch)
   {
     var val = text.value;
     
     switch(ch)
     {
       case "BackSpace":
         if(val.length)
         {
           var span = null;

           if(document.selection)
             span = document.selection.createRange().duplicate();

           if(span && span.text.length > 0)
           {
             span.text = "";
             getCaretPositions(text);
           }
           else
             deleteAtCaret(text);
         }

         break;
       case "Enter":
        numpad_change(text.id);
        
       break;
       
       case "C":
        text.value="0";
        numpad_change(text.id);
        
       break;
       default:
         insertAtCaret(text,ch);
     }
   }

   // This function retrieves the position (in chars, relative to
   // the start of the text) of the edit cursor (caret), or, if
   // text is selected in the TEXTAREA, the start and end positions
   // of the selection.
   //
   function getCaretPositions(ctrl)
   {
     var CaretPosS = -1, CaretPosE = 0;

     // Mozilla way:
     if(ctrl.selectionStart || (ctrl.selectionStart == '0'))
     {
       CaretPosS = ctrl.selectionStart;
       CaretPosE = ctrl.selectionEnd;

       insertionS = CaretPosS == -1 ? CaretPosE : CaretPosS;
       insertionE = CaretPosE;
     }
     // IE way:
     else if(document.selection && ctrl.createTextRange)
     {
       var start = end = 0;
       try
       {
         start = Math.abs(document.selection.createRange().moveStart("character", -10000000)); // start

         if (start > 0)
         {
           try
           {
             var endReal = Math.abs(ctrl.createTextRange().moveEnd("character", -10000000));

             var r = document.body.createTextRange();
             r.moveToElementText(ctrl);
             var sTest = Math.abs(r.moveStart("character", -10000000));
             var eTest = Math.abs(r.moveEnd("character", -10000000));

             if ((ctrl.tagName.toLowerCase() != 'input') && (eTest - endReal == sTest))
               start -= sTest;
           }
           catch(err) {}
         }
       }
       catch (e) {}

       try
       {
         end = Math.abs(document.selection.createRange().moveEnd("character", -10000000)); // end
         if(end > 0)
         {
           try
           {
             var endReal = Math.abs(ctrl.createTextRange().moveEnd("character", -10000000));

             var r = document.body.createTextRange();
             r.moveToElementText(ctrl);
             var sTest = Math.abs(r.moveStart("character", -10000000));
             var eTest = Math.abs(r.moveEnd("character", -10000000));

             if ((ctrl.tagName.toLowerCase() != 'input') && (eTest - endReal == sTest))
              end -= sTest;
           }
           catch(err) {}
         }
       }
       catch (e) {}

       insertionS = start;
       insertionE = end
     }
   }

   function setRange(ctrl, start, end)
   {
     if(ctrl.setSelectionRange) // Standard way (Mozilla, Opera, ...)
     {
       ctrl.setSelectionRange(start, end);
     }
     else // MS IE
     {
       var range;

       try
       {
         range = ctrl.createTextRange();
       }
       catch(e)
       {
         try
         {
           range = document.body.createTextRange();
           range.moveToElementText(ctrl);
         }
         catch(e)
         {
           range = null;
         }
       }

       if(!range) return;

       range.collapse(true);
       range.moveStart("character", start);
       range.moveEnd("character", end - start);
       range.select();
     }

     insertionS = start;
     insertionE = end;
   }

   function deleteSelection(ctrl)
   {
     if(insertionS == insertionE) return;

     var tmp = (document.selection && !window.opera) ? ctrl.value.replace(/\r/g,"") : ctrl.value;
     ctrl.value = tmp.substring(0, insertionS) + tmp.substring(insertionE, tmp.length);

     setRange(ctrl, insertionS, insertionS);
   }

   function deleteAtCaret(ctrl)
   {
     // if(insertionE < insertionS) insertionE = insertionS;
     if(insertionS != insertionE)
     {
       deleteSelection(ctrl);
       return;
     }

     if(insertionS == insertionE)
       insertionS = insertionS - 1;

     var tmp = (document.selection && !window.opera) ? ctrl.value.replace(/\r/g,"") : ctrl.value;
     ctrl.value = tmp.substring(0, insertionS) + tmp.substring(insertionE, tmp.length);

     setRange(ctrl, insertionS, insertionS);
   }

// This function inserts text at the caret position:
//
function insertAtCaret(ctrl, val)
{
 if(insertionS != insertionE) deleteSelection(ctrl);
 if(isgecko && document.createEvent && !window.opera)
 {
   var e = document.createEvent("KeyboardEvent");
   if(e.initKeyEvent && ctrl.dispatchEvent)
   {
     e.initKeyEvent("keypress",        // in DOMString typeArg,
                    false,             // in boolean canBubbleArg,
                    true,              // in boolean cancelableArg,
                    null,              // in nsIDOMAbstractView viewArg, specifies UIEvent.view. This value may be null;
                    false,             // in boolean ctrlKeyArg,
                    false,             // in boolean altKeyArg,
                    false,             // in boolean shiftKeyArg,
                    false,             // in boolean metaKeyArg,
                    null,              // key code;
                    val.charCodeAt(0));// char code.

     ctrl.dispatchEvent(e);
   }
 }
 // else {
   var tmp = (document.selection && !window.opera) ? ctrl.value.replace(/\r/g,"") : ctrl.value;
   var newVal=  tmp.substring(0, insertionS) + val + tmp.substring(insertionS, tmp.length);
   ctrl.value = newVal;
 //}

 setRange(ctrl, insertionS + val.length, insertionS + val.length);
}
   
function getMouse(e){
    var ev=(!e)?window.event:e;//IE:Moz
    if (ev.pageX){//Moz
        pos[0]=ev.pageX+window.pageXOffset;
        pos[1]=ev.pageY+window.pageYOffset;
    }
    else if(ev.clientX){//IE
        pos[0]=ev.clientX+document.body.scrollLeft;
        pos[1]=ev.clientY+document.body.scrollTop;
    }
}

function findPosX(obj)
  {
    var curleft = 0;
    if(obj.offsetParent)
        while(1) 
        {
          curleft += obj.offsetLeft;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.x)
        curleft += obj.x;
    return curleft;
  }

  function findPosY(obj)
  {
    var curtop = 0;
    if(obj.offsetParent)
        while(1)
        {
          curtop += obj.offsetTop;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.y)
        curtop += obj.y;
    return curtop;
  }