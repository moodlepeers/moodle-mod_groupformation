
/* if DOM ready, go on */
$(document).ready(function () {
	// get data
	var data = $.parseJSON($("#json-content").html());
	/* caption */
	var caption = d3.select("#gf-carousel .carousel-inner")
		.selectAll("div")
		.data(data)
		.enter()
			.append("div")
			.attr("class", function(d, i){return i == 0? "item active" : "item";});
		caption
			.append("h2")
			.attr("class", "text-center")
			.text(function(d) {return d.caption});
		/* if first page, add intro */
		caption
			.each(function(d, i) {
				if (d.mode == "text")
				d3.select(this).append("div").attr("class", "well").html("<p>"+d.text.replace(/\\n/g,"<br>")+"</p>");
			});

		caption
			.append("div")
			.attr("id", function(d, i){return d.mode == "text" ? "gf-accordion" : "gf_chart"+i;});

		caption
			.each(function(d, i) {
				if (d.mode == "chart") {
					var divv = "gf_chart"+i+"accordion";
					d3.select(this).append("div").attr("id", divv);
					if (d.directions == 1) {
						buildChartOneSide("#gf_chart"+i, d.criteria, d.bars);
						buildPersonalResult(d.criteria, i, "#"+divv);
						$(window).bind('resize', buildChartOneSide("#gf_chart"+i, d.criteria, d.bars)); /* resize event */
					} else {
						buildChartDoubleSide("#gf_chart"+i, d.criteria, d.bars);
						buildPersonalResult(d.criteria, i, "#"+divv);
						$(window).bind('resize', buildChartDoubleSide("#gf_chart"+i, d.criteria, d.bars)); /* resize event */
					}
				}
			});


	/* activate popover info */
	$(function () {
	  $('[data-toggle="popover"]').popover();
	});


});


//////////////////////////////
// Collapse Box Definitions -- FOR EACH CHART//
//////////////////////////////
function buildPersonalResult(datam, index, divId) {

	var pan = d3.select(divId).selectAll("div .panel .panel-default")
		.data(datam)
		.enter()
			.append("div")
			.attr("class", "panel panel-default");
		/* panel heading */
	var panHead = pan
			.append("div")
			.attr("class", "panel-heading")
			.attr("role", "tab")
			.attr("id", function(d, i) {return "heading"+index+i;})
				.append("h4")
				.attr("class", "panel-title");

		/* Header Text */
		panHead
				.append("a")
				.attr("role", "button")
				.attr("data-toggle", "collapse")
				//.attr("data-parent", "#gf-accordion")		// close all other panels
				.attr("href", function(d, i) {return "#collapse"+index+i;})
				.attr("aria-expanded", "true")
				.attr("aria-controls", function(d, i) {return "collapse"+index+i;})
				.text(function(d) {return d.captions.cutoffCaption;});
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
			.attr("id", function(d, i) {return "collapse"+index+i;})
			.attr("class", "panel-collapse collapse in")
			.attr("role", "tabpanel")
			.attr("aria-labelledBy", function(d, i) {return "collapse"+index+i;})
				.append("div")
				.attr("class", "panel-body")
						.text(function (d, i) {return d.cutoff;});
}
