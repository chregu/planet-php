<?php


class Cache {
    
     public static function get($id) {
        return file_get_contents("cache.xml");
     }
}