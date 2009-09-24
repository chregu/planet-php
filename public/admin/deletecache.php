<?php
//baad style
chdir("../tmp/");
  exec("find cache/outputcache/ -mindepth 1 | xargs rm -rf");
  exec("find cache/outputcache.meta/  -mindepth 1 -mtime +7 | xargs rm -rf");
  exec("find magpie/ -mindepth 1 -mmin +360 | xargs rm -rf");
