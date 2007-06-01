<?php
/**
 * @package net.fernmark.pedigree
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 4505 2006-10-29 15:53:49Z tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Helper function returns widget_config array for universalchoocer to look for dogs
 */
function net_fernmark_pedigree_widget_universalchooser_config_dog($require_sex = false, $idfield = 'id', $add_none = true, $none_label = 'unknown', $none_value = 0)
{
    $ret = array
    (
        'class' => 'net_fernmark_pedigree_dog_dba',
        'component' => 'net.fernmark.pedigree',
        'idfield' => $idfield,
        'titlefield' => 'name_with_kennel',
        'searchfields' => array
        (
            'name',
            'regno',
            /* I don't think we want to search by these
            'kennel.official',
            'breeder.firstname',
            'breeder.lastname',
            */
        ),
        'auto_wildcards' => 'both',
        'constraints' => array
        (
            array
            (
                'field' => 'sitegroup',
                'op'    => '=',
                'value' => $_MIDGARD['sitegroup'],
            ),
        ),
        'orders' => array(array('name' => 'ASC')),
        'allow_create' => true, // we need to make a handler though!
    );
    /* for the weirdest reason QB get this value as 0 (on universalchooser_handler it's still 10)
    if ($require_sex)
    {
        $ret['constraints'][] = array
        (
            'field' => 'sex',
            'op'    => '=',
            'value' => (int)$require_sex,
        );
    }
    */
    if ($add_none)
    {
        $ret['static_options'] = array
        (
            $none_value => $none_label,
        );
    }
    return $ret;
}

/**
 * Helper function returns widget_config array for universalchoocer to look for persons
 */
function net_fernmark_pedigree_widget_universalchooser_config_person($idfield = 'id', $add_none = true, $none_label = 'unknown', $none_value = 0)
{
    $ret = array
    (
        'class' => 'org_openpsa_contacts_person',
        'component' => 'org.openpsa.contacts',
        'idfield' => $idfield,
        'titlefield' => 'name',
        'searchfields' => array
        (
            'firstname',
            'lastname',
            'email',
        ),
        'auto_wildcards' => 'both',
        'constraints' => array
        (
            array
            (
                'field' => 'sitegroup',
                'op'    => '=',
                'value' => $_MIDGARD['sitegroup'],
            ),
        ),
        'orders' => array
        (
            array('lastname' => 'ASC'),
            array('firstname' => 'ASC'),
        ),
        'allow_create' => true, // we need to make a handler though!
    );
    if ($add_none)
    {
        $ret['static_options'] = array
        (
            $none_value => $none_label,
        );
    }
    return $ret;
}

/**
 * Helper function returns widget_config array for universalchoocer to look for groups
 */
function net_fernmark_pedigree_widget_universalchooser_config_group($idfield = 'id', $add_none = true, $none_label = 'unknown', $none_value = 0)
{
    $ret = array
    (
        'class' => 'org_openpsa_contacts_group',
        'component' => 'org.openpsa.contacts',
        'idfield' => $idfield,
        'titlefield' => 'official',
        'searchfields' => array
        (
            'official',
            'name',
        ),
        'auto_wildcards' => 'both',
        'constraints' => array
        (
            array
            (
                'field' => 'sitegroup',
                'op'    => '=',
                'value' => $_MIDGARD['sitegroup'],
            ),
        ),
        'orders' => array
        (
            array('official' => 'ASC'),
            array('name' => 'ASC'),
        ),
        'allow_create' => true, // we need to make a handler though!
    );
    if ($add_none)
    {
        $ret['static_options'] = array
        (
            $none_value => $none_label,
        );
    }
    return $ret;
}

/**
 * Helper to return good type_config array for type_select when using universalchooser
 */
function net_fernmark_pedigree_widget_universalchooser_type_config($none_label = 'unknown', $none_value = 0)
{
    $ret = array
    (
        'options' => array
        (
            $none_value => $none_label,
        ),
        'require_corresponding_option' => false,
        'allow_other' => true,
    );
    return $ret;
}

/**
 * Returns the symbol for the sex of a dog object
 */
function net_fernmark_pedigree_dog_sex_symbol(&$dog)
{
    switch($dog->sex)
    {
        case NET_FERMARK_PEDIGREE_SEX_MALE:
            $sex_symbol = "♂";
            break;
        case NET_FERMARK_PEDIGREE_SEX_FEMALE:
            $sex_symbol = "♀";
            break;
        default:
            $sex_symbol = '';
    }
    return $sex_symbol;
}

?>