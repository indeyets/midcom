<?php
$_MIDCOM->auth->require_valid_user();
?>
<html>
    <head>
        <style>
            ul.cloud li
            {
                list-style: none;
                display: inline;
                margin-right: 1em;
                font-size: smaller;
            }
            ul.cloud li em
            {
                font-size: larger;
                font-style: normal;
            }
        </style>
    </head>
    <body>
<?php
$qb = net_nemein_attention_concept_dba::new_query_builder();
$qb->add_constraint('person', '=', $_MIDGARD['user']);
//$qb->add_constraint('explicit', '=', false);

if (isset($_GET['profile']))
{
    $qb->add_constraint('profile', '=', $_GET['profile']);
}

$concepts = $qb->execute();
echo "<ul class=\"cloud\">\n";
foreach ($concepts as $concept)
{
    $key = $concept->concept;
    
    $vals = (int) ($concept->value * 100) / 20;
    while ($vals > 0)
    {
        $vals--;
        $key = "<em>{$key}</em>";
    }

    echo "    <li title=\"{$concept->value} score for {$concept->concept}\">{$key}</li>\n";
}
echo "</ul>\n";
?>
    </body>
</html>