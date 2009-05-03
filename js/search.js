  function niceURL () {
            var value=document.getElementById("searchtext").value;
            value = webroot + "search/" + encodeURI(value);
			value = value.replace(/\+/g,"%252B");
			
			window.location.href = value
            return false;
          }

function addEngine()
    {
        if ((typeof window.sidebar == "object") && (typeof window.sidebar.addSearchEngine == "function"))
        {
            window.sidebar.addSearchEngine(
                "http://www.planet-php.net/planet-php.src",    /* engine URL */
                "http://www.planet-php.net/themes/img/planet-php.jpg",       /* icon URL */
                "Planet-PHP",                                  /* engine name */
                "Web" );                                       /* category name */
        }
        else
        {
            alert("Mozilla M15 or later is required to add a search engine.");
        }
    }

