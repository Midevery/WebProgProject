@extends('layouts.auth')

@section('title', 'Sign In - Kisora Shop')

@section('content')
    <h1 class="auth-title">Sign In</h1>

    <form method="POST" action="{{ route('signin.post') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="login">Username / Email</label>
            <input 
                type="text" 
                id="login" 
                name="login" 
                class="form-control form-input @error('login') is-invalid @enderror" 
                value="{{ old('login') }}" 
                placeholder="Enter your username or email"
                required
                autofocus
            >
            @error('login')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <div class="password-input-wrapper" style="position: relative;">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control form-input @error('password') is-invalid @enderror" 
                    placeholder="Enter your password"
                    required
                    style="padding-right: 45px;"
                >
                <button type="button" class="password-toggle-btn" onclick="togglePassword('password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 5px;">
                    <i class="bi bi-eye" id="password-toggle-icon"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Keep Sign In</label>
        </div>

        <button type="submit" class="btn auth-button w-100">Sign In</button>

        <div class="auth-link mt-3">
            No Account? <a href="{{ route('signup') }}">Sign Up</a>
        </div>
    </form>
    
    @push('scripts')
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-toggle-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
    @endpush
@endsection

