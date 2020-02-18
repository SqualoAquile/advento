$(function () {
   
   if ($('#placa').attr('data-anterior') == '' ){
    // Tá no adicionar
    $('#consumiveis-form').hide();

   }else{
    // Tá no Editar
    $('#consumiveis-form').show();
   }
    
});