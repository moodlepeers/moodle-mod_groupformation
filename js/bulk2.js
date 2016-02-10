
// d3.json("example_evaluation_data.json", function(data) {
	// get data from hidden div#json-content
	var data = $.parseJSON($("#json-content").html());
	var buildChart = function buildChart() {
			// remove svg for resize-effect
			$("#gf_chart svg").remove();




/////////////////////
//Helper functions //
/////////////////////

			/* bulk height size */
			var bulkHeight = 50;
				//bulksPerCat = d3.sum(bulkMode) +1; // + 1, cause we have 3 different bulks, an user bulk is always active

			/* calc scaling value for bulk width */
			function getBulkWidth(value) {
				return (100*value) < 50? widthScaleLeft(100-(2*(50-(100*value)))) : widthScaleRight(((100*value) -50)+(100*value)-150)
			}
			/* calc scaling value for tranform width (for right horizontal positioning) */
			function getTransformWidth(value) {
				return ((100*value) < 50? widthScaleLeft(2*(50-(100*value))) : widthScaleRight(0));
			}

/////////////////
// PROPERTIES  //
/////////////////

			/* SVG Leinwand */
			var width 		= $("div.gf_pad_content").width(),
			/* labels section */
			labelsSection 	= 30,
			/* scale bar height (x-achsis) */
			scaleBarHeight 	= 20;


			/* set div width */
			$("#gf_chart").width(width);

			/* svgMitte: Chart */
			var middleWidth 	= width * 0.6,
				middleHeight 	= (data.big5.length * (bulkHeight * 2)) - 20;

			/* set master-div height */
			$("#gf_chart").height(middleHeight + labelsSection + scaleBarHeight);

			var widthScaleLeft = d3.scale.linear()
			                .domain	([100, 0])
			                .range	([0, middleWidth/2]),
				widthScaleRight = d3.scale.linear()
			                .domain	([0, 100])
			                .range	([middleWidth/2, middleWidth]);

			svgHeight = middleHeight + labelsSection + scaleBarHeight; // 20 = Scale + Border height


			/* Balken Farben Domain */
			// var color = d3.scale.linear()
			//             .domain([0, 100])
			//             .range(["red", "blue"]);
			var color = ["blue", "green", "purple"];

			/* svg sidebars labels */
			var sideBarHeight 	= middleHeight,
				leftWidth 		= width * 0.2,
				leftBackground 	= "none";


/////////////
//Leinwand //
/////////////
			var svg = d3.select("div#gf_chart")
						   .append	("svg")
						   .attr	("width", width)
						   .attr	("height", svgHeight)
						   .style	("overflow", "visible")
						   .attr	("font-size", 10);

			/* 4 Bereiche: oberer Rand ScaleBar / links Beschriftung / Chart / rechts Beschriftung */
			var svgLabels	= svg.append("g").attr("id", "labels"),
				svgLinks	= svg.append("g").attr("id", "links"),
				svgRechts	= svg.append("g").attr("id", "rechts"),
				svgMitte	= svg.append("g").attr("id", "mitte");

			// labels section
			svgLabels.append("rect").attr("transform", "translate(" + leftWidth + ",0 )")
				.attr("width", middleWidth).attr("height", "30").attr("fill", "none");

			// user Label
			var userLabel = svgLabels.append("g").attr("transform", "translate(" + leftWidth + ",10 )");
			userLabel.append("rect").attr("width", 30).attr("height", 10).attr("fill", "blue")
				.on("click", function() {$("g.userBars g rect").toggle();});
			userLabel.append("text").attr("dx", 50).attr("dy", 8).text("user").attr("font-size", 10);
			// group label
			var groupLabel = svgLabels.append("g").attr("transform", "translate(" + (leftWidth+100) + ",10 )");
			groupLabel.append("rect").attr("width", 30).attr("height", 10).attr("fill", "green")
				.on("click", function() {$("g.groupBars g rect").toggle();});
			groupLabel.append("text").attr("dx", 50).attr("dy", 8).text("group").attr("font-size", 10);
			// global label
			var globalLabel = svgLabels.append("g").attr("transform", "translate(" + (leftWidth+200) + ",10 )");
			globalLabel.append("rect").attr("width", 30).attr("height", 10).attr("fill", "purple")
				.on("click", function() {$("g.globalBars g rect").toggle();});
			globalLabel.append("text").attr("dx", 50).attr("dy", 8).text("global").attr("font-size", 10);
			// add info icon
			svgLabels.append("image")
				.attr("width", 20)
				.attr("height", 20)
				.attr("xlink:href", "data:image/png;base64,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")
				.attr("transform", "translate(" + (leftWidth+middleWidth-25) + ",5 )")
				.on("click", function() {
					$("#gf-modal").modal();
				});

			// styling extra
			var verticalLines = d3.select("div#gf_chart svg");
			for (i = 1; i < 10; i++) {
				verticalLines
					.append("line")
					.attr("x1", leftWidth + i*(middleWidth/10))
					.attr("y1", function(d, i) {return (labelsSection + scaleBarHeight); })
					.attr("x2", leftWidth + i*(middleWidth/10))
					.attr("y2", function(d, i) {return (svgHeight); })
					.attr("stroke",(i == 5) ? "black" : "grey")
					.attr("stroke-width", (i == 5) ? 3 : 1);
			}

////////////////
// SVG left  //
////////////////
			svgLinks
				.attr("transform", "translate(0, "+(labelsSection + scaleBarHeight)+")")
				/* background */
				.append("rect")
				.attr("width", leftWidth)
				.attr("height", sideBarHeight)
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
							.attr("fill", leftBackground)
							.attr("width", leftWidth)
							.attr("height", bulkHeight)
							.attr("y", function (d, i) { return (i * (bulkHeight * 2) + 11); });
							// .attr("stroke", "black")
							// .attr("stroke-width", 0.5);
							// .attr("stroke-dasharray", "10 5");


			/* Labels */
			var barsText = gBoxLeft
							.append("text")
							.attr("fill", "black")
							/* position im SVG */
							.attr("x", 0)
							.attr("y", function (d, i) {
								return ((i * (bulkHeight * 2)) // how many categories
								+ (scaleBarHeight-5) // axis Height
								);
							})
							.attr("text-anchor", "middle")
							/* position innerhalb der box */
							.attr("dx", leftWidth/2)
							.attr("dy", (bulkHeight / 2))
							/* font format */
							.style("font", "1.2em sans-serif")
							.text(function (d) {return d.name });



/////////////////
// SVG Rechts  //
/////////////////
			svgRechts
				.attr("transform", "translate("+(leftWidth+middleWidth)+", "+ (labelsSection + scaleBarHeight)+")")
				/* background */
				.append("rect")
				.attr("width", leftWidth)
				.attr("height", sideBarHeight)
				.attr("fill", leftBackground);

			/* links: Label Group Boxes */
			var gBoxRight = svgRechts.selectAll("g")
						.data(data.big5)
						.enter()
							.append("g");

			/* Box um Labels herum */
			var textBoxRight = gBoxRight
							.append("rect")
							.attr("fill", leftBackground)
							.attr("width", leftWidth)
							.attr("height", bulkHeight)
							.attr("y", function (d, i) { return (i * (bulkHeight * 2) + 11); });
							// .attr("stroke", "black")
							// .attr("stroke-width", 0.5);
							// .attr("stroke-dasharray", "10 5");

			/* Labels */
			var barsTextRight = gBoxRight
							.append("text")
							.attr("fill", "black")
							/* position im SVG */
							.attr("x", 0)
							.attr("y", function (d, i) {
								return ((i * (bulkHeight * 2)) // how many categories
								+ (scaleBarHeight-5) // axis Height - half of font-size
								);
							})
							.attr("text-anchor", "middle")
							/* position innerhalb der box */
							.attr("dx", leftWidth/2)
							.attr("dy", (bulkHeight / 2))
							/* font format */
							.style("font", "1.2em sans-serif")
							.text(function (d) {return d.name });

///////////////
// SVG Mitte //
///////////////
			svgMitte
				.attr("transform", "translate("+leftWidth+", "+labelsSection+")")
				/* background color */
				.append("rect")
				.attr("transform", "translate(0, 20)")
				.attr("width", middleWidth)
				.attr("height", middleHeight)
				.attr("fill", "#fbfbfb")
				.attr("stroke", "#d1cdcd")
				.attr("stroke-width", "1px");

			/* Achse entwerfen */
			var axisLeft = d3.svg.axis()
					    .scale(widthScaleLeft)
						.orient("top");

			var axisRight = d3.svg.axis()
					    .scale(widthScaleRight)
						.orient("top");

			/* linker Hälfte Achse anfuegen */
			svgMitte.append("g")
				.attr("transform", "translate(0, "+scaleBarHeight+")")
				.call(axisLeft);
			/* rechter Hälfte Achse anfuegen */
			svgMitte.append("g")
				.attr("transform", "translate(0, "+scaleBarHeight+")")
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
										.attr("height", bulkHeight / 3)
										.attr("fill", color[0])
										.attr("y", function (d, i) {return (i * (bulkHeight*2)
										   + (bulkHeight / 3)); })
										/* nach links wachsen oder rechts */
										.attr("transform", function (d) {return "translate(" + getTransformWidth(d.values.user) + ", 30)"; } );

				}

				/* remove user bulks */
				function removeUserBulks() {
					svgMitte.select(".userBars").remove();
				}

			/* add group bulks */
			function addGroupBulks() {
				var groupBars = svgMitte.append("g")
								 .attr("class", "groupBars")
								 .selectAll("rect")
								 .data(data.big5)
								 .enter()
									.append("g")
									.append("rect")
									.attr("width", function (d) {return getBulkWidth(d.values.group); })
									.attr("height", bulkHeight / 3)
									.attr("fill", color[1])
									.attr("y", function (d, i) {
										return ((i * (bulkHeight*2)) +30); })
									/* nach links wachsen oder rechts */
									.attr("transform", function (d) {return "translate(" + getTransformWidth(d.values.group) + ", 0)"; } );
			}

			/* add global bulks */
			function addGlobalBulks() {
				var globalBars = svgMitte.append("g")
								 .attr("class", "globalBars")
								 .selectAll("rect")
								 .data(data.big5)
								 .enter()
									.append("g")
									.append("rect")
									.attr("width", function (d) {return getBulkWidth(d.values.course); })
									.attr("height", bulkHeight / 3)
									.attr("fill", color[2])
									.attr("y", function (d, i) {
										return ((i * (bulkHeight*2) +30)
										+ (bulkHeight / 3)*2 ); })
									/* nach links wachsen oder rechts */
									.attr("transform", function (d) {return "translate(" + getTransformWidth(d.values.course) + ", 0)"; } );
			}

			/* check if groupbox activated and activate bulk if yes */
			addGroupBulks();
			/* init userBulks */
			addUserBulks();
			/* check if groupbox activated and activate bulk if yes */
			addGlobalBulks();

	} // build chart function

///////////////////////
// Modal Definition  //
///////////////////////
			// configure modal
			var modalContent = d3.select("#gf-modal")
				.append		("div")
				.attr		("class", "modal-dialog")
					.append		("div")
					.attr		("class", "modal-content");

			modalContent.append("div").attr("class", "modal-header");

			modalContent.select(".modal-header")
				.append		("button")
				.attr		("class", "close")
				.attr		("data-dismiss", "modal")
				.attr		("aria-label", "Close")
					.append		("span")
					.attr		("aria-hidden", "true")
					.html		("&times;");

			modalContent.select(".modal-header")
				.append		("h4")
				.attr		("class", "modal-title")
				.attr		("id", "myModalLabel")
				.text		("Info-Box");

			var modalAccordion = modalContent
				.append		("div")
				.attr		("class", "modal-body")
				.append		("div")
				.attr		("id", "gf-modal-accordion");

			modalPan = modalAccordion.selectAll("div .panel .panel-default")
				.data(data.big5)
				.enter()
					.append		("div")
					.attr		("class", "panel panel-info");
			/* panel heading */
			modalPan
				.append("div")
				.attr("class", "panel-heading")
				.attr("role", "tab")
				.attr("id", function(d, i) {return "mHeading"+i;})
					.append("h4")
					.attr("class", "panel-title")
						.append("a")
						.attr("role", "button")
						.attr("data-toggle", "collapse")
						.attr("data-parent", "#gf-modal-accordion")		// close all other panels
						.attr("href", function(d, i) {return "#mCollapse"+i;})
						.attr("aria-expanded", "true")
						.attr("aria-controls", function(d, i) {return "mCollapse"+i;})
						.text(function(d) {return d.name;});
			/* panel body */
			modalPan
				.append("div")
				.attr("id", function(d, i) {return "mCollapse"+i;})
				.attr("class", "panel-collapse collapse")
				.attr("role", "tabpanel")
				.attr("aria-labelledBy", function(d, i) {return "mCollapse"+i;})
					.append("div")
					.attr("class", "panel-body");

//////////////////////////////
// Collapse Box Definitions //
//////////////////////////////
			var pan = d3.select("#gf-accordion").selectAll("div .panel .panel-default")
				.data(data.big5)
				.enter()
					.append("div")
					.attr("class", "panel panel-default");
				/* panel heading */
				pan
					.append("div")
					.attr("class", "panel-heading")
					.attr("role", "tab")
					.attr("id", function(d, i) {return "heading"+i;})
						.append("h4")
						.attr("class", "panel-title")
							.append("a")
							.attr("role", "button")
							.attr("data-toggle", "collapse")
							//.attr("data-parent", "#gf-accordion")		// close all other panels
							.attr("href", function(d, i) {return "#collapse"+i;})
							.attr("aria-expanded", "true")
							.attr("aria-controls", function(d, i) {return "collapse"+i;})
							.text(function(d) {return d.name;});
				/* panel body */
				pan
					.append("div")
					.attr("id", function(d, i) {return "collapse"+i;})
					.attr("class", "panel-collapse collapse in")
					.attr("role", "tabpanel")
					.attr("aria-labelledBy", function(d, i) {return "collapse"+i;})
						.append("div")
						.attr("class", "panel-body");


	/* if DOM ready, go on */
	$(document).ready(function () {
		/* build chart first time */
		buildChart();
		/* resize-event */
		$(window).bind('resize', buildChart);
	});


//}); // d3.json-grabber
