@extends('layouts.app')
@section('title', 'ApiSpecification')
@section('content')
<div class="container">
    <h2>API Specifications</h2>

    <!-- API Tokens -->
    <div class="card mb-4">
        <div class="card-header">API Tokens</div>
        <div class="card-body">
            <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#addTokenModal">
                Add New
            </button>

            <table class="table">
                <thead>
                    <tr>
                        <th>IP</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tokens as $token)
                        <tr>
                            <td>{{ $token->ip }}</td>
                            <td>{{ $token->token }}</td>
                            <td>{{ $token->status }}</td>
                            <td>
                                <a href="{{ route('api.token.toggle', $token->id) }}" class="btn btn-sm btn-warning">Toggle Status</a>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

            <!-- Add New Token -->
            <div class="modal fade" id="addTokenModal" tabindex="-1" aria-labelledby="addTokenModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <form action="{{ route('api.token.store') }}" method="POST">
                    @csrf  
                    <div class="modal-header">
                    <h5 class="modal-title" id="addTokenModalLabel">Add New Token</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <div class="mb-2">
                        <input type="text" name="ip" placeholder="IP Address" class="form-control" required>
                    </div>
                    </div>
                    <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Token</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
                </div>
            </div>
</div>



        </div>
    </div>

    <!-- Callback URLs -->
    <div class="card">
        <div class="card-header">Callback URLs</div>
        <div class="card-body">
            <form action="{{ route('api.callback.update') }}" method="POST">
                @csrf
                <div class="mb-2">
                     <label for="payin_callback" class="form-label">Payin Callback URL</label>
                    <input type="url" name="payin_callback" placeholder="Payin Callback URL" class="form-control" value="{{ $callback->payin_callback ?? '' }}" required>
                </div>
                <div class="mb-2">
                     <label for="payout_callback" class="form-label">Payout Callback URL</label>
                    <input type="url" name="payout_callback" placeholder="Payout Callback URL" class="form-control" value="{{ $callback->payout_callback ?? '' }}" required>
                </div>
                <button type="submit" class="btn btn-success">Update URLs</button>
            </form>
        </div>
    </div>
</div>
@endsection
