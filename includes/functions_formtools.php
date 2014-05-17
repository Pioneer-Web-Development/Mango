<?php
//<!--VERSION: 1.0 **||**-->

//print a text box

function tableStart($formoptions,$headers,$cols,$searchblock='',$sortcol='false')
{
    ?>
    <div id='tableoptions' style='margin:10 10 0 10;float:right;width:20%;'>
        <div class='ui-state-highlight ui-corner-all' style='padding: 5px;'>
           <p><span class='ui-icon ui-icon-info' style='float: left; margin-right: 0.3em;'></span><strong>Actions:</strong></p> 
            <?php print implode("<br>",explode(",",$formoptions)); ?>
        </div>
        <?php if ($searchblock!=''){?>
        <div class='ui-state-highlight ui-corner-all' style='padding: 5px;margin-top:10px;'>
           <p><span class='ui-icon ui-icon-info' style='float: left; margin-right: 0.3em;'></span><strong>Search:</strong></p> 
            <?php print $searchblock; ?>
        </div>
        <?php } ?>
    </div>
        
    <div style='float:left;width:72%;margin-right:10px;'> <!-- opens up the div for the table! -->
    <?php
    $headers=explode(",",$headers);
    $h=0;
    print "<table id='stable' class='ui-widget' style='width:100%'>\n<thead>\n<tr>\n";
    foreach($headers as $key=>$header)
    {
        print "<th>$header</th>";
        $h++;
    }
    $dif=$cols-$h;
    print "<th colspan=$dif class='{sorter: $sortcol}'>Actions</th>";
    print "</tr>\n</thead>
    <tbody>
    ";
}

function tableEnd($set=array('numrows'=>'0'),$extrascript='',$sortcol=0,$sortdir='asc',$stateSave='false')
{
    if($_POST['stateSave']){$stateSave='true';}
    if($set['numrows']>10){$limit=10;}else{$limit=$set['numrows'];}
    print '</tbody>
    </table>';
    if($set['numrows']==0){
        displayMessage('No records found','error');
    }
    //wrap up, we're not using anything fancy other than the delete box
        ?>
    <script>
    $('a.delete').click(function() { 
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
                    window.location = a.href;
                }
                
            },
            open: function() {
                $('.ui-dialog-buttonpane > button:last').focus();
            }
       
        });
        return false;
    })
    
    <?php echo $extrascript; ?>
    
    $('#stable').dataTable( {
        "bPaginate": true,
        "sDom": '<"clear">lTfrtip',
        "iDisplayLength": 25,
        "bLengthChange": true,
        "bFilter": true,
        "bSort": true,
        "bInfo": false,
        "bJQueryUI": true,
        "bStateSave": <?php echo $stateSave ?>,
        "sPaginationType": "full_numbers",
        "bAutoWidth": true,
        "aaSorting": [[ <?php echo $sortcol ?>, <?php echo '"'.$sortdir.'"'; ?> ]]
    });
    
   </script>
   <?php
    
   print "</div><div class='clear'></div> <!--closes table div and clears the float -->\n";
    
}



function input_text($element_name, $value, $size='', $disabled=false, $onchange='',$onfocus='',$onblur='',$onkeypress='',$onkeydown='',$onkeyup='',$validation='',$onclick='',$label='',$explain_text='')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
    }
    $temp.='<input type="text" id="' . $element_name .'" name="' . $element_name .'"';
    if ($disabled) {$temp.= " readonly";}
    $temp.= " value=\"$value\"";
    if ($size!='') {
        $temp.= " size=$size";
    }
    if ($onchange!=''){
        $temp.=" onchange=\"$onchange\"";
    }
    if ($onfocus!=''){
        $temp.=" onfocus=\"$onfocus\"";
    }
    if ($onblur!=''){
        $temp.=" onblur=\"$onblur\"";
    }
    if ($onkeypress!=''){
        $temp.=" onkeypress=\"$onkeypress\"";
    }
    if ($onkeydown!=''){
        $temp.=" onkeydown=\"$onkeydown\"";
    }
    if ($onkeyup!=''){
        $temp.=" onkeyup=\"$onkeyup\"";
    }
    if ($onclick!=''){
        $temp.=" onclick=\"$onclick\"";
    }
    if ($validation!=''){
        $temp.=" $validation";
    }
    $temp.="></input>";
    if ($label!="")
    {
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n";
        print "<div class='clear'></div>\n";
    } else {
       return $temp;
    }
}

//print a submit button
function input_submit($element_name, $button_name, $disabled=false)
{
    print '<input type="submit" id="' . $element_name .'" name="' . $element_name .'" value="';
    print htmlentities($button_name) .'"';
    if ($disabled) {print " disabled";}
    print '></input>';
}

//print a textarea
function input_textarea($element_name, $value, $cols='40',$rows='5',$mce=true,$disabled=false,$onchange='',$onfocus='',$onblur='',$onkeypress='',$onkeydown='',$onkeyup='',$validation='',$label='',$explain_text='')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
    }
    
    if ($mce){$meditor=" class=\"GuiEditor\"";}else{$meditor=" class=\"noGuiEditor\"";}
    $temp.= "<textarea id=\"$element_name\" name=\"$element_name\" cols=\"$cols\" rows=\"$rows\"$meditor";
    if ($disabled) {print " readonly";}
    if ($onchange!=''){
        $temp.=" onchange=\"$onchange\"";
    }
    if ($onfocus!=''){
        $temp.=" onfocus=\"$onfocus\"";
    }
    if ($onblur!=''){
        $temp.=" onblur=\"$onblur\"";
    }
    if ($onkeypress!=''){
        $temp.=" onkeypress=\"$onkeypress\"";
    }
    if ($onkeydown!=''){
        $temp.=" onkeydown=\"$onkeydown\"";
    }
    if ($onkeyup!=''){
        $temp.=" onkeyup=\"$onkeyup\"";
    }
    if ($validation!=''){
        $temp.=" $validation";
    }
    
    $temp.= '>';
    $temp.=htmlentities($value) ;
    
    $temp.= '</textarea>';
    if ($label!='')
    {
        print "<small>$explain_text</small><br>\n";
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    
    } else {
        return $temp;
    }
}

//print a radio button or checkbox
function input_radiocheck($type, $element_name, $element_value=0, $onclick='', $validation='',$label='',$explain_text='')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
    }
    $temp="<input type='$type' id='$element_name' name='$element_name'";
    if ($element_value) {
        $temp.=" checked='checked'";
    }
    if ($validation!=''){
        $temp.=" $validation";
    }
    if (!$onclick=='') {
        $temp.= "onclick='$onclick'></input>\n";
    } else {
        $temp.= "></input>\n";
    }
    if ($label!='')
    {
        print $temp."<label for='$element_name'>$explain_text</label>\n";
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}

function input_checkbox($element_name, $element_value=0, $onclick='', $validation='',$label='',$explain_text='', $groupclass='')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
    }
    $temp="<input type='checkbox' id='$element_name' name='$element_name'";
    if ($element_value) {
        $temp.=" checked='checked'";
    }
    if ($validation!=''){
        $temp.=" $validation";
    }
    if ($groupclass!=''){
        $temp.=" class='$groupclass'";
    }
    if (!$onclick=='') {
        $temp.= "onclick='$onclick'></input>\n";
    } else {
        $temp.= "></input>\n";
    }
    if ($label!='')
    {
        print $temp."<label for='$element_name'>$explain_text</label>\n";
        print "</div>\n<div class='clear'></div>\n";
    } else if ($explain_text!='') {
        $temp.="<label for='$element_name'>$explain_text</label>\n";
        return $temp;
    } else {
        return $temp;
    }
}

//print a password text field
function input_password($element_name, $values, $validation='')
{
    print "<input type=\"password\" id=\"$element_name\" name=\"$element_name\" value=\"";
    print htmlentities($values[$element_name]) ."\" $validation></input>\n";
}

//print a <select> menu
function input_select($element_name, $selected, $options, $multiple = false, $action='', $disabled=false, $validation='',$label='',$explain_text='',$stylewidth=0)
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
    }
    
    // print out the <select> tag
    $temp="<select ";
    // if multiple choices are permitted, add the multiple attribute
    // and add a [] to the end of the tag name
    if ($multiple) { 
        $temp.= "name='".$element_name."[]' id='".$element_name."[]'  multiple='multiple'";
    } else {
        $temp.= "name='$element_name' id='$element_name'";
    }
    if (!$action=='') {$temp.=' onChange="'.$action.'"';}
    if ($validation!=''){$temp.= " $validation";}
    if($stylewidth>0)
    {
        $temp.=" style='width:".$stylewidth."px'";
    }
    if ($disabled) {
        $temp.= " disabled>\n";
    } else {
        $temp.= " >\n";
    }
    
    // set up the list of things to be selected
    $selected_options = array();
    if ($multiple) {
        foreach ($selected[$element_name] as $val) {
            $selected_options[$val] = true;
        }
    } else {
        $selected_options[ $selected[$element_name] ] = true;
    }

    // print out the <option> tags
    foreach ($options as $option => $option_label) {
        $temp.="<option value='". ($option) . "'";
        if ($selected==($option_label)) {
            $temp.=" selected='selected'";
        }
        $temp.= ">" . htmlentities($option_label) . "</option>\n";
    }
    $temp.="</select>\n";
    if ($label!='')
    {
        print $temp."<small>$explain_text</small>\n";
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp."\n";
    }
}

function safe_text($text,$dir)
{
    if ($dir=='in') {
        $text=str_replace('\r\n','<br />',$text);
        $text=str_replace('\n','<br />',$text);
        //$text=htmlentities($text,ENT_QUOTES);
        $text=addslashes($text);  
    } else {
        $text=stripslashes($text); 
        //$text=html_entity_decode ( $text, ENT_QUOTES);
        $text=str_replace('<br />','\n',$text);
    }     
    return $text;   
}


function input_date($element_name,$date,$label='',$explain_text='')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
    }
    //$temp="<div><script>DateInput('$element_name', true, 'YYYY-MM-DD','$date')</script></div>\n";
    $temp="<input type='text' name='$element_name' id='$element_name' value='$date'/><script type='text/javascript'>$('#$element_name').datepicker({ dateFormat: 'yy-mm-dd' });</script>\n";
    
    if ($label!='')
    {
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}

function input_time($element_name,$hour,$minute,$label='',$explain_text='')
{
    $minutes=array();
    for ($i=0;$i<=60;$i++){
        if ($i<10){
            $minutes["0$i"]="0$i";
        }else{
            $minutes[$i]=$i;
        }
    }
    $hours=array("00"=>"Midnight",
                    "01"=>"1 am",
                    "02"=>"2 am",
                    "03"=>"3 am",
                    "04"=>"4 am",
                    "05"=>"5 am",
                    "06"=>"6 am",
                    "07"=>"7 am",
                    "08"=>"8 am",
                    "09"=>"9 am",
                    "10"=>"10 am",
                    "11"=>"11 am",
                    "12"=>"Noon",
                    "13"=>"1 pm",
                    "14"=>"2 pm",
                    "15"=>"3 pm",
                    "16"=>"4 pm",
                    "17"=>"5 pm",
                    "18"=>"6 pm",
                    "19"=>"7 pm",
                    "20"=>"8 pm",
                    "21"=>"9 pm",
                    "22"=>"10 pm",
                    "23"=>"11 pm"
                );

    $temp=input_select($element_name."_hour",$hours[$hour],$hours).":";
    $temp.=input_select($element_name."_minute",$minutes[$minute],$minutes);
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
    
}

function input_datetime($element_name,$datetime,$label='',$explain_text='')
{
    /*
    $date=date("Y-m-d",strtotime($datetime));
    $hour=date("H",strtotime($datetime));
    $minute=date("i",strtotime($datetime));
    print "<!-- datetime is $datetime, date is $date, hour is $hour, minute is $minute -->\n";
    $minutes=array();
    for ($i=0;$i<=60;$i++){
        if ($i<10){
            $minutes["0$i"]="0$i";
        }else{
            $minutes[$i]=$i;
        }
    }
    $hours=array("00"=>"Midnight",
                    "01"=>"1 am",
                    "02"=>"2 am",
                    "03"=>"3 am",
                    "04"=>"4 am",
                    "05"=>"5 am",
                    "06"=>"6 am",
                    "07"=>"7 am",
                    "08"=>"8 am",
                    "09"=>"9 am",
                    "10"=>"10 am",
                    "11"=>"11 am",
                    "12"=>"Noon",
                    "13"=>"1 pm",
                    "14"=>"2 pm",
                    "15"=>"3 pm",
                    "16"=>"4 pm",
                    "17"=>"5 pm",
                    "18"=>"6 pm",
                    "19"=>"7 pm",
                    "20"=>"8 pm",
                    "21"=>"9 pm",
                    "22"=>"10 pm",
                    "23"=>"11 pm"
                );
    $temp="<div><script>DateInput('".$element_name."_date', true, 'YYYY-MM-DD','$date')</script></div>\n";
    $temp.=input_select($element_name."_hour",$hours[$hour],$hours).":";
    $temp.=input_select($element_name."_minute",$minutes[$minute],$minutes);
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
    */
    $temp="<input type='text' name='$element_name' id='$element_name' value='$date'/>
    <script type='text/javascript'>$('#$element_name').datetimepicker();</script>\n";
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }    
}

function input_color($element_name,$color,$label='',$explain_text='')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
    }
    print input_text($element_name,$color,'10',false,'','','','','','','','',$label,$explain_text);
    ?>
    <script type='text/javascript'>
    $('#<?php echo $element_name; ?>').ColorPicker({
    onSubmit: function(hsb, hex, rgb, el) {
        $(el).val(hex);
        $(el).ColorPickerHide();
    },
    onBeforeShow: function () {
        $(this).ColorPickerSetColor(this.value);
    },
    onChange: function (hsb, hex, rgb) {
        $(this).css('backgroundColor', '#' + hex);
    }
})
.bind('keyup', function(){
    $(this).ColorPickerSetColor(this.value);
});

</script>
    <?php
    
    if ($label!='')
    {
        print "</div>\n<div class='clear'></div>\n";
    }
}

function input_address($element_name,$location_name='',$location_street='',$location_city='',$location_state='ID',$location_zip='',$label='',$explain_text='')
{
    $temp="Location Name: ";
    $temp.=input_text($element_name."_name",$location_name,50);
    $temp.="\n<br>Street Address: ";
    $temp.=input_text($element_name."_street",$location_street,50);
    $temp.="\n<br><div style='float:left;margin-right:15px;'>City: ";
    $temp.=input_text($element_name."_city",$location_city,30);
    $temp.="</div>\n<div style='float:left;'>State: ";
    $temp.=input_select($element_name."_state",$GLOBALS['states'][$location_state],$GLOBALS['states']);
    $temp.="</div>\n<div style='float:left;margin-left:15px;'>Zip: \n";
    $temp.=input_text($element_name."_zip",$location_zip,10);
    $temp.="</div>\n<div class='clear'></div>\n";
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}





//below here are the new versions of these functions, with more logical placement of the elements
function make_text($element_name, $value, $label='',$explain_text='', $size=50, $validation='',$disabled=false, $onchange='',$onfocus='',$onblur='',$onkeypress='',$onkeydown='',$onkeyup='',$onclick='',$maxlength=255,$placeholder='')
{
    if ($label!='')
    {
        print "<div class='label'><label for='$element_name'>$label</label></div><div class='input'>";
    }
    $temp.='<input type="text" id="' . $element_name .'" name="' . $element_name .'"';
    if ($disabled) {$temp.= " readonly";}
    $temp.= " value=\"$value\"";
    if ($size!='') {
        $temp.= " size=$size";
    }
    if ($placeholder!='') {
        $temp.= " placeholder='$placeholder'";
    } else {
        $temp.=" placeholder='$label'";
    }
    if ($onchange!=''){
        $temp.=" onchange=\"$onchange\"";
    }
    if ($onfocus!=''){
        $temp.=" onfocus=\"$onfocus\"";
    }
    if ($onblur!=''){
        $temp.=" onblur=\"$onblur\"";
    }
    if ($onkeypress!=''){
        $temp.=" onkeypress=\"$onkeypress\"";
    }
    if ($onkeydown!=''){
        $temp.=" onkeydown=\"$onkeydown\"";
    }
    if ($onkeyup!=''){
        $temp.=" onkeyup=\"$onkeyup\"";
    }
    if ($onclick!=''){
        $temp.=" onclick=\"$onclick\"";
    }
    if ($validation!=''){
        $temp.=" $validation";
    }
    $temp.=" maxlength=$maxlength";
    $temp.="/>";
    if ($label!="")
    {
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n";
        print "<div class='clear'></div>\n";
    } else {
       return $temp;
    }
}


function make_email($element_name, $value, $label='',$explain_text='',$confirm=false)
{
    if ($label!='')
    {
        print "<div class='label'><label for='$element_name'>$label</label></div><div class='input'>";
    }
    $temp="<input class='input' type='text' id='$element_name' name='$element_name' value='$value' size=50 maxlength=255 />";
    if ($confirm)
    {
        $temp.="<br /><input class='input' type='text' size=50 id='".$element_name."_confirm' name='".$element_name."_confirm' 
        value='$value' maxlength=255 onblur='if(this.value!=document.getElementById(\"$element_name\").value){alert(\"Emails do not match!\");}'/>";
    }
    if ($label!="")
    {
        if ($explain_text!=''){print "<small>$explain_text</small><br />\n";}
        print $temp;
        print "</div>\n";
        print "<div class='clear'></div>\n";
    } else {
       return $temp;
    }
}

function make_number($element_name, $value, $label='',$explain_text='', $validation='',$disabled=false, $onchange='',$onfocus='',$onblur='',$onkeydown='',$onkeyup='',$onclick='')
{
    if ($label!='')
    {
        print "<div class='label'><label for='$element_name'>$label</label></div><div class='input'>";
    }
    $temp.='<input type="text" id="' . $element_name .'" name="' . $element_name .'"';
    if ($disabled) {$temp.= " readonly";}
    $temp.= " value=\"$value\"";
    $temp.= " size=10";
    if ($onchange!=''){
        $temp.=" onchange=\"$onchange\"";
    }
    if ($onfocus!=''){
        $temp.=" onfocus=\"$onfocus\"";
    }
    if ($onblur!=''){
        $temp.=" onblur=\"$onblur\"";
    }
    $temp.=" onkeypress=\"return isNumberKey(event);\"";
    if ($onkeydown!=''){
        $temp.=" onkeydown=\"$onkeydown\"";
    }
    if ($onkeyup!=''){
        $temp.=" onkeyup=\"$onkeyup\"";
    }
    if ($onclick!=''){
        $temp.=" onclick=\"$onclick\"";
    }
    if ($validation!=''){
        $temp.=" $validation";
    }
    $temp.="></input>";
    if ($label!="")
    {
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n";
        print "<div class='clear'></div>\n";
    } else {
       return $temp;
    }
}

//print a submit button
function make_submit($element_name, $button_name, $label='',$disabled=false,$onclick='',$buttonclass='submit')
{
    $temp="<div class='label'>$label</div><div class='input'>\n";
    $temp.="<input type=\"submit\" id=\"$element_name\" name=\"$element_name\" value=\"$button_name\" class=\"$buttonclass\"";
    if ($disabled) {$temp.= " disabled=\"disabled\"";}
    if ($onclick!='') {$temp.= " onclick=\"$onclick\"";}
    $temp.= " />\n";
    $temp.= "</div>\n<div class='clear'></div>\n";
    print $temp;
}

//print a regular button
function make_button($element_name, $button_name, $label='',$disabled=false,$onclick='',$buttonclass='submit')
{
    $temp="<div class='label'>$label</div><div class='input'>\n";
    $temp.="<input type=\"button\" id=\"$element_name\" name=\"$element_name\" value=\"$button_name\" class=\"$buttonclass\"";
    if ($disabled) {$temp.= " disabled";}
    if ($onclick!='') {$temp.= " onclick=\"$onclick\"";}
    $temp.= " />\n";
    $temp.= "</div>\n<div class='clear'></div>\n";
    print $temp;
}

function make_csssubmit($button_name, $button_value, $form_name, $color='', $clickcolor='')
{
    $temp="<div class='label'>&nbsp;</div><div class='input'>\n";
    $temp.="<input type='hidden' name='$button_name' value='$button_value' />\n";
    $temp.="<div class='cssSubmitButton'";
    if ($color!=''){$temp.=" style='background-color:$color;'";}
    if ($clickcolor!=''){$temp.=" onMouseOver=\"this.style.backgroundColor='$clickcolor'\" onMouseOut=\"this.style.backgroundColor='$color'\"";}
    $temp.="><a href='#' onClick='document.".$form_name.".submit();'>$button_value</a><span></span></div>\n";
    $temp.= "</div>\n<div class='clear'></div>\n";
    print $temp;
}

//print a textarea
function make_textarea($element_name, $value, $label='',$explain_text='',$cols='80',$rows='15',$mce=true,$validation='',$disabled=false,$onchange='',$onfocus='',$onblur='',$onkeypress='',$onkeydown='',$onkeyup='')
{
    if ($label!='')
    {
        $temp="<div class='label'>$label</div><div class='input'>\n";
    }
    
    if ($mce){$meditor=" class=\"GuiEditor\"";}else{$meditor=" class=\"noGuiEditor\"";}
    if ($explain_text!='')
    {
        $temp.="<small>$explain_text</small><br>\n";
    }
    $temp.= "<textarea id=\"$element_name\" name=\"$element_name\" cols=\"$cols\" rows=\"$rows\"$meditor";
    if ($disabled) {print " readonly";}
    if ($onchange!=''){
        $temp.=" onchange=\"$onchange\"";
    }
    if ($onfocus!=''){
        $temp.=" onfocus=\"$onfocus\"";
    }
    if ($onblur!=''){
        $temp.=" onblur=\"$onblur\"";
    }
    if ($onkeypress!=''){
        $temp.=" onkeypress=\"$onkeypress\"";
    }
    if ($onkeydown!=''){
        $temp.=" onkeydown=\"$onkeydown\"";
    }
    if ($onkeyup!=''){
        $temp.=" onkeyup=\"$onkeyup\"";
    }
    if ($validation!=''){
        $temp.=" $validation";
    }
    
    $temp.= ">$value</textarea>\n";
    if ($label!='')
    {
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    
    } else {
        return $temp;
    }
    
}

//print a radio button or checkbox
function make_radiocheck($type, $element_name, $element_value=0, $label='', $explain_text='',$validation='', $onclick='')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>\n";
    }
    $temp="<input type='$type' id='$element_name' name='$element_name'";
    if ($element_value) {
        $temp.=" checked='checked'";
    }
    if ($validation!=''){
        $temp.=" $validation";
    }
    if (!$onclick=='') {
        $temp.= "onclick='$onclick' />\n";
    } else {
        $temp.= "/>\n";
    }
    if ($label!='')
    {
        print $temp."<LABEL FOR='$element_name'>$explain_text</label>\n";
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}

//print a checkbox
function make_checkbox($element_name, $element_value=0, $label='', $explain_text='',$validation='', $onclick='', $groupclass='')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>\n";
    }
    $temp="<input type='checkbox' id='$element_name' name='$element_name'";
    if ($element_value) {
        $temp.=" checked='checked'";
    }
    if ($validation!=''){
        $temp.=" $validation";
    }
    if ($groupclass!=''){
        $temp.=" class='$groupclass'";
    }
    if (!$onclick=='') {
        $temp.= "onclick='$onclick' />\n";
    } else {
        $temp.= "/>\n";
    }
    if ($label!='')
    {
        print $temp."<LABEL FOR='$element_name'>$explain_text</label>\n";
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}

//print a password text field
function make_password($element_name, $value, $label='', $explain_text='',$confirm=false)
{
    if ($label!='')
    {
        print "<div class='label'>$label</div>&nbsp;<div class='input'>\n";
    }
    $temp="<input class='input' type='password' id='$element_name' name='$element_name' value='$value' ></input>\n";
    if ($confirm)
    {
        $temp.="<br /><input class='input' type='password' id='".$element_name."_confirm' name='".$element_name."_confirm'
         value='$value' onblur='if(this.value!=document.getElementById(\"$element_name\").value){alert(\"Passwords do not match!\");}' />\n";
        
    }
    if ($label!='')
    {
        if ($explain_text!='')
        {
            print "<small>$explain_text</small><br />\n";
        }
        print $temp;
        print "&nbsp;</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}

//print a hidden text field
function make_hidden($element_name, $value)
{
    print "<input type='hidden' id='$element_name' name='$element_name' value='$value' />\n";
    
}

function make_file($element_name, $label='', $explain_text='',$filetoshow='', $buttonclass='submit')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div>&nbsp;<div class='input'>\n";
    }
    $temp="<input class='input' type=\"file\" id=\"$element_name\" name=\"$element_name\" class=\"$buttonclass\"/ onblur=\"document.getElementById('".$element_name."_hidden').value=this.value;\">\n";
    $temp.="<input type='hidden' id='".$element_name."_hidden' name='".$element_name."_hidden' value=''/>\n";
    if ($label!='')
    {
        if ($explain_text!='')
        {
            print "<small>$explain_text</small><br>\n";
        }
        print $temp;
        $fileparts=explode("/",$filetoshow);
    
        if ($filetoshow!='' && file_exists($filetoshow) && end($fileparts)!='')
        {
            print "<br /><img src='$filetoshow' height=100 border=0>\n";
        }
        print "&nbsp;</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}

function make_multifile($element_name,$path,$label,$explain_text)
{
    if ($label!='')
    {
        print "<div class='label'>$label</div>&nbsp;<div style='float:left;'>\n";
    }
    $temp="<div id='$element_name'></div>\n";
    $temp.="
    <script type='text/javascript'>
      \$(document).ready(function () {
        var currentDir='$path';
        \$('#$element_name').fineUploader({
          request: {
            endpoint: 'includes/ajax_handlers/fineUploaderHandler.php'
          },
          debug: true,
          validation: {
            allowedExtensions: ['jpeg', 'jpg', 'gif', 'png'],
            sizeLimit: 5000 * 1024 //kb size
          },
          failedUploadTextDisplay: {
            mode: 'custom',
            maxChars: 40,
            responseProperty: 'error',
            enableTooltip: true
          },
          debug: true
        })
        .on('submit', function(event, id, filename) {
             \$(this).fineUploader('setParams', {'dir': '$path'});
          })
      });
    </script>
    ";    
    if ($label!='')
    {
        if ($explain_text!='')
        {
            print "<small>$explain_text</small><br>\n";
        }
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}

//print a <select> menu
function make_select($element_name, $selected, $options, $label='',$explain_text='',$validation='',$editable = false, $action='', $disabled=false)
{
    if ($label!='')
    {
        print "<div class='label'>$label</div>\n  <div class='input'>\n";
        if ($explain_text!='')
        {
            print "<small>$explain_text</small><br />\n";
        }

    }
    
    // print out the <select> tag
    $temp="  <select ";
    $temp.= "name='$element_name' id='$element_name'";
    if (!$action=='') {$temp.=' onChange="'.$action.'"';}
    if ($validation!=''){$temp.= " $validation";}
    if ($disabled) {
        $temp.= " disabled>\n";
    } else {
        $temp.= ">\n";
    }
    
    // set up the list of things to be selected
    $selected_options = array();
    if ($multiple) {
        foreach ($selected[$element_name] as $val) {
            $selected_options[$val] = true;
        }
    } else {
        $selected_options[ $selected[$element_name] ] = true;
    }

    // print out the <option> tags
    foreach ($options as $option => $option_label) {
        $temp.="    <option value='". ($option) . "'";
        if ($selected==$option_label) {
            $temp.=" selected='selected'";
        }
        $temp.= ">" . ($option_label) . "</option>\n";
    }
    $temp.="  </select>\n";
    if($editable)
    {
        $temp.="<script type='text/javascript'>
        \$(function() {
  \$('#$element_name').editableSelect(
    {
      bg_iframe: true,
      autocomplete:true,
      onSelect: function(list_item) {
        alert('List item text: '+ list_item.text());
        // 'this' is a reference to the instance of EditableSelect
        // object, so you have full access to everything there
        alert('Input value: '+ this.text.val());
      },
      case_sensitive: false, // If set to true, the user has to type in an exact
                             // match for the item to get highlighted
      items_then_scroll: 10 // If there are more than 10 items, display a scrollbar
    }
  );
  var select = \$('$element_name:first');
});   
    </script>
       ";
    }
    if ($label!='')
    {
        print $temp;
        print "  </div>\n<div class='clear'></div>\n";
    } else {
        return $temp."\n";
    }
    
}


function make_state($element_name, $selected, $label='',$explain_text='')
{
    $states=array("AL"=>"ALABAMA",
    "AK"=>"ALASKA",
    "AZ"=>"ARIZONA",
    "AR"=>"ARKANSAS",
    "CA"=>"CALIFORNIA",
    "CO"=>"COLORADO",
    "CT"=>"CONNECTICUT",
    "DE"=>"DELAWARE",
    "DC"=>"DISTRICT OF COLUMBIA",
    "FL"=>"FLORIDA",
    "GA"=>"GEORGIA",
    "GU"=>"GUAM",
    "HI"=>"HAWAII",
    "ID"=>"IDAHO",
    "IL"=>"ILLINOIS",
    "IN"=>"INDIANA",
    "IA"=>"IOWA",
    "KS"=>"KANSAS",
    "KY"=>"KENTUCKY",
    "LA"=>"LOUISIANA",
    "ME"=>"MAINE",
    "MD"=>"MARYLAND",
    "MA"=>"MASSACHUSETTS",
    "MI"=>"MICHIGAN",
    "MN"=>"MINNESOTA",
    "MS"=>"MISSISSIPPI",
    "MO"=>"MISSOURI",
    "MT"=>"MONTANA",
    "NE"=>"NEBRASKA",
    "NV"=>"NEVADA",
    "NH"=>"NEW HAMPSHIRE",
    "NJ"=>"NEW JERSEY",
    "NM"=>"NEW MEXICO",
    "NY"=>"NEW YORK",
    "NC"=>"NORTH CAROLINA",
    "ND"=>"NORTH DAKOTA",
    "OH"=>"OHIO",
    "OK"=>"OKLAHOMA",
    "OR"=>"OREGON",
    "PW"=>"PALAU",
    "PA"=>"PENNSYLVANIA",
    "PR"=>"PUERTO RICO",
    "RI"=>"RHODE ISLAND",
    "SC"=>"SOUTH CAROLINA",
    "SD"=>"SOUTH DAKOTA",
    "TN"=>"TENNESSEE",
    "TX"=>"TEXAS",
    "UT"=>"UTAH",
    "VT"=>"VERMONT",
    "VA"=>"VIRGINIA",
    "VI"=>"VIRGIN ISLANDS",
    "WA"=>"WASHINGTON",
    "WV"=>"WEST VIRGINIA",
    "WI"=>"WISCONSIN",
    "WY"=>"WYOMING"
    );
    
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
        if ($explain_text!='')
        {
            print "<small>$explain_text</small><br />\n";
        }

    }
    
    // print out the <select> tag
    $temp="<select class='input' ";
    // if multiple choices are permitted, add the multiple attribute
    // and add a [] to the end of the tag name
    $temp.= "name='$element_name' id='$element_name'";
    $temp.= ">\n";
    
    
    // print out the <option> tags
    foreach ($states as $option => $option_label) {
        $temp.="<option value='". ($option) . "'";
        if ($selected==$option_label || $selected==$option) {
            $temp.=" selected='selected'";
        }
        $temp.= ">" . ($option_label) . "</option>\n";
    }
    $temp.="</select>\n";
    if ($label!='')
    {
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp."\n";
    }
}


function make_date($element_name,$date,$label='',$explain_text='')
{
    //just in case date is empty, set to today
    if ($date=='')
    {
        $date=date("Y-m-d");
    }
    //just in case, we're breaking date to two items and using only the first
    $date=explode(" ",$date);
    $date=$date[0];
    
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
    }
    //$temp="<div><script>DateInput('$element_name', true, 'YYYY-MM-DD','$date')</script></div>\n";
    $temp="<input type='text' name='$element_name' id='$element_name' value='$date'/><script type='text/javascript'>\$('#$element_name').datepicker({ dateFormat: 'yy-mm-dd' });</script>\n";
    
    if ($label!='')
    {
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}

function make_time($element_name,$time,$label='',$explain_text='')
{
    $temp="<input type='text' name='$element_name' id='$element_name' value='$time'/>
    <script type='text/javascript'>$('#$element_name').timepicker();</script>\n";
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    } 
    
}

function make_datetime($element_name,$datetime,$label='',$explain_text='',$stepMinute=1,$minDate='',$maxDate='')
{
    $datetime=date("Y-m-d H:i",strtotime($datetime));
    $temp="<input type='text' name='$element_name' id='$element_name' value='$datetime'/>
    <script type='text/javascript'>
    \$('#$element_name').datetimepicker({ 
        dateFormat: 'yy-mm-dd', 
        stepMinute: $stepMinute";
    if($minDate!='')
    {
        $tempDate=explode(" ",$minDate);
        $tempTime=$tempDate[1];
        $tempTime=explode(":",$tempTime);
        $tempDate=explode("-",$tempDate[0]);
        $tempMonth=$tempDate[1]-1;
        $temp.=",\nminDateTime: new Date($tempDate[0], $tempMonth, $tempDate[2], $tempTime[0], $tempTime[1])";
    }
    if($maxDate!='')
    {
        $tempDate=explode(" ",$maxDate);
        $tempTime=$tempDate[1];
        $tempTime=explode(":",$tempTime);
        $tempDate=explode("-",$tempDate[0]);
        $tempMonth=$tempDate[1]-1;
        $temp.=",\nmaxDateTime: new Date($tempDate[0], $tempMonth, $tempDate[2], $tempTime[0], $tempTime[1])";
    }
    $temp.="\n});
    </script>\n";
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    } 
}

function make_color($element_name,$color,$label='',$explain_text='')
{
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
    }
    if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
    print "<input type='text' id='$element_name' name='$element_name' value='$color' size='10' style='background-color:$color;'></input>\n"; 
    ?>
    <script type='text/javascript'>
    $('#<?php echo $element_name; ?>').ColorPicker({
    onSubmit: function(hsb, hex, rgb, el) {
        $(el).val(hex);
        $(el).ColorPickerHide();
    },
    onBeforeShow: function () {
        $(this).ColorPickerSetColor(this.value);
    },
    onChange: function (hsb, hex, rgb) {
        $('#<?php echo $element_name; ?>').css('backgroundColor', '#' + hex);
        $('#<?php echo $element_name; ?>').val('#' + hex);
    }
    })
    .bind('keyup', function(){
        $(this).ColorPickerSetColor(this.value);
    });
</script>
    <?php
    if ($label!='')
    {
        print "</div>\n<div class='clear'></div>\n";
    } 
}

function make_address($element_name,$location_name='',$location_street='',$location_city='',$location_state='ID',$location_zip='',$label='',$explain_text='')
{
    $states=array("AL"=>"ALABAMA",
    "AK"=>"ALASKA",
    "AZ"=>"ARIZONA",
    "AR"=>"ARKANSAS",
    "CA"=>"CALIFORNIA",
    "CO"=>"COLORADO",
    "CT"=>"CONNECTICUT",
    "DE"=>"DELAWARE",
    "DC"=>"DISTRICT OF COLUMBIA",
    "FL"=>"FLORIDA",
    "GA"=>"GEORGIA",
    "GU"=>"GUAM",
    "HI"=>"HAWAII",
    "ID"=>"IDAHO",
    "IL"=>"ILLINOIS",
    "IN"=>"INDIANA",
    "IA"=>"IOWA",
    "KS"=>"KANSAS",
    "KY"=>"KENTUCKY",
    "LA"=>"LOUISIANA",
    "ME"=>"MAINE",
    "MD"=>"MARYLAND",
    "MA"=>"MASSACHUSETTS",
    "MI"=>"MICHIGAN",
    "MN"=>"MINNESOTA",
    "MS"=>"MISSISSIPPI",
    "MO"=>"MISSOURI",
    "MT"=>"MONTANA",
    "NE"=>"NEBRASKA",
    "NV"=>"NEVADA",
    "NH"=>"NEW HAMPSHIRE",
    "NJ"=>"NEW JERSEY",
    "NM"=>"NEW MEXICO",
    "NY"=>"NEW YORK",
    "NC"=>"NORTH CAROLINA",
    "ND"=>"NORTH DAKOTA",
    "OH"=>"OHIO",
    "OK"=>"OKLAHOMA",
    "OR"=>"OREGON",
    "PW"=>"PALAU",
    "PA"=>"PENNSYLVANIA",
    "PR"=>"PUERTO RICO",
    "RI"=>"RHODE ISLAND",
    "SC"=>"SOUTH CAROLINA",
    "SD"=>"SOUTH DAKOTA",
    "TN"=>"TENNESSEE",
    "TX"=>"TEXAS",
    "UT"=>"UTAH",
    "VT"=>"VERMONT",
    "VA"=>"VIRGINIA",
    "VI"=>"VIRGIN ISLANDS",
    "WA"=>"WASHINGTON",
    "WV"=>"WEST VIRGINIA",
    "WI"=>"WISCONSIN",
    "WY"=>"WYOMING"
    );
    
    
    $temp="Location Name: ";
    $temp.=input_text($element_name."_name",$location_name,50);
    $temp.="\n<br>Street Address: ";
    $temp.=input_text($element_name."_street",$location_street,50);
    $temp.="\n<br><div style='float:left;margin-right:15px;'>City: ";
    $temp.=input_text($element_name."_city",$location_city,30);
    $temp.="</div>\n<div style='float:left;'>State: ";
    $temp.=input_select($element_name."_state",$states[$location_state],$states);
    $temp.="</div>\n<div style='float:left;margin-left:15px;'>Zip: \n";
    $temp.=input_text($element_name."_zip",$location_zip,10);
    $temp.="</div>\n<div class='clear'></div>\n";
    if ($label!='')
    {
        print "<div class='label'>$label</div><div class='input'>";
        if ($explain_text!=''){print "<small>$explain_text</small><br>\n";}
        print $temp;
        print "</div>\n<div class='clear'></div>\n";
    } else {
        return $temp;
    }
}

function make_phone($element_name,$phonenumber,$label='',$explain_text='')
{
    //split phonenumber into pieces
    
    $onblur=" onblur=\"document.getElementById('$element_name').value=document.getElementById('".$element_name."_areacode').value+document.getElementById('".$element_name."_exchange').value+document.getElementById('".$element_name."_main').value+document.getElementById('".$element_name."_extension').value;\"";
    $areacode=substr($phonenumber,0,3);
    $exchange=substr($phonenumber,3,3);
    $main=substr($phonenumber,6,4);
    $extension=substr($phonenumber,10);
    if ($phonenumber=='')
    {
        if (isset($GLOBALS['default_areacode'])){$areacode=$GLOBALS['default_areacode'];}else {$areacode='000';}   
        $exchange='000';
        $main='0000';
        $extension='';
    }
    if ($label!='')
    {
        print "<div class='label'><label for='$element_name'>$label</label></div><div class='input'>\n";
    }
    $temp.="<span style='float:left;'>(<input type='text' name='".$element_name."_areacode' id='".$element_name."_areacode' maxlength=3 size=3 value='$areacode'";
    $temp.=" onkeypress=\"return isNumberKey(event);\"";
    $temp.=$onblur;
    $temp.=" />) </span>\n";
    $temp.=" <span style='float:left;'><input type='text' name='".$element_name."_exchange' id='".$element_name."_exchange' maxlength=3 size=3 value='$exchange'";
    $temp.=" onkeypress=\"return isNumberKey(event);\"";
    $temp.=$onblur;
    $temp.=" /></span>\n";
    $temp.="<span style='float:left;'>-</span><span style='float:left;'><input type='text' name='".$element_name."_main' id='".$element_name."_main' maxlength=4 size=4 value='$main'";
    $temp.=" onkeypress=\"return isNumberKey(event);\"";
    $temp.=$onblur;
    $temp.=" /></span>\n";
    $temp.="<span style='float:left;'> Ext: </span><span style='float:left;'><input type='text' name='".$element_name."_extension' id='".$element_name."_extension' maxlength=5 size=5 value='$extension'";
    $temp.=" onkeypress=\"return isNumberKey(event);\"";
    $temp.=$onblur;
    $temp.=" /></span><span class='clear'></span>\n";
    $temp.="<input type='hidden' name='$element_name' id='$element_name' value='$phonenumber' />\n";
    if ($label!="")
    {
        if ($explain_text!=''){print "<small>$explain_text</small><br />\n";}
        print $temp;
        print "</div>\n";
        print "<div class='clear'></div>\n";
    } else {
       return $temp;
    } 
}


function make_slider($element_name,$value,$label,$explain_text,$min=0,$max=100,$increment=1)
{
    if ($label!='')
    {
        print "<div class='label'><label for='$element_name'>$label</label></div><div class='input'>\n";
    }
    $temp="<input type='text' id='".$element_name."' name='".$element_name."' style='border:0; color:#f6931f; font-weight:bold;' />";
    $temp.="<div id='".$element_name."_slider'></div>";
    ?>
    <script>
    $(function() {
        $( "#<?php echo $element_name; ?>_slider" ).slider({
            range: "min",
            value: <?php echo intval($value);?>,
            min: <?php echo intval($min);?>,
            max: <?php echo intval($max);?>,
            step: <?php echo intval($increment);?>,
            slide: function( event, ui ) {
                $( "#<?php echo $element_name; ?>" ).val(ui.value);
            }
        });
        $( "#<?php echo $element_name; ?>" ).val($("#<?php echo $element_name; ?>_slider").slider( "value" ));
    });
    </script>
    <?php
    if ($label!="")
    {
        if ($explain_text!=''){print "<small>$explain_text</small><br />\n";}
        print $temp;
        print "</div>\n";
        print "<div class='clear'></div>\n";
    } else {
       return $temp;
    } 
}

function make_descriptor($text,$label='Information')
{
    print "<div class='label'>$label</div><div class='input'>\n";
    print $text;
    print "</div>\n";
    print "<div class='clear'></div>\n";
}
?>