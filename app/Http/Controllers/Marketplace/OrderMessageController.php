<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderMessageResource;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class OrderMessageController extends Controller
{
    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        abort_if($user->id !== $order->user_id, 404);

        //        TODO: make dedicated ShowOrderMessage Request!
        $direction = request('direction', 'before');
        $cursor = request('cursor');

        $query = $order->messages()
            ->with('user')
            ->orderBy('created_at', $direction === 'after' ? 'asc' : 'desc')
            ->orderBy('uuid', $direction === 'after' ? 'asc' : 'desc');

        if ($cursor) {
            try {
                $decoded = json_decode(base64_decode($cursor), true);
                if (!isset($decoded['created_at'], $decoded['uuid'])) {
                    throw new InvalidArgumentException('Invalid cursor format');
                }

                if ($direction === 'after') {
                    $query->where(function ($q) use ($decoded) {
                        $q->where('created_at', '>', $decoded['created_at'])
                            ->orWhere(function ($q2) use ($decoded) {
                                $q2->where('created_at', $decoded['created_at'])
                                    ->where('uuid', '>', $decoded['uuid']);
                            });
                    });
                } else {
                    $query->where(function ($q) use ($decoded) {
                        $q->where('created_at', '<', $decoded['created_at'])
                            ->orWhere(function ($q2) use ($decoded) {
                                $q2->where('created_at', $decoded['created_at'])
                                    ->where('uuid', '<', $decoded['uuid']);
                            });
                    });
                }
            } catch (Exception $e) {
                return response()->json(['error' => 'Invalid cursor'], 400);
            }
        }

        return DB::transaction(function () use ($query, $direction) {
            $messages = $query->limit(10)->lockForUpdate()->get();

            $items = $messages->map(function ($message) {
                $message->cursor = base64_encode(json_encode([
                    'created_at' => $message->created_at->toISOString(),
                    'uuid' => $message->uuid,
                ]));

                return $message;
            });

            if ($direction === 'before') {
                $items = $items->sortBy('created_at')->values();
            }

            return response()->json([
                'data' => OrderMessageResource::collection($items),
                'next_cursor' => $items->isNotEmpty() ? $items->last()->cursor : null,
                'prev_cursor' => $items->isNotEmpty() ? $items->first()->cursor : null,
            ]);
        });
    }

    public function store(Request $request, Order $order)
    {
        $user = $request->user();
        abort_if($user->id !== $order->user_id, 404);

        //        TODO: make dedicated StoreOrderMessage Request!
        $validated = $request->validate([
            'message' => 'required|string|min:3|max:255',
        ]);

        try {
            return DB::transaction(function () use ($order, $validated) {
                $order = Order::where('uuid', $order->uuid)->lockForUpdate()->first();

                $message = $order->messages()->create([
                    'message' => trim($validated['message']),
                    'user_id' => auth()->id(),
                ]);

                $message->load('user');

                $message->cursor = base64_encode(json_encode([
                    'created_at' => $message->created_at->toISOString(),
                    'uuid' => $message->uuid,
                ]));

                //                TODO: consider adding real-time broadcasting!
                // broadcast(new MessageSent($message, $order))->toOthers();

                return response()->json([
                    'message' => new OrderMessageResource($message),
                    'success' => true,
                ]);
            });
        } catch (Exception $e) {
            Log::error('Message send error', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            //            TODO: standarize this!
            return response()->json([
                'error' => 'Błąd wysyłania wiadomości',
            ], 500);
        }
    }
}
