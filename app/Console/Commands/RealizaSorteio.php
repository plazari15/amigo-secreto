<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

            $Participantes = User::orderBy('id', 'rand')->get();

            if($Participantes->count() < 3){
                $this->error('Precisamos de no minimo 3 participantes');
                exit;
            }

            $resultado = $this->executaSorteio($Participantes);



            dd($resultado);

            foreach ($resultado as $user => $sorteado){
                $user = User::find($user);
                $sorteado = User::find($sorteado);

                $this->alert('Sorteio realizado para '.$user->name);
                Log::info('Sorteio realizado para '.$user->name);

                if($user->telefone){
                    Log::info('enviando resultado via SMS para'.$user->name." ({$user->telefone})");
                    Log::info($this->getMensagem($user->name, $sorteado->name));
//                    $totalVoice = new \TotalVoice\Client(env('TOTAL_VOICE'));
//                    $response = $totalVoice->sms->enviar($user->telefone, $this->getMensagem($user->name, $sorteado->name));
                    //Log::debug($response->getContent());
                }else{
                    Log::info('enviando resultado via EMAIL para'.$user->name." ({$user->telefone})");
                    Log::info($this->getMensagem($user->name, $sorteado->name));
//                    \Mail::raw($this->getMensagem($user->name, $sorteado->name), function ($email) use ($sorteado, $user){
//                        $email->subject($user->name .' Saiu o resultado do seu amigo Secreto');
//                        $email->from('postmaster@mail.matilha.design', 'Pedro Lazari');
//                        $email->to($user->email);
//                    });
                }
                Log::info('------------------------------');
                sleep(1);
            }
            return $this->info('Sorteio finalizado');
        }

        private function executaSorteio($Participantes){
            while (true){
                $Pessoas = $Participantes;

                $PessoasCollect = collect($Pessoas);

                $PessoasCollect = $PessoasCollect->shuffle();
//            $Primeiro = $PessoasCollect->shift(); //Pega o primeiro\
//            $PessoasCollect->push($Primeiro); //Coloca ele no fim

                $PessoasCollect = $PessoasCollect->all();
                $Pessoas = $Pessoas->values();

                $resultado = [];
                $this->info('SHUFFLE');
                $PessoasShuffled = $Pessoas->shuffle();

                foreach ($PessoasShuffled->all() as $i => $pessoa) {
                    $resultado[$pessoa->id] = $PessoasCollect[$i]->id;
                }

                foreach ($resultado as $user => $sorteado){
                    if($user == $sorteado){
                        
                    }

                }

                return $resultado;
            }

    }

        protected function getMensagem($nomePessoa, $nomeAmigoSecreto)
        {
            $mensagem = 'Oi ' . $nomePessoa . ' o seu amigo secreto é: ';
            $mensagem .= $nomeAmigoSecreto . ' Sorteio realizado em: ' . Carbon::now()->format('d/m/Y H:i');

            return $mensagem;
        }



}
