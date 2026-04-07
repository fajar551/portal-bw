function deleteCustomfiledConfirm(fid) {
    if (confirm("Are you sure you want to delete this field and ALL DATA associated with it?")) {
        window.location = route('admin.pages.setup.customclientfields.delete', {id: fid});
    }
}
