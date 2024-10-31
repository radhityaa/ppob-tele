@extends('layouts.welcome.app')
@section('title', 'Riwayat Transaksi')

@section('content')
    <div class="login-el">
        <div class="card">
            <div class="card-body">
                <!-- Logo -->
                <div class="app-brand justify-content-center mb-4">
                    <a href="#" class="app-brand-link gap-2">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}" width="50"
                            height="50">
                        <span class="app-brand-text demo text-body fw-bolder text-uppercase">Ayasya Tech</span>
                    </a>
                </div>
                <!-- /Logo -->
                <h4 class="mb-2 text-center">Riwayat Transaksi</h4>
                <p class="mb-4">Silahkan Login telebih dahulu untuk melihat Riwayat Transaksi</p>

                <form id="formAuthentication" class="mb-3" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="user_token" class="form-label">User Token</label>
                        <input type="text" class="form-control" id="user_token" name="user_token"
                            value="{{ old('user_token') }}" placeholder="User Token" autocomplete="off" required />
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary d-grid w-100 btn-login" type="submit">Login</button>
                        <x-button-loading />
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="tableEl">
    </div>
@endsection

@push('page-js')
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })

        $('document').ready(function() {
            $('.btn-login').removeClass('d-none')
            $('.btn-loading').addClass('d-none')
            $('.login-el').removeClass('d-none')

            $('#formAuthentication').on('submit', function(e) {
                e.preventDefault()

                $('.btn-login').addClass('d-none')
                $('.btn-loading').removeClass('d-none')

                let userToken = $('#user_token').val()

                $.ajax({
                    url: '{{ route('histories.transaction.login') }}',
                    method: 'POST',
                    data: {
                        user_token: userToken
                    },
                    success: function(res) {
                        $('.btn-login').removeClass('d-none')
                        $('.btn-loading').addClass('d-none')
                        $('.login-el').addClass('d-none')

                        Swal.fire({
                            title: "Success!",
                            text: res.message,
                            icon: "success"
                        });

                        let url = "{!! route('histories.transaction', ':token') !!}"
                        url = url.replace(':token', userToken)

                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function(res) {
                                var tableEl = $('#tableEl')
                                tableEl.empty()

                                $.each(res, function(i, data) {
                                    var formatPrice = new Intl.NumberFormat(
                                        'id-ID', {
                                            minimumFractionDigits: 0,
                                            currency: 'IDR'
                                        }).format(data.price)

                                    var badgeClass = data.status ===
                                        'Sukses' ? 'bg-label-success' : (
                                            data.status === 'Pending' ?
                                            'bg-label-warning' :
                                            'bg-label-danger');

                                    var textClass = data.status ===
                                        'Sukses' ? 'text-success' : (
                                            data.status === 'Pending' ?
                                            'text-warning' :
                                            'text-danger');

                                    var cardHtml = `
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <span class="badge ${badgeClass}">${data.status}</span>
                                                        <div class="d-flex justify-content-center">
                                                            <sup class="h6 fw-medium pricing-currency mt-3 mb-0 me-1 ${textClass}">Rp</sup>
                                                            <h1 class="mb-0 ${textClass}">${formatPrice}</h1>
                                                        </div>
                                                    </div>
                                                    ${data.created_at}
                                                    <ul class="ps-3 g-2">
                                                        <li class="text-truncate-multiline">Invoice: ${data.invoice}</li>
                                                        <li class="text-truncate-multiline">Kode: ${data.buyer_sku_code}</li>
                                                        <li class="text-truncate-multiline">Target: ${data.target}</li>
                                                        <li class="text-truncate-multiline">Produk: ${data.product_name}</li>
                                                        <li class="text-truncate-multiline">Harga: ${data.price}</li>
                                                        <li class="text-truncate-multiline">Ket: ${data.message}</li>
                                                        <li class="text-truncate-multiline">SN: ${data.sn}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    tableEl.append(cardHtml)
                                })

                            },
                            error: function(error) {
                                Swal.fire({
                                    title: "Failed!",
                                    text: "Error, silahkan refresh ulang halaman",
                                    icon: "error"
                                });
                            }
                        })



                    },
                    error: function(error) {
                        $('.btn-login').removeClass('d-none')
                        $('.btn-loading').addClass('d-none')
                        $('.login-el').removeClass('d-none')

                        Swal.fire({
                            title: "Failed!",
                            text: error.responseJSON.message,
                            icon: "error"
                        });
                    }
                })
            })
        })
    </script>
@endpush
