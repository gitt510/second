
/* ========================================
/ event handler
======================================== */

function handleEnter(event) {
    if (event.keyCode === 13) {
        createSearchResult()
    }
}

function reloadAnalyzePage(button) {
    // get company code & name from current url
    const searchParams = new URLSearchParams(window.location.search);
    const code = searchParams.get('code');
    const name = searchParams.get('name');


    // get expr from button attribue
    let expr = button.getAttribute('expr');
    if (expr = '3 month') {
        expr = 90;
    } else if (expr = '1 year') {
        expr = 365;
    } else if (expr = '3 year') {
        expr = 1095
    }

    // make anayze page url with query
    const query = `?code=${code}&name=${name}&expr=${expr}`;
    const url = "analyze.html" + query;

    // reload page
    window.location.href = url;
}

function toggleFavorite(button) {
    // const isFavorite = this.classList.contains('is_favorite')
    // 状態を反転
    button.classList.toggle('is_favorite');

    // 反転した状態をdbにupload
    const searchParams = new URLSearchParams(window.location.search);
    const code = searchParams.get('code');
    const name = searchParams.get('name');
    const isFavorite = Number(button.classList.contains('is_favorite'));
    console.log(code, isFavorite);
    fetch(`php/manage-favorite.php?action=update&code=${code}&name=${name}&value=${isFavorite}`)
        .then(resp => resp.json())
        .then(Data => {
            console.log(Data);
        })
    .catch(error => {
        console.error('Error:', error);
        fetch(`php/manage-favorite.php?action=update&code=${code}&name=${name}&value=${isFavorite}`)
        .then(response => response.text())
        .then(data => console.log('Error response:', data))
    });
}


/* ========================================
/ text decoration
======================================== */
function createDecorateText(text, color='red', style = {}) {
    const span = document.createElement('span');
    span.textContent = text;
    span.style.color = color;
    Object.assign(span.style, style);
    return span
}

function openAnalyzePage(text, code, name, expr = null) {
    // define click handler
    function clickHandler(code, name, expr) {
        let query = `?code=${code}&name=${name}`;
        if (expr != null) {
            query += `&expr=${expr}`;
        }
        const url = "analyze.html" + query
        window.open(url, "_blank");
    }

    // create a tag with link
    const aTag = document.createElement('a');
    aTag.href = '#';
    aTag.textContent = text;
    aTag.onclick = function(event) {
        event.preventDefault();
        clickHandler(code, name, expr);
    }

    return aTag
}


// function insert_null_data(array, count) {
//     const keys = Object.keys(array[0]);
//     for (let i = array.length; i < count; i++) {
//         array.push(keys.reduce((acc, key) => ({ ...acc, [key]: "" }), {}));
//     }
//     return array
// }

/* ========================================
/ chart js 
======================================== */
function render_stock_prices_chart(ctx, dates, stockPrices, movingAverages, diviedends) {
    const myChart = new Chart(ctx, {
        data: {
            labels: dates,
            datasets: [
                {
                    type: 'line',
                    label: 'daily close',
                    data: stockPrices.map(item => item.close),
                    borderColor: '#363434',
                    borderColor: '#363434',
                    borderWidth: 1.5, // default is 3
                    radius: 0,
                    yAxisID: 'yLeft'
                },
                {
                    type: 'line',
                    label: 'sma 5',
                    data: movingAverages.map(item => item.sma_5),
                    borderColor: '#D00000',
                    backgroundColor: '#D00000',
                    borderWidth: 3,
                    radius: 0,
                    yAxisID: 'yLeft'
                },
                {
                    type: 'line',
                    label: 'sma 25',
                    data: movingAverages.map(item => item.sma_25),
                    borderColor: '#FFBA08',
                    backgroundColor: '#FFBA08',
                    borderWidth: 3,
                    radius: 0,
                    yAxisID: 'yLeft'
                },
                {
                    type: 'line',
                    label: 'sma 75',
                    data: movingAverages.map(item => item.sma_75),
                    borderColor: '#3F88C5',
                    backgroundColor: '#3F88C5',
                    borderWidth: 3,
                    radius: 0,
                    yAxisID: 'yLeft'
                },
            ],
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            stacked: false,
            scales: {
                yLeft: {type: 'linear', position: 'left'},
                yRight: {type: 'linear', position: 'right', beginAtZero: true}
            },
            plugins: {
                drawBackground: {
                    // diviedends: diviedends
                }
            }
        },
    });

    return myChart
}

function render_macd_chart(ctx, dates, oscillators) {
    const myChart = new Chart(ctx, {
        data: {
            labels: dates,
            datasets: [
                {
                    type: 'line',
                    label: 'MACD',
                    data: oscillators.map(item => item.macd),
                    borderColor: '#D00000',
                    backgroundColor: '#D00000',
                    borderWidth: 2, 
                    radius: 0,
                    yAxisID: 'yLeft'
                },
                {
                    type: 'line',
                    label: 'signal',
                    data: oscillators.map(item => item._signal),
                    borderColor: '#3F88C5',
                    backgroundColor: '#3F88C5',
                    borderWidth: 2,
                    radius: 0,
                    yAxisID: 'yLeft'
                },
                {
                    type: 'bar',
                    label: 'histgram',
                    data: oscillators.map(item => item.histgram),
                    borderColor: '#D2CFD2',
                    backgroundColor: '#D2CFD2',
                    borderWidth: 2,
                    radius: 0,
                    yAxisID: 'yRight'
                },
            ],
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            stacked: false,
            scales: {
                yLeft: {type: 'linear', position: 'left'},
                yRight: {type: 'linear', position: 'right'}
            },
            plugins: {
                drawBackground: {
                    // diviedends: diviedends
                }
            }
        },
    });

    return myChart
}

function insert_golden_cross(chart, dates, array) {
    // define variavle
    const takeProfits = new Array(dates.length).fill(null);
    const lossCuts = new Array(dates.length).fill(null);
    const unresolved = new Array(dates.length).fill(null);

    // insert data according to results
    array.forEach(function(elem){
        // parse data
        const eventDate = elem.event_date;
        const eventPrice = elem.event_price;
        const result = elem.result;

        // define variavle
        const dayIdx = dates.indexOf(eventDate)
        if (result == 'win') {
            takeProfits[dayIdx] = eventPrice;
        } else if (result == 'lose') {
            lossCuts[dayIdx] = eventPrice;
        } else if (result == 'unresolved') {
            unresolved[dayIdx] = eventPrice;
        } else {
            return;
        }
    })

    // 
    const newDatasets = [
        {
            type: 'line',
            label: 'golden cross(利確)',
            data: takeProfits,
            borderColor: '#6061AB',
            backgroundColor: '#6061AB',
            borderWidth: 0,
            pointStyle: 'circle',
            radius: 10,
            yAxisID: 'yLeft'
        },
        {
            type: 'line',
            label: 'golden cross(損切)',
            data: lossCuts,
            borderColor: '#FE3282',
            backgroundColor: '#FE3282',
            borderWidth: 0,
            pointStyle: 'circle',
            radius: 10,
            yAxisID: 'yLeft'
        },
        {
            type: 'line',
            label: 'golden cross(未確)',
            data: unresolved,
            borderColor: '#ffbf01',
            backgroundColor: '#ffbf01',
            borderWidth: 0,
            pointStyle: 'circle',
            radius: 10,
            yAxisID: 'yLeft'
        }
    ]
    // newDatasets.forEach(Data => chart.data.datasets.push(Data));
    newDatasets.forEach(Data => chart.data.datasets.unshift(Data));
    chart.update();
}


/* ========================================
/ show simulation result
======================================== */
function show_simulation_result(array, eventName, tagID) {
    // create table for drawing result
    const srcDocElem = document.getElementById(tagID)
    const table = document.createElement('table')
    srcDocElem.appendChild(table);

    // create table header
    const thead = document.createElement("thead");
    const tr = document.createElement('tr');
    const th1 = document.createElement('th');
    const th2 = document.createElement('th');
    const th3 = document.createElement('th');
    th1.textContent = 'date';
    th2.textContent = 'result';
    th3.textContent = 'amount'
    tr.append(th1, th2, th3);
    thead.appendChild(tr);
    table.appendChild(thead);


    // create table body
    const tbody = document.createElement("tbody");
    let winCount = 0, loseCount = 0;
    array.forEach(function(elem){
        // parse data
        const eventDate = elem.event_date;
        const buyDate = elem.buy_date;
        const sellDate = elem.sell_date;
        const result = elem.result;
        let price = elem.price;
        let color = null;

        // define variavle
        if (result == 'win') {
            price = `+${price}`;
            color = Constants.COLOR.TAKEPROFIT;
            winCount += 1;
        } else if (result == 'lose') {
            price = `-${price}`;
            color = Constants.COLOR.LOSSCUT;
            loseCount += 1;
        } else if (result == 'unresolved') {
            // var color = '#ffbf01';
        } else {
            return;
        }
        var diffInDays = null;
        if (sellDate !== null) {
            var diffInDays = new Date(sellDate) - new Date(buyDate); // it's in mill sec oeder
            diffInDays = parseInt(diffInDays / 60 / 60 / 24 / 1000);
        }

        // add content in table
        const tr = document.createElement('tr');
        const td1 = document.createElement('td');
        const td2 = document.createElement('td');
        const td3 = document.createElement('td');
        td1.textContent = eventDate;
        if (diffInDays !== null) {
            td2.textContent = `${result} in ${diffInDays}days`;
            td3.appendChild(createDecorateText(price, color));
        } else {
            td2.textContent = `unresoloved`;
            td3.textContent = '';
        }
        tr.append(td1, td2, td3);
        tbody.appendChild(tr);
    })

    // add tableebody to table
    let lastTr = document.createElement('tr');
    const lastTd = document.createElement('td');
    lastTd.appendChild(createDecorateText(`total: ${winCount}W${loseCount}L`, 'black', {fontWeight: "bold", textDecoration: 'underline'}));
    lastTd.colSpan = 3;
    lastTd.style.textAlign = 'right';
    lastTd.style.backgroundColor = 'rgba(0, 128, 0, 0.2)';
    lastTr.appendChild(lastTd);
    tbody.appendChild(lastTr);
    table.appendChild(tbody)
}