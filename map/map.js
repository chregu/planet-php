function startMap() {
	webroot = "http://planet.blogug.ch/";
	lon =  8.22;
	lat =  46.95;
	var z = 8;
	lang = 'de';
	
	
	
	var coord = window.location.hash.split(",");
	if (coord.length == 4) {
		coord[0] = coord[0].replace(/#/,"");
		mapWidget = new MapWidget(document.getElementById('mapContainer'), {
			viewArea: [ coord[0], coord[1], coord[2],coord[3]],
			apiuser: 'blogug',
			idcRcvURL : webroot + '_blank.html', 
			lang: lang
		}); 
		
		document.getElementById("permalink").setAttribute("href","http://planet.blogug.ch/map/#"+coord[0]+ "," +  coord[1]  + "," + coord[2]  + "," +  coord[3]);

	} else {
		
		mapWidget = new MapWidget(document.getElementById('mapContainer'), {
			lon: lon,
			lat: lat,
			z: z,
			
			apiuser: 'blogug',
			idcRcvURL : webroot + '_blank.html', 
			lang: lang
		});
	}
	mapWidget.addEventHandler('load', mapLoaded);
	
	
	mapWidget.addEventHandler('mapmove', function(evt) {
		BLx = evt.area[0];
		BLy = evt.area[3]; 
		TRx = evt.area[2];
		TRy = evt.area[1]; 
		
		
		document.getElementById("permalink").setAttribute("href","http://planet.blogug.ch/map/#"+Math.ceil(BLx * 10000)/10000 + "," +  Math.floor(TRy * 10000)/10000  + "," +  Math.floor(TRx * 10000)/10000  + "," +  Math.ceil(BLy * 10000)/10000);
	});
}

function mapLoaded() {
	mapWidget.enableLayer({feed: feedid2, refresh: false, id: layerids} );
	
	
	
	
}

function toggleLayer(e) {
	
	if (e.checked) {
		mapWidget.disableLayer({feed: feedid2, refresh: false, id: ''} );
		mapWidget.enableLayer({feed: feedid2, refresh: false, id: 'blogug_0_day'} );
	} else {
		mapWidget.enableLayer({feed: feedid2, refresh: false, id: ''} );
		//mapWidget.disableLayer({feed: feedid2, refresh: false, id: 'blogug_0_day'} );
	}		
}
