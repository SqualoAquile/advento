<script src="<?php echo BASE_URL;?>/assets/js/ferramentas_condutores.js" type="text/javascript"></script>
<form id="ferramentas-form" autocomplete="off" novalidate>
    <h3 class="mt-5 mb-4">Ferramentas</h3>
    <div class="table-responsive mb-lg-5 mb-3">
        <table id="ferramentas" class="table table-striped table-hover table-fixed bg-white">
            <thead>
                <tr role="form" class="d-flex flex-column flex-lg-row">
                    <th class="col-lg">
                        <label class="font-weight-bold" for="ferramenta_nome">* Ferramenta</label>
                        <input type="text" class="form-control" id="ferramenta_nome" name="ferramenta_nome" data-mascara_validacao="false" required>
                    </th>
                    <th class="col-lg">
                        <label class="font-weight-bold" for="ferramenta_quant">* Quantidade</label>
                        <input type="text" class="form-control" id="ferramenta_quant" name="ferramenta_quant" data-mascara_validacao="numero" maxlength="5" required>
                    </th>
                    <th class="col-lg">
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