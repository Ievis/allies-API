<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostPaymentPlanRequest;
use App\Http\Requests\PostPurchaseRequest;
use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Services\TinkoffRequestService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseActionController extends Controller
{
    private $request_former_service;

    private $course;
    private $payment;
    private $payment_plan;

    private function getRequestFormerService()
    {
        $this->request_former_service = new TinkoffRequestService(auth()->user()->id);
    }

    public function __invoke(PostPurchaseRequest $request)
    {
        $data = $request->validated();
        $this->course = Course::findOrFail($data['course_id']);
        $this->payment_plan = PaymentPlan::findOrFail($data['payment_plan_id']);

        $user_id = auth()->user()->id;

        $course_user = DB::table('course_user')
            ->where('user_id', $user_id)
            ->where('course_id', $this->course->id)
            ->whereDate('expires_at', '>', Carbon::now())
            ->orWhere('is_annual', true)
            ->where('user_id', $user_id)
            ->where('course_id', $this->course->id)
            ->first();

        if (!empty($course_user->is_annual)) {
            return response()->json([
                'success' => false,
                'message' => 'У вас уже есть пожизненный доступ к данному курсу'
            ])
                ->header('Charset', 'utf-8')
                ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        }

        if ($course_user) {
            return response()->json([
                'success' => false,
                'message' => 'У вас уже есть доступ к данному курсу'
            ])
                ->header('Charset', 'utf-8')
                ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        }

        $this->getRequestFormerService();

        $amount = $this->payment_plan->amount;
        $this->createPayment($user_id, $this->course->id, $amount);

        if (!$this->payment_plan->is_annual) {
            $get_customer_response = $this->request_former_service
                ->setGetCustomerRequestData()
                ->formGetCustomerRequest()
                ->makeRequest();

            if (!$get_customer_response->Success) {
                $this->request_former_service
                    ->setAddCustomerRequestData()
                    ->formAddCustomerRequest()
                    ->makeRequest();
            }
        }

        $this->request_former_service
            ->setOrderId($this->payment->id)
            ->setAmount($amount);

        $create_payment_response = $this->request_former_service
            ->setInitRequestData($this->payment_plan->is_annual)
            ->formInitRequest()
            ->makeRequest();

        if (!$create_payment_response->Success) {
            $this->payment->delete();

            return response()->json([
                'success' => false,
                'message' => $create_payment_response->Message,
                'details' => $create_payment_response->Details,
            ])
                ->header('Charset', 'utf-8')
                ->setEncodingOptions(JSON_UNESCAPED_UNICODE);;
        }

        $this->payment->payment_id = $create_payment_response->PaymentId;
        $this->payment->save();

        return response()->json([
            'success' => true,
            'paymentURL' => $create_payment_response->PaymentURL
        ]);
    }

    private function createPayment($user_id, $course_id, $amount)
    {
        $this->payment = new Payment([
            'user_id' => $user_id,
            'course_id' => $course_id,
            'payment_plan_id' => $this->payment_plan->id
        ]);

        $this->payment->save();
    }
}
