<form method="post">
    <label>
        <span>Title</span>
        <input type="text" name="title" value="" tal:attributes="value page/title" />
    </label>
    
    <label>
        <span>Content</span>
        <textarea name="content" rows="20" cols="40" wrap="off"><span tal:replace="page/content" /></textarea>
    </label>
    
    <div class="form_toolbar">
        <input type="submit" name="save" value="Save" accesskey="s" />
    </div>
</form>