<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$product = $data['product'];
$view_product = $data['view_product'];
?>
<li><a href="<?php echo $data['view_product_url']; ?>">&(view_product['code']:h);: &(view_product['title']:h);</a></li>