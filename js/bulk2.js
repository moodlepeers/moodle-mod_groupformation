
// d3.json("example_evaluation_data.json", function(data) {
// get data from hidden div#json-content

	function buildChartDoubleSide (chartid, data) {
			// remove svg for resize-effect
			$(chartid+" svg").remove();




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
			var width 		= $(window).width(),
			/* labels section */
			labelsSection 	= 30,
			/* scale bar height (x-achsis) */
			scaleBarHeight 	= 20;


			/* set div width */
			$(chartid).width(width);

			/* svgMitte: Chart */
			var middleWidth 	= width * 0.6,
				middleHeight 	= (data.criteria.length * (bulkHeight * 2)) - 20;

			/* set master-div height */
			$(chartid).height(middleHeight + labelsSection + scaleBarHeight);

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
			var svg = d3.select("div"+chartid)
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
			var verticalLines = d3.select("div"+chartid+" svg");
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
						.data(data.criteria)
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
						.data(data.criteria)
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
									 .data(data.criteria)
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
								 .data(data.criteria)
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
								 .data(data.criteria)
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

//export {buildChartDoubleSide};
// }); // d3.json-grabber
