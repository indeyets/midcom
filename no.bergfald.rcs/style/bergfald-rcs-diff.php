<?php
/*
 * Created on Aug 22, 2005
 *
 */
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$diff   = $request_data['diff'];
$latest = $request_data['latest_revision'];
$comment= $request_data['comment'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(request_data['view_title']);</h1>

<dl>
<?php
foreach ($diff as $attribute => $values)  
{
    if (!array_key_exists('diff', $values)) 
    {
        continue;
    }

    if (!no_bergfald_rcs_handler::is_field_showable($attribute))
    {
        continue;
    }

    echo "<dt>{$attribute}</dt>\n";
    echo "    <dd><pre>\n";
    echo htmlentities($values['diff']);
    echo "    </pre></dd>\n";
}
?>
</dl>