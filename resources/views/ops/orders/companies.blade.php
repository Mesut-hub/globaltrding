<x-ops-layout title="Orders">
  <div class="page-head">
    <div><h1>Orders</h1><div class="desc">Select a company to view its orders or update a cargo's status.</div></div>
  </div>
  <div class="card">
    <table>
      <thead><tr><th>Company</th><th>Open orders</th><th>In transit</th><th style="text-align:right;"></th></tr></thead>
      <tbody>
        @foreach($customers as $c)
          <tr>
            <td><div class="cust-name">{{ $c->company_name }}</div><div class="cust-id">{{ $c->customer_code }}</div></td>
            <td>{{ $c->open_orders_count }}</td>
            <td>{{ $c->in_transit_count }}</td>
            <td style="text-align:right;"><a href="{{ route('ops.orders.company', $c) }}" class="btn btn-line btn-sm">View company →</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-ops-layout>