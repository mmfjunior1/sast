<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Routines\EncerramentoLeilao;

class EncerrarLeilao extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leilao:encerrar {lote?} {cli?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encerra os leilões';

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
     * @return mixed
     */
    public function handle()
    {
        $lote = (int) $this->argument('lote');
        $cli = (int) $this->argument('cli');
        EncerramentoLeilao::encerramento($lote, $cli);
    }
}
