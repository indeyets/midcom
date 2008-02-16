<?php
/**
 * @package pl.olga.windguru
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/*
this is the function clients will use
*/
function windguru_forecast($id_spot,$code,$lang="") {
	$wgf = new WindguruFcst($id_spot,$code,$lang);
	echo $wgf->show();
}

/**
 * @package pl.olga.windguru
 */
class WindguruFcst {

	var $config, $id_spot, $code, $id_model, $db, $status, $html, $data, $lang, $version, $encoding;

	function WindguruFcst($id_spot,$code,$lang='',&$config) {

		$this->config = $config;
		$this->id_spot = (int)$id_spot;
		$this->code = $code;
		$this->id_model = 3;
		$this->db = false;
		$this->status = array(WG_STATUS_NWW3 => "", WG_STATUS_GFS => "");
		$this->last_status_check = 0;
		$this->readStatus(); // read local data status
		$this->html = "";
		$this->data = array(WG_STATUS_NWW3 => "", WG_STATUS_GFS => "");
		$this->lang = 'en';
		$this->setLang($lang);
		$this->encoding = "";
 		$this->version = "1.5 beta";
	}

	/*
	prints the forecast if available
	takes care of updating from windguru.cz, caching the forecast, reading data status from windguru.cz etc...
	*/
	function show()
	{
		if (!$this->id_spot || !$this->code)
		{
			debug_add("Missing Sopt ID and/or Spot Key",MIDCOM_LOG_ERROR);
			debug_pop();
			return false;
		}

		if (!$this->config->get('id_user'))
		{
			debug_add("Missing Windguru user ID",MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
		}

		$this->updateStatus(); // try to update data status from windguru.cz when it's time

		$this->readCache();


		if (!$this->html
			|| ($this->data[$this->id_model] != $this->status[$this->id_model]))
		{
			$this->updateCache();
		}

		if (!$this->html
			|| ($this->data[$this->id_model] > $this->status[$this->id_model]))
		{
				$this->updateStatus(1);
		}

		if (!$this->html)
		{
			debug_add("Windguru data is missing",MIDCOM_LOG_ERROR);
			debug_pop();
			return false;
		}
		else
		{
			return str_replace("wg_images/","/midcom-static/pl.olga.windguru/images/",$this->html);
		}
	}

	function readCache()
	{

		$qb = pl_olga_windguru_cache_dba::new_query_builder();
		$qb->add_constraint('spot','=',$this->id_spot);
		$qb->add_constraint('model','=',$this->id_model);
        $qb->add_constraint('lang','=',$this->lang);
		$result = $qb->execute();

		if (count($result))
		{
			$this->html = $result[0]->data;
			$this->data[$this->id_model] = $result[0]->met;
			$this->data[10] = $result[0]->wave;
			return true;
		}

		return false;

	}

	function idMod($str) { // get model id
		$str = strtolower($str);
		if($str=='gfs') return WG_STATUS_GFS;
		if($str=='nww3') return WG_STATUS_NWW3;
		return WG_STATUS_NONE;
	}

	function updateCache()
	{
		$this->getForecast();
		$_MIDCOM->auth->request_sudo();

        $qb = pl_olga_windguru_cache_dba::new_query_builder();
        $qb->add_constraint('spot','=',$this->id_spot);
        $qb->add_constraint('model','=',$this->id_model);
        $qb->add_constraint('lang','=',$this->lang);
        $result = $qb->execute();

        if (count($result))
        {
			$result[0]->data = $this->html;
			$result[0]->met = $this->data[$this->id_model] ? $this->data[$this->id_model] : '';
			$result[0]->wave = $this->data[10] ? $this->data[10] : '';

            if (!$result[0]->update())
            {
                debug_add("Cache {$this->id_spot} update failed: ".mgd_errstr(),MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
			debug_add("Cache {$this->id_spot} updated. ");
            debug_pop();
		}
		else
		{
			$tmp = new pl_olga_windguru_cache_dba();
			$tmp->spot = $this->id_spot;
			$tmp->model = $this->id_model;
			$tmp->lang = $this->lang;
            $tmp->data = $this->html;
            $tmp->met = $this->data[$this->id_model] ? $this->data[$this->id_model] : '';
            $tmp->wave = $this->data[10] ? $this->data[10] : '';

            if (!$tmp->create())
            {
                debug_add("Cache {$this->id_spot} create failed: ".mgd_errstr(),MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }

		}

		return true;
	}

	function readStatus()
	{
		$this->status = array();

        foreach ($this->config->get('status') as $statusid)
        {
			$qb = pl_olga_windguru_status_dba::new_query_builder();
			$qb->add_constraint('status','=',$statusid);
			$result = $qb->execute();

			if (count($result))
			{
            	$this->status[$statusid] = $result[0]->value;
			}
			else
            {
				$_MIDCOM->auth->request_sudo();
				$tmp = new pl_olga_windguru_status_dba();
                $tmp->value = time();
				$tmp->status = $statusid;
                if (!$tmp->create())
                {
                    debug_add("Status {$statusid} autocreate failed: ".mgd_errstr(),MIDCOM_LOG_ERROR);
                    debug_pop();
                }

				$this->status[$statusid] = time();
            }

        }

		$this->last_status_check = $this->status[WG_STATUS_NONE];

		return true;
	}

// updates data status from windguru.cz, if reload==1 then it updates immediatelly otherwise it will not
// update if last update was less then 10 minutes ago

	function updateStatus($reload = 0)
	{
		$_MIDCOM->auth->request_sudo();

		$return = false;
		if (!$this->last_status_check)
		{
			$this->readStatus();
		}

		$delay = 10 * 60; // 10 minutes

		if(!$reload)
			{
			if ((time() - $this->last_status_check) < $delay)
			{
				return true; // if we checked recently do not check again
			}
		}

		$status = $this->getDataStatus();

		if (!count($status))
		{
			return false;
		}

		$this->status = array();
		$this->status[WG_STATUS_GFS] = $status['gfs'];
		$this->status[WG_STATUS_NWW3] = $status['nww3'];
		$this->status[WG_STATUS_NONE] = time();

		foreach ($this->config->get('status') as $statusid)
		{
            $qb = pl_olga_windguru_status_dba::new_query_builder();
            $qb->add_constraint('status','=',$statusid);
            $result = $qb->execute();

            if (count($result))
            {
                $tmp = $result[0];
				$tmp->value = $this->status[$statusid];

				if (!$tmp->update())
				{
					debug_add("Status $statusid update failed: ".mgd_errstr(),MIDCOM_LOG_ERROR);
					debug_pop();
					return false;
				}
			}

			unset($tmp);
		}

		return true;
	}

	function getDataStatus($url = "http://www.windguru.cz/data_status.php")
	{
		$file = @file($url);
		$status = array();

		if (!is_array($file))
		{
			return $status;
		}

		if (!count($file))
		{
			return $status;
		}

		foreach ($file as $row)
		{
			$tmp = explode(";",trim($row));
			if (count($tmp)==3)
			{
				if($tmp[0])
				{
					$status[$tmp[0]] = trim($tmp[2]);
				}
			}
		}

		return $status;
	}

	function getForecast($lang="")
	{



		$url = "http://www.windguru.cz/int/distr2.php?u=".$this->config->get('id_user')."&s={$this->id_spot}&c={$this->code}&lng={$this->lang}&v=".urlencode($this->version)."&enc={$this->encoding}";
		$fcst = @file($url);

		if (!is_array($fcst))
        {
            return false;
        }

		end($fcst);
		$last = trim(prev($fcst));

		if (substr($last,0,9)!='<!--MDATA')
        {
            return false;
        }

		$last = substr($last,10,-3);
		if($last)
        {
			$tmp = explode(",",$last);
			foreach($tmp as $row)
            {
				$arr = explode(";",$row);
				if(count($arr)==2)
                {
                    $this->data[$this->idMod($arr[0])] = substr($arr[1],0,4)."-".substr($arr[1],4,2)."-".substr($arr[1],6,2)." ".substr($arr[1],8,2).":00:00";
                }
			}
		}
		else
        {
			return false;
		}

		$this->html = implode("",$fcst);
		if($this->html) return true; // reading was succesful
	}

	function setLang($lang="") 
    {
		if ($lang)
		{
			$this->lang = $lang;
		}
		elseif($this->config->get('lang'))
		{
			$this->lang = $this->config->get('lang');
		}
		else
		{
			$this->lang = 'en';
		}
	}

}


?>