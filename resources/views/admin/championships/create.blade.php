@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Criar Novo Campeonato</h5>
                </div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.championships.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Título -->
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Título</label>
                                <input type="text"
                                       class="form-control @error('title') is-invalid @enderror"
                                       id="title"
                                       name="title"
                                       value="{{ old('title') }}"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tipo de Campeonato -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Tipo de Campeonato</label>
                                <select class="form-select @error('type') is-invalid @enderror"
                                        id="type"
                                        name="type"
                                        required>
                                    <option value="league">Liga</option>
                                    <option value="normal">Campeonato Normal</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Modalidade -->
                            <div class="col-md-6 mb-3">
                                <label for="modality" class="form-label">Modalidade</label>
                                <select class="form-select @error('modality') is-invalid @enderror"
                                        id="modality"
                                        name="modality"
                                        required>
                                    <option value="presential">Presencial</option>
                                    <option value="digital">Digital</option>
                                </select>
                                @error('modality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Campos para Campeonato Presencial -->
                            <div id="presential-fields">
                                <div class="col-md-12 mb-3">
                                    <label for="address" class="form-label">Endereço Completo</label>
                                    <input type="text"
                                           class="form-control @error('address') is-invalid @enderror"
                                           id="address"
                                           name="address"
                                           value="{{ old('address') }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">Cidade</label>
                                        <input type="text"
                                               class="form-control @error('city') is-invalid @enderror"
                                               id="city"
                                               name="city"
                                               value="{{ old('city') }}">
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="state" class="form-label">Estado</label>
                                        <input type="text"
                                               class="form-control @error('state') is-invalid @enderror"
                                               id="state"
                                               name="state"
                                               value="{{ old('state') }}">
                                        @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="venue" class="form-label">Local do Evento</label>
                                    <input type="text"
                                           class="form-control @error('venue') is-invalid @enderror"
                                           id="venue"
                                           name="venue"
                                           value="{{ old('venue') }}"
                                           placeholder="Ex: Centro de Convenções, Hotel, etc">
                                    @error('venue')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Campos para Campeonato Digital -->
                            <div id="digital-fields" style="display: none;">
                                <div class="col-md-12 mb-3">
                                    <label for="submission_instructions" class="form-label">Instruções para Envio</label>
                                    <textarea class="form-control @error('submission_instructions') is-invalid @enderror"
                                              id="submission_instructions"
                                              name="submission_instructions"
                                              rows="3">{{ old('submission_instructions') }}</textarea>
                                    @error('submission_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="allowed_formats" class="form-label">Formatos Permitidos</label>
                                    <input type="text"
                                           class="form-control @error('allowed_formats') is-invalid @enderror"
                                           id="allowed_formats"
                                           name="allowed_formats"
                                           value="{{ old('allowed_formats') }}"
                                           placeholder="Ex: PDF, DOC, JPG, etc">
                                    @error('allowed_formats')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Mês (apenas para Liga) -->
                            <div class="col-md-3 mb-3 league-field">
                                <label for="month" class="form-label">Mês</label>
                                <select class="form-select @error('month') is-invalid @enderror"
                                        id="month"
                                        name="month">
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ old('month') == $i ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ano (apenas para Liga) -->
                            <div class="col-md-3 mb-3 league-field">
                                <label for="year" class="form-label">Ano</label>
                                <select class="form-select @error('year') is-invalid @enderror"
                                        id="year"
                                        name="year">
                                    @for($i = date('Y'); $i <= date('Y') + 1; $i++)
                                        <option value="{{ $i }}" {{ old('year') == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                @error('year')
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
                                       value="{{ old('start_date') }}"
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">Hora Início</label>
                                <input type="time"
                                       class="form-control @error('start_time') is-invalid @enderror"
                                       id="start_time"
                                       name="start_time"
                                       value="{{ old('start_time') }}"
                                       required>
                                @error('start_time')
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
                                       value="{{ old('end_date') }}"
                                       required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label">Hora Fim</label>
                                <input type="time"
                                       class="form-control @error('end_time') is-invalid @enderror"
                                       id="end_time"
                                       name="end_time"
                                       value="{{ old('end_time') }}"
                                       required>
                                @error('end_time')
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
                                          required>{{ old('description') }}</textarea>
                                @error('description')
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
                                       value="{{ old('location') }}"
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
                                       value="{{ old('max_participants') }}"
                                       required>
                                @error('max_participants')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Imagem -->
                            <div class="col-md-12 mb-3">
                                <label for="image" class="form-label">Imagem</label>
                                <input type="file"
                                       class="form-control @error('image') is-invalid @enderror"
                                       id="image"
                                       name="image"
                                       accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.championships.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Criar Campeonato
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
document.addEventListener('DOMContentLoaded', function() {
    const modalitySelect = document.getElementById('modality');
    const presentialFields = document.getElementById('presential-fields');
    const digitalFields = document.getElementById('digital-fields');

    function toggleFields() {
        const isPresential = modalitySelect.value === 'presential';
        presentialFields.style.display = isPresential ? 'block' : 'none';
        digitalFields.style.display = isPresential ? 'none' : 'block';

        // Toggle required attributes
        const presentialInputs = presentialFields.querySelectorAll('input');
        const digitalInputs = digitalFields.querySelectorAll('input, textarea');

        presentialInputs.forEach(input => {
            input.required = isPresential;
        });

        digitalInputs.forEach(input => {
            input.required = !isPresential;
        });
    }

    modalitySelect.addEventListener('change', toggleFields);
    toggleFields(); // Execute on page load
});
</script>
@endpush
@endsection
