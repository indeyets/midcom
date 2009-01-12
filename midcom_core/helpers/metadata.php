<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Metadata helper for MidCOM 3
 *
 *
 * @package midcom_core
 */
class midcom_core_helpers_metadata
{
    public static function is_approved(&$object)
    {
        if ($object->metadata->approved >= $object->metadata->revised)
        {
            return true;
        }
        
        return false;
    }
    
    public static function approve(&$object)
    {
        $_MIDCOM->authorization->require_do('midcom:approve', $object);

        $object->approve();
    }
    
    public static function unapprove(&$object)
    {
        $_MIDCOM->authorization->require_do('midcom:approve', $object);

        $object->unapprove();
    }
    
    public static function is_locked(&$object, $check_locker = true)
    {
        if (empty($object->metadata->locked))
        {
            return false;
        }
        
        $lock_time = strtotime($object->metadata->locked . ' GMT');
        $lock_timeout = $lock_time + ($_MIDCOM->configuration->get('metadata_lock_timeout') * 60);
        
        if (time() > $lock_timeout)
        {
            // Stale lock
            // TODO: Should we clear the stale lock here?
            return false;
        }
        
        if (   empty($object->metadata->locker)
            && $check_locker)
        {
            // Shared lock
            return false;
        }
        
        if ($_MIDCOM->authentication->is_user())
        {
            $person = $_MIDCOM->authentication->get_person();
            
            if (    $check_locker
                &&  (   $object->metadata->locker == $person->guid
                     || $object->metadata->locker == '')
                )
            {
                // If you locked it yourself, you can also edit it
                return false;
            }
        }
        
        return true;
    }
    
    public static function lock(&$object, $shared = false, $token = null)
    {
        $_MIDCOM->authorization->require_do('midgard:update', $object);
        
        if ($object->is_locked())
        {
            return;
        }

        $object->lock();
    }
    
    public static function unlock(&$object)
    {
        $_MIDCOM->authorization->require_do('midgard:update', $object);

        if (!$object->is_locked())
        {
            return;
        }

        $object->unlock();
    }
}
?>