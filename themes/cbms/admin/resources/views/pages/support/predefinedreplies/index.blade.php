@extends('layouts.basecbms')

@section('title')
<title>{{ Cfg::getValue('CompanyName') }} -  Predefined Replies</title>
@endsection

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <!-- Sidebar Shortcut -->
                 
                <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Predefined Replies</h4>
                                    </div>
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
                                <!-- START HERE -->
                                <div class="card p-3">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <nav>
                                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                    <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-add-category" role="tab" aria-controls="nav-add-category" aria-selected="true">Add
                                                        Category</a>
                                                    <a class="nav-item nav-link" id="nav-predefined-reply-tab" data-toggle="tab" href="#nav-predefined-reply" role="tab" aria-controls="nav-predefined-reply" aria-selected="false">Add
                                                        Predefined Reply</a>
                                                    <a class="nav-item nav-link" id="nav-search-filter-tab" data-toggle="tab" href="#nav-search-filter" role="tab" aria-controls="nav-search-filter" aria-selected="false">Search/Filter</a>
                                                </div>
                                            </nav>
                                            <div class="tab-content" id="nav-tabContent">
                                                    <div class="tab-pane fade show active" id="nav-add-category" role="tabpanel" aria-labelledby="nav-add-category-tab">
                                                        <div class="card p-3">
                                                            <form  action="{{ url($baseURL.'category-store') }}" method="POST">
                                                            {{ csrf_field() }}
                                                                <div class="form-group row">
                                                                    <label for="category-name" class="col-sm-2 col-form-label">Category Name</label>
                                                                    <div class="col-sm-10 col-lg-8">
                                                                        @if ($errors->has('category_name'))
                                                                            <span class="text-danger">{{ $errors->first('category_name') }}</span>
                                                                        @endif
                                                                        <input type="text" name="category_name" id="category-name" class="form-control">
                                                                    </div>
                                                                    <div class="col-sm-12 col-lg-2">
                                                                        <button type="submit" class="btn btn-success ">Add
                                                                            Category</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="nav-predefined-reply" role="tabpanel" aria-labelledby="nav-predefined-reply-tab">
                                                        <div class="card p-3">
                                                            <div class="form-group row">
                                                                <label for="article-name" class="col-sm-2 col-form-label">Article Name</label>
                                                                <div class="col-sm-12 col-lg-5">
                                                                    <input type="text" name="article_name" id="article-name" class="form-control">
                                                                </div>
                                                                <div class="col-sm-12 col-lg-5">
                                                                    <button class="btn btn-success px-5">
                                                                        Add Article
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                <div class="tab-pane fade" id="nav-search-filter" role="tabpanel" aria-labelledby="nav-search-filter-tab">
                                                    <div class="card p-3">
                                                        <div class="row">
                                                            <div class="col-lg-6 col-sm-12">
                                                                <div class="from-group row">
                                                                    <label for="article-name" class="col-sm 2 col-form-label">Article
                                                                        Name</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" name="article_name" id="article-name" class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 col-sm-12">
                                                                <div class="form-group row">
                                                                    <label for="message-field" class="col-sm-2 col-form-label">Message</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" name="message" id="message-field" class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-12 d-flex justify-content-center">
                                                                <button class="btn btn-primary mt-2 px-5">
                                                                    <span class="align-middle"><i class="ri-search-line mr-2"></i></span>
                                                                    Search
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <!--
                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb">
                                                    <li class="breadcrumb-item"><a href="predefined-replies-categories.html">Home</a></li>
                                                    <li class="breadcrumb-item active">(Category Name)</li>
                                                </ol>
                                            </nav>
                                            -->
                                            <h4 class="card-title mt-3">
                                                    Categories
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @foreach($category as $r)
                                        <div class="col-lg-2 p-3 font-size-16">
                                            <div class="d-flex align-items-center">
                                                <i class="ri-file-fill mr-2"></i>
                                                <a href="predefined-replies-edit.html" class="link-category">
                                                    {{ $r->name }}
                                                </a>
                                                <div class="action-btn ml-3 mt-1">
                                                    <a href="{{ url($baseURL.'category-edit/'.$r->id) }}" ><i class="ri-edit-box-line"></i></a>
                                                    <a href="{{ url($baseURL.'category-destroy/'.$r->id) }}" title="Delete Category" class="delete-icon delcat" data-id="{{ $r->id }}" data-name="{{ $r->name }}" title="Delete Category" class="delete-icon delcat" >
                                                        <i class="ri-indeterminate-circle-fill"></i>
                                                        <form id="catDELETE{{$r->id}}" action="{{ url($baseURL.'category-destroy/'.$r->id) }}" method="post">
                                                                    {{ method_field('DELETE') }}
                                                                    {{ csrf_field() }}
                                                                    <input type="hidden" name="id" value="{{$r->id}}">
                                                                    <input type="submit" style="display:none;">
                                                        </form>
                                                    </a>
                                                   
                                                </div>
                                            </div>
                                           <!--<p class="p-0 m-0">Message</p> -->
                                        </div>
                                        @endforeach

                                    </div>
                                </div>
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
<script src="{{ Theme::asset('assets/libs/summernote/summernote.min.js') }}"></script>
<script src="{{ Theme::asset('assets/js/showEditor.js') }}"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
      
         $(".delcat").click(function() {
             //alert( "Handler for .click() called." );
             swal({
				title: "Warning..!",
				text: "Do you want to delete  "+$(this).data('name')+" ?",
				icon: "danger",
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