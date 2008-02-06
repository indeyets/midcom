<?php
/**
 * Created on Feb 26, 2006
 * @author tarjei huse
 * @package midgard.admin.sitegroup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 */

/**
 * A runner to create a host
 *
 * @package midgard.admin.sitegroup
 *
 */
class midgard_admin_sitegroup_creation_host extends midgard_admin_sitegroup_creation_base
{

    /**
     * The object containing the values that should be used
     * to create the object
     * @var object midgard.admin.sitegroup_creation_values
     */
    var $config = null;

    /**
     * The topic to use for the host. If null, it will be created, else it
     * should be the root topic object for that site.
     * */
    var $topic = null;

    /**
     * The style to use for the site. If null, a very simple style will
     * be created.
     */
    var $style = null;

    /**
     * The host object.
     */
    var $host = null;

    /**
     * The root page for the host.
     */
    var $page = null;

    /**
     * Page elements for the root page.
     */
    var $codeinit = null;
    var $codefinish = null;
    /**
     * The sitegroupid that should own the elements.
     */
    var $sitegroup_id = 0;
    /**
     * The sitegroup object to be used (if any)
     */
    var $_sitegroup = null;

    function midgard_admin_sitegroup_creation_host($config)
    {
        $this->config = $config;
        $this->verbose = $config->get_value('verbose');
        if ($config->get_value('style') !== null)
        {
            $this->style = $config->get_value('style');
        }

        if ($config->get_value('topic') !== null)
        {
            $this->topic = $config->get_value('topic');
        }

        $this->sitegroup_id = $this->config->get_value('sitegroup_id');

        if ($this->verbose)
        {
            echo "Using sitegroup_id: " . $this->sitegroup_id . " (current SG is {$_MIDGARD['sitegroup']})\n";
        }

        if (!class_exists('midcom_helper_hostconfig'))
        {
            require_once 'midcom/lib/midcom/helper/hostconfig.php';
        }
    }


    /**
     * static factory method
     */
    function factory($config)
    {
        if ($config->get_value('verbose'))
        {
            echo "Found versions: " . substr(mgd_version(), 0, 3) ." (".mgd_version(). ")" .  "\n";
        }
        if (substr(mgd_version(), 0, 3) == '1.8' )
        {
            return new midgard_admin_sitegroup_creation_host_18($config);
        }
        else
        {
            return new midgard_admin_sitegroup_creation_host_17($config);
        }
    }

    /**
     * This is run at the end of
     */
    function finish() {}

    /**
     * Get the id of the created host
     */
    function get_id()
    {
        return $this->_host->id;
    }

    /**
     * The run method executes the core to create what should be created
     * @return boolean true on success.
     * @abstract
     *
     */
    function run()
    {
        // safeguard
        if (!$this->validate())
        {
            if ($this->verbose)
            {
                print ("Not validating, returning false!\n");
            }
            return false;
        }

        $this->_authenticate($this->sitegroup_id);

        if ($this->topic === null)
        {
            print "Creating root topic\n";
            if (!$this->_create_topic())
            {
                if ($this->verbose)
                {
                    echo "Could not create topic: ".mgd_errstr()."\n";
                }
                $this->_clean();
                return false;
            }
        }

        if ($this->style === null)
        {

            print "Creating root style\n";
            if (!$this->_create_style())
            {
                if ($this->verbose)
                {
                    echo "Could not create style: ".mgd_errstr()."\n";
                }
                $this->_clean();
                return false;
            }
        }

        print "Creating root page\n";
        if (!$this->_create_page())
        {

            if ($this->verbose)
            {
                print "Could not create page: ".mgd_errstr()."\n";
            }
            $this->_clean();
            return false;
        }

        // Host must be created as SG0!
        $this->_authenticate(0);

        print "Creating host\n";
        if (!$this->_create_host())
        {
            if ($this->verbose)
            {
                print "Could not create host: ".mgd_errstr()."\n";
            }
            $this->_clean();
            return false;
        }
        $this->finish();
        return true;
    }

    /**
     * The clean method deletes all objects we've created during this run
     * @return boolean true on success.
     */
    function _clean()
    {
        print "Cleaning up...\n";
        if ($this->host)
        {
            midcom_helper_purge_object($this->host->guid);
            if ($this->verbose)
            {
                print "Deleting host {$this->host->guid}... " . mgd_errstr() . "\n";
            }
        }

        if ($this->page)
        {
            midcom_helper_purge_object($this->page->guid);
            if ($this->verbose)
            {
                print "Deleting page {$this->page->guid}... " . mgd_errstr() . "\n";
            }
        }

        if ($this->style)
        {
            midcom_helper_purge_object($this->style->guid);
            if ($this->verbose)
            {
                print "Deleting style {$this->style->guid}... " . mgd_errstr() . "\n";
            }
        }

        if ($this->topic)
        {
            midcom_helper_purge_object($this->topic->guid);
            if ($this->verbose)
            {
                print "Deleting topic {$this->topic->guid}... " . mgd_errstr() . "\n";
            }
        }

    }

    /**
     * Makes sure the user is in the correct sitegroup.
     *
     * Note: I have not managed to get this to work in 1.7.4.
     *
     * @param int sitegroup id
     * @access private
     */
    function _authenticate($sitegroup_id)
    {
        if (   $_MIDGARD['admin']
            && !$this->config->get_username())
        {

            return true;
        }
        $midgard = mgd_get_midgard();
        if ($sitegroup_id == 0)
        {
            mgd_auth_midgard($this->config->get_username(), $this->config->get_password());
            return true;
        }
        $sitegroup = mgd_get_sitegroup($sitegroup_id);
        if ($midgard->sitegroup != $sitegroup)
        {
            echo "Logging in as " . $this->config->get_username()."!".$sitegroup->name . "\n";
            mgd_auth_midgard($this->config->get_username()."!".$sitegroup->name, $this->config->get_password(), false);
            //print_r($_MIDGARD);
            return true;
        }
    }

    /**
     * Creates the host.
     */
    function _create_host()
    {
       $attributes  = Array
        (
            'name' => $this->config->get_value('hostname'),
            'online' => true,
            'sitegroup' => $this->sitegroup_id,
            'root' => $this->page->id,
        );

        if ($this->config->get_value('host_prefix') != '/')
        {
            $attributes['prefix'] = $this->config->get_value('host_prefix');
        }

        // Call the appropriate creation method for the Midgard version
        if (!$this->_create_object("host", $attributes, $this->sitegroup_id, "host")){
            return false;
        }

        return true;
    }

    /**
     *  @access private
     */
    function _create_topic()
    {
        $attributes  = Array
        (
            'name' => $this->config->get_value('hostname') . $this->config->get_value('host_prefix') . " root topic",
            'extra' => $this->config->get_value('topic_name'),
            'component' => $this->config->get_value('topic_midcom'),
            'style' => '/' . $this->config->get_value('extend_style'),
            'styleInherit' => true,
        );

        // Call the appropriate creation method for the Midgard version
        if (!$this->_create_object("topic", $attributes, $this->sitegroup_id, "topic"))
        {
            return false;
        }

        return true;
    }

    /**
     * Creates a basic style
     * @access private
     */
    function _create_style()
    {
        if ($this->config->get_value('extend_style') != 'none')
        {
            // Extend existing style template
            $qb = new midgard_query_builder('midgard_style');
            $qb->add_constraint('up', '=', 0);
            $qb->add_constraint('name', '=', $this->config->get_value('extend_style'));
            $styles = @$qb->execute();

            if (count($styles) > 0)
            {
                $attributes  = Array
                (
                    'up' => $styles[0]->id,
                    'sitegroup' => $this->sitegroup_id,
                    'name' => sprintf('%s for %s', $this->config->get_value('extend_style'), $this->config->get_value('hostname') . str_replace('/', ':', $this->config->get_value('host_prefix'))),
                );

                if (!$this->_create_object('style', $attributes, $this->sitegroup_id, "style"))
                {
                    return false;
                }

                return true;
            }
        }

        // Fallback, generate empty Midgard style
        $attributes  = Array
        (
            'sitegroup' => $this->sitegroup_id,
            'name' => sprintf('%s for %s', $this->config->get_value('style_name'), $this->config->get_value('hostname') . str_replace('/', ':', $this->config->get_value('host_prefix'))),
        );

        if (!$this->_create_object("style", $attributes, $this->sitegroup_id, "style"))
        {
            return false;
        }

        return $this->_create_styleelements();
    }

    /**
     * create the ROOT element needed for the style.
     */
    function _create_styleelements()
    {
        $element = new midgard_element();
        $element->style = $this->style->id;
        $element->name = 'ROOT';
        $element->sitegroup = $this->sitegroup_id;
        $element->value = "
<html>
    <head>
        <title><(title)> - <?php echo \$_MIDCOM->get_context_data(MIDCOM_CONTEXT_PAGETITLE); ?></title>
        <?php
        echo \$_MIDCOM->print_head_elements();
        ?>
    </head>
    <body <?php echo \$_MIDCOM->print_jsonload(); ?> >
        <?php
        \$_MIDCOM->toolbars->show();
        ?>
        <(content)>
        <?php
        \$_MIDCOM->uimessages->show();
        ?>
    </body>
</html>";

        if (!$element->create())
        {
            echo "Failed to create ROOT element for style\n";
            return false;
        }

        return true;

    }
    /**
     * Create the root page for the host.
     * Since mrfc0025 demands one page per host, this must be done here.
     * In the future code-init and code-finish will be changed to midcom-init and midcom-finish.
     *
     */
    function _create_page()
    {

        $attributes = array
        (
            'name' => $this->config->get_value('hostname') . $this->config->get_value('host_prefix') . " root",
            'title' => $this->config->get_value('topic_name'),
            'style' => $this->style->id,
            'info' => 'active',
            'content' => $this->config->get_value('root_page_content'),
            'author' => 1,
        );

        if (!$this->_create_object("page", $attributes, $this->sitegroup_id, "page"))
        {
            if ($this->verbose)
            {
                echo "Could not create root page!\n";
            }
            return false;
        }

        if ( !$this->_create_codeinit() )
        {
            if ($this->verbose)
            {
                echo "Could not create codeinint elements!\n";
            }
            return false;
        }

        if ( !$this->_create_codefinish())
        {
            if ($this->verbose)
            {
                echo "Could not create codefinish elements!\n";
            }
            return false;
        }
        return true;
    }
    /**
     * create the code-init element to be used. Please note that this
     * should probably be refactored out into a separate class.
     */

    function _create_codeinit()
    {
        if ($_MIDGARD['config']['prefix'] == '/usr')
        {
            $prefix = '/var';
        }
        else if ($_MIDGARD['config']['prefix'] == '/usr/local')
        {
            $prefix = '/var/local';
        }
        else
        {
            $prefix = $_MIDGARD['config']['prefix'].'/var';
        }

        $hostconfig = new midcom_helper_hostconfig($this->page);
        $hostconfig->set('midcom_root_topic_guid', $this->get_topic_guid());
        $hostconfig->set('cache_base_directory', $prefix.'/cache/midgard/midcom/');
        $hostconfig->set('log_filename', $prefix.'/log/midgard/midcom/'.$this->config->get_value('hostname').".log");
        $hostconfig->set('midcom_path', $this->config->get_value('midcom_path'));

        $attributes = array
        (
            'name' => "code-init",
            'value' => $hostconfig->get_code_init(),
            'page' => $this->page->id,
            'info' => 'inherit'
        );

        if (!$this->_create_object("pageelement", $attributes, $this->sitegroup_id, "codeinit"))
        {
            if ($this->verbose)
            {
                echo "Could not create code-init!\n";
            }
            return false;
        }

    return true;
    }

    function _create_codefinish()
    {
        $attributes = array
        (
            'name' => "code-finish",
            'value' => "<?php \$_MIDCOM->finish(); ?>",
            'page' => $this->page->id,
            'info' => 'inherit'
        );

    if (!$this->_create_object("pageelement", $attributes, $this->sitegroup_id, "codefinish"))
        {
            if ($this->verbose)
            {
                echo "Could not create code-finish!\n";
            }
            return false;
        }

        return true;

    }

    /**
     * Abstract out the object creation as it differs in 1.7 and 1.8.
     * @param string classname
     * @param array ( attributename => value ) attributes to be created
     * @param string name of the attribute to save the storage in .
     * @param int the sitegroup the object should have
     * @return int id of object or 0 if not created.
     *
     */
    function _create_object($class, $attributes, $storage = null )
    {
        print "YOU ARE NOT USING THE FACTORY() METHOD TO CREATE THIS CLASS!!!\n";
        print "change your code!!!\n";
        return 0;
    }
    /**
     * Wrapper to get the topic guid.
     * @return string topic guid
     */
    function get_topic_guid()
    {
        return false;
    }

    /**
     * All classes should have a validator method that must return true
     * for the class to run.
     * @return boolean
     * @access public
     * @abstract
     *
     */
    function validate()
    {
        if (!$this->can_create_sitegroup())
        {
            if ($this->verbose)
            {
                printf("Only root user can create hosts!\n");
            }

            return false;
        }

        /*
         * This is not necessary on PEAR-installed hosts
        if (!file_exists($this->config->get_value('midcom_path') . "/midcom.php"))
        {
            echo "MidCOM not found at the specified path";
            return false;
        }
        */

        if (!$this->validate_configuration_variables())
        {

            return false;
        }
        if ($this->sitegroup_id != 0)
        {
            //$sg = new midgard_sitegroup();
            $sg = mgd_get_sitegroup($this->sitegroup_id);

            //if (!$sq->get_by_id($this->sitegroup_id)) {
            //    return false;
            //}
            if (!$sg  && $this->verbose)
            {
                printf("Sitegroup_id does not exist!! You must use an existing sitegroup.\n");
            }

        }


        return true;
    }

    /**
     * Check if the user is in sitegroup zero.
     * Ahould be converted to an acl check when this is supported
     * in the midgard API.
     */
    function can_create_sitegroup()
    {
        if ($_MIDGARD['root'] != 1)
        {
            return false;
        }
        return true;
    }

    /**
     * helper that checks that all the configuration variables
     * has a value
     * @access protected
     */
    function validate_configuration_variables()
    {

        $required = array ('hostname', 'host_prefix', 'midcom_path');

        foreach ($required as $key => $name)
        {
            if ($this->config->get_value($name) === null)
            {
                if ($this->verbose)
                {
                    print "$name is missing value! This must be set to work.\n";
                }
                return false;
            }
        }
        return true;
    }

}

/**
 * @package midgard.admin.sitegroup
 */
class midgard_admin_sitegroup_creation_host_17 extends midgard_admin_sitegroup_creation_host
{
    /**
     * @param string tablename
     * @param array ( attributename => value ) attributes to be created
     * @param string name of the attribute to save the storage in .
     * @param int the sitegroup the object should have
     * @return int id of object or 0 if not created.
     */
    function _create_object($class, $attributes, $sitegroup, $storage = null )
    {
        $classicapi = "mgd_get_{$class}";
        if ($class == 'pageelement')
        {
            $classicapi = 'mgd_get_page_element';
        }

        $classname = "midgard_{$class}";

        if (!class_exists($classname))
        {
            if ($this->verbose)
            {
                print "Class {$classname} does not exist!\n";
            }
            return false;
        }

        $obj = new $classname();
        foreach ($attributes as $name => $value)
        {
            $obj->$name = $value;
        }
        $obj->sitegroup = $sitegroup;

        $id = $obj->create();
        if (!$id)
        {
            if ($this->verbose)
            {
                print "Could not create object {$classname}. Error: " . mgd_errstr() . "\n";
                print_r($obj);
            }
            return false;
        }
        $id = $obj->id;

        $obj = new $classname();
        $obj->get_by_id($id);
        //$obj = $classicapi($id);
        if ($obj->sitegroup != $sitegroup)
        {
            if (!method_exists('setsitegroup', $obj))
            {
                if ($this->verbose)
                {
                    echo "Class " . get_class($obj) . " doesn't have the 'setsitegroup' method (we're in SG {$_MIDGARD['sitegroup']}).\n";
                }
                print_r($obj);
                $obj->delete();
                return false;
            }
            else
            {
                $obj->setsitegroup($sitegroup);
            }
        }

        // get the object a third time to be sure it is correctly set.
        if ($storage !== null)
        {
            // we want to use the new kind of object to have a clean interface
            //$classname = "midgard_$class";
            $this->$storage = new $classname();
            $this->$storage->get_by_id($obj->id);
        }
        return $this->$storage->id;
    }
    /**
     * Wrapper to get the topic guid
     */
    function get_topic_guid() {
        if ($this->topic->guid)
        {
            return $this->topic->guid;
        }
        else
        {
            return $this->topic->guid();
        }
    }

    /**
     * set the correct sitegroup for the topic, host and page parameters.
     */
    function finish()
    {
        print "Setting topic and page sitegroup...\n";

        //$this->topic->setsitegroup($this->sitegroup_id);
        //$this->page->setsitegroup($this->sitegroup_id);
    }

}

/**
 * @package midgard.admin.sitegroup
 */
class midgard_admin_sitegroup_creation_host_18 extends midgard_admin_sitegroup_creation_host {


    /**
     *
     * @param string tablename
     * @param array ( attributename => value ) attributes to be created
     * @param string name of the attribute to save the storage in .
     * @param int the sitegroup the object should have
     * @return int id of object or 0 if not created.
     */
    function _create_object($class, $attributes, $sitegroup, $storage = null)
    {
        $classname = "midgard_$class";
        if (!class_exists($classname))
        {
            if ($this->verbose)
            {
                print "Class $classname does not exist!\n";
            }
            return false;
        }

        $object = new $classname();

        foreach ($attributes as $name => $value)
        {
            $object->$name = $value;
        }

        $object->sitegroup = $sitegroup;
        if (!$object->create())
        {
            if ($this->verbose)
            {
                print "Could not create object $classname ($storage). Error: " . mgd_errstr() . "\n";
            }
            return false;
        }
        $id = $object->id;

        // get the object a third time to be sure it is correctly set.
        if ($storage !== null)
        {
            $this->$storage = new $classname($id);
        }
        return $id;
    }
    /**
     * Wrapper to get the topic guid.
     *
     */
    function get_topic_guid()
    {
        return $this->topic->guid;
    }

}
