<?php

class pl_olga_files_viewer {

    var $_debug_prefix;

    var $_config;
    var $_topic;
    var $_article;
    var $_layout;
    var $_path;
    var $_root;
    var $_filename;

    var $errcode;
    var $errstr;
    var $breadcrumb;
    

    function pl_olga_files_viewer($topic, $config) {

      global $argv,$midcom;

        $this->_debug_prefix = "pl.olga.files viewer::";

        $this->_config = $config;
        $this->_topic = $topic;

        $this->_article = false;
        $this->_layout = false;

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        $this->_go = false;
        $this->_root = $this->_config->get("root_path");

        $tmp=$argv;
        $tmp[0]="";
        $this->_path=join("/",$tmp);

        $this->_filename=str_replace("//","/",$this->_root."/".$this->_path);

        $prefix = $midcom->prefix."/".$topic->name;
        foreach($tmp as $atom)
         if($atom){
          $prefix.="/".$atom;
          $this->breadcrumb.='/<a href="'.$prefix.'">'.$atom.'</a>';
         }
    }


    function can_handle($argc, $argv) {

        debug_push($this->_debug_prefix . "can_handle");
        debug_pop();

        $path=implode("/",$argv);
        if(strstr($path,"..")) return false;
        return true;
    }


    function _getArticle ($argc, $argv) {

        debug_push($this->_debug_prefix . "_getArticle");
        debug_pop();


    }

    // handle($argc, $argv)
    //
    // ....
    //
    function handle() {

        global $midcom;
        global $pl_olga_files_layouts;
        debug_push($this->_debug_prefix . "handle");



        if ($this->errcode != MIDCOM_ERROK) {
            debug_pop();
            return false;
        }

        if(is_file($this->_filename)) {
         $proc=popen("file -b ".$this->_filename,"r");
         $filetype=fread($proc,4096);
         pclose($proc);
         if (strstr($filetype,"text")==""){
          $f=fopen($this->_filename,"r");
          $GLOBALS["midcom"]->header("Content-Type: application/octet-stream");
          $GLOBALS["midcom"]->header("Content-Length: ".filesize($this->_filename));
          fpassthru($f);
          exit();
         }         
        }


        $GLOBALS["pl_olga_files_nap_activeid"] = $this->_topic->id;
        $GLOBALS['midcom']->set_pagetitle($this->_topic->extra.": ".$this->_path);

        // initialize layout
/*        $substyle = $this->_layout->get_layout_name();
        if ($substyle != "default") {
            debug_add ("pushing substyle $substyle", MIDCOM_LOG_DEBUG);
            $midcom->substyle_append($substyle);
        }
*/

        debug_pop();
        return true;
    }
    

    function show() {
        debug_push($this->_debug_prefix . "show");
        global $view,$argv,$argc;

     $path=str_replace("//","/",$this->_config->get("root_path")."/".$this->_path);
     $view["dir"]=$this->_path;
     $view["breadcrumb"]=$this->breadcrumb;
     

     if (is_dir($path)) {
      midcom_show_style("files-dir-header");
      $dir=opendir($path);
      while ($fname=readdir($dir)) {
       if($fname!="." && $fname!="..") $item[]=$fname;
      }
      closedir($dir);

      sort($item);
      for ($i=0;$i<sizeof($item);$i++){
       if (is_dir("$path/$item[$i]")) {
        $view["name"]=$item[$i];
        $view["mtime"]=strftime("%d %b %Y",filemtime("$path/$item[$i]"));
        midcom_show_style("files-dir-diritem");
       } else {
        $item1=$item[$i];
        $fsize=filesize("$path/$item[$i]")*1;
        $fsize=number_format($fsize/1024,2,","," ")." kb";
        $view["name"]=$item[$i];
        $view["size"]=$fsize;
        $view["mtime"]=strftime("%d %b %Y",filemtime("$path/$item[$i]"));
        midcom_show_style("files-dir-fileitem");
       }
      }
      midcom_show_style("files-dir-footer");
     } elseif (is_file($path)) {
      midcom_show_style("files-file-header");
      $view["file"]=join("",file($path));
      midcom_show_style("files-file-item");
      midcom_show_style("files-file-footer");
     }
     debug_pop();
     return true;
    }


    function get_metadata() {

        if ($this->_article) {
            return array (
                MIDCOM_META_CREATOR => $this->_article->creator,
                MIDCOM_META_EDITOR  => $this->_article->revisor,
                MIDCOM_META_CREATED => $this->_article->created,
                MIDCOM_META_EDITED  => $this->_article->revised
            );
        }
        else
            return false;
    }

} // viewer

?>