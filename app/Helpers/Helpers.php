<?php

function countLots($codigo) {
    $modelLeilao = new \App\Leiloes;
    return $modelLeilao::find($codigo)->lots()->count();
}

function pager($pagina, $sql, $post, $bind) {
    
    $numLista = 12;
    $leiloes = \Illuminate\Support\Facades\DB::select($sql, $bind);
    
    $num = $leiloes[0]->conta;
    
    if (!$pagina) {
        $pagina = 1;
    }    
    $inicio = $pagina - 1;
    $inicio = $numLista * $inicio;
    
    //calculando pagina anterior
    $menos = $pagina - 1;
    
    //calculando pagina posterior
    $mais = $pagina + 1;
    
    $pgs = ceil($num / $numLista);   
    
    $idestado = isset($input['idestado']) ? $input['idestado'] : '';
    $idcidade = isset($input['idcidade']) ? $input['idcidade'] : '';
    $valor = isset($input['valor']) ? $input['valor'] : '';
    $categoria = isset($input['idcategoria']) ? $input['idcategoria'] : '';
    $paginator =  '';
        
    if($pgs > 1)
    {   
        if($menos > 0)
        {
            $paginator .= '<a href="'.$post.'?pagina='.$menos.'" ><span class="fa fa-chevron-left"></span></a>';
        }
        
        if (($pagina - 4) < 1) {
            $anterior = 1;
        }else{
            $anterior = $pagina - 4;
        }        
        if (($pagina + 4) > $pgs) {
            $posterior = $pgs;
        }else{
            $posterior = $pagina + 4;           
        }    
        for($i = $anterior; $i <= $posterior; $i++)
        {
            if($i != $pagina) {
                $paginator .= '<a href="'.$post.'?pagina='.$i.'">'.$i.'</a>';
            }else {
                $paginator .= '<a href="javascript:;" class="active">'.$i.'</a>';
            }
        }
                
        if($mais <= $pgs) {
            $paginator .= '<a href="'.$post.'?pagina='.$mais.'"><span class="fa fa-chevron-right"></a>';
        }
    
    }
    
	  return $paginator;      
}

function getLots($codigo) {
    $modelLeilao = new \App\Leiloes;
    return $modelLeilao::with('lots')->find($codigo);
}

function countEnables($codigo) {
    $modelEnables = new \App\Habilitados;
    return $modelEnables::where('idlote', '=', $codigo)->count();
}

function termoUso() {
    $termoUso = \App\TermoUso::all();
    $texto = '';
    if ($termoUso->count() > 0) {
        $texto = $termoUso[0]->texto;
    }
    return $texto;
    
}

function getInstitutionalText($title) {
    $title = ucwords(str_replace('-', ' ', $title));
    $title = str_replace(' ', '', $title);
    $title = "\\App\\$title";
    $text = $title::all()->toArray();
    return $text[0]['texto'];
}

function formataData($data = '', $time = false) {
    $data = trim ($data);
    if ($data == '') {
        return '';   
    }
    if ($time) {
        if (strstr($data, '/')) {
            return \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $data)->format('Y-m-d H:i:s');
        }
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data)->format('d/m/Y H:i:s');
    }
    if (strstr($data, '/')) {
        return \Carbon\Carbon::createFromFormat('d/m/Y', $data)->format('Y-m-d');
    }
    return \Carbon\Carbon::createFromFormat('Y-m-d', $data)->format('d/m/Y');
    
}

function getLocationCache() {
    $location =  Illuminate\Support\Facades\Cache::remember('localizacao', 3600, function(){
        return \App\Localizacao::all()->first();
    });
    return $location;
}

function returnShortLoginName($nome) {
    $nome = explode(' ', $nome);
    $nome = @$nome[0] . ' ' . @$nome[1];
    return ucwords(strtolower($nome));
}
function statusLeilaoAtividades($codStatus) {
    $codStatus = (int) $codStatus;
    
    switch($codStatus) {
        case 7:
            $str = '<label class="label futuro">FUTURO</label>';
        break;
        case 1:
            $str = '<label class="label aberto">ABERTO</label>';
        break;
        case 8:
            $str = '<label class="label aberto">VENDIDO</label>';
        break;
        case 6:
             $str = '<label class="lable encerrado">Encerrado</option>';
        break;
        case 2:
             $str = '<label class="lable encerrado">Prejudicado</option>';
        break;
        case 3:
             $str = '<label class="lable encerrado">Suspenso</option>';
        break;
        case 4:
             $str = '<label class="lable encerrado">Adjudicado</option>';
        break;
        case 5:
             $str = '<label class="lable encerrado">Acordo homologado</option>';
        break;
        case 7:
             $str = '<label class="lable encerrado">Futuro</option>';
        break;
        case 8:
             $str = '<label class="lable encerrado">Vendido</option>';
        break;
    }
    
    return $str;
}
function statusLeilao($status, $visualizaLote = false, $h = 4, $onlyClass = false) {
    $arrayStatus = [];
    $arrayStatus[1] =  'Aberto|aberto|';
    $arrayStatus[6] =  'Encerrado|encerrado|Encerrado';
    $arrayStatus[2] =  'Prejudicado|encerrado|Prejudicado';
    $arrayStatus[3] =  'Suspenso|encerrado|Suspenso';
    $arrayStatus[4] =  'Adjudicado|encerrado|Adjudicado';
    $arrayStatus[5] =  'Acordo homologado|encerrado|Acordo homologado';
    $arrayStatus[7] =  'Futuro|futuro|Futuro';
    $arrayStatus[8] =  'Vendido|aberto|Vendido';
    $status = explode('|', $arrayStatus[$status]);
    $classe = $status[1];
    if ($onlyClass) {
        return $classe;
    }
    $statusLeilao = $status[0];
    if ($visualizaLote) {
        return ['<h'.$h.' class="' . $classe . '">' . strtoupper($statusLeilao) . '</h'.$h.'>', $status[2]];    
    }
    return '<div class="status ' . $classe . '"><h5>' . $statusLeilao . '</h5></div>';
}

function tipoLeilao($tipo) {
    $tipo = (int) $tipo;
    $strTitpo = 'JUDICIAL';
    switch($tipo) {
        case 1:
            $strTitpo = 'JUDICIAL';
        break;
        case 2:
            $strTitpo = 'EXTRAJUDICIAL';
        break;
        case 3:
            $strTitpo = 'BENEFICENTE';
        break;
        default:
            $strTitpo = 'ALIENAÇÃO PARTICULAR';
        break;
    }
    return $strTitpo;
}

function lelaoDatas($value, $numero = 1) {
    $value = (int) $value;
    switch ($value) {
        case 1:
            return $numero. 'º Leilão';
        break;
        case 2:
            return $numero. 'º Praça';
        break;
        default:
            return 'Praça única';
        break;
    }
}

function dadosLote($qtdLote, $leilao ) {
    $mktimeHoje = time();
    $dataInicial = $leilao->leilao_data_inicial . ' ' . $leilao->leilao_hora_inicial;
    $dataFinal = $leilao->leilao_data_final . ' ' . $leilao->leilao_hora_final;
    $dataFinal2 = $leilao->leilao2_data_final . ' ' . $leilao->leilao2_hora_final;
    $timeStampAbertura = strtotime($dataInicial);
    $timeStampFechamento = strtotime($dataFinal);
    $timeStampFechamento2 = strtotime($dataFinal2);
    //$lance_data_1 = ($mktimeHoje <= $timeStampAbertura) ? 'Confira nos lotes' : '<s>Confira nos lotes</s>';
    $lance_data_1 = ($mktimeHoje <= $timeStampFechamento) ? 'Confira nos lotes' : '<s>Confira nos lotes</s>';
    
    $lance_data_2 = ($mktimeHoje <= $timeStampFechamento) ? 'Confira nos lotes' : '<s>Confira nos lotes</s>';
    $str = '';
    if($qtdLote != 1)
	{
        
		$str = '<p>'.lelaoDatas($leilao->leilao_data_tipo).' '.formataData($leilao->leilao_data_final).' '.'às'.' '.substr($leilao->leilao_hora_final, 0, -3).'<br>'. 'LANCE MÍNIMO' .': '.$lance_data_1.'</p>';
	
		if($leilao->leilao2_data_tipo != 3) {
            $str .= '<p>'.lelaoDatas($leilao->leilao2_data_tipo, 2).' '.formataData($leilao->leilao2_data_final).' '.'às'.' '.substr($leilao->leilao2_hora_final, 0, -3).'<br>'. 'LANCE MÍNIMO' .': '.$lance_data_2.'</p>';
        }
		else {
            $str .= '<p><br><br></p>';
        }
        $lotes = getLots($leilao->codigo);
        return [$str, $lotes, urlTitulo($leilao->titulo)];
    }
    $lotes = getLots($leilao->codigo);
    
    $lance_data_1 = ($mktimeHoje <= $timeStampFechamento) ? 'R$ '.number_format($lotes->lots[0]->lance_data_1, 2, ',', '.') : '<s>R$ '.number_format($lotes->lots[0]->lance_data_1, 2, ',', '.').'</s>';
	$lance_data_2 = ($mktimeHoje <= $timeStampFechamento2) ? 'R$ '.number_format($lotes->lots[0]->lance_data_2, 2, ',', '.') : '<s>R$ '.number_format($lotes->lots[0]->lance_data_2, 2, ',', '.').'</s>';
	$str = '<p>'.lelaoDatas($leilao->leilao2_data_tipo, 1).' '.formataData($leilao->leilao_data_final).' '.'às'.' '.substr($leilao->leilao_hora_final, 0, -3).'<br>'. 'LANCE MÍNIMO' .': '.$lance_data_1.'</p>';
	
    if($leilao->leilao2_data_tipo != 3) {
       $str .= '<p>'.lelaoDatas($leilao->leilao2_data_tipo, 2).' '.formataData($leilao->leilao2_data_final).' '.'às'.' '.substr($leilao->leilao2_hora_final, 0, -3).'<br>'. 'LANCE MÍNIMO' .':  '.$lance_data_2.'</p>';
    }
    else {
        $str .= '<p><br><br></p>';
    }
    
    return [$str, $lotes, urlTitulo($leilao->titulo)];
}

function lanceMinino($lote) {
    $time = time();
    $dataInicial1 = strtotime($lote->leilao->leilao_data_final . ' ' . $lote->leilao->leilao_hora_final);
    $lance1 = $lote->lance_data_1;
    $lance2 = $lote->lance_data_2;
    $lanceMinimo = $lance1;
    if ($dataInicial1 < $time ) {
        $lanceMinimo = $lance2;
    }
    if ($lote->maiorLance->count() > 0) {
        $lanceMinimo = $lote->maiorLance[0]->valor + $lote->incremento;
    }
    return $lanceMinimo;
}

function vencedorLeilao($lances, $parcelado =  false, $lanceEscolhido) {
    $cadastro = \App\Cadastros::find($lances[0]->idcadastro);
    $apelido = $cadastro->apelido;
    $nome = $apelido;
    if ($parcelado) {
        $lanceEscolhido = $lanceEscolhido->toArray();
        if (count($lanceEscolhido) > 0) {
            $cadastro = \App\Cadastros::find($lanceEscolhido[0]['idcadastro']);
            $apelido = $cadastro->apelido;
            $nome = '@' . $apelido;
            return [$nome, formataData($lanceEscolhido[0]['data_lance'], true), $lanceEscolhido[0]['valor']];
        }
        $lances = $lances->toArray();
        $lances = array_values(array_filter($lances, function($v, $k ) {
             return $v['status'] == 0;
        }, ARRAY_FILTER_USE_BOTH));
        if (count($lances) == 0) {
            return false;    
        }
        return [$nome, formataData($lances[0]['data_lance'], true), $lances[0]['valor']];
    }
    return [$nome, formataData($lances[0]->data_lance, true), $lances[0]->valor];
}

function urlTitulo($titulo) {
    $titulo = strtolower($titulo);
    $titulo = str_replace('-', '', $titulo);

    $comAcentos = array('/', '?', '!', ', ', '–', '(', ')', '.', '²', '@', '&', '  ', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú', '-', ',', ':', ';');
    $semAcentos = array('_', '', '', ',','', '-', '-', '', '','arroba', 'e', ' ', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', '0', 'U', 'U', 'U','', '-', '-', '-');
    $titulo = str_replace($comAcentos, $semAcentos, $titulo);
    $titulo = str_replace(' ', '-', $titulo);

    return strtolower($titulo);
}


function modalidadeLeilao($modalidade) {
    $modalidade = (int) $modalidade;
    $desc = '';
    switch($modalidade) {
        case 1:
            $desc = 'Online';
        break;
        case 2:
            $desc = 'Presencial';
        break;
        default:
            $desc = 'Presencial / Online';
        break;
    }
    return $desc;
}

function aberturaEncerramento($dataInicial, $leilao) {
    $time = time();
    
    if ($leilao->tipo == 4 && $leilao->usar_cronometro == 0) {
        return '';
    }
    $timeStampAbertura = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dataInicial)->timestamp;
    if ($timeStampAbertura >  $time) {
        return 'Abertura';
    }
    return 'Encerramento';
}

function retornaDataCronometro($leilao, $status) {
    $time = time();
    $dataInicial1 = strtotime($leilao->leilao_data_inicial . ' ' . $leilao->leilao_hora_inicial);
    $dataFinal1 = strtotime($leilao->leilao_data_final . ' ' . $leilao->leilao_hora_final);

    $dataInicial2 = strtotime($leilao->leilao2_data_inicial . ' ' . $leilao->leilao2_hora_inicial);
    $dataFinal2 = strtotime($leilao->leilao2_data_final . ' ' . $leilao->leilao2_hora_final);
    if ($time <  $dataInicial1) {
        return date('m/d/Y H:i:s', $dataInicial1);
    }

    if ($time >  $dataInicial1 && $time < $dataFinal1) {
        return date('m/d/Y H:i:s', $dataFinal1);
    }

    if ($time <  $dataInicial2) {
        return date('m/d/Y H:i:s', $dataInicial2);
    }

    if ($time >  $dataInicial2 && $time < $dataFinal2) {
        return date('m/d/Y H:i:s', $dataFinal2);
    }
    
    return date('m/d/Y H:i:s', $dataFinal2);
}

function retornaLanceInicial($lote, $stroke = false, $campo = false) {
    $time = time();
    $dataFinal = $lote->leilao->leilao_data_final . ' ' . $lote->leilao->leilao_hora_final;
    if ($campo == 'lance_data_2') {
        $dataFinal = $lote->leilao->leilao2_data_final . ' ' . $lote->leilao->leilao2_hora_final;
    }
    $timeStampFechamento = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dataFinal)->timestamp;
    
    if ($time >= $timeStampFechamento) {
        if ($campo) {
            $valor = $stroke == true ? '<s>R$ '.number_format($lote->$campo, 2, ',', '.') .'</s>': 'R$ '.number_format($lote->$campo, 2, ',', '.')  ;    
            return  $valor;
        }
        return  'R$ '.number_format($lote->lance_data_1, 2, ',', '.');
    }
    if ($campo) {
        return  'R$ '.number_format($lote->$campo, 2, ',', '.');
    }
    if ($timeStampFechamento > $time) {
        return  'R$ '.number_format($lote->lance_data_1, 2, ',', '.');    
    }
    return  'R$ '.number_format($lote->lance_data_2, 2, ',', '.');
}

function redimencionaImagem($imagem, $altura, $largura)
{
	//REDIMENSIONANDO AS IMAGENS
	$foto = $imagem;

	$tamMax = array($altura, $largura);

    // Comprime imagem
    // 0 => Sem comprimir
    // 100 => Melhor compre��o
	$comprimi = 70;

    //0 => largura
    //1 => Altura
    //2 => Formato da imagem
	list($imgLarg, $imgAlt, $imgTipo) = getimagesize($foto);
    $novaLargura = $imgLarg;
    $novaAltura = $imgAlt;
    //verifica se a imagem � maior que o m�ximo permitido
	if($imgLarg > $tamMax[0] || $imgAlt > $tamMax[1])
	{
        //verifica se a largura � maior que a altura
		if($imgLarg > $imgAlt)
		{
			$novaLargura = $tamMax[0];
			$novaAltura = round(($novaLargura / $imgLarg) * $imgAlt);
		}
        //se a altura for maior que a largura
		elseif($imgAlt > $imgLarg)
		{
			$novaAltura = $tamMax[1];
			$novaLargura = round(($novaAltura / $imgAlt) * $imgLarg);
		}
        //altura == largura
		else
		{
			$novaAltura = $novaLargura = max($tamMax);
		}
	}

    // Cria a imagem baseada na imagem original
	switch ($imgTipo){
		case 1: 
		$srcImg = imagecreatefromgif($foto); 
		break;

		case 2: 
		$srcImg = imagecreatefromjpeg($foto);
		break;

		case 3: 
		$srcImg = imagecreatefrompng($foto);
		break;

		default: 
		return ''; 
		break;
	}

    // cria a nova imagem
	$destImg = imagecreatetruecolor(@$novaLargura, @$novaAltura);

    // copia para a imagem de destino a imagem original redimensionada
	imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, @$novaLargura, @$novaAltura, $imgLarg, $imgAlt);

    // Sava a imagem
	switch ($imgTipo){
		case 1: 
		imagegif($destImg, $imagem, NULL, $comprimi); 
		break;
		case 2: 
		imagejpeg($destImg, $imagem, $comprimi); 
		break; 
		case 3: 
		imagepng($destImg, $imagem, NULL, $comprimi);
		break;

		default: echo '';
		break;
	}
}

function retornaIdEstado($estado) {
    if ($estado == '' || $estado == 'todos-os-estados') {
        return 0;
    }
    $estado = \App\Estados::select('codigo')->where('uf', $estado)->get();
    if ($estado->count() > 0)  {
        return (int) $estado[0]->codigo;
    }
    return 88888899;
}

function retornaIdCidade($cidade, $idestado) {
    if ($cidade == '' || $cidade == 'todas-as-cidades') {
        return 0;
    }
    $cidade = ucwords(str_replace("-", " ", $cidade));
    //die($cidade);
    $cidade = \App\Cidades::select('codigo')->where('nome', $cidade)->where('idestado', $idestado)->get();
    if ($cidade->count() > 0)  {
        return (int) $cidade[0]->codigo;
    }
    return 8888889;
}

function retornaIdCategoria($segmento) {
    $id = 0;
    switch($segmento) {
        case 'residenciais':
            $id = 4;
        break;
        case 'comerciais':
            $id = 5;
        break;
        case 'rurais':
            $id = 6;
        break;
    }
    return $id;
}

function retornaIdSubCategoria($segmento) {
    $subcategoria = 0;
    switch($segmento) {
		case 'apartamento':
		    $subcategoria = 18;
		break;
		case 'casa-sobrado':
		    $subcategoria = 19;
		break;
		case 'sala-escritorios':
		    $subcategoria = 20;
		break;
		case 'galpao':
		    $subcategoria = 21;
		break;
		case 'terreno-lote-comercial':
		    $subcategoria = 22;
		break;
		case 'glebas':
		    $subcategoria = 23;
		break;
		case 'terreno-lote':
		    $subcategoria = 24;
		break;
		case 'cobertura':
		    $subcategoria = 25;
		break;

		case 'fazenda':
		    $subcategoria = 26;
		break;
		case 'chacara-sitio':
		    $subcategoria = 27;
		break;
		case 'veiculos':
		    $subcategoria = 28;
		break;
		case 'veiculos':
		    $subcategoria = 29;
        break;
        case 'vaga-de-garagem-residencial':
		    $subcategoria = 30;
        break;
        case 'vaga-de-garagem-comercial':
		    $subcategoria = 31;
		break;
	}
    return $subcategoria;
}

function getAdmEmails(){
    $emails = \App\User::where('tipo', 1)->get()->pluck('email')->toArray();
    return $emails;
}

function getAllEmails(){
    $emails = \App\User::get()->pluck('email')->toArray();
    return $emails;
}

function leiloesDestaque() {
    $location =  Illuminate\Support\Facades\Cache::remember('localizacao', 3600, function(){
        return \App\Localizacao::all()->first();
    });
    Illuminate\Support\Facades\Cache::forget('destaques');
    $destaques = Illuminate\Support\Facades\Cache::remember('destaques', 300, function (){
        return \Illuminate\Support\Facades\DB::select('select * from (
            select * from (SELECT DISTINCT A.*,  B.categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 1 and A.suspender = 2
            AND
            B.lote_destaque = 1
            order by A.leilao2_data_final asc, A.leilao2_hora_final asc , categoria asc
            ) as aberto

            union all

            select * from (SELECT DISTINCT A.*,  B.categoria
            FROM leiloes A
            INNER JOIN lotes B ON A.codigo = B.idleilao
            WHERE A.encerrado = 7 and A.suspender = 2
            AND 
            B.lote_destaque = 1
            order by leilao_data_inicial asc, leilao_hora_inicial asc, categoria
            ) as futuro
) as todos
limit 8');
    }); 
    return $destaques;
}

function maiorLanceParcelado($lances) {
    if ($lances->count() > 0) {
        $lances = $lances->toArray();
        $lancesParc0 = array_filter($lances, function ($k, $v){
            return $k['parcelas'] == 0;
        }, ARRAY_FILTER_USE_BOTH);
        if (count($lancesParc0) > 0) {
            return [$lancesParc0[0]['valor'], $lancesParc0[0]['data_lance']];
        }
        return [$lances[0]['valor'], $lances[0]['data_lance']];
    }
    return false;
}

function ulimosLances($lote) {
    $lancesMerge = array_merge($lote->maiorLance->toArray(), $lote->maiorLanceParcelado->toArray());
    $arrayLances = array();
    foreach($lancesMerge as $dataLance) {
        $timeLance = strtotime($dataLance['data_lance'])  + $dataLance['codigo'];
        $arrayLances[$timeLance] = $dataLance;
    }
    krsort($arrayLances);
    $html = '';
    $qtdView = 3;
    if (count($lancesMerge) > 0) {
        $html = '<div class="row titulo">
            <div class="col-xs-3">
                <p>Usuário</p>
            </div>
            <div class="col-xs-3">
                <p>Valor</p>
            </div>
            <div class="col-xs-3">
                <p>Data</p>
            </div>
            <div class="col-xs-3">
                <p>Tipo</p>
            </div>
        </div>';
    
        foreach($arrayLances as $dataLance) {
            $apelido = $dataLance['apelido'];
            $tipoLance = @$dataLance['tipo'] == 1 ? 'Proposta de compra' : 'Lance';
            if (strlen($apelido) > 10) {
                $apelido = substr($apelido, 0, 10) . '...';
            }
            $html .= '<div class="row">';
            
            $html .= ' <div class="col-xs-3">
                        <P> @ ' . $apelido . '</P>
                    </div>';
            $html .= '<div class="col-xs-3">
                        <p>R$ ' . number_format($dataLance['valor'], 2, ',', '.') . '</p>
                    </div>';
            $html .='<div class="col-xs-3">
                        <p>' . formataData($dataLance['data_lance'], true ) . '</p>
                    </div>';
            $html .= '<div class="col-xs-3">
                        <p> ' . $tipoLance . '</p>
                
                    </div>';
            $html .= '</div>';
        
            $qtdView--;
            if ($qtdView == 0) {
                break;
            }
        }
        return $html;
    }
    return '<h5 class="nenhum">Nenhum lance vencendo até o momento</h5>';
}

function maiorLance($lote) {
    $html = '';
    if($lote->maiorLance->count() > 0) {
        $html .= '<div class="row titulo">
            <div class="col-xs-6">
                <p>Valor</p>
            </div>
            <div class="col-xs-6">
                <p>Data</p>
            </div>
        </div>';
        $html .= '<div class="row">
            <div class="col-xs-6">
                <p>R$ ' . number_format($lote->maiorLance[0]->valor, 2, ',', '.') . '</p>
            </div>
            <div class="col-xs-6">
                <p>' . formataData($lote->maiorLance[0]->data_lance, true) . '</p>
            </div>
        </div>';
        $html .=  statusLeilao($lote->leilao->encerrado, true, 5)[0];
    }else if($lote->maiorLanceParcelado->count() > 0) {
        $maiorLance = maiorLanceParcelado($lote->maiorLanceParcelado);
        $html .= '<div class="row titulo">
            <div class="col-xs-6">
                <p>Valor</p>
            </div>
            <div class="col-xs-6">
                <p>Data</p>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <p>R$ ' . number_format($maiorLance[0], 2, ',', '.') . '</p>
            </div>
            <div class="col-xs-6">
                <p>' . formataData($maiorLance[1], true) . '</p>
            </div>
        </div>';
        $html .=  statusLeilao($lote->leilao->encerrado, true, 5)[0] ;
    }else {
        $html .= '<h5 class="nenhum">Nenhum lance vencendo até o momento</h5>';
        $html .=  statusLeilao($lote->leilao->encerrado, true, 5)[0];
    }
    return $html;
}
function ConverteData() {
    return true;
}