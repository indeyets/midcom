<?php
/**
 * Handler for the universalchooser searches
 *
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
//debug_print_r('_REQUEST',  $_REQUEST);

// Common variables
$encoding = 'UTF-8';

// Common headers
$_MIDCOM->cache->content->content_type('text/xml');
$_MIDCOM->header('Content-type: text/xml; charset=' . $encoding);
echo '<?xml version="1.0" encoding="' . $encoding . '" standalone="yes"?>' . "\n";
echo "<response>\n";

// Make sure we have search term
if (!isset($_REQUEST['search']))
{
    echo "    <status>0</status>\n";
    echo "    <errstr>Search term not defined</errstr>\n";
    echo "</response>\n";
    $_MIDCOM->finish();
    exit();
}
$search = str_replace('*', '%', $_REQUEST['search']);

// Get local copies of other variables from request
$map = array('component', 'class', 'titlefield', 'idfield', 'searchfields', 'constraints', 'orders');
foreach ($map as $varname)
{
    if (isset($_REQUEST[$varname]))
    {
        $$varname = $_REQUEST[$varname];
    }
    else
    {
        $$varname = false;
    }
}

// Load component if required
if (!class_exists($class))
{
    $_MIDCOM->componentloader->load($component);
}
// Could not get required class defined, abort
if (!class_exists($class))
{
    echo "    <status>0</status>\n";
    echo "    <errstr>Class {$class} could not be loaded</errstr>\n";
    echo "</response>\n";
    $_MIDCOM->finish();
    exit();
}
// No fields to search by, abort
if (empty($searchfields))
{
    echo "    <status>0</status>\n";
    echo "    <errstr>No fields to search for defined</errstr>\n";
    echo "</response>\n";
    $_MIDCOM->finish();
    exit();
}

// idfield or titlefield empty, abort
if (   empty($titlefield)
    || empty($idfield))
{
    echo "    <status>0</status>\n";
    echo "    <errstr>titlefield or idfield not defined</errstr>\n";
    echo "</response>\n";
    $_MIDCOM->finish();
    exit();
}


$qb = call_user_func(array($class, 'new_query_builder'));
if (is_array($constraints))
{
    foreach ($constraints as $data)
    {
        if (   !isset($data['field'])
            || !isset($data['op'])
            || !isset($data['value']))
        {
            debug_add('Constraint is not fully defined, skipping', MIDCOM_LOG_WARN);
            continue;
        }
        debug_add("adding constraint: {$data['field']} {$data['op']} '{$data['value']}'");
        $qb->add_constraint($data['field'], $data['op'], $data['value']);
    }
}

$qb->begin_group('OR');
foreach ($searchfields as $field)
{
    debug_add("adding search (ORed) constraint: {$field} LIKE '{$search}'");
    $qb->add_constraint($field, 'LIKE', $search);
}
$qb->end_group();

if (is_array($orders))
{
    foreach ($orders as $data)
    {
        foreach($data as $field => $order)
        {
            debug_add("adding order: {$field}, {$order}");
            $qb->add_order($field, $order);
        }
    }
}

$results = $qb->execute();
if ($results === false)
{
    echo "    <status>0</status>\n";
    echo "    <errstr>Error when executing QB</errstr>\n";
    echo "</response>\n";
    $_MIDCOM->finish();
    exit();
}

echo "    <status>1</status>\n";
echo "    <errstr></errstr>\n";
//echo "    <errstr>All OK</errstr>\n";

echo "    <results>\n";
foreach ($results as $object)
{
    // Silence to avoid notices breaking the XML in case of nonexisten field
    $id = @$object->$idfield;
    $title = @$object->$titlefield;
    debug_add("adding result: id={$id} title='{$title}'");
    echo "      <line>\n";
    echo "          <id>{$id}</id>\n";
    echo "          <title>{$title}</title>\n";
    echo "      </line>\n";
}
echo "    </results>\n";

echo "</response>\n";
?>