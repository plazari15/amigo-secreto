<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class RealizaSorteio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amigo:sortear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sorteia os Amigos Secretos';

    protected $participantes;

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
            $this->alert('Iniciando o sorteio do Amigo Secreto');

            $Participantes = User::all();

            if($Participantes->count() < 3){
                $this->error('Precisamos de no minimo 3 participantes');
                exit;
            }

            $Pessoas = $Participantes->shuffle();
            $PessoasCollect = collect($Pessoas);
            $Primeiro = $PessoasCollect->shift();
            $PessoasCollect->push($Primeiro);

            $PessoasCollect = $PessoasCollect->values();
            $Pessoas = $Pessoas->values();

            $resultado = [];
            foreach ($Pessoas as $i => $pessoa) {
                $resultado[$pessoa->id] = $PessoasCollect[$i]->id;
            }

            foreach ($resultado as $user => $sorteado){
                $user = User::find($user);
                $sorteado = User::find($sorteado);

                $this->alert('Sorteio realizado para '.$user->name);

                if($user->telefone){
                    $totalVoice = new \TotalVoice\Client(env('TOTAL_VOICE'));
                    $totalVoice->sms->enviar($user->telefone, $this->getMensagem($user->name, $sorteado->name));
                }else{
                    \Mail::raw($this->getMensagem($user->name, $sorteado->name), function ($email) use ($sorteado, $user){
                        $email->subject($user->name .' Saiu o resultado do seu amigo Secreto');
                        $email->from('postmaster@mail.matilha.design', 'Pedro Lazari');
                        $email->to($user->email);
                    });
                }
                sleep(2);
            }
            return $this->info('Sorteio finalizado');
        }

        protected function getMensagem($nomePessoa, $nomeAmigoSecreto)
        {
            $mensagem = 'Oi ' . $nomePessoa . ' o seu amigo secreto é: ';
            $mensagem .= $nomeAmigoSecreto . ' Não se esqueça: Guarde esta mensagem, pois o resultado não é armazenado.';

            return $mensagem;
        }



}
