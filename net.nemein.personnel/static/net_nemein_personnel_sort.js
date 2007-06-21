var sections = new Array();
var edit_icon_src = '/midcom-static/stock-icons/16x16/edit.png';
var save_icon_src = '/midcom-static/stock-icons/16x16/save.png';
var cancel_icon_src = '/midcom-static/stock-icons/16x16/cancel.png';
var submit_switch = false;

/**
 * Show the input field for changing the contents of the title
 * 
 * @access public
 * @param Object object     Parent object, which contains the information
 * @param String type       Type string for form handling
 */
function alter_title(object, type)
{
    var group_title = object.getElementsByTagName('span')[0];
    var input_field = object.getElementsByTagName('input')[0];
    
    var new_input = document.createElement('input');
    new_input.type = 'text';
    new_input.name = 'unset[' + input_field.id + ']';
    new_input.value = group_title.innerHTML;
    new_input.className = 'input';
    
    // Save button
    var save = document.createElement('img');
    save.src = save_button_src;
    save.alt = 'Save';
    save.className = 'button save';
    save.onclick = function()
    {
        save_title(this.parentNode, true, type);
    }
    
    // Cancel button
    var cancel = document.createElement('img');
    cancel.src = cancel_button_src;
    cancel.alt = 'Cancel';
    cancel.className = 'button cancel';
    cancel.onclick = function()
    {
        save_title(this.parentNode, false, type);
    }
    
    object.insertBefore(new_input, group_title.nextSibling);
    object.insertBefore(save, new_input.nextSibling);
    object.insertBefore(cancel, save.nextSibling);
    object.ondblclick = function ()
    {
        return;
    }
    
    object.ondblclick = function()
    {
        return;
    }
    
    group_title.style.display = 'none';
    
    // Check for the textareas
    var divs = object.getElementsByTagName('div');
    if (!divs)
    {
        return;
    }
    
    for (var i = 0; i < divs.length; i++)
    {
        if (divs[i].className !== 'fields')
        {
            continue;
        }
        
        if (!divs[i].id)
        {
            var date = new Date();
            divs[i].id = 'fields_temporaryid_' + date.getTime();
        }
        
        new Effect.SlideDown(divs[i].id);
    }
}

/**
 * Change the title and store the information in a hidden input field. Then
 * remove the text input field and related buttons.
 *
 * @param object object    Root object to save
 * @param boolean save     Switch to determine if the information should be saved
 * @param string type      Type of the object: either group or question
 * @return boolean         Indicate success
 */
function save_title(object, save, type)
{
    var title = object.getElementsByTagName('span')[0];
    var input_field = object.getElementsByTagName('input')[0];
    var new_input = object.getElementsByTagName('input')[1];
    var save_button = object.getElementsByTagName('img')[0];
    var cancel_button = object.getElementsByTagName('img')[1];
    
    if (type == 'group')
    {
        if (   save
            && group_exists(new_input.value)
            && title.innerHTML !== new_input.value)
        {
            alert('Group with that name already exists!');
            return false;
        }
    }
    
    if (save)
    {
        title.innerHTML = new_input.value;
        var string = input_field.value;
        var args = string.split('::');
        input_field.value = type + '::' + args[1] + '::' + new_input.value;
    }
    
    // Show the element
    title.style.display = 'block';
    
    object.removeChild(save_button);
    object.removeChild(cancel_button);
    object.removeChild(new_input);
    object.ondblclick = function()
    {
        alter_title(this, type);
    }
    
    var divs = object.getElementsByTagName('div');
    
    if (!divs)
    {
        return;
    }
    
    for (var i = 0; i < divs.length; i++)
    {
        if (divs[i].className != 'fields')
        {
            continue;
        }
        
        new Effect.SlideUp(divs[i].id);
    }
}

// Check if the requested group already exists
function group_exists(string)
{
    var groups = document.getElementById('net_nemein_personnel_groups').getElementsByTagName('li');
    
    return false;
    
    for (i = 0; i < groups.length; i++)
    {
        // Skip those li elements that aren't straight children of the requested master group
        if (groups[i].parentNode.id != 'net_nemein_personnel_groups')
        {
            continue;
        }
        
        // Skip if no span element was found (just bulletproofing)
        if (!groups[i].getElementsByTagName('span'))
        {
            continue;
        }
        
        // Return true on the first match
        if (groups[i].getElementsByTagName('span')[0].innerHTML == string)
        {
            return true;
        }
    }
    
    // No matches found, return false
    return false;
}

// Creates a new group element
function create_group()
{
    var groups_list = document.getElementById('net_nemein_personnel_groups');
    var group_name = document.getElementById('net_nemein_personnel_new_group_name');
    
    // Skip the empty group names
    if (group_name.value == '')
    {
        return;
    }
    
    if (group_exists(group_name.value))
    {
        alert('Group with that name already exists!');
        return false;
    }
    
    // Create the list item and header element
    var list_item = document.createElement('li');
    list_item.className = 'group';
    
    var list_header = document.createElement('h3');
    list_header.ondblclick = function()
    {
        alter_title(this, 'group');
    }
    
    list_header.className = 'handle';
    
    // Textual title name
    var new_title = document.createElement('span');
    new_title.innerHTML = group_name.value;
    new_title.setAttribute('title', l10n_tooltip_doubleclick + ' ' + l10n_tooltip_drag_n_drop);
    
    // Create the hidden input element for the form handling
    var new_input = document.createElement('input');
    new_input.type = 'hidden';
    new_input.name = 'sortable[]';
    new_input.value = 'group::new::' + group_name.value;
    
    // Create an empty placeholder list
    var new_list = document.createElement('ul');
    new_list.className = 'section';
    
    i = document.getElementById('net_nemein_personnel_groups').getElementsByTagName('ul').length;
    n = i + 1;
    
    new_list.id = 'net_nemein_personnel_group_list_' + n;
    
    // Does this actually need an event to destroy the invisible list item?
    sections[i] = new_list.id;
    
    // Append all the new objects
    groups_list.appendChild(list_item);
    list_item.appendChild(list_header);
    
    list_header.appendChild(new_title);
    list_header.appendChild(new_input);
    list_item.appendChild(new_list);
    
    group_name.value = '';
    
    refresh_sortables_lists();
}

function refresh_sortables_lists()
{
    destroy_line_item_sortables();
    create_line_item_sortables();
//    Sortable.create('net_nemein_personnel_groups',{tag:'li', handle: 'handle'});
    Sortable.create('net_nemein_personnel_groups',{tag:'li', handle:'handle'});
}

function create_line_item_sortables()
{
    for(var i = 0; i < sections.length; i++)
    {
        Sortable.create(sections[i], {tag:'li', dropOnEmpty: true, containment: sections, only:'sortable'});
    }
}

function destroy_line_item_sortables()
{
    for(var i = 0; i < sections.length; i++)
    {
        Sortable.destroy(sections[i]);
    }
}

function initialize_ordering()
{
    sortable_lists = document.getElementById('net_nemein_personnel_groups').getElementsByTagName('ul');
    
    // Get the initial sections
    for (n = 0; n < sortable_lists.length; n++)
    {
        sections[n] = sortable_lists[n].id;
    }
    
    // Set the initial sortables
    for (n = 0; n < sortable_lists.length; n++)
    {
        Sortable.create(sortable_lists[n].id,{tag:'li', dropOnEmpty: true, containment: sections, only:'sortable'});
    }
    
    // Set the master list to be sortable
    Sortable.create('net_nemein_personnel_groups',{tag:'li', handle: 'handle', only:'group'});
}
