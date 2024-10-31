@extends('layouts.welcome.app')

@section('title', 'Daftar Harga')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 py-2">
                    <label for="service" class="form-label">Layanan</label>
                    <select class="form-select form-control" id="service">
                        <option value="" selected disabled>-- Pilih Layanan --</option>
                        @foreach (getProducts() as $item)
                            <option value="{{ $item->category }}">{{ $item->category }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 py-2" style="display: none;" id="providerEl">
                    <label for="provider" class="form-label">Provider</label>
                    <select class="form-select form-control" id="provider">
                        <option value="" selected disabled>-- Pilih Provider --</option>
                    </select>
                </div>

                <div class="col-md-4 py-2" style="display: none;" id="categoryEl">
                    <label for="category" class="form-label">Kategori</label>
                    <select class="form-select form-control" id="category">
                        <option value="" selected disabled>-- Pilih Kategori --</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <div class="row g-4" id="servicesCon">
        </div>
    </div>
@endsection

@push('page-js')
    <script>
        let url = ''
        let method = ''

        function getUrl() {
            return url
        }

        function getMethod() {
            return method
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })

        $('select#service').on('change', function() {
            var service = $(this).val()

            $.ajax({
                url: "{{ route('prabayar.getProvider') }}",
                method: "GET",
                data: {
                    service: service
                },
                success: function(res) {
                    $('#providerEl').show()

                    let wrapper = $('select#provider')
                    let options = ''
                    wrapper.empty()
                    options = '<option value="" selected disabled>-- Pilih Provider --</option>'
                    $.each(res, function(index, item) {
                        options += '<option value="' + item.brand + '">' + item.brand +
                            '</option>'
                    })
                    wrapper.append(options)
                },
                error: function(err) {
                    console.log(err);
                }
            })
        })

        $('select#provider').on('change', function() {
            var provider = $(this).val()
            var service = $('select#service').val()

            $.ajax({
                url: "{{ route('prabayar.getType') }}",
                method: "GET",
                data: {
                    provider: provider,
                    service: service
                },
                success: function(res) {
                    $('#categoryEl').show()

                    let wrapper = $('select#category')
                    let options = ''
                    wrapper.empty()
                    options = '<option value="" selected disabled>-- Pilih Kategori --</option>'
                    $.each(res, function(index, item) {
                        options += '<option value="' + item.type + '">' + item.type +
                            '</option>'
                    })
                    wrapper.append(options)
                },
                error: function(err) {
                    console.log(err);
                }
            })
        })

        $('select#category').on('change', function() {
            var provider = $('select#provider').val()
            var service = $('select#service').val()
            var category = $(this).val()
            var servicesContainer = $('#servicesCon')

            $.ajax({
                url: "{{ route('prabayar.getServices') }}",
                method: "GET",
                data: {
                    provider: provider,
                    service: service,
                    category: category
                },
                success: function(res) {
                    servicesContainer.empty(); // Clear the existing content

                    // Assuming 'res' is an array of objects
                    $.each(res, function(i, service) {
                        var formatPrice = new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: 0,
                            currency: 'IDR'
                        }).format(service.price)

                        var badgeClass = service.seller_product_status ? 'bg-label-primary' :
                            'bg-label-danger';
                        var textClass = service.seller_product_status ? 'text-primary' :
                            'text-danger';
                        var buttonDisabled = !service.seller_product_status ? 'disabled' :
                            '';
                        var bgColor = service.seller_product_status ? 'bg-primary' :
                            'bg-danger';

                        var cardHtml = `
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="badge ${badgeClass}">${service.seller_product_status ? 'Normal' : 'Gangguan'}</span>
                                <div class="d-flex justify-content-center">
                                    <sup class="h6 fw-medium pricing-currency mt-3 mb-0 me-1 ${textClass}">Rp</sup>
                                    <h1 class="mb-0 ${textClass}">${formatPrice}</h1>
                                </div>
                            </div>
                            <ul class="ps-3 g-2">
                                <li class="text-truncate-multiline">Kode: ${service.buyer_sku_code}</li>
                                <li class="text-truncate-multiline">${service.product_name}</li>
                                <li class="text-truncate-multiline">Stok: ${service.unlimited_stock ? 'Unlimited' : service.stock}</li>
                                <li class="text-truncate-multiline">Multi Transaksi: ${service.multi ? 'Ya' : 'Tidak'}</li>
                                <li class="text-truncate-multiline">Cut Off: ${service.start_cut_off} s/d ${service.end_cut_off}</li>
                                <li class="text-truncate-multiline">${service.desc}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
                        servicesContainer.append(cardHtml);
                    });
                },
                error: function(err) {
                    console.log(err);
                }
            })
        })

        $(document).ready(function() {
            $('.btn-loading').addClass('d-none')
            $('.btn-save').removeClass('d-none')

            $('#form').on('submit', function(e) {
                e.preventDefault()
                $('.btn-loading').removeClass('d-none')
                $('.btn-save').addClass('d-none')
            })
        })
    </script>
@endpush
