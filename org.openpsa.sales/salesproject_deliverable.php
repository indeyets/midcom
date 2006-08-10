<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
 
class org_openpsa_sales_salesproject_deliverable extends __org_openpsa_sales_salesproject_deliverable
{
    function org_openpsa_sales_salesproject_deliverable($id = null)
    {
        return parent::__org_openpsa_sales_salesproject_deliverable($id);
    }
    
    function get_parent_guid_uncached()
    {
        if ($this->up != 0)
        {
            $parent = new org_openpsa_sales_salesproject_deliverable($this->up);            
            return $parent;
        }
        elseif ($this->salesproject != 0)
        {
            $parent = new org_openpsa_sales_salesproject($this->salesproject);            
            return $parent;
        }
        else
        {
            return null;
        }
    }

    function _on_creating()
    {
        $this->calculate_price(false);
        return parent::_on_creating();
    }
    
    function _on_updating()
    {
        $this->calculate_price();
        return parent::_on_updating();
    }
    
    function calculate_price($update = true)
    {
        if ($this->id)
        {
            $pricePerUnit = 0;
            $deliverable_qb = org_openpsa_sales_salesproject_deliverable::new_query_builder();
            $deliverable_qb->add_constraint('salesproject', '=', $this->salesproject);
            $deliverable_qb->add_constraint('up', '=', $this->id);
            $deliverables = $deliverable_qb->execute();
                        
            foreach ($deliverables as $deliverable)
            {
                $pricePerUnit = $pricePerUnit + $deliverable->price;
            }
            
            if (count($deliverables) > 0)
            {
                $this->pricePerUnit = $pricePerUnit;
            }
        }
    
        $price = $this->units * $this->pricePerUnit;
        
        if ($price != $this->price)
        {
            $this->price = $price;
            
            if ($update)
            {
                $this->update();
                $parent = $this->get_parent_guid_uncached();
                if (is_object($parent))
                {
                    $parent->calculate_price();
                }
            }
        }
    }
    
    function order()
    {
        if ($this->state > ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED)
        {
            return false;
        }
        
        // TODO: Create tasks for the delivery as needed
        
        $this->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED;
        return $this->update();
    }
    
    function deliver()
    {
        if ($this->state > ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DELIVERED)
        {
            return false;
        }
        
        // TODO: Change status of tasks connected to the deliverable
        
        $this->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DELIVERED;
        $this->end = time();
        return $this->update();
    }
    
    function invoice($sum)
    {
        if ($this->state > ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED)
        {
            return false;
        }
        
        $open_amount = $this->price - $this->invoiced;
        if ($sum > $open_amount)
        {
            return false;
        }
        
        // TODO: Generate org.openpsa.invoices invoice
        
        $this->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED;
        $this->invoiced = $this->invoiced + $sum;
        return $this->update();
    }
}
?>