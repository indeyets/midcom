<?php
$holder_attrs = $data['holder_attributes'];
$css_class = $data['css_class'];
?>
<div class="<?php echo $css_class; ?>" <?php echo $holder_attrs; ?>>
    <span tal:condition="toolbar/has_logos" tal:omit-tag="">

    <div class="<?php echo $css_class; ?>_logos">
        <span tal:repeat="logo toolbar/logos" metal:use-macro="midcom_helper_toolbar_logo" />
    </div>
    </span>
    
    <div class="<?php echo $css_class; ?>_sections">
        <span tal:repeat="section toolbar/sections" metal:use-macro="midcom_helper_toolbar_section" />        
    </div>
    <div class="<?php echo $css_class; ?>_dragbar"></div>
</div>

<div tal:comment="toolbar logo macro"
     tal:omit-tag=""
     metal:define-macro="midcom_helper_toolbar_logo" >

        <a href="#url" title="title"
           tal:attributes="href logo/url; title logo/title">
            <img src="#src" alt="title"
                 tal:attributes="src logo/path; alt logo/title" />

        </a>

</div>

<div tal:comment="toolbar section macro"
     tal:omit-tag=""
     metal:define-macro="midcom_helper_toolbar_section" >
     
         <div class="${section/css_class} <?php echo $css_class; ?>_section">
            <span class="${section/css_class}_title <?php echo $css_class; ?>_section_title"
                  tal:content="section/title">Section title here</span>

            <ul class="${section/css_class}_items <?php echo $css_class; ?>_section_items">
                <span tal:repeat="item section/items"
                      tal:define="section section"
                      metal:use-macro="midcom_helper_toolbar_section_item" />

            </ul>
        </div>
</div>

<div tal:comment="toolbar section item macro"
     tal:omit-tag=""
     metal:define-macro="midcom_helper_toolbar_section_item" >
     
                <li class="${item/css_class}"
                    tal:condition="item/enabled">

                <span tal:condition="not: item/is_post" tal:omit-tag="">
                    <a href="${item/url}" title="${item/label}" class="${section/css_class}_item_link" accesskey="${item/accesskey}" >
                        <span tal:condition="item/icon" tal:omit-tag="">

                        <img src="${item/iconurl}" alt="${item/label}" />
                        </span>

                        &nbsp;<span tal:content="item/htmllabel" class="${section/css_class}_item_label"></span>
                    </a>
                </span>
                <span tal:condition="item/is_post" tal:omit-tag="">
                    <form>
                    Form item
                    </form>
                </span>

                </li>
    
</div>