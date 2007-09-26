<?php
/**
 * @package net.nehmer.static
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
 * @package net.nehmer.static
 */
class net_nehmer_static_handler_configuration extends midcom_core_handler_configdm
{
    function net_nehmer_static_handler_configuration()
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
        $GLOBALS['net_nehmer_static_schemadbs'] = array_merge
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
 * net_nehmer_static_symlink_topic_list_loop().
 *
 * @todo Rewrite to use some intelligent QB driven code.
 * @return Array A list of guid > Topic name pairs.
 */
function net_nehmer_static_symlink_topic_list()
{
    $midgard = mgd_get_midgard();
    $sg = $midgard->sitegroup;

    $param = Array
    (
        "result" => Array("" => $_MIDCOM->i18n->get_string('symlink_topic disabled')),
        "stack" => Array(),
        "last_level" => 0,
        "last_topic" => null,
        "glue" => " > ",
        "sg" => $sg,
    );

    mgd_walk_topic_tree("net_nehmer_static_symlink_topic_list_loop", 0, 99, &$param, true, "name");

    return $param["result"];
}

function net_nehmer_static_symlink_topic_list_loop ($topicid, $level, &$param)
{
    if ($topicid == 0)
    {
        // debug_add("Topic ID is 0, skipping this one, it is the lists root.");
        return;
    }
    $topic = new midcom_db_topic($topicid);
    if ($param["sg"] != 0 && $topic->sitegroup != $param["sg"])
    {
        return;
    }

    if ($level > $param["last_level"])
    {
        if ($param["last_level"] != 0)
        {
            array_push($param["stack"], $param["last_topic"]);
        }
        $param["last_level"] = $level;
    }
    else if ($level < $param["last_level"])
    {
        for ($i = $param["last_level"]; $i > $level; $i--)
        {
            array_pop($param["stack"]);
        }
        $param["last_level"] = $level;
    }

    $guid = $topic->guid();
    if ($topic->extra != "")
    {
        $topicname = substr($topic->extra, 0, 30);
    }
    else
    {
        $topicname = substr($topic->name, 0, 30);
    }
    $param["last_topic"] = $topicname;

    if ($topic->parameter("midcom", "component") != "net.nehmer.static")
    {
        return;
    }

    if ($level > 1)
    {
        $param["result"][$guid] = implode($param["glue"], $param["stack"]) . $param["glue"] . $topicname;
    }
    else
    {
        $param["result"][$guid] = $topicname;
    }
}

?>