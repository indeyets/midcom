var sections = new Array();
var edit_icon_src = 'http://devel-xen-17.nemein.net/midcom-static/stock-icons/16x16/edit.png';
var submit_switch = false;

function alter_title(object)
{
    var group_title = object.getElementsByTagName('span')[0];
    var input_field = object.getElementsByTagName('input')[0];
    
    var new_input = document.createElement('input');
    new_input.type = 'text';
    new_input.value = group_title.innerHTML;
    new_input.className = 'net_nemein_personnel_inputfield';
    
    var button = document.createElement('input');
    button.type = 'submit';
    button.value = 'OK';
    button.setAttribute('onClick', 'javascript:save_title(this.parentNode)');
    button.setAttribute('onSubmit', 'javascript:alert("foo");');
    
    object.appendChild(new_input);
    object.appendChild(button);
    object.setAttribute('onDblClick', 'javascript:void;');
    group_title.style.display = 'none';
}

function save_title(object)
{
    var group_title = object.getElementsByTagName('span')[0];
    var input_field = object.getElementsByTagName('input')[0];
    var new_input = object.getElementsByTagName('input')[1];
    var button = object.getElementsByTagName('input')[2];
    
    if (   group_exists(new_input.value)
        && group_title.innerHTML !== new_input.value)
    {
        alert('Group with that name already exists!');
        return false;
    }
    
    group_title.innerHTML = new_input.value;
    group_title.style.display = 'block';
    var string = input_field.value;
    var args = string.split('::');
    input_field.value = 'group::' + args[1] + '::' + new_input.value;
    
    object.removeChild(button);
    object.removeChild(new_input);
    object.setAttribute('onDblClick', 'javascript:alter_title(this)');
    
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
    list_header.setAttribute('onDblClick', 'javascript:alter_title(this);');
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
    // new_list.setAttribute('onMouseUp', 'destroy_hidden_list_items(this);');
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

function check_submit()
{
    if (submit_switch != true)
    {
        return false;
    }
    
    return true;
}

function set_submit(submit_switch)
{
    submit = submit_switch;
}