@extends('layouts.app')

@section('title', 'Customer Profile - Kisora Shop')

@section('content')
<div class="container my-4" style="background-color: var(--kisora-light-blue); padding: 2rem; border-radius: 10px;">
    <a href="{{ route('home') }}" class="btn btn-outline-primary mb-3">← Back</a>
    
    <div class="row">
        <div class="col-md-3 text-center mb-4">
            <div class="position-relative d-inline-block">
                <img id="profilePreview" src="{{ $user->profile_image ? asset($user->profile_image) : 'https://picsum.photos/200/200?random=' . $user->id }}" class="rounded-circle img-fluid" style="width: 200px; height: 200px; object-fit: cover;" alt="Profile">
                <label for="profile_image" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" title="Upload Photo">
                    <i class="bi bi-camera"></i>
                </label>
            </div>
            <div class="mt-3">
                <small class="text-muted">Click camera icon to upload</small>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">My Profile</h3>
                    
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" class="d-none" onchange="previewImage(this)">
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="{{ $user->username }}" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                            <small class="text-muted">This will be used for login and notifications.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+62 812-3456-7890">
                            <small class="text-muted">Leave blank if you prefer not to share.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="male" value="Male" {{ old('gender', $user->gender) === 'Male' ? 'checked' : '' }}>
                                <label class="form-check-label" for="male">Male</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="female" value="Female" {{ old('gender', $user->gender) === 'Female' ? 'checked' : '' }}>
                                <label class="form-check-label" for="female">Female</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Date of Birth</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div style="position: relative;">
                            <input type="password" id="current_password" class="form-control" name="current_password" required style="padding-right: 45px;">
                            <button type="button" onclick="togglePassword('current_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 5px;">
                                <i class="bi bi-eye" id="current_password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div style="position: relative;">
                            <input type="password" id="new_password" class="form-control" name="password" required style="padding-right: 45px;">
                            <button type="button" onclick="togglePassword('new_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 5px;">
                                <i class="bi bi-eye" id="new_password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password_confirmation_modal" class="form-control" name="password_confirmation" required style="padding-right: 45px;">
                            <button type="button" onclick="togglePassword('password_confirmation_modal')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; padding: 5px;">
                                <i class="bi bi-eye" id="password_confirmation_modal-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


