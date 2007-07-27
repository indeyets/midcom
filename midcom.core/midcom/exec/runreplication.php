<?php
/**
 * Run replication script
 *
 * This script raises the /tmp/runreplication flag used by the staging2live
 * system.
 *
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:reindex.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
// FIXME: Determine a permission for this
$_MIDCOM->auth->require_admin_user();

if (   $_SERVER['REQUEST_METHOD'] != 'post'
    || !array_key_exists('return_to', $_POST))
{
    $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Only POST requests are allowed here.');
}

if (!$GLOBALS['midcom_config']['staging2live_staging'])
{
    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'This server is not a staging server.');
}

if (touch('/tmp/runreplication'))
{
    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('staging2live', 'midcom'), $_MIDCOM->i18n->get_string('approved changes will be replicated within next minute', 'midcom'), 'ok');
    $_MIDCOM->relocate($_POST['return_to']);
}

$_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create the "/tmp/runreplication" flag file.');
?>