let dtable;
if (dtable) {
    dtable.destroy();
    $('#data').empty();
}
$.get('/modules/addons/sellBacklink/ajax/dataFreezeAccounts.php', function (data){
    data = data.reverse();
    let counter = 1;
    
    data.forEach(val => {
        $('#data').append(`
            <tr>
                <td>${counter}</td>
                <td>${val.clientid ? `${val.clientid}` : '-'}</td>
                <td>${val.status ? `${val.status}` : '-'}</td>
                <td>
                    <button class="btn btn-primary" onclick="editModal('${val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#edit_freeze">Edit</button>
                        
                    <button class="btn btn-danger" onclick="deleteModal('${val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#delete_freeze">Delete</button>
                </td>
              </tr>
        `)
        
        counter++;
    })
    
    dtable = new DataTable('#dtable');
})

function createFreeze(){
    let clientid = document.getElementById("clientid").value;
    let status = document.getElementById("status").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    
    if(!clientid){
        alert(`Please provide a value for client id.`);
        return;
    }
    
    if(!status){
        alert(`Please provide a value for status.`);
        return;
    }
    
    loader.style.display = "flex";
    content.textContent = "Creating data freeze account, please wait..."
    
    fetch('/modules/addons/sellBacklink/ajax/createFreezeAccount.php?clientid=' + clientid + '&status=' + status )
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

function editModal(id){
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    let editElement = document.getElementById("edit_freeze_button");
    
    editElement.setAttribute('onclick', 'editFreeze("' + id + '")');
    content.textContent = "Getting data freeze account, please wait..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/detailFreezeAccount.php?id=' + id )
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            let clientid = document.getElementById("clientid_edit");
            let status = document.getElementById("status_edit");
            
            clientid.value = data.clientid;
            status.value = data.status;
            loader.style.display = "none";
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}

function editFreeze(id){
    let clientid = document.getElementById("clientid_edit").value;
    let status = document.getElementById("status_edit").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    
    if(!clientid){
        alert(`Please provide a value for clientid.`);
        return;
    }
    
    if(!status){
        alert(`Please provide a value for status.`);
        return;
    }
    
    content.textContent = "Editing data freeze account, please wait..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/editFreezeAccount.php?clientid=' + clientid + '&status=' + status + '&id=' + id)
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

function deleteModal(id){
    let deleteElement = document.getElementById("delete_freeze");
    deleteElement.setAttribute('onclick', 'deleteFreeze("' + id + '")');
}

function deleteFreeze(id){
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    content.textContent = "Deleting data freeze account, please wait..."
    loader.style.display = "flex";
    fetch('/modules/addons/sellBacklink/ajax/deleteFreeze.php?id=' + id)
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