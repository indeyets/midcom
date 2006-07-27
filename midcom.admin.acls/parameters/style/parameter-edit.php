<?php
/**
 * Created on Feb 12, 2006
 * @author tarjei huse
 * @package midcom.admin.parameters
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$request_data['datamanager']->_controller->display_form();