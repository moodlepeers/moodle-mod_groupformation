// import {buildChartOneSide} from "bulkFam.js";
// import {buildChartDoubleSide} from "bulk2.js";

/* if DOM ready, go on */
$(document).ready(function () {
	// get data
	var data = $.parseJSON($("#json-content").html());
	console.debug(data);
	/* caption */
	var caption = d3.select("#carousel-example-generic .carousel-inner")
		.selectAll("div")
		.data(data)
		.enter()
			.append("div")
			.attr("class", function(d, i){return i == 0? "item active" : "item";});
		caption
			.append("h2")
			.attr("class", "text-center")
			.text(function(d) {return d.caption});

		caption
			.append("div")
			.attr("id", function(d, i){return d.mode == "text" ? "gf-accordion" : i == 2? "gf_fam_chart" : "gf_chart"})
			.each(function(d, i) {
				if (d.mode == "chart" && i == 2) {
					console.debug(d);
					buildChartOneSide("#gf_fam_chart", d.criteria);
					$(window).bind('resize', buildChartOneSide("#gf_fam_chart", d.criteria)); /* resize event */
				}
			});
				// {

				// });
			// (function (d) {
			// 	if (function(d){return d.mode;} == "chart") {
			//
			// 		return .append("div")
			// 		.attr("id", "gf_fam_chart");
			// 	/* build chart */
			// 		buildChartOneSide("#gf_fam_chart", function(d) {return d.criteria;});
			// 		$(window).bind('resize', buildChartOneSide("#gf_fam_chart", function(d){return d.criteria;})); /* resize event */
			// 	} else {
			// 	/* ausklappbare Texte */
			// 		return .append("div")
			// 		.attr("id", "gf_accordion");
			// 	}
			// });

	// body.append("div")
	//


	//////////////////////////////
	// Collapse Box Definitions //
	//////////////////////////////
				var pan2 = d3.select("#gf-accordion").selectAll("div .panel .panel-default")
					.data(data[1].criteria)
					.enter()
						.append("div")
						.attr("class", "panel panel-default");
					/* panel heading */
				var	pan2Head = pan2
						.append("div")
						.attr("class", "panel-heading")
						.attr("role", "tab")
						.attr("id", function(d, i) {return "heading"+i;})
							.append("h4")
							.attr("class", "panel-title");
					/* Header Text */
					pan2Head
							.append("a")
							.attr("role", "button")
							.attr("data-toggle", "collapse")
							//.attr("data-parent", "#gf-accordion")		// close all other panels
							.attr("href", function(d, i) {return "#collapse"+i;})
							.attr("aria-expanded", "true")
							.attr("aria-controls", function(d, i) {return "collapse"+i;})
							.text(function(d) {return d.name;});
					/* Header Info Button */
					pan2Head
						.append("span")
						.attr("class", "glyphicon glyphicon-info-sign")
						.style("margin-left", "5px")
						.attr("data-toggle", "popover")
						.attr("data-trigger", "hover")
						.attr("title", function(d) {return d.name;})
						.attr("data-content", function(d) {return d.captions.maxText})
						.attr("data-placement", "right");
					/* panel body */
					pan2
						.append("div")
						.attr("id", function(d, i) {return "collapse"+i;})
						.attr("class", "panel-collapse collapse in")
						.attr("role", "tabpanel")
						.attr("aria-labelledBy", function(d, i) {return "collapse"+i;})
							.append("div")
							.attr("class", "panel-body")
							.text("Lorem ipsum und so");

//////////////////////////////
// Collapse Box Definitions //
//////////////////////////////
			var pan = d3.select("#gf-fam-accordion").append("g").selectAll("div .panel .panel-default")
				.data(data[2].criteria)
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
					.append("span")
					.attr("class", "glyphicon glyphicon-info-sign")
					.style("margin-left", "5px")
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


			/* build chart first time */
			// buildChartDoubleSide();
			// /* resize-event */
			// $(window).bind('resize', buildChartDoubleSide);
			//
			/* activate popover info */
			$(function () {
			  $('[data-toggle="popover"]').popover();
		  	});


});
