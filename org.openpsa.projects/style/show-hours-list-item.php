<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view = $view_data['hour_entry'];
$view_array = $view->get_array();

$editable = '';
if ($_MIDCOM->auth->can_do('midgard:update', $view->_storage))
{
    $editable = ' editable="true"';
}

echo "<report guid=\"{$view_data['hour_entry_guid']}\"{$editable}>\n";
foreach ($view_array as $key => $value)
{
    if (substr($key, 0, 1) != '_')
    {
        if (   is_array($value)
            && array_key_exists('strdate', $value))
        {
            // Date type
            echo "<{$key}>{$value['strdate']}</{$key}>\n";
        }
        else if ($key == 'hours')
        {
            //Make sure we have dot as decimal separator, the JS will choke otherwise
            $value = str_replace(',', '.', (float)$value);
            echo "<{$key}>{$value}</{$key}>\n";
        }
        elseif (is_bool($value))
        {
            if ($value == true)
            {
                $value = 'yes';
            }
            else
            {
                $value = 'no';
            }
            echo "<{$key}>{$value}</{$key}>\n";
        }
        else
        {
            // String-like type
            echo "<{$key}>{$value}</{$key}>\n";
        }
    }
}
echo "</report>\n";
?>

