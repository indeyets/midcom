<?php

/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:date.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class provides an abstract base class for all indexer query filters.
 * 
 * The date filter restricts the query to documents where the filtered field
 * falls within the given timespan (which can be open at any one end).
 * 
 * @abstract Abstract indexer filter class
 * @package midcom.services
 * @see midcom_services_indexer
 */

class midcom_services_indexer_filter_date extends midcom_services_indexer_filter
{
    
    /**
     * Start timestamp, may be 0
     * 
     * @var int
     * @access private
     */
    var $_start;
    
    /**
     * End timestamp, may be 0
     * 
     * @var int
     * @access private
     */
    var $_end;
    
    /**
     * Create a new date filter.
     * 
     * Only one of the filter bounds may be 0, indicating a no limit in that
     * direction.
     * 
     * @param string $field The name of the field that should be filtered.
     * @param string $start Start of filter range (or 0 for no start filter)
     * @param string $end End of filter range (or 0 for no end filter)
     */
    function __construct($field, $start, $end)
    {
        parent::__construct($field);
        
        if ($start == 0 && $end == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Both start and end of a datefilter must not be 0.');
            // This will exit.
        }
        
        $this->_start = $start;
        $this->_end = $end;
        $this->type = 'datefilter';
    }
    
    /**
     * Returns the start of the filter range, may be 0.
     * 
     * @return int Timestamp or 0 for no filter.
     */
    function get_start()
    {
        return $this->_start;
    }
    
    /**
     * Returns the end of the filter range, may be 0.
     * 
     * @return int Timestamp or 0 for no filter.
     */
    function get_end()
    {
        return $this->_end;
    }
    
}

?>