<?php
// Available request keys: controller, schema, schemadb
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['l10n']->get('manage reports'); ?></h1>

<?php 
if (count($data['reports']) > 0)
{
    echo "<table>\n";
    echo "  <thead>\n";
    echo "    <tr>\n";    
    echo "      <th>".$data['l10n']->get('pilot')."</th>\n";
    echo "      <th>".$data['l10n_midcom']->get('email')."</th>\n";
    echo "      <th>".$data['l10n_midcom']->get('date')."</th>\n";
    echo "      <th>".$data['l10n_midcom']->get('plane')."</th>\n";
    echo "      <th>".$data['l10n']->get('club')."</th>\n";
    echo "      <th>".$data['l10n']->get('aerodrome')."</th>\n";
    echo "      <th>".$data['l10n']->get('score')."</th>\n";
    echo "      <th></th>\n";
    echo "    </tr>\n";    
    echo "  </thead>\n";
    echo "  <tbody>\n";
    foreach ($data['reports'] as $guid => $report)
    {
        echo "    <tr>\n";
        echo "      <td>{$report['sendername']}</td>\n";
        echo "      <td>{$report['senderemail']}</td>\n";
        echo "      <td>{$report['date']}</td>\n";
        echo "      <td>{$report['plane']}</td>\n";
        echo "      <td>{$report['organization']}</td>\n";
        echo "      <td>{$report['aerodrome']}</td>\n";
        echo "      <td>{$report['score']}</td>\n";
        echo "      <td>";
        if ($data['reports_objects'][$guid]->can_do('midgard:delete'))
        {
            echo "<a href=\"{$prefix}manage/delete/{$guid}.html\">".$data['l10n_midcom']->get('delete')."</a>";
        }
        echo "</td>\n";
        echo "    </tr>\n";
    }
    echo "  </tbody>\n";    
    echo "</table>\n";
}

$data['report_qb']->show_pages();
?>