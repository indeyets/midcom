<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAL toolbar helper
 *
 * @package midcom_core
 */
class midcom_core_helpers_toolbar_tal extends midcom_core_helpers_toolbar
{
    protected $template = '';
    
    protected function initialize()
    {
        $html = "<a href=\"#\">\n";
        $html .= "    <img src=\"".MIDCOM_STATIC_URL."/midcom_core/services/toolbars/midgard-logo.png\" width=\"16\" height=\"16\"/ alt=\"Midgard\">\n";
        $html .= "</a>\n";
        
        $this->template = <<<EOS
        <div class="{$this->css_class}" {$this->holder_attributes}>
            <span tal:condition="toolbar/has_logos" tal:omit-tag="">
                <div class="{$this->css_class}_logos">
                    <span tal:repeat="logo toolbar/logos" metal:use-macro="midcom_helper_toolbar_logo" />
                </div>
            </span>
            <div class="{$this->css_class}_items">
                <span tal:repeat="section toolbar/sections" metal:use-macro="midcom_helper_toolbar_section" />
            </div>
            <div class="{$this->css_class}_dragbar"></div>
        </div>

        <div tal:comment="toolbar logo macro"
             metal:define-macro="midcom_helper_toolbar_logo" >
        
             <a href="#url" title="title"
                tal:attributes="href logo/url; title logo/title">
                 <img src="#src" alt="title"
                      tal:attributes="src logo/path; alt logo/title" />

             </a>

        </div>

        <div tal:comment="toolbar section macro"
             metal:define-macro="midcom_helper_toolbar_section" >
        
            <span class="\${section/css_class}_title"
                  tal:content="section/title">Section title here</span>
            <ul class="\${section/css_class}_items">
                <span tal:repeat="item section/items"
                      tal:define="section section"
                      metal:use-macro="midcom_helper_toolbar_section_item" />

            </ul>

        </div>

        <div tal:comment="toolbar section item macro"
             tal:omit-tag=""
             metal:define-macro="midcom_helper_toolbar_section_item" >
             
            <li class="\${item/css_class}"
                tal:condition="item/enabled">

            <span tal:condition="not: item/is_post" tal:omit-tag="">
                <a href="\${item/url}" title="\${item/label}" class="\${section/css_class}_item_link" accesskey="\${item/accesskey}" >
                    <span tal:condition="item/icon" tal:omit-tag="">

                    <img src="\${item/iconurl}" alt="\${item/label}" />
                    </span>

                    &nbsp;<span tal:content="item/htmllabel" class="\${section/css_class}_item_label"></span>
                </a>
            </span>
            <span tal:condition="item/is_post" tal:omit-tag="">
                <form>
                Form item
                </form>
            </span>

            </li>
            
        </div>
EOS;
    }
    
    public function render(&$toolbar)
    {
        if (!class_exists('PHPTAL'))
        {
            require('PHPTAL.php');
        }
        
        $tal = new PHPTAL();        

        // $tal->MIDCOM = $_MIDCOM;
        $tal->toolbar = $toolbar;
        
        $tal->setSource($this->template);

        $html = $tal->execute();
        
        return $html;
    }
}

?>