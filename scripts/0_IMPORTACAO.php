<?php

use App\Classes\ZapNotificacao;
use App\Imports\Admissaoimport;
use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\VagasAbertas;
use App\Rules\AreaEmpresaRules;
use App\Rules\CpfValidoEmpresaRules;
use App\Rules\VagaAbertaEmpresaRules;
use App\Rules\VerificaCpfEmpresaRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->terminate($request, $response);

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');

unset($argv[0]);
$import = new Admissaoimport;
\Excel::import($import, public_path('montisol_mig_2023.xlsx'));

$empresa_id = 63122;
$user_id = $empresa_id;
$jayParsedAry = collect([
    [
        "cpf" => "000.028.622-20",
        "numero_cracha" => "35580"
    ],
    [
        "cpf" => "000.130.622-71",
        "numero_cracha" => "27353"
    ],
    [
        "cpf" => "000.139.983-74",
        "numero_cracha" => "47416"
    ],
    [
        "cpf" => "000.224.263-02",
        "numero_cracha" => "86883"
    ],
    [
        "cpf" => "000.259.163-43",
        "numero_cracha" => "86928"
    ],
    [
        "cpf" => "000.529.923-30",
        "numero_cracha" => "85600"
    ],
    [
        "cpf" => "000.565.353-33",
        "numero_cracha" => "76186"
    ],
    [
        "cpf" => "000.639.563-57",
        "numero_cracha" => "85603"
    ],
    [
        "cpf" => "000.814.602-01",
        "numero_cracha" => "87399"
    ],
    [
        "cpf" => "000.968.823-44",
        "numero_cracha" => "47319"
    ],
    [
        "cpf" => "000.984.103-26",
        "numero_cracha" => "77529"
    ],
    [
        "cpf" => "001.018.983-12",
        "numero_cracha" => "78870"
    ],
    [
        "cpf" => "001.444.033-40",
        "numero_cracha" => "87332"
    ],
    [
        "cpf" => "001.484.573-39",
        "numero_cracha" => "28368"
    ],
    [
        "cpf" => "001.487.933-63",
        "numero_cracha" => "79814"
    ],
    [
        "cpf" => "001.617.773-84",
        "numero_cracha" => "46985"
    ],
    [
        "cpf" => "001.657.652-75",
        "numero_cracha" => "40284"
    ],
    [
        "cpf" => "001.737.673-46",
        "numero_cracha" => "81680"
    ],
    [
        "cpf" => "001.750.593-30",
        "numero_cracha" => "46876"
    ],
    [
        "cpf" => "001.822.553-52",
        "numero_cracha" => "76516"
    ],
    [
        "cpf" => "001.870.573-11",
        "numero_cracha" => "000013444"
    ],
    [
        "cpf" => "002.050.742-94",
        "numero_cracha" => "35700"
    ],
    [
        "cpf" => "002.122.313-08",
        "numero_cracha" => "N/A"
    ],
    [
        "cpf" => "002.275.653-12",
        "numero_cracha" => "N/A"
    ],
    [
        "cpf" => "002.691.843-92",
        "numero_cracha" => "87221"
    ],
    [
        "cpf" => "002.944.553-10",
        "numero_cracha" => "82818"
    ],
    [
        "cpf" => "003.074.353-26",
        "numero_cracha" => "81692"
    ],
    [
        "cpf" => "003.074.823-24",
        "numero_cracha" => "86919"
    ],
    [
        "cpf" => "003.085.833-02",
        "numero_cracha" => "82775"
    ],
    [
        "cpf" => "003.095.623-41",
        "numero_cracha" => "76833"
    ],
    [
        "cpf" => "003.220.602-06",
        "numero_cracha" => "000025385"
    ],
    [
        "cpf" => "003.308.232-48",
        "numero_cracha" => "000036023"
    ],
    [
        "cpf" => "003.471.463-41",
        "numero_cracha" => "87320"
    ],
    [
        "cpf" => "003.493.613-04",
        "numero_cracha" => "86304"
    ],
    [
        "cpf" => "003.584.743-37",
        "numero_cracha" => "76873"
    ],
    [
        "cpf" => "003.597.943-78",
        "numero_cracha" => "77803"
    ],
    [
        "cpf" => "003.642.862-02",
        "numero_cracha" => "000051487"
    ],
    [
        "cpf" => "003.875.773-78",
        "numero_cracha" => "87261"
    ],
    [
        "cpf" => "003.916.313-06",
        "numero_cracha" => "87354"
    ],
    [
        "cpf" => "003.938.833-61",
        "numero_cracha" => "53755"
    ],
    [
        "cpf" => "004.033.012-54",
        "numero_cracha" => "85477"
    ],
    [
        "cpf" => "004.226.533-90",
        "numero_cracha" => "76176"
    ],
    [
        "cpf" => "004.323.473-99",
        "numero_cracha" => "89444"
    ],
    [
        "cpf" => "004.403.333-85",
        "numero_cracha" => "86609"
    ],
    [
        "cpf" => "004.435.043-00",
        "numero_cracha" => "77332"
    ],
    [
        "cpf" => "004.698.623-51",
        "numero_cracha" => "70621"
    ],
    [
        "cpf" => "005.015.913-54",
        "numero_cracha" => "28179"
    ],
    [
        "cpf" => "005.033.053-59",
        "numero_cracha" => "48126"
    ],
    [
        "cpf" => "005.609.193-10",
        "numero_cracha" => "30017"
    ],
    [
        "cpf" => "005.625.453-95",
        "numero_cracha" => "46857"
    ],
    [
        "cpf" => "005.666.283-17",
        "numero_cracha" => "86285"
    ],
    [
        "cpf" => "005.777.343-26",
        "numero_cracha" => "76718"
    ],
    [
        "cpf" => "005.780.823-65",
        "numero_cracha" => "19827"
    ],
    [
        "cpf" => "005.992.843-31",
        "numero_cracha" => "86759"
    ],
    [
        "cpf" => "006.084.502-35",
        "numero_cracha" => "000047815"
    ],
    [
        "cpf" => "006.333.663-40",
        "numero_cracha" => "85622"
    ],
    [
        "cpf" => "007.080.693-47",
        "numero_cracha" => "52003"
    ],
    [
        "cpf" => "007.204.572-89",
        "numero_cracha" => "000047144"
    ],
    [
        "cpf" => "007.219.902-40",
        "numero_cracha" => "28869"
    ],
    [
        "cpf" => "007.261.493-57",
        "numero_cracha" => "27313"
    ],
    [
        "cpf" => "007.264.523-78",
        "numero_cracha" => "77431"
    ],
    [
        "cpf" => "007.449.942-41",
        "numero_cracha" => "81673"
    ],
    [
        "cpf" => "007.626.192-10",
        "numero_cracha" => "50324"
    ],
    [
        "cpf" => "007.693.513-28",
        "numero_cracha" => "78375"
    ],
    [
        "cpf" => "008.150.243-55",
        "numero_cracha" => "76594"
    ],
    [
        "cpf" => "008.206.393-11",
        "numero_cracha" => "49759"
    ],
    [
        "cpf" => "008.493.852-88",
        "numero_cracha" => "84373"
    ],
    [
        "cpf" => "008.513.993-98",
        "numero_cracha" => "87124"
    ],
    [
        "cpf" => "008.542.333-50",
        "numero_cracha" => "31880"
    ],
    [
        "cpf" => "008.574.442-55",
        "numero_cracha" => "000035322"
    ],
    [
        "cpf" => "008.586.813-23",
        "numero_cracha" => "73521"
    ],
    [
        "cpf" => "008.891.923-40",
        "numero_cracha" => "25922"
    ],
    [
        "cpf" => "008.914.083-47",
        "numero_cracha" => "82761"
    ],
    [
        "cpf" => "008.934.782-02",
        "numero_cracha" => "54246"
    ],
    [
        "cpf" => "009.079.313-75",
        "numero_cracha" => "N/A"
    ],
    [
        "cpf" => "009.199.743-76",
        "numero_cracha" => "81682"
    ],
    [
        "cpf" => "009.243.253-05",
        "numero_cracha" => "85640"
    ],
    [
        "cpf" => "009.444.863-98",
        "numero_cracha" => "86791"
    ],
    [
        "cpf" => "009.485.253-71",
        "numero_cracha" => "25272"
    ],
    [
        "cpf" => "009.618.753-05",
        "numero_cracha" => "6802"
    ],
    [
        "cpf" => "010.030.443-52",
        "numero_cracha" => "16829"
    ],
    [
        "cpf" => "010.043.883-06",
        "numero_cracha" => "86282"
    ],
    [
        "cpf" => "010.197.003-00",
        "numero_cracha" => "84274"
    ],
    [
        "cpf" => "010.234.393-44",
        "numero_cracha" => "76870"
    ],
    [
        "cpf" => "010.494.052-26",
        "numero_cracha" => "000055447"
    ],
    [
        "cpf" => "010.763.343-42",
        "numero_cracha" => "85608"
    ],
    [
        "cpf" => "010.921.423-47",
        "numero_cracha" => "46983"
    ],
    [
        "cpf" => "011.034.933-45",
        "numero_cracha" => "77065"
    ],
    [
        "cpf" => "011.417.633-70",
        "numero_cracha" => "70565"
    ],
    [
        "cpf" => "011.488.943-06",
        "numero_cracha" => "46783"
    ],
    [
        "cpf" => "011.741.523-54",
        "numero_cracha" => "46733"
    ],
    [
        "cpf" => "011.777.183-08",
        "numero_cracha" => "27381"
    ],
    [
        "cpf" => "011.790.013-38",
        "numero_cracha" => "77066"
    ],
    [
        "cpf" => "011.805.423-63",
        "numero_cracha" => "87316"
    ],
    [
        "cpf" => "012.014.913-39",
        "numero_cracha" => "86256"
    ],
    [
        "cpf" => "012.090.243-57",
        "numero_cracha" => "85458"
    ],
    [
        "cpf" => "012.211.773-54",
        "numero_cracha" => "82310"
    ],
    [
        "cpf" => "012.213.673-07",
        "numero_cracha" => "47260"
    ],
    [
        "cpf" => "012.275.602-90",
        "numero_cracha" => "000047164"
    ],
    [
        "cpf" => "012.306.403-12",
        "numero_cracha" => "81875"
    ],
    [
        "cpf" => "012.337.963-64",
        "numero_cracha" => "87383"
    ],
    [
        "cpf" => "012.341.213-79",
        "numero_cracha" => "76364"
    ],
    [
        "cpf" => "012.345.853-67",
        "numero_cracha" => "76500"
    ],
    [
        "cpf" => "012.359.763-35",
        "numero_cracha" => "47594"
    ],
    [
        "cpf" => "012.482.393-93",
        "numero_cracha" => "17836"
    ],
    [
        "cpf" => "012.635.293-30",
        "numero_cracha" => "82803"
    ],
    [
        "cpf" => "012.823.853-48",
        "numero_cracha" => "77394"
    ],
    [
        "cpf" => "012.917.373-83",
        "numero_cracha" => "86248"
    ],
    [
        "cpf" => "013.156.083-24",
        "numero_cracha" => "67534"
    ],
    [
        "cpf" => "013.194.783-44",
        "numero_cracha" => "87405"
    ],
    [
        "cpf" => "013.210.572-10",
        "numero_cracha" => "000036882"
    ],
    [
        "cpf" => "013.239.122-82",
        "numero_cracha" => "000053062"
    ],
    [
        "cpf" => "013.349.623-67",
        "numero_cracha" => "85842"
    ],
    [
        "cpf" => "013.401.963-60",
        "numero_cracha" => "86305"
    ],
    [
        "cpf" => "013.452.693-74",
        "numero_cracha" => "79445"
    ],
    [
        "cpf" => "013.662.673-46",
        "numero_cracha" => "86754"
    ],
    [
        "cpf" => "013.665.142-97",
        "numero_cracha" => "35710"
    ],
    [
        "cpf" => "014.158.702-41",
        "numero_cracha" => "43433"
    ],
    [
        "cpf" => "014.271.783-56",
        "numero_cracha" => "47905"
    ],
    [
        "cpf" => "014.344.252-07",
        "numero_cracha" => "52063"
    ],
    [
        "cpf" => "014.399.033-01",
        "numero_cracha" => "47323"
    ],
    [
        "cpf" => "014.786.883-19",
        "numero_cracha" => "53880"
    ],
    [
        "cpf" => "014.838.573-79",
        "numero_cracha" => "81704"
    ],
    [
        "cpf" => "014.871.353-00",
        "numero_cracha" => "85676"
    ],
    [
        "cpf" => "014.930.133-27",
        "numero_cracha" => "87358"
    ],
    [
        "cpf" => "014.936.373-71",
        "numero_cracha" => "67034"
    ],
    [
        "cpf" => "015.432.842-13",
        "numero_cracha" => "000041371"
    ],
    [
        "cpf" => "015.509.163-86",
        "numero_cracha" => "49594"
    ],
    [
        "cpf" => "015.681.473-09",
        "numero_cracha" => "53676"
    ],
    [
        "cpf" => "015.870.233-69",
        "numero_cracha" => "86957"
    ],
    [
        "cpf" => "015.893.103-30",
        "numero_cracha" => "87801"
    ],
    [
        "cpf" => "016.236.243-98",
        "numero_cracha" => "85450"
    ],
    [
        "cpf" => "016.439.662-43",
        "numero_cracha" => "000043776"
    ],
    [
        "cpf" => "016.661.662-19",
        "numero_cracha" => "72671"
    ],
    [
        "cpf" => "016.947.953-65",
        "numero_cracha" => "77374"
    ],
    [
        "cpf" => "017.172.543-30",
        "numero_cracha" => "85595"
    ],
    [
        "cpf" => "017.209.803-30",
        "numero_cracha" => "46644"
    ],
    [
        "cpf" => "017.343.512-25",
        "numero_cracha" => "000035534"
    ],
    [
        "cpf" => "017.679.863-35",
        "numero_cracha" => "70343"
    ],
    [
        "cpf" => "017.690.883-85",
        "numero_cracha" => "49593"
    ],
    [
        "cpf" => "017.793.662-21",
        "numero_cracha" => "41800"
    ],
    [
        "cpf" => "017.828.012-78",
        "numero_cracha" => "72658"
    ],
    [
        "cpf" => "018.010.052-14",
        "numero_cracha" => "55045"
    ],
    [
        "cpf" => "018.284.453-63",
        "numero_cracha" => "85668"
    ],
    [
        "cpf" => "018.546.133-67",
        "numero_cracha" => "21940"
    ],
    [
        "cpf" => "018.546.543-90",
        "numero_cracha" => "27832"
    ],
    [
        "cpf" => "019.157.683-22",
        "numero_cracha" => "76706"
    ],
    [
        "cpf" => "019.228.323-59",
        "numero_cracha" => "86288"
    ],
    [
        "cpf" => "019.352.433-30",
        "numero_cracha" => "86869"
    ],
    [
        "cpf" => "019.392.123-59",
        "numero_cracha" => "87352"
    ],
    [
        "cpf" => "019.407.033-60",
        "numero_cracha" => "86279"
    ],
    [
        "cpf" => "019.454.363-36",
        "numero_cracha" => "47430"
    ],
    [
        "cpf" => "019.830.712-85",
        "numero_cracha" => "000043691"
    ],
    [
        "cpf" => "020.166.203-58",
        "numero_cracha" => "5799"
    ],
    [
        "cpf" => "020.258.892-08",
        "numero_cracha" => "000055429"
    ],
    [
        "cpf" => "020.332.112-07",
        "numero_cracha" => "000054153"
    ],
    [
        "cpf" => "020.346.263-70",
        "numero_cracha" => "86961"
    ],
    [
        "cpf" => "020.409.243-45",
        "numero_cracha" => "84801"
    ],
    [
        "cpf" => "020.516.173-17",
        "numero_cracha" => "21225"
    ],
    [
        "cpf" => "020.532.161-58",
        "numero_cracha" => "72415"
    ],
    [
        "cpf" => "020.799.653-95",
        "numero_cracha" => "76975"
    ],
    [
        "cpf" => "020.812.283-43",
        "numero_cracha" => "86320"
    ],
    [
        "cpf" => "020.858.753-51",
        "numero_cracha" => "47320"
    ],
    [
        "cpf" => "021.061.983-02",
        "numero_cracha" => "46848"
    ],
    [
        "cpf" => "021.243.772-05",
        "numero_cracha" => "53984"
    ],
    [
        "cpf" => "021.263.502-66",
        "numero_cracha" => "000043726"
    ],
    [
        "cpf" => "021.265.683-00",
        "numero_cracha" => "83501"
    ],
    [
        "cpf" => "021.266.893-52",
        "numero_cracha" => "52005"
    ],
    [
        "cpf" => "021.473.803-50",
        "numero_cracha" => "19822"
    ],
    [
        "cpf" => "021.580.983-17",
        "numero_cracha" => "86139"
    ],
    [
        "cpf" => "021.617.249-75",
        "numero_cracha" => "87791"
    ],
    [
        "cpf" => "021.665.283-92",
        "numero_cracha" => "85846"
    ],
    [
        "cpf" => "021.756.832-73",
        "numero_cracha" => "87937"
    ],
    [
        "cpf" => "021.844.133-99",
        "numero_cracha" => "86893"
    ],
    [
        "cpf" => "021.902.913-05",
        "numero_cracha" => "87117"
    ],
    [
        "cpf" => "022.033.263-01",
        "numero_cracha" => "13491"
    ],
    [
        "cpf" => "022.171.153-83",
        "numero_cracha" => "86943"
    ],
    [
        "cpf" => "022.545.703-23",
        "numero_cracha" => "86931"
    ],
    [
        "cpf" => "022.604.463-70",
        "numero_cracha" => "79811"
    ],
    [
        "cpf" => "022.743.313-08",
        "numero_cracha" => "86269"
    ],
    [
        "cpf" => "022.792.783-40",
        "numero_cracha" => "86941"
    ],
    [
        "cpf" => "022.891.503-12",
        "numero_cracha" => "86884"
    ],
    [
        "cpf" => "022.961.822-75",
        "numero_cracha" => "43999"
    ],
    [
        "cpf" => "023.095.043-43",
        "numero_cracha" => "46527"
    ],
    [
        "cpf" => "023.199.872-40",
        "numero_cracha" => "55098"
    ],
    [
        "cpf" => "023.339.923-28",
        "numero_cracha" => "86309"
    ],
    [
        "cpf" => "023.416.563-40",
        "numero_cracha" => "76603"
    ],
    [
        "cpf" => "023.487.472-43",
        "numero_cracha" => "42280"
    ],
    [
        "cpf" => "023.729.792-21",
        "numero_cracha" => "68683"
    ],
    [
        "cpf" => "024.022.123-06",
        "numero_cracha" => "47578"
    ],
    [
        "cpf" => "024.101.023-36",
        "numero_cracha" => "65788"
    ],
    [
        "cpf" => "024.189.483-23",
        "numero_cracha" => "70059"
    ],
    [
        "cpf" => "024.203.543-46",
        "numero_cracha" => "30763"
    ],
    [
        "cpf" => "024.326.823-86",
        "numero_cracha" => "82309"
    ],
    [
        "cpf" => "024.367.925-42",
        "numero_cracha" => "82816"
    ],
    [
        "cpf" => "024.498.123-00",
        "numero_cracha" => "27813"
    ],
    [
        "cpf" => "024.529.253-57",
        "numero_cracha" => "48222"
    ],
    [
        "cpf" => "024.536.213-43",
        "numero_cracha" => "86916"
    ],
    [
        "cpf" => "024.743.533-30",
        "numero_cracha" => "67185"
    ],
    [
        "cpf" => "024.856.432-37",
        "numero_cracha" => "54798"
    ],
    [
        "cpf" => "024.875.163-80",
        "numero_cracha" => "000018907"
    ],
    [
        "cpf" => "024.878.353-03",
        "numero_cracha" => "46880"
    ],
    [
        "cpf" => "024.881.943-79",
        "numero_cracha" => "81695"
    ],
    [
        "cpf" => "024.936.063-29",
        "numero_cracha" => "87385"
    ],
    [
        "cpf" => "025.018.502-46",
        "numero_cracha" => "47851"
    ],
    [
        "cpf" => "025.032.502-03",
        "numero_cracha" => "56153"
    ],
    [
        "cpf" => "025.073.372-22",
        "numero_cracha" => "53128"
    ],
    [
        "cpf" => "025.078.920-56",
        "numero_cracha" => "78869"
    ],
    [
        "cpf" => "025.138.642-20",
        "numero_cracha" => "000044916"
    ],
    [
        "cpf" => "025.256.443-01",
        "numero_cracha" => "77071"
    ],
    [
        "cpf" => "025.363.693-07",
        "numero_cracha" => "87359"
    ],
    [
        "cpf" => "025.364.423-22",
        "numero_cracha" => "83581"
    ],
    [
        "cpf" => "025.395.713-36",
        "numero_cracha" => "53869"
    ],
    [
        "cpf" => "025.556.953-03",
        "numero_cracha" => "87560"
    ],
    [
        "cpf" => "025.610.822-61",
        "numero_cracha" => "54248"
    ],
    [
        "cpf" => "025.917.283-99",
        "numero_cracha" => "70622"
    ],
    [
        "cpf" => "026.003.553-08",
        "numero_cracha" => "77805"
    ],
    [
        "cpf" => "026.155.243-05",
        "numero_cracha" => "86104"
    ],
    [
        "cpf" => "026.163.363-59",
        "numero_cracha" => "87408"
    ],
    [
        "cpf" => "026.310.023-50",
        "numero_cracha" => "86247"
    ],
    [
        "cpf" => "026.345.522-03",
        "numero_cracha" => "000041836"
    ],
    [
        "cpf" => "026.410.863-95",
        "numero_cracha" => "19825"
    ],
    [
        "cpf" => "026.431.293-77",
        "numero_cracha" => "81864"
    ],
    [
        "cpf" => "026.624.593-52",
        "numero_cracha" => "86268"
    ],
    [
        "cpf" => "026.695.933-48",
        "numero_cracha" => "81080"
    ],
    [
        "cpf" => "026.715.533-66",
        "numero_cracha" => "13496"
    ],
    [
        "cpf" => "026.858.143-61",
        "numero_cracha" => "73523"
    ],
    [
        "cpf" => "026.905.483-99",
        "numero_cracha" => "18740"
    ],
    [
        "cpf" => "026.989.093-90",
        "numero_cracha" => "86874"
    ],
    [
        "cpf" => "026.997.113-06",
        "numero_cracha" => "87122"
    ],
    [
        "cpf" => "027.015.442-62",
        "numero_cracha" => "49839"
    ],
    [
        "cpf" => "027.124.773-83",
        "numero_cracha" => "86920"
    ],
    [
        "cpf" => "027.137.013-09",
        "numero_cracha" => "46535"
    ],
    [
        "cpf" => "027.326.423-04",
        "numero_cracha" => "85612"
    ],
    [
        "cpf" => "027.597.113-90",
        "numero_cracha" => "77398"
    ],
    [
        "cpf" => "027.599.902-57",
        "numero_cracha" => "000047493"
    ],
    [
        "cpf" => "027.754.982-56",
        "numero_cracha" => "50968"
    ],
    [
        "cpf" => "027.770.823-08",
        "numero_cracha" => "46962"
    ],
    [
        "cpf" => "027.893.853-19",
        "numero_cracha" => "48009"
    ],
    [
        "cpf" => "028.171.783-43",
        "numero_cracha" => "79631"
    ],
    [
        "cpf" => "028.417.522-67",
        "numero_cracha" => "42162"
    ],
    [
        "cpf" => "028.526.073-10",
        "numero_cracha" => "24165"
    ],
    [
        "cpf" => "028.603.343-78",
        "numero_cracha" => "25864"
    ],
    [
        "cpf" => "028.646.763-11",
        "numero_cracha" => "47498"
    ],
    [
        "cpf" => "028.650.403-05",
        "numero_cracha" => "77653"
    ],
    [
        "cpf" => "028.756.212-38",
        "numero_cracha" => "77209"
    ],
    [
        "cpf" => "029.109.793-69",
        "numero_cracha" => "87842"
    ],
    [
        "cpf" => "029.323.753-00",
        "numero_cracha" => "65809"
    ],
    [
        "cpf" => "029.399.493-52",
        "numero_cracha" => "12019"
    ],
    [
        "cpf" => "029.402.673-83",
        "numero_cracha" => "87345"
    ],
    [
        "cpf" => "029.449.113-92",
        "numero_cracha" => "86286"
    ],
    [
        "cpf" => "029.477.422-06",
        "numero_cracha" => "53442"
    ],
    [
        "cpf" => "029.617.533-17",
        "numero_cracha" => "53899"
    ],
    [
        "cpf" => "029.852.393-03",
        "numero_cracha" => "87340"
    ],
    [
        "cpf" => "029.915.833-04",
        "numero_cracha" => "6325"
    ],
    [
        "cpf" => "029.980.663-48",
        "numero_cracha" => "70710"
    ],
    [
        "cpf" => "030.069.033-90",
        "numero_cracha" => "84250"
    ],
    [
        "cpf" => "030.333.083-09",
        "numero_cracha" => "77524"
    ],
    [
        "cpf" => "030.354.703-08",
        "numero_cracha" => "87327"
    ],
    [
        "cpf" => "030.412.083-93",
        "numero_cracha" => "77154"
    ],
    [
        "cpf" => "030.520.813-65",
        "numero_cracha" => "86414"
    ],
    [
        "cpf" => "030.742.173-24",
        "numero_cracha" => "87165"
    ],
    [
        "cpf" => "030.763.072-23",
        "numero_cracha" => "000053445"
    ],
    [
        "cpf" => "030.836.173-30",
        "numero_cracha" => "28871"
    ],
    [
        "cpf" => "030.846.233-50",
        "numero_cracha" => "47128"
    ],
    [
        "cpf" => "030.923.523-51",
        "numero_cracha" => "85689"
    ],
    [
        "cpf" => "030.927.483-44",
        "numero_cracha" => "84275"
    ],
    [
        "cpf" => "031.005.173-85",
        "numero_cracha" => "86289"
    ],
    [
        "cpf" => "031.090.642-35",
        "numero_cracha" => "000045241"
    ],
    [
        "cpf" => "031.133.303-60",
        "numero_cracha" => "16732"
    ],
    [
        "cpf" => "031.237.563-86",
        "numero_cracha" => "25923"
    ],
    [
        "cpf" => "031.440.403-12",
        "numero_cracha" => "47070"
    ],
    [
        "cpf" => "031.472.653-58",
        "numero_cracha" => "86951"
    ],
    [
        "cpf" => "031.588.592-03",
        "numero_cracha" => "54419"
    ],
    [
        "cpf" => "031.683.683-42",
        "numero_cracha" => "86400"
    ],
    [
        "cpf" => "031.749.172-55",
        "numero_cracha" => "44915"
    ],
    [
        "cpf" => "031.783.513-02",
        "numero_cracha" => "87813"
    ],
    [
        "cpf" => "031.868.963-47",
        "numero_cracha" => "21205"
    ],
    [
        "cpf" => "031.942.092-21",
        "numero_cracha" => "50282"
    ],
    [
        "cpf" => "032.184.272-31",
        "numero_cracha" => "47962"
    ],
    [
        "cpf" => "032.257.003-47",
        "numero_cracha" => "86416"
    ],
    [
        "cpf" => "032.273.953-55",
        "numero_cracha" => "76814"
    ],
    [
        "cpf" => "032.695.212-86",
        "numero_cracha" => "56172"
    ],
    [
        "cpf" => "032.722.743-59",
        "numero_cracha" => "70477"
    ],
    [
        "cpf" => "033.021.072-69",
        "numero_cracha" => "000040787"
    ],
    [
        "cpf" => "033.388.733-60",
        "numero_cracha" => "46665"
    ],
    [
        "cpf" => "033.661.783-61",
        "numero_cracha" => "88541"
    ],
    [
        "cpf" => "033.932.763-44",
        "numero_cracha" => "87169"
    ],
    [
        "cpf" => "033.985.483-93",
        "numero_cracha" => "49125"
    ],
    [
        "cpf" => "034.025.833-05",
        "numero_cracha" => "87407"
    ],
    [
        "cpf" => "034.139.993-03",
        "numero_cracha" => "50918"
    ],
    [
        "cpf" => "034.270.873-28",
        "numero_cracha" => "86423"
    ],
    [
        "cpf" => "034.290.422-16",
        "numero_cracha" => "48112"
    ],
    [
        "cpf" => "034.303.563-42",
        "numero_cracha" => "17024"
    ],
    [
        "cpf" => "034.452.453-12",
        "numero_cracha" => "85623"
    ],
    [
        "cpf" => "034.457.022-33",
        "numero_cracha" => "50280"
    ],
    [
        "cpf" => "034.478.923-31",
        "numero_cracha" => "81732"
    ],
    [
        "cpf" => "034.732.983-79",
        "numero_cracha" => "88713"
    ],
    [
        "cpf" => "034.920.413-61",
        "numero_cracha" => "87571"
    ],
    [
        "cpf" => "035.228.012-37",
        "numero_cracha" => "41779"
    ],
    [
        "cpf" => "035.246.822-00",
        "numero_cracha" => "50950"
    ],
    [
        "cpf" => "035.416.433-31",
        "numero_cracha" => "12163"
    ],
    [
        "cpf" => "035.863.423-73",
        "numero_cracha" => "86922"
    ],
    [
        "cpf" => "035.871.153-38",
        "numero_cracha" => "70060"
    ],
    [
        "cpf" => "035.972.513-90",
        "numero_cracha" => "52331"
    ],
    [
        "cpf" => "035.975.083-40",
        "numero_cracha" => "21631"
    ],
    [
        "cpf" => "035.991.273-79",
        "numero_cracha" => "87257"
    ],
    [
        "cpf" => "036.172.003-38",
        "numero_cracha" => "53696"
    ],
    [
        "cpf" => "036.192.593-05",
        "numero_cracha" => "82800"
    ],
    [
        "cpf" => "036.198.902-42",
        "numero_cracha" => "000055455"
    ],
    [
        "cpf" => "036.259.973-47",
        "numero_cracha" => "86936"
    ],
    [
        "cpf" => "036.318.733-28",
        "numero_cracha" => "47912"
    ],
    [
        "cpf" => "036.328.663-25",
        "numero_cracha" => "87227"
    ],
    [
        "cpf" => "036.531.973-25",
        "numero_cracha" => "86117"
    ],
    [
        "cpf" => "036.743.853-41",
        "numero_cracha" => "76609"
    ],
    [
        "cpf" => "036.803.273-66",
        "numero_cracha" => "78711"
    ],
    [
        "cpf" => "036.833.003-61",
        "numero_cracha" => "86948"
    ],
    [
        "cpf" => "036.947.173-31",
        "numero_cracha" => "60153"
    ],
    [
        "cpf" => "037.080.513-51",
        "numero_cracha" => "85372"
    ],
    [
        "cpf" => "037.260.823-01",
        "numero_cracha" => "68572"
    ],
    [
        "cpf" => "037.368.953-56",
        "numero_cracha" => "76859"
    ],
    [
        "cpf" => "037.413.723-40",
        "numero_cracha" => "70937"
    ],
    [
        "cpf" => "037.531.413-05",
        "numero_cracha" => "79813"
    ],
    [
        "cpf" => "037.977.363-55",
        "numero_cracha" => "88209"
    ],
    [
        "cpf" => "038.035.053-05",
        "numero_cracha" => "77056"
    ],
    [
        "cpf" => "038.202.153-36",
        "numero_cracha" => "85598"
    ],
    [
        "cpf" => "038.460.863-98",
        "numero_cracha" => "52006"
    ],
    [
        "cpf" => "038.892.493-45",
        "numero_cracha" => "27375"
    ],
    [
        "cpf" => "038.898.883-56",
        "numero_cracha" => "77397"
    ],
    [
        "cpf" => "038.944.243-70",
        "numero_cracha" => "84796"
    ],
    [
        "cpf" => "039.079.723-50",
        "numero_cracha" => "46838"
    ],
    [
        "cpf" => "039.253.283-27",
        "numero_cracha" => "86239"
    ],
    [
        "cpf" => "039.253.303-05",
        "numero_cracha" => "77550"
    ],
    [
        "cpf" => "039.296.143-16",
        "numero_cracha" => "86300"
    ],
    [
        "cpf" => "039.316.743-71",
        "numero_cracha" => "25622"
    ],
    [
        "cpf" => "039.603.003-36",
        "numero_cracha" => "21901"
    ],
    [
        "cpf" => "039.766.953-43",
        "numero_cracha" => "87102"
    ],
    [
        "cpf" => "039.866.963-50",
        "numero_cracha" => "77782"
    ],
    [
        "cpf" => "040.044.882-30",
        "numero_cracha" => "55047"
    ],
    [
        "cpf" => "040.142.283-63",
        "numero_cracha" => "76984"
    ],
    [
        "cpf" => "040.238.052-54",
        "numero_cracha" => "49991"
    ],
    [
        "cpf" => "040.254.973-28",
        "numero_cracha" => "71713"
    ],
    [
        "cpf" => "040.478.623-55",
        "numero_cracha" => "47911"
    ],
    [
        "cpf" => "040.522.123-12",
        "numero_cracha" => "79819"
    ],
    [
        "cpf" => "040.688.333-54",
        "numero_cracha" => "87108"
    ],
    [
        "cpf" => "040.741.773-74",
        "numero_cracha" => "48107"
    ],
    [
        "cpf" => "040.973.033-50",
        "numero_cracha" => "76705"
    ],
    [
        "cpf" => "041.001.223-81",
        "numero_cracha" => "71129"
    ],
    [
        "cpf" => "041.229.302-19",
        "numero_cracha" => "55426"
    ],
    [
        "cpf" => "041.245.573-02",
        "numero_cracha" => "87323"
    ],
    [
        "cpf" => "041.261.463-42",
        "numero_cracha" => "47902"
    ],
    [
        "cpf" => "041.554.792-06",
        "numero_cracha" => "51228"
    ],
    [
        "cpf" => "041.804.683-28",
        "numero_cracha" => "53863"
    ],
    [
        "cpf" => "042.126.993-62",
        "numero_cracha" => "77423"
    ],
    [
        "cpf" => "042.132.643-35",
        "numero_cracha" => "71757"
    ],
    [
        "cpf" => "042.205.973-01",
        "numero_cracha" => "86106"
    ],
    [
        "cpf" => "042.414.053-55",
        "numero_cracha" => "69445"
    ],
    [
        "cpf" => "042.475.333-20",
        "numero_cracha" => "72634"
    ],
    [
        "cpf" => "042.913.193-39",
        "numero_cracha" => "77814"
    ],
    [
        "cpf" => "042.989.483-07",
        "numero_cracha" => "77643"
    ],
    [
        "cpf" => "043.038.883-70",
        "numero_cracha" => "21211"
    ],
    [
        "cpf" => "043.038.913-20",
        "numero_cracha" => "70943"
    ],
    [
        "cpf" => "043.053.953-39",
        "numero_cracha" => "85621"
    ],
    [
        "cpf" => "043.217.323-44",
        "numero_cracha" => "86259"
    ],
    [
        "cpf" => "043.554.023-80",
        "numero_cracha" => "76697"
    ],
    [
        "cpf" => "043.725.843-26",
        "numero_cracha" => "62690"
    ],
    [
        "cpf" => "043.998.913-27",
        "numero_cracha" => "79818"
    ],
    [
        "cpf" => "044.126.113-29",
        "numero_cracha" => "87224"
    ],
    [
        "cpf" => "044.314.553-96",
        "numero_cracha" => "25867"
    ],
    [
        "cpf" => "044.500.363-44",
        "numero_cracha" => "82303"
    ],
    [
        "cpf" => "044.580.803-93",
        "numero_cracha" => "79835"
    ],
    [
        "cpf" => "044.643.693-32",
        "numero_cracha" => "77393"
    ],
    [
        "cpf" => "044.677.933-48",
        "numero_cracha" => "85671"
    ],
    [
        "cpf" => "044.731.193-00",
        "numero_cracha" => "85466"
    ],
    [
        "cpf" => "045.428.423-39",
        "numero_cracha" => "87126"
    ],
    [
        "cpf" => "045.499.113-44",
        "numero_cracha" => "52010"
    ],
    [
        "cpf" => "045.516.973-03",
        "numero_cracha" => "21899"
    ],
    [
        "cpf" => "045.650.753-18",
        "numero_cracha" => "76608"
    ],
    [
        "cpf" => "045.724.852-16",
        "numero_cracha" => "000052714"
    ],
    [
        "cpf" => "045.955.373-95",
        "numero_cracha" => "70624"
    ],
    [
        "cpf" => "046.268.053-37",
        "numero_cracha" => "47573"
    ],
    [
        "cpf" => "046.468.413-71",
        "numero_cracha" => "86287"
    ],
    [
        "cpf" => "046.545.473-98",
        "numero_cracha" => "85834"
    ],
    [
        "cpf" => "046.702.283-66",
        "numero_cracha" => "82806"
    ],
    [
        "cpf" => "046.704.753-71",
        "numero_cracha" => "53717"
    ],
    [
        "cpf" => "046.714.633-00",
        "numero_cracha" => "86774"
    ],
    [
        "cpf" => "047.307.143-69",
        "numero_cracha" => "46413"
    ],
    [
        "cpf" => "047.672.623-99",
        "numero_cracha" => "87229"
    ],
    [
        "cpf" => "048.223.163-78",
        "numero_cracha" => "70342"
    ],
    [
        "cpf" => "048.402.413-25",
        "numero_cracha" => "7121"
    ],
    [
        "cpf" => "048.456.863-92",
        "numero_cracha" => "86297"
    ],
    [
        "cpf" => "048.558.913-39",
        "numero_cracha" => "87821"
    ],
    [
        "cpf" => "048.574.993-98",
        "numero_cracha" => "86947"
    ],
    [
        "cpf" => "048.888.533-71",
        "numero_cracha" => "87920"
    ],
    [
        "cpf" => "049.182.523-46",
        "numero_cracha" => "85457"
    ],
    [
        "cpf" => "049.265.303-84",
        "numero_cracha" => "76714"
    ],
    [
        "cpf" => "049.747.443-30",
        "numero_cracha" => "25926"
    ],
    [
        "cpf" => "049.832.232-74",
        "numero_cracha" => "51227"
    ],
    [
        "cpf" => "050.056.933-94",
        "numero_cracha" => "86133"
    ],
    [
        "cpf" => "050.544.623-50",
        "numero_cracha" => "82291"
    ],
    [
        "cpf" => "050.667.503-39",
        "numero_cracha" => "82306"
    ],
    [
        "cpf" => "050.708.883-23",
        "numero_cracha" => "9634"
    ],
    [
        "cpf" => "050.886.923-40",
        "numero_cracha" => "82784"
    ],
    [
        "cpf" => "050.920.373-69",
        "numero_cracha" => "87220"
    ],
    [
        "cpf" => "051.193.703-22",
        "numero_cracha" => "68984"
    ],
    [
        "cpf" => "051.376.983-85",
        "numero_cracha" => "86756"
    ],
    [
        "cpf" => "051.476.073-74",
        "numero_cracha" => "86165"
    ],
    [
        "cpf" => "051.686.183-24",
        "numero_cracha" => "76809"
    ],
    [
        "cpf" => "051.741.833-93",
        "numero_cracha" => "77343"
    ],
    [
        "cpf" => "051.878.663-38",
        "numero_cracha" => "48363"
    ],
    [
        "cpf" => "051.932.783-70",
        "numero_cracha" => "87313"
    ],
    [
        "cpf" => "052.056.663-74",
        "numero_cracha" => "47428"
    ],
    [
        "cpf" => "052.167.693-23",
        "numero_cracha" => "11885"
    ],
    [
        "cpf" => "052.206.813-89",
        "numero_cracha" => "89030"
    ],
    [
        "cpf" => "052.215.753-02",
        "numero_cracha" => "70057"
    ],
    [
        "cpf" => "052.506.853-82",
        "numero_cracha" => "87336"
    ],
    [
        "cpf" => "052.554.123-33",
        "numero_cracha" => "80537"
    ],
    [
        "cpf" => "052.712.433-89",
        "numero_cracha" => "53615"
    ],
    [
        "cpf" => "052.748.113-02",
        "numero_cracha" => "46930"
    ],
    [
        "cpf" => "052.911.423-24",
        "numero_cracha" => "73538"
    ],
    [
        "cpf" => "053.181.923-00",
        "numero_cracha" => "51177"
    ],
    [
        "cpf" => "053.568.793-18",
        "numero_cracha" => "79812"
    ],
    [
        "cpf" => "053.590.613-75",
        "numero_cracha" => "70940"
    ],
    [
        "cpf" => "053.627.473-80",
        "numero_cracha" => "70481"
    ],
    [
        "cpf" => "053.635.383-26",
        "numero_cracha" => "87382"
    ],
    [
        "cpf" => "053.722.783-07",
        "numero_cracha" => "16459"
    ],
    [
        "cpf" => "053.722.933-73",
        "numero_cracha" => "87252"
    ],
    [
        "cpf" => "053.724.543-00",
        "numero_cracha" => "48228"
    ],
    [
        "cpf" => "053.819.643-23",
        "numero_cracha" => "53636"
    ],
    [
        "cpf" => "053.848.413-62",
        "numero_cracha" => "74138"
    ],
    [
        "cpf" => "053.853.623-36",
        "numero_cracha" => "86143"
    ],
    [
        "cpf" => "054.155.693-24",
        "numero_cracha" => "86966"
    ],
    [
        "cpf" => "054.307.953-80",
        "numero_cracha" => "53673"
    ],
    [
        "cpf" => "054.338.623-64",
        "numero_cracha" => "86606"
    ],
    [
        "cpf" => "054.686.213-63",
        "numero_cracha" => "N/A"
    ],
    [
        "cpf" => "054.705.103-44",
        "numero_cracha" => "82802"
    ],
    [
        "cpf" => "054.834.723-90",
        "numero_cracha" => "86426"
    ],
    [
        "cpf" => "054.916.373-51",
        "numero_cracha" => "82252"
    ],
    [
        "cpf" => "054.977.293-63",
        "numero_cracha" => "88507"
    ],
    [
        "cpf" => "055.166.163-10",
        "numero_cracha" => "86780"
    ],
    [
        "cpf" => "055.491.533-27",
        "numero_cracha" => "81656"
    ],
    [
        "cpf" => "055.764.303-10",
        "numero_cracha" => "53825"
    ],
    [
        "cpf" => "055.911.423-03",
        "numero_cracha" => "80138"
    ],
    [
        "cpf" => "056.016.433-50",
        "numero_cracha" => "52332"
    ],
    [
        "cpf" => "056.361.743-81",
        "numero_cracha" => "27836"
    ],
    [
        "cpf" => "056.541.053-96",
        "numero_cracha" => "67036"
    ],
    [
        "cpf" => "056.690.403-92",
        "numero_cracha" => "77375"
    ],
    [
        "cpf" => "056.882.253-60",
        "numero_cracha" => "87254"
    ],
    [
        "cpf" => "056.929.313-89",
        "numero_cracha" => "77157"
    ],
    [
        "cpf" => "056.973.753-28",
        "numero_cracha" => "67533"
    ],
    [
        "cpf" => "057.482.583-58",
        "numero_cracha" => "000055134"
    ],
    [
        "cpf" => "057.518.653-40",
        "numero_cracha" => "21627"
    ],
    [
        "cpf" => "057.657.383-38",
        "numero_cracha" => "53858"
    ],
    [
        "cpf" => "057.739.313-89",
        "numero_cracha" => "19829"
    ],
    [
        "cpf" => "057.841.003-60",
        "numero_cracha" => "76597"
    ],
    [
        "cpf" => "057.971.893-05",
        "numero_cracha" => "86136"
    ],
    [
        "cpf" => "058.142.923-00",
        "numero_cracha" => "18246"
    ],
    [
        "cpf" => "058.381.513-82",
        "numero_cracha" => "86969"
    ],
    [
        "cpf" => "058.479.253-01",
        "numero_cracha" => "49675"
    ],
    [
        "cpf" => "058.911.063-29",
        "numero_cracha" => "17027"
    ],
    [
        "cpf" => "059.096.997-84",
        "numero_cracha" => "70523"
    ],
    [
        "cpf" => "059.695.763-79",
        "numero_cracha" => "47346"
    ],
    [
        "cpf" => "059.887.813-06",
        "numero_cracha" => "77057"
    ],
    [
        "cpf" => "060.342.033-84",
        "numero_cracha" => "77305"
    ],
    [
        "cpf" => "060.524.273-96",
        "numero_cracha" => "85673"
    ],
    [
        "cpf" => "060.531.183-89",
        "numero_cracha" => "87941"
    ],
    [
        "cpf" => "060.593.263-82",
        "numero_cracha" => "18224"
    ],
    [
        "cpf" => "060.640.573-94",
        "numero_cracha" => "87105"
    ],
    [
        "cpf" => "060.836.903-90",
        "numero_cracha" => "76189"
    ],
    [
        "cpf" => "060.952.313-96",
        "numero_cracha" => "76509"
    ],
    [
        "cpf" => "061.388.573-21",
        "numero_cracha" => "76505"
    ],
    [
        "cpf" => "061.906.983-01",
        "numero_cracha" => "17025"
    ],
    [
        "cpf" => "062.505.713-96",
        "numero_cracha" => "86408"
    ],
    [
        "cpf" => "062.989.753-06",
        "numero_cracha" => "49592"
    ],
    [
        "cpf" => "063.151.973-45",
        "numero_cracha" => "76992"
    ],
    [
        "cpf" => "063.422.343-70",
        "numero_cracha" => "88486"
    ],
    [
        "cpf" => "063.504.803-50",
        "numero_cracha" => "85445"
    ],
    [
        "cpf" => "064.929.683-44",
        "numero_cracha" => "68556"
    ],
    [
        "cpf" => "065.525.343-29",
        "numero_cracha" => "68791"
    ],
    [
        "cpf" => "065.598.093-82",
        "numero_cracha" => "81092"
    ],
    [
        "cpf" => "065.807.903-42",
        "numero_cracha" => "19824"
    ],
    [
        "cpf" => "066.412.403-80",
        "numero_cracha" => "86313"
    ],
    [
        "cpf" => "069.833.283-02",
        "numero_cracha" => "73529"
    ],
    [
        "cpf" => "070.493.183-45",
        "numero_cracha" => "86361"
    ],
    [
        "cpf" => "070.600.303-95",
        "numero_cracha" => "68797"
    ],
    [
        "cpf" => "070.600.643-70",
        "numero_cracha" => "77004"
    ],
    [
        "cpf" => "070.640.743-11",
        "numero_cracha" => "87803"
    ],
    [
        "cpf" => "070.997.943-61",
        "numero_cracha" => "87788"
    ],
    [
        "cpf" => "071.048.643-00",
        "numero_cracha" => "50138"
    ],
    [
        "cpf" => "071.220.193-90",
        "numero_cracha" => "87830"
    ],
    [
        "cpf" => "071.306.202-93",
        "numero_cracha" => "51724"
    ],
    [
        "cpf" => "071.755.963-75",
        "numero_cracha" => "82778"
    ],
    [
        "cpf" => "072.024.513-36",
        "numero_cracha" => "73536"
    ],
    [
        "cpf" => "072.031.333-38",
        "numero_cracha" => "70946"
    ],
    [
        "cpf" => "072.471.783-88",
        "numero_cracha" => "74129"
    ],
    [
        "cpf" => "074.369.493-73",
        "numero_cracha" => "81089"
    ],
    [
        "cpf" => "074.895.463-54",
        "numero_cracha" => "86315"
    ],
    [
        "cpf" => "074.925.343-61",
        "numero_cracha" => "79831"
    ],
    [
        "cpf" => "075.090.213-24",
        "numero_cracha" => "85866"
    ],
    [
        "cpf" => "076.124.943-53",
        "numero_cracha" => "6365"
    ],
    [
        "cpf" => "076.550.533-91",
        "numero_cracha" => "87249"
    ],
    [
        "cpf" => "077.119.173-17",
        "numero_cracha" => "71320"
    ],
    [
        "cpf" => "077.965.653-99",
        "numero_cracha" => "87349"
    ],
    [
        "cpf" => "078.437.763-46",
        "numero_cracha" => "68687"
    ],
    [
        "cpf" => "079.216.053-38",
        "numero_cracha" => "81869"
    ],
    [
        "cpf" => "081.672.643-43",
        "numero_cracha" => "73527"
    ],
    [
        "cpf" => "083.403.043-80",
        "numero_cracha" => "86246"
    ],
    [
        "cpf" => "084.047.857-75",
        "numero_cracha" => "70948"
    ],
    [
        "cpf" => "084.453.753-50",
        "numero_cracha" => "77549"
    ],
    [
        "cpf" => "089.266.443-67",
        "numero_cracha" => "85615"
    ],
    [
        "cpf" => "093.905.303-93",
        "numero_cracha" => "79836"
    ],
    [
        "cpf" => "094.293.523-34",
        "numero_cracha" => "30855"
    ],
    [
        "cpf" => "100.105.197-18",
        "numero_cracha" => "87835"
    ],
    [
        "cpf" => "102.046.297-30",
        "numero_cracha" => "77050"
    ],
    [
        "cpf" => "106.775.373-79",
        "numero_cracha" => "73534"
    ],
    [
        "cpf" => "106.800.713-34",
        "numero_cracha" => "78873"
    ],
    [
        "cpf" => "116.083.947-60",
        "numero_cracha" => "86971"
    ],
    [
        "cpf" => "129.435.347-06",
        "numero_cracha" => "73533"
    ],
    [
        "cpf" => "137.544.753-04",
        "numero_cracha" => "77206"
    ],
    [
        "cpf" => "141.205.692-68",
        "numero_cracha" => "87176"
    ],
    [
        "cpf" => "147.017.313-15",
        "numero_cracha" => "86125"
    ],
    [
        "cpf" => "147.325.303-97",
        "numero_cracha" => "87412"
    ],
    [
        "cpf" => "168.127.352-72",
        "numero_cracha" => "21592"
    ],
    [
        "cpf" => "169.536.302-72",
        "numero_cracha" => "78499"
    ],
    [
        "cpf" => "179.081.753-68",
        "numero_cracha" => "31095"
    ],
    [
        "cpf" => "186.071.959-72",
        "numero_cracha" => "11669"
    ],
    [
        "cpf" => "195.962.194-72",
        "numero_cracha" => "19440"
    ],
    [
        "cpf" => "198.254.553-49",
        "numero_cracha" => "86887"
    ],
    [
        "cpf" => "208.368.132-00",
        "numero_cracha" => "000054223Q"
    ],
    [
        "cpf" => "219.477.593-53",
        "numero_cracha" => "77402"
    ],
    [
        "cpf" => "226.118.273-20",
        "numero_cracha" => "86872"
    ],
    [
        "cpf" => "236.414.603-87",
        "numero_cracha" => "77413"
    ],
    [
        "cpf" => "236.707.463-15",
        "numero_cracha" => "85610"
    ],
    [
        "cpf" => "237.191.503-30",
        "numero_cracha" => "85947"
    ],
    [
        "cpf" => "237.279.853-72",
        "numero_cracha" => "28004"
    ],
    [
        "cpf" => "237.342.133-04",
        "numero_cracha" => "86307"
    ],
    [
        "cpf" => "238.477.003-91",
        "numero_cracha" => "28689"
    ],
    [
        "cpf" => "238.892.333-68",
        "numero_cracha" => "46929"
    ],
    [
        "cpf" => "249.779.003-59",
        "numero_cracha" => "81736"
    ],
    [
        "cpf" => "249.960.173-68",
        "numero_cracha" => "77530"
    ],
    [
        "cpf" => "250.104.333-20",
        "numero_cracha" => "76810"
    ],
    [
        "cpf" => "251.614.233-15",
        "numero_cracha" => "86906"
    ],
    [
        "cpf" => "251.844.903-53",
        "numero_cracha" => "77422"
    ],
    [
        "cpf" => "252.376.083-53",
        "numero_cracha" => "86882"
    ],
    [
        "cpf" => "253.049.503-30",
        "numero_cracha" => "47108"
    ],
    [
        "cpf" => "253.089.633-04",
        "numero_cracha" => "46641"
    ],
    [
        "cpf" => "253.486.473-49",
        "numero_cracha" => "30877"
    ],
    [
        "cpf" => "253.537.483-87",
        "numero_cracha" => "87351"
    ],
    [
        "cpf" => "253.747.533-04",
        "numero_cracha" => "77633"
    ],
    [
        "cpf" => "253.982.703-97",
        "numero_cracha" => "77535"
    ],
    [
        "cpf" => "255.773.443-87",
        "numero_cracha" => "87804"
    ],
    [
        "cpf" => "256.947.823-72",
        "numero_cracha" => "77514"
    ],
    [
        "cpf" => "257.395.903-10",
        "numero_cracha" => "46740"
    ],
    [
        "cpf" => "257.888.308-42",
        "numero_cracha" => "47497"
    ],
    [
        "cpf" => "257.921.646-49",
        "numero_cracha" => "47159"
    ],
    [
        "cpf" => "258.205.243-49",
        "numero_cracha" => "76494"
    ],
    [
        "cpf" => "263.876.178-80",
        "numero_cracha" => "76179"
    ],
    [
        "cpf" => "269.231.572-34",
        "numero_cracha" => "16343"
    ],
    [
        "cpf" => "269.491.803-49",
        "numero_cracha" => "85619"
    ],
    [
        "cpf" => "270.327.873-04",
        "numero_cracha" => "30998"
    ],
    [
        "cpf" => "270.429.603-00",
        "numero_cracha" => "46730"
    ],
    [
        "cpf" => "271.266.763-87",
        "numero_cracha" => "36869"
    ],
    [
        "cpf" => "271.817.663-68",
        "numero_cracha" => "77159"
    ],
    [
        "cpf" => "272.257.123-49",
        "numero_cracha" => "77531"
    ],
    [
        "cpf" => "272.798.986-53",
        "numero_cracha" => "16221"
    ],
    [
        "cpf" => "279.866.833-68",
        "numero_cracha" => "85698"
    ],
    [
        "cpf" => "280.716.918-01",
        "numero_cracha" => "78378"
    ],
    [
        "cpf" => "288.232.903-25",
        "numero_cracha" => "65903"
    ],
    [
        "cpf" => "288.820.832-68",
        "numero_cracha" => "36119"
    ],
    [
        "cpf" => "289.088.133-49",
        "numero_cracha" => "76515"
    ],
    [
        "cpf" => "289.229.468-14",
        "numero_cracha" => "47577"
    ],
    [
        "cpf" => "290.251.503-06",
        "numero_cracha" => "46928"
    ],
    [
        "cpf" => "290.276.323-91",
        "numero_cracha" => "N/A"
    ],
    [
        "cpf" => "290.605.083-00",
        "numero_cracha" => "86146"
    ],
    [
        "cpf" => "291.782.403-44",
        "numero_cracha" => "11612"
    ],
    [
        "cpf" => "292.136.733-53",
        "numero_cracha" => "86299"
    ],
    [
        "cpf" => "292.840.153-91",
        "numero_cracha" => "77395"
    ],
    [
        "cpf" => "294.077.102-20",
        "numero_cracha" => "000009090"
    ],
    [
        "cpf" => "303.245.643-68",
        "numero_cracha" => "N/A"
    ],
    [
        "cpf" => "303.471.573-00",
        "numero_cracha" => "84430"
    ],
    [
        "cpf" => "304.164.693-53",
        "numero_cracha" => "85844"
    ],
    [
        "cpf" => "307.127.748-23",
        "numero_cracha" => "66112"
    ],
    [
        "cpf" => "310.313.898-90",
        "numero_cracha" => "25620"
    ],
    [
        "cpf" => "318.847.252-87",
        "numero_cracha" => "000043835"
    ],
    [
        "cpf" => "329.714.192-15",
        "numero_cracha" => "35176"
    ],
    [
        "cpf" => "332.650.383-00",
        "numero_cracha" => "47257"
    ],
    [
        "cpf" => "337.072.193-72",
        "numero_cracha" => "85589"
    ],
    [
        "cpf" => "351.445.723-91",
        "numero_cracha" => "25609"
    ],
    [
        "cpf" => "352.050.083-34",
        "numero_cracha" => "76817"
    ],
    [
        "cpf" => "354.238.623-53",
        "numero_cracha" => "76720"
    ],
    [
        "cpf" => "355.039.983-91",
        "numero_cracha" => "78872"
    ],
    [
        "cpf" => "358.185.812-68",
        "numero_cracha" => "87329"
    ],
    [
        "cpf" => "368.656.805-53",
        "numero_cracha" => "86264"
    ],
    [
        "cpf" => "375.270.703-82",
        "numero_cracha" => "76707"
    ],
    [
        "cpf" => "376.006.993-20",
        "numero_cracha" => "78712"
    ],
    [
        "cpf" => "376.032.723-00",
        "numero_cracha" => "86126"
    ],
    [
        "cpf" => "376.609.262-68",
        "numero_cracha" => "000022660"
    ],
    [
        "cpf" => "376.736.533-20",
        "numero_cracha" => "76496"
    ],
    [
        "cpf" => "376.835.783-04",
        "numero_cracha" => "74867"
    ],
    [
        "cpf" => "387.836.145-91",
        "numero_cracha" => "77072"
    ],
    [
        "cpf" => "395.197.832-53",
        "numero_cracha" => "20246"
    ],
    [
        "cpf" => "400.864.418-09",
        "numero_cracha" => "79834"
    ],
    [
        "cpf" => "401.921.643-68",
        "numero_cracha" => "46978"
    ],
    [
        "cpf" => "404.891.593-20",
        "numero_cracha" => "86937"
    ],
    [
        "cpf" => "404.919.863-00",
        "numero_cracha" => "47475"
    ],
    [
        "cpf" => "405.424.403-34",
        "numero_cracha" => "84797"
    ],
    [
        "cpf" => "405.813.573-53",
        "numero_cracha" => "25659"
    ],
    [
        "cpf" => "407.220.663-68",
        "numero_cracha" => "86933"
    ],
    [
        "cpf" => "407.265.093-53",
        "numero_cracha" => "85633"
    ],
    [
        "cpf" => "407.288.703-04",
        "numero_cracha" => "82246"
    ],
    [
        "cpf" => "407.379.423-04",
        "numero_cracha" => "36205"
    ],
    [
        "cpf" => "408.005.983-34",
        "numero_cracha" => "46982"
    ],
    [
        "cpf" => "408.729.983-04",
        "numero_cracha" => "85639"
    ],
    [
        "cpf" => "409.356.203-20",
        "numero_cracha" => "86963"
    ],
    [
        "cpf" => "409.415.583-04",
        "numero_cracha" => "53896"
    ],
    [
        "cpf" => "418.003.703-34",
        "numero_cracha" => "26477"
    ],
    [
        "cpf" => "418.430.103-72",
        "numero_cracha" => "77318"
    ],
    [
        "cpf" => "418.585.813-20",
        "numero_cracha" => "20383"
    ],
    [
        "cpf" => "423.555.752-15",
        "numero_cracha" => "000044313"
    ],
    [
        "cpf" => "427.714.713-53",
        "numero_cracha" => "86964"
    ],
    [
        "cpf" => "428.028.063-00",
        "numero_cracha" => "53714"
    ],
    [
        "cpf" => "428.274.933-49",
        "numero_cracha" => "82294"
    ],
    [
        "cpf" => "431.541.743-20",
        "numero_cracha" => "86782"
    ],
    [
        "cpf" => "431.972.303-10",
        "numero_cracha" => "86319"
    ],
    [
        "cpf" => "432.144.013-00",
        "numero_cracha" => "86796"
    ],
    [
        "cpf" => "432.334.063-04",
        "numero_cracha" => "53947"
    ],
    [
        "cpf" => "432.422.003-49",
        "numero_cracha" => "66161"
    ],
    [
        "cpf" => "437.715.053-72",
        "numero_cracha" => "76703"
    ],
    [
        "cpf" => "438.093.673-20",
        "numero_cracha" => "28385"
    ],
    [
        "cpf" => "439.625.134-34",
        "numero_cracha" => "70840"
    ],
    [
        "cpf" => "444.804.073-91",
        "numero_cracha" => "86115"
    ],
    [
        "cpf" => "446.961.403-34",
        "numero_cracha" => "28413"
    ],
    [
        "cpf" => "449.840.573-00",
        "numero_cracha" => "86409"
    ],
    [
        "cpf" => "452.366.403-00",
        "numero_cracha" => "47261"
    ],
    [
        "cpf" => "452.946.743-00",
        "numero_cracha" => "79208"
    ],
    [
        "cpf" => "453.188.163-04",
        "numero_cracha" => "47670"
    ],
    [
        "cpf" => "453.189.563-00",
        "numero_cracha" => "87314"
    ],
    [
        "cpf" => "459.942.853-15",
        "numero_cracha" => "86190"
    ],
    [
        "cpf" => "467.305.085-15",
        "numero_cracha" => "86942"
    ],
    [
        "cpf" => "468.101.533-49",
        "numero_cracha" => "N/A"
    ],
    [
        "cpf" => "468.713.142-53",
        "numero_cracha" => "20708"
    ],
    [
        "cpf" => "471.249.463-87",
        "numero_cracha" => "74446"
    ],
    [
        "cpf" => "471.394.393-20",
        "numero_cracha" => "70617"
    ],
    [
        "cpf" => "474.570.233-72",
        "numero_cracha" => "31103"
    ],
    [
        "cpf" => "483.005.943-53",
        "numero_cracha" => "53633"
    ],
    [
        "cpf" => "483.520.983-49",
        "numero_cracha" => "87247"
    ],
    [
        "cpf" => "483.583.393-72",
        "numero_cracha" => "76868"
    ],
    [
        "cpf" => "488.068.463-53",
        "numero_cracha" => "77051"
    ],
    [
        "cpf" => "489.555.073-72",
        "numero_cracha" => "46879"
    ],
    [
        "cpf" => "489.603.903-30",
        "numero_cracha" => "85642"
    ],
    [
        "cpf" => "492.841.503-53",
        "numero_cracha" => "76502"
    ],
    [
        "cpf" => "493.521.053-20",
        "numero_cracha" => "76593"
    ],
    [
        "cpf" => "494.043.343-91",
        "numero_cracha" => "73522"
    ],
    [
        "cpf" => "507.678.603-49",
        "numero_cracha" => "38989"
    ],
    [
        "cpf" => "514.474.362-53",
        "numero_cracha" => "000046076"
    ],
    [
        "cpf" => "515.411.653-49",
        "numero_cracha" => "70936"
    ],
    [
        "cpf" => "515.466.123-00",
        "numero_cracha" => "38655"
    ],
    [
        "cpf" => "515.655.603-53",
        "numero_cracha" => "81099"
    ],
    [
        "cpf" => "515.704.402-04",
        "numero_cracha" => "000034169"
    ],
    [
        "cpf" => "515.730.163-49",
        "numero_cracha" => "46877"
    ],
    [
        "cpf" => "529.038.853-20",
        "numero_cracha" => "66046"
    ],
    [
        "cpf" => "529.275.983-04",
        "numero_cracha" => "85618"
    ],
    [
        "cpf" => "531.277.813-34",
        "numero_cracha" => "86970"
    ],
    [
        "cpf" => "542.703.592-34",
        "numero_cracha" => "000041452"
    ],
    [
        "cpf" => "548.263.222-91",
        "numero_cracha" => "54154"
    ],
    [
        "cpf" => "563.713.903-25",
        "numero_cracha" => "82812"
    ],
    [
        "cpf" => "571.398.903-82",
        "numero_cracha" => "76821"
    ],
    [
        "cpf" => "574.415.612-72",
        "numero_cracha" => "000050385"
    ],
    [
        "cpf" => "579.604.372-20",
        "numero_cracha" => "000100125"
    ],
    [
        "cpf" => "586.531.982-53",
        "numero_cracha" => "16483"
    ],
    [
        "cpf" => "588.420.712-34",
        "numero_cracha" => "77350"
    ],
    [
        "cpf" => "591.350.422-49",
        "numero_cracha" => "53796"
    ],
    [
        "cpf" => "594.103.102-53",
        "numero_cracha" => "100438"
    ],
    [
        "cpf" => "600.495.483-79",
        "numero_cracha" => "86302"
    ],
    [
        "cpf" => "601.058.273-39",
        "numero_cracha" => "86310"
    ],
    [
        "cpf" => "601.248.224-87",
        "numero_cracha" => "81866"
    ],
    [
        "cpf" => "601.438.793-59",
        "numero_cracha" => "47648"
    ],
    [
        "cpf" => "601.522.723-05",
        "numero_cracha" => "86636"
    ],
    [
        "cpf" => "601.621.793-08",
        "numero_cracha" => "87251"
    ],
    [
        "cpf" => "601.679.773-18",
        "numero_cracha" => "86124"
    ],
    [
        "cpf" => "601.691.833-43",
        "numero_cracha" => "77176"
    ],
    [
        "cpf" => "601.756.593-11",
        "numero_cracha" => "85624"
    ],
    [
        "cpf" => "601.862.303-03",
        "numero_cracha" => "87335"
    ],
    [
        "cpf" => "601.899.923-44",
        "numero_cracha" => "86134"
    ],
    [
        "cpf" => "602.003.253-10",
        "numero_cracha" => "84269"
    ],
    [
        "cpf" => "602.082.433-05",
        "numero_cracha" => "81728"
    ],
    [
        "cpf" => "602.108.413-62",
        "numero_cracha" => "81702"
    ],
    [
        "cpf" => "602.169.493-78",
        "numero_cracha" => "68790"
    ],
    [
        "cpf" => "602.171.083-50",
        "numero_cracha" => "86638"
    ],
    [
        "cpf" => "602.192.283-29",
        "numero_cracha" => "87844"
    ],
    [
        "cpf" => "602.254.993-04",
        "numero_cracha" => "21622"
    ],
    [
        "cpf" => "602.379.773-38",
        "numero_cracha" => "81088"
    ],
    [
        "cpf" => "602.425.923-92",
        "numero_cracha" => "21629"
    ],
    [
        "cpf" => "602.500.673-31",
        "numero_cracha" => "86953"
    ],
    [
        "cpf" => "602.535.653-08",
        "numero_cracha" => "69593"
    ],
    [
        "cpf" => "602.561.963-89",
        "numero_cracha" => "86144"
    ],
    [
        "cpf" => "602.581.483-06",
        "numero_cracha" => "84266"
    ],
    [
        "cpf" => "602.621.703-70",
        "numero_cracha" => "77809"
    ],
    [
        "cpf" => "602.694.743-40",
        "numero_cracha" => "71325"
    ],
    [
        "cpf" => "602.728.913-98",
        "numero_cracha" => "46500"
    ],
    [
        "cpf" => "602.744.783-40",
        "numero_cracha" => "76182"
    ],
    [
        "cpf" => "602.776.033-82",
        "numero_cracha" => "77308"
    ],
    [
        "cpf" => "602.788.823-74",
        "numero_cracha" => "49595"
    ],
    [
        "cpf" => "602.788.833-46",
        "numero_cracha" => "53617"
    ],
    [
        "cpf" => "602.789.523-30",
        "numero_cracha" => "74136"
    ],
    [
        "cpf" => "602.837.273-06",
        "numero_cracha" => "77403"
    ],
    [
        "cpf" => "602.876.283-02",
        "numero_cracha" => "46734"
    ],
    [
        "cpf" => "602.876.353-50",
        "numero_cracha" => "87338"
    ],
    [
        "cpf" => "602.885.203-19",
        "numero_cracha" => "86254"
    ],
    [
        "cpf" => "602.907.253-64",
        "numero_cracha" => "47036"
    ],
    [
        "cpf" => "602.907.333-83",
        "numero_cracha" => "77385"
    ],
    [
        "cpf" => "602.913.573-26",
        "numero_cracha" => "46988"
    ],
    [
        "cpf" => "602.957.043-90",
        "numero_cracha" => "76853"
    ],
    [
        "cpf" => "603.034.373-40",
        "numero_cracha" => "16311"
    ],
    [
        "cpf" => "603.045.853-19",
        "numero_cracha" => "86968"
    ],
    [
        "cpf" => "603.107.203-38",
        "numero_cracha" => "18251"
    ],
    [
        "cpf" => "603.196.133-48",
        "numero_cracha" => "86316"
    ],
    [
        "cpf" => "603.318.963-97",
        "numero_cracha" => "87258"
    ],
    [
        "cpf" => "603.391.373-63",
        "numero_cracha" => "69444"
    ],
    [
        "cpf" => "603.392.963-21",
        "numero_cracha" => "66542"
    ],
    [
        "cpf" => "603.442.413-51",
        "numero_cracha" => "70566"
    ],
    [
        "cpf" => "603.463.253-66",
        "numero_cracha" => "77321"
    ],
    [
        "cpf" => "603.545.513-10",
        "numero_cracha" => "77521"
    ],
    [
        "cpf" => "603.837.513-90",
        "numero_cracha" => "87326"
    ],
    [
        "cpf" => "604.057.213-23",
        "numero_cracha" => "76181"
    ],
    [
        "cpf" => "604.144.593-26",
        "numero_cracha" => "73542"
    ],
    [
        "cpf" => "604.221.213-39",
        "numero_cracha" => "77396"
    ],
    [
        "cpf" => "604.228.383-90",
        "numero_cracha" => "89373"
    ],
    [
        "cpf" => "604.359.473-03",
        "numero_cracha" => "86443"
    ],
    [
        "cpf" => "604.679.293-27",
        "numero_cracha" => "12018"
    ],
    [
        "cpf" => "604.719.473-77",
        "numero_cracha" => "77323"
    ],
    [
        "cpf" => "604.728.273-32",
        "numero_cracha" => "86237"
    ],
    [
        "cpf" => "604.789.803-37",
        "numero_cracha" => "53682"
    ],
    [
        "cpf" => "604.839.063-79",
        "numero_cracha" => "77647"
    ],
    [
        "cpf" => "604.884.373-93",
        "numero_cracha" => "76709"
    ],
    [
        "cpf" => "604.964.433-05",
        "numero_cracha" => "19821"
    ],
    [
        "cpf" => "604.964.923-57",
        "numero_cracha" => "86908"
    ],
    [
        "cpf" => "605.038.983-76",
        "numero_cracha" => "79753"
    ],
    [
        "cpf" => "605.092.613-10",
        "numero_cracha" => "19828"
    ],
    [
        "cpf" => "605.175.473-33",
        "numero_cracha" => "70065"
    ],
    [
        "cpf" => "605.234.013-45",
        "numero_cracha" => "87324"
    ],
    [
        "cpf" => "605.458.713-79",
        "numero_cracha" => "81724"
    ],
    [
        "cpf" => "605.672.803-06",
        "numero_cracha" => "87403"
    ],
    [
        "cpf" => "605.921.903-90",
        "numero_cracha" => "76986"
    ],
    [
        "cpf" => "605.961.073-07",
        "numero_cracha" => "82797"
    ],
    [
        "cpf" => "606.361.123-14",
        "numero_cracha" => "21628"
    ],
    [
        "cpf" => "606.463.793-56",
        "numero_cracha" => "86913"
    ],
    [
        "cpf" => "606.480.013-50",
        "numero_cracha" => "13325"
    ],
    [
        "cpf" => "606.504.903-47",
        "numero_cracha" => "77338"
    ],
    [
        "cpf" => "606.571.513-19",
        "numero_cracha" => "25863"
    ],
    [
        "cpf" => "606.706.503-74",
        "numero_cracha" => "82790"
    ],
    [
        "cpf" => "606.721.033-97",
        "numero_cracha" => "69517"
    ],
    [
        "cpf" => "606.760.503-10",
        "numero_cracha" => "53721"
    ],
    [
        "cpf" => "606.801.853-99",
        "numero_cracha" => "88506"
    ],
    [
        "cpf" => "606.845.943-82",
        "numero_cracha" => "82763"
    ],
    [
        "cpf" => "606.861.013-67",
        "numero_cracha" => "86317"
    ],
    [
        "cpf" => "606.889.853-94",
        "numero_cracha" => "68692"
    ],
    [
        "cpf" => "607.017.313-97",
        "numero_cracha" => "73524"
    ],
    [
        "cpf" => "607.146.823-06",
        "numero_cracha" => "88195"
    ],
    [
        "cpf" => "607.218.823-07",
        "numero_cracha" => "85626"
    ],
    [
        "cpf" => "607.236.853-04",
        "numero_cracha" => "86965"
    ],
    [
        "cpf" => "607.241.093-66",
        "numero_cracha" => "81707"
    ],
    [
        "cpf" => "607.424.953-96",
        "numero_cracha" => "86118"
    ],
    [
        "cpf" => "607.517.783-38",
        "numero_cracha" => "53639"
    ],
    [
        "cpf" => "607.552.233-65",
        "numero_cracha" => "71334"
    ],
    [
        "cpf" => "607.559.523-67",
        "numero_cracha" => "76819"
    ],
    [
        "cpf" => "607.598.743-60",
        "numero_cracha" => "86154"
    ],
    [
        "cpf" => "607.637.963-44",
        "numero_cracha" => "77331"
    ],
    [
        "cpf" => "607.656.803-86",
        "numero_cracha" => "86927"
    ],
    [
        "cpf" => "607.694.783-79",
        "numero_cracha" => "85609"
    ],
    [
        "cpf" => "607.704.803-81",
        "numero_cracha" => "86128"
    ],
    [
        "cpf" => "607.757.073-76",
        "numero_cracha" => "53701"
    ],
    [
        "cpf" => "607.761.943-48",
        "numero_cracha" => "76864"
    ],
    [
        "cpf" => "607.775.003-45",
        "numero_cracha" => "86114"
    ],
    [
        "cpf" => "607.804.373-06",
        "numero_cracha" => "87396"
    ],
    [
        "cpf" => "607.812.123-59",
        "numero_cracha" => "85837"
    ],
    [
        "cpf" => "607.895.173-45",
        "numero_cracha" => "47915"
    ],
    [
        "cpf" => "608.021.403-27",
        "numero_cracha" => "73539"
    ],
    [
        "cpf" => "608.044.433-00",
        "numero_cracha" => "49120"
    ],
    [
        "cpf" => "608.306.093-18",
        "numero_cracha" => "19823"
    ],
    [
        "cpf" => "608.372.553-41",
        "numero_cracha" => "73595"
    ],
    [
        "cpf" => "608.430.033-24",
        "numero_cracha" => "86283"
    ],
    [
        "cpf" => "608.630.753-98",
        "numero_cracha" => "53439"
    ],
    [
        "cpf" => "608.739.843-07",
        "numero_cracha" => "86445"
    ],
    [
        "cpf" => "608.831.873-29",
        "numero_cracha" => "86956"
    ],
    [
        "cpf" => "608.897.413-36",
        "numero_cracha" => "86188"
    ],
    [
        "cpf" => "608.972.133-60",
        "numero_cracha" => "87337"
    ],
    [
        "cpf" => "609.054.073-00",
        "numero_cracha" => "47906"
    ],
    [
        "cpf" => "609.163.513-19",
        "numero_cracha" => "86292"
    ],
    [
        "cpf" => "609.218.843-03",
        "numero_cracha" => "27164"
    ],
    [
        "cpf" => "609.230.443-02",
        "numero_cracha" => "53631"
    ],
    [
        "cpf" => "609.302.673-62",
        "numero_cracha" => "87120"
    ],
    [
        "cpf" => "609.393.223-07",
        "numero_cracha" => "77185"
    ],
    [
        "cpf" => "609.396.263-65",
        "numero_cracha" => "25928"
    ],
    [
        "cpf" => "609.401.993-86",
        "numero_cracha" => "71318"
    ],
    [
        "cpf" => "609.478.853-23",
        "numero_cracha" => "80131"
    ],
    [
        "cpf" => "609.563.113-00",
        "numero_cracha" => "73556"
    ],
    [
        "cpf" => "609.795.083-71",
        "numero_cracha" => "87112"
    ],
    [
        "cpf" => "609.801.323-39",
        "numero_cracha" => "86240"
    ],
    [
        "cpf" => "609.893.833-42",
        "numero_cracha" => "88208"
    ],
    [
        "cpf" => "609.930.053-81",
        "numero_cracha" => "76612"
    ],
    [
        "cpf" => "609.980.213-42",
        "numero_cracha" => "86430"
    ],
    [
        "cpf" => "610.021.263-39",
        "numero_cracha" => "48861"
    ],
    [
        "cpf" => "610.064.413-44",
        "numero_cracha" => "70941"
    ],
    [
        "cpf" => "610.091.503-08",
        "numero_cracha" => "47431"
    ],
    [
        "cpf" => "610.113.283-80",
        "numero_cracha" => "89070"
    ],
    [
        "cpf" => "610.224.853-85",
        "numero_cracha" => "21964"
    ],
    [
        "cpf" => "610.237.003-10",
        "numero_cracha" => "47973"
    ],
    [
        "cpf" => "610.371.573-38",
        "numero_cracha" => "87181"
    ],
    [
        "cpf" => "610.373.843-18",
        "numero_cracha" => "86252"
    ],
    [
        "cpf" => "610.433.373-77",
        "numero_cracha" => "53634"
    ],
    [
        "cpf" => "610.472.783-24",
        "numero_cracha" => "87341"
    ],
    [
        "cpf" => "610.527.383-54",
        "numero_cracha" => "86321"
    ],
    [
        "cpf" => "610.530.773-00",
        "numero_cracha" => "84272"
    ],
    [
        "cpf" => "610.537.053-95",
        "numero_cracha" => "84800"
    ],
    [
        "cpf" => "610.579.433-98",
        "numero_cracha" => "48048"
    ],
    [
        "cpf" => "610.599.123-17",
        "numero_cracha" => "74143"
    ],
    [
        "cpf" => "610.607.632-49",
        "numero_cracha" => "25050"
    ],
    [
        "cpf" => "610.773.413-93",
        "numero_cracha" => "85620"
    ],
    [
        "cpf" => "610.958.533-50",
        "numero_cracha" => "77420"
    ],
    [
        "cpf" => "611.017.693-19",
        "numero_cracha" => "82312"
    ],
    [
        "cpf" => "611.018.793-35",
        "numero_cracha" => "79830"
    ],
    [
        "cpf" => "611.064.313-08",
        "numero_cracha" => "87552"
    ],
    [
        "cpf" => "611.070.093-24",
        "numero_cracha" => "70341"
    ],
    [
        "cpf" => "611.424.743-48",
        "numero_cracha" => "82788"
    ],
    [
        "cpf" => "611.483.083-00",
        "numero_cracha" => "76993"
    ],
    [
        "cpf" => "611.484.293-60",
        "numero_cracha" => "53632"
    ],
    [
        "cpf" => "611.674.253-08",
        "numero_cracha" => "87554"
    ],
    [
        "cpf" => "611.870.123-78",
        "numero_cracha" => "76830"
    ],
    [
        "cpf" => "611.973.253-52",
        "numero_cracha" => "86876"
    ],
    [
        "cpf" => "612.041.063-55",
        "numero_cracha" => "53437"
    ],
    [
        "cpf" => "612.095.813-45",
        "numero_cracha" => "86772"
    ],
    [
        "cpf" => "612.343.953-71",
        "numero_cracha" => "87121"
    ],
    [
        "cpf" => "612.359.333-18",
        "numero_cracha" => "70930"
    ],
    [
        "cpf" => "612.499.073-30",
        "numero_cracha" => "25619"
    ],
    [
        "cpf" => "612.613.713-21",
        "numero_cracha" => "87119"
    ],
    [
        "cpf" => "612.648.933-02",
        "numero_cracha" => "77370"
    ],
    [
        "cpf" => "612.670.333-20",
        "numero_cracha" => "84267"
    ],
    [
        "cpf" => "612.727.153-37",
        "numero_cracha" => "50456"
    ],
    [
        "cpf" => "612.744.323-75",
        "numero_cracha" => "76493"
    ],
    [
        "cpf" => "612.783.143-18",
        "numero_cracha" => "85558"
    ],
    [
        "cpf" => "612.811.353-21",
        "numero_cracha" => "70061"
    ],
    [
        "cpf" => "612.821.673-08",
        "numero_cracha" => "70066"
    ],
    [
        "cpf" => "612.867.953-62",
        "numero_cracha" => "86312"
    ],
    [
        "cpf" => "612.892.903-69",
        "numero_cracha" => "85611"
    ],
    [
        "cpf" => "612.901.913-01",
        "numero_cracha" => "87118"
    ],
    [
        "cpf" => "612.913.933-07",
        "numero_cracha" => "73530"
    ],
    [
        "cpf" => "612.956.813-40",
        "numero_cracha" => "86294"
    ],
    [
        "cpf" => "612.962.543-05",
        "numero_cracha" => "77078"
    ],
    [
        "cpf" => "613.004.563-88",
        "numero_cracha" => "87817"
    ],
    [
        "cpf" => "613.017.853-00",
        "numero_cracha" => "85540"
    ],
    [
        "cpf" => "613.166.863-96",
        "numero_cracha" => "88196"
    ],
    [
        "cpf" => "613.188.123-56",
        "numero_cracha" => "87381"
    ],
    [
        "cpf" => "613.262.973-41",
        "numero_cracha" => "84240"
    ],
    [
        "cpf" => "613.284.693-01",
        "numero_cracha" => "85697"
    ],
    [
        "cpf" => "613.287.723-14",
        "numero_cracha" => "47901"
    ],
    [
        "cpf" => "613.291.783-78",
        "numero_cracha" => "77399"
    ],
    [
        "cpf" => "613.310.993-90",
        "numero_cracha" => "85835"
    ],
    [
        "cpf" => "613.323.313-35",
        "numero_cracha" => "76178"
    ],
    [
        "cpf" => "613.324.903-01",
        "numero_cracha" => "86238"
    ],
    [
        "cpf" => "613.340.823-59",
        "numero_cracha" => "86122"
    ],
    [
        "cpf" => "613.341.223-26",
        "numero_cracha" => "85588"
    ],
    [
        "cpf" => "613.344.883-04",
        "numero_cracha" => "86267"
    ],
    [
        "cpf" => "613.373.333-01",
        "numero_cracha" => "87810"
    ],
    [
        "cpf" => "613.415.523-30",
        "numero_cracha" => "86120"
    ],
    [
        "cpf" => "613.423.553-93",
        "numero_cracha" => "87344"
    ],
    [
        "cpf" => "613.453.453-64",
        "numero_cracha" => "86301"
    ],
    [
        "cpf" => "613.455.503-75",
        "numero_cracha" => "71331"
    ],
    [
        "cpf" => "613.456.593-85",
        "numero_cracha" => "86111"
    ],
    [
        "cpf" => "613.474.353-44",
        "numero_cracha" => "86245"
    ],
    [
        "cpf" => "613.475.143-09",
        "numero_cracha" => "87785"
    ],
    [
        "cpf" => "613.497.383-10",
        "numero_cracha" => "87824"
    ],
    [
        "cpf" => "613.527.633-62",
        "numero_cracha" => "86314"
    ],
    [
        "cpf" => "613.532.783-60",
        "numero_cracha" => "82739"
    ],
    [
        "cpf" => "613.549.043-54",
        "numero_cracha" => "86119"
    ],
    [
        "cpf" => "613.553.693-12",
        "numero_cracha" => "68558"
    ],
    [
        "cpf" => "613.582.963-75",
        "numero_cracha" => "77641"
    ],
    [
        "cpf" => "613.592.883-09",
        "numero_cracha" => "86378"
    ],
    [
        "cpf" => "613.595.933-65",
        "numero_cracha" => "80137"
    ],
    [
        "cpf" => "613.613.673-26",
        "numero_cracha" => "86255"
    ],
    [
        "cpf" => "613.615.863-97",
        "numero_cracha" => "76188"
    ],
    [
        "cpf" => "613.663.363-99",
        "numero_cracha" => "73535"
    ],
    [
        "cpf" => "613.758.523-96",
        "numero_cracha" => "86284"
    ],
    [
        "cpf" => "613.766.053-25",
        "numero_cracha" => "82786"
    ],
    [
        "cpf" => "613.778.343-01",
        "numero_cracha" => "79832"
    ],
    [
        "cpf" => "613.790.013-40",
        "numero_cracha" => "69917"
    ],
    [
        "cpf" => "613.817.833-50",
        "numero_cracha" => "76518"
    ],
    [
        "cpf" => "613.850.923-43",
        "numero_cracha" => "87787"
    ],
    [
        "cpf" => "613.896.843-34",
        "numero_cracha" => "79192"
    ],
    [
        "cpf" => "613.976.253-76",
        "numero_cracha" => "77344"
    ],
    [
        "cpf" => "613.989.573-13",
        "numero_cracha" => "76968"
    ],
    [
        "cpf" => "614.025.003-00",
        "numero_cracha" => "71756"
    ],
    [
        "cpf" => "614.034.013-65",
        "numero_cracha" => "86322"
    ],
    [
        "cpf" => "614.117.023-44",
        "numero_cracha" => "87827"
    ],
    [
        "cpf" => "614.176.483-50",
        "numero_cracha" => "85602"
    ],
    [
        "cpf" => "614.257.223-93",
        "numero_cracha" => "87798"
    ],
    [
        "cpf" => "614.367.723-97",
        "numero_cracha" => "86241"
    ],
    [
        "cpf" => "614.501.943-38",
        "numero_cracha" => "71003"
    ],
    [
        "cpf" => "614.674.813-78",
        "numero_cracha" => "77426"
    ],
    [
        "cpf" => "614.801.673-71",
        "numero_cracha" => "26955"
    ],
    [
        "cpf" => "614.802.573-62",
        "numero_cracha" => "70335"
    ],
    [
        "cpf" => "614.840.173-82",
        "numero_cracha" => "71328"
    ],
    [
        "cpf" => "615.002.143-29",
        "numero_cracha" => "87819"
    ],
    [
        "cpf" => "615.144.413-22",
        "numero_cracha" => "47761"
    ],
    [
        "cpf" => "615.173.653-25",
        "numero_cracha" => "82311"
    ],
    [
        "cpf" => "615.401.373-68",
        "numero_cracha" => "86116"
    ],
    [
        "cpf" => "615.435.263-81",
        "numero_cracha" => "86878"
    ],
    [
        "cpf" => "615.573.803-33",
        "numero_cracha" => "86110"
    ],
    [
        "cpf" => "615.750.553-25",
        "numero_cracha" => "86262"
    ],
    [
        "cpf" => "615.780.413-09",
        "numero_cracha" => "86108"
    ],
    [
        "cpf" => "615.789.453-99",
        "numero_cracha" => "86263"
    ],
    [
        "cpf" => "615.789.553-51",
        "numero_cracha" => "86159"
    ],
    [
        "cpf" => "616.082.983-10",
        "numero_cracha" => "87836"
    ],
    [
        "cpf" => "616.195.683-73",
        "numero_cracha" => "21207"
    ],
    [
        "cpf" => "616.254.033-21",
        "numero_cracha" => "81729"
    ],
    [
        "cpf" => "616.660.453-06",
        "numero_cracha" => "82740"
    ],
    [
        "cpf" => "616.673.843-92",
        "numero_cracha" => "79828"
    ],
    [
        "cpf" => "616.781.493-73",
        "numero_cracha" => "85453"
    ],
    [
        "cpf" => "616.822.403-39",
        "numero_cracha" => "79816"
    ],
    [
        "cpf" => "616.830.653-60",
        "numero_cracha" => "69597"
    ],
    [
        "cpf" => "616.831.143-29",
        "numero_cracha" => "85461"
    ],
    [
        "cpf" => "616.839.533-47",
        "numero_cracha" => "71006"
    ],
    [
        "cpf" => "616.840.363-99",
        "numero_cracha" => "82293"
    ],
    [
        "cpf" => "616.882.973-32",
        "numero_cracha" => "68561"
    ],
    [
        "cpf" => "616.887.013-03",
        "numero_cracha" => "88884"
    ],
    [
        "cpf" => "616.897.673-67",
        "numero_cracha" => "71758"
    ],
    [
        "cpf" => "616.971.723-85",
        "numero_cracha" => "70340"
    ],
    [
        "cpf" => "616.982.633-92",
        "numero_cracha" => "70338"
    ],
    [
        "cpf" => "617.024.223-05",
        "numero_cracha" => "87255"
    ],
    [
        "cpf" => "617.060.893-50",
        "numero_cracha" => "89362"
    ],
    [
        "cpf" => "617.186.943-00",
        "numero_cracha" => "48090"
    ],
    [
        "cpf" => "617.188.633-57",
        "numero_cracha" => "53638"
    ],
    [
        "cpf" => "617.398.543-81",
        "numero_cracha" => "79829"
    ],
    [
        "cpf" => "617.460.923-51",
        "numero_cracha" => "68796"
    ],
    [
        "cpf" => "617.531.973-76",
        "numero_cracha" => "50921"
    ],
    [
        "cpf" => "617.632.233-29",
        "numero_cracha" => "73528"
    ],
    [
        "cpf" => "617.669.363-26",
        "numero_cracha" => "87226"
    ],
    [
        "cpf" => "617.679.703-90",
        "numero_cracha" => "77649"
    ],
    [
        "cpf" => "617.737.862-53",
        "numero_cracha" => "27177"
    ],
    [
        "cpf" => "618.195.423-60",
        "numero_cracha" => "85838"
    ],
    [
        "cpf" => "618.590.163-32",
        "numero_cracha" => "21201"
    ],
    [
        "cpf" => "619.059.663-00",
        "numero_cracha" => "85455"
    ],
    [
        "cpf" => "619.255.653-94",
        "numero_cracha" => "87111"
    ],
    [
        "cpf" => "619.305.363-80",
        "numero_cracha" => "86432"
    ],
    [
        "cpf" => "619.473.583-08",
        "numero_cracha" => "52328"
    ],
    [
        "cpf" => "619.494.403-03",
        "numero_cracha" => "77655"
    ],
    [
        "cpf" => "619.494.623-74",
        "numero_cracha" => "82770"
    ],
    [
        "cpf" => "620.325.502-53",
        "numero_cracha" => "49100"
    ],
    [
        "cpf" => "620.362.783-61",
        "numero_cracha" => "70934"
    ],
    [
        "cpf" => "620.394.053-48",
        "numero_cracha" => "77405"
    ],
    [
        "cpf" => "620.482.583-60",
        "numero_cracha" => "87832"
    ],
    [
        "cpf" => "620.922.563-27",
        "numero_cracha" => "52007"
    ],
    [
        "cpf" => "620.973.413-81",
        "numero_cracha" => "53687"
    ],
    [
        "cpf" => "621.171.163-88",
        "numero_cracha" => "85452"
    ],
    [
        "cpf" => "621.502.943-20",
        "numero_cracha" => "40407"
    ],
    [
        "cpf" => "621.733.683-97",
        "numero_cracha" => "74134"
    ],
    [
        "cpf" => "621.733.813-00",
        "numero_cracha" => "53628"
    ],
    [
        "cpf" => "622.250.313-63",
        "numero_cracha" => "71322"
    ],
    [
        "cpf" => "622.634.903-45",
        "numero_cracha" => "79206"
    ],
    [
        "cpf" => "623.278.923-76",
        "numero_cracha" => "87311"
    ],
    [
        "cpf" => "623.329.993-45",
        "numero_cracha" => "86891"
    ],
    [
        "cpf" => "623.389.233-32",
        "numero_cracha" => "87114"
    ],
    [
        "cpf" => "623.446.613-36",
        "numero_cracha" => "82286"
    ],
    [
        "cpf" => "624.286.673-00",
        "numero_cracha" => "88718"
    ],
    [
        "cpf" => "624.650.103-60",
        "numero_cracha" => "77546"
    ],
    [
        "cpf" => "624.742.863-49",
        "numero_cracha" => "49266"
    ],
    [
        "cpf" => "625.177.383-94",
        "numero_cracha" => "81655"
    ],
    [
        "cpf" => "625.772.903-30",
        "numero_cracha" => "74128"
    ],
    [
        "cpf" => "627.198.072-49",
        "numero_cracha" => "000022593"
    ],
    [
        "cpf" => "627.431.123-87",
        "numero_cracha" => "18216"
    ],
    [
        "cpf" => "627.431.203-04",
        "numero_cracha" => "17123"
    ],
    [
        "cpf" => "627.859.233-91",
        "numero_cracha" => "76818"
    ],
    [
        "cpf" => "631.328.943-97",
        "numero_cracha" => "86243"
    ],
    [
        "cpf" => "631.923.183-17",
        "numero_cracha" => "85597"
    ],
    [
        "cpf" => "634.861.302-00",
        "numero_cracha" => "000035471"
    ],
    [
        "cpf" => "637.739.253-28",
        "numero_cracha" => "79817"
    ],
    [
        "cpf" => "637.935.203-15",
        "numero_cracha" => "77086"
    ],
    [
        "cpf" => "637.955.153-00",
        "numero_cracha" => "11611"
    ],
    [
        "cpf" => "638.710.413-00",
        "numero_cracha" => "81663"
    ],
    [
        "cpf" => "638.789.763-72",
        "numero_cracha" => "85592"
    ],
    [
        "cpf" => "640.807.223-20",
        "numero_cracha" => "47669"
    ],
    [
        "cpf" => "641.375.113-49",
        "numero_cracha" => "79195"
    ],
    [
        "cpf" => "642.201.173-34",
        "numero_cracha" => "30773"
    ],
    [
        "cpf" => "642.294.833-68",
        "numero_cracha" => "76192"
    ],
    [
        "cpf" => "642.340.613-87",
        "numero_cracha" => "86778"
    ],
    [
        "cpf" => "643.599.723-34",
        "numero_cracha" => "77361"
    ],
    [
        "cpf" => "643.888.163-53",
        "numero_cracha" => "46643"
    ],
    [
        "cpf" => "644.235.222-68",
        "numero_cracha" => "4969"
    ],
    [
        "cpf" => "644.617.003-30",
        "numero_cracha" => "53712"
    ],
    [
        "cpf" => "644.951.673-91",
        "numero_cracha" => "86392"
    ],
    [
        "cpf" => "645.758.533-72",
        "numero_cracha" => "77335"
    ],
    [
        "cpf" => "645.995.903-04",
        "numero_cracha" => "66045"
    ],
    [
        "cpf" => "647.408.973-34",
        "numero_cracha" => "47717"
    ],
    [
        "cpf" => "647.726.243-68",
        "numero_cracha" => "77541"
    ],
    [
        "cpf" => "648.167.023-34",
        "numero_cracha" => "86411"
    ],
    [
        "cpf" => "648.811.803-04",
        "numero_cracha" => "36879"
    ],
    [
        "cpf" => "649.982.823-87",
        "numero_cracha" => "88524"
    ],
    [
        "cpf" => "650.577.593-53",
        "numero_cracha" => "76180"
    ],
    [
        "cpf" => "652.296.493-72",
        "numero_cracha" => "86141"
    ],
    [
        "cpf" => "652.385.323-34",
        "numero_cracha" => "67211"
    ],
    [
        "cpf" => "653.391.933-49",
        "numero_cracha" => "28075"
    ],
    [
        "cpf" => "653.734.483-20",
        "numero_cracha" => "46640"
    ],
    [
        "cpf" => "654.625.643-68",
        "numero_cracha" => "77304"
    ],
    [
        "cpf" => "654.792.463-72",
        "numero_cracha" => "86866"
    ],
    [
        "cpf" => "655.016.403-68",
        "numero_cracha" => "47583"
    ],
    [
        "cpf" => "655.521.803-72",
        "numero_cracha" => "77407"
    ],
    [
        "cpf" => "656.315.523-53",
        "numero_cracha" => "47593"
    ],
    [
        "cpf" => "656.754.793-68",
        "numero_cracha" => "86449"
    ],
    [
        "cpf" => "656.844.513-49",
        "numero_cracha" => "76700"
    ],
    [
        "cpf" => "659.157.122-15",
        "numero_cracha" => "000010475"
    ],
    [
        "cpf" => "659.232.432-53",
        "numero_cracha" => "27297"
    ],
    [
        "cpf" => "659.350.473-49",
        "numero_cracha" => "77628"
    ],
    [
        "cpf" => "659.354.033-15",
        "numero_cracha" => "13915"
    ],
    [
        "cpf" => "659.437.083-91",
        "numero_cracha" => "86777"
    ],
    [
        "cpf" => "660.381.623-72",
        "numero_cracha" => "87348"
    ],
    [
        "cpf" => "661.824.332-72",
        "numero_cracha" => "000033363"
    ],
    [
        "cpf" => "662.036.863-87",
        "numero_cracha" => "85135"
    ],
    [
        "cpf" => "662.800.603-49",
        "numero_cracha" => "85667"
    ],
    [
        "cpf" => "662.879.283-87",
        "numero_cracha" => "46726"
    ],
    [
        "cpf" => "663.147.043-91",
        "numero_cracha" => "46963"
    ],
    [
        "cpf" => "663.586.123-87",
        "numero_cracha" => "76504"
    ],
    [
        "cpf" => "665.160.483-04",
        "numero_cracha" => "86773"
    ],
    [
        "cpf" => "665.528.343-49",
        "numero_cracha" => "77813"
    ],
    [
        "cpf" => "666.751.463-00",
        "numero_cracha" => "48667"
    ],
    [
        "cpf" => "666.758.983-53",
        "numero_cracha" => "77175"
    ],
    [
        "cpf" => "667.809.853-68",
        "numero_cracha" => "86949"
    ],
    [
        "cpf" => "670.491.252-53",
        "numero_cracha" => "50195"
    ],
    [
        "cpf" => "672.629.613-49",
        "numero_cracha" => "68567"
    ],
    [
        "cpf" => "677.160.322-91",
        "numero_cracha" => "000040437"
    ],
    [
        "cpf" => "684.803.693-53",
        "numero_cracha" => "76983"
    ],
    [
        "cpf" => "685.649.023-20",
        "numero_cracha" => "38812"
    ],
    [
        "cpf" => "686.686.192-68",
        "numero_cracha" => "30206"
    ],
    [
        "cpf" => "687.945.172-15",
        "numero_cracha" => "77079"
    ],
    [
        "cpf" => "689.752.362-34",
        "numero_cracha" => "000050676"
    ],
    [
        "cpf" => "691.012.643-20",
        "numero_cracha" => "6202"
    ],
    [
        "cpf" => "691.664.552-00",
        "numero_cracha" => "42845"
    ],
    [
        "cpf" => "691.760.603-06",
        "numero_cracha" => "70932"
    ],
    [
        "cpf" => "696.802.132-34",
        "numero_cracha" => "000025684"
    ],
    [
        "cpf" => "700.485.612-74",
        "numero_cracha" => "50306"
    ],
    [
        "cpf" => "700.654.892-68",
        "numero_cracha" => "824"
    ],
    [
        "cpf" => "701.025.342-09",
        "numero_cracha" => "49898"
    ],
    [
        "cpf" => "701.842.082-20",
        "numero_cracha" => "48404"
    ],
    [
        "cpf" => "702.451.852-91",
        "numero_cracha" => "72652"
    ],
    [
        "cpf" => "702.982.002-95",
        "numero_cracha" => "000055442"
    ],
    [
        "cpf" => "705.259.193-68",
        "numero_cracha" => "77526"
    ],
    [
        "cpf" => "713.024.843-20",
        "numero_cracha" => "83769"
    ],
    [
        "cpf" => "713.043.043-53",
        "numero_cracha" => "82292"
    ],
    [
        "cpf" => "713.598.692-04",
        "numero_cracha" => "000009007"
    ],
    [
        "cpf" => "718.826.262-49",
        "numero_cracha" => "41495"
    ],
    [
        "cpf" => "721.375.093-34",
        "numero_cracha" => "30766"
    ],
    [
        "cpf" => "721.459.193-68",
        "numero_cracha" => "84271"
    ],
    [
        "cpf" => "721.611.833-20",
        "numero_cracha" => "76715"
    ],
    [
        "cpf" => "724.012.493-20",
        "numero_cracha" => "82782"
    ],
    [
        "cpf" => "725.068.903-78",
        "numero_cracha" => "47597"
    ],
    [
        "cpf" => "725.647.921-20",
        "numero_cracha" => "87222"
    ],
    [
        "cpf" => "726.851.583-91",
        "numero_cracha" => "86281"
    ],
    [
        "cpf" => "727.054.773-49",
        "numero_cracha" => "46813"
    ],
    [
        "cpf" => "731.319.973-20",
        "numero_cracha" => "79444"
    ],
    [
        "cpf" => "732.423.907-25",
        "numero_cracha" => "84268"
    ],
    [
        "cpf" => "735.171.193-34",
        "numero_cracha" => "87409"
    ],
    [
        "cpf" => "736.170.243-00",
        "numero_cracha" => "87104"
    ],
    [
        "cpf" => "736.529.973-87",
        "numero_cracha" => "87406"
    ],
    [
        "cpf" => "741.993.403-78",
        "numero_cracha" => "46719"
    ],
    [
        "cpf" => "745.060.053-68",
        "numero_cracha" => "88719"
    ],
    [
        "cpf" => "745.585.363-72",
        "numero_cracha" => "76190"
    ],
    [
        "cpf" => "746.567.673-87",
        "numero_cracha" => "77058"
    ],
    [
        "cpf" => "746.604.703-34",
        "numero_cracha" => "86121"
    ],
    [
        "cpf" => "747.768.863-91",
        "numero_cracha" => "87253"
    ],
    [
        "cpf" => "749.766.563-49",
        "numero_cracha" => "76813"
    ],
    [
        "cpf" => "750.033.413-34",
        "numero_cracha" => "87179"
    ],
    [
        "cpf" => "752.385.193-20",
        "numero_cracha" => "47321"
    ],
    [
        "cpf" => "753.961.062-04",
        "numero_cracha" => "000021476"
    ],
    [
        "cpf" => "755.824.653-91",
        "numero_cracha" => "82733"
    ],
    [
        "cpf" => "756.174.633-49",
        "numero_cracha" => "86187"
    ],
    [
        "cpf" => "756.281.713-87",
        "numero_cracha" => "53827"
    ],
    [
        "cpf" => "756.299.763-20",
        "numero_cracha" => "87256"
    ],
    [
        "cpf" => "757.551.493-72",
        "numero_cracha" => "46993"
    ],
    [
        "cpf" => "757.689.503-97",
        "numero_cracha" => "66530"
    ],
    [
        "cpf" => "758.785.003-10",
        "numero_cracha" => "87123"
    ],
    [
        "cpf" => "759.293.332-20",
        "numero_cracha" => "2665"
    ],
    [
        "cpf" => "759.687.893-87",
        "numero_cracha" => "86296"
    ],
    [
        "cpf" => "759.937.913-49",
        "numero_cracha" => "69591"
    ],
    [
        "cpf" => "760.647.862-72",
        "numero_cracha" => "000036562"
    ],
    [
        "cpf" => "761.070.353-20",
        "numero_cracha" => "76970"
    ],
    [
        "cpf" => "761.103.463-49",
        "numero_cracha" => "78877"
    ],
    [
        "cpf" => "762.893.973-20",
        "numero_cracha" => "60732"
    ],
    [
        "cpf" => "763.094.012-20",
        "numero_cracha" => "000000896"
    ],
    [
        "cpf" => "763.134.763-87",
        "numero_cracha" => "76712"
    ],
    [
        "cpf" => "763.747.003-25",
        "numero_cracha" => "66043"
    ],
    [
        "cpf" => "769.585.823-49",
        "numero_cracha" => "5654"
    ],
    [
        "cpf" => "772.557.393-34",
        "numero_cracha" => "36878"
    ],
    [
        "cpf" => "773.388.983-91",
        "numero_cracha" => "86911"
    ],
    [
        "cpf" => "773.707.403-15",
        "numero_cracha" => "86450"
    ],
    [
        "cpf" => "780.976.143-91",
        "numero_cracha" => "76863"
    ],
    [
        "cpf" => "781.182.122-20",
        "numero_cracha" => "000047150"
    ],
    [
        "cpf" => "782.133.633-53",
        "numero_cracha" => "77205"
    ],
    [
        "cpf" => "782.244.623-15",
        "numero_cracha" => "77436"
    ],
    [
        "cpf" => "785.184.583-15",
        "numero_cracha" => "6248"
    ],
    [
        "cpf" => "786.852.813-34",
        "numero_cracha" => "77412"
    ],
    [
        "cpf" => "787.390.123-87",
        "numero_cracha" => "76503"
    ],
    [
        "cpf" => "787.479.372-20",
        "numero_cracha" => "10131"
    ],
    [
        "cpf" => "787.948.703-49",
        "numero_cracha" => "77540"
    ],
    [
        "cpf" => "791.799.753-72",
        "numero_cracha" => "87331"
    ],
    [
        "cpf" => "791.947.963-00",
        "numero_cracha" => "48111"
    ],
    [
        "cpf" => "795.612.174-49",
        "numero_cracha" => "88210"
    ],
    [
        "cpf" => "796.797.732-72",
        "numero_cracha" => "42834"
    ],
    [
        "cpf" => "798.341.322-04",
        "numero_cracha" => "000007738"
    ],
    [
        "cpf" => "799.205.943-34",
        "numero_cracha" => "86868"
    ],
    [
        "cpf" => "801.534.963-04",
        "numero_cracha" => "85380"
    ],
    [
        "cpf" => "802.236.723-00",
        "numero_cracha" => "86888"
    ],
    [
        "cpf" => "805.746.703-78",
        "numero_cracha" => "78875"
    ],
    [
        "cpf" => "807.071.653-34",
        "numero_cracha" => "77418"
    ],
    [
        "cpf" => "807.348.472-20",
        "numero_cracha" => "000004713"
    ],
    [
        "cpf" => "809.230.462-91",
        "numero_cracha" => "42504"
    ],
    [
        "cpf" => "810.508.953-04",
        "numero_cracha" => "85606"
    ],
    [
        "cpf" => "811.335.533-20",
        "numero_cracha" => "16406"
    ],
    [
        "cpf" => "813.443.252-20",
        "numero_cracha" => "000005777"
    ],
    [
        "cpf" => "815.469.393-34",
        "numero_cracha" => "86939"
    ],
    [
        "cpf" => "816.815.423-15",
        "numero_cracha" => "86132"
    ],
    [
        "cpf" => "817.106.313-68",
        "numero_cracha" => "82251"
    ],
    [
        "cpf" => "817.413.913-34",
        "numero_cracha" => "77182"
    ],
    [
        "cpf" => "819.042.073-91",
        "numero_cracha" => "88199"
    ],
    [
        "cpf" => "819.456.553-72",
        "numero_cracha" => "86261"
    ],
    [
        "cpf" => "819.512.053-91",
        "numero_cracha" => "86398"
    ],
    [
        "cpf" => "819.514.343-15",
        "numero_cracha" => "81686"
    ],
    [
        "cpf" => "820.150.152-72",
        "numero_cracha" => "000025618"
    ],
    [
        "cpf" => "820.808.443-34",
        "numero_cracha" => "47410"
    ],
    [
        "cpf" => "820.828.473-49",
        "numero_cracha" => "86960"
    ],
    [
        "cpf" => "820.842.893-00",
        "numero_cracha" => "77070"
    ],
    [
        "cpf" => "821.024.303-91",
        "numero_cracha" => "65338"
    ],
    [
        "cpf" => "821.399.703-49",
        "numero_cracha" => "46831"
    ],
    [
        "cpf" => "823.595.633-00",
        "numero_cracha" => "85664"
    ],
    [
        "cpf" => "823.852.203-00",
        "numero_cracha" => "77427"
    ],
    [
        "cpf" => "825.374.883-34",
        "numero_cracha" => "53828"
    ],
    [
        "cpf" => "827.201.503-04",
        "numero_cracha" => "51381"
    ],
    [
        "cpf" => "827.239.312-34",
        "numero_cracha" => "72666"
    ],
    [
        "cpf" => "827.790.312-04",
        "numero_cracha" => "15669"
    ],
    [
        "cpf" => "828.745.302-04",
        "numero_cracha" => "000004629"
    ],
    [
        "cpf" => "829.957.102-20",
        "numero_cracha" => "000039877"
    ],
    [
        "cpf" => "830.700.443-87",
        "numero_cracha" => "28221"
    ],
    [
        "cpf" => "836.670.243-04",
        "numero_cracha" => "14940"
    ],
    [
        "cpf" => "838.197.622-72",
        "numero_cracha" => "000041615"
    ],
    [
        "cpf" => "838.252.583-00",
        "numero_cracha" => "53671"
    ],
    [
        "cpf" => "840.708.973-72",
        "numero_cracha" => "87115"
    ],
    [
        "cpf" => "840.719.823-49",
        "numero_cracha" => "69652"
    ],
    [
        "cpf" => "841.020.043-00",
        "numero_cracha" => "87101"
    ],
    [
        "cpf" => "841.239.583-20",
        "numero_cracha" => "76511"
    ],
    [
        "cpf" => "842.316.023-87",
        "numero_cracha" => "86946"
    ],
    [
        "cpf" => "842.771.062-34",
        "numero_cracha" => "000044496"
    ],
    [
        "cpf" => "842.932.253-15",
        "numero_cracha" => "27143"
    ],
    [
        "cpf" => "844.018.852-87",
        "numero_cracha" => "4553"
    ],
    [
        "cpf" => "844.166.453-68",
        "numero_cracha" => "47253"
    ],
    [
        "cpf" => "845.248.983-87",
        "numero_cracha" => "86149"
    ],
    [
        "cpf" => "846.310.313-87",
        "numero_cracha" => "87555"
    ],
    [
        "cpf" => "847.816.373-53",
        "numero_cracha" => "77161"
    ],
    [
        "cpf" => "850.311.353-87",
        "numero_cracha" => "76829"
    ],
    [
        "cpf" => "850.942.853-00",
        "numero_cracha" => "87786"
    ],
    [
        "cpf" => "853.255.543-87",
        "numero_cracha" => "86787"
    ],
    [
        "cpf" => "856.539.003-97",
        "numero_cracha" => "85666"
    ],
    [
        "cpf" => "868.191.512-68",
        "numero_cracha" => "28443'"
    ],
    [
        "cpf" => "872.977.873-53",
        "numero_cracha" => "N/A"
    ],
    [
        "cpf" => "874.568.263-15",
        "numero_cracha" => "46859"
    ],
    [
        "cpf" => "876.347.913-34",
        "numero_cracha" => "27805"
    ],
    [
        "cpf" => "877.062.022-91",
        "numero_cracha" => "000010671"
    ],
    [
        "cpf" => "882.559.132-20",
        "numero_cracha" => "27739"
    ],
    [
        "cpf" => "884.134.543-87",
        "numero_cracha" => "21902"
    ],
    [
        "cpf" => "885.355.922-53",
        "numero_cracha" => "000051000"
    ],
    [
        "cpf" => "885.826.045-72",
        "numero_cracha" => "65791"
    ],
    [
        "cpf" => "888.751.722-34",
        "numero_cracha" => "37945"
    ],
    [
        "cpf" => "889.768.902-72",
        "numero_cracha" => "46248"
    ],
    [
        "cpf" => "893.319.903-91",
        "numero_cracha" => "86940"
    ],
    [
        "cpf" => "894.765.143-53",
        "numero_cracha" => "77337"
    ],
    [
        "cpf" => "895.012.173-53",
        "numero_cracha" => "85438"
    ],
    [
        "cpf" => "896.040.322-91",
        "numero_cracha" => "87574"
    ],
    [
        "cpf" => "897.881.922-20",
        "numero_cracha" => "000028680"
    ],
    [
        "cpf" => "900.092.582-72",
        "numero_cracha" => "27170"
    ],
    [
        "cpf" => "904.873.703-68",
        "numero_cracha" => "71315"
    ],
    [
        "cpf" => "909.705.782-53",
        "numero_cracha" => "47974"
    ],
    [
        "cpf" => "910.169.833-87",
        "numero_cracha" => "77367"
    ],
    [
        "cpf" => "910.741.183-91",
        "numero_cracha" => "50980"
    ],
    [
        "cpf" => "912.199.572-91",
        "numero_cracha" => "72656"
    ],
    [
        "cpf" => "913.888.132-20",
        "numero_cracha" => "000020248"
    ],
    [
        "cpf" => "914.288.822-00",
        "numero_cracha" => "000019797"
    ],
    [
        "cpf" => "915.117.633-53",
        "numero_cracha" => "28509"
    ],
    [
        "cpf" => "915.527.293-20",
        "numero_cracha" => "67535"
    ],
    [
        "cpf" => "917.225.512-91",
        "numero_cracha" => "000041407"
    ],
    [
        "cpf" => "918.263.223-53",
        "numero_cracha" => "87357"
    ],
    [
        "cpf" => "919.589.892-15",
        "numero_cracha" => "000036527"
    ],
    [
        "cpf" => "920.449.483-20",
        "numero_cracha" => "46667"
    ],
    [
        "cpf" => "923.003.243-34",
        "numero_cracha" => "59452"
    ],
    [
        "cpf" => "923.425.582-87",
        "numero_cracha" => "25641"
    ],
    [
        "cpf" => "931.009.493-15",
        "numero_cracha" => "52120"
    ],
    [
        "cpf" => "932.237.732-15",
        "numero_cracha" => "000036957"
    ],
    [
        "cpf" => "932.795.903-53",
        "numero_cracha" => "28216"
    ],
    [
        "cpf" => "934.659.552-34",
        "numero_cracha" => "23384"
    ],
    [
        "cpf" => "935.477.813-53",
        "numero_cracha" => "48858"
    ],
    [
        "cpf" => "937.604.423-15",
        "numero_cracha" => "86257"
    ],
    [
        "cpf" => "941.524.973-00",
        "numero_cracha" => "87262"
    ],
    [
        "cpf" => "942.013.403-25",
        "numero_cracha" => "46871"
    ],
    [
        "cpf" => "946.389.423-34",
        "numero_cracha" => "86944"
    ],
    [
        "cpf" => "948.997.113-87",
        "numero_cracha" => "77164"
    ],
    [
        "cpf" => "949.740.713-00",
        "numero_cracha" => "28184"
    ],
    [
        "cpf" => "950.267.623-87",
        "numero_cracha" => "74445"
    ],
    [
        "cpf" => "951.588.983-91",
        "numero_cracha" => "86265"
    ],
    [
        "cpf" => "951.628.362-49",
        "numero_cracha" => "50785"
    ],
    [
        "cpf" => "954.327.013-91",
        "numero_cracha" => "53630"
    ],
    [
        "cpf" => "955.401.482-15",
        "numero_cracha" => "000040202"
    ],
    [
        "cpf" => "958.319.243-00",
        "numero_cracha" => "85845"
    ],
    [
        "cpf" => "958.829.362-68",
        "numero_cracha" => "000026276"
    ],
    [
        "cpf" => "958.852.183-15",
        "numero_cracha" => "39238"
    ],
    [
        "cpf" => "960.546.203-63",
        "numero_cracha" => "48123"
    ],
    [
        "cpf" => "960.646.923-91",
        "numero_cracha" => "83809"
    ],
    [
        "cpf" => "960.782.512-87",
        "numero_cracha" => "67530"
    ],
    [
        "cpf" => "961.088.433-49",
        "numero_cracha" => "87321"
    ],
    [
        "cpf" => "961.167.063-04",
        "numero_cracha" => "86412"
    ],
    [
        "cpf" => "961.294.503-97",
        "numero_cracha" => "86863"
    ],
    [
        "cpf" => "968.225.623-20",
        "numero_cracha" => "85674"
    ],
    [
        "cpf" => "969.396.563-91",
        "numero_cracha" => "76601"
    ],
    [
        "cpf" => "970.027.392-04",
        "numero_cracha" => "36062"
    ],
    [
        "cpf" => "970.646.702-59",
        "numero_cracha" => "05947"
    ],
    [
        "cpf" => "972.927.372-34",
        "numero_cracha" => "3418"
    ],
    [
        "cpf" => "973.111.203-06",
        "numero_cracha" => "6804"
    ],
    [
        "cpf" => "975.518.723-53",
        "numero_cracha" => "77208"
    ],
    [
        "cpf" => "975.529.923-87",
        "numero_cracha" => "86405"
    ],
    [
        "cpf" => "977.692.822-68",
        "numero_cracha" => "41308"
    ],
    [
        "cpf" => "981.533.073-04",
        "numero_cracha" => "86273"
    ],
    [
        "cpf" => "983.155.012-91",
        "numero_cracha" => "000051762"
    ],
    [
        "cpf" => "983.866.383-20",
        "numero_cracha" => "77162"
    ],
    [
        "cpf" => "984.036.802-87",
        "numero_cracha" => "000041854"
    ],
    [
        "cpf" => "985.246.072-20",
        "numero_cracha" => "76297"
    ],
    [
        "cpf" => "988.242.523-20",
        "numero_cracha" => "78710"
    ],
    [
        "cpf" => "989.140.063-87",
        "numero_cracha" => "53693"
    ],
    [
        "cpf" => "990.456.643-72",
        "numero_cracha" => "86607"
    ],
    [
        "cpf" => "995.172.722-00",
        "numero_cracha" => "50956"
    ],
    [
        "cpf" => "998.649.593-87",
        "numero_cracha" => "86421"
    ],
    [
        "cpf" => "999.589.703-25",
        "numero_cracha" => "87113"
    ]
]);
$dados = $import->dados->map(function ($line) use($jayParsedAry) {
    return [
        "curriculo" => [
            'cpf' => Sistema::mascaraCpf($line['cpf']),
            "nome" => (string)$line['nome'],
            "naturalidade" => (string)$line['naturalidade'],
            "email" => (string)mb_strtolower(trim($line['email'])) ?? Sistema::EMAILPADRAO,
            "cnh" => (string)$line['cnh'],
            "cnh_vencimento" => $line['cnh_vencimento'] ? Date::excelToDateTimeObject($line['cnh_vencimento'])->format('d/m/Y') : null,
            "estado_civil" => (string)$line['estado_civil'],
            "rg" => (string)preg_replace("/[^0-9]/", "", $line['rg']),
            "rg_data_emissao" => $line['rg_emissao'] ? Date::excelToDateTimeObject($line['rg_emissao'])->format('d/m/Y') : null,
            "nascimento" => $line['nascimento'] ? Date::excelToDateTimeObject($line['nascimento'])->format('d/m/Y') : null,
            "sexo" => mb_strtoupper($line['sexo']) == "M" ? "Masculino" : "Feminino",
            "filiacao_pai" => (string)$line['pai'],
            "filiacao_mae" => (string)$line['mae'],
            "pcd" => mb_strtolower(trim($line['pcd'])) == "sim",
            "cid" => (string)$line['cid'],
            "vaga_pretendida" => intval($line['cod_vaga']),
            "telefone" => [
                "whatsapp" => mb_strtolower(trim($line['whatsapp'])) == "sim" ? "whatsapp" : "celular",
                "numero" => Sistema::mascaraTelefone($line['telefone_numero']),
            ],
            "endereco" => [
                "cep" => Sistema::mascaraCep($line['cep']),
                "logradouro" => (string)$line['endereco'],
                "numero" => (string)$line['numero'],
                "complemento" => (string)$line['complemento'],
                "bairro" => (string)$line['bairro'],
                "municipio" => (string)$line['municipio'],
                "uf" => (string)$line['uf'],
            ],
        ],
        "admissao" => [
            "area_etiqueta_id" => $line['cod_area'],
            "centro_custo_id" => $line['centro_custo'],
            "filial" => $line['filial'] == 's',
            "centro_custo_filial_id" => $line['centro_custo_filial_id'] ?? null,
            "data_entrega_area" => $line['data_entrega_area'] ? Date::excelToDateTimeObject($line['data_entrega_area'])->format('d/m/Y') : null,
            "salario" => number_format(floatval($line['salario']), 2, ',', '.'),
            "pis" => (string)$line['pis'],
            "ctps_numero" => (string)$line['ctps_numero'],
            "ctps_serie" => (string)$line['ctps_serie'],
            "ctps_data_emissao" => $line['ctps_data_emissao'] ? Date::excelToDateTimeObject($line['ctps_data_emissao'])->format('d/m/Y') : null,
            "titulo_eleitor_numero" => (string)$line['titulo_eleitor_numero'],
            "titulo_eleitor_sessao" => (string)$line['titulo_eleitor_sessao'],
            "titulo_eleitor_zona" => (string)$line['titulo_eleitor_zona'],
            "tipo_admissao" => mb_strtoupper($line['tipo_admissao']),
            "data_admissao" => Date::excelToDateTimeObject(trim((string)$line['data_admissao']))->format('d/m/Y'),
            "data_aso" => Date::excelToDateTimeObject(trim((string)$line['data_aso']))->format('d/m/Y'),
            "admissao_encerramento" => $line['admissao_encerramento'] ? Date::excelToDateTimeObject($line['admissao_encerramento'])->format('d/m/Y') : null,
            "prazo_experiencia" => ucfirst(trim($line['prazo_experiencia'])),
            "encaminhado_documento" => mb_strtolower(trim($line['encaminhado_documento'])) == "sim",
            "encaminhado_documento_data" => $line['encaminhado_documento_data'] ? Date::excelToDateTimeObject($line['encaminhado_documento_data'])->format('d/m/Y') : null,
            "encaminhado_exame" => mb_strtolower(trim($line['encaminhado_exame'])) == "sim",
            "encaminhado_exame_data" => $line['encaminhado_exame_data'] ? Date::excelToDateTimeObject($line['encaminhado_exame_data'])->format('d/m/Y') : null,
            "encaminhado_treinamento" => mb_strtolower(trim($line['encaminhado_treinamento'])) == "sim",
            "encaminhado_treinamento_data" => $line['encaminhado_treinamento_data'] ? Date::excelToDateTimeObject($line['encaminhado_treinamento_data'])->format('d/m/Y') : null,
//            "numero_cracha" => (string)$line['numero_cracha'],
            "numero_cracha" => $jayParsedAry->filter(function ($item) use ($line) {
                return $item['cpf'] == $line['cpf'];
            })->first()['numero_cracha'] ?? (string)$line['numero_cracha'],
            "matricula" => (string)$line['matricula'],
            "banco" => [
                "nome" => (string)$line['banco'],
                "agencia" => (string)$line['agencia'],
                "conta" => (string)$line['conta'],
                "pix" => mb_strtolower(trim($line['pix'])) == "sim",
                "pix_tipo_chave" => $line['pix_tipo_chave'],
                "pix_chave" => (string)$line['pix_chave']
            ]
        ]
    ];
})->filter(function ($item) {
    return $item['curriculo']['cpf'] != '';
})->unique('curriculo.cpf');

if ($dados->count() == 0) {
    return response()->json([
        'msg' => 'Nenhum registro encontrado',
        "status" => 'error'
    ], 400);
}

$dados = $dados->toArray();
//return var_dump($dados);

/*
$dadosValidados = \Validator::make($dados, [
    '*.curriculo.cpf' => ['required',
        'min:14',
        'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
        new CpfValidoEmpresaRules($empresa_id),
        new VerificaCpfEmpresaRules($empresa_id, true)
    ],
    '*.curriculo.nome' => 'required|max:255',
//    '*.curriculo.email' => 'email:rfc,dns',
//    '*.curriculo.email' => 'email:rfc,dns',
    '*.curriculo.nascimento' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
    '*.curriculo.rg' => 'nullable|max:200',
    '*.curriculo.rg_data_emissao' => 'nullable|max:10|regex:/^\d{2}\/\d{2}\/\d{4}$/',
    '*.curriculo.filiacao_pai' => 'max:255',
    '*.curriculo.filiacao_mae' => 'required|max:255',
    '*.curriculo.pcd' => 'required|boolean',
    '*.curriculo.cid' => 'required_if:*.curriculo.pcd,true',
    '*.curriculo.vaga_pretendida' => ['required', new VagaAbertaEmpresaRules($empresa_id)],
    '*.curriculo.endereco.cep' => 'required|min:9',
    '*.curriculo.endereco.logradouro' => 'required|max:255',
    '*.curriculo.endereco.numero' => 'nullable|max:10',
    '*.curriculo.endereco.complemento' => 'nullable|max:255',
    '*.curriculo.endereco.bairro' => 'required|max:255',
    '*.curriculo.endereco.municipio' => 'required|max:255',
    '*.curriculo.endereco.uf' => 'required|max:2|regex:/^[A-Z]{2}$/',
    '*.curriculo.telefone.whatsapp' => 'required|in:' . implode(",", TelefoneCurriculo::TIPOS),
    '*.curriculo.telefone.numero' => 'required|max:16',
    '*.admissao.area_etiqueta_id' => ['required', new AreaEmpresaRules($empresa_id)],
    '*.admissao.data_entrega_area' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
    '*.admissao.salario' => 'max:100',
    '*.admissao.pis' => 'nullable|max:200',
    '*.admissao.ctps_numero' => 'nullable|max:200',
    '*.admissao.ctps_serie' => 'nullable|max:200',
    '*.admissao.ctps_data_emissao' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
    '*.admissao.titulo_eleitor_numero' => 'nullable|max:200',
    '*.admissao.titulo_eleitor_sessao' => 'nullable|max:200',
    '*.admissao.titulo_eleitor_zona' => 'nullable|max:200',
    '*.admissao.data_aso' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
    '*.admissao.data_admissao' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
    '*.admissao.tipo_admissao' => "required|in:" . implode(",", Admissao::TODOS_TIPOS_ADMISSAO),
    '*.admissao.admissao_encerramento' => [
        function ($attribute, $value, $fail) use ($dados) {
            $i = (int)explode('.', $attribute)[0];

            if (in_array($dados[$i]['admissao']['tipo_admissao'], [Admissao::TIPO_ADMISSAO_INTERMITENTE, Admissao::TIPO_ADMISSAO_DETERMINADO, Admissao::TIPO_ADMISSAO_TEMPORARIO])
                && is_null($value)
                && preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $value) == 0
            ) {
                $fail("O {$attribute} deve ser preenchido com formato da data dd/mm/aaaa");
            }
        }],
    '*.admissao.prazo_experiencia' => [function ($attribute, $value, $fail) use ($dados) {
        $i = (int)explode('.', $attribute)[0];
        if ($dados[$i]['admissao']['tipo_admissao'] == Admissao::TIPO_ADMISSAO_FIXO && !in_array($value, Admissao::TODOS_PRAZOS)) {
            $fail("A linha {$attribute} só pode ser um dos tipos de prazo: " . implode(',', Admissao::TODOS_PRAZOS));
        }
    }],
    '*.admissao.banco.nome' => 'nullable|max:200',
    '*.admissao.banco.agencia' => 'nullable|max:200',
    '*.admissao.banco.conta' => 'nullable|max:200',
    '*.admissao.banco.pix' => 'boolean',
    '*.admissao.banco.pix_tipo_chave' => 'required_if:*.admissao.banco.pix,true|max:200',
    '*.admissao.banco.pix_chave' => 'required_if:*.admissao.banco.pix,true|max:200',
]);

if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
    print_r([
        'msg' => 'Erro ao fazer importação',
        'erros' => $dadosValidados->errors(),
    ]);
    die();

}
*/


try {
    $count = 0;
    DB::beginTransaction();
    foreach ($dados as $item) {
        Auth::loginUsingId($user_id);

        $usuario = User::where('empresa_id', $empresa_id)->whereHas('Curriculo', function ($q) use ($item) {
            $q->where('cpf', $item['curriculo']['cpf']);
        });

        $dadosUser = [
            'nome' => $item['curriculo']['nome'],
            'login' => $item['curriculo']['email'],
            'password' => Sistema::SenhaCpf($item['curriculo']['cpf']),
            'tipo' => User::FUNCIONARIO,
            'ativo' => true,
            'temp' => false,
            'termos' => false,
            'empresa_id' => $empresa_id
        ];


        if ($usuario->count() == 0) {
//            \Log::info("Iniciando criação do Colaborador - " . $item['curriculo']['nome']);
            echo "Criando o Colaborador - " . $item['curriculo']['nome'] . "  \n";
            $usuario = User::create($dadosUser);
        } else {
            echo "Atualizando do Colaborador - " . $item['curriculo']['nome'] . "  \n";
            $usuario = $usuario->first();
            $usuario->update($dadosUser);
        }

        //Cria ou atualiza os dados bancarios
        $dadosConta = [
            'banco' => $item['admissao']['banco']['nome'],
            'agencia' => $item['admissao']['banco']['agencia'],
            'conta' => $item['admissao']['banco']['conta'],
            'pix' => $item['admissao']['banco']['pix'],
            'tipochavepix' => $item['admissao']['banco']['pix_tipo_chave'],
            'chavepix' => $item['admissao']['banco']['pix_chave'],
        ];

        $usuario->BancoConta ? $usuario->BancoConta->update($dadosConta) : $usuario->BancoConta()->create($dadosConta);

        //Cria ou atualiza o Curriculo
        $dadosCurriculo = [
            'id' => $usuario->id,
            'cpf' => $item['curriculo']['cpf'],
            'nome' => $item['curriculo']['nome'],
            'estado_civil' => $item['curriculo']['estado_civil'],
            'cnh' => $item['curriculo']['cnh'],
            'cnh_vencimento' => $item['curriculo']['cnh_vencimento'],
            'email' => $item['curriculo']['email'],
            'nascimento' => $item['curriculo']['nascimento'],
            'naturalidade' => $item['curriculo']['naturalidade'],
            'logradouro' => $item['curriculo']['endereco']['logradouro'],
            'end_numero' => $item['curriculo']['endereco']['numero'],
            'complemento' => $item['curriculo']['endereco']['complemento'],
            'bairro' => $item['curriculo']['endereco']['bairro'],
            'municipio' => $item['curriculo']['endereco']['municipio'],
            'uf' => $item['curriculo']['endereco']['uf'],
            'cep' => $item['curriculo']['endereco']['cep'],
            'uf_vaga' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Municipio->uf,
            'municipio_id' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Municipio->id,
            'rg' => $item['curriculo']['rg'],
            'rg_data_emissao' => $item['curriculo']['rg_data_emissao'],
            'filiacao_pai' => $item['curriculo']['filiacao_pai'],
            'filiacao_mae' => $item['curriculo']['filiacao_mae'],
            'sexo' => $item['curriculo']['sexo'],
            'pcd' => $item['curriculo']['pcd'],
            'cid' => $item['curriculo']['cid'],
            'vaga_pretendida' => $item['curriculo']['vaga_pretendida']
        ];

        $curriculo = Curriculo::find($usuario->id);

        if (is_null($curriculo)) {
            $curriculo = Curriculo::create($dadosCurriculo);
        } else {
            $curriculo->update($dadosCurriculo);
        }

        //Cria ou atualiza o Telefone
        $dadosTel = [
            'curriculo_id' => $curriculo->id,
            'tipo' => $item['curriculo']['telefone']['whatsapp'],
            'pais' => "55",
            'numero' => $item['curriculo']['telefone']['numero'],
            'principal' => true,
        ];

        $telefone_id = $curriculo->Telefones()->updateOrCreate($dadosTel)->id;

        //Cria ou atualiza o Feedback
        $curriculo->Feedback()->updateOrCreate([
            'curriculo_id' => $curriculo->id,
            'selecionado' => 'sim',
            'vaga_id' => $item['curriculo']['vaga_pretendida'],
            'cliente_id' => $empresa_id,
            'empresa_id' => $empresa_id,
            'interesse' => true,
            'contato_realizado' => true,
            'telefone_id' => $telefone_id,
            'vagas_abertas_id' => $item['curriculo']['vaga_pretendida']
        ]);

        //Criações de entrevistas
        $curriculo->Feedback->parecerRh()->updateOrCreate(['nota' => 9]);
        $curriculo->Feedback->parecerRota()->updateOrCreate([]);
        $curriculo->Feedback->parecerTecnica()->updateOrCreate([]);
        $curriculo->Feedback->parecerTeste()->updateOrCreate([]);
        $curriculo->Feedback->individualRh()->updateOrCreate([]);
        $curriculo->Feedback->gestorRh()->updateOrCreate([]);
        $curriculo->Feedback->entrevistaRh()->updateOrCreate([]);

        //Criações de resultado integrado
        $curriculo->Feedback->ResultadoIntegrado()->updateOrCreate([
            'responsavel_envio' => 'importacao',
            'documentos_entregue' => false,
            'encaminhado_exame' => (bool)$item['admissao']['encaminhado_exame'],
            'encaminhado_exame_data' => $item['admissao']['encaminhado_exame_data'],
            'encaminhado_treinamento' => (bool)$item['admissao']['encaminhado_treinamento'],
            'encaminhado_treinamento_data' => $item['admissao']['encaminhado_treinamento_data'],
        ]);

        //Criações de admissao
        $curriculo->Feedback->Admissao()->updateOrCreate([
            'centro_custo_id' => $item['admissao']['centro_custo_id'],
            'area_etiqueta_id' => $item['admissao']['area_etiqueta_id'],
            'data_entrega_area' => $item['admissao']['data_entrega_area'],
            'data_admissao' => $item['admissao']['data_admissao'],
            'cargo' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Vaga->nome,
            'funcao' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Vaga->nome,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'salario' => $item['admissao']['salario'],
            'pis' => $item['admissao']['pis'],
            'tipo_admissao' => $item['admissao']['tipo_admissao'],
            'prazo_experiencia' => $item['admissao']['prazo_experiencia'],
            'data_encerramento' => $item['admissao']['admissao_encerramento'],
            'usuario_id' => auth()->user()->id,
        ]);

        Admissao::tipoAdmissaoAvalNoventaCriarAtualizar($curriculo->Feedback->id, $item['admissao']['tipo_admissao'], $item['admissao']['prazo_experiencia'], $item['admissao']['data_admissao'], $item['admissao']['admissao_encerramento']);
        AdmissaoAso::criarAtualizar($curriculo->Feedback->Admissao->id, $empresa_id, $item['admissao']['data_aso']);

        //DadosAdmissoes
        $curriculo->Feedback->Admissao->DadosAdmissoes()->updateOrCreate([
            'ctps_numero' => $item['admissao']['ctps_numero'],
            'ctps_serie' => $item['admissao']['ctps_serie'],
            'ctps_data_emissao' => $item['admissao']['ctps_data_emissao'],
            'titulo_eleitor_numero' => $item['admissao']['titulo_eleitor_numero'],
            'titulo_eleitor_sessao' => $item['admissao']['titulo_eleitor_sessao'],
            'titulo_eleitor_zona' => $item['admissao']['titulo_eleitor_zona'],
        ]);
        DB::commit();
    }

    $empresa = User::select(['nome'])->find($empresa_id);
    \Log::info('Importação realizada com sucesso da Empresa ' . $empresa->nome);
    (new ZapNotificacao())->enviar([
        'enviado_id' => $user_id,
        'telefone' => '5598999023762',
        'mensagem' => 'Importação realizada com sucesso da Empresa ' . $empresa->nome . ' - ' . $empresa_id
    ]);
    return response()->json(['msg' => 'Importação realizada com sucesso'], 201);
} catch (\Exception $e) {
    DB::rollback();
    \Log::error($e->getMessage() . ' - ' . $e->getLine());

    echo $e->getMessage() . ' - ' . $e->getLine() . "\n";
//    return response()->json(['error' => $e->getMessage() . ' - ' . $e->getLine()], 500);
}
