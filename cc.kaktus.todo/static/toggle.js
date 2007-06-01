function cc_kaktus_todo_toggle(id)
{
    var element = document.getElementById(id);
    
    if (!id)
    {
        alert('Could not get the requested element: '.id);
        return;
    }
    
    if (element.style.display == 'none')
    {
        Effect.SlideDown(id);
//        alert('It is hidden');
    }
    else
    {
        Effect.SlideUp(id);
//        alert('It is already visible!');
    }
}

function cc_kaktus_todo_synchronize(id)
{
    
}