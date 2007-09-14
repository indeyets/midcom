var widgets = [];
var current_pos = null;
var position_marker = null;
var widgets_mapstrations = [];

function init_position_widget(widget_id, mapstration)
{   
    jQuery('#' + widget_id).each(function(i,w){
        var widget = jQuery(w);

        widgets[w.id] = widget;

        var geodata_btn = jQuery('#' + widget_id + '_geodata_btn', widget);
        var indicator = jQuery('#' + widget_id + '_indicator', widget);
        var status_box = jQuery('#' + widget_id + '_status_box', widget);

        var backend_url = jQuery('input.position_widget_backend_url',widget).attr('value');
        var backend_service = jQuery('input.position_widget_backend_service',widget).attr('value');
        
        var input_data = {};

        jQuery('.position_widget_input',widget).each(function(i, o){
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
        });

        widgets_mapstrations[widget_id] = mapstration;
        widgets_mapstrations[widget_id].addMapTypeControls();
        widgets_mapstrations[widget_id].addEventListener('click', new_position);
        
        function new_position(point)
        {
            current_pos = point;
            
            jQuery('#' + input_data['latitude']['id']).attr('value',current_pos.lat);
            jQuery('#' + input_data['longitude']['id']).attr('value',current_pos.lon);
            
            set_marker('Current position','',widget_id);
            get_reversed_geodata();
        }

        indicator.hide();

        geodata_btn.bind('click', function(e){
            refresh_geodata();
            geodata_btn.hide();
            indicator.show();
        });
        
        function get_reversed_geodata()
        {
            var get_params = {
                service: backend_service,
                dir: 'reverse',
                latitude: current_pos.lat,
                longitude: current_pos.lon
            };
            
            jQuery.ajax({
                type: "GET",
                url: backend_url,
                data: get_params,
                dataType: "xml",
                error: function(request, type, expobj){
                    parse_error(request.responseText);
                },
                success: function(data){
                    var parsed = parse_response(data);
                    update_widget_inputs(parsed);
                }
            });
            
            function parse_error(error_string)
        	{
                indicator.hide();
                geodata_btn.show();
                
                status_box.html(error_string);
        	}

            function parse_response(data)
        	{
        	    status_box.html('');
        	    
                var results = [];
                jQuery('position',data).each(function(idx) {            
                    var rel_this = jQuery(this);

                    results[idx] = {
                        accuracy: rel_this.find("accuracy").text(),
                        city: rel_this.find("city").text(),
                        region: rel_this.find("region").text(),
                        country: rel_this.find("country").text(),
                        accuracy: rel_this.find("accuracy").text()
                    };
                });

            	return results[0];
        	}
        }
        
        function refresh_geodata()
        {
            var get_params = {
                service: backend_service
            };

            jQuery('.position_widget_input',widget).each(function(i, o){
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

            // jQuery.each(input_data, function(i,o){
            //     if (o['value'] != '')
            //     {
            //         get_params[i] = o['value'];
            //     }
            // });

            jQuery.ajax({
                type: "GET",
                url: backend_url,
                data: get_params,
                dataType: "xml",
                error: function(request, type, expobj){
                    parse_error(request.responseText);
                },
                success: function(data){
                    var parsed = parse_response(data);
                    update_widget(parsed);
                }
            });

            function parse_error(error_string)
        	{
                indicator.hide();
                geodata_btn.show();
                
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
            geodata_btn.show();

            update_widget_inputs(location_data);

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
            
            set_marker(label, info, widget_id);
        }
        
        function update_widget_inputs(location_data)
        {
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
        }

        function get_key_name(key)
        {
            //var re = /^\s*(\s*)_(\w{5,11})_(\s*)$/;
            var re = /widget_([a-z]*)_([a-z]{5,11})_([a-z]*)/;
            var reg = re.exec(key);
            // console.log("Reg: "+reg);
            // console.log("Reg.length: "+reg.length);
            // console.log("Reg[0]: "+reg[0]+" Reg[1]: "+reg[1]+" Reg[2]: "+reg[2]);
            
            return reg[3];
        }

    });
}

function init_current_pos(widget_id,lat,lon)
{
    current_pos = new LatLonPoint(lat,lon);
    set_marker('Current position','',widget_id);
}

function set_marker(label, info, widget_id)
{
    // console.log("set_marker label: "+label+" info: "+info+" widget_id: "+widget_id);
    
    if (position_marker != null)
    {
         widgets_mapstrations[widget_id].removeMarker(position_marker);
    }
    
    position_marker = new Marker(current_pos);
    
    if (label != undefined)
    {
        position_marker.setLabel(label);                
    }
    
    if (   info != undefined
        && info != '')
    {
        position_marker.setInfoBubble(info);
    }
    
    widgets_mapstrations[widget_id].addMarker(position_marker);

    // if (info != undefined)
    // {
    //     position_marker.openBubble();
    // }
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
