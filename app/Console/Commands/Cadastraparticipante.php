<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class Cadastraparticipante extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amigo:cadastrar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cadastra o participante no Amigo Secreto  ';

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
        $nome = $this->ask('Nome do participante');

        $telefone = $email = null;
        $tipo = $this->choice('Como comunicar?', ['SMS',  'Email'], '0');
        if($tipo == 'SMS'){
            $telefone = $this->ask('Telefone do participante');
        }else{
            $email = $this->ask('Email do participante');
        }

        User::create([
           'name' => $nome,
           'email' => $email,
           'telefone' => $telefone,
            'presente' => null
        ]);
    }
}
