<?php


/* NOT FINISHED YET, but a start... */
$h = new microextractor("hreview.xml");
var_dump($h->extractHReview());

$h = new microextractor("hreview2.xml");
var_dump($h->extractHReview());

/*$h = new microExtractor("hreview4.xml");
var_dump($h->extractHReview());
*/
$h = new microextractor("vcard2.xml");
var_dump($h->extractHCard());


class microextractor {
    
    
    public function __construct($xml) {
        
        $this->dom = new domdocument();
        $this->dom->load($xml);
        $this->xp = new domxpath($this->dom);
    }
        
        
    public function extractHreview() {
        
        
        $hrevs = $this->xp->query("//*[contains(concat(' ', normalize-space(@class), ' '),' hreview ') ]");
        
        foreach ($hrevs as $hrev) {
            
            $rev = array();
            $rev['description'] = $this->extractData('description',$hrev);
            $rev['summary'] = $this->extractData('summary',$hrev);
            $rev['rating'] = $this->extractData('rating',$hrev);
            $rev['version'] = $this->extractData('version',$hrev);
            $rev['dtreviewed'] = $this->extractData('dtreviewed',$hrev);
            $rev['fnorg'] = $this->extractData('fn org',$hrev);
            $rev['item'] = $this->extractData('item',$hrev);
            $rev['reviewer'] = $this->extractData('reviewer',$hrev);
            
            $vc = $this->getFirstNode('vcard',$hrev);
            if ($vc) {
                $rev['hcard'] = $this->extractHCard($vc);
            }
            
        }
        return $rev;
    }
    
    public function extractHCard($ctx = null) {
        if ($ctx) {
            
            $hrevs = $this->xp->query("ancestor-or-self::*[contains(concat(' ', normalize-space(@class), ' '),' vcard ') ]", $ctx);
        } else {
            $hrevs = $this->xp->query("//*[contains(concat(' ', normalize-space(@class), ' '),' vcard ') ]");
        }
        
        $cards = array();
        
        foreach ($hrevs as $hrev) {
            $card = array();
            $card['org'] = $this->extractData('org',$hrev);
            $card['fn'] = $this->extractData('fn',$hrev);
            $card['url'] = $this->extractAttribute('url','href',$hrev);
            $card['title'] = $this->extractData('title',$hrev);
            $cards[] = $card;
        }
        
        return $cards;
            
        
    }
    
    function extractAttribute($classname,$attribute,$hrev) {
        $desc = $this->getFirstNode($classname,$hrev);
        if ($desc) {
            return $desc->getAttribute($attribute);
        } else {
            return null;
        }
    }
    
    function extractData($classname,$hrev) {
        $desc = $this->getFirstNode($classname,$hrev);
        if ($desc) {
            $xml = '';
            if ($desc->localName == "abbr") {
                return $desc->getAttribute("title");
            }
            
            foreach ($desc->childNodes as $c) {
                $xml .= $this->dom->saveXML($c);
            }
            return $xml;
        } else {
            return null;
        }
        
    }
    
    function getFirstNode($classname,$hrev) {
        $desc = $this->xp->query(".//*[contains(concat(' ', normalize-space(@class), ' '),' $classname ')]",$hrev);
        if ($desc->length > 0) {
            return $desc->item(0);
        } else {
            return null;
        }
    }
}
