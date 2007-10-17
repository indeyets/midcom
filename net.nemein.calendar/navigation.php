<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar NAP interface class.
 * 
 * @package net.nemein.calendar
 */

class net_nemein_calendar_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;
    
    /**
     * Simple constructor, calls base class.
     */
    function net_nemein_calendar_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Returns a static leaf list with access to the archive.
     */
    function get_leaves()
    {
        $leaves = array();
        
        if (   $this->_config->get('archive_enable')
            && $this->_config->get('archive_in_navigation')
            && $this->_config->get('show_navigation_pseudo_leaves'))
        {
            $leaves["{$this->_topic->id}_ARCHIVE"] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "archive/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('archive'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                MIDCOM_META_CREATED => $this->_topic->metadata->created,
                MIDCOM_META_EDITED => $this->_topic->metadata->revised,
            );
        }
        
        if (   $this->_config->get('show_navigation_pseudo_leaves')
            && $this->_config->get('categories_in_navigation')
            && $this->_config->get('categories') != '')
        {
            $categories = explode(',', $this->_config->get('categories'));
            foreach ($categories as $category)
            {
                $leaves["{$this->_topic->id}_CAT_{$category}"] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "?net_nemein_calendar_category={$category}",
                        MIDCOM_NAV_NAME => $category,
                    ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                    MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                    MIDCOM_META_CREATED => $this->_topic->metadata->created,
                    MIDCOM_META_EDITED => $this->_topic->metadata->revised,
                );
            }
        }
        
        if (   $this->_config->get('show_navigation_pseudo_leaves')
            && $this->_config->get('archive_years_in_navigation'))
        {
            // Check for symlink
            if (!$this->_content_topic)
            {
                $this->_determine_content_topic();
            }

            $qb = net_nemein_calendar_event_dba::new_query_builder();    
            $qb->add_constraint('node', '=', $this->_content_topic->id);
            $qb->add_order('start');
            $qb->set_limit(1);
            $result = $qb->execute_unchecked();
            $first_year = (int) date('Y', strtotime($result[0]->start));
            $year = $first_year;
            $this_year = (int) date('Y', time());
            while ($year <= $this_year)
            {
                $next_year = $year + 1;
                $leaves["{$this->_topic->id}_ARCHIVE_{$year}"] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "archive/between/{$year}-01-01/{$next_year}-01-01/",
                        MIDCOM_NAV_NAME => $year,
                    ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                    MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                    MIDCOM_META_CREATED => $this->_topic->metadata->created,
                    MIDCOM_META_EDITED => $this->_topic->metadata->revised,
                );
                $year = $next_year;
            }
            $leaves = array_reverse($leaves);
        }
        
        return $leaves;
    }
    
    /**
     * Set the content topic to use. This will check against the configuration setting 'symlink_topic'.
     * We don't do sanity checking here for performance reasons, it is done when accessing the topic,
     * that should be enough.
     *
     * @access protected
     */
    function _determine_content_topic()
    {

        $guid = $this->_config->get('symlink_topic');
        if (is_null($guid))
        {
            // No symlink topic
            // Workaround, we should talk to an DBA object automatically here in fact.
            $this->_content_topic = new midcom_db_topic($this->_topic->id);
            debug_pop();
            return;
        }

        $this->_content_topic = new midcom_db_topic($guid);

        if (! $this->_content_topic)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: ' . mgd_errstr(),
                MIDCOM_LOG_ERROR);
            debug_pop();
            $_MIDCOM->generate_error('Failed to open symlink content topic.');
            // This will exit.
        }

    }
}

?>