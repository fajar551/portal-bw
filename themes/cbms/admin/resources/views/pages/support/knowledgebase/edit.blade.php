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
                                     <form action="{{ url($url.'knowledgebase/category-update/'.$category->id) }}" method="post" enctype="multipart/form-data">
                                       <div class="card p-3">
                                          @method('PUT')
                                          {{ csrf_field() }}
                                          <input type="hidden" name="id" value="{{$category->id}}"/>
                                          <div class="form-group row">
                                                <label for="catName" class="col-sm-2 col-form-label">Parent Category</label>
                                                <div class="col-sm-10">
                                                   <select name="parentcategory" class="form-control">
                                                      <option>Top Level</option>
                                                      @foreach($perent as $p)
                                                         <option value="{{ $p->id }}" {{ ($category->parentid == $p->id )?'selected':'' }}  >{{ $p->name }}</option>
                                                      @endforeach
                                                   </select>
                                                </div>
                                          </div>
                                          <div class="form-group row">
                                             <label for="catName" class="col-sm-2 col-form-label">Category Name</label>
                                             <div class="col-sm-6">
                                                @if ($errors->has('name'))
                                                      <span class="text-danger">{{ $errors->first('name') }}</span>
                                                @endif
                                                <input type="text" name="name" value="{{$category->name}}" id="catName" class="form-control" />
                                             </div>
                                             <div class="col-sm-3">
                                                <div class="form-check mt-2">
                                                      <input class="form-check-input" name="hidden" type="checkbox"  id="gridCheck1" {{ ($category->hidden ==1 )?'checked':'' }}>
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
                                                <input type="text" name="description" id="description-input" value="{{ $category->description  }}" class="form-control">
                                             </div>
                                          </div>
                                          
                                       </div>
                                       <div class="card">
                                          <div class="card-body">
                                             <div class="card-title mb-5">
                                                <h3>Multi-Lingual Translations</h3>
                                             </div>
                                             <div class="inputmulti">
                                             @foreach($catid as $k => $v)

                                             <div class="row mb-2">
                                                <div class="col-md-6">
                                                   <div class="form-group row">
                                                      <label for="catName" class="col-sm-3 col-form-label"><span class="font-weight-bold">{{$k}}</span>: Name</label>
                                                      <div class="col-sm-9">
                                                            <input type="text" name="nmultilang_name[{{strtolower($k)}}]" value="{{$v['name']}}"  class="form-control">
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-md-6">
                                                   <div class="form-group row">
                                                      <label for="catName" class="col-sm-2 col-form-label">Description:</label>
                                                      <div class="col-sm-10">
                                                            <input type="text" name="multilang_desc[{{strtolower($k)}}]" value="{{$v['description']}}" class="form-control">
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>

                                             @endforeach
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

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection