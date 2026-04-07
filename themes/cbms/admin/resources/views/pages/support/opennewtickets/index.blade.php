@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Open New Ticket</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">

                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Open New Ticket</h4>
                                    </div>
                                </div>
                            </div>

                            @if (Session::has('success'))
                                <div class="alert alert-success">
                                    {{ Session::get('success') }}
                                    @php
                                        Session::forget('success');
                                    @endphp
                                </div>
                            @endif

                            <form action="../support/opennewtickets/store" method="post" enctype="multipart/form-data"
                                novalidate id="ticketForm">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card p-3">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <label for="client-name"
                                                            class="col-sm-2 col-form-label">Client</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <select name="clientid" id="client-name"
                                                                class="form-control select2 {{ $errors->has('clientid') ? 'is-invalid' : '' }}"
                                                                style="width: 100%;" required>
                                                                @if (!empty($user))
                                                                    <option value="{{ $user->id }}">
                                                                        {{ $user->firstname }} {{ $user->lastname }} |
                                                                        {{ $user->email }} | {{ $user->companyname }}
                                                                    </option>
                                                                @endif
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                Please select a client.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label for="product-service" class="col-sm-2 col-form-label">Product/Service</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <select name="product_id" id="product-service" class="form-control">
                                                                <option value="">None</option>
                                                                @foreach($products as $groupName => $groupProducts)
                                                                <optgroup label="{{ $groupName }}">
                                                                    @foreach($groupProducts as $product)
                                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                                    @endforeach
                                                                </optgroup>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row">
                                                        <label for="name-field" class="col-sm-2 col-form-label">Name</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <input type="text" name="name" id="name-field"
                                                                class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                                                required>
                                                            <div class="invalid-feedback text-muted">
                                                                This field will be filled automatically.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label for="email-address" class="col-sm-2 col-form-label">Email
                                                            Address</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input type="email" name="email" id="email-address"
                                                                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                                                required>
                                                            <div class="invalid-feedback text-muted">
                                                                This field will be filled automatically.
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-5 d-flex align-items-center">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" name="sendemail"
                                                                    class="custom-control-input" id="customCheck1">
                                                                <label class="custom-control-label" for="customCheck1">Send
                                                                    Email</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="cc-recipients" class="col-sm-2 col-form-label">CC
                                                            Recipients</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input type="text" name="cc_recipient" id="cc-recipients"
                                                                class="form-control">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <p class="mt-2">(Comma Separated)</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="subject"
                                                            class="col-sm-2 col-form-label">Subject</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <input type="text" name="subject"
                                                                class="form-control {{ $errors->has('subject') ? 'is-invalid' : '' }}"
                                                                id="subject" required>
                                                            <div class="invalid-feedback">
                                                                Please enter a subject for the ticket.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="form-group row">
                                                                <label for="department"
                                                                    class="col-lg-4 col-form-label">Department</label>
                                                                <div class="col-sm-12 col-lg-8">
                                                                    <select name="deptid" id="department"
                                                                        class="form-control select2-search-disable {{ $errors->has('deptid') ? 'is-invalid' : '' }}"
                                                                        style="width: 100%;" required>
                                                                        @foreach ($dep as $k)
                                                                            <option value="{{ $k->id }}">
                                                                                {{ $k->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="invalid-feedback">
                                                                        Please select a department.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="form-group row">
                                                                <label for="priority"
                                                                    class="col-lg-4 col-form-label">Priority</label>
                                                                <div class="col-sm-12 col-lg-8">
                                                                    <select name="priority" id="priority"
                                                                        class="form-control select2-search-disable"
                                                                        style="width: 100%;" required>
                                                                        <option value="Low">Low</option>
                                                                        <option value="Medium">Medium</option>
                                                                        <option value="High">High</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered dt-responsive w-100"
                                                            id="datatabletiket">
                                                            <thead>
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Product/Service</th>
                                                                    <th>Amount</th>
                                                                    <th>Billing Cycle</th>
                                                                    <th>Signup Date</th>
                                                                    <th>Next Due Date</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    @if ($errors->has('message'))
                                                       <div class="alert alert-danger" role="alert">
                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                            {{ $errors->first('message') }}
                                                        </div>
                                                    @endif
                                                    <textarea class="summernote" name="message" id="replymessage" rows="15"></textarea>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="d-flex justify-content-center">
                                                        <a href="" class="m-3">
                                                            Insert Knowledgebase Link
                                                        </a>
                                                        <a href="" class="m-3"
                                                            onclick="loadpredef('0'); return false">
                                                            Insert Predefined Reply
                                                        </a>
                                                    </div>
                                                    <div id="prerepliescontainer" class="bg-light mb-3 rounded"
                                                        style="display: none;">
                                                        <div class="p-1">
                                                            <input type="text" id="predefq" value=""
                                                                placeholder="Search" class="form-control form-control-sm">
                                                        </div>
                                                        <div id="prerepliescontent" class="row p-3">

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group row">
                                                        <label for="attachment"
                                                            class="col-sm-2 col-form-label">Attachments</label>
                                                        <div class="col-sm-12 col-lg-8">
                                                            <div class="input-group mb-3">
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input"
                                                                        name="attachments[]" id="inputGroupFile01"
                                                                        aria-describedby="inputGroupFileAddon01">
                                                                    <label class="custom-file-label"
                                                                        for="inputGroupFile01">Choose file</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <button id="add-more" class="btn btn-primary btn-block">
                                                                Add More
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div id="addform">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12 d-flex justify-content-center">
                                                    <input id="inputtype" type="hidden" value="" name="type">
                                                    <button type="submit" class="btn btn-success px-5">Open
                                                        Ticket</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Required datatable js -->
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/markdown.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/to-markdown.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/bootstrap-markdown.js') }}"></script>
    <script type="text/javascript">
        (function() {
            var fieldSelection = {
                addToReply: function() {
                    var url = arguments[0] || '',
                        title = arguments[1] || '';
                    (e = this.jquery ? this[0] : this), (text = '')

                    if (title != '') {
                        text = '[' + title + '](' + url + ')'
                    } else {
                        text = url
                    }

                    return (
                        ('selectionStart' in e &&
                            function() {
                                e.value = "";
                                if (e.value == '\n\n') {
                                    e.selectionStart = 0
                                    e.selectionEnd = 0
                                }
                                e.value =
                                    e.value.substr(0, e.selectionStart) +
                                    text +
                                    e.value.substr(e.selectionEnd, e.value.length)
                                e.focus()
                                console.log('step 1');
                                return this
                            }) ||
                        (document.selection &&
                            function() {
                                e.focus()
                                document.selection.createRange().text = text
                                return this
                            }) ||
                        function() {
                            e.value += text
                            return this
                        }
                    )()
                },
            }
            jQuery.each(fieldSelection, function(i) {
                jQuery.fn[i] = this
            })
        })()

        $('#predefq').keyup(function() {
            var intellisearchlength = $('#predefq').val().length;
            if (intellisearchlength > 2) {
                $.ajax({
                    type: 'POST',
                    url: route('admin.pages.js.predefine'),
                    data: {
                        action: 'loadpredefinedreplies',
                        predefq: $('#predefq').val(),
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(res) {
                        $("#prerepliescontent").html(res.data);
                    }
                })
            } else {
                $.ajax({
                    type: 'POST',
                    url: route('admin.pages.js.predefine'),
                    data: {
                        action: 'loadpredefinedreplies',
                        catid: 0,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(res) {
                        $("#prerepliescontent").html(res.data);
                    }
                })
            }
        })

        function loadpredef(catid) {
            $("#prerepliescontainer").slideToggle();
            $("#prerepliescontent").html(`
            <div class="col-12 text-center">
               <div class="spinner-border" role="status">
                  <span class="sr-only">Loading...</span>
               </div>
               <h6 class="mt-3">Please Wait</h6>
            </div>
            `);
            $.ajax({
                type: 'POST',
                url: route('admin.pages.js.predefine'),
                data: {
                    action: "loadpredefinedreplies",
                    cat: catid,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    $("#prerepliescontent").html(res.data);
                }
            })
        }

        function selectpredefcat(catid) {
            $.ajax({
                type: 'POST',
                url: route('admin.pages.js.predefine'),
                data: {
                    action: 'loadpredefinedreplies',
                    cat: catid,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    $("#prerepliescontent").html(res.data);
                }
            })
        }

        function selectpredefreply(artid) {
            $('#replymessage').empty();
            $.ajax({
                type: 'POST',
                url: route('admin.pages.js.predefine'),
                data: {
                    action: 'getpredefinedreply',
                    id: artid,
                    _token: '{{ csrf_token() }}',
                },
                success: function(res) {
                    $('#replymessage').addToReply(res.data)
                }
            });
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $(".summernote").markdown({
                autofocus: false,
                hideable: false,
                savable: false,
                width: 'inherit',
                height: '150px',
                resize: 'vertical',
                iconlibrary: 'fa',
                language: 'en',
                fullscreen: {},
                dropZoneOptions: null,
                hiddenButtons: [],
                disabledButtons: []
            });
            //datatabletiket
            $('#datatabletiket').dataTable({
                "bPaginate": false
            });
            $('#client-name').select2({
                minimumInputLength: 2,
                placeholder: 'Client',
                ajax: {
                    type: "post",
                    url: '{{ url('admin/support/getClientselect2') }}',
                    dataType: 'json',
                    /*   delay: 250, */
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.firstname + ' ' + item.lastname + ' | ' + item
                                        .email + ' | ' + item.companyname,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#client-name').on('change', function() {
                var clientID = this.value;
                $('#datatabletiket tbody').html();
                $('#name-field').val();
                $('#email-address').val();
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ url('admin/support/getservice') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        client: clientID
                    },
                    success: function(data) {
                        //console.log(data);
                        $('#name-field').val(data.client.firstname + ' ' + data.client
                            .lastname + ' #' + data.client.id).attr('disabled', 'disabled');
                        $('#email-address').val(data.client.email).attr('disabled', 'disabled');
                        /* $('#panel-Client-Replis h4').text(data.clientReplies); */
                        $('#datatabletiket tbody').html(data.html);

                    }
                });

            });

            $('#datatabletiket').on('change', "input[name='related_service']:checked", function() {
                var value = $(this).data('type');
                var name = '';
                if (value == 'product') {
                    name = 'serviceid';
                }
                if (value == 'domain') {
                    name = 'domainid';
                }
                $('#inputtype').val($(this).val()).attr('name', name);
            });


            $('#add-more').click(function() {
                var newId = 'inputGroupFile' + ($('.custom-file-input').length + 1); // Generate a new unique ID
                $('#addform').after(`<div class="col-lg-12">
                                        <div class="form-group row">
                                            <label for="${newId}"
                                                class="col-sm-2 col-form-label">Attachments</label>
                                            <div class="col-sm-12 col-lg-8">
                                                <div class="input-group mb-3">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="attachments[]" id="${newId}" aria-describedby="inputGroupFileAddon01">
                                                        <label class="custom-file-label" for="${newId}">Choose file</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-2">
                                            </div>
                                        </div>
                                    </div>`);
                return false;
            });

            // Update file input label with selected file name
            $(document).on('change', '.custom-file-input', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Initialize Toast
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Handle product select changes
            $('#product-service').on('change', function() {
                var productID = this.value;
                if (productID) {
                    $('#ticket_mode').val('product');
                    $('#client-name').prop('disabled', true);
                    $('#email-address').prop('readonly', true);
                    $('#name-field').prop('readonly', true);
                    $('#client-name').val('').trigger('change');
                    updateTableHeaders(['#', 'Name', 'Email']);
                    getClientsWithProduct(productID);
                } else {
                    $('#ticket_mode').val('client');
                    $('#client-name').prop('disabled', false);
                    $('#email-address').prop('readonly', false);
                    $('#name-field').prop('readonly', false);
                    updateTableHeaders(['', 'Product/Service', 'Amount', 'Billing Cycle', 'Signup Date', 'Next Due Date', 'Status']); // Reset table headers
                    clearClientInfo();
                }
            });

            // Handle client select changes
            $('#client-name').on('change', function() {
                var clientID = this.value;
                if (clientID) {
                    $('#ticket_mode').val('client');
                    $('#product-service').prop('disabled', true);
                    $('#product-service').val('').trigger('change');
                    updateClientInfo(clientID);
                } else {
                    $('#ticket_mode').val('');
                    $('#product-service').prop('disabled', false);
                    clearClientInfo();
                }
            });

            // Update getClientsWithProduct function
            function getClientsWithProduct(productID) {
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ route("admin.pages.support.opennewtickets.getClientsWithProduct") }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        product_id: productID
                    },
                    success: function(data) {
                        clearClientInfo();
                        $('#datatabletiket tbody').html(data.html);

                        // Update the hidden selected_clients field with all client IDs
                        if (data.client_ids) {
                            $('#selected-clients').val(data.client_ids.join(','));
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                        var replaceMessage = "No clients found for this product";
                        showToast(replaceMessage);
                        $('#datatabletiket tbody').html('<tr style="background-color: #f0f0f0;"><td colspan="3" class="text-center">' + replaceMessage + '</td></tr>');
                    }
                });
            }

            function showToast(message) {
                Toast.fire({
                    icon: 'error',
                    text: message
                });
            }

            // Function to update client information
            function updateClientInfo(clientID) {
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ url('admin/support/getservice') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        client: clientID
                    },
                    success: function(data) {
                        var fullName = '';
                        if (data.client.firstname) {
                            fullName += data.client.firstname;
                        }
                        if (data.client.lastname) {
                            fullName += (fullName ? ' ' : '') + data.client.lastname;
                        }
                        fullName += ' #' + data.client.id;

                        $('#name-field').val(fullName).attr('disabled', 'disabled');
                        $('#email-address').val(data.client.email).attr('disabled', 'disabled');
                        $('#datatabletiket tbody').html(data.html);
                    }
                });
            }

            // Function to clear client information
            function clearClientInfo() {
                $('#name-field').val('').removeAttr('disabled');
                $('#email-address').val('').removeAttr('disabled');
                $('#datatabletiket tbody').html('');
            }

            // Function to update table headers
            function updateTableHeaders(headers) {
                var headerHtml = headers.map(function(header) {
                    return '<th>' + header + '</th>';
                }).join('');
                $('#datatabletiket thead tr').html(headerHtml);
            }

            function dataservide() {
                var clientID = $('#client-name').val();
                $('#datatabletiket tbody').html();
                $('#name-field').val();
                $('#email-address').val();
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ url('admin/support/getservice') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        client: clientID
                    },
                    success: function(data) {
                        if (data.client) {
                            var fullName = '';
                            if (data.client.firstname) {
                                fullName += data.client.firstname;
                            }
                            if (data.client.lastname) {
                                fullName += (fullName ? ' ' : '') + data.client.lastname;
                            }
                            fullName += ' #' + data.client.id;

                            $('#name-field').val(fullName).attr('disabled', 'disabled');
                            $('#email-address').val(data.client.email).attr('disabled', 'disabled');
                            $('#datatabletiket tbody').html(data.html);
                        } else {
                            // showToast("Client data is not available.");
                            $('#datatabletiket tbody').html('<tr style="background-color: #f0f0f0;"><td colspan="7" class="text-center">No data available in table</td></tr>');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                        var replaceMessage = "No Services found for this client";
                        showToast(replaceMessage);
                        $('#datatabletiket tbody').html('<tr style="background-color: #f0f0f0;"><td colspan="7" class="text-center">' + replaceMessage + '</td></tr>');
                    }
                });
            };
            dataservide();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('ticketForm');
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    </script>
@endsection
