<?php
  include("includes/mainmenu.php");
  
  if($_POST)
  {
      $action=$_POST['submit'];
  } else {
      $action=$_GET['action'];
  }
  
  switch($action)
  {
      case "import":
      import();
      break;
      
      case "Import Manifest":
      import_manifest();
      break;
      
      default:
      import();
      break;
  }
  
  function import()
  {
      print "<form method=post enctype='multipart/form-data'>\n";
      make_file('edi','Newsprint Manifest','Please select the newsprint manifest to be imported.');
      make_submit('submit','Import Manifest');
      print "</form>\n";
  }
  
  function import_manifest()
  {
    $file=$_FILES['edi']['tmp_name'];
    $contents=file_get_contents($file);
    $lines=explode("\n",$contents);
    
    $order=array();
    $rolls=array();
    $inshipment=false;
    $inorder=false;
    $indescription=false;
    $initem=false;
    $orderitems=0;
    $roll=0;
    foreach($lines as $line)
    {
        $lineparts=explode("*",$line);
        switch($lineparts[0])
        {
            case "BSN":
                $order['manifest_number']=$lineparts[2];
                $mdate=substr($lineparts[3],0,4).'-'.substr($lineparts[3],4,2).'-'.substr($lineparts[3],6,2).' '.substr($lineparts[4],0,2).':'.substr($lineparts[4],2,2);
                $order['manifest_date']=$mdate;
            break;
            
            case "HL":
                if(trim($lineparts[3])=='S')
                {
                    $inshipment=true;
                    $inorder=false;
                    $indescription=false;
                    $initem=false;
                            
                } elseif(trim($lineparts[3])=='O')
                {
                    $inshipment=false;
                    $inorder=true;
                    $indescription=false;
                    $initem=false;
    
                } elseif(trim($lineparts[3])=='D')
                {
                    $inshipment=false;
                    $inorder=false;
                    $indescription=true;
                    $initem=false;
                    $orderitems++;
                    $roll=0;
                } elseif(trim($lineparts[3])=='I')
                {
                    $roll++;
                    $inshipment=false;
                    $inorder=false;
                    $indescription=false;
                    $initem=true;
                }
             break;
             
             case "N1":
                if($lineparts[1]=='SO')
                {
                    $order['sold_to']=trim($lineparts[2]);
                }elseif($lineparts[1]=='ST')
                {
                    $order['ship_to']=trim($lineparts[2]);
                }elseif($lineparts[1]=='MP')
                {
                    $order['vendor']=trim($lineparts[2]);
                }
             break;
             
             case "MEA":
                if($inshipment)
                {
                    if($lineparts[1]=='CT' && $lineparts[4]=='RL')
                    {
                        $order['total_packs']=trim($lineparts[3]);    
                    }elseif($lineparts[1]=='CT' && $lineparts[4]=='PK')
                    {
                        $order['total_rolls']=trim($lineparts[3]);
                    } elseif($lineparts[1]=='WT' && $lineparts[2]=='G')
                    {
                        $order['total_gross']=trim($lineparts[3]);
                        $order['total_gross_units']=trim($lineparts[4]);    
                    }   
                }elseif($inorder)
                {
                    if($lineparts[1]=='CT' && $lineparts[4]=='RL')
                    {
                        $order['order_packs']=trim($lineparts[3]);    
                    }elseif($lineparts[1]=='CT' && $lineparts[4]=='PK')
                    {
                        $order['order_rolls']=trim($lineparts[3]);
                    } elseif($lineparts[1]=='WT' && $lineparts[2]=='G')
                    {
                        $order['order_gross']=$lineparts[3];
                        $order['order_gross_units']=trim($lineparts[4]);    
                    }
                }elseif($indescription)
                {
                    if($lineparts[1]=='CT' && $lineparts[4]=='RL')
                    {
                        $order['order_item_'.$orderitems]['item_packs']=trim($lineparts[3]);    
                    }elseif($lineparts[1]=='CT' && $lineparts[4]=='PK')
                    {
                        $order['order_item_'.$orderitems]['item_rolls']=trim($lineparts[3]);
                    } elseif($lineparts[1]=='WT' && $lineparts[2]=='G')
                    {
                        $order['order_item_'.$orderitems]['item_gross']=trim($lineparts[3]);
                        $order['order_item_'.$orderitems]['item_gross_units']=trim($lineparts[4]);    
                    } elseif($lineparts[1]=='WT' && $lineparts[2]=='BW')
                    {
                        $order['order_item_'.$orderitems]['item_basis_weight']=trim($lineparts[3]);    
                        $order['order_item_'.$orderitems]['item_basis_weight_unit']=trim($lineparts[4]);    
                    }
                }elseif($initem)
                {
                    if($lineparts[2]=='G')
                    {
                       $order['order_item_'.$orderitems]['rolls'][$roll]['roll_weight']=trim($lineparts[3]); 
                       $order['order_item_'.$orderitems]['rolls'][$roll]['roll_weight_unit']=trim($lineparts[4]); 
                    }
                }
             
             break;
             
             case "LIN":
                if($indescription)
                {
                   $order['order_item_'.$orderitems]['grade_code']=$lineparts[3];
                   $order['order_item_'.$orderitems]['grade_name']=$lineparts[5];
                   $order['order_item_'.$orderitems]['grade_color']=$lineparts[7];
                } elseif($initem)
                {
                    if($lineparts[2]=='PG')
                    {
                        $order['order_item_'.$orderitems]['rolls'][$roll]['roll_tag']=trim($lineparts[5]);
                    } elseif($lineparts[2]=='RO')
                    {
                        $order['order_item_'.$orderitems]['rolls'][$roll]['roll_tag']=trim($lineparts[3]);
                    } 
                }
             break;
             
             case "PO4":
                 $order['order_item_'.$orderitems]['rolls_per_pack']=trim($lineparts[1]);
                 $order['order_item_'.$orderitems]['roll_width']=trim($lineparts[2]);
                 $order['order_item_'.$orderitems]['roll_width_units']=trim($lineparts[3]);
                 $order['order_item_'.$orderitems]['roll_diameter']=trim($lineparts[12]);
                 $order['order_item_'.$orderitems]['roll_diameter_units']=trim($lineparts[13]);
             break;
                
        }    
    }
    
    print "<pre>";
    print_r($order);
    print "</pre>\n";
  
  }
  footer();
?>
