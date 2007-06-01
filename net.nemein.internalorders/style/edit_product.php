<!-- edit_order -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<style>

label
{
	clear:both;
	display:block;
}



label select, label input
{
	display:block;
	clear:both;
	float:left;
}

fieldset
{
	clear:both;
}

.radios label
{
    margin:0;
    padding:0;
    display:inline;
}

</style>


<h1><?php echo sprintf($data['l10n']->get('edit %s'), $data['product']->title); ?></h1>

<form method="post" action="" name="net_nemein_internalorders_form">
	<fieldset>
		<legend><?php echo $data['l10n']->get('internal order'); ?></legend>
		<label>
			<?php echo $data['l10n']->get('title'); ?>
			<input type="text" name="net_nemein_internalorders_title" value="<?php echo $data['product']->title; ?>" />
		</label>
		<label>
			<?php echo $data['l10n']->get('code'); ?>
			<input type="text" name="net_nemein_internalorders_name" value="<?php echo $data['product']->name; ?>" />
		</label>
		<label>
			<?php echo $data['l10n']->get('price'); ?>
			<input type="text" name="net_nemein_internalorders_price" value="<?php echo $data['product']->extra1; ?>" />
		</label>

                <label>
                        <?php echo $data['l10n']->get('prod_group'); ?>
                        <input type="text" name="net_nemein_internalorders_prod_group" value="<?php echo $data['product']->extra2; ?>" />
                </label>


                 <label>
                        <?php echo $data['l10n']->get('prod_sub_group'); ?>
                        <input type="text" name="net_nemein_internalorders_prod_sub_group" value="<?php echo $data['product']->extra3; ?>" />
                </label>


		<style>
		#testdata
		{
			clear:both;
		    margin:20px;
		    border:2px solid #000000;
		    padding:10px;
		}
		
		</style>
<!-- 		<div id="testdata">
		<pre>
		<?php


		?>
		</pre>
		</div>
-->		
		



<br /><br />
		<br /><br />
		<input type="hidden" name="net_nemein_internalorders_pricelist_update" value="1" />
		<input type="submit" value="<?php echo $data['l10n']->get('submit'); ?>" />
	</fieldset>
</form>
<!-- / edit_order -->
