<div class="modal fade" id="perpanjangan-otomatis" tabindex="-1" role="dialog"
   aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
   <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalCenterTitle">{{ __('client.domainsautorenew') }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <form action="{{ route('pages.services.update.autorenew') }}" method="POST">
            @csrf
            <div class="domain-id">
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-lg-12">
                     <div class="alert alert-info" role="alert">
                        {{ __('client.domainrenewexp') }}:
                     </div>
                  </div>
                  <div class="col-lg-12">
                     <div class="form-group row align-items-center">
                        <div class="col-7">
                           {{ __('client.domainautorenewstatus') }}
                        </div>
                        <div class="col-2 text-center" id="domain-res-stats">
                        </div>
                        <div class="col-3 text-right">
                           <div class="custom-control custom-switch">
                              <input type="checkbox" name="domainStatusChange" class="custom-control-input"
                                 id="autorenew-check" value="1">
                              <label class="custom-control-label" for="autorenew-check"></label>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" id="clear-stats" data-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-success">Save Change</button>
            </div>
         </form>
      </div>
   </div>
</div>
