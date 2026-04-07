
let dtable;
if (dtable) {
    dtable.destroy();
    $('#data').empty();
}
$.get('/modules/addons/sellBacklink/ajax/dataAllTransaction.php', function (data){
    data = data.reverse();
    let counter = 1;
    
    data.forEach(val => {
        $('#data').append(`
            <tr>
                <td>${counter}</td>
                <td>${val.penjual_id ? `<p onclick="redirectToClient(${val.penjual_id})" style="cursor: pointer; color:#202f60">${val.penjual_id}</p>` : '-'}</td>
                <td>${val.pembeli_id ? `<p onclick="redirectToClient(${val.pembeli_id})" style="cursor: pointer; color:#202f60">${val.pembeli_id}</p>` : '-'}</td>
                <td>${val.blog_url ? `<a href="${val.blog_url}">${val.blog_url}</a>` : '-'}</td>
                <td>${val.link_url ? `<a href="${val.link_url}">${val.link_url}</a>` : '-'}</td>
                <td>${val.domain_pembeli_1 ? `${val.domain_pembeli_1} dan ${val.domain_pembeli_2}` : '-'}</td>
                <td>${val.kata_kunci_1 ? `${val.kata_kunci_1} , ${val.kata_kunci_2}` : '-'}</td>
                <td>${val.price ? `Rp${val.price}` : '-'}</td>
                <td>
                ${val.status_penjual === "Need Action" && val.status_invoice === "Unpaid"
                    ? "-"
                    : val.status_penjual === "Need Action" &&  val.status_invoice !== "Unpaid"
                    ? "Need Write Content"
                    : val.status_penjual}
                </td>
                <td>${val.status_admin === "Need Action" ? "-" : val.status_admin}</td>
                <td>
                ${val.status_pembeli === "Need Action" && val.status_invoice === "Unpaid"
                    ? "-"
                    : val.status_pembeli === "Need Action" &&  val.status_invoice !== "Unpaid"
                    ? "Waiting for Blogger"
                    : val.status_pembeli}
                </td>
                <td>${val.invoiceid ? `${val.invoiceid} (${val.status_invoice})` : '-'}</td>
                <td>
                    <div style="display:flex; flex-direction:column; justify-content:center;">
                        <div style="margin-bottom:10px">
                            <button class="btn btn-success" onclick="changeStatusAdmin('${val.uuid ? val.uuid : val.id}', 'Approve')" style="border-radius:8px" data-toggle="modal" data-target="#approveReject">Approve</button>
                            <button class="btn btn-danger" onclick="changeStatusAdmin('${val.uuid ? val.uuid : val.id}', 'Reject')" style="border-radius:8px" data-toggle="modal" data-target="#rejectTransaction">Reject</button>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="modalEditTransaction('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#editTransaction">Edit</button>
                            <button class="btn btn-danger" onclick="modalDeleteTransaction('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#deleteTransaction">Delete</button>
                        </div>
                    </div>
                </td>
              </tr>
        `)
        
        counter++;
    })
    
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

function createTransaction(){
    let penjual_id = document.getElementById("penjual_id").value;
    let pembeli_id = document.getElementById("pembeli_id").value;
    let blog_url = document.getElementById("blog_url").value;
    let link_url = document.getElementById("link_url").value;
    let price = document.getElementById("price").value;
    let status_admin = document.getElementById("status_admin").value;
    let status_client = document.getElementById("status_client").value;
    let invoiceid = document.getElementById("invoiceid").value;
    let loader = document.getElementById("modal_loader_backlink");
    
    if(!penjual_id){
        alert(`Please provide a value for penjual id.`);
        return;
    }
    
    if(!pembeli_id){
        alert(`Please provide a value for pembeli id.`);
        return;
    }
    
    if(!blog_url){
        alert(`Please provide a value for blog url.`);
        return;
    }
    
    if(!link_url){
        alert(`Please provide a value for link url.`);
        return;
    }
    
    if(!price){
        alert(`Please provide a value for price.`);
        return;
    }
    
    if(!invoiceid){
        alert(`Please provide a value for invoice id.`);
        return;
    }
    
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/createTransaction.php?penjual_id=' + penjual_id + '&pembeli_id=' + pembeli_id + '&blog_url=' + blog_url + '&link_url=' + link_url + '&price=' + price + '&status_admin=' + status_admin + '&status_client=' + status_client + '&invoiceid=' + invoiceid)
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

function modalEditTransaction(id){
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    let editElement = document.getElementById("edit_transaction");
    
    editElement.setAttribute('onclick', 'editTransaction("' + id + '")');
    content.textContent = "Getting data transaction, please wait..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/detailTransaction.php?id=' + id )
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            let penjual_id = document.getElementById("penjual_id_edit");
            let pembeli_id = document.getElementById("pembeli_id_edit");
            let blog_url = document.getElementById("blog_url_edit");
            let link_url = document.getElementById("link_url_edit");
            let price = document.getElementById("price_edit");
            let status_admin = document.getElementById("status_admin_edit");
            let status_client = document.getElementById("status_client_edit");
            let invoiceid = document.getElementById("invoiceid_edit");
            
            penjual_id.value = data.penjual_id;
            pembeli_id.value = data.pembeli_id;
            blog_url.value = data.blog_url;
            link_url.value = data.link_url;
            price.value = data.price;
            status_admin.value = data.status_admin;
            status_client.value = data.status_client;
            invoiceid.value = data.invoiceid;
            loader.style.display = "none";
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}

function editTransaction(id){
    let penjual_id = document.getElementById("penjual_id_edit").value;
    let pembeli_id = document.getElementById("pembeli_id_edit").value;
    let blog_url = document.getElementById("blog_url_edit").value;
    let link_url = document.getElementById("link_url_edit").value;
    let price = document.getElementById("price_edit").value;
    let status_admin = document.getElementById("status_admin_edit").value;
    let status_client = document.getElementById("status_client_edit").value;
    let invoiceid = document.getElementById("invoiceid_edit").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    
    if(!penjual_id){
        alert(`Please provide a value for penjual id.`);
        return;
    }
    
    if(!pembeli_id){
        alert(`Please provide a value for pembeli id.`);
        return;
    }
    
    if(!blog_url){
        alert(`Please provide a value for blog url.`);
        return;
    }
    
    if(!price){
        alert(`Please provide a value for price.`);
        return;
    }
    
    if(!invoiceid){
        alert(`Please provide a value for invoice id.`);
        return;
    }
    
    content.textContent = "Editing data blog, please wait..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/editTransaction.php?penjual_id=' + penjual_id + '&pembeli_id=' + pembeli_id + '&blog_url=' + blog_url + '&link_url=' + link_url + '&price=' + price + '&status_admin=' + status_admin + '&status_client=' + status_client + '&invoiceid=' + invoiceid + '&id=' + id)
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

function modalDeleteTransaction(id){
    let deleteElement = document.getElementById("delete_transaction");
    deleteElement.setAttribute('onclick', 'deleteTransaction("' + id + '")');
}

function deleteTransaction(id){
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    content.textContent = "Deleting data transaction, please wait..."
    loader.style.display = "flex";
    fetch('/modules/addons/sellBacklink/ajax/deleteTransaction.php?id=' + id)
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

function changeStatusAdmin(id, status){
    let title = document.getElementById("title-approve-reject");
    let content = document.getElementById("content-approve-reject");
    if(status === "Approve"){
        title.textContent = "Approve Backlink";
        content.textContent = "Apakah Anda ingin approve Backlink ini?";
    }else{
        title.textContent = "Reject Backlink";
        content.textContent = "Apakah Anda ingin reject Backlink ini?";
    }
    let approveRejectElement = document.getElementById("approveReject_transaction");
    approveRejectElement.setAttribute('onclick', 'approveRejectTransaction("' + id + '", "' + String(status) + '")');
    let rejectElement = document.getElementById("reject_transaction");
    rejectElement.setAttribute('onclick', 'approveRejectTransaction("' + id + '", "' + String(status) + '")');

}

function approveRejectTransaction(id, status){
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    let notes = document.getElementById("notes").value;
    content.textContent = "Change status transaction, please wait..."
    loader.style.display = "flex";
    fetch('/modules/addons/sellBacklink/ajax/statusTransaction.php?id=' + id + '&status=' + status + '&notes=' + notes)
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