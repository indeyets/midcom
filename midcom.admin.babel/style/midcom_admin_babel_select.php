<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$languages = $data['l10n']->_language_db;
$curlang = $_MIDCOM->i18n->get_current_language();
?>
<h1><?php echo $data['l10n']->get('select language to translate')?></h1>

<table class="midcom_admin_babel_languages">
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('language'); ?></th>
            <th></th>
            <th><?php echo $data['l10n']->get('core component status'); ?></th>
            <th><?php echo $data['l10n']->get('other component status'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($languages as $language => $language_info) 
        {
            $language_name = $language_info['enname'];

            // Calculate status
            $state = midcom_admin_babel_main::calculate_language_status($language);  
            $percentage = round(100 / $state['strings_core']['total'] * $state['strings_core']['translated']);
            $percentage_other = round(100 / $state['strings_other']['total'] * $state['strings_other']['translated']);

            if ($percentage >= 96)
            {
                $status = 'ok';
            }
            elseif ($percentage >= 75)
            {
                $status = 'acceptable';
            }
            else
            {
                $status = 'bad';
            }        
            
            echo "        <tr class=\"{$status}\">\n";
            echo "            <td><a href=\"{$prefix}__ais/l10n/status/{$language}/\">{$language_name}</a></td>\n";
            echo "            <td>{$language_info['localname']}</td>\n";
            echo "            <td title=\"{$state['strings_core']['translated']} / {$state['strings_core']['total']}\">{$percentage}%</td>\n";
            echo "            <td title=\"{$state['strings_other']['translated']} / {$state['strings_other']['total']}\">{$percentage_other}%</td>\n";
            echo "        </tr>\n";
        }
        ?>
    </tbody>
</table>

<p>
    <?php 
    echo $data['l10n']->get('read information from midgard wiki on how to add languages');
    ?>
</p>