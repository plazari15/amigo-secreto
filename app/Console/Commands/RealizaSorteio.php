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

            foreach ($Participantes as $participante) {
                if(isset($participante->email)){
                    if(!filter_var($participante->email, FILTER_VALIDATE_EMAIL)){
                       $this->ERROR('TEM EMAIL INVALIDO');
                        return false;
                    }
                }
            }

            $resultado = $this->executaSorteio($Participantes);

            $bar = $this->output->createProgressBar(count($resultado));


            foreach ($resultado as $user => $sorteado){
                $user = User::find($user);
                $sorteado = User::find($sorteado);

                Log::info('Sorteio realizado para '.$user->name);

                if($user->telefone){
                    Log::info('enviando resultado via SMS para'.$user->name." ({$user->telefone})");
                    $totalVoice = new \TotalVoice\Client(env('TOTAL_VOICE'));
                    $response = $totalVoice->sms->enviar($user->telefone, $this->getMensagem($user->name, $sorteado->name));
                    Log::debug($response->getContent());
                }elseif($user->email){
                    Log::info('enviando resultado via EMAIL para'.$user->name." ({$user->telefone})");
                    \Mail::raw($this->getMensagem($user->name, $sorteado->name), function ($email) use ($sorteado, $user){
                        $email->subject($user->name .' Saiu o resultado do seu amigo Secreto');
                        $email->from('postmaster@mail.matilha.design', 'Pedro Lazari');
                        $email->to($user->email);
                    });
                }else{
                    $this->info('Não será possível notificar o '.$user->id);
                    return false;
                }
                $bar->advance();
                sleep(5);
            }
            $bar->finish();
            return $this->info('Sorteio finalizado');
        }

        private function executaSorteio($Participantes){
            while (true){
                $this->comment('Embaralhando...');
                //Iniciando
                $countPart = count($Participantes);
                $count = 0;
                $valido = true;


                $Pessoas = $Participantes;
                $PessoasCollect = collect($Pessoas);
                $PessoasCollect = $PessoasCollect->shuffle();
                $PessoasCollect = $PessoasCollect->all();
                $Pessoas = $Pessoas->values();

                $resultado = [];
                $PessoasShuffled = $Pessoas->shuffle();

                foreach ($PessoasShuffled->all() as $i => $pessoa) {
                    $resultado[$pessoa->id] = $PessoasCollect[$i]->id;

                    if($pessoa->id == $PessoasCollect[$i]->id){
                        $count++;
                    }
                }



                if($count <= 0){
                    return $resultado;
                }else{
                    $this->error("{$count} Participantes inválidos...");
                }

                $this->comment('Nova tentativa...');
                sleep(5);
            }

    }

        protected function getMensagem($nomePessoa, $nomeAmigoSecreto)
        {
            $mensagem = 'Oi ' . $nomePessoa . ' o seu amigo secreto é: ';
            $mensagem .= $nomeAmigoSecreto . ' Sorteio realizado em: ' . Carbon::now()->format('d/m/Y H:i') . '. Não se esqueça: Guarde esta mensagem, pois o resultado não é armazenado.';

            return $mensagem;
        }



}
