<?php
/*
 * Created on Aug 22, 2005
 *
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$diff   = $data['diff'];
$latest = $data['latest_revision'];
$comment= $data['comment'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(data['view_title']);</h1>

<dl class="no_bergfald_rcs_diff">
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
    echo "    <dd>\n";
    echo nl2br($values['diff']);
    echo "    </dd>\n";
}
?>
</dl>