var _l10n_select_two = 'select exactly two choices';

var prev = new Array(2);
prev[0] = '';
prev[1] = '';

$j(document).ready(function()
{
    $j('#midgard_admin_asgard_rcs_version_compare tbody td input[@type="checkbox"]').click(function()
    {
        toggle_checkbox(this);
        
        if ($j(this).attr('checked'))
        {
            $j(this.parentNode.parentNode).addClass('selected');
        }
        else
        {
            $j(this.parentNode.parentNode).removeClass('selected');
        }
    });
    
    $j('#midgard_admin_asgard_rcs_version_compare').submit(function()
    {
        var count = 0;
        $j('#midgard_admin_asgard_rcs_version_compare').find('tbody td input[@type="checkbox"]').each(function(i)
        {
            if ($j(this).attr('checked'))
            {
                count++;
            }
        });
        
        if (count == 2)
        {
            return true;
        }
        
        alert(_l10n_select_two);
        return false;
    });
});

function toggle_checkbox(object)
{
    if (!$j(object).attr('checked'))
    {
        return;
    }
    
    if (prev[1])
    {
        $j('#' + prev[1]).attr('checked', '');
        $j('#' + prev[1] + '_row').removeClass('selected');
    }
    
    if (prev[0])
    {
        prev[1] = prev[0];
    }
    
    prev[0] = object.id;
}