<?php
// Function for calculating file sizes
// Original from http://www.theukwebdesigncompany.com/articles/php-file-manager.php
function net_nemein_downloads_helper_filesize($size) {
  if ($size > 104876) {
    return $return_size=sprintf("%01.2f",$size / 1048576)." Mb";
  } elseif ($size > 1024) {
    return $return_size=sprintf("%01.2f",$size / 1024)." Kb";
  } else {
    return $return_size=$size." Bytes";
  }
}
?>