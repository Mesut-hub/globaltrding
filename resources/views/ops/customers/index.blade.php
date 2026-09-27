<x-ops-layout title="Customer Registration">
  <div class="page-head">
    <div>
      <h1>Customer registration</h1>
      <div class="desc">Register companies, manage their contacts, and issue portal access.</div>
    </div>
    <a href="{{ route('ops.customers.index', ['new' => 1]) }}" class="btn btn-gold" @if($showAddForm) style="display:none;" @endif>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg>
      Add customer
    </a>
  </div>

  <div class="stats">
    <div class="stat"><div class="n">{{ $stats['total'] }}</div><div class="l">Total customers</div></div>
    <div class="stat"><div class="n gold">{{ $stats['draft'] }}</div><div class="l">Draft — awaiting send</div></div>
    <div class="stat"><div class="n">{{ $stats['active'] }}</div><div class="l">Active</div></div>
    <div class="stat"><div class="n">{{ $stats['blocked'] }}</div><div class="l">Blocked / suspended</div></div>
  </div>

  @if($showAddForm)
    <form method="POST" action="{{ route('ops.customers.store') }}" class="card" style="margin-bottom:22px;">
      @csrf
      <div class="panel-head">
        <h2>Add customer</h2>
        <a href="{{ route('ops.customers.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
      </div>
      <div class="panel-body">
        <div>
          <div class="subhead" style="margin-bottom:10px;">Company details</div>
          <div class="field-grid">
            <div class="field"><label>Company name</label><input name="company_name" required></div>
            <div class="field"><label>Full commercial name</label><input name="full_commercial_name"></div>
            <div class="field"><label>Registration number</label><input name="registration_number"></div>
            <div class="field"><label>Phone</label><input name="phone"></div>
            <div class="field"><label>Website</label><input name="website"></div>
            <div class="field"><label>Company email</label><input name="email" type="email" required></div>
          </div>
        </div>

        <div>
          <div class="subhead" style="margin-bottom:10px;">Contacts</div>
          @foreach(['owner' => 'Owner', 'contact_person' => 'Contact', 'other' => 'Other'] as $role => $label)
            <div class="contact-row">
              <input type="hidden" name="contacts[{{ $loop->index }}][role]" value="{{ $role }}">
              <div class="role-tag">{{ $label }}</div>
              <div class="field"><label>Name</label><input name="contacts[{{ $loop->index }}][name]" placeholder="Full name"></div>
              <div class="field"><label>Phone</label><input name="contacts[{{ $loop->index }}][phone]" placeholder="Phone"></div>
              <div class="field"><label>Email</label><input name="contacts[{{ $loop->index }}][email]" type="email" placeholder="Email"></div>
              <div></div>
            </div>
          @endforeach
        </div>

        <div class="hint-strip">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
          <div><strong>Save</strong> keeps this as a draft (shown with a pending mark below) without contacting anyone. <strong>Save &amp; send</strong> emails every contact above their portal credentials and the registration confirmation, and clears the pending mark.</div>
        </div>
      </div>
      <div class="panel-foot">
        <button class="btn btn-line" type="submit">Save</button>
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
              @if($c->isDraft())
                <span class="pill pill-draft">⚠ Draft</span>
              @elseif($c->isActive())
                <span class="pill pill-active">✓ Active</span>
              @else
                <span class="pill pill-blocked">{{ ucfirst($c->status) }}</span>
              @endif
            </td>
            <td style="color:var(--text-500);">{{ $c->created_at->format('d M Y') }}</td>
            <td><div class="row-actions">
              @if($c->isDraft())
                <form method="POST" action="{{ route('ops.customers.send', $c) }}" onsubmit="return confirm('Send registration email now?');">
                  @csrf<button class="icon-btn" title="Send registration"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/></svg></button>
                </form>
              @else
                <form method="POST" action="{{ route('ops.customers.reset', $c) }}" onsubmit="return confirm('Send a new password to this customer\'s contacts?');">
                  @csrf<button class="icon-btn" title="Reset password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></button>
                </form>
              @endif
            </div></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-ops-layout>