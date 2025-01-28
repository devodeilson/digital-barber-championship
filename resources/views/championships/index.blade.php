@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Campeonatos Disponíveis</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($championships as $championship)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    @if($championship->image)
                                        <img src="{{ Storage::url($championship->image) }}"
                                             class="card-img-top"
                                             alt="{{ $championship->title }}">
                                    @endif
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $championship->title }}</h5>
                                        <p class="card-text">{{ Str::limit($championship->description, 100) }}</p>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> Inscrições:
                                                {{ $championship->registration_start->format('d/m/Y') }} até
                                                {{ $championship->registration_end->format('d/m/Y') }}
                                            </small>
                                        </div>

                                        <div class="mb-3">
                                            <strong class="text-primary">
                                                <i class="fas fa-dollar-sign"></i> Taxa: R$ {{ number_format($championship->entry_fee, 2, ',', '.') }}
                                            </strong>
                                        </div>

                                        @php
                                            $participant = $championship->participants->where('id', auth()->id())->first();
                                        @endphp

                                        @if(!$participant)
                                            @if($championship->isRegistrationOpen())
                                                <a href="{{ route('payments.show', $championship) }}"
                                                   class="btn btn-primary btn-block">
                                                    <i class="fas fa-sign-in-alt"></i> Inscrever-se
                                                </a>
                                            @else
                                                <button class="btn btn-secondary btn-block" disabled>
                                                    Inscrições Encerradas
                                                </button>
                                            @endif
                                        @elseif($participant->pivot->status === 'pending_payment')
                                            <a href="{{ route('payments.show', $championship) }}"
                                               class="btn btn-warning btn-block">
                                                <i class="fas fa-dollar-sign"></i> Pagar Inscrição
                                            </a>
                                        @elseif($participant->pivot->status === 'paid' || $participant->pivot->status === 'confirmed')
                                            @if($championship->status === 'active')
                                                <a href="{{ route('videos.create', $championship) }}"
                                                   class="btn btn-success btn-block">
                                                    <i class="fas fa-video"></i> Enviar Vídeo
                                                </a>
                                            @elseif($championship->status === 'voting')
                                                <a href="{{ route('votes.index', $championship) }}"
                                                   class="btn btn-info btn-block">
                                                    <i class="fas fa-star"></i> Votar
                                                </a>
                                            @else
                                                <a href="{{ route('rankings.index', $championship) }}"
                                                   class="btn btn-secondary btn-block">
                                                    <i class="fas fa-trophy"></i> Ver Resultados
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="card-footer">
                                        <small class="text-muted">
                                            <i class="fas fa-users"></i>
                                            {{ $championship->participants->count() }} participantes
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    Nenhum campeonato disponível no momento.
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $championships->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
