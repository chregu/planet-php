<?xml version="1.0" encoding="utf-8" ?>
<html>
 <head>
  <title>Planet PEAR administration</title>
 </head>
 <body>
<h1>mi casa es su casa</h1>
{error}
<!-- BEGIN feed.list -->
<table class="feedlist" border="1">
     <caption>Existing feeds</caption>
     <!-- BEGIN list.entry -->
     <tr><td>{id}</td><td>{link}</td><td><a href="admin.php?delete={id}">delete</a></td></tr>
     <!-- END list.entry -->
</table>
<p/>
<!-- END feed.list -->
<!-- BEGIN feed.delete -->
<fieldset class="deletefeed"><legend>Delete a feed</legend>
<p>Do you really want to delete feed #{id}?</p>
<p><a href="admin.php?delete={id}&amp;deleteReally=yes">yes</a>
<a href="admin.php">no</a></p>
</fieldset>
<!-- END feed.delete -->
<!-- BEGIN feed.add -->
<form method="post" action="admin.php">
    <fieldset class="addfeed">
        <legend>Add a feed</legend>
        <label for="feedurl">Feed URL:</label> <input type="text" name="feedurl" value=""/><br/>
        <input type="submit" value="Submit"/>
    </fieldset>
</form>
<!-- END feed.add -->
<p><a href="admin.php?logout">logout</a></p>
</body>
</html>
