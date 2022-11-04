<?php

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

$dados = [
    ['cpf' => '666.349.703-00', 'nascimento' => (new \MasterTag\DataHora('20/11/1979'))->dataInsert()],
    ['cpf' => '083.257.433-36', 'nascimento' => (new \MasterTag\DataHora('06/12/2002'))->dataInsert()],
    ['cpf' => '648.190.513-34', 'nascimento' => (new \MasterTag\DataHora('20/08/1980'))->dataInsert()],
    ['cpf' => '606.924.603-90', 'nascimento' => (new \MasterTag\DataHora('08/09/1998'))->dataInsert()],
    ['cpf' => '688.781.333-53', 'nascimento' => (new \MasterTag\DataHora('03/07/1974'))->dataInsert()],
    ['cpf' => '647.817.803-00', 'nascimento' => (new \MasterTag\DataHora('22/04/1977'))->dataInsert()],
    ['cpf' => '051.364.773-26', 'nascimento' => (new \MasterTag\DataHora('17/03/1997'))->dataInsert()],
    ['cpf' => '006.816.563-36', 'nascimento' => (new \MasterTag\DataHora('22/02/1980'))->dataInsert()],
    ['cpf' => '607.972.883-41', 'nascimento' => (new \MasterTag\DataHora('02/10/1995'))->dataInsert()],
    ['cpf' => '774.961.913-53', 'nascimento' => (new \MasterTag\DataHora('22/02/1978'))->dataInsert()],
    ['cpf' => '249.807.223-34', 'nascimento' => (new \MasterTag\DataHora('05/06/1963'))->dataInsert()],
    ['cpf' => '602.104.463-07', 'nascimento' => (new \MasterTag\DataHora('16/06/1988'))->dataInsert()],
    ['cpf' => '791.625.923-00', 'nascimento' => (new \MasterTag\DataHora('15/06/1978'))->dataInsert()],
    ['cpf' => '673.602.543-53', 'nascimento' => (new \MasterTag\DataHora('23/08/1974'))->dataInsert()],
    ['cpf' => '253.733.153-20', 'nascimento' => (new \MasterTag\DataHora('20/08/1964'))->dataInsert()],
    ['cpf' => '816.801.043-49', 'nascimento' => (new \MasterTag\DataHora('09/10/1978'))->dataInsert()],
    ['cpf' => '016.955.581-03', 'nascimento' => (new \MasterTag\DataHora('30/05/1985'))->dataInsert()],
    ['cpf' => '405.869.873-04', 'nascimento' => (new \MasterTag\DataHora('28/03/1969'))->dataInsert()],
    ['cpf' => '957.308.853-34', 'nascimento' => (new \MasterTag\DataHora('14/05/1978'))->dataInsert()],
    ['cpf' => '004.633.753-94', 'nascimento' => (new \MasterTag\DataHora('30/06/1983'))->dataInsert()],
    ['cpf' => '874.560.793-15', 'nascimento' => (new \MasterTag\DataHora('10/11/1980'))->dataInsert()],
    ['cpf' => '011.660.023-30', 'nascimento' => (new \MasterTag\DataHora('11/11/1986'))->dataInsert()],
    ['cpf' => '428.222.383-91', 'nascimento' => (new \MasterTag\DataHora('28/08/1972'))->dataInsert()],
    ['cpf' => '017.582.893-84', 'nascimento' => (new \MasterTag\DataHora('13/09/1986'))->dataInsert()],
    ['cpf' => '065.877.673-89', 'nascimento' => (new \MasterTag\DataHora('07/04/1996'))->dataInsert()],
    ['cpf' => '006.012.373-74', 'nascimento' => (new \MasterTag\DataHora('02/06/1999'))->dataInsert()],
    ['cpf' => '960.726.793-15', 'nascimento' => (new \MasterTag\DataHora('16/03/1979'))->dataInsert()],
    ['cpf' => '612.873.963-64', 'nascimento' => (new \MasterTag\DataHora('07/10/2000'))->dataInsert()],
    ['cpf' => '029.567.813-59', 'nascimento' => (new \MasterTag\DataHora('18/07/1988'))->dataInsert()],
    ['cpf' => '014.079.493-06', 'nascimento' => (new \MasterTag\DataHora('07/03/1986'))->dataInsert()],
    ['cpf' => '050.105.243-76', 'nascimento' => (new \MasterTag\DataHora('03/08/1990'))->dataInsert()],
    ['cpf' => '860.853.101-10', 'nascimento' => (new \MasterTag\DataHora('01/09/1980'))->dataInsert()],
    ['cpf' => '699.595.952-34', 'nascimento' => (new \MasterTag\DataHora('25/03/1981'))->dataInsert()],
    ['cpf' => '002.148.113-01', 'nascimento' => (new \MasterTag\DataHora('04/05/1983'))->dataInsert()],
    ['cpf' => '488.172.793-15', 'nascimento' => (new \MasterTag\DataHora('14/10/1973'))->dataInsert()],
    ['cpf' => '033.910.643-31', 'nascimento' => (new \MasterTag\DataHora('31/07/1985'))->dataInsert()],
    ['cpf' => '021.427.103-01', 'nascimento' => (new \MasterTag\DataHora('21/11/1984'))->dataInsert()],
    ['cpf' => '051.075.133-44', 'nascimento' => (new \MasterTag\DataHora('16/02/1989'))->dataInsert()],
    ['cpf' => '608.591.183-12', 'nascimento' => (new \MasterTag\DataHora('06/02/1991'))->dataInsert()],
    ['cpf' => '064.791.513-80', 'nascimento' => (new \MasterTag\DataHora('22/06/2001'))->dataInsert()],
    ['cpf' => '063.433.023-36', 'nascimento' => (new \MasterTag\DataHora('16/04/1994'))->dataInsert()],
    ['cpf' => '852.737.083-20', 'nascimento' => (new \MasterTag\DataHora('24/06/1980'))->dataInsert()],
    ['cpf' => '613.850.953-69', 'nascimento' => (new \MasterTag\DataHora('28/05/2001'))->dataInsert()],
    ['cpf' => '039.183.103-88', 'nascimento' => (new \MasterTag\DataHora('12/10/1990'))->dataInsert()],
    ['cpf' => '608.688.083-26', 'nascimento' => (new \MasterTag\DataHora('03/02/1996'))->dataInsert()],
    ['cpf' => '058.557.743-92', 'nascimento' => (new \MasterTag\DataHora('25/11/1993'))->dataInsert()],
    ['cpf' => '949.490.693-49', 'nascimento' => (new \MasterTag\DataHora('15/05/1980'))->dataInsert()],
    ['cpf' => '005.740.973-09', 'nascimento' => (new \MasterTag\DataHora('21/01/1984'))->dataInsert()],
    ['cpf' => '058.380.983-92', 'nascimento' => (new \MasterTag\DataHora('25/10/1991'))->dataInsert()],
    ['cpf' => '648.187.483-15', 'nascimento' => (new \MasterTag\DataHora('04/10/1979'))->dataInsert()],
    ['cpf' => '007.675.133-39', 'nascimento' => (new \MasterTag\DataHora('04/10/1984'))->dataInsert()],
    ['cpf' => '650.088.123-00', 'nascimento' => (new \MasterTag\DataHora('27/12/1974'))->dataInsert()],
    ['cpf' => '914.457.443-68', 'nascimento' => (new \MasterTag\DataHora('01/03/1982'))->dataInsert()],
    ['cpf' => '075.911.273-81', 'nascimento' => (new \MasterTag\DataHora('17/03/1999'))->dataInsert()],
    ['cpf' => '056.607.823-61', 'nascimento' => (new \MasterTag\DataHora('05/08/1994'))->dataInsert()],
    ['cpf' => '041.360.763-12', 'nascimento' => (new \MasterTag\DataHora('03/01/1992'))->dataInsert()],
    ['cpf' => '066.682.703-60', 'nascimento' => (new \MasterTag\DataHora('03/07/1995'))->dataInsert()],
    ['cpf' => '611.296.463-51', 'nascimento' => (new \MasterTag\DataHora('29/11/1997'))->dataInsert()],
    ['cpf' => '030.988.253-24', 'nascimento' => (new \MasterTag\DataHora('17/11/1987'))->dataInsert()],
    ['cpf' => '066.697.053-01', 'nascimento' => (new \MasterTag\DataHora('05/06/1995'))->dataInsert()],
    ['cpf' => '027.762.253-00', 'nascimento' => (new \MasterTag\DataHora('30/03/1985'))->dataInsert()],
    ['cpf' => '738.223.703-63', 'nascimento' => (new \MasterTag\DataHora('18/07/1976'))->dataInsert()],
    ['cpf' => '010.391.203-77', 'nascimento' => (new \MasterTag\DataHora('26/04/1982'))->dataInsert()],
    ['cpf' => '039.218.523-74', 'nascimento' => (new \MasterTag\DataHora('21/08/1988'))->dataInsert()],
    ['cpf' => '008.319.863-60', 'nascimento' => (new \MasterTag\DataHora('29/03/1984'))->dataInsert()],
    ['cpf' => '613.644.313-94', 'nascimento' => (new \MasterTag\DataHora('15/03/1997'))->dataInsert()],
    ['cpf' => '448.846.052-68', 'nascimento' => (new \MasterTag\DataHora('27/04/1973'))->dataInsert()],
    ['cpf' => '053.081.083-23', 'nascimento' => (new \MasterTag\DataHora('26/01/1992'))->dataInsert()],
    ['cpf' => '057.743.103-02', 'nascimento' => (new \MasterTag\DataHora('16/03/1992'))->dataInsert()],
    ['cpf' => '613.434.743-45', 'nascimento' => (new \MasterTag\DataHora('23/11/1999'))->dataInsert()],
    ['cpf' => '609.705.743-12', 'nascimento' => (new \MasterTag\DataHora('24/07/1993'))->dataInsert()],
    ['cpf' => '044.516.723-84', 'nascimento' => (new \MasterTag\DataHora('12/01/1978'))->dataInsert()],
    ['cpf' => '405.640.293-00', 'nascimento' => (new \MasterTag\DataHora('07/05/1970'))->dataInsert()],
    ['cpf' => '617.922.783-73', 'nascimento' => (new \MasterTag\DataHora('08/09/2001'))->dataInsert()],
    ['cpf' => '652.083.743-15', 'nascimento' => (new \MasterTag\DataHora('15/08/1981'))->dataInsert()],
    ['cpf' => '027.077.613-38', 'nascimento' => (new \MasterTag\DataHora('22/03/1987'))->dataInsert()],
    ['cpf' => '015.796.363-22', 'nascimento' => (new \MasterTag\DataHora('07/10/1986'))->dataInsert()],
    ['cpf' => '935.753.193-91', 'nascimento' => (new \MasterTag\DataHora('14/09/1979'))->dataInsert()],
    ['cpf' => '858.008.483-00', 'nascimento' => (new \MasterTag\DataHora('23/06/1978'))->dataInsert()],
    ['cpf' => '299.889.003-25', 'nascimento' => (new \MasterTag\DataHora('29/07/1973'))->dataInsert()],
    ['cpf' => '196.749.023-68', 'nascimento' => (new \MasterTag\DataHora('05/11/1958'))->dataInsert()],
    ['cpf' => '729.755.473-15', 'nascimento' => (new \MasterTag\DataHora('07/11/1976'))->dataInsert()],
    ['cpf' => '002.808.933-29', 'nascimento' => (new \MasterTag\DataHora('05/05/1984'))->dataInsert()],
    ['cpf' => '828.275.673-34', 'nascimento' => (new \MasterTag\DataHora('13/11/1978'))->dataInsert()],
    ['cpf' => '684.789.843-72', 'nascimento' => (new \MasterTag\DataHora('23/02/1974'))->dataInsert()],
    ['cpf' => '760.816.563-49', 'nascimento' => (new \MasterTag\DataHora('23/10/1972'))->dataInsert()],
    ['cpf' => '794.507.503-78', 'nascimento' => (new \MasterTag\DataHora('14/10/1973'))->dataInsert()],
    ['cpf' => '668.712.553-20', 'nascimento' => (new \MasterTag\DataHora('27/04/1983'))->dataInsert()],
    ['cpf' => '809.942.883-87', 'nascimento' => (new \MasterTag\DataHora('19/04/1979'))->dataInsert()],
    ['cpf' => '804.900.853-34', 'nascimento' => (new \MasterTag\DataHora('20/09/1977'))->dataInsert()],
    ['cpf' => '023.484.842-18', 'nascimento' => (new \MasterTag\DataHora('10/06/1997'))->dataInsert()],
    ['cpf' => '782.506.333-34', 'nascimento' => (new \MasterTag\DataHora('18/05/1977'))->dataInsert()],
    ['cpf' => '047.667.883-89', 'nascimento' => (new \MasterTag\DataHora('20/07/1993'))->dataInsert()],
    ['cpf' => '602.808.613-46', 'nascimento' => (new \MasterTag\DataHora('03/09/1990'))->dataInsert()],
    ['cpf' => '028.493.582-40', 'nascimento' => (new \MasterTag\DataHora('12/07/1997'))->dataInsert()],
    ['cpf' => '056.319.283-61', 'nascimento' => (new \MasterTag\DataHora('23/01/1992'))->dataInsert()],
    ['cpf' => '024.152.493-86', 'nascimento' => (new \MasterTag\DataHora('28/03/1984'))->dataInsert()],
    ['cpf' => '895.092.003-44', 'nascimento' => (new \MasterTag\DataHora('04/01/1977'))->dataInsert()],
    ['cpf' => '724.785.513-49', 'nascimento' => (new \MasterTag\DataHora('04/05/1977'))->dataInsert()],
    ['cpf' => '041.207.373-06', 'nascimento' => (new \MasterTag\DataHora('08/12/1989'))->dataInsert()],
    ['cpf' => '606.506.423-84', 'nascimento' => (new \MasterTag\DataHora('01/07/1970'))->dataInsert()],
    ['cpf' => '404.479.443-04', 'nascimento' => (new \MasterTag\DataHora('14/06/1970'))->dataInsert()],
    ['cpf' => '029.151.043-44', 'nascimento' => (new \MasterTag\DataHora('07/12/1988'))->dataInsert()],
    ['cpf' => '053.693.113-58', 'nascimento' => (new \MasterTag\DataHora('07/11/1993'))->dataInsert()],
    ['cpf' => '601.929.563-02', 'nascimento' => (new \MasterTag\DataHora('05/08/1988'))->dataInsert()],
    ['cpf' => '034.730.503-26', 'nascimento' => (new \MasterTag\DataHora('29/10/1984'))->dataInsert()],
    ['cpf' => '661.216.553-72', 'nascimento' => (new \MasterTag\DataHora('13/07/1981'))->dataInsert()],
    ['cpf' => '432.258.613-91', 'nascimento' => (new \MasterTag\DataHora('06/04/1972'))->dataInsert()],
    ['cpf' => '647.611.183-34', 'nascimento' => (new \MasterTag\DataHora('14/08/1980'))->dataInsert()],
    ['cpf' => '066.705.843-50', 'nascimento' => (new \MasterTag\DataHora('17/09/1993'))->dataInsert()],
    ['cpf' => '053.138.123-43', 'nascimento' => (new \MasterTag\DataHora('17/06/1991'))->dataInsert()],
    ['cpf' => '027.246.563-18', 'nascimento' => (new \MasterTag\DataHora('20/04/1988'))->dataInsert()],
    ['cpf' => '030.368.063-69', 'nascimento' => (new \MasterTag\DataHora('03/04/1986'))->dataInsert()],
    ['cpf' => '605.520.223-90', 'nascimento' => (new \MasterTag\DataHora('26/01/1991'))->dataInsert()],
    ['cpf' => '847.162.153-34', 'nascimento' => (new \MasterTag\DataHora('27/10/1976'))->dataInsert()],
    ['cpf' => '069.211.783-07', 'nascimento' => (new \MasterTag\DataHora('15/04/1995'))->dataInsert()],
    ['cpf' => '011.795.403-95', 'nascimento' => (new \MasterTag\DataHora('31/08/1983'))->dataInsert()],
    ['cpf' => '467.766.803-53', 'nascimento' => (new \MasterTag\DataHora('11/09/1971'))->dataInsert()],
    ['cpf' => '000.306.443-30', 'nascimento' => (new \MasterTag\DataHora('02/12/1982'))->dataInsert()],
    ['cpf' => '617.488.643-35', 'nascimento' => (new \MasterTag\DataHora('07/01/1997'))->dataInsert()],
    ['cpf' => '980.839.103-68', 'nascimento' => (new \MasterTag\DataHora('18/04/1983'))->dataInsert()],
    ['cpf' => '612.376.263-07', 'nascimento' => (new \MasterTag\DataHora('20/01/1999'))->dataInsert()],
    ['cpf' => '034.985.383-57', 'nascimento' => (new \MasterTag\DataHora('06/10/1988'))->dataInsert()],
    ['cpf' => '764.136.753-49', 'nascimento' => (new \MasterTag\DataHora('15/07/1976'))->dataInsert()],
    ['cpf' => '024.629.053-67', 'nascimento' => (new \MasterTag\DataHora('15/11/1987'))->dataInsert()],
    ['cpf' => '997.324.243-20', 'nascimento' => (new \MasterTag\DataHora('11/04/1982'))->dataInsert()],
    ['cpf' => '624.652.503-29', 'nascimento' => (new \MasterTag\DataHora('01/12/2003'))->dataInsert()],
    ['cpf' => '571.650.253-91', 'nascimento' => (new \MasterTag\DataHora('20/01/1973'))->dataInsert()],
    ['cpf' => '880.849.223-00', 'nascimento' => (new \MasterTag\DataHora('01/03/2024'))->dataInsert()],
    ['cpf' => '007.420.943-40', 'nascimento' => (new \MasterTag\DataHora('10/01/1986'))->dataInsert()],
    ['cpf' => '617.044.273-55', 'nascimento' => (new \MasterTag\DataHora('29/09/2002'))->dataInsert()],
    ['cpf' => '084.873.823-39', 'nascimento' => (new \MasterTag\DataHora('19/04/2002'))->dataInsert()],
    ['cpf' => '650.746.963-72', 'nascimento' => (new \MasterTag\DataHora('03/06/1979'))->dataInsert()],
    ['cpf' => '006.937.463-51', 'nascimento' => (new \MasterTag\DataHora('03/02/1983'))->dataInsert()],
    ['cpf' => '025.690.323-95', 'nascimento' => (new \MasterTag\DataHora('01/02/1984'))->dataInsert()],
    ['cpf' => '020.997.743-40', 'nascimento' => (new \MasterTag\DataHora('24/05/1987'))->dataInsert()],
    ['cpf' => '963.320.773-87', 'nascimento' => (new \MasterTag\DataHora('15/12/1982'))->dataInsert()],
    ['cpf' => '052.391.343-59', 'nascimento' => (new \MasterTag\DataHora('21/08/1988'))->dataInsert()],
    ['cpf' => '804.478.563-91', 'nascimento' => (new \MasterTag\DataHora('20/12/1974'))->dataInsert()],
    ['cpf' => '028.235.562-65', 'nascimento' => (new \MasterTag\DataHora('11/03/1994'))->dataInsert()],
];

foreach ($dados as $c){
    DB::table('curriculos')->where('cpf', $c['cpf'])->update(['nascimento' => $c['nascimento']]);
}

dd('Atualizado com sucesso!');
