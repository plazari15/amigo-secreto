<?php
/**
 * Created by PhpStorm.
 * User: pedrolazari
 * Date: 03/12/2017
 * Time: 15:07
 */

namespace App\Observers;


use Illuminate\Support\Facades\Mail;
use TotalVoice\Client;

class UserObserver
{

    public function created($model){
        if($model->telefone){
            $totalvoice = new Client(env('TOTAL_VOICE'));

            $envio = $totalvoice->sms->enviar($model->telefone, $model->name.", você está inscrito no amigo secreto! Em breve receberá neste numero o resultado do sorteio.");

        }else{
            Mail::raw($model->name.", você está inscrito no amigo secreto! Em breve receberá neste Email o resultado do sorteio.", function ($email) use ($model){
               $email->subject($model->name .' Você está inscrito no Amigo Secreto :)');
               $email->from('postmaster@mail.matilha.design', 'Pedro Lazari');
               $email->to($model->email);
            });
        }

    }

}