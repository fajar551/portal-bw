@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Edit Knowledgebase</title>
@endsection
@section('content')
<div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <!-- <div class="row">
                                <div class="col-12 p-3">
                                    <div class="page-title-box d-flex align-items-center justify-content-between">
                                        <h4 class="mb-0">Dashboard</h4>
                                    </div>
                                </div>
                            </div> -->
                <!-- end page title -->
                <div class="row">
                     
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Edit Knowledgebase</h4>
                                    </div>
                                </div>
                            </div>
                            @if(Session::has('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                                @php
                                    Session::forget('success');
                                @endphp
                            </div>
                            @endif
                            <div class="row">
                                <div class="col-lg-12">
                                  @if($artikel)
                                     <form action="{{ url($url.'knowledgebase/article-update/'.$artikel->id) }}" method="post" enctype="multipart/form-data">
                                       <div class="card p-3">
                                          <div class="card-body">
                                          @method('PUT')
                                          {{ csrf_field() }}
                                          <input type="hidden" name="id" value="{{$artikel->id}}">
                                          <div class="form-group row">
                                             <label for="description-input" class="col-sm-2 col-form-label">Article Name</label>
                                             <div class="col-sm-8">
                                                   @if ($errors->has('articlename'))
                                                      <span class="text-danger">{{ $errors->first('articlename') }}</span>
                                                   @endif
                                                   <input type="text" name="articlename" id="titleinput" value="{{$artikel->title}}" class="form-control">
                                             </div>
                                          </div>
                                          <div class="from-group row mb-3">
                                             <label for="cat-input" class="col-sm-2 col-form-label">Categories</label>
                                             <div class="col-sm-8">
                                                   <select name="categories[]" class="form-control" multiple>
                                                      @foreach($category as $r)
                                                         <option value="{{$r->id}}" {{ in_array($r->id,$categoriInLink)?'selected':'' }} >{{$r->name}}</option>>
                                                      @endforeach
                                                   </select>
                                             </div>
                                          </div>
                                          <div class="from-group row mb-3">

                                             <label for="views-input" class="col-sm-2 col-form-label">Views</label>
                                             <div class="col-sm-1">
                                                   <input type="text" name="views" id="views" value="{{$artikel->views}}" class="form-control">
                                             </div>

                                             <label for="useful-input" class="col-sm-1 col-form-label text-right">Votes For</label>
                                             <div class="col-sm-1">
                                                   <input type="text" name="useful" id="useful" value="{{$artikel->useful}}" class="form-control">
                                             </div>

                                             <label for="votes-input" class="col-sm-1 col-form-label text-right">Total</label>
                                             <div class="col-sm-1">
                                                   <input type="text" name="votes" id="votes" value="{{$artikel->votes}}"  class="form-control">
                                             </div>
                                          </div>

                                          <div class="form-group row">
                                             <label for="order-input" class="col-sm-2 col-form-label">Display Order</label>
                                             <div class="col-sm-8">
                                                   <input type="text" name="order" id="orderinput"  value="{{$artikel->order}}" class="form-control">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label for="order-input" class="col-sm-2 col-form-label">Private</label>
                                             <div class="col-sm-8">
                                                   <div class="form-check mt-2">
                                                      <input class="form-check-input" name="private" type="checkbox" id="gridCheck1">
                                                      <label class="form-check-label" for="gridCheck1">
                                                      Tick this box to make the article private so only logged in users can view
                                                      </label>
                                                   </div>    
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label for="order-input" class="col-sm-2 col-form-label">Tags</label>
                                             <div class="col-sm-8">
                                                   <select name="tag[]" class="form-control selecttag" multiple>
                                                      @foreach($tagSeleted as $r)
                                                         <option value="{{ $r->tag }}"  selected>{{ $r->tag }}</option>
                                                      @endforeach
                                                   </select>
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label for="order-input" class="col-sm-2 col-form-label">Description</label>
                                             <div class="col-sm-8">
                                                   @if ($errors->has('description'))
                                                      <span class="text-danger">{{ $errors->first('description') }}</span>
                                                   @endif
                                                   <textarea name="description" class="summernote">{{ $artikel->article}}</textarea>
                                             </div>
                                          </div> 
                                          <!--
                                          <div class="form-group">
                                             <button type="submit" class="btn btn-success px-3 m-3">Save changes</button>
                                          </div>
                                          -->
                                          </div>                                          
                                       </div>
                                       <div class="card">
                                          <div class="card-body">
                                             <div class="card-title mb-5">
                                                <h3>Multi-Lingual Translations</h3>
                                             </div>
                                             <div class="inputmulti">
                                                <div class="accordion" id="lang">
                                                  @foreach($multi as $k => $v)
                                                  <div class="card">
                                                      <a href="#" class="collapsed" data-toggle="collapse" 
                                                         data-target="#lang{{ $loop->iteration }}"
                                                         aria-expanded="false" 
                                                         aria-controls="lang{{ $loop->iteration }}">
                                                          <div class="card-header text-left" id="langhead{{ $loop->iteration }}">
                                                              {{ $k }}
                                                          </div>
                                                      </a>
                                                      <div id="lang{{ $loop->iteration }}" class="collapse" 
                                                           aria-labelledby="langhead{{ $loop->iteration }}" 
                                                           data-parent="#lang{{ $loop->iteration }}">
                                                          <div class="card-body">
                                                              <div class="form-group row">
                                                                  <label class="col-sm-2 col-form-label">Article Name</label>
                                                                  <div class="col-sm-8">
                                                                      <input type="text" 
                                                                             name="lang[{{ strtolower($k) }}][articlename]" 
                                                                             value="{{ $v['title'] }}" 
                                                                             class="form-control">
                                                                  </div>
                                                              </div>
                                                              <div class="form-group row">
                                                                  <label class="col-sm-2 col-form-label">Description</label>
                                                                  <div class="col-sm-8">
                                                                      <textarea name="lang[{{ strtolower($k) }}][description]" 
                                                                                class="summernote">{{ $v['article'] }}</textarea>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                              @endforeach

                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="card">
                                          <div class="card-body">
                                             <div class="col-lg-12 d-flex justify-content-center">
                                                <button type="submit" class="btn btn-success px-3 mr-3">Save changes</button>
                                                <a href="{{ url($url.'knowledgebase') }}"  class="btn btn-danger px-3">Cancel</a>
                                             </div>
                                          </div>
                                       </div>
                                    </form>
                                    @else
                                      <div class="alert alert-danger">
                                        Article not found
                                      </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>






@endsection
@section('scripts')
   <script src="{{ Theme::asset('assets/libs/summernote/summernote.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/js/showEditor.js') }}"></script>
   <script type="text/javascript">
    $(document).ready(function(){
      $('.selecttag').select2({
            tags: true,
            placeholder: "Add a Tag",
       });
   });
</script> 
@endsection