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
 * <code>
 * $_MIDCOM->load_library("pl.olga.tellafriend");
 * $taf = new pl_olga_tellafriend();
 * </code>
 *
 * Then prepare a form. You should embed the form into a page you want to
 * notify about. I use layers:
 *
 * <code>
 * <div style="visibility:hidden" id="taf">
 * <?php echo $taf->controller->display_form(); ?>
 * </div>
 * <a href="javascript:;" onclick="your_favourite_show_hide_layer_js_function()">Notify a friend</a>;
 * </code>
 *
 * In case you would like to have the form shown when submitted form was
 *  incomplete, you can detect the state and set visibility of a layer:
 *
 * <code>
 * <?php
 * $taf_visible = ($taf->result=='edit' && $GLOBALS["_POST"]["_qf__pl_olga_tellafriend"]) ? "visible" : "hidden";
 * ?>
 * <div style="visibility:&(taf_visible);" id="taf">
 * <?php echo $taf->controller->display_form(); ?>
 * </div>
 * <a href="javascript:;" onclick="your_favourite_show_hide_layer_js_function()">Notify a friend>/a>
 * </code>
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
            // Load form values. Hmm.. Shall I call DM or FM?
            $dm = $this->controller->datamanager->types;

            // Most dirty hack ever. If we got 'save' we got form POST'ed. So the referer is
            // the page (URL) we want to notify about :)
            $link_url = $_SERVER['HTTP_REFERER'];

            $conf_url = $this->_config->get('link_url');
            if ($conf_url)
            {
                $link_url = $conf_url;
            }
            if (empty($link_url))
            {
                $link_url = $_MIDCOM->get_host_prefix() . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            }

            $from = "\"{$dm['sender_name']->value}\" <{$dm['sender']->value}>";
            if (empty($from))
            {
                $from = $this->_config->get('mail_sender_email');
            }

            $subject = str_replace("__SENDER_NAME__", $dm['sender']->value, $this->_config->get('mail_subject'));
            $subject = str_replace("__LINK_URL__", $link_url, $subject);

            $content = str_replace("__SENDER_NAME__", $dm['sender_name']->value, $this->_config->get('sysmsg'));
            $content = str_replace("__SENDER_EMAIL__", $dm['sender']->value, $content);
            $content = str_replace("__SUBJECT__", $subject, $content);
            $content = str_replace("__LINK_URL__", $link_url, $content);
            $content = str_replace("__DATETIME__", strftime("%x %X", time()), $content);
            $content = str_replace("__MESSAGE__", $dm['comment']->value, $content);

            $mail = new org_openpsa_mail();
            $mail->to = $dm['recipient']->value;
            $mail->from = $from;
            $mail->subject = $subject;
            $mail->body = $content;
            $mail->send();
        }
    }

}
?>