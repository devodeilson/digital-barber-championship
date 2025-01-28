@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Estatísticas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['users_count'] }}</h3>
                    <p>Usuários</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['championships_count'] }}</h3>
                    <p>Campeonatos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['contents_count'] }}</h3>
                    <p>Conteúdos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>R$ {{ number_format($stats['revenue'], 2, ',', '.') }}</h3>
                    <p>Receita Total</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Campeonatos Ativos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Campeonatos Ativos</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Participantes</th>
                                    <th>Prêmio</th>
                                    <th>Término</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeChampionships as $championship)
                                    <tr>
                                        <td>{{ $championship->name }}</td>
                                        <td>{{ $championship->contents_count }}</td>
                                        <td>R$ {{ number_format($championship->prize, 2, ',', '.') }}</td>
                                        <td>{{ $championship->end_date->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.championships.show', $championship) }}"
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Nenhum campeonato ativo no momento.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
