@extends('layouts.main')
@section('content')
    <div class="row mt-8">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">
                                External accounts
                            </h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('external-account.add',['provider'=>'google']) }}"
                               class="btn btn-sm btn-primary">
                                Add account
                            </a>
                        </div>
                    </div>
                </div>
                @if($externalAccounts->total())
                    <div class="table-responsive">
                        <!-- Projects table -->
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col">Provider name</th>
                                <th scope="col">Provider id</th>
                                <th scope="col">Account name</th>
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($externalAccounts as $externalAccount)
                                <tr>
                                    <th scope="row">
                                        {{ ucfirst($externalAccount->provider_name) }}
                                    </th>
                                    <td>
                                        {{ $externalAccount->provider_id }}
                                    </td>
                                    <td>
                                        {{ $externalAccount->name }}
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-danger"
                                           href="{{ route('external-account.disconnect',['provider' => $externalAccount->provider_id, 'provider_id'=>$externalAccount->provider_id]) }}"
                                           onclick="event.preventDefault(); document.getElementById('external_account_{{ $externalAccount->provider_id }}').submit();">
                                            <span>Disconnect</span>
                                        </a>
                                        <form id="external_account_{{ $externalAccount->provider_id }}"
                                              action="{{ route('external-account.disconnect',['provider' => $externalAccount->provider_name, 'provider_id'=>$externalAccount->provider_id]) }}"
                                              method="POST"
                                              class="d-none">
                                            @method('delete')
                                            @csrf
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mr-3 ml-3">
                        <div class="alert alert-info">
                            <i class="fa fa-exclamation-triangle"></i>
                            <span>
                            You haven't added any account yet
                        </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
