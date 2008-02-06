<?php
require_once 'Console/Getargs.php';

error_reporting(E_ALL);

class export_style {

    var $packagename;
    var $style;
    var $name;
    var $args;
    var $output;
    var $static_output;
    var $static_source;
    var $dumped_files = array();
    var $base_url;
    var $tmp_file_url;
    var $sg;
    var $package_element_paths = array();

    function export_style($shell = true)
    {
        if ($shell)
        {
            $this->get_opts();
        }
    }

    function get_opts()
    {
        $opts_config = array();
        $opts_config['style_path'] = array (
                'short' => 's',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Path of style to be exported',
        );
        $opts_config['template_name'] = array (
                'short' => 't',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'The name of this style',
        );
        $opts_config['output_dir'] = array (
                'short' => 'o',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Path to the directory where to dump the style',
        );
        $opts_config['base_url'] = array (
                'short' => 'b',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Base URL for fetching files',
        );
        $opts_config['static_base'] = array (
                'short' => 'sb',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Path (local) to midcom-static',
        );
        $opts_config['version'] = array (
                'short' => 'v',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Style version number',
        );
        $opts_config['description'] = array (
                'short' => 'd',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Description of the style',
        );
        $opts_config['user'] = array (
                    'short' => 'u',
                    'max'   => 1,
                    'min'   => 1,
                    'desc'  => 'Username to log in with',
                );
        $opts_config['password'] = array (
                    'short' => 'p',
                    'max'   => 1,
                    'min'   => 1,
                    'desc'  => 'password to log in with',
                );
        $opts_config['configuration'] = array (
                'short' => 'c',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Name of the midgard configuration file.',
                'default' => 'midgard',
        );
        
        
        $args = Console_Getargs::factory($opts_config);
        $header = "Usage: " .basename($GLOBALS['argv'][0])." [options]\n\n" ;
        if (PEAR::isError($args))
        {
            if ($args->getCode() === CONSOLE_GETARGS_ERROR_USER)
            {
                echo Console_Getargs::getHelp($opts_config, $header /*, $args->getMessage()*/)."\n";
            }
            else if ($args->getCode() === CONSOLE_GETARGS_HELP)
            {
                echo Console_Getargs::getHelp($opts_config, $header)."\n";
            }
            exit;

        }
        $this->args = $args;
        echo "using config: " . $this->args->getValue('configuration') . "\n";
        mgd_config_init($this->args->getValue('configuration'));
        if (! mgd_auth_midgard($this->args->getValue('user'),$this->args->getValue('password'),false) ) 
        {
            die("Could not log in. Exiting \n");
        }
    }
    
    function get_style_by_path($path)
    {
        $parts = explode('/', $path);
        $up = 0;
        $style = false;
        foreach ($parts as $name)
        {
            if (empty($name))
            {
                continue;
            }
            $qb = new midgard_query_builder('midgard_style');
            $qb->add_constraint('name', '=', $name);
            $qb->add_constraint('up', '=', $up);
            $qb->add_order('name');
            //$qb->add_constraint('sitegroup', '<>', '');
            //mgd_debug_start();
            $substyles = $qb->execute();
            //mgd_debug_stop();
            if (empty($substyles))
            {
                return false;
            }
            $style = $substyles[0];
            $up = $style->id;
        }
        return $style;
    }
    
    function run()
    {
        $dir =  $this->args->getValue('output_dir');
        if (!is_dir($dir))
        {
            die("$dir is not a directory.\n");
        }
        $this->output = $dir;
        $this->static_output = $this->output . '/static';
        
        $stylepath = $this->args->getValue('style_path');
        $style = $this->get_style_by_path($stylepath);
        if (!is_object($style))
        {
            die("Cannot get_style_by_path('{$stylepath}'), errstr: " . mgd_errstr() . ".\n");
        }
        $this->style = $style->id;
        $this->sg = $style->sitegroup;
        
        $name = $this->args->getValue('template_name');
        if (empty($name))
        {
            die("Style name not given");
        }
        $this->name = $name;

        $urlbase = $this->args->getValue('base_url');
        if (empty($urlbase))
        {
            echo "WARNING: Base URL not given, cannot dump file references\n";
            if (!$this->prompt('Continue ?', false))
            {
                exit();
            }
        }
        else
        {
            $this->base_url = $urlbase;
        }
        $this->static_source = $this->args->getValue('static_base');

        if (preg_match('/^template_/', $this->name))
        {
            $this->packagename = str_replace(array('-',' '), array('','_'), $this->name);
        }
        else
        {
            $this->packagename = 'style_' . str_replace(array('-',' '), array('','_'), $this->name);
        }

        
        if (!$this->dump_style($this->style))
        {
            return false;
        }
        return $this->make_package();
    }
    
    function recurse($style_id, $style_name)
    {
        // TODO: Sanitize style names better
        $style_name = str_replace(array('/', ' '), array('-', '_'), $style_name);
        $recurser = new export_style(false);
        // TODO-PHP5: Apart from dumped_files, these must be copied as value or things will break
        $recurser->style = $style_id;
        $recurser->output = $this->output . "/{$style_name}";
        $recurser->static_output = $this->static_output;
        $recurser->sg = $this->sg;
        $recurser->name = $this->name;
        $recurser->dumped_files =& $this->dumped_files;
        $recurser->package_element_paths =& $this->package_element_paths;
        $recurser->base_url = $this->base_url;
        $recurser->static_source = $this->static_source;
        $recurser->packagename = $this->packagename;
        if (!is_dir($recurser->output))
        {
            mkdir($recurser->output);
        }
        return $recurser->dump_style($recurser->style);
    }
    
    /**
     * Dumps all elements in style_id and recurses to substyles
     */
    function dump_style($style_id)
    {
        $qb = new midgard_query_builder('midgard_element');
        $qb->add_constraint('style', '=', $style_id);
        $qb->add_order('name');
        $elements = $qb->execute();
        foreach ($elements as $element)
        {
            $ret = $this->dump_element($element);
            if (!$ret)
            {
                // Report error
                echo "ERROR: Could not dump element '{$element->name}'\n";
                return false;
            }
        }
        
        $qb2 = new midgard_query_builder('midgard_style');
        $qb2->add_constraint('up', '=', $style_id);
        //mgd_debug_start();
        $substyles = $qb2->execute();
        //mgd_debug_stop();
        foreach ($substyles as $style)
        {
            $ret = $this->recurse($style->id, $style->name);
            if (!$ret)
            {
                // Report error
                echo "ERROR: Could not dump style #{$style->id} ({$style->name})\n";
                return false;
            }
        }
        return true;
    }
    
    /**
     * Dumps the given element object as element_name.php
     *
     * Also finds image etc references in the element and tries to dump those to static (and then rewrite the reference)
     */
    function dump_element($element)
    {
        // Normalize linefeeds
        $element->value = preg_replace("/\n\r|\r\n|\r/","\n", $element->value);
        $files = $this->find_file_references($element->value);
        if ($files === false)
        {
            // error occurred, what to do ??
        }
        if (is_array($files))
        {
            foreach ($files as $uri => $normalized)
            {
                $name = $this->dump_file($normalized);
                if (!$name)
                {
                    // file dump failure
                    continue;
                }
                $newuri = '<' . '?php echo MIDCOM_STATIC_URL; ?' . '>' . "/{$this->packagename}/{$name}";
                $element->value = str_replace($uri, $newuri, $element->value);
            }
        }
        $path = $this->output . "/{$element->name}.php";
        $fp = fopen($path, 'w');
        if (!$fp)
        {
            // Report error
            echo "ERROR: Could not open {$path} for writing\n";
            return false;
        }
        fwrite($fp, $element->value, strlen($element->value));
        fclose($fp);
        echo "INFO: Dumped element '{$element->name}' to {$path}\n";
        $this->package_element_paths[] = $path;
        return true;
    }
    
    /**
     * Dumps a given uri to static, returns the name of the dumped file on success
     */
    function dump_file($uri)
    {
        if (!is_dir($this->static_output))
        {
            mkdir($this->static_output);
        }
        // Check if we have already dumped this file
        if (array_key_exists($uri, $this->dumped_files))
        {
            return $this->dumped_files[$uri];
        }
        $fp = fopen($uri, 'r');
        if (!$fp)
        {
            // report error ?
            echo "ERROR: Could not open {$uri}\n";
            return false;
        }
        $data = null;
        while (!feof($fp))
        {
            $data .= fread($fp, 4096);
        }
        fclose($fp);
        $origname = preg_replace('/^.*\//', '', $uri);
        // TODO-PHP5: This also must be a copy!
        $name = $origname;
        $path = $this->static_output . "/{$name}";
        // If we dump by http, do not overwrite old files
        $extpart = false;
        if (!preg_match('/(.*)\.(.*)$/', $origname, $matches_ext))
        {
            // Could not figure name vs extension (probably invalid file name)
            return false;
        }
        /*
        echo "DEBUG: matches_ext\n===\n";
        print_r($matches_ext);
        echo "===\n";
        */
        $namepart = $matches_ext[1];
        $extpart = $matches_ext[2];
        if (!strstr($uri, $this->static_source))
        {
            /*
            $count = 1;
            while (file_exists($path))
            {
                if ($count > 15)
                {
                    // Report error
                    echo "ERROR: Too many retries for dumping file {$origname}\n";
                    return false;
                }
                $count++;
                $name = "{$namepart}_{$count}.{$extpart}";
                $path = $this->static_output . "/{$name}";
            }
            */
            $path = $this->static_output . "/{$namepart}.{$extpart}";
        }
        if (strtolower($extpart) == 'css')
        {
            $this->tmp_file_url = $uri;
            // The file is a stylesheet, look inside for more file references...
            $css_files = $this->find_file_references($data);
            foreach ($css_files as $uri => $normalized)
            {
                $css_file_name = $this->dump_file($normalized);
                if (!$css_file_name)
                {
                    // file dump failure
                    continue;
                }
                $newuri = "{$css_file_name}";
                $element->value = str_replace($uri, $newuri, $data);
            }
            $this->tmp_file_url = false;
        }
        $fp = fopen($path, 'w');
        if (!$fp)
        {
            // Report error
            return false;
        }
        fwrite($fp, $data, strlen($data));
        fclose($fp);
        echo "INFO: Dumped file {$path}\n";
        $this->dumped_files[$uri] = $name;
        return $this->dumped_files[$uri];
    }
    
    /**
     * Walks trough $this->output directory tree and creates a package.xml based on findings
     */
    function make_package()
    {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $version = $this->args->getValue('version');
        if (empty($version))
        {
            $version = '1.0.' . time();
        }
        $description = $this->args->getValue('description');
        if (empty($description))
        {
            $path = $this->args->getValue('style_path');
            $description = "Style '{$path}' exported with style_export.php";
        }
        $package_xml = <<<EOF
<package packagerversion="1.4.5" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd">
    <name>{$this->packagename}</name>
    <channel>pear.midcom-project.org</channel>
    <summary>Style {$this->name}</summary>
    <description>
        {$description}
    </description>
    <lead>
        <name>Unknown</name>
        <user>anonymous</user>
        <email>unknown</email>
        <active>yes</active>
    </lead>
    <date>{$date}</date>
    <time>{$time}</time>
    <version>
        <release>{$version}</release>
        <api>1.0.0</api>
    </version>
    <stability>
        <release>stable</release>
        <api>stable</api>
    </stability>
    <license>Unknown</license>
    <notes>
        Exported with style_export.php
    </notes>
    <contents>
        <dir name="/">

EOF;
        foreach ($this->package_element_paths as $path)
        {
            $baseinstalldir = dirname(str_replace($this->output, "/{$this->name}", $path));
            $name = str_replace($this->output . '/', '', $path);
            $package_xml .= "            <file name=\"{$name}\" baseinstalldir=\"{$baseinstalldir}\" role=\"midgardelement\" />\n";
        }
        // TODO: Find also old files in static (first use wget to make sure we have them all, then opendir/readdir to list them)
        foreach ($this->dumped_files as $name)
        {
            $package_xml .= "            <file name=\"static/{$name}\" role=\"web\" install-as=\"{$name}\" baseinstalldir=\"/{$this->packagename}\" />\n";
        }
        $package_xml .= <<<EOF
        </dir>
    </contents>
    <dependencies>
        <required>
            <php>
                <min>4.3.0</min>
            </php>
            <pearinstaller>
                <min>1.4.0</min>
            </pearinstaller>
            <package>
                <name>Role_Midgardelement</name>
                <channel>pear.midcom-project.org</channel>
            </package>
            <package>
                <name>Role_Web</name>
                <channel>pearified.com</channel>
            </package>
            <!--<extension>
                <name>midgard</name>
                <min>1.7.4</min>
            </extension>-->
        </required>
    </dependencies>
    <phprelease />
</package>
EOF;
        //echo "DEBUG: package.xml\n===\n{$package_xml}\n===\n";
        $fp = fopen($this->output . '/package.xml', 'w');
        if (!$fp)
        {
            die("Cannot create package.xml\n");
        }
        fwrite($fp, $package_xml, strlen($package_xml));
        fclose($fp);
        return true;
    }

    /**
     * Search for references to files. returns array of found uris on success
     */
    function find_file_references($content)
    {
        if (empty($this->base_url))
        {
            return false;
        }
        $found = array();
        
        $merged = array();
        $regex_src = "/(src|<link.*?href)=(['\"])(.*?)\\2/i";
        preg_match_all($regex_src, $content, $matches_src);
        foreach ($matches_src[3] as $uri)
        {
            $merged[] = $uri;
        }
        $regex_url = "/(url)\s*\(([\"'´])?(.*?)\\2?\)/i";
        preg_match_all($regex_url, $content, $matches_url);
        foreach ($matches_url[3] as $uri)
        {
            $merged[] = $uri;
        }
        
        foreach ($merged as $uri)
        {
            $normalized = false;
            //echo "DEBUG: found uri '{$uri}'\n";
            if (array_key_exists($uri, $found))
            {
                // Already there, skip
                continue;
            }
            if (   stristr($uri, $this->packagename)
                // UGLY: if url does not start with '/', '.' or 'http' it is considered to be in static (because inside CSS this is the correct way)
                || !preg_match('#^(/|\.|http)#', $uri))
            {
                // Reference to the styles midcom-static
                if (empty($this->static_source))
                {
                    // Cannot create local path
                    continue;
                }
                $normalized = "{$this->static_source}/{$this->packagename}/" . basename($uri);
            }
            else
            {
                if (   stristr($uri, 'midcom-static')
                    || stristr($uri, 'midcom_static')
                    || (   preg_match('/^\.\./', $uri)
                        && stristr($this->tmp_file_url, 'midcom-static'))
                    )
                {
                    // SKip files already in static
                    //echo "DEBUG: uri recognized as static, skipping\n";
                    continue;
                }
            }
            
            // Other manipulation rules ??
            
            // Normalize uri
            if (empty($normalized))
            {
                if (   preg_match('/^\.\./', $uri)
                    && $this->tmp_file_url)
                {
                    $normalized = dirname($this->tmp_file_url) . $uri;
                }
                else if (!preg_match('|^https?://|', $uri))
                {
                    $normalized = $this->base_url . $uri;
                }
                else
                {
                    // No need to normalize
                    $normalized = $uri;
                }
            }
            $found[$uri] = $normalized;
        }
        
        return $found;
    }
        
    /**
     * Ask the user a boolean question
     * Based on this: http://www.phpguru.org/downloads/Console/Console.phps
     * @return boolean false if not set.
     */
    function prompt($question, $default = null)
    {
            if (!is_null($default)) {
                $defaultStr = $default ? '[Yes]/No' : 'Yes/[No]';
            } else {
                $defaultStr = 'Yes/No';
            }
            $fp = fopen('php://stdin', 'r');
            
            while (true) {
                echo $question, " ", $defaultStr, ": ";
                $response = trim(fgets($fp, 8192));
                
                if (!is_null($default) AND $response == '') {
                    return $default;
                }
    
                switch (strtolower($response)) {
                    case 'y':
                    case '1':
                    case 'yes':
                    case 'true':
                        return true;
                    
                    case 'n':
                    case '0':
                    case 'no':
                    case 'false':
                        return false;
                    
                    default:
                        continue;
                }
            }  
    }

}

$runner = new export_style();
$runner->run();

?>