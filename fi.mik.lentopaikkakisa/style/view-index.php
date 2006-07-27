<?php
// Available request keys: controller, schema, schemadb
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h1><?php echo $data['node']->extra; ?></h1>

<h2><?php echo $data['l10n']->get('latest reports'); ?></h2>

<?php 
if (count($data['latest']) > 0)
{
    echo "<table class=\"latest\">\n";
    echo "  <thead>\n";
    echo "    <tr>\n";    
    echo "      <th>".$data['l10n']->get('pilot')."</th>\n";
    echo "      <th>".$data['l10n_midcom']->get('date')."</th>\n";
    echo "      <th>".$data['l10n']->get('aerodrome')."</th>\n";    
    echo "      <th>".$data['l10n']->get('score')."</th>\n";
    echo "    </tr>\n";    
    echo "  </thead>\n";
    echo "  <tbody>\n";
    foreach ($data['latest'] as $report)
    {
        echo "    <tr>\n";
        echo "      <td>{$report->sendername}</td>\n";
        echo "      <td>".strftime('%x', $report->date)."</td>\n";
        echo "      <td>{$report->aerodrome}</td>\n";
        echo "      <td>{$report->score}</td>\n";
        echo "    </tr>\n";
    }
    echo "  </tbody>\n";    
    echo "</table>\n";
}
?>