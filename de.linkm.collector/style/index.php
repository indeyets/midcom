<?php
    global $view;
    global $view_cat;
    global $view_l10n;
    global $view_l10nmidcom;
    
    if (array_key_exists ("image", $view))  {
        $image = $view["image"]["thumbnail"];
        $fullimg = $view["image"];
    } else {
        $image = false;
        $fullimg_url = false;
    }
    
?>
<h2><?php echo $view_l10n->get("available categories"); ?></h2>

<?php if ($image) { ?>
  <a href="&(fullimg['url']);">
    <img src="&(image['url']);" align="right" border="0"
      alt="&(image['description']);" 
      title="&(image['description']); (Bild in voller Größe: &(fullimg['size_x']);x&(fullimg['size_y']);, &(fullimg['formattedsize']); Bytes)"
      &(image['size_line']:h);>
  </a>
<?php } ?>

&(view['content']:F);

<ul>
<?php foreach ($view_cat as $id => $data) { ?>
    <li><a href="&(data['url']);/">&(data['name']);</a></li>
<?php } ?>
</ul>
