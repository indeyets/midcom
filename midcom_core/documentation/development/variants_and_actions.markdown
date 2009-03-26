Variants and actions
====================

In MidCOM3 we're introducing a new concept of _variants_ into Midgard terminology. Variants are different views to same content object, determined based on URL request. Some examples:

* `articlename.html`: Full article as HTML
* `articlename.xml`: Full article as XML (Replicator serialization)
* `articlename.content.html`: Content field of article as HTML

The variants should be served as independent views to the content, and should support all the normal interaction that a content object has. For instance, if user has permission to update the article, he should be able to upload new article contents to the `articlename.content.html` URL as HTTP PUT.

## Actions

An object, or a variant of an object should be able to have multiple actions assigned for it. The actions are a combination of:

* Route to the action (dispatcher `generate_url` with needed arguments)
* HTTP method
* Possible POST or GET arguments to include
* Label for the action
* Icon for the action

Actions available for a given object or variant are determined by introspection. Any installed component can declare actions to provide to an object. For example, a _content versioning_ component could provide actions like _browse revisions_ and _restore revision_.

Component should list what classes it provides actions for in component manifest. Asterisk (`*`) can be used if actions apply for any  class:

    action_types:
        - midgard_page

Example of a `get_object_actions` method for a component interface class:

    public function get_object_actions(&$object, $variant = null)
    {
        $actions = array();
        if (!$_MIDCOM->authorization->can_do('midgard:update', $object))
        {
            // User is not allowed to edit so we have no actions available
            return $actions;
        }
        
        $actions['edit'] = array
        (
            'url' => $_MIDCOM->dispatcher->generate_url('page_edit', array('name' => $object->name)),
            'method' => 'GET',
            'label' => $_MIDCOM->i18n->get('key: edit', 'midcom_core'),
            'icon' => 'midcom_core/stock_icons/16x16/edit.png',
        );
    }

In general, URLs for actions should contain the `mgd:` prefix (`objectname/mgd:edit/` for example).