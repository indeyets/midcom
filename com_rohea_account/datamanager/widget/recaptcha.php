<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager recaptcha
 *
 * @package midcom_helper_datamanager
 */
class com_rohea_account_datamanager_widget_recaptcha extends midcom_helper_datamanager_widget
{
    /**
     * Maximum length of the string encapsulated by this type. 0 means no limit.
     * -1 tries to bind to the types maxlength member, if available.
     *
     * @var int
     */
    public $maxlength = -1;

    /**
     * The size of the input box.
     *
     * @var int
     */
    public $size = 40;
    
    private $recaptcha_publickey;
    private $recaptcha_privatekey;

    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return boolean Indicating Success
     */
    protected function on_initialize()
    {
        //There MUST be a version of recaptchalib in widget directory
        require_once('recaptchalib.php');
        $configuration = new midcom_core_services_configuration_yaml('com_rohea_account'); 
        $this->recaptcha_publickey = $configuration->recaptcha_publickey;
        $this->recaptcha_privatekey = $configuration->recaptcha_privatekey;
        if (   ! array_key_exists('value', $this->type)
            || is_array($this->type->value)
            || is_object($this->type->value))
        {
            /*
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} does not have a value member or it is an array or object, you cannot use the text widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            */
            return false;
        }

        return true;
    }

    public function sync_widget2type($result)
    {
        $this->type->value = $result;
    }

    public function on_validate()
    {   
        //Checking if the recaptcha was correct
        $resp = recaptcha_check_answer($this->recaptcha_privatekey,
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);    
        if (!$resp->is_valid) return false;
        else return true;
    }

    /**
     * Renders the form controls (if not frozen) or read-only view (if frozen)
     * of the widget as html
     */
    public function render_html()
    {
        //rendering captcha javascript
        return recaptcha_get_html($this->recaptcha_publickey);    
    }
}

?>