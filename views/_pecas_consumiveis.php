<script src="<?php echo BASE_URL;?>/assets/js/pecas_consumiveis.js" type="text/javascript"></script>
<form id="consumiveis-form" autocomplete="off" novalidate>
    <h3 class="mt-5 mb-4">Peças e Consumíveis</h3>
    <div class="table-responsive mb-lg-5 mb-3">
        <table id="consumiveis" class="table table-striped table-hover table-fixed bg-white">
            <thead>
                <tr role="form" class="d-flex flex-column flex-lg-row">
                    <th class="col-lg">
                        <label class="font-weight-bold" for="consumivel_tipo">* Tipo de Consumível</label>
                        <select id="consumivel_tipo" name="consumivel_tipo" class="form-control" data-mascara_validacao = "false" required >
                            <option value="" selected >Selecione</option>
                        </select>
                    </th>
                    <th class="col-lg">
                        <label class="font-weight-bold" for="consumivel_nome">* Consumível / Peça</label>
                        <input type="text" class="form-control" id="consumivel_nome" name="consumivel_nome" data-mascara_validacao="false" required>
                    </th>
                    <th class="col-lg">
                        <label class="font-weight-bold" for="consumivel_categoria">* Categoria</label>
                        <select id="consumivel_categoria" name="consumivel_categoria" class="form-control" data-mascara_validacao = "false" required >
                            <option value="" selected >Selecione</option>   
                        </select>
                    </th>
                    <th class="col-lg">
                        <label class="font-weight-bold" for="consumivel_val_km">* Validade (Km)</label>
                        <input type="text" class="form-control" id="consumivel_val_km" name="consumivel_val_km" data-mascara_validacao="numero" required data-podeZero='true'  maxlength="6">
                    </th>
                    <th class="col-lg">
                        <label class="font-weight-bold" for="consumivel_val_dias">* Validade (Dias)</label>
                        <input type="text" class="form-control" id="consumivel_val_dias" name="consumivel_val_dias" data-mascara_validacao="numero" required data-podeZero='true'  maxlength="3">
                    </th>
                    <th class="col-lg">
                        <label class="font-weight-bold" for="consumivel_horas_manut">* Tempo Manut. (h)</label>
                        <input type="text" class="form-control" id="consumivel_horas_manut" name="consumivel_horas_manut" data-mascara_validacao="numero" required maxlength="3">
                    </th>
                    <th class="col-lg-1">
                        <label>Ações</label>
                        <br>
                        <button type="submit" class="btn btn-primary">Incluir</a>
                    </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</form>