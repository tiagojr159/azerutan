<?php
include 'header.php';
?>
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
				<div class="card-header">Inscrições</div>
				<div class="card-body">
					<p class="mb-2">As inscrições para associação estão abertas. Ao se associar você poderá participar dos cursos oferecidos e obter o contrato de cultura necessário para apresentação de projetos.</p>

					<a href="http://localhost/azerutan/inscricao.php" class="btn btn-primary" target="_blank" rel="noopener" aria-label="Inscrever-se na Associação">Inscrever‑se na Associação</a>
				</div>
			</div>
		</div>
		<div class="col-lg-6 mb-3">
			<div class="card-az h-100 d-flex flex-column justify-content-between">
				<div class="card-header">Secretaria Azerutan</div>
				<div class="card-body d-flex align-items-center">
					<a href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC" target="_blank" rel="noopener" class="d-inline-flex align-items-center gap-12">
						<img src="<?php echo $baseIcones ?>whatsapp-icon.png" alt="WhatsApp" width="56" height="56" />
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

	<?php
	// Separar projetos abertos e fechados
	$projetosAbertos  = [];
	$projetosFechados = [];

	while ($row = mysqli_fetch_assoc($result)) {
		$status = strtolower(trim((string)($row['inscricoes_abertas'] ?? '')));
		$openValues = ['1','true','sim','s','aberto','open','yes','y'];
		$isOpen = in_array($status, $openValues, true);

		if ($isOpen) {
			$projetosAbertos[] = $row;
		} else {
			$projetosFechados[] = $row;
		}
	}
	?>

	<!-- ===== ABA 1: INSCRIÇÕES ABERTAS ===== -->
	<div class="mb-4">
		<h3 class="section-title text-success">Inscrições Abertas</h3>
		<div class="row">
			<?php if (count($projetosAbertos) === 0): ?>
				<p class="text-muted">Nenhum projeto com inscrições abertas no momento.</p>
			<?php endif; ?>

			<?php foreach ($projetosAbertos as $row): ?>
				<div class="col-6 col-md-4 col-lg-3 mb-3 d-flex">
					<a href="projeto.php?id=<?= urlencode($row['id']); ?>"
					   class="card-az proj-card w-100 text-decoration-none text-dark">
						<img class="proj-icon"
							 src="<?= $link_imagem_projeto; ?>../<?= $row['link_img']; ?>"
							 alt="<?= htmlspecialchars($row['nome'], ENT_QUOTES); ?>"
							 onerror="this.onerror=null;this.src='img/icones/projeto.png';" />
						<span class="proj-name"><?= htmlspecialchars($row['nome']); ?></span>
						<span class="proj-cat"><?= htmlspecialchars($row['categoria']); ?></span>
						<span class="proj-cat text-success">
							<b>ABERTO</b>
						</span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- ===== ABA 2: INSCRIÇÕES FECHADAS ===== -->
	<div class="mb-4">
		<h3 class="section-title text-danger">Inscrições Encerradas</h3>
		<div class="row">
			<?php if (count($projetosFechados) === 0): ?>
				<p class="text-muted">Nenhum projeto com inscrições encerradas.</p>
			<?php endif; ?>

			<?php foreach ($projetosFechados as $row): ?>
				<div class="col-6 col-md-4 col-lg-3 mb-3 d-flex">
					<a href="projeto.php?id=<?= urlencode($row['id']); ?>"
					   class="card-az proj-card w-100 text-decoration-none text-dark">
						<img class="proj-icon"
							 src="<?= $link_imagem_projeto; ?>../<?= $row['link_img']; ?>"
							 alt="<?= htmlspecialchars($row['nome'], ENT_QUOTES); ?>"
							 onerror="this.onerror=null;this.src='img/icones/projeto.png';" />
						<span class="proj-name"><?= htmlspecialchars($row['nome']); ?></span>
						<span class="proj-cat"><?= htmlspecialchars($row['categoria']); ?></span>
						<span class="proj-cat text-danger">
							<b>FECHADO</b>
						</span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>


<?php require_once 'footer.php'; ?>

<!-- JS -->
<script src="./template/jquery-3.3.1.slim.min.js"></script>
<script src="./template/popper.min.js"></script>
<script src="./template/bootstrap.min.js"></script>
</body>

</html>
