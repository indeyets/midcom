<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

$_MIDCOM->auth->require_valid_user();
@ini_set('memory_limit', -1);
echo "<p>Starting</p>";
$qm =& midcom_helper_replicator_queuemanager::get();
// Ponder: add some progress indicator to the process method ??
$qm->process_queue();
echo "<p>Done</p>";
?>