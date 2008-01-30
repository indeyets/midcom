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
        
        // Trigger the onChange event of the input
        $j(this).change();
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
        
        // Trigger the onChange event of the input
        $j(this).change();
    });
    
    $j(this).attr('checked', '');
}

$j(document).ready(function()
{
    $j('#batch_process tbody tr').find('td:first').addClass('first');
    $j('#batch_process tbody tr').find('td:last').addClass('last');
    
    $j("#batch_process tbody input[@type='checkbox']").each(function(i)
    {
        $j(this).change(function()
        {
            var object = this.parentNode;
            var n = 0;
            
            while (!object.tagName.match(/tr/i))
            {
                object = object.parentNode;
                
                // Protect against infinite loops
                if (n > 20)
                {
                    return;
                }
            }
            
            if ($j(this).attr('checked'))
            {
                $j(object).addClass('row_selected');
            }
            else
            {
                $j(object).removeClass('row_selected');
            }
        });
    });
});
