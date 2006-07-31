<pre>
<?php

if (! $_MIDCOM->auth->admin)
{
    echo "You need to be an administrator to view this page.\n";
}
else
{
	foreach ($_MIDCOM->componentloader->manifests as $manifest)
	{
		$manifest->get_name_translated();
	    echo "{$manifest->name}: {$manifest->name_translated}";
	    if ($manifest->purecode)
	    {
	        echo ' (PURECODE)';
	    }
	    echo "\n";
	    if (count($manifest->privileges) > 0)
	    {
	        echo "\tRegistered Privileges:\n";
	        foreach ($manifest->privileges as $name => $defaults)
	        {
	            echo "\t\t{$name}\n";
	        }
	    }
	    if (count($manifest->class_definitions))
	    {
	        echo "\tRegistered class definition files:\n";
	        foreach ($manifest->class_definitions as $name)
	        {
	            echo "\t\t{$name}\n";
	        }
	    }
	}
}
?>
</pre>