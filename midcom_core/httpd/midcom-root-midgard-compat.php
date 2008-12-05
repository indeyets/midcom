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
    $_MIDCOM = new midcom_core_midcom('midgard');
}

// code- elements used for things run before output
?>
<(code-global)>
<?php
if (mgd_is_element_loaded('code-init'))
{
    ?><(code-init)><?php
}
elseif (isset($_MIDCOM))
{
    // Call the controller if available
    $_MIDCOM->process();
}

if (isset($_MIDCOM))
{
    $_MIDCOM->templating->template();

    // Read contents from the output buffer and pass to MidCOM rendering
    $_MIDCOM->templating->display();
}
else
{
    ?><(ROOT)><?php
}
// code-finish can be used for custom caching etc
?>
<(code-finish)>