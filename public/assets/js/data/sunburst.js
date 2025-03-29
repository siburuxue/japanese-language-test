;function sunburst(config){
    Highcharts.setOptions({
        lang: {
            thousandsSep: ','
        },
        credits:{
            enabled:false
        },
        exporting:{
            enabled:false
        }
    });

    var data = config.callback.call(null);


    Highcharts.getOptions().colors.splice(0, 0, 'transparent');


    Highcharts.chart(config.container, {

        chart: {
            height: '100%'
        },

        title: {
            text: config.title
        },
        subtitle: {
            text: ''
        },
        series: [{
            type: "sunburst",
            data: data,
            allowDrillToNode: true,
            cursor: 'pointer',
            dataLabels: {
                /**
                 * A custom formatter that returns the name only if the inner arc
                 * is longer than a certain pixel size, so the shape has place for
                 * the label.
                 */
                formatter: function () {
                    var shape = this.point.node.shapeArgs;

                    var innerArcFraction = (shape.end - shape.start) / (2 * Math.PI);
                    var perimeter = 2 * Math.PI * shape.innerR;

                    var innerArcPixels = innerArcFraction * perimeter;

                    if (innerArcPixels > 16) {
                        return this.point.name;
                    }
                }
            },
            levels: [{
                level: 2,
                colorByPoint: true,
                dataLabels: {
                    rotationMode: 'parallel'
                }
            },
                {
                    level: 3,
                    colorVariation: {
                        key: 'brightness',
                        to: -0.5
                    }
                }, {
                    level: 4,
                    colorVariation: {
                        key: 'brightness',
                        to: 0.5
                    }
                }]

        }],
        tooltip: {
            headerFormat: "",
            pointFormat: '<b>{point.name}</b>' + config.itemTitle + '：<b>{point.value}</b>'
        }
    });
}