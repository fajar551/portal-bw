// let urlParamBlog = new URLSearchParams(window.location.search);
// let backlink = urlParamBlog.get("backlink");

// if (backlink) {
//     let url_blog = document.getElementById("blog_url");
//     url_blog.value = backlink;
//     $("#createBlog").modal("show");
// }

// $(document).ready(function () {
//     $(".js-example-basic-multiple").select2({
//         placeholder: "Pilih kategori backlink",
//         allowClear: true,
//     });
//     $(".js-example-basic-single").select2();
// });

// let id = document.getElementById("myId").value;
// let uuid = document.getElementById("uuid").value;

// let dtableMyBlog;
// // if (dtableMyBlog) {
// //     dtableMyBlog.destroy();
// //     $("#data-my-blog").empty();
// // }
// $.get(
//     "/backlink/action?action=dataMyBlog&id=" + uuid,
//     function (response) {
//         try {
//             const data = response;
//             dtableMyBlog = new DataTable("#dtable-my-blog");

//             if (data.code === "error") {
//                 alert("Error: " + data.message);
//                 $('#data-my-blog tbody').html('<tr style="background-color: #f0f0f0;"><td colspan="11" class="text-center">No data available</td></tr>');
//                 return;
//             }

//             if (!Array.isArray(data) || data.length === 0) {
//                 console.error("Expected an array but got:", data);
//                 $('#data-my-blog tbody').html('<tr style="background-color: #f0f0f0;"><td colspan="11" class="text-center">No data available</td></tr>');
//                 return;
//             }

//             data.reverse();
//             let counter = 1;

//             data.forEach((val) => {
//                 $("#data-my-blog").append(`
//                 <tr>
//                     <td>${counter}</td>
//                     <td>${val.blog_name ? `${val.blog_name}` : "-"}</td>
//                     <td>${val.blog_url
//                         ? `<a href="${val.blog_url}">${val.blog_url}</a>`
//                         : "-"
//                     }</td>
//                     <td>${val.price ? `Rp${val.price}` : "-"}</td>
//                     <td>${val.category ? `${val.category}` : "-"}</td>
//                     <td>${val.language ? `${val.language}` : "-"}</td>
//                     <td>${val.ranking_da ? `${val.ranking_da}` : "-"}</td>
//                     <td>${val.ranking_pa ? `${val.ranking_pa}` : "-"}</td>
//                     <td>${val.traffic ? `${val.traffic}` : "-"}</td>
//                     <td>${val.status ? `${val.status}` : "-"}</td>
//                     <td>
//                         ${val.status === "Reject"
//                         ? `<button class="btn btn-blue" onclick="modalResubmit('${val.uuid ? val.uuid : val.id
//                         }')" style="border-radius:8px" data-toggle="modal" data-target="#resubmitBlog">Resubmit Blog</button>`
//                         : ""
//                     }
//                         <button class="btn btn-primary" onclick="modalEditBlog('${val.uuid ? val.uuid : val.id
//                     }')" style="border-radius:8px" data-toggle="modal" data-target="#editBlog">Edit</button>
//                         <button class="btn btn-danger" style="border-radius:8px; padding:8px 10px" onclick="modalDeleteBlog('${val.uuid ? val.uuid : val.id
//                     }')" data-toggle="modal" data-target="#deleteBlog">Hapus</button>
//                     </td>
//                 </tr>
//             `);

//                 counter++;
//             });

//         } catch (error) {
//             console.error("Failed to parse response:", error);
//             alert("An error occurred while processing the data.");
//             $('#data-my-blog tbody').html('<tr style="background-color: #f0f0f0;"><td colspan="11" class="text-center">No data available</td></tr>');
//         }
//     }
// ).fail(function (jqXHR, textStatus, errorThrown) {
//     console.log("Request failed: " + textStatus);
//     console.log("Error: " + errorThrown);
//     console.log("Response Text: " + jqXHR.responseText);
//     $('#data-my-blog tbody').html('<tr style="background-color: #f0f0f0;"><td colspan="11" class="text-center">No data available</td></tr>');
// });

// $.get("/modules/addons/sellBacklink/ajax/getDataCategory.php", function (data) {
//     let selectElement = document.getElementById("category");
//     let selectElement2 = document.getElementById("category_edit");

//     data.forEach((val) => {
//         let option = document.createElement("option");
//         option.value = val.category_name;
//         option.textContent = val.category_name;
//         selectElement.appendChild(option);

//         let option2 = document.createElement("option");
//         option2.value = val.category_name;
//         option2.textContent = val.category_name;
//         selectElement2.appendChild(option2);
//     });
// });

// function createBlog() {
//     $("#platformFee").modal("show");
// }

// function informasiPlatformFee() {
//     let category = $("#category").val();
//     let emailAdmin = document.getElementById("emailAdmin").value;
//     let blog_name = document.getElementById("blog_name").value;
//     let blog_url = document.getElementById("blog_url").value;
//     let language = document.getElementById("language").value;
//     let price = document.getElementById("price").value;
//     let kata_kunci = document.getElementById("kata_kunci").value;
//     let loader = document.getElementById("modal_loader_backlink");
//     let content = document.getElementById("content-modal");

//     if (!blog_name) {
//         alert(`Tolong masukkan nama blog Anda.`);
//         return;
//     }

//     if (!blog_url) {
//         alert(`Tolong masukkan url blog Anda.`);
//         return;
//     }

//     if (!category) {
//         alert(`Tolong masukkan kategori blog Anda.`);
//         return;
//     }

//     if (!language) {
//         alert(`Tolong masukkan bahasa blog Anda.`);
//         return;
//     }

//     if (!price) {
//         alert(`Tolong masukkan harga backlink Anda.`);
//         return;
//     }

//     if (price < 50000) {
//         alert(`Harga minimal jual backlink adalah Rp50.000.`);
//         return;
//     }

//     if (!kata_kunci) {
//         alert(`Tolong masukkan kata kunci backlink Anda.`);
//         return;
//     }

//     content.textContent = "Sedang mendaftarkan blog Anda, mohon tunggu...";
//     loader.style.display = "flex";

//     fetch(
//         "/modules/addons/sellBacklink/ajax/createBlog.php?blog_name=" +
//         blog_name +
//         "&blog_url=" +
//         blog_url +
//         "&category=" +
//         category +
//         "&language=" +
//         language +
//         "&price=" +
//         price +
//         "&kata_kunci=" +
//         kata_kunci +
//         "&userid=" +
//         id +
//         "&emailAdmin=" +
//         emailAdmin
//     )
//         .then((response) => {
//             if (!response.ok) {
//                 throw new Error(`HTTP error!`);
//             }

//             return response.json();
//         })
//         .then((data) => {
//             $("#informasiBlog").modal("show");
//             loader.style.display = "none";
//         })
//         .catch((error) => {
//             console.error("Fetch error:", error);
//         });
// }

// function modalEditBlog(id_blog) {
//     let loader = document.getElementById("modal_loader_backlink");
//     let content = document.getElementById("content-modal");
//     let editElement = document.getElementById("edit_blog");

//     editElement.setAttribute("onclick", 'editBlog("' + id_blog + '")');
//     content.textContent = "Sedang mengambil data, mohon tunggu...";
//     loader.style.display = "flex";

//     fetch("/modules/addons/sellBacklink/ajax/detailBlog.php?id=" + id_blog)
//         .then((response) => {
//             if (!response.ok) {
//                 throw new Error(`HTTP error!`);
//             }

//             return response.json();
//         })
//         .then((data) => {
//             let blog_name = document.getElementById("blog_name_edit");
//             let blog_url = document.getElementById("blog_url_edit");
//             let language = document.getElementById("language_edit");
//             let price = document.getElementById("price_edit");
//             let kata_kunci = document.getElementById("kata_kunci_edit");
//             let categoryArray = data.category.split(",");

//             blog_name.value = data.blog_name;
//             blog_url.value = data.blog_url;
//             $("#category_edit").val(categoryArray).trigger("change");
//             language.value = data.language;
//             price.value = data.price;
//             kata_kunci.value = data.kata_kunci;
//             loader.style.display = "none";
//         })
//         .catch((error) => {
//             loader.style.display = "none";
//             console.error("Fetch error:", error);
//         });
// }

// function editBlog(id_blog) {
//     let blog_name = document.getElementById("blog_name_edit").value;
//     let blog_url = document.getElementById("blog_url_edit").value;
//     let category = $("#category_edit").val();
//     let language = document.getElementById("language_edit").value;
//     let price = document.getElementById("price_edit").value;
//     let kata_kunci = document.getElementById("kata_kunci_edit").value;
//     let loader = document.getElementById("modal_loader_backlink");
//     let content = document.getElementById("content-modal");

//     if (!blog_name) {
//         alert(`Tolong masukkan nama blog Anda.`);
//         return;
//     }

//     if (!blog_url) {
//         alert(`Tolong masukkan url blog Anda.`);
//         return;
//     }

//     if (!category) {
//         alert(`Tolong masukkan kategori blog Anda.`);
//         return;
//     }

//     if (!language) {
//         alert(`Tolong masukkan bahasa blog Anda.`);
//         return;
//     }

//     if (!price) {
//         alert(`Tolong masukkan harga backlink Anda.`);
//         return;
//     }

//     if (price < 50000) {
//         alert(`Harga minimal jual backlink adalah Rp50.000.`);
//         return;
//     }

//     if (!kata_kunci) {
//         alert(`Tolong masukkan kata kunci backlink Anda.`);
//         return;
//     }

//     content.textContent = "Sedang mengubah data blog Anda, mohon tunggu...";
//     loader.style.display = "flex";

//     fetch(
//         "/modules/addons/sellBacklink/ajax/editBlog.php?blog_name=" +
//         blog_name +
//         "&blog_url=" +
//         blog_url +
//         "&category=" +
//         category +
//         "&language=" +
//         language +
//         "&price=" +
//         price +
//         "&userid=" +
//         id +
//         "&id=" +
//         id_blog +
//         "&kata_kunci=" +
//         kata_kunci
//     )
//         .then((response) => {
//             if (!response.ok) {
//                 throw new Error(`HTTP error!`);
//             }

//             return response.json();
//         })
//         .then((data) => {
//             location.reload();
//         })
//         .catch((error) => {
//             loader.style.display = "none";
//             console.error("Fetch error:", error);
//         });
// }

// function informasiBlog() {
//     window.location.href = "https://portal.qwords.com/index.php?m=sellBacklink";
// }

// function modalDeleteBlog(id_blog) {
//     let deleteElement = document.getElementById("delete_blog");
//     deleteElement.setAttribute("onclick", 'deleteBlog("' + id_blog + '")');
// }

// function deleteBlog(id_blog) {
//     let loader = document.getElementById("modal_loader_backlink");
//     let content = document.getElementById("content-modal");
//     content.textContent = "Sedang menghapus blog Anda, mohon tunggu...";
//     loader.style.display = "flex";
//     fetch("/modules/addons/sellBacklink/ajax/deleteBlog.php?id=" + id_blog)
//         .then((response) => {
//             if (!response.ok) {
//                 throw new Error(`HTTP error!`);
//             }

//             return response.json();
//         })
//         .then((data) => {
//             location.reload();
//         })
//         .catch((error) => {
//             loader.style.display = "none";
//             console.error("Fetch error:", error);
//         });
// }

// function modalResubmit(id_blog) {
//     let resubmitElement = document.getElementById("resubmit_blog");
//     resubmitElement.setAttribute("onclick", 'resubmitBlog("' + id_blog + '")');
// }

// function resubmitBlog(id_blog) {
//     let emailAdmin = document.getElementById("emailAdmin").value;
//     let loader = document.getElementById("modal_loader_backlink");
//     let content = document.getElementById("content-modal");
//     content.textContent =
//         "Sedang mendaftarkan kembali blog Anda, mohon tunggu...";
//     loader.style.display = "flex";
//     fetch(
//         "/modules/addons/sellBacklink/ajax/resubmitBlog.php?id=" +
//         id_blog +
//         "&emailAdmin=" +
//         emailAdmin
//     )
//         .then((response) => {
//             if (!response.ok) {
//                 throw new Error(`HTTP error!`);
//             }

//             return response.json();
//         })
//         .then((data) => {
//             location.reload();
//         })
//         .catch((error) => {
//             loader.style.display = "none";
//             console.error("Fetch error:", error);
//         });
// }

// function filterBlog() {
//     let category = $("#category_filter").val();
//     let loader = document.getElementById("modal_loader_backlink");
//     let content = document.getElementById("content-modal");

//     if (!category) {
//         alert(`Tolong masukkan filter blog.`);
//         return;
//     }

//     content.textContent = "Sedang memfilter blog, mohon tunggu...";
//     loader.style.display = "flex";

//     fetch(
//         "/modules/addons/sellBacklink/ajax/dataAllBlog.php?category=" + category
//     )
//         .then((response) => {
//             if (!response.ok) {
//                 throw new Error(`HTTP error!`);
//             }

//             return response.json();
//         })
//         .then((data) => {
//             if (dtableMyBlog) {
//                 dtableMyBlog.destroy();
//                 $("#data-my-blog").empty();
//             }
//             data = data.reverse();
//             let counter = 1;

//             data.forEach((val) => {
//                 $("#data-my-blog").append(`
//                     <tr>
//                         <td>${counter}</td>
//                         <td>${val.blog_name ? `${val.blog_name}` : "-"}</td>
//                         <td>${val.blog_url
//                         ? `<a href="${val.blog_url}">${val.blog_url}</a>`
//                         : "-"
//                     }</td>
//                         <td>${val.price ? `Rp${val.price}` : "-"}</td>
//                         <td>${val.category ? `${val.category}` : "-"}</td>
//                         <td>${val.language ? `${val.language}` : "-"}</td>
//                         <td>${val.ranking_da ? `${val.ranking_da}` : "-"}</td>
//                         <td>${val.ranking_pa ? `${val.ranking_pa}` : "-"}</td>
//                         <td>${val.traffic ? `${val.traffic}` : "-"}</td>
//                         <td>${val.status ? `${val.status}` : "-"}</td>
//                         <td>
//                             ${val.status === "Reject"
//                         ? `<button class="btn btn-blue" onclick="modalResubmit('${val.uuid ? val.uuid : val.id
//                         }')" style="border-radius:8px" data-toggle="modal" data-target="#resubmitBlog">Resubmit Blog</button>`
//                         : ""
//                     }
//                             <button class="btn btn-primary" onclick="modalEditBlog('${val.uuid ? val.uuid : val.id
//                     }')" style="border-radius:8px" data-toggle="modal" data-target="#editBlog">Edit</button>
//                             <button class="btn btn-danger" style="border-radius:8px; padding:8px 10px" onclick="modalDeleteBlog('${val.uuid ? val.uuid : val.id
//                     }')" data-toggle="modal" data-target="#deleteBlog">Hapus</button>
//                         </td>
//                     </tr>
//                 `);

//                 counter++;
//             });

//             dtableMyBlog = new DataTable("#dtable-my-blog");
//             loader.style.display = "none";
//         })
//         .catch((error) => {
//             loader.style.display = "none";
//             console.error("Fetch error:", error);
//         });
// }

// $("#category_filter").on("select2:unselecting", function (e) {
//     let loader = document.getElementById("modal_loader_backlink");
//     let content = document.getElementById("content-modal");
//     content.textContent = "Sedang menghilangkan filter, mohon tunggu...";
//     loader.style.display = "flex";
//     if (dtableMyBlog) {
//         dtableMyBlog.destroy();
//         $("#data-my-blog").empty();
//     }
//     $.get(
//         "https://portal.qwords.com/modules/addons/sellBacklink/ajax/dataMyBlog.php?id=" +
//         uuid,
//         function (data) {
//             data = data.reverse();
//             let counter = 1;

//             data.forEach((val) => {
//                 $("#data-my-blog").append(`
//                 <tr>
//                     <td>${counter}</td>
//                     <td>${val.blog_name ? `${val.blog_name}` : "-"}</td>
//                     <td>${val.blog_url
//                         ? `<a href="${val.blog_url}">${val.blog_url}</a>`
//                         : "-"
//                     }</td>
//                     <td>${val.price ? `Rp${val.price}` : "-"}</td>
//                     <td>${val.category ? `${val.category}` : "-"}</td>
//                     <td>${val.language ? `${val.language}` : "-"}</td>
//                     <td>${val.status ? `${val.status}` : "-"}</td>
//                     <td>${val.notes ? `${val.notes}` : "-"}</td>
//                     <td>${val.ranking_ra && val.ranking_da && val.ranking_ga
//                         ? `RA:${val.ranking_ra} DA:${val.ranking_da} GA:${val.ranking_ga}`
//                         : "-"
//                     }</td>
//                     <td>
//                         ${val.status === "Reject"
//                         ? `<button class="btn btn-blue" onclick="modalResubmit('${val.uuid ? val.uuid : val.id
//                         }')" style="border-radius:8px" data-toggle="modal" data-target="#resubmitBlog">Resubmit Blog</button>`
//                         : ""
//                     }
//                         <button class="btn btn-primary" onclick="modalEditBlog('${val.uuid ? val.uuid : val.id
//                     }')" style="border-radius:8px" data-toggle="modal" data-target="#editBlog">Edit</button>
//                         <button class="btn btn-danger" style="border-radius:8px; padding:8px 10px" onclick="modalDeleteBlog('${val.uuid ? val.uuid : val.id
//                     }')" data-toggle="modal" data-target="#deleteBlog">Hapus</button>
//                     </td>
//                 </tr>
//             `);

//                 counter++;
//             });

//             dtableMyBlog = new DataTable("#dtable-my-blog");
//             loader.style.display = "none";
//         }
//     );
// });