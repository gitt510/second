
fetch("php/make-top-news.php")
    .then(response => response.json())
    .then(data => {  
        // sort result 
        data.sort(function(a, b) {
            if (a.latest_event_date > b.latest_event_date) return -1;
            if (a.latest_event_date < b.latest_event_date) return 1;
            if (a.code > b.code) return -1;
            if (a.code < b.code) return 1;
        });

        //
        const table = document.getElementById('my-table-1');

        // create table header
        var thead = document.createElement("thead");
        var tr = document.createElement('tr');
        var th1 = document.createElement('th');
        var th2 = document.createElement('th');
        var th3 = document.createElement('th');
        var th4 = document.createElement('th');
        th1.textContent = 'name';
        th2.textContent = 'latestClose';
        th3.textContent = 'events'
        tr.append(th1, th2, th3, th3);
        thead.appendChild(tr);
        table.appendChild(thead);

        // creat tabel body
        var tbody = document.createElement("tbody");
        data.forEach(elem => {
            //
            const code = elem.code;
            const name = elem.name;
            const events = elem.events;
            const latestClose = elem.latest_close;

            // 
            const tr = document.createElement('tr');
            const td1 = document.createElement('td');
            const td2 = document.createElement('td');
            const td3 = document.createElement('td');
            td1.appendChild(openAnalyzePage(`${code} ${name}`, code, name, 90))
            td2.textContent = latestClose;
            events.forEach(event => {
                const li = document.createElement('li');
                li.textContent = event
                td3.appendChild(li)
            })
            tr.append(td1, td2, td3);
            tbody.appendChild(tr);
        });
        table.appendChild(tbody);
        }
)
    