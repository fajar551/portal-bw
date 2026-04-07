@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} - Predefined Replies</title>
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
                        <div class="col-sm-12">
                           <h4 class="mb-3">Predefined Replies</h4>
                        </div>
                        @php
                           $aInt = new \App\Helpers\Admin();
                        @endphp
                        @if ($action == '')
                           <div class="col-lg-12">
                              @if ($addedcat)
                                 <div class="alert alert-success"><i
                                       class="fas fa-info-circle mr-2"></i>{!! \App\Helpers\AdminFunctions::infoBoxMessage('<b>Success</b>', $aInt->lang('support', 'predefaddedcat')) !!}</div>
                              @endif
                              @if ($save)
                                 <div class="alert alert-success"><i
                                       class="fas fa-info-circle mr-2"></i>{!! \App\Helpers\AdminFunctions::infoBoxMessage('<b>Success</b>', $aInt->lang('support', 'predefsave')) !!}</div>
                              @endif
                              @if ($savecat)
                                 <div class="alert alert-success"><i
                                       class="fas fa-info-circle mr-2"></i>{!! \App\Helpers\AdminFunctions::infoBoxMessage('<b>Success</b>', $aInt->lang('support', 'predefsavecat')) !!}</div>
                              @endif
                              @if ($delete)
                                 <div class="alert alert-success"><i
                                       class="fas fa-info-circle mr-2"></i>{!! \App\Helpers\AdminFunctions::infoBoxMessage('<b>Success</b>', $aInt->lang('support', 'predefdelete')) !!}</div>
                              @endif
                              @if ($deletecat)
                                 <div class="alert alert-success"><i
                                       class="fas fa-info-circle mr-2"></i>{!! \App\Helpers\AdminFunctions::infoBoxMessage('<b>Success</b>', $aInt->lang('support', 'predefdeletecat')) !!}</div>
                              @endif
                           </div>
                           <div class="col-lg-12">
                              <!-- START HERE -->
                              @php
                                 $catLists = \App\Models\Ticketpredefinedcat::where('parentid', $catid ? $catid : 0)
                                     ->orderBy('name', 'asc')
                                     ->get();
                              @endphp
                              <div class="card p-3">
                                 <div class="row">
                                    <div class="col-lg-12">
                                       <nav>
                                          <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                             <a class="nav-item nav-link" id="nav-home-tab" data-toggle="tab"
                                                href="#nav-add-category" role="tab" aria-controls="nav-add-category"
                                                aria-selected="true">Add
                                                Category</a>
                                             <a class="nav-item nav-link" id="nav-predefined-reply-tab" data-toggle="tab"
                                                href="#nav-predefined-reply" role="tab"
                                                aria-controls="nav-predefined-reply" aria-selected="false">Add
                                                Predefined Reply</a>
                                             <a class="nav-item nav-link" id="nav-search-filter-tab" data-toggle="tab"
                                                href="#nav-search-filter" role="tab" aria-controls="nav-search-filter"
                                                aria-selected="false">Search/Filter</a>
                                          </div>
                                       </nav>
                                       <div class="tab-content" id="nav-tabContent">
                                          <div class="tab-pane fade" id="nav-add-category" role="tabpanel"
                                             aria-labelledby="nav-add-category-tab">
                                             <div class="card p-3 mb-1">
                                                <form method="POST"
                                                   action="{{ route($route, ['catid' => $catid ? $catid : '0', 'addcategory' => 'true']) }}">
                                                   @csrf
                                                   <div class="form-group row">
                                                      <label class="col-sm-12 col-md-2 col-form-label"
                                                         for="catname">{{ $aInt->lang('support', 'catname') }}</label>
                                                      <div class="col-sm-12 col-md-10">
                                                         <input type="text" name="catname" size="40"
                                                            class="form-control">
                                                      </div>
                                                      <div class="col-sm-12 text-center">
                                                         <button type="submit"
                                                            class="btn btn-success px-5 mt-3">{{ $aInt->lang('support', 'addcategory') }}</button>
                                                      </div>
                                                   </div>
                                                </form>
                                             </div>
                                          </div>
                                          <div class="tab-pane fade" id="nav-predefined-reply" role="tabpanel"
                                             aria-labelledby="nav-predefined-reply-tab">
                                             <div class="card p-3 mb-1">
                                                @if ($catid != 0)
                                                   <form
                                                      action="{{ route($route, ['catid' => $catid, 'addreply' => 'true']) }}"
                                                      method="POST">
                                                      @csrf
                                                      <div class="form-group row">
                                                         <label for="article-name" class="col-sm-2 col-form-label">Article
                                                            Name</label>
                                                         <div class="col-sm-12 col-lg-5">
                                                            <input type="text" name="name" id="article-name"
                                                               class="form-control">
                                                         </div>
                                                         <div class="col-sm-12 col-lg-5">
                                                            <button type="submit" class="btn btn-success px-5">
                                                               Add Article
                                                            </button>
                                                         </div>
                                                      </div>
                                                   </form>
                                                @else
                                                   <h5 class="mb-0">
                                                      {{ $aInt->lang('support', 'pdnotoplevel') }}
                                                   </h5>
                                                @endif
                                             </div>
                                          </div>
                                          <div class="tab-pane fade" id="nav-search-filter" role="tabpanel"
                                             aria-labelledby="nav-search-filter-tab">
                                             <div class="card p-3 mb-1">
                                                <form action="{{ route($route, ['catid' => $catid]) }}" method="post">
                                                   @csrf
                                                   <div class="row">
                                                      <div class="col-lg-6 col-sm-12">
                                                         <div class="from-group row">
                                                            <label for="article-name"
                                                               class="col-sm 2 col-form-label">Article
                                                               Name</label>
                                                            <div class="col-sm-10">
                                                               <input type="text" name="title" id="article-name"
                                                                  class="form-control">
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-lg-6 col-sm-12">
                                                         <div class="form-group row">
                                                            <label for="message-field"
                                                               class="col-sm-2 col-form-label">Message</label>
                                                            <div class="col-sm-10">
                                                               <input type="text" name="message" id="message-field"
                                                                  class="form-control">
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <input type="hidden" name="search" value="1">
                                                      <div class="col-lg-12 d-flex justify-content-center">
                                                         <button type="submit" class="btn btn-primary mt-2 px-5">
                                                            <span class="align-middle"><i
                                                                  class="ri-search-line mr-2"></i></span>
                                                            Search
                                                         </button>
                                                      </div>
                                                   </div>
                                                </form>
                                             </div>
                                          </div>
                                          @if ($catid != 0)
                                             @php
                                                $categories = \App\Models\Ticketpredefinedcat::where('id', $catid ? $catid : 0)
                                                    ->orderBy('name', 'asc')
                                                    ->get();
                                                
                                                $categories2 = \App\Models\Ticketpredefinedcat::where('parentid', $catid ? $catid : 0)
                                                    ->orderBy('name', 'asc')
                                                    ->get();
                                             @endphp
                                             @foreach ($categories as $k => $v)
                                                @php
                                                   $catparentid = $v->parentid;
                                                   $catname = $v->name;
                                                   $catbreadcrumbnav = " > <a href=\"" . route($route, ['catid' => $v->id]) . "\">" . $catname . '</a>';
                                                @endphp
                                                @if ($catparentid != 0)
                                                   @php
                                                      $result = \App\Models\Ticketpredefinedcat::where('id', $catparentid)->get();
                                                   @endphp
                                                   @foreach ($result as $kr => $vr)
                                                      @php
                                                         $cattempid = $vr->id;
                                                         $catparentid = $vr->parent;
                                                         $catname = $vr->name;
                                                         $catbreadcrumbnav = ' > <a href=' . route($route, ['catid' => $cattempid]) . '>' . $catname . '</a>' . $catbreadcrumbnav;
                                                      @endphp
                                                   @endforeach
                                                @endif
                                                <p class="{{ $search == 1 ? 'd-none' : '' }}">
                                                   {{ $aInt->lang('support', 'youarehere') }}: <a
                                                      href="{{ route($route) }}">{{ $aInt->lang('support', 'toplevel') }}</a>
                                                   {!! $catbreadcrumbnav !!} </p>
                                             @endforeach
                                          @endif
                                       </div>
                                    </div>

                                    <div class="col-lg-12 {{ $search == 1 ? 'd-none' : '' }}">
                                       @if ($catid == 0)
                                          <h5 class="my-3">
                                             {{ $aInt->lang('support', 'category') }}
                                          </h5>
                                       @else
                                          @if ($categories2->isNotEmpty())
                                             <h5 class="my-3">
                                                {{ $aInt->lang('support', 'category') }}
                                             </h5>
                                             <div class="row">
                                                @foreach ($categories2 as $r => $c)
                                                   <div class="col-sm-12 col-md-3 mb-2">
                                                      @php
                                                         $result3 = \App\Models\Ticketpredefinedreply::select('id')
                                                             ->where('catid', $c->id)
                                                             ->count();
                                                      @endphp
                                                      <i class="fas fa-folder mr-1 text-warning"></i><a
                                                         href="{{ route($route, ['catid' => $c->id]) }}">{{ $c->name }}
                                                      </a>({{ $result3 }})
                                                      <a
                                                         href="{{ route($route, ['action' => 'editcat', 'id' => $c->id]) }}"><i
                                                            class="fas fa-edit"></i></a>
                                                      <a href="{{ route($route, ['sub' => 'deletecategory', 'id' => $c->id, 'catid' => $catid]) }}"
                                                         class="delcat" data-name="{{ $c->name }}"><i
                                                            class="fas fa-minus-circle text-danger"></i></a>
                                                   </div>
                                                @endforeach
                                             </div>
                                          @endif

                                          <h5 class="my-3">
                                             {{ $aInt->lang('support', 'replies') }}
                                          </h5>
                                       @endif
                                       @if ($catid == 0)
                                          <div class="row">
                                             @foreach ($catLists as $k => $v)
                                                <div class="col-sm-12 col-md-3 col-lg-3 mb-2">
                                                   @php
                                                      $result3 = \App\Models\Ticketpredefinedreply::select('id')
                                                          ->where('catid', $v->id)
                                                          ->count();
                                                   @endphp
                                                   <i class="fas fa-folder mr-1 text-warning"></i><a
                                                      href="{{ route($route, ['catid' => $v->id]) }}">{{ $v->name }}
                                                   </a>({{ $result3 }})
                                                   <a
                                                      href="{{ route($route, ['action' => 'editcat', 'id' => $v->id]) }}"><i
                                                         class="fas fa-edit"></i></a>
                                                   <a href="{{ route($route, ['sub' => 'deletecategory', 'id' => $v->id, 'catid' => $catid]) }}"
                                                      data-catid="{{ $v->id }}" class="delcat"
                                                      data-name="{{ $v->name }}"><i
                                                         class="fas fa-minus-circle text-danger"></i></a>
                                                </div>
                                             @endforeach
                                          </div>
                                       @else
                                          @php
                                             $result = \App\Models\Ticketpredefinedreply::where('catid', $catid)
                                                 ->orderBy('name', 'asc')
                                                 ->get();
                                          @endphp
                                          <div class="row">
                                             {{-- {{dd($result->isNotEmpty())}} --}}
                                             @if ($result->isNotEmpty())
                                                @foreach ($result as $k => $v)
                                                   <div class="col-sm-12 mb-3">
                                                      <h6 class="font-weight-bold">
                                                         <i class="far fa-sticky-note mr-1 text-black-50"></i><a
                                                            href="{{ route($route, ['action' => 'edit', 'id' => $v->id]) }}">{{ $v->name }}</a>
                                                         <a class="delcat"
                                                            href="{{ route($route, ['sub' => 'delete', 'id' => $v->id, 'catid' => $catid]) }}"
                                                            data-name="{{ $v->name }}">
                                                            <i class="ml-1 fas fa-minus-circle text-danger"></i>
                                                         </a>
                                                      </h6>
                                                      @if ($v->reply != '')
                                                         <div style="width: 100%; max-height: 40px; overflow: hidden;">
                                                            {{ $v->reply }}</div>
                                                      @else
                                                         <div>...</div>
                                                      @endif
                                                   </div>
                                                @endforeach
                                             @else
                                                <div class="col-sm-12 mb-3">
                                                   <h6>{{ $aInt->lang('support', 'norepliesfound') }}</h6>
                                                </div>
                                             @endif
                                          </div>
                                       @endif
                                    </div>

                                    @if ($search == 1)
                                       @php
                                          $where = '';
                                          $catName = '';
                                          // dd( && $search != 0 && $title != '' );
                                          if (isset($catid) && $catid != 0) {
                                              $where .= " AND catid='" . \App\Helpers\Database::db_escape_string($catid ?? 0) . "'";
                                          }
                                          if ($title) {
                                              $where .= " AND name LIKE '%" . \App\Helpers\Database::db_escape_string($title) . "%'";
                                          }
                                          if ($message) {
                                              $where .= " AND reply LIKE '%" . \App\Helpers\Database::db_escape_string($message) . "%'";
                                          }
                                          if ($where) {
                                              $where = substr($where, 5);
                                          }
                                          // dd($where);
                                          if (($search != 0 && $catid == '' && $catid == 0 && $title == '') || $where == '') {
                                              $result = \App\Models\Ticketpredefinedreply::select()
                                                  ->orderBy('name', 'asc')
                                                  ->get();
                                          } else {
                                              $result = \App\Models\Ticketpredefinedreply::whereRaw($where)
                                                  ->orderBy('name', 'asc')
                                                  ->get();
                                          }
                                          
                                          if ($catid != 0 && $search == 1) {
                                              $currCat = \App\Models\Ticketpredefinedcat::where('id', $catid)->get();
                                              foreach ($currCat as $k) {
                                                  $catName .= $k->name;
                                              }
                                          }
                                       @endphp
                                       <div class="col-lg-12">
                                          <h5 class="mt-3">Search Result</h5>
                                          <p>
                                             {{ $aInt->lang('support', 'youarehere') }}:
                                             <a href="{{ route($route) }}">{{ $aInt->lang('support', 'toplevel') }} >
                                             </a>
                                             {{-- {{ dd(isset($catid)) }} --}}
                                             @if (isset($catid) && $catid != 0)
                                                <a href="{{ route($route, ['catid' => $catid]) }}">{{ $catid != '0' ? $catName : '' }}
                                                   > </a>
                                             @endif
                                             <a href="{{ route($route) }}">{{ $aInt->lang('', 'search') }}</a>
                                          </p>
                                       </div>
                                       @if ($result->isNotEmpty())
                                          @foreach ($result as $k => $v)
                                             <div class="col-sm-12 mb-3">
                                                <h6 class="font-weight-bold">
                                                   <i class="far fa-sticky-note mr-1 text-black-50"></i><a
                                                      href="{{ route($route, ['action' => 'edit', 'id' => $v->id]) }}">{{ $v->name }}</a>
                                                   <a href="#">
                                                      <i class="ml-1 fas fa-minus-circle text-danger"></i>
                                                   </a>
                                                </h6>
                                                @if ($v->reply != '')
                                                   <div style="width: 100%; max-height: 40px; overflow: hidden;">
                                                      {{ $v->reply }}</div>
                                                @else
                                                   <div>...</div>
                                                @endif
                                             </div>
                                          @endforeach
                                       @else
                                          <div class="col-12 mb-3 text-center">

                                             <h3> $aInt->lang('support', 'norepliesfound') }}</h3>
                                          </div>
                                       @endif
                                       <div class="col-12">
                                          <button class="btn btn-light px-3" type="button"
                                             onclick="javascript:history.go(-1)">Previous page</button>
                                       </div>
                                    @endif
                                 </div>
                              </div>
                           @else
                              @php
                                 $result = \App\Models\Ticketpredefinedreply::where('id', $id)->get();
                                 $categoryListDropdown = \App\Models\Ticketpredefinedcat::select()
                                     ->orderBy('name', 'asc')
                                     ->get();
                              @endphp
                              @if ($action == 'edit')
                                 <div class="col-sm-12 col-md-12">
                                    @foreach ($result as $k => $v)
                                       @php
                                          $catid = $v->id;
                                          $name = $v->name;
                                          $reply = $v->reply;
                                          $categoriesId = $v->catid;
                                          $markdown = $aInt->addMarkdownEditor('predefinedReplyMDE', 'predefined_reply_' . md5($id . session()->get('adminid')), 'predefinedReply');
                                       @endphp
                                    @endforeach
                                    <form action="{{ route($route, ['sub' => 'save', 'id' => $id]) }}" method="post">
                                       @csrf
                                       <div class="p-3 mb-2 bg-light rounded">
                                          <div class="form-group row">
                                             <label for="category" class="col-sm-12 col-md-2 col-form-label">
                                                {{ $aInt->lang('support', 'category') }}
                                             </label>
                                             <div class="col-sm-12 col-md-8">
                                                <select name="catid" id="catid" class="form-control">
                                                   @foreach ($categoryListDropdown as $k => $v)
                                                      <option value="{{ $v->id }}"
                                                         {{ $categoriesId == $v->id ? 'selected' : '' }}>
                                                         {{ $v->name }}</option>
                                                   @endforeach
                                                </select>
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label for="replyname" class="col-sm-12 col-md-2 col-form-label">
                                                {{ $aInt->lang('support', 'replyname') }}
                                             </label>
                                             <div class="col-sm-12 col-md-8">
                                                <input type="text" name="name" class="form-control"
                                                   value="{{ $name }}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label for="replyname" class="col-sm-12 col-md-2 col-form-label">
                                                {{ $aInt->lang('mergefields', 'title') }}
                                             </label>
                                             <div class="col-sm-12 col-md-8">
                                                <ul class="list-unstyled">
                                                   <li>
                                                      [NAME] - {{ $aInt->lang('mergefields', 'ticketname') }}
                                                   </li>
                                                   <li>
                                                      [FIRSTNAME] - {{ $aInt->lang('fields', 'firstname') }}
                                                   </li>
                                                   <li>
                                                      [EMAIL] - {{ $aInt->lang('mergefields', 'ticketemail') }}
                                                   </li>
                                                </ul>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="row">
                                          <div class="col-sm-12 col-md-12">
                                             <textarea name="reply" id="predefinedReply" rows="18"
                                                class="form-control">{{ $reply }}</textarea>
                                          </div>
                                       </div>
                                       <div class="d-flex justify-content-center my-2">
                                          <button type="submit" class="btn btn-success px-3 mx-1">Save Changes</button>
                                          <a href="javascript:history.go(-1)">
                                             <button type="button" class="btn btn-light px-3 mx-1">Cancel Changes</button>
                                          </a>
                                       </div>
                                    </form>
                                 </div>
                              @else
                                 @if ($action == 'editcat')
                                    <div class="col-sm-12 col-md-12">
                                       <form action="{{ route($route, ['sub' => 'savecat', 'id' => $id]) }}" method="post">
                                          @csrf
                                          <div class="p-3 mb-2 bg-light rounded">
                                             <div class="form-group row">
                                                <label for="category" class="col-sm-12 col-md-2 col-form-label">
                                                   {{ $aInt->lang('support', 'category') }}
                                                </label>
                                                <div class="col-sm-12 col-md-8">
                                                   <select name="parentid" id="parentid" class="form-control">
                                                      <option value="0">{{$aInt->lang('support', 'toplevel')}}</option>
                                                      @foreach ($categoryListDropdown as $k => $v)
                                                         <option value="{{ $v->id }}" class="{{$id == $v->id ? 'd-none' : ''}}">
                                                            {{ $v->name }}
                                                         </option>
                                                         @php
                                                            if ($id == $v->id) {
                                                               $catName = $v->name;
                                                            }
                                                         @endphp
                                                      @endforeach
                                                   </select>
                                                </div>
                                             </div>
                                             <div class="form-group row">
                                                <label for="replyname" class="col-sm-12 col-md-2 col-form-label">
                                                   {{ $aInt->lang('support', 'catname') }}
                                                </label>
                                                <div class="col-sm-12 col-md-8">
                                                   <input type="text" name="name" class="form-control" value="{{ $catName }}">
                                                </div>
                                             </div>
                                          </div>
                                          <div class="d-flex justify-content-center my-2">
                                             <button type="submit" class="btn btn-success px-3 mx-1">Save Changes</button>
                                             <a href="javascript:history.go(-1)">
                                                <button type="button" class="btn btn-light px-3 mx-1">Cancel Changes</button>
                                             </a>
                                          </div>
                                       </form>
                                    </div>
                                 @endif
                              @endif
                        @endif
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
   <script src="{{ Theme::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
   <!-- BS Markdown -->
   <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/markdown.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/to-markdown.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/bootstrap-markdown.js') }}"></script>
   @if ($action == 'edit')
      <script type="text/javascript">
         $(document).ready(function() {
            {!! $markdown !!}
         })
      </script>
   @endif
   <script type="text/javascript">
      $(document).ready(function() {

         $(".delcat").click(function() {
            const id = $(this).data('catid');
            Swal.fire({
                  title: "Are you sure?",
                  text: "Do you want to delete  " + $(this).data('name') + " ?",
                  icon: "error",
                  showCancelButton: true,
                  confirmButtonText: 'Delete',
                  confirmButtonColor: '#b3160b',
               })
               .then((result) => {
                  if (result.isConfirmed) {
                     Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                     )
                     location.href = $(this).attr('href');
                  } else {

                     return false;
                  }
               });
            return false;
         });


         $(".delete-artikel").click(function() {
            //alert( "Handler for .click() called." );
            swal({
                  title: "Warning..!",
                  text: "Do you want to delete  " + $(this).data('name') + " ?",
                  icon: "warning",
                  buttons: true,
                  dangerMode: true,
               })
               .then((value) => {
                  if (value) {
                     //window.location.href = $(this).attr('href');
                     //console.log((this).dataset.id,'idnya');
                     $('#artikelDELETE' + (this).dataset.id).submit();
                  } else {

                     return false;
                  }
               });
            return false;
         });






      });
   </script>

@endsection
