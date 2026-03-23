<?php
namespace App\Services;
use App\Models\Member;
use App\Models\MemberPoint;
use App\Models\Setting;
use App\Models\Transaction;

class PointService
{
    public function calculatePoints(float $total): float
    {
        $formula = (float) Setting::get('points_per_rupiah', 0.01);
        return floor($total * $formula);
    }

    public function earnPoints(Member $member, Transaction $transaction): ?MemberPoint
    {
        $points = $this->calculatePoints($transaction->total);
        if ($points <= 0) return null;

        $member->increment('points_balance', $points);

        return MemberPoint::create([
            'member_id' => $member->id,
            'transaction_id' => $transaction->id,
            'points' => $points,
            'type' => 'earn',
            'notes' => "Poin dari transaksi {$transaction->invoice_number}",
        ]);
    }

    public function redeemPoints(Member $member, float $points, string $notes = ''): MemberPoint
    {
        if ($member->points_balance < $points) {
            throw new \Exception('Saldo poin tidak mencukupi.');
        }

        $member->decrement('points_balance', $points);

        return MemberPoint::create([
            'member_id' => $member->id,
            'points' => $points,
            'type' => 'redeem',
            'notes' => $notes,
        ]);
    }
}
