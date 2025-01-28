@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Conteúdos</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-filter me-2"></i>Filtrar por Status
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('admin.contents.index') }}">Todos</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.contents.index', ['status' => 'pending']) }}">Pendentes</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.contents.index', ['status' => 'approved']) }}">Aprovados</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.contents.index', ['status' => 'rejected']) }}">Rejeitados</a></li>
            </ul>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form action="{{ route('admin.contents.index') }}" method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control"
                               placeholder="Buscar por título" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Imagem</th>
                            <th>Título</th>
                            <th>Usuário</th>
                            <th>Campeonato</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contents as $content)
                            <tr>
                                <td>
                                    @if($content->image)
                                        <img src="{{ Storage::url($content->image) }}"
                                             alt="Thumbnail" class="img-thumbnail"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $content->title }}</td>
                                <td>{{ $content->user->name }}</td>
                                <td>{{ $content->championship->name }}</td>
                                <td>
                                    <span class="badge bg-{{
                                        $content->status === 'approved' ? 'success' :
                                        ($content->status === 'rejected' ? 'danger' : 'warning')
                                    }}">
                                        {{ ucfirst($content->status) }}
                                    </span>
                                </td>
                                <td>{{ $content->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.contents.show', $content) }}"
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($content->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success"
                                                    onclick="approveContent({{ $content->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="showRejectModal({{ $content->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDelete({{ $content->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="approve-form-{{ $content->id }}"
                                          action="{{ route('admin.contents.approve', $content) }}"
                                          method="POST" class="d-none">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <form id="delete-form-{{ $content->id }}"
                                          action="{{ route('admin.contents.destroy', $content) }}"
                                          method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Nenhum conteúdo encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $contents->links() }}
    </div>
</div>

<!-- Modal de Rejeição -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeitar Conteúdo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reject-form" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rejeitar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function approveContent(id) {
    if (confirm('Tem certeza que deseja aprovar este conteúdo?')) {
        document.getElementById('approve-form-' + id).submit();
    }
}

function confirmDelete(id) {
    if (confirm('Tem certeza que deseja excluir este conteúdo?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}

function showRejectModal(id) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    document.getElementById('reject-form').action = `/admin/contents/${id}/reject`;
    modal.show();
}
</script>
@endpush
@endsection 
