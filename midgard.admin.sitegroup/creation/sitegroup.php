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
 * A runner to create a sitegroup
 *
 * @package midgard.admin.sitegroup
 *
 */
class midgard_admin_sitegroup_creation_sitegroup extends midgard_admin_sitegroup_creation_base
{

    /**
     * The object containing the values that should be used
     * to create the object
     * @var object midgard.admin.sitegroup_creation_values
     */
    var $config = null;

    /**
     * The sitegroup object to be created
     * @access private
     */
    var $_sitegroup = null;

    /**
     * Cleanuphelper
     * @var array of objects that has been created.
     */
    var $_cleanup = array();

    function midgard_admin_sitegroup_creation_base($config)
    {
        $this->config = $config;
        $this->verbose = $config->get_value('verbose');
    }

    /**
     * Get the id of the created sitegroup
     */
    function get_id()
    {
        return $this->_sitegroup->id;
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
            if ($this->verbose) {
                print("Not validating, returning false!\n");
            }
            return false;
        }
        if ($this->_create_sitegroup())
        {

            return $this->_create_admingroup_with_admin();
        }
        if ($this->verbose) {
            print("Could not create sitegroup:" . $this->config->get_value('sitegroup_name') . " " . mgd_errstr() . "\n" );
        }
        return false;
    }

    function _create_sitegroup()
    {
        if (substr(mgd_version(), 0,2) == '1.8' )
        {
            $this->_sitegroup = new midgard_sitegroup();
            $this->_sitegroup->name = $this->config->get_value('sitegroup_name');
            return $this->_sitegroup->create();
        } else {
            if ($this->verbose) {
                print("Using old midgard way\n");
            }
            $this->_sitegroup = mgd_get_sitegroup();
            $this->_sitegroup->name = $this->config->get_value('sitegroup_name');
            $sgid = $this->_sitegroup->create();
            if (is_int($sgid)) {
                $this->_sitegroup = mgd_get_sitegroup($sgid);
                if ($this->verbose) {
                    print "Sitegroup created!\n";
                }
                return true;
            }
            return false;
        }
    }
    /**
     * Creates the admin group and the first administrator.
     *
     * return boolean true if success.
     */
    function _create_admingroup_with_admin()
    {
        $group = new midgard_group();
        $group->name = sprintf($this->config->get_value('admingroup_name'), $this->_sitegroup->name);
        $group->sitegroup = $this->_sitegroup->id;

        if (!$group->create())
        {
            if ($this->verbose) {
                print "Could not create group!" . mgd_errstr();
            }
            return false;
        }

        $group = mgd_get_group($group->id);

        $group->setsitegroup($this->_sitegroup->id);
        $person = new midgard_person();
        $person->username = $this->config->get_value('admin_name');
        $person->lastname = $this->config->get_value('admin_name');
        $person->sitegroup= $this->_sitegroup->id;
        if (!$person->create()) {
            if ($this->verbose) {
                print "Could not create person!" . mgd_errstr();
            }
            return false;
        }


        $person = mgd_get_person($person->id);
        $person->setsitegroup($this->_sitegroup->id);

        if (! mgd_update_password($person->id, $this->config->get_value('admin_name'), $this->config->get_value('admin_password'))
            && $this->verbose ) {
            print "Could not set person password\n";
        }

        $member = new midgard_member();
        $member->uid = $person->id;
        $member->gid = $group->id;
        $member->sitegroup = $this->_sitegroup->id;

        if (!$member->create()) {
            if ($this->verbose)
            {
                print "Could not add person to admingroup" . mgd_errstr();
            }
            return false;
        }

        $member = mgd_get_member($member->id);
        $member->setsitegroup($this->_sitegroup->id);
        $this->_cleanup[0] = $member;
        $this->_sitegroup->admingroup = $group->id;
        $this->_sitegroup->update();
        if ($this->verbose) {
            print "Created sitegroup with id {$this->_sitegroup->id} and admin: {$person->id}, group:_{$group->id}\n";
        }

        return true;

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
                printf("User cannot create sitegroup!\n");
            }

            return false;
        }

        if ($this->validate_configuration_variables()) {

            return true;
        }

        return false;
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
    function validate_configuration_variables() {

        foreach (get_object_vars($this->config) as $name => $val)
        {
            if ($this->config->get_value($name) === null)
            {
                if ($this->verbose)
                {
                    print "{$name} is missing value! This must be set to work.\n";
                }
                return false;
            }
        }
        return true;
    }

}
