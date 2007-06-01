<h2><?php echo $data['component_details']['name']; ?> (&(data['component']:h);)</h2>
<p>
<?php
echo $data['component_details']['description'];
?>
</p>
<ul>
<?php
foreach ($data['style_elements'] as $style_element => $filename)
{
?>
    <li><a href="edit/&(style_element);/">&lt;(&(style_element);)&gt;</a></li>
<?php
}
?>
</ul>
