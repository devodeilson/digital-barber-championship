@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h4>Rankings - {{ $championship->title }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Ranking Diário
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Posição</th>
                                    <th>Participante</th>
                                    <th>País</th>
                                    <th>Pontuação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyRanking as $rank)
                                    <tr class="{{ $rank->position <= 4 ? 'table-success' : '' }}">
                                        <td>
                                            @if($rank->position <= 3)
                                                <i class="fas fa-trophy text-warning"></i>
                                            @endif
                                            {{ $rank->position }}º
                                        </td>
                                        <td>{{ $rank->user->name }}</td>
                                        <td>
                                            @if($rank->user->country)
                                                <img src="{{ asset('images/flags/' . $rank->user->country . '.png') }}"
                                                     alt="{{ $rank->user->country }}"
                                                     style="width: 20px;">
                                            @endif
                                        </td>
                                        <td>{{ number_format($rank->score, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhum ranking disponível</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy"></i> Ranking Geral
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Posição</th>
                                    <th>Participante</th>
                                    <th>País</th>
                                    <th>Pontuação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($generalRanking as $rank)
                                    <tr class="{{ $rank->position <= 4 ? 'table-success' : '' }}">
                                        <td>
                                            @if($rank->position <= 3)
                                                <i class="fas fa-trophy text-warning"></i>
                                            @endif
                                            {{ $rank->position }}º
                                        </td>
                                        <td>{{ $rank->user->name }}</td>
                                        <td>
                                            @if($rank->user->country)
                                                <img src="{{ asset('images/flags/' . $rank->user->country . '.png') }}"
                                                     alt="{{ $rank->user->country }}"
                                                     style="width: 20px;">
                                            @endif
                                        </td>
                                        <td>{{ number_format($rank->score, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhum ranking disponível</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($championship->status === 'finished')
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star"></i> Finalistas Classificados
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <h6>Parabéns aos classificados!</h6>
                            <p>Estes participantes estão classificados para a Copa Final.</p>
                        </div>
                        <ol class="list-group list-group-numbered">
                            @foreach($generalRanking->take(4) as $finalist)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $finalist->user->name }}
                                    @if($finalist->user->country)
                                        <img src="{{ asset('images/flags/' . $finalist->user->country . '.png') }}"
                                             alt="{{ $finalist->user->country }}"
                                             style="width: 20px;">
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .table-success {
        background-color: #d1e7dd !important;
    }
    .fa-trophy {
        color: #ffd700;
    }
</style>
@endpush
@endsection
