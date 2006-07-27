<?php
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$objects = $request_data['renderer']->toObject();
?>
<div class="midcom_helper_imagepopup">
    <form  <?php echo $objects->attributes ; ?> >
    
    <div class="midcom_helper_imagepopup_fileupload" > 
        <h1><?php echo $objects->sections[0]->header ?></h1>
        <p>Upload new file: &nbsp;
        <?php 
        // the next section is the upload new file and the submitt button
        foreach  ($objects->sections[1]->elements as $file ) 
        {
        
            echo $file->html ;
            if ($file->elements !== null) {
                foreach  ($file->elements as $element) 
                {
                    echo $element->html . "\n" ;
                }
            }
        }
        ?>
        </p>
        <?php if ( $request_data['error'] != '' ) { ?>
            <div style="color:red;padding:1em;background: white;">
                <?php  echo $request_data['error'];  ?>
            </div>
        <?php } ?>
    </div>
    
    <div id="files">
        <table border="0" cellpadding="0" cellspacing="0" >
        
        <tr><?php 
        $i = 0;
        if ($objects->sections[0] !== null) 
        {
            foreach  ($objects->sections[0]->elements as $file ) 
            {
                
                if ($i%4 == 0 ) 
                {
                    ?></tr><tr><?php 
                }
                ?><td  class="image" valign="top"><?php 
                
                echo $file->html . "\n";
                if ($file->elements !== null) {
                    foreach  ($file->elements as $element) 
                    {
                        echo $element->html . "\n" ;
                    }
                }    
                
                ?></td><?php
                $i++;
            }
        }
        ?>
        </tr>
        </table>
    </div>
    </form>
</div>