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
 * For now also works around bug #259 if applicaple and checks the serialization against bug #244
 *
 * Also catches and handles the metadata rewriting that caused bug #302
 *
 * @package midcom.helper.replicator
 * @param midgard_object $object reference to an object
 * @return string object serialized (or false in case of failure)
 */
function midcom_helper_replicator_serialize(&$given_object)
{
    /**
     * Workaround for bug #302
     */
    if ((int)$given_object->metadata->created == $given_object->metadata->created)
    {
        // FIXME: This should be copy/clone not reference, but clone() complains about uncloneable objects...
        $object = $given_object;
        midcom_baseclasses_core_dbobject::_rewrite_timestamps_to_isodate($object);
    }
    else
    {
        $object =& $given_object;
    }
    /**
     * Workaround for bug #259
     */
    if (   !isset($object->lang)
        || $object->lang === 0)
    {
        // Non-ML or not in langx does not trigger bug #259
        $stat = midgard_replicator::serialize($object);
        return midcom_helper_replicator_serialize_check_bug244($stat, $object);
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
    return midcom_helper_replicator_serialize_check_bug244($stat, $object);
}

/**
 * Checks if serialization would trigger bug #244, if so returns failure
 * and raises UI message.
 *
 * @param string $serialized reference to serialized object
 * @return string $serialized or false if triggers bug #244
 */
function midcom_helper_replicator_serialize_check_bug244(&$serialized, &$object)
{
    //debug_push_class('function', __FUNCTION__);
    if (!preg_match_all('%<[^>]+?lang="[^"]+"[^>]+?>%', $serialized, $matches))
    {
        // not ML or no extra languages present.
        /*
        debug_add('no lang matches');
        debug_pop();
        */
        return $serialized;
    }
    //debug_print_r('$matches: ', $matches);
    $langs = count($matches[0]);
    unset($matches);
    if ($langs <= 1)
    {
        // master + 1 does not trigger bug #244
        /*
        debug_add("\$langs <= 1, does not trigger bug #244");
        debug_pop();
        */
        unset($langs);
        return $serialized;
    }
    if ($langs % 2 == 0)
    {
        //debug_add("\$langs={$langs}, triggers bug #244");
        $total_langs = $langs+1;
        $class = get_class($object);
        $object_url = $_MIDCOM->get_host_prefix() . "__mfa/asgard/object/view/{$object->guid}/";
        $msg = "Object <a target='_blank' href='{$object_url}'>{$class} #{$object->id}</a> has {$total_langs} languages, this triggers <a target='_blank' href='http://trac.midgard-project.org/ticket/244'>bug #244</a>.";
        $msg .= " Object will not be replicated, please go add one more language to the object in <a target='_blank' href='{$object_url}'>Asgard</a>.";
        $_MIDCOM->uimessages->add('midcom.helper.replicator', $msg, 'error');
        $GLOBALS['midcom_helper_replicator_logger']->log_object($object, "has {$total_langs} languages, this triggers bug #244, preventing export.", MIDCOM_LOG_ERROR);
        debug_push_class('function', __FUNCTION__);
        debug_add("Object {$class} #{$object->id} has {$total_langs} languages, this triggers bug #244, preventing export", MIDCOM_LOG_WARN);
        debug_pop();
        unset($class, $object_url, $msg, $total_langs, $langs);
        mgd_set_errno(MGD_ERR_ERROR);
        return false;
    }
    /*
    debug_add("\$langs % 2 != 0, does not trigger bug #244");
    debug_pop();
    */
    unset($langs);
    return $serialized;
}

/**
 * Wrapper for accessing blob serialization routines
 *
 * @param midgard_attachment $object reference to attachment object
 * @return string blob serialized (or false in case of failure)
 * @see midcom_helper_replicator_serialize
 */
function midcom_helper_replicator_serialize_blob(&$object)
{
    return midgard_replicator::serialize_blob($object);
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