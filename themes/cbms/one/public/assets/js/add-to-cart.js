const Toast = Swal.mixin({
    toast: true,
    position: "top-right",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

async function addToCart(prodId) {
    const productName = $("#main-product").text();
    const priceDomain = $("#tldPrice").data('price');
    const tld = $("#price-domain p").text();
    const domainName = $("#domain-name").text();
    const regdropdown = $("#inputDomainRegPeriod0");

    let regperiod = $(regdropdown).val();

    $(regdropdown).on("change", async () => {
        regperiod = await regdropdown.val();
        return regperiod
    });

    const domain = {
        type: 'Register Domain',
        price: priceDomain,
        period: regperiod,
        domainName: domainName,
        productName: productName,
        extensionTld: tld,
    };

    console.log(domain);
    // break;

    $("#cart-item").empty();
    $("#cart-item").prepend(
        `
        <div class="px-3 text-right">
            <button class="btn btn-link text-danger text-right p-0" title="Remove" onclick="removeItem()"><i class="fas fa-trash"></i></button>
        </div>
        <a href="#" class="text-reset">
            <div class="media px-3">
            <i class="fas fa-server mr-2" style="font-size: 30px;"></i>
            <div class="media-body">
                <h6 class="mt-0 mb-1">${domain.productName}</h6>
                <p class="font-size-12 mb-1">${domain.domainName}</p>
                <p class="text-right font-weight-bold text-qw mb-0" id="price-dropdown-cart">${domain.price}</p>
                </div>
            </div>
        </a>
        `
    );

    Swal.fire({
        title: "Success",
        text: "You're item has been added to cart!",
        icon: "success",
        confirmButtonColor: "#f7904b",
        cancelButtonColor: "#d33",
        confirmButtonText: "Continue",
    }).then((result) => {
        if (result.isConfirmed) {
            const data = { prodId: prodId, domain: domain };
            const csrf_token = $('meta[name="csrf-token"]').attr("content");
            let url = route("postDataOrder", prodId);
            let urlNext = route('pages.services.order.config', prodId)

            fetch(url, {
                method: "POST", // or 'PUT'
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": csrf_token,
                },
                body: JSON.stringify(data),
            })
                .then((response) => response.json())
                .then((data) => {
                    window.location.replace(urlNext);
                })
                .catch((error) => {
                    console.error("Error:", error);
                });
        }
    });
}

function removeItem() {
    $("#cart-item").empty();

    Toast.fire({
        icon: "success",
        title: "Item Removed",
    });
}
