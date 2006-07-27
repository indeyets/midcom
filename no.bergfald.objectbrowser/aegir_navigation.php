<?php

/**
 * Created on Aug 3, 2005
 * @package no.bergfald.objectbrowser
 */

class no_bergfald_objectbrowser_aegir_navigation extends midcom_admin_aegir_module_navigation
{

    /**
     * Id of the current node.
     * @access private
     * @var string
     */
    var $_current_node = null;
    /**
     * Id of current leaf
     *  @access private
     * @var string
     */
    var $_current_leaf = null;

    /**
     * Id of tree_root (where applicable)
     * @access private
     * @var string
     */
    var $_root_node = '0';

    /**
     * very simple cache for those nasty list_nodes get node combos
     * @var array of midgardobjects.
     */
    var $_obj_cache = array ();

    /**
     * Pointer to the schema object
     * @var no_bergfald_objectbrowser_schema
     * @access private
     */
    var $_schema = null;
    function no_bergfald_objectbrowser_aegir_navigation()
    {
        if (!class_exists("no_bergfald_objectbrowser_schema"))
        {
            require_once (MIDCOM_ROOT . "/no/bergfald/objectbrowser/schema.php");
        }
        $this->_schema = & no_bergfald_objectbrowser_schema :: get_instance();
    }

    /**
     * What the root node is and how it is defined might be different for different
     * handlers. Therefore we need a way to check this.
     * @return string node_id
     */
    function get_root_node()
    {
        return '0';
    }
    /**
     * Check if a node_id is the root node of the tree.
     * @return boolean true if node_id is root node.
     * @param string node_id
     */
    function is_root_node($node_id)
    {
        return $node_id == 'none';

    }

    /**
     * Reads a node data structure from the database
     *
     * @param mixed
     * 		int $id The ID of the topic for which the NAP information is requested.
     * 		midcom_baseclasses_database_topic
     * @return Array Node data structure
     * @access public
     */

    function get_node($guid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (array_key_exists($guid, $this->_obj_cache))
        {
            $object = & $this->_obj_cache[$guid];
        }
        elseif (!is_object($guid))
        {
            debug_add("Trying to load $guid from the db");
            if (mgd_is_guid($guid))
            {

                $object = mgd_get_new_object_by_guid($guid);
                $this->_obj_cache[$guid] = & $object;
            }
            elseif ($this->_schema->objecttype_exists($guid))
            {

                $nodedata = array ();
                $nodedata[MIDCOM_NAV_NAME] = $this->_schema->get_type_name($guid);
                $nodedata[MIDCOM_NAV_GUID] = $guid;
                $nodedata[MIDCOM_NAV_ID] = $guid;
                $nodedata[MIDCOM_NAV_TYPE] = 'node';
                $nodedata[MIDCOM_NAV_COMPONENT] = 'objectbrowser';
                $nodedata[MIDCOM_NAV_URL] = "$guid";
                $nodedata[MIDCOM_NAV_ICON] = null;
                debug_pop();
                return $nodedata;
            }
            else
            {
                debug_add("Object $guid not found!");
                debug_pop();

                return false;
            }
        }
        else
        {

            $object = & $guid;
        }

        if (!$object)
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Cannot load NAP information, aborting: Could not load the {$guid} {$object->name} from the database (".mgd_errstr().').');
        }

        $name_attribute = $this->_schema->get_name_attribute(& $object);
        $nodedata[MIDCOM_NAV_NAME] = "";
        foreach ($name_attribute as $attr) {
            if (isset ($object->$attr)) {
                $nodedata[MIDCOM_NAV_NAME] .= $object->$attr;
            }
        }
        if ($nodedata[MIDCOM_NAV_NAME] == "") {
            $nodedata[MIDCOM_NAV_NAME] = "Id: " . $object->id . " " . implode ($name_attribute, " ");
        }
        // Now complete the node data structure, we need a metadata object for this:
        $nodedata[MIDCOM_NAV_URL] = $object->guid;
        //$nodedata[MIDCOM_NAV_NAME] = trim($nodedata[MIDCOM_NAV_NAME]) == '' ? $object->name : $nodedata[MIDCOM_NAV_NAME];
        $nodedata[MIDCOM_NAV_GUID] = $object->guid;
        $nodedata[MIDCOM_NAV_ID] = $object->guid;
        $nodedata[MIDCOM_NAV_TYPE] = 'node';
        //$nodedata[MIDCOM_NAV_SCORE] = $object->score;
        $nodedata[MIDCOM_NAV_OBJECT] = & $object;
        $nodedata[MIDCOM_NAV_ICON] = null;
        $nodedata[MIDCOM_NAV_COMPONENT] = 'objectbrowser';
        /*
        $nodedata[MIDCOM_NAV_SUBNODES] = null;
        $nodedata[MIDCOM_NAV_LEAVES] = null;
        */

        debug_pop();
        return $nodedata;

    }
    /**
     * This will give you a key-value pair describeing the leaf with the ID
     * $node_id.
     * The defined keys are described above in leaf data interchange
     * format. You will get false if the leaf ID is invalid.
     *
     * @param string $guid	The leaf-id to be retrieved.
     * @return Array		The leaf-data as outlined in the class introduction, false on failure
     */
    function get_leaf($guid)
    {
        //debug_push_class(__CLASS__, __FUNCTION__);

        if (array_key_exists($guid, $this->_obj_cache))
        {
            $object = & $this->_obj_cache[$guid];

        }
        elseif (!is_object($guid))
        {

            $object = mgd_get_new_object_by_guid($guid);

        }
        else
        {
            $object = & $guid;
        }

        if (!$object)
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Get_leaf: Cannot load navigation information, aborting: Could not load the object {$guid} from the database (".mgd_errstr().').');
            // This will exit().
        }
        //debug_add("Trying to load NAP data for object {$object->name} (#{$object->id})");

        // Now complete the node data structure, we need a metadata object for this:
        $leaf[MIDCOM_NAV_NAME] = $object->name;
        $leaf[MIDCOM_NAV_URL] = $object->guid;
        $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL] = $object->guid;
        $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_NAME] = $object->name;
        $leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_URL] = $object->guid;
        $leaf[MIDCOM_NAV_NAME] = trim($leaf[MIDCOM_NAV_NAME]) == '' ? $object->name : $leaf[MIDCOM_NAV_NAME];
        $leaf[MIDCOM_NAV_GUID] = $object->guid;
        $leaf[MIDCOM_NAV_ID] = $object->guid;
        $leaf[MIDCOM_NAV_TYPE] = 'leaf';
        $leaf[MIDCOM_NAV_ICON] = null;
        $leaf[MIDCOM_NAV_COMPONENT] = 'objectbrowser';
        $leaf[MIDCOM_NAV_OBJECT] = & $object;

        return $leaf;
    }
    /**
     * list the nodes below a certain point
     * This is done in the following way, based on the $node_up:
     * none => list all objecttypes
     * <objectname> => list all objects with up == this object or all if up is undef.
     * <guid>  => List all objects with this as their upvalue
     *
     *
     *
     */
    function list_nodes($node_up = 0, $nodename = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $nodes = array ();
        // debug_add("Listing nodes for $node_up");

        // TODO (tn): If this string checks here is a check for the GUID of the object,
        // it should be replaced by mgd_is_guid.
        if ($node_up == '0' || (strlen($node_up) != 32 && strlen($node_up) != 80))
        {
            // debug_add("Node $node_up ($nodename ) is not a guid-node " . strlen($node_up) );
            if ($node_up == '0')
            {
                $i = 0;
                foreach ($_MIDGARD['schema']['types'] as $type => $vl)
                {
                    if (!$this->_schema->is_leaf($type) && !$this->_schema->nav_hide($type))
                    {
                        $nodes[$i] = $type;
                        $i ++;
                    }
                }

                return $nodes;

            }
            elseif ($this->_schema->objecttype_exists($node_up))
            {
                //debug_add("Creating querybuilder for node $node_up, expecting this to be a");
                $qb = new MidgardQueryBuilder($node_up);
                if (!$qb)
                {
                    debug_add("Could not create querybuilder for $node_up");
                }

                $up_attribute = $this->_schema->get_up_attribute($node_up);
                if ($up_attribute != '')
                {
                    debug_add("ARRAY adding constraint $up_attribute for $node_up");
                    $qb->add_constraint($up_attribute, '=', 0);

                }

                //  debug_add("Executing query for $node_up, $up_attribute");
                $result =  $qb->execute();


                if ($result === null )
                {
                     debug_add("God nullresult for $node_up");
                }
                /* I'm not sure if this is the best solution, or if it is a temporary workaround.
                 * Point is: Not all of the groups (the one with id 0)  had a guid!
                 * */
                if ($node_up == 'midgard_group')
                {
                    array_shift($result);
                }

                if ($result !== null)
                {
                    for ($i = 0; $i < count($result); $i ++)
                    {
                        $nodes[$i] = $result[$i]->guid;
                    }
                    return $nodes;
                }
                debug_add("Returning empty ARRAY for $node_up");
                return array ();
            }
        }
        if (array_key_exists($node_up, $this->_obj_cache))
        {
            $object = & $this->_obj_cache[$node_up];
        }
        else
        {
            $object = mgd_get_new_object_by_guid($node_up);
        }

        $type = get_class($object);
        $up_attribute = $this->_schema->get_up_attribute($type);

        $qb = new MidgardQueryBuilder($type);

        if (!$up_attribute)
        {
            debug_add("Attribute $type does not have an up attribute, therefor not listing");
            return array ();

        }

        //debug_add("Executing query for $node_up, type: $type  (up:$up_attribute), id: " . $object->id);
        if ($up_attribute)
        {
            $qb->add_constraint($up_attribute, '=', $object->id);
        }
        /*
        if ($object->sitegroup > 0 ) {
              $qb->add_constraint('sitegroup', '=', $object->sitegroup);
        }
        */

        if (!$result = @ $qb->execute())
        {
            //debug_add(" Query failed because:" . mgd_errstr());
        }

        $nodes = array ();
        for ($i = 0; $i < count($result); $i ++)
        {
            $this->_obj_cache[$result[$i]->guid] = & $result[$i];
            $nodes[$i] = $result[$i]->guid;
        }

        debug_pop();
        return $nodes;

    }
    /**
    * Leaf listing function, the default implementation returns an empty array indicating
    * no leaves. Note, that the active leaf index set by the other parts of the component
    * must match one leav out of this list.
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
    * @return Array NAP compilant list of leaves.
    */
    /**
     * Returns all leaves for the current content topic.
     *
     * It will hide the index leaf from the NAP information unless we are in Autoindex
     * mode. The leaves' title are used as a description within NAP, and the toolbar will
     * contain edit and delete links.
     */
    function get_leaves($node_up = 0)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $sort = 'score';
        $reverse = false;
        $return = array ();
        if (!$sort)
        {
            $sort = 'score';
        }
        if (substr($sort, 0, 7) == 'reverse')
        {
            $sort = substr($sort, 8);
            $reverse = true;
        }

        if ($node_up == 0)
            return array ();

        $object = mgd_get_new_object_by_guid($node_up);
        $type = get_class($object);

        $children = $this->_schema->get_children(& $object);

        foreach ($children as $child => $nada)
        {

            $qb = new MidgardQueryBuilder($child);
            $qb->add_constraint($child['up'], '=', $object->id);

            $result = $qb->execute();

            // Prepare everything

            $toolbar[50] = Array (MIDCOM_TOOLBAR_URL => '', MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'), MIDCOM_TOOLBAR_HELPTEXT => null, MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png', MIDCOM_TOOLBAR_ENABLED => true);
            $toolbar[51] = Array (MIDCOM_TOOLBAR_URL => '', MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'), MIDCOM_TOOLBAR_HELPTEXT => null, MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png', MIDCOM_TOOLBAR_ENABLED => true);

            foreach ($result as $leaf)
            {
                // Match the toolbar to the correct URL.
                $toolbar[50][MIDCOM_TOOLBAR_URL] = "objectbrowser/edit/{$leaf->guid}.html";
                $toolbar[50][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:update', $leaf) == false);
                $toolbar[51][MIDCOM_TOOLBAR_URL] = "objectbrowser/delete/{$leaf->guid}.html";
                $toolbar[51][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:delete', $leaf) == false);

                $leaves[$leaf->guid] = array (MIDCOM_NAV_SITE => Array (MIDCOM_NAV_URL => "objectbrowser/{$leaf->guid}.html", MIDCOM_NAV_NAME => ($leaf->title != '') ? $leaf->title : $leaf->name), MIDCOM_NAV_URL => "objectbrowser/{$leaf->guid}.html", MIDCOM_NAV_NAME => ($leaf->title != '') ? $leaf->title : $leaf->name, MIDCOM_NAV_ADMIN => Array (MIDCOM_NAV_URL => "objectbrowser/{$leaf->guid}", MIDCOM_NAV_NAME => ($leaf->title != '') ? $leaf->title : $leaf->name), MIDCOM_NAV_GUID => $leaf->guid, MIDCOM_NAV_TOOLBAR => $toolbar, MIDCOM_NAV_TYPE => 'leaf', MIDCOM_META_CREATOR => $leaf->creator, MIDCOM_META_EDITOR => $leaf->revisor, MIDCOM_META_CREATED => $leaf->created, MIDCOM_META_EDITED => $leaf->revised);

            }
        }

        debug_pop();
        return $leaves;
    }

    /* Disabled and replaced by mgd_is_guid (tn)
     * TODO: Normally this can be removed unless it is called from the outside
     *     which shouldn't happen for a private function though.
     *
     *  *
     * Decide if a string is a guid
     * @param string guid strin
     * @return boolean true if guid
     * /
    function _is_guid($string)
    {
        $strlen = strlen($string);
        if ($strlen == 32 || $strlen == 80)
        {
            return true;
        }
    }
    */

    function list_leaves($node_up)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (!mgd_is_guid($node_up))
        {
            debug_pop();
            return array ();
        }

        $object = mgd_get_new_object_by_guid($node_up);
        $type = get_class($object);

        $children = $this->_schema->get_children($type);
        $i = 0;
        $leaves = array ();

        if ($children !== null)
            foreach ($children as $child => $nada)
            {
                $result = array ();
                $qb = null;
                $qb = new MidgardQueryBuilder($child);
                if (!$qb)
                {
                    debug_add("Failed to create querybuilder for $child type object!");
                }
                $up_attribute = $this->_schema->get_leaf_up_attribute($child);

                if (!$up_attribute)
                {
                    debug_add("No up attribute for $child", MIDCOM_LOG_DEBUG);
                    continue;
                }

                $qb->add_constraint($up_attribute, '=', $object->id);

                // $qb->add_order($sort);
                $result = @ $qb->execute();
                if (is_null($result))
                {
                    continue;
                }

                //debug_add("Nr of leaves: " . $qb->count());
                /* remember_ you're in a foreach loop!' */
                foreach ($result as $key => $leaf)
                {
                    //debug_add("Adding object $key => {$result[$key]->guid}");
                    $this->_obj_cache[$result[$key]->guid] = & $result[$key];
                    $leaves[$i] = $result[$key]->guid;
                    $i ++;
                }
            }
        debug_pop();

        return $leaves;
    }

    /**
     * Returns the ID of the node to which $leaf_id is accociated to, false
     * on failure.
     *
     * @param string $leaf_id   The Leaf-ID to search an uplink for.
     * @return int          The ID of the Node for which we have a match, or false on failure.
     * @see midcom_helper__basicnav::get_leaf_uplink()
     */
    function get_leaf_uplink($leafid)
    {

        $leaf = $this->get_leaf($leafid);

        $leaf_class = get_class($node[MIDCOM_NAV_OBJECT]);

        $parent_class = $node[MIDCOM_NAV_OBJECT]->parent();
        $up_attribute = $this->_schema->get_leaf_up_attribute($leaf_class);

        /* no up == no parent... */
        if (!$up_attribute)
        {
            return 0;
        }

        $parent = new $parent_class ();
        $parent->get_by_id($leaf[MIDCOM_NAV_OBJECT]-> {
            $up_attribute });

        $this->_obj_cache[$parent->guid] = & $parent;

        return $parent->guid;
    }
    /**
     * Returns the ID of the node to which $node_id is assosiated to, false
     * on failure. The root node's uplink is -1.
     *
     * @param int $node_id  The Leaf-ID to search an uplink for.
     * @return int          The ID of the Node for which we have a match, -1 for the root node, or false on failure.
     * @see midcom_helper__basicnav::get_node_uplink()
     */
    function get_node_uplink($nodeid)
    {

        $node = $this->get_node($nodeid);
        if (!$node || !array_key_exists(MIDCOM_NAV_OBJECT, $node))
        {
            return -1;
        }

        $node_class = get_class($node[MIDCOM_NAV_OBJECT]);

        $parent_class = $node[MIDCOM_NAV_OBJECT]->parent();

        if ($node_class == $parent_class)
        { // node..
            $up_attribute = $this->_schema->get_up_attribute($node_class);
        }
        else
        {
            // leaf
            $up_attribute = $this->_schema->get_leaf_up_attribute($node_class);
        }

        /* no up == no parent... */
        if (!$up_attribute)
        {
            //debug_add("Returning node_up == 0");
            return -1;
        }
        if ($node[MIDCOM_NAV_OBJECT]-> {
            $up_attribute }
        == 0)
        {
            return $parent_class;
        }
        $parent = new $parent_class ();
        $parent->get_by_id($node[MIDCOM_NAV_OBJECT]-> {
            $up_attribute });

        $this->_obj_cache[$parent->guid] = & $parent;
        return $parent->guid;
    }



}
?>

