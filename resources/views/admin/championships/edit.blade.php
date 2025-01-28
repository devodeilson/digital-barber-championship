@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Editar Liga</h5>
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

                    <form action="{{ route('admin.championships.update', $championship) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Imagem Atual -->
                            @if($championship->image)
                                <div class="col-md-12 mb-4 text-center">
                                    <img src="{{ Storage::url($championship->image) }}"
                                         alt="Imagem da Liga"
                                         class="img-fluid rounded"
                                         style="max-height: 200px;">
                                </div>
                            @endif

                            <!-- Título -->
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Título</label>
                                <input type="text"
                                       class="form-control @error('title') is-invalid @enderror"
                                       id="title"
                                       name="title"
                                       value="{{ old('title', $championship->title) }}"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Descrição -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="3"
                                          required>{{ old('description', $championship->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Data Início -->
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Data Início</label>
                                <input type="date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       id="start_date"
                                       name="start_date"
                                       value="{{ old('start_date', $championship->start_date->format('Y-m-d')) }}"
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Data Fim -->
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Data Fim</label>
                                <input type="date"
                                       class="form-control @error('end_date') is-invalid @enderror"
                                       id="end_date"
                                       name="end_date"
                                       value="{{ old('end_date', $championship->end_date->format('Y-m-d')) }}"
                                       required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Local -->
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Local</label>
                                <input type="text"
                                       class="form-control @error('location') is-invalid @enderror"
                                       id="location"
                                       name="location"
                                       value="{{ old('location', $championship->location) }}"
                                       required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Máximo de Participantes -->
                            <div class="col-md-6 mb-3">
                                <label for="max_participants" class="form-label">Máximo de Participantes</label>
                                <input type="number"
                                       class="form-control @error('max_participants') is-invalid @enderror"
                                       id="max_participants"
                                       name="max_participants"
                                       value="{{ old('max_participants', $championship->max_participants) }}"
                                       required>
                                @error('max_participants')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status"
                                        required>
                                    <option value="draft" {{ old('status', $championship->status) === 'draft' ? 'selected' : '' }}>Rascunho</option>
                                    <option value="active" {{ old('status', $championship->status) === 'active' ? 'selected' : '' }}>Ativo</option>
                                    <option value="finished" {{ old('status', $championship->status) === 'finished' ? 'selected' : '' }}>Finalizado</option>
                                    <option value="cancelled" {{ old('status', $championship->status) === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nova Imagem -->
                            <div class="col-md-12 mb-3">
                                <label for="image" class="form-label">Nova Imagem</label>
                                <input type="file"
                                       class="form-control @error('image') is-invalid @enderror"
                                       id="image"
                                       name="image"
                                       accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Classifica para Copa -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="qualifies_for_cup"
                                           name="qualifies_for_cup"
                                           value="1"
                                           {{ old('qualifies_for_cup', $championship->qualifies_for_cup) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="qualifies_for_cup">
                                        Classifica para a Copa Final
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.championships.index') }}" class="btn btn-secondary">Cancelar</a>
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
