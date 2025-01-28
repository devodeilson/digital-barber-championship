@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Enviar Vídeo - {{ $championship->title }}</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('videos.store', $championship) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Título do Vídeo</label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Categoria</label>
                            <select class="form-select @error('category_id') is-invalid @enderror"
                                    id="category_id"
                                    name="category_id"
                                    required>
                                <option value="">Selecione uma categoria</option>
                                @foreach($championship->categories as $category)
                                    <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      required>{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="video" class="form-label">Vídeo</label>
                            <input type="file"
                                   class="form-control @error('video') is-invalid @enderror"
                                   id="video"
                                   name="video"
                                   accept="video/mp4"
                                   required>
                            <div class="form-text">
                                Formato aceito: MP4. Tamanho máximo: 100MB. Duração: 10-30 segundos.
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6>Dicas para um bom vídeo:</h6>
                            <ul class="mb-0">
                                <li>Grave em um ambiente bem iluminado</li>
                                <li>Use um fundo neutro e limpo</li>
                                <li>Mantenha a câmera estável</li>
                                <li>Fale de forma clara e objetiva</li>
                                <li>Mostre detalhes do seu trabalho</li>
                            </ul>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-cloud-upload-alt"></i> Enviar Vídeo
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
    // Validação do tamanho e duração do vídeo
    document.getElementById('video').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file.size > 100 * 1024 * 1024) {
            alert('O arquivo é muito grande. O tamanho máximo é 100MB.');
            this.value = '';
        }
    });
</script>
@endpush
@endsection
