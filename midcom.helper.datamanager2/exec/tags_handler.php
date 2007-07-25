<?php
/**
 * Handler for the tags-widget searches
 *
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: universalchooser_handler.php 11263 2007-07-17 23:26:37Z flack $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

debug_print_r('_REQUEST',  $_REQUEST);

// Common variables
$encoding = 'UTF-8';

// Common headers
$_MIDCOM->cache->content->content_type('text/xml');
$_MIDCOM->header('Content-type: text/xml; charset=' . $encoding);
echo '<?xml version="1.0" encoding="' . $encoding . '" standalone="yes"?>' . "\n";
echo "<response>\n";

if (! isset($_REQUEST["query"]))
{
    echo "    <status>0</status>\n";
    echo "    <errstr>Search term not defined</errstr>\n"; //TODO: Localize message
    echo "</response>\n";

    debug_add("Empty query string. Quitting now.");
    debug_pop();
    $_MIDCOM->finish();
    exit();
}

// $q = strtolower($_REQUEST["q"]);
$query = $_REQUEST["query"];

/*
<results>
 <result>
  <id>home</id>
  <name>Name</name>
  <color>#4c4c4c</color>
 </result>
</results>
*/

// // Convert tradiotional wildcard to SQL wildcard
// $query = str_replace('*', '%', $_REQUEST['query']);
// // Make sure we don't have multiple successive wildcards (performance killer)
// $query = preg_replace('/%+/', '%', $query);

// $items = array(
//     'home' => array( 'name' => 'Home', 'color' => '#000000' ),
//     'family' => array( 'name' => 'Family', 'color' => '#4c4c4c' )
// );

$items = array(
    array( 'id' => 'home', 'name' => 'Home', 'color' => '#628ce4' ),
    array( 'id' => 'family', 'name' => 'Family', 'color' => '#628ce4' )
);

// $res = "";
// foreach ($items as $key => $data) {
//  if (strpos(strtolower($key), $q) !== false) {
//      $res .= "$key|";
//      $i = 1;
//      $data_key_count = count($data);
//      foreach ($data as $key => $value)
//      {
//          $res .= "$value";
//          if ($i < $data_key_count)
//          {
//              $res .= "|";
//          }
//          $i++;
//      }
//      $res .= "\n";
//  }
// }
// debug_add("Found results: {$res}");

$results = array();
$added_keys = array();
foreach ($items as $i => $item)
{
    foreach ($item as $key => $value)
    {
        if (   strpos(strtolower($key), $query) !== false
            || strpos(strtolower($value), $query) !== false)
        {
            if (! array_key_exists($i, $added_keys))
            {
                $results[] = $item;
                $added_keys[$i] = true;
            }
        }        
    }
}

if (empty($results))
{
    echo "    <status>0</status>\n";
    echo "    <errstr>No results</errstr>\n"; //TODO: Localize message
    echo "</response>\n";

    debug_add("No results.");
    debug_pop();
    $_MIDCOM->finish();
    exit();    
}

echo "    <status>1</status>\n";
echo "    <results>\n";
foreach ($results as $i => $result)
{
    echo "        <result>\n";
    echo "            <id>{$result['id']}</id>\n";
    echo "            <name>{$result['name']}</name>\n";
    echo "            <color>{$result['color']}</color>\n";
    echo "        </result>\n";
}
echo "    </results>\n";
echo "</response>\n";

// echo json_encode($results);
debug_print_r('Found results',$results);

debug_pop();
$_MIDCOM->finish();
exit();

?>