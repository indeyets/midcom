<?php
/**
 * Handler for universalchooser creates
 *
 * @package org.openpsa.contacts
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
    case 'midgard_person':
    case 'org_openpsa_person':
/* REMINDER: JS to call (on correct window, so add parents as neccessary) when person is finally done
echo "midcom_helper_datamanager2_widget_universalchooser_add_option('{$idsuffix}', '{$person->$idfield}', '{$person->$titlefield}');
*/
?>
<p>
TBD: Person creation form (seach was "<?php echo $search; ?>").
</p>
<?php
        break;
    case 'midgard_group':
    case 'org_openpsa_organization':
/* REMINDER: JS to call (on correct window, so add parents as neccessary) when group is finally done
echo "midcom_helper_datamanager2_widget_universalchooser_add_option('{$idsuffix}', '{$group->$idfield}', '{$group->$titlefield}');
*/
?>
<p>
TBD: Group creation form (seach was "<?php echo $search; ?>").
</p>
<?php
        break;
    default:
        echo "<p>Don't know how to create new {$class}</p>\n";
}

?>