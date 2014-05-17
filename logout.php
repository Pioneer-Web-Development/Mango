<?php
  session_start();
  unset($_SESSION['mango']);
  session_unset();
  session_destroy();
  setcookie("mango", 0,time()-3600);
  redirect('index.php');
  function redirect($url) {
   if (!headers_sent())
       header('Location: '.$url);
   else {
       echo '<script type="text/javascript">';
       echo 'window.location.href="'.$url.'";';
       echo '</script>';
       echo '<noscript>';
       echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
       echo '</noscript>';
   }
} 
?>
