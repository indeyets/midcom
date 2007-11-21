<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

// This form is usually handled by a remote transporter that Basic authenticates with some local user account
$_MIDCOM->auth->require_valid_user('basic');

// Disable limits as replication files may be large
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);

if (isset($_POST['midcom_helper_replicator_import_xml']))
{
    if (!isset($_POST['midcom_helper_replicator_use_force']))
    {
        $use_force = false;
    }
    else
    {
        $use_force = (boolean)$_POST['midcom_helper_replicator_use_force'];
    }
    $importer = midcom_helper_replicator_importer::create('xml');
    $status = $importer->import($_POST['midcom_helper_replicator_import_xml'], $use_force);
    if (!$status)
    {
        switch (mgd_errno())
        {
            case MGD_ERR_DUPLICATE:
            case MGD_ERR_OBJECT_IMPORTED:
                echo "<p>Your data has been imported earlier.</p>\n";
                $_MIDCOM->finish();
                exit(); 
        
            case MGD_ERR_SITEGROUP_VIOLATION:
            case MGD_ERR_ACCESS_DENIED:
                $error_code = MIDCOM_ERRFORBIDDEN;
                break;
            
            default:
                $error_code = MIDCOM_ERRCRIT;
                break;
        }
    
        $_MIDCOM->generate_error($error_code, "Import failed: {$importer->error}");
        // This will exit()
    }
    
    echo "<p>Your data was imported successfully</p>\n";
    $_MIDCOM->finish();
    exit();
}
?>
<h1>Import XML data</h1>
<form action="" method="post" class="datamanager">
    <label for="midcom_helper_replicator_import_xml">
        <div class="field_text">Importable XML data</div>
        <textarea name="midcom_helper_replicator_import_xml" id="midcom_helper_replicator_import_xml" cols="80" rows="30"></textarea>
    </label>
    <div class="form_toolbar">
        <input type="submit" name="midcom_helper_replicator_import" class="save" value="Import" />
    </div>
</form>