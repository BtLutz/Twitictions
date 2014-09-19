var graph;
var xPadding = 30;
var yPadding = 30;

var values = [{}];

var c;

function initializeGraph(TMP_KEY, DATA)
{
	graph = document.getElementById("graph");
	var c = graph.getContext('2d');

	var json = $.parseJSON(DATA);
	for (i=0;i<json.results.length;i++) {
		var sentimate_score = json.results[i].Sentiment_score;
		var XYArray = {
			X: i,
			Y: Number(sentimate_score)
		};
		values.push(XYArray);
	}

	c.lineWidth = 2;
	c.strokeStyle = "#000";
	c.font = "italic 8pt sans-serif";
	c.textAlign = "center";

	c.beginPath();
	c.moveTo(xPadding, 0);
	c.lineTo(xPadding, graph.height - yPadding);
	c.lineTo(graph.width, graph.height - yPadding);
	c.stroke();

	for (var i = 1; i < values.length; i++)
	{
		c.fillText(i, getXpixel(i), graph.height - yPadding + 20);
	}

	c.textAlign = "right";
	c.textBaseline = "middle";

	for (var i = -1; i <= 1; i++)
	{
		c.fillText(i, xPadding - 10, getYpixel(i+1));
	}

	c.strokeStyle = "#f00";
	c.beginPath();
	c.moveTo(getXpixel(0), getYpixel(values[0].Y+1));

	for (var i = 0; i < values.length; i++)
	{
		c.lineTo(getXpixel(i), getYpixel(values[i].Y+1));
	}

	c.stroke();


	c.strokeStyle = "#f00";
	c.beginPath();
	c.moveTo(9, 0.28326);

	c.lineTo(10, .43);

	c.stroke();

	c.fillStyle="#333";

	for (var i = 0; i < values.length; i++)
	{
		c.beginPath();
		c.arc(getXpixel(i), getYpixel(values[i].Y + 1), 4, 0, Math.PI * 2, true);
		c.fill();
	}
}

function getSlope()
{
	var dataSetLength = values.length;
	var slope = (values[dataSetLength-1].Y - values[dataSetLength-2].Y) / (values[dataSetLength-1].X - values[dataSetLength-2].X);
	console.log(values);
	return slope;
}

function getMaxY() 
{
	var max = 0;

	for (var i = 0; i < values.length; i++)
	{
		if (values[i].Y > max) {
			max = values[i][Y];
		}

		max += 10 - max % 10;
		return max;
	}

}

function getXpixel(val)
{
	return ((graph.width - xPadding) / values.length) * val + (xPadding * 1.5);
}

function getYpixel(val)
{
	return graph.height - (((graph.height - yPadding) / 3) * val) - yPadding*3.2;
}

function canvasState(canvas)
{
	this.valid = false; // When set to true, the canvas will redraw all the elements
	this.shapes = []; // Collection of object to be shown
	this.mouseDown = false; // Keep track of if the user is currently clicking on an object
	this.selection = null; // Keep track of what object is visible
	this.mousePositionX = null; // Current mouse cursor position over Canvas element X
	this.mousePositionY = null; // Current mouse cursor position over Canvas element Y

	var myState = this;
	// Fixes a problem where double clicking causes text to get selected on the canvas
	canvas.addEventListener('selectstart', function(e) {e.preventDefault(); return false;});
	// Allows us to detect if currently over an object
	canvas.addEventListener('mouseDown', function(e) {
		var mouse = myState.getMouse(e);
		var mx = mouse.x;
		var my = mouse.y;
		var shapes = myState.shapes;
		var l = shapes.length;
		for (var i = l-1; i >= 0; i--)
		{
			if (shapes[i].contains(mx, my))
			{
				var mySel = shapes[i];
				myState.mouseDown = true;
				myState.valid = false;
				myState.selection = mySel;
				return;
			}
		}

		if (myState.selection) {
			myState.selection = null;
			myState.valid = false; // Clear selection border
		}
	}, true);
	// Detect change in mouse position
	canvas.addEventListener('mouseMove', function(e) {
		var mouse = myState.getMouse(e);
		myState.selection.x = mouse.x; // Current X position of mouse
		myState.selection.y = mouse.y; // Current Y position of mouse
		myState.valid = false;
	}, true);
	// Detect mouse up
	canvas.addEventListener("mouseup", function(e) {
	myState.mouseDown = false;
	}, true);
}

	

	/*var data = [{
	    "description": "Predicted Twitter",
	    "21:42": .5345,
	    "17:34": .7234,
	    "22:50": -.1031,
	    "23:12": -.4232,
	    "18:24": .0001
	}, {
	    "description": "Actual Twitter",
	    "16:43": -.0001,
	    "8:22": .4232,
	    "13:24": .1031,
	    "6:59": -.7234,
	    "3:21": -.5345
	    
	}]

	//Width and height
	var margin = {
	    top: 20,
	    right: 20,
	    bottom: 30,
	    left: 50
	},
	width = 660 - margin.left - margin.right,
	    height = 440 - margin.top - margin.bottom;


	var parseDate = d3.time.format("%y").parse;

	//Scales and Axis
	var x = d3.time.scale().range([0, width]);

	var y = d3.scale.linear().range([height, 0]);

	var xAxis = d3.svg.axis()
	    .scale(x)
	    .orient("bottom")
	    .tickFormat(d3.format("0000"))
	    .tickValues([2013, 2012, 2011, 2010, 2009, 2008, 2006]);

	var yAxis = d3.svg.axis()
	    .scale(y)
	    .orient("left")
	    .ticks(5);

	//Organise the data
	var seriesYears = d3.keys(data[0]).filter(function (key) {
	    return (key !== "description")
	})

	data.forEach(function (d, i) {
	    d.global = seriesYears.map(function (name) {
	        return {
	            year: name.slice(4),
	            value: +d[name]
	        }
	    });
	    //console.log(d.global[i].year);
	});

	console.log(parseDate("10-10-2013"));


	//Domain
	x.domain(d3.extent(seriesYears, function (d) {
	    return +d.slice(4);
	}));
	y.domain([-1, 1]); // set the y domain to go from 0 to the maximum value of d.close


	// Define the line
	var line = d3.svg.line() // set 'valueline' to be a line
	.x(function (d, i) {
	    return x(d.year)
	})
	    .y(function (d, i) {
	    return y(d.value)
	})

	// Adds the svg canvas
	var svg = d3.select("#graph4") // Explicitly state where the svg element will go on the web page (the 'body')
	.append("svg") // Append 'svg' to the html 'body' of the web page
	.attr("width", width + margin.left + margin.right) // Set the 'width' of the svg element
	.attr("height", height + margin.top + margin.bottom) // Set the 'height' of the svg element
	.append("g").attr("class", "graph4").attr("transform", "translate(" + margin.left + "," + margin.top + ")"); // in a place that is the actual area for the graph

	var g = svg.selectAll('g').data(data).enter().append('g').attr('class', 'globalWarmingLines');

	// Add the valueline path.
	g.append("path") // append the valueline line to the 'path' element
	.attr("class", function (d, i) {    
	    return "sw2 line " + "color" + i + "stroke";
	}) // apply the 'line' CSS styles to this path
	.attr("d", function (d) {
	    return line(d.global);
	})

	svg.append("g") // Add the X Axis
	.attr("class", "x axis").attr("transform", "translate(0," + height + ")").call(xAxis);

	svg.append("g") // Add the Y Axis
	.attr("class", "y axis").call(yAxis);

	loadData();

	function loadData(key)
	{
		if (!key) {
			key = "mhacks";
		}
		$.ajax({
			url:"http://www.joetorraca.com/tweetingController/app/tweetController.php?route=load&hashtag="+encodeURIComponent("#")+key,
			success:function(g) {
				var json = $.parseJSON(g);
				var length = json.results.length;
				for (i=0;i<length;i++) {
					console.log(json.results[i]);
				}
			}
		});
	}*/