/**
 * The functions in this file are based on the G2image plugin found here:
 * http://g2image.steffensenfamily.com/index.php?title=Main_Page
 */

/**
 * Mangle the HTML generated to Datamanager 2 to add the image insertion links
 */
function imagePopupConvertImagesForAddition()
{
    // We convert the image "size lists" into the addition clickers
    imageRows = document.getElementsByClassName('midcom_helper_datamanager2_widget_images_image');
    if (imageRows)
    {
        for (i = 0; i < imageRows.length; i++) 
        {
            imageGuid = imageRows[i].getAttribute('title');
            if (imageGuid)
            {
                image = new Array();
                image['guid'] = imageGuid;
                
                // Find the rest of the metadata
                imageCells = imageRows[i].getElementsByTagName('td');
                if (!imageCells)
                {
                    alert("No cells!");
                }
                
                for (ii = 0; ii < imageCells.length; ii++) 
                {
                    imageCellTitle = imageCells[ii].getAttribute('title');
                    
                    // Image filename
                    if (Element.hasClassName(imageCells[ii], 'filename'))
                    {
                        image['name'] = imageCells[ii].getAttribute('title');
                        imageLinks = imageCells[ii].getElementsByTagName('a');

                        // Tweak the image links
                        for (iii = 0; iii < imageLinks.length; iii++) 
                        {
                            imageLinks[iii].href = 'javascript:insertImage("' + imageGuid + '");';
                            imageLinks[iii].target = '';
                            imageLinks[iii].title = 'Click to insert';
                        }
                    }
                    else if (Element.hasClassName(imageCells[ii], 'title'))
                    {
                        image['title'] = imageCells[ii].getAttribute('title');
                    }
                    // TODO: Get type
                }
                
                // Populate image to the image info Array
                imagepopup_images[imageGuid] = new Array();
                imagepopup_images[imageGuid]['title'] = image['title'];
                imagepopup_images[imageGuid]['name'] = image['name'];
                imagepopup_images[imageGuid]['type'] = 'image';
            }
        }
    }
}
function imagePopupConvertFilesForAddition()
{
    // We convert the image "size lists" into the addition clickers
    imageRows = document.getElementsByClassName('midcom_helper_datamanager2_widget_downloads_download');
    if (imageRows)
    {
        for (i = 0; i < imageRows.length; i++) 
        {
            imageGuid = imageRows[i].getAttribute('title');
            if (imageGuid)
            {
                image = new Array();
                image['guid'] = imageGuid;
                
                // Find the rest of the metadata
                imageCells = imageRows[i].getElementsByTagName('td');
                if (!imageCells)
                {
                    alert("No cells!");
                }
                
                for (ii = 0; ii < imageCells.length; ii++) 
                {
                    imageCellTitle = imageCells[ii].getAttribute('title');
                    
                    // Image filename
                    if (Element.hasClassName(imageCells[ii], 'filename'))
                    {
                        image['name'] = imageCells[ii].getAttribute('title');
                        imageLinks = imageCells[ii].getElementsByTagName('a');

                        // Tweak the image links
                        for (iii = 0; iii < imageLinks.length; iii++) 
                        {
                            imageLinks[iii].href = 'javascript:insertImage("' + imageGuid + '");';
                            imageLinks[iii].target = '';
                            imageLinks[iii].title = 'Click to insert';
                        }
                    }
                    else if (Element.hasClassName(imageCells[ii], 'title'))
                    {
                        image['title'] = imageCells[ii].getAttribute('title');
                    }
                    // TODO: Get type
                }
                
                // Populate image to the image info Array
                imagepopup_images[imageGuid] = new Array();
                imagepopup_images[imageGuid]['title'] = image['title'];
                imagepopup_images[imageGuid]['name'] = image['name'];
                imagepopup_images[imageGuid]['type'] = 'attachment';
                
            }
        }
    }
}
 
/**
 * HTML insertion to selected area function
 * TODO: this functionshould make it possible to insert images into markdown textareas as well!
 */
function insertAtCursor(myField, myValue) 
{
	//IE support
	if (document.selection && !window.opera) 
	{
		myField.focus();
		sel = window.opener.document.selection.createRange();
		sel.text = myValue;
	}
	//MOZILLA/NETSCAPE/OPERA support
	else if (myField.selectionStart || myField.selectionStart == '0') 
	{
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos)
		+ myValue
		+ myField.value.substring(endPos, myField.value.length);
	}
    else 
    {
		myField.value += myValue;
	}
}

/**
 * Insert an image to its correct place in the editor
 * TODO: Add support for Markdown areas
 */
function insertImage(objId) 
{
    // Generate the HTML needed to be inserted
	imagehtml=makeHtmlForInsertion(objId);

	// Insert the HTML into TinyMCE
    window.opener.tinyMCE.execCommand("mceInsertContent", true, imagehtml);
    
    // Close the popup
	window.close();
}

/**
 * Generate the HTML required for image insertion based on its type
 */
function makeHtmlForInsertion(objId)
{	
	var iminfo = imagepopup_images[objId];
	var html_code = '';

	switch (iminfo['type']) 
	{
		
		case "attachment":
		html_code = '<a href="' + imagepopup_images['prefix'] + 
					    objId +"/" + iminfo['name'] + '" >' + iminfo['title'] + '</a>';
		
		break;
		
		case "image":
		default:
		html_code = '<img src="' +
					    imagepopup_images['prefix'] + 
					    objId + '/' + iminfo['name'] + '" alt="' +
					    iminfo['title'] + '" title="' +
					    iminfo['title'] + '"/>';
		break;
	}
	
	return html_code;
}

function imagePopupConvertResultsForAddition()
{
    resultRows = document.getElementsByClassName('midcom_helper_imagepopup_search_result_item');
    if (resultRows)
    {
        for (i = 0; i < resultRows.length; i++) 
        {
            itemGuid = resultRows[i].getAttribute('title');
            itemType = resultRows[i].getAttribute('rel');
            if (itemGuid)
            {
                item = new Array();
                item['guid'] = itemGuid;
                item['type'] = itemType;
				
				informationBlocks = resultRows[i].getElementsByTagName('span');
                if (!informationBlocks)
                {
                    alert("No metadata information available for object "+item['guid']+"!");
                }

				for (ii = 0; ii < informationBlocks.length; ii++)
				{
					informationTitle = informationBlocks[ii].getAttribute('title');
					item[informationTitle] = informationBlocks[ii].innerHTML;
				}

                imageLinks = resultRows[i].getElementsByTagName('a');

                // Tweak the image links
                for (iii = 0; iii < imageLinks.length; iii++) 
                {
                    imageLinks[iii].href = 'javascript:insertImage("' + itemGuid + '");';
                    imageLinks[iii].target = '';
                    imageLinks[iii].title = 'Click to insert';
                }
                
                // Populate image to the image info Array
                imagepopup_images[itemGuid] = new Array();
                imagepopup_images[itemGuid]['title'] = item['title'] == undefined ? item['name'] : item['title'];
                imagepopup_images[itemGuid]['name'] = item['name'];
                imagepopup_images[itemGuid]['type'] = itemType;
            }
        }
    }
}