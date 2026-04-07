let id = document.getElementById("uuid").value;
let email = document.getElementById("myEmail").value;
let dtable;
if (dtable) {
    dtable.destroy();
    $('#data').empty();
}
$.get('/modules/addons/sellBacklink/ajax/myHistory.php?id=' + id, function (data) {

    dataTableHistory = data.data.reverse();

    let counter = 1;

    dataTableHistory.forEach(val => {
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
    var dtableWrapper = $('#dtable_wrapper');
    var sortingClassWidth = $('.sorting').width();
    var newDiv = $('<div>').append(`
        <table style="width:35%" class="table table-striped table-bordered">
            <tr>
                <td style="font-weight:bold;">Total:</td>
                <td style="font-weight:bold;">${data.totalIncome ? `Rp${data.totalIncome}` : '-'}</td>
                <td style="font-weight:bold;">${data.totalOutcome ? `Rp${data.totalOutcome}` : '-'}</td>
            </tr>
        </table>
        `);

    dtableWrapper.find('div:eq(6)').after(newDiv);
})


$.get('/modules/addons/sellBacklink/ajax/getDataBank.php?id=' + id, function (data){
    let selectElement = document.getElementById("dataNorek");
    let selectElement2 = document.getElementById("dataCair");

    data.forEach(val => {
        // Create a new option element for dataNorek
        let option = document.createElement("option");
        option.value = val.id;
        option.textContent = `${val.name_rek} - ${val.no_rek}`;
        selectElement.appendChild(option);

        // Create a new option element for dataCair
        let option2 = document.createElement("option");
        option2.value = val.id;
        option2.textContent = `${val.name_rek} - ${val.no_rek}`;
        selectElement2.appendChild(option2);
    });
});

$.get('/modules/addons/sellBacklink/ajax/getTotalDeposit.php?uuid=' + id, function (data){
    let total_whmcs = document.getElementById("total_whmcs"); 
    let total_backlink = document.getElementById("total_backlink"); 
    let total_backlink_hidden = document.getElementById("total_backlink_hidden"); 
    let total_whmcs_hidden = document.getElementById("total_whmcs_hidden"); 

    total_whmcs.textContent = `Rp${data.credit}`;
    total_backlink.textContent = `Rp${data.credit_backlink}`;
    total_backlink_hidden.value = data.credit_backlink;
    total_whmcs_hidden.value = data.credit;
})

function settingRekening(){
    let nama_bank = document.getElementById("nama_bank").value;
    let no_rek = document.getElementById("no_rek").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    content.textContent = "Setting rekening bank, please wait..."
    loader.style.display = "flex";
    fetch('/modules/addons/sellBacklink/ajax/settingRekening.php?uuid=' + id + '&nama_bank=' + nama_bank + '&no_rek=' + no_rek + '&email=' + email)
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

function cairkanDana(){
    let valueRekening = document.getElementById("dataCair").value;
    let requestedPrice = document.getElementById("requested-price").value;
    let total_backlink_hidden = document.getElementById("total_backlink_hidden").value;
    let total_whmcs_hidden = document.getElementById("total_whmcs_hidden").value;
    
    if(parseFloat(requestedPrice) < 200000){
        alert(`Dana yang ingin Anda cairkan kurang dari Rp200.000`);
        return;
    }
    
    if(parseFloat(total_backlink_hidden) < 200000){
        alert(`Total deposit backlink terjual Anda kurang dari Rp200.000`);
        return;
    }
    
    if(parseFloat(total_backlink_hidden) < parseFloat(requestedPrice)){
        alert(`Total deposit backlink terjual Anda kurang dari dana yang ingin dicairkan`);
        return;
    }
    
    if(parseFloat(total_whmcs_hidden) < parseFloat(total_backlink_hidden)){
        alert(`Total deposit whmcs lebih sedikit dari total deposit backlink! Mohon hubungi Admin untuk lebih lanjut`);
        return;
    }
    
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    content.textContent = "Processing deposit, please wait..."
    loader.style.display = "flex";
    fetch('/modules/addons/sellBacklink/ajax/cairkanDana.php?uuid=' + id + '&valueRekening=' + valueRekening + '&email=' + email + '&price=' + requestedPrice )
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            window.location.href = `https://portal.qwords.com/viewticket.php?tid=${data.tid}&c=${data.c}`
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}