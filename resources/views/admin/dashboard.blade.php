@extends('layouts.app')

@section('content')
<div class="p-4">
    <h3 class="mb-4">Tableau de bord</h3>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total</h5>
                    <h2 class="card-text">{{ $stats['total'] ?? 0 }}</h2>
                    <p class="card-text">Collaborateurs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Actifs</h5>
                    <h2 class="card-text">{{ $stats['actifs'] ?? 0 }}</h2>
                    <p class="card-text">En activité</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Départements</h5>
                    <h2 class="card-text">{{ $stats['departements_count'] ?? 0 }}</h2>
                    <p class="card-text">Services</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Moyenne</h5>
                    <h2 class="card-text">{{ number_format($stats['salaire_moyen'] ?? 0, 2, ',', ' ') }} €</h2>
                    <p class="card-text">Salaire moyen</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Derniers collaborateurs</h5>
                </div>
                <div class="card-body">
                    @if($recentCollaborateurs->count() > 0)
                        @foreach($recentCollaborateurs as $collab)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong>{{ $collab->first_name }} {{ $collab->last_name }}</strong><br>
                                    <small class="text-muted">{{ $collab->poste }} • {{ $collab->department }}</small>
                                </div>
                                <span class="badge badge-{{ $collab->statut }}">{{ $collab->statut }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">Aucun collaborateur trouvé</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Par statut</h5>
                </div>
                <div class="card-body">
                    <canvas id="statsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('statsChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Actifs', 'Inactifs', 'Congé', 'Licenciés'],
                datasets: [{
                    data: [
                        {{ $stats['actifs'] ?? 0 }},
                        {{ $stats['inactifs'] ?? 0 }},
                        {{ $stats['conges'] ?? 0 }},
                        {{ $stats['licencies'] ?? 0 }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#6c757d',
                        '#ffc107',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection