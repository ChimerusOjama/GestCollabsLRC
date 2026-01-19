class CollaborateurManager {
    constructor() {
        this.baseUrl = '/api/v1';
        this.token = localStorage.getItem('api_token');
        this.currentUser = JSON.parse(localStorage.getItem('current_user') || '{}');
        this.init();
    }

    init() {
        this.bindEvents();
        this.checkAuth();
        this.loadDashboard();
    }

    bindEvents() {
        // Navigation
        document.getElementById('nav-collaborateurs').addEventListener('click', (e) => {
            e.preventDefault();
            this.loadCollaborateurs();
        });

        document.getElementById('btn-refresh').addEventListener('click', () => {
            this.refreshCurrentView();
        });

        document.getElementById('btn-logout').addEventListener('click', () => {
            this.logout();
        });

        // Afficher l'utilisateur actuel
        if (this.currentUser.full_name) {
            document.getElementById('current-user').textContent = this.currentUser.full_name;
        }
    }

    async checkAuth() {
        if (!this.token) {
            window.location.href = '/login';
            return;
        }

        try {
            await this.makeRequest('GET', '/user');
        } catch (error) {
            if (error.response && error.response.status === 401) {
                this.logout();
            }
        }
    }

    async makeRequest(method, endpoint, data = null) {
        const config = {
            method: method,
            url: `${this.baseUrl}${endpoint}`,
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        if (data) {
            config.data = data;
        }

        try {
            const response = await axios(config);
            return response.data;
        } catch (error) {
            console.error('API Error:', error.response || error);
            
            if (error.response && error.response.status === 401) {
                this.logout();
            }
            
            this.showToast(error.response?.data?.message || 'Erreur API', 'error');
            throw error;
        }
    }

    async loadDashboard() {
        const content = `
            <h3 class="mb-4">Tableau de bord</h3>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total</h5>
                            <h2 id="total-collaborateurs" class="card-text">--</h2>
                            <p class="card-text">Collaborateurs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Actifs</h5>
                            <h2 id="actifs-count" class="card-text">--</h2>
                            <p class="card-text">En activité</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Départements</h5>
                            <h2 id="departements-count" class="card-text">--</h2>
                            <p class="card-text">Services</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Moyenne</h5>
                            <h2 id="salaire-moyen" class="card-text">--</h2>
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
                            <div id="recent-collaborateurs" class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Par statut</h5>
                        </div>
                        <div class="card-body">
                            <div id="stats-by-status" class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('app-content').innerHTML = content;
        await this.loadDashboardData();
    }

    async loadDashboardData() {
        try {
            const stats = await this.makeRequest('GET', '/stats/collaborateurs');
            const recent = await this.makeRequest('GET', '/collaborateurs?per_page=5');

            // Mettre à jour les statistiques
            document.getElementById('total-collaborateurs').textContent = stats.total || '0';
            document.getElementById('actifs-count').textContent = stats.actifs || '0';
            document.getElementById('departements-count').textContent = stats.departements_count || '0';
            document.getElementById('salaire-moyen').textContent = stats.salaire_moyen ? 
                `${stats.salaire_moyen.toLocaleString('fr-FR')} €` : '--';

            // Afficher les derniers collaborateurs
            let recentHtml = '';
            if (recent.data && recent.data.length > 0) {
                recent.data.forEach(collab => {
                    recentHtml += `
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>${collab.first_name} ${collab.last_name}</strong><br>
                                <small class="text-muted">${collab.poste} • ${collab.department}</small>
                            </div>
                            <span class="badge badge-${collab.statut}">${collab.statut}</span>
                        </div>
                    `;
                });
            } else {
                recentHtml = '<p class="text-muted">Aucun collaborateur trouvé</p>';
            }
            document.getElementById('recent-collaborateurs').innerHTML = recentHtml;

        } catch (error) {
            console.error('Erreur chargement dashboard:', error);
        }
    }

    async loadCollaborateurs() {
        const content = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Gestion des collaborateurs</h3>
                <button class="btn btn-primary" id="btn-add-collaborateur">
                    <i class="bi bi-plus-circle"></i> Nouveau collaborateur
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="search-collaborateur" 
                                   placeholder="Rechercher par nom, matricule, email...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="filter-department">
                                <option value="">Tous les départements</option>
                                <option value="RH">RH</option>
                                <option value="IT">IT</option>
                                <option value="Finance">Finance</option>
                                <option value="Administration">Administration</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="filter-statut">
                                <option value="">Tous les statuts</option>
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="congé">Congé</option>
                                <option value="licencié">Licencié</option>
                            </select>
                        </div>
                    </div>

                    <div id="collaborateurs-table">
                        <div class="text-center py-5">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('app-content').innerHTML = content;
        
        // Ajouter les écouteurs d'événements
        setTimeout(() => {
            document.getElementById('btn-add-collaborateur').addEventListener('click', () => {
                this.showAddForm();
            });

            document.getElementById('search-collaborateur').addEventListener('input', (e) => {
                this.searchCollaborateurs(e.target.value);
            });

            document.getElementById('filter-department').addEventListener('change', () => {
                this.filterCollaborateurs();
            });

            document.getElementById('filter-statut').addEventListener('change', () => {
                this.filterCollaborateurs();
            });
        }, 100);

        await this.loadCollaborateursTable();
    }

    async loadCollaborateursTable() {
        try {
            const response = await this.makeRequest('GET', '/collaborateurs');
            this.renderCollaborateursTable(response.data);
        } catch (error) {
            console.error('Erreur chargement collaborateurs:', error);
        }
    }

    renderCollaborateursTable(collaborateurs) {
        let html = `
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
                    <tbody>
        `;

        if (collaborateurs && collaborateurs.length > 0) {
            collaborateurs.forEach(collab => {
                html += `
                    <tr>
                        <td><strong>${collab.matricule}</strong></td>
                        <td>${collab.first_name} ${collab.last_name}</td>
                        <td>${collab.email}</td>
                        <td>${collab.department}</td>
                        <td>${collab.poste}</td>
                        <td>
                            <span class="badge badge-${collab.statut}">
                                ${collab.statut}
                            </span>
                        </td>
                        <td>${new Date(collab.date_embauche).toLocaleDateString('fr-FR')}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1 view-collaborateur" 
                                    data-id="${collab.id}" title="Voir">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning me-1 edit-collaborateur" 
                                    data-id="${collab.id}" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-collaborateur" 
                                    data-id="${collab.id}" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html += `
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        Aucun collaborateur trouvé
                    </td>
                </tr>
            `;
        }

        html += `
                    </tbody>
                </table>
            </div>
        `;

        document.getElementById('collaborateurs-table').innerHTML = html;

        // Ajouter les écouteurs d'événements pour les boutons
        document.querySelectorAll('.view-collaborateur').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.viewCollaborateur(id);
            });
        });

        document.querySelectorAll('.edit-collaborateur').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.editCollaborateur(id);
            });
        });

        document.querySelectorAll('.delete-collaborateur').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.deleteCollaborateur(id);
            });
        });
    }

    async viewCollaborateur(id) {
        try {
            const response = await this.makeRequest('GET', `/collaborateurs/${id}`);
            this.showCollaborateurModal(response.data, 'view');
        } catch (error) {
            console.error('Erreur visualisation collaborateur:', error);
        }
    }

    async editCollaborateur(id) {
        try {
            const response = await this.makeRequest('GET', `/collaborateurs/${id}`);
            this.showCollaborateurModal(response.data, 'edit');
        } catch (error) {
            console.error('Erreur édition collaborateur:', error);
        }
    }

    showCollaborateurModal(data, mode) {
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const modal = new bootstrap.Modal(document.getElementById('formModal'));

        if (mode === 'view') {
            modalTitle.textContent = `Détails: ${data.first_name} ${data.last_name}`;
            modalBody.innerHTML = this.getViewForm(data);
        } else if (mode === 'edit') {
            modalTitle.textContent = `Modifier: ${data.first_name} ${data.last_name}`;
            modalBody.innerHTML = this.getEditForm(data);
            
            // Ajouter l'écouteur pour le formulaire d'édition
            setTimeout(() => {
                document.getElementById('editForm').addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.updateCollaborateur(data.id, e.target);
                });
            }, 100);
        }

        modal.show();
    }

    getViewForm(data) {
        return `
            <div class="row">
                <div class="col-md-6">
                    <h6>Informations personnelles</h6>
                    <p><strong>Matricule:</strong> ${data.matricule}</p>
                    <p><strong>Nom complet:</strong> ${data.first_name} ${data.last_name}</p>
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Téléphone:</strong> ${data.phone || 'Non renseigné'}</p>
                    <p><strong>Date de naissance:</strong> ${data.date_of_birth ? 
                        new Date(data.date_of_birth).toLocaleDateString('fr-FR') : 'Non renseignée'}</p>
                </div>
                <div class="col-md-6">
                    <h6>Informations professionnelles</h6>
                    <p><strong>Département:</strong> ${data.department}</p>
                    <p><strong>Poste:</strong> ${data.poste}</p>
                    <p><strong>Statut:</strong> <span class="badge badge-${data.statut}">${data.statut}</span></p>
                    <p><strong>Date d'embauche:</strong> ${new Date(data.date_embauche).toLocaleDateString('fr-FR')}</p>
                    <p><strong>Ancienneté:</strong> ${data.anciennete || 0} ans</p>
                    <p><strong>Salaire:</strong> ${data.salaire ? 
                        `${parseFloat(data.salaire).toLocaleString('fr-FR')} €` : 'Non renseigné'}</p>
                </div>
            </div>
            ${data.notes ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Notes</h6>
                        <p>${data.notes}</p>
                    </div>
                </div>
            ` : ''}
        `;
    }

    getEditForm(data) {
        return `
            <form id="editForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Matricule *</label>
                        <input type="text" class="form-control" name="matricule" 
                               value="${data.matricule}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Statut *</label>
                        <select class="form-control" name="statut" required>
                            <option value="actif" ${data.statut === 'actif' ? 'selected' : ''}>Actif</option>
                            <option value="inactif" ${data.statut === 'inactif' ? 'selected' : ''}>Inactif</option>
                            <option value="congé" ${data.statut === 'congé' ? 'selected' : ''}>Congé</option>
                            <option value="licencié" ${data.statut === 'licencié' ? 'selected' : ''}>Licencié</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom *</label>
                        <input type="text" class="form-control" name="first_name" 
                               value="${data.first_name}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom *</label>
                        <input type="text" class="form-control" name="last_name" 
                               value="${data.last_name}" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" 
                               value="${data.email}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" name="phone" 
                               value="${data.phone || ''}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Département *</label>
                        <select class="form-control" name="department" required>
                            <option value="RH" ${data.department === 'RH' ? 'selected' : ''}>RH</option>
                            <option value="IT" ${data.department === 'IT' ? 'selected' : ''}>IT</option>
                            <option value="Finance" ${data.department === 'Finance' ? 'selected' : ''}>Finance</option>
                            <option value="Administration" ${data.department === 'Administration' ? 'selected' : ''}>Administration</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Poste *</label>
                        <input type="text" class="form-control" name="poste" 
                               value="${data.poste}" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salaire</label>
                        <input type="number" step="0.01" class="form-control" name="salaire" 
                               value="${data.salaire || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date d'embauche *</label>
                        <input type="date" class="form-control" name="date_embauche" 
                               value="${data.date_embauche}" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3">${data.notes || ''}</textarea>
                </div>
                
                <div class="text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        `;
    }

    async updateCollaborateur(id, form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            await this.makeRequest('PUT', `/collaborateurs/${id}`, data);
            this.showToast('Collaborateur mis à jour avec succès', 'success');
            
            // Fermer le modal et rafraîchir la table
            bootstrap.Modal.getInstance(document.getElementById('formModal')).hide();
            await this.loadCollaborateursTable();
            
        } catch (error) {
            console.error('Erreur mise à jour:', error);
        }
    }

    async deleteCollaborateur(id) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce collaborateur ?')) {
            return;
        }

        try {
            await this.makeRequest('DELETE', `/collaborateurs/${id}`);
            this.showToast('Collaborateur supprimé avec succès', 'success');
            await this.loadCollaborateursTable();
        } catch (error) {
            console.error('Erreur suppression:', error);
        }
    }

    showAddForm() {
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const modal = new bootstrap.Modal(document.getElementById('formModal'));

        modalTitle.textContent = 'Nouveau collaborateur';
        modalBody.innerHTML = this.getAddForm();
        
        modal.show();

        // Ajouter l'écouteur pour le formulaire d'ajout
        setTimeout(() => {
            document.getElementById('addForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.createCollaborateur(e.target);
            });
        }, 100);
    }

    getAddForm() {
        return `
            <form id="addForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Matricule *</label>
                        <input type="text" class="form-control" name="matricule" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Statut *</label>
                        <select class="form-control" name="statut" required>
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                            <option value="congé">Congé</option>
                            <option value="licencié">Licencié</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom *</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom *</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mot de passe *</label>
                        <input type="password" class="form-control" name="password" 
                               minlength="8" required>
                        <small class="form-text text-muted">Minimum 8 caractères</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" class="form-control" name="date_of_birth">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Département *</label>
                        <select class="form-control" name="department" required>
                            <option value="">Sélectionner...</option>
                            <option value="RH">RH</option>
                            <option value="IT">IT</option>
                            <option value="Finance">Finance</option>
                            <option value="Administration">Administration</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Poste *</label>
                        <input type="text" class="form-control" name="poste" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salaire</label>
                        <input type="number" step="0.01" class="form-control" name="salaire">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date d'embauche *</label>
                        <input type="date" class="form-control" name="date_embauche" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <textarea class="form-control" name="address" rows="2"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3"></textarea>
                </div>
                
                <div class="text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        `;
    }

    async createCollaborateur(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            await this.makeRequest('POST', '/collaborateurs', data);
            this.showToast('Collaborateur créé avec succès', 'success');
            
            // Fermer le modal et rafraîchir la table
            bootstrap.Modal.getInstance(document.getElementById('formModal')).hide();
            await this.loadCollaborateursTable();
            
        } catch (error) {
            console.error('Erreur création:', error);
        }
    }

    async searchCollaborateurs(term) {
        if (term.length < 2) {
            await this.loadCollaborateursTable();
            return;
        }

        try {
            const response = await this.makeRequest('GET', `/collaborateurs/search/${term}`);
            this.renderCollaborateursTable(response.data);
        } catch (error) {
            console.error('Erreur recherche:', error);
        }
    }

    async filterCollaborateurs() {
        const department = document.getElementById('filter-department').value;
        const statut = document.getElementById('filter-statut').value;

        let url = '/collaborateurs?';
        const params = [];

        if (department) params.push(`department=${department}`);
        if (statut) params.push(`statut=${statut}`);

        if (params.length > 0) {
            url += params.join('&');
        } else {
            url = '/collaborateurs';
        }

        try {
            const response = await this.makeRequest('GET', url);
            this.renderCollaborateursTable(response.data);
        } catch (error) {
            console.error('Erreur filtrage:', error);
        }
    }

    refreshCurrentView() {
        const currentNav = document.querySelector('.nav-link.active');
        if (currentNav.id === 'nav-collaborateurs') {
            this.loadCollaborateursTable();
        } else if (currentNav.textContent.includes('Tableau')) {
            this.loadDashboardData();
        }
    }

    async logout() {
        try {
            await this.makeRequest('POST', '/logout');
        } catch (error) {
            // Ignorer les erreurs de déconnexion
        }

        localStorage.removeItem('api_token');
        localStorage.removeItem('current_user');
        window.location.href = '/login';
    }

    showToast(message, type = 'info') {
        // Créer un toast Bootstrap
        const toastContainer = document.getElementById('toast-container') || 
                               this.createToastContainer();
        
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" 
                 role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                            data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.innerHTML += toastHtml;
        
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    }
}

// Initialiser l'application
document.addEventListener('DOMContentLoaded', () => {
    window.app = new CollaborateurManager();
});