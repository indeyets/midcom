<?php
/*
 * Created on Aug 17, 2005
 *
 */
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$history = $request_data['history'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$guid = $request_data['guid'];
$source = $request_data['source'];

echo "<h1>{$request_data['view_title']}</h1>\n";

if (count($history) == 0) 
{
   echo $request_data['l10n']->get('No revisions exist.');
} 
else 
{
    ?>
    <form name="no_bergfald_rcs_history" action="" >
        <table>
            <thead>
                <tr>
                    <th>Revision</th>
                    <th>Date</th>
                    <th>User</th>
                    <th>Lines</th>
                    <th>Message</th>
                    <th></th>                    
                </tr>
            </thead>
            <tbody>
    <?php
    foreach ($history as $rev => $history) 
    {
        echo "                <tr>\n";
        echo "                    <td>{$rev}</td>\n";
        echo "                    <td>".strftime('%x %X', $history['date'])."</td>\n";
        
        if ($history['user'])
        {
            $user = $_MIDCOM->auth->get_user($history['user']);
            $user = $user->get_storage();
            echo "                    <td>{$user->name}</td>\n";            
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
        echo "                    <td><a href='{$prefix}rcs/preview/$source/$guid/$rev'>Preview</td>\n";
        echo "                </tr>\n";
    }
    ?>
            </tbody>
        </table>
    </form>
    <?php
}

?>

