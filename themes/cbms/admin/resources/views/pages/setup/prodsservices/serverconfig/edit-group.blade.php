@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Servers</title>
@endsection

@section('content')
   <div class="main-content">
      <div class="page-content">
         <div class="container-fluid">
            <div class="row">
               <!-- MAIN CARD -->
               <div class="col-xl-12">
                  <div class="view-client-wrapper">
                     <div class="row">
                        <div class="col-12">
                           <div class="card-title mb-3">
                              <h4 class="mb-3">Servers</h4>
                           </div>
                           @if (session('name_req'))
                              <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Something Went Wrong!</h5>
                                 <small>{!! session('name_req') !!}</small>
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                 </button>
                              </div>
                           @endif
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <form
                              action="{{ route('admin.pages.setup.prodsservice.serverconfig.update-group', $selectedGroup->id) }}"
                              method="POST">
                              <div class="card p-3">
                                 <h5 class="mb-3">Edit Group</h5>
                                 @csrf
                                 <div class="form-group row">
                                    <label for="" class="col-sm-12 col-md-2 col-form-label">Name</label>
                                    <div class="col-sm-12 col-md-4">
                                       <input type="text" class="form-control" name="name"
                                          value="{{ $selectedGroup->name }}">
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="" class="col-sm-12 col-md-2 col-form-label">Fill Type</label>
                                    <div class="col-sm-12 col-md-8">
                                       <div class="custom-control custom-radio">
                                          <input type="radio" id="filltype1" name="filltype" class="custom-control-input"
                                             value="1" {{ $selectedGroup->filltype == 1 ? 'checked' : '' }}>
                                          <label class="custom-control-label" for="filltype1">Add to the least full
                                             server</label>
                                       </div>
                                       <div class="custom-control custom-radio">
                                          <input type="radio" id="filltype2" name="filltype" class="custom-control-input"
                                             value="2" {{ $selectedGroup->filltype == 2 ? 'checked' : '' }}>
                                          <label class="custom-control-label" for="filltype2">Fill active server until
                                             full then switch to next least used</label>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="" class="col-sm-12 col-md-2 col-form-label">Selected Server</label>
                                    <div class="col-sm-12 col-md-4">
                                       <select name="serverList[]" id="serverList" class="form-control"
                                          style="height: 210px" multiple>
                                          @foreach ($serverList as $id => $server)
                                             <option value="{{ $id }}">{{ $server }}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                    <div
                                       class="col-sm-12 col-lg-1 d-lg-flex justify-content-center align-items-center flex-wrap">
                                       <button type="button" class="btn btn-primary" id="addServer">Add<i
                                             class="fas fa-arrow-circle-right d-none d-md-block px-md-4"></i></button>
                                       <button type="button" class="btn btn-danger" id="removeServer"><i
                                             class="fas fa-arrow-circle-left d-none d-md-block px-md-4"></i>Remove</button>
                                    </div>
                                    <div class="col-sm-12 col-md-4">
                                       <select name="selectedServer[]" id="selectedServer" class="form-control"
                                          style="height: 210px" multiple>
                                          @foreach ($serverName as $key => $name)
                                             @foreach ($name as $id => $item)
                                                <option value="{{ $id }}" {{ $id ? 'selected' : '' }}>
                                                   {{ $item }}</option>
                                             @endforeach
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-sm-12 d-flex justify-content-center">
                                    <button type="submit" class="btn btn-success px-3 mx-1">Save Changes</button>
                                    <a href="{{ route('admin.pages.setup.prodsservice.serverconfig.index') }}">
                                       <button type="button" class="btn btn-light px-3">Cancel Changes</button>
                                    </a>
                                 </div>
                              </div>
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- End MAIN CARD -->
            </div>
         </div>
      </div>
   </div>
@endsection

@section('scripts')
   <script type="text/javascript">
      $(document).ready(function() {
         $("#addServer").click(function() {
            $("#serverList option:selected").remove().appendTo($("#selectedServer"));
         })
         $("#removeServer").click(function() {
            $("#selectedServer option:selected").remove().appendTo($("#serverList"));
         })
      })
   </script>
@endsection
