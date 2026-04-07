@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Knowledgebase</title>
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
                                        <h4 class="mb-3">Knowledgebase</h4>
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
                                    <div class="card p-3">
                                        <nav>
                                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                @if(!$errors->has('articlename') || empty($articel) )
                                                <a class="nav-item nav-link active" id="nav-add-category-tab"
                                                    data-toggle="tab" href="#nav-add-category" role="tab"
                                                    aria-controls="nav-add-category" aria-selected="true">Add
                                                    Category</a>
                                                <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab"
                                                    href="#nav-add-download" role="tab" aria-controls="nav-add-download"
                                                    aria-selected="false">Article</a>
                                                @else
                                                <a class="nav-item nav-link " id="nav-add-category-tab"
                                                    data-toggle="tab" href="#nav-add-category" role="tab"
                                                    aria-controls="nav-add-category" aria-selected="false">Add
                                                    Category</a>
                                                <a class="nav-item nav-link active" id="nav-profile-tab" data-toggle="tab"
                                                    href="#nav-add-download" role="tab" aria-controls="nav-add-download"
                                                    aria-selected="true">Add Article</a>


                                                @endif
                                            </div>
                                        </nav>
                                        <div class="tab-content" id="nav-tabContent">
                                            <div class="tab-pane fade @if(!$errors->has('articlename')) show active @endif " id="nav-add-category" role="tabpanel"
                                                aria-labelledby="nav-add-category-tab">
                                                <div class="card p-3">
                                                    <form action="{{ url($url.'knowledgebase/category-store') }}" method="post" enctype="multipart/form-data">
                                                    {{ csrf_field() }}
                                                    <div class="form-group row">
                                                        <label for="catName" class="col-sm-2 col-form-label">Category Name</label>
                                                        <div class="col-sm-3">
                                                            @if ($errors->has('name'))
                                                                <span class="text-danger">{{ $errors->first('name') }}</span>
                                                            @endif
                                                            <input type="text" name="name" id="catName"
                                                                class="form-control" />
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <div class="form-check mt-2">
                                                                <input class="form-check-input" name="hidden" type="checkbox"
                                                                    id="gridCheck1">
                                                                <label class="form-check-label" for="gridCheck1">
                                                                    Tick to Hide
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="description-input"
                                                            class="col-sm-2 col-form-label">Description</label>
                                                            <div class="col-sm-8">
                                                                    @if ($errors->has('description'))
                                                                        <span class="text-danger">{{ $errors->first('description') }}</span>
                                                                    @endif
                                                                <input type="text" name="description" id="description-input"
                                                                    class="form-control">
                                                            </div>
                                                    </div>
                                                    <div class="col-lg-12 d-flex justify-content-center">
                                                        <button type="submit" class="btn btn-success px-3">Add Category</button>
                                                    </div>
                                                    </form>

                                                </div>
                                            </div>

                                            <div class="tab-pane fade font-size-16 @if($errors->has('articlename')) show active @endif" id="nav-add-download" role="tabpanel"
                                                aria-labelledby="nav-add-download-tab">
                                                <div class="card pt-3 px-3">
                                                    <form action="{{ url($url.'knowledgebase/article-store') }}" method="POST" enctype="multipart/form-data">
                                                        @csrf   
                                                        <div class="form-group row">
                                                            <label for="description-input" class="col-sm-2 col-form-label">Article Name</label>
                                                            <div class="col-sm-8">
                                                                @if ($errors->has('articlename'))
                                                                    <span class="text-danger">{{ $errors->first('articlename') }}</span>
                                                                @endif
                                                                <input type="text" name="articlename" id="titleinput" value="" class="form-control">
                                                            </div>
                                                        </div>
                                                        <!--
                                                        <div class="from-group row mb-3">
                                                            <label for="cat-input" class="col-sm-2 col-form-label">Categories</label>
                                                            <div class="col-sm-8">
                                                                <select name="categories[]" class="form-control" multiple>
                                                                    @foreach($category as $r)
                                                                        <option value="$r->id">$r->name</option>>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="from-group row mb-3">

                                                            <label for="views-input" class="col-sm-2 col-form-label">Views</label>
                                                            <div class="col-sm-1">
                                                                <input type="text" name="views" id="views" value="3" class="form-control">
                                                            </div>

                                                            <label for="useful-input" class="col-sm-1 col-form-label text-right">Votes For</label>
                                                            <div class="col-sm-1">
                                                                <input type="text" name="useful" id="useful" value="0" class="form-control">
                                                            </div>

                                                            <label for="votes-input" class="col-sm-1 col-form-label text-right">Total</label>
                                                            <div class="col-sm-1">
                                                                <input type="text" name="votes" id="votes" value="0" class="form-control">
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <label for="order-input" class="col-sm-2 col-form-label">Display Order</label>
                                                            <div class="col-sm-8">
                                                                <input type="text" name="order" id="orderinput" value="0" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="order-input" class="col-sm-2 col-form-label">Private</label>
                                                            <div class="col-sm-8">
                                                                <div class="form-check mt-2">
                                                                    <input class="form-check-input" name="hidden" type="checkbox" id="gridCheck1">
                                                                    <label class="form-check-label" for="gridCheck1">
                                                                    Tick this box to make the article private so only logged in users can view
                                                                    </label>
                                                                </div>    
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="order-input" class="col-sm-2 col-form-label">Tags</label>
                                                            <div class="col-sm-8">
                                                                <select id="selecttag" name="tag[]" class="form-control" multiple></select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="order-input" class="col-sm-2 col-form-label">Description</label>
                                                            <div class="col-sm-8">
                                                                @if ($errors->has('description'))
                                                                    <span class="text-danger">{{ $errors->first('description') }}</span>
                                                                @endif
                                                                <textarea name="description" class="summernote">Write Description</textarea>
                                                            </div>
                                                        </div>   -->
                                                        <div class="form-group">
                                                            <button type="submit" class="btn btn-success px-3 m-3">Add Article</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>


                                        </div>

                                        @if(empty($catAr))

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <nav aria-label="breadcrumb">
                                                    <ol class="breadcrumb">
                                                        <li class="breadcrumb-item active" aria-current="page">Home</li>
                                                    </ol>
                                                </nav>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="bg-light px-3 py-2 my-3 rounded">
                                                    <h3>
                                                        Browse By category
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                 @if(empty($category))
                                                <div class="alert alert-danger" role="alert">
                                                    You cannot add an article to the top level category 
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row">
                                            

                                            @foreach($category as $r)
                                            <div class="col-lg-4 p-3 font-size-16">
                                                <div class="d-flex align-items-center">
                                                    <i class=" ri-folder-fill mr-2"></i>
                                                    <a href="{{ url($url.'knowledgebase/'.$r->id) }}"
                                                        class="link-category">
                                                        {{$r->name}}
                                                    </a>
                                                    <div class="action-btn ml-2 mt-1 pt-3">
                                                        <a href="{{ url($url.'knowledgebase/category-edit/'.$r->id) }}" class="p-0 mr-2 editcat" data-id="{{$r->id}}" data-parentid="{{$r->parentid}}" data-name="{{$r->name}}" data-description="{{$r->description}}" data-hidden="{{$r->hidden}}" data-catid="{{ $r->catid }}" data-language="{{$r->language}}"  title="Edit Category" class="edit-icon">
                                                            <i class="ri-edit-box-line"></i>
                                                        </a>
                                                        <a href="{{ url($url.'knowledgebase/category-destroy/'.$r->id) }}" data-id="{{ $r->id }}" data-name="{{ $r->name }}" title="Delete Category" class="delete-icon delcat">
                                                            <i class="ri-indeterminate-circle-fill"></i>
                                                            <form id="catDELETE{{$r->id}}" action="{{ url($url.'knowledgebase/category-destroy/'.$r->id) }}" method="post">
                                                                     {{ method_field('DELETE') }}
                                                                     {{ csrf_field() }}
                                                                     <input type="hidden" name="id" value="{{$r->id}}">
                                                                     <input type="submit" style="display:none;">
                                                            </form>
                                                        </a>
                                                    </div>
                                                </div>
                                                <p>{{ $r->description }}</p>
                                            </div>
                                            @endforeach
                                        </div>

                                        @else
                                            <!-- artikel --->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <nav aria-label="breadcrumb">
                                                        <ol class="breadcrumb">
                                                            <li class="breadcrumb-item" aria-current="page"><a href="{{ url($url.'knowledgebase') }}">Knowledgebase Home</a></li>
                                                            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
                                                        </ol>
                                                    </nav>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="bg-light px-3 py-2 my-3 rounded">
                                                        <h3>
                                                             Articles
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                            @foreach($catAr as $r)
                                                <div class="col-lg-4 p-3 font-size-16">
                                                    <div class="d-flex align-items-center p-1">
                                                        <i class=" ri-folder-fill mr-2"></i>
                                                        <a href="{{ url($url.'knowledgebase/article/'.$r->id) }}"
                                                            class="link-category">
                                                            {{$r->title}}
                                                        </a>
                                                        <div class="action-btn ml-2 mt-1 pt-3">
                                                            <a href="{{ url($url.'knowledgebase/article/'.$r->id) }}" class="p-0 mr-2 editcat" data-id="{{$r->id}}"  data-name="{{$r->title}}" title="Edit Artikel" class="edit-icon">
                                                                <i class="ri-edit-box-line"></i>
                                                            </a>
                                                            <a href="{{ url($url.'knowledgebase/article-destroy/'.$r->id) }}" data-id="{{ $r->id }}" data-name="{{ $r->title }}" title="Delete Category" class="delete-icon delete-artikel">
                                                                <i class="ri-indeterminate-circle-fill"></i>
                                                                <form id="artikelDELETE{{$r->id}}" action="{{ url($url.'knowledgebase/article-destroy/'.$r->id) }}" method="post">
                                                                        {{ method_field('DELETE') }}
                                                                        {{ csrf_field() }}
                                                                        <input type="hidden" name="id" value="{{$r->id}}">
                                                                        <input type="submit" style="display:none;">
                                                                </form>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <p>Views : {{ $r->views  }} </p>
                                                </div>
                                                @endforeach
                                            </div>



                                            <!-- artikel -->
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<!-- edit -->
<div class="modal fade" id="formedit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editfrom" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ csrf_field() }}
                    <div class="form-group row">
                        <label for="catName" class="col-sm-2 col-form-label">Category Name</label>
                        <div class="col-sm-6">
                            <input type="text" name="name" id="catName" class="form-control" />
                            @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                        <div class="col-sm-3">
                            <div class="form-check mt-2">
                                <input class="form-check-input" name="hidden" type="checkbox" id="gridCheck1">
                                <label class="form-check-label" for="gridCheck1">Tick to Hide</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="description-input" class="col-sm-2 col-form-label">Description</label>
                        <div class="col-sm-8">
                            @if ($errors->has('description'))
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                            @endif
                            <input type="text" name="description" id="description-input" class="form-control">
                         </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger px-3" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success px-3">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- edit-->
@section('scripts')
<script src="{{ Theme::asset('assets/libs/summernote/summernote.min.js') }}"></script>
<script src="{{ Theme::asset('assets/js/showEditor.js') }}"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
       //editcat
       $('#selecttag').select2({
            tags: true,
            placeholder: "Add a Tag",
       });

       
         $(".delcat").click(function() {
             //alert( "Handler for .click() called." );
             swal({
				title: "Warning..!",
				text: "Do you want to delete  "+$(this).data('name')+" ?",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
			.then((value) => {
				if(value){
					//window.location.href = $(this).attr('href');
                    //console.log((this).dataset.id,'idnya');
                    $('#catDELETE'+(this).dataset.id).submit();
				}else{
				
					return false;
				}
			});
			return false;
        }); 

       
         $(".delete-artikel").click(function() {
             //alert( "Handler for .click() called." );
             swal({
				title: "Warning..!",
				text: "Do you want to delete  "+$(this).data('name')+" ?",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
			.then((value) => {
				if(value){
					//window.location.href = $(this).attr('href');
                    //console.log((this).dataset.id,'idnya');
                    $('#artikelDELETE'+(this).dataset.id).submit();
				}else{
				
					return false;
				}
			});
			return false;
        }); 






    });
</script> 
@endsection