
//d3.json("example_evaluation_data.json", function(data) {
	var data = $.parseJSON($("#json-content").html());
	var buildChartFam = function buildChartFam() {
			// remove svg for resize-effect
			$("#gf_fam_chart svg").remove();

			// get data from hidden div#json-content
			//var data = $.parseJSON($("#json-content").html());


/////////////////////
//Helper functions //
/////////////////////

			/* bulk height size */
			var bulkHeight = 50;
				//bulksPerCat = d3.sum(bulkMode) +1; // + 1, cause we have 3 different bulks, an user bulk is always active

			/* calc scaling value for bulk width */
			function getBulkWidth(value) {
				return widthScale(100*value);
			}
			/* calc scaling value for tranform width (for right horizontal positioning) */
			function getTransformWidth(value) {
				return widthScale(100*value);
			}

/////////////////
// PROPERTIES  //
/////////////////

			/* SVG Leinwand */
			var width 		= $(window).width(),
			/* labels section */
			labelsSection 	= 30,
			/* scale bar height (x-achsis) */
			scaleBarHeight 	= 20;


			/* set div width */
			$("#gf_fam_chart").width(width);

			/* svgMitte: Chart */
			var middleWidth 	= width * 0.6,
				middleHeight 	= (data.fam.length * (bulkHeight * 2)) - 20;

			/* set master-div height */
			$("#gf_fam_chart").height(middleHeight + labelsSection + scaleBarHeight);

			var widthScale = d3.scale.linear()
			                .domain	([0, 100])
			                .range	([0, middleWidth]);
				// widthScaleRight = d3.scale.linear()
			    //             .domain	([0, 100])
			    //             .range	([middleWidth/2, middleWidth]);

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
			var svg = d3.select("div#gf_fam_chart")
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
				.attr("width", middleWidth).attr("height", "30").attr("fill", "#fff");

			// user Label
			var userLabel = svgLabels.append("g").attr("transform", "translate(" + leftWidth + ",7 )");
			userLabel.append("rect").attr("width", 15).attr("height", 15).attr("fill", "blue")
				.attr("stroke", "black").attr("stroke-width", 2);
			userLabel.append("text").attr("dx", 50).attr("dy", 11).text("user").attr("font-size", 12);
			// group label
			var groupLabel = svgLabels.append("g").attr("transform", "translate(" + (leftWidth+100) + ",7 )");
			groupLabel.append("rect").attr("width", 15).attr("height", 15).attr("fill", "white")
				.attr("stroke", "black").attr("stroke-width", 2)
				.on("click", function() {
					if ((d3.select(this).attr("fill") == color[1])) {
						d3.select(this).attr("fill", "white");
						$("g.groupBars g rect").hide();
					} else {
						d3.select(this).attr("fill", color[1]);
						$("g.groupBars g rect").show();
					}
				});
			groupLabel.append("text").attr("dx", 50).attr("dy", 11).text("group").attr("font-size", 12);
			// global label
			var globalLabel = svgLabels.append("g").attr("transform", "translate(" + (leftWidth+200) + ",7 )");
			globalLabel.append("rect").attr("width", 15).attr("height", 15).attr("fill", "white")
				.attr("stroke", "black").attr("stroke-width", 2)
				.on("click", function() {
					if ((d3.select(this).attr("fill") == color[2])) {
						d3.select(this).attr("fill", "white");
						$("g.globalBars g rect").hide();
					} else {
						d3.select(this).attr("fill", color[2]);
						$("g.globalBars g rect").show();
					}
				});
			globalLabel.append("text").attr("dx", 50).attr("dy", 11).text("global").attr("font-size", 12);

			// styling extra
			var verticalLines = d3.select("div#gf_fam_chart svg");
			for (i = 1; i < 10; i++) {
				verticalLines
					.append("line")
					.attr("x1", leftWidth + i*(middleWidth/10))
					.attr("y1", function(d, i) {return (labelsSection + scaleBarHeight); })
					.attr("x2", leftWidth + i*(middleWidth/10))
					.attr("y2", function(d, i) {return (svgHeight); })
					.attr("stroke","grey")
					.attr("stroke-width", 1);
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
						.data(data.fam)
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
					    .scale(widthScale)
						.orient("top");

			// var axisRight = d3.svg.axis()
			// 		    .scale(widthScaleRight)
			// 			.orient("top");

			/* linker Hälfte Achse anfuegen */
			svgMitte.append("g")
				.attr("transform", "translate(0, "+scaleBarHeight+")")
				.call(axisLeft);


				var userBars;
				/* add user bulks */
				function addUserBulks() {
						userBars = svgMitte.append("g")
									 .attr("class", "userBars")
									 .selectAll("rect")
									 .data(data.fam)
									 .enter()
									 	.append("g")
									 	.append("rect")
										.attr("width", function (d) {return getBulkWidth(d.values.user); })
										.attr("height", bulkHeight / 3)
										.attr("fill", color[0])
										.attr("y", function (d, i) {return (i * (bulkHeight*2)
										   + (bulkHeight / 3)); })
										/* nach links wachsen oder rechts */
										.attr("transform", function (d) {return "translate(0, 30)"; } );

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
								 .data(data.fam)
								 .enter()
									.append("g")
									.append("rect")
									.attr("width", function (d) {return getBulkWidth(d.values.group); })
									.attr("height", bulkHeight / 3)
									.attr("fill", color[1])
									.attr("y", function (d, i) {
										return ((i * (bulkHeight*2)) +30); });
			}

			/* add global bulks */
			function addGlobalBulks() {
				var globalBars = svgMitte.append("g")
								 .attr("class", "globalBars")
								 .selectAll("rect")
								 .data(data.fam)
								 .enter()
									.append("g")
									.append("rect")
									.attr("width", function (d) {return getBulkWidth(d.values.course); })
									.attr("height", bulkHeight / 3)
									.attr("fill", color[2])
									.attr("y", function (d, i) {
										return ((i * (bulkHeight*2) +30)
										+ (bulkHeight / 3)*2 ); });
			}

			/* check if groupbox activated and activate bulk if yes */
			addGroupBulks();
			/* init userBulks */
			addUserBulks();
			/* check if groupbox activated and activate bulk if yes */
			addGlobalBulks();
			/* first, hide other bulks than user */
			$("g.groupBars g rect").hide();
			$("g.globalBars g rect").hide();


	} // build fam chart function

//////////////////////////////
// Collapse Box Definitions //
//////////////////////////////
			var pan = d3.select("#gf-fam-accordion").selectAll("div .panel .panel-default")
				.data(data.fam)
				.enter()
					.append("div")
					.attr("class", "panel panel-default");
				/* panel heading */
			var panHead = pan
					.append("div")
					.attr("class", "panel-heading")
					.attr("role", "tab")
					.attr("id", function(d, i) {return "heading"+(i+5);})
						.append("h4")
						.attr("class", "panel-title");

				/* Header Text */
				panHead
						.append("a")
						.attr("role", "button")
						.attr("data-toggle", "collapse")
						//.attr("data-parent", "#gf-accordion")		// close all other panels
						.attr("href", function(d, i) {return "#collapse"+i;})
						.attr("aria-expanded", "true")
						.attr("aria-controls", function(d, i) {return "collapse"+i;})
						.text(function(d) {return d.name;});
				/* Header Info Button */
				panHead
					.append("sup")
					.append("span")
					.attr("class", "glyphicon glyphicon-info-sign")
					.style("font-size", "0.8em")
					.attr("data-toggle", "popover")
					.attr("data-trigger", "hover")
					.attr("title", function(d) {return d.name;})
					.attr("data-content", function(d) {return d.captions.maxText})
					.attr("data-placement", "right");
				/* panel body */
				pan
					.append("div")
					.attr("id", function(d, i) {return "collapse"+(i+5);})
					.attr("class", "panel-collapse collapse in")
					.attr("role", "tabpanel")
					.attr("aria-labelledBy", function(d, i) {return "collapse"+(i+5);})
						.append("div")
						.attr("class", "panel-body")
						.text("Lorem ipsum und so");
	/* if DOM ready, go on */
	$(document).ready(function () {
		/* build chart first time */
		buildChartFam();
		/* resize-event */
		$(window).bind('resize', buildChartFam);

		/* activate info popover */
		$(function () {
		  $('[data-toggle="popover"]').popover()
		})
	});


//}); // d3.json-grabber
