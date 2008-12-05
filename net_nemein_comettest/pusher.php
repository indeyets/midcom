<?php
/**
 * @package net_nemein_comettest
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comet pusher class
 * based on PI library (http://pi-js.googlecode.com)
 *
 * @package net_nemein_comettest
 */
class net_nemein_comettest_pusher
{
    public function __construct() {}
    
    static function pushdata($data, $type, $name)
    {
		switch ($type)
		{
			case 1:
				echo "<end />".$data;
				echo str_pad('', 4096)."\n";
			break;					
			case 2:
				header("Content-type: application/x-dom-event-stream");

				print "Event: $name\n";
				print "data: $data\n\n";				
			break;				
			case 3:
				print "<script>parent._cometObject.event.push(\"{$data}\")</script>";
			break;
		}
    }
}
?>