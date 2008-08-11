<?php
/**
 * @package org.maemo.socialnews 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.maemo.socialnews
 * 
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_maemo_socialnews_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.maemo.socialnews';        
    }

    function _on_watched_dba_create($favourite)
    {
        if ($favourite->objectType != 'midgard_article')
        {
            // We only recalculate for articles now
            return;
        }
        
        if (!$_MIDCOM->componentloader->load_graceful('midcom.services.at'))
        {
            // No at system installed, skip
            return;
        }
        
        // Register the recalculate to midcom.services.at instead of running interactively
        $args = array
        (
            'article' => $favourite->objectGuid,
        );
        
        $atstat = midcom_services_at_interface::register(time(), 'org.maemo.socialnews', 'recalculate', $args);
        if (!$atstat)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to register at service to recalculate score for item {$favourite->guid}", MIDCOM_LOG_WARN);
            debug_pop();
        }
        
        $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.maemo.socialnews', 'org.maemo.socialnews'), sprintf($_MIDCOM->i18n->get_string('item %s has been registered for score recalculation', 'org.maemo.socialnews'), $favourite->objectTitle), 'ok');
    }
    
   /**
     * AT handler for handling recalculations
     * @param array $args handler arguments
     * @param object &$handler reference to the cron_handler object calling this method.
     * @return boolean indicating success/failure
     */
    function recalculate($args, &$handler)
    {
        $article = new midcom_db_article($args['article']);
        if (   !$article
            || !$article->guid)
        {
            return false;
        }
        $calculator = new org_maemo_socialnews_calculator();
        $calculator->calculate_article($article, true);
        
        return true;
    }
}
?>