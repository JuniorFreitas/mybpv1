<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Lancamento
 *
 * @property int $id
 * @property int|null $cliente_id
 * @property int $quem_cadastrou
 * @property int|null $quem_alterou
 * @property int $plano_id
 * @property string|null $descricao
 * @property float $valor
 * @property float $saldo
 * @property string $operacao
 * @property mixed $data_hora
 * @property mixed|null $data_pendente quando vai receber ou pagar
 * @property mixed|null $data_hora_concluido quando recebeu ou pagou
 * @property bool $concluido
 * @property mixed|null $created_at
 * @property mixed|null $updated_at
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LancamentoForma[] $Formas
 * @property-read int|null $formas_count
 * @property-read \App\Models\PlanoConta|null $PlanoConta
 * @property-read \App\Models\User|null $QuemAlterou
 * @property-read \App\Models\User|null $QuemCadastrou
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $credito
 * @property-read mixed $debito
 * @property-read mixed $operacao_text
 * @property-read mixed $saldo_format
 * @property-read mixed $valor_format
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereConcluido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereDataHora($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereDataHoraConcluido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereDataPendente($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereOperacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento wherePlanoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereQuemAlterou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereQuemCadastrou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereValor($value)
 * @mixin \Eloquent
 * @property-read mixed $dias_atraso
 * @property-read mixed $dias_atraso_concluido
 * @property-read \App\Models\User|null $Empresa
 * @property int $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereEmpresaId($value)
 */
class Lancamento extends Model {
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'lancamento';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    public const DEBITO = 'D';
    public const CREDITO = 'C';

    public $timestamps = true;
    protected $table = 'lancamentos';
    protected $fillable = [
        'empresa_id',
        'quem_cadastrou',
        'quem_alterou',
        'plano_id',
        'descricao',
        'valor',
        'saldo',
        'operacao',
        'data_hora',
        'data_pendente',
        'data_hora_concluido',
        'concluido',

    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'quem_cadastrou' => 'int',
        'quem_alterou' => 'int',
        'plano_id' => 'int',
        'descricao' => 'string',
        'valor' => 'float',
        'saldo' => 'float',
        'operacao' => 'string',
        'data_hora' => 'datetime:d/m/Y à\s H:i',
        'data_pendente' => 'datetime:d/m/Y',
        'data_hora_concluido' => 'datetime:d/m/Y à\s H:i',
        'concluido' => 'boolean',

        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected $appends = [
        'valorFormat',
        'saldoFormat',
        'debito',
        'credito',
        'operacaoText',
        'diasAtraso',
        'diasAtrasoConcluido',
    ];


    protected static function booted() {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
            $model->quem_cadastrou = auth()->id();
        });

        static::updating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
            $model->quem_alterou = auth()->id();
        });

        static::addGlobalScope(new ScopeEmpresa());
    }

    public function PlanoConta() {
        return $this->hasOne(PlanoConta::class, 'id', 'plano_id');
    }

    public function Formas() {
        return $this->hasMany(LancamentoForma::class, 'lancamento_id', 'id');
    }

    public function QuemCadastrou() {
        return $this->hasOne(User::class, 'id', 'quem_cadastrou');
    }

    // Retorna uma lista de id_lancamentos para poder trabalhar com editar e excluir

    public function QuemAlterou() {
        return $this->hasOne(User::class, 'id', 'quem_alterou');
    }

    // retorna um array com id_lancamentos numa determinada data/hora

    public function Empresa() {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    // STATICOS

    public static function cadastrar($dataHora, $EMPRESA_ID, $PLANO_ID, $descricao, $valor) {

        // 1° - Verificar se é um lançamento de repasse de aluguel para o locador, na conta do locador
        $cliente = Cliente::find($EMPRESA_ID);

        // 2° - Cadastrar o lançamento
        $hoje = new DataHora($dataHora);


        // saber como vai ficar o saldo, depois do lançamento
        /*$saldo =  self::saldoLancamentoAnterior($hoje->dataHoraInsert(),$ID_CONTA,$ID_IMOVEL,NULL);
        $saldoTotal =self::saldoLancamentoAnterior($hoje->dataHoraInsert(),$ID_CONTA,NULL,NULL);
        $saldo += $valor; // saldo com o valor que vai ser adicionado/removido
        $saldoTotal+= $valor;*/

        $lancamento = new Lancamento();
        $lancamento->plano_id = $PLANO_ID;
        $lancamento->descricao = $descricao;
        $lancamento->valor = $valor;
        $lancamento->saldo = 0;
        $lancamento->operacao = $valor >= 0 ? self::CREDITO : self::DEBITO;
        $lancamento->data_hora = $hoje->dataHoraInsert();


        $lancamento->save();
        $lancamento->refresh();

        $saldo = self::saldoLancamentoAnterior($hoje->dataHoraInsert(), $EMPRESA_ID, $lancamento->id);
        $lancamento->saldo = $valor + $saldo;
        $lancamento->save();

        // 5° - Atualizar o saldo de todos os lançamentos seguintes (de toda a conta)
        Lancamento::where('data_hora', '>', $hoje->dataHoraInsert())
            ->increment('saldo', $valor);

        // 7° Retornar o lançamento
        return $lancamento;

    }

    // retorna null ou o queryBuild de Id de lancamentos

    public static function saldoLancamentoAnterior($dataHora, $EMPRESA_ID, $ID_LANCAMENTO_DESCONSIDERAR = NULL) {

        $campo = "saldo";

        $dataHora = new DataHora($dataHora);

        $consulta = Lancamento::select(['id', 'data_hora', 'saldo']);

        // 1° - Verificar quantos lançamentos tem colocado essa dataHora
        $quantidadeDeLancamentos = self::quantidadeLancamentos($dataHora->dataHoraInsert(), $EMPRESA_ID);

        // 1.1 Se não tem nenhum lançamento para essa nova data, entao fica facil, é pegar algum lançamento anterior a essa dataHora
        //if ($quantidadeDeLancamentos <= 1) {
        if ($quantidadeDeLancamentos == 0) {
            $consulta
                //->where('data_hora','<=',$dataHora->dataHoraInsert())
                ->where('data_hora', '<', $dataHora->dataHoraInsert());
            if ($ID_LANCAMENTO_DESCONSIDERAR != NULL) {
                $consulta->whereNotIn('id', [$ID_LANCAMENTO_DESCONSIDERAR]);
            }
            $consulta->orderByDesc('data_hora')->orderByDesc('id')->take(1);

            //retornar
            $total = 0.00;
            foreach ($consulta->get() as $registro) {

                $id = $registro->id;
                //$data_horaF = $registro->dataHoraLancamento();
                $data_horaF = $registro->data_hora;
                if ($registro->id >= $ID_LANCAMENTO_DESCONSIDERAR) {
                    continue;
                }
                /*if($achou==false && $registro->id != $ID_LANCAMENTO_DESCONSIDERAR){
                    continue;
                }
                if($achou==false && $registro->id == $ID_LANCAMENTO_DESCONSIDERAR){
                    $achou=true;
                    continue;
                }*/

                $total = $registro->saldo;
                break;
            }

            return $total;
        }
        // 1.2 Se só tem 1, pode ser o mesmo lançamento, ou outro. Entao desconsidera o ID_LANCAMENTO_DESCONSIDERAR caso seja ele mesmo.
        if ($quantidadeDeLancamentos >= 1) {
            //if ($quantidadeDeLancamentos >= 2) {
            //fazer um distinct de data_hora menores e buscar apenas nesse intervalo. depois disso buscar o saldo do lançamento logo em seguinda.
            $consulta->select('data_hora')->distinct('data_hora')
                ->where('data_hora', '<=', $dataHora->dataHoraInsert());
            if ($ID_LANCAMENTO_DESCONSIDERAR != NULL) {
                $consulta->whereNotIn('id', [$ID_LANCAMENTO_DESCONSIDERAR]);
            }
            //-------------------
            $dataUnicas = $consulta->orderByDesc('data_hora')->take(2)->get()->transform(function (Lancamento $lan) {
                $data = new DataHora($lan->data_hora);
                $lan->data_horaTeste = $data->dataHoraInsert();
                return $lan;
            })
                ->pluck('data_horaTeste')->toArray();

            if (count($dataUnicas) > 0) {
                $dataAnterior = count($dataUnicas) > 1 ? $dataUnicas[1] : $dataUnicas[0];
            } else {
                $dataAnterior = $dataHora->dataHoraInsert();
            }


            //------------------
            //$consulta = Lancamento::select("$campo as saldo"); // repetir esse comando para nao dar erro no distinct
            $consulta = Lancamento::select(['id', 'data_hora', 'saldo']); // repetir esse comando para nao dar erro no distinct
            $consulta
                //->where('data_hora','<=',$dataHora->dataHoraInsert())
                ->whereBetween('data_hora', [$dataAnterior, $dataHora->dataHoraInsert()]);

            /*if ($ID_LANCAMENTO_DESCONSIDERAR != NULL) {
                $consulta->whereNotIn('id', [$ID_LANCAMENTO_DESCONSIDERAR]);
            }*/
            $consulta->orderByDesc('data_hora')->orderByDesc('id');

            // 1.2 Se já tem mais de 1, pode ser o mesmo lançamento e mais outros. Fazer os testes

            /*if($quantidadeDeLancamentos > 1){
                // 2° - pegar a lista desses ID de lançamentos na mesma data.
                $listaDeIDs = self::listaIdLancamentoLancamentos($dataHora->dataHoraInsert(),$ID_CONTA,$ID_IMOVEL);
                if($ID_LANCAMENTO_DESCONSIDERAR != NULL){
                    $listaDeIDs[] = $ID_LANCAMENTO_DESCONSIDERAR;
                }
                sort($listaDeIDs); // ordenar

                // Se passar um $ID_LANCAMENTO_DESCONSIDERAR, e esse ID, não esta no incio da fila, entao é desse lançamento, para tras
                $sinalDeMenor= "<=";
                if(array_search($ID_LANCAMENTO_DESCONSIDERAR,$listaDeIDs) > 0){
                    $sinalDeMenor = "<=";
                }
                if(array_search($ID_LANCAMENTO_DESCONSIDERAR,$listaDeIDs) === 0){
                    $sinalDeMenor = "<";
                }

                $consulta
                    ->where('data_hora',$sinalDeMenor,$dataHora->dataHoraInsert())
                    ->where('conta_id',$ID_CONTA);

                if($ID_IMOVEL!=null){
                    $consulta->where('imovel_id',$ID_IMOVEL);
                }
                if($ID_LANCAMENTO_DESCONSIDERAR!=NULL && array_search($ID_LANCAMENTO_DESCONSIDERAR,$listaDeIDs) > 0){ // se tem um lançamento atras dele, desconsidera-lo
                    $consulta->where('id','<',$ID_LANCAMENTO_DESCONSIDERAR);
                }
                $consulta->orderByDesc('data_hora')->orderByDesc('id')->take(1);
            }*/

            $total = 0.00;

            $lista = $consulta->get();
            $quantidade = $lista->count();
            $indexBusca = $lista->search(function ($item, $key) use ($ID_LANCAMENTO_DESCONSIDERAR) {
                return $item->id == $ID_LANCAMENTO_DESCONSIDERAR;
            });
            if ($quantidade > 1) {
                $registro = $lista[$indexBusca + 1];
                $id = $registro->id;
                $data_horaF = $registro->data_hora;

                $total = $registro->saldo; // pode ser a coluna saldo ou saldo_total
            }


            return $total;
        }
    }

    public static function quantidadeLancamentos($dataHora, $EMPRESA_ID) {
        $dataHora = new DataHora($dataHora);

        $consulta = Lancamento::whereDataHora($dataHora->dataHoraInsert());
        return $consulta->orderBy('data_hora', 'asc')->count();

    }


    public function getDiasAtrasoAttribute() {

        if ($this->data_pendente == null) {
            return false;
        }
        $pendente = new DataHora($this->data_pendente);
        $hoje = new DataHora();
        return DataHora::diferencaDias($pendente->dataInsert(), $hoje->dataInsert());
    }

    public function getDiasAtrasoConcluidoAttribute() {

        if ($this->data_pendente == null) {
            return false;
        }
        $pendente = new DataHora($this->data_pendente);
        $hoje = new DataHora($this->data_hora_concluido);
        return DataHora::diferencaDias($pendente->dataInsert(), $hoje->dataInsert());
    }

    public function getValorFormatAttribute() {
        return number_format($this->valor, 2, ",", ".");
    }

    // Lançar um debito na conta do cliente

    public function getSaldoFormatAttribute() {
        return number_format($this->saldo, 2, ",", ".");
    }

    // Getway de lancarCredito e lancarDebito

    public function getDebitoAttribute() {
        return $this->operacao == self::DEBITO ? true : false;
    }

    // Editar o lançamento ----------------------------------------------------------

    public function getCreditoAttribute() {
        return $this->operacao == self::CREDITO ? true : false;
    }

    // Deletar o lançamento--------------------------------------------------------

    public function getOperacaoTextAttribute() {
        if ($this->credito) {
            return "Crédito";
        } else {
            return "Debito";
        }
    }

    // retorna o idlancamento_ccl, retorna 0 se não encontrar

    public function editar($PLANO_ID, $descricao, $novoValor, $OPERACAO, $dataHora, $feito = true, $data_hora_futura = '') {
        $dataHora = new DataHora($dataHora);

        $valorAtual = $this->valor;
        $data_hora_futura = new DataHora($data_hora_futura);


        //$saldoAtual = $this->saldo(false);
        //echo "valor atual (imóvel {$this->idImovel()}): ".$valorAtual."<br>";
        //$saldoA = self::saldoLancamentoAnterior($dataHora->dataHoraInsert(),$this->Conta->id,$ID_IMOVEL,$this->id);
        //echo "Saldo anterior antes (imóvel {$this->idImovel()}): $saldoA <br>";

        $EMPRESA_ID = auth()->user()->cliente_id;
        // 2.1 - Remover isso nos saldos_totais na frente desse lançamento, pois irá mudar de data e de imovel. (desconsiderar o proprio lancamento)
        $lancamentosSeguintes = self::listaDeLancamentoSeguintes($this->id, $this->data_hora, $EMPRESA_ID);

        Lancamento::whereIn('id', $lancamentosSeguintes)->decrement('saldo', $valorAtual);

        $this->data_hora = $dataHora->dataHoraInsert();
        $this->save();

        // 3° - Verificar o novo saldo, se esta colocando agora um ID_IMOVEL ou não

        //$saldoAtual = $this->saldo;
        //echo "valor atual: ".$saldoAtual."<br>";
        $saldoLancamentoAnterior = self::saldoLancamentoAnterior($dataHora->dataHoraInsert(), $EMPRESA_ID, $this->id);

        $NOVO_SALDO = $saldoLancamentoAnterior + $novoValor; // se esta colocando agora um ID_IMOVEL, entao o saldo anterior é do imóvel
        //echo "Saldo anterior (da nova data): ".$saldoLancamentoAnterior."<br>";
        //echo "valor Atual + Saldo anterior (NOVO SALDO Atualizado): ".$NOVO_SALDO."<br>";

        // 4.1 - Atualizar os saldosTotais de todos os outros lançamentos da conta
        $lancamentosSeguintes = self::listaDeLancamentoSeguintes($this->id, $dataHora->dataHoraInsert(), $EMPRESA_ID);

        // Colocar esse novoValor em todos os saldos . (desconsiderar o proprio lancamento)

        Lancamento::whereIn('id', $lancamentosSeguintes)
            ->whereNotIn('id', [$this->id]) // desconsidera o proprio lançamento que ainda nao mudou de posição
            ->increment('saldo', $novoValor);


        // 5° - Finalmente atualizar este lançamento
        $this->data_hora = $dataHora->dataHoraInsert();
        $this->descricao = $descricao;
        $this->plano_id = $PLANO_ID;
        $this->valor = $novoValor;
        $this->saldo = $NOVO_SALDO;
        $this->operacao = $OPERACAO;
        $this->save();


        //echo "--------------------------------<BR>";
        //echo "novoValor: $novoValor  -  novoSaldo: $NOVO_SALDO";


    }

    // retorna o Lancamento, retorn 0 se não encontrar

    private static function listaDeLancamentoSeguintes($ID_LANCAMENTO, $dataHora, $EMPRESA_ID) {

        $LISTA = collect(); // lista de lancamentos

        $lancamento = Lancamento::find($ID_LANCAMENTO);

        $dataHora = new DataHora($dataHora);
        // 1° - verificar se esta com outros lançamentos e atualizar
        $quantidadeLancamentos = self::quantidadeLancamentos($dataHora->dataHoraInsert(), $EMPRESA_ID);

        if ($quantidadeLancamentos > 1) { // se tiver mais do que 1 (inclui o próprio $ID_LANCAMENTO )
            // trabalhar apenas com os lançamentos com data/hora iguais
            //$consulta = Lancamento::whereEmpresaId($EMPRESA_ID);
            //$consulta->where('data_hora','>=',$dataHora->dataHoraInsert())
            $consulta = Lancamento::whereDataHora($dataHora->dataHoraInsert())
                //->where('id','>',$lancamento->id)
                ->orderBy('data_hora', 'asc')
                ->orderBy('id', 'asc');

            //foreach ($consulta->select(['id'])->get()->pluck('id')->toArray() as $id_lancamento) {
            foreach ($consulta->get() as $objLancamento) {
                $LISTA[] = $objLancamento;
            }

        }
        // verificar agora, em que ordenação esse lançamento vai ficar
        $LISTA[] = $lancamento;
        $LISTA = $LISTA->sortBy('data_hora')->sortBy('id');

        //todo: feito a correcao de bug aqui do saldo. verificar depois o metodo de cadastrar

        // Trabalhar com os lançamentos seguintes
        //$consulta = Lancamento::whereEmpresaId($EMPRESA_ID);
        $consulta = Lancamento::whereNotIn('id', [$lancamento->id]) // excluir esse lançamento dos seguinte, por é ele mesmo e nao pode.
        ->where('data_hora', '>', $dataHora->dataHoraInsert())
            ->orderBy('data_hora', 'asc')
            ->orderBy('id', 'asc');

        //foreach ($consulta->select(['id'])->get()->pluck('id')->toArray() as $id_lancamento) {
        foreach ($consulta->get() as $objLancamento) {
            $LISTA[] = $objLancamento;
        }
        $LISTA = $LISTA->sortBy('data_hora')->sortBy('id')->filter(function (Lancamento $obj) use ($lancamento) {
            if ($obj->data_hora == $lancamento->data_hora) {
                if ($obj->id > $lancamento->id) {
                    return true; // todos os lancamentos seguintes depois de ordenar, adicionado verificação de lançamentos iguais no mesmo dia
                } else {
                    return false;
                }
            }
            if ($lancamento->id === $obj->id) {
                return false;
            }
            return true;
        });
        //$collection = collect($LISTA);
        //$unique = $collection->unique();
        $unique = $LISTA->unique();
        $teste = $unique->values()->pluck('id')->toArray();
        return $unique->values()->pluck('id')->toArray();


    }

    // Returna idlancamento_ccl (apenas o ultimo) ou FALSE se tem lançamentos futuros à data informada

    public function excluir() {

        $valor = $this->valor;

        //1° - Atualizar o saldo total
        $listaDeLancamentos = self::listaDeLancamentoSeguintes($this->id, $this->data_hora, $this->empresa_id);
        // Trabalhar com os lançamentos seguintes
        Lancamento::whereIn('id', $listaDeLancamentos)->decrement('saldo', $valor);

        // 4° - Finalmente apagar esse lançamento
        $this->delete(); // já exclui as formas de pagamento em cascata

        return response()->json([], 200);

    }

}
