$(function () {
    $.ajax({
        url: baselink + '/ajax/buscaCategoriasConsumiveis',
        type: 'POST',
        data: {
            tipo_veiculo: 'passeio' 
        },
        dataType: 'json',
        success: function (dado) {
            // console.log(dado);
            if(dado.length > 0){
                $('#consumivel_categoria').empty().append('<option value="" selected  >Selecione</option>') 
                for(var i=0; i< dado.length; i++){  
                    $('#consumivel_categoria').append("<option value='"+dado[i]['value']+"' >"+dado[i]['value']+"</option>")
                }
            }else{
                alert('Não foram encontradas categorias. Cadastre-as!');  
            }                                           
        }
    });

    $('#caminhao').change(function(){
        if( $('#caminhao').is(':checked') == true ){
            $('#consumivel_tipo').empty()
            .append('<option value="" selected  >Selecione</option>') 
            .append("<option value='peça' >Peça</option>")
            .append("<option value='equipamento' >Equipamento</option>");
                
        }else{
            $('#consumivel_tipo').empty()
            .append('<option value="" selected  >Selecione</option>') 
            .append("<option value='peça' >Peça</option>");
        }

    });

    $('#passeio').change(function(){
        if( $('#caminhao').is(':checked') == true ){
            $('#consumivel_tipo').empty()
            .append('<option value="" selected  >Selecione</option>') 
            .append("<option value='peça' >Peça</option>")
            .append("<option value='equipamento' >Equipamento</option>");
                
        }else{
            $('#consumivel_tipo').empty()
            .append('<option value="" selected  >Selecione</option>') 
            .append("<option value='peça' >Peça</option>");
        }

    }).change();

    
    var $formContatos = $('table#consumiveis thead tr[role=form]'),
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

    $('#consumiveis-form').submit(function (event) {
        
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
        if ($('[name=consumiveis]') && $('[name=consumiveis]').val().length) {
            var contatos = $('[name=consumiveis]').val().split('[');
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

            $('#consumiveis tbody')
                .prepend('<tr class="d-flex flex-column flex-lg-row" data-id="' + lastInsertId + '">' + tds + botoes + '</tr>');

        } else {
            // Caso tenha algum valor é por que o contato está sendo editado

            $('#consumiveis tbody tr[data-id="' + currentId + '"]')
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
        $('#consumiveis tbody tr').each(function () {
            var par = $(this).closest('tr');
            var tdTipo = par.children("td:nth-child(1)");
            var tdNome = par.children("td:nth-child(2)");
            var tdCategoria = par.children("td:nth-child(3)");
            var tdValidadeKm = par.children("td:nth-child(4)");
            var tdValidadeDias = par.children("td:nth-child(5)");
            var tdHorasManut = par.children("td:nth-child(6)");

            content += '[' + tdTipo.text() + ' * ' + tdNome.text() + ' * ' + tdCategoria.text() + ' * ' + tdValidadeKm.text() + ' * ' + tdValidadeDias.text() + ' * ' + tdHorasManut.text() + ']';
        });

        $('[name=consumiveis]')
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
        $('table#consumiveis tbody tr .btn')
            .removeClass('disabled');


        var $par = $(this).closest('tr'),
            tdTipo = $par.children("td:nth-child(1)"),
            tdNome = $par.children("td:nth-child(2)"),
            tdCategoria = $par.children("td:nth-child(3)"),
            tdValidadeKm = $par.children("td:nth-child(4)"),
            tdValidadeDias = $par.children("td:nth-child(5)"),
            tdHorasManut = $par.children("td:nth-child(6)");

        // Desabilita ele mesmo e os botões irmãos de editar e excluir da linha atual
        $par
            .find('.btn')
            .addClass('disabled');

        $('select[name=consumivel_tipo]').val(tdTipo.text()).attr('data-anterior', tdTipo.text());
        $('input[name=consumivel_nome]').val(tdNome.text()).attr('data-anterior', tdNome.text()).focus();
        $('select[name=consumivel_categoria]').val(tdCategoria.text()).attr('data-anterior', tdCategoria.text());
        $('input[name=consumivel_val_km]').val(tdValidadeKm.text()).attr('data-anterior', tdValidadeKm.text());
        $('input[name=consumivel_val_dias]').val(tdValidadeDias.text()).attr('data-anterior', tdValidadeDias.text());
        $('input[name=consumivel_horas_manut]').val(tdHorasManut.text()).attr('data-anterior', tdHorasManut.text());

        $('table#consumiveis thead tr[role=form]')
            .attr('data-current-id', $par.attr('data-id'))
            .find('.is-valid, .is-invalid')
            .removeClass('is-valid is-invalid');
    };

    // Ao dar submit neste form, chama essa função que pega os dados do formula e Popula a tabela
    function Save() {

        Popula([
            $('select[name=consumivel_tipo]').val(),
            $('input[name=consumivel_nome]').val(),
            $('select[name=consumivel_categoria]').val(),
            $('input[name=consumivel_val_km]').val(),
            $('input[name=consumivel_val_dias]').val(),
            $('input[name=consumivel_horas_manut]').val()
        ]);

        SetInput();
    };

    // Validação se o nome já existe entre os contatos daquela tabela auxiliar
    $('[name=consumivel_nome]').blur(function () {

        var $this = $(this),
            contatos = Contatos(),
            nomes = [];

        $this.removeClass('is-valid is-invalid');
        $this.siblings('.invalid-feedback').remove();

        if (contatos) {

            // Posição 0 é o nome do contato
            contatos.forEach(contato => nomes.push(contato[1].toLowerCase()));

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

                        $this.after('<div class="invalid-feedback">Já existe um consumível com este nome</div>');
                    }
                }
            }

        }
    });


    $( "#consumivel_nome" ).autocomplete({
        source: function( request, response ) {
            var id, idaux, tipo_veic;
                idaux = window.location.href;
                idaux = idaux.split('/');
                id = idaux.slice(-1);
            if( $('#caminhao').is(':checked') == true ){
                tipo_veic = 'caminhao';
            }else{
                tipo_veic = 'passeio';
            }    
            if ( $( "#consumivel_tipo" ).val() != '' ){
                $.ajax( {
                    url: baselink + '/ajax/buscaConsumiveis',
                    type:"POST",
                    dataType: "json",
                    data: {
                        term: request.term,
                        tipo_veic: tipo_veic,
                        tipo_consu: $( "#consumivel_tipo" ).val(),
                        id_veic: id[0]
                    },
                    success: function( data ) {
                        $(this).attr('data-categoria') == '';
                        response( data );
                    }
                } );
            }else{
                $( "#consumivel_tipo" ).focus();
                return false;
            }
        },
        minLength: 2,
        select: function( event, ui ) {
            $('#consumivel_categoria').val('');
            $('#consumivel_categoria').val(ui.item.categoria); 
        },
        response: function( event, ui ) {
        }
        }).focus(function(event) {
        var termo = "";
        termo = $(this).val().trim();
        $(this).autocomplete( "search" , termo );
        }).blur(function(){
            if ( $(this).val() == '' ){
                $( "#consumivel_categoria" ).val('').removeClass('is-valid is-invalid');
            }
        });
    
        $( "#consumivel_nome" ).parent('div').addClass('ui-widget');

        $("#consumivel_nome").on('click',function(){
            $("#consumivel_nome").keyup();
        });

});