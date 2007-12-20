<?php
/*
 * Created on Aug 17, 2005
 *
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$history = $data['history'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$guid = $data['guid'];

echo "<h1>{$data['view_title']}</h1>\n";

if (count($history) == 0) 
{
   echo $data['l10n']->get('No revisions exist.');
   return;
} 
?>
<div class="rcs_navigation">
<?php
echo $data['rcs_toolbar']->render();
?>
</div>
<form method="get" action="&(_MIDGARD['uri']);">
    <div>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th><?php echo $data['l10n']->get('revision'); ?></th>
                    <th><?php echo $data['l10n']->get('date'); ?></th>
                    <th><?php echo $data['l10n']->get('user'); ?></th>
                    <th><?php echo $data['l10n']->get('lines'); ?></th>
                    <th><?php echo $data['l10n']->get('message'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($history as $rev => $history) 
            {
                echo "                <tr>\n";
                echo "                    <td><input type=\"checkbox\" name=\"compare[]\" value=\"{$rev}\" />\n";
                echo "                    <td><a href='{$prefix}__mfa/asgard/object/rcs/preview/$guid/$rev'>{$rev}</a></td>\n";
                echo "                    <td>".strftime('%x %X Z', $history['date'])."</td>\n";
                
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
                elseif ($history['ip'])
                {
                    echo "                    <td>{$history['ip']}</td>\n";
                }
                else
                {
                    echo "                    <td></td>\n";            
                }
                echo "                    <td>{$history['lines']}</td>\n";                       
                echo "                    <td>{$history['message']}</td>\n";
                echo "                    <td></td>\n";
                echo "                </tr>\n";
            }
            ?>
            </tbody>
        </table>
        <input type="submit" name="f_compare" value="<?php echo $data['l10n']->get('compare'); ?>" />
    </div>
</form>