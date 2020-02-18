<?php
class Veiculos extends model {

    protected $table = "veiculos";
    protected $permissoes;
    protected $shared;

    public function __construct() {
        $this->permissoes = new Permissoes();
        $this->shared = new Shared($this->table);
    }
    
    public function infoItem($id) {
        $array = array();
        $arrayAux = array();

        $id = addslashes(trim($id));
        $sql = "SELECT * FROM " . $this->table . " WHERE id='$id' AND situacao = 'ativo'";      
        $sql = self::db()->query($sql);

        if($sql->rowCount()>0){
            $array = $sql->fetch(PDO::FETCH_ASSOC);
            $array = $this->shared->formataDadosDoBD($array);
        }
        
        return $array; 
    }

    public function adicionar($request) {
        
        $ipcliente = $this->permissoes->pegaIPcliente();
        $request["alteracoes"] = ucwords($_SESSION["nomeUsuario"])." - $ipcliente - ".date('d/m/Y H:i:s')." - CADASTRO";
        
        $request["situacao"] = "ativo";

        $keys = implode(",", array_keys($request));

        $values = "'" . implode("','", array_values($this->shared->formataDadosParaBD($request))) . "'";

        $sql = "INSERT INTO " . $this->table . " (" . $keys . ") VALUES (" . $values . ")";
        
        self::db()->query($sql);

        $erro = self::db()->errorInfo();

        if (empty($erro[2])){

            $_SESSION["returnMessage"] = [
                "mensagem" => "Registro inserido com sucesso!",
                "class" => "alert-success"
            ];
        } else {
            $_SESSION["returnMessage"] = [
                "mensagem" => "Houve uma falha, tente novamente! <br /> ".$erro[2],
                "class" => "alert-danger"
            ];
        }
    }

    public function editarOriginal($id, $request) {

        if(!empty($id)){

            $id = addslashes(trim($id));

            $ipcliente = $this->permissoes->pegaIPcliente();
            $hist = explode("##", addslashes($request['alteracoes']));

            if(!empty($hist[1])){ 
                $request['alteracoes'] = $hist[0]." | ".ucwords($_SESSION["nomeUsuario"])." - $ipcliente - ".date('d/m/Y H:i:s')." - ALTERAÇÃO >> ".$hist[1];
            }else{
                $_SESSION["returnMessage"] = [
                    "mensagem" => "Houve uma falha, tente novamente! <br /> Registro sem histórico de alteração.",
                    "class" => "alert-danger"
                ];
                return false;
            }

            /////// separar os item das peças e consumiveis e salvar numa tabela separada além de salvar num campo text dessa tabela


            ////////////////////////////////////////////////////////////////////////////////////////
            $request = $this->shared->formataDadosParaBD($request);

            // Cria a estrutura key = 'valor' para preparar a query do sql
            $output = implode(', ', array_map(
                function ($value, $key) {
                    return sprintf("%s='%s'", $key, $value);
                },
                $request, //value
                array_keys($request)  //key
            ));

            $sql = "UPDATE " . $this->table . " SET " . $output . " WHERE id='" . $id . "'";
             
            self::db()->query($sql);

            $erro = self::db()->errorInfo();

            if (empty($erro[2])){

                $_SESSION["returnMessage"] = [
                    "mensagem" => "Registro alterado com sucesso!",
                    "class" => "alert-success"
                ];
            } else {
                $_SESSION["returnMessage"] = [
                    "mensagem" => "Houve uma falha, tente novamente! <br /> ".$erro[2],
                    "class" => "alert-danger"
                ];
            }
        }
    }
    
    public function excluirOriginal($id){
        if(!empty($id)) {

            $id = addslashes(trim($id));

            //se não achar nenhum usuario associado ao grupo - pode deletar, ou seja, tornar o cadastro situacao=excluído
            $sql = "SELECT alteracoes FROM ". $this->table ." WHERE id = '$id' AND situacao = 'ativo'";
            $sql = self::db()->query($sql);
            
            if($sql->rowCount() > 0){  

                $sql = $sql->fetch();
                $palter = $sql["alteracoes"];
                $ipcliente = $this->permissoes->pegaIPcliente();
                $palter = $palter." | ".ucwords($_SESSION["nomeUsuario"])." - $ipcliente - ".date('d/m/Y H:i:s')." - EXCLUSÃO";

                $sqlA = "UPDATE ". $this->table ." SET alteracoes = '$palter', situacao = 'excluido' WHERE id = '$id' ";
                self::db()->query($sqlA);

                $erro = self::db()->errorInfo();

                if (empty($erro[2])){

                    $_SESSION["returnMessage"] = [
                        "mensagem" => "Registro deletado com sucesso!",
                        "class" => "alert-success"
                    ];
                } else {
                    $_SESSION["returnMessage"] = [
                        "mensagem" => "Houve uma falha, tente novamente! <br /> ".$erro[2],
                        "class" => "alert-danger"
                    ];
                }
            }
        }
    }

    public function nomeClientes($termo){
        // echo "aquiiii"; exit;
        $array = array();
        // 
        $sql1 = "SELECT `id`, `nome` FROM `generico` WHERE situacao = 'ativo' AND nome LIKE '%$termo%' ORDER BY nome ASC";

        $sql1 = self::db()->query($sql1);
        $nomesAux = array();
        $nomes = array();
        if($sql1->rowCount() > 0){  
            
            $nomesAux = $sql1->fetchAll(PDO::FETCH_ASSOC);

            foreach ($nomesAux as $key => $value) {
                $nomes[] = array(
                    "id" => $value["id"],
                    "label" => $value["nome"],
                    "value" => $value["nome"]
                );     
            }

        }

        // fazer foreach e criar um array que cada elemento tenha id: label: e value:
        // print_r($nomes); exit; 
        $array = $nomes;

       return $array;
    }

    public function buscaConsumiveis($request){
        // print_r($termo); exit;
        // echo "aquiiii"; exit;
        $termo = trim(addslashes($request['term']));
        $tipo_veic = trim(addslashes($request['tipo_veic']));
        $tipo_consu = trim(addslashes($request['tipo_consu']));
        $id_veic = trim(addslashes($request['id_veic']));

        $array = array();
        // 
        $sql1 = "SELECT * FROM `consumiveis` WHERE situacao = 'ativo' AND tipo_veiculo = '$tipo_veic' AND tipo_consumivel = '$tipo_consu' AND id_veiculo = '$id_veic' AND ( peca LIKE '%$termo%' OR categoria LIKE '%$termo%' ) ORDER BY peca ASC";

        // echo $sql1; exit;
        $sql1 = self::db()->query($sql1);
        $nomesAux = array();
        $nomes = array();
        if($sql1->rowCount() > 0){  
            
            $nomesAux = $sql1->fetchAll(PDO::FETCH_ASSOC);

            // print_r($nomesAux); exit;
            foreach ($nomesAux as $key => $value) {
                $nomes[] = array(
                    "id" => $value["id"],
                    "label" => $value["peca"]." -- ".$value["categoria"],
                    "value" => $value["peca"],
                    "categoria" => $value["categoria"],
                );     
            }
        }
        return $nomes;
    }    

    public function buscaCategorias($termo){
        // print_r($termo); exit;
        // echo "aquiiii"; exit;
        $array = array();
        // 
        $sql1 = "SELECT * FROM `categoriaconsumiveis` WHERE situacao = 'ativo' AND ( nome LIKE '%$termo%' ) ORDER BY nome ASC";

        // echo $sql1; exit;
        $sql1 = self::db()->query($sql1);
        $nomesAux = array();
        $nomes = array();
        if($sql1->rowCount() > 0){  
            
            $nomesAux = $sql1->fetchAll(PDO::FETCH_ASSOC);

            // print_r($nomesAux); exit;
            foreach ($nomesAux as $key => $value) {
                $nomes[] = array(
                    "id" => $value["id"],
                    "label" => $value["nome"],
                    "value" => $value["nome"],
                );     
            }
        }
        return $nomes;  
    }
    
    public function buscaCategoriasConsumiveis(){
        // print_r($termo); exit;
        // echo "aquiiii"; exit;
        $array = array();
        // 
        $sql1 = "SELECT * FROM `categoriaconsumiveis` WHERE situacao = 'ativo' ORDER BY nome ASC";

        // echo $sql1; exit;
        $sql1 = self::db()->query($sql1);
        $nomesAux = array();
        $nomes = array();
        if($sql1->rowCount() > 0){  
            
            $nomesAux = $sql1->fetchAll(PDO::FETCH_ASSOC);

            // print_r($nomesAux); exit;
            foreach ($nomesAux as $key => $value) {
                $nomes[] = array(
                    "id" => $value["id"],
                    "label" => $value["nome"],
                    "value" => $value["nome"],
                );     
            }
        }
        return $nomes;  
    }
    ////////////////////////////////////////////////////////////////////////////////

    public function editar($id, $request) {

        if(!empty($id)){

            $id = addslashes(trim($id));

            $erroItensBooleanExcluir = $this->excluirItens($id);
            if ( $erroItensBooleanExcluir == false ){
                $_SESSION["returnMessage"] = [
                    "mensagem" => "Houve uma falha, tente novamente! <br /> ".$erro[2],
                    "class" => "alert-danger"
                ];
                return false;
            }
            
            $erroItensBooleanAdicionar = $this->adicionarItens($request, $id);
            if ( $erroItensBooleanAdicionar == false ){
                $_SESSION["returnMessage"] = [
                    "mensagem" => "Houve uma falha, tente novamente! <br /> ".$erro[2],
                    "class" => "alert-danger"
                ];
                return false;
            }

            $ipcliente = $this->permissoes->pegaIPcliente();
            $hist = explode("##", addslashes($request['alteracoes']));

            if(!empty($hist[1])){ 
                $request['alteracoes'] = $hist[0]." | ".ucwords($_SESSION["nomeUsuario"])." - $ipcliente - ".date('d/m/Y H:i:s')." - ALTERAÇÃO >> ".$hist[1];
            }else{
                $_SESSION["returnMessage"] = [
                    "mensagem" => "Houve uma falha, tente novamente! <br /> Registro sem histórico de alteração.",
                    "class" => "alert-danger"
                ];
                return false;
            }

            $request = $this->shared->formataDadosParaBD($request);

            // Cria a estrutura key = 'valor' para preparar a query do sql
            $output = implode(', ', array_map(
                function ($value, $key) {
                    return sprintf("%s='%s'", $key, $value);
                },
                $request, //value
                array_keys($request)  //key
            ));

            $sql = "UPDATE " . $this->table . " SET " . $output . " WHERE id='" . $id . "'";
            
            // print_r($sql); exit;
            self::db()->query('START TRANSACTION;');
            self::db()->query($sql);

            $erro = self::db()->errorInfo();
            
            if( empty($erro[2]) ){
                            
                self::db()->query('COMMIT;');
                $_SESSION["returnMessage"] = [
                    "mensagem" => "Registro alterado com sucesso!",
                    "class" => "alert-success"
                ];

            }else{

                self::db()->query('ROLLBACK;');
                $_SESSION["returnMessage"] = [
                    "mensagem" => "Houve uma falha, tente novamente! <br /> ".$erro[2],
                    "class" => "alert-danger"
                ];
            }
        }
    }

    public function excluir($id){
        if(!empty($id)) {
            $id = addslashes(trim($id));
            //se não achar nenhum usuario associado ao grupo - pode deletar, ou seja, tornar o cadastro situacao=excluído
            $sql = "SELECT alteracoes FROM veiculos WHERE id = '$id' AND situacao = 'ativo'";
            $sql = self::db()->query($sql);
            
            if($sql->rowCount() > 0){  
                $sql = $sql->fetch();
                $palter = $sql["alteracoes"];
                $ipcliente = $this->permissoes->pegaIPcliente();
                $palter = $palter." | ".ucwords($_SESSION["nomeUsuario"])." - $ipcliente - ".date('d/m/Y H:i:s')." - EXCLUSÃO";
                $sqlA = "UPDATE veiculos SET alteracoes = '$palter', situacao = 'excluido' WHERE id = '$id' ";

                self::db()->query('START TRANSACTION;');
                self::db()->query($sqlA);

                $erroA = self::db()->errorInfo();
                if ( empty($erroA[2]) ){
                    
                    $sqlB = "UPDATE consumiveis SET situacao = 'excluido' WHERE id_veiculo = '$id' ";
                    self::db()->query($sqlB);
                    $erroB = self::db()->errorInfo();

                    if( empty($erroB[2]) ){
                            
                        self::db()->query('COMMIT;');
                        $_SESSION["returnMessage"] = [
                            "mensagem" => "Registro deletado com sucesso!",
                            "class" => "alert-success"
                        ];
        
                    }else{
        
                        self::db()->query('ROLLBACK;');
                        $_SESSION["returnMessage"] = [
                            "mensagem" => "Houve uma falha, tente novamente! <br /> ".$erro[2],
                            "class" => "alert-danger"
                        ];
                    }
                }else{

                    self::db()->query('ROLLBACK;');
                    $_SESSION["returnMessage"] = [
                        "mensagem" => "Houve uma falha, tente novamente! <br /> ".$erro[2],
                        "class" => "alert-danger"
                    ];
                }    
            }
        }
    }

    private function excluirItens($id_veiculo) {

        $returnItens = false;

        if(!empty($id_veiculo)) {

            $id_veiculo = addslashes(trim($id_veiculo));

            $sql = "DELETE FROM consumiveis WHERE id_veiculo = '$id_veiculo' ";
            self::db()->query('START TRANSACTION;');
            self::db()->query($sql);
            $erro = self::db()->errorInfo();

            if( empty($erro[2]) ){
                            
                self::db()->query('COMMIT;');
                $returnItens = true;

            }else{

                self::db()->query('ROLLBACK;');
                $returnItens = false;
            }

        }

        return $returnItens;

    }

    private function adicionarItens($request, $id_veiculo) {

        // print_r($request); exit;
        $returnItens = false;
        $_itens = $request['consumiveis'];

        $ipcliente = $this->permissoes->pegaIPcliente();
        $hist = explode("##", addslashes($request['alteracoes']));

        if(!empty($hist[1])){ 
            $alteracoes = $hist[0]." | ".ucwords($_SESSION["nomeUsuario"])." - $ipcliente - ".date('d/m/Y H:i:s')." - ALTERAÇÃO >> ".$hist[1];
        
        }else{
            $returnItens = false;
            return $returnItens;
        }

        if ($_itens != "") {

            $format_itens = str_replace("][", "|", $_itens);
            $format_itens = str_replace(" *", ",", $format_itens);
            $format_itens = str_replace("[", "", $format_itens);
            $format_itens = str_replace("]", "", $format_itens);
    
            $itens = explode("|", $format_itens);
            
            // print_r($itens); exit;
            $sqlItens = "INSERT INTO  consumiveis (id, id_veiculo, tipo_veiculo, placa_veiculo, modelo_veiculo, peca, tipo_consumivel, categoria, validade_km, validade_dias, tempo_manut, alteracoes, situacao) VALUES ";

            foreach ($itens as $keyItem => $item) {
    
                $explodedItem = explode(", ", $item);
                // print_r($explodedItem); exit;

                // validade KM
                if ($explodedItem[3] == "NaN") {
                    $explodedItem[3] = 0;
                }
                // Validade Dias
                if ($explodedItem[4] == "NaN") {
                    $explodedItem[4] = 0;
                }
                // Horas Manut
                if ($explodedItem[5] == "NaN") {
                    $explodedItem[5] = 0;
                }
    
                $sqlItens .= "(
                    DEFAULT,
                    '" . $id_veiculo . "',
                    '" . $request['tipo_veiculo']. "',
                    '" . $request['placa']. "',
                    '" . $request['modelo']. "',
                    '" . $explodedItem[1] . "',
                    '" . $explodedItem[0] . "',
                    '" . $explodedItem[2] . "',
                    '" . $explodedItem[3] . "',
                    '" . $explodedItem[4] . "',
                    '" . $explodedItem[5] . "',
                    '" . $alteracoes . "',
                    'ativo'
                ),";
            }

            // tirar a última vírgula da string
            $sqlItens = substr($sqlItens,0,-1);
            
            self::db()->query('START TRANSACTION;');
            self::db()->query($sqlItens);

            $erroItens = self::db()->errorInfo();

            if( empty($erroItens[2]) ){
                            
                self::db()->query('COMMIT;');
                $returnItens = true;

            }else{

                self::db()->query('ROLLBACK;');
                $returnItens = false;
            }

        }


        return $returnItens;

    }
}