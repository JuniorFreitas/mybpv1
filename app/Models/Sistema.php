<?php

namespace App\Models;

use App\Events\Notificacoes\NotificacaoEvent;
use DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use MasterTag\DataHora;
use MasterTag\GExtenso;

class Sistema
{

    public const UrlServidor = 'http://159.89.154.53:8991/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS';
    public const EMAILPADRAO = 'sistema@mybp.com.br';

    public static function nomeDoGrupo()
    {
        $usuario = auth()->user();
        return Papel::findOrFail($usuario->grupo_id)->nome;
    }

    public static function nomeDoUsuario()
    {
        return auth()->user()->nome;
    }

    public static function emailDoGrupo()
    {
        $usuario = auth()->user();
        $grupo = Papel::find($usuario->grupo_id);
        if ($grupo) {
            return $grupo->email;
        }
//        return self::$EMAIL_CONTRATO; // no caso de não encontrar
    }

    public static function permitirLinks($links)
    {

        if (func_num_args() == 0) {
            return false;
        }
        $lista = [];

        for ($i = 0; $i < func_num_args(); $i++) {
            $lista[] = func_get_arg($i);
        }

        $retorno = false;
        $listaDeHabilidade = auth()->user()->listaDeHabilidades();
        $listaDeHabilidade = collect($listaDeHabilidade);

        foreach ($lista as $habilidade) {
            if ($listaDeHabilidade->search($habilidade) !== false) {
                $retorno = true;
                break;
            }
        }

        return $retorno;


    }

    // Gera uma chave unica para cada operação de pagina. Muito usado para identificar os upLoads do usuario em sessão
    public static function gerarChave()
    {
        return auth()->id() . "_" . str_random(30);
    }

    public static function atualizaUltimoAcesso()
    {
        $agora = new DataHora();
        $usuario = auth()->user();
        $usuario->ultimo_acesso = $agora->dataHoraInsert();
        $usuario->save();
    }

    public static function validaCPF($cpf)
    {
        // Converte em somente número todos os digitos
        $cpf = str_pad(preg_replace('/[^0-9]/i', '', $cpf), 11, '0', STR_PAD_LEFT);
        // Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
        if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {
            return false;
        } else {   // Calcula os números para verificar se o CPF é verdadeiro
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf[$c] * (($t + 1) - $c);
                }

                $d = ((10 * $d) % 11) % 10;

                if ($cpf[$c] != $d) {
                    return response()->json([
                        'msg' => 'CPF Inválido',
                        'status' => 'error'
                    ], 400);
                }
            }

            return true;
        }
    }

    // Verifica se CPF do cliente já existe no sistema cadastrado
    public static function verificaCpfCadastrado($classe, $cpf)
    {
        if (!empty($cpf)) {
            if (self::validaCPF($cpf)) {
                $result = $classe::where('cpf', $cpf)->first();
                if (!$result) {
                    return response()->json([], 201);
                } else {
                    return response()->json([
                        'msg' => 'CPF ja cadastrado em nosso Banco de dados',
                    ], 400);
                }
            } else {
                return response()->json([
                    'msg' => 'CPF Inválido',
                ], 400);
            }
        }

    }

    public static function validaCNPJ($cnpj)
    {
        $cnpj = str_replace(".", "", $cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        if (strlen($cnpj) != 14) {
            return false;
        }
        $soma1 = ($cnpj[0] * 5) +
            ($cnpj[1] * 4) +
            ($cnpj[2] * 3) +
            ($cnpj[3] * 2) +
            ($cnpj[4] * 9) +
            ($cnpj[5] * 8) +
            ($cnpj[6] * 7) +
            ($cnpj[7] * 6) +
            ($cnpj[8] * 5) +
            ($cnpj[9] * 4) +
            ($cnpj[10] * 3) +
            ($cnpj[11] * 2);
        $resto = $soma1 % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        $soma2 = ($cnpj[0] * 6) +
            ($cnpj[1] * 5) +
            ($cnpj[2] * 4) +
            ($cnpj[3] * 3) +
            ($cnpj[4] * 2) +
            ($cnpj[5] * 9) +
            ($cnpj[6] * 8) +
            ($cnpj[7] * 7) +
            ($cnpj[8] * 6) +
            ($cnpj[9] * 5) +
            ($cnpj[10] * 4) +
            ($cnpj[11] * 3) +
            ($cnpj[12] * 2);
        $resto = $soma2 % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;
        if (($cnpj[12] == $digito1) && ($cnpj[13] == $digito2)) {
            return true;
        } else {
            return response()->json([
                'msg' => 'CNPJ Inválido',
            ], 400);
        }
    }

    // Verifica se CNPJ já existe
    public static function verificaCnpjCadastrado($classe, $cnpj)
    {
        if (!empty($cnpj)) {
            if (self::validaCNPJ($cnpj)) {
                $result = $classe::where('cnpj', $cnpj)->first();
                if (!$result) {
                    return response()->json([], 201);
                } else {
                    return response()->json([
                        'msg' => 'CNPJ ja cadastrado em nosso Banco de dados',
                    ], 400);
                }
            } else {
                return response()->json([
                    'msg' => 'CNPJ Inválido',
                ], 400);
            }
        }
    }

    // Auxiliar de caluclar % de qualquer valor
    public static function pctDe($valor, $pct)
    {
        $resposta = $valor * ($pct / 100);
        if ($resposta < 0.00) {
            return 0.00;
        } else {
            return $resposta;
        }
    }

    public static function horaJs()
    {
        $agora = new DataHora(null);
        $ano = $agora->ano();
        $mes = $agora->mes();
        $dia = $agora->dia();

        $hora = $agora->hora();
        $minuto = $agora->minuto();
        $segundo = $agora->segundo();
        $mes--;

        return "$ano,$mes,$dia,$hora,$minuto,$segundo";
    }

    public static function hoje()
    {
        $agora = new DataHora(null);
        return "São Luís-MA, " . $agora->dia() . " de " . $agora->mesExtM() . " de " . $agora->ano() . " - ";
    }

    public static function DinheiroInsert($dinheiro)
    {
        $valorForma = str_replace('.', '', $dinheiro);
        return $valorForma = str_replace(',', '.', $valorForma);
    }

    public static function DinheiroFormat($dinheiro)
    {
        return number_format($dinheiro, ',', '.');
    }

    public static function convertBase($arquivo, $storage = null)
    {
        if (!$storage) {
            $path = storage_path($arquivo);
        } else {
            $path = $arquivo;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        echo $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    /**
     * @param $arquivo
     * @param $storage
     * @return string
     */
    public static function convertBase3($arquivo, $storage = null)
    {
        if (!$storage) {
            $path = storage_path($arquivo);
        } else {
            $path = $arquivo;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);

        $mime = 'image';
        if ($type == 'pdf') {
            $mime = 'application';
        }

        return "data:$mime/" . $type . ';base64,' . base64_encode($data);
    }

    public static function convertBase2($arquivo, $storage = null)
    {

        if (!$storage) {
            $path = storage_path($arquivo);
        } else {
            $path = $arquivo;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return base64_encode($data);
    }

    public static function dataExtensa($data)
    {
        $data = new DataHora($data . ' 00:00:00');
        return $data->dataCompletaExt();
    }

    public static function valorPorExtenso($valor = 0, $moeda = TRUE, $genero = "M")
    {
        // deixar o numero como string 1.200,00, como 120000
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(',', "", $valor);
        if ($valor == 0.00) {
            return "zero";
        }

        if ($moeda) { // Moeda é real, reais , centavo, centavos
            return str_replace("um centavos", "um centavo", GExtenso::moeda($valor, 2));
        } else {
            // se for apenas numero
            if ($genero == "M") { // Genero masculino
                return GExtenso::numero($valor, GExtenso::GENERO_MASC);
            } else { // Genero feminio
                return GExtenso::numero($valor, GExtenso::GENERO_FEM);
            }
        }
    }

    public static function convertFloat($numeroString)
    {
        $numeroString = str_replace('.', '', $numeroString);
        $numeroString = str_replace(',', '.', $numeroString);
        return floatval($numeroString);
    }

    public static function cnpjSearch($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        $URL = "https://receitaws.com.br/v1/cnpj/{$cnpj}";
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        return json_decode($contents, JSON_PRETTY_PRINT);
    }

    public static function transformCpfCnpj($valor)
    {
        ## Retirando tudo que não for número.
        $valor = preg_replace("/[^0-9]/", "", $valor);
        $tipo = NULL;
        if (strlen($valor) == 11) {
            $tipo = "cpf";
        }
        if (strlen($valor) == 14) {
            $tipo = "cnpj";
        }
        switch ($tipo) {
            default:
                $formatado = "Não foi possível definir tipo de dado";
                break;

            case "cpf":
                $bloco_1 = substr($valor, 0, 3);
                $bloco_2 = substr($valor, 3, 3);
                $bloco_3 = substr($valor, 6, 3);
                $dig_verificador = substr($valor, -2);
                $formatado = $bloco_1 . "." . $bloco_2 . "." . $bloco_3 . "-" . $dig_verificador;
                break;

            case "cnpj":
                $bloco_1 = substr($valor, 0, 2);
                $bloco_2 = substr($valor, 2, 3);
                $bloco_3 = substr($valor, 5, 3);
                $bloco_4 = substr($valor, 8, 4);
                $digito_verificador = substr($valor, -2);
                $formatado = $bloco_1 . "." . $bloco_2 . "." . $bloco_3 . "/" . $bloco_4 . "-" . $digito_verificador;
                break;
        }

        return $formatado;
    }

    public static function dataBrasil($valor)
    {
        $data = new DataHora($valor);
        return $data->dataCompleta() . ' às ' . $data->horaCompleta();
    }

    public static function dataTransform($valor)
    {
        $valor = preg_replace("/[^0-9]/", "", $valor);

        $bloco1 = substr($valor, 0, 2);
        $bloco2 = substr($valor, 2, 2);
        $bloco3 = substr($valor, 4, 4);

        $formatado = $bloco1 . '/' . $bloco2 . '/' . $bloco3;
        return $formatado;
    }

    public static function ParceriaVagas()
    {
        return [418, 419, 420, 421, 310];
    }

    public static function ParceriaGraca()
    {
        return [323, 370, 371, 297];
    }

    public static function maskCpf($cpf)
    {
        $pt1 = substr($cpf, 0, 3);
        $pt2 = substr($cpf, 12, 2);
        return $pt1 . '.XXX.XXX-' . $pt2;
    }

    public static function validaEmail($email)
    {
        $email = trim($email);
        $resultado = preg_match("/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i", $email);
        if ($resultado === 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function SenhaCpf($cpf)
    {
        //Senha de 6 digitos do cpf
        $sonumero = preg_replace("/[^0-9]/", "", $cpf);
        return bcrypt(substr($sonumero, 0, 6));
    }

    public static function telegram($msg)
    {
        $ch = curl_init();
        $url = "https://api.telegram.org/bot" . env('BOT_ID') . '/SendMessage';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'text' => $msg,
            'chat_id' => env('CHAT_ID'),
            'parse_mode' => 'html'
        ]));
        curl_exec($ch);
        curl_close($ch);
    }

    public static function uuid($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function configAws()
    {
        return [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT')
        ];
    }

    //Mudar depois para a
    public static function pg($resultado, $mergeArray = [])
    {
        $itens = array_merge([
            'itens' => $resultado->items()
        ], $mergeArray);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $itens
        ]);
    }

    public static function ativaDesativa($model)
    {
        $model->ativo = !$model->ativo;
        $model->save();
        $model->refresh();
        return response()->json(['ativo' => $model->ativo]);
    }

    public static function stateful()
    {
        return "localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1";
    }

    public static function keyCache($key)
    {
        return "empresa_id_" . auth()->user()->empresa_id . "_" . $key;
    }

    /**
     * @param $key
     * @param $data
     * @param null $expiration
     * @return mixed
     */

    public static function putCache($key, $data, $expiration = null)
    {
        return \Cache::put(self::keyCache($key), $data, $expiration);
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function getCache($key)
    {
        return \Cache::get(self::keyCache($key));
    }


    public static function deleteCache($key)
    {
        self::keyCache($key);
    }

    public static function verificaHdev()
    {
        $host = explode('.', \request()->getHost());
        return $host[0] == 'hdev';
    }

    //Ajuste para inserir a ultima data em ferias previstas
    public function ajuste(\Illuminate\Http\Request $request)
    {
        try {
            DB::beginTransaction();

            $colaborador = FeriasPrevista::whereNotNull('periodo_aquisitivo')->get();

            foreach ($colaborador as $colaboradorPeriodo) {

                $data_admissao = FeedbackCurriculo::whereCurriculoId($colaboradorPeriodo['colaborador_id'])
                    ->with('Admissao')->first();

                $dataAdmissao = $data_admissao->Admissao->data_admissao;

                $periodo = PeriodoAquisitivo::where('id', '=', $colaboradorPeriodo->periodo_aquisitivo_id)->first();

                $date = new DataHora($dataAdmissao);
                $ultimoAnoPeriodoAquisitivo = $periodo['ano_final'] . '-' . $date->mes() . '-' . $date->dia();
                $newDate = new DataHora($ultimoAnoPeriodoAquisitivo);
                $newDate->addDia(330);
                $ultimaData = $newDate->dataInsert();

                $c = FeriasPrevista::find($colaboradorPeriodo['id']);

                $c->update(['ultima_data' => $ultimaData]);
            }
            DB::commit();
            return 'ok';
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Férias:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return $msg;
        }
    }

    public static function listaEmpresas()
    {
        return \DB::table('users')->whereAtivo(true)
//            ->whereNotIn('empresa_id', [User::MYBP_EMPRESA_ID])
            ->whereTemp(false)->selectRaw('DISTINCT empresa_id')
            ->get(['empresa_id'])->pluck('empresa_id')->toArray();
    }

    public static function syncFuncionarios()
    {
        $Empresas = \App\Models\User::select('id', 'nome')->whereTipo(\App\Models\User::EMPRESA)->whereAtivo(true)->get();

        try {
            echo "Iniciando Sincronização...\n";
            DB::beginTransaction();
            foreach ($Empresas as $Empresa) {
                echo "Sincronizando $Empresa->nome...\n";
                $Funcionarios = \App\Models\User::select('id', 'nome', 'empresa_id')->whereEmpresaId($Empresa->id)->whereIn('tipo', \App\Models\User::TIPOS_USUARIOS_COMUNS);
                $Empresa->EmpresaFuncionarios()->sync($Funcionarios->pluck('id'));
                foreach ($Funcionarios->get() as $Funcionario) {
                    $Funcionario->ClienteFuncionarios()->sync($Empresa->id);
                }
            }
            echo "Fim de Sincronização...\n";
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getTraceAsString();
        }
    }

    public static function syncAso()
    {
        $Empresas = \App\Models\User::select(['id', 'nome'])->whereNotIn('id', [100])->whereTipo(\App\Models\User::EMPRESA)->whereAtivo(true)->get();

        try {
            echo "Iniciando Sincronização...\n";
            DB::beginTransaction();
            foreach ($Empresas as $Empresa) {
                echo "Sincronizando $Empresa->nome...\n";

                $admissoes = Admissao::withoutGlobalScopes()->select(['id', 'data_aso'])
                    ->whereHas('Feedback', function ($query) use ($Empresa) {
                        $query->withoutGlobalScopes()->whereEmpresaId($Empresa->id);
                    })->get();

                foreach ($admissoes as $admissao) {
                    $admissaoAso = AdmissaoAso::where('admissao_id', $admissao->id)->whereAtivo(true)->first();

                    if (!is_null($admissaoAso)) {
                        if (($admissaoAso->data_aso && !$admissao->data_aso) || ($admissaoAso->data_aso && $admissao->data_aso)) {
                            $admissao->update(['data_aso' => (new DataHora($admissaoAso->data_aso))->dataInsert()]);
                        }
                    }

                    if ((!is_null($admissaoAso) && !$admissaoAso->data_aso && $admissao->data_aso)) {
                        DB::table('admissao_asos')->where('admissao_id', $admissao->id)->update(['ativo' => false]);
                        AdmissaoAso::updateOrCreate([
                            'empresa_id' => $Empresa->id,
                            'admissao_id' => $admissao->id,
                            'user_alterou_id' => 1,
                            'data_aso' => (new DataHora($admissaoAso->data_aso))->dataInsert(),
                            'ativo' => 1
                        ]);
                        $admissao->update(['data_aso' => $admissaoAso->data_aso]);
                    }
                }
            }
            echo "Fim de Sincronização...\n";
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getTraceAsString());
            echo $e->getTraceAsString();
        }
    }

    public static function grupoClinicaExame($empresa_id)
    {
        $Empresas = \App\Models\User::select('id', 'nome', 'empresa_id')->whereTipo(\App\Models\User::EMPRESA)->whereAtivo(true);
        if ($empresa_id == 0) {
            $Empresas = $Empresas->get();
        } else {
            $Empresas = $Empresas->whereId($empresa_id)->get();
        }

        try {
            echo "Iniciando...\n";
            DB::beginTransaction();
            foreach ($Empresas as $Empresa) {
                $apelido = strtoupper(\App\Models\Cliente::withoutGlobalScopes()->select('id', 'apelido')->find($Empresa->id)->apelido);
                $nome_grupo = "$apelido - Clinica Exame";
                if (\App\Models\Papel::withoutGlobalScopes()->whereNome($nome_grupo)->count() == 0) {
                    echo "Criando Grupo Clinica Exame para $Empresa->nome...\n";
                    $grupoClinica = \App\Models\Papel::withoutGlobalScopes()->create(['nome' => "$nome_grupo", 'descricao' => 'Grupo para clinicas de exame', 'empresa_id' => $Empresa->id, 'email' => 'dev@mybp.com.br', 'ativo' => true]);
                    $habilidades_id = Habilidade::where('nome', 'like', 'acesso_clinica%')->where('nome', 'alterar-senha')->pluck('id');
                    $grupoClinica->habilidades()->attach($habilidades_id);
                } else {
                    echo "Grupo Clinica Exame ja existe na $Empresa->nome...\n";
                }
            }
            echo "Fim...\n";
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage() . ' - ' . $e->getLine() . ' - ' . $e->getFile();
        }
    }


    public static function validaRuleCPF($cpf)
    {
        // Converte em somente número todos os digitos
        $cpf = str_pad(preg_replace('/[^0-9]/i', '', $cpf), 11, '0', STR_PAD_LEFT);
        // Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
        if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {
            return false;
        } else {   // Calcula os números para verificar se o CPF é verdadeiro
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf[$c] * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf[$c] != $d) {
                    return false;
                }
            }
            return true;
        }
    }

    public static function verificaRuleCpfCadastrado($cpf, $empresa_id)
    {
        $cpf = self::transformCpfCnpj($cpf);
        if (self::validaRuleCPF($cpf)) {
            $user = User::select(['id', 'nome', 'empresa_id'])->where('tipo', '!=', User::EMPRESA)
                ->whereEmpresaId($empresa_id)->whereHas('Curriculo', function ($q) use ($cpf) {
                    $q->select(['id', 'nome'])->withoutGlobalScopes()->whereCpf($cpf);
                })->first();

            $curriculo = $user->Curriculo()->withoutGlobalScopes()->first();

            if ($curriculo) {
                return (object)["msg" => "CPF ja cadastrado", "error" => true];
            }
        }
//        return true;

    }

    /**
     * @param $empresa_id
     * @return \Illuminate\Validation\Rules\Unique
     */
    public static function RuleCpfUnique($cpf, $empresa_id)
    {
        return Rule::unique('curriculos')->where('cpf', $cpf)->where(function ($query) use ($empresa_id) {
            $query->join('users', function ($join) use ($empresa_id) {
                $join->on('users.id', '=', 'curriculos.id')
                    ->where('users.empresa_id', '=', $empresa_id);
            });
        });
    }


    /**
     * @param $cpf
     * @return string
     */
    public static function mascaraCpf($cpf)
    {
        $sonumero = preg_replace("/[^0-9]/", "", $cpf);
        return substr($sonumero, 0, 3) . '.' . substr($sonumero, 3, 3) . '.' . substr($sonumero, 6, 3) . '-' . substr($sonumero, 9, 2);
    }

    /**
     * @param $cpf
     * @return string
     */
    public static function mascaraCep($cep)
    {
        $sonumero = preg_replace("/[^0-9]/", "", $cep);
        return substr($sonumero, 0, 5) . '-' . substr($sonumero, 5, 3);
    }

    /**
     * @param $telefone
     * @return mixed|string
     */
    public static function mascaraTelefone($telefone)
    {
        $formatedPhone = preg_replace('/[^0-9]/', '', $telefone);
        $matches = [];
        preg_match('/^([0-9]{2})([0-9]{4,5})([0-9]{4})$/', $formatedPhone, $matches);
        if ($matches) {
            $primeiro = strlen($matches[2]) == 5 ? substr($matches[2], 0, 1) . ' ' . substr($matches[2], 1, 4) : $matches[2];
            return '(' . $matches[1] . ') ' . $primeiro . '-' . $matches[3];
        }

        return $telefone;
    }

    /**
     * @param $dados
     * @return void
     */
    public static function LogFormatado($dados)
    {
        \Log::debug(print_r(json_encode($dados, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 1));
    }

    /**
     * Busca a empresa ou filial
     * @param $centro_custo_filial_id
     * @param $empresa_id
     * @return array
     */
    public static function getEmpresaFilialMatriz($centro_custo_filial_id, $empresa_id)
    {
        return !is_null($centro_custo_filial_id) ? Sistema::getFilial($empresa_id, $centro_custo_filial_id) : Sistema::getEmpresa($empresa_id);
    }

    /**
     * @param $empresa_id
     * @return array
     */
    public static function getEmpresa($empresa_id): array
    {
        $cliente = Cliente::select([
            'id',
            'razao_social',
            'cnpj',
            'nome_fantasia',
            'cep',
            'logradouro',
            'numero',
            'complemento',
            'bairro',
            'municipio',
            'uf'
        ])->find($empresa_id);

        if (!$cliente) {
            return [];
        }

        return [
            'id' => $cliente->id,
            'empresa_id' => $empresa_id,
            'razao_social' => $cliente->razao_social,
            'cnpj' => $cliente->cnpj,
            'nome_fantasia' => $cliente->nome_fantasia,
            'endereco_completo' => mb_strtoupper($cliente->endereco_completo),
            'logo' => self::convertBase3($cliente->Logo->first()->urlThumb, true),
            'filial' => false,
        ];
    }

    /**
     * @param $empresa_id
     * @param $centro_custo_filial_id
     * @return array
     */
    public static function getFilial($empresa_id, $centro_custo_filial_id): array
    {
        $centroCustoFilial = CentroCustoFilial::with('Filial:id,empresa_id,dados')->find($centro_custo_filial_id);

        if (!$centroCustoFilial) {
            return [];
        }

        $filial = $centroCustoFilial->Filial;
        $dados = (object)$filial->dados;

        $logo = isset($dados->logo) ? self::convertBase3($dados->Logo->first()->urlThumb, true) : self::convertBase3(Cliente::find($empresa_id)->Logo->first()->urlThumb, true);

        return [
            'id' => $filial->id,
            'empresa_id' => $empresa_id,
            'razao_social' => $dados->razao_social,
            'cnpj' => $dados->cnpj,
            'nome_fantasia' => $dados->nome_fantasia,
            'endereco_completo' => mb_strtoupper($filial->getEnderecoCompletoAttribute()),
            'logo' => $logo,
            'filial' => true,
        ];
    }

    /**
     * @param $funcionario_id
     * @param $empresa_id
     * @return object
     */
    public static function getColaboradorDados($funcionario_id, $empresa_id): object
    {
        $feedbackCurriculo = FeedbackCurriculo::select(['id', 'curriculo_id'])
            ->whereCurriculoId($funcionario_id)
            ->whereEmpresaId($empresa_id)
            ->with([
                'Curriculo:id,nome,nascimento,rg,orgao_expeditor,cpf',
                'Admissao:id,centro_custo_id,filial,centro_custo_filial_id,cargo,funcao,matricula,data_admissao,feedback_id',
                'Admissao.CentroCusto:id,label',
                'Admissao.AreaEtiqueta:id,label',
                'Admissao.DadosAdmissoes',
                'Admissao.CentroCustoFilial:id,empresa_id,centro_custo_id,cliente_filial_id',
            ])
            ->first();

        $ctps_numero = $feedbackCurriculo->Admissao->DadosAdmissoes->ctps_numero ?? '';
        $ctps_serie = $feedbackCurriculo->Admissao->DadosAdmissoes->ctps_serie ?? '';

        return (object)[
            'nome' => mb_strtoupper(User::select(['nome'])->whereId($funcionario_id)->first()->nome ?? "NÃO INFORMADO {$funcionario_id}"),
            'nascimento' => $feedbackCurriculo->Curriculo->nascimento ?? "NÃO INFORMADO",
            'cpf' => $feedbackCurriculo->Curriculo->cpf ?? "NÃO INFORMADO",
            'matricula' => $feedbackCurriculo->Admissao->matricula ?? "NÃO INFORMADO",
            'data_admissao' => $feedbackCurriculo->Admissao->data_admissao ?? "NÃO INFORMADO",
            'cargo' => $feedbackCurriculo->Admissao->cargo ?? "NÃO INFORMADO",
            'funcao' => $feedbackCurriculo->Admissao->funcao ?? "NÃO INFORMADO",
            'centro_custo' => $feedbackCurriculo->Admissao->CentroCusto->label ?? "NÃO INFORMADO",
            'area' => $feedbackCurriculo->Admissao->AreaEtiqueta->label ?? "NÃO INFORMADO",
            'pertence_filial' => $feedbackCurriculo->Admissao->filial ?? "NÃO INFORMADO",
            'ctps' => $ctps_numero . '-' . $ctps_serie,
            'centro_custo_filial' => self::getFilial($empresa_id, $feedbackCurriculo->Admissao->centro_custo_filial_id) ?: null,
        ];
    }


    public static function exportaExcelPython($usuario, $local, $dados, $nome_arquivo)
    {
        try {
            $caminho_script = base_path("scripts/python/export.py");
            $autenticado = $usuario;
            $jsonData = json_encode($dados);
            Redis::set($nome_arquivo, $jsonData);
            $redisNome = env('REDIS_PREFIX') . $nome_arquivo;
            $resultado = exec("python3 $caminho_script $nome_arquivo $redisNome");
            if ($resultado != "") {
                $nomedoarquivo = "$nome_arquivo.xls";
                Exportacao::create([
                    'user_id' => $autenticado->id,
                    'arquivo' => $nomedoarquivo,
                    'local' => $local,
                    'removido' => false,
                ]);
                Event::dispatch(new NotificacaoEvent([
                    'user_id' => $autenticado->id,
                    'local' => $local,
                ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
            } else {
                throw new \Exception("Erro ao gerar o arquivo");
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * @param $nomeCache
     * @param $empresa_id
     * @param $usuario_id
     * @return string
     */
    public static function nomeCache($nomeCache, $empresa_id)
    {
        $empresa_id = $empresa_id ?? auth()->user()->empresa_id;
        $nomedocache = "{$nomeCache}_{$empresa_id}";
        return $nomedocache;
    }

}
