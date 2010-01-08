<?php
/**
  Copyright 2009 Liip AG

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

   Code partly copied from Padraic Brady's Zend_Feed_Pubsubhubbub
*/

/**
    Example

    $noti = new lx_notifier();

    $blogname = "kleiner test";
    $url = "http://example.org/";
    $checkurl = $url;

    $mainfeed = $url ."rss.xml";
    $topicurls = array(
            $url . 'atom.xml',
            $mainfeed
    );
    $services = array("http://rpc.pingomatic.com/");
    $noti->addWeblogUpdates(array($mainfeed), $services, $url, $blogname, $checkurl );

    $clouds = array('http://rpc.rsscloud.org:5337/rsscloud/ping');
    $noti->addRssClouds($topicurls,$clouds);

    $hubs = array("http://pubsubhubbub.appspot.com");
    $noti->addPubSubHubs($topicurls, $hubs);

    $supid = getSUPId();
    $noti->addSup("http://friendfeed.com/api/public-sup-ping?supid=" . $supid . "&url=" . $mainfeed);

    $noti->notifyAll();

 */

class lx_notifier {

    protected $multi = null;
    protected $chs = array();

    public function __construct() {
        $this->multi = curl_multi_init();

    }

    public function addPubSubHubs($topicUrls = array(), $huburls = array("http://pubsubhubbub.appspot.com")) {
        $params = array();
        $params[] = 'hub.mode=publish';
        $topics = $topicUrls;
        foreach ($topics as $topicUrl) {
            $params[] = 'hub.url=' . urlencode($topicUrl);
        }
        $paramString = implode('&', $params);
        $success = 0;
        foreach ($huburls as $huburl) {
            $success += $this->_notifyPubSub($paramString, $huburl);
        }
        return $success;
    }

    protected function _notifyPubSub($postdata, $huburl) {
        $ch = $this->getDefaultCurl($huburl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        return $this->addCurl($ch);
    }


    public function addWeblogUpdates($topicurls, $pingurls, $url, $name,  $checkUrl) {
        foreach ($topicurls as $topicurl) {
            foreach ($pingurls as $pingurl) {
                $this->addWeblogUpdate($topicurl, $pingurl, $url, $name, $checkUrl);
            }
        }
    }

    protected function addWeblogUpdate($topicurl, $pingurl, $url, $name,  $checkurl) {
        $ch = $this->getDefaultCurl($pingurl);
        $postdata =

        '<?xml version="1.0" encoding="UTF-8"?>
        <methodCall>
        <methodName>weblogUpdates.extendedPing</methodName>
        <params>
        <param>
        <value><string>' . htmlspecialchars($name) . '</string></value>
        </param>
        <param>
        <value><string>' . htmlspecialchars($url) . '</string></value>
        </param>
        <param>
        <value><string>' . htmlspecialchars($checkurl) . '</string></value>
        </param>
        <param>
        <value><string>' . htmlspecialchars($topicurl) . '</string></value>
        </param>
        </params>
        </methodCall>';

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        return $this->addCurl($ch);

    }

    public function addSup($supurl) {
        $ch = $this->getDefaultCurl($supurl);
        return $this->addCurl($ch);
    }

    public function addRssClouds($topicurls, $cloudurls) {
        foreach ($cloudurls as $url) {
            foreach ($topicurls as $topicurl) {
                $this->addRssCloud($topicurl, $url);
            }
        }
    }

    public function addRssCloud($topicurl, $cloudurl) {
        $ch = $this->getDefaultCurl($cloudurl);
        $postdata = 'url=' . urlencode($topicurl);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        return $this->addCurl($ch);
    }


    protected function getDefaultCurl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        return $ch;
    }

    protected function addCurl($ch) {
        if ($this->multi) {
            curl_multi_add_handle($this->multi, $ch);
            $this->chs[] = $ch;
        } else {
            curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            if ($info['http_code'] != 204) {
                return 0;
            }
        }
        return 1;
    }

    public function notifyAll() {
        if ($this->multi) {
            $running = null;
            //execute the handles
            curl_multi_exec($this->multi, $running);
            while ($running > 0) {
                usleep(100000); // 1/10 sec
                curl_multi_exec($this->multi, $running);
            }

        }

        foreach ($this->chs as $ch) {
            //error_log(var_Export(curl_getinfo($ch), true));
            curl_multi_remove_handle($this->multi, $ch);
            curl_close($ch);
        }
        curl_multi_close($this->multi);

    }

}