@extends('layouts.app')

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Nouveau collaborateur</h3>
        <a href="{{ route('admin.collaborateurs.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.collaborateurs.store') }}" method="POST" id="createForm">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Matricule *</label>
                        <input type="text" class="form-control @error('matricule') is-invalid @enderror" 
                               name="matricule" value="{{ old('matricule') }}" required>
                        @error('matricule')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Statut *</label>
                        <select class="form-control @error('statut') is-invalid @enderror" name="statut" required>
                            <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            <option value="congé" {{ old('statut') == 'congé' ? 'selected' : '' }}>Congé</option>
                            <option value="licencié" {{ old('statut') == 'licencié' ? 'selected' : '' }}>Licencié</option>
                        </select>
                        @error('statut')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom *</label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                               name="first_name" value="{{ old('first_name') }}" required>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom *</label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                               name="last_name" value="{{ old('last_name') }}" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mot de passe *</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               name="password" minlength="8" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Minimum 8 caractères</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                               name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                               name="date_of_birth" value="{{ old('date_of_birth') }}">
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Département *</label>
                        <select class="form-control @error('department') is-invalid @enderror" name="department" required>
                            <option value="">Sélectionner...</option>
                            <option value="RH" {{ old('department') == 'RH' ? 'selected' : '' }}>RH</option>
                            <option value="IT" {{ old('department') == 'IT' ? 'selected' : '' }}>IT</option>
                            <option value="Finance" {{ old('department') == 'Finance' ? 'selected' : '' }}>Finance</option>
                            <option value="Administration" {{ old('department') == 'Administration' ? 'selected' : '' }}>Administration</option>
                        </select>
                        @error('department')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Poste *</label>
                        <input type="text" class="form-control @error('poste') is-invalid @enderror" 
                               name="poste" value="{{ old('poste') }}" required>
                        @error('poste')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salaire</label>
                        <input type="number" step="0.01" class="form-control @error('salaire') is-invalid @enderror" 
                               name="salaire" value="{{ old('salaire') }}">
                        @error('salaire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date d'embauche *</label>
                        <input type="date" class="form-control @error('date_embauche') is-invalid @enderror" 
                               name="date_embauche" value="{{ old('date_embauche') }}" required>
                        @error('date_embauche')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" 
                              name="address" rows="2">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('createForm').addEventListener('submit', function(e) {
        const button = this.querySelector('button[type="submit"]');
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Création...';
    });
</script>
@endpush
@endsection