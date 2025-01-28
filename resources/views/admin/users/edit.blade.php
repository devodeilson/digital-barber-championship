@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Editar Perfil</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Avatar Preview -->
                            <div class="col-md-12 mb-4 text-center">
                                <div class="avatar-wrapper">
                                    <img src="{{ $user->avatar_url }}"
                                         class="rounded-circle preview-avatar"
                                         width="150"
                                         height="150"
                                         alt="Avatar">
                                </div>
                            </div>

                            <!-- Avatar Upload -->
                            <div class="col-md-12 mb-3">
                                <label for="avatar" class="form-label">Foto de Perfil</label>
                                <input type="file"
                                       class="form-control @error('avatar') is-invalid @enderror"
                                       id="avatar"
                                       name="avatar"
                                       accept="image/*">
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nome -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nome</label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Telefone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- País -->
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">País</label>
                                <input type="text"
                                       class="form-control @error('country') is-invalid @enderror"
                                       id="country"
                                       name="country"
                                       value="{{ old('country', $user->country) }}">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nova Senha -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Nova Senha</label>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirmar Senha -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('avatar').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('.preview-avatar').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
@endpush
@endsection
