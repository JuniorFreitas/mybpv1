<?php

namespace App\Models;

use Auth;
use finfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * App\Models\Arquivo
 *
 * @property int $id
 * @property int|null $quem_enviou
 * @property string $nome
 * @property bool $imagem
 * @property string|null $layout
 * @property string $extensao
 * @property string $file
 * @property string|null $thumb
 * @property int $bytes
 * @property bool $temporario
 * @property string|null $chave
 * @property string|null $disco
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $url
 * @property-read string $url_delete
 * @property-read string $url_download
 * @property-read string $url_thumb
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo query()
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereChave($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereDisco($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereExtensao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereImagem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereLayout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereQuemEnviou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereTemporario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Arquivo extends Model
{
    protected $table = 'arquivos';

    protected $fillable = [
        'quem_enviou',
        'nome',
        'imagem',
        'layout',
        'extensao',
        'file',
        'thumb',
        'bytes',
        'temporario',
        'chave',
        'disco',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'id' => 'int',
        'quem_enviou' => 'int', //user_id que enviou
        'nome' => 'string', // ou titulo
        'imagem' => 'boolean',
        'layout' => 'string',
        'extensao' => 'string',
        'file' => 'string',
        'thumb' => 'string',
        'bytes' => 'int',
        'temporario' => 'boolean',
        'chave' => 'string',
        'disco' => 'string',
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
    ];
    public $timestamps = true;

    protected $appends = ['url', 'urlThumb', 'urlDownload', 'urlDelete'];

    // MIME_TYPE ARQUIVOS
    const MIME_GIF = "image/gif";
    const MIME_JPG = "image/jpg";
    const MIME_JPEG = "image/jpeg";
    const MIME_PNG = "image/png";
    const MIME_PDF = "application/pdf";
    const MIME_DOCX = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
    const MIME_DOC = "application/msword";
    const MIME_XLS = "application/vnd.ms-excel";
    const MIME_XLSX = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
    const MIME_PPT = "application/vnd.ms-powerpoint";
    const MIME_PPTX = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
    const MIME_PPS = "application/vnd.ms-powerpoint";
    const MIME_PPSX = "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
    const MIME_TXT = "text/plain";
    const MIME_RAR = "application/x-rar-compressed";
    const MIME_RAR2 = "application/x-rar";
    const MIME_ZIP = "application/zip";
    const MIME_ZIP2 = "application/octet-stream";

    const MIMEAPENASIMAGENS = [
        self::MIME_JPG,
        self::MIME_JPEG,
        self::MIME_GIF,
        self::MIME_PNG,
    ];

    const MIMEAPENASIMAGENSPDF = [
        self::MIME_JPG,
        self::MIME_JPEG,
        self::MIME_GIF,
        self::MIME_PNG,
        self::MIME_PDF,
    ];

    const MIMEAPENASDOCUMENTOS = [
        self::MIME_PDF,
        self::MIME_DOCX,
        self::MIME_DOC,
        self::MIME_XLS,
        self::MIME_XLSX,
        self::MIME_PPT,
        self::MIME_PPTX,
        self::MIME_PPS,
        self::MIME_PPSX,
    ];

    const MIMESTODOS = [
        self::MIME_GIF,
        self::MIME_JPG,
        self::MIME_JPEG,
        self::MIME_PNG,
        self::MIME_PDF,
        self::MIME_DOCX,
        self::MIME_DOC,
        self::MIME_XLS,
        self::MIME_XLSX,
        self::MIME_PPT,
        self::MIME_PPTX,
        self::MIME_PPS,
        self::MIME_PPSX,
        self::MIME_TXT,
        self::MIME_RAR,
        self::MIME_RAR2,
        self::MIME_ZIP,
        self::MIME_ZIP2,
    ];

    const S3 = 's3';
    const DISCO_CLOUD = 'disco-cloud';
    const DISCO_FOTOCURRICULO = 'disco-fotocurriculo';
    const DISCO_CLIENTE = 'disco-cliente';
    const DISCO_PROSPECT = 'disco-prospect';
    const DISCO_FORNECEDOR = 'disco-fornecedor';
    const DISCO_SERVICO_FORNECEDOR = 'disco-servicofornecedor';
    const DISCO_OCORRENCIA = 'disco-ocorrencia';
    const DISCO_TREINAMENTO = 'disco-treinamento';
    const DISCO_CIH = 'evidencia-cih';
    const DISCO_MEDIDAS = 'evidencia-medidas';
    const DISCO_DOCUMENTOS_PRE_ADMISSAO = 'disco-documentospreadmissao';
    const DISCO_DOSSIE = 'disco-dossie';
    const DISCO_TREINAMENTO_LISTA_PRESENCA = 'listapresenca';
    const DISCO_REQUISICAO_VAGA = 'requisicao-vaga';
    const DISCO_PUBLICO = 'public';
    const DISCO_PONTO_ELETRONICO = 'disco-ponto-eletronico';
    const DISCO_PERFIL_USUARIO = 'disco-perfil-usuario';
    const DISCO_WEEKLY_REPORT = 'disco-weekly-report';
    const DISCO_EXCEL = 'disco-excel';
    const DISCO_EXAMES = 'disco-exames';
    const DISCO_AFASTAMENTO = 'disco-afastamento';
    const DISCO_CONTROLE_EXAMES_RESULTADO = 'disco-controle-exames-resultado';
    const DISCO_DOCUMENTO_CONTRATO = 'disco-documento-contrato';
    const DISCO_DOCUMENTO_EMPRESA = 'disco-documento-empresa';
    const DISCO_DOCUMENTO_SSMA = 'disco-documento-ssma';
    const DISCO_MOVIMENTACAO = 'disco-movimentacao';
    const DISCO_ASSINATURA = 'disco-assinatura';
    const DISCO_EXPORTACAO = 'disco-exportacao';

    const LISTAGEM_DISCOS = [
        self::DISCO_CLOUD,
        self::DISCO_FOTOCURRICULO,
        self::DISCO_CLIENTE,
        self::DISCO_PROSPECT,
        self::DISCO_FORNECEDOR,
        self::DISCO_SERVICO_FORNECEDOR,
        self::DISCO_OCORRENCIA,
        self::DISCO_TREINAMENTO,
        self::DISCO_CIH,
        self::DISCO_MEDIDAS,
        self::DISCO_DOCUMENTOS_PRE_ADMISSAO,
        self::DISCO_DOSSIE,
        self::DISCO_TREINAMENTO_LISTA_PRESENCA,
        self::DISCO_REQUISICAO_VAGA,
        self::DISCO_PUBLICO,
        self::DISCO_PONTO_ELETRONICO,
        self::DISCO_PERFIL_USUARIO,
        self::DISCO_WEEKLY_REPORT,
        self::DISCO_EXCEL,
        self::DISCO_EXAMES,
        self::DISCO_AFASTAMENTO,
        self::DISCO_CONTROLE_EXAMES_RESULTADO,
        self::DISCO_DOCUMENTO_CONTRATO,
        self::DISCO_DOCUMENTO_EMPRESA,
        self::DISCO_DOCUMENTO_SSMA,
        self::DISCO_MOVIMENTACAO,
        self::DISCO_ASSINATURA,
        self::DISCO_EXPORTACAO,
    ];

    /**
     * @return string
     */
    public function getUrlAttribute()
    {
        if (in_array($this->disco, self::LISTAGEM_DISCOS)) {
            return config('filesystems.disks.' . $this->disco . '.urlShow') . "/{$this->file}";
        }
        return "";
    }

    /**
     * @return string
     */
    public function getUrlThumbAttribute()
    {
        if (in_array($this->disco, self::LISTAGEM_DISCOS)) {
            return config('filesystems.disks.' . $this->disco . '.urlThumb') . "/{$this->file}";
        }
        return "";
    }

    /**
     * @return string
     */
    public function getUrlDownloadAttribute()
    {
        if (in_array($this->disco, self::LISTAGEM_DISCOS)) {
            return config('filesystems.disks.' . $this->disco . '.urlDownload') . "/{$this->file}";
        }
        return "";
    }

    /**
     * @return string
     */
    public function getUrlDeleteAttribute()
    {
        if (in_array($this->disco, self::LISTAGEM_DISCOS)) {
            return config('filesystems.disks.' . $this->disco . '.urlDelete') . "/{$this->file}";
        }
        return "";
    }

    /**
     * @param $path
     * @return mixed|string
     */
    public static function getMimeType($path)
    {
        $file = $path;
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file);
        $partes = explode('; ', $mime);
        return $partes[0];
    }

    /**
     * @param $nome
     * @return string
     */
    public static function gerarNomeFoto($nome)
    {
        $partes = explode('.', $nome);
        return $partes[0] . '_g.' . $partes[1]; // nome_g.jpg por exemplo
    }

    /**
     * @param $nome
     * @return string
     */
    public static function gerarNomeThumb($nome)
    {
        $partes = explode('.', $nome);
        return $partes[0] . '_p.' . $partes[1]; // nome_p.jpg por exemplo
    }

    /**
     * @param $nome
     * @return mixed|string
     */
    public static function pegarNomeArquivo($nome)
    {
        $partes = explode('.', $nome);
        return $partes[0];
    }

    /**
     * @param $path
     * @return array
     */
    public static function pegarDimensoes($path)
    {
        $tamanhos = getimagesize($path);
        return [
            'largura' => $tamanhos[0],
            'altura' => $tamanhos[1],
        ];
    }

    /**
     * @param $path
     * @return bool
     */
    public static function seForImagem($path)
    {
        $mime = mime_content_type($path);

        $tipo = substr($mime, 0, 5);
        if ($tipo == "image") {
            return true;
        } else {
            return false;
        }

    }

    /**
     * @param $path
     * @return string
     */
    public static function pegarLayout($path)
    {
        if (self::seForImagem($path)) {
            $largura = Arquivo::pegarDimensoes($path)['largura'];
            $altura = Arquivo::pegarDimensoes($path)['altura'];
        }
        if ($largura == -1 || $altura == -1) {
            return "";
        }
        if ($largura > $altura) {
            return "paisagem";
        }
        if ($largura < $altura) {
            return "retrato";
        }
        if ($largura == $altura) {
            return "quadrado";
        }
    }

    /**
     * @param $path
     * @return false|mixed
     */
    public static function maiorComprimento($path)
    {
        if (self::seForImagem($path)) {
            $largura = self::pegarDimensoes($path)['largura'];
            $altura = self::pegarDimensoes($path)['altura'];
            return max($largura, $altura);
        }
        return false;
    }

    /**
     * @param $path
     * @param $novaLargura
     * @return array
     */
    public static function calculaLaguraAlturaProporcional($path, $novaLargura)
    {
        $largura = self::pegarDimensoes($path)['largura'];
        $altura = self::pegarDimensoes($path)['altura'];

        if ($largura > $altura) {
            $nova_largura = $novaLargura;
            $nova_altura = ($altura * $novaLargura) / $largura;
        } else {

            $nova_largura = ($largura * $novaLargura) / $altura;
            $nova_altura = $novaLargura;
        }

        return [
            'largura' => floor($nova_largura),
            'altura' => floor($nova_altura),
        ];
    }

    /**
     * @param Request $request
     * @param $nomePost
     * @param $nomeDisco
     * @return Arquivo
     */
    public static function gravaArquivoCliente(Request $request, $nomePost, $nomeDisco): Arquivo
    {
        //Dados do arquivo
        $path = $request->file($nomePost)->path();
        $nome = Arquivo::pegarNomeArquivo($request->file($nomePost)->getClientOriginalName());
        $bytes = $request->file($nomePost)->getSize();
        $extensao = $request->file($nomePost)->extension(); // sem ponto
        $imagem = Arquivo::seForImagem($path);
        $largura = null;
        $altura = null;
        if ($imagem) {
            $largura = Arquivo::pegarDimensoes($path)['largura'];
            $altura = Arquivo::pegarDimensoes($path)['altura'];
        }

        $nomeDoArquivo = $request->file($nomePost)->store(null, $nomeDisco); // grava o arquivo direto do request
        //Se for Arquivo de imagem fazer dois arquivos
        if ($imagem) {
            $pathOriginal = Storage::disk($nomeDisco)->path($nomeDoArquivo);

            $thumb_path = Storage::disk($nomeDisco)->path(Arquivo::gerarNomeThumb($nomeDoArquivo));
            $fotoGrande_path = Storage::disk($nomeDisco)->path(Arquivo::gerarNomeFoto($nomeDoArquivo));

            //Thumb
            $novas = Arquivo::calculaLaguraAlturaProporcional($pathOriginal, 200);
            Image::make($pathOriginal)->resize($novas['largura'], $novas['altura'])->save($thumb_path);

            //Grande
            $tamanhoFinal = Arquivo::maiorComprimento($pathOriginal) > 300 ? 300 : Arquivo::maiorComprimento($pathOriginal);
            $novas = Arquivo::calculaLaguraAlturaProporcional($pathOriginal, $tamanhoFinal);
            Image::make($pathOriginal)->resize($novas['largura'], $novas['altura'])->save($fotoGrande_path);

            Storage::disk($nomeDisco)->delete($nomeDoArquivo);//apagar o original
        }

        //Salvando no banco
        if ($imagem) {
            $dados = [
                'quem_enviou' => Auth::id(), //user_id que enviou
                'nome' => $nome, // ou titulo
                'imagem' => true,
                'layout' => self::pegarLayout($fotoGrande_path),
                'extensao' => "." . $extensao,
                'file' => Arquivo::gerarNomeFoto($nomeDoArquivo),
                'thumb' => Arquivo::gerarNomeThumb($nomeDoArquivo),
                'bytes' => $bytes,
                'temporario' => true,
                'chave' => $request->get('chave'),
                'disco' => $nomeDisco
            ];

        } else {
            $dados = [
                'quem_enviou' => Auth::id(), //user_id que enviou
                'nome' => $nome, // ou titulo
                'imagem' => false,
                'layout' => null,
                'extensao' => "." . $extensao,
                'file' => $nomeDoArquivo,
                'thumb' => null,
                'bytes' => $bytes,
                'temporario' => true,
                'chave' => $request->get('chave'),
                'disco' => $nomeDisco
            ];
        }

        $model = self::create($dados);
        return $model;
    }

    /**
     * @param Request $request
     * @param $nomePost
     * @param $nomeDisco
     * @return Arquivo
     */
    public static function gravaArquivo(Request $request, $nomePost, $nomeDisco): Arquivo
    {
        //Dados do arquivo
        $path = $request->file($nomePost)->path();
        $nome = Arquivo::pegarNomeArquivo($request->file($nomePost)->getClientOriginalName());
        $bytes = $request->file($nomePost)->getSize();
        $extensao = $request->file($nomePost)->extension(); // sem ponto
        $imagem = Arquivo::seForImagem($path);

        $nomeDoArquivo = $request->file($nomePost)->store(null, $nomeDisco); // grava o arquivo direto do request

        //Se for Arquivo de imagem fazer dois arquivos
        if ($imagem) {
            $file = $request->file($nomePost);
            //Imagem Grande
            $imgGrande = Image::make($file);
            $tamanhoFinal = Arquivo::maiorComprimento($file->path()) > 800 ? 800 : Arquivo::maiorComprimento($file->path());
            $tamanhoReal = Arquivo::calculaLaguraAlturaProporcional($file, $tamanhoFinal);
            $imgG = $imgGrande->resize($tamanhoReal['largura'], $tamanhoReal['altura'])->stream()->detach();
            Storage::disk($nomeDisco)->put($nomeDoArquivo, $imgG);

            //Thumb
            $imgThumb = Image::make($file);
            $nomeArquivoThumb = Arquivo::gerarNomeThumb($nomeDoArquivo);
            $tamanhoThumb = Arquivo::calculaLaguraAlturaProporcional($file, 200);

            $thumb = $imgThumb->resize($tamanhoThumb['largura'], $tamanhoThumb['altura'])->stream()->detach();
            Storage::disk($nomeDisco)->put($nomeArquivoThumb, $thumb);
        }

        $dados = [
            'quem_enviou' => auth()->id(),
            'nome' => $nome,
            'imagem' => $imagem,
            'layout' => $imagem ? self::pegarLayout($file->path()) : null,
            'extensao' => "." . $extensao,
            'file' => $nomeDoArquivo,
            'thumb' => $imagem ? $nomeArquivoThumb : null,
            'bytes' => $bytes,
            'temporario' => true,
            'chave' => $request->get('chave'),
            'disco' => $nomeDisco,
        ];

        $model = self::create($dados);
        return $model;
    }

    /**
     * @param Request $request
     * @param $nomePost
     * @param $nomeDisco
     * @return Arquivo
     */
    public static function gravaArquivoReal(Request $request, $nomePost, $nomeDisco): Arquivo
    {
        //Dados do arquivo
        $path = $request->file($nomePost)->path();
        $nome = Arquivo::pegarNomeArquivo($request->file($nomePost)->getClientOriginalName());
        $bytes = $request->file($nomePost)->getSize();
        $extensao = $request->file($nomePost)->extension(); // sem ponto
        $imagem = Arquivo::seForImagem($path);

        $nomeDoArquivo = $request->file($nomePost)->store(null, $nomeDisco); // grava o arquivo direto do request

        if ($imagem) {
            $file = $request->file($nomePost);
            //Thumb
            $imgThumb = Image::make($file);
            $nomeArquivoThumb = Arquivo::gerarNomeThumb($nomeDoArquivo);
            $tamanhoThumb = Arquivo::calculaLaguraAlturaProporcional($file, 100);

            $thumb = $imgThumb->resize($tamanhoThumb['largura'], $tamanhoThumb['altura'])->stream()->detach();
            Storage::disk($nomeDisco)->put($nomeArquivoThumb, $thumb);
        }

        $dados = [
            'quem_enviou' => auth()->id(),
            'nome' => $nome,
            'imagem' => $imagem,
            'layout' => $imagem ? self::pegarLayout($file->path()) : null,
            'extensao' => "." . $extensao,
            'file' => $nomeDoArquivo,
            'thumb' => $imagem ? $nomeArquivoThumb : null,
            'bytes' => $bytes,
            'temporario' => true,
            'chave' => $request->get('chave'),
            'disco' => $nomeDisco,
        ];

        $model = self::create($dados);
        return $model;

    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function excluir()
    {
        $disco = Storage::disk($this->disco);
        if ($disco->exists($this->file)) {
            if ($this->imagem) {
                $disco->delete($this->thumb);
            }
            $disco->delete($this->file);
            $this->delete();
            return true;
        }
        return false;
    }

    /**
     * @param Request $request
     * @param array $permitidos
     * @param $disco
     * @return \Illuminate\Http\JsonResponse
     */
    public static function uploadAnexos(Request $request, array $permitidos, $disco)
    {
        if ($request->file('arquivo')->isValid()) {
            $mimeType = $request->file('arquivo')->getMimeType();

            if (in_array($mimeType, $permitidos)) {
                $arquivo = Arquivo::gravaArquivo($request, 'arquivo', $disco);
                return response()->json($arquivo, 201);
            }

            return response()->json([
                'msg' => "O upload do arquivo \"{$request->file('arquivo')->getClientOriginalName()}\" falhou. Permitidos apenas " . implode(",", $permitidos),
                'erros' => []
            ], 400);

        }

        return response()->json([
            'msg' => "O upload do anexo falhou",
            'erros' => []
        ], 400);

    }

    /**
     * @param $disco
     * @param $arquivo
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public static function anexoShow($disco, $arquivo)
    {
        if (Storage::disk($disco)->exists($arquivo)) {
            return \Storage::disk($disco)->response($arquivo);
        }
        abort(404);
    }

    /**
     * @param $disco
     * @param $arquivo
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public static function anexoDownload($disco, $arquivo)
    {
        if (Storage::disk($disco)->exists($arquivo)) {
            $item = Arquivo::whereFile($arquivo)->whereDisco($disco)->orWhere('thumb', $arquivo)->first(['nome', 'extensao']);
            return Storage::disk($disco)->download($arquivo, $item->nome . $item->extensao);
        }
        abort(404);
    }

    /**
     * @param $disco
     * @param $arquivo
     * @return bool|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public static function anexoDelete($disco, $arquivo)
    {
        $model = Arquivo::whereFile($arquivo)
            ->whereDisco($disco)
            ->orWhere('thumb', $arquivo)
            ->first(['id', 'nome', 'file', 'imagem', 'thumb', 'extensao', 'temporario']);

        if ($model && $model->temporario) {
            $discoStorage = Storage::disk($disco);
            if ($discoStorage->exists($arquivo)) {
                if ($model->imagem) {
                    $discoStorage->delete($model->thumb);
                }
                $discoStorage->delete($model->file);
                $model->delete();
                return true;
            }
            return response("", 404);
        }
        return response("", 404);
    }

    public static function apagaAnexo($id_anexo)
    {
        $arquivo = self::find($id_anexo);
        if ($arquivo) {
            $arquivo->excluir();
        }
    }

}
