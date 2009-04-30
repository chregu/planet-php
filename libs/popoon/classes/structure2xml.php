<?php
class popoon_classes_structure2xml {
    
    private $parent;
    private $queries = null;
    private $queryCacheOptions = null;
    private $db = null;
    
    function __construct($parent,$tablePrefix) {
        $this->tablePrefix = $tablePrefix;
        $this->parent = $parent;
        $this->api = popoon_helpers_simplecache::getInstance();
        if (isset($this->parent->db)) {
            $this->db = $this->parent->db;
        } elseif (isset($GLOBALS['POOL']->db)) {
            $this->db = $GLOBALS['POOL']->db;
        }
    }
    
    private function getAttrib($value) {
        return $this->parent->getAttrib($value);
    }
    
    private  function getParameter($value) {
        return $this->parent->getParameter($value);
    }
    
    
    function showPage ($configXml,$PageOptions = array(), $returnDb2XmlObject = false) 
    {
        
        // get the queries, either cached from file system or generated
        
        if (is_null($this->queries)) {
            $this->queries = $this->getQueries($configXml,$PageOptions);
        }
         $sql2xml = new XML_db2xml($this->db,"bx","Extended");
        
        if ($this->getParameter("contentIsXml")) {
            $sql2xml->setContentIsXml(true);
        }
        
        // i should add this for all options .... later maybe
        if (!(is_null($this->getAttrib("xml_seperator")) ))
        {
            $sql2xml->setOptions(array("user_options" => array("xml_seperator"=>$this->getAttrib("xml_seperator"))));
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
                if (isset($query['noOutput']) && $query['noOutput']) {
                    
                    continue;
                }
                $query['user_options']['result_root'] = $structureName;
                if ($query['type'] == "dbquery"){
                    //caching the sql2xml part
                    $query["query"] = $this->replaceVarsInWhere($query["query"]);
                    bx_log::log("stucture2xml: ".$query['query']);
                    if ( $this->parent->st2xmlCaching == "true" ) { 
                        if (! (isset($query["maxLastChanged"]) )) {
                            $this->db->loadModule('extended');
                            $query["maxLastChanged"]  = $this->db->extended->getOne($query['queryLastChanged']);
                            
                        } 
                        if ( $cachedXML = $this->api->simpleCacheCheck("","st2xml_data",$query['query'],"file", $query["maxLastChanged"])) {
                            $sql2xml->addWithInput("File",$cachedXML);
                        } 
                        else {
                            $sql2xml->setOptions(array("user_tableInfo"=>$query['tableInfo'],"user_options"=>$query['user_options']));
                            $sql2xml->add($query['query']);
                            $ctx = new DomXpath($sql2xml->Format->xmldoc);
                            $resultTree = $ctx->query("$structureName",$sql2xml->Format->xmlroot );
                            
                            $this->api->simpleCacheWrite("","st2xml_data",$query['query'],"<?xml version='1.0' ?".">".$sql2xml->Format->xmldoc->saveXML($resultTree->item(0)),"file", $query["maxLastChanged"]);
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
                    if ($this->parent->st2xmlCaching == "true" ) {
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
        
        if (PEAR::isError($dbMainStructure)) {
            throw new PopoonPEARException($dbMainStructure);
        }
        
        if (is_array($dbMainStructure['children']))
        {
            
            foreach ($dbMainStructure['children'] as $structureName) {
                
                $dbStructure = $configClass->getValues( "$rootpath/$structureName");
                if (PEAR::isError($dbStructure)) {
                    die($dbStructure->getUserinfo() .  "\n" . $dbStructure->getMessage());
                }
                if (isset($dbStructure["expires"])) {
                    $allqueries[$structureName]['expires'] = time() - strtotime(preg_replace("#access\s+minus\s+#", "-",$dbStructure["expires"]));
                } 
                else if (isset($this->parent->defaultExpires)) {
                    
                    $allqueries[$structureName]['expires'] = $this->parent->defaultExpires;
                } else {
                    $allqueries[$structureName]['expires'] = 3600;
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
                    $allqueries[$structureName]['type'] = "dbquery";
                }
                if (isset($dbStructure['noOutput']) && $dbStructure['noOutput'] = 'true') {
                   $allqueries[$structureName]['noOutput'] = true;
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
        $query = " from ".$this->tablePrefix.$dbMasterValues['children'][0] . " as " . $dbMasterValues['children'][0];
        $name = $dbMasterValues['children'][0];
        $dbStructure = $configClass->getValues("$rootpath/$name");
        
        $queryfields =  $this->getQueryFields($name,$dbStructure,"root",$tableInfo);
        $queryfields =  substr($queryfields,1);
        $andcondition = "";
        if (isset($dbMasterValues["recursive"]) && $dbMasterValues["recursive"] == "tree") {
            
            include_once("bitlib/SQL/Tree.php");
            $t = new sql_tree($this->db);
            $t->tablename = $this->tablePrefix.$name;
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
            $t->tablename = $this->tablePrefix.$name;
            $data = explode(" , ",$dbStructure['fields']);
            $query = $t->supers_query_byname(array("id"=>$sqlOptions[start_id]),$data,False);
            
        }        
        
        elseif (isset($dbMasterValues["recursive"]) && $dbMasterValues["recursive"] == "children") {
            
            include_once("bitlib/SQL/Tree.php");
            $t = new sql_tree($this->db);
            $t->tablename = $this->tablePrefix.$name;
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
                $query .= " where ((". $dbMasterValues["where"] .") " .$sqlOptions["where"].") $andcondition";
            }
            
            elseif ("AND" == $_append_check_dbMaster || "OR " == $_append_check_dbMaster)
            {
                
                $query .= " where ((". $sqlOptions["where"] .") " .$dbMasterValues["where"].") $andcondition";
                
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
        if (PEAR::isError($dbStructure)) {
            $e = new PopoonPEARException($dbStructure);
            if (version_compare(phpversion(),"5.0.2",">") ) {
                $e->userInfo = "There seems to be a problem with PHP 5.0.3.
                        If you are using PHP 5.0.3 and see this message, 
                        try down grading to 5.0.2 or upgrading to 5.0.4-dev, 
                        until we find a solution. ";
                
            }
            throw $e;    
        }
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
        
        if ( false && $queries = $this->api->simpleCacheCheck($configXml,"st2xml_queries",$this->queryCacheOptions)) {
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
    
        function replaceVarsInWhere($where) {
        
        //this does replace $VAR in structure.xml with the actual variable from requests 
        // or popooon parameter with type "structure2xml"
        // %VAR  with an sql_regcase ( hello gets to [Hh][Ee][Ll][Ll][Oo] ) and
        // +VAR with +VAR ("hello world" gets to "+hello +world" this is useful for fulltext search in mysql)
        $regs = array();
        $repl = array();
        if (is_array($this->getParameter("structure2xml"))) {
            $requests = array_merge($_REQUEST,$this->getParameter("structure2xml"));
        } else {
            $requests = $_REQUEST;
        }
        return self::replaceVarsInWhereStatic($where,$requests);
       
    }
    
    static function replaceVarsInWhereStatic($where,$requests) {
        $regs = array();
        $repl = array();
        
         foreach ($requests as $key => $val)
        {
            /* not so sure about that */
            
            if (is_null($val) or $val ===    false)
            {
                continue;
                //$val = 0;
            }
            else if(is_array($val)) {
                if (count($val) == 0) {
                    $val = '0';
                } else {
                    @$val = "'".join("','",$val)."'";
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
        $where = preg_replace('#\[\[[^\]]*\$[^\]]+\]\]#',"",$where);
        $where = str_replace(array("[[","]]"),"",$where);
        //replace where condition, if there's an empty one...
        $where = preg_replace("#where\s*\(\s*\)#"," where ( 1 = 1 ) ",$where);
        //and delete an eventuall "and" only at the beginning
        $where = preg_replace("#where\s*\(\s*and#"," where (  ",$where);
        return $where;
        
    }
}