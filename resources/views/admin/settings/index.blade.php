@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Configurações do Sistema</h5>
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

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')

                        <div class="row">
                            <!-- Informações Básicas -->
                            <div class="col-md-12 mb-4">
                                <h6 class="text-primary">Informações Básicas</h6>
                                <hr>
                            </div>

                            <!-- Nome do Site -->
                            <div class="col-md-6 mb-3">
                                <label for="site_name" class="form-label">Nome do Site</label>
                                <input type="text"
                                       class="form-control @error('site_name') is-invalid @enderror"
                                       id="site_name"
                                       name="site_name"
                                       value="{{ old('site_name', $settings['site_name']) }}"
                                       required>
                                @error('site_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email de Contato -->
                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label">Email de Contato</label>
                                <input type="email"
                                       class="form-control @error('contact_email') is-invalid @enderror"
                                       id="contact_email"
                                       name="contact_email"
                                       value="{{ old('contact_email', $settings['contact_email']) }}"
                                       required>
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Descrição do Site -->
                            <div class="col-md-12 mb-3">
                                <label for="site_description" class="form-label">Descrição do Site</label>
                                <textarea class="form-control @error('site_description') is-invalid @enderror"
                                          id="site_description"
                                          name="site_description"
                                          rows="3"
                                          required>{{ old('site_description', $settings['site_description']) }}</textarea>
                                @error('site_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Redes Sociais -->
                            <div class="col-md-12 mb-4">
                                <h6 class="text-primary">Redes Sociais</h6>
                                <hr>
                            </div>

                            <!-- Facebook -->
                            <div class="col-md-4 mb-3">
                                <label for="social_media_facebook" class="form-label">Facebook</label>
                                <input type="url"
                                       class="form-control @error('social_media.facebook') is-invalid @enderror"
                                       id="social_media_facebook"
                                       name="social_media[facebook]"
                                       value="{{ old('social_media.facebook', $settings['social_media']['facebook']) }}">
                                @error('social_media.facebook')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Instagram -->
                            <div class="col-md-4 mb-3">
                                <label for="social_media_instagram" class="form-label">Instagram</label>
                                <input type="url"
                                       class="form-control @error('social_media.instagram') is-invalid @enderror"
                                       id="social_media_instagram"
                                       name="social_media[instagram]"
                                       value="{{ old('social_media.instagram', $settings['social_media']['instagram']) }}">
                                @error('social_media.instagram')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Twitter -->
                            <div class="col-md-4 mb-3">
                                <label for="social_media_twitter" class="form-label">Twitter</label>
                                <input type="url"
                                       class="form-control @error('social_media.twitter') is-invalid @enderror"
                                       id="social_media_twitter"
                                       name="social_media[twitter]"
                                       value="{{ old('social_media.twitter', $settings['social_media']['twitter']) }}">
                                @error('social_media.twitter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Imagens -->
                            <div class="col-md-12 mb-4">
                                <h6 class="text-primary">Imagens</h6>
                                <hr>
                            </div>

                            <!-- Logo -->
                            <div class="col-md-6 mb-3">
                                <label for="logo" class="form-label">Logo</label>
                                <input type="file"
                                       class="form-control @error('logo') is-invalid @enderror"
                                       id="logo"
                                       name="logo"
                                       accept="image/*">
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Favicon -->
                            <div class="col-md-6 mb-3">
                                <label for="favicon" class="form-label">Favicon</label>
                                <input type="file"
                                       class="form-control @error('favicon') is-invalid @enderror"
                                       id="favicon"
                                       name="favicon"
                                       accept="image/x-icon,image/png">
                                @error('favicon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
