@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Configurações da Conta</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Avatar -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="text-center">
                                    @if($user->avatar)
                                        <img src="{{ Storage::url($user->avatar) }}"
                                             class="rounded-circle img-thumbnail"
                                             alt="Avatar" style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 150px; height: 150px;">
                                            <i class="fas fa-user fa-4x"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="avatar" class="form-label">Avatar</label>
                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                           id="avatar" name="avatar">
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($user->avatar)
                                    <div class="mb-3">
                                        <a href="{{ route('admin.settings.delete-avatar') }}"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Tem certeza que deseja remover seu avatar?')">
                                            <i class="fas fa-trash me-2"></i>Remover Avatar
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Informações Pessoais -->
                        <h5 class="mb-4">Informações Pessoais</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country" class="form-label">País</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror"
                                           id="country" name="country" value="{{ old('country', $user->country) }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Alterar Senha -->
                        <h5 class="mb-4">Alterar Senha</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Senha Atual</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                           id="current_password" name="current_password">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                           id="new_password" name="new_password">
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control"
                                           id="new_password_confirmation" name="new_password_confirmation">
                                </div>
                            </div>
                        </div>

                        <!-- Preferências -->
                        <h5 class="mb-4">Preferências</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="theme" class="form-label">Tema</label>
                                    <select class="form-select @error('theme') is-invalid @enderror"
                                            id="theme" name="theme">
                                        <option value="light" {{ $user->preferences?->theme === 'light' ? 'selected' : '' }}>
                                            Claro
                                        </option>
                                        <option value="dark" {{ $user->preferences?->theme === 'dark' ? 'selected' : '' }}>
                                            Escuro
                                        </option>
                                    </select>
                                    @error('theme')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="language" class="form-label">Idioma</label>
                                    <select class="form-select @error('language') is-invalid @enderror"
                                            id="language" name="language">
                                        <option value="pt_BR" {{ $user->preferences?->language === 'pt_BR' ? 'selected' : '' }}>
                                            Português (Brasil)
                                        </option>
                                        <option value="en" {{ $user->preferences?->language === 'en' ? 'selected' : '' }}>
                                            English
                                        </option>
                                    </select>
                                    @error('language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label d-block">Notificações</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               id="notifications_enabled" name="notifications_enabled"
                                               {{ $user->preferences?->notifications_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notifications_enabled">
                                            Ativar notificações
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
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
@endsection
