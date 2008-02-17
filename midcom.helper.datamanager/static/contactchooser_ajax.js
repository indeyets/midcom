//Store for callback data on per element basis
var ooAjaxContactsWidget_results_store = new Array();

//Add an entry to selected list of contacts widget
function ooAjaxContactsWidget_addSelected(guid, element)
{
    //Get the div for placing results to
    listDiv = document.getElementById('widget_contactchooser_selected_'+element.id);
    
    //Check mode and set to default if not present
    if (document.getElementById(element.id+'_ajaxWidgetMode'))
    {
        modeStr = document.getElementById(element.id+'_ajaxWidgetMode').value;
    }
    else
    {
        modeStr = 'single, guid';
    }

    //Get data from store
    person = ooAjaxContactsWidget_results[element.id + '_' + guid];
    /*
    id = person.getElementsByTagNameNS('http://ns.yukatan.fi/2005/midgard', 'id')[0].firstChild.data;
    fname = person.getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'firstName')[0].firstChild.data;
    lname = person.getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'lastName')[0].firstChild.data;
    */
    id = compat_getElementsByTagNameNS('http://ns.yukatan.fi/2005/midgard', 'mgd', 'id', person)[0].firstChild.data;
    fname = compat_getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'foaf', 'firstName', person)[0].firstChild.data;
    lname = compat_getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'foaf', 'lastName', person)[0].firstChild.data;

    //Only allow adding of new value, disallow readd of existing ones
    if (document.getElementById('widget_contactchooser_' + element.id + '_' + id))
    {
        //Input exists
        //return false;
        return;
    }

    //Parse mode    
    modeArr = ooAjaxParseMode(modeStr);

    onChangeHandler = '';
    //If we allow only single input clear old list and make the checkbox un-uncheckable
    if (modeArr['single'])
    {
        ooRemoveChildNodes(listDiv);
        onChangeHandler = 'ooAjaxTableFormHandler.checked=true;';
    }
    
    //Which identifier to return
    if (modeArr['id'])
    {
        useId = id;
    }
    else
    {
        useId = guid;
    }
    
    //Make visible
    ooAjaxSetClass(listDiv, 'widget_contactchooser_selected', true);
    listDiv.style.display = 'block';
    

    //Create label and input and append them to the selected list.
    personItem = ooCreateElement('li',null,false);
    personLabel = ooCreateElement('label', 
                            {'for': 'widget_contactchooser_' + element.id + '_' + id}, 
                            {},
                            false);
    personInput = ooCreateElement('input', 
                            {   'id': 'widget_contactchooser_' + element.id + '_' + id, 
                                'type': 'checkbox',
                                'name': element.id + '[' + useId + ']',
                                'checked': true,
                                'onChange': onChangeHandler}, 
                            {},
                            false);
    /*
    personItem.appendChild(personInput);
    personItem.appendChild(personLabel);
    personLabel.appendChild(document.createTextNode(lname + ', ' + fname))
    */
    //This way seems to be necessary due to IE stupidness, though the one above is cleaner    
    personLabel.appendChild(personInput);
    personLabel.appendChild(document.createTextNode(lname + ', ' + fname))
    personItem.appendChild(personLabel);
    
    listDiv.appendChild(personItem);
    //Work around IE not checking the contact properly
    document.getElementById('widget_contactchooser_' + element.id + '_' + id).checked = 'checked';

    //return true;
    return;
}

//Clear and hide the search results list
function ooAjaxContactsWidget_hideResults(element)
{
    listDiv = document.getElementById('widget_contactchooser_resultset_'+element.id);
    //ooRemoveChildNodes(listDiv);
    //ooAjaxSetClass(listDiv, 'widget_contactchooser_selected hidden', true);
    listDiv.style.display = 'none';
}

function ooAjaxContactsWidget_results(resultList, element)
{
    //Make the style "focused"
    //ooAjaxSetClass(element, 'ajax_editable ajax_focused', false);
    
    //Get the div for placing results to
    listDiv = document.getElementById('widget_contactchooser_resultset_'+element.id);
    //Clear current stuff
    ooRemoveChildNodes(listDiv);
    //Make visible
    //ooAjaxSetClass(listDiv, 'widget_contactchooser_resultset', true);
    listDiv.style.display = 'block';
     
    //persons = resultList.getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'Person');
    persons = compat_getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'foaf', 'Person', resultList);
    if (   !persons
        || persons.length==0)
    {
        //No results, do something
        return false;
    }
    
    for (var i=0;i < persons.length; i++)    
    {
        //Local copies of some info
        /*
        id = persons[i].getElementsByTagNameNS('http://ns.yukatan.fi/2005/midgard', 'id')[0].firstChild.data;
        guid = persons[i].getElementsByTagNameNS('http://ns.yukatan.fi/2005/midgard', 'guid')[0].firstChild.data;
        fname = persons[i].getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'firstName')[0].firstChild.data;
        lname = persons[i].getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'lastName')[0].firstChild.data;
        */
        id = compat_getElementsByTagNameNS('http://ns.yukatan.fi/2005/midgard', 'mgd', 'id', persons[i])[0].firstChild.data;
        guid = compat_getElementsByTagNameNS('http://ns.yukatan.fi/2005/midgard', 'mgd', 'guid', persons[i])[0].firstChild.data;
        fname = compat_getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'foaf', 'firstName', persons[i])[0].firstChild;
        if (fname)
        {
            fname = fname.data;
        }
        lname = compat_getElementsByTagNameNS('http://xmlns.com/foaf/0.1/', 'foaf', 'lastName', persons[i])[0].firstChild;
        if (lname)
        {
            lname = lname.data;
        }
        
        if (!fname && !lname)
        {
            // This person has no name
            lname = '#'+id;
            fname = 'person';
        }

        //Global cache for the object
        ooAjaxContactsWidget_results[element.id + '_' + guid] = persons[i];
        
        //jsCall = "javascript:ooAjaxContactsWidget_addSelected('" + guid + "', document.getElementById('" + element.id + "')); return false;";
        jsCall = "javascript:ooAjaxContactsWidget_addSelected('" + guid + "', document.getElementById('" + element.id + "'));";
        resultItem = ooCreateElement('li',null,false);
        resultLink = ooCreateElement('a', 
                            {   'onClick': jsCall,
                                'href': jsCall }, 
                            {'display': 'block'},
                            fname + ', ' + lname);
        resultItem.appendChild(resultLink);
        listDiv.appendChild(resultItem);
    }
    
    return true;
}

function ooAjaxContactsWidget(element)
{
    ajaxUrl = ooAjaxUrl(element);
    if (!ajaxUrl)
    {
        //Raise error somewhere so that developer can see it.
        ooDisplayMessage('ooAjaxContactsWidget: Handler URL for element id "' + element.id + '" cannot be found', 'error');
        return false;
    }
    
    if (element.value)
    {
        ooAjaxGet(ajaxUrl, 'search=' + element.value, element, 'ooAjaxContactsWidget_results');
    }
    
    return true;
}