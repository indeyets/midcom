<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:navigation.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Base class to encapsulate a NAP interface. Does all the necessary work for
 * setting the object to the right topic. you just have to fill the gaps for
 * getting the leaves and node data.
 *
 * Normally, it is enough if you override the members list_leaves() and get_node().
 * You usually don't even need to write a constructor, as the default one should
 * be enough for your purposes. If you need extra initialization work done when
 * "entering" a topic, use the event handler _on_set_object().
 *
 * @package midcom.baseclasses
 */

class midcom_baseclasses_components_navigation
{
    /**#@+
     * Component state variable, set during startup. There should be no need to change it
     * in most cases.
     *
     * @access protected
     */

    /**
     * Internal helper, holds the name of the component. Should be used whenever the
     * components' name is required instead of hardcoding it.
     *
     * @var string
     */
    var $_component = '';

    /**
     * Component data storage area.
     *
     * @var Array
     */
    var $_component_data = null;

    /**
     * The topic for which we are handling a request.
     *
     * @var MidgardTopic
     */
    var $_topic = null;

    /**
     * The current configuration.
     *
     * @var midcom_helper_configuration
     */
    var $_config = null;

    /**
     * A handle to the i18n service.
     *
     * @var midcom_services_i18n
     */
    var $_i18n = null;

    /**
     * The components' L10n string database
     *
     * @var midcom_services__i18n_l10n
     */
    var $_l10n = null;

    /**
     * The global MidCOM string database
     *
     * @var midcom_services__i18n_l10n
     */
    var $_l10n_midcom = null;

    /**#@-*/

    /**
     * Create the navigation instance, the constructor doesn't do anything
     * yet, startup is handled by initialize().
     */
    public function __construct()
    {
        // Nothing to do
    }

    /**
     * Initialize the NAP class, sets all state variables.
     *
     * @param string $component The name of the component.
     */
    public function initialize($component)
    {
        $this->_component = $component;
        $this->_component_data =& $GLOBALS['midcom_component_data'][$this->_component];

        $this->_i18n =& $_MIDCOM->get_service('i18n');
        $this->_l10n =& $this->_i18n->get_l10n($this->_component);
        $this->_l10n_midcom =& $this->_i18n->get_l10n('midcom');

        $this->_config = $this->_component_data['config'];
    }

    /**
     * Leaf listing function, the default implementation returns an empty array indicating
     * no leaves. Note, that the active leaf index set by the other parts of the component
     * must match one leaf out of this list.
     *
     * Here are some code fragments, that you usually connect through some kind of
     * while $articles->fetch() loop:
     *
     * <code>
     * <?php
     *  // Prepare the toolbar
     *  $toolbar[50] = Array(
     *      MIDCOM_TOOLBAR_URL => '',
     *      MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
     *      MIDCOM_TOOLBAR_HELPTEXT => null,
     *      MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
     *      MIDCOM_TOOLBAR_ENABLED => true
     *  );
     *  $toolbar[51] = Array(
     *      MIDCOM_TOOLBAR_URL => '',
     *      MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
     *      MIDCOM_TOOLBAR_HELPTEXT => null,
     *      MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
     *      MIDCOM_TOOLBAR_ENABLED => true
     *  );
     *
     *  while ($articles->fetch ()) {
     *      // Match the toolbar to the correct URL.
     *      $toolbar[50][MIDCOM_TOOLBAR_URL] = "edit/{$articles->id}.html";
     *      $toolbar[51][MIDCOM_TOOLBAR_URL] = "delete/{$articles->id}.html";
     *
     *      $leaves[$articles->id] = array
     *      (
     *          MIDCOM_NAV_SITE => Array
     *          (
     *              MIDCOM_NAV_URL => $articles->name . ".html",
     *              MIDCOM_NAV_NAME => ($articles->title != "") ? $articles->title : $articles->name
     *          ),
     *          MIDCOM_NAV_ADMIN => Array
     *          (
     *              MIDCOM_NAV_URL => "view/" . $articles->id,
     *              MIDCOM_NAV_NAME => ($articles->title != "") ? $articles->title : $articles->name
     *          ),
     *          MIDCOM_NAV_GUID => $articles->guid(),
     *          MIDCOM_NAV_TOOLBAR => $toolbar,
     *          MIDCOM_META_CREATOR => $articles->creator,
     *          MIDCOM_META_EDITOR => $articles->revisor,
     *          MIDCOM_META_CREATED => $articles->created,
     *          MIDCOM_META_EDITED => $articles->revised
     *      )
     *  }
     *
     *  return $leaves;
     *
     * ?>
     * </code>
     *
     * @return Array NAP compliant list of leaves.
     */
    public function get_leaves()
    {
        return Array();
    }

    /**
     * Return the node configuration. This defaults to use the topic the
     * NAP instance has been set to directly. You can usually fall back to this
     * behavior safely, adding a toolbar using the $toolbar parameter in child classes.
     *
     * So you'd probably do something like this:
     *
     * <code>
     * <?php
     *  $toolbar = Array();
     *  $toolbar[100] = Array
     *  (
     *      MIDCOM_TOOLBAR_URL => 'config.html',
     *      MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
     *      MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
     *      MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
     *      MIDCOM_TOOLBAR_ENABLED => true
     *  );
     *  return parent::get_node($toolbar);
     * ?>
     * </code>
     *
     * The default uses the extra field of the topic as NAV_NAME, which is the
     * default set by MidCOM AIS and shouldn't be changed therefore.
     *
     * @param Array $toolbar Set this parameter in child classes calling this base method to add a toolbar to the default node listing.
     *   This parameter is not set by the framework and can safely be omitted in the average base class.
     * @return Array NAP compliant node declaration
     */
    public function get_node($toolbar = null)
    {
        if (!$this->_topic->metadata)
        {
            return null;
        }

        return array (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_NAV_CONFIGURATION => $this->_config,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised,
        );
    }


    /**
     * Set a new content object. This updates the local configuration copy with the
     * topic in question. It calls the event handler _on_set_object after initializing
     * everything in case you need to do some custom initializations as well.
     *
     * @param MidgardTopic $topic The topic to process.
     * @return boolean Indicating success.
     */
    public function set_object($topic)
    {
        $this->_topic = $topic;
        $this->_config->store_from_object($topic, $this->_component);

        return $this->_on_set_object();
    }

    /**
     * Event handler called after a new topic has been set. The configuration is
     * already loaded at this point.
     *
     * @access protected
     * @return boolean Set this to false to indicate that you could not set this instance
     *   to the topic. NAP will abort loading this node and log the error accordingly.
     *   Return true if everything is fine.
     */
    function _on_set_object()
    {
        return true;
    }
}
?>