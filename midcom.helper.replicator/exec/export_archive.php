<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

$_MIDCOM->auth->require_admin_user();

function midcom_helper_replicator_export_archive_clean_subscription(&$subscription)
{
    $subscription->delete();
    if (method_exists($subscription, 'purge'))
    {
        $subscription->purge();
    }
}

// do temp subscription
$subscription = new midcom_helper_replicator_subscription_dba();
$subscription->title = date('Y-m-d H:i:s') . ' temporary for export_archive.php';
$subscription->status = MIDCOM_REPLICATOR_MANUAL;
$subscription->transporter = 'archive';
$subscription->exporter = 'fulldump';
if (!$subscription->create())
{
    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not create temp subscription, last Midgard error: ' . mgd_errstr());
    // This will exit()
}
$filepath = tempnam('/tmp', 'midcom_helper_replicator_export_archive_');
// Remove the file created by tempnam
unlink($filepath);
// Append extension
$filepath .= '.tar.bz2';
$subscription->set_parameter('midcom_helper_replicator_transporter_archive', 'filepath', $filepath);
$subscription->set_parameter('midcom_helper_replicator_transporter_archive', 'archive_type', 'tar.bz2');
$subscription->set_parameter('midcom_helper_replicator_transporter_archive', 'file_overwrite', '1');

// export fulldump
$exporter =& midcom_helper_replicator_exporter::create($subscription);
if (empty($exporter))
{
    midcom_helper_replicator_export_archive_clean_subscription($subscription);
    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'could not instantiate exporter, see debug log for details');
    // This will exit()
}
$transporter =& $exporter->transporter;
if (!$exporter->dump_all())
{
    midcom_helper_replicator_export_archive_clean_subscription($subscription);
    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'exporter->dump_all() returned failure, see debug log for details');
    // This will exit()
}
if (!$transporter->create_archive())
{
    midcom_helper_replicator_export_archive_clean_subscription($subscription);
    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "transporter->create_archive() returned failure, see debug log for details");
    // This will exit()
}

// delete (&& purge) subscription
midcom_helper_replicator_export_archive_clean_subscription($subscription);

// send the file

// NOTE: if archive format is changed the filename generation needs to be changed as well
$new_name = midcom_generate_urlname_from_string("{{$_SERVER['SERVER_NAME']}}{$_MIDGARD['self']}") . '_' . date('Ymd_Hi') . '.tar.bz2';
// Use this call to be future-proof in case format changes/becomes selectable
$mimetype = `file -b -i '$filepath'`;

// necessary headers
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // some day in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header("Content-type: {$mimetype}");
header("Content-Disposition: attachment; filename={$new_name}");
header('Content-Length: ' . filesize($filepath));
header('Content-Transfer-Encoding: binary');
// Disable buffering to avoid wasting memory
while(@ob_end_flush());
readfile($filepath);
// clean up the file
unlink($filepath);
flush();
//Restart OB to keep midcom happy
ob_start();

?>