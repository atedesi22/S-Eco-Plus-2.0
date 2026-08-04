<?php

use App\Models\Account;
use App\Models\InternalMessage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $comptables = User::role('comptable')->get();
    $commerciaux = User::role(['commercial', 'chef_commercial'])->get();

    foreach ($commerciaux as $agent) {
        $accountsCount = Account::where('created_by', $agent->id)->whereDate('created_at', now())->count();
        $totalCollected = Transaction::where('performed_by', $agent->id)->where('type', 'deposit')->whereDate('created_at', now())->sum('amount');

        $body = "Rapport journalier automatique du commercial : {$agent->name}\n"
              . "- Nouveaux comptes : {$accountsCount}\n"
              . "- Total collecté : " . number_format($totalCollected) . " XAF\n";

        foreach ($comptables as $comptable) {
            InternalMessage::create([
                'sender_id'    => $agent->id,
                'recipient_id' => $comptable->id,
                'subject'      => "Rapport Journalier - {$agent->name} - " . now()->format('d/m/Y'),
                'body'         => $body,
            ]);
        }
    }
})->dailyAt('23:59');
