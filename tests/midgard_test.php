<?php

class midgard_test extends PHPUnit_FrameWork_TestCase
{    
    public $midgard_config_file = 'midgard_php_test';
    public $dbtype = 'MySQL';
    
    // Ensure PHPUnit doesn't serialize/unserialize $_MIDGARD_CONNECTION
    protected $backupGlobals = false;

    public function check_extension()
    {
        if (   !extension_loaded('midgard')
            && !extension_loaded('midgard2'))
        {
            $this->markTestSkipped('Midgard extension is not available');
        }
    }
    
    public function check_dbtype()
    {
        if (is_null($this->dbtype))
        {
            // FIXME: better error ??
            $this->markTestSkipped('You need to provide a valid libgda database type');
        }
    }

    public function create_config($config_file)
    {
        if (is_null($config_file))
        {
            return;
        }

        $config = new midgard_config();
        $config->dbtype = $this->dbtype;
        $config->database = 'midgard_php_test';
        $config->dbuser = 'midgard_php_test';
        $config->dbpass = 'midgard_php_test';
        $config->blobdir = "/tmp/{$config_file}";
        $config->loglevel = 'warning';

        if (! $config->save_file($config_file))
        {
            $this->markTestSkipped('Could not generate a test configuration file');
        }
    }

    public function open_connection($config_file)
    {
        // Open connection
        $midgard = new midgard_connection();

        if (!$midgard->open($config_file))
        {
            $this->markTestSkipped('Could not connect to test database');
        }
        $connected = true;
    }

    public function prepare_storage()
    {
        // Generate tables
        if (!midgard_config::create_midgard_tables())
        {
            $this->markTestSkipped('Could not generate Midgard tables for test database');
        }
        // And update as necessary
        
        foreach ($_MIDGARD['schema']['types'] as $type => $val)
        {
            if (midgard_config::class_table_exists($type))
            {
                if (!midgard_config::update_class_table($type))
                {
                    $this->markTestSkipped('Could not update ' . $type . ' tables in test database');
                }
                continue;
            }

            if (!midgard_config::create_class_table($type))
            {
                $this->markTestSkipped('Could not create ' . $type . ' tables in test database');
            }
        }
        
        if (!midgard_user::auth('root', 'password'))
        {
            echo "auth failed\n";
            $this->markTestSkipped('Could not authenticate as ROOT');
        }
    }

    public function prepare_blobdir($config_file)
    {
        $config = new midgard_config();
        $config->read_file($config_file);
        
        $dirs = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E','F');
        $subdirs = $dirs;

        $blobdir = $config->blobdir;

        if (!is_dir($blobdir))
        {
            mkdir($blobdir);
        }

        if (!is_writable($config->blobdir))
        {
            $this->markTestSkipped("Blobdir {$config->blobdir} is not writable");
        }

        foreach($dirs as $dir)
        {
            foreach($subdirs as $subdir)
            {
                $subblobdir = "{$blobdir}/{$subdir}";
                
                if (!is_dir($subblobdir))
                {
                    mkdir($subblobdir);
                }
            }
        }
    }

    /**
     * Make a special Midgard DB for tests
     */
    public function setUp()
    {
        if (   isset($_MIDGARD_CONNECTION)
            && $_MIDGARD_CONNECTION !== null)
        {
            // At the moment we can connect only once
            return;
        }

        $this->check_extension();        
        $this->check_dbtype();
        $this->create_config($this->midgard_config_file);
        $this->open_connection($this->midgard_config_file);
        $this->prepare_storage();
        $this->prepare_blobdir($this->midgard_config_file);
    }

    public function tearDown()
    {
        
        /**
         * FIXME: Delete the database here. No API for it now
         */
        // $midgard->close();
    }
}
?>