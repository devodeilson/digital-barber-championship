<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\ChampionshipInvitation;
use App\Mail\ContentApprovalNotification;
use App\Mail\VoteNotification;
use App\Mail\PaymentConfirmation;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function sendChampionshipInvitation($user, $championship)
    {
        try {
            Mail::to($user->email)
                ->queue(new ChampionshipInvitation($user, $championship));
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send championship invitation email', [
                'user_id' => $user->id,
                'championship_id' => $championship->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function sendContentApprovalNotification($content)
    {
        try {
            Mail::to($content->user->email)
                ->queue(new ContentApprovalNotification($content));
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send content approval email', [
                'content_id' => $content->id,
                'user_id' => $content->user_id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function sendVoteNotification($vote)
    {
        try {
            Mail::to($vote->content->user->email)
                ->queue(new VoteNotification($vote));
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send vote notification email', [
                'vote_id' => $vote->id,
                'content_id' => $vote->content_id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function sendPaymentConfirmation($transaction)
    {
        try {
            Mail::to($transaction->user->email)
                ->queue(new PaymentConfirmation($transaction));
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
} 