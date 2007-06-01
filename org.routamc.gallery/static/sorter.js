/**
 * Those sections, which should allow trading of sortables items between each others
 * 
 * @var Array sections
 */
var sections = new Array();

/**
 * URL of the edit image
 * 
 * @var String edit_icon_src
 */
var edit_icon_src = '/midcom-static/stock-icons/16x16/edit.png';

/**
 * URL of the save image
 * 
 * @var String save_icon_src
 */
var save_button_src = '/midcom-static/stock-icons/16x16/save.png';

/**
 * URL of the cancel image
 * 
 * @var String cancel_icon_src
 */
var cancel_button_src = '/midcom-static/stock-icons/16x16/cancel.png';

/**
 * Create a new question
 */
function create_group()
{
    var date = new Date();
    var input = document.getElementById('create_group');
    
    if (group_exists(input.value))
    {
        alert('Group with that name already exists!');
        return;
    }
    
    // Root element
    var root = document.getElementById('org_routamc_gallery_sort');
    
    // Group list element
    var group = document.createElement('li');
    group.className = 'group';
    group.id = 'org_routamc_gallery_sort_new_' + date.getTime();
    root.appendChild(group);
    
    // Create the group title
    var group_title = document.createElement('h3');
    group_title.className = 'handle';
    group_title.setAttribute('ondblclick', 'javascript:alter_title(this, "group");');
    group.appendChild(group_title);
    
    // Create the group title textual content
    var span = document.createElement('span');
    span.innerHTML = input.value;
    group_title.appendChild(span);
    
    // Create a question group container
    var group_questions = document.createElement('ul');
    group_questions.className = 'section';
    group_questions.id = group.id + '_subset';
    group.appendChild(group_questions);
    
    // Title editor
    var group_input = document.createElement('input');
    group_input.type = 'hidden';
    group_input.name = 'sortable[]';
    group_input.value = 'group_new:' + date.getTime() + '_' + input.value;
    group_title.appendChild(group_input);
    
    var i = sections.length;
    sections[i] = group.id + '_subset';
    
    // Refresh the sortables list
    refresh_sortables_lists();
    
    // Clear the input title text
    input.value = '';
}

/**
 * Refreshes the list of sortables upon creation of new items
 * 
 * @access public
 */
function refresh_sortables_lists()
{
    destroy_line_item_sortables();
    create_line_item_sortables();
    Sortable.create('org_routamc_gallery_sort',{tag:'li', handle:'handle'});
}

/**
 * Create sortables for each section
 * 
 * @access public
 */
function create_line_item_sortables()
{
    for(var i = 0; i < sections.length; i++)
    {
        Sortable.create(sections[i], {tag:'li', dropOnEmpty: true, containment: sections, only:'sortable'});
    }
}

/**
 * Destroy the sortables lists
 * 
 * @access public
 */
function destroy_line_item_sortables()
{
    for(var i = 0; i < sections.length; i++)
    {
        Sortable.destroy(sections[i]);
    }
}

/**
 * Initialize the sortables listing on the first time
 * 
 * @access public
 */
function initialize_ordering()
{
    sortable_lists = document.getElementById('org_routamc_gallery_sort').getElementsByTagName('ul');
    
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
    Sortable.create('org_routamc_gallery_sort',{tag:'li', handle: 'handle', only:'group'});
}

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
    new_input.name = 'unset[]';
    new_input.value = group_title.innerHTML;
    new_input.className = 'input';
    
    // Save button
    var save = document.createElement('img');
    save.src = save_button_src;
    save.alt = 'Save';
    save.setAttribute('onClick', 'javascript:save_title(this.parentNode, true, "' + type + '")');
    
    // Cancel button
    var cancel = document.createElement('img');
    cancel.src = cancel_button_src;
    cancel.alt = 'Cancel';
    cancel.setAttribute('onClick', 'javascript:save_title(this.parentNode, false, "' + type + '")');
    
    object.appendChild(new_input);
    object.appendChild(save);
    object.appendChild(cancel);
    object.setAttribute('ondblclick', '');
    group_title.style.display = 'none';
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
        var args = string.split('_');
        input_field.value = type + '_' + args[1] + '_' + new_input.value;
    }
    
    // Show the element
    title.style.display = 'block';
    
    object.removeChild(save_button);
    object.removeChild(cancel_button);
    object.removeChild(new_input);
    object.setAttribute('ondblclick', 'javascript:alter_title(this, "' + type + '")');
}

/**
 * Check if the group already exists
 * 
 * @access public
 * @param String string     Name of the new group
 * @return boolean          True if group exists and false if the name is unique
 */
function group_exists(string)
{
    var groups = document.getElementById('org_routamc_gallery_sort').getElementsByTagName('li');
    
    for (i = 0; i < groups.length; i++)
    {
        // Skip those li elements that aren't straight children of the requested master group
        if (groups[i].parentNode.id != 'org_routamc_gallery_sort')
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

/**
 * Switch 'hidden' in the class name.
 * 
 * @access public
 * @param input checkbox       Checkbox input
 * @param String class_name    Name of the class that should be hidden
 * @param String root_object   ID of the root object
 */
function hide_images(checkbox, root_object_id)
{
    var root = document.getElementById(root_object_id);
    var items = root.getElementsByTagName('img');
    
    for (var i = 0; i < items.length; i++)
    {
        if (checkbox.checked)
        {
            items[i].className = items[i].className + ' hidden';
        }
        else
        {
            items[i].className = items[i].className.replace(/\s*hidden$/, '');
        }
    }
}

