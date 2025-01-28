@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $championship->title }}</h4>
                </div>
                <div class="card-body">
                    @if($championship->image)
                        <img src="{{ Storage::url($championship->image) }}"
                             class="img-fluid rounded mb-4"
                             alt="{{ $championship->title }}">
                    @endif

                    <div class="mb-4">
                        <h5>Descrição</h5>
                        <p>{{ $championship->description }}</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Categorias</h5>
                            <ul class="list-group">
                                @foreach($championship->categories as $category)
                                    <li class="list-group-item">
                                        {{ $category->name }}
                                        @if($category->description)
                                            <small class="d-block text-muted">
                                                {{ $category->description }}
                                            </small>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Informações</h5>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <i class="fas fa-calendar"></i> Inscrições:
                                    {{ $championship->registration_start->format('d/m/Y') }} até
                                    {{ $championship->registration_end->format('d/m/Y') }}
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-star"></i> Votação:
                                    {{ $championship->voting_start->format('d/m/Y') }} até
                                    {{ $championship->voting_end->format('d/m/Y') }}
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-dollar-sign"></i> Taxa:
                                    R$ {{ number_format($championship->entry_fee, 2, ',', '.') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if($championship->status === 'active')
                        <div class="alert alert-info">
                            <h5 class="alert-heading">Status: Em Andamento</h5>
                            <p>Este campeonato está ativo. Os participantes podem enviar seus vídeos.</p>
                        </div>
                    @elseif($championship->status === 'voting')
                        <div class="alert alert-warning">
                            <h5 class="alert-heading">Status: Em Votação</h5>
                            <p>A fase de votação está aberta. Vote nos seus vídeos favoritos!</p>
                        </div>
                    @elseif($championship->status === 'finished')
                        <div class="alert alert-success">
                            <h5 class="alert-heading">Status: Finalizado</h5>
                            <p>Este campeonato já foi finalizado. Confira os resultados!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ações</h5>
                </div>
                <div class="card-body">
                    @if(auth()->user()->isParticipatingIn($championship))
                        @if($championship->status === 'active')
                            <a href="{{ route('videos.create', $championship) }}"
                               class="btn btn-success btn-lg btn-block mb-3">
                                <i class="fas fa-video"></i> Enviar Vídeo
                            </a>
                        @endif

                        @if($championship->status === 'voting')
                            <a href="{{ route('votes.index', $championship) }}"
                               class="btn btn-primary btn-lg btn-block mb-3">
                                <i class="fas fa-star"></i> Votar
                            </a>
                        @endif

                        <a href="{{ route('rankings.index', $championship) }}"
                           class="btn btn-info btn-lg btn-block">
                            <i class="fas fa-trophy"></i> Ver Rankings
                        </a>
                    @else
                        @if($championship->isRegistrationOpen())
                            <a href="{{ route('payments.show', $championship) }}"
                               class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-sign-in-alt"></i> Inscrever-se
                            </a>
                        @else
                            <button class="btn btn-secondary btn-lg btn-block" disabled>
                                Inscrições Encerradas
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Participantes</h5>
                </div>
                <div class="card-body">
                    <p class="text-center">
                        <strong>{{ $championship->participants->count() }}</strong> participantes inscritos
                    </p>
                    <div class="participant-list">
                        @foreach($championship->participants->take(10) as $participant)
                            <div class="d-flex align-items-center mb-2">
                                @if($participant->avatar)
                                    <img src="{{ Storage::url($participant->avatar) }}"
                                         class="rounded-circle me-2"
                                         style="width: 30px; height: 30px;"
                                         alt="{{ $participant->name }}">
                                @else
                                    <div class="rounded-circle bg-secondary text-white me-2 d-flex align-items-center justify-content-center"
                                         style="width: 30px; height: 30px;">
                                        {{ substr($participant->name, 0, 1) }}
                                    </div>
                                @endif
                                {{ $participant->name }}
                                @if($participant->country)
                                    <img src="{{ asset('images/flags/' . $participant->country . '.png') }}"
                                         class="ms-auto"
                                         style="width: 20px;"
                                         alt="{{ $participant->country }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
