@extends('layouts.clientbase')


@section('title')
    <title>My Rent Domain Page</title>
@endsection

@section('content')
    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 col-md-8">
                    <h2 class="mb-0">My Rent Domain</h2>
                    <small class="text-muted">By CBMS</small>
                </div>
                 {{-- Message alert --}}
                <div class="col-md-12">
                    @if (Session::get('alert-message'))
                        <div class="alert alert-{{ Session::get('alert-type') }}" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {!! nl2br(Session::get('alert-message')) !!}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <b>Error:</b>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                {{-- Message alert --}}

                <div class="col-md-12 mt-3">
                    <div class="table-responsive">
                        <div class="card">
                            <div class="card-body">
                                <h5>My Rent Domain</h5>
                                <table id="rentDomainTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">Domain</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Harga</th>
                                            <th scope="col">Start Rent</th>
                                            <th scope="col">End Rent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($domain_rents_transaction as $data)
                                            <tr>
                                                <td>{{ $data->domain ?? '-' }}</td>
                                                <td>
                                                    @if($data->status == 'NEED_VERIFY')
                                                        <span class="badge badge-warning">Unverified</span>
                                                    @elseif($data->status == 'VERIFIED')
                                                        <span class="badge badge-success">Verified</span>
                                                    @else
                                                        <span class="badge badge-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ isset($data->price) ? 'Rp ' . number_format($data->price, 0, ',', '.') : '-' }}</td>
                                                <td>{{ $data->start_rent ?? '-' }}</td>
                                                <td>{{ $data->end_rent ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($domain_rents_transaction->isEmpty())
                                    <div class="alert alert-info mt-3" role="alert">
                                        Data is not available.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a class="btn btn-secondary" href="{{ route('pages.domain.selldomains.index') }}">Back</a>
    </div>
@endsection

@section('scripts')
<!-- DataTables JS -->
<script>
    $(document).ready(function() {
        $('#rentDomainTable').DataTable({
            "language": {
                "emptyTable": "Data is not available." // Pesan ketika tidak ada data
            }
        });
    });
</script>
@endsection