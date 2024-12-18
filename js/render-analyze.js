
// get query parameter of URL
const searchParams = new URLSearchParams(window.location.search);
const code = searchParams.get('code');
const name = searchParams.get('name');
const expr = searchParams.get('expr') ?? 1095

//
const title = `${code} ${name}`

// change page title
document.title = title

// change h1-header text
const h1Title = document.getElementById('h1-title');
h1Title.textContent = title;

// 
const favoriteStar = document.getElementById('favorite-star')
fetch(`php/manage-favorite.php?action=get&code=${code}`)
    .then(response => response.json())
    .then(Data => {
        console.log(Data);
        const isFavorite = Data[0].is_favorite;
        console.log(isFavorite);
        favoriteStar.classList.toggle('is_favorite', isFavorite)
    })

// const drawBackground = {
//     id: 'drawBackground',
//     defaults: {
//         diviedends: null,
//     },
//     afterDraw(chart, args, options) {
//         // get property of drawing chart
//         var ctx = chart.ctx;
//         var labels = chart.data.labels;
//         var xscale = chart.scales["x"];
//         var yscale = chart.scales["leftY"];

//         //
//         function extractSpecificMonthDates(array, month) {
//             const ret = array.filter(elem => {
//                 const date = new Date(elem);
//                 return date.getMonth() === month;
//             });
//             return ret.reduce((acc, elem) => {
//                 const year = new Date(elem).getFullYear();
//                 if (!acc[year]) {
//                     acc[year] = [];
//                 }
//                 acc[year].push(elem);
//                 return acc;
//             }, {});
//         }

//         // fill 
//         options.diviedends.forEach((data) => {
//             const monthDates = extractSpecificMonthDates(labels, data);
//             for (const [year, dates] of Object.entries(monthDates)) {
//                 var left = xscale.getPixelForValue(labels.indexOf(dates.at(0)));
//                 var right = xscale.getPixelForValue(labels.indexOf(dates.at(-1))); 
//                 var top = yscale.top;
//                 console.log(left, right, top);
//                 ctx.fillStyle = "rgba(0, 0, 255, 0.2)";
//                 ctx.fillRect(left, top, right - left, yscale.height);
//             }
//         })
//     }
// };
// Chart.register(drawBackground);

// get and show stock prices data by using fetch API and php script
fetch(`php/make-analyze.php?code=${code}&expr=${expr}`)
    .then(response => response.json())
    .then(Data => {
        // parse data
        const stockPrices = Data.stock_prices;
        const dates = stockPrices.map(item => item.date);
        const movingAverages = Data.moving_averages;
        const oscillators = Data.oscillators;
        const diviedends = Data.diviedends.map(data => data.record_date);
        const smaCrossReturns = Data.sma_cross_returns;
        const emaCrossReturns = Data.ema_cross_returns;
        const macdCrossReturns = Data.macd_cross_returns;
        const rsiDropReturns = Data.rsi_drop_returns;

        // show stock prices chart
        const ctx = document.getElementById('mychart').getContext('2d');        
        const myChart = render_stock_prices_chart(
            ctx, dates, stockPrices, movingAverages, diviedends
        );
        const macdCtx = document.getElementById('macd-chart').getContext('2d');
        const macdChart = render_macd_chart(macdCtx, dates, oscillators);

        // insert golden cross
        insert_golden_cross(myChart, dates, smaCrossReturns);
        insert_golden_cross(macdChart, dates, macdCrossReturns);

        // show simulation result
        show_simulation_result(smaCrossReturns, 'goldenCross', 'simultion-result-1');
        show_simulation_result(emaCrossReturns, 'goldenCross', 'simultion-result-2');
        show_simulation_result(macdCrossReturns, 'goldenCross', 'simultion-result-3');
        show_simulation_result(rsiDropReturns, 'oversold', 'simultion-result-4');
 
        // insert rsi in stock price chart
        const resultArray = new Array(dates.length).fill(null);
        rsiDropReturns.forEach(elem => {
            const eventDate = elem.event_date;
            const dayIdx = dates.indexOf(eventDate)
            const result = elem.result;
            resultArray[dayIdx] = result
        })
        const newDatasets = [
            {
                type: 'bar',
                label: 'rsi',
                data: oscillators.map(item => item.rsi_14),
                borderWidth: 1, // default is 3
                radius: 0 ,
                yAxisID: 'yRight',
                backgroundColor: function(context) {
                        var index = context.dataIndex;
                        var value = context.dataset.data[index];
                        if (value <= 30) {
                            result = resultArray[index];
                            if (result == 'win') {
                                return '#6061AB';
                            } else if (result == 'lose') {
                                return '#FE3282';
                            } else if (result == 'unresolved') {
                                return '#ffbf01';
                            } else {
                                return '#D2CFD2';
                            }
                        } else {
                            return '#D2CFD2'
                        }
                }
            }
        ];
        newDatasets.forEach(data => myChart.data.datasets.push(data));
        myChart.update();
    }
).catch(error => {
    console.error('Error:', error);
    // retry fetch and show it as teext
    fetch(`php/make-analyze.php?code=${code}&expr=${expr}`)
    .then(response => response.text()) // レスポンスをテキストとして取得
    .then(data => console.log('Error response:', data))
});