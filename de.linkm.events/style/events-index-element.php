<?php
global $view;
global $view_id;
global $view_detail;

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

  <tr>
    <td><?php 
         if (isset($view["date"]["timestamp"]) && $view["date"]["timestamp"] > 0) {
           if (isset($view["startdate"]["timestamp"]) && $view["startdate"]["timestamp"] > 0) {
             // If both dates
             echo de_linkm_events_helpers_timelabel($view["startdate"]["timestamp"],$view["date"]["timestamp"]);
           } else {
             // View only the end date
             echo $view["date"]["local_strfulldate"];
           }
         } ?></td>
    <td><span class="event-name"><?php 
         if ($view_detail == true) {
           echo "<a href=\"".$prefix.$view_id.".html\">";
         } 
         echo htmlspecialchars($view["title"]);
         if ($view_detail == true) {
           echo "</a>";
         }
        ?></span></td>
    <td><?php echo htmlspecialchars($view["location"]); ?></td>
  </tr>
