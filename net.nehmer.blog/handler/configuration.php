<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once(MIDCOM_ROOT . '/midcom/core/handler/configdm.php');

/**
 * TAViewer component configuration screen.
 *
 * This class extends the standard configdm mechanism as we need a few hooks for the
 * symlink topic stuff.
 *
 * @package net.nehmer.blog
 */
class net_nehmer_blog_handler_configuration extends midcom_core_handler_configdm
{
    function net_nehmer_blog_handler_configuration()
    {
        parent::midcom_core_handler_configdm();
    }

    /**
     * Populate a single global variable with the current schema database, so that the
     * configuration schema works again.
     *
     * @todo Rewrite this to use the real schema select widget, which is based on some
     *     other field which contains the URL of the schema.
     */
    function _on_handler_configdm_preparing()
    {
        $GLOBALS['net_nehmer_blog_schemadbs'] = array_merge
        (
            Array
            (
                '' => $this->_l10n->get('default setting')
            ),
            $this->_config->get('schemadbs')
        );
    }

}

/**
 * Symlink topic list base function, this calls mgd_walk_topic_tree, which in turn calls
 * net_nehmer_blog_symlink_topic_list_loop().
 *
 * @todo Rewrite to use some intelligent QB driven code.
 * @return Array A list of guid > Topic name pairs.
 */
function net_nehmer_blog_symlink_topic_list()
{
    $newstopics = array
    (
        '' => '',
    );

    $nav = new midcom_helper_nav();
    $mc = midcom_db_topic::new_collector('component', 'net.nehmer.blog');
    $mc->add_value_property('id');
    $mc->execute();
    $topics = $mc->list_keys();
    foreach ($topics as $topic_guid => $value)
    {
        $id = $mc->get_subkey($topic_guid, 'id');
        $nav = new midcom_helper_nav();
        $path = $nav->get_breadcrumb_data($id);

        $path_components = array();
        foreach ($path as $node)
        {
            $path_components[] = (string) $node[MIDCOM_NAV_NAME];
        }

        $breadcrumb = implode(' > ', $path_components);
        $newstopics[$topic_guid] = $breadcrumb;
    }

    return $newstopics;
}

?>