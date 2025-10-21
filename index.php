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
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" id="drop01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
					<input class="form-control mr-2" type="text" placeholder="Pesquisar" aria-label="Pesquisar" />
					<button class="btn btn-outline-light my-2 my-sm-0" type="submit">Buscar</button>
				</form>
			</div>
		</div>
	</nav>

	<!-- Hero -->
	<div class="container">
		<section class="hero">
			<div class="row align-items-center">
				<div class="col-lg-7">
					<h1 class="mb-2">Associação Cultural Azerutan</h1>
					<p class="mb-0">
						Inscrições, renovação e lista de candidatos do grupo de teatro <strong>Azerutan</strong>.
					</p>
				</div>
				<div class="col-lg-5 text-lg-right mt-3 mt-lg-0">
					<span class="status-chip">INSCRIÇÕES ABERTAS</span>
				</div>
			</div>
		</section>
	</div>



	<!-- Cronograma + WhatsApp -->
	<div class="container mt-3">
		<div class="row">
			<div class="col-lg-6 mb-3">
				<div class="card-az h-100">
					<div class="card-header">Cronograma</div>
					<div class="card-body">
						<ul class="list-unstyled mb-0">
							<li class="mb-2"><strong>Renovação</strong>: 1º a 30 de Janeiro</li>
							<li><strong>Nova Matrícula</strong>: 1º a 29 de Fevereiro</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-lg-6 mb-3">
				<div class="card-az h-100 d-flex flex-column justify-content-between">
					<div class="card-header">Secretaria Azerutan</div>
					<div class="card-body d-flex align-items-center">
						<a href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC" target="_blank" rel="noopener" class="d-inline-flex align-items-center gap-12">
							<img src="images/icone/whatsapp-icon.png" alt="WhatsApp" width="56" height="56" />
							<span>Entre no grupo no WhatsApp para tirar dúvidas</span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Projetos (ícones) -->
	<div class="container mt-32">
		<h2 class="section-title">Projetos</h2>
		<div class="row">
			<?php while ($row = mysqli_fetch_assoc($result)) {
				$cat = strtolower(preg_replace('/[^a-z0-9_-]+/i', '', $row['categoria'] ?? 'projeto'));
				$icon = "img/icones/{$cat}.png";
			?>
				<div class="col-6 col-md-4 col-lg-3 mb-3 d-flex">
					<a href="projeto.php?id=<?= urlencode($row['id']); ?>"
						class="card-az proj-card w-100 text-decoration-none text-dark">
						<img class="proj-icon"
							src="<?= $link_imagem_projeto; ?><?=$row['link_img']; ?>"
							alt="<?= htmlspecialchars($row['nome'], ENT_QUOTES); ?>"
							onerror="this.onerror=null;this.src='img/icones/projeto.png';" />
						<div class="proj-name"><?= htmlspecialchars($row['nome']); ?></div>
						<div class="proj-cat"><?= htmlspecialchars($row['categoria']); ?></div>
					</a>
				</div>
			<?php } ?>
		</div>
	</div>

	<!-- Realização -->
	<div class="container mt-32">
		<div class="card-az">
			<div class="card-body text-center">
				<h3 class="mb-3" style="color:var(--primary-700); font-weight:700">Realização</h3>
				<img src="../projeto/images/AssocAzerutan.png" style="max-width:520px; width:100%; height:auto;" alt="Associação Azerutan" />
			</div>
		</div>
	</div>

	<footer class="container mt-32 mb-4 text-center" style="color:var(--muted)">
		© Azerutan 2017–<?php echo date('Y'); ?>
	</footer>

	<!-- JS -->
	<script src="./template/jquery-3.3.1.slim.min.js"></script>
	<script src="./template/popper.min.js"></script>
	<script src="./template/bootstrap.min.js"></script>
</body>

</html>