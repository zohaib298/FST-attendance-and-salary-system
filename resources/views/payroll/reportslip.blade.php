<x-layout>
<div class="min-h-screen py-8 px-4" style="background: linear-gradient(135deg,#0f3460,#16213e)">

  {{-- Header Buttons --}}
  <div class="max-w-4xl mx-auto mb-5 flex justify-end gap-3 no-print">
    <button onclick="window.print()"
      class="flex items-center gap-2 px-6 py-2.5 rounded-lg text-white text-sm font-semibold shadow-md transition"
      style="background:linear-gradient(135deg,#e63946,#c1121f)">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
      </svg>
      Print Slip
    </button>
    <a href="{{ url()->previous() }}"
      class="flex items-center gap-2 px-6 py-2.5 rounded-lg text-white text-sm font-semibold shadow-md transition"
      style="background:#475569">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Back
    </a>
  </div>

  <!-- Slip Card -->
  <div class="max-w-4xl mx-auto bg-white rounded-2xl overflow-hidden shadow-2xl">

    <!-- Top Accent Bar -->
    <div class="h-1.5" style="background:linear-gradient(90deg,#e63946,#f77f00,#fcbf49)"></div>

    <!-- Header -->
    <div class="flex items-center justify-between px-10 py-7" style="background:linear-gradient(135deg,#0f3460,#16213e)">
      <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16 w-auto" onerror="this.style.display='none'">
      <div class="text-right">
        <div class="text-2xl font-black tracking-widest text-white" style="font-family:'Playfair Display',serif">FIRESAFETY</div>
        <div class="text-xs font-semibold tracking-widest mt-1" style="color:rgba(255,255,255,0.6)">TRADING (PVT) LTD</div>
      </div>
    </div>

    <!-- Title Band -->
    <div class="text-center py-2.5" style="background:linear-gradient(90deg,#e63946,#f77f00)">
      <span class="text-lg font-bold tracking-widest text-white uppercase">Salary Slip</span>
    </div>

    <!-- Employee Info -->
    <div class="px-9 py-6 border-b border-gray-100" style="background:#f8fafc">
      <div class="grid md:grid-cols-2 gap-2">
        <div class="space-y-2">
          <div class="flex items-baseline gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 w-36">Employee Name</span>
            <span class="text-sm font-semibold text-gray-800">{{ $employee->name }}</span>
          </div>
          <div class="flex items-baseline gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 w-36">Designation</span>
            <span class="text-sm font-semibold text-gray-800">{{ $employee->department ?? 'Driver' }}</span>
          </div>
        </div>
        <div class="space-y-2">
          <div class="flex items-baseline gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 w-36">Pay for Month</span>
            <span class="text-sm font-semibold text-gray-800">{{ \Carbon\Carbon::create($year,$month)->format('F') }}</span>
          </div>
          <div class="flex items-baseline gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 w-36">Year</span>
            <span class="text-sm font-bold text-gray-900">{{ $year }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Earnings Table -->
    <div class="px-9 pt-6">
      <div class="rounded-xl overflow-hidden border border-gray-200">
        <table class="w-full">
          <thead>
            <tr style="background:linear-gradient(90deg,#16a34a,#15803d)">
              <th class="px-5 py-3 text-left text-white text-xs font-bold tracking-widest uppercase">Earnings</th>
              <th class="px-5 py-3 text-center text-white text-xs font-bold tracking-widest uppercase w-24">Details</th>
              <th class="px-5 py-3 text-right text-white text-xs font-bold tracking-widest uppercase w-36">Amount / PKR</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Salary</td><td class="px-5 py-2.5 text-center text-gray-300 text-sm">&mdash;</td><td class="px-5 py-2.5 text-right text-sm font-bold" style="color:#16a34a">{{ number_format($basic,2) }}</td></tr>
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Bike Allowance</td><td class="px-5 py-2.5 text-center text-gray-300 text-sm">&mdash;</td><td class="px-5 py-2.5 text-right text-sm text-gray-600">{{ number_format($bikeAllowance??0,2) }}</td></tr>
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Overtime (Hours)</td><td class="px-5 py-2.5 text-center text-sm font-semibold" style="color:#2563eb">{{ $overtimeHours }}</td><td class="px-5 py-2.5 text-right text-sm text-gray-600">{{ number_format($overtimeAmount,2) }}</td></tr>
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Night (Days)</td><td class="px-5 py-2.5 text-center text-sm font-semibold" style="color:#7c3aed">{{ $nightDuties }}</td><td class="px-5 py-2.5 text-right text-sm text-gray-600">{{ number_format($nightAmount,2) }}</td></tr>
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Mobile Allowance</td><td class="px-5 py-2.5 text-center text-gray-300 text-sm">&mdash;</td><td class="px-5 py-2.5 text-right text-sm text-gray-600">{{ number_format($mobileAllowance??0,2) }}</td></tr>
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Commission</td><td class="px-5 py-2.5 text-center text-gray-300 text-sm">&mdash;</td><td class="px-5 py-2.5 text-right text-sm text-gray-600">{{ number_format($commission??0,2) }}</td></tr>
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Other Bonus</td><td class="px-5 py-2.5 text-center text-gray-300 text-sm">&mdash;</td><td class="px-5 py-2.5 text-right text-sm text-gray-600">{{ number_format($otherBonus??0,2) }}</td></tr>
          </tbody>
          <tfoot>
            <tr style="background:#fff7ed">
              <td colspan="2" class="px-5 py-3 text-right text-sm font-bold text-gray-600">Gross Earnings</td>
              <td class="px-5 py-3 text-right font-bold text-lg" style="color:#f77f00">{{ number_format($grossEarnings,2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Deductions Table -->
    <div class="px-9 pt-5">
      <div class="rounded-xl overflow-hidden border border-gray-200">
        <table class="w-full">
          <thead>
            <tr style="background:linear-gradient(90deg,#dc2626,#b91c1c)">
              <th class="px-5 py-3 text-left text-white text-xs font-bold tracking-widest uppercase">Deductions</th>
              <th class="px-5 py-3 text-center text-white text-xs font-bold tracking-widest uppercase w-24">Details</th>
              <th class="px-5 py-3 text-right text-white text-xs font-bold tracking-widest uppercase w-36">Amount / PKR</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Advance Deduction</td><td class="px-5 py-2.5 text-center text-gray-300 text-sm">&mdash;</td><td class="px-5 py-2.5 text-right text-sm font-semibold" style="color:#dc2626">{{ number_format($advanceDeduction??0,2) }}</td></tr>
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Absent after Allowed Leaves</td><td class="px-5 py-2.5 text-center text-sm font-semibold" style="color:#dc2626">{{ $absentDays }}</td><td class="px-5 py-2.5 text-right text-sm font-semibold" style="color:#dc2626">{{ number_format($absentDeduction,2) }}</td></tr>
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Late Deductions (After 3 Lates = 1 Absent)</td><td class="px-5 py-2.5 text-center text-sm font-semibold" style="color:#dc2626">{{ $lateCount }}</td><td class="px-5 py-2.5 text-right text-sm font-semibold" style="color:#dc2626">{{ number_format($lateDeduction,2) }}</td></tr>
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5 text-sm text-gray-700">Any Other Less</td><td class="px-5 py-2.5 text-center text-gray-300 text-sm">&mdash;</td><td class="px-5 py-2.5 text-right text-sm font-semibold" style="color:#dc2626">{{ number_format($otherDeductions??0,2) }}</td></tr>
          </tbody>
          <tfoot>
            <tr style="background:#fef2f2">
              <td colspan="2" class="px-5 py-3 text-right text-sm font-bold text-gray-600">Gross Deductions</td>
              <td class="px-5 py-3 text-right font-bold text-lg" style="color:#dc2626">{{ number_format($totalDeductions,2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Net Payable -->
    <div class="mx-9 my-6 rounded-xl px-7 py-5 flex justify-between items-center shadow-lg" style="background:linear-gradient(135deg,#0f3460,#16213e)">
      <span class="text-xl font-black tracking-widest text-white">NET PAYABLE</span>
      <span class="text-3xl font-black" style="color:#fcbf49">PKR {{ number_format($netPayable,2) }}</span>
    </div>

    <!-- Advance Cards -->
    <div class="grid grid-cols-3 gap-4 px-9 pb-6">
      <div class="rounded-xl p-4 text-center" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);border:1px solid #86efac">
        <div class="text-xs font-bold uppercase tracking-widest mb-1" style="color:#15803d">Total Advance</div>
        <div class="text-lg font-extrabold" style="color:#14532d">{{ number_format($totalAdvance,2) }}</div>
      </div>
      <div class="rounded-xl p-4 text-center" style="background:linear-gradient(135deg,#bfdbfe,#93c5fd);border:1px solid #60a5fa">
        <div class="text-xs font-bold uppercase tracking-widest mb-1" style="color:#1e40af">This Month Deduction</div>
        <div class="text-lg font-extrabold" style="color:#1e3a8a">{{ number_format($thisMonthAdvanceDeduction,2) }}</div>
      </div>
      <div class="rounded-xl p-4 text-center" style="background:linear-gradient(135deg,#e9d5ff,#d8b4fe);border:1px solid #c084fc">
        <div class="text-xs font-bold uppercase tracking-widest mb-1" style="color:#6b21a5">Remaining Advance</div>
        <div class="text-lg font-extrabold" style="color:#581c87">{{ number_format($remainingAdvance,2) }}</div>
      </div>
    </div>

    <!-- Signatures -->
    <div class="grid grid-cols-2 gap-6 px-9 py-6 border-t border-gray-100" style="background:#f8fafc">
      <div class="text-center">
        <div class="border-t-2 border-gray-800 w-40 mx-auto pt-2"></div>
        <div class="text-xs font-semibold text-gray-700 mt-1">Chief Executive</div>
        <div class="text-xs text-gray-400">Fire Safety Trading (PVT) LTD</div>
      </div>
      <div class="text-center">
        <div class="border-t-2 border-gray-800 w-40 mx-auto pt-2"></div>
        <div class="text-xs font-semibold text-gray-700 mt-1">Employee Signature</div>
      </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-xs text-gray-400 py-3" style="background:#f1f5f9">
      This is a computer generated salary slip | Valid without signature
    </div>

  </div>
</div>

<style>
  /* Normal screen styles yahan... */
  
  @media print {
    .no-print { display: none !important; }
    body { background: white; padding: 0; margin: 0; }
    .max-w-4xl { max-width: 100%; margin: 0; }
    .rounded-2xl, .rounded-xl { border-radius: 0 !important; }
    .shadow-2xl, .shadow-lg { box-shadow: none !important; }
    @page { size: A4; margin: 0.5cm; }
    
    /* 🔥 Colors print mein bhi aayenge 🔥 */
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      color-adjust: exact !important;
    }
  }
</style>

</x-layout>