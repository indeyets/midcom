<?php
/**
 * Created on Feb 12, 2006
 * @author tarjei huse
 * @package midcom.admin.acls
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$priviledges =$request_data['l10n']->get('privileges for'); 

$object = $request_data['object'];
?><h1>&(priviledges); &(object);</h1>
<?
//var_dump($request_data['div']);

$request_data['datamanager']->display_form();
//var_dump($request_data['schema']['acls']->fields);
?>