let urlParamPesan = new URLSearchParams(window.location.search);
let blog = urlParamPesan.get('blog');

$(document).ready(function() {
    $('.js-example-basic-multiple').select2({
        placeholder: "Pilih kategori backlink",
        allowClear: true
    });
    $('.js-example-basic-single').select2();
});

let dtable;

if (dtable) {
    dtable.destroy();
    $('#data').empty();
}

$.get('/modules/addons/sellBacklink/ajax/dataAllBlog.php', function (data) {
    data = data.reverse();
    let counter = 1;

    data.forEach(val => {
        // Append the main row
        $('#data').append(`
            <tr>
                <td>${counter}</td>
                <td>${val.userid ? `<p onclick="redirectToClient(${val.userid})" style="cursor: pointer; color:#202f60">${val.userid}</p>` : '-'}</td>
                <td>${val.client_email ? `${val.client_email}` : '-'}</td>
                <td>${val.blog_name ? `${val.blog_name}` : '-'}</td>
                <td>${val.blog_url ? `<a href="${val.blog_url}">${val.blog_url}</a>` : '-'}</td>
                <td>${val.price ? `Rp${val.price}` : '-'}</td>
                <td>${val.category ? `${val.category}` : '-'}</td>
                <td>${val.language ? `${val.language}` : '-'}</td>
                <td>${val.response_time ? `${val.response_time}` : '-'}</td>
                <td>${val.status ? `${val.status}` : '-'}</td>
                <td>${val.ranking_da ? `${val.ranking_da}` : '-'}</td>
                <td>${val.ranking_pa ? `${val.ranking_pa}` : '-'}</td>
                <td>${val.traffic ? `${val.traffic}` : '-'}</td>
                <td>
                    <button class="btn btn-success" onclick="modalRanking('${val.uuid ? val.uuid : val.id}', ${val.userid})" style="border-radius:8px" data-toggle="modal" data-target="#rankingBlog">Add Ranking</button>
                    <button class="btn btn-primary" onclick="modalEditBlog('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#editBlog">Edit</button>
                    <button class="btn btn-danger" style="border-radius:8px" onclick="modalDeleteBlog('${val.uuid ? val.uuid : val.id}')" data-toggle="modal" data-target="#deleteBlog">Delete</button>
                </td>
            </tr>
        `);

        counter++;
    });

    dtable = new DataTable('#dtable', {
        orderCellsTop: true, 
        columnDefs: [
            { targets: '_all', className: 'text-center' },
        ],
    });
});


let dtablePesan;
if (dtablePesan) {
    dtablePesan.destroy();
    $('#data-pesan').empty();
}
$.get('/modules/addons/sellBacklink/ajax/allBlogFilter.php', function (data){
    data = data.reverse();
    let counter = 1;
    let useridPesan = document.getElementById("myId");
    let myUserid;
    if(useridPesan){
        myUserid = document.getElementById("myId").value;
    }
    data.forEach(val => {
        $('#data-pesan').append(`
            <tr>
                <td>${counter}</td>
                <td>${val.blog_name ? `
                    <div style="display:inline-block">
                        <p style="margin-bottom:0px">
                            ${val.blog_name}
                        </p>
                        ${val.response_time === "Fast Response" ? `
                            <p style="padding:5px;font-size:10px;background-color:#BFD8AF;border-radius:10px;font-weight:bold;width: fit-content;margin: 0 auto;">
                                ${val.response_time}
                            </p>` : 
                            val.response_time === "Medium Response" ? `
                            <p style="padding:5px;font-size:10px;background-color:#FFF3CF;border-radius:10px;font-weight:bold;width: fit-content;margin: 0 auto;">
                                ${val.response_time}
                            </p>` :
                            val.response_time === "Slow Response" ? `
                            <p style="padding:5px;font-size:10px;background-color:#FEA1A1;border-radius:10px;font-weight:bold;width: fit-content;margin: 0 auto;">
                                ${val.response_time}
                            </p>` :
                            ""
                        }
                    </div>` : '-'}
                </td>
                <td>${val.blog_url ? `<a href="${val.blog_url}">${val.blog_url}</a>` : '-'}</td>
                <td>${val.price ? `Rp${val.price}` : '-'}</td>
                <td>${val.category ? `${val.category}` : '-'}</td>
                <td>${val.language ? `${val.language}` : '-'}</td>
                <td>${val.ranking_da ? `${val.ranking_da}` : '-'}</td>
                <td>${val.ranking_pa ? `${val.ranking_pa}` : '-'}</td>
                <td>${val.traffic ? `${val.traffic}` : '-'}</td>
                <td>
                    ${parseInt(val.userid) === parseInt(myUserid) ? `<button class="btn btn-primary" onclick="pesanBacklink('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#pesanBacklink" disabled>Pesan</button>` : `<button class="btn btn-primary" onclick="pesanBacklink('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#pesanBacklink">Pesan</button>`}
                    
                </td>
            </tr>
        `);
    
        counter++;
    });

    if(blog){
        dtablePesan = new DataTable('#dtable-pesan', {
            orderCellsTop: true, 
            columnDefs: [
                { targets: '_all', className: 'text-center' },
            ],
            searching: true,
            search: {
                search: blog
            }
        });
    }else{
        dtablePesan = new DataTable('#dtable-pesan', {
            orderCellsTop: true, 
            columnDefs: [
                { targets: '_all', className: 'text-center' },
            ],
        });
    }
    
})

$.get('https://portal.qwords.com/modules/addons/sellBacklink/ajax/getDataCategory.php', function (data){
    let selectElement = document.getElementById("category");
    let selectElement2 = document.getElementById("category_edit");
    let selectElement3 = document.getElementById("category_filter");

    data.forEach(val => {
        if(selectElement){
            let option = document.createElement("option");
            option.value = val.category_name;
            option.textContent = val.category_name;
            selectElement.appendChild(option);
        }
        
        if(selectElement2){
            let option2 = document.createElement("option");
            option2.value = val.category_name;
            option2.textContent = val.category_name;
            selectElement2.appendChild(option2);
        }
        
        if(selectElement3){
            let option3 = document.createElement("option");
            option3.value = val.category_name;
            option3.textContent = val.category_name;
            selectElement3.appendChild(option3);
        }
    });
});

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

function createBlog(){
    let blog_name = document.getElementById("blog_name").value;
    let blog_url = document.getElementById("blog_url").value;
    let category = document.getElementById("category").value;
    let language = document.getElementById("language").value;
    let price = document.getElementById("price").value;
    
    let userid;
    let elUserid = document.getElementById("userid");
    if(elUserid){
        userid = document.getElementById("userid").value;
    }
    
    let clientid;
    let elClientId  = document.getElementById("user_id");
    if(elClientId){
        clientid = document.getElementById("user_id").value;
    }
    
    let katakunci = document.getElementById("kata_kunci").value;
    let response_time = document.getElementById("response_time").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    
    if(!blog_name){
        alert(`Tolong masukkan value untuk nama blog.`);
        return;
    }
    
    if(!blog_url){
        alert(`Tolong masukkan value untuk url blog.`);
        return;
    }
    
    if(!category){
        alert(`Tolong masukkan value untuk kategori.`);
        return;
    }
    
    if(!language){
        alert(`Tolong masukkan value untuk bahasa.`);
        return;
    }
    
    if(!price){
        alert(`Tolong masukkan value untuk harga.`);
        return;
    }
    
    let createUserId = '';
    
    if(!userid){
        createUserId = clientid;
    }else{
        createUserId = userid;
    }
    
    content.textContent = "Sedang membuat blog, mohon tunggu..."
    loader.style.display = "flex";
    
    if(response_time === "pilih"){
        fetch('/modules/addons/sellBacklink/ajax/createBlog.php?blog_name=' + blog_name + '&blog_url=' + blog_url + '&category=' + category + '&language=' + language + '&price=' + price + '&userid=' + createUserId + '&kata_kunci=' + katakunci)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error!`);
                }
                
                return response.json();
            })
            .then(data => {
                location.reload();
                loader.style.display = "none";
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }else{
        fetch('/modules/addons/sellBacklink/ajax/createBlog.php?blog_name=' + blog_name + '&blog_url=' + blog_url + '&category=' + category + '&language=' + language + '&price=' + price + '&userid=' + createUserId + '&kata_kunci=' + katakunci + '&response_time=' + response_time)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error!`);
                }
                
                return response.json();
            })
            .then(data => {
                location.reload();
                loader.style.display = "none";
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }
}

function modalRanking(id, userid){
    let rankingElement = document.getElementById("ranking_blog");
    rankingElement.setAttribute('onclick', 'rankingBlog("' + id + '",' + userid +')');
}

function rankingBlog(id, userid){
    let ranking_pa = document.getElementById("ranking_pa").value;
    let ranking_da = document.getElementById("ranking_da").value;
    let ranking_traffic = document.getElementById("ranking_traffic").value;
    let status = document.getElementById("status_blog").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    
    if(!status){
        alert(`Tolong masukkan data untuk status.`);
        return;
    }
    if(status === "Reject"){
        let notes = document.getElementById("notes_blog").value;
        if(!notes){
            alert(`Tolong masukkan value untuk catatan.`);
            return;
        }
        content.textContent = "Sedang mengedit data blog, mohon tunggu..."
        loader.style.display = "flex";
        fetch('/modules/addons/sellBacklink/ajax/addRanking.php?status=' + status + '&blog_id=' + id + '&userid=' + userid + '&notes=' + notes)
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
    }else{
        if(!ranking_pa){
            alert(`Tolong masukkan data untuk ranking PA.`);
            return;
        }
        
        if(!ranking_da){
            alert(`Tolong masukkan data untuk ranking DA.`);
            return;
        }
        
        if(!ranking_traffic){
            alert(`Tolong masukkan data untuk ranking traffic.`);
            return;
        }
        content.textContent = "Sedang mengedit data blog, mohon tunggu..."
        loader.style.display = "flex";
        fetch('/modules/addons/sellBacklink/ajax/addRanking.php?ranking_pa=' + ranking_pa + '&ranking_da=' + ranking_da +  '&ranking_traffic=' + ranking_traffic + '&status=' + status + '&blog_id=' + id + '&userid=' + userid)
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
}

function modalEditBlog(id){
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    let editElement = document.getElementById("edit_blog");
    
    editElement.setAttribute('onclick', 'editBlog("' + id + '")');
    content.textContent = "Sedang mengambil data, mohon tunggu..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/detailBlog.php?id=' + id )
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            let blog_name = document.getElementById("blog_name_edit");
            let blog_url = document.getElementById("blog_url_edit");
            let category = document.getElementById("category_edit");
            let language = document.getElementById("language_edit");
            let price = document.getElementById("price_edit");
            let clientid = document.getElementById("user_id_edit");
            let katakunci = document.getElementById("kata_kunci_edit");
            let responsetime = document.getElementById("response_time_edit");
            let categoryArray = data.category.split(',');
            
            $('#category_edit').val(categoryArray).trigger('change');
            blog_name.value = data.blog_name;
            blog_url.value = data.blog_url;
            language.value = data.language;
            price.value = data.price;
            clientid.value = data.userid;
            katakunci.value = data.kata_kunci;
            responsetime.value = data.response_time === "" ? "pilih" : data.response_time;
            loader.style.display = "none";
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}

function editBlog(id){
    let blog_name = document.getElementById("blog_name_edit").value;
    let blog_url = document.getElementById("blog_url_edit").value;
    let category = document.getElementById("category_edit").value;
    let language = document.getElementById("language_edit").value;
    let price = document.getElementById("price_edit").value;
    let clientid = document.getElementById("user_id_edit").value;
    let kata_kunci = document.getElementById("kata_kunci_edit").value;
    let response_time = document.getElementById("response_time_edit").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    
    if(!blog_name){
        alert(`Tolong masukkan data untuk nama blog.`);
        return;
    }
    
    if(!blog_url){
        alert(`Tolong masukkan data untuk url blog.`);
        return;
    }
    
    if(!category){
        alert(`Tolong masukkan data untuk kategori.`);
        return;
    }
    
    if(!language){
        alert(`Tolong masukkan data untuk bahasa.`);
        return;
    }
    
    if(!price){
        alert(`Tolong masukkan data untuk harga.`);
        return;
    }
    
    if(!clientid){
        alert(`Tolong masukkan data untuk user id.`);
        return;
    }
    
    if(response_time){
        content.textContent = "Sedang mengedit data, mohon tunggu..."
        loader.style.display = "flex";
        let response_edit;
        
        if(response_time === "pilih"){
            response_edit = "";
        }else{
            response_edit = response_time;
        }
        
        fetch('/modules/addons/sellBacklink/ajax/editBlog.php?blog_name=' + blog_name + '&blog_url=' + blog_url + '&category=' + category + '&language=' + language + '&price=' + price + '&userid=' + clientid + '&id=' + id + '&kata_kunci=' + kata_kunci + '&response_time=' + response_edit)
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
    
    
}

function modalDeleteBlog(id){
    let deleteElement = document.getElementById("delete_blog");
    deleteElement.setAttribute('onclick', 'deleteBlog("' + id + '")');
}

function deleteBlog(id){
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    content.textContent = "Sedang menghapus data, mohon tunggu..."
    loader.style.display = "flex";
    fetch('/modules/addons/sellBacklink/ajax/deleteBlog.php?id=' + id)
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

function pesanBacklink(id){
    let pesanButtonElement = document.getElementById("pesan_blog_now");
    pesanButtonElement.setAttribute('onclick', 'pesanBlog("' + id + '")');
}

function pesanBlog(blog_id){
    let id = document.getElementById("myId").value;
    let email = document.getElementById("myEmail").value;
    let kata_kunci_1 = document.getElementById("kata_kunci_1").value;
    let kata_kunci_2 = document.getElementById("kata_kunci_2").value;
    let notes = document.getElementById("notes").value;
    let url_website_1 = document.getElementById("url_website_1").value;
    let url_website_2 = document.getElementById("url_website_2").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    if(url_website_1){
        let domain =  url_website_1.split('.')
        if(!domain[0]){
            alert(`Tolong masukkan nama domain Anda.`);
            return;
        }
        
        if(!domain[1]){
            alert(`Tolong masukkan ekstensi domain Anda.`);
            return;
        }
    }
    
    if(url_website_2){
        let domain2 =  url_website_2.split('.')
        if(!domain2[0]){
            alert(`Tolong masukkan nama domain Anda.`);
            return;
        }
        
        if(!domain2[1]){
            alert(`Tolong masukkan ekstensi domain Anda.`);
            return;
        }
    }
    if(!url_website_1){
        alert(`Tolong masukkan url website Anda.`);
        return;
    }
    if(!kata_kunci_1){
        alert(`Tolong masukkan kata kunci Anda.`);
        return;
    }
    content.textContent = "Sedang memproses pesanan, mohon tunggu..."
    loader.style.display = "flex";
    fetch('/modules/addons/sellBacklink/ajax/createOrder.php?blog_id=' + blog_id + '&userid=' + id + '&url_website_1=' + url_website_1 + '&url_website_2=' + url_website_2 + '&email=' + email + '&kata_kunci_1=' + kata_kunci_1 + '&kata_kunci_2=' + kata_kunci_2 + '&notes=' + notes)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            window.location.href = `https://portal.qwords.com/viewinvoice.php?id=${data.invoiceid}`;
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}

function changeStatus(el){
    let notesElement = document.getElementById("notes_element");
    let rankingElement = document.getElementById("ranking_element");
    if(el.value === "Reject"){
        notesElement.style.display = "block";
        rankingElement.style.display = "none";
    }else{
        notesElement.style.display = "none";
        rankingElement.style.display = "block";
    }
}

function simpanFilter(){
    let category = $('#category_filter').val();
    let ranking_da = document.getElementById("ranking_da").value;
    let ranking_pa = document.getElementById("ranking_pa").value;
    let traffic = document.getElementById("traffic").value;
    let harga_minimal = document.getElementById("harga_minimal").value;
    let harga_maksimal = document.getElementById("harga_maksimal").value;
    let kata_kunci = document.getElementById("kata_kunci").value;
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    
    content.textContent = "Sedang memfilter data, mohon tunggu..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/dataAllBlog.php?category=' + category + '&ranking_da=' + ranking_da + '&ranking_pa=' + ranking_pa + '&traffic=' + traffic + '&kata_kunci=' + kata_kunci + '&harga_minimal=' + harga_minimal + '&harga_maksimal=' + harga_maksimal + '&admin=false')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            if (dtablePesan) {
                dtablePesan.destroy();
                $('#data-pesan').empty();
            }
            data = data.reverse();
            let counter = 1;
            let myUserid = document.getElementById("myId").value;
            data.forEach(val => {
                $('#data-pesan').append(`
                    <tr>
                        <td>${counter}</td>
                        <td>${val.blog_name ? `${val.blog_name}` : '-'}</td>
                        <td>${val.blog_url ? `<a href="${val.blog_url}">${val.blog_url}</a>` : '-'}</td>
                        <td>${val.price ? `Rp${val.price}` : '-'}</td>
                        <td>${val.category ? `${val.category}` : '-'}</td>
                        <td>${val.language ? `${val.language}` : '-'}</td>
                        <td>${val.ranking_da ? `${val.ranking_da}` : '-'}</td>
                        <td>${val.ranking_pa ? `${val.ranking_pa}` : '-'}</td>
                        <td>${val.traffic ? `${val.traffic}` : '-'}</td>
                        <td>
                            ${parseInt(val.userid) === parseInt(myUserid) ? `<button class="btn btn-primary" onclick="pesanBacklink('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#pesanBacklink" disabled>Pesan</button>` : `<button class="btn btn-primary" onclick="pesanBacklink('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#pesanBacklink">Pesan</button>`}
                            
                        </td>
                    </tr>
                `);
            
                counter++;
            });
        
            
            dtablePesan = new DataTable('#dtable-pesan');
            loader.style.display = "none";
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}

function filterBlogClient(){
    let category = $('#category_filter').val();
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    
    if(!category){
        alert(`Tolong masukkan kategori blog.`);
        return;
    }
    
    content.textContent = "Sedang memfilter data, mohon tunggu..."
    loader.style.display = "flex";
    
    fetch('/modules/addons/sellBacklink/ajax/dataAllBlog.php?category=' + category)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error!`);
            }
            
            return response.json();
        })
        .then(data => {
            if (dtablePesan) {
                dtablePesan.destroy();
                $('#data-pesan').empty();
            }
            data = data.reverse();
            let counter = 1;
            let myUserid = document.getElementById("myId").value;
            data.forEach(val => {
                $('#data-pesan').append(`
                    <tr>
                        <td>${counter}</td>
                        <td>${val.blog_name ? `${val.blog_name}` : '-'}</td>
                        <td>${val.blog_url ? `<a href="${val.blog_url}">${val.blog_url}</a>` : '-'}</td>
                        <td>${val.price ? `Rp${val.price}` : '-'}</td>
                        <td>${val.category ? `${val.category}` : '-'}</td>
                        <td>${val.language ? `${val.language}` : '-'}</td>
                        <td>${val.ranking_da ? `${val.ranking_da}` : '-'}</td>
                        <td>${val.ranking_pa ? `${val.ranking_pa}` : '-'}</td>
                        <td>${val.traffic ? `${val.traffic}` : '-'}</td>
                        <td>
                            ${parseInt(val.userid) === parseInt(myUserid) ? `<button class="btn btn-primary" onclick="pesanBacklink('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#pesanBacklink" disabled>Pesan</button>` : `<button class="btn btn-primary" onclick="pesanBacklink('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#pesanBacklink">Pesan</button>`}
                            
                        </td>
                    </tr>
                `);
            
                counter++;
            });
        
            
            dtablePesan = new DataTable('#dtable-pesan');
            loader.style.display = "none";
        })
        .catch(error => {
            loader.style.display = "none";
            console.error('Fetch error:', error);
        });
}

$('#category_filter').on('select2:unselecting', function (e) {
    let loader = document.getElementById("modal_loader_backlink");
    let content = document.getElementById("content-modal");
    content.textContent = "Unfiltering order, please wait..."
    loader.style.display = "flex";
    if (dtable) {
        dtable.destroy();
        $('#data').empty();
    }
    $.get('https://portal.qwords.com/modules/addons/sellBacklink/ajax/dataAllBlog.php', function (data){
        data = data.reverse();
        let counter = 1;
    
        data.forEach(val => {
            $('#data').append(`
                <tr>
                    <td>${counter}</td>
                    <td>${val.userid ? `${val.userid}` : '-'}</td>
                    <td>${val.blog_name ? `${val.blog_name}` : '-'}</td>
                    <td>${val.blog_url ? `<a href="${val.blog_url}">${val.blog_url}</a>` : '-'}</td>
                    <td>${val.price ? `Rp${val.price}` : '-'}</td>
                    <td>${val.category ? `${val.category}` : '-'}</td>
                    <td>${val.language ? `${val.language}` : '-'}</td>
                    <td>${val.status ? `${val.status}` : '-'}</td>
                    <td>${val.ranking_ra && val.ranking_da && val.ranking_ga ? `RA:${val.ranking_ra} DA:${val.ranking_da} GA:${val.ranking_ga}` : '-'}</td>
                    <td>
                        <button class="btn btn-success" onclick="modalRanking('${val.uuid ? val.uuid : val.id}', ${val.userid})" style="border-radius:8px" data-toggle="modal" data-target="#rankingBlog">Add Ranking</button>
                        <button class="btn btn-primary" onclick="modalEditBlog('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#editBlog">Edit</button>
                        <button class="btn btn-danger" style="border-radius:8px" onclick="modalDeleteBlog('${val.uuid ? val.uuid : val.id}')" data-toggle="modal" data-target="#deleteBlog">Delete</button>
                    </td>
                </tr>
            `);
        
            counter++;
        });
    
        
        dtable = new DataTable('#dtable');
        loader.style.display = "none";
    })
    
    if (dtablePesan) {
        dtablePesan.destroy();
        $('#data-pesan').empty();
    }
    $.get('https://portal.qwords.com/modules/addons/sellBacklink/ajax/allBlogFilter.php', function (data){
        data = data.reverse();
        let counter = 1;
        let myUserid = document.getElementById("myId").value;
        data.forEach(val => {
            $('#data-pesan').append(`
                <tr>
                    <td>${counter}</td>
                    <td>${val.blog_name ? `${val.blog_name}` : '-'}</td>
                    <td>${val.blog_url ? `<a href="${val.blog_url}">${val.blog_url}</a>` : '-'}</td>
                    <td>${val.price ? `Rp${val.price}` : '-'}</td>
                    <td>${val.category ? `${val.category}` : '-'}</td>
                    <td>${val.language ? `${val.language}` : '-'}</td>
                    <td>${val.ranking_ra && val.ranking_da && val.ranking_ga ? `RA:${val.ranking_ra} DA:${val.ranking_da} GA:${val.ranking_ga}` : '-'}</td>
                    <td>
                        ${parseInt(val.userid) === parseInt(myUserid) ? `<button class="btn btn-primary" onclick="pesanBacklink('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#pesanBacklink" disabled>Pesan</button>` : `<button class="btn btn-primary" onclick="pesanBacklink('${val.uuid ? val.uuid : val.id}')" style="border-radius:8px" data-toggle="modal" data-target="#pesanBacklink">Pesan</button>`}
                        
                    </td>
                </tr>
            `);
        
            counter++;
        });
    
        
        dtablePesan = new DataTable('#dtable-pesan');
        loader.style.display = "none";
    })
});
