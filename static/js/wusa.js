/**
 * WUSA - Web USage Analysis 
 * (C) 2012 by The Kellerkinder | Lukas Plattner & Stefan Rottensteiner
 */
(function() {				
	/**
	 * Internal debug function
	 * @param mixed text
	 */         
	var debug = function(text){
		/* Deactivated for now */
		/* console.log(text); */
	} 
	/**                                
	 * The Traker Him-Self
	 */               
	var wuTrakk = function(){		   
		/**
		 * Return the user agents viewport dimension as a string like "1280x780"         
		 * @access private
	     * @return string                                 
	     */
	    var getUAViewportDimension = function(){
	      try {
	        var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName("body")[0],
	          x=w.innerWidth||e.clientWidth||g.clientWidth,
	          y=w.innerHeight||e.clientHeight||g.clientHeight;
	          return parseInt(x) + "x" + parseInt(y);
	       } catch(ex){
	        return '0x0';
	       }                   
	    };
	    /**
	     * Return the users prefered language or "xx-xx" if not found.
	     * @return String 
	     */
	    var getUALanguage = function(){
	      try {
	        return ( navigator.language || navigator.browserLanguage || navigator.userLanguage ).toLowerCase();
	      } catch(ex){
	        return 'xx-xx';
	      }    
	    }
	    /**
	     * Return the documents char-set or "x" if not found
	     * @return String
	     */
	    var getDocCharset = function(){
	    	try {
	    		return document.characterSet || document.charset || "x";
	    	} catch(ex){
	    		return 'x';
	    	}
	    }
	    /**
	     * Return the screen dimension as a string like "1280x780"         
	     * @access private
	     * @return string                                 
	     */                                         
	    var getUAScreenDimension = function(){
	      try {
	        return screen ? parseInt(screen.width) + "x" + parseInt(screen.height) : "0x0";
	      } catch(ex){
	        return '0x0';
	      }                       
	    };
	    /**
	     * Return the user agent color depth          
	     * @access private                      
	     */                               
	    var getUAColorDepth = function(){
	      try {    
	        return screen ? parseInt(screen.colorDepth) : "0";
	      } catch(ex){
	        return '0';
	      }                   
	    }
	    /**
	     * Send the tracking request
	     * @param string cmd	The command. Mostly something like "trackPageview"
	     * @param object params	Command specific params. For example for sending a "trackEvent" 
	     */
	    var sendData = function(cmd,params){
	        /* Set protocoll */
	        var src = (document.location.protocol.indexOf("https") === 0 ? 'https' : 'http') + "://";
	        /* Set hostname */
	        src+=document.location.host;
	        /* Set path where the requested is awaited */
	        src+="/pixel/cp.php?cmd=" + cmd + "&";
			/* Ensure the connection ID */
	        ensureIDS(values);
	        /* Ensure current user time */
	        ensureWUUT(values);
	        /* Add params */
	        for (k in values){
	            v = values[k];
	            if (typeof v == "string" || typeof v == "number" ) {
	              src+=encodeURIComponent(k) + "=" + encodeURIComponent(v) + "&";                                                          
	            }
	        }
	        if (typeof params != "undefined" && params){
            src+="_wucmdp=" + encodeURIComponent(JSON.stringify(params));        
	        }
	        /* Up, up and away! */
	        var i = new Image();
	        i.src = src;              
	    }
	    /**
	     * Map given left key name to the right side.          
	     * @access private                      
	     */                               
	    var keyMapper = {
			accountID       : "_wuaid",
			_trackPageview  : "trackPageview",
			_trackEvent     : "trackEvent"
	    };
	    keyMapper.map = function(k){
	      return ( typeof this[k] == "undefined" ? k : this[k] );        
	    }     
	    /**
	     * Triggered action: Track a page view / page impression
	     */
	    var trapTrackPageview = function(params){
        var eventParams = parameterMerge({
            url      : ""
          },params);     
        if (eventParams["url"] == "") eventParams = null;      
	      sendData( 'pageView', eventParams );
	    }   
      /**
       * Merges the given <params> into <defaultParams>  
       * @param Object defaultParams
       * @param Object params
       * @return Object       
       */                                 
      var parameterMerge = function( defaultParams, params ) {
        if (!params) return defaultParams;
         
        var result = defaultParams,
          paramName,
          paramValue,
          paramIndex = -1; 
          
        for (paramName in defaultParams ) {
          paramIndex++;
          if (typeof params[paramIndex] != "undefined" ) {
            result[paramName] = params[paramIndex];  
          } else if ( typeof params[paramName] != "undefined" ) {
            result[paramName] = params[paramName];  
          } else {
            result[paramName] = defaultParams[paramName]; 
          }          
        }
        return result;      
      }                   
	    /**
	     * Triggered action: Track an event
	     * like "Start playing video" etc.
	     */
	    var trapTrackEvent = function(params){        
        var eventParams = parameterMerge({
            category      : "",
            action        : "",
            label         : "",
            value         : "",
            noninteraction: false
          },params);         
	      sendData( 'event', eventParams );
	    }   
	    /**
	     * Set a cookie
	     * @param string name Name of the cookie
	     * @param string value The plain cookie value
	     * @param int expDays Optional days from now when the cookie should expire. Defaults to "Never"  
	     */
	    var setCookie = function(name, value, expSecs ){
 			var now 	= new Date(),
				expMSecs= (expSecs ? expSecs *1000 : false),
				value   = escape(value) ;
			if(expMSecs) {
				now.setTime( now.getTime() + expMSecs );
				value+="; expires=" + now.toUTCString();
			}
			value = value + "; path=/";
			document.cookie = name + "=" + value;
	    }                      
	    /**
	     * Get a cookie
	     * @param string name Nae of the cookie
	     * @return String containung the plain cookie content
	     */
	    var getCookie = function(name){
	      var i,x,y,cookies=document.cookie.split(";");
	      for (i=0;i<cookies.length;i++){
	        x = cookies[i].substr(0,cookies[i].indexOf("="));
	        y = cookies[i].substr(cookies[i].indexOf("=")+1);
	        x = x.replace(/^\s+|\s+$/g,"");
	        if (x == name) {
	          return unescape(y);
	        }
	      }
	      return false;
	    }   	        
		/**
		 * Ensure client and visit cookies and values
	     * @param array values
		 */
		var ensureIDS = function(values){
			var _wuvid 		= getCookie("__wuvid"),
				_wucid 		= getCookie("__wucid"),
				newVisit	= false;
			if (_wuvid && _wucid) {
				_wuvid 		= _wuvid.split("-");
				// One more page view for current visit
				_wuvid[4] 	= parseInt(_wuvid[4])+1;
			} else {
				// Start a new visit / session
				newVisit 	= true;
				_wuvid 		= generateCIDPrefix(values,_wucid);
				_wuvid[4] 	= Math.floor(( new Date()).getTime() / 1000 );
				// First page view of this visit
				_wuvid[4] 	= 1;
			}
			if (_wucid) {
				_wucid 		= _wucid.split("-");
				// One more visit for this client
				_wucid[4]   = _wuvid[4];
				if (newVisit) _wucid[5] = parseInt(_wucid[5])+1;
				_wucid[6] 	= parseInt(_wucid[6])+1;
			} else {
				console.log("Start new client ID");
				_wucid 		= [].concat(_wuvid);
				_wucid[5]   = 1;
				_wucid[6]   = 1;
			}
			
			values["_wuvid"] = _wuvid.join("-");
			setCookie("__wuvid", values["_wuvid"], 60*30 );

			values["_wucid"]  = _wucid.join("-");
			setCookie("__wucid", values["_wucid"] , 60*60*24*365*2 );
		}
		var generateCIDPrefix = function (values,_wucid){
			if (_wucid) {
				var prefix = _wucid.split("-");
				prefix = [ prefix[0],prefix[1],prefix[2],prefix[3] ];
				return prefix;
			}
			return [
				// Tracker Hash - Part I
				parseInt(values['_wuaid'].match(/^[A-Z]{2}\-(\d+)\-.+$/)[1]),
				// Tracker Hash - Part II
				parseInt(values['_wuaid'].match(/^[A-Z]{2}\-.+\-(\d+)$/)[1]),
				// Client ID starting time in seconds
				Math.floor(( new Date()).getTime() / 1000 ),
				// Addition random value
				Math.floor(Math.random() * 100000 )
			];
		}
	    /**
	     * Normaly called right bevore the data is beeing sent to
	     * ensure, we have the clients current time.
	     * @param array values
	     */    
	    var ensureWUUT = function(values){
	    	values["_wuut"] = ( new Date() ).getTime();
	    	return values["_wuut"];
	    }
	    /**
	     *  If an value is set, the key is checked against this
	     *  array. So if you for example push an "_trackPageview",
	     *  this key is trapped and handled by the specified method.                        
	     * @access private           
	     */                     
	    var dataTraps = {
	      trackPageview  : trapTrackPageview,
	      trackEvent     : trapTrackEvent
	    };
	    /**
	     * The parameter values for the requests
	     * @access private                      
	     */                     
		var values = {
			/* Web User Trak Version */
			_wuv 	: "0.1.1",
			  /* Account ID */
			_wuaid	: "WU-XXX-Y",
			  /* Connection ID */
			_wucid	: false,
			  /* Session ID */
			_wusid	: false,			
			  /* Document title */
			_wudt 	: document.title || "",
			  /* Document referrer */            
			_wudr	: document.referrer || "" ,
			  /* Document path */
			_wudp	: window.location.pathname || "",
			  /* Document host name */
			_wuhn	: window.location.hostname || "",
			/* Document char-set */
			_wudcs	: getDocCharset(),
			  /* User agent screen dimension */                                      
			_wusr	: getUAScreenDimension(),
			  /* User agent view port dimension */
			_wuvp	: getUAViewportDimension(), 
			  /* User agent color depth */
			_wucd	: getUAColorDepth(),
			  /* User agent language preference */
			_wuul : getUALanguage(),      
			  /* User agent time in UTC */
			_wuut	: ( new Date() ).getTime() 						
		};
	    /**
	     * Set a parameter or trigger a reaction
	     * @param k mixed
	     * @param v mixed                       
	     * @access public           
	     */                     
		this.set = function(k,v){
			if ( "object" == typeof k) {
				for (p in k){
					if ("object" == typeof k[p]) {
						v = k[p].slice(1);
						this.set(k[p][0],v);                
					}
				}
			} else if ( "string" == typeof k) {
		        /* Map key */
		        k = keyMapper.map(k);              
		        if (typeof dataTraps[k] == "function") {
		          /* Given key is a data trap, so call it and process the data */
		          typeof dataTraps[k].call(this,v);
		        } else if (k != "_wucid"){
		          /* Just set the data */
		          values[ k ]= ( "undefined" == v ? "" : ( "object" == typeof v && v.length <= 1 ? v[0] : v ) );
		        }				
			}
		}          
		/**
		 * Return the internal values
		 * @return Object
		 */
		this.get = function(){
	      return values;
	    }
	} /* End of Tracker Object */
	
	/* Create a new instance of the Web User Traker */         
	window._wut = new wuTrakk();
	if ( typeof window._wuq != "undefined"){
		/* Nice, got some data. So set and process it */
		window._wut.set.call( window._wut, window._wuq );
	}        
	/* Overwrite the nativ push() method, so we can react on it */
	window._wuq.push = function(){ window._wut.set.call( window._wut, arguments ); }	
}());