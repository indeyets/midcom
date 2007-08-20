<?php
$_MIDCOM->auth->require_admin_user();

if (   !isset($_POST['address'])
    || !strstr($_POST['address'], '__PRODUCT_CODE__')
{
    ?>
    <h1>Import product images</h1>
    
    <p>You can import product images that have the product code in their filename/URL here. 
    Type the address format below with string <code>__PRODUCT_CODE__</code> showing where the code should go to.</p>
    
    <form method="post">
        <label>
            URL/path
            <input type="text" name="address" value="/tmp/__PRODUCT_CODE__.jpg" />
        </label>
        <input type="submit" value="Import images" />
    </form>
    <?php
}
else
{
    @ini_set('memory_limit', -1);
    @ini_set('max_execution_time', 0);
    
    // Import product images
    $_MIDCOM->componentloader->load_graceful('org.openpsa.products');
    $qb = org_openpsa_products_product_dba::new_query_builder();
    $qb->add_constraint('code', '<>', '');
    $products = $qb->execute();
    
    $schemadb = $GLOBALS['midcom_component_data']['org.openpsa.products']['config']->get('schemadb_product');
    $schema = midcom_helper_datamanager2_schema::load_database($schema);
    $datamanager = new midcom_helper_datamanager2_datamanager($schema);
    foreach ($products as $product)
    {
        // Get old image
        $image = file_get_contents(str_replace('__PRODUCT_CODE__', $product->code, $_POST['address']));
        if (empty($image))
        {
            continue;
        }
        
        // Save image to a temp file
        $tmp_name = tempnam('/tmp', 'org_openpsa_products_product_oldimage_');
        $fp = fopen($tmp_name, 'w');
    
        if (!fwrite($fp, $image))
        {
            //Could not write, clean up and continue
            echo("Error when writing file {$tmp_name}");
            fclose($fp);
            continue;
        }
        fclose($fp);
     
        $datamanager->autoset_storage($product);   
        $datamanager->types['image']->set_image("{$product->code}.jpg", $tmp_name, $product->title);
        $datamanager->save();
    }
}
?>