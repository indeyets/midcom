jQuery.fn.moderate_form = function()
{
    jQuery(this).find('input[@type="submit"]').click(function()
    {
        // Enter a new hidden value
        jQuery('<input type="hidden" />')
            .attr('name', jQuery(this).attr('name'))
            .attr('value', jQuery(this).attr('value'))
            .appendTo(jQuery(this.parentNode));
    });
    
    jQuery(this).find('form').submit(function()
    {
        var object = this;
        
        // Get the parent row
        var i = 0;
        while (i < 20 && !object.tagName.match(/^tr$/i))
        {
            object = object.parentNode;
            i++;
        }
        
        var action = jQuery(this).attr('action');
        
        if (action.match(/\?/))
        {
            var delimiter = '&';
        }
        else
        {
            var delimiter = '?';
        }
        
        jQuery(this).attr('action', action + delimiter + 'ajax');
        
        jQuery(this).ajaxSubmit(
        {
            target: '#' + jQuery(object).attr('id'),
            beforeSubmit: function()
            {
                jQuery(object).addClass('loading');
            },
            success: function()
            {
                jQuery(object).removeClass('loading');
            }
        });
        
        return false;
    });
}

jQuery(document).ready(function()
{
    jQuery('#org_routamc_photostream_moderate').moderate_form();
});