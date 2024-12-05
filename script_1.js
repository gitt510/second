
// fetch APIでPHPスクリプトからデータを非同期取得
fetch('myEntryPoint.php?ticker_code=8008')
    .then(response => response.json())  // レスポンスをJSONにパース
    .then(Data => {
        // キャンバスのコンテキストを取得
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        // Chart.jsで折れ線グラフを作成
        new Chart(ctx, {
            type: 'line',
            data: {
                // マップ関数で月のラベルを抽出
                labels: Data.map(item => item.date),
                datasets: [
                    {
                    // label and data
                    label: 'daily close',
                    data: Data.map(item => item.close),
                    // line style
                    borderColor: 'rgb(39, 77, 152)',
                    borderWidth: 1, // default is 3
                    // point style 
                    radius: 0 ,
                    },
                    {
                    // label and data
                    label: 'short moving average',
                    data: Data.map(item => item.sma),
                    // line style
                    borderColor: 'rgb(155, 39, 14)',
                    borderWidth: 2,
                    // point style 
                    radius: 0,
                    },
                    {
                    // label and data
                    label: 'long moving average',
                    data: Data.map(item => item.lma),
                    // line style
                    borderColor: 'rgb(17, 114, 18)',
                    borderWidth: 2,
                    // point style 
                    radius: 0,
                    },
                    {
                    // label and data
                    label: 'golden cross',
                    data: Data.map(item => item.gcross),
                    // line style
                    borderColor: 'rgb(0, 114, 18)',
                    borderWidth: 2,
                    // point style 
                    radius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                stacked: false,
            }
        });
    })
    .catch(error => console.error('Error:', error)); // エラーハンドリング
