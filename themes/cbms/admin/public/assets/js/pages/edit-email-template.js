// $('input[type="file"]').on("change", function(e) {
//     let fileName = e.target.files[0].name;
//     $(".custom-file-label").html(fileName);
// });

$("#anotherFileInput").on("click", function() {
    $(".attachmentsfiles").append(`<input class="form-control mb-3" type="file" name="attachments[]">`);
    // $(".file-column").append(`
    // <div class="input-group mb-3">
    //     <div class="custom-file">
    //         <input type="file" class="custom-file-input"
    //             id="inputGroupFile01">
    //         <label class="custom-file-label" for="inputGroupFile01"
    //             aria-describedby="inputGroupFileAddon01">Choose
    //             file</label>
    //     </div>
    // </div>
    // `);
});
