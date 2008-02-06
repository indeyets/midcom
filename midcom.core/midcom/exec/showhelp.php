<?php
//TODO: use header & footers
//TODO: sanitize parameters
//TODO: add bells and whistles
function showhelptopics($component)
{
    print("This is the barebone version of <b>Help Component</b> showing the help topics of <i>$component</i>");
    print("<br>");

    $component_dir = str_replace('.', '/', $component);
    $path = MIDCOM_ROOT . "/{$component_dir}/documentation/."; 

    $dh = opendir($path);
    while (($file = readdir($dh))) 
    {
        if (fnmatch("*.txt", $file))
        {
            $href = "/midcom-exec-midcom/showhelp.php?c=$component&f=$file";
            print("<a href=$href>$file<a/><br>");
        }
    }
    closedir($dh);
}

function showhelpcontent($component, $file)
{
    $_MIDCOM->load_library('net.nehmer.markdown');
    $marker = new net_nehmer_markdown_markdown;

    $component_dir = str_replace('.', '/', $component);
    $path = MIDCOM_ROOT . "/{$component_dir}/documentation/$file";
    $text = file_get_contents($path); 

    print($marker->render($text));
}

print('<?'.'xml version="1.0" encoding="UTF-8"?'.">\n");

$component = $_REQUEST['c'];
if (isset($_REQUEST['f'])) 
    showhelpcontent($component, $_REQUEST['f']);
else 
    showhelptopics($component);

?>