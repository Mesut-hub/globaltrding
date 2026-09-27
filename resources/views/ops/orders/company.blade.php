<x-ops-layout title="{{ $customer->company_name }}">
  <a href="{{ route('ops.orders.companies') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">← Back to Orders</a>
  <div class="page-head">
    <div><h1>{{ $customer->company_name }}</h1><div class="desc"><span class="cust-id">{{ $customer->customer_code }}</span></div></div>
  </div>

  <div class="subtabs">
    <a href="{{ route('ops.orders.company', $customer) }}" class="subtab {{ $tab === 'orders' ? 'active' : '' }}">Orders</a>
    <a href="{{ route('ops.orders.company', ['customer' => $customer, 'tab' => 'updates']) }}" class="subtab {{ $tab === 'updates' ? 'active' : '' }}">Updates</a>
  </div>

  @if($tab === 'orders')
    <div class="page-head" style="margin-top:18px;">
      <div class="desc" style="font-size:13px;">{{ $orders->count() }} orders on file for this company</div>
      <a href="{{ route('ops.orders.company', ['customer' => $customer, 'new' => 1]) }}" class="btn btn-gold btn-sm" @if($showAddForm) style="display:none;" @endif>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg>Add order
      </a>
    </div>

    @if($showAddForm)
      <form method="POST" action="{{ $editingOrder ? route('ops.orders.update', ['customer'=>$customer,'order'=>$editingOrder]) : route('ops.orders.store', $customer) }}" class="card" style="margin-bottom:18px;">
        @csrf
        @if($editingOrder) @method('PUT') @endif
        <div class="panel-head"><h2>{{ $editingOrder ? 'Edit order' : 'Add order' }}</h2><a href="{{ route('ops.orders.company', $customer) }}" class="btn btn-ghost btn-sm">Cancel</a></div>
        <div class="panel-body">
          <div class="field-grid">
            <div class="field"><label>Order number (invoice #)</label><input name="order_number" value="{{ old('order_number', $editingOrder->order_number ?? '') }}" required></div>
            <div class="field"><label>Order date</label><input type="date" name="order_date" value="{{ old('order_date', optional($editingOrder?->order_date)->format('Y-m-d')) }}"></div>
            <div class="field"><label>Delivered date</label><input type="date" name="delivered_date" value="{{ old('delivered_date', optional($editingOrder?->delivered_date)->format('Y-m-d')) }}"></div>
            <div class="field"><label>Balanced &amp; finished date</label><input type="date" name="balanced_finished_date" value="{{ old('balanced_finished_date', optional($editingOrder?->balanced_finished_date)->format('Y-m-d')) }}"></div>
            <div class="field"><label>Product</label><input name="product_name" value="{{ old('product_name', $editingOrder->product_name ?? '') }}"></div>
            <div class="field"><label>Payment term</label><input name="payment_term" value="{{ old('payment_term', $editingOrder->payment_term ?? '') }}"></div>
            <div class="field"><label>Quantity</label>
              <div style="display:flex;gap:6px;"><input name="quantity" value="{{ old('quantity', $editingOrder->quantity ?? '') }}" style="flex:1;">
                <select name="quantity_unit" style="width:80px;">
                  @foreach(['kg'=>'kg','ton'=>'Ton','pcs'=>'PCs','g'=>'g'] as $val=>$lbl)
                    <option value="{{ $val }}" {{ old('quantity_unit', $editingOrder->quantity_unit ?? 'kg') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                  @endforeach
                </select></div>
            </div>
            <div class="field"><label>Contracted</label>
              <select name="is_contracted">
                <option value="1" {{ old('is_contracted', $editingOrder->is_contracted ?? null) == 1 ? 'selected' : '' }}>Contracted</option>
                <option value="0" {{ old('is_contracted', $editingOrder->is_contracted ?? null) == 0 ? 'selected' : '' }}>Non-contracted</option>
              </select>
            </div>
            <div class="field"><label>Delivery time</label><input name="delivery_time" value="{{ old('delivery_time', $editingOrder->delivery_time ?? '') }}"></div>
            <div class="field"><label>Shipping term</label><input name="shipping_term" value="{{ old('shipping_term', $editingOrder->shipping_term ?? '') }}"></div>
            <div class="field"><label>Delivery point</label>
              <select name="delivery_point" onchange="document.getElementById('deliveryAddr').style.display = this.value==='other' ? '' : 'none';">
                @foreach(\App\Models\Order::deliveryPointOptions() as $val=>$lbl)
                  <option value="{{ $val }}" {{ old('delivery_point', $editingOrder->delivery_point ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
              </select>
            </div>
            <div class="field" id="deliveryAddr" style="{{ old('delivery_point', $editingOrder->delivery_point ?? '') === 'other' ? '' : 'display:none;' }}"><label>Delivery address</label><input name="delivery_address" value="{{ old('delivery_address', $editingOrder->delivery_address ?? '') }}"></div>
          </div>
          <div><div class="subhead" style="margin-bottom:8px;">Description</div><textarea name="description" rows="2" style="width:100%;padding:9px 11px;border:1px solid var(--line);border-radius:8px;font-size:13.5px;font-family:inherit;">{{ old('description', $editingOrder->description ?? '') }}</textarea></div>
          <div>
            <div class="subhead" style="margin-bottom:8px;">Responsible person(s)</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              @php($checkedIds = $editingOrder ? $editingOrder->responsiblePersons->pluck('id')->all() : [])
              @foreach($customer->contacts as $contact)
                <label class="chk-chip"><input type="checkbox" name="responsible_persons[]" value="{{ $contact->id }}" {{ in_array($contact->id, $checkedIds) ? 'checked' : '' }}> {{ $contact->name }} — {{ \App\Models\CustomerContact::roleOptions()[$contact->role] }}</label>
              @endforeach
            </div>
          </div>
          <div class="hint-strip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            <div><strong>Save</strong> keeps this order as a draft. <strong>Save &amp; send</strong> emails the order confirmation to the checked people above and shows it in their portal.</div>
          </div>
        </div>
        <div class="panel-foot">
          <button class="btn btn-line" name="action" value="save">Save</button>
          <button class="btn btn-gold" name="action" value="send">Save &amp; send</button>
        </div>
      </form>
    @endif

    <div class="card">
      <table>
        <thead><tr><th>Order</th><th>Product</th><th>Qty</th><th>Status</th><th>Order date</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
          @foreach($orders as $o)
            <tr>
              <td class="cust-id">{{ $o->order_number }}</td><td>{{ $o->product_name }}</td>
              <td>{{ rtrim(rtrim((string)$o->quantity, '0'), '.') }} {{ $o->quantity_unit }}</td>
              <td>
                @if($o->status === 'draft')<span class="pill pill-draft">⚠ Draft</span>
                @else<span class="pill pill-active">✓ {{ $o->isDelivered() ? 'Sent — delivered' : 'Sent — in transit' }}</span>@endif
              </td>
              <td style="color:var(--text-500);">{{ $o->order_date?->format('d M Y') }}</td>
              <td><div class="row-actions">
                <a href="{{ route('ops.orders.company', ['customer'=>$customer,'edit_order'=>$o->id]) }}" class="icon-btn" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                @if($o->status === 'draft')
                  <form method="POST" action="{{ route('ops.orders.send', ['customer'=>$customer,'order'=>$o]) }}">
                    @csrf
                    <button class="icon-btn" title="Send"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/></svg></button>
                  </form>
                @endif
              </div></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="field" style="max-width:340px;margin:18px 0 16px;">
      <label>Order</label>
      <form method="GET" action="{{ route('ops.orders.company', $customer) }}" id="orderSelectForm">
        <input type="hidden" name="tab" value="updates">
        <select name="order" onchange="document.getElementById('orderSelectForm').submit();">
          @foreach($orders->where('status', 'sent') as $o)
            <option value="{{ $o->id }}" {{ $selectedOrder?->id === $o->id ? 'selected' : '' }}>{{ $o->order_number }} — {{ $o->product_name }} ({{ $o->isDelivered() ? 'delivered' : 'in transit' }})</option>
          @endforeach
        </select>
      </form>
    </div>

    @if($selectedOrder)
      <form method="POST" action="{{ $editingStatus ? route('ops.orders.status.update', ['customer'=>$customer,'order'=>$selectedOrder,'update'=>$editingStatus]) : route('ops.orders.status.store', ['customer'=>$customer,'order'=>$selectedOrder]) }}" class="card" style="margin-bottom:18px;">
        @csrf
        @if($editingStatus) @method('PUT') @endif
        <div class="panel-head"><h2>{{ $editingStatus ? 'Edit status update' : 'Add status update' }}</h2>@if($editingStatus)<a href="{{ route('ops.orders.company', ['customer'=>$customer,'tab'=>'updates','order'=>$selectedOrder->id]) }}" class="btn btn-ghost btn-sm">Cancel</a>@endif</div>
        <div class="panel-body">
          <div class="field-grid">
            <div class="field">
              <label>Stage</label>
              <select name="stage_key" onchange="document.getElementById('customStage').style.display = this.value==='custom' ? '' : 'none';">
                @foreach($stageOptions as $key => $label)<option value="{{ $key }}" {{ ($editingStatus->stage_key ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                <option value="custom">Custom stage…</option>
              </select>
            </div>
            <div class="field" id="customStage" style="display:none;"><label>Custom stage name</label><input name="custom_label" value="{{ $editingStatus->stage_label ?? '' }}"></div>
            <div class="field"><label>Date</label><input type="date" name="stage_date" value="{{ optional($editingStatus?->stage_date)->format('Y-m-d') }}" required></div>
            <div class="field" style="grid-column:span 3;"><label>Notes (optional)</label><input name="notes" value="{{ $editingStatus->notes ?? '' }}" placeholder="e.g. cleared customs at Mersin, released same day"></div>
          </div>
          @unless($editingStatus)
          <div class="hint-strip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            <div><strong>Update</strong> saves this stage to the timeline immediately. <strong>Update &amp; send</strong> also emails it to the order's responsible person(s). Selecting <strong>Cargo delivered successfully</strong> posts the green delivery confirmation to the customer.</div>
          </div>
          @endunless
        </div>
        <div class="panel-foot">
          @if($editingStatus)
            <button class="btn btn-gold">Save changes</button>
          @else
            <button class="btn btn-line" name="send_email" value="0">Update</button>
            <button class="btn btn-gold" name="send_email" value="1">Update &amp; send</button>
          @endif
        </div>
      </form>

      <div class="timeline-card">
        <h2>History — {{ $selectedOrder->order_number }}</h2>
        <div class="tl">
          @foreach($selectedOrder->statusUpdates as $u)
            <div class="tl-item {{ $loop->last ? 'current' : 'done' }}">
              <div class="tl-dot">@unless($loop->last)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>@endunless</div>
              <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                <div>
                  <div class="tl-label">{{ $u->stage_label }}</div>
                  <div class="tl-date">{{ $u->stage_date->format('d M Y') }}@if($u->notes) — {{ $u->notes }}@endif</div>
                </div>
                <div class="row-actions">
                  <a href="{{ route('ops.orders.company', ['customer'=>$customer,'tab'=>'updates','order'=>$selectedOrder->id,'edit_status'=>$u->id]) }}" class="icon-btn" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                  <form method="POST" action="{{ route('ops.orders.status.resend', ['customer'=>$customer,'order'=>$selectedOrder,'update'=>$u]) }}">@csrf<button class="icon-btn" title="Resend"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/></svg></button></form>
                  <form method="POST" action="{{ route('ops.orders.status.delete', ['customer'=>$customer,'order'=>$selectedOrder,'update'=>$u]) }}" onsubmit="return confirm('Remove this status entry?');">@csrf @method('DELETE')<button class="icon-btn" title="Cancel"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button></form>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @else
      <p class="desc">No sent orders to track for this company yet.</p>
    @endif
  @endif
</x-ops-layout>