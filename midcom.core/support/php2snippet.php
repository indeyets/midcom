<?php
/**
 * Generate Midgard snippetdir hierarchies and snippets from a tree of PHP files. Run this in the MidCOM root directory (for example /usr/share/php/midcom)
 *
 * @author Henri Bergius
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
require_once('Console/Getargs.php');
error_reporting(E_ALL);

$opts_config =array(); 
$opts_config['configuration'] = array 
(
    'short' => 'c',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'Name of the midgard configuration file.',
    'default' => 'midgard.conf',
);  
$opts_config['verbose'] = array
(
    'short' => 'v',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'Be verbose',
    'default' => true,
);

$args = Console_Getargs::factory($opts_config);
if (PEAR::isError($args)) 
{
    $header = "Usage: " .basename($GLOBALS['argv'][0])." [options]\n\n" ;
    if ($args->getCode() === CONSOLE_GETARGS_ERROR_USER) 
    {
        echo Console_Getargs::getHelp($opts_config, $header , $args->getMessage())."\n";
    }
    else if ($args->getCode() === CONSOLE_GETARGS_HELP) 
    {
        echo Console_Getargs::getHelp($opts_config, $header)."\n";
    }

    exit;
}

if ($args->isDefined('configuration'))
{
    $configfile = $args->getValue('configuration');
}
else
{
    $configfile = 'midgard';
}

if ($args->isDefined('verbose'))
{
    $GLOBALS['verbose'] = $args->getValue('verbose');
}
else
{
    $GLOBALS['verbose'] = false;
}

if ($GLOBALS['verbose'])
{
    echo "Starting midgard with config file: " . $configfile. "\n";
}
mgd_config_init($configfile);

function php2snippet_import_directory($parent_snippetdir, $path)
{
    if ($GLOBALS['verbose'])
    {
        echo "Processing {$path} ...\n";
    }
    
    $directory = dir($path);
    
    $snippetdir_qb = new midgard_query_builder('midgard_snippetdir');
    $snippetdir_qb->add_constraint('up', '=', $parent_snippetdir);
    $snippetdir_qb->add_constraint('name', '=', basename($path));
    $snippetdirs = $snippetdir_qb->execute();
    if (empty($snippetdirs))
    {
        $snippetdir = new midgard_snippetdir();
        $snippetdir->up = $parent_snippetdir;
        $snippetdir->name = basename($path);
        $stat = $snippetdir->create();
        if (!$stat)
        {
            die("Failed to create snippetdir {$path}, reason " . mgd_errstr());
        }
    }
    else
    {
        // Use first match
        $snippetdir = $snippetdirs[0];
    }
    
    // List contents
    while (false !== ($entry = $directory->read())) 
    {
        $child_path = "{$path}/{$entry}";
        
        if (substr($entry, 0, 1) == '.')
        {
            // Ignore dotfiles
            continue;
        }
        
        // Process
        if (is_dir($child_path))
        {
            php2snippet_import_directory($snippetdir->id, $child_path);
        }
        else
        {
            $path_parts = pathinfo($entry);
            if (   isset($path_parts['extension'])
                && $path_parts['extension'] == 'php')
            {
                if ($GLOBALS['verbose'])
                {
                    echo "- Processing snippet {$path_parts['filename']}\n";
                }
                $snippet_qb = new midgard_query_builder('midgard_snippet');
                $snippet_qb->add_constraint('up', '=', $snippetdir->id);
                $snippet_qb->add_constraint('name', '=', $path_parts['filename']);
                $snippets = $snippet_qb->execute();
                if (empty($snippets))
                {
                    $snippet = new midgard_snippet();
                    $snippet->up = $snippetdir->id;
                    $snippet->name = $path_parts['filename'];
                    $snippet->code = file_get_contents($child_path);
                    $stat = $snippet->create();
                    if (!$stat)
                    {
                        die("Failed to create snippet {$child_path}, reason " . mgd_errstr());
                    }
                }
                else
                {
                    // Use first match
                    $snippet = $snippets[0];
                    $snippet->code = file_get_contents($child_path);
                    $stat = $snippet->update();
                    if (!$stat)
                    {
                        die("Failed to update snippet {$child_path} (#{$snippet->id}), reason " . mgd_errstr());
                    }
                }
            }
        }
    }
    
    // Clean up snippetdirs that end up empty
    $empty = true;
    $child_snippetdir_qb = new midgard_query_builder('midgard_snippetdir');
    $child_snippetdir_qb->add_constraint('up', '=', $snippetdir->id);
    if ($child_snippetdir_qb->count() != 0)
    {
        $empty = false;
    }
    
    $child_snippet_qb = new midgard_query_builder('midgard_snippet');
    $child_snippet_qb->add_constraint('up', '=', $snippetdir->id);
    if ($child_snippet_qb->count() != 0)
    {
        $empty = false;
    }
    
    if ($empty)
    {
        if ($GLOBALS['verbose'])
        {
            echo "- Deleting {$path} (#{$snippetdir->id}) because it is empty...\n";
        }
        $snippetdir->delete();
    }
}

php2snippet_import_directory(0, $_SERVER['PWD']);
?>