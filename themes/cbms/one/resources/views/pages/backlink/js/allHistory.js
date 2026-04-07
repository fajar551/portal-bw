let link = document.getElementById("moduleLink").value;
let dtable;
if (dtable) {
    dtable.destroy();
    $('#data').empty();
}
$.get('/modules/addons/sellBacklink/ajax/allHistoryTransaction.php', function (data){
    data = data.reverse();
    let counter = 1;

    data.forEach(val => {
        $('#data').append(`
            <tr>
                <td>${counter}</td>
                <td>${val.penjual_id ? `<p onclick="redirectToClient(${val.penjual_id})" style="cursor: pointer; color:#202f60">${val.penjual_id}</p>` : '-'}</td>
                <td>${val.first_name ? `${val.first_name} ${val.last_name}` : '-'}</td>
                <td>${val.email ? `${val.email}` : '-'}</td>
                <td>Rp${val.credit_backlink ? `${val.credit_backlink}` : '-'}</td>
                <td>Rp${val.credit ? `${val.credit}` : '-'}</td>
                <td>
                    <a href="${link}&action=detailTransaction&id=${val.penjual_id}&email=${val.email}">
                        <button class="btn btn-primary" style="border-radius:8px" data-toggle="modal" data-target="#editBlog">See Detail</button>
                    </a>
                </td>
            </tr>
        `);
    
        counter++;
    });

    
    dtable = new DataTable('#dtable');
})

function redirectToClient(userid) {
    fetch('/modules/addons/sellBacklink/ajax/redirectToClient.php?userid=' + userid)
        .then(response => response.json())
        .then(data => {
            if (data && data.redirectUrl) {
                window.open(data.redirectUrl, '_blank');
            } else {
                console.error('Invalid response from server:', data);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
        });
}