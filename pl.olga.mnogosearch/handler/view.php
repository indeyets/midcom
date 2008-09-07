<?php
/**
 * @package pl.vox.www
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 4153 2006-09-20 18:28:00Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * p.v.www index page handler
 *
 * @package pl.vox.www
 */

class pl_olga_mnogosearch_handler_view extends midcom_baseclasses_components_handler
{
    var $_content_topic = null;

    var $searchmode = 'all';

    var $wordsmode = 'beg';

    var $query = null;

    var $query_orig = null;

    var $pageresults = null;

    var $pagenumber = 0;

    var $sortorder = "RPD";

    /**
     * Simple default constructor.
     */
    function pl_olga_mnogosearch_handler_view()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        if (!extension_loaded('mnogosearch')) 
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Mnogosearch PHP extension is required to run Mnogosearch frontend!");
        }

        $this->_topic =& $this->_request_data['content_topic'];

        $this->sortorder = $this->_config->get('sortorder');
        $this->searchmode = $this->_config->get('searchmode');
        $this->wordsmode = $this->_config->get('wordsmode');

        if (is_array($_GET) and !empty($_GET))
        {
            if (array_key_exists('q',$_GET))
            {
                $this->query = urldecode($_GET['q']);
                $this->query_orig = $_GET['q'];
            }

            if (array_key_exists('np',$_GET))
            {
                $this->pagenumber = $_GET['np'];
            }

            if (array_key_exists('ps',$_GET))
            {
                $this->pageresults = $_GET['ps'];
            }
            else
            {
                $this->pageresults = $this->_config->get('pageresults');
            }

        }

        if ($this->query)
        {
            $this->udm_agent = Udm_Alloc_Agent($this->_config->get('dbaddr'));
            Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_CHARSET,'utf-8');
            Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_BROWSER_CHARSET,'utf-8');
            Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_PAGE_SIZE,$this->pageresults);
            Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_PAGE_NUM,$this->pagenumber);

            if ($this->_config->get('trackquery'))
            {
                Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_TRACK_MODE,UDM_ENABLED);
            }
            else
            {
                Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_TRACK_MODE,UDM_DISABLED);
            }

            if ($this->_config->get('cache'))
            {
                Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_CACHE_MODE,UDM_ENABLED);
            }
            else
            {
                Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_CACHE_MODE,UDM_DISABLED);
            }

            if (Udm_Api_Version() >=  30111)
            {
                if ($this->_config->get('crosswords'))
                {
                    Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_CROSS_WORDS,UDM_ENABLED);
                }
                else
                {
                    Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_CROSS_WORDS,UDM_DISABLED);
                }
            }

            if ($this->_config->get('detectclones'))
            {
                Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_DETECT_CLONES,UDM_ENABLED);
            }
            else
            {
                Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_DETECT_CLONES,UDM_DISABLED);
            }

            Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_EXCERPT_SIZE,$this->_config->get('excerptsize'));
            Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_EXCERPT_PADDING,$this->_config->get('excerptpadding'));
            Udm_Set_Agent_Param_Ex($this->udm_agent,'dateformat',$this->_config->get('dateformat'));

            if (Udm_Api_Version() >=  30215) 
            {
                Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_SORT_ORDER,$this->sortorder);
                Udm_Set_Agent_Param($this->udm_agent,UDM_PARAM_RESULTS_LIMIT,$this->_config->get('resultslimit'));
            }

            Udm_Parse_Query_String($this->udm_agent,$_SERVER['QUERY_STRING']);
        }

    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {

        $_MIDCOM->set_pagetitle("{$this->_topic->extra} : {$this->query}");

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $this->query,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        if (empty($this->query)) return true;

        $res = Udm_Find($this->udm_agent,$this->query);

        if(!$res)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, Udm_Error($this->udm_agent));
        }
        else
        {
            $stats['found'] = Udm_Get_Res_Param($res,UDM_PARAM_FOUND);
            $stats['rows'] = Udm_Get_Res_Param($res,UDM_PARAM_NUM_ROWS);

            $stats['wordinfo'] = Udm_Get_Res_Param($res,UDM_PARAM_WORDINFO_ALL);

            $stats['searchtime'] = Udm_Get_Res_Param($res,UDM_PARAM_SEARCHTIME);
            $stats['first_doc'] = Udm_Get_Res_Param($res,UDM_PARAM_FIRST_DOC);
            $stats['last_doc'] = Udm_Get_Res_Param($res,UDM_PARAM_LAST_DOC);

            if (!$stats['found']) {
                $this->_stats = $stats;
                return true;
            }

            $stats['pageresults'] = $this->pageresults;
            $stats['pagenumber'] = $this->pagenumber;

            $from = IntVal($this->pagenumber)*IntVal($this->pagenumber);
            $to = IntVal($this->pagenumber+1)*IntVal($this->pageresults);

            if ($to>$stats['found'])
            {
                $to = $stats['found'];
            }

            if (($from + $this->pageresults) < $stats['found'])
            {
                $stats['isnext'] = 1;
            }

            for($i = 0;$i<$stats['rows']; $i++)
            {
                $docs[$i]['excerpt_flag'] = 0;
                $docs[$i]['clonestr'] = '';

                $rec_id = Udm_Get_Res_Field($res,$i,UDM_FIELD_URLID);

                $global_res_position = $i;

                if (Udm_Api_Version() >=  30207)
                {
                    $origin_id = Udm_Get_Res_Field($res,$i,UDM_FIELD_ORIGINID);
                    if ($origin_id)
                    {
                        continue;
                    }
                    else
                    {
                        for($j = 0;$j<$stats['rows'];$j++)
                        {
                            $cl_origin_id = Udm_Get_Res_Field($res,$j,UDM_FIELD_ORIGINID);

                            if (($cl_origin_id) &&
                            ($cl_origin_id == $rec_id))
                            {
                                $url = Udm_Get_Res_Field($res,$j,UDM_FIELD_URL);
                                $contype = Udm_Get_Res_Field($res,$j,UDM_FIELD_CONTENT);
                                $docsize = Udm_Get_Res_Field($res,$j,UDM_FIELD_SIZE);
                                $lastmod = Udm_Get_Res_Field($res,$j,UDM_FIELD_MODIFIED);
                                $docs[$i]['clonestr'] .=  "<li><A HREF = '{$url}'>{$url}</A></li>";
                            }
                        }
                    }
                }

                if (Udm_Api_Version() >=  30204)
                {
                    $docs[$i]['excerpt_flag'] = Udm_Make_Excerpt($this->udm_agent, $res, $i);
                }

                $docs[$i]['ndoc'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_ORDER);
                $docs[$i]['rating'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_RATING);
                $docs[$i]['url'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_URL);
                $docs[$i]['contype'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_CONTENT);
                $docs[$i]['docsize'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_SIZE);
                $docs[$i]['lastmod'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_MODIFIED);

                $title = Udm_Get_Res_Field($res,$i,UDM_FIELD_TITLE);
                $title = ($title) ? htmlspecialChars($title):basename($docs[$i]['url']);

                $docs[$i]['title'] = $this->ParseDocText($title);
                $docs[$i]['text'] = $this->ParseDocText(Udm_Get_Res_Field($res,$i,UDM_FIELD_TEXT));
                $docs[$i]['keyw'] = $this->ParseDocText(Udm_Get_Res_Field($res,$i,UDM_FIELD_KEYWORDS));
                $docs[$i]['desc'] = $this->ParseDocText(Udm_Get_Res_Field($res,$i,UDM_FIELD_DESC));

                $docs[$i]['crc'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_CRC);

                if (Udm_Api_Version() >=  30203) 
                {
                    $docs[$i]['doclang'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_LANG);
                    $docs[$i]['doccharset'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_CHARSET);
                }

                $docs[$i]['category'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_CATEGORY);

                if (Udm_Api_Version() >=  30207)
                {
                    $docs[$i]['pop_rank'] = Udm_Get_Res_Field($res,$i,UDM_FIELD_POP_RANK);
                }
                else
                {
                    $docs[$i]['pop_rank'] = '';
                }
            }

            // Free result
            Udm_Free_Res($res);
        }

        $this->_stats = $stats;
        $this->_docs = $docs;
        return true;
    }


    /**
     * Shows the loaded article.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view ($handler_id, &$data)
    {

        if (!$this->query) {
            $this->_request_data['query'] = "";
            midcom_show_style('show-form');
        }
        else
        {
            $this->_request_data['query'] = $this->query;
            $this->_request_data['query_orig'] = $this->query_orig;
            $this->_request_data['stats'] = $this->_stats;
            midcom_show_style('show-form');
            if ($this->_stats['found'])
            {
                midcom_show_style('results');
                midcom_show_style('navi');
                midcom_show_style('result-start');

                foreach($this->_docs as $doc)
                {
                    $this->_request_data['doc'] = $doc;
                    midcom_show_style('result-item');
                }
                midcom_show_style('result-end');
                midcom_show_style('navi');
            }
            else
            {
                midcom_show_style('results');
                midcom_show_style('empty');
            }
        }
    }

    function ParseDocText($text)
    {
        $str = $text;
        $hlbeg = $this->_config->get('hlbeg');
        $hlend = $this->_config->get('hlend');

        $str = str_replace("\2",$hlbeg,$str);
        $str = str_replace("\3",$hlend,$str);
        while (substr_count($str,$hlbeg) > substr_count($str,$hlend))
        {
            $str .= $hlend;
        }
        return $str;
    }
}

?>