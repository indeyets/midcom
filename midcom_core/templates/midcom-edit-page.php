<?php
/**
 * @package midcom_core
 */
?>
<form method="post">
    <label>
        <span>URL name</span>
        <input type="text" name="name" value="" tal:attributes="value midcom_core/page/name" />
    </label>

    <label>
        <span>Title</span>
        <input type="text" name="title" value="" tal:attributes="value midcom_core/page/title" />
    </label>
    
    <label>
        <span>Content</span>
        <textarea name="content" rows="20" cols="40" wrap="off"><span tal:replace="midcom_core/page/content" /></textarea>
    </label>
    
    <div class="form_toolbar">
        <input type="submit" name="save" value="Save" accesskey="s" />
    </div>
</form>