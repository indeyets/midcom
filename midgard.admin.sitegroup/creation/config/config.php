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
 * The creation_config classes are pure value containers for the creation classes.
 * They can be used to serialize events and to abstract out the values of the class.
 *
 * @package midgard.admin.sitegroup
 *
 */
class midgard_admin_sitegroup_creation_config
{

    // all creators have a verbose option.
    var $verbose = false;
    /**
     * Password for the user to log in as when creating objects in a sitegroup.
     * @access private
     */
    var $_password = null;
    /**
     * username for the user to log in as when creating objects in a sitegroup.
     * @access private
     */
    var $_username = null;

    function midgard_admin_sitegroup_creation_config() {}

    /**
     * Simple function to get a value.
     * @return mixed
     * @param string name of the value
     */
    function get_value($valuename)
    {
        if (array_key_exists($valuename, get_object_vars($this))) {
            return $this->$valuename;
        }
        return null;
    }
    /**
     * Set a value
     * @param string name of the value
     * @param mixed the value of the value.
     * @return boolean false if value doesn't exist
     */
    function set_value($valuename, $value)
    {
        if (array_key_exists($valuename, get_object_vars($this))) {
            $this->$valuename = $value;
            return true;
        }
        return false;
    }
    /**
     * check if the container has a value
     * @return boolean true if value exists
     */
    function has_value($valuename)
    {
        return array_key_exists($valuename, get_object_vars($this));
    }
    /**
     * Sets a value to null
     * @param string $valuename of the value
     * @return boolean true if value was deleted.
     */
    function delete_value($valuename)
    {
        if (array_key_exists($valuename, get_object_vars($this)))
        {
            $this->$valuename = null;
            return true;
        }
        return false;
    }

    /**
     * Do not use the normal get/set_value api for these as they are related to auth.
     */
    function get_password()
    {
        return $this->_password;
    }

    function get_username()
    {
        return $this->_username;
    }

    function set_password($pwd)
    {
        $this->_password = $pwd;
    }

    function set_username($uid)
    {
        $this->_username = $uid;
    }
}

/**
 * @package midgard.admin.sitegroup
 */
class midgard_admin_sitegroup_creation_config_sitegroup extends midgard_admin_sitegroup_creation_config
{
    /**
     * Name of the sitegroup
     */
    var $sitegroup_name = null;
    /**
     * Name of the admin
     */
    var $admin_name = 'admin';
    /**
     * Admin password
     */
    var $admin_password = null;
    /**
     * Admingroup name
     */
    var $admingroup_name = '%s administrators';

}

/**
 * @package midgard.admin.sitegroup
 */
class midgard_admin_sitegroup_creation_config_host extends midgard_admin_sitegroup_creation_config
{
    /**
     * Hostname
     */
    var $hostname = null;
    /**
     * prefix
     */
    var $host_prefix = null;

    /**
     * The midcom
     * code-init element
     */
    var $code_init = "";
    /**
     * the midcom
     * code finish element.
     */
    var $code_finish = '<?php $_MIDCOM->finish(); ?>';

    /**
     * Name of the style to be created (if one has to be created)
     */
    var $style_name = "Basic midcom style";

    /**
     * if set, use this style, if not create a new one,
     * based on the extend_style.
     */
    var $style = null;

    /**
     * If style isn't set, create a style to use that is based on this style.
     */
    var $extend_style = 'none';

    /**
     * Use an already created rootpage that has the guid set below.
     * @todo not implemented
     */
    var $root_page = null;

    /**
     * The content of the root page
     */
    var $root_page_content = "<?php echo \$_MIDCOM->content(); ?>\n";

    /**
     * The topic to use for the midcom
     */
    var $topic_midcom = "";

    /**
     * If there is not special topic to use for midcom, use this as the title for the topic.
     * @var string
     */
    var $topic_name = "Midgardian";

    /**
     * The path to the midcom installation
     */
    var $midcom_path = "midcom/lib";

    /**
     * The id of the sitegroup to create the host in
     */
    var $sitegroup_id = 0;

}

