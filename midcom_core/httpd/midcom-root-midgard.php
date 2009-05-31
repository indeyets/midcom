<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * MidgardRootFile for running MidCOM 3 under Apache
 */
// Load MidCOM 3
// Note: your MidCOM base directory has to be in PHP include_path
require('midcom_core/framework.php');
$_MIDCOM = new midcom_core_midcom('midgard');

// Process the request in order to populate needed data
$_MIDCOM->process();

// Serve the request
$_MIDCOM->serve();

// End
unset($_MIDCOM);
?>