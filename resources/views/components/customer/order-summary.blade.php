{{-- Ringkasan order. Props: $totals --}}
@props(['totals' => ['subtotal' => 0, 'total' => 0]])

<dl class="space-y-1.5 rounded-2xl border border-pk-brown/10 bg-white p-4 text-sm shadow-card" data-order-summary>
    <div class="flex justify-between text-pk-brown-soft"><dt>Subtotal</dt><dd class="font-semibold text-pk-brown" data-total="subtotal">Rp {{ number_format($totals['subtotal'], 0, ',', '.') }}</dd></div>
    <div class="flex justify-between border-t border-pk-brown/10 pt-2 font-heading text-lg font-semibold text-pk-brown"><dt>Total</dt><dd data-total="total">Rp {{ number_format($totals['total'], 0, ',', '.') }}</dd></div>
</dl>
