/**
 * moodle-mod_groupformation JavaScript
 * https://github.com/moodlepeers/moodle-mod_groupformation
 *
 *
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['mod_groupformation/d3','jquery'],function(d3, $) {

    return function buildChartDoubleSide(chartid, datam, bars) {
        // Remove svg for resize-effect.
        $(chartid + " svg").remove();

        /* bulk height size */
        var bulkHeight = 50;

        /* calc scaling value for bulk width */
        function getBulkWidth(value) {
            return (100 * value) < 50 ? widthScaleLeft(100 - (2 * (50 - (100 * value)))) : widthScaleRight(((100 * value) - 50) + (100 * value) - 150)
        }

        /* calc scaling value for tranform width (for right horizontal positioning) */
        function getTransformWidth(value) {
            return ((100 * value) < 50 ? widthScaleLeft(2 * (50 - (100 * value))) : widthScaleRight(0));
        }

        /* SVG Leinwand */
        var width = $("#gf-carousel").width(),
            /* labels section */
            labelsSection = 30,
            /* scale bar height (x-achsis) */
            scaleBarHeight = 20;

        /* svgMitte: Chart */
        var middleWidth = width * 0.6,
            middleHeight = (datam.length * (bulkHeight * 2)) - 20;

        /* set master-div height */
        $(chartid).height(middleHeight + labelsSection + scaleBarHeight);

        var widthScaleLeft = d3.scaleLinear()
                .domain([100, 0])
                .range([0, middleWidth / 2]),
            widthScaleRight = d3.scaleLinear()
                .domain([0, 100])
                .range([middleWidth / 2, middleWidth]);

        svgHeight = middleHeight + labelsSection + scaleBarHeight; // 20 = Scale + Border height.

        /* bulk colors */
        var color = ["rgb(136, 222, 0)",
            datam[0].values.group != null ? "rgb(65, 207, 68)" : "white",
            datam[0].values.course != null ? "rgb(83, 147, 78)" : "white"];

        /* svg sidebars labels */
        var sideBarHeight = middleHeight,
            leftWidth = width * 0.2,
            leftBackground = "none";

        var svg = d3.select("div" + chartid)
            .append("svg")
            .attr("width", width)
            .attr("height", svgHeight)
            .style("overflow", "visible")
            .attr("font-size", 12);

        /* 4 Bereiche: oberer Rand ScaleBar / links Beschriftung / Chart / rechts Beschriftung */
        var svgLabels = svg.append("g").attr("id", "labels"),
            svgLinks = svg.append("g").attr("id", "links"),
            svgRechts = svg.append("g").attr("id", "rechts"),
            svgMitte = svg.append("g").attr("id", "mitte");

        // Labels section.
        svgLabels.append("rect").attr("transform", "translate(" + leftWidth + ",0 )")
            .attr("width", middleWidth).attr("height", "30").attr("fill", "none");

        // User Label.
        var userLabel = svgLabels.append("g").attr("transform", "translate(" + leftWidth + ",7 )");
        userLabel.append("rect").attr("width", 15).attr("height", 15).attr("fill", color[0])
            .attr("stroke", "black").attr("stroke-width", 2);
        userLabel.append("text").attr("dx", 20).attr("dy", 11).text(bars.user).attr("font-size", 12);
        // Group label.
        var groupLabel = svgLabels.append("g").attr("transform", "translate(" + (leftWidth + 100) + ",7 )");
        groupLabel.append("rect").attr("width", 15).attr("height", 15).attr("fill", "white")
            .attr("stroke", "black").attr("stroke-width", 2)
            .on("click", function () {
                if ((d3.select(this).attr("fill") == color[1])) {
                    d3.select(this).attr("fill", "white");
                    $("g.groupBars g rect").hide();
                } else {
                    d3.select(this).attr("fill", color[1]);
                    $("g.groupBars g rect").show();
                }
            });
        groupLabel.append("text").attr("dx", 20).attr("dy", 11).text(bars.group).attr("font-size", 12);
        // Global label.
        var globalLabel = svgLabels.append("g").attr("transform", "translate(" + (leftWidth + 200) + ",7 )");
        globalLabel.append("rect").attr("width", 15).attr("height", 15).attr("fill", "white")
            .attr("stroke", "black").attr("stroke-width", 2)
            .on("click", function () {
                if ((d3.select(this).attr("fill") == color[2])) {
                    d3.select(this).attr("fill", "white");
                    $("g.globalBars g rect").hide();
                } else {
                    d3.select(this).attr("fill", color[2]);
                    $("g.globalBars g rect").show();
                }
            });
        globalLabel.append("text").attr("dx", 20).attr("dy", 11).text(bars.course).attr("font-size", 12);

        // Styling extra.
        var verticalLines = d3.select("div" + chartid + " svg");
        for (i = 1; i < 10; i++) {
            verticalLines
                .append("line")
                .attr("x1", leftWidth + i * (middleWidth / 10))
                .attr("y1", function (d, i) {
                    return (labelsSection + scaleBarHeight);
                })
                .attr("x2", leftWidth + i * (middleWidth / 10))
                .attr("y2", function (d, i) {
                    return (svgHeight);
                })
                .attr("stroke", (i == 5) ? "black" : "grey")
                .attr("stroke-width", (i == 5) ? 3 : 1);
        }

        svgLinks
            .attr("transform", "translate(0, " + (labelsSection + scaleBarHeight) + ")")
            /* background */
            .append("rect")
            .attr("width", leftWidth)
            .attr("height", sideBarHeight)
            .attr("fill", leftBackground);

        /* links: Label Group Boxes */
        var gBoxLeft = svgLinks.selectAll("g")
            .data(datam)
            .enter()
            .append("g");

        /* Box um Labels herum */
        var textBox = gBoxLeft
            .append("rect")
            .attr("fill", leftBackground)
            .attr("width", leftWidth)
            .attr("height", bulkHeight)
            .attr("y", function (d, i) {
                return (i * (bulkHeight * 2) + 11);
            });

        /* Labels */
        var barsText = gBoxLeft
            .append("text")
            .attr("fill", "black")
            /* position im SVG */
            .attr("x", 0)
            .attr("y", function (d, i) {
                return ((i * (bulkHeight * 2)) + (scaleBarHeight - 5));
            })
            .attr("text-anchor", "middle")
            /* position innerhalb der box */
            .attr("dx", leftWidth / 2)
            .attr("dy", (bulkHeight / 2))
            /* font format */
            .style("font", "1.2em sans-serif")
            .text(function (d) {
                return d.captions.minCaption
            });

        svgRechts
            .attr("transform", "translate(" + (leftWidth + middleWidth) + ", " + (labelsSection + scaleBarHeight) + ")")
            /* background */
            .append("rect")
            .attr("width", leftWidth)
            .attr("height", sideBarHeight)
            .attr("fill", leftBackground);

        /* links: Label Group Boxes */
        var gBoxRight = svgRechts.selectAll("g")
            .data(datam)
            .enter()
            .append("g");

        /* Box um Labels herum */
        var textBoxRight = gBoxRight
            .append("rect")
            .attr("fill", leftBackground)
            .attr("width", leftWidth)
            .attr("height", bulkHeight)
            .attr("y", function (d, i) {
                return (i * (bulkHeight * 2) + 11);
            });

        /* Labels */
        var barsTextRight = gBoxRight
            .append("text")
            .attr("fill", "black")
            /* position im SVG */
            .attr("x", 0)
            .attr("y", function (d, i) {
                return ((i * (bulkHeight * 2)) + (scaleBarHeight - 5));
            })
            .attr("text-anchor", "middle")
            /* position innerhalb der box */
            .attr("dx", leftWidth / 2)
            .attr("dy", (bulkHeight / 2))
            /* font format */
            .style("font", "1.2em sans-serif")
            .text(function (d) {
                return d.captions.maxCaption
            });

        svgMitte
            .attr("transform", "translate(" + leftWidth + ", " + labelsSection + ")")
            /* background color */
            .append("rect")
            .attr("transform", "translate(0, 20)")
            .attr("width", middleWidth)
            .attr("height", middleHeight)
            .attr("fill", "#fbfbfb")
            .attr("stroke", "#d1cdcd")
            .attr("stroke-width", "1px");

        /* Achse entwerfen */
        var axisLeft = d3.axisTop(widthScaleLeft);

        var axisRight = d3.axisTop(widthScaleRight);

        /* linker Hälfte Achse anfuegen */
        svgMitte.append("g")
            .attr("transform", "translate(0, " + scaleBarHeight + ")")
            .call(axisLeft);
        /* rechter Hälfte Achse anfuegen */
        svgMitte.append("g")
            .attr("transform", "translate(0, " + scaleBarHeight + ")")
            .call(axisRight);

        var userBars;
        /* add user bulks */
        function addUserBulks() {
            userBars = svgMitte.append("g")
                .attr("class", "userBars")
                .selectAll("rect")
                .data(datam)
                .enter()
                .append("g")
                .append("rect")
                .attr("width", function (d) {
                    return getBulkWidth(d.values.user);
                })
                .attr("height", bulkHeight / 3)
                .attr("fill", color[0])
                .attr("y", function (d, i) {
                    return (i * (bulkHeight * 2) + (bulkHeight / 3));
                })
                /* nach links wachsen oder rechts */
                .attr("transform", function (d) {
                    return "translate(" + getTransformWidth(d.values.user) + ", 30)";
                });
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
                .data(datam)
                .enter()
                .append("g")
                .append("rect")
                .attr("width", function (d) {
                    return getBulkWidth(d.values.group == null ? 0.5 : d.values.group);
                })
                .attr("height", bulkHeight / 3)
                .attr("fill", color[1])
                .attr("y", function (d, i) {
                    return ((i * (bulkHeight * 2)) + 30);
                })
                /* nach links wachsen oder rechts */
                .attr("transform", function (d) {
                    return "translate(" + getTransformWidth(d.values.group) + ", 0)";
                });
        }

        /* add global bulks */
        function addGlobalBulks() {
            var globalBars = svgMitte.append("g")
                .attr("class", "globalBars")
                .selectAll("rect")
                .data(datam)
                .enter()
                .append("g")
                .append("rect")
                .attr("width", function (d) {
                    return getBulkWidth(d.values.course == null ? 0.5 : d.values.course);
                })
                .attr("height", bulkHeight / 3)
                .attr("fill", color[2])
                .attr("y", function (d, i) {
                    return ((i * (bulkHeight * 2) + 30) + (bulkHeight / 3) * 2 );
                })
                /* nach links wachsen oder rechts */
                .attr("transform", function (d) {
                    return "translate(" + getTransformWidth(d.values.course) + ", 0)";
                });
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

    }
});
