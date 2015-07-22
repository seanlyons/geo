var neighborhoods_layer;
var neighborhoods_count = -1;
var map;
var geojson;
var L;
var x = 37.729223;
var y = -122.444564;

function debug(str) {
    //console.log(str);
}

function add_ele(obj, ele) {
    debug('add_ele: obj is ', obj, ' ele is ' + ele);
    for (i in obj) {
        debug('add_ele: i ', i, 'obj[i] = ' +obj[i]+', ele = ', ele);
        if (obj[i] == ele) {
            debug('add_ele: found!');
            return;
        }
    }
    obj.push(ele);
    debug('add_ele: done!');
}

function del_ele(obj, ele) {
    debug('del_ele: obj is ', obj, ' ele is ' + ele);
    for (i in obj) {
        debug('del_ele: i ', i, ' = ', ele);
        if (obj[i] == ele) {
            obj.splice(i, 1);
            debug('del_ele: found!');
        }
    }
    debug('del_ele: returning obj ', obj);
    return;
}

function contains(haystack, needle) {
    for (i in haystack) {
        if (haystack[i] == needle) {
            return true;
        }
    }
    return false;
}


function generate_cookie_id(len) {
    if ($.cookie('id')) {
        debug('64', $.cookie('hit'));
        return;
    }
    len = (len || 29);
    
    //Generate a high-entropy string to stand in for the UID. This produces something like 29^62 (4.6e90)
    //combinations per second, which is random enough for me. Can add OAuth or something later if needed.
    alphanum = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    now = Math.floor((new Date).getTime() / 1000);
    str = '';
    for (i = 0; i <= len; i++) {
        str += alphanum[Math.floor((Math.random() * alphanum.length) + 1)];
    }
    str += '_' + now;
    $.cookie('id', str);
    empty = [];
    $.cookie('hit', JSON.stringify(empty));
}

//$.removeCookie('id');
generate_cookie_id();

function initialize(self_coords) {
    L.mapbox.accessToken = 'pk.eyJ1IjoiZHVlbmRlIiwiYSI6Ijk0NDg5NTU2M2Q3OGU2ZmZhMWNjYzg3MjMyOWM1YmVlIn0.dKQrnkU5ALH3ezZ9pZ4Yeg';
    sf_coords = [37.765,-122.44];
    map = L.mapbox.map('map-one', 'mapbox.streets').setView(sf_coords, 13);
	
    default_css = { fillColor: '#444', weight: 1 };
	neighborhoods_layer = L.geoJson(geojson, default_css).addTo(map);
    
	$.getJSON("sf2.json", function(json) {
        $.each(json, function(x) {
            json[x].features[0]['id'] = x;
            n = neighborhoods_layer.addData(json[x]);
        });
    
        hits = JSON.parse( $.cookie('hit') );

        debug('hits!');
        debug(hits);
        debug('!hits!');    
               
        n.eachLayer(function(distinct) {
            debug('distinct > ');
            debug(distinct.feature.id + ' => ' + distinct.feature.properties.name);
            if (contains(hits, distinct.feature.id)) {
                toggle_neighborhood_color(distinct);
            }
            
        });
    });
}

function get_location() {
    navigator.geolocation.getCurrentPosition(obtain_x_y);
}

function obtain_x_y(position) {
	x = position.coords.latitude;
	y = position.coords.longitude;
	L.marker([x, y]).addTo(map);
}

function toggle_neighborhood_color(layer) {
	off = '#444';
	on = '#f00';
	if (!layer.options.fillColor
	|| !layer.options
	|| !layer.options.fillColor) {
		return;
	}
    id = layer.feature.id;
    if (!$.cookie('hit')) {
        window.alert('how are you missing the hit cookie?');
        return;
    }
    hits = JSON.parse( $.cookie('hit') );
    
	if (layer.options.fillColor == off) {
		layer.setStyle({color:on, fillColor:on});
        add_ele(hits, id);
	} else {
        del_ele(hits, id);
		layer.setStyle({color:off, fillColor:off});
	}
    $.cookie('hit', JSON.stringify( hits ));
}

function in_neighborhood(x_y, callback_name) {
	map.eachLayer(function(layer) {
		callback_name(layer);
	});
}

function locate_neighborhood(arg) {
	//debug("FNORD! " + arg);
}

get_location();
initialize([x, y]);
in_neighborhood([x, y], locate_neighborhood);

neighborhoods_layer.on('click', function(e) {
    neighborhood_name = e.layer.feature.properties.name;
    map.eachLayer(function(layer) {
        feature = layer['feature'];

        if (feature
        && feature.properties
        && feature.properties.name
        && neighborhood_name == feature.properties.name) {
            toggle_neighborhood_color(layer);
            debug("Hey! that's "+ feature.id +", " + neighborhood_name);
        }
    });
    debug($.cookie('hit'));
});