<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a simple widget for the account datatype, it queries username, password
 * and password verification.
 *
 * This widget should only be used with the account datatype.
 *
 * For compatibility with the Midgard account management, usernames are restricted
 * to 15 characters, passwords to 10 characters length.
 *
 * <b>Configuration parameters:</b>
 *
 * None.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "account" => array (
 *     "description" => "Account",
 *     "datatype" => "account"
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * All three input fields use the class input.shorttext. The fieldset
 * is set to fieldset.account.
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_account extends midcom_helper_datamanager_widget {

    function _constructor (&$datamanager, $field, $defaultvalue) {
        parent::_constructor ($datamanager, $field, $defaultvalue);
    }

    function draw_view () {
        $view =& $this->_value;
        ?>
<div class="form_account">
  <ul>
    <li><strong><?php echo $this->_l10n_midcom->get("username"); ?>:</strong> <?echo htmlspecialchars($view["username"]);?></li>
    <li>
      <strong><?php echo $this->_l10n_midcom->get("password"); ?>:</strong> <?echo htmlspecialchars($view["password"]);?>
<?php if ($this->_value["enable_crypt"]) { ?>
      (<?php echo $this->_l10n_midcom->get("encrypted"); ?>)
<?php } ?>
    </li>
  </ul>
</div>
<?php
    }

    /**
     * The account widget will use a field group to put all account-related
     * fields together.
     */
    function draw_widget_start()
    {
        $css = $this->get_css_classes_required();
        if ($css != '')
        {
            $css = " class='{$css}'";
        }
        echo "<fieldset class='account' id='{$this->_fieldname}_fieldset'>\n";
        echo "  <legend{$css}>\n";
        echo '    ' . htmlspecialchars($this->_field['description']);
        $this->draw_helptext();
        echo "\n";
        echo "  </legend>\n";
    }

    /**
     * The account widget will use a field group to put all account-related
     * fields together.
     */
    function draw_widget_end()
    {
        echo "</fieldset>\n";
    }

    function draw_widget () {
        $view =& $this->_value;

        $field = "{$this->_fieldname}_username";
        $label = $this->_l10n_midcom->get('username');
        $value = htmlspecialchars($view["username"], ENT_QUOTES);
        echo "  <label for='{$field}' id='{$field}_label'>\n";
        echo "    {$label}\n";
        echo "    <input class='shorttext' name='{$field}' id='{$field}' maxlength='15' value='${value}' />\n";
        echo "  </label>\n";

        $field = "{$this->_fieldname}_password";
        $label = $this->_l10n_midcom->get('password');
        echo "  <label for='{$field}' id='{$field}_label'>\n";
        echo "    {$label}\n";
        echo "    <input class='shorttext' name='{$field}' id='{$field}' maxlength='10' value='' />\n";
        echo "  </label>\n";

        $field = "{$this->_fieldname}_verify_password";
        $label = $this->_l10n_midcom->get('verify password');
        echo "  <label for='{$field}' id='{$field}_label'>\n";
        echo "    {$label}\n";
        echo "    <input class='shorttext' name='{$field}' id='{$field}' maxlength='10' value='' />\n";
        echo "  </label>\n";
    }

    function _read_formdata () {
        if (array_key_exists($this->_fieldname . "_username", $_REQUEST))
            $this->_value["username"] = $_REQUEST[$this->_fieldname . "_username"];
        if (array_key_exists($this->_fieldname . "_password", $_REQUEST))
            $this->_value["password"] = $_REQUEST[$this->_fieldname . "_password"];
        if (array_key_exists($this->_fieldname . "_verify_password", $_REQUEST))
            $this->_value["verify_password"] = $_REQUEST[$this->_fieldname . "_verify_password"];
    }

}


?>