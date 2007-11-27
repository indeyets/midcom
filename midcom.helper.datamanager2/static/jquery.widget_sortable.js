jQuery.fn.create_sortable = function()
{
    // Hide the inputs
    $j(this).find('input[@type="text"]').css({display: 'none'});
    
    $j(this).each(function(i)
    {
        $j(this).sortable({
            containment: 'parent',
            change: function(e, ui)
            {
                // Update all the text inputs to keep track on the changes
                $j(this.parentNode).find('input[@type="text"]').each(function(i)
                {
                    $j(this).attr('value', i + 1);
                });
            }
        });
    });
}

$j(document).ready(function()
{
    $j('body p.sortable-help').css({display: 'none !important'});
    $j('body p.sortable-help-jquery').css({display: 'block !important'});
});
