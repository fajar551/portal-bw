$(document).ready(function () {
    bsCustomFileInput.init();
});

$("#attachmentFile").on("change", function () {
    bsCustomFileInput.init();
});

$(".btn-add-files").on("click", function () {
    const uniq = Math.floor(Math.random() * 20);
    bsCustomFileInput.init();
    $("#attachmentFile").append(
        `
            <div class="custom-file mb-1">
                <input type="file" class="custom-file-input" id="inputGroupFile0${uniq}" name="attachments[]">
                <label class="custom-file-label" for="inputGroupFile0${uniq}">Choose file</label>
            </div>
        `
    );
});
