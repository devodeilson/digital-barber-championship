@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Relatórios</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.reports.generate') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo de Relatório</label>
                            <select name="type" class="form-select" required>
                                <option value="users">Usuários</option>
                                <option value="championships">Campeonatos</option>
                                <option value="revenue">Receita</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Data Inicial</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Data Final</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-alt me-2"></i>Gerar Relatório
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
