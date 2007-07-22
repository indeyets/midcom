<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: categorylister.php 3339 2006-05-02 11:48:56Z torben $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.maemo.calendar
 */

class org_maemo_calendar_callbacks_buddylister extends midcom_baseclasses_components_purecode
{
    /**
     * The array with the data we're working on.
     *
     * @var array
     * @access private
     */
    var $_data = null;

    /**
     * The callback class instance, a callback matching the signature required for the DM2 select
     * type callbacks.
     *
     * @var object
     * @access private
     */
    var $_callback = null;

    /**
     * The user who's buddys are being listed.
     *
     * @var int
     * @access private
     */
    var $_user = null;

    /**
     * The number of characters which have to be stripped off to transform a fully qualified#
     * user identifier to a local one. Thus, one can substr($key, $this->_user_prefix_length)
     * at all times.
     *
     * @var int
     * @access private
     */
    var $_user_prefix_length = null;

    /**
     * Initializes the class to the category listing in the configuration. It does the neccessary
     * postprocessing to move the configuration syntax to the rendering one.
     *
     * If $sitelisting is true, the component is requesting the listing for the site category 
     * index. In that case "site_" prefixed config options take precedence over the standard
     * option names to allow you to have limited category listings on-site.
     *
     * @param int $user The user who's buddys to show.
     * @param bool $sitelisting The callback is used to display the onsite listing instead of
     *     the standard DM2 interface 
     */
    function org_maemo_calendar_callbacks_buddylister($user, $sitelisting = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_component = 'org.maemo.calendar';

        parent::midcom_baseclasses_components_purecode();
        
        debug_add("got arg[0]: {$user}");
        
        // $this->_user = $user;
        //         
        //         if (empty($this->_user))
        //         {
            $this->_user = $_MIDCOM->auth->user->get_storage();
        // }
        
        $this->_user_prefix_length = strlen($this->_user->guid) + 1;
        $data =& $_MIDCOM->get_custom_context_data('request_data');

        $this->_data = Array();
        
        $qb = net_nehmer_buddylist_entry::new_query_builder();
        $qb->add_constraint('account', '=', $this->_user->guid);
        //$qb->add_constraint('isapproved', '=', true);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies_qb = $qb->execute();
        
        // $own_name = $this->_user->lastname . ', ' . $this->_user->firstname;
        // $this->_data["{$this->_user->guid}-{$this->_user->guid}"] = $own_name;
        
        foreach ($buddies_qb as $buddy)
        {
            $person = new midcom_db_person($buddy->buddy);
            if ($person)
            {
                $name = $person->lastname . ', ' . $person->firstname;
                $this->_data["{$this->_user->guid}-{$person->guid}"] = $name;
                //$this->_data["{$this->_user}-{$person->guid}"] = $person;
            }
        }
        
        debug_print_r('_data: ',$this->_data);
        
        debug_pop();
    }

    /** Ignored. */
    function set_type(&$type) {}

    function get_name_for_key($key)
    {
        //$name = $this->_data[$key]->lastname . ', ' . $this->_data[$key]->firstname;
        $name = $this->_data[$key];
        return $name;
    }

    function key_exists($key)
    {
        return array_key_exists($key, $this->_data);
    }

    function list_all()
    {
        return $this->_data;
    }

}
?>