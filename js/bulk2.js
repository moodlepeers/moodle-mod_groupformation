
//d3.json("example_evaluation_data.json", function(data) {
	var data = $.parseJSON($("#json-content").html());
	var buildChart = function buildChart() {
			// remove svg for resize-effect
			$("#gf_chart svg").remove();

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
			var width 		= $(window).width() * 0.7,
			/* labels section */
			labelsSection 	= 30,
			/* scale bar height (x-achsis) */
			scaleBarHeight 	= 20;


			/* set div width */
			$("#gf_chart").width(width);

			/* svgMitte: Chart */
			var middleWidth 	= width * 0.6,
				middleHeight 	= (data.big5.criteria.length * (bulkHeight * 2)) - 20;

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
						.data(data.big5.criteria)
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
						.data(data.big5.criteria)
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
									 .data(data.big5.criteria)
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
								 .data(data.big5.criteria)
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
								 .data(data.big5.criteria)
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
			/* first, hide other bulks than user */
			$("g.groupBars g rect").hide();
			$("g.globalBars g rect").hide();


	} // build chart function

///////////////////////
// Modal Definition  //
///////////////////////
			// configure modal
			// var modalContent = d3.select("#gf-modal")
			// 	.append		("div")
			// 	.attr		("class", "modal-dialog")
			// 		.append		("div")
			// 		.attr		("class", "modal-content");
			//
			// modalContent.append("div").attr("class", "modal-header");
			//
			// modalContent.select(".modal-header")
			// 	.append		("button")
			// 	.attr		("class", "close")
			// 	.attr		("data-dismiss", "modal")
			// 	.attr		("aria-label", "Close")
			// 		.append		("span")
			// 		.attr		("aria-hidden", "true")
			// 		.html		("&times;");
			//
			// modalContent.select(".modal-header")
			// 	.append		("h4")
			// 	.attr		("class", "modal-title")
			// 	.attr		("id", "myModalLabel")
			// 	.text		("Info-Box");
			//
			// var modalAccordion = modalContent
			// 	.append		("div")
			// 	.attr		("class", "modal-body")
			// 	.append		("div")
			// 	.attr		("id", "gf-modal-accordion");
			//
			// modalPan = modalAccordion.selectAll("div .panel .panel-default")
			// 	.data(data.big5.criteria)
			// 	.enter()
			// 		.append		("div")
			// 		.attr		("class", "panel panel-info");
			// /* panel heading */
			// modalPan
			// 	.append("div")
			// 	.attr("class", "panel-heading")
			// 	.attr("role", "tab")
			// 	.attr("id", function(d, i) {return "mHeading"+i;})
			// 		.append("h4")
			// 		.attr("class", "panel-title")
			// 			.append("a")
			// 			.attr("role", "button")
			// 			.attr("data-toggle", "collapse")
			// 			.attr("data-parent", "#gf-modal-accordion")		// close all other panels
			// 			.attr("href", function(d, i) {return "#mCollapse"+i;})
			// 			.attr("aria-expanded", "true")
			// 			.attr("aria-controls", function(d, i) {return "mCollapse"+i;})
			// 			.text(function(d) {return d.name;});
			// /* panel body */
			// modalPan
			// 	.append("div")
			// 	.attr("id", function(d, i) {return "mCollapse"+i;})
			// 	.attr("class", "panel-collapse collapse")
			// 	.attr("role", "tabpanel")
			// 	.attr("aria-labelledBy", function(d, i) {return "mCollapse"+i;})
			// 		.append("div")
			// 		.attr("class", "panel-body");

//////////////////////////////
// Collapse Box Definitions //
//////////////////////////////
			var pan = d3.select("#gf-accordion").selectAll("div .panel .panel-default")
				.data(data.big5.criteria)
				.enter()
					.append("div")
					.attr("class", "panel panel-default");
				/* panel heading */
			var	panHead = pan
					.append("div")
					.attr("class", "panel-heading")
					.attr("role", "tab")
					.attr("id", function(d, i) {return "heading"+i;})
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
					.append("span")
					.attr("class", "glyphicon glyphicon-info-sign")
					.style("margin-left", "5px")
					.attr("data-toggle", "popover")
					.attr("data-trigger", "hover")
					.attr("title", function(d) {return d.name;})
					.attr("data-content", function(d) {return 'eval_text_fam_herausforderung_2]]"},{"name":"intere:"[[eval_max_caption_erfolgswahrscheinlichkeit]]","maxText":"[[eval_max_text_erfolgswahrscheinlichkeit]]","finalText":null},"cutoff":"[[eval_text_fam_erfolgswahrscheinlichkeit_2]]"},{"name":"misserfolgsbefuerchtung","values":{"user":0.46666666666667,"group":null,"course":null},"range":{"min":0,"max":1},"mode":1,"captions":{"maxCaption":"[[eval_max_caption_misserfolgsbefuerchtung]]","maxText":"[[eval_max_text_misserfolgsbefuerchtung]]","finalText":null},"cutoff":"[[eval_text_fam_misserfolgsbefuerchtung_2]]"}]}}        '})
					.attr("data-placement", "right");
				/* panel body */
				pan
					.append("div")
					.attr("id", function(d, i) {return "collapse"+i;})
					.attr("class", "panel-collapse collapse in")
					.attr("role", "tabpanel")
					.attr("aria-labelledBy", function(d, i) {return "collapse"+i;})
						.append("div")
						.attr("class", "panel-body")
						.text("Lorem ipsum und so");


	/* if DOM ready, go on */
	$(document).ready(function () {
		/* build chart first time */
		buildChart();
		/* resize-event */
		$(window).bind('resize', buildChart);

		/* activate popover info */
		$(function () {
		  $('[data-toggle="popover"]').popover()
		})
	});


//}); // d3.json-grabber
