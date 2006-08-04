<?php
/**
 *  Mileages are just a special case of expense, in fact they might not
 *  need their own object at all...
 */
class org_openpsa_projects_mileage extends org_openpsa_projects_expense
{
    function org_openpsa_projects_mileage($identifier=NULL)
    {
        parent::org_openpsa_projects_expense($identifier);
    }
}
?>