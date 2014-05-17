    $(function() {
        var userid=$('#userid').val();
        $( ".column" ).sortable({
            connectWith: ".column",
            stop: function(event, ui)
            {
                var blockorder1=$("#mainContentHolderCol1").sortable("serialize");
                var blockorder2=$("#mainContentHolderCol2").sortable("serialize");
                var blockorder3=$("#mainContentHolderCol3").sortable("serialize");
                $.ajax({
                      url: "includes/ajax_handlers/updateDashboard.php",
                      global: false,
                      type: "POST",
                      data: ({uid: userid, col1 : blockorder1, col2 : blockorder2, col3 : blockorder3, action:'reorder'}),
                      success: function(msg){
                         if(msg!='')
                         {
                             alert(msg);
                         }
                      }
                   }
                )
            }
        });
       
        $( ".dragBox" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
            .find( ".dashboardHeader" )
                .addClass( "ui-widget-header ui-corner-all" )
                .end()
            .find( ".dashboardBox" );

        $( ".dashboardHeader .ui-icon" ).click(function() {
            $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
            $( this ).parents( ".dragBox:first" ).find(".dashboardBox").toggle();
            var blockid=$(this).closest("div").attr("id");
            var userid=$('#userid').val();
            $.ajax({
                  url: "includes/ajax_handlers/updateDashboard.php",
                  type: "POST",
                  data: ({action:'toggle',id:blockid,uid: userid}),
                  success: function(msg){
                     if(msg!='')
                     {
                         alert(msg);
                     }
                  }
            })
        });

        $( ".column" ).disableSelection();
    });
    
    function showorder()
    {
        var blockorder1=$("#mainContentHolderCol1").sortable("serialize");
        alert(blockorder1);
        var blockorder2=$("#mainContentHolderCol2").sortable("serialize");
        alert(blockorder2);
        var blockorder3=$("#mainContentHolderCol3").sortable("serialize");
        alert(blockorder3);
    }

