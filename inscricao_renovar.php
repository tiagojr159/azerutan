<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inscrição - Paixão de Cristo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="tts.js"></script>
  <link rel="stylesheet" href="styles-autocomplete.css">
  <style>
    body {
      background: url('aaa.jpg') no-repeat center center fixed;
      background-size: cover;
      background-color: rgba(236, 239, 241, 0.9);
      background-blend-mode: lighten;
      font-family: 'Roboto', sans-serif;
      color: #333;
    }

    .container-custom {
      max-width: 900px;
      background-color: #FFFFFF;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      padding: 30px;
      margin: 100px auto 30px auto;
    }

    .btn-primary {
      background-color: #1976D2;
      border-color: #1976D2;
    }

    .btn-primary:hover {
      background-color: #1565C0;
      border-color: #1565C0;
    }

    .btn-success {
      background-color: #4CAF50;
      border-color: #4CAF50;
    }

    .btn-success:hover {
      background-color: #43A047;
      border-color: #43A047;
    }

    h2 {
      color: #2E7D32;
      font-weight: bold;
    }

    label {
      font-weight: bold;
    }

    .border-danger {
      border: 2px solid #D32F2F !important;
    }

    @media (max-width: 768px) {
      .container-custom {
        padding: 20px 15px;
      }
    }
  </style>
</head>

<body>

  <!-- Navbar adicionada -->
  <nav class="navbar navbar-expand-md navbar-dark fixed-top" style="background-color: #2E7D32;">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Azerutan</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav me-auto mb-2 mb-md-0">
          <li class="nav-item">
            <a class="nav-link active" href="index.php">Início</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Opções</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="form_colaborador.php?novo=1">Nova Matrícula</a></li>
              <li><a class="dropdown-item" href="inscricao_renovar.php">Renovar Matrícula</a></li>
              <li><a class="dropdown-item" href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC">Secretaria Azerutan</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-custom mt-5 pt-5">
    <div class="text-center">
      <img src="topo_paixao.jpg" class="img-fluid mb-3" alt="Topo Paixão">
      <a href='lista.php' class='btn btn-success mb-3'>Voltar</a>
      <img src="../projeto/images/AssocAzerutan.png" class="img-fluid my-3" alt="Logo Azerutan">
    </div>

    <h2 class="text-center">Inscrição da Paixão de Cristo</h2>
    <p class="fw-bold">Termo de participação:</p>
    <ul>
      <li>Estou ciente de que minha participação no espetáculo é voluntária.</li>
      <li>Estou ciente de que não há vínculo empregatício.</li>
      <li>Aceito o uso da minha imagem para divulgação do espetáculo.</li>
      <li>Minhas informações são verdadeiras.</li>
    </ul>

    <form action="crud_consulta.php" method="get" name="dados" id="form-inscricao" class="mt-4">
      <input type="hidden" name="action" value="valida_renovacao">
      <input type="hidden" name="id_colaborador" id="id_colaborador">

      <div class="mb-3">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="autocomplete-ajax-bairro" class="form-control"
          placeholder="Digite seu nome" style="text-transform: uppercase;">
      </div>

      <div class="mb-4">
        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" name="data_nascimento" id="data_nascimento" class="form-control">
      </div>

      <button type="submit" name="editar" class="btn btn-primary w-100">Renovar Matrícula</button>
    </form>
  </div>

  <script src="js/jquery.autocomplete.js"></script>
  <script src="js/autocompletar.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    $(document).ready(function () {
      $("#form-inscricao").submit(function (e) {
        var nome = $("#autocomplete-ajax-bairro").val().trim();
        var dataNascimento = $("#data_nascimento").val().trim();

        if (nome === "") {
          alert("Por favor, escolha seu Nome no campo nome antes de enviar o formulário.");
          $("#autocomplete-ajax-bairro").addClass("border-danger");
          e.preventDefault();
        }

        if (dataNascimento === "") {
          alert("Por favor, preencha a Data de Nascimento antes de enviar o formulário.");
          $("#data_nascimento").addClass("border-danger");
          e.preventDefault();
        } else {
          $("#data_nascimento").removeClass("border-danger");
        }
      });

      $('#autocomplete-ajax-bairro').autocomplete({
        serviceUrl: 'crud_consulta.php?action=consultaNome',
        dataType: 'json',
        onSelect: function (suggestion) {
          $('#id_colaborador').val(suggestion.data);
        }
      });
    });
  </script>

</body>

</html>
