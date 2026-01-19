<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LRC Group - Gestion des collaborateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        }
        .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #3498db;
            color: white;
        }
        .stat-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-actif { background-color: #28a745; }
        .badge-inactif { background-color: #6c757d; }
        .badge-congé { background-color: #ffc107; }
        .badge-licencié { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4 text-center">
                    <h4 class="text-white mb-0">LRC Group</h4>
                    <small class="text-muted">Gestion des collaborateurs</small>
                </div>
                <nav class="nav flex-column p-3">
                    <a class="nav-link active" href="#">
                        <i class="bi bi-house-door me-2"></i> Tableau de bord
                    </a>
                    <a class="nav-link" href="#" id="nav-collaborateurs">
                        <i class="bi bi-people me-2"></i> Collaborateurs
                    </a>
                    <a class="nav-link" href="#" id="nav-departements">
                        <i class="bi bi-building me-2"></i> Départements
                    </a>
                    <a class="nav-link" href="#" id="nav-managers">
                        <i class="bi bi-person-badge me-2"></i> Managers
                    </a>
                    <div class="mt-4">
                        <small class="text-muted px-3">ADMINISTRATION</small>
                        <a class="nav-link" href="#">
                            <i class="bi bi-gear me-2"></i> Paramètres
                        </a>
                        <a class="nav-link" href="#" id="nav-logs">
                            <i class="bi bi-journal-text me-2"></i> Logs API
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-10 p-0">
                <!-- Top navbar -->
                <nav class="navbar navbar-light bg-light border-bottom">
                    <div class="container-fluid">
                        <span class="navbar-brand">Bienvenue, <span id="current-user">Admin</span></span>
                        <div class="d-flex">
                            <button class="btn btn-outline-primary btn-sm me-2" id="btn-refresh">
                                <i class="bi bi-arrow-clockwise"></i> Actualiser
                            </button>
                            <button class="btn btn-danger btn-sm" id="btn-logout">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </button>
                        </div>
                    </div>
                </nav>

                <!-- Content area -->
                <div class="p-4" id="app-content">
                    <!-- Le contenu sera chargé dynamiquement ici -->
                    <div class="text-center py-5">
                        <h4 class="text-muted">Chargement de l'interface...</h4>
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les formulaires -->
    <div class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Contenu du formulaire -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/js/app.js"></script>
</body>
</html>