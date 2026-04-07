<div class="modal fade" id="atur-name-server" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
   aria-hidden="true" data-keyboard="false" data-backdrop="static">
   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalCenterTitle">Atur name server</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <form action="{{ route('pages.services.update.nameservers') }}" method="POST">
            <div class="modal-body">
               <div class="alert alert-primary" role="alert">
                  Anda dapat mengubah kemanakah domain akan diarahkan di halaman ini. Mohon diperhatikan bahwa perubahan
                  DNS pada domain membutuhkan waktu sedikitnya 24 Jam untuk Propagasi sempurna
               </div>
               @csrf
               <div class="domain-id">
                   
               </div>
               <div class="mt-3">
                  <div class="custom-control custom-radio">
                     <input type="radio" id="customRadio11" name="nschoice" class="custom-control-input" value="default" checked>
                     <label class="custom-control-label" for="customRadio11">Gunakan default nameservers</label>
                  </div>
                  <div class="custom-control custom-radio">
                     <input type="radio" id="customRadio12" name="nschoice" class="custom-control-input" value="custom"> 
                     <label class="custom-control-label" for="customRadio12">Gunakan custom nameservers (masukan di
                        bawah)</label>
                     <div class="form-group row mt-2">
                        <label for="ns1" class="col-sm-2 col-form-label">Nameserver 1</label>
                        <div class="col-sm-10">
                           <input type="text" name="ns1" class="form-control" id="ns1" value="dnsiix1.goldenfast.net">
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="ns2" class="col-sm-2 col-form-label">Nameserver 2</label>
                        <div class="col-sm-10">
                           <input type="text" name="ns2" class="form-control" id="ns2" value="dnsiix2.goldenfast.net">
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="ns3" class="col-sm-2 col-form-label">Nameserver 3</label>
                        <div class="col-sm-10">
                           <input type="text" name="ns3" class="form-control" id="ns3" value="">
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="ns4" class="col-sm-2 col-form-label">Nameserver 4</label>
                        <div class="col-sm-10">
                           <input type="text" name="ns4" class="form-control" id="ns4" value="">
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="ns5" class="col-sm-2 col-form-label">Nameserver 5</label>
                        <div class="col-sm-10">
                           <input type="text" name="ns5" class="form-control" id="ns5" value="">
                        </div>
                     </div>
                  </div>
               </div>

            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-success">Update Nameservers</button>
            </div>
         </form>

      </div>
   </div>
</div>
