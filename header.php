<?php
require_once 'auth.php';

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMW FRANCE CONCESSIONS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        /* === BMW M-Power NAVBAR === */
        .navbar-bmw {
            position: relative;
            background: linear-gradient(110deg,
                    #009ADA 0%,
                    #009ADA 25%,
                    #13274F 25%,
                    #13274F 50%,
                    #E60A14 50%,
                    #E60A14 75%,
                    #1B1B1B 75%,
                    #1B1B1B 100%);
            color: #fff;
            border-bottom: 3px solid #000;
            transform: skewX(-12deg);
            overflow: hidden;
        }

        .navbar-bmw .container-fluid {
            transform: skewX(12deg);
        }

        .navbar-bmw .navbar-brand {
            color: #fff;
            font-weight: 800;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-bmw img.logo {
            height: 42px;
            filter: drop-shadow(1px 1px 2px rgba(0, 0, 0, 0.3));
        }

        .navbar-bmw .nav-link {
            color: #fff;
            font-weight: 500;
            margin-left: 15px;
            transition: 0.3s;
        }

        .navbar-bmw .nav-link:hover {
            color: #FFD700;
            text-decoration: none;
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-bmw">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg"
                    alt="BMW Logo" class="logo">
                BMW FRANCE CONCESSIONS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navmenu">
                <div class="collapse navbar-collapse justify-content-end" id="navmenu">
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Concessions</a>
                        </li>

                        <?php if (function_exists('estAdmin') && estAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link text-warning fw-bold" href="admin_dashboard.php">Administration</a>
                            </li>
                        <?php endif; ?>

                        <?php if (function_exists('estConnecte') && estConnecte()): ?>
                            <?php $u = getUtilisateur(); ?>
                            <li class="nav-item">
                                <span class="nav-link disabled text-white">
                                    👤 <?= htmlspecialchars($u['prenom'] ?? $u['username']) ?>
                                    <?php if (estAdmin()): ?><span class="badge bg-warning text-dark">Admin</span><?php endif; ?>
                                </span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-danger fw-bold" href="logout.php">Déconnexion</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                            <li class="nav-item"><a class="nav-link" href="register.php">Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>
        </div>
    </nav>


    <div class="container">
        </header>