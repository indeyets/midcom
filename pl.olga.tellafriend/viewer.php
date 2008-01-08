<?php
/**
 * @package pl.olga.tellafriend
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 918 2005-04-19 06:56:24Z torben $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 * Sample usage. You should use it from within the component style like
 *  "show-article". First, load and instantiate the library:
 *
 * <pre>
 * <?php
 * $_MIDCOM->load_library("pl.olga.tellafriend");
 * $taf = new pl_olga_tellafriend();
 * ?>
 * </pre>
 *
 * Then prepare a form. You should embed the form into a page you want to
 * notify about. I use layers:
 *
 * <pre>
 * &lt;div style="visibility:hidden" id="taf"&gt;
 * <?php echo $taf->controller->display_form(); ?>
 * &lt;/div&gt;
 * &lt;a href="javascript:;" onclick="your_favourite_show_hide_layer_js_function()&gt;Notify a friend&lt;/a&gt;
 * </pre>
 *
 * In case you would like to have the form shown when submitted form was
 *  incomplete, you can detect the state and set visibility of a layer:
 *
 * <pre>
 * <?php
 * $taf_visible = ($taf->result=='edit' && $GLOBALS["_POST"]["_qf__pl_olga_tellafriend"])?"visible":"hidden";
 * ?>
 * &lt;div style="visibility:&(taf_visible);" id="taf"&gt;
 * <?php echo $taf->controller->display_form(); ?>
 * &lt;/div&gt;
 * &lt;a href="javascript:;" onclick="your_favourite_show_hide_layer_js_function()&gt;Notify a friend&lt;/a&gt;
 * </pre>
 *
 * @package pl.olga.tellafriend
 */

class pl_olga_tellafriend  extends midcom_baseclasses_components_purecode
{

    var $controller;
    var $_schemadb;
    var $result;

    function pl_olga_tellafriend ()
    {
        $this->_component = "pl.olga.tellafriend";
        parent::midcom_baseclasses_components_purecode();

        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->controller =& midcom_helper_datamanager2_controller::create('nullstorage');
        $this->controller->schemadb =& $this->_schemadb;
    	$this->controller->initialize();
    	$this->controller->formmanager->initialize("pl_olga_tellafriend");

        $this->result = $this->controller->process_form();

        if ($this->result == 'save')
        {
            // Most dirty hack ever. If we got 'save' we got form POST'ed. So the referer is
            // the page (URL) we want to notify about :)
            $url = $_SERVER['HTTP_REFERER'];

            // Load form values. Hmm.. Shall I call DM or FM?
            $dm = $this->controller->datamanager->types;

            $mail = new org_openpsa_mail();
            $mail->subject = $this->_config->get('mail_subject');
            $mail->body = sprintf($this->_config->get('sysmsg'), $url);
            $mail->body .= $dm['comment']->value;
            $mail->from = "\"{$dm['sender_name']->value}\" <{$dm['sender']->value}>";
            $mail->to = $dm['recipient']->value;

            $mail->send();
        }

    }

}
?>
