<?php
?>
<div class="calendar-modal-window-content">
    <h1>Add buddy</h1>
    <div onclick="close_modal_window();">Close</div>

    <div class="search-block">
        <form name="buddylist-search-form" id="buddylist-search-form">
            <input type="hidden" name="result_headers[name]" id="result_headers_name" value="Name" />
            <input type="hidden" name="search_fields[]" id="search_fields_firstname" value="firstname" />
            <input type="hidden" name="search_fields[]" id="search_fields_lastname" value="lastname" />
            <input type="hidden" name="search_fields[]" id="search_fields_username" value="username" />
            <input type="hidden" name="search_fields[]" id="search_fields_email" value="email" />
            <input type="hidden" name="result_ordering[lastname]" id="result_ordering_lastname" value="ASC" />
            <input type="hidden" name="result_ordering[firstname]" id="result_ordering_firstname" value="ASC" />
            <label for="sq_string">Search:</label>
            <input type="text" name="sq[string]" id="sq_string" value="" />
            <input type="submit" name="search" value="Search" />
            <img id="search-indicator" src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/indicator.gif" alt="Searching..." style="display: none;"/>
        </form>
        <div class="search-result-count" id="buddylist-search-result-count" style="display: none;"><span class="count">0</span> person(s) found</div>
    </div>
    <div class="search-result-block">
        <div class="search-result-content" id="buddylist-search-results"></div>
    </div>
</div>
<script type="text/javascript">
enable_buddylist_search();
</script>