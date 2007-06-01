function check_visibility(value)
{
    var day_selector = document.getElementById('net_nemein_repeathandler_days');
    
    if (value != 'weekly_by_day')
    {
        day_selector.style.display = 'none';
        return;
    }
    
    if (day_selector.style.display == 'none')
    {
        day_selector.style.display = 'block';
    }
}

function check_radiobox(id)
{
    var radiobox = document.getElementById(id);
    
    if (!radiobox)
    {
        return;
    }
    
    var inputs = radiobox.parentNode.getElementsByTagName('input');
    
    for (var i = 0; i < inputs.length; i++)
    {
        if (inputs[i].type != 'radio')
        {
            continue;
        }
        
        var text = document.getElementById(inputs[i].id + '_field');
        
        if (   !text
            || text.length == 0)
        {
            continue;
        }
        
        if (text.id == radiobox.id + '_field')
        {
            text.removeAttribute('readonly');
        }
        else
        {
            text.setAttribute('readonly', 'readonly');
        }
    }
}