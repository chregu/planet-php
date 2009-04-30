<?php

class dbsaxStream {
    
    var $html;
    var $position = 0;
    var $dbposition = 0;
    var $xml = "";
    var $table = "blogs";
    var $rowField = "blog";
    
    function stream_open ($path, $mode, $options, &$opened_path) {
        include_once("MDB2.php");
        $this->db = MDB2::Connect($GLOBALS['BX_config']['dsn']);
        if (MDB2::isError($this->db) ) {
            trigger_error("could not connect to DB in dbsax stream", E_USER_WARNING);
            return false;
        }
        if( strpos($mode,"r") !== false) {
            $this->res = $this->db->query("select * from " . $this->table);
            if (MDB2::isError($this->res) ) {
                trigger_error("Query returned error in dbsax stream", E_USER_WARNING);
                return false;
            }
            $this->numRows = $this->res->numRows();
            
        } else {
            trigger_error("Only read support currently in dbsax stream", E_USER_ERROR);
            return false;
        }
        return true;
    }
    
    function stream_read($count) {
        while (strlen($this->xml) < $count) {
            $xml = $this->getXml();
            if (strlen($xml) == 0) {
                break;
            }
            $this->xml .= $xml;
        }
        $ret = substr($this->xml,0,$count);
        $this->xml = substr($this->xml,$count);
        return $ret;
    }
    
    function stream_write($data) {
        return false;
    }
    function stream_stat() {
        return array();
    }
    
    function getXml() {
        if ($this->dbposition == 0) {
            $xml = '<'.'?xml version="1.0" ?>';
            $xml .= '<'.$this->table.'>';
            
        }
        
        $row = $this->res->fetchRow(MDB2_FETCHMODE_ASSOC);
        $this->dbposition++;
        if (is_array($row)) {
            $xml .= '<'.$this->rowField.'>';
            //TODO: Better errorhandling;
            
            
            foreach($row as $key => $value) {
                $xml .= '<'.$key.'>';
                $xml .= $value;
                $xml .= '</'.$key.'>';
            }
            
            
            $xml .= '</'.$this->rowField.'>'."\n";
        } 
        if ($this->dbposition == $this->numRows) {
            $xml .= '</'. $this->table . '>';   
        }
        return $xml;
    }
    
    function stream_eof() {
        if ($this->dbposition >= $this->numRows) {
            return true;
        } else {
            return false;
        }
    }
    
    function stream_close() {
        $this->db->disconnect();
        return true;
    }
    
}



?>
