<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

$_MIDCOM->auth->require_valid_user();
$exporters = array
(
    'mirror',
    'staging2live',
    'fulldump',
);
$transporters = array
(
    'archive',
    'archive_serial',
    'email',
    'http',
);
$importers = array
(
    'xml',
    'archive',
);
$subscription = new midcom_helper_replicator_subscription();
foreach ($exporters as $name)
{
    $subscription->exporter = $name;
    echo "Creating {$name} exporter, ";
    $exporter = midcom_helper_replicator_exporter::create($subscription);
    echo "done<br/>\n";
}
$subscription->exporter = '';
foreach ($transporters as $name)
{
    $subscription->transporter = $name;
    echo "Creating {$name} transporter, ";
    $transporter = midcom_helper_replicator_transporter::create($subscription);
    echo "done<br/>\n";
}
$subscription->transporter = '';
foreach ($importers as $name)
{
    echo "Creating {$name} importer, ";
    $transporter = midcom_helper_replicator_importer::create($name);
    echo "done<br/>\n";
}


?>