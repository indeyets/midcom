/**
 * Width of the placeholder for placing the picture horizontally to the center.
 * Leave to 0 if not using the feature
 * 
 * @access public
 * @var integer
 */
var image_placeholder_width = 0;

/**
 * Height of the placeholder for placing the picture vertically to the center.
 * Leave to 0 if not using the feature
 * 
 * @access public
 * @var integer
 */
var image_placeholder_height = 0;

/**
 * ID of the image placeholder, which will be having images in it
 * 
 * @access public
 * @var String
 */
var image_placeholder = 'cc_kaktus_exhibitions_image_placeholder';

/**
 * ID of the text placeholder, which will be having alternate text in it
 * 
 * @access public
 * @var String
 */
var placeholder_text = 'cc_kaktus_exhibitions_image_placeholder_text';

/**
 * Sets onclick events for each a-tag in the requested ID container
 * 
 * @access public
 * @param String id    ID of the container
 * @return void
 */
function set_onclick_events(id)
{
    var list = document.getElementById(id);
    
    if (!list)
    {
        return;
    }
    
    var links = list.getElementsByTagName('a');
    
    for (var i = 0; i < links.length; i++)
    {
        // Store the old image location
        links[i].rel = links[i].href;
        
        links[i].href = '#' + i;
        links[i].onclick = function()
        {
            show_image(this);
        }
        
        if (String(i) == window.location.hash.replace(/#/, ''))
        {
            var placeholder = document.getElementById(placeholder);
            if (placeholder)
            {
                var images = placeholder.getElementsByTagName('img');
                var date = new Date();
                
                // Hide every image loaded in the beginning if there is something else to display
                for (var n = 0; n < images.length; n++)
                {
                    if (!images[n].id)
                    {
                        images[n].id = 'temp_startup_' + date.getTime();
                    }
                    
                    images[n].style.display = 'none';
                    
                    destroy_image(images[n].id);
                }
            }
            
            show_image(links[i]);
        }
    }
}

/**
 * Shows the requested image
 * 
 * @access private
 * @param object    A tagged object
 */
function show_image(object)
{
    var src = object.rel;
    var title = object.title;
    add_anchors(object.href.match(/#(.+)$/)[1]);
    
    var placeholder = document.getElementById(image_placeholder);
    
    if (!placeholder)
    {
        alert('Could not get the placeholder: ' + src);
        return;
    }
    
    var spans = object.getElementsByTagName('span');
    var width = 0;
    var height = 0;
    
    for (var i = 0; i < spans.length; i++)
    {
        switch (spans[i].className)
        {
            case 'width':
                width = spans[i].innerHTML;
                break;
            case 'height':
                height = spans[i].innerHTML;
                break;
        }
    }
    
    var images = placeholder.getElementsByTagName('img');
    
    // Does the image need to be changed?
    var change = false;
    
    // Check the existing images: if there is even one that has different src than
    // the requested, set change to true
    for (var i = 0; i < images.length; i++)
    {
        if (   images[i].src != src
            && images[i].style.display != 'none')
        {
            change = true;
        }
    }
    
    // If nothing needs to be changed, end the function
    if (!change)
    {
        return;
    }
    
    // Date object
    var date = new Date();
    
    var img = new Image();
    img.src = src;
    img.style.display = 'none';
    img.id = 'temp_' + date.getTime();
    img.className = 'visible';
    
    if (width)
    {
        img.width = width;
        
        if (image_placeholder_width)
        {
            img.style.marginLeft = ((image_placeholder_width - img.width) / 2) + 'px';
            img.style.marginRight = ((image_placeholder_width - img.width) / 2) + 'px';
        }
    }
    
    if (height)
    {
        img.height = height;
        
        if (image_placeholder_height)
        {
            img.style.marginTop = ((image_placeholder_height - img.height) / 2) + 'px';
            img.style.marginBottom = ((image_placeholder_height - img.height) / 2) + 'px';
        }
    }
    
    var text = document.getElementById(placeholder_text);
    
    if (  text
       && text.innerHTML != title)
    {
        text.innerHTML = title;
    }
    
    var id = img.id;
    
    placeholder.appendChild(img);
    
    Effect.Appear(img.id, {duration:1});
    
    for (var i = 0; i < images.length; i++)
    {
        if (!images[i].id)
        {
            images.id = 'temp_' + i + '_' + date.getTime();
        }
        
        if (images[i].id == id)
        {
            continue;
        }
        
        images[i].className = 'replaced';
        
        Effect.Fade(images[i].id, {duration:1});
        setTimeout('destroy_image("' + images[i].id + '");', 2000);
    }
}

/**
 * Destroy the requested image
 * 
 * @access private
 * @return void
 */
function destroy_image(id)
{
    var image = document.getElementById(id);
    
    if (!image)
    {
        return;
    }
    
    image.parentNode.removeChild(image);
}

/**
 * Add anchor tag (i.e. hash location) to each A tag
 * 
 * @access private
 * @return void
 */
function add_anchors(hashed)
{
    var description = document.getElementById('cc_kaktus_exhibitions_subpages');
    
    if (!description)
    {
        return;
    }
    
    var links = description.getElementsByTagName('a');
    
    if (!links)
    {
        return;
    }
    
    for (var i = 0; i < links.length; i++)
    {
        if (!links[i].href.match(/#/))
        {
            links[i].href = links[i].href + '#';
        }
        
        links[i].href = links[i].href.replace(/#.*$/, '#' + hashed);
    }
}
