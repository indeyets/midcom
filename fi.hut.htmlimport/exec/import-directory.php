<?php
/**
 * HTML files -> n.n.static folder/article importer
 * Based on n.n.static/exec/import-mscms.php
 */
$_MIDCOM->auth->require_admin_user();
// Get us to full live mode
$_MIDCOM->cache->content->enable_live_mode();

$importer = new fi_hut_htmlimport_importer();
if (isset($_POST['ruleset']))
{
    $importer->select_ruleset($_POST['ruleset']);
}

@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);

while(@ob_end_flush());

?>
<h1>Import content from directory of HTML files</h1>
<?php
if (array_key_exists('directory', $_POST))
{
    $folder = $importer->list_files($_POST['directory']);
/*
    echo "DEBUG: Files to be imported<pre>\n";
    ob_start();
    var_dump($folder);
    $folder_r = ob_get_contents();
    ob_end_clean();
    echo htmlentities($folder_r);
    echo "</pre>\n";
    flush();
*/
    $importer->import_folder($folder, $_POST['parent']);
}
else
{
    ?>
    <form method="post">
        <label>
            <span>Directory path of the files to import</span>
            <input type="text" name="directory" />
        </label>
        <br/>
        <label>
            <span>Import under folder</span>
            <select name="parent">
                <?php
                $root_topic_obj = new midcom_db_topic($GLOBALS['midcom_config']['midcom_root_topic_guid']);
                $qb = midcom_db_topic::new_query_builder();
                $qb->add_constraint('up', 'INTREE', $root_topic_obj->id);
                $qb->add_constraint('component', '=', 'net.nehmer.static');
                $folders = $qb->execute();
                foreach ($folders as $folder)
                {
                    // TODO: use nap to show path
                    echo "    <option value=\"{$folder->id}\">{$folder->name} ({$folder->extra})</option>\n";
                }
                ?>
            </select>
        </label>
        <br/>
        <label>
            <span>Ruleset to use</span>
            <select name="ruleset">
                <?php
                foreach ($importer->rulesets as $name => $ruleset)
                {
                    echo "    <option value=\"{$name}\">{$name}</option>\n";
                }
                ?>
            </select>
        </label>
        <br/>
        <div class="form_toolbar">
            <input type="submit" value="Import" />
        </div>
    </form>
    <?php
}

// restart ob
ob_start();
?>