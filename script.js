document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('myChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['赤', '青', '黄'],
            datasets: [{
                label: '色の数値',
                data: [12, 19, 3],
                backgroundColor: ['red', 'blue', 'yellow']
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'サンプルグラフ'
            }
        }
    });
});

/*
var data = {
    "datasets": [
      {
        "backgroundColor": "rgb(156, 39, 176)",
        "borderColor": "rgb(156, 39, 176)",
        "fill": false,
        "data": [
          10,
          120,
          80,
          134
        ],
        "id": "amount",
        "label": "Purchase amount (USD)",
				"yAxisID":"left"
      },
      {
        "backgroundColor": "rgb(39, 176, 200)",
        "borderColor": "rgb(39, 176, 200)",
        "fill": false,
        "data": [
          300,
          -1200,
          500,
          -340
        ],
        "id": "amount",
        "label": "Purchase amount (USD)",
				"yAxisID":"right"

      }
    ],
    "labels": [
      "2017-01-02",
      "2017-04-02",
      "2017-07-02",
      "2018-01-02"
    ]
  };
var options = {
    "elements": {
      "rectangle": {
        "borderWidth": 2
      }
    },
    "layout": {
      "padding": 0
    },
    "legend": {
      "display": true,
      "labels": {
        "boxWidth": 16
      }
    },
    "maintainAspectRatio": false,
    "responsive": true,
    "scales": {
      "xAxes": [
        {
          "gridLines": {
            "display": false
          },
          "scaleLabel": {
            "display": true,
            "labelString": "Date"
          },
          "stacked": false,
          "ticks": {
            "autoSkip": true,
            "beginAtZero": true
          },
          "time": {
            "tooltipFormat": "[Q]Q - YYYY",
            "unit": "quarter"
          },
          "type": "time"
        }
      ],
      "yAxes": [
        {
          "scaleLabel": {
            "display": true,
            "labelString": "Purchase amount (USD)"
          },
					"id": "left",
          "stacked": false,
          "ticks": {
            "beginAtZero": true
          }
        },
        {
          "scaleLabel": {
            "display": true,
            "labelString": "Purchase count"
          },
					"id": "right",
					"position": "right",
          "stacked": false,
          "ticks": {
            "beginAtZero": true
          }
        }
      ]
    },
    "title": {
      "display": false
    },
    "tooltips": {
      "intersect": false,
      "mode": "index",
      "position": "nearest",
      "callbacks": {}
    }
  }
var type = "line";

var myChart = new Chart(document.getElementById("myChart").getContext('2d'), {options, data, type});
var myChart2 = new Chart(document.getElementById("myChart2").getContext('2d'), {
	options: {
		...options,
		scales: {
			...options.scales,
			yAxes: [
					{
						"scaleLabel": {
							"display": true,
							"labelString": "Purchase amount (USD)"
						},
						"id": "left",
						"stacked": false,
						"ticks": {
							"beginAtZero": true,
							suggestedMin: -200,
							suggestedMax: 200
						}
					},
					{
						"scaleLabel": {
							"display": true,
							"labelString": "Purchase count"
						},
						"id": "right",
						"position": "right",
						"stacked": false,
						"ticks": {
							"beginAtZero": true,
							suggestedMin: -2000,
							suggestedMax: 2000
						}
					}
			]
		}
	},
	data,
	type
});
*/