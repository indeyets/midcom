<?php
/**
 * OpenPSA Interface helper.
 * 
 * @package net.nemein.supportview
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

// We must tune down error reporting since OpenPSA TechSupport is NOT E_ALL compatible  
error_reporting(E_ALL ^ E_NOTICE);
//$GLOBALS['midgard'] = mgd_get_midgard();

// Load the libraries
mgd_include_snippet_php("/TechSupport/Init Saving");

if (!function_exists('list_obj_att')) {
    /**
     * @ignore
     */
    function list_obj_att($obj, $iconCols) {
        global $midgard, $fileUrl, $attlist;

        if ($obj->id) {
            $attlist=$obj->listattachments();
        }

        if ($attlist && $attlist->N > 0) {
            midcom_show_style("view-attachment-header");
            while ($attlist->fetch()) {
                $fileUrl = $midgard->self."midcom-serveattachment-$attlist->id/$attlist->name";
                midcom_show_style("view-attachment-item");
            }
            midcom_show_style("view-attachment-footer");
        }
    }
}

error_reporting(E_ALL);
?>
