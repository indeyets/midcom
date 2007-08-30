<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:_sessioning.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Base singelton class of the MidCOM sessioning service.
 *
 * This is a singelton class, that is accessible through the MidCOM Service
 * infrastructure. It manages session data of MidCOM driven applications.
 *
 * This sessioning interface will always work with copies, never with references
 * to work around a couple of bugs mentioned in the details below.
 *
 * This class provides a generic interface to store keyed session values in the
 * domain of the corresponding component.
 *
 * All requests involving this service will always be flagged as no_cache.
 *
 * If you store class instances within a session, which is perfectly safe in
 * general, there are known problems due to the fact, that a class declaration
 * has to be available before it can be deserialized. As PHP sessioning does this
 * deserialization automatically, this might fail with MidCOM, where the sequence
 * in which the code gets loaded and the sessioning gets started up is actually
 * undefined. To get around this problems, the sessioning system stores not the
 * actual data in the sessioning array, but a serialized string of the data, which
 * can always be deserialized on PHP sessioning startup (its a string after all).
 * This has a important implication though: The sessioning system always stores
 * copies of the data, not references. So if you put something in to the session
 * store and modify it afterwards, this change will not be reflected in the
 * sessioning store.
 *
 * It will try to be as graceful as possible when starting up the sessioning. Note,
 * that side-effects that might occur together with NemeinAuth are not fully
 * investigated yet.
 *
 * <b>Important:</b>
 *
 * Do <b>never</b> create an instance of this class directly. This is handled
 * by the framework. Instead use midcocm_service_session which ensures the
 * singelton pattern.
 *
 * Do <b>never</b> work directly with the $_SESSION["midcom_session_data"]
 * variable, this is a 100% must-not, as this will break functionality.
 *
 * @package midcom.services
 * @access private
 * @see midcom_service_session
 */
class midcom_service__sessioning
{
    /**
     * The constructor will initialize the sessioning, set the output nocacheable
     * and initialize the session data. This might involve creating an empty
     * session array.
     */
    function midcom_service__sessioning()
    {
        static $started = false;

        if ($started)
        {
            $_MIDCOM->generate_error("MidCOM Sessioning has already been started, it must not be started twice. Aborting",
                                               MIDCOM_ERRCRIT);
        }

        $started = true;

        @session_start();

        // Disable caching for sessioned requests
        $_MIDCOM->cache->content->no_cache();

        // Check for session data and load or initialize it, if neccessary
        if (! array_key_exists("midcom_session_data", $_SESSION))
        {
            $_SESSION["midcom_session_data"] = Array();
            $_SESSION["midcom_session_data"]["midcom.service.sessioning"]["startup"] = serialize(time());
        }
    }

    /**
     * Checks, if the specified key has been added to the session store.
     *
     * This is often used in conjunction with get to verify a keys existence.
     *
     * @param string $domain	The domain in which to search for the key.
     * @param mixed $key		The key to query.
     * @return bool				Indicating availabiliity.
     */
    function exists ($domain, $key)
    {
        if (! array_key_exists($domain, $_SESSION["midcom_session_data"]))
        {
            debug_add("SESSION: Request for the domain [$domain] failed, because the domain doesn't exist.");
            return false;
        }

        if (! array_key_exists($key, $_SESSION["midcom_session_data"][$domain]))
        {
            debug_add("SESSION: Request for the key [$key] in the domain [$domain] failed, because the key doesn't exist.");
            return false;
        }

        return true;
    }

    /**
     * This is a small, internal helper function, which will load, unserialize and
     * return a given key's value. It is shared by get and remove.
     *
     * @param string $domain	The domain in which to search for the key.
     * @param mixed $key		The key to query.
     * @return mixed			The session key's data value, or NULL on failure.
     */
    function _get_helper ($domain, $key)
    {
        return unserialize($_SESSION["midcom_session_data"][$domain][$key]);
    }

    /**
     * Returns a value from the session.
     *
     * Returns null if the key
     * is non-existant. Note, that this is not neccessarily a valid non-existance
     * check, as the sessioning system does allow null values. Use the exists function
     * if unsure.
     *
     * @param string $domain	The domain in which to search for the key.
     * @param mixed $key		The key to query.
     * @return mixed			The session key's data value, or NULL on failure.
     * @see midcom_service__sessioning::exists()
     */
    function get ($domain, $key)
    {
        if ($this->exists($domain, $key))
        {
            return $this->_get_helper($domain, $key);
        }
        else
        {
            return null;
        }
    }

    /**
     * Removes the value associated with the specified key. Returns null if the key
     * is non-existant or the value of the key just removed otherwise. Note, that
     * this is not neccessarily a valid non-existance check, as the sessioning
     * system does allow null values. Use the exists function if unsure.
     *
     * @param string $domain	The domain in which to search for the key.
     * @param mixed $key		The key to remove.
     * @return mixed			The session key's data value, or NULL on failure.
     * @see midcom_service__sessioning::exists()
     */
    function remove ($domain, $key)
    {
        if ($this->exists($domain, $key))
        {
            $data = $this->_get_helper($domain, $key);
            unset($_SESSION["midcom_session_data"][$domain][$key]);
            debug_print_r("SESSION: Dump after removing $domain/$key (serialized) ", $_SESSION["midcom_session_data"]);
            return $data;
        }
        else
        {
            return null;
        }
    }

    /**
     * This will store the value to the specified key.
     *
     * Note, that a _copy_ is stored,
     * the actual object is not referenced in the session data. You will have to update
     * it manually in case of changes.
     *
     * @param string $domain	The domain in which to search for the key.
     * @param mixed	$key		Session value identifier.
     * @param mixed	$value		Session value.
     */
    function set ($domain, $key, $value)
    {
        $_SESSION["midcom_session_data"][$domain][$key] = serialize($value);
        debug_print_r("SESSION: Dump after setting $domain/$key (serialized) ", $_SESSION["midcom_session_data"]);
    }
}


?>