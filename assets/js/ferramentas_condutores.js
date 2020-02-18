$(function () {

    var $formContatos = $('table#ferramentas thead tr[role=form]'),
        lastInsertId = 0,
        botoes = `
            <td class="col-lg-2">
                <a href="javascript:void(0)" class="editar-contato btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="javascript:void(0)" class="excluir-contato btn btn-sm btn-danger">
                    <i class="fas fa-trash-alt"></i>
                </a>
            </td>
        `;

    // [Editar] Esse trecho de código abaixo serve para quando a pagina for carregada
    // Ler o campo hidden e montar a tabela com os contatos daquele registro
    Contatos().forEach(function (contato) {
        Popula(contato);
    });

    $('#ferramentas-form').submit(function (event) {
        
        event.preventDefault();

        var $form = $(this)[0],
            $fields = $($form).find('.form-control');

        // Desfocar os campos para validar
        $fields.trigger('blur');

        if ($form.checkValidity() && !$($form).find('.is-invalid').length) {

            Save();

            // Limpar formulario
            $form.reset();
            $($form).removeClass('was-validated');
            
            $fields
                .removeClass('is-valid is-invalid')
                .removeAttr('data-anterior');

            $fields.first().focus();
        } else {
            $($form).addClass('was-validated');

            // Da foco no primeiro campo com erro
            $($form).find('.is-invalid, :invalid').first().focus();
        }
    });

    // Retorna um array de contatos puxados do campo hidden com o atributo nome igual a contatos
    function Contatos() {
        var returnContatos = [];
        if ($('[name=ferramentas]') && $('[name=ferramentas]').val().length) {
            var contatos = $('[name=ferramentas]').val().split('[');
            for (var i = 0; i < contatos.length; i++) {
                var contato = contatos[i];
                if (contato.length) {
                    contato = contato.replace(']', '');
                    var dadosContato = contato.split(' * ');
                    returnContatos.push(dadosContato);
                }
            }
        };
        return returnContatos;
    };

    // Escreve o html na tabela
    function Popula(values) {

        if (!values) return;

        var currentId = $formContatos.attr('data-current-id'),
            tds = '';

        // Coloca a tag html TD em volta de cada valor vindo do form de contatos
        values.forEach(value => tds += `<td class="col-lg text-truncate">` + value + `</td>`);

        if (!currentId) {
            // Se for undefined então o contato está sendo criado

            // Auto incrementa os ID's dos contatos
            lastInsertId += 1;

            $('#ferramentas tbody')
                .prepend('<tr class="d-flex flex-column flex-lg-row" data-id="' + lastInsertId + '">' + tds + botoes + '</tr>');

        } else {
            // Caso tenha algum valor é por que o contato está sendo editado

            $('#ferramentas tbody tr[data-id="' + currentId + '"]')
                .html(tds + botoes);

            // Seta o data id como undefined para novos contatos poderem ser cadastrados
            $formContatos.removeAttr('data-current-id');
        }

        $('.editar-contato').bind('click', Edit);
        $('.excluir-contato').bind('click', Delete);
    };

    // Pega as linhas da tabela auxiliar e manipula o hidden de contatos
    function SetInput() {
        var content = '';
        $('#ferramentas tbody tr').each(function () {
            var par = $(this).closest('tr');
            var tdNome = par.children("td:nth-child(1)");
            var tdQuant = par.children("td:nth-child(2)");

            content += '[' + tdNome.text() + ' * ' + tdQuant.text() + ']';
        });

        $('[name=ferramentas]')
            .val(content)
            .attr('data-anterior-aux', content)
            .change();
    };

    // Delete contato da tabela e do hidden
    function Delete() {
        var par = $(this).closest('tr');
        par.remove();
        SetInput();
    };

    // Seta no form o contato clicado para editar, desabilita os botoes de ações deste contato e seta o id desse contato
    // no form dos contatos
    function Edit() {

        // Volta para válido todos os botoões de editar e excluir
        $('table#ferramentas tbody tr .btn')
            .removeClass('disabled');


        var $par = $(this).closest('tr'),
            tdNome = $par.children("td:nth-child(1)"),
            tdQuant = $par.children("td:nth-child(2)");

        // Desabilita ele mesmo e os botões irmãos de editar e excluir da linha atual
        $par
            .find('.btn')
            .addClass('disabled');

        $('input[name=ferramenta_nome]').val(tdNome.text()).attr('data-anterior', tdNome.text()).focus();
        $('input[name=ferramenta_quant]').val(tdQuant.text()).attr('data-anterior', tdQuant.text());

        $('table#ferramentas thead tr[role=form]')
            .attr('data-current-id', $par.attr('data-id'))
            .find('.is-valid, .is-invalid')
            .removeClass('is-valid is-invalid');
    };

    // Ao dar submit neste form, chama essa função que pega os dados do formula e Popula a tabela
    function Save() {

        Popula([
            $('input[name=ferramenta_nome]').val(),
            $('input[name=ferramenta_quant]').val()
        ]);

        SetInput();
    };

    // Validação se o nome já existe entre os contatos daquela tabela auxiliar
    $('[name=ferramenta_nome]').blur(function () {

        var $this = $(this),
            contatos = Contatos(),
            nomes = [];

        $this.removeClass('is-valid is-invalid');
        $this.siblings('.invalid-feedback').remove();

        if (contatos) {

            // Posição 0 é o nome do contato
            contatos.forEach(contato => nomes.push(contato[0].toLowerCase()));

            if ($this.val()) {

                var value = $this.val().toLowerCase(),
                    dtAnteriorLower = $this.attr('data-anterior') ? $this.attr('data-anterior') : '';

                if (dtAnteriorLower.toLowerCase() != value) {

                    $this.removeClass('is-invalid is-valid');
                    $this[0].setCustomValidity('');
                    
                    if (nomes.indexOf(value) == -1) {
                        // Não existe, pode seguir

                        $this.addClass('is-valid');

                        $this[0].setCustomValidity('');
                    } else {
                        // Já existe, erro

                        $this.addClass('is-invalid');

                        $this[0].setCustomValidity('invalid');

                        $this.after('<div class="invalid-feedback">Já existe uma ferramenta com este nome</div>');
                    }
                }
            }

        }
    });

    $( "#ferramenta_nome" ).autocomplete({
        source: function( request, response ) {
        $.ajax( {
            url: baselink + '/ajax/nomeFerramentas',
            type:"POST",
            dataType: "json",
            data: {
            term: request.term
            },
            success: function( data ) {
                response( data );
            }
        } );
        },
        minLength: 2,
        select: function( event, ui ) {
        },
        response: function( event, ui ) {
        }
        }).focus(function(event) {
        var termo = "";
        termo = $(this).val().trim();
        $(this).autocomplete( "search" , termo );
        });
    
        $( "#ferramenta_nome" ).parent('div').addClass('ui-widget');

        $("#ferramenta_nome").on('click',function(){
            $("#ferramenta_nome").keyup();
        });


});