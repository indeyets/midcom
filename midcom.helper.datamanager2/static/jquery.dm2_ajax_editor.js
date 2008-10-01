(function($){
    
    $.dm2 = $.dm2 || {};
    
    $.dm2.ajax_editor = {
        debug_enabled: false,
        possible_states: ['view', 'edit', 'preview', 'save', 'cancel'],
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
        show_message: function(message, title, type) {
            if (typeof ($.midcom_services_uimessage_add) != 'function') {
                return;
            }
            
            if (typeof(type) == 'undefined') {
                var type = MIDCOM_SERVICES_UIMESSAGES_TYPE_OK;
            }
            if (typeof(title) == 'undefined') {
                var title = 'Datamanager';
            }
            
            $.midcom_services_uimessage_add({
                type: type,
                title: title,
                message: message
            });
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
        _change_state: function(new_state) {
            this.state.previous = this.state.current;
            this.state.current = new_state;
            
            this.state_changed();
        },
        _fields_to_form: function() {
            var self = this;
            
            this.form.set_state(this.state.current);
            
            $.each($('.'+this.identifier), function(i){
                var field = $(this);
                var id = field.attr('id');
                var name = id.replace(self.identifier+'_', '');
                
                var value = self._get_field_input_value(field);
                
                if (value !== null) {
                    self.form.set_value(name, value);
                }
            });
            
            this._disable_wysiwygs();
        },
        _get_field_input_value: function(field) {            
            var self = this;
            
            var id = field.attr('id');
            var name = id.replace(self.identifier+'_', '');
            
            var input_id = self.identifier + '_qf_' + name;
            
            var input = $('#'+input_id);
            if (! input) {
                return null;
            }
            
            var input_class = input.attr('class');
            
            var value = null;
            
            $.each($.dm2.ajax_editor.wysiwygs.configs, function(wysiwyg_name, config){
                if (typeof(config.className) == 'undefined') {
                    return;
                }
                if (typeof($.dm2.ajax_editor.wysiwygs[wysiwyg_name]) == 'undefined') {
                    return;
                }
                
                if (config.className == input_class) {
                    value = $.dm2.ajax_editor.wysiwygs[wysiwyg_name].get_value(field, input);
                }                    
            });
            
            if (value === null) {
                value = input.val();
            }
            
            return value;
        },
        _fields_from_form: function() {
            $.dm2.ajax_editor.debug("fields from form");
            
            var self = this;
            
            $.each($('.'+this.identifier), function(i){
                var field = $(this);
                var id = field.attr('id');
                var name = id.replace(self.identifier+'_', '');
                
                var value = self.form.get_value(name);

                var input_id = self.identifier + '_qf_' + name;
                
                var input = $('#'+input_id);
                
                if (! input) {
                    return;
                }
                
                var input_class = input.attr('class');
                
                switch(input_class)
                {
                    case 'tinymce':
                        input.val(value);
                    break;
                    case 'shorttext':
                        input.val(value);
                    break;
                }
            });            
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
                
                $(this).removeClass($.dm2.ajax_editor.generate_classname('editable_area'));
                $(this).removeClass($.dm2.ajax_editor.generate_classname('editable_area_hover'));
                $(this).removeClass($.dm2.ajax_editor.generate_classname('editing_area'));
                $(this).removeClass($.dm2.ajax_editor.generate_classname('preview_area'));
                
                field.unbind();
                if (self.state.current == 'view')
                {                    
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
                else if (self.state.current == 'preview')
                {                    
                    $(this).addClass($.dm2.ajax_editor.generate_classname('preview_area'));
                }
                else if (self.state.current == 'edit')
                {
                    $(this).addClass($.dm2.ajax_editor.generate_classname('editing_area'));
                }
                
                self.dimensions.fields[id] = self._calculate_dimensions(field);
                
                self.fields[id] = {
                    name: name,
                    elem: field
                };
            });
            
            if (this.first_field_id !== null) {
                this._build_toolbar();
            }
            
            if (self.state.previous == 'preview') {
                this._fields_from_form();
            }
            if (self.state.current == 'edit') {
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
            } else {
                send_data[self.identifier+'_'+this.state.current] = 1;
            }
            
            $.ajax({
                global: false,
                type: "GET",
                url: location.href,                
                dataType: "xml",
                data: send_data,
                success: function(data) {                    
                    self._parse_response(data);
                },
                error: function(xhr,err,e){
                    $.dm2.ajax_editor.debug("Error loading fields!");
                    $.dm2.ajax_editor.debug(err);
                }
            });
        },
        _parse_response: function(data) {
            this.parsed_data = {
                identifier: $('form',data).attr('id'),
                new_identifier: $('form',data).attr('new_identifier'),
                is_editable: $('form',data).attr('editable'),
                exit_code: $('form',data).attr('exitcode'),
            };
            
            this.errors = $('form',data).find('error');
            
            if (this.errors.length > 0) {
                console.log("errors: ");
                console.log(this.errors);
                this._change_state('edit');
            } else {
                if (this.parsed_data.exit_code == 'save') {
                    $.dm2.ajax_editor.show_message('Form saved successfully');
                }
            }
            
            var xml_fields = $('form',data).find('field');

            var self = this;
            $.each(xml_fields, function(){
                var field = $(this);
                var name = field.attr('name');
                var content = field.text();
                
                self.form_fields[name] = content;
            });
            
            // $.dm2.ajax_editor.debug("this.fields:");
            // $.dm2.ajax_editor.debug(this.fields);
            // $.dm2.ajax_editor.debug("this.form_fields:");
            // $.dm2.ajax_editor.debug(this.form_fields);
            
            this.results_parsed();
            this._prepare_fields();
        },
        _enable_wysiwygs: function() {
            $.dm2.ajax_editor.debug("Enable wysiwygs");
            
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
        _disable_wysiwygs: function() {
            $.dm2.ajax_editor.debug("Disable wysiwygs");
            
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
                        $.dm2.ajax_editor.wysiwygs[wysiwyg_name].disable($(self.form_fields[field.name]));
                    }                    
                });
            });
        },
        _build_toolbar: function() {
            this.buttons = {};
            
            var self = this;
            
            if (this.state.current == 'preview') {
                this.buttons['edit'] = {
                    name: this.identifier + '_edit',
                    value: this.config.edit_btn_value,
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
            
            if (   this.state.current == 'edit'
                || this.state.current == 'preview')
            {
                this.buttons['save'] = {
                    name: this.identifier + '_save',
                    value: this.config.save_btn_value,
                    elem: null
                };

                this.buttons['cancel'] = {
                    name: this.identifier + '_cancel',
                    value: this.config.cancel_btn_value,
                    elem: null
                };
            }
            
            this.render_toolbar();
        },
        _execute_action: function(action, event) {
            $.dm2.ajax_editor.debug("execute action "+action);
            
            this._change_state(action);
            
            switch(action)
            {
                case 'edit':
                    $.dm2.ajax_editor.debug("edit form");
                    this._disable_wysiwygs();
                    this._fetch_fields();
                break;
                case 'preview':
                    $.dm2.ajax_editor.debug("generate preview");
                    this._fields_to_form();
                    this.form.submit('preview');
                break;
                case 'save':
                    $.dm2.ajax_editor.debug("save form");
                    
                    if (this.state.previous == 'preview') {
                        $.dm2.ajax_editor.debug("from preview");
                        this.form.set_state(this.state.current);
                    } else {
                        this._fields_to_form();
                    }
                    
                    this.form.submit();
                break;
                case 'cancel':
                default:                
                    $.dm2.ajax_editor.debug("cancel editing");
                    this._disable_wysiwygs();
                    this._fetch_fields();
                    this._change_state('view');
            }
        },
        
        state_changed: function() {},
        render_toolbar: function() {},
        results_parsed: function() {},
        initialize: function() {}
    });
    
    $.dm2.ajax_editor.inline = $.extend({}, $.dm2.ajax_editor.base, {
        className: 'inline',        
        initialize: function() {
            $.dm2.ajax_editor.debug("Initialize inline editor!");
        },
        results_parsed: function() {            
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
            
            if (this.state.current != 'edit')
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
            
            if (!this.toolbar) {
                this.toolbar = $('<div />').attr({
                    className: toolbar_class
                }).hide();
                
                this.toolbar.insertBefore(this.fields[this.first_field_id].elem);
            }
            
            this.toolbar.html('');
            
            var self = this;
            
            $.each(this.buttons, function(action_name, button){
                var element = $('<input type="submit" name="' + button.name + '" value="' + button.value + '" />');
                element.appendTo(self.toolbar);
                
                element.bind('click', function(e){
                    self._execute_action(action_name, e);
                });
                
                self.buttons[action_name].elem = element;
            });
            
            if (   this.state.current != 'view'
                && this.state.current != 'cancel')
            {
                this.toolbar.show();
            }
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
            
            var self = this;
            $.each($.dm2.ajax_editor.possible_states, function(i,state){
                if (typeof(self.values[editor.identifier + '_' + state]) != 'undefined') {
                    delete self.values[editor.identifier + '_' + state];
                }
                if (typeof(self.values['midcom_helper_datamanager2_' + state]) != 'undefined') {
                    delete self.values['midcom_helper_datamanager2_' + state];
                }
            });
            
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
                    editor._parse_response(data);
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
            $.dm2.ajax_editor.debug("Enable tinymce for "+id);
            
            //var field_dimensions = this.dimensions.fields[id];
            tinyMCE.execCommand('mceAddControl', false, id);
        },
        disable: function(field) {
            var id = field.attr('id');
            $.dm2.ajax_editor.debug("Disable tinymce for "+id);
            
            if (tinyMCE.get(id)) {
                tinyMCE.execCommand('mceRemoveControl', true, id);
            }
        },
        get_value: function(field, input) {
            if (typeof(input) == 'undefined') {
                var id = field.attr('id');
                var name = id.replace(self.identifier+'_', '');
                
                var input_id = self.identifier + '_qf_' + name;
                
                var input = $('#'+input_id);
                if (! input) {
                    return '';
                }
            }
            
            tinyMCE.triggerSave(true, true);
            return input.val();
        }
    });
    
})(jQuery);
