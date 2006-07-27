<?php

/**
 * The callback class used for different callbacks from the datamanager2 form
 * used by midcom.admin.content2_config.
 * @package midcom.admin.content2
 * */

class midcom_admin_content2_callbacks_styleselector  {

    /**
     * Small cach of the styles
     * @var array
     * @access private
     */
    var $_styles = null;
    var $_styles_reverse = null;

    function midcom_admin_content2_callbacks_styleselector() {
        //
        //$this->_list_all_styles();
    }

    function set_type(&$type) {}

    function get_name_for_key($key)
    {

        // populate the stylelist. We'll need it later anyhow.'
        if ($this->_styles === null) {
            $this->_list_all_styles();
        }
        return ;
    }

    function key_exists($key)
    {
        // populate the stylelist. We'll need it later anyhow.'
        if ($this->_styles === null) {
            $this->_list_all_styles();
        }
        return array_key_exists($key, $this->_styles_reverse);

    }

    function _style_exists($id )
    {
        $qb = new midcom_core_querybuilder('midcom_db_style');
        $qb->set_limit(1);
        $qb->add_constraint('id', '=', $id);
        //$res = $qb->execute();

        return ($qb->count() > 0 );
    }

    function list_all() {
        if ($this->_styles == null) {
            $this->_list_all_styles();
        }
        return $this->_styles;
    }
    /**
     * This callback lists all the styles available to the user
     * according to normal midgard access rules
     * (note: does not use midcom new_qb() as I didn't get it to work)
     * @return void This function populates the _styles and styles_reverse arrays.
     *  array id => stylepath list of styles by/ id.
     * @access private
     */
    function _list_all_styles() {

        //$qb =  midcom_db_style::new_query_builder();
        $qb = new MidgardQueryBuilder('midgard_style');
        /*
        $qb->begin_group('OR');
        if ($_MIDGARD['sitegroup'] != 0) {
            $qb->add_constraint('sitegroup', '=',$_MIDGARD['sitegroup']);
        }
        $qb->add_constraint('sitegroup', '=', 0);
        $qb->end_group();
        */
        /** @todo: add_order('sitegroup') makes errors now
         * hopefully that will go away some time
         */
        //$qb->add_order('sitegroup', 'ASC');
        $qb->add_order('up');


        $result = @$qb->execute();
        // nonrecursive implementation, yay!

        $this->_styles = array
        (
            '' => '   ' . $_MIDCOM->i18n->get_string('default setting', 'midcom'),
        );

        foreach ($result as $key => $obj)
        {
            if ($obj->up == 0 )
            {
                $this->_styles[$obj->id] = '/' . $obj->name;
                $this->_styles_reverse['/' . $obj->name] = $obj->id;
            } else {
                $this->_styles[$obj->id] = $this->_styles[$obj->up] .  '/' . $obj->name;
                $this->_styles_reverse[$this->_styles[$obj->up] .  '/' . $obj->name] = $obj->id;
            }
        }
        asort($this->_styles);

    }

}