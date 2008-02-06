<?php
/**
 * @package pl.olga.mnogosearch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 4153 2006-09-20 18:28:00Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * p.o.mnogosearch site interface class
 *
 * This is a complete rewrite of the topic-article viewer the has been made for MidCOM 2.6.
 * It incorporates all of the goodies current MidCOM has to offer and can serve as an
 * example component therefore.
 *
 * @package pl.olga.mnogosearch
 */
class pl_olga_mnogosearch_viewer extends midcom_baseclasses_components_request
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    function pl_olga_mnogosearch_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {

        $this->_request_data['content_topic'] =& $this->_topic;

        // *** Prepare the request switch ***

        $this->_request_switch['config'] = Array
        (
            'handler' => Array('pl_olga_mnogosearch_handler_config', 'config'),
        'fixed_args' => array('config'),
        );

        $this->_request_switch['view'] = Array
        (
            'handler' => Array('pl_olga_mnogosearch_handler_view', 'view'),
        );
    debug_pop();
    }


    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
//        $this->_request_data['schemadb'] =
//            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        return true;
    }

}

?>