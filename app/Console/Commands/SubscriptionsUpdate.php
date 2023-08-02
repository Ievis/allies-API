<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\TinkoffRequestService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $not_handled_payments = Payment::where('is_callback_handled', '=', false)->get();

        if (!empty($not_handled_payments)) {
            foreach ($not_handled_payments as $payment) {
                $request_former = new TinkoffRequestService($payment->user_id);

                $response = $request_former
                    ->setOrderId($payment->id)
                    ->setCheckOrderRequestData()
                    ->formCheckOrderRequest()
                    ->makeRequest();

                if (empty($response->Success)) continue;

                $payment->status = collect($response->Payments)
                    ->where('PaymentId', $payment->payment_id)
                    ->first()
                    ?->Status;

                if (empty($payment->status)) {
                    $payment->status = 'NO STATUS';
                    $payment->is_callback_handled = true;
                    $payment->save();

                    continue;
                }
                if ($payment->status !== 'CONFIRMED') {
                    $payment->status = 'FAILED';
                    $payment->is_callback_handled = true;
                    $payment->save();

                    continue;
                }

                $payment_plan = $payment->payment_plan()->first();
                $course_user = DB::table('course_user')
                    ->where('user_id', '=', $payment->user_id)
                    ->where('course_id', '=', $payment->course_id)
                    ->first();

                if (empty($course_user)) {
                    DB::table('course_user')
                        ->insert([
                            'user_id' => $payment->user_id,
                            'course_id' => $payment->course_id,
                            'payment_id' => $payment->id,
                            'is_teacher' => false,
                            'expires_at' => Carbon::now()->addMonths($payment_plan->months ?? 1),
                            'is_annual' => $payment_plan->is_annual
                        ]);
                } else {
                    DB::table('course_user')
                        ->where('user_id', '=', $course_user->user_id)
                        ->where('course_id', '=', $course_user->course_id)
                        ->update([
                            'expires_at' => Carbon::now()->addMonths($payment_plan->months ?? 1),
                            'is_annual' => $payment_plan->is_annual
                        ]);
                }

                $payment->is_callback_handled = true;
                $payment->save();
            }
        }

        $course_user_rebill_records = DB::table('course_user')
            ->where('is_annual', false)
            ->whereDate('expires_at', '<', Carbon::now())
            ->get()
            ->toArray();

        foreach ($course_user_rebill_records as $course_user_rebill_record) {
            $payment = Payment::find($course_user_rebill_record->payment_id);
            if ($payment->rebill_attempts > 20 or empty($payment->rebill_id)) {
                DB::table('course_user')
                    ->where('user_id', $course_user_rebill_record->user_id)
                    ->where('course_id', $course_user_rebill_record->course_id)
                    ->delete();
                continue;
            }

            $request_former = new TinkoffRequestService($payment->user_id);
            $response = $request_former
                ->setPaymentId($payment->payment_id)
                ->setRebillId($payment->rebill_id)
                ->setChargeRequestData()
                ->formChargeRequest()
                ->makeRequest();

            if (empty($response->Success)) {
                $payment->rebill_attempts++;

                $payment->save();
            }

            $response->Status = $response->Status ?? 'NOT DEFINED';

            if ($response->Status !== 'CONFIRMED') {
                $payment->rebill_id = null;

                $payment->save();
                continue;
            }

            DB::table('course_user')
                ->where('user_id', '=', $course_user_rebill_record->user_id)
                ->where('course_id', '=', $course_user_rebill_record->course_id)
                ->update([
                    'expires_at' => Carbon::now()->addMonths($payment_plan->months ?? 1)
                ]);

            Log::channel('payment')->info($response->Message);
        }

        return Command::SUCCESS;
    }
}
