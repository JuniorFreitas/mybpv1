<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mybp:exportarExcel {usuario} {local} {dados} {arquivo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta dados para excel a partir de um array recebe como argumento o array e o nome do arquivo';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \App\Models\Sistema::exportaExcelPython($this->argument('usuario'), $this->argument('local'), $this->argument('dados'), $this->argument('arquivo'));
    }
}
