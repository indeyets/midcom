<?php
/**
 * @package midcom_core
 */
?>
<form method="post">
    <label>
        <span>Title</span>
        <input type="text" name="title" value="" tal:attributes="value net_nemein_news/article/title" />
    </label>
    
    <label>
        <span>Content</span>
        <textarea name="content" rows="20" cols="40" wrap="off"><span tal:replace="net_nemein_news/article/content" /></textarea>
    </label>
    
    <div class="form_toolbar">
        <input type="submit" name="save" value="Save" accesskey="s" />
    </div>
</form>