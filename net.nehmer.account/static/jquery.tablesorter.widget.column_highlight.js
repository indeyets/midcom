(function($)
{
    $.tablesorter.addWidget({
        id: 'column_highlight',
        format: function(table)
        {
            $j(this).find('tbody tr:odd').each(function(i)
            {
                $j(this).removeClass('even');
                $j(this).addClass('odd');
            });
            
            $j(this).find('tbody tr:even').each(function(i)
            {
                $j(this).removeClass('odd');
                $j(this).addClass('even');
            });
        }
    });
})(jQuery);
