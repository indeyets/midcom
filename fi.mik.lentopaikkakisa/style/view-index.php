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
    echo "      <th>&nbsp;</th>\n";
    echo "      <th>".$data['l10n']->get('aerodrome')."</th>\n";    
    echo "      <th>".$data['l10n']->get('score')."</th>\n";    
    echo "    </tr>\n";    
    echo "  </thead>\n";
    echo "  <tbody>\n";
    foreach ($data['latest'] as $report)
    {
        echo "    <tr>\n";
        $pilot = new org_openpsa_contacts_person_dba($report->pilot);
        echo "      <td>{$pilot->name}</td>\n";
        echo "      <td>".strftime('%x', $report->end)."</td>\n";
        echo "      <td>{$report->origin}</td>\n";
        echo "      <td>{$report->scoreorigin}</td>\n";
        echo "      <td>&mdash;</td>\n";
        echo "      <td>{$report->destination}</td>\n";
        echo "      <td>{$report->scoredestination}</td>\n";
        echo "    </tr>\n";
    }
    echo "  </tbody>\n";    
    echo "</table>\n";
}
?>