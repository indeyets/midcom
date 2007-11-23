jQuery.fn.create_sortable = function()
{
    $j(this).each(function(i)
    {
        $j(this).sortable({
            containment: $j(this).attr('id')
        });
    });
}