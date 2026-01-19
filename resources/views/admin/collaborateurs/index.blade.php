@extends('layouts.app')

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Gestion des collaborateurs</h3>
        <a href="{{ route('admin.collaborateurs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouveau collaborateur
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchInput" 
                           placeholder="Rechercher par nom, matricule, email...">
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="filterDepartment">
                        <option value="">Tous les départements</option>
                        @foreach($departements as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="filterStatus">
                        <option value="">Tous les statuts</option>
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                        <option value="congé">Congé</option>
                        <option value="licencié">Licencié</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Département</th>
                            <th>Poste</th>
                            <th>Statut</th>
                            <th>Date embauche</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="collaborateursTable">
                        @forelse($collaborateurs as $collab)
                            <tr>
                                <td><strong>{{ $collab->matricule }}</strong></td>
                                <td>{{ $collab->first_name }} {{ $collab->last_name }}</td>
                                <td>{{ $collab->email }}</td>
                                <td>{{ $collab->department }}</td>
                                <td>{{ $collab->poste }}</td>
                                <td>
                                    <span class="badge badge-{{ $collab->statut }}">
                                        {{ $collab->statut }}
                                    </span>
                                </td>
                                <td>{{ $collab->date_embauche->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.collaborateurs.show', $collab->id) }}" 
                                       class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.collaborateurs.edit', $collab->id) }}" 
                                       class="btn btn-sm btn-outline-warning me-1" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.collaborateurs.destroy', $collab->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Êtes-vous sûr ?')" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Aucun collaborateur trouvé
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $collaborateurs->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const filterDepartment = document.getElementById('filterDepartment');
        const filterStatus = document.getElementById('filterStatus');
        
        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const department = filterDepartment.value;
            const status = filterStatus.value;
            
            const rows = document.querySelectorAll('#collaborateursTable tr');
            
            rows.forEach(row => {
                const matricule = row.cells[0]?.textContent.toLowerCase() || '';
                const nom = row.cells[1]?.textContent.toLowerCase() || '';
                const email = row.cells[2]?.textContent.toLowerCase() || '';
                const dept = row.cells[3]?.textContent || '';
                const statut = row.cells[5]?.querySelector('.badge')?.textContent || '';
                
                const matchesSearch = !searchTerm || 
                    matricule.includes(searchTerm) || 
                    nom.includes(searchTerm) || 
                    email.includes(searchTerm);
                    
                const matchesDept = !department || dept === department;
                const matchesStatus = !status || statut === status;
                
                row.style.display = (matchesSearch && matchesDept && matchesStatus) ? '' : 'none';
            });
        }
        
        searchInput.addEventListener('input', filterTable);
        filterDepartment.addEventListener('change', filterTable);
        filterStatus.addEventListener('change', filterTable);
    });
</script>
@endpush
@endsection