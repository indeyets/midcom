<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.32 2006/06/08 14:12:38 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * @package org.openpsa.contacts
 */
class fi_incognito_protustree_item {
    var $id = 0;
    var $name = "";
    var $type = "";
   // var $children = array();

    /*
     * id:String (Objects guid)
     * name:String
     * type: String
     * children:Array or Boolean
     */
    function fi_incognito_protustree_item($id="",$name="",$type="group",$children="load_later",$options=array()) {
        // print("\nfi_incognito_protustree_item:".$name."\n");
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->parseOptions($options);
        $this->addChildren($children);
    }

    /*
     * Will receive empty array if item has no children,
     * "load_later":String if we are at the maximum depth and item has children
     */
    function addChildren($children) {
        // print("\nchildren:\n");
        // print_r($children);
        // print("\n");
         if($children == "load_later") {
         } else {
            if(count($children) == 0 || empty($children)) {
                $this->children = array();
            }
            if(count($children) > 0) {
                $this->children = $children;
            }

        }
    }

    function parseOptions($options) {
        // print("\noptions:\n");
        // print_r($options);
        // print("\n");

        if(isset($options['parent_id'])) {
            $this->parent_id = $options['parent_id'];
        }
        if(isset($options['parent_type']) && $options['parent_type'] != "") {
            $this->parent_type = $options['parent_type'];
        }
    }

    function as_json() {
        return json_encode($this);
    }

}

/**
 * org.openpsa.contacts MFA interface class.
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_mfa extends midcom_baseclasses_components_handler
{
    var $current_depth = 0;
    var $queryRoot = array();
    var $asQueryRoot = false;
    var $rootItem = "";
    var $start_from_root = false;
    var $first_time_get = true;

    /**
     * Simple constructor, which only initializes the parent constructor.
     */
    function org_openpsa_contacts_mfa()
    {
        parent::midcom_baseclasses_components_handler();
        $this->queryRoot = array( 'id' => '', 'name' => '', 'type' => '' );
        $this->rootItem = new fi_incognito_protustree_item(0,"Groups root","group",array());
        $this->active_depth = 0;
        $this->search_depth = 3;
        $this->first_time_get = true;
    }

    /**
     * Get the plugin handlers, which act alike with Request Switches of MidCOM
     * Baseclasses Components (midcom.baseclasses.components.request)
     *
     * @access public
     */
    function get_plugin_handlers()
    {
        return Array
        (
            'tree_json' => Array
            (
                'handler' => Array('org_openpsa_contacts_mfa', 'tree'),
                'fixed_args' => array('tree', 'json'),
            ),
            'tree_json2' => Array
            (
                'handler' => Array('org_openpsa_contacts_mfa', 'tree'),
                'fixed_args' => array('tree', 'json2'),
            ),
        );
    }

    // function _load_persons($parent_id, $depth) {
    //
    //     $mcu = new midgard_collector('midgard_member', 'gid', $parent_id);
    //     $mcu->set_key_property('uid');
    //     $mcu->execute();
    //     $uids = $mcu->list_keys();
    //
    //     //print("parent_id: ".$parent_id);
    //     //print_r($uids);
    //
    //     $users = array();
    //
    //     if(!empty($uids)) {
    //         //print("start uid loop");
    //         foreach ($uids as $uidk => $ukval) {
    //             //$muid = $mcp->get_subkey($uidk, 'uid');
    //             $mcp = new midgard_collector('midgard_person', 'id', $uidk);
    //             $mcp->set_key_property('id');
    //             $mcp->add_value_property('guid');
    //             $mcp->add_value_property('firstname');
    //             $mcp->add_value_property('lastname');
    //             $mcp->add_value_property('username');
    //             $mcp->execute();
    //             $user_keys = $mcp->list_keys();
    //
    //             // print(" uidk: ".$uidk);
    //             // print(" count: " . count($user_ids));
    //             // print_r($user_ids);
    //
    //             if(!empty($user_keys)) {
    //                 //print(" user_ids loop start ");
    //                 foreach ($user_keys as $uid => $uval) {
    //                     $guid = $mcp->get_subkey($uid, 'guid');
    //                     $fname = $mcp->get_subkey($uid, 'firstname');
    //                     $lname = $mcp->get_subkey($uid, 'lastname');
    //
    //                     $fullname = (!$fname ? '' : $fname.' ') . $lname;
    //
    //                     if($fullname == "" || $fullname == " ") {
    //                         $fullname = $mcp->get_subkey($uid, 'username');
    //                     }
    //
    //                     //print_r($uitem);
    //
    //                     $uitem = new fi_incognito_protustree_item($guid,$fullname,"user",array());
    //                     array_push($users, $uitem);
    //                 }
    //             }
    //         }
    //     }
    //     return $users;
    // }

    function _get_person_name($obj) {
        // print("_get_person_name: ");
        // print_r($obj->username);
        // print(" - ");

        $fname = $obj->firstname;
        $lname = $obj->lastname;

        $fullname = (!$fname ? '' : $fname.' ') . $lname;

        if($fullname == "" || $fullname == " ") {
            $fullname = $obj->username;
        }

        // print_r($fullname);
        // print("\n");

        return $fullname;
    }

    function _get_group_name($obj) {
        // print("_get_group_name: ");
        // print_r($obj->name);
        // print(" - ");

        $name = $obj->official;
        if (!$name) {
            $name = $obj->name;
        }
        if (!$name) {
            $name = "Group #{$obj->id}";
        }

        // print_r($name);
        // print("\n");

        return $name;
    }

    function _get_item($type,$obj,$get_children=true) {
        // print("_get_item: ");
        // print_r($obj->name);
        // print("\n");

        if(!$obj) {
            return false;
        }

        $guid = $obj->guid;
        $name = $type == "group" ? $this->_get_group_name($obj) : $this->_get_person_name($obj);
        if($get_children) {
            $children = $this->_get_object_children($type,$obj);
        } else {
            $has_children = $this->_does_object_have_children($obj);
            $children = ($has_children ? "load_later" : array());
        }


        if($obj->owner == 0) {
            $options = array( 'parent_id' => 0,
                              'parent_type' => "root" );
        } else {
            $options = array( 'parent_id' => $obj->owner,
                              'parent_type' => "group" );
        }

        $item = new fi_incognito_protustree_item($guid,$name,$type,$children,$options);

        return $item;
    }

    function _get_item_parent($item) {
        $parent = false;
        // print("_get_item_parent: ");
        // print_r($item->name);
        // print("\n");

        if(isset($item->parent_id) && $item->parent_id == 0) {
            $parent = $this->rootItem;
        }
        $parent_type = isset($item->parent_type) ? $item->parent_type : "root";

        switch($parent_type) {
            case 'root':
                $parent = $this->rootItem;
            break;
            case 'group':
                $grp = new midcom_db_group($item->parent_id);
                $parent = $this->_get_item("group", $grp,false);
            break;
            case 'person':
                $grp = new midcom_db_group($item->parent_id);
                $parent = $this->_get_item("group", $grp,false);
            break;
            default:
            break;
        }

        return $parent;
    }

    function _get_object_children($type,$obj=false,$owner=false) {
        $children = array();
        $no_child_groups = false;
        $runCnt = 0;
        $childs = array();

        //print("\n this->active_depth: ".$this->active_depth."\n");

        if($type == "group") {
            if(!$this->first_time_get) {
                $this->active_depth += 1;
            } else {
                $runCnt = 1;
            }
        }
        $this->first_time_get = false;

        if($this->search_depth == false || ($this->active_depth < $this->search_depth)) {

            if($obj) {
                //print("_get_object_children for: ".$obj->id." ".$obj->name);
            } else {
                //print("fetch all root groups");
            }
            //print("\n");
            //print("type: ".$type."\n");

            if($type == "group") {

                $owner_id = !empty($obj) ? $obj->id : (($owner && $owner >= 0) ? $owner : 0);
                //print("owner_id: " . $owner_id . "\n");

                $mc = new midgard_collector('midgard_group', 'owner', $owner_id);
                $mc->set_key_property('id');
                $mc->add_value_property('guid');
                $mc->add_value_property('name');
                $mc->execute();
                $group_ids = $mc->list_keys();

                $group_persons = array();
                if($obj && !$this->start_from_root) {
                    $group_persons = $this->_get_object_children("person",$obj);
                } elseif(!$obj && $this->start_from_root) {
                    $group_persons = $this->_get_object_children("person",false,0);
                }

                if($group_ids && !empty($group_ids)) {
                    foreach($group_ids as $id => $value) {
                        $guid = $mc->get_subkey($id, 'guid');
                        $persons = array();
                        $childs = array();

                        $grp = new midcom_db_group( $guid );

                        $options = array( 'parent_id' => $grp->owner,
                                          'parent_type' => "group" );
                        $name = $this->_get_group_name($grp);

                        if(($this->active_depth+$runCnt) < $this->search_depth) {
                            $persons = $this->_get_object_children("person",$grp);
                        }

                        $chs = $this->_get_object_children($type,$grp);
                        if($chs != "load_later" && !empty($chs)) {
                            foreach($chs as $child) {
                                array_push($childs,$child);
                            }
                        }

                        if($persons != "load_later" && !empty($persons)) {
                            foreach($persons as $person) {
                                array_push($childs,$person);
                            }
                        }

                        if(empty($persons) && ($chs == "load_later")) {
                            $childs = "load_later";
                        }

                        $children[] = new fi_incognito_protustree_item($guid,$name,$type,$childs,$options);
                    }
                }

                if($group_persons != "load_later" && !empty($group_persons)) {
                    foreach($group_persons as $person) {
                        array_push($children,$person);
                    }
                }

                if(empty($group_persons) && $childs == "load_later") {
                    return "load_later";
                }

            }
            if($type == "person") {
                $parent_id = !empty($obj) ? $obj->id : (($owner && $owner >= 0) ? $owner : 0);
                //print("parent_id: " . $parent_id . "\n");

                $mc = new midgard_collector('midgard_member', 'gid', $parent_id);
                $mc->set_key_property('id');
                $mc->add_value_property('uid');
                $mc->execute();
                $member_uids = $mc->list_keys();

                if(!empty($member_uids)) {
                    foreach ($member_uids as $muidk => $muval) {
                        $uid = $mc->get_subkey($muidk, 'uid');

                        $qb = new MidgardQueryBuilder('midgard_person');
                        $qb->add_constraint('id', '=', $uid);
                        $person = $qb->execute();

                        if($person) {
                            $guid = $person[0]->guid;
                            $name = $this->_get_person_name($person[0]);
                            $options = array( 'parent_id' => $parent_id,
                                              'parent_type' => "group" );

                            $children[] = new fi_incognito_protustree_item($guid,$name,$type,array(),$options);
                        }
                    }
                }
            }

        } else {
            $has_children = $this->_does_object_have_children($obj,$owner);
            return ($has_children ? "load_later" : array());
        }

        return $children;
    }

    function _does_object_have_children($obj=false,$owner=false) {
        $owner_id = !empty($obj) ? $obj->id : (($owner && $owner >= 0) ? $owner : 0);

        $mcg = new midgard_collector('midgard_group', 'owner', $owner_id);
        $mcg->set_key_property('id');
        $mcg->add_value_property('guid');
        $mcg->add_value_property('name');
        $mcg->execute();
        $group_ids = $mcg->list_keys();

        if($group_ids && !empty($group_ids)) {
            return true;
        }

        $mcm = new midgard_collector('midgard_member', 'gid', $owner_id);
        $mcm->set_key_property('id');
        $mcm->add_value_property('uid');
        $mcm->execute();
        $member_uids = $mcm->list_keys();

        if($member_uids && !empty($member_uids)) {
            return true;
        }

        return false;
    }

    /**
     * Loads requested object tree
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return boolean Indicating success
     */
    function _handler_tree($handler_id, $args, &$data)
    {
        $_MIDCOM->skip_page_style = true;

        switch ($handler_id)
        {
            case '____mfa-users-tree_json':

                if (array_key_exists('item_id', $_GET)) {
                    $data['item_id'] = $_GET['item_id'];
                } else {
                    $data['item_id'] = 0;
                }

                if(array_key_exists('depth', $_GET)) {
                    $data['depth'] = (int)$_GET['depth'];
                } else {
                    $data['depth'] = false;
                }

                if(array_key_exists('include_parents', $_GET) && $data['item_id'] > 0) {
                    $data['include_parents'] = (int)$_GET['include_parents'] == 1 ? true : false;
                    $data['root_item_id'] = array_key_exists('root_item_id', $_GET) ? $_GET['root_item_id'] : 0;
                } else {
                    $data['include_parents'] = false;
                }

                $this->search_depth = $data['depth'];

                if($data['item_id'] == '0') {
                    $this->start_from_root = true;

                    $data['children'] = $this->_get_object_children("group",false,0);

                    $this->rootItem->addChildren($data['children']);

                    $ready_to_format = $this->rootItem;
                    $pitem = $this->rootItem;
                } else {
                    $data['group'] = new midcom_db_group($data['item_id']);

                    $data['item'] = $this->_get_item("group",$data['group']);

                    $ready_to_format = $data['item'];
                    $pitem = $data['item'];
                }

                if($data['include_parents']) {
                    if($data['root_item_id'] == '0') {
                        $root = $this->rootItem;
                    } else {
                        $rootGrp = $data['group'] = new midcom_db_group($data['root_item_id']);
                        $root = $data['item'] = $this->_get_item("group",$rootGrp,false);
                    }

                    $pitem_id = -1;
                    $res = array();

                    while((string)$pitem_id != (string)$root->id) {
                        $prevItem = $pitem;
                        $pitem = $this->_get_item_parent($pitem);
                        $pitem->addChildren($prevItem);
                        $pitem_id = $pitem->id;
                        $res = $pitem;
                    }

                    $ready_to_format = $res;
                }

                $data['groups_formatted'] = midcom_helper_json_encode($ready_to_format);
            break;
            case '____mfa-users-tree_json2':
                //print_r("data['groups_formatted']: ");
                //print_r($data['groups_formatted']);
                //print("\n");
            break;
            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Unrecognized tree format.');
                // This will exit.
        }

        return true;
    }

    /**
     * Output the object tree in desired format
     *
     * @access private
     */
    function _show_tree($handler_id, &$data)
    {
        echo $data['groups_formatted'];
    }
}

?>