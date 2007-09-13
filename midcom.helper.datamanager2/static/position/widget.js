var widgets = [];
var current_pos = null;
var widgets_mapstrations = [];

function init_position_widget(widget_id, mapstration)
{   
    jQuery('#' + widget_id).each(function(i,w){
        var widget = jQuery(w);

        widgets[w.id] = widget;

        var geoclue_btn = jQuery('.geoclue_button', widget);
        var indicator = jQuery('.indicator', widget);
        var status_box = jQuery('.status_box', widget);
        
        var position_marker = null;

        widgets_mapstrations[widget_id] = mapstration;
        widgets_mapstrations[widget_id].addMapTypeControls();
        widgets_mapstrations[widget_id].addEventListener('click', new_position);
        
        function new_position(point)
        {
            current_pos = point;
            
            jQuery('#' + input_data['latitude']['id']).attr('value',point.lat);
            jQuery('#' + input_data['longitude']['id']).attr('value',point.lon);
            
            set_marker('Current position');
        }
        
        var input_data = {};

        indicator.hide();

        geoclue_btn.bind('click', function(e){
            refresh_geoclue();
            geoclue_btn.hide();
            indicator.show();
        });

        function refresh_geoclue()
        {
            var backend_url = jQuery('input.position_widget_backend_url',widget).attr('value');
            var backend_service = jQuery('input.position_widget_backend_service',widget).attr('value');

            var get_params = {
                service: backend_service
            };

            jQuery('input.position_widget_input',widget).each(function(i,o){
                var jqo = jQuery(o);
                var key = get_key_name(jqo.attr('name'));
                input_data[key] = {
                    id: jqo.attr('id'),
                    value: jqo.attr('value')
                };
                if (input_data[key]['value'] == undefined)
                {
                    input_data[key]['value'] = '';
                }
                else
                {
                    get_params[key] = input_data[key]['value'];
                }
            });

            jQuery.ajax({
                type: "GET",
                url: backend_url,
                data: get_params,
                dataType: "xml",
                error: function(request, type, expobj){
                    parse_error(request.responseText);

                    indicator.hide();
                    geoclue_btn.show();
                },
                success: function(data){
                    var parsed = parse_response(data);
                    update_widget(parsed);
                }
            });

            function parse_error(error_string)
        	{
                status_box.html(error_string);
        	}

            function parse_response(data)
        	{
        	    status_box.html('');
        	    
                var results = [];
                jQuery('position',data).each(function(idx) {            
                    var rel_this = jQuery(this);

                    results[idx] = {      	    
                        latitude: rel_this.find("latitude").text(), 
                        longitude: rel_this.find("longitude").text(),
                        accuracy: rel_this.find("accuracy").text(),
                        city: rel_this.find("city").text(),
                        region: rel_this.find("region").text(),
                        country: rel_this.find("country").text(),
                        postalcode: rel_this.find("postalcode").text()
                    };
                });

            	return results[0];
        	}
        }

        function update_widget(location_data)
        {
            indicator.hide();
            geoclue_btn.show();

            jQuery.each(location_data, function(key,value){
                if (input_data[key])
                {
                    jQuery('#' + input_data[key]['id']).attr('value',value);
                }
                else
                {
                    //console.log("Key: "+key+" not found in items.");
                }
            });

            current_pos = new LatLonPoint(location_data['latitude'],location_data['longitude']);

            var info = location_data['city'] + ", " + location_data['country'] + ", " + location_data['postalcode'];
            var label = 'Current position';
            if (input_data['description'])
            {
                label = input_data['description'];
            }
            else if (location_data['description'])
            {
                label = location_data['description'];
            }
            
            set_marker(label, info);
        }

        function get_key_name(key)
        {
            // if (pos = key.indexOf('name') != -1)
            // {
            //     return 'name';
            // }
            if (pos = key.indexOf('country') != -1)
            {
                return 'country';
            }
            if (pos = key.indexOf('city') != -1)
            {
                return 'city';
            }
            if (pos = key.indexOf('street') != -1)
            {
                return 'street';
            }
            if (pos = key.indexOf('postalcode') != -1)
            {
                return 'postalcode';
            }
            if (pos = key.indexOf('latitude') != -1)
            {
                return 'latitude';
            }
            if (pos = key.indexOf('longitude') != -1)
            {
                return 'longitude';
            }
        }
        
        function set_marker(label, info)
        {
            //console.log("set_marker label: "+label+" info: "+info);
            
            if (position_marker != null)
            {
                 widgets_mapstrations[widget_id].removeMarker(position_marker);
            }
            
            position_marker = new Marker(current_pos);
            
            if (label != undefined)
            {
                position_marker.setLabel(label);                
            }
            
            if (info != undefined)
            {
                position_marker.setInfoBubble(info);                
            }
            
            widgets_mapstrations[widget_id].addMarker(position_marker);

            // if (info != undefined)
            // {
            //     position_marker.openBubble();
            // }
        }

    });
}

function init_current_pos(lat,lon)
{
    current_pos = new LatLonPoint(lat,lon);
}

function position_map_to_current(widget_id)
{
    //console.log("position_map_to_current widget_id: "+widget_id);
    //console.log("current_pos: "+current_pos);
    
    if (current_pos != null)
    {
        widgets_mapstrations[widget_id].resizeTo(400,280);
        widgets_mapstrations[widget_id].setCenterAndZoom(current_pos, 13);
        widgets_mapstrations[widget_id].resizeTo(420,300);
    }
}
