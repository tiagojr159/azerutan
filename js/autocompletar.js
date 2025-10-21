

function validacaoCadastro() {


	var nome = document.getElementById("nome");
	var telefone = document.getElementById("telefone");
	var cidade = document.getElementById("autocomplete-ajax-cidade");
	var grupo = document.getElementById("autocomplete-ajax-grupo");
	var cargo = document.getElementById("cargo");
	var rua = document.getElementById("autocomplete-ajax-rua");

	if (nome.value == '') {
		alert('Por favor preencher um NOME');
		return false;
	}

	if (telefone.value == '') {
		alert('Por favor preencher um TELEFONE');
		return false;
	}

	if (cidade.value == '') {
		alert('Por favor preencher um CIDADE');
		return false;
	}

	if (grupo.value == '') {
		alert('Por favor preencher um GRUPO');
		return false;
	}

	if (cargo.value == '') {
		alert('Por favor preencher um CARGO');
		return false;
	}

	if (rua.value == '') {
		alert('Por favor preencher um RUA');
		return false;
	}

	document.getElementById("formCadastrar").submit();

}






$(function () {
    'use strict';

    var listaInstituicao = [];

    $.get("crud_consulta.php?action=consultaNome", function (res) {
        var linhas = res.split(";"); // Separa as linhas

        linhas.forEach(function (linha) {
            if (linha.trim() !== "") { // Garante que não há linhas vazias
                var partes = linha.split("|"); // Divide ID e Nome
                var id = partes[0];
                var nome = partes[1];

                listaInstituicao.push({ value: nome, data: id }); // Salva nome e ID
            }
        });
    }).done(function () {
        $('#autocomplete-ajax-bairro').autocomplete({
            lookup: listaInstituicao,
            lookupFilter: function (suggestion, originalQuery, queryLowerCase) {
                var re = new RegExp('\\b' + $.Autocomplete.utils.escapeRegExChars(queryLowerCase), 'gi');
                return re.test(suggestion.value);
            },
            onSelect: function (suggestion) {
                $('#nome_colaborador').val(suggestion.value);
                $('#id_colaborador').val(suggestion.data);
                $('#selction-ajax-bairro').html('Você selecionou: ' + suggestion.value + ', ID: ' + suggestion.data);
            },
            onHint: function (hint) {
                $('#autocomplete-ajax-x-bairro').val(hint);
            },
            onInvalidateSelection: function () {
                $('#selction-ajax-bairro').html('Nenhum selecionado');
            }
        });
    });
});








