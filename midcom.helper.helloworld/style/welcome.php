<?php
/* Simple style element, displaying some text.
 *
 * Retrieve a reference to the request data which contains the localization
 * tables.
 */
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('hello world'); ?>!</h1>

<p>
Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod 
tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, 
quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo
consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie 
consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto 
odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait 
nulla facilisi.
</p>

<p>
Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod 
tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, 
quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo 
consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie 
consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto 
odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait 
nulla facilisi.
</p>
