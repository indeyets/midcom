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
    require('midcom_core/framework.php');
}

// code- elements used for things run before output
?>
<(code-global)>
<?php
if (mgd_is_element_loaded('code-init'))
{
    ?><(code-init)><?php
}
else
{
    // Call the controller if available
    $_MIDCOM->process();
}

// Start output buffer so we can do templating
ob_start();

$_MIDCOM->template();

// Read contents from the output buffer and pass to MidCOM rendering
$template_content = ob_get_contents();
ob_end_clean();
$_MIDCOM->display($template_content);

// code-finish can be used for custom caching etc
?>
<(code-finish)>