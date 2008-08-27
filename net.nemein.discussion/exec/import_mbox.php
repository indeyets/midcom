<?php
if (!class_exists('net_nemein_discussion_email_importer'))
{
    require(MIDCOM_ROOT . '/net/nemein/discussion/helper_email_import.php');
}
$_MIDCOM->auth->require_admin_user();

//Disable limits
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);

/*
echo "DEBUG: _FILES<pre>\n";
print_r($_FILES);
echo "</pre>\n";
*/

if (   !isset($_FILES['mboxfile'])
    || !is_array($_FILES['mboxfile'])
    || !isset($_FILES['mboxfile']['tmp_name'])
    || !file_exists($_FILES['mboxfile']['tmp_name'])
    )
{
?>
<h1>Upload file</h1>
<form method="post" enctype="multipart/form-data">
    Unix mbox format: <input type="file" name="mboxfile" /> <br/>
    Topic to import to: 
    <select name="import_to_topic">
<?php
    $qb = midcom_db_topic::new_query_builder();
    $qb->add_constraint('component', '=', 'net.nemein.discussion');
    $nap = new midcom_helper_nav();
    $topics = $qb->execute();
    foreach ($topics as $topic)
    {
        $node = $nap->get_node($topic->id);
        echo "        <option value='{$topic->id}'>{$topic->extra} ({$_MIDGARD['prefix']}/{$node[MIDCOM_NAV_RELATIVEURL]})</option>\n";
    }
?>
    </select><br/>
    Strict parent checks:
    <select name="strict_parent">
        <option value="1">Yes</option>
        <option value="0">No</option>
    </select><br/>
    Use force:
    <select name="use_force">
        <option value="0">No</option>
        <option value="1">Yes</option>
    </select><br/>
    <input type="submit" name="post" value="Upload">
</form>
<?php
    return;
    // This will exit the handler
}

// Get mbox data (normalize newlines while at it)
$mbox_data = preg_replace("/\n\r|\r\n|\r/", "\n", file_get_contents($_FILES['mboxfile']['tmp_name']));
// Split at markers
$mails = preg_split("/(^|\n{2})From \w+@\w+.*?[0-9]{2}:[0-9]{2}:[0-9]{2}\s+[0-9]{4}\n/", $mbox_data);
// go through the bodies found
foreach($mails as $key => $mailbody)
{
    if (empty($mailbody))
    {
        echo "** Key {$key} has empty body<br/>\n";
        continue;
    }
    /*
    echo "Found in key {$key} body <pre>\n";
    echo htmlentities($mailbody);
    echo "</pre>\n<br/>\n";
    */
    // Decode body
    $importer = new net_nemein_discussion_email_importer();
    $importer->topic = $_POST['import_to_topic'];
    if (!$importer->parse($mailbody))
    {
        echo "** Error parsing key {$key}<br/>\n";
        continue;
    }

    $mail =& $importer->parsed;

    /*
    echo "DEBUG: Key {$key}, decoded headers: <pre>\n";
    echo htmlentities(sprint_r($mail->headers));
    echo "</pre>\n and body:<pre>\n";
    echo htmlentities($mail->body);
    echo "</pre>\n<br/>\n";
    */

    // ** Mail decoded, start actual import logic
    if (!$importer->import((boolean)$_POST['strict_parent'], (boolean)$_POST['use_force']))
    {
        echo "** Error importing key {$key} ({$importer->parsed->subject}), see debug log for details<br>\n";
        continue;
    }
    echo "Key {$key} ({$importer->parsed->subject}) imported<br>\n";
}

?>