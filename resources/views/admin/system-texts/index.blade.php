@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Textos do Sistema</h5>
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

            <form action="{{ route('admin.system-texts.batch-update') }}" method="POST">
                @csrf
                @method('POST')

                <ul class="nav nav-tabs mb-4" id="textTabs" role="tablist">
                    @foreach($groups as $group)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                               id="{{ $group }}-tab"
                               data-bs-toggle="tab"
                               href="#{{ $group }}"
                               role="tab">
                                {{ ucfirst($group) }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="textTabsContent">
                    @foreach($groups as $group)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                             id="{{ $group }}"
                             role="tabpanel">

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Chave</th>
                                            <th>Português</th>
                                            <th>Inglês</th>
                                            <th>Espanhol</th>
                                            <th>Descrição</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($texts->get($group, []) as $text)
                                            <tr>
                                                <td>{{ $text->key }}</td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control"
                                                           name="texts[{{ $text->id }}][content_pt]"
                                                           value="{{ $text->content_pt }}">
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control"
                                                           name="texts[{{ $text->id }}][content_en]"
                                                           value="{{ $text->content_en }}">
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control"
                                                           name="texts[{{ $text->id }}][content_es]"
                                                           value="{{ $text->content_es }}">
                                                </td>
                                                <td>{{ $text->description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
