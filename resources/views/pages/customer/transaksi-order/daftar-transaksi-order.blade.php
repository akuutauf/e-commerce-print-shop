@extends('layouts.customer.template-customer')

@section('title')
    <title>Daftar Transaksi & Order | Print-Shop</title>
@endsection

@php
    // fungsi konversi data tipe date ke tanggal
    function dateConversion($date)
    {
        $month = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        $slug = explode('-', $date);
        return $slug[2] . ' ' . $month[(int) $slug[1]] . ' ' . $slug[0];
    }

    function priceConversion($price)
    {
        $formattedPrice = number_format($price, 0, ',', '.');
        return $formattedPrice;
    }

    // fungsi auto repair one word
    function underscore($string)
    {
        // Ubah string menjadi lowercase
        $string = strtolower($string);

        // Ganti spasi dengan underscore
        $string = str_replace(' ', '_', $string);

        return $string;
    }

    function timestampConversion($timestamp)
    {
        // Format tanggal dan waktu asli
        $dateString = $timestamp;

        // Mengkonversi format menjadi waktu yang mudah dibaca
        $data = strtotime($dateString);
        $date = date('d-m-Y', $data);
        $time = date('H:i:s', $data);

        // konversi tanggal
        $month = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        $slug = explode('-', $date);
        $result_date = $slug[0] . ' ' . $month[(int) $slug[1]] . ' ' . $slug[2];

        $result = $result_date . ' ' . '(' . $time . ')';
        return $result;
    }
@endphp

@section('content')
    <!-- cart + summary -->
    <section class="my-5 transaksi-order-responsive">
        <div class="container">

            <div class="row">
                <div class="col-lg-4 col-md-6 col-12">
                    <div>
                        <h4 class="card-title mb-4 alert alert-primary mt-3 text-center">Manajemen Transaksi</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm card-hover">
                        <div class="rounded-2 px-3 py-2 bg-white">
                            <!-- Pills navs -->
                            <ul class="nav nav-pills nav-justified mb-3" id="ex1" role="tablist">
                                <li class="nav-item d-flex" role="presentation">
                                    <a class="nav-link d-flex align-items-center justify-content-center w-100 active nav-side-theme-product bg-secondary text-white"
                                        id="ex1-tab-1" data-mdb-toggle="pill" href="#transaction-product" role="tab"
                                        aria-controls="transaction-product" aria-selected="true">Daftar Transaksi Produk</a>
                                </li>
                                <li class="nav-item d-flex" role="presentation">
                                    <a class="nav-link d-flex align-items-center justify-content-center w-100 nav-side-theme-service bg-secondary text-white"
                                        id="ex1-tab-2" data-mdb-toggle="pill" href="#order-service" role="tab"
                                        aria-controls="order-service" aria-selected="false">Daftar Pesanan Jasa</a>
                                </li>
                            </ul>
                            <!-- Pills navs -->

                            <!-- Pills content -->
                            <div class="tab-content" id="ex1-content">
                                <div class="tab-pane fade show active" id="transaction-product" role="tabpanel"
                                    aria-labelledby="ex1-tab-1">

                                    {{-- Daftar Transaksi Produk --}}
                                    <div class="row">
                                        @if (!$transaction_product_customers->isEmpty())
                                            @foreach ($transaction_product_customers as $item)
                                                <div class="col-lg-12 mb-3">
                                                    <div class="card shadow-sm border card-hover">
                                                        <div class="card-body">
                                                            <div class="row mb-4">
                                                                <div class="col-lg-7 col-md-7 col-7">
                                                                    <span class="fw-bold">Kode Transaksi :
                                                                        {{ $item->id }}</span>
                                                                    <span class="text-success fw-medium">[
                                                                        {{ $item->status_delivery }}
                                                                        ]</span> <br>
                                                                    <span class="text-secondary">Tanggal Transaksi :
                                                                        {{ timestampConversion($item->order_date) }}
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    class="col-lg-5 col-md-5 col-5 d-flex justify-content-end">
                                                                    <div class="pt-3 pb-2">
                                                                        <button type="button"
                                                                            class="btn btn-cancel-checkout mb-2"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#exampleModal{{ $item->id }}">Batalkan
                                                                            Transaksi</button>

                                                                        @if ($item->status_delivery == 'Start Order')
                                                                            <a href="{{ route('transaction.order.show_transaction_product', $item->id) }}"
                                                                                class="btn btn-checkout mb-2">
                                                                                Checkout Transaksi
                                                                            </a>
                                                                        @elseif($item->status_delivery == 'Order Checkouted')
                                                                            <button type="button"
                                                                                class="btn btn-checkout mb-2"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#upload-transaction-product{{ $item->id }}">
                                                                                Upload Bukti Pembayaran
                                                                            </button>

                                                                            @if (!empty($item->snap_token))
                                                                                <button id="pay-button-{{ $item->id }}"
                                                                                    type="button"
                                                                                    class="btn btn-theme mb-2"
                                                                                    title="Lakukan Pembayaran">
                                                                                    <i class="fa-solid fa-credit-card"></i>
                                                                                </button>
                                                                            @endif

                                                                            @if (isset($item) && !empty($item->snap_token))
                                                                                <script type="text/javascript">
                                                                                    document.getElementById('pay-button-{{ $item->id }}').onclick = function() {
                                                                                        // SnapToken acquired from previous step
                                                                                        snap.pay('{{ $item->snap_token }}', {
                                                                                            // Optional
                                                                                            onSuccess: function(result) {
                                                                                                window.location.href = '{{ route('transaction.order.customer.list') }}';
                                                                                                /* You may add your own js here, this is just example */
                                                                                                // document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                                                                                            },
                                                                                            // Optional
                                                                                            onPending: function(result) {
                                                                                                /* You may add your own js here, this is just example */
                                                                                                document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                                                                                            },
                                                                                            // Optional
                                                                                            onError: function(result) {
                                                                                                /* You may add your own js here, this is just example */
                                                                                                document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                                                                                            }
                                                                                        });
                                                                                    };
                                                                                </script>
                                                                            @endif

                                                                            <!-- Modal Upload Bukti Pembayaran Transaksi -->
                                                                            <div class="modal fade"
                                                                                id="upload-transaction-product{{ $item->id }}"
                                                                                tabindex="-1"
                                                                                aria-labelledby="exampleModalLabel"
                                                                                aria-hidden="true">
                                                                                <div class="modal-dialog">
                                                                                    <div class="modal-content">
                                                                                        <div class="modal-header">
                                                                                            <h5 class="modal-title"
                                                                                                id="exampleModalLabel">
                                                                                                Upload Bukti Pembayaran
                                                                                                Transaksi
                                                                                            </h5>
                                                                                            <button type="button"
                                                                                                class="btn-close"
                                                                                                data-bs-dismiss="modal"
                                                                                                aria-label="Close"></button>
                                                                                        </div>
                                                                                        <form
                                                                                            action="{{ route('transaction.order.upload_transaction_order_payment', $item->id) }}"
                                                                                            method="POST" class="m-3"
                                                                                            enctype="multipart/form-data">
                                                                                            @method('put')
                                                                                            @csrf

                                                                                            <div class="modal-body">
                                                                                                <span><b>Total Pembayaran
                                                                                                        :</b>
                                                                                                    Rp.
                                                                                                    {{ priceConversion($item->total_price_transaction_order) }}</span><br>
                                                                                                <span><b>Status Pembayaran
                                                                                                        :</b>
                                                                                                    @if ($item->prof_order_payment == 'empty')
                                                                                                        Belum Dibayar
                                                                                                    @else
                                                                                                        Lunas
                                                                                                    @endif
                                                                                                </span>

                                                                                                <div
                                                                                                    class="form-control mt-4 mb-2">
                                                                                                    <div
                                                                                                        class="form-group mb-3">
                                                                                                        <label
                                                                                                            for="prof_order_payment"
                                                                                                            class="form-label">Upload
                                                                                                            Foto Bukti
                                                                                                            Pembayaran
                                                                                                        </label>
                                                                                                        <input
                                                                                                            id="prof_order_payment"
                                                                                                            name="prof_order_payment"
                                                                                                            class="form-control @error('prof_order_payment') is-invalid @enderror"
                                                                                                            type="file"
                                                                                                            id="formFile"
                                                                                                            required>
                                                                                                    </div>
                                                                                                    @if ($errors->has('prof_order_payment'))
                                                                                                        <div
                                                                                                            class="invalid feedback text-danger mb-3">
                                                                                                            *upload gambar
                                                                                                            kurang dari 10
                                                                                                            Mb
                                                                                                            (jpg/png/webp)
                                                                                                        </div>
                                                                                                    @endif
                                                                                                </div>

                                                                                            </div>
                                                                                            <div class="modal-footer">
                                                                                                <button type="button"
                                                                                                    class="btn btn-checklist"
                                                                                                    data-bs-dismiss="modal">Batal</button>
                                                                                                <button type="submit"
                                                                                                    class="btn btn-hapus">Simpan</button>
                                                                                            </div>
                                                                                        </form>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <!-- Modal Delete Transaksi -->
                                                                    <div class="modal fade"
                                                                        id="exampleModal{{ $item->id }}" tabindex="-1"
                                                                        aria-labelledby="exampleModalLabel"
                                                                        aria-hidden="true">
                                                                        <div class="modal-dialog">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title"
                                                                                        id="exampleModalLabel">
                                                                                        Hapus
                                                                                        transaksi produk?
                                                                                    </h5>
                                                                                    <button type="button"
                                                                                        class="btn-close"
                                                                                        data-bs-dismiss="modal"
                                                                                        aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <i>*note : proses checkout akan
                                                                                        dibatalkan jika
                                                                                        anda
                                                                                        menghapus
                                                                                        transaksi produk.</i><br><br>
                                                                                    <span><b>Tanggal Order :</b>
                                                                                        {{ timestampConversion($item->order_date) }}</span><br>
                                                                                    <span><b>Status :</b>
                                                                                        {{ $item->status_delivery }}</span>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button"
                                                                                        class="btn btn-checklist"
                                                                                        data-bs-dismiss="modal">Batal</button>
                                                                                    <a href="{{ route('transaction.order.destroy', $item->id) }}"
                                                                                        type="button"
                                                                                        class="btn btn-hapus">Hapus</a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="row justify-content-around">
                                                                <div class="col-lg-3 col-md-4 mb-2 mt-2">
                                                                    <p>
                                                                        <span class="fw-medium">Kontak</span> <br>
                                                                        <span class="text-secondary">Nama Pelanggan :
                                                                            {{ auth()->user()->name }}</span> <br>
                                                                        <span class="text-secondary">Nomor HP :
                                                                            {{ auth()->user()->phone }}</span>
                                                                        <br>
                                                                        <span class="text-secondary">Email :
                                                                            {{ auth()->user()->email }}</span>
                                                                    </p>
                                                                </div>
                                                                <div class="col-lg-5 col-md-6 mb-2 mt-2">
                                                                    <p class="text-justify">
                                                                        <span class="fw-medium">Informasi Pesanan</span>
                                                                        <br>
                                                                        <span class="text-secondary">
                                                                            @if ($item->order_address == 'Sistem')
                                                                                Alamat : <i>Belum ditambahkan</i>.
                                                                            @else
                                                                                Alamat : {{ $item->order_address }}.
                                                                            @endif
                                                                        </span> <br>
                                                                        <span class="text-secondary">
                                                                            @if ($item->order_note == '')
                                                                                Catatan : <i>Tidak ditambahkan</i>.
                                                                            @else
                                                                                Catatan : {{ $item->order_note }}.
                                                                            @endif
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                                <div class="col-lg-4 col-md-12 mb-2 mt-2">
                                                                    <span class="fw-medium">Pembayaran</span> <br>
                                                                    <span class="text-secondary">Ongkos Pengiriman :
                                                                        <span class="text-success fw-medium">
                                                                            Rp.
                                                                            @if ($item->delivery_price == 0)
                                                                                {{ priceConversion($deliveryPriceCart) }}
                                                                            @else
                                                                                {{ priceConversion($item->delivery_price) }}
                                                                            @endif
                                                                        </span>
                                                                    </span>
                                                                    <br>
                                                                    <span class="text-secondary">Total Pembayaran :
                                                                        <span class="text-success fw-medium">
                                                                            Rp.
                                                                            @if ($item->total_price_transaction_order == 0)
                                                                                {{ priceConversion($total_price_cart + $deliveryPriceCart) }}
                                                                            @else
                                                                                {{ priceConversion($item->total_price_transaction_order) }}
                                                                            @endif
                                                                        </span>
                                                                    </span>
                                                                    <br>
                                                                    <span class="text-secondary">
                                                                        @if ($item->prof_order_payment == 'empty')
                                                                            Status Pembayaran :
                                                                            <span
                                                                                class="span text-white bg-red-theme px-3 rounded">
                                                                                Belum Dibayar
                                                                            </span>
                                                                        @else
                                                                            Status Pembayaran :
                                                                            <span
                                                                                class="span text-white bg-green-theme px-3 rounded">
                                                                                Sudah Dibayar
                                                                            </span>
                                                                        @endif
                                                                    </span> <br>
                                                                    <span class="text-secondary">
                                                                        @if ($item->order_confirmed == 'No')
                                                                            Status Pesanan :
                                                                            <span
                                                                                class="span text-white bg-red-theme px-3 rounded">
                                                                                Pending
                                                                            </span>
                                                                        @elseif($item->order_confirmed == 'Yes')
                                                                            Status Pesanan :
                                                                            <span
                                                                                class="span text-white bg-green-theme px-3 rounded">
                                                                                Diproses
                                                                            </span>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div>
                                                                <small class="text-secondary">
                                                                    *note : transaksi produk akan tetap
                                                                    ditampilkan sampai Admin melakukan Konfirmasi Pemesanan.
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @elseif($transaction_product_customers->isEmpty())
                                            <p>*Tidak ada riwayat transaksi produk aktif milik pelanggan</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="tab-pane fade mb-2" id="order-service" role="tabpanel">

                                    {{-- Daftar Order Service --}}
                                    <div class="row">
                                        @if (!$order_service_customers->isEmpty())
                                            @foreach ($order_service_customers as $item)
                                                <div class="col-lg-12 mb-3">
                                                    <div class="card shadow-sm border card-hover">
                                                        <div class="card-body">
                                                            <div class="row mb-4">
                                                                <div class="col-lg-7 col-md-7 col-7">
                                                                    <span class="fw-bold">Kode Pesanan :
                                                                        {{ $item->id }}</span>
                                                                    <span class="text-success fw-medium">[
                                                                        {{ $item->status_delivery }}
                                                                        ]</span> <br>
                                                                    <span class="text-secondary">Tanggal Pesanan :
                                                                        {{ timestampConversion($item->order_date) }}
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    class="col-lg-5 col-md-5 col-5 d-flex justify-content-end">
                                                                    <div class="pt-3 pb-2">

                                                                        <button type="button"
                                                                            class="btn btn-cancel-checkout mb-2"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#exampleModal{{ $item->id }}">Batalkan
                                                                            Pesanan</button>

                                                                        @if ($item->status_delivery == 'Start Order')
                                                                            <a href="{{ route('transaction.order.show_order_service', $item->id) }}"
                                                                                class="btn btn-checkout mb-2">
                                                                                Checkout Pesanan
                                                                            </a>
                                                                        @elseif($item->status_delivery == 'Order Checkouted')
                                                                            <button type="button"
                                                                                class="btn btn-checkout mb-2"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#upload-order-service{{ $item->id }}">
                                                                                Upload Bukti Pembayaran
                                                                            </button>

                                                                            @if (!empty($item->snap_token))
                                                                                <button
                                                                                    id="pay-button-service-{{ $item->id }}"
                                                                                    type="button"
                                                                                    class="btn btn-theme mb-2"
                                                                                    title="Lakukan Pembayaran">
                                                                                    <i class="fa-solid fa-credit-card"></i>
                                                                                </button>
                                                                            @endif

                                                                            @if (isset($item) && !empty($item->snap_token))
                                                                                <script type="text/javascript">
                                                                                    document.getElementById('pay-button-service-{{ $item->id }}').onclick = function() {
                                                                                        // SnapToken acquired from previous step
                                                                                        snap.pay('{{ $item->snap_token }}', {
                                                                                            // Optional
                                                                                            onSuccess: function(result) {
                                                                                                window.location.href = '{{ route('transaction.order.customer.list') }}';
                                                                                                /* You may add your own js here, this is just example */
                                                                                                // document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                                                                                            },
                                                                                            // Optional
                                                                                            onPending: function(result) {
                                                                                                /* You may add your own js here, this is just example */
                                                                                                document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                                                                                            },
                                                                                            // Optional
                                                                                            onError: function(result) {
                                                                                                /* You may add your own js here, this is just example */
                                                                                                document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                                                                                            }
                                                                                        });
                                                                                    };
                                                                                </script>
                                                                            @endif

                                                                            <!-- Modal Upload Bukti Pembayaran Order -->
                                                                            <div class="modal fade"
                                                                                id="upload-order-service{{ $item->id }}"
                                                                                tabindex="-1"
                                                                                aria-labelledby="exampleModalLabel"
                                                                                aria-hidden="true">
                                                                                <div class="modal-dialog">
                                                                                    <div class="modal-content">
                                                                                        <div class="modal-header">
                                                                                            <h5 class="modal-title"
                                                                                                id="exampleModalLabel">
                                                                                                Upload Bukti Pembayaran
                                                                                                Order
                                                                                            </h5>
                                                                                            <button type="button"
                                                                                                class="btn-close"
                                                                                                data-bs-dismiss="modal"
                                                                                                aria-label="Close"></button>
                                                                                        </div>
                                                                                        <form
                                                                                            action="{{ route('transaction.order.upload_transaction_order_payment', $item->id) }}"
                                                                                            method="POST" class="m-3"
                                                                                            enctype="multipart/form-data">
                                                                                            @method('put')
                                                                                            @csrf

                                                                                            <div class="modal-body">
                                                                                                <span><b>Total Pembayaran
                                                                                                        :</b>
                                                                                                    Rp.
                                                                                                    {{ priceConversion($item->total_price_transaction_order) }}</span><br>
                                                                                                <span><b>Status Pembayaran
                                                                                                        :</b>
                                                                                                    @if ($item->prof_order_payment == 'empty')
                                                                                                        Belum Dibayar
                                                                                                    @else
                                                                                                        Lunas
                                                                                                    @endif
                                                                                                </span>

                                                                                                <div
                                                                                                    class="form-control mt-4 mb-2">
                                                                                                    <div
                                                                                                        class="form-group mb-3">
                                                                                                        <label
                                                                                                            for="prof_order_payment"
                                                                                                            class="form-label">Upload
                                                                                                            Foto Bukti
                                                                                                            Pembayaran
                                                                                                        </label>
                                                                                                        <input
                                                                                                            id="prof_order_payment"
                                                                                                            name="prof_order_payment"
                                                                                                            class="form-control @error('prof_order_payment') is-invalid @enderror"
                                                                                                            type="file"
                                                                                                            id="formFile"
                                                                                                            required>
                                                                                                    </div>
                                                                                                    @if ($errors->has('prof_order_payment'))
                                                                                                        <div
                                                                                                            class="invalid feedback text-danger mb-3">
                                                                                                            *upload gambar
                                                                                                            kurang dari 10
                                                                                                            Mb
                                                                                                            (jpg/png/webp)
                                                                                                        </div>
                                                                                                    @endif
                                                                                                </div>

                                                                                            </div>
                                                                                            <div class="modal-footer">
                                                                                                <button type="button"
                                                                                                    class="btn btn-checklist"
                                                                                                    data-bs-dismiss="modal">Batal</button>
                                                                                                <button type="submit"
                                                                                                    class="btn btn-hapus">Simpan</button>
                                                                                            </div>
                                                                                        </form>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <!-- Modal Delete Transaksi -->
                                                                    <div class="modal fade"
                                                                        id="exampleModal{{ $item->id }}"
                                                                        tabindex="-1" aria-labelledby="exampleModalLabel"
                                                                        aria-hidden="true">
                                                                        <div class="modal-dialog">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title"
                                                                                        id="exampleModalLabel">
                                                                                        Hapus
                                                                                        pesanan jasa?
                                                                                    </h5>
                                                                                    <button type="button"
                                                                                        class="btn-close"
                                                                                        data-bs-dismiss="modal"
                                                                                        aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <i>*note : proses checkout akan
                                                                                        dibatalkan jika
                                                                                        anda
                                                                                        menghapus
                                                                                        pesanan jasa.</i><br><br>
                                                                                    <span><b>Tanggal Order :</b>
                                                                                        {{ timestampConversion($item->order_date) }}</span><br>
                                                                                    <span><b>Status :</b>
                                                                                        {{ $item->status_delivery }}</span>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button"
                                                                                        class="btn btn-checklist"
                                                                                        data-bs-dismiss="modal">Batal</button>
                                                                                    <a href="{{ route('transaction.order.destroy', $item->id) }}"
                                                                                        type="button"
                                                                                        class="btn btn-hapus">Hapus</a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="row justify-content-around">
                                                                <div class="col-lg-3 col-md-4 mb-2 mt-2">
                                                                    <p>
                                                                        <span class="fw-medium">Kontak</span> <br>
                                                                        <span class="text-secondary">Nama Pelanggan :
                                                                            {{ auth()->user()->name }}</span> <br>
                                                                        <span class="text-secondary">Nomor HP :
                                                                            {{ auth()->user()->phone }}</span>
                                                                        <br>
                                                                        <span class="text-secondary">Email :
                                                                            {{ auth()->user()->email }}</span>
                                                                    </p>
                                                                </div>
                                                                <div class="col-lg-5 col-md-6 mb-2 mt-2">
                                                                    <p class="text-justify">
                                                                        <span class="fw-medium">Informasi Pesanan</span>
                                                                        <br>
                                                                        <span class="text-secondary">
                                                                            @if ($item->order_address == 'Sistem')
                                                                                Alamat : <i>Belum ditambahkan</i>.
                                                                            @else
                                                                                Alamat : {{ $item->order_address }}.
                                                                            @endif
                                                                        </span> <br>
                                                                        <span class="text-secondary">
                                                                            @if ($item->order_note == '')
                                                                                Catatan : <i>Tidak ditambahkan</i>.
                                                                            @else
                                                                                Catatan : {{ $item->order_note }}.
                                                                            @endif
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                                <div class="col-lg-4 col-md-12 mb-2 mt-2">
                                                                    <span class="fw-medium">Pembayaran</span> <br>
                                                                    <span class="text-secondary">Ongkos Pengiriman :
                                                                        <span class="text-success fw-medium">
                                                                            Rp.
                                                                            @if ($item->delivery_price == 0)
                                                                                {{ priceConversion($deliveryPriceOrder) }}
                                                                            @else
                                                                                {{ priceConversion($item->delivery_price) }}
                                                                            @endif
                                                                        </span>
                                                                        <br>
                                                                        <span class="text-secondary">
                                                                            Biaya Jasa =
                                                                            <span class="text-success fw-medium">
                                                                                Rp. {{ priceConversion($servicePrice) }}
                                                                            </span>
                                                                        </span>
                                                                        <br>
                                                                        <span class="text-secondary">Total Pembayaran :
                                                                            <span class="text-success fw-medium">
                                                                                Rp.
                                                                                @if ($item->total_price_transaction_order == 0)
                                                                                    {{ priceConversion($total_price_order + $deliveryPriceOrder + $servicePrice) }}
                                                                                @else
                                                                                    {{ priceConversion($item->total_price_transaction_order) }}
                                                                                @endif
                                                                            </span>
                                                                        </span>
                                                                        <br>
                                                                        <span class="text-secondary">
                                                                            @if ($item->prof_order_payment == 'empty')
                                                                                Status Pembayaran :
                                                                                <span
                                                                                    class="span text-white bg-red-theme px-3 rounded">
                                                                                    Belum Dibayar
                                                                                </span>
                                                                            @else
                                                                                Status Pembayaran :
                                                                                <span
                                                                                    class="span text-white bg-green-theme px-3 rounded">
                                                                                    Sudah Dibayar
                                                                                </span>
                                                                            @endif
                                                                        </span> <br>
                                                                        <span class="text-secondary">
                                                                            @if ($item->order_confirmed == 'No')
                                                                                Status Pesanan :
                                                                                <span
                                                                                    class="span text-white bg-red-theme px-3 rounded">
                                                                                    Pending
                                                                                </span>
                                                                            @elseif($item->order_confirmed == 'Yes')
                                                                                Status Pesanan :
                                                                                <span
                                                                                    class="span text-white bg-green-theme px-3 rounded">
                                                                                    Diproses
                                                                                </span>
                                                                            @endif
                                                                        </span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div>
                                                                <small class="text-secondary">
                                                                    *note : order jasa akan tetap
                                                                    ditampilkan sampai Admin melakukan Konfirmasi Pemesanan.
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @elseif($order_service_customers->isEmpty())
                                            <p>*Tidak ada riwayat pesanan jasa aktif milik pelanggan</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- Pills content -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@section('script')
    <!-- TODO: Remove ".sandbox" from script src URL for production environment. Also input your client key in "data-client-key" -->
    <script src="{{ env('MIDTRANS_URL_ACTIVE') }}" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
@endsection
