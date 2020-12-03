<?php

namespace App\Utils;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class SendMail 
{
    //public $view = 'layouts.emails.default';
    public $view = '';

    public function send($emails, array $arrayVars, $subject, $attach = false)
    {
         Mail::to($this->view, $arrayVars, function($message) use ($emails, $subject, $attach)
        {
            $emailFrom = isset($arrayVars['emailFrom']) ? $arrayVars['emailFrom'] : env('MAIL_FROM_ADDRESS');
            $fromName = isset($arrayVars['fromName']) ? $arrayVars['fromName'] : env('MAIL_FROM_NAME');
            
            $message->from($emailFrom, $fromName)->to($emails)->subject($subject);    
            if ($attach) {
                $message->attach($attach);
            }
            $message->send();
        });
        return true;
    }
}
