<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Services\TinkoffRequestFormerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PaymentCallbackController extends Controller
{
    private $request_former_service;
    private Payment $payment;
    private PaymentPlan $payment_plan;
    private $request_body;
    private $course_user;

    public function __invoke(Request $request)  // 4300 0000 0000 0777 - 11/22 - 111
    {
        $this->set_request_body($request);
        $this->payment = Payment::find($this->request_body->OrderId);
        $this->set_request_former_service();
        $this->payment_plan = $this->payment->payment_plan()->first();

        if ($this->ensure_is_payment_status_failed()) {
            $this->set_fail_payment_status();
            return;
        }

        $this->set_course_user_data();
        $this->update_course_user_data();
        $this->update_payment_status();
    }

    private function set_request_body(Request $request)
    {
        $source = $request->getContent();
        $this->request_body = (object)json_decode($source, true);
    }

    private function set_course_user_data()
    {
        $this->course_user = DB::table('course_user')
            ->where('course_id', '=', $this->payment->course_id)
            ->where('user_id', '=', $this->payment->user_id)
            ->first();
    }

    private function set_request_former_service()
    {
        $this->request_former_service = new TinkoffRequestFormerService($this->payment->user_id);
    }

    private function update_payment_status()
    {
        $this->payment->status = $this->request_body->Status;
        $this->payment->card_id = $this->request_body->CardId ?? null;
        $this->payment->rebill_id = $this->request_body->RebillId ?? null;
        $this->payment->is_callback_handled = true;
        $this->payment->save();
    }

    private function update_course_user_data()
    {
        if ($this->ensure_is_course_user_data_exsists()) {
            $now = new Carbon($this->course_user->expires_at);
            $expires_at = $now->addMonths($this->payment_plan->months ?? 1);

            DB::table('course_user')
                ->where('course_id', '=', $this->payment->course_id)
                ->where('user_id', '=', $this->payment->user_id)
                ->update([
                    'expires_at' => $expires_at,
                    'is_annual' => $this->payment_plan->is_annual
                ]);
            return;
        }

        $this->insert_course_user_data();
    }

    private function insert_course_user_data()
    {
        DB::table('course_user')
            ->insert([
                'user_id' => $this->payment->user_id,
                'course_id' => $this->payment->course_id,
                'payment_id' => $this->payment->id,
                'is_teacher' => false,
                'expires_at' => Carbon::now()->addMonth(),
                'is_annual' => $this->payment_plan->is_annual
            ]);
    }

    private function ensure_is_course_user_data_exsists()
    {
        return !empty($this->course_user);
    }

    private function ensure_is_payment_status_failed()
    {
        return !$this->request_body->Success;
    }

    private function set_fail_payment_status()
    {
        $this->payment->status = 'FAILED';
        $this->payment->save();
    }
}
