<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request, Mission $mission)
    {
        $user = $request->user();

        // Only company can create payment
        if ($user->role !== 'company' || $mission->company_id !== $user->company->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($mission->status !== 'assigned') {
            return response()->json(['message' => 'Mission must be assigned before payment'], 400);
        }

        if ($mission->payment && $mission->payment->status !== 'failed') {
            return response()->json(['message' => 'Payment already exists'], 400);
        }

        $amount = (int) ($mission->total_amount * 100); // Convert to cents

        // In a real implementation, this would create a Stripe PaymentIntent
        // For now, we'll create a mock payment record
        $payment = Payment::create([
            'mission_id' => $mission->id,
            'company_id' => $mission->company_id,
            'provider_id' => $mission->provider_id,
            'amount' => $mission->total_amount,
            'platform_fee' => $mission->platform_fee,
            'provider_amount' => $mission->price,
            'status' => 'held',
            'currency' => 'EUR',
            'description' => "Payment for mission: {$mission->title}",
            'stripe_payment_intent_id' => 'pi_' . uniqid(),
        ]);

        // Update mission status
        $mission->update(['status' => 'in_progress']);

        return response()->json([
            'payment' => $payment,
            'client_secret' => 'mock_client_secret_' . uniqid(),
        ]);
    }

    public function show(Payment $payment)
    {
        return response()->json([
            'payment' => $payment->load(['mission', 'company', 'provider']),
        ]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // In production, verify the webhook signature
        // For now, we'll just log the webhook

        Log::info('Stripe webhook received', [
            'payload' => $payload,
            'sig_header' => $sigHeader,
        ]);

        // Handle different webhook events
        // In production, parse the payload and handle events like:
        // - payment_intent.succeeded
        // - payment_intent.payment_failed
        // - account.updated (for Stripe Connect)

        return response()->json(['received' => true]);
    }
}
