<?php
/**
 * Handler for universalchooser creates
 *
 * @package net.fernmark.pedigree
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: universalchooser_handler.php 3864 2006-08-23 17:51:28Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
//debug_print_r('_REQUEST',  $_REQUEST);

// Get local copies of variables from request
$map = array('idsuffix', 'class', 'titlefield', 'idfield', 'search');
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

$parent = $class;
do
{
    $baseclass = $parent;
    $parent = get_parent_class($baseclass);
}
while ($parent !== false);

switch($baseclass)
{
    case 'net_fernmark_pedigree_dog':
/* REMINDER: JS to call (on correct window, so add parents as neccessary) when person is finally done
echo "midcom_helper_datamanager2_widget_universalchooser_add_option('{$idsuffix}', '{$dog->$idfield}', '{$dog->$titlefield}');
*/
?>
<p>
TBD: Dog creation form (seach was "<?php echo $search; ?>").
</p>
<?php
        break;
    default:
        echo "<p>Don't know how to create new {$class}</p>\n";
}

?>