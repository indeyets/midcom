function handleAisNavigationDisplay(image)
{
    navigation = document.getElementById('ais_bottom_navigation');
    if (navigation.style.display == 'block')
    {
        // Hide the navigation area
        navigation.style.display = 'none';
        image.src = '/midcom-static/stock-icons/16x16/stock_right.png';
    }
    else
    {
        // Show the navigation area    
        navigation.style.display = 'block';
        image.src = '/midcom-static/stock-icons/16x16/stock_down.png';    
    }
}