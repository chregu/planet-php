<?php

// +----------------------------------------------------------------------+
// | popoon                                                               |
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
// | Author: Carl Hasselskog <Carl@calle.nu>                              |
// |         Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+

class popoon_helpers_lang {
    
    /**
     *  tries to negotiate the preferred language the browser sends
     *    does not take the q= parameter into account right now
     *
     *  and "de" is the same as "de-ch" (everyting after the - is stripped)
     */
    static function preferredBrowserLanguage ($possibleLangs, $default = null) {
        $acceptedLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        if ($acceptedLang != "") {
            // form an array of preferred languages
            $languages = str_replace(" ", "", $acceptedLang);
            $languages = explode(",", $languages);
            foreach ($languages as $lang) {
                //strip everything before - or ;
                if ($pos = strpos($lang, "-")) {
                    $lang = substr($lang,0,$pos);
                } else if ($pos = strpos($lang,";")) {
                    $lang = substr($lang,0,$pos);
                }
                //check if it's in the array of possible langs
                if (in_array($lang,$possibleLangs)) {
                   return $lang;
                }
            } 
        } 
        // if default language is set, return this
        if ($default) {
            return $default;
        } 
        
        // otherwise take the first one from the array of possible languages.
        return $possibleLangs[0];
    }
}