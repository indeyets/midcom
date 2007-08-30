<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to handle the simple "communityhtml" type, which is a very
 * simplified form of bbcode.
 *
 * This widget can only be used with the communityhtml type, for which it is
 * enforced as widget.
 *
 * <b>Configuration parameters:</b>
 *
 * Configuration is done by the datatype.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "communityhtml" => Array (
 *     "description" => "Content",
 *     "datatype" => "communityhtml"
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The toolbar is a br-separated span.communityhtml_toolbar within the
 * label of the field. Its contents have no further style.
 *
 * The input field is of textarea.communityhtml.
 *
 * @package midcom.helper.datamanager
 */

class midcom_helper_datamanager_widget_communityhtml extends midcom_helper_datamanager_widget {

    /**
     * List of smileys, as passed from the communityhtml type.
     *
     * @var Array
     * @access private
     */
    var $_smileys;

    /**
     * List of colors, as passed from the communityhtml type.
     *
     * @var Array
     * @access private
     */
    var $_colors;

    /**
     * List of font sizes, as passed from the communityhtml type.
     *
     * @var Array
     * @access private
     */
    var $_sizes;

    function _constructor (&$datamanager, $field, $defaultvalue) {
        parent::_constructor ($datamanager, $field, $defaultvalue);

        $this->_smileys = $this->_field["datatype_communityhtml_smileys"];
        $this->_colors= $this->_field["datatype_communityhtml_colors"];
        $this->_sizes= $this->_field["datatype_communityhtml_sizes"];
    }

    function draw_view () {
        ?><div class="form_longtext"><?echo htmlspecialchars($this->_value);?></div><?php
    }

    /**
     * Set the css class to always include the communityhtml class.
     *
     * @return string The CSS classes indicating the required state.
     */
    function get_css_classes_required()
    {
        $css = 'communityhtml';
        if ($this->required)
        {
            $css .= ' required';
            if ($this->missingrequired)
            {
                $css .= ' missing';
            }
        }
        return $css;
    }

    /**
     * Add the required JScript stuff before the actual widget so
     * that it doesn't interfere with the label stuff.
     */
    function draw_widget_start()
    {
        // Define the JScript helper
        ?>
<script type="text/javascript">
function addTag(tag) {
    element = document.getElementById("<?php echo $this->_fieldname; ?>");
    element.value += tag;
    element.focus();
}
</script>
<?php
        parent::draw_widget_start();
    }


    /**
     * This renders the complete toolbar based on the main type configuration.
     */
    function draw_widget () {
        $midgard = $_MIDCOM->get_midgard();

        // Generate toolbar
        echo "<br />\n<span class='communityhtml_toolbar'>\n";
        foreach ($this->_smileys as $tagname => $url)
        {
            $tagname = htmlspecialchars($tagname);
            $url = htmlspecialchars($url);
            echo "  <a href=\"javascript:addTag(' [{$tagname}] ');\"><img src='$url' alt='tagname' /></a>\n";
        }
        ?>
  <a href="javascript:addTag(' [b][/b] ')" style="margin-left: 0.5em; margin-right: 0.25em; font-weight: bold; text-decoration: none; color: black; font-size: 18px;">B</a>
  <a href="javascript:addTag(' [i][/i] ')" style="margin-right: 0.5em; font-style: italic; color: black; font-size: 18px;">I</a>
  <a href="javascript:addTag(' [u][/u] ')" style="margin-right: 0.5em; text-decoration: underline; color: black; font-size: 18px;">U</a>
  <select onchange="addTag(this.options[this.options.selectedIndex].value);">
    <option value="" selected="selected">Color</option>
<?php
        foreach ($this->_colors as $tagname => $colorname) {
            $tagname = htmlspecialchars($tagname);
            echo "    <option value=' [{$tagname}][/{$tagname}] ' style='color:{$tagname}'>{$tagname}</option>\n";
        }

        ?>
  </select>
  <select onchange="addTag(this.options[this.options.selectedIndex].value)">
    <option value="" selected="selected">Font Size</option>
<?php
        foreach ($this->_sizes as $tagname => $size)
        {
            $tagname = htmlspecialchars($tagname);
            echo "    <option value=' [{$tagname}][/{$tagname}] '>{$tagname}</option>\n";
        }
        ?>
  </select>
</span>
<textarea id="<?php echo $this->_fieldname;?>" class="communityhtml" name="<?php
    echo $this->_fieldname;?>"><?php echo htmlspecialchars($this->_value);?></textarea>
<?php
    }
}


?>