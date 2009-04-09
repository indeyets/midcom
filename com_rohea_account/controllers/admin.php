<?php
/**
 * @package com_rohea_mjumpaccount
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Basic controller admin
 *
 * @package com_rohea_mjumpaccount
 */
class com_rohea_account_controllers_admin
{


    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }

    public function action_admin($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_admin();
        
        $data['name'] = "com_rohea_account";
        
        $qb = new midgard_query_builder('midgard_person');
        $persons = $qb->execute();
        $registered_persons = array();
        foreach($persons as $p)
        {
            $p->remove_url = $_MIDCOM->dispatcher->generate_url('admin_remove', array('username' => $p->username), $_MIDCOM->context->page);
            if ($p->username != $p->guid)
            {
                $registered_persons[] = $p;
            }        
        }
        $data['registered_persons'] = $registered_persons;
    }
    
    private function get_linked_classes()
    {
        static $linked = array();
        if (!empty($linked))
        {
            // Already built
            return $linked;
        }

        foreach ($_MIDGARD['schema']['types'] as $classname => $null)
        {
            $reflector = new midgard_reflection_property($classname);
            $dummy = new $classname();
            $properties = get_object_vars($dummy);
            foreach ($properties as $property => $default)
            {
                if (!$reflector->is_link($property))
                {
                    continue;
                }
                
                $link_type = $reflector->get_midgard_type($property);
                if ($link_type == MGD_TYPE_UINT)
                {
                    $target_property = 'id';
                }
                elseif ($link_type == MGD_TYPE_GUID)
                {
                    $target_property = 'guid';
                }
                else
                {
                    // Don't deal with the other types of links for now
                    continue;
                }
                
                $target_class = $reflector->get_link_name($property);
    
                if (!isset($linked[$target_class]))
                {
                    $linked[$target_class] = array();
                }
                if (!isset($linked[$target_class][$target_property]))
                {
                    $linked[$target_class][$target_property] = array();
                }

                $linked[$target_class][$target_property][] = array
                (
                    'class' => $classname,
                    'property' => $property,
                );
            }
        }
        return $linked;
    }
    
    private function get_dependencies_for($object, $count_only = false)
    {
        $classname = get_class($object);
        $linked = $this->get_linked_classes();
        if (!isset($linked[$classname]))
        {
            // Nothing links to this class
            return array();
        }
        
        $dependencies = array();
        
        foreach ($linked[$classname] as $property => $links)
        {
            if (!$object->$property)
            {
                continue;
            }
            
            foreach ($links as $link)
            {
                $qb = new midgard_query_builder($link['class']);
                $qb->add_constraint($link['property'], '=', $object->$property);
                
                if ($count_only)
                {
                    $deps = $qb->count();
                    if ($deps > 0)
                    {
                        $dependencies[] = array
                        (
                            'class' => $link['class'],
                            'property' => $link['property'],
                            'dependencies' => $deps,
                            'actionname' => "action[{$link['class']}]",
                        );
                    }
                    continue;
                }
                
                $deps = $qb->execute();
                if (count($deps) > 0)
                {
                    $dependencies[] = array
                    (
                        'class' => $link['class'],
                        'property' => $link['property'],
                        'dependencies' => $deps,
                        'actionname' => "action[{$link['class']}]",
                    );
                }
            }
        }
        return $dependencies;
    }
    
    private function get_user_by_username($username)
    {
        $qb = new midgard_query_builder('midgard_person');
        $qb->add_constraint('username', '=', $username);
        $users = $qb->execute();
        if (count($users) == 0)
        {
            throw new midcom_exception_notfound("User {$username} not found.");
        }
        return $users[0];
    }
    
    public function action_remove($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_admin();
        $data['person'] = $this->get_user_by_username($args['username']);
        
        if (   isset($_POST['action'])
            && is_array($_POST['action']))
        {
            $assign_to = $this->get_user_by_username($_POST['assign_to_username']);
            $dependencies = $this->get_dependencies_for($data['person']);
            $linked = $this->get_linked_classes();
            foreach ($dependencies as $dependency)
            {
                if (!isset($_POST['action'][$dependency['class']]))
                {
                    // No action defined for these deps, skip
                    continue;
                }
                $class_action = $_POST['action'][$dependency['class']];

                foreach ($dependency['dependencies'] as $dep)
                {
                    switch ($class_action)
                    {
                        case 'delete':
                            // TODO: Subdeps
                            $dep->delete();
                            break;
                        case 'assign':
                            foreach ($linked[get_class($data['person'])] as $property => $links)
                            {
                                foreach ($links as $link)
                                {
                                    if ($link['class'] != $dependency['class'])
                                    {
                                        continue;
                                    }
                                    
                                    $linked_property = $link['property'];
                                    $dep->$linked_property = $assign_to->$property;
                                    $dep->update();
                                }
                            }
                            break;
                    }
                }
            }

            // Check that all dependencies are handled
            $data['dependency_counts'] = $this->get_dependencies_for($data['person'], true);
            if (!empty($data['dependency_counts']))
            {
                // Go back to view, some objects remain unhandled
                return;
            }
            
            $data['person']->delete();
            header('Location: ' . $_MIDCOM->dispatcher->generate_url('admin', array(), $_MIDCOM->context->page));
            die();
        }
        
        $data['dependency_counts'] = $this->get_dependencies_for($data['person'], true);
    }
}
?>
