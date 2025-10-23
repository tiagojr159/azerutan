<?php
//header.php
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Paixão de Cristo de Igarassu — Azerutan</title>

    <!-- Bootstrap -->
    <link href="./template/bootstrap.min.css" rel="stylesheet" />

    <!-- Estilo do layout (paleta reaproveitada) -->
    <style>
        :root {
            --primary: #20b2aa;
            --primary-700: #17928b;
            --bg: #f4f7f8;
            --card: #ffffff;
            --text: #1b1f23;
            --muted: #6c757d;
            --ok: #32cd32;
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

        html, body {
            height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
        }

        .navbar {
            background: var(--primary);
        }

        .navbar .nav-link, .navbar-brand {
            font-weight: 600;
        }

        .nav-link:hover {
            color: var(--light-green) !important;
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
            letter-spacing: .2px;
        }

        .hero p {
            opacity: .95;
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

        .btn-success {
            background: var(--green);
            border-color: var(--green);
        }

        .btn-success:hover {
            background: var(--darker-green);
            border-color: var(--darker-green);
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

        .status-pendente {
            color: var(--red);
            font-weight: bold;
        }

        .status-ok {
            color: var(--green);
            font-weight: bold;
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
            gap: 12px;
        }

        .mt-32 {
            margin-top: 32px;
        }

        /* ajustes mobile */
        @media (max-width: 575.98px) {
            .navbar-brand {
                font-size: 1.05rem;
            }

            .hero {
                padding: 1.25rem;
                margin-top: 4rem;
            }

            .card-az {
                border-radius: .9rem;
            }

            .proj-icon {
                width: 64px;
                height: 64px;
            }
            
            /* Transformar a tabela em lista em modo mobile */
            .table-responsive {
                border: none;
                margin-bottom: 1rem;
                overflow-x: visible;
                width: 100%;
            }
            
            .table {
                font-size: 0.85rem;
                width: 100%;
                min-width: auto;
                border: none;
            }
            
            .table thead {
                display: none;
            }
            
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            
            .table tr {
                margin-bottom: 1rem;
                border: 1px solid #e8ecee;
                border-radius: 0.5rem;
                padding: 0.75rem;
                background: #fff;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }
            
            .table td {
                text-align: center !important;
                padding: 0.5rem 0;
                border: none;
                position: relative;
                padding-left: 40%;
                min-height: 40px;
            }
            
            .table td:before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 35%;
                padding-right: 10px;
                font-weight: bold;
                color: var(--muted);
                text-align: left;
                font-size: 0.8rem;
            }
            
            /* Ajustes para a foto */
            .table td:first-child {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 0.5rem;
                min-height: 80px;
                margin-bottom: 0.5rem;
                background: var(--bg);
                border-radius: 0.5rem;
            }
            
            .table td:first-child:before {
                display: none;
            }
            
            .table td:first-child img {
                max-width: 80px;
                max-height: 80px;
                border-radius: 0.5rem;
                object-fit: cover;
            }
            
            /* Ajustes para a coluna de ações */
            .table td:last-child {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                padding-top: 0.75rem;
                border-top: 1px dashed #e8ecee;
                margin-top: 0.5rem;
                justify-content: center;
            }
            
            .table td:last-child:before {
                display: none;
            }
            
            /* Garantir que os botões de ação sejam visíveis */
            .btn-info {
                display: inline-block !important;
                color: #fff !important;
                background-color: #17a2b8 !important;
                border-color: #17a2b8 !important;
                padding: 0.25rem 0.5rem !important;
                font-size: 0.75rem !important;
                line-height: 1.2 !important;
                border-radius: 0.2rem !important;
                margin: 0 !important;
                white-space: nowrap !important;
                visibility: visible !important;
                opacity: 1 !important;
                position: static !important;
                width: auto !important;
                height: auto !important;
                overflow: visible !important;
                clip: auto !important;
                clip-path: none !important;
            }
            
            .btn-info:hover {
                color: #fff !important;
                background-color: #138496 !important;
                border-color: #117a8b !important;
            }
            
            /* Forçar exibição do botão de certificado */
            .btn-certificado {
                display: inline-block !important;
                visibility: visible !important;
                opacity: 1 !important;
                position: relative !important;
                z-index: 9999 !important;
            }
            
            /* Ajustes para dropdown do menu */
            .navbar-nav .dropdown-menu {
                position: static;
                float: none;
                background-color: transparent;
                border: 0;
                box-shadow: none;
            }
            
            .navbar-nav .dropdown-item {
                color: rgba(255,255,255,.8);
                padding: 0.5rem 1rem;
            }
            
            .navbar-nav .dropdown-item:hover, 
            .navbar-nav .dropdown-item:focus {
                color: #fff;
                background-color: rgba(255,255,255,.1);
            }
        }

        /* Estilos para desktop (alinhado à esquerda) */
        @media (min-width: 576px) {
            .table td:not(:first-child):not(:last-child) {
                text-align: left !important;
            }
            
            .table td:last-child {
                text-align: center !important;
            }
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

            .btn:not(.table .btn) {
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
                        <a class="nav-link dropdown-toggle" href="#" id="drop01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Secretaria
                        </a>
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