<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'EcoVoyage' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8faf7;
        }

        .navbar-eco {
            background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar-eco .navbar-brand {
            font-weight: 700;
            font-size: 1.6rem;
            color: #fff !important;
            letter-spacing: -0.5px;
        }

        .navbar-eco .navbar-brand i {
            color: #b7e4c7;
            margin-right: 6px;
        }

        .navbar-eco .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 600;
            transition: color 0.2s, background-color 0.2s;
            margin: 0 2px;
            border-radius: 6px;
        }

        .navbar-eco .nav-link:hover,
        .navbar-eco .nav-link.active {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.15);
        }

        .navbar-eco .navbar-text {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .navbar-eco .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.6);
            color: #fff;
        }

        .navbar-eco .btn-outline-light:hover {
            background-color: #fff;
            color: #2d6a4f;
        }

        .eco-container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        .navbar-eco .btn-outline-light:focus {
            background-color: #fff;
            color: #2d6a4f;
        }
    </style>
</head>

<body>
    <?php
    $rolePaths = [
        'super_admin' => 'admin',
        'regional_auditor' => 'auditor',
        'guide' => 'guide',
        'traveler' => 'traveler',
    ];
    $rolePrefix = isset($_SESSION['user_id']) ? $rolePaths[$_SESSION['role']] : 'guest';
    ?>
    <nav class="navbar navbar-expand-lg navbar-eco mb-4">
        <div class="container">
            <a class="navbar-brand"
                href="<?= isset($_SESSION['user_id']) ? BASE_URL . $rolePrefix . '/dashboard' : BASE_URL ?>">
                <i class="bi bi-tree-fill"></i>EcoVoyage
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                style="border-color: rgba(255,255,255,0.5);">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <div class="d-flex ms-auto align-items-center">
                    <ul class="navbar-nav me-2">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>guest/index">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>guest/tours">Browse Tours</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auth/register">Register</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auth/login">Login</a></li>
                        <?php elseif ($_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/dashboard">Dashboard</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/users">Users</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/tours">Tours</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/settings">Settings</a></li>

                        <?php elseif ($_SESSION['role'] == 'traveler'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>traveler/dashboard">Dashboard</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>traveler/tours">Browse Tours</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>traveler/bookings">My Bookings</a>
                            </li>


                        <?php elseif ($_SESSION['role'] == 'guide'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>guide/dashboard">Guide Panel</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>guide/tours">My Tours</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>guide/bookings">Bookings</a></li>

                        <?php elseif ($_SESSION['role'] == 'auditor'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auditor/dashboard">Audit Panel</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auditor/reports">Reports</a></li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item dropdown">
                                <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
                                    type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle fs-5"></i>
                                    <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL . $rolePrefix ?>/settings">
                                            <i class="bi bi-gear me-2"></i> Account Settings
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container eco-container">