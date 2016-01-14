$(function() {


	// get data from hidden div#json-content
	var data = $.parseJSON($("#json-content").html());

	/////////////////////
	//Helper functions //
	/////////////////////

	/* calc scaling value for bulk width */
	function getBulkWidth(value) {
		return (100*value) < 50? widthScaleLeft(100-(2*(50-(100*value)))) : widthScaleRight(((100*value) -50)+(100*value)-150)
	}
	/* calc scaling value for tranform width */
	function getTransformWidth(value) {
		return ((100*value) < 50? widthScaleLeft(2*(50-(100*value))) : widthScaleRight(0));
	}

	/////////////////
	// PROPERTIES  //
	/////////////////

	/* SVG Leinwand */
	var width = 500,
	    height = 600;

	/* svgMitte: Chart */
	var middleWidth = 500,
		middleHeight = data.big5.length * 100;

	var widthScaleLeft = d3.scale.linear()
	                .domain([100, 0])
	                .range([0, width/2]),
		widthScaleRight = d3.scale.linear()
	                .domain([0, 100])
	                .range([width/2, width]),
		middleBackground = "#e6e7e6";

	/* Welche Balken anzuzeigen sind, 0 oder 1*/
	// [group, course]
	var bulkMode = [0, 0];


	/* Balken Size */
	var bulkHeight = 50,
		bulksPerCat = d3.sum(bulkMode) +1; // + 1, weil 3 Balken, nur zwei aber im BulkMode

	/* Balken Farben Domain */
	// var color = d3.scale.linear()
	//             .domain([0, 100])
	//             .range(["red", "blue"]);
	var color = ["blue", "green", "purple"];

	/* svgLinks: Beschriftung linke Seite */
	var leftHeight = data.big5.length * 100,
		leftWidth = 150,
		leftBackground = "#fafafa";

	/* init tip: inactive, actually not working in moodle */
	// tip = d3.tip()
	// 	.attr('class', 'd3-tip')
	// 	.html(function(d) { return d.captions.minText; });
	/////////////
	//Leinwand //
	/////////////
	var svg = d3.select("div#gf_chart")
				   .append("svg")
				   .attr("width", width)
				   .attr("height", height)
				   .style("overflow", "visible");

	/* 3 Bereiche: links Beschriftung / Chart / rechts Beschriftung */
	var svgMitte = svg.append("g").attr("id", "mitte"),
		svgLinks = svg.append("g").attr("id", "links"),
		svgRechts = svg.append("g").attr("id", "rechts");

	////////////////
	// SVG Links  //
	////////////////
	svgLinks
		.attr("transform", "translate(0, 20)")
		/* background */
		.append("rect")
		.attr("width", leftWidth)
		.attr("height", leftHeight)
		.attr("fill", leftBackground);
		//.attr("stroke", "blue");

	/* links: Label Group Boxes */
	var gBoxLeft = svgLinks.selectAll("g")
				.data(data.big5)
				.enter()
					.append("g");

	/* Box um Labels herum */
	var textBox = gBoxLeft
					.append("rect")
					.attr("fill", "rgba(220,160,140,0.0)")
					.attr("width", leftWidth)
					.attr("height", bulkHeight)
					.attr("y", function (d, i) { return (i * (bulkHeight * 2) + 11); })
					.attr("stroke", "black")
					.attr("stroke-dasharray", "10 5");
					// add tooltip
					// .on("mouseover", tip.show)
					// .on("mouseout", tip.hide);

	/* activate tooltip */
	// gBoxLeft.call(tip);
	/* Labels */
	var barsText = gBoxLeft
					.append("text")
					.attr("fill", "black")
					/* position im SVG */
					.attr("x", 0)
					.attr("y", function (d, i) {
						return ((i * (bulkHeight * 2)) // how many categories
						+ 15 // axis Height
						);
					})
					.attr("text-anchor", "middle")
					/* position innerhalb der box */
					.attr("dx", leftWidth/2)
					.attr("dy", (bulkHeight / 2))
					/* font format */
					.style("font", "1.2em sans-serif")
					.text(function (d) {return d.name });



	//gBoxLeft.selectAll("g rect")
	// .attr('width', 50)
	// .attr('height', 50)
	// .attr('y', 50)
	// .attr('x', 50)


	//gBoxLeft.call(tip);

	/////////////////
	// SVG Rechts  //
	/////////////////
	svgRechts
		.attr("transform", "translate("+(leftWidth+middleWidth)+", 20)")
		/* background */
		.append("rect")
		.attr("width", leftWidth)
		.attr("height", leftHeight)
		.attr("fill", leftBackground);

	/* links: Label Group Boxes */
	var gBoxRight = svgRechts.selectAll("g")
				.data(data.big5)
				.enter()
					.append("g");

	/* Box um Labels herum */
	var textBoxRight = gBoxRight
					.append("rect")
					.attr("fill", "rgba(220,160,140,0.0)")
					.attr("width", leftWidth)
					.attr("height", bulkHeight)
					.attr("y", function (d, i) { return (i * (bulkHeight * 2) + 11); })
					.attr("stroke", "black")
					.attr("stroke-dasharray", "10 5");
					//add tooltip
					// .on("mouseover", tip.show)
					// .on("mouseout", tip.hide);

	/* Labels */
	var barsTextRight = gBoxRight
					.append("text")
					.attr("fill", "black")
					/* position im SVG */
					.attr("x", 0)
					.attr("y", function (d, i) {
						return ((i * (bulkHeight * 2)) // how many categories
						+ 15 // axis Height
						);
					})
					.attr("text-anchor", "middle")
					/* position innerhalb der box */
					.attr("dx", leftWidth/2)
					.attr("dy", (bulkHeight / 2))
					/* font format */
					.style("font", "1.2em sans-serif")
					.text(function (d) {return d.name });
	/* tooltips aktivieren: actually inactive */
	// svg.call(tip);

	///////////////
	// SVG Mitte //
	///////////////
	svgMitte
		.attr("transform", "translate("+leftWidth+", 0)")
		/* background color */
		.append("rect")
		.attr("transform", "translate(0, 20)")
		.attr("width", middleWidth)
		.attr("height", middleHeight)
		.attr("fill", middleBackground);

	/* Achse entwerfen */
	var axisLeft = d3.svg.axis()
			    .scale(widthScaleLeft)
				.orient("top");

	var axisRight = d3.svg.axis()
			    .scale(widthScaleRight)
				.orient("top");

	/* linke Hälfte der Achse anfuegen */
	svgMitte.append("g")
		.attr("transform", "translate(0, 20)")
		.call(axisLeft);
	/* rechte Hälfte der Achse anfuegen */
	svgMitte.append("g")
		.attr("transform", "translate(0, 20)")
		.call(axisRight);


		var userBars;
		/* add user bulks */
		function addUserBulks() {
				userBars = svgMitte.append("g")
							 .attr("class", "userBars")
							 .selectAll("rect")
							 .data(data.big5)
							 .enter()
							 	.append("g")
							 	.append("rect")
								.attr("width", function (d) {return getBulkWidth(d.values.user); })
								.attr("height", bulkHeight / bulksPerCat)
								.attr("fill", color[0])
								.attr("y", function (d, i) {return i * (bulkHeight*2); })
								/* nach links wachsen oder rechts */
								.attr("transform", function (d) {return "translate(" + getTransformWidth(d.values.user) + ", 30)"; } );

		}

		/* remove user bulks */
		function removeUserBulks() {
			svgMitte.select(".userBars").remove();
		}

	/* add group bulks */
	function addGroupBulks() {
		bulkMode[0] = 1;
		var groupBars = svgMitte.append("g")
						 .attr("class", "groupBars")
						 .selectAll("rect")
						 .data(data.big5)
						 .enter()
							.append("g")
							.append("rect")
							.attr("width", function (d) {return getBulkWidth(d.values.group); })
							.attr("height", bulkHeight / bulksPerCat)
							.attr("fill", color[1])
							.attr("y", function (d, i) {
								return ((i * (bulkHeight*2) +30)
								+ (bulkMode[1] == 1 ? (bulkHeight / bulksPerCat)*2 : (bulkHeight / bulksPerCat))); })
							/* nach links wachsen oder rechts */
							.attr("transform", function (d) {return "translate(" + getTransformWidth(d.values.group) + ", 0)"; } );
	}
	/* remove group bulks */
	function removeGroupBulks() {
		bulkMode[0] = 0;
		svgMitte.select(".groupBars").remove();
	}
	/* checkbox event function */
	function checkGroupBulks() {
		if($("#gf-group-checkbox").is(":checked")) {
			addGroupBulks();
		} else {
			removeGroupBulks();
		}
	}

	/* add global bulks */
	function addGlobalBulks() {
		bulkMode[1] = 1;
		var globalBars = svgMitte.append("g")
						 .attr("class", "globalBars")
						 .selectAll("rect")
						 .data(data.big5)
						 .enter()
							.append("g")
							.append("rect")
							.attr("width", function (d) {return getBulkWidth(d.values.course); })
							.attr("height", bulkHeight / bulksPerCat)
							.attr("fill", color[2])
							.attr("y", function (d, i) {
								return ((i * (bulkHeight*2) +30)
								+ (bulkMode[0] == 1 ? (bulkHeight / bulksPerCat)*2 : (bulkHeight / bulksPerCat))); })
							/* nach links wachsen oder rechts */
							.attr("transform", function (d) {return "translate(" + getTransformWidth(d.values.course) + ", 0)"; } );
	}

	/* remove global bulks */
	function removeGlobalBulks() {
		bulkMode[1] = 0;
		svgMitte.select(".globalBars").remove();
	}

	/* checkbox event */
	function checkGlobalBulks() {
		if ($("#gf-global-checkbox").is(":checked")) {
			addGlobalBulks();
		} else {
			removeGlobalBulks();
		}
	}

	// correct bulk sizes
	function checkBulkSizes() {
		bulksPerCat = bulksPerCat = d3.sum(bulkMode) +1;
		// val.selectAll("g g rect").data(data.big5)
		// 	.attr("height", bulkHeight / bulksPerCat)
		// 	.attr("y", function (d, i) {
		// 		return ((i * (bulkHeight*2) +30)
		// 		+ (bulkMode[0] == 1 ? (bulkHeight / bulksPerCat)*2 : (bulkHeight / bulksPerCat)));
		// 	});
		removeUserBulks();
		activeBulks = [bulkMode[0], bulkMode[1]];
		if (activeBulks[0] == 1) removeGroupBulks();
		if (activeBulks[1] == 1) removeGlobalBulks();

		addUserBulks();
		if (activeBulks[0] == 1) {addGroupBulks();}
		if (activeBulks[1] == 1) {addGlobalBulks();}
	}

	// events
	$("#gf-group-checkbox").on("click", function () {
		/* switch box */
		checkGroupBulks();
		/* correct bulk sizes */
		checkBulkSizes();
	});

	$("#gf-global-checkbox").on("click", function () {
		/* switch box */
		checkGlobalBulks();
		/* correct bulk sizes */
		checkBulkSizes();
	})


	/* init userBulks */
	addUserBulks();


});
