<?php
/**
 * @package midcom.helper.replicator
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Workaround for Zend bug regarding object handling inside methods
 *
 * Manifests as "Wrong parameter count for serialize()" when you call
 * it (otherwise properly) inside MidCOM application
 *
 * For now also works around bug #259 if applicaple
 *
 * @package midcom.helper.replicator
 * @param midgard_object $object reference to an object
 * @return string object serialized (or false in case of failure)
 */
function midcom_helper_replicator_serialize(&$object)
{
    //return midgard_replicator::serialize($object);
    /**
     * Workaround for bug #259
     */
    if (   !isset($object->lang)
        || $object->lang === 0)
    {
        // Non-ML or not in langx does not trigger the bug
        return midgard_replicator::serialize($object);
    }
    $current_language = (int)$_MIDGARD['lang'];
    $current_default_language = mgd_get_default_lang();
    mgd_set_lang(0);
    mgd_set_default_lang(0);
    $object_class = get_class($object);
    $object_lang0 = new $object_class($object->guid);
    if (   !$object_lang0
        || !isset($object_lang0->guid)
        || empty($object_lang0->guid))
    {
        // Sanity check failed
        $errno = mgd_errno();
        mgd_set_default_lang($current_default_language);
        mgd_set_lang($current_language);
        mgd_set_errno($errno);
        unset($object_class, $object_lang0, $current_language, $current_default_language, $errno);
        return false;
    }
    else
    {
        $stat = midgard_replicator::serialize($object_lang0);
    }
    $errno = mgd_errno();
    mgd_set_default_lang($current_default_language);
    mgd_set_lang($current_language);
    mgd_set_errno($errno);
    unset($object_class, $object_lang0, $current_language, $current_default_language, $errno);
    return $stat;
}

/**
 * Wrapper for accessing blob serialization routines
 *
 * Serves two purposes
 *  1. API has changed between 1.8.2 and 1.8.3
 *  2. see midcom_helper_replicator_serialize
 *
 * @param midgard_attachment $object reference to attachment object
 * @return string blob serialized (or false in case of failure)
 * @see midcom_helper_replicator_serialize
 */
function midcom_helper_replicator_serialize_blob(&$object)
{
    if (is_callable(array('midgard_replicator', 'serialize_blob')))
    {
        // Use this method if it's available
        return midgard_replicator::serialize_blob($object);
    }
    // Old (semantically incorrect) method
    return midgard_replicator::export_blob($object);
}

/**
 * Workaround for Zend bug regarding object handling inside methods
 *
 * Manifests as "PHP object does not have __res property" when you call
 * it (otherwise properly) inside MidCOM application
 *
 * @param string &$xml reference to importable XML
 * @param boolean $use_force whether to use force
 * @return array of objects unserialized from XML (or false for failure)
 * @see midcom_helper_replicator_serialize
 */
function midcom_helper_replicator_unserialize(&$xml, $use_force = false)
{
    if ($use_force)
    {
        return midgard_replicator::unserialize($xml, $use_force);
    }
    return midgard_replicator::unserialize($xml);
}

/**
 * Workaround for Zend bug regarding object handling inside methods
 *
 * Manifests as "Wrong parameter count for import_object()" when you call
 * it (otherwise properly) inside MidCOM application
 *
 * @param midgard_object &$object reference to an object
 * @param boolean $use_force whether to use force
 * @return boolean indicating success/failure
 */
function midcom_helper_replicator_import_object(&$object, $use_force = false)
{
    if ($use_force)
    {
        return midgard_replicator::import_object($object, $use_force);
    }
    return midgard_replicator::import_object($object);
}

/**
 * Workaround for Zend bug regarding object handling inside methods
 *
 * @param string &$xml reference to importable XML
 * @param boolean $use_force whether to use force
 * @return boolean indicating success/failure
 * @see midcom_helper_replicator_import_object
 */
function midcom_helper_replicator_import_from_xml(&$xml, $use_force = false)
{
    if ($use_force)
    {
        return midgard_replicator::import_from_xml($xml, $use_force);
    }
    return midgard_replicator::import_from_xml($xml);
}
?>