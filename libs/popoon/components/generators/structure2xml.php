<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: structure2xml.php 4004 2005-04-18 06:24:55Z chregu $


/**
* Module for generating an XML-Document with the help of a structure File
*  and db2xml.
*
* This is one of the core features of popoon. You give it a XML-File
*  which describes your DB-Structure and it makes a XML-Document out of
*  it. More Documentation about that structure XML will follow (or see
*   the examples in the popoon      Distribution)
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: structure2xml.php 4004 2005-04-18 06:24:55Z chregu $
* @package  popoon
*/
class popoon_components_generators_structure2xml extends popoon_components_generator {
    
    // do st2xmlCaching;
    var $st2xmlCaching = false;
    
    
    // If we want to have different options for saving the queries than    
    // $PageOptions, do them here
    var $queryCacheOptions = null;
    
    // holds the information about the different queries 
    var $queries = null;
    var $defaultExpires = 3600;
    var $db =false;
    var $dsn = "";
    var $tablePrefix = "";
    
    function __construct (&$sitemap) {
        parent::__construct($sitemap);
    }
    
    function init($attribs) {
        parent::init($attribs);
        if ($this->dsn) {
            $this->db= bx_helpers_db::getDbFromDsn($this->dsn,$this->getParameterDefault("dboptions"));
            if ($this->dsn['tableprefix']) {
                   $this->tablePrefix = $this->dsn['tableprefix'];
            }
            
        }
        
     
    }
    
    function DomStart(&$xml)
    {
        
        $xml = $this->showPage($this->getAttrib("src"));
        return True;
    }
    
    
    function showPage ($configXml,$PageOptions = array(), $returnDb2XmlObject = false) 
    {
        
        
        
        $this->api = popoon_helpers_simplecache::getInstance();
        // get the queries, either cached from file system or generated
        
        if (is_null($this->queries)) {
            $this->queries = $this->getQueries($configXml,$PageOptions);
        }
        
        $sql2xml = new XML_db2xml($this->db,"bx","Extended");
        // i should add this for all options .... later maybe
        if (!(is_null($this->getAttrib("xml_seperator")) ))
        {
            $sql2xml->setOptions(array("user_options" => array("xml_seperator"=>$this->getAttrib("xml_seperator"))));
        }
        /*I'm not sure, if we need the structure.xml in the output....  normaly we don't */
        if (isset($PageOptions["All"]["include_structure_xml"]) && $PageOptions["All"]["include_structure_xml"])
        {
            $sql2xml->add($configXml);
        }
        
        $sql2xml->setOptions(array("user_options" => array("print_empty_ids"=>False)));
        
        
        
        if (isset($PageOptions["All"]["user_options"])){
            $sql2xml->setOptions(array("user_options" => $PageOptions["All"]["user_options"]));
        }
        
        if (isset($PageOptions["querystring"])){
            $sql2xml->setOptions(array("user_options"=>array("result_root"=>"querystring")));
            $pageo = array();
            foreach($PageOptions["querystring"] as $key => $value)
            {
                if (!is_array($value))
                {
                    $pageo[$key] = $value;
                }
            }
            $sql2xml->add($pageo);
        }
        if ($array2xml = $this->getParameter("array2xml")) {
            foreach ($array2xml as $name => $array) {
                $sql2xml->setOptions(array("user_options"=>array("result_root"=>$name)));
                $sql2xml->add($array);
            }
        }
        
        if (is_array($this->queries))
        {
            $_maxLastChangedAll = array(0);
            
            foreach ($this->queries as $structureName => $query) {
                if  ($structureName == "_queryInfo") {
                    continue;
                }
                $query['user_options']['result_root'] = $structureName;
                if ($query['type'] == "dbquery"){
                    //caching the sql2xml part
                    $query["query"] = $this->replaceVarsInWhere($query["query"]);
                    if ( $this->st2xmlCaching == "true" ) { 
                        if (! (isset($query["maxLastChanged"]) )) {
                            $query["maxLastChanged"]  = $this->db->getOne($query['queryLastChanged']);
                            
                        } 
                        
                        if ( $cachedXML = $this->api->simpleCacheCheck("","st2xml_data",$query['query'],"file", $query["maxLastChanged"])) {
                            $sql2xml->addWithInput("File",$cachedXML);
                        } 
                        else {
                            $sql2xml->setOptions(array("user_tableInfo"=>$query['tableInfo'],"user_options"=>$query['user_options']));
                            $sql2xml->add($query['query']);
                            $ctx = xpath_new_context($sql2xml->Format->xmldoc);
                            $resultTree = $ctx->xpath_eval("$structureName",$sql2xml->Format->xmlroot );
                            $this->api->simpleCacheWrite("","st2xml_data",$query['query'],"<?xml version='1.0' ?".">".$sql2xml->Format->xmldoc->dump_node($resultTree->nodeset[0]),"file", $query["maxLastChanged"]);
                        }
                        $_maxLastChangedAll[] = $query["maxLastChanged"];
                        
                    }
                    else
                    {
                        
                        $sql2xml->setOptions(array("user_tableInfo"=>$query['tableInfo'],"user_options"=>$query['user_options']));
                        $sql2xml->add($query['query']);
                        
                    }
                }
                else if ( $query['type'] == "aggregate" ) {
                    if ($this->st2xmlCaching == "true" ) {
                        //http stuff
                        if (strpos($query['query'],"http") === 0 ) {
                            
                            //if maxLastChanged is set, we already checked it in the Validity container..
                            if (! (isset($query["maxLastChanged"]) )) {
                                //check for Last Modified automatically creates the cached file, if not already there
                                $query["maxLastChanged"] = $this->api->simpleCacheHttpLastModified($query['query'], time() - $query['expires'],$this->getParameter ("default","proxy") );
                            }
                            $_maxLastChangedAll[] = $query["maxLastChanged"];
                            $sql2xml->addWithInput("File",$this->api->simpleCacheGenerateName("simpleCacheHttp",$query['query']),array("root"=> $structureName));
                        } 
                        // if not starting with http, it will be a local file... hopefully
                        else {
                            $sql2xml->addWithInput("File",$query['query']);
                        }
                    } 
                    else {
                        // with no caching, just get and add it.. (maybe we should allow http caching without db-caching..)
                        $sql2xml->add($query['query'],array("root"=> $structureName));
                        
                    }
                } 
                else {
                    //quite useless right now... 
                    $sql2xml->add($query['query'],array("root"=> $structureName));
                }
            }
            $this->queries["_queryInfo"]["maxLastChanged"] = max ($_maxLastChangedAll);
            
            // send http cache headers so the sent page expires immediately 
            $lastChangedDate = gmdate("r", $this->queries["_queryInfo"]["maxLastChanged"]);
            if (isset($this->sitemap)) {
            $this->sitemap->setHeader("Last-Modified", $lastChangedDate);
            $this->sitemap->setHeader('Expires', $lastChangedDate);
            
            //var_dump(gmdate("r", $this->queries["_queryInfo"]["maxLastChanged"] ));
            // FIXME: if there is no bitlib2 code anymore, this if can go away
            if (method_exists($this->sitemap,"setLastModified")) {
                $this->sitemap->setLastModified($this->queries["_queryInfo"]["maxLastChanged"] );
            }
            }
        }
        if ($returnDb2XmlObject) {
            return $sql2xml;   
        } else {
            return $sql2xml->getXmlObject();
        }
        
    }
    
    
    function Structures2Sql ($configFile,$sqlOptions=array(),$rootpath= "/structure")
    {
        
        $configClass = bx_helpers_db::getConfigClass($configFile);
        
        $dbMainStructure = $configClass->getValues( $rootpath);
        if (is_array($dbMainStructure['children']))
        {
            foreach ($dbMainStructure['children'] as $structureName) {
                
                $dbStructure = $configClass->getValues( "$rootpath/$structureName");
                if (isset($dbStructure["expires"])) {
                    $allqueries[$structureName]['expires'] = time() - strtotime(preg_replace("#access\s+minus\s+#", "-",$dbStructure["expires"]));
                } 
                else {
                    $allqueries[$structureName]['expires'] = $this->defaultExpires;
                }
                if (isset($dbStructure["type"]) && $dbStructure["type"] == "aggregate") {
                    
                    $allqueries[$structureName]['query'] = $dbStructure["src"];
                    $allqueries[$structureName]['type'] = "aggregate";
                }
                
                else {
                    /*  This code looks like unnecessary...
                    can be removed later, if it's really not needed 
                    
                    $queryfields = $dbStructure['children'][0].".*";
                    $query = "from ".$dbStructure['children'][0];
                    $name = $dbStructure['children'][0];;
                    $dbStructure = $configClass->getValues("$rootpath/$structureName/$name");
                    */                 
                    $tableInfo= array();
                    if (!isset($sqlOptions[$structureName])) { $sqlOptions[$structureName] = array();} //E_ALL fix
                    
                    $allqueries[$structureName]['query'] = $this->Structure2Sql($configClass,$tableInfo,$sqlOptions[$structureName],$rootpath."/".$structureName);
                    $allqueries[$structureName]['tableInfo'] = $tableInfo;
                    $this->printDebug("SQL-Query for Section: $structureName: ".$allqueries[$structureName]['query']);
                    $allqueries[$structureName]['type'] = "dbquery";
                }
            }
        }
        // print "<pre>";print_r($allqueries);
        return $allqueries;
        
    }
    
    function Structure2Sql ( $configFile ,&$tableInfo,$sqlOptions=array(),$rootpath = "/bxconfig/structure")
    {
        //if it's a string, then it musst be a file, otherwise it's already a config class
        $configClass = bx_helpers_db::getConfigClass($configFile);
        
        $dbMasterValues = $configClass->getValues( $rootpath);
        // if dont is set, then stop the query building... and return nothing, not used at the moment
        if (isset($sqlOptions['dont']) &&  $sqlOptions['dont'])
        {
            return Null;
        }
        $queryfields = $dbMasterValues['children'][0].".*";
        
        $query = " from ".$this->tablePrefix.$dbMasterValues['children'][0]  . " as " . $dbMasterValues['children'][0];
        $name = $dbMasterValues['children'][0];
        
        $dbStructure = $configClass->getValues("$rootpath/$name");
        
        $queryfields =  $this->getQueryFields($name,$dbStructure,"root",$tableInfo);
        $queryfields =  substr($queryfields,1);
        $andcondition = "";
        if (isset($dbMasterValues["recursive"]) && $dbMasterValues["recursive"] == "tree") {
            
            include_once("bitlib/SQL/Tree.php");
            $t = new sql_tree($this->db);
            $t->tablename = $name;
            $data = explode(" , ",$dbStructure['fields']);
            $sitemapStartTreeID = $this->getAttrib("treeStartID");
            if (isset($sqlOptions['start_id'])) {
                
            } elseif (  $sitemapStartTreeID  ) {
                $sqlOptions['start_id'] = $sitemapStartTreeID ;
            } elseif  ( isset($dbMasterValues["start_id"]) ) {
                $sqlOptions['start_id'] = $dbMasterValues["start_id"];
            } else {
                $sqlOptions['start_id'] = 1;
            }
            $query = $t->children_query_byname(array("ID"=>$sqlOptions['start_id']),$data,True);
        }
        
        elseif (isset($dbMasterValues["recursive"]) && $dbMasterValues["recursive"] == "parents") {
            
            include_once("bitlib/SQL/Tree.php");
            $t = new sql_tree($this->db);
            $t->tablename = $name;
            $data = explode(" , ",$dbStructure['fields']);
            $query = $t->supers_query_byname(array("id"=>$sqlOptions[start_id]),$data,False);
            
        }        
        
        elseif (isset($dbMasterValues["recursive"]) && $dbMasterValues["recursive"] == "children") {
            
            include_once("bitlib/SQL/Tree.php");
            $t = new sql_tree($this->db);
            $t->tablename = $name;
            $data = explode(" , ",$dbStructure['fields']);
            $query = $t->children_query_byname(array("id"=>$sqlOptions[start_id]),$data);
        }
        else {
            $path = $rootpath;
            
            while (isset($dbStructure["children"]) &&  is_array($dbStructure["children"]))
            
            {
                
                $path = $path."/".$name;
                
                $parentname = $name;
                
                if (! isset($dbStructure["nofields"]) )
                {
                    $xmlparent = $parentname;
                }
                
                $parentdbStructure = $dbStructure;
                foreach($dbStructure["children"] as $child) {
                    $name = $child;
                    
                    
                    $dbStructure = $configClass->getValues( "$path/$name");
                    
                    $queryfields = $queryfields. $this->getQueryFields($child,$dbStructure,$xmlparent,$tableInfo);
                    
                    
                    if (! isset($dbStructure["thatfield"])) { $dbStructure["thatfield"] = "id";}
                    if (! isset($dbStructure["thisfield"])) { $dbStructure["thisfield"] = "id";}
                    $query = $query ." left join ".$this->tablePrefix.$name. " as $name on ($name.".$dbStructure["thisfield"]." = $parentname.".$dbStructure["thatfield"];
                    if (isset($dbStructure["objectfield"]) )
                    {
                        $query = "$query and $parentname.".$dbStructure["objectfield"]." = '$name'";
                    }
                    elseif (isset($parentdbStructure["objectfield2"]))
                    {
                        $query = "$query and $parentname.".$parentdbStructure["objectfield2"]." = '$name'";
                        
                    }
                    
                    $query .= ")";
                    
                    if (isset($dbStructure["activefield"]))
                    {
                        $activequery = "$name.".$dbStructure["activefield"] ."= 1 or isnull($name.".$dbStructure["activefield"] .")";
                        if (isset($dbStructure["activefromfield"]))
                        {
                            $activequery .= " or (( $child.".$dbStructure["activefield"] ." = 2) and (($child.".$dbStructure["activefromfield"] ."< now()  or $child.".$dbStructure["activetillfield"] ." = 0) and ($child.".$dbStructure["activetillfield"] ."> now() or $child.".$dbStructure["activetillfield"] ." = 0)))";
                        }
                        
                        
                        $andconditions[] = $activequery;                        
                    }
                    
                    
                }
                // if stopattable is set, then stop the query building...
                if (isset($sqlOptions["stopattable"]) && $sqlOptions["stopattable"] == $name) {
                    break;
                }
                // debug::print_rp($dbStructure);
            }
            $query = "select $queryfields".$query;
            if (!isset($sqlOptions["ShowAll"]) && isset($andconditions) && is_array($andconditions))
            {
                $andcondition = " and (".implode($andconditions,") and (").")";
            }
            else
            {
                $andcondition = "";
            }
        }
        
        if (isset($sqlOptions["where"]))
        {
            if (!isset($sqlOptions["where"])) { $sqlOptions["where"] = "";} //E_ALL fix
            if (!isset($dbMasterValues["where"])) { $dbMasterValues["where"] = "";} //E_ALL fix     
            $_append_check = strtoupper(substr($sqlOptions["where"],0,3));
            $_append_check_dbMaster = strtoupper(substr($dbMasterValues["where"],0,3));
            
            if ("AND" == $_append_check || "OR " == $_append_check)
            {
                $query .= " where (". $dbMasterValues["where"] ." " .$sqlOptions["where"].") $andcondition";
            }
            
            elseif ("AND" == $_append_check_dbMaster || "OR " == $_append_check_dbMaster)
            {
                
                $query .= " where (". $sqlOptions["where"] ." " .$dbMasterValues["where"].") $andcondition";
                
            }
            
            
            else
            {
                $query .= " where (".$sqlOptions["where"] .") $andcondition";
            }
        }
        
        elseif (isset($dbMasterValues["where"]))
        {
            //            $_where = $this->replaceVarsInWhere($dbMasterValues["where"]);
            $_where = $dbMasterValues["where"];
            // if there is already a group by (coming from tree searches...)
            if (strpos($query,"group by"))
            {	
                // we assume, there's a "where" already
                $query = str_replace ("group by",$_where .$andcondition ." group by",$query);
            }
            else {
                $query .= " where (".$_where .") $andcondition";
            }
            
            
        }
        if (isset($sqlOptions["groupby"]))
        {
            $query .= " group by $sqlOptions[groupby]";
        }
        elseif (isset($dbMasterValues["groupby"]))
        {
            $query .= " group by $dbMasterValues[groupby]";
        }
        if (isset($sqlOptions["orderby"]))
        {
            $query .= " order by $sqlOptions[orderby]";
        }
        elseif (isset($dbMasterValues["orderby"]))
        {
            $query .= " order by $dbMasterValues[orderby]";
        }
        
        
        if (isset($sqlOptions["limit"]))
        {
            $query .= " LIMIT $sqlOptions[limit]";
        }
        elseif (isset($dbMasterValues["limit"]))
        {
            $query .= " LIMIT $dbMasterValues[limit]";
        }
        
        //if we have the simplepermWhere attribute and a table named "Section" add this to the query...
        // this is not really safe... there could be something else named Section.
        
        if ($simplepermWhere = $this->getAttrib("simplepermWhere") )
        {
            
            $query = str_replace("where", "where $simplepermWhere and ", $query);
        }
        
        return $query;
    }
    function getQueryFields($tablename,$dbStructure,$xmlparent,&$tableInfo)
    {
        $queryfields = ""; //E_ALL fix
        
        if (! isset($dbStructure["nofields"]) && !(isset($dbStructure["fields"]) && (strlen($dbStructure["fields"]) == 0)))
        {
            if (isset($dbStructure["fields"]) )
            {
                $fields = explode(" , ",$dbStructure["fields"]);
                foreach ($fields as $fieldname)
                {
                    
                    if (! preg_match ("/\(/",$fieldname))
                    {
                        $queryfields = $queryfields . ", $tablename.$fieldname ";
                    }
                    else {
                        $queryfields = $queryfields . ", $fieldname ";
                    }
                    
                }
            }
            else
            {
                $queryfields = $queryfields . ", $tablename.*";
            }
            
            $tableInfo['parent_table'][$tablename]="$xmlparent";
            
        }
        return $queryfields;
    }
    
    function getQueries($configXml,$PageOptions) {
        
        //here comes the new cache code
        
        $config = bx_helpers_db::getConfigClass($configXml);
        if (!$this->queryCacheOptions) {
            $this->queryCacheOptions = $PageOptions;
        } 
        
        if ( $queries = $this->api->simpleCacheCheck($configXml,"st2xml_queries",$this->queryCacheOptions)) {
        } 
        // we don't have the queries cached, generate them..
        else {
            $queries = $this->Structures2Sql($config,$PageOptions);
            
            // generate the query for lastchanged stuff
            // even if we don't do st2xml caching, it's not a big deal
            // to do this here anyway, since this is only generated, when
            // structure/structure.xml changes, which should not happen very often...
            // Hint for PERFORMANCE hungry people :)
            // the tree sqls could be made easier here
            // this one generates:
            // select max(greatest(unix_timestamp(Section.changed), 0)) from Section as Section, Section as b where Section.l between b.l and b.r and Section.l between '2' and '61'
            // but 
            // select max(greatest(Section.changed, 0)) from Section where Section.l between '2' and '61' 
            // would give back the same result and is faster.
            // It doesn't matter if you use Mysql4 with query caching, but it's slightly slower without
            // To implement that, we would have to change structure2sql, which then should return an omptimized query
            // (to lazy to do that now)
            
            foreach ($queries as $structureName => $query) {                
                $_changedFields = "";
                // get all the tables
                // HINT: document2object and section2document are missing here... could maybe lead to wrong
                //  updates, if you change one of that tables without changing any other table
                if ($query["type"] == "dbquery" && is_array($query["tableInfo"]["parent_table"] ) )
                {
                    foreach($query["tableInfo"]["parent_table"] as $table => $parent) {
                        $_changedFields .= 'unix_timestamp('.$table .'.changed), ';
                    }
                    
                    
                    // strip everything away before "from"
                    $_cleanFrom = substr($query["query"], strpos($query["query"],"from"));
                    
                    // strip group by away
                    $_checkGroupBy = strpos($_cleanFrom,"group by") ;                    
                    if ( $_checkGroupBy !== false) {
                        $_cleanFrom = substr($_cleanFrom,0,$_checkGroupBy);
                    }
                    
                    // strip order by away
                    $_checkOrderBy = strpos($_cleanFrom,"order by") ;
                    if ( $_checkOrderBy !== false) {
                        $_cleanFrom = substr($_cleanFrom,0,$_checkOrderBy);
                    }
                    
                    $queries[$structureName]["queryLastChanged"] = 'select max(greatest('. $_changedFields.'0)) ' .$_cleanFrom;
                } 
            }
            $this->api->simpleCacheWrite($configXml,"st2xml_queries",$this->queryCacheOptions,$queries);
        }
        //that's it for caching the queries..
        return $queries;
    }
    
    /**
    * Generate cacheKey
    *
    * Calls the method inherited from 'Component'
    *
    * @param   array  attributes
    * @param   int    last cacheKey
    * @see     generateKeyDefault()
    */
    function generateKey($attribs, $keyBefore){
        $this->queryCacheOptions = array(
        "attribs" => $this->attribs,  // not ideal, because they won't get resolved this way
        "src" => $this->getAttrib("src")
        );
        return($this->generateKeyDefault(array($_GET["path"],$this->attribs), $keyBefore));
    }
    
    
    /** Generate validityObject  
    *
    * This is common to all "readers", you'll find the same code there.
    * I'm thinking about making a method in the class component named generateValidityFile() or alike
    * instead of having the same code everywhere..
    *
    * @author Hannes Gassert <hannes.gassert@unifr.ch>
    * @see  checkvalidity()
    * @return  array  $validityObject contains the components attributes plus file modification time and time of last access.
    */
    function generateValidity(){
        return($this->queries["_queryInfo"]["maxLastChanged"]);
    }
    
    /**
    * Check validity of a validityObject from cache
    *
    * This implements only the most simple form: If there's no fresher version, take that from cache.
    * I guess we'll need some more refined criteria..
    *
    * @return  bool  true if the validityObject indicates that the cached version can be used, false otherwise.
    * @param   object  validityObject
    */
    function checkValidity($validityObject){
        
        require_once("bitlib/admin/php/api.php");
        $this->api = admin_api::getInstance();
        
        if (! ($queries = $this->api->simpleCacheCheck($this->getAttrib("src"),"st2xml_queries",$this->queryCacheOptions)))
        {
            
            // if there's no cached file, Validity is false
            return false;
        }
        else
        {
            $this->queries = $queries;
        }
        if ( $this->st2xmlCaching == "true" ) { 
            
            $_maxLastChanged = array();
            foreach($this->queries as $structureName => $query) {
                if ($query['type'] == "dbquery"){
                    $this->queries[$structureName]["maxLastChanged"]= ( $this->db->getOne($query['queryLastChanged']));
                    $_maxLastChanged[] = $this->queries[$structureName]["maxLastChanged"];
                } 
                // if it's of type aggregate 
                else if ($query['type'] == "aggregate" ) {
                    //and starts with http...
                    if (strpos($query['query'],"http") === 0 ) {
                        $_maxLastChanged[] =  $this->api->simpleCacheHttpLastModified($query['query'], time() - $query['expires'],$this->getParameter("default","proxy"));
                    }
                    // if not starting with http, it will be a local file... hopefully
                    else {
                        $_maxLastChanged[] =  filemtime($query['query']);
                    }
                }
                
                
            }
            $_maxLastChanged = max($_maxLastChanged);
            $this->queries["_queryInfo"] = array("maxLastChanged" => $_maxLastChanged);
            $this->sitemap->setLastModified($_maxLastChanged);
            if ($validityObject >= $_maxLastChanged) {
                return true;
            } else {
                return false;
            }
            
        }
        else {
            return false;
        }
        
        // check for available queries here;
    }
    
    function replaceVarsInWhere($where) {
        
        //this does replace $VAR in structure.xml with the actual variable from requests 
        // or popooon parameter with type "structure2xml"
        // %VAR  with an sql_regcase ( hello gets to [Hh][Ee][Ll][Ll][Oo] ) and
        // +VAR with +VAR ("hello world" gets to "+hello +world" this is useful for fulltext search in mysql)
        $regs = array();
        $repl = array();
        
        $requests = array_merge($_REQUEST,$this->getParameter("structure2xml"));
        foreach ($requests as $key => $val)
        {
            /* not so sure about that */
            
            if (is_null($val) or $val ===    false)
            {
                $val = 0;
            }
            else if(is_array($val)) {
                if (count($val) == 0) {
                    $val = '0';
                } else {
                    $val = "'".join("','",$val)."'";
                }
            }
            else {
                $val = trim($val);
            }
            $regs[] ="\$$key";
            $repl[] = "$val";                
            $regs[] ="%$key";                
            $repl[] = sql_regcase($val);                
            $regs[] ="+$key";
            $repl[] = "+".join(" +",explode(" ",$val));
        }
        $where = str_replace($regs,$repl,$where);
        return $where;
    }
    
    
}
?>
