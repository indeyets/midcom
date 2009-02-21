# -*- coding: utf-8 -*-
import _midgard as midgard
import dbus.mainloop.glib
import gobject
import configuration
# import midcom_cache
import sys
conf = configuration.configuration()

conf.load_component_configuration("midcom_core")

print conf.get('authentication_configuration', 'fallback_translation')
