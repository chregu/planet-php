<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
   <?php echo PROJECT_NAME_HR; ?>
  </title>
  <link rel="icon" href="/themes/<?php echo $BX_config['theme']; ?>/favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="/themes/<?php echo $BX_config['theme']; ?>/favicon.ico" type="image/x-icon" />
  <link href="/themes/<?php echo $BX_config['theme']; ?>/css/style.css" rel="stylesheet" type="text/css" />
  <link rel="alternate" type="application/rss+xml" title="RSS" href="http://feeds.feedburner.com/PLANETPEAR" />
  <link rel="alternate" type="application/atom+xml" title="Atom" href="http://feeds.feedburner.com/PLANETPEAR-ATOM" />
  <link rel="outline" type="text/x-opml" title="OPML Feed list" href="/opml" />
  <link rel="search" type="application/opensearchdescription+xml" title="Planet PEAR search" href="/opensearch.xml" />
 </head>
 <body>
  <div id="head">
   <a href="/"><img src="/themes/planet-pear/img/pear-planet.png" width="305" height="70" hspace="30" alt="Planet PEAR" title="Planet PEAR" border="0" /></a>
   <div id="topnavi">
    All news in one place
   </div>
  </div>

  <div id="middlecontent">

<?php foreach ($entries as $entry): ?>
   <div class="box">
    <fieldset>
     <legend><a href="<?php echo $entry['blog_link']; ?>"><?php echo $entry['blog_author']; ?></a></legend>
     <a href="<?php echo $entry['link']; ?>" class="blogTitle"><?php echo $entry['title']; ?></a>
     (<?php echo $entry['dc_date']; ?>)
     <div class="feedcontent">
      <p>
        <?php
        if (!empty($entry['content_encoded'])) {
          echo $entry['content_encoded'];
        } else {
          echo $entry['description'];
        }
        ?>
      </p>

     </div><a href="<?php echo $entry['link']; ?>">Link</a>
    </fieldset>
   </div>
<?php endforeach; ?>

   <div id="pageNavi">

<?php if ($nav['next'] !== null || $nav['prev'] !== null): ?>

    <fieldset>
     <legend>More Entries</legend>
<?php if ($nav['next'] !== null): ?>
<span style="float: right;"><a href="/index/<?php echo $nav['next']; ?>">Next 10 Older Entries</a></span>
<?php endif; ?>
<?php if ($nav['prev'] !== null): ?>
<span style="float: left;"><a href="/index/<?php echo $nav['prev']; ?>">Previous 10 Newer Entries</a></span>
<?php endif; ?>

<br />
    </fieldset>

<?php endif; ?>

   </div>
  </div>
  <div id="rightcol">

   <div class="menu">
    <fieldset>
     <legend>Search Planet PEAR</legend>
     <form onsubmit="niceURL(); return false;" name="search" method="get" action="/" id="search">
      <input id="searchtext" type="text" name="search" /><input class="submit" type="submit" value="Go" />
     </form><a id="searchbarLink" href="javascript:addEngine()" name="searchbarLink">Mozilla Searchbar</a>
    </fieldset>
   </div>

   <div class="menu">
    <fieldset>
     <legend><a href="/opml">Blogs</a></legend>
    <?php foreach ($blogs as $blog): ?>
    <a href="<?php echo $blog['link']; ?>" class="blogLinkPad"><?php echo $blog['author']; ?></a>
    <?php endforeach; ?>
    </fieldset>
   </div>
   <div class="buttons">

    <fieldset>
     <legend>Links</legend>
     <a href="#"><img border="0" alt="RSS 0.92" src="/images/rss092.gif" width="80" height="15" /></a>
     &#160; <a href="http://feeds.feedburner.com/PLANETPEAR"><img border="0" alt="RDF 1." src="/images/rss1.gif" width="80" height="15" /></a><br />
     <a href="http://feeds.feedburner.com/PLANETPEAR-ATOM"><img border="0" alt="Atom Feed" src="/images/atompixel.png" width="80" height="15" /></a> &#160; <br />
     <a href="http://www.php.net/"><img border="0" alt="PHP5 powered" src="/images/phppowered.png" width="80" height="15" /></a> &#160; <a href="http://pear.php.net/"><img alt="PEAR" border="0" src="/images/pearpowered.png" width="80" height="15" /></a>
    </fieldset>

   </div>
   <div class="menu">
    <fieldset>
     <legend>Planetarium</legend><a class="blogLinkPad" href="http://drupal.org/planet/">Drupal</a><a class="blogLinkPad" href="http://www.planetmysql.org/">MySQL</a><a class="blogLinkPad" href="http://www.planet-php.net/">PHP</a>
    </fieldset>
   </div>
   <div class="buttons">

    <fieldset>
     <legend>Link the Planet</legend> <code>&lt;a href="http://www.planet-pear.org/"&gt;Planet PEAR&lt;/a&gt;</code>
    </fieldset>
   </div>
   <div class="menu">
    <fieldset>

     <legend>Contact</legend><a class="blogLinkPad" href="http://twitter.com/klimpong">&#64;klimpong</a>
    </fieldset>
   </div>
   <div class="menu">
    <fieldset>
     <legend>FAQ and Code</legend>
     <a class="blogLinkPad" href="http://blog.liip.ch/archive/2005/05/26/planet-php-faq.html">Planet PHP FAQ</a>
     <a class="blogLinkPad" href="#" onclick="alert('To be fixed.');">Add your PHP blog</a>
     <a class="blogLinkPad" href="http://github.com/till/planet-php/">Code on GitHub</a>
    </fieldset>
   </div>
   <div class="menu">
    <fieldset>
     <legend>Sponsors</legend>
     <div class="nnbe">
      Hosted by <a class="inlineBlogLink" href="http://till.klampaeckel.de">Till Klampaeckel</a><br />
      Logo designed by <a class="inlineBlogLink" href="http://viebrock.ca/">Colin Viebrock</a>, enhanced by <a href="http://cweiske.de">Christian Weiske</a>.
     </div>
    </fieldset>
   </div>
  </div><script language="JavaScript" src="/js/search.js" type="text/javascript">
</script>
 </body>
</html>
