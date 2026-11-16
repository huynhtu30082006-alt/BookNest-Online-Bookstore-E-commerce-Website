<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Exceptions\OrderException;
use App\Services\OrderService;

class OrderController extends Controller
{
    /** Order history for logged-in members. */
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->withCount('items')->latest('id')->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeView($request, $order);

        return view('orders.show', [
            'order' => $order->load('items.product', 'coupon'),
            'confirmation' => false,
        ]);
    }

    public function confirmation(Request $request, Order $order)
    {
        $this->authorizeView($request, $order);

        return view('orders.show', [
            'order' => $order->load('items.product', 'coupon'),
            'confirmation' => true,
        ]);
    }

    /** Cancel a Pending order (member who owns it, or guest who placed / looked it up in this session). */
    public function cancel(Request $request, Order $order, OrderService $orders)
    {
        $this->authorizeView($request, $order);

        try {
            $orders->cancel($order);
        } catch (OrderException $e) {
            return redirect()->route('orders.show', $order)->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('success', 'Your order has been cancelled.');
    }

    /** Guest order-status lookup form (order reference + email). */
    public function trackForm()
    {
        return view('orders.track');
    }

    public function track(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email'],
        ]);

        $id = (int) preg_replace('/\D/', '', $data['reference']);
        $order = $id > 0 ? Order::find($id) : null;

        // Same message for "no such order" and "wrong email" so references cannot be probed.
        if (! $order || strcasecmp($order->guest_email, $data['email']) !== 0) {
            return back()->withInput()->with('error', 'No order matches that reference and email.');
        }

        $request->session()->push('viewable_orders', $order->id);

        return redirect()->route('orders.show', $order);
    }

    /** Members see their own orders; guests only orders they placed / looked up in this session. */
    private function authorizeView(Request $request, Order $order): void
    {
        $user = $request->user();
        if ($user && $order->user_id === $user->id) {
            return;
        }

        if (in_array($order->id, $request->session()->get('viewable_orders', []), true)) {
            return;
        }

        abort(404);
    }
}
