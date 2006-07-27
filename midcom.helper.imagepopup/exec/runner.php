<?php
/**
 * Created on Mar 12, 2006
 * @author tarjei huse
 * @package midcom.helper.imagepopup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */

/**
 * This class tricks the midcom style system into working. 
 * Also it makes it easy te execute the handler in exec mode. 
 * 
 */
class midcom_helper_imagepopup_runner 
{
    var $handler = null;

    function midcom_helper_imagepopup_runner ()
    {
        //$handler =& $_MIDCOM->componentloader->get_component_class('midcom.helper.imagepopup');
        require MIDCOM_ROOT . '/midcom/helper/imagepopup/handler/list.php';
        require MIDCOM_ROOT . '/midcom/helper/imagepopup/nulltopic.php';
        $nulltopic = new midcom_helper_imagepopup_nulltopic();
        $nulltopic->parameter('midcom' , 'component', 'midcom.helper.imagepopup');
        $this->handler = new midcom_helper_imagepopup_handler_list();
        
        $this->handler->_i18n =& $_MIDCOM->get_service('i18n');
        $this->handler->_l10n =& $this->handler->_i18n->get_l10n('midcom.helper.imagepopup');
        $this->handler->_l10n_midcom =& $this->handler->_i18n->get_l10n('midcom');
        $this->handler->_request_data['l10n'] =& $this->handler->_l10n; 
        $this->handler->exec_initialize();
        $_MIDCOM->_context[$_MIDCOM->_currentcontext][MIDCOM_CONTEXT_CONTENTTOPIC] =$nulltopic;
        //didn't work: '$_MIDCOM->set_custom_context_data(MIDCOM_CONTEXT_CONTENTTOPIC, $nulltopic);
        //$_MIDCOM->set_custom_context_data(MIDCOM_CONTEXT_REQUESTTYPE, MIDCOM_REQUEST_CONTENT);
        $_MIDCOM->set_custom_context_data('request_data', &$this->handler->_request_data);
        
    }
    
    function run() 
    {
        if ($GLOBALS['argv'][0] == 'folder')
        {
        	$type = 'list_topic';
        	array_shift($GLOBALS['argv']);
        }
        else
        {
        	$type = 'list_object';
        }
        if (!$this->handler->_handler_list($type, $GLOBALS['argv']))
        {
            return;
        }
        $_MIDCOM->style->enter_context($_MIDCOM->_currentcontext);
        midcom_show_style('style-init');        
        $this->handler->_show_list();
        midcom_show_style('style-finish');
        $_MIDCOM->style->leave_context();
    }
}

$runner = new midcom_helper_imagepopup_runner();
$runner->run();

?>