<?php
$_MIDCOM->auth->require_admin_user();

if (array_key_exists('approve', $_POST))
{
    $meta =& midcom_helper_metadata::retrieve($_POST['approve']);
    if ($meta)
    {
        $meta->approve();
    }
}
?>
<form method="post" action="">
    <input type="submit" name="approveall" value="Approve all" />
</form>
<?php
$site_root = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
$GLOBALS['items'] = 0;
print("<table border='1'><tr><th>type</th><th>name</th><th>operations</th></tr>");
traverse_tree($site_root->id, 0);
print("</table>");

function traverse_tree($id, $depth)
{
    $tocheck = array
    (
        "article" => "topic",
        "topic" => "up"
    );

    foreach ($tocheck as $type => $parent)
    {
        $qb = new midgard_query_builder('midgard_' . $type);
        if ($parent != "")
        {
            $qb->add_constraint($parent, '=', $id); 
        }
        $results = $qb->execute();
        foreach($results as $result)
        {
            if ($type == "topic") 
            {
                traverse_tree($result->id, $depth+1); 
            }

            $meta =& midcom_helper_metadata::retrieve($result->guid);
            if ($meta->is_approved() == true)
            {
                // Skip approved
                continue;
            }
            $GLOBALS['items']++;

            if (   isset($result->title) 
                && $result->title != "") 
            {
                $title = $result->title;
            } 
            else 
            { 
                $title = $result->name; 
            }
            print("<tr><td>" . $type . "</td><td><a href='/midcom-permalink-" . $result->guid . "'>" . $title . "</a></td><td>");
            
            if (array_key_exists('approveall', $_POST))
            {
                $meta->approve();
                print("Approved");
            }
            else
            {
                print('<form method="post" action=""><input type="hidden" name="approve" value="' . $result->guid . '" /><input type="submit" value="Approve" /></form>');
            }
                
            print("</td></tr>");
        }
    }
    
}
?>
<form method="post" action="">
    <input type="submit" name="approveall" value="Approve all" />
</form>