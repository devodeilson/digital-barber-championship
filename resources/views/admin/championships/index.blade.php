@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Ligas</h5>
            <a href="{{ route('admin.championships.create') }}" class="btn btn-primary btn-sm btn-action">
                <i class="fas fa-plus"></i>
                <span>Nova Liga</span>
            </a>
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

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Imagem</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Participantes</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($championships as $championship)
                            <tr>
                                <td>
                                    @if($championship->image)
                                        <img src="{{ asset('storage/' . $championship->image) }}"
                                             alt="Imagem da liga"
                                             class="img-thumbnail"
                                             style="max-width: 50px;">
                                    @else
                                        <span class="text-muted">Sem imagem</span>
                                    @endif
                                </td>
                                <td>{{ $championship->title }}</td>
                                <td>{{ $championship->type }}</td>
                                <td>
                                    <span class="badge bg-{{ $championship->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ $championship->status == 'active' ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>{{ $championship->start_date->format('d/m/Y') }}</td>
                                <td>{{ $championship->end_date->format('d/m/Y') }}</td>
                                <td>{{ $championship->participants_count ?? 0 }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.championships.edit', $championship) }}"
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.championships.show', $championship) }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.championships.destroy', $championship) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Tem certeza que deseja excluir esta liga?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Nenhuma liga encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($championships->hasPages())
                    <div class="mt-4">
                        {{ $championships->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
