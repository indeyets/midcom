(function($){
    
    $.dm2 = $.dm2 || {};
    
    $.dm2.ajax_editor = {
        debug_enabled: true,
        class_prefix: 'dm2_ajax_editor',
        config: {
            mode: 'inline',
            allow_creation: true,
            save_btn_value: 'Save',
            edit_btn_value: 'Edit',
            cancel_btn_value: 'Cancel',
            preview_btn_value: 'Preview'
        },
        instances: null
    };
    $.extend($.dm2.ajax_editor, {
        init: function(identifier, config) {
            
            var config = $.extend({}, $.dm2.ajax_editor.config, config || {});
            
            $.dm2.ajax_editor.create_instance(identifier, config);
        },
        create_instance: function(identifier, config)  {
            if ($.dm2.ajax_editor.instances === null) {
                $.dm2.ajax_editor.instances = {};
            }
            
            if (typeof($.dm2.ajax_editor[config.mode]) != 'undefined') {
                $.dm2.ajax_editor.instances[identifier] = $.dm2.ajax_editor[config.mode].init(identifier, config);
            } else {
                $.dm2.ajax_editor.instances[identifier] = $.dm2.ajax_editor.inline.init(identifier, config);
            }
        },
        get_instance: function(identifier) {
            if (typeof($.dm2.ajax_editor.instances[identifier]) != 'undefined') {
                return $.dm2.ajax_editor.instances[identifier];
            } else {
                return {};
            }
        },
        set_class_prefix: function(prefix) {
            if (typeof(prefix) != 'undefined') {
                $.dm2.ajax_editor.class_prefix = prefix;
            }
        },
        generate_classname: function(suffix) {
            return $.dm2.ajax_editor.class_prefix + '_' + suffix;
        },
        debug: function(msg) {
            if ($.dm2.ajax_editor.debug_enabled) {
                console.log(msg);
            }
        }
    });
    
    $.dm2.ajax_editor.base = {
        config: {            
            allow_creation: true,
            render_target_only: false,
            save_btn_value: 'Save',
            edit_btn_value: 'Edit',
            cancel_btn_value: 'Cancel',
            preview_btn_value: 'Preview'
        },
        identifier: 'midcom_helper_datamanager2_controller_ajax',
        className: 'base',
        state: {
            current: 'view',
            previous: 'view'
        },
        in_creation_mode: false,
        toolbar: null,
        buttons: null,
        form: null,
        fields: null,
        first_field_id: null,
        last_field_id: null,
        form_fields: null,
        dimensions: {
            fields: null,
            form: null
        },
        parsed_data: null,
        errors: null
    };
    $.extend($.dm2.ajax_editor.base, {
        init: function(identifier, config) {
            if (typeof(identifier) != 'undefined') {
                this.identifier = identifier;
            }
            this.config = $.extend({}, $.dm2.ajax_editor.base.config, config || {});
            
            this.fields = {};
            this.form_fields = {};

            this._prepare_fields();
            this.form = $.dm2.ajax_editor.form.init(this.identifier, this.fields);
                        
            this.initialize();
            
            return this;
        },
        _fields_to_form: function() {
            var self = this;
            
            this.form.set_state(this.state.current);
            
            $.each($('.'+this.identifier), function(i){
                var field = $(this);
                var id = field.attr('id');
                var name = id.replace(self.identifier+'_', '');
                
                var input_id = self.identifier + '_qf_' + name;
                
                var input = $('#'+input_id);
                if (! input) {
                    return;
                }
                
                var input_class = input.attr('class');
                
                var value = '';
                
                switch(input_class)
                {
                    case 'tinymce':
                        tinyMCE.triggerSave(true,true);
                        value = input.val();
                    break;
                    case 'shorttext':
                        value = input.val();
                    break;
                }
                
                self.form.set_value(name, value);
            });
        },
        _fields_from_form: function() {
            
        },
        _prepare_fields: function() {
            
            if (this.dimensions.fields === null) {
                this.dimensions.fields = {};
            }
            if (this.dimensions.form === null) {
                this.dimensions.form = {};
            }
            
            var self = this;
            $.each($('.'+this.identifier), function(i){
                var field = $(this);
                var id = field.attr('id');
                var name = id.replace(self.identifier+'_', '');
                
                if (i == 0) {
                    self.first_field_id = id;
                }
                self.last_field_id = id;
                
                field.unbind();
                if (   self.state.current == 'view'
                    || self.state.current == 'preview')
                {
                    $(this).removeClass($.dm2.ajax_editor.generate_classname('editing_area'));
                    $(this).addClass($.dm2.ajax_editor.generate_classname('editable_area'));
                
                    var hover_class = $.dm2.ajax_editor.generate_classname('editable_area_hover');
                    field.bind('mouseover', function(){
                        $(this).addClass(hover_class);
                    }).bind('mouseout', function(){
                        $(this).removeClass(hover_class);
                    }).dblclick(function(){
                        self._fetch_fields(true);
                    });                    
                }
                else if (self.state.current == 'edit')
                {
                    $(this).removeClass($.dm2.ajax_editor.generate_classname('editable_area'));
                    $(this).addClass($.dm2.ajax_editor.generate_classname('editing_area'));
                }
                
                self.dimensions.fields[id] = self._calculate_dimensions(field);
                
                self.fields[id] = {
                    name: name,
                    elem: field
                };
            });
            
            if (self.state.current == 'edit') {
                this._build_toolbar();
                this._enable_wysiwygs();
            }
        },
        _calculate_dimensions: function(field) {
            var position = field.position();
            var dimensions = {
                width: field.width(),
                height: field.height(),
                top: position.top,
                left: position.left
            };
            
            return dimensions;
        },
        _fetch_fields: function(edit_mode) {
            $.dm2.ajax_editor.debug("fetch fields for "+this.identifier);
            
            if (typeof(edit_mode) == 'undefined') {
                var edit_mode = false;
            }
            
            var self = this;
            
            var send_data = {};
            
            if (edit_mode) {
                if (this.state.current == 'edit') {
                    return;
                }
                this.state.current = 'edit';
                
                send_data[self.identifier+'_edit'] = 1;
            }
            
            $.ajax({
                global: false,
                type: "GET",
                url: location.href,                
                dataType: "xml",
                data: send_data,
                success: function(data) {                    
                    self._parse_fetch_fields_response(data);
                },
                error: function(xhr,err,e){
                    $.dm2.ajax_editor.debug("Error loading fields!");
                    $.dm2.ajax_editor.debug(err);
                }
            });
        },
        _parse_fetch_fields_response: function(data) {
            this.parsed_data = {
                identifier: $('form',data).attr('id'),
                new_identifier: $('form',data).attr('new_identifier'),
                is_editable: $('form',data).attr('editable'),
                exit_status: $('form',data).attr('exitstatus'),
            };
            
            this.errors = $('form',data).find('error');
            
            if (this.errors.length > 0) {
                console.log("errors: ");
                console.log(this.errors);
                return;
            }
            
            var xml_fields = $('form',data).find('field');

            var self = this;
            $.each(xml_fields, function(){
                var field = $(this);
                var name = field.attr('name');
                var content = field.text();
                
                self.form_fields[name] = content;
            });
            
            $.dm2.ajax_editor.debug(this.fields);
            $.dm2.ajax_editor.debug(this.form_fields);
            
            this.fetch_fields_parsed();
            this._prepare_fields();
        },
        _enable_wysiwygs: function() {
            var self = this;
            
            $.each(this.fields, function(i, field){                
                $.each($.dm2.ajax_editor.wysiwygs.configs, function(wysiwyg_name, config){
                    if (typeof(config.className) == 'undefined') {
                        return;
                    }
                    if (typeof($.dm2.ajax_editor.wysiwygs[wysiwyg_name]) == 'undefined') {
                        return;
                    }
                    
                    if ($(self.form_fields[field.name]).hasClass(config.className)) {
                        $.dm2.ajax_editor.wysiwygs[wysiwyg_name].enable($(self.form_fields[field.name]));
                    }                    
                });
            });
        },
        _build_toolbar: function() {
            this.buttons = {};
            
            var self = this;
            
            if (this.state.current == 'view') {
                this.buttons['edit'] = {
                    name: this.identifier + '_edit',
                    value: this.config.edit_btn_value,
                    elem: null
                };
            }

            if (   this.state.current == 'edit'
                || this.state.current == 'preview')
            {
                this.buttons['save'] = {
                    name: this.identifier + '_save',
                    value: this.config.save_btn_value,
                    elem: null
                };
            }
            
            if (this.state.current == 'edit') {
                this.buttons['preview'] = {
                    name: this.identifier + '_preview',
                    value: this.config.preview_btn_value,
                    elem: null
                };
            }
            
            this.buttons['cancel'] = {
                name: this.identifier + '_cancel',
                value: this.config.cancel_btn_value,
                elem: null
            };
            
            this.render_toolbar();
        },
        _execute_action: function(action, event) {
            $.dm2.ajax_editor.debug("execute action "+action);
            
            this.state.current = action;
            
            switch(action)
            {
                case 'edit':
                    $.dm2.ajax_editor.debug("edit form");
                break;
                case 'preview':
                    $.dm2.ajax_editor.debug("generate preview");
                break;
                case 'save':
                    this._fields_to_form();
                    this.form.submit();
                break;
                case 'cancel':
                default:
                    $.dm2.ajax_editor.debug("cancel editing");
            }
        },

        render_toolbar: function() {},
        fetch_fields_parsed: function() {},
        initialize: function() {}
    });
    
    $.dm2.ajax_editor.inline = $.extend({}, $.dm2.ajax_editor.base, {
        className: 'inline',
        
        initialize: function() {
            $.dm2.ajax_editor.debug("Initialize inline editor!");
            //$.dm2.ajax_editor.generate_classname(this.className);
            
            //$.dm2.ajax_editor.debug(this.dimensions);
        },
        fetch_fields_parsed: function() {            
            var self = this;
            
            if (   this.parsed_data.is_editable
                && this.state.current == 'edit')
            {                
                var unreplaced_fields = [];
                $.each(this.fields, function(i, field){
                    if (typeof(self.form_fields[field.name]) == 'undefined') {
                        unreplaced_fields.push(field.name);
                        return;
                    }
                    
                    field.elem.html(self.form_fields[field.name]);
                });
            }
            
            if (   this.state.current == 'view'
                || this.state.current == 'preview')
            {
                var unreplaced_fields = [];
                $.each(this.fields, function(i, field){
                    if (typeof(self.form_fields[field.name]) == 'undefined') {
                        unreplaced_fields.push(field.name);
                        return;
                    }
                    
                    field.elem.html(self.form_fields[field.name]);
                });
            }
        },
        render_toolbar: function() {
            var toolbar_class = $.dm2.ajax_editor.generate_classname('toolbar');
            
            this.toolbar = $('<div />').attr({
                className: toolbar_class
            });
            
            var self = this;
            
            $.each(this.buttons, function(action_name, button){
                var element = $('<input type="submit" name="' + button.name + '" value="' + button.value + '" />');
                element.appendTo(self.toolbar);
                
                element.bind('click', function(e){
                    self._execute_action(action_name, e);
                });
                
                self.buttons[action_name].elem = element;
            });
            
            this.toolbar.insertBefore(this.fields[this.first_field_id].elem);
        }
        
    });
    
    $.dm2.ajax_editor.form = {
        identifier: null,
        fields: null,
        values: null
    };
    $.extend($.dm2.ajax_editor.form, {
        init: function(identifier, fields) {
            this.identifier = identifier;
            this.fields = fields || {};
            this.values = {};
            
            this._set_defaults();
            
            return this;
        },
        _set_defaults: function() {
            this.values['_qf__' + this.identifier + '_qf'] = 1;
        },
        set_state: function(state) {
            var editor = $.dm2.ajax_editor.get_instance(this.identifier);
            
            if (editor.state.current != state)
            {
                delete this.formValues[editor.identifier + '_' + editor.state.current];
                delete this.formValues['midcom_helper_datamanager2_' + editor.state.current];
            }
            
            this.values[editor.identifier + '_' + state] = 1;
            this.values['midcom_helper_datamanager2_' + state] = 1;
        },
        set_value: function(field_name, value) {            
            //console.log("set value for "+field_name+": "+value);
            
            //this.values['midcom_helper_datamanager2_' + field_name] = value;
            this.values[field_name] = value;
        },
        get_value: function(field_name) {
            return this.values[field_name];
            //return this.values['midcom_helper_datamanager2_' + field_name];
        },
        submit: function(next_state) {
            if (typeof(next_state) == 'undefined') {
                var next_state = 'view';
            }
            
            var self = this;
            var editor = $.dm2.ajax_editor.get_instance(this.identifier);
            $.ajax({
                global: false,
                type: 'POST',
                url: location.href,
                dataType: 'xml',
                contentType: 'application/x-www-form-urlencoded',
                data: this.values,
                success: function(data) {
                    editor.state.previous = editor.state.current;
                    editor.state.current = next_state;
                    editor._parse_fetch_fields_response(data);
                },
                error: function(xhr, err, e) {
                    $.dm2.ajax_editor.debug("Error saving form!");
                    $.dm2.ajax_editor.debug(err);
                }
            });
        }
    });
    
    $.dm2.ajax_editor.wysiwygs = {
        configs: {
            tinymce: {
                className: 'tinymce'
            }
        }
    };

    $.dm2.ajax_editor.wysiwygs.tinymce = {};
    $.extend($.dm2.ajax_editor.wysiwygs.tinymce, {
        enable: function(field) {
            var id = field.attr('id');
            //var field_dimensions = this.dimensions.fields[id];
            
            tinyMCE.execCommand('mceAddControl',false, id);
        },
        disable: function(field) {
            var id = field.attr('id');
            
            if (tinyMCE.get(id)) {
                tinyMCE.execCommand('mceRemoveControl', true, id);
            }
        }
    });
    
    $.dm2.helpers = $.dm2.helpers || {};
    
    $.dm2.helpers.events = {};
    $.dm2.helpers.events.signals = {
        _listeners: null,
        trigger: function(signal, data) {   
            if (   $.dm2.helpers.events.signals._listeners === null
                || typeof $.dm2.helpers.events.signals._listeners[signal] == 'undefined')
            {
                return;
            }
            
            if (   typeof data == 'undefined'
                || typeof data != 'object')
            {
                var data = [];
            }
            
            $.each($.dm2.helpers.events.signals._listeners[signal], function(i,listener){
                if (typeof listener != 'object') {
                    return;
                }
                
                if (typeof listener.func == 'function') {
                    var args = data;
                    if (   typeof listener.args != 'undefined'
                        && listener.args != null)
                    {
                        $.each(listener.args, function(i,a){
                            args.push(a);
                        });                        
                    }
                    
                    listener.func.apply(listener.func, args);
                    
                    if (! listener.keep) {
                        $.dm2.helpers.events.signals._listeners[signal][i] = null;
                    }
                }
            });
        },
        listen: function(signal, listener, data, keep) {
            if ($.dm2.helpers.events.signals._listeners === null) {
                $.dm2.helpers.events.signals._listeners = {};
            }
            if (typeof $.dm2.helpers.events.signals._listeners[signal] == 'undefined') {
                $.dm2.helpers.events.signals._listeners[signal] = [];
            }
            
            if (typeof keep == 'undefined') {
                var keep = true;
            }
            
            var lstnr = {
                func: listener,
                args: data,
                keep: keep
            };
            
            $.dm2.helpers.events.signals._listeners[signal].push(lstnr);
        }
    };
    
})(jQuery);