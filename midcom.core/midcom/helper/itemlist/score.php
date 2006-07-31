<?php

class  midcom_helper_itemlist_score extends midcom_helper_itemlist
{
  
    /* get_sorted_list  - get a list objects ready for showing. 
     * */
  
    function get_sorted_list ()
    {
        $nodes_list = $this->_basicnav->list_nodes($this->parent_node_id);
        if ($nodes_list === false)
        {
	      return false;
        }
        $leaves_list = $this->_basicnav->list_leaves($this->parent_node_id);
        if ($leaves_list === false)
        {
	      return false;
        }

        // If there are no child elements, return empty array
        if (count($nodes_list) == 0 && count($leaves_list) == 0)
        {
            return array();
        }

        $result = array();
        $counter = 0;
        if ($nodes_list)
        {
            foreach ($nodes_list as $node_id) {
                $result[$counter] = $this->_basicnav->get_node($node_id);
                $counter++;
            }
        }
        if ($leaves_list)
        {
            foreach ($leaves_list as $leaf_id) {
                $result[$counter] = $this->_basicnav->get_leaf($leaf_id); 
                $counter++;
            }
        }
        if (! usort($result, array ("midcom_helper_itemlist_score", "sort_cmp") ) )
        {
            $GLOBALS['midcom']->generate_error("ARG"); 
        }
        return $result;
    }

    function sort_cmp ($a, $b)
    {
        // This should also sort out the situation were score is not
        // set. 
        if ($a[MIDCOM_NAV_SCORE] === $b[MIDCOM_NAV_SCORE]) {
          return strcmp($a[MIDCOM_NAV_NAME],$b[MIDCOM_NAV_NAME]);
        }
        return (integer) (($a[MIDCOM_NAV_SCORE] > $b[MIDCOM_NAV_SCORE]) ? 1 : -1);
    }

}
?>
