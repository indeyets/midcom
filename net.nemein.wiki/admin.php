<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wiki Admin interface class.
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_admin extends midcom_baseclasses_components_request_admin
{

    function net_nemein_wiki_admin($topic, $config) 
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    function _on_initialize()
    {
        // TODO: Create index article if it is missing
        // Old code for it was:
/*
      // Create root wiki page as required
      if (!mgd_get_article_by_name($this->_topic->id, "index")) {
        $article = mgd_get_article();
        $article->title = $this->_topic->extra;
        $article->name = "index";
        $article->topic = $this->_topic->id;
        $article->author = $GLOBALS["midgard"]->user;
        $article->content = $this->_l10n->get('wiki default page content');
        $stat = $article->create();
        if ($stat) {
          debug_add("Root wikipage article $stat created");
        } else {
         debug_add("Failed to create root wikipage article, reason ".mgd_errstr());
        }
      }
*/        
        
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'schemadb' => 'file:/net/nemein/wiki/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => true,
        );
    }
}
?>