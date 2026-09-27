<x-ops-layout title="Customer Registration">
  <div class="page-head">
    <div><h1>Customer registration</h1><div class="desc">Register companies, manage their contacts, and issue portal access.</div></div>
    <a href="{{ route('ops.customers.index', ['new' => 1]) }}" class="btn btn-gold" @if($showAddForm) style="display:none;" @endif>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg>Add customer
    </a>
  </div>

  <div class="stats">
    <div class="stat"><div class="n">{{ $stats['total'] }}</div><div class="l">Total customers</div></div>
    <div class="stat"><div class="n gold">{{ $stats['draft'] }}</div><div class="l">Draft — awaiting send</div></div>
    <div class="stat"><div class="n">{{ $stats['active'] }}</div><div class="l">Active</div></div>
    <div class="stat"><div class="n">{{ $stats['blocked'] }}</div><div class="l">Blocked / suspended</div></div>
  </div>

  @if($showAddForm)
    @php($roleLabels = ['owner' => 'Owner', 'contact_person' => 'Contact', 'other' => 'Other'])
    <form method="POST" action="{{ $editing ? route('ops.customers.update', $editing) : route('ops.customers.store') }}" class="card" style="margin-bottom:22px;">
      @csrf
      @if($editing) @method('PUT') @endif
      <div class="panel-head">
        <h2>{{ $editing ? 'Edit customer' : 'Add customer' }}</h2>
        <a href="{{ route('ops.customers.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
      </div>
      <div class="panel-body">
        <div>
          <div class="subhead" style="margin-bottom:10px;">Company details</div>
          <div class="field-grid">
            <div class="field"><label>Company name</label><input name="company_name" value="{{ old('company_name', $editing->company_name ?? '') }}" required></div>
            <div class="field"><label>Full commercial name</label><input name="full_commercial_name" value="{{ old('full_commercial_name', $editing->full_commercial_name ?? '') }}"></div>
            <div class="field"><label>Registration number</label><input name="registration_number" value="{{ old('registration_number', $editing->registration_number ?? '') }}"></div>
            <div class="field"><label>Phone</label><input name="phone" value="{{ old('phone', $editing->phone ?? '') }}"></div>
            <div class="field"><label>Website</label><input name="website" value="{{ old('website', $editing->website ?? '') }}"></div>
            <div class="field"><label>Company email</label><input name="email" type="email" value="{{ old('email', $editing->email ?? '') }}" required></div>
          </div>
        </div>

        <div>
          <div class="subhead" style="margin-bottom:10px;">Contacts</div>
          @foreach($roleLabels as $role => $label)
            @php($existing = $editing?->contacts->firstWhere('role', $role))
            <div class="contact-row">
              <input type="hidden" name="contacts[{{ $loop->index }}][role]" value="{{ $role }}">
              <div class="role-tag">{{ $label }}</div>
              <div class="field"><label>Name</label><input name="contacts[{{ $loop->index }}][name]" value="{{ $existing->name ?? '' }}" placeholder="Full name"></div>
              <div class="field"><label>Phone</label><input name="contacts[{{ $loop->index }}][phone]" value="{{ $existing->phone ?? '' }}" placeholder="Phone"></div>
              <div class="field"><label>Email</label><input name="contacts[{{ $loop->index }}][email]" type="email" value="{{ $existing->email ?? '' }}" placeholder="Email"></div>
              <div></div>
            </div>
          @endforeach
        </div>

        <div class="hint-strip">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
          <div><strong>Save</strong> keeps this as a draft without contacting anyone. <strong>Save &amp; send</strong> emails every contact their portal credentials and the registration confirmation, and clears the pending mark.</div>
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
      <thead><tr><th>Customer</th><th>Contact</th><th>Status</th><th>Registered</th><th style="text-align:right;">Actions</th></tr></thead>
      <tbody>
        @foreach($customers as $c)
          @php($primaryContact = $c->contacts->first())
          <tr>
            <td><div class="cust-name">{{ $c->company_name }}</div><div class="cust-id">{{ $c->customer_code }}</div></td>
            <td>{{ $primaryContact->name ?? '—' }}<div class="cust-sub">{{ $primaryContact->email ?? '' }}</div></td>
            <td>
              @if($c->isDraft())<span class="pill pill-draft">⚠ Draft</span>
              @elseif($c->isActive())<span class="pill pill-active">✓ Active</span>
              @else<span class="pill pill-blocked">{{ ucfirst($c->status) }}</span>@endif
            </td>
            <td style="color:var(--text-500);">{{ $c->created_at->format('d M Y') }}</td>
            <td><div class="row-actions">
              <a href="{{ route('ops.customers.index', ['edit' => $c->id]) }}" class="icon-btn" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>

              @if($c->isDraft())
                <form method="POST" action="{{ route('ops.customers.send', $c) }}" onsubmit="return confirm('Send registration email now?');">@csrf<button class="icon-btn" title="Send registration"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/></svg></button></form>
              @else
                <form method="POST" action="{{ route('ops.customers.reset', $c) }}" onsubmit="return confirm('Send a new password to this customer\'s contacts?');">@csrf<button class="icon-btn" title="Reset password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></button></form>
              @endif

              @if($c->status === 'active')
                <form method="POST" action="{{ route('ops.customers.suspend', $c) }}" onsubmit="return confirm('Suspend this customer?');">@csrf<button class="icon-btn" title="Suspend"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M10 9v6M14 9v6"/></svg></button></form>
              @elseif($c->status === 'suspended' || $c->status === 'blocked')
                <form method="POST" action="{{ route('ops.customers.reactivate', $c) }}" onsubmit="return confirm('Reactivate this customer?');">@csrf<button class="icon-btn" title="Reactivate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg></button></form>
              @endif

              @if($c->status !== 'blocked')
                <form method="POST" action="{{ route('ops.customers.block', $c) }}" onsubmit="return confirm('Block this customer?');">@csrf<button class="icon-btn" title="Block"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg></button></form>
              @endif

              <form method="POST" action="{{ route('ops.customers.destroy', $c) }}" onsubmit="return confirm('Delete this customer permanently?');">@csrf @method('DELETE')<button class="icon-btn" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg></button></form>
            </div></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-ops-layout>