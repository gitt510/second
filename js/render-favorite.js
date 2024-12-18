
// get favorite company from database via php
fetch(`php/manage-favorite.php?action=get`)
    .then(response => response.json())
    .then(Data => {
        // 
        const ul = document.getElementById('favorite-list');
        Data.forEach((element) => {
            //
            const code = element.code;
            const name = element.name;

            //
            const li = document.createElement('li');
            li.appendChild(openAnalyzePage(`${code} ${name}`, code, name, 90));
            ul.appendChild(li)
        });
    })