/**
 * Pie Chart Manager
 *
 * Required libraries: jquery.js, highcharts.js
 *
 * @param {string} dataUrl usr where get data
 * @returns {Function}
 * @constructor
 */
var PieChartManager = function (dataUrl) {
    "use strict";

    /**
     * Public method for load and render pie chart
     *
     * @param {string} pieName pie name
     * @param {string} pieTitle pie title
     * @param {function} callback user callback function
     */
    var loadPieChart = function(pieName, pieTitle, callback) {
        $.post(dataUrl, {field: pieName}, function(response) {
            if (undefined !== response) {
                if (response.error) {
                    return callback(response, pieName);
                } else {
                    renderPieChart(response.data, pieName, pieTitle);
                }
            } else {
                return callback(response, pieName);
            }
        });
    };

    /**
     * Method for rendering pie cahrt
     *
     * @param {object} data data for rendering pie chart
     * @param {string} pieName pie name
     * @param {string} pieTitle pie title
     */
    var renderPieChart = function(data, pieName, pieTitle) {
        $('#pie-' + pieName).highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: pieTitle
            },
            tooltip: {
                valueDecimals: 0
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Amount',
                data: data
            }]
        });
    };

    return function(pieName, pieTitle, callback) {
        loadPieChart(pieName, pieTitle, callback);
    };
};