<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$page =& $data['wikipage'];
$history =& $data['history'];

$version_string = "<a href=\"{$prefix}__ais/rcs/preview/{$page->guid}/{$data['version']}\">{$data['version']}</a>";

$url = "{$_MIDGARD['self']}midcom-permalink-{$page->guid}";
?>
<tr>
    <td>
        <a rel="note" class="subject url" href="&(url);">&(page.title);</a>
    </td>
    <td>
        &(version_string:h);
    </td>
    <td class="revisor">
        <?php
        if ($history['user'])
        {
            $user = $_MIDCOM->auth->get_user($history['user']);
            if(is_object($user))
            {
                $person = $user->get_storage();
                if (class_exists('org_openpsa_contactwidget'))
                {
                    $user_card = new org_openpsa_contactwidget($person);
                    $person_label = $user_card->show_inline();
                }
                else
                {
                    $person_label = $person->name;
                }
                echo "                    <td>{$person_label}</td>\n";
            }
            elseif ($history['ip'])
            {
                echo "                    <td>{$history['ip']}</td>\n";
            }
            else
            {
                echo "                    <td></td>\n";            
            }
        }
        else
        {
            echo "                    <td></td>\n";            
        }
        ?>
    </td>
    <td>
        <?php
        echo "<abbr class=\"dtposted\" title=\"".gmdate('Y-m-d\TH:i:s\Z', $history['date']). "\">".strftime('%x %X', $history['date'])."</abbr>\n";
        ?>
    </td>
    <td class="message">
        <?php echo substr($history['message'], 0, 40) . '...'; ?>
    </td>
</tr>