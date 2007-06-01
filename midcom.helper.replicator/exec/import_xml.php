<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/
$_MIDCOM->auth->require_valid_user();

echo "<h1>Import data from XML</h1>\n";

if (   array_key_exists('midcom_helper_replicator_import', $_POST)
    && is_uploaded_file($_FILES['midcom_helper_replicator_import_file']['tmp_name']))
{
    $xml_contents = file_get_contents($_FILES['midcom_helper_replicator_import_file']['tmp_name']);
    
    $importer = midcom_helper_replicator_importer::create('xml');
    $status = $importer->import($xml_contents, true);
    if ($status)
    {
        echo "<p>Your data was imported successfully</p>\n";
    }
    else
    {
        echo "<p>Import failed: {$importer->error}</p>\n";
    }
}
?>
<form enctype="multipart/form-data" action="" method="post" class="datamanager">
    <label for="midcom_helper_replicator_import_file">
        <span class="field_text">Importable XML file</span>
        <input type="file" class="fileselector" name="midcom_helper_replicator_import_file" id="midcom_helper_replicator_import_file" />
    </label>
    <div class="form_toolbar">
        <input type="submit" name="midcom_helper_replicator_import" class="save" value="Import" />
    </div>
</form>
