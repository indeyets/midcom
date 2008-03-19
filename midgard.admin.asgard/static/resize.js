var ie6 = false;

$j(document).ready(function()
{
    $j('<div></div>')
        .attr('id', 'midgard_admin_asgard_resizer')
        .css('left', $j('#content').css('margin-left'))
        .mouseover(function()
        {
            $j(this).addClass('hover');
        })
        .mouseout(function()
        {
            $j(this).removeClass('hover');
        })
        .appendTo('#container-wrapper');
    
    $j('#midgard_admin_asgard_resizer').draggable({
        axis: 'axis-x',
        containment: '#container-wrapper',
        stop: function()
        {
            var offset = $j(this).offset();
            
            if (!ie6)
            {
                var navigation_width = offset.left - 36;
                var content_margin_left = offset.left + 6;
            }
            else
            {
                var navigation_width = offset.left - 16;
                var content_margin_left = offset.left + 6;
            }
            
            $j('#navigation').css('width', navigation_width + 'px');
            $j('#content').css('margin-left', content_margin_left + 'px');
            
            
            jQuery.post(MIDGARD_ROOT + '__mfa/asgard/preferences/ajax/', {offset: offset.left});
        }
    });
});
