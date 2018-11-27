<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ChecaCadastros extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amigo:validar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Valida todos os participantes enviando uma mensagem para eles.';

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
        $mensagem = $this->ask('Mensagem');

       $users =  User::all();

        foreach ($users as $user) {
            Log::info('Enviando mensagem de verificação para '.$user->name);
            $totalVoice = new \TotalVoice\Client(env('TOTAL_VOICE'));
            $response = $totalVoice->sms->enviar($user->telefone, $mensagem);
            Log::debug(print_r($response->getContent(), true));
       }
    }
}
