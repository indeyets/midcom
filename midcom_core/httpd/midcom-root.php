<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * MidgardRootFile for running MidCOM 3 under Apache
 */
// If code-compat is defined we use that, otherwise we load MidCOM 3
if (mgd_is_element_loaded('code-compat'))
{
    ?><(code-compat)><?php
}
else
{
    // Note: your MidCOM base directory has to be in PHP include_path
    require('midcom_core/midcom.php');
}

// code- elements used for things run before output
?>
<(code-global)>
<(code-init)>
<?php
// TODO: Call controller here

// Start output buffer so we can do templating
ob_start();
?>
<(ROOT)>
<?php

// Read contents from the output buffer
ob_end_flush();
// TODO: Call TAL here

// code-finish can be used for custom caching etc
?>
<(code-finish)>