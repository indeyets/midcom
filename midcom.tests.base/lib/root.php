<?php   
function mgd_execute_udf($variable, $selector)
   {
      $function = mgd_register_filter($selector);
      $function($variable);
   }
   function mgd_register_filter($selector, $function=NULL)
   {
      static $udf = array();

      if (is_null($function)) {
         return $udf[$selector];
      }

      if ($function == '') {
         unset($udf[$selector]);
      } else {
         $udf[$selector] = $function;
      }

      return 1;
   }
?>

