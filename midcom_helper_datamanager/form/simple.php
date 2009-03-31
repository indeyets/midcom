<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager Form Manager simple form renderer.
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_form_simple extends midcom_helper_datamanager_form
{

    /**
     * Checks our POST data and acts accordingly
     */
    public function process()
    {
        $handle_locks = false;
        if (   $this->datamanager->storage instanceof midcom_helper_datamanager_storage_midgard
            && $this->datamanager->storage->object
            && $this->datamanager->storage->object->guid)
        {
            if (midcom_core_helpers_metadata::is_locked($this->datamanager->storage->object))
            {
                throw new midcom_helper_datamanager_exception_locked();
            }
            $handle_locks = true;
        }

        $results = $this->get_submit_values();
        $operation = $this->compute_form_result();

        switch ($operation)
        {
            case 'cancel':
                if ($handle_locks)
                {
                    midcom_core_helpers_metadata::unlock($this->datamanager->storage->object);
                }
                throw new midcom_helper_datamanager_exception_cancel();
            
            case 'previous':
            case 'edit':
                // What ?
                if ($handle_locks)
                {
                    midcom_core_helpers_metadata::lock($this->datamanager->storage->object);
                }
                break;

            case 'next':
                $this->pass_results_to_method('on_submit', $results, true);
                $this->pass_results_to_method('sync_widget2type', $results, false);
                if ($handle_locks)
                {
                    midcom_core_helpers_metadata::lock($this->datamanager->storage->object);
                }
                // and then what ?
                break;

            case 'save':
                if ($handle_locks)
                {
                    midcom_core_helpers_metadata::unlock($this->datamanager->storage->object);
                }
                $this->pass_results_to_method('on_submit', $results, true);
                $this->pass_results_to_method('sync_widget2type', $results, false);
                
                if ($this->datamanager->save())
                {
                    // Saved successfully
                    throw new midcom_helper_datamanager_exception_save();
                }
                // and then what ?
                break;

            default:
                throw new midcom_helper_datamanager_exception_datamanager("Don't know how to handle operation {$operation}");
        }
        return $operation;
    }

}

?>