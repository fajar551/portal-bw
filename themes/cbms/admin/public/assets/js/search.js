$(function() {
    
    //searchkey
    $('#frmIntelligentSearch').submit(function() {
        
        $('#intelligentSearchResults').show();
        $('.searloading').show();
        $('.resuletdata').empty();
        var numResults=1;
        var searchterm =$('#searchkey').val();
        var hide_inactive =$('input[name="hide_inactive"]:checked').val()?$('input[name="hide_inactive"]:checked').val():0;
        if(searchterm.length < 2){
            $('.searloading').hide();
            $('.resuletdata').html(`<div class="alert alert-danger" role="alert">Search term must be at least 2 characters long</div>`);
            return false;
        }

        $.ajax({
            type: 'POST',
            headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
            url:  route('admin.intelligentsearch'),
            data: {searchterm:searchterm,hide_inactive:hide_inactive},
            dataType: 'json',
            success: function(data){
                var html='';
                var jumlah=0;
                if(data.client.count > 0){
                   
                    var listdata='';
                    $.each(data.client.data, function( key, value ) {
                        var label=value.status;
                        listdata+=`<a href="`+route('admin.pages.clients.viewclients.clientsummary.index',{_query:{userid:value.id}})+`" class="list-group-item list-group-item-action" ><span class="icon"><i class="far fa-user"></i></span><strong>`+value.firstname+` `+value.lastname+`</strong> #`+value.id+`<span class="label  `+label.toLowerCase()+`">`+value.status+`</span><em>`+value.email+`</em></a>`;
                    });
                   // listdata +='</ul>';
                   var  pagination='';
                   if(data.client.count > numResults ){
                    pagination+=`
                            <div class="text-center">
                                <button  class="btn btn-info btn-xs loadmore" data-pagination="client" >
                                    <span class="loading-pagination" style="display:none;"> <i class="fas fa-spinner fa-spin fa-1x"></i></span> 
                                    <span class="btnpagination">Load more</span></button>
                            </div>
                            `;
                    }
                    html+=`
                            <div class="item">
                                <div class="item-header" id="accordionclients">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Clients (<span class="count">`+data.client.count+`</span>)
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseOne" class="collapse show" aria-labelledby="accordionclients" data-parent="#accordionExample">
                                    <div id="loadpagination-client" class="t-p list-group">
                                        `+listdata+`
                                    </div>
                                    <div id="pagination-client" class="pt-2 pb-2">
                                        `+pagination+`
                                    </div>
                                </div>
                            </div>`;
                        jumlah = jumlah + data.client.count;
                }
               
                if(data.contacts.count > 0){
                    var listdata='';
                    $.each( data.contacts.data, function( key, value ) {
                        listdata+=`<a href="`+route('admin.pages.clients.viewclients.clientsummary.index',{_query:{userid:value.clientId,contactid:value.id}})+`" class="list-group-item list-group-item-action" ><span class="icon"><i class="far fa-user"></i></span><strong>`+value.firstname+` `+value.lastname+`</strong> #`+value.id+`<em>`+value.email+`</em></a></li>`;
                    });
                    var  pagination='';
                   if(data.contacts.count > numResults ){
                    pagination+=`
                            <div class="text-center">
                                <button  class="btn btn-info btn-xs loadmore" data-pagination="contacts" >
                                    <span class="loading-pagination" style="display:none;"> <i class="fas fa-spinner fa-spin fa-1x"></i></span> 
                                    <span class="btnpagination">Load more</span></button>
                            </div>
                            `;
                    }
                    html+=`
                            <div class="item">
                                <div class="item-header" id="accordioncontacts">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            Contacts (<span class="count">`+data.contacts.count+`</span>)
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="accordioncontacts" data-parent="#accordionExample">
                                    <div id="loadpagination-contacts" class="t-p list-group">
                                        `+listdata+`
                                    </div>
                                    <div id="pagination-contacts" class="pt-2 pb-2">
                                        `+pagination+`
                                    </div>
                                </div>
                            </div>    
                    `;

                    jumlah = jumlah + data.contacts.count;
                }
                //console.log(jumlah,'jml');
                /* service */
                if(data.service.count > 0){
                    var listdata='';
                    $.each( data.service.data, function( key, value ) {
                        //console.log(value,'value');
                        var label=value.domainstatus;
                        listdata+=`
                                <a class="list-group-item list-group-item-action"  href="`+route('admin.pages.clients.viewclients.clientservices.index',{_query:{userid:value.userid,productselect:value.id}})+`">
                                    <span class="icon"><i class="fas fa-cube"></i></span>
                                    <strong>`+value.product_name+` - `+value.domain+`</strong>
                                    <span class="label `+label.toLowerCase()+`">`+value.domainstatus+`</span>
                                    <em>`+value.firstname+` `+value.lastname+` #`+value.userid+`</em>
                                </a>
                        `;
                    });
                    var pagination='';
                    if(data.service.count > numResults ){
                        pagination+=`
                                <div class="text-center">
                                    <button  class="btn btn-info btn-xs loadmore" data-pagination="service" >
                                        <span class="loading-pagination" style="display:none;"> <i class="fas fa-spinner fa-spin fa-1x"></i></span> 
                                        <span class="btnpagination">Load more</span></button>
                                </div>
                                `;
                    }

                    html+=`
                            <div class="item">
                                <div class="item-header" id="accordionproductservide">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            Products/Services (<span class="count">`+data.service.count+`</span>)
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseThree" class="collapse" aria-labelledby="accordionproductservide" data-parent="#accordionExample">
                                    <div id="loadpagination-service" class="t-p list-group">
                                    `+listdata+`
                                    </div>
                                    <div id="pagination-service" class="pt-2 pb-2">
                                        `+pagination+`
                                    </div>
                                </div>
                            </div>
                    
                        `;

                        jumlah = jumlah + data.service.count;
                }
                /*Domain*/
                if(data.domain.count > 0){
                    var listdata='';
                    $.each(data.domain.data, function( key, value ) {
                        //console.log(value,'value');
                        var label=value.status;
                        listdata+=`
                                <a class="list-group-item list-group-item-action"  href="`+route('admin.pages.clients.viewclients.clientdomain.index',{_query:{userid:value.userid,domainid:value.id}})+`">
                                    <span class="icon"><i class="fas fa-globe-americas"></i></span>
                                    <strong>`+value.domain+`</strong>
                                    <span class="label `+label.toLowerCase()+`">`+value.status+`</span>
                                    <em>`+value.firstname+` `+value.lastname+` #`+value.userid+`</em>
                                </a>
                        `;
                    });

                    var pagination='';
                    if(data.domain.count > numResults ){
                        pagination+=`
                                <div class="text-center">
                                    <button  class="btn btn-info btn-xs loadmore" data-pagination="domain" >
                                        <span class="loading-pagination" style="display:none;"> <i class="fas fa-spinner fa-spin fa-1x"></i></span> 
                                        <span class="btnpagination">Load more</span></button>
                                </div>
                                `;
                    }

                    html+=`
                            <div class="item">
                                <div class="item-header" id="accordionproductdomain">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            Domains (<span class="count">`+data.domain.count+`</span>)
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseFour" class="collapse" aria-labelledby="accordionproductdomain" data-parent="#accordionExample">
                                    <div id="loadpagination-domain" class="t-p list-group">
                                        `+listdata+`
                                    </div>
                                    <div id="pagination-domain" class="pt-2 pb-2">
                                    `+pagination+`
                                    </div>
                                </div>
                            </div>
                    `;

                    jumlah = jumlah + data.domain.count;
                }
                /* ticket */
                if(data.ticket.count > 0){
                    var listdata='';
                    $.each( data.ticket.data, function( key, value ) {
                        var label=value.domainstatus;
                        listdata+=`
                                <a class="list-group-item list-group-item-action"  href="`+route('admin.pages.support.supporttickets.view',{id:value.id})+`">
                                    <span class="icon"><i class="fas fa-comments"></i></span>
                                    <strong>Ticket #`+value.tid+`</strong>
                                    <em>`+value.title+`</em>
                                </a>
                        `;
                    });
                    var pagination='';
                    if(data.ticket.count > numResults ){
                        pagination+=`
                                <div class="text-center">
                                    <button  class="btn btn-info btn-xs loadmore" data-pagination="ticket" >
                                        <span class="loading-pagination" style="display:none;"> <i class="fas fa-spinner fa-spin fa-1x"></i></span> 
                                        <span class="btnpagination">Load more</span></button>
                                </div>
                                `;
                    }

                    html+=`
                            <div class="item">
                                <div class="item-header" id="accordionsupportticket">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFour">
                                        Support Tickets (<span class="count">`+data.ticket.count+`</span>)
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseFive" class="collapse" aria-labelledby="accordionsupportticket" data-parent="#accordionExample">
                                    <div id="loadpagination-ticket" class="t-p list-group">
                                       `+listdata+`
                                    </div>
                                    <div id="pagination-ticket" class="pt-2 pb-2">
                                        `+pagination+`
                                    </div>
                                </div>
                            </div>
                        `;
                    jumlah = jumlah + data.ticket.count;
                }

                if(data.invoices.count > 0){
                    var listdata='';
                    $.each( data.invoices.data, function( key, value ) {
                        var label=value.domainstatus;
                        listdata+=`
                                <a class="list-group-item list-group-item-action"  href="`+route('admin.pages.billing.invoices.edit',{id:value.id})+`">
                                    <span class="icon"><i class="fas fa-file-invoice"></i></span>
                                    <strong>Invoice #`+value.id+`</strong>
                                    <em>`+value.firstname+` (`+value.companyname+`) #`+value.userid+`</em>
                                </a>
                        `;
                    });


                    var pagination='';
                    if(data.invoices.count > numResults ){
                        pagination+=`
                                <div class="text-center">
                                    <button  class="btn btn-info btn-xs loadmore" data-pagination="invoices" >
                                        <span class="loading-invoices" style="display:none;"> <i class="fas fa-spinner fa-spin fa-1x"></i></span> 
                                        <span class="btninvoices">Load more</span></button>
                                </div>
                                `;
                    }


                    html+=`
                            <div class="item">
                                <div class="item-header" id="accordioninvoice">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFour">
                                        INVOICES (<span class="count">`+data.invoices.count+`</span>)
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseFive" class="collapse" aria-labelledby="accordioninvoice" data-parent="#accordionExample">
                                    <div id="loadpagination-invoices" class="t-p list-group">
                                    `+listdata+`
                                    </div>
                                    <div id="pagination-invoices" class="pt-2 pb-2">
                                        `+pagination+`
                                    </div>
                                </div>
                            </div>
                        `;

                        jumlah = jumlah + data.invoices.count;
                }


                //console.log(data.invoices,'invoice');


                $('.searloading').hide();
                $('.resuletdata').html(html);
                $('.search-result-count').text(jumlah);
            }
        });



        return false;
    });

    $('#intelligentSearchResults .close').click(function(){
        $('#intelligentSearchResults').hide();
        return false;
    });


    //intelligentSearchResults
    $( "#intelligentSearchResults" ).on( "click",".loadmore", function() {
        var action= $(this).data('pagination');
        var searchterm =$('#searchkey').val();
        var hide_inactive =$('input[name="hide_inactive"]:checked').val()?$('input[name="hide_inactive"]:checked').val():0;
        
        $('#pagination-'+action+' .loading-pagination').show();
        $('#pagination-'+action+' .loadmore').attr('disable',true);

        $.ajax({
            type: 'POST',
            headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
            url:  route('admin.intelligentsearch'),
            data: {searchterm:searchterm,hide_inactive:hide_inactive,action:action},
            dataType: 'json',
            success: function(data){
                var listdata='';
                switch(data.page) {
                    case 'client':
                    $.each(data.data, function( key, value ) {
                        var label=value.status;
                        listdata+=`<a href="`+route('admin.pages.clients.viewclients.clientsummary.index',{_query:{userid:value.id}})+`" class="list-group-item list-group-item-action" ><span class="icon"><i class="far fa-user"></i></span><strong>`+value.firstname+` `+value.lastname+`</strong> #`+value.id+`<span class="label  `+label.toLowerCase()+`">`+value.status+`</span><em>`+value.email+`</em></a>`;
                    });
                    break;
                    case 'contacts':
                        $.each(data.data, function( key, value ) {
                            listdata+=`<a href="`+route('admin.pages.clients.viewclients.clientsummary.index',{_query:{userid:value.clientId,contactid:value.id}})+`" class="list-group-item list-group-item-action" ><span class="icon"><i class="far fa-user"></i></span><strong>`+value.firstname+` `+value.lastname+`</strong> #`+value.id+`<em>`+value.email+`</em></a></li>`;
                        });
                    break;
                    case 'service':
                        $.each( data.data, function( key, value ) {
                            //console.log(value,'value');
                            var label=value.domainstatus;
                            listdata+=`
                                    <a class="list-group-item list-group-item-action"  href="`+route('admin.pages.clients.viewclients.clientservices.index',{_query:{userid:value.userid,productselect:value.id}})+`">
                                        <span class="icon"><i class="fas fa-cube"></i></span>
                                        <strong>`+value.product_name+` - `+value.domain+`</strong>
                                        <span class="label `+label.toLowerCase()+`">`+value.domainstatus+`</span>
                                        <em>`+value.firstname+` `+value.lastname+` #`+value.userid+`</em>
                                    </a>
                            `;
                        });

                    break;
                    case 'domain':
                        $.each(data.data, function( key, value ) {
                            //console.log(value,'value');
                            var label=value.status;
                            listdata+=`
                                    <a class="list-group-item list-group-item-action"  href="`+route('admin.pages.clients.viewclients.clientdomain.index',{_query:{userid:value.userid,domainid:value.id}})+`">
                                        <span class="icon"><i class="fas fa-globe-americas"></i></span>
                                        <strong>`+value.domain+`</strong>
                                        <span class="label `+label.toLowerCase()+`">`+value.status+`</span>
                                        <em>`+value.firstname+` `+value.lastname+` #`+value.userid+`</em>
                                    </a>
                            `;
                        });
                    break;
                    
                    case 'ticket':
                        $.each( data.data, function( key, value ) {
                            var label=value.domainstatus;
                            listdata+=`
                                    <a class="list-group-item list-group-item-action"  href="`+route('admin.pages.support.supporttickets.view',{id:value.id})+`">
                                        <span class="icon"><i class="fas fa-comments"></i></span>
                                        <strong>Ticket #`+value.tid+`</strong>
                                        <em>`+value.title+`</em>
                                    </a>
                            `;
                        });
                    break;
                    case 'invoices':
                        $.each( data.data, function( key, value ) {
                            var label=value.domainstatus;
                            listdata+=`
                                <a class="list-group-item list-group-item-action"  href="`+route('admin.pages.billing.invoices.edit',{id:value.id})+`">
                                    <span class="icon"><i class="fas fa-file-invoice"></i></span>
                                    <strong>Invoice #`+value.id+`</strong>
                                    <em>`+value.firstname+` (`+value.companyname+`) #`+value.userid+`</em>
                                </a>
                            `;
                        });

                    break;

                }
                $('#intelligentSearchResults #loadpagination-'+data.page).append(listdata);
                $('#intelligentSearchResults #pagination-'+data.page).hide();

            }
        });


    });
    
});
