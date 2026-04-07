<header id="page-topbar">
   <div class="navbar-header">
      <div class="d-flex align-items-center">
         <button type="button" class="btn btn-sm mr-2 d-lg-none header-item" id="vertical-menu-btn">
            <i class="fa fa-fw fa-bars"></i>
         </button>

         <div class="header-breadcumb">
            <div class="p-0 m-0 navbar-page-header">@yield('page-title')</div>
         </div>
      </div>
      <div class="d-flex align-items-center">

        {{-- Cart icon --}}
         {{-- <div class="dropdown d-inline-block ml-2">
            <button type="button" class="btn header-item noti-icon" id="page-header-cart-dropdown"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <i class="fas fa-shopping-cart"></i>
               <span class="badge badge-danger badge-pill">6</span>
            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
               aria-labelledby="page-header-cart-dropdown" id="cart-container">
               <div class="pt-3 px-3">
                  <div class="row align-items-center">
                     <div class="col">
                        <h6 class="m-0"> Cart  </h6>
                        <hr>
                     </div>
                  </div>
               </div>
               <div data-simplebar style="max-height: 230px;" id="cart-item">
                    <h5 class="text-center">No item</h5>
               </div>
               <div class="p-2 border-top">
                  <a class="btn btn-sm btn-success btn-block text-center" href="javascript:void(0)">
                     <i class="mdi mdi-arrow-down-circle mr-1"></i> View All
                  </a>
               </div>
            </div>
         </div> --}}

         {{-- Cart icon --}}
          <div class="dropdown d-inline-block ml-2">
            <button type="button" class="btn header-item noti-icon" id="page-header-cart-dropdown"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-shopping-cart"></i>
              @if(session('cart_summary'))
                  <span class="badge badge-danger badge-pill">{{ session('cart_summary')['count'] }}</span>
              @endif
            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
              aria-labelledby="page-header-cart-dropdown" id="cart-container">
              <div class="pt-3 px-3">
                  <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0">Shopping Cart</h6>
                        <hr>
                    </div>
                  </div>
              </div>
              <div data-simplebar style="max-height: 230px;" id="cart-item">
                  @if(session('cart_summary') && session('cart_summary')['items']->count() > 0)
                    @foreach(session('cart_summary')['items'] as $item)
                        <div class="p-2 border-bottom">
                          <div class="row align-items-center">
                              <div class="col ml-2">
                                <h6 class="m-0">{{ $item->name }}</h6>
                                <p class="mb-0 font-size-12">{{ Format::formatCurrency($item->price) }}</p>
                              </div>
                              <div class="col-auto d-flex align-items-center">
                                <span class="badge badge-primary mr-2">{{ $item->quantity }}</span>
                                <button class="btn btn-sm text-danger" onclick="removeCartItem('{{ $item->id }}')" style="padding: 0;">
                                    <i class="fas fa-trash"></i>
                                </button>
                              </div>
                          </div>
                        </div>
                    @endforeach
                  @else
                    <h5 class="text-center p-3">No items</h5>
                  @endif
              </div>
              @if(session('cart_summary') && session('cart_summary')['items']->count() > 0)
                  <div class="p-2 border-top">
                    <div class="d-flex justify-content-between p-2">
                        <span>Subtotal:</span>
                        <span>{{ Format::formatCurrency(session('cart_summary')['subtotal']) }}</span>
                    </div>
                    <div class="d-flex justify-content-between p-2">
                        <span>Total:</span>
                        <span>{{ Format::formatCurrency(session('cart_summary')['total']) }}</span>
                    </div>
                    <a class="btn btn-sm btn-success btn-block text-center" href="{{ route('pages.services.order.viewchart', ['id' => session('cart_summary')['items']->first()->id]) }}">
                        View Cart
                    </a>
                  </div>
              @endif
            </div>
          </div>

        {{-- Notification --}}
         <div class="dropdown d-inline-block ml-2">
            <button type="button" class="btn header-item noti-icon" id="page-header-notifications-dropdown"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <i class="fas fa-bell"></i>
               {{-- <span class="badge badge-danger badge-pill">6</span> --}}
            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
               aria-labelledby="page-header-notifications-dropdown" id="notification-container">
               <div class="p-3">
                  <div class="row align-items-center">
                     <div class="col">
                        <h6 class="m-0"> Notifications </h6>
                     </div>
                     <div class="col-auto">
                        <a href="#!" class="small"> View All</a>
                     </div>
                  </div>
               </div>
               <div data-simplebar style="max-height: 230px; padding-bottom: 10px">
                  <h6 class="text-center text-muted">No Notification</h6>
               </div>
            </div>
         </div>

         @auth
            <div class="dropdown d-inline-block ml-2">
               <button type="button" class="btn header-item" id="page-header-user-dropdown" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
                  <img class="rounded-circle header-profile-user"
                     src="{{ Theme::asset('assets/images/users/avatar-2.jpg') }}" alt="Header Avatar">
                  <span
                     class="d-none d-sm-inline-block ml-1 user-name">{{ ucfirst(Auth::user()->firstname . ' ' . Auth::user()->lastname) }}</span>
                  <i class="mdi mdi-chevron-down d-none d-sm-inline-block"></i>
               </button>
               <div class="dropdown-menu dropdown-menu-right">

                  <a class="dropdown-item d-flex align-items-center justify-content-between"
                     href="{{ url('emailnotes') }}">
                     <span>Email Notes</span>
                     {{-- <span>
                        <span class="badge badge-pill badge-danger">3</span>
                     </span> --}}
                  </a>
                  <a class="dropdown-item d-flex align-items-center justify-content-between"
                     href="{{ url('uploadterms') }}">
                     <span>Upload Account Terms</span>
                  </a>
                  <a class="dropdown-item d-flex align-items-center justify-content-between"
                     href="{{ url('detailprofile') }}">
                     <span>Edit Account Details</span>
                  </a>
                  <a class=" dropdown-item d-flex align-items-center justify-content-between"
                     href="{{ url('contactsub') }}">
                     <span>Contact / Sub-Account</span>
                  </a>
                  <a class="dropdown-item d-flex align-items-center justify-content-between"
                     href="{{ url('securitysettings') }}">
                     <span>Security Settings</span>
                  </a>
                  <a class="dropdown-item d-flex align-items-center justify-content-between"
                     href="{{ url('updatepassword') }}">
                     <span>Update Password</span>
                  </a>

                  <a class="dropdown-item d-flex align-items-center justify-content-between"
                     href="{{ route('logout') }}">
                     <span class="text-danger">Log Out</span>
                  </a>
                  <form action="{{ route('logout') }}" method="POST">
                     @csrf
                  </form>
               </div>
            </div>
         @endauth

      </div>
   </div>
</header>
<script src="{{ Theme::asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>

let actionUrl2 = route('commandAJAX')
      const csrfRootNavbar = $('meta[name="csrf-token"]').attr("content");
      const url2 = actionUrl2;

    async function removeCartItem(el) {
         const url2 = actionUrl2;
         const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            showConfirmButton: false,
            timer: 1000,
            timerProgressBar: true
         });

         // Tambahkan konfirmasi sebelum menghapus
         const result = await Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Item ini akan dihapus dari keranjang",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
         });

         // Jika user klik "Ya, hapus!"
         if (result.isConfirmed) {
            const getCycle = $('#billingCycle').val() ?? 'Monthly'
            const cycle = getCycle.toLowerCase();
            const data = {
               action: 'removeitem',
               sessionCartId: el
            }

            fetch(url2, {
               method: 'POST',
               headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-Token': csrfRootNavbar,
               },
               body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(async res => {
               await Toast.fire({
                  icon: 'success',
                  title: 'Sukses',
                  text: 'Item Sudah Terhapus!'
               })
               
               // Update cart navbar
               updateCartNavbar(res.data.cartSummary);
               
               await updatePayment(cycle)
               $('.updated-price-holder').empty()
            }).catch(err => console.log(err))
         }
      }

      function updateCartNavbar(cartSummary) {
         // Update badge count
         const badge = $('#page-header-cart-dropdown .badge');
         if (cartSummary.count > 0) {
            badge.text(cartSummary.count);
            badge.show();
         } else {
            badge.hide();
         }

         // Update cart items
         let cartItemsHtml = '';
         if (cartSummary.items && cartSummary.items.length > 0) {
            cartSummary.items.forEach(item => {
               cartItemsHtml += `
                  <div class="p-2 border-bottom">
                     <div class="row align-items-center">
                        <div class="col">
                           <h6 class="m-0">${item.name}</h6>
                           <p class="mb-0 font-size-12">${item.attributes.priceformatted}</p>
                        </div>
                        <div class="col-auto">
                           <span class="badge badge-primary">${item.quantity}</span>
                        </div>
                     </div>
                  </div>
               `;
            });

            // Add totals and view cart button
            cartItemsHtml += `
               <div class="p-2 border-top">
                  <div class="d-flex justify-content-between p-2">
                     <span>Subtotal:</span>
                     <span>${cartSummary.subtotal}</span>
                  </div>
                  <div class="d-flex justify-content-between p-2">
                     <span>Total:</span>
                     <span>${cartSummary.total}</span>
                  </div>
                  <a class="btn btn-sm btn-success btn-block text-center" href="{{ route('pages.services.order.viewchart', ['id' => ':id']) }}".replace(':id', cartSummary.items[0].id)>
                     View Cart
                  </a>
               </div>
            `;
         } else {
            cartItemsHtml = '<h5 class="text-center p-3">No items</h5>';
         }

         $('#cart-item').html(cartItemsHtml);
         
         // If cart is empty, redirect to cart page
         if (!cartSummary.items || cartSummary.items.length === 0) {
            window.location.href = "{{ route('pages.services.myservices.index') }}";
         }
      }
</script>
