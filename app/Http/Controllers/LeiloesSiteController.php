<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeiloesSiteController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function show($id)
    {
        return view('admin.leiloes.index', ['user' => User::findOrFail($id)]);
    }
    /**
     * List all profiles.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function index(Request $request, $tipo = 'todos')
    {
        $input = $request->all();
        $pagina = isset($input['pagina']) ? (int) $input['pagina'] : null;
        $pagina = $pagina > 0 ? $pagina : 1;
        $pag = 0;
        $qtdLista = 12;
        
        if($pagina){
            $pag++;
        }
        
        if($pagina == FALSE || $pagina == 1) {
            $pag = 0;
        }
        else {
            $pag = $pagina - 1;
        }

        $pag = $pag * $qtdLista;

        $strWhere = '';
        $idestado = isset($input['idestado']) && $input['idestado'] != '' ? $input['idestado'] : 0;
        $uf = '';
        if ($idestado !== 0 && strstr($idestado, '|')) {
            $idestado = explode('|', $idestado);
            $uf = $idestado[0];
            $idestado= (int) $idestado[1];
        }
        
        $idcidade = isset($input['idcidade']) ? $input['idcidade'] : 0;
        $cidade = '';
        $bairro  = isset($input['bairro']) ? $input['bairro'] : '';
        if ($idcidade !== 0 && strstr($idcidade, '|')) {
            $idcidade = explode('|', $idcidade);
            $cidade = $idcidade[0];
            $idcidade= (int) $idcidade[1];
        }
        $input['idcategoria'] = (int) @$input['idcategoria'];
        $valor = isset($input['valor']) ? (int) $input['valor'] : 0;
        $idcategoria = isset($input['idcategoria']) ?  $input['idcategoria'] : 0;
        $idcategoria = $idcategoria > 0 ? $idcategoria : retornaIdCategoria($request->segment(2));
        $input['idsubcategoria'] = (int) @$input['idsubcategoria'];
        $idSubcategoria = isset($input['idsubcategoria']) ?  (int) $input['idsubcategoria'] : 0;
        $idSubcategoria = $idSubcategoria > 0 ? $idSubcategoria : retornaIdSubcategoria($request->segment(3));

        $input['idsubcategoria'] = $input['idsubcategoria'] > 0 ? $input['idsubcategoria'] : $idSubcategoria;
        $input['idcategoria'] = $input['idcategoria'] > 0 ? $input['idcategoria'] : $idcategoria;
        
        switch($tipo) {
            case 'judiciais-e-extrajudiciais':
                $strWhere .= ' AND A.tipo in(1,2) AND A.encerrado in (1,7)';
            break;
            case 'alienacao-particular':
                $strWhere .= ' AND A.tipo = 4 AND A.encerrado in (1,7)';
            break;
            case 'encerrados':
                $strWhere .= ' AND A.encerrado in(8, 5, 4, 3, 2, 6)';
            break;
            case 'judiciais-alienacao-particular':
                $strWhere .= ' AND A.tipo in(1,4) AND A.encerrado in (1,7)';
            break;
            case 'extrajudiciais':
                $strWhere .= ' AND A.tipo in(2) AND A.encerrado in (1,7)';
            break;
            case 'beneficentes':
                $strWhere .= ' AND A.tipo in(3) AND A.encerrado in (1,7)';
            break;
        }
        $idestado = $idestado > 0 ? $idestado : retornaIdEstado($request->segment(4));
        $idcidade = $idcidade > 0 ? $idcidade : retornaIdCidade($request->segment(5), $idestado);
        if ($idestado > 0) {
            $strWhere .= ' AND B.idestado = ' . $idestado;
            $uf = $uf != '' ? $uf : $request->segment(5);
            $input['idestado'] = $uf . '|' . $idestado;
        }
        if ($idcidade > 0) {
            $strWhere .= ' AND B.idcidade = ' . $idcidade;
            $cidade = $cidade != '' ? $cidade : $request->segment(5);
            $input['idcidade'] = $cidade . '|' . $idcidade;
        }
        if ($idcategoria > 0 && $idcategoria < 7 && $idcategoria != 3) {
            $strWhere .= ' AND B.categoria = ' . $idcategoria;
            $input['categoria'] = $idcategoria;
        }
        
        $input['valor'] = $valor;
        switch($valor) {
            case 1:
                $strWhere .= " AND B.min_venda < 100000";
            break;
            case 2:
                $strWhere .= " AND B.min_venda >= 100000 AND B.min_venda <= 500000";
            break;
            case 3:
                $strWhere .= " AND B.min_venda > 500000";
            break;
        }
        $bairro = trim($bairro);
        if ($bairro != '' && $bairro != 'Carregando....') {
            $strWhere .= " AND B.bairro  = :bairro " ;
        }
        
        if ($idSubcategoria > 0) {
            $strWhere .= " AND B.subcategoria  =   " .$idSubcategoria ;
        }

        $sql = 'select * from (
                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 1 and A.suspender = 2
                                -- and B.categoria in (4,5,6) and B.subcategoria not in(29,30)
                                ' . $strWhere . '
                                order by A.leilao2_data_final asc, categoria asc, A.leilao2_hora_final asc, desconto_p desc, B.lance_data_1 desc
                                ) as aberto

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 7 and A.suspender = 2
                                and B.categoria in (4,5,6) and B.subcategoria not in(29,30)
                                ' . $strWhere . '
                                order by leilao_data_inicial asc, leilao_hora_inicial asc, categoria asc, desconto_p desc, B.lance_data_1 desc
                                ) as futuro

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 7 and A.suspender = 2
                                and B.categoria in (4,5) and B.subcategoria in(29,30)
                                ' . $strWhere . '
                                order by leilao_data_inicial asc, leilao_hora_inicial asc, categoria asc, desconto_p desc, B.lance_data_1 desc
                                ) as futuro3

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 7 and A.suspender = 2
                                and B.categoria = 7 
                                ' . $strWhere . '
                                order by leilao_data_inicial asc, leilao_hora_inicial asc, subcategoria asc, desconto_p desc, B.lance_data_1 desc
                                ) as futuro1
                                
                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 8 and A.suspender = 2
                                and B.categoria in (4,5,6)
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
                                ) as vendido

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 8 and A.suspender = 2
                                and B.categoria = 7 
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
                                ) as vendido1

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 6 and A.suspender = 2
                                and B.categoria in (4,5,6)
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
                                ) as encerrado

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 6 and A.suspender = 2
                                and B.categoria = 7 
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
                                ) as encerrado1

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 3 and A.suspender = 2
                                and B.categoria in (4,5,6)
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
                                ) as suspenso

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 3 and A.suspender = 2
                                and B.categoria = 7 
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
                                ) as suspenso1
                                
                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 5 and A.suspender = 2
                                and B.categoria in (4,5,6)
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
                                ) as acordo

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 5 and A.suspender = 2
                                and B.categoria = 7 
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
                                ) as acordo1

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 2 and A.suspender = 2
                                and B.categoria in (4,5,6)
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
                                ) as prejudicado

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 2 and A.suspender = 2
                                and B.categoria = 7 
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
                                ) as prejudicado1

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                WHERE A.encerrado = 4 and A.suspender = 2
                                and B.categoria in (4,5,6)
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
                                ) as adjudicado

                                union all

                                select * from (SELECT DISTINCT A.*,B.idleilao, categoria
                                FROM leiloes A
                                INNER JOIN lotes B ON A.codigo = B.idleilao
                                and B.categoria = 7 
                                WHERE A.encerrado = 4 and A.suspender = 2
                                ' . $strWhere . '
                                order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
                                ) as adjudicado1
            ) as TODOS';
        
        
        $selectCount = 'select count(*) as conta from (
            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 1 and A.suspender = 2
            -- and B.categoria in (4,5,6) and B.subcategoria not in(29,30)
            ' . $strWhere . '
            order by A.leilao2_data_final asc, categoria asc, A.leilao2_hora_final asc, desconto_p desc, B.lance_data_1 desc
            ) as aberto

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 7 and A.suspender = 2
            and B.categoria in (4,5,6) and B.subcategoria not in(29,30)
            ' . $strWhere . '
            order by leilao_data_inicial asc, leilao_hora_inicial asc, categoria asc, desconto_p desc, B.lance_data_1 desc
            ) as futuro

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 7 and A.suspender = 2
            and B.categoria in (4,5) and B.subcategoria in(29,30)
            ' . $strWhere . '
            order by leilao_data_inicial asc, leilao_hora_inicial asc, categoria asc, desconto_p desc, B.lance_data_1 desc
            ) as futuro3

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 7 and A.suspender = 2
            and B.categoria = 7 
            ' . $strWhere . '
            order by leilao_data_inicial asc, leilao_hora_inicial asc, subcategoria asc, desconto_p desc, B.lance_data_1 desc
            ) as futuro1
            
            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 8 and A.suspender = 2
            and B.categoria in (4,5,6)
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
            ) as vendido

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 8 and A.suspender = 2
            and B.categoria = 7 
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
            ) as vendido1

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 6 and A.suspender = 2
            and B.categoria in (4,5,6)
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
            ) as encerrado

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 6 and A.suspender = 2
            and B.categoria = 7 
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
            ) as encerrado1

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 3 and A.suspender = 2
            and B.categoria in (4,5,6)
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
            ) as suspenso

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 3 and A.suspender = 2
            and B.categoria = 7 
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
            ) as suspenso1
            
            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 5 and A.suspender = 2
            and B.categoria in (4,5,6)
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
            ) as acordo

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 5 and A.suspender = 2
            and B.categoria = 7 
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
            ) as acordo1

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 2 and A.suspender = 2
            and B.categoria in (4,5,6)
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
            ) as prejudicado

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 2 and A.suspender = 2
            and B.categoria = 7 
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
            ) as prejudicado1

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 4 and A.suspender = 2
            and B.categoria in (4,5,6)
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, categoria asc, desconto_p desc
            ) as adjudicado

            union all

            select * from (SELECT DISTINCT A.*,B.idleilao, categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            and B.categoria = 7 
            WHERE A.encerrado = 4 and A.suspender = 2
            ' . $strWhere . '
            order by leilao2_data_final desc, leilao2_hora_final desc, subcategoria asc, desconto_p desc
            ) as adjudicado1
) as TODOS';
       
        $leiloes = DB::select($sql .' limit ' . $pag . ', ' . $qtdLista, ['bairro' => $bairro, 'subcategoria' => $idSubcategoria] );
       
        return view('site.leiloes.leiloes', ['bind' => ['bairro' => $bairro, 'subcategoria' => $idSubcategoria] , 'leiloes' => $leiloes, 'paginator' => $pagina,'sql' => $selectCount, 'input' => $input, 'request' => $request ]);
    }

    /**
     * List all profiles.
     *
     * @param  Request  $request
     * @return redirect()
     */

    public function search(Request $request)
    {
        $input = $request->all();
        $cadastros = new Cadastros();
        $nome = trim($input['nome']);
        $cpfCnpj = trim($input['cpf_cnpj']);
        $status = (int) $input['status'];
        $dataInicial = trim($input['data_inicial']);
        $dataFinal = trim($input['data_final']);

        if ($nome != '') {
            $cadastros = $cadastros->where('nome', 'like', '%' . $nome . '%')
                                ->orWhere('razao_social', 'like ', '%' . $nome . '%');
        }

        if ($cpfCnpj != '') {
            $cadastros = $cadastros->where('cpf', 'like', '%' . $cpfCnpj . '%')
                                ->orWhere('cnpj', 'like ', '%' . $cpfCnpj . '%');
        }

        if ($status != 10) {
            $cadastros = $cadastros->where('status', '=',  $status );
        }

        if ($dataInicial != '' && $dataFinal != '') {
            $dataInicial = explode('/', $dataInicial);
            $dataFinal = explode('/', $dataFinal);
            $dataInicial = $dataInicial[2] . '-' . $dataInicial[1] . '-' . $dataInicial[0];
            $dataFinal = $dataFinal[2] . '-' . $dataFinal[1] . '-' . $dataFinal[0];
            $cadastros = $cadastros->whereBetween('data_cadastro', [$dataInicial, $dataFinal]);
        }
        $cadastros = $cadastros->get();
        
        return view('admin.cads.index', ['cads' => $cadastros, 'input' => $input]);
    }

    /**
     * Update the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function save(Request $request)
    {
        $input = $request->all();
       
        $cadastro = new Leiloes();
        $input['codigo'] = (int) $input['codigo'];
        $cadastro->data_cadastro = date('Y-m-d') ;
        
        if ($input['codigo'] > 0) {
            $cadastro = Leiloes::find($input['codigo']);
            unset($cadastro->data_cadastro);
        }
        $cadastro->tipo     = $input['tipo'];
        $cadastro->modalidade   = $input['modalidade'];
        $cadastro->modelo       = $input['modelo'];
        $cadastro->idleiloeiro  = (int) $input['idleiloeiro'];
        $cadastro->habilitacao   = (int) $input['habilitacao'];
        $cadastro->titulo  = $input['titulo'];
        $cadastro->subtitulo = $input['subtitulo'];
        
        $cadastro->numero            = $input['numero'];
        $cadastro->publicacao    = '';//$input['publicacao'];
        $cadastro->idcomitente      = $input['idcomitente'];
        $cadastro->leilao_data_tipo      = $input['leilao_data_tipo'];
        $timeStampDatainicial = strtotime($input['leilao_data_inicial']);
        $cadastro->leilao_data_inicial   = date('Y-m-d', strtotime($input['leilao_data_inicial']));
        $cadastro->leilao_hora_inicial   = $input['leilao_hora_inicial'];

        $cadastro->leilao_data_final    = date('Y-m-d', strtotime($input['leilao_data_final']));
        $cadastro->leilao_hora_final    = $input['leilao_hora_final'];
        $cadastro->leilao2_data_tipo   = $input['leilao2_data_tipo'];
        $cadastro->leilao2_data_inicial   = date('Y-m-d', strtotime($input['leilao2_data_inicial']));
        $cadastro->leilao2_hora_inicial   = $input['leilao2_hora_inicial'];
        $cadastro->leilao2_data_final     = date('Y-m-d', strtotime($input['leilao2_data_final']));
        $cadastro->leilao2_hora_final     = $input['leilao2_hora_final'];
        $cadastro->responsavel           = $input['responsavel'];
        $cadastro->endereco            = $input['endereco'];

        $cadastro->cidade           = $input['cidade'];
        $cadastro->visitacao        = $input['visitacao'];
        $cadastro->restrito         = $input['restrito'];
        if ($request->jornal) {
            $cadastro->jornal       = $request->jornal->getClientOriginalName();//$input['jornal'];
            $request->jornal->storeAs('public/documentos', $cadastro->jornal);
        }
        if ($request->edital) {
            $cadastro->edital    = $request->edital->getClientOriginalName();// $input['edital'];
            $request->edital->storeAs('public/documentos', $cadastro->edital);
        }
        if ($request->logo) {
            $cadastro->logo        = $request->logo->getClientOriginalName();//$input['logo'];
            $request->logo->storeAs('public/documentos', $cadastro->logo);
        }
        if ($request->destaque) {
            $cadastro->destaque     = $request->destaque->getClientOriginalName();//$input['destaque'];
            $request->destaque->storeAs('public/documentos', $cadastro->destaque);
        }
        $cadastro->imagem_360 = '';
        if ($request->imagem_360) {
            $cadastro->imagem_360   = $request->imagem_360->getClientOriginalName();//$input['imagem_360'];
            $request->imagem_360->storeAs('public/documentos', $cadastro->imagem_360);
        }
        $cadastro->youtube      = $input['youtube'];
        $cadastro->condicoes    = $input['condicoes'];
        $cadastro->regras       = $input['regras'];
        //status futuro
        if ($timeStampDatainicial > time()) {
            $input['encerrado'] = 7;
        }
        $cadastro->encerrado    = (int) $input['encerrado'];

        $cadastro->save();
        
        return redirect()->back();
    }

    /**
     * Delete the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function delete($id)
    {
        $id = (int) $id;
        $cadastro = Leiloes::find($id);
        $cadastro->delete();
        return redirect('leiloes');
    }

    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function get($id)
    {
        $id = (int) $id;
        $cadastros = Leiloes::all();
        return view('admin.leiloes.index', ['cads' => $cadastros, 'vet' => Leiloes::findOrFail($id)->toArray()]);
    }
}