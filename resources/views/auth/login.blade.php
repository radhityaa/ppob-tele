@extends('layouts.auth.app')

@section('title', 'Login')

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
                <a href="#" class="app-brand-link gap-2">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}" width="50" height="50">
                    <span class="app-brand-text demo text-body fw-bolder text-uppercase">Ayasya Tech</span>
                </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-2">Selamat Datang Di Web Report</h4>
            <p class="mb-4">Silahkan Login telebih dahulu untuk melanjutkan</p>

            <form id="formAuthentication" class="mb-3" action="{{ route('login.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor HP</label>
                    <input type="number" class="form-control" id="phone" name="phone"
                        placeholder="Nomor HP terdaftar di telegram" value="{{ old('phone') }}" autofocus />
                </div>
                <div class="mb-3">
                    <label for="user_tel_id" class="form-label">User ID</label>
                    <input type="number" class="form-control" id="user_tel_id" name="user_tel_id"
                        placeholder="User ID Telegram" autofocus />
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                        <label class="form-check-label" for="remember"> Remember Me </label>
                    </div>
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
                </div>
            </form>
        </div>
    </div>
@endsection
