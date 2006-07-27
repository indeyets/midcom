<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$categories = Array();

foreach ($data['config']->get('categories') as $key => $name)
{
    if (strpos($name, '|') !== false)
    {
        $parts = explode('|', $name);
        if (! array_key_exists($parts[0], $categories))
        {
            $categories[$parts[0]] = Array();
        }
        $categories[$parts[0]][$key] = $parts[1];
    }
    else
    {
        $categories[$key] = $name;
    }
}
/* Array entry structure:
 *
 * Single entries without subcategories:
 * $key => $name
 *
 * Two-Level entries:
 * $basename => Array ($key => $subname, ...)
 */
?>

<ul>
<?php
foreach ($categories as $key => $value)
{
    echo "<li>";
    if (is_array($value))
    {
        $mainname = $key;
        echo "{$mainname}: <ul>";
        foreach ($value as $key => $subname)
        {
            ?><li><a href="&(prefix);list/&(key);/&(data['mode']);/1.html">&(subname);</a></li><?php
        }
        echo "</ul>";
    }
    else
    {
        ?><a href="&(prefix);list/&(key);/&(data['mode']);/1.html">&(value);</a><?php
    }
    echo "</li>\n";
}
?>
</ul>
