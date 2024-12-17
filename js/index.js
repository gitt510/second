function createSearchResult() {
    fetch('php/index.php')
    .then(response => response.json())
    .then(Data => {
        // parse return of php
        const companies = Data.companies;

        // convert alphanumeri char from full-width to half-width
        let inputValue = document.getElementById('search-field').value;
        inputValue = inputValue.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function(s) {
            return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
         });

        // render result
        const resultField = document.getElementById('search-result-field');
        resultField.innerHTML = '';
        if (inputValue) {
            companies.forEach(elem => {
                // get company code and name
                const code = elem.code;
                const name = elem.name;

                // if input is included in either code or name > show result
                let actionFlg = false;
                if (code.includes(inputValue)) {
                    actionFlg = true;
                } else if (name.includes(inputValue)) {
                    actionFlg = true;
                }
                if (actionFlg) {
                    let li = document.createElement('li');
                    const text = `${code} ${name}`;
                    li.appendChild(openAnalyzePage(text, code, name, 90));
                    resultField.appendChild(li);
                }
            })
        }
    });
}
