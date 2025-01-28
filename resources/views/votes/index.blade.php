@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h4>Votação - {{ $championship->title }}</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5 class="alert-heading">Como funciona a votação:</h5>
                        <ul class="mb-0">
                            <li>Avalie os vídeos com notas de 1 a 5 estrelas</li>
                            <li>Você pode votar em quantos vídeos quiser</li>
                            <li>Você pode alterar seu voto a qualquer momento</li>
                            <li>A votação termina em: {{ $championship->voting_end->format('d/m/Y H:i') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @forelse($videos as $video)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="ratio ratio-16x9">
                        <iframe
                            src="https://www.youtube.com/embed/{{ $video->youtube_id }}"
                            title="{{ $video->title }}"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $video->title }}</h5>
                        <p class="card-text">
                            <small class="text-muted">
                                Por: {{ $video->user->name }}
                                @if($video->user->country)
                                    <img src="{{ asset('images/flags/' . $video->user->country . '.png') }}"
                                         alt="{{ $video->user->country }}"
                                         class="ms-1"
                                         style="width: 20px;">
                                @endif
                            </small>
                        </p>
                        <p class="card-text">{{ Str::limit($video->description, 100) }}</p>

                        <div class="rating-container" data-video-id="{{ $video->id }}">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star star-rating {{ ($userVotes[$video->id] ?? 0) >= $i ? 'active' : '' }}"
                                   data-rating="{{ $i }}"></i>
                            @endfor
                            <span class="ms-2 rating-info">
                                <small>
                                    ({{ number_format($video->getAverageRating(), 1) }}/5.0 -
                                    {{ $video->getTotalVotes() }} votos)
                                </small>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    Nenhum vídeo disponível para votação no momento.
                </div>
            </div>
        @endforelse

        <div class="col-12 mt-4">
            {{ $videos->links() }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .star-rating {
        cursor: pointer;
        color: #ddd;
        font-size: 1.25rem;
        transition: color 0.2s;
    }
    .star-rating:hover,
    .star-rating.active {
        color: #ffc107;
    }
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.rating-container').forEach(container => {
    const stars = container.querySelectorAll('.star-rating');
    const videoId = container.dataset.videoId;

    stars.forEach(star => {
        star.addEventListener('click', async () => {
            const rating = star.dataset.rating;

            try {
                const response = await fetch(`/videos/${videoId}/vote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ rating })
                });

                const data = await response.json();

                if (data.success) {
                    // Atualiza as estrelas
                    stars.forEach(s => {
                        s.classList.toggle('active', s.dataset.rating <= rating);
                    });

                    // Atualiza o contador de votos
                    container.querySelector('.rating-info').innerHTML =
                        `<small>(${data.newAverage.toFixed(1)}/5.0 - ${data.totalVotes} votos)</small>`;
                }
            } catch (error) {
                console.error('Erro ao votar:', error);
                alert('Erro ao registrar voto. Tente novamente.');
            }
        });
    });
});
</script>
@endpush
@endsection
