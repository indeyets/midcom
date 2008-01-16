<?php
require_once 'Console/Getargs.php';

error_reporting(E_ALL);

class import_style {

    var $style;
    var $args;

    function import_style() {
        $opts_config = array();
        $opts_config[CONSOLE_GETARGS_PARAMS] = array (
                //'short' => 's',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'the name of the styledir to be imported.',
        );
        $opts_config['configuration'] = array (
                'short' => 'c',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Name of the midgard configuration file.',
                'default' => 'midgard',
        );



        $args = Console_Getargs::factory($opts_config);
        $header = "Usage: " .basename($_SERVER['SCRIPT_NAME'])." [options]\n\n" ;
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
        echo "using config: " . $args->getValue('configuration') . "\n";
        mgd_config_init($args->getValue('configuration'));
    }

    function run () {
        $dir = $this->args->getValue(CONSOLE_GETARGS_PARAMS);
        if (!is_dir($dir)) {
            die("$dir is not a directory.Exiting...\n");
        }

        $name = basename($dir);
        $style_name = "template_{$name}";
        $qb = new midgardQueryBuilder('midgard_style');
        $qb->add_constraint('name', '=', $style_name);
        $qb->add_constraint('up', '=', 0);

        $styles = $qb->execute();

        if (count($styles) == 0)
        {
            // Create missing style template
            $new_style = new midgard_style();
            $new_style->up = 0;
            $new_style->name = $style_name;
            $stat = $new_style->create();
            if (!$stat)
            {
                PEAR::raiseError("Failed to create Midgard style \"{$style_name}\", check config directives in the Midgard conf.d file \"{$init_file}\". Error was " . mgd_errstr());
            }
            $style = new midgard_style();
            $style->get_by_id($new_style->id);
        }
        else
        {
            echo "installing into existing style...\n";
            $style = $styles[0];
        }

        echo "Installing template: " . $style->name ;

        $files = dir($dir);
        $elements = array();
        while (($file = $files->read()) !== false ) {
            if (substr($file,0, 1) == '.' || !is_file("$dir/$file") ) continue;
            $path = pathinfo($file);
            if ($path['extension'] == 'php') {

                $elements[] = str_replace('.php', '', $path['basename']);
            } else {
                echo "@todo: install static $file\n";
            }
        }
        //print_r($elements); exit;
        foreach ($elements as $element_name ) {
            $this->add_element_to_style($style, $element_name);
        }

        echo "Remember that you have to clear the midgard pagecache to see effects!\n";

    }

    function add_element_to_style ($style, $element_name) {
        $dir = $this->args->getValue(CONSOLE_GETARGS_PARAMS);

        $file = $dir . "/" . $element_name . ".php";
        if (!is_file($file) || ! is_readable($file)) {
            die("$file is not accessible!\n");
        }

        $qb = new midgard_query_builder('midgard_element');
        $qb->add_constraint('style', '=', $style->id);
        $qb->add_constraint('name', '=', $element_name);
        $elements = @$qb->execute();

        if (count($elements) == 0)
        {
            // Create missing element
            $new_element = new midgard_element();
            $new_element->style = $style->id;
            $new_element->name = $element_name;
            $stat = $new_element->create();
            if (!$stat)
            {
                PEAR::raiseError("Failed to create element \"{$element_name}\" under Midgard style \"{$style->name}\", error " . mgd_errstr());
            }
            $element = new midgard_element();
            $element->get_by_id($new_element->id);
        }
        else
        {

            $element = $elements[0];
            // check if there is a newer version in the db.
            $date = strtotime ( $element->metadata->revised ) ;

            //YYYY-MM-DDThh:mm:ss
            $file_date = filemtime($file);
            if ($date > $file_date && !$this->pompt("Do you want to overwrite the current $element_name element?", false ) ) {
                return;
            }
        }

        $element->value = file_get_contents($file);
        if (!$element->update()) {
            die (mgd_err_str());
        }
        return;
    }
    /**
     * Ask the user a boolean question
     * Based on this: http://www.phpguru.org/downloads/Console/Console.phps
     * @return boolean false if not set.
     */
    function pompt($question, $default = null) {
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

    $runner = new import_style();
    $runner->run();

?>
