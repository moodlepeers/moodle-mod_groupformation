/**
 * moodle-mod_groupformation JavaScript
 * https://github.com/moodlepeers/moodle-mod_groupformation
 *
 * This layouts the evaluation view for users based on the embedded JSON data (using d3.js)
 *
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(['jquery','mod_groupformation/d3', 'mod_groupformation/singlesidechart', 'mod_groupformation/doublesidechart'],
    function($, d3, buildChartSingleSide , buildChartDoubleSide ){
    $(document).ready(function () {
        var data = $.parseJSON($("#json-content").html());
        /* caption */
        var caption = d3.select("#gf-carousel .carousel-inner")
            .selectAll("div")
            .data(data)
            .enter()
            .append("div")
            .attr("class", function (d, i) {
                return i == 0 ? "carousel-item item active" : "carousel-item item";
            });
        caption
            .append("h2")
            .attr("class", "text-center")
            .attr("style", "margin:1em;")
            .text(function (d) {
                return d.caption
            });
        /* if first page, add intro */
        caption
            .each(function (d, i) {
                if (d.mode == "text") {
                    d3.select(this).append("div").attr("class", "gf_well").html("<p>" + d.text.replace(/\\n/g, "<br>") + "</p>");
                }
            });

        caption
            .append("div")
            .attr("id", function (d, i) {
                return d.mode == "text" ? "gf-accordion" : "gf_chart" + i;
            })
            .attr("style", "margin-bottom:2em;");

        caption
            .each(function (d, i) {
                if (d.mode == "chart") {
                    var divv = "gf_chart" + i + "accordion";
                    d3.select(this).append("div").attr("id", divv);//.attr("class", "panel-group");
                    if (d.directions == 1) {
                        buildChartSingleSide("#gf_chart" + i, d.criteria, d.bars);
                        buildPersonalResult(d.criteria, i, "#" + divv);
                        $(window).resize(buildChartSingleSide("#gf_chart" + i, d.criteria, d.bars));
                        /* resize event */
                    } else {
                        buildChartDoubleSide("#gf_chart" + i, d.criteria, d.bars);
                        buildPersonalResult(d.criteria, i, "#" + divv);
                        $(window).bind('resize', buildChartDoubleSide("#gf_chart" + i, d.criteria, d.bars));
                        /* resize event */
                    }
                }
            });

        /* activate popover info */
        $(function () {
            $('[data-toggle="popover"]').popover();
        });

    });

    function buildPersonalResult(datam, index, divId) {

        var pan = d3.select(divId).selectAll("div .card .panel .panel-default")
            .data(datam)
            .enter()
            .append("div")
            .attr("class", "card panel panel-default");
        /* panel heading */
        var panDiv = pan
            .append("div")
            .attr("class", "card-header panel-heading")
            .attr("role", "tab")
            .attr("id", function (d, i) {
                return "heading" + index + i;
            });
        var panHead = panDiv.append("h4");

        /* Header Text */
        panHead
        /* .append("a")
        .attr("role", "button")
        .attr("data-toggle", "collapse")
        .attr("href", function (d, i) {
            return "#collapse" + index + i;
        })
        .attr("aria-expanded", "true")
        .attr("aria-controls", function (d, i) {
            return "collapse" + index + i;
        })
        */
            .text(function (d) {
                return d.captions.cutoffCaption;
            });
        /* Header Info Button */
        panHead
            .append("span")
            .attr("class", "fa fa-info-circle")
            .style("margin-left", "5px")
            .attr("data-toggle", "popover")
            .attr("data-trigger", "hover")
            .attr("title", function (d) {
                return d.captions.cutoffCaption;
            })
            .attr("data-content", function (d) {
                return d.captions.maxText
            })
            .attr("data-placement", "right");
        /* panel body */
        pan
            .append("div")
            .attr("id", function (d, i) {
                return "collapse" + index + i;
            })
            .attr("class", "card-collapse panel-collapse collapse in")
            .attr("role", "tabpanel")
            .attr("aria-labelledBy", function (d, i) {
                return "collapse" + index + i;
            })
            .append("div")
            .attr("class", "card-block panel-body")
            .append("p")
            .attr("class", "gf_p_accordion")
            .text(function (d, i) {
                return d.cutoff;
            });
    }
});
