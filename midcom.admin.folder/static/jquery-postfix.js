$j(document).ready(function()
{
    $j('#midcom_admin_folder_order_form ul.sortable input').css('display', 'none');
    $j('#midcom_admin_folder_order_form_sort_type div.form_toolbar').css('display', 'none');
    
    $j('#midcom_admin_folder_order_form_sort_type select').change(function()
    {
        $j('#midcom_admin_folder_order_form_sort_type').submit();
    });
    
    $j('#midcom_admin_folder_order_form_sort_type').submit(function()
    {
        var date = new Date();
        var location = $j('#midcom_admin_folder_order_form_sort_type').attr('action') + '?ajax&time=' + date.getTime();
        
        $j('#midcom_admin_folder_order_form_sort_type').ajaxSubmit
        (
            {
                url: location,
                target: '#midcom_admin_folder_order_form_wrapper'
            }
        );
        window.location.hash = '#midcom_admin_folder_order_form_wrapper';
        return false;
    });
    
    $j('#midcom_admin_folder_order_form').submit(function()
    {
        $j(this).find('ul').each(function(i)
        {
            var count = $j(this).find('li').size();
            
            $j(this).find('li').each(function(i)
            {
                $j(this).find('input').attr('value', i);
            });
        });
    });
});
