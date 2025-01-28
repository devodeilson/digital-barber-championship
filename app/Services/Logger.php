<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Logger
{
    protected $context = [];

    public function __construct()
    {
        $this->setDefaultContext();
    }

    protected function setDefaultContext()
    {
        $this->context = [
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String()
        ];
    }

    public function activity($action, $model = null, $details = [])
    {
        $data = array_merge($this->context, [
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'details' => $details
        ]);

        Log::channel('activity')->info($action, $data);
    }

    public function security($event, $details = [])
    {
        $data = array_merge($this->context, [
            'event' => $event,
            'details' => $details
        ]);

        Log::channel('security')->warning($event, $data);
    }

    public function payment($status, $transaction, $details = [])
    {
        $data = array_merge($this->context, [
            'status' => $status,
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'payment_method' => $transaction->payment_method,
            'details' => $details
        ]);

        Log::channel('payments')->info("Payment {$status}", $data);
    }

    public function error($exception, $context = [])
    {
        $data = array_merge($this->context, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context
        ]);

        Log::channel('errors')->error($exception->getMessage(), $data);
    }
} 