jQuery.fn.check_all = function(target)
{
    var checked = $j(this).attr('checked') ? true : false;
    
    $j(target).find("input[@type='checkbox']").each(function(i)
    {
        // Skip the write protected
        if ($j(this).attr('disabled'))
        {
            return;
        }
        
        if (checked)
        {
            $j(this).attr('checked', 'checked');
        }
        else
        {
            $j(this).attr('checked', '');
        }
    });
}

jQuery.fn.invert_selection = function(target)
{
    $j(target).find("input[@type='checkbox']").each(function(i)
    {
        // Skip the write protected
        if ($j(this).attr('disabled'))
        {
            return;
        }
        
        if ($j(this).attr('checked'))
        {
            $j(this).attr('checked', '');
        }
        else
        {
            $j(this).attr('checked', 'checked');
        }
    });
    
    $j(this).attr('checked', '');
}