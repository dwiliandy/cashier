<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;

class MidtransService
{
  public function __construct()
  {
    Config::$serverKey = config('services.midtrans.server_key');
    Config::$isProduction = config('services.midtrans.is_production');
    Config::$isSanitized = true;
    Config::$is3ds = true;
  }

  public function getSnapToken(array $transactionDetails, array $itemDetails, array $customerDetails): string
  {
    $params = [
      'transaction_details' => $transactionDetails,
      'item_details' => $itemDetails,
      'customer_details' => $customerDetails,
    ];

    try {
      return Snap::getSnapToken($params);
    } catch (\Exception $e) {
      \Log::error('Midtrans Error: ' . $e->getMessage());
      throw $e;
    }
  }

  public function getNotification()
  {
    return new Notification();
  }
}
