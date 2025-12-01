@extends('layouts.auth')

@section('title', 'Sign Up - Kisora Shop')

@section('content')
    <h1 class="auth-title">Sign Up</h1>

    <form method="POST" action="{{ route('signup.post') }}" enctype="multipart/form-data">
        @csrf
        
        <!-- Profile Image Upload -->
        <div class="mb-3 text-center">
            <label class="form-label d-block">Profile Picture (Optional)</label>
            <div class="position-relative d-inline-block">
                <img id="profilePreview" src="https://via.placeholder.com/150x150?text=No+Image" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 2px solid #ddd; cursor: pointer;" alt="Profile Preview" onclick="document.getElementById('profile_image').click()">
                <label for="profile_image" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 35px; height: 35px; cursor: pointer; padding: 0; display: flex; align-items: center; justify-content: center;" title="Upload Photo">
                    <i class="bi bi-camera" style="font-size: 14px;"></i>
                </label>
            </div>
            <input type="file" id="profile_image" name="profile_image" accept="image/*" class="d-none" onchange="previewImage(this)">
            <small class="text-muted d-block mt-2">Click image to upload (Max 2MB)</small>
            @error('profile_image')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                class="form-control form-input @error('username') is-invalid @enderror" 
                value="{{ old('username') }}" 
                placeholder="Enter your username"
                required
                autofocus
            >
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="name">Name</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                class="form-control form-input @error('name') is-invalid @enderror" 
                value="{{ old('name') }}" 
                placeholder="Enter your full name"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-control form-input @error('email') is-invalid @enderror" 
                value="{{ old('email') }}" 
                placeholder="Enter your email"
                required
            >
            @error('email')
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

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <div class="password-input-wrapper" style="position: relative;">
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="form-control form-input" 
                    placeholder="Confirm your password"
                    required
                    style="padding-right: 45px;"
                >
                <button type="button" class="password-toggle-btn" onclick="togglePassword('password_confirmation')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 5px;">
                    <i class="bi bi-eye" id="password_confirmation-toggle-icon"></i>
                </button>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="phone">Phone Number</label>
            <input 
                type="tel" 
                id="phone" 
                name="phone" 
                class="form-control form-input @error('phone') is-invalid @enderror" 
                value="{{ old('phone') }}" 
                placeholder="Enter your phone number"
            >
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="address">Address</label>
            <textarea 
                id="address" 
                name="address" 
                class="form-control form-input @error('address') is-invalid @enderror" 
                rows="3"
                placeholder="Enter your address"
            >{{ old('address') }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="date_of_birth">Date of Birth</label>
            <div class="date-input-wrapper">
                <input 
                    type="date" 
                    id="date_of_birth" 
                    name="date_of_birth" 
                    class="form-control form-input @error('date_of_birth') is-invalid @enderror" 
                    value="{{ old('date_of_birth') }}"
                    placeholder="DD/MM/YYYY"
                >
                <span class="calendar-icon"><i class="bi bi-calendar3"></i></span>
            </div>
            @error('date_of_birth')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Gender</label>
            <div class="gender-group">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        id="gender_male" 
                        name="gender" 
                        value="Male"
                        {{ old('gender') === 'Male' ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="gender_male">Male</label>
                </div>
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        id="gender_female" 
                        name="gender" 
                        value="Female"
                        {{ old('gender') === 'Female' ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="gender_female">Female</label>
                </div>
            </div>
            @error('gender')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn auth-button w-100">Sign Up</button>

        <div class="auth-link mt-3">
            Have Account? <a href="{{ route('signin') }}">Sign In</a>
        </div>
    </form>
    
    @push('scripts')
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
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

