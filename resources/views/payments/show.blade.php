@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Pagamento da Inscrição</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h5>{{ $championship->title }}</h5>
                        <p class="text-muted">Taxa de Inscrição: R$ {{ number_format($championship->entry_fee, 2, ',', '.') }}</p>
                    </div>

                    <div class="alert alert-info">
                        <h5 class="alert-heading">Instruções de Pagamento</h5>
                        <p>Para confirmar sua inscrição, faça um PIX para a chave abaixo:</p>
                    </div>

                    <div class="text-center mb-4">
                        <div class="pix-key-container p-3 bg-light rounded">
                            <h6>Chave PIX:</h6>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control"
                                       value="{{ $championship->pix_key }}"
                                       readonly>
                                <button class="btn btn-outline-primary copy-btn"
                                        data-clipboard-text="{{ $championship->pix_key }}">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <ul class="mb-0">
                            <li>Após realizar o pagamento, aguarde a confirmação automática</li>
                            <li>O prazo de confirmação é de até 5 minutos</li>
                            <li>Guarde o comprovante do PIX</li>
                        </ul>
                    </div>

                    <form action="{{ route('payments.process', $championship) }}"
                          method="POST"
                          class="text-center">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> Já Realizei o Pagamento
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
<script>
    new ClipboardJS('.copy-btn');

    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-copy"></i>';
            }, 2000);
        });
    });
</script>
@endpush
@endsection
