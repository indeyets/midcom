<?php
/**
 * @package midcom_core
 */
?>
<form method="post">
    <label>
        <span>Title</span>
        <input type="text" name="title" value="" tal:attributes="value net_nemein_news/article_dm/types/title/as_raw" />
    </label>
    
    <label>
        <span>Content</span>
        <textarea name="content" rows="20" cols="40" wrap="off"><span tal:replace="net_nemein_news/article_dm/types/content/as_raw" /></textarea>
    </label>
    
    <div class="form_toolbar">
        <input type="submit" name="save" value="Save" accesskey="s" />
    </div>
</form>