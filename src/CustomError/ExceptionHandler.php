<?php

namespace CustomError;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Exception;

trait ExceptionHandler
{
    /**
     * @var bool
     */ 
    private $sendMails = [
        \App\Mail\SendException::class,
    ];
    
    /**
     * @var array
     */ 
    private $dontRender = [
        \InvalidArgumentException::class,
    ];
    
    /**
     * Check application in production
     */
    private function inProduction() : bool
    {
        return app()->environment() === 'production';
    }
    
    /**
     * Handler an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     */
    private function errorHandler(Request $request, Exception $exception)
    {
        if (!$this->inProduction()) {
            return false;
        }
        
        if (!$this->mapDontRender($exception)) {
            return false;
        }
        
        $this->handlerShotMail($request, $exception);
        
        return abort(500);
    }
    
    /**
     * Handler shot mail
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return void
     */
    private function handlerShotMail(Request $request, Exception $exception)
    {
        foreach ($this->getMails() as $mail) {
            Mail::send(new $mail($request, $exception));
        }
    }
    
    /**
     * Get mails
     * 
     * @return array
     */ 
    private function getMails() : array
    {
        return $this->sendMails;
    }
    
    /**
     * Mapping dont render
     * 
     * @param \Illuminate\Http\Request $request
     * @return bool
     */ 
    private function mapDontRender(Exception $exception)
    {
        foreach ($this->getDontRender() as $dontRender) {
            if ($exception instanceof $dontRender) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get dont renders
     * 
     * @return array
     */ 
    private function getDontRender() : array
    {
        return $this->dontRender;
    }
}