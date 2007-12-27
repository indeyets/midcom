<?php
$_MIDCOM->auth->require_valid_user();
$_MIDCOM->header('content-type: text/xml');

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<APML xmlns=\"http://www.apml.org/apml-0.6\" version=\"0.6\">\n";
echo "<Head>\n";
echo "    <Title>APML for {$_MIDCOM->auth->user->name}</Title>\n";
echo "    <Generator>Midgard " . mgd_version() . "</Generator>\n";
echo "    <DateCreated>" . date('c') . "</DateCreated>\n";
echo "</Head>\n";
echo "<Body>\n";

$qb = net_nemein_attention_concept_dba::new_query_builder();
$qb->add_constraint('person', '=', $_MIDGARD['user']);
$qb->add_order('profile');
$qb->add_order('explicit', 'ASC');

if (isset($_GET['profile']))
{
    $qb->add_constraint('profile', '=', $_GET['profile']);
}

$concepts = $qb->execute();
$lastprofile = 'default';
$inprofile = false;
$inimplicit = false;
$inexplicit = false;
foreach ($concepts as $concept)
{
    if (empty($concept->profile))
    {
        $concept->profile = 'default';
    }
    
    if ($concept->profile != $lastprofile)
    {
        if ($inprofile)
        {
            if ($inimplicit)
            {
                echo "            </Concepts>\n";
                echo "        </ImplicitData>\n";
                $inimplicit = false;
            }
            
            if ($inexplicit)
            {
                echo "            </Concepts>\n";
                echo "        </ExplicitData>\n";
                $inexplicit = false;
            }
            
            echo "    </Profile>\n";
        }
        
        echo "    <Profile name=\"{$concept->profile}\">\n";
        $inprofile = true;
        $lastprofile = $concept->profile;
    }
    
    if (   !$concept->explicit
        && !$inimplicit)
    {
        echo "        <ImplicitData>\n";
        echo "            <Concepts>\n";
        $inimplicit = true;
    }
    
    if (   $concept->explicit
        && $inimplicit)
    {
        echo "            </Concepts>\n";
        echo "        </ImplicitData>\n";
        $inimplicit = false;
    }
    
    if (   $concept->explicit
        && !$inexplicit)
    { 
        echo "        <ExplicitData>\n";
        echo "            <Concepts>\n";
        $inexplicit = true;
    }

    echo "                <Concept key=\"{$concept->concept}\" value=\"{$concept->value}\" from=\"{$concept->source}\" updated=\"" . date('c', $concept->metadata->published) . "\"/>\n";
}

if ($inprofile)
{
    if ($inimplicit)
    {
        echo "            </Concepts>\n";
        echo "        </ImplicitData>\n";
    }
    if ($inexplicit)
    {
        echo "            </Concepts>\n";
        echo "        </ExplicitData>\n";
    }
    echo "    </Profile>\n";
}

echo "</Body>\n";
echo "</APML>\n";
?>