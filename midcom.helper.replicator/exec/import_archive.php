<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/
$_MIDCOM->auth->require_valid_user();

echo "<h1>Import data from archive</h1>\n";

if (   array_key_exists('midcom_helper_replicator_import', $_POST)
    && is_uploaded_file($_FILES['midcom_helper_replicator_import_archive']['tmp_name']))
{
    // Rename tmp file to have proper extension
    $new_tmp_name = $_FILES['midcom_helper_replicator_import_archive']['tmp_name'];
    if (preg_match('/\.(zip|tar(\.gz|\.bz2)?|tgz)$/', strtolower($_FILES['midcom_helper_replicator_import_archive']['name']), $extension_matches))
    {
        $new_tmp_name = "{$_FILES['midcom_helper_replicator_import_archive']['tmp_name']}.{$extension_matches[1]}";
        $mv_cmd = "mv -f {$_FILES['midcom_helper_replicator_import_archive']['tmp_name']} {$new_tmp_name}";
        exec($mv_cmd, $output, $ret);
        if ($ret != 0)
        {
            // Move failed
            $msg = "failed to execute '{$mv_cmd}'";
            echo "<p>Failed to import, reason: {$msg}</p>";
            return ;
        }
    }

    $importer = midcom_helper_replicator_importer::create('archive');
    $status = $importer->import($new_tmp_name, true);
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
    <label for="midcom_helper_replicator_import_archive">
        <span class="field_text">Archive of importable XML files</span>
        <input type="file" class="fileselector" name="midcom_helper_replicator_import_archive" id="midcom_helper_replicator_import_archive" />
    </label>
    <div class="form_toolbar">
        <input type="submit" name="midcom_helper_replicator_import" class="save" value="Import" />
    </div>
</form>
