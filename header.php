<?php
require_once 'config/conexao.class.php';
require_once 'config/crud.class.php';
require_once 'config.php';

$con  = new conexao();
$conn = $con->connect();
if (!$conn) {
    die('Erro ao conectar ao banco de dados: ' . $con->getError());
}

$sql = "SELECT id, nome, categoria, link_img FROM projetos WHERE ativo = 1 ORDER BY anoprojeto DESC";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Erro na consulta: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Paixão de Cristo de Igarassu — Azerutan</title>

    <!-- Bootstrap -->
    <link href="./template/bootstrap.min.css" rel="stylesheet" />

    <!-- Estilo do layout (paleta reaproveitada) -->
    <style>
        :root {
            --primary: #20b2aa;
            /* lightseagreen */
            --primary-700: #17928b;
            --bg: #f4f7f8;
            --card: #ffffff;
            --text: #1b1f23;
            --muted: #6c757d;
            --ok: #32cd32;
            /* limegreen */
        }

        html,
        body {
            height: 100%
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
        }

        .navbar {
            background: var(--primary);
        }

        .navbar .nav-link,
        .navbar-brand {
            font-weight: 600
        }

        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-700) 100%);
            color: #fff;
            border-radius: 1rem;
            padding: 2rem;
            margin-top: 5rem;
        }

        .hero h1 {
            font-weight: 700;
            letter-spacing: .2px
        }

        .hero p {
            opacity: .95
        }

        .section-title {
            font-size: clamp(1.25rem, 1.2rem + .6vw, 1.75rem);
            font-weight: 700;
            margin: 1.5rem 0 .75rem;
            color: var(--primary-700);
        }

        .card-az {
            background: var(--card);
            border: 1px solid #e8ecee;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .04);
            height: 100%;
        }

        .card-az .card-header {
            background: transparent;
            border-bottom: 1px solid #eef2f3;
            font-weight: 700;
            color: var(--primary-700);
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-700);
            border-color: var(--primary-700);
        }

        .status-chip {
            font-size: clamp(.95rem, .9rem + .2vw, 1.15rem);
            color: #fff;
            background: var(--ok);
            border-radius: .6rem;
            padding: .25rem .6rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* grade dos projetos (responsivo) */
        .proj-card {
            transition: transform .18s ease, box-shadow .18s ease;
            text-align: center;
            padding: 1.25rem 1rem;
        }

        .proj-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 26px rgba(0, 0, 0, .08);
        }

        .proj-icon {
            width: 172px;
            height: 72px;
            object-fit: contain;
            margin-bottom: .5rem;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .12));
        }

        .proj-name {
            font-weight: 700;
            margin: .25rem 0 0;
            font-size: 1.05rem;
        }

        .proj-cat {
            font-size: .85rem;
            color: var(--muted);
        }

        /* utilidades */
        .gap-12 {
            gap: 12px
        }

        .mt-32 {
            margin-top: 32px
        }

        /* ajustes mobile */
        @media (max-width: 575.98px) {
            .navbar-brand {
                font-size: 1.05rem
            }

            .hero {
                padding: 1.25rem;
                margin-top: 4rem
            }

            .card-az {
                border-radius: .9rem
            }

            .proj-icon {
                width: 64px;
                height: 64px
            }
        }

        :root {
            --light-gray: #ECEFF1;
            --forest-green: #2E7D32;
            --white: #FFFFFF;
            --light-green: #A5D6A7;
            --blue: #1976D2;
            --darker-blue: #1565C0;
            --green: #4CAF50;
            --darker-green: #43A047;
            --red: #D32F2F;
            --dark-gray: #333;
            --grayish-blue: #B0BEC5;
            --very-light-green: #E8F5E9;
            --pale-green: #DCEDC8;
            --almost-white-green: #F1F8E9;
            --soft-green: #C8E6C9;
            --medium-green: #81C784;
            --bright-green: #66BB6A;
        }




        .nav-link:hover {
            color: var(--light-green) !important;
        }

        .btn-primary {
            background-color: var(--blue);
            border-color: var(--blue);
        }

        .btn-primary:hover {
            background-color: var(--darker-blue);
            border-color: var(--darker-blue);
        }

        .btn-success {
            background-color: var(--green);
            border-color: var(--green);
        }

        .btn-success:hover {
            background-color: var(--darker-green);
            border-color: var(--darker-green);
        }


        .status-pendente {
            color: var(--red);
            font-weight: bold;
        }

        .status-ok {
            color: var(--green);
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group span {
            display: block;
            margin-bottom: 5px;
        }

        .form-group .status-pendente {
            padding: 5px 10px;
            border-radius: 5px;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--grayish-blue);
            border-radius: 4px;
        }

        .label-input {
            border: 2px solid var(--grayish-blue);
            border-radius: 4px;
            padding: 8px 12px;
            background: var(--light-green);
            display: inline-block;
            cursor: pointer;
        }

        .label-input:hover {
            background: var(--pale-green);
        }

        .label-input:active {
            background: var(--soft-green);
        }

        .label-input input[type="file"] {
            display: none;
        }

        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .image-gallery img {
            max-width: 180px;
            border-radius: 4px;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: var(--dark-gray);
            background-color: var(--light-gray);
            margin-top: 20px;
        }

        @media (max-width: 576px) {
            .card-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Azerutan</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMain"
                aria-controls="navMain" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="index.php">Início</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="institucional.php">Institucional</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="index.php">Secretaria</a>
                        <div class="dropdown-menu" aria-labelledby="drop01">
                            <a class="dropdown-item" href="form_colaborador.php?novo=1">Nova Matrícula</a>
                            <a class="dropdown-item" href="inscricao_renovar.php">Renovar Matrícula</a>
                            <a class="dropdown-item" href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC" target="_blank" rel="noopener">WhatsApp — Secretaria</a>
                        </div>
                    </li>
                </ul>
                <form class="form-inline my-2 my-lg-0">
                    <a href="https://www.instagram.com/azerutan" class="btn btn-outline-light my-2 my-sm-0" target="_blank" rel="noopener" class="ml-2" aria-label="Instagram" title="Instagram">
                        INSTAGRAM
                    </a>
                </form>
            </div>
        </div>
    </nav>