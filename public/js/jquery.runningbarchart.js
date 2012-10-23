/**
 * runningBarChar -  A usefull and funny running chart jQuery plug-in
 * @copyright 2012, Rottensteiner Stefan - rottensteiner.stefan@gmx.at
 * @version 0.1.4
 * @license http://www.opensource.org/licenses/mit-license.php  - MIT
 * @license http://www.opensource.org/licenses/gpl-license.php  - GPL
 * Online compressed with http://jscompress.com/
 */
(function( $ ){
	/* Plug-In version */
	var version = "0.1.4";

	/* Default options */
	var defaultOptions = {
		direction: "rtl",
		rows: {
			maxValue  : 100,
			stepWidth : 20
		},
		columns : {
			number: 10
		}
	}

	/* Private method interface */
	var _addRow = function( value, label ){
		var data    	= $(this).data("runningBarChart"),
			ra          = data.ra,
			label       = value + "",
			rowCount    = ra.find("div.row").length,
			rowHeight   = ( rowCount ? Math.floor(   ( 1 / ( 1 + rowCount)) * 100 ) : 100 );
		ra.prepend('<div class="row" data-value="'+value+'"><div class="label">'+label+'</div></div>');
		ra.find("div.row").css("height", rowHeight + "%" );
	}
	/**
	 * Functions used for "Right To Left" movment
	 */
	var functionsRTL = {
		/**
		 * Shift the columns from right to left, remove the
		 * first left column.
		 */
		shiftColumns: function($this,ca,scw){
			ca.animate({
				marginLeft: (-1 *(scw) ) + "px"
			}, "fast", function(){
				$this.find(".column:first-child").remove();
				ca.css("marginLeft","auto");
			});
		},
		/**
		 * Insert a new column at the right side
		 */
		insertColumn: function(ca, node ){
			return ca.append(node);
		},
		/**
		 * Get the right most column content
		 */
		getWorkingColumnContent: function(ca){
			return ca.find(".column:last-child .content");
		},
		/**
		 * Get the right most column
		 */
		getWorkingColumn: function(ca){
			return ca.find(".column:last-child");
		}
	};
	/**
	 * Functions used for "Left To Right" movement
	 */
	var functionsLTR = {
		/**
		 * Shift the columns from left to right, remove the
		 * last right column.
		 */
		shiftColumns: function ($this,ca,cw){
			ca.css({
				marginLeft: (-1*cw)+ "px"
			});
			ca.animate({
				marginLeft: "0px"
			}, "fast", function(){
				$this.find(".column:last-child").remove();
			});
		},
		/**
		 * Insert a new column at the left side
		 */
		insertColumn: function(ca, node ){
			return ca.prepend(node);
		},
		/**
		 * Get the left most column content
		 */
		getWorkingColumnContent: function(ca){
			return ca.find(".column:first-child .content");
		},
		/**
		 * Get the left most column
		 */
		getWorkingColumn: function(ca){
			return ca.find(".column:first-child");
		}
	};
	/* Public method interface */
	var methods = {
		/**
		 * Initialize
		 * @param Object options
		 */
		init : function( options ) {
			var $this 	= $(this),
			 	data 	= $this.data('runningBarChart');

			// If the plugin hasn't been initialized yet
			if ( ! data || typeof data == "undefined" || !data.isInitialized ) {

				// Do the setup stuff here
				var localOptions = $this.data('runningbarchart') || {};
				
				if ( localOptions && typeof localOptions == "object" ) {
					// Given options => local options => default options
					data = $.extend( true, {}, defaultOptions , localOptions, options );
				} else {
					// Given options => default options
	            	data = $.extend( true, {}, defaultOptions , options );
				}
				// minValue not yet supported
	            data.rows.minValue = data.rows.stepWidth;

				// Remember the the target element
				data.target = $this;
				// The rows will go there
				$this.append('<div class="row-area"><div class="content" ></div></div>');
				// The data columns will go there
				$this.append('<div class="column-area"><div class="content" ></div><div class="groups" ></div></div>');
	              // Short cutes for faster access
				data.ca = $this.find(".column-area > .content");
				data.ra = $this.find(".row-area > .content");

				data.direction 	= ( data.direction == "rtl" ? "rtl" : "ltr" );
				data.fns 		= ( data.direction == "rtl" ? functionsRTL : functionsLTR );

				$this.data("runningBarChart", data);

				data.target.addClass( data.direction );

				// Create rows
	            for(var rowValue = data.rows.minValue; rowValue <= data.rows.maxValue; rowValue+=data.rows.stepWidth ){
	              _addRow.call( this, rowValue );
	            }

				// Create columns
				var colValues 			= typeof data.columns.values == "array" ? data.columns.values.reverse() : [] ,
					colCounter 			= colValues.length >= 1 ? colValues.length : data.columns.number,
					defaultValue        = ( data.columns.multivalue ? [0] : 0 );
				data.columns.number  	= colCounter;
				data.columns.cssWidth	= (   Math.floor((1 / colCounter )* 1000) ) / 10 + "%";

				while (colCounter){
					methods["addColumn"].call( this, typeof colValues[colCounter] == "undefined" ? 0 : typeof colValues[colCounter] );
					colCounter--;
				}

				var w = parseFloat(data.fns.getWorkingColumn(data.ca).width());
				data.columns.cssWidth = w+"px";
				data.ca.find(".column").css("width", data.columns.cssWidth );
				data.ca.css("width", (parseFloat( data.ca.width() ) + w ) + "px" );

				data.forceRTLColumnShift = ( data.direction == "rtl" );
			}
		},
		/**
		 * Remove various stuff if the "runningBarChart" is destroyed
		 */
		destroy : function( ) {
			 var $this	= $(this),
			     data 	= $this.data('runningBarChart');
			 // Namespacing RTW
			 $(window).unbind('.runningBarChart');
			 $this.removeData('runningBarChart');
	 		},
		/**
		 * Add column and set the value. The left most coolumn will be
		 * removed if necessary.
		 * @param number colValue The value this column should represent
		 */
		addColumn : function (colValue) {
			var data    		= $(this).data("runningBarChart"),
				ca      		= data.ca;
			colValue = Math.max(0, parseFloat(colValue));

			data.fns.insertColumn(
				ca,
				'<div class="column" style="width: '+(data.columns.cssWidth)+';"><div class="content low" data-value-y="'+colValue+'" style="height:0%"><div class="label low">'+colValue+'</div></div></div>'
			);
			var scw = data.fns.getWorkingColumn(ca).width(),
				fcw = ca.find(".column").length * scw;
			if (data.forceRTLColumnShift || fcw > ca.width()) {
				data.fns.shiftColumns( $(this),ca,scw );
			}
			this.skipAnim = true;
			methods["setColumValue"].call( this, colValue );
		},
		/**
		 * Set the value of a colum. Auto-extends the rows if necessary.
		 * @param number colValue The value this column should represent
		 */
		setColumnValue : function(value){
			var data    	= $(this).data("runningBarChart"),
				ca      	= data.ca,
				cc     		= data.fns.getWorkingColumnContent(ca), //ca.find(".column:last-child .content"),
				cl          = cc.find(".label"),
				maxValue	= data.rows.maxValue,
				stepWidth 	= data.rows.stepWidth,
				currentValue= parseFloat(cc.attr("data-value-y")),
				height  	= 0;
			value = Math.max(0, parseFloat(value));

	//		if ( currentValue != value ) {
				cc.attr("data-value-y", value );
				cl.html(value);

				if ( value <= maxValue ) {
					if (this.skipAnim == true) {
						cc.css("height", ((value / maxValue ) * 100) + "%").fadeIn();
					} else {
						cc.stop(false, true).animate({height: ((value / maxValue ) * 100) + "%" });
					}
				} else {
					/* Have to add rows, because the new value is bigger than
					 * the current "maxValue".
					 */
					// Number of missing rows
					var rowsNeeded = Math.ceil( (value - maxValue ) / stepWidth );
					while (rowsNeeded) {
						// Add the new rows and expand the "max y value"
						maxValue = ( data.rows.maxValue = data.rows.maxValue + stepWidth ) ;
						addRow.call( this, data.rows.maxValue );
						rowsNeeded--;
					}
					// Adjust content height of the other columns to the
					// new maximum y value.
					ca.find(".column > .content").each(function(){
						var $this 	= $(this),
							height  = ( parseFloat($this.attr("data-value-y")) / maxValue ) * 100;
						$this.css("height", height + "%" );
					});
				}

				cl.removeClass("high");
				cl.removeClass("low");
				cc.removeClass("high");
				cl.removeClass("low");

				if (value <= stepWidth) {
					cl.addClass("low");
					cc.addClass("low");
				} else if (value >= ( maxValue - stepWidth*0.5 ) ) {
					cl.addClass("high");
					cc.addClass("high");
				}
	//		}
			this.skipAnim = false;
		},
		/**
		 * Returns the value of the current working column
		 * @return number
		 */
		getColumnValue : function(){
			var data    	= $(this).data("runningBarChart"),
				cc     		= data.fns.getWorkingColumnContent(data.ca);
			return parseFloat(cc.attr("data-value-y"));
		},
		/**
		 * Adds a new row, if the given value is greater than the current rows.maxValue.
		 * Caution: Does not check any integrity.
		 * @param number value
		 */
		addRow : function(value){
			var data    	= $(this).data("runningBarChart"),
				maxValue	= data.rows.maxValue,
				stepWidth 	= data.rows.stepWidth,
				adjustCols  = false;

			if ( !isNaN(value) && value > maxValue  ) {
				data.rows.maxValue = value;
				_addRow.call( this, value );
				adjustCols = true;
			} else if ( isNaN(value) ) {
				data.rows.maxValue = maxValue + stepWidth;
				_addRow.call( this, data.rows.maxValue );
				adjustCols = true;
			}

			if ( adjustCols ) {
				data.ca.find(".column > .content").each(function(){
					var $this 	= $(this),
						height  = ( parseFloat($this.attr("data-value-y")) /data.rows.maxValue ) * 100;
					$this.css("height", height + "%" );
				});
			}
		}
	};
	/* Legacy stuff */
	methods["setColumValue"] =  methods["setColumnValue"];

$.fn.runningBarChart = function( method ) {
	if ( methods[method] ) {
		var settings = Array.prototype.slice.call( arguments, 1 );
		if ( method.indexOf("get") === 0 ) {
			var methodResults = [];
			this.each(function(){
		    	methodResults.push( methods[method].apply( this, settings ) );
			});
			return ( methodResults.length == 1 ? methodResults[0] : methodResults ) ;
		} else {
			return this.each(function(){
		    	return methods[method].apply( this, settings );
			});
		}

	} else if ( typeof method === 'object' || ! method ) {
		var settings = arguments;
		return this.each(function(){
			return methods.init.apply( this, settings );
		});
	} else {
		$.error( 'Method ' +  method + ' does not exist on jQuery.runningBarChart V.' + version );
	}
};
})( jQuery );