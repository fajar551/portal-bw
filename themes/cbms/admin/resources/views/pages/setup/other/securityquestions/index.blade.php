@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Security Questions</title>
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
                                        <h4 class="mb-3">Security Questions</h4>
                                    </div>
                                    @if ($message = Session::get('success'))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert"
                                                id="success-alert">
                                                <h5>Question Added Successfully!</h5>
                                                <small>{{ $message }}</small>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h4 class="card-title">
                                                    Questions List
                                                </h4>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th width="800">Question</th>
                                                                        <th width="800">Uses</th>
                                                                        <th></th>
                                                                        {{-- <th></th> --}}
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse ($results as $result)
                                                                    <tr>
                                                                        <td>{{$result['question']}}</td>
                                                                        <td>{{$result['uses']}}</td>
                                                                        <td>
                                                                            <form onsubmit="return confirm('Click OK if you are sure you want to delete this question?')" action="{{route('admin.pages.setup.other.securityquestions.delete')}}" method="post">
                                                                                @csrf
                                                                                <input type="hidden" name="id" value="{{$result['id']}}">
                                                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                                            </form>
                                                                        </td>
                                                                        {{-- <td></td> --}}
                                                                    </tr>                                                                        
                                                                    @empty
                                                                    <tr>
                                                                        <td colspan="3">No Data</td>
                                                                    </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h4 class="card-title">Add Security Questions</h4>
                                                <form action="{{route('admin.pages.setup.other.securityquestions.post')}}" method="post">
                                                    @csrf
                                                    <div class="form-group row">
                                                        <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                                            Security Question</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input name="question" type="text" class="form-control" required>
                                                        </div>
                                                        <div class="col-lg-5">
                                                            <button class="btn btn-success mb-0">Save Changes</button>
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
                    <!-- End MAIN CARD -->
                </div>
            </div>
        </div>
    </div>
@endsection
