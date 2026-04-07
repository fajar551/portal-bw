let urlParams = new URLSearchParams(window.location.search);
let id = urlParams.get('id');
let email = urlParams.get('email');
let dtable;
if (dtable) {
    dtable.destroy();
    $('#data').empty();
}
$.get('/modules/addons/sellBacklink/ajax/myHistory.php?id=' + id, function (data){
    data = data.data.reverse();
    let counter = 1;

    data.forEach(val => {
        $('#data').append(`
            <tr>
                <td>${counter}</td>
                <td>${val.income ? `Rp${val.income}` : '-'}</td>
                <td>${val.outcome ? `Rp${val.outcome}` : '-'}</td>
                <td>${val.notes ? `${val.notes}` : '-'}</td>
                <td>${val.created_at ? `${val.created_at}` : '-'}</td>
            </tr>
        `);
    
        counter++;
    });

    
    dtable = new DataTable('#dtable');
})

$.get('/modules/addons/sellBacklink/ajax/getDataBank.php?id=' + id, function (data){
    let selectElement = document.getElementById("dataNorek");

    // Create a new option element
    let option = document.createElement("option");
    
    data.forEach(val => {
        option.value = val.no_rek;
        option.textContent = `${val.name_rek} - ${val.no_rek}`;
        selectElement.appendChild(option);
    });
})

$.get('/modules/addons/sellBacklink/ajax/getTotalDeposit.php?email=' + email, function (data){
    let total_whmcs = document.getElementById("total_whmcs"); 
    let total_backlink = document.getElementById("total_backlink"); 
    let email_user = document.getElementById("email_user"); 
    let nama_user = document.getElementById("nama_user"); 

    total_whmcs.textContent = `Rp${data.credit}`;
    total_backlink.textContent = `Rp${data.credit_backlink}`;
    email_user.textContent = email;
    nama_user.textContent = `${data.firstname} ${data.lastname}`
})

function settingRekening(){
    let nama_bank = document.getElementById("nama_bank").value;
    let no_rek = document.getElementById("no_rek").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    content.textContent = "Sedang menambahkan rekening bank, mohon tunggu..."
    loader.style.display = "flex";
    fetch('/modules/addons/sellBacklink/ajax/settingRekening.php?userid=' + id + '&nama_bank=' + nama_bank + '&no_rek=' + no_rek)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            location.reload();
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}