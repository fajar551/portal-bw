let urlParamPenjualan = new URLSearchParams(window.location.search);
let idParam = urlParamPenjualan.get('id');
let statusPenjual = urlParamPenjualan.get('status_penjual');
let statusPembeli = urlParamPenjualan.get('status_pembeli');
let urlBlog = urlParamPenjualan.get('url_blog');

let id = document.getElementById("myId").value;
let email = document.getElementById("myEmail").value;

if(idParam && statusPenjual && statusPembeli && urlBlog){
    $('#modalPesan').modal('show');
    modalKirimPesan(idParam, statusPenjual, statusPembeli, urlBlog);
}

let dtablePesan;
if (dtablePesan) {
    dtablePesan.destroy();
    $('#data-pesan').empty();
}
$.get('/modules/addons/sellBacklink/ajax/dataTransactionPembeli.php?id=' + id, function (data){
    data = data.reverse();
    let counter = 1;

    data.forEach(val => {
        $('#data-pesan').append(`
            <tr>
                <td>${counter}</td>
                <td>${val.blog_url ? `<a href="${val.blog_url}">${val.blog_url}</a>` : '-'}</td>
                <td>${val.status_admin !== 'Approve' ? '' : `<a href="${val.link_url}">${val.link_url}</a>`}</td>
                <td>${val.price ? `Rp${val.price}` : '-'}</td>
                <td>
                  ${
                    val.status_admin === "Reject"
                      ? "Revisions"
                      : val.status_penjual === "Submitted" && val.status_admin === "Approve" && val.status_pembeli !== "Done"
                      ? "Submitted"
                      : val.status_penjual === "Submitted" && val.status_admin === "Need Action"
                      ? "Need Action"
                      : val.status_pembeli === "Done"
                      ? "Success"
                      : val.status_penjual
                      ? val.status_penjual
                      : "-"
                  }
                </td>
                <td>${val.status_penjual === "Approved" || val.status_penjual === "Need Action" ? '-' : val.status_pembeli && (val.status_penjual !== "Approved" || val.status_penjual !== "Need Action") ? `${val.status_pembeli}` : '-'}</td>
                <td>
                  ${val.notes ? val.notes : '-'}
                </td>
                <td><a href="https://portal.qwords.com/viewinvoice.php?id=${val.invoiceid}">#${val.invoiceid}</a></td>
                <td>
                    ${val.status_admin === "Approve" ? `
                        <button class="btn btn-primary" onclick="modalApprove('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px; margin:5px;" data-toggle="modal" data-target="#modalApprove">Approve</button>
                    ` : ''}
                    ${val.status_pembeli === 'Done' ? "-" : `
                        <button class="btn btn-danger" onclick="modalKirimPesan('${val.uuid ? val.uuid : val.id}', '${val.status_penjual}', '${val.status_pembeli}', '${val.blog_url}')" style="border-radius:8px; padding: 8px 10px; margin:5px;" data-toggle="modal" data-target="#modalPesan">Chat Penjual</button>`}
                </td>
            </tr>
        `);
    
        counter++;
    });


    
    dtablePesan = new DataTable('#dtable-pesan');
})

function modalEditLinkUrl(id_blog){
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    let editElement = document.getElementById("edit_link_url");
    
    editElement.setAttribute('onclick', 'editLinkUrl("' + id_blog + '")');
    content.textContent = "Sedang mengambil data, mohon tunggu..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/detailTransaction.php?id=' + id_blog )
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            let link_url = document.getElementById("link_url_edit");
            
            link_url.value = data.link_url;
            loader.style.display = "none";
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}

function editLinkUrl(id_blog){
    let link_url = document.getElementById("link_url_edit").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    if(!link_url){
        alert(`Tolong masukkan link url Anda.`);
        return;
    }
    
    content.textContent = "Sedang mengedit link url Anda, mohon tunggu..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/editLinkUrl.php?link_url=' + link_url + '&id=' + id_blog)
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

function modalApprove(id_blog){
    let approveElement = document.getElementById("approve_transaction");
    approveElement.setAttribute('onclick', 'approveBacklink("' + id_blog + '")');
}

function approveBacklink(id_blog){
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    content.textContent = "Sedang mengganti status backlink, mohon tunggu..."
    loader.style.display = "flex";
    fetch('/modules/addons/sellBacklink/ajax/statusTransaction.php?id=' + id_blog + '&status=Approve&type=pembeli&email=' +email)
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

function modalLaporAdmin(invoiceid, transaksi_id){
    let laporElement = document.getElementById("lapor_transaction");
    laporElement.setAttribute('onclick', 'laporAdmin(' + invoiceid + ',"' + transaksi_id + '")');
}

function laporAdmin(invoiceid, transaksi_id){
    let notes = document.getElementById("notes").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    if(!notes){
        alert(`Tolong masukkan catatan Anda.`);
        return;
    }
    
    content.textContent = "Sedang melaporkan backlink, mohon tunggu..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/laporAdmin.php?notes=' + notes + '&transaksi_id=' + transaksi_id + '&userid=' + id + '&email=' + email)
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

function modalKirimPesan(transaksi_id, status_penjual, status_pembeli, url){
    let pesanElement = document.getElementById("kirim_pesan");
    let status_penjual_element = document.getElementById("status_penjual");
    let status_pembeli_element = document.getElementById("status_pembeli");
    let nama_penjual_element = document.getElementById("nama_penjual");
    let nama_pembeli_element = document.getElementById("nama_pembeli");
    let url_blog_element = document.getElementById("url_blog");
    
    status_penjual_element.textContent = 'Status Penjual: ' + status_penjual;
    status_pembeli_element.textContent = 'Status Pembeli: ' + status_pembeli;
    url_blog_element.textContent = url;
    
    pesanElement.setAttribute('onclick', 'kirimPesan("' + transaksi_id + '")');
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    
    content.textContent = "Sedang mengambil data pesan, mohon tunggu..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/getPesan.php?transaksi_id=' + transaksi_id + '&userid=' + id)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            loader.style.display = "none";
            nama_penjual_element.textContent = "Penjual: " + data.penjual;
            nama_pembeli_element.textContent = "Pembeli: " + data.pembeli;
            renderMessages(data.data_chat);
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}

function renderMessages(messages) {
    // Assuming messages is an array of chat data
    const messageContainer = document.getElementById("message_container");

    // Clear existing messages
    messageContainer.innerHTML = "";

    messages.forEach((message, index) => {
        const messageDiv = document.createElement("div");
        messageDiv.classList.add(`chatId-${index + 1}`, "chat-container");

        const nameDiv = document.createElement("div");
        nameDiv.innerHTML = `<h4 style="font-weight:bold" class="${message.role === 'Penjual' ? 'chat-penjual' : 'chat-pembeli'}">${message.nama.charAt(0)}</h4>`;

        const messageContentDiv = document.createElement("div");
        messageContentDiv.classList.add(`message-${index + 1}`);
        messageContentDiv.style.marginLeft = "20px";
        messageContentDiv.innerHTML = `
            <p><span style="font-weight:bold">${message.nama} (${message.role}) -</span> ${message.created_at}</p>
            <p style="margin-bottom:0px">${message.message}</p>
        `;

        messageDiv.appendChild(nameDiv);
        messageDiv.appendChild(messageContentDiv);

        messageContainer.appendChild(messageDiv);
    });
    
    if(messages.length === 0){
        messageContainer.innerHTML = `<p>Tidak ada komentar</p>`;
    }
}

function kirimPesan(transaksi_id){
    let message = document.getElementById("message").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    if(!message){
        alert(`Tolong masukkan pesan Anda.`);
        return;
    }
    
    content.textContent = "Sedang mengirim pesan, mohon tunggu..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/kirimPesan.php?message=' + message + '&transaksi_id=' + transaksi_id + '&userid=' + id)
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