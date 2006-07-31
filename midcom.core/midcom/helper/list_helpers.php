<?
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:list_helpers.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/*
 * Helper functions.[B 
 */


/** midcom_helper_datamanager_list_styles get an array of the styles bellow
 * an id.
 * @param int  id - id of root style. Null if root.
 *        bool recursive,
 *
 * @return array list of styles with complete paths.
 *
 
 */

function midcom_helper_list_styles($id=null,$recursive = false,
$root = '/', $nameprefix =""){

    $return = array();
    if ($root == '/' && $id != null) {
    /* the path before the current style  */
        $style = mgd_get_style($id);
        if (!$id) return array(); /* maybe raise an error?  */

        do {
            $root = '/' . $style->name  . $root;
            $style = mgd_get_style($style->up);
        } while ($style->up);
    }
    if ($id == null) {
        $styles = mgd_list_styles();
    } else {
        $styles = mgd_list_styles($id);
    }

    if (!$styles) return array();

    while ($styles->fetch()) {
        $return[ $root .  $styles->name] = $nameprefix . $styles->name;
        if ($recursive) {
          $nameprefix .= "_"; /* somehow &nbsp; didn't work :/ */
          $nroot = $root .  $styles->name . '/';
          $return = $return +  midcom_helper_datamanager_list_styles(
$styles->id,$recursive, $nroot, $nameprefix);
        }
    }

    return $return;

}
?>
