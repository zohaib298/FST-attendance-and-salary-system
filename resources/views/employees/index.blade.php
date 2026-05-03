
<x-layout>
<div class="flex min-h-screen bg-gray-100">
    <x-sidebar />
    <main class="flex-1 p-8">
        @php
        $useEmployees = $employees ?? collect();
        $daysInMonth  = \Carbon\Carbon::create($year, $month)->daysInMonth;
        $workingDays  = collect();
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = \Carbon\Carbon::create($year, $month, $d);
            if (!$date->isSunday()) $workingDays->push($date);
        }
        $totalWorkingDays = $workingDays->count();
        $isPastMonth    = \Carbon\Carbon::create($year,$month)->lt(now()->startOfMonth());
        $isCurrentMonth = ($month == now()->month && $year == now()->year);
        $allAttendances  = $allAttendances  ?? collect();
        $todayAttendance = $todayAttendance ?? collect();
        $present = $todayAttendance->where('status','present')->count();
        $absent  = $todayAttendance->where('status','absent')->count();
        $leave   = $todayAttendance->where('status','leave')->count();
        $late    = $todayAttendance->where('late',1)->count();
        $prevMonth = $month-1 < 1  ? 12 : $month-1;
        $prevYear  = $month-1 < 1  ? $year-1 : $year;
        $nextMonth = $month+1 > 12 ? 1  : $month+1;
        $nextYear  = $month+1 > 12 ? $year+1 : $year;
        $officeStart      = \Carbon\Carbon::createFromTime(9,30,0);
        $lateToAbsentRule = $settings['late_to_absent'] ?? 3;

        $salaryData = collect();
        foreach ($useEmployees as $emp) {
            $empAtt      = $allAttendances[$emp->id] ?? collect();
            $presentDays = $empAtt->where('status','present')->count();
            $absentDays  = $empAtt->where('status','absent')->count();
            $leaveDays   = $empAtt->where('status','leave')->count();
            $nightDuties = (int)$empAtt->sum('night');
            $totalAdv    = (float)$empAtt->sum('advance');
            $overtimeHrs = (float)$empAtt->sum('overtime');
            $lateDays    = (int)$empAtt->where('late',1)->count();
            $lateAbsents = (int)floor($lateDays / $lateToAbsentRule);

            $basic           = (float)($emp->basic_salary      ?? 0);
           // NAYA — AttendanceSummary se lo
$empSummary  = $allAttendances[$emp->id] ?? collect();
$bikeAllow   = (float)($empSummary->first()?->bike_allowance   ?? 0);
$mobileAllow = (float)($empSummary->first()?->mobile_allowance ?? 0);
$otherAllow  = (float)($empSummary->first()?->other_allowance  ?? 0);
$commission  = (float)($empSummary->first()?->commission       ?? 0);
            $nightRate       = (float)($emp->night_rate        ?? 500);
            $lateDeductPer   = (float)($emp->late_deduction    ?? 0);
            $absentDeductPer = (float)($emp->absent_deduction  ?? 0);

            $perDay  = $basic > 0 ? round($basic / 26) : 0;
            $perHour = $perDay > 0 ? round($perDay / 8) : 0;

           $earnedBasic     = max(0, ($presentDays + $leaveDays - $lateAbsents)) * $perDay;
$empAddedMonth   = \Carbon\Carbon::parse($emp->created_at)->month;
$empAddedYear    = \Carbon\Carbon::parse($emp->created_at)->year;
$isEmpAddedMonth = ($empAddedMonth == $month && $empAddedYear == $year);
$totalAllowances = $isEmpAddedMonth ? ($bikeAllow + $mobileAllow + $otherAllow + $commission) : 0;
            $nightPay         = $nightDuties * $nightRate;
            $overtimePay      = $overtimeHrs * $perHour;
            $grossSalary      = $earnedBasic + $totalAllowances + $nightPay + $overtimePay;
            $lateDeductTotal  = $lateDays * $lateDeductPer;
            $absentDeductTotal= ($absentDays + $lateAbsents) * $absentDeductPer;
            $netSalary        = $grossSalary - $lateDeductTotal - $absentDeductTotal - $totalAdv;
            $manualNet        = $emp->manual_net_salary ?? null;

            $salaryData->push((object)[
                'emp'              => $emp,
                'presentDays'      => $presentDays,
                'absentDays'       => $absentDays,
                'leaveDays'        => $leaveDays,
                'nightDuties'      => $nightDuties,
                'lateDays'         => $lateDays,
                'lateAbsents'      => $lateAbsents,
                'effectiveAbsent'  => $absentDays + $lateAbsents,
                'basic'            => $basic,
                'bikeAllow'        => $bikeAllow,
                'mobileAllow'      => $mobileAllow,
                'otherAllow'       => $otherAllow,
                'commission'       => $commission,
                'totalAllowances'  => $totalAllowances,
                'nightPay'         => $nightPay,
                'overtime'         => $overtimeHrs,
                'overtimePay'      => $overtimePay,
                'earnedBasic'      => $earnedBasic,
                'lateDeductTotal'  => $lateDeductTotal,
                'absentDeductTotal'=> $absentDeductTotal,
                'advance'          => $totalAdv,
                'grossSalary'      => $grossSalary,
                'netSalary'        => $netSalary,
                'manualNet'        => $manualNet,
            ]);
        }
        $totalGross  = $salaryData->sum('grossSalary');
        $totalNet    = $isPastMonth
            ? $salaryData->sum(fn($r) => $r->manualNet !== null ? $r->manualNet : $r->netSalary)
            : $salaryData->sum('netSalary');
        $totalAdvSum = $salaryData->sum('advance');
        @endphp

        {{-- TOP BAR --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2 flex-wrap">
                    🏢 HR Dashboard
                    <span class="text-sm font-semibold bg-blue-100 text-blue-700 px-3 py-1 rounded-full">{{ $activeBranch }}</span>
                    @if($isPastMonth)<span class="text-xs bg-amber-100 text-amber-600 px-2 py-1 rounded-full">Past Month</span>@endif
                </h1>
                <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::create($year,$month)->format('F Y') }}</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="?branch={{ $activeBranch }}&month={{ $prevMonth }}&year={{ $prevYear }}" class="border border-gray-300 bg-white px-3 py-2 text-gray-600 text-sm rounded-l-lg hover:bg-gray-50">&larr;</a>
                <span class="border-t border-b border-gray-300 bg-white px-5 py-2 text-sm font-bold text-gray-800 min-w-[140px] text-center">{{ \Carbon\Carbon::create($year,$month)->format('F Y') }}</span>
                <a href="?branch={{ $activeBranch }}&month={{ $nextMonth }}&year={{ $nextYear }}" class="border border-gray-300 bg-white px-3 py-2 text-gray-600 text-sm rounded-r-lg hover:bg-gray-50">&rarr;</a>
                <button onclick="window.print()" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">🖨️ Print</button>
            </div>
        </div>

        {{-- BRANCH SELECTOR --}}
        <div class="mb-5 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm flex items-center gap-2 flex-wrap">
            <span class="text-xs text-gray-400 font-semibold uppercase tracking-wide mr-1">Branch:</span>
            @foreach($allBranchNames as $br)
            <a href="?branch={{ $br }}&month={{ $month }}&year={{ $year }}"
               class="px-4 py-1.5 rounded-lg text-sm font-semibold transition border {{ $activeBranch===$br ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-600' }}">
                {{ $br }}
            </a>
            @endforeach
        </div>

        {{-- ALERTS --}}
        @if(session('success'))<div class="mb-4 bg-green-100 text-green-700 px-4 py-3 rounded-lg text-sm">✅ {{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded-lg text-sm">❌ {{ session('error') }}</div>@endif

        {{-- LATE RULE BANNER --}}
        <div class="mb-4 bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm text-orange-700">
                ⏰ <span class="font-semibold">Late Rule:</span> Har <strong>{{ $lateToAbsentRule }}</strong> baar late = 1 din absent
            </div>
            <button onclick="document.getElementById('lateRuleModal').classList.remove('hidden')"
                class="text-xs bg-orange-100 text-orange-700 border border-orange-300 px-3 py-1.5 rounded-lg hover:bg-orange-200 font-medium">
                ⚙️ Change
            </button>
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-5">
            <div class="bg-white p-4 rounded-xl shadow-sm border">
                <p class="text-gray-400 text-xs uppercase mb-1">Employees</p>
                <h2 class="text-2xl font-bold text-gray-800">{{ $useEmployees->count() }}</h2>
            </div>
            <div class="bg-green-50 p-4 rounded-xl shadow-sm border border-green-100">
                <p class="text-gray-400 text-xs uppercase mb-1">Present</p>
                <h2 class="text-2xl font-bold text-green-600">{{ $present }}</h2>
                <p class="text-xs text-green-400">Today</p>
            </div>
            <div class="bg-red-50 p-4 rounded-xl shadow-sm border border-red-100">
                <p class="text-gray-400 text-xs uppercase mb-1">Absent</p>
                <h2 class="text-2xl font-bold text-red-500">{{ $absent }}</h2>
                <p class="text-xs text-red-300">Today</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-xl shadow-sm border border-yellow-100">
                <p class="text-gray-400 text-xs uppercase mb-1">Leave</p>
                <h2 class="text-2xl font-bold text-yellow-500">{{ $leave }}</h2>
                <p class="text-xs text-yellow-400">Today</p>
            </div>
            <div class="bg-orange-50 p-4 rounded-xl shadow-sm border border-orange-100">
                <p class="text-gray-400 text-xs uppercase mb-1">Late</p>
                <h2 class="text-2xl font-bold text-orange-500">{{ $late }}</h2>
                <p class="text-xs text-orange-400">Today</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-xl shadow-sm border border-purple-100">
                <p class="text-gray-400 text-xs uppercase mb-1">Working Days</p>
                <h2 class="text-2xl font-bold text-purple-600">{{ $totalWorkingDays }}</h2>
            </div>
        </div>

        {{-- TABS --}}
        <div class="mb-5 border-b border-gray-200 bg-white rounded-t-xl px-2">
            <nav class="flex">
                <button onclick="showTab('daily')"   id="tab-daily"   class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 -mb-px">📋 Daily Attendance</button>
                <button onclick="showTab('monthly')" id="tab-monthly" class="tab-btn px-5 py-3 text-sm text-gray-500 border-b-2 border-transparent hover:text-gray-700 -mb-px">📊 Monthly Summary</button>
                <button onclick="showTab('salary')"  id="tab-salary"  class="tab-btn px-5 py-3 text-sm text-gray-500 border-b-2 border-transparent hover:text-gray-700 -mb-px">💰 Salary Sheet</button>
            </nav>
        </div>

        {{-- TAB 1: DAILY ATTENDANCE --}}
        <div id="panel-daily" class="tab-panel">
            <div class="bg-white border rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b flex justify-between items-center flex-wrap gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">
                            📅 {{ $isCurrentMonth ? \Carbon\Carbon::now()->format('l, d F Y') : \Carbon\Carbon::create($year,$month)->format('F Y').' — Past Month' }}
                            <span class="text-xs text-gray-400 ml-2">({{ $activeBranch }})</span>
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">Office Start: 9:30 AM</p>
                    </div>
                    <button onclick="openAddEmployeeModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700">👤➕ Add Employee</button>
                </div>
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <input type="hidden" name="branch" value="{{ $activeBranch }}">
                    <input type="hidden" name="month"  value="{{ $month }}">
                    <input type="hidden" name="year"   value="{{ $year }}">
                    <input type="hidden" name="date"   value="{{ now()->format('Y-m-d') }}">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs text-gray-400 font-semibold">#</th>
                                    <th class="px-3 py-3 text-left text-xs text-gray-400 font-semibold">EMPLOYEE</th>
                                    <th class="px-3 py-3 text-left text-xs text-gray-400 font-semibold">DESIGNATION</th>
                                    <th class="px-3 py-3 text-center text-xs text-gray-400 font-semibold">STATUS</th>
                                    <th class="px-3 py-3 text-center text-xs text-gray-400 font-semibold">CHECK IN</th>
                                    <th class="px-3 py-3 text-center text-xs text-gray-400 font-semibold">CHECK OUT</th>
                                    <th class="px-3 py-3 text-center text-xs text-orange-400 font-semibold">LATE</th>
                                    <th class="px-3 py-3 text-center text-xs text-indigo-400 font-semibold">OT HRS</th>
                                    <th class="px-3 py-3 text-center text-xs text-blue-400 font-semibold">NIGHT</th>
                                    <th class="px-3 py-3 text-center text-xs text-red-400 font-semibold">ADVANCE</th>
                                    <th class="px-3 py-3 text-center text-xs text-gray-400 font-semibold">NOTES</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($useEmployees as $i => $emp)
                                @php
                                    $att     = $todayAttendance[$emp->id] ?? null;
                                    $status  = $att?->status ?? 'present';
                                    $checkIn = $att?->check_in ?? '';
                                    $lateAuto= $checkIn && \Carbon\Carbon::parse($checkIn)->gt($officeStart) ? 1 : 0;
                                    $lateVal = $att?->late ?? $lateAuto;
                                @endphp
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="px-3 py-2.5 text-gray-300 text-xs">{{ $i+1 }}</td>
                                    <td class="px-3 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-blue-700 font-bold text-xs shrink-0 border border-blue-200">{{ strtoupper(substr($emp->name,0,2)) }}</div>
                                            <div>
                                                <div class="font-semibold text-gray-800 text-sm">{{ $emp->name }}</div>
                                                <div class="text-xs text-gray-400">ID: {{ $emp->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-gray-500">{{ $emp->department }}</td>
                                    <td class="px-3 py-2.5 text-center">
                                        <select name="attendance[{{ $emp->id }}]" onchange="updateRow(this)"
                                            class="border rounded-lg px-2 py-1.5 text-xs font-semibold w-28 {{ $status=='present'?'text-green-700 bg-green-50 border-green-200':($status=='absent'?'text-red-600 bg-red-50 border-red-200':'text-yellow-700 bg-yellow-50 border-yellow-200') }}">
                                            <option value="present" {{ $status=='present'?'selected':'' }}>✅ Present</option>
                                            <option value="absent"  {{ $status=='absent' ?'selected':'' }}>❌ Absent</option>
                                            <option value="leave"   {{ $status=='leave'  ?'selected':'' }}>🟡 Leave</option>
                                            <option value="halfday" {{ $status=='halfday'?'selected':'' }}>🔸 Half Day</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="time" name="checkin[{{ $emp->id }}]" value="{{ $checkIn }}" onchange="checkLate(this, {{ $emp->id }})"
                                            class="border border-gray-200 rounded px-2 py-1 text-xs {{ $status=='absent'?'opacity-30 pointer-events-none':'' }}">
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="time" name="checkout[{{ $emp->id }}]" value="{{ $att?->check_out??'' }}"
                                            class="border border-gray-200 rounded px-2 py-1 text-xs {{ $status=='absent'?'opacity-30 pointer-events-none':'' }}">
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="hidden" name="late[{{ $emp->id }}]" id="late-{{ $emp->id }}" value="{{ $lateVal }}">
                                        <span id="late-badge-{{ $emp->id }}"
                                            class="{{ $lateVal ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-300' }} px-2 py-0.5 rounded text-xs font-semibold cursor-pointer"
                                            onclick="toggleLate({{ $emp->id }})">{{ $lateVal ? 'LATE' : '—' }}</span>
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="number" name="overtime[{{ $emp->id }}]" min="0" max="12" step="0.5" value="{{ $att?->overtime??0 }}"
                                            class="border border-gray-200 rounded px-1 py-1 w-14 text-center text-xs">
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="number" name="night[{{ $emp->id }}]" min="0" value="{{ $att?->night??0 }}"
                                            class="border border-gray-200 rounded px-1 py-1 w-14 text-center text-xs">
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="number" name="advance[{{ $emp->id }}]" min="0" value="{{ $att?->advance??0 }}"
                                            class="border border-gray-200 rounded px-1 py-1 w-20 text-center text-xs">
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="text" name="notes[{{ $emp->id }}]" value="{{ $att?->notes??'' }}" placeholder="..."
                                            class="border border-gray-200 rounded px-2 py-1 w-24 text-xs text-gray-500">
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-12 text-gray-300">
                                        <div class="text-3xl mb-2">👥</div>
                                        <p class="mb-3">Koi employee nahi — Add karein</p>
                                        <button type="button" onclick="openAddEmployeeModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">👤➕ Add Employee</button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-between items-center rounded-b-xl">
                        <button type="button" onclick="markAll('present')" class="px-4 py-1.5 bg-green-50 text-green-700 border border-green-200 rounded-lg text-xs font-semibold hover:bg-green-100">✅ All Present</button>
                        <button type="submit" class="bg-blue-600 text-white px-8 py-2 rounded-lg font-semibold text-sm hover:bg-blue-700">💾 Save Attendance</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TAB 2: MONTHLY SUMMARY --}}
        <div id="panel-monthly" class="tab-panel hidden">
            <div class="bg-white border rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b flex justify-between items-center flex-wrap gap-3">
                    <h2 class="text-base font-semibold text-gray-800">
                        📊 Monthly Summary — {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}
                        <span class="text-xs text-gray-400">({{ $activeBranch }})</span>
                    </h2>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-orange-500 font-medium">⏰ {{ $lateToAbsentRule }} late = 1 absent</span>
                        <button onclick="openAddEmployeeModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700">👤➕ Add Employee</button>
                    </div>
                </div>
                @if($isPastMonth)
                <div class="px-6 py-2.5 bg-amber-50 border-b border-amber-200 text-xs text-amber-800">
                    ✏️ <strong>Past Month:</strong> Net Salary manually edit kar sakte ho
                </div>
                @endif
                <form method="POST" action="{{ route('monthly.summary.save') }}">
                    @csrf
                    <input type="hidden" name="branch" value="{{ $activeBranch }}">
                    <input type="hidden" name="month"  value="{{ $month }}">
                    <input type="hidden" name="year"   value="{{ $year }}">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs text-gray-400 font-semibold">EMPLOYEE</th>
                                    <th class="px-3 py-3 text-center text-xs text-gray-500 font-semibold">BASIC</th>
                                    <th class="px-3 py-3 text-center text-xs text-blue-400 font-semibold">ALLOWANCES</th>
                                    <th class="px-3 py-3 text-center text-xs text-green-500 font-semibold">✅ P</th>
                                    <th class="px-3 py-3 text-center text-xs text-red-400 font-semibold">❌ A</th>
                                    <th class="px-3 py-3 text-center text-xs text-yellow-400 font-semibold">🟡 L</th>
                                    <th class="px-3 py-3 text-center text-xs text-orange-400 font-semibold">⏰ Late</th>
                                    <th class="px-3 py-3 text-center text-xs text-blue-400 font-semibold">🌙 Night</th>
                                    <th class="px-3 py-3 text-center text-xs text-indigo-400 font-semibold">⏱ OT</th>
                                    <th class="px-3 py-3 text-center text-xs text-red-400 font-semibold">💳 Adv</th>
                                    <th class="px-3 py-3 text-center text-xs text-orange-500 font-semibold">GROSS</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold {{ $isPastMonth ? 'text-amber-600 bg-amber-50' : 'text-purple-500' }}">
                                        {{ $isPastMonth ? '✏️ NET' : 'NET' }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($salaryData as $row)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2.5">
                                        <input type="hidden" name="emp_ids[]" value="{{ $row->emp->id }}">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs shrink-0">{{ strtoupper(substr($row->emp->name,0,2)) }}</div>
                                            <div>
                                                <div class="font-semibold text-gray-800 text-sm">{{ $row->emp->name }}</div>
                                                <div class="text-xs text-gray-400">{{ $row->emp->department }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-center text-xs font-medium text-gray-700">{{ $row->basic > 0 ? number_format($row->basic) : '—' }}</td>
                                    <td class="px-3 py-2.5 text-center text-xs text-blue-600 font-medium">
                                        @if($row->totalAllowances > 0)
                                            <span title="Bike: {{ number_format($row->bikeAllow) }} | Mobile: {{ number_format($row->mobileAllow) }} | Other: {{ number_format($row->otherAllow) }} | Comm: {{ number_format($row->commission) }}" class="cursor-help">
                                                +{{ number_format($row->totalAllowances) }} ℹ️
                                            </span>
                                        @else <span class="text-gray-300">—</span> @endif
                                    </td>
                                    <td class="px-2 py-2.5 text-center">
                                        <input type="number" name="present[{{ $row->emp->id }}]" min="0" max="{{ $totalWorkingDays }}" value="{{ $row->presentDays }}"
                                            class="w-14 border border-green-200 bg-green-50 rounded px-1 py-1 text-center text-xs text-green-700 font-bold">
                                    </td>
                                    <td class="px-2 py-2.5 text-center">
                                        <input type="number" name="absent[{{ $row->emp->id }}]" min="0" value="{{ $row->absentDays }}"
                                            class="w-14 border border-red-200 bg-red-50 rounded px-1 py-1 text-center text-xs text-red-600 font-bold">
                                    </td>
                                    <td class="px-2 py-2.5 text-center">
                                        <input type="number" name="leave[{{ $row->emp->id }}]" min="0" value="{{ $row->leaveDays }}"
                                            class="w-14 border border-yellow-200 bg-yellow-50 rounded px-1 py-1 text-center text-xs text-yellow-600 font-bold">
                                    </td>
                                    <td class="px-2 py-2.5 text-center">
                                        <input type="number" name="late[{{ $row->emp->id }}]" min="0" value="{{ $row->lateDays }}"
                                            class="w-14 border border-orange-200 bg-orange-50 rounded px-1 py-1 text-center text-xs text-orange-600 font-bold">
                                    </td>
                                    <td class="px-2 py-2.5 text-center">
                                        <input type="number" name="night[{{ $row->emp->id }}]" min="0" value="{{ $row->nightDuties }}"
                                            class="w-14 border border-blue-200 bg-blue-50 rounded px-1 py-1 text-center text-xs text-blue-600 font-bold">
                                    </td>
                                    <td class="px-2 py-2.5 text-center">
                                        <input type="number" name="overtime[{{ $row->emp->id }}]" min="0" step="0.5" value="{{ $row->overtime }}"
                                            class="w-14 border border-indigo-200 bg-indigo-50 rounded px-1 py-1 text-center text-xs text-indigo-600 font-bold">
                                    </td>
                                    <td class="px-2 py-2.5 text-center">
                                        <input type="number" name="advance[{{ $row->emp->id }}]" min="0" value="{{ $row->advance }}"
                                            class="w-20 border border-red-200 bg-red-50 rounded px-1 py-1 text-center text-xs text-red-500 font-bold">
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-bold text-orange-600 text-xs">{{ $row->grossSalary > 0 ? number_format($row->grossSalary) : '—' }}</td>
                                    <td class="px-2 py-2.5 text-center">
                                        @if($isPastMonth)
                                            <input type="number" name="manual_net[{{ $row->emp->id }}]"
                                                value="{{ $row->manualNet !== null ? $row->manualNet : ($row->netSalary > 0 ? $row->netSalary : '') }}"
                                                placeholder="Amount..."
                                                class="w-28 border-2 border-amber-300 bg-amber-50 rounded-lg px-2 py-1.5 text-center text-sm font-bold text-amber-800 outline-none">
                                        @else
                                            <span class="font-bold text-sm {{ $row->netSalary > 0 ? 'text-purple-700' : 'text-gray-300' }}">
                                                {{ $row->netSalary > 0 ? 'Rs. '.number_format($row->netSalary) : '—' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center py-12 text-gray-300">
                                        <div class="text-3xl mb-2">📊</div>
                                        <p class="mb-3">Koi employee nahi</p>
                                        <button type="button" onclick="openAddEmployeeModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">👤➕ Add Employee</button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-between items-center rounded-b-xl">
                        <button type="button" onclick="openAddEmployeeModal()" class="px-4 py-2 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm font-semibold hover:bg-green-100">👤➕ Add Employee</button>
                        <button type="submit" class="bg-blue-600 text-white px-8 py-2 rounded-lg font-semibold text-sm hover:bg-blue-700">💾 Save Monthly Data</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TAB 3: SALARY SHEET --}}
        <div id="panel-salary" class="tab-panel hidden">
            <div class="grid grid-cols-3 gap-4 mb-5">
                <div class="bg-white border rounded-xl p-5 shadow-sm">
                    <p class="text-gray-400 text-xs uppercase mb-1">Total Gross</p>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $totalGross > 0 ? 'Rs. '.number_format($totalGross) : '—' }}</h2>
                </div>
                <div class="bg-red-50 border border-red-100 rounded-xl p-5 shadow-sm">
                    <p class="text-gray-400 text-xs uppercase mb-1">Total Advances</p>
                    <h2 class="text-2xl font-bold text-red-500">{{ $totalAdvSum > 0 ? 'Rs. '.number_format($totalAdvSum) : '—' }}</h2>
                </div>
                <div class="bg-green-50 border border-green-100 rounded-xl p-5 shadow-sm">
                    <p class="text-gray-400 text-xs uppercase mb-1">Net Payable</p>
                    <h2 class="text-2xl font-bold text-green-600">{{ $totalNet > 0 ? 'Rs. '.number_format($totalNet) : '—' }}</h2>
                </div>
            </div>
            <div class="bg-white border rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h2 class="text-base font-semibold text-gray-800">
                        💰 Salary Sheet — {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}
                        <span class="text-xs text-gray-400">({{ $activeBranch }})</span>
                    </h2>
                    <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">🖨️ Print</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs text-gray-400 font-semibold">#</th>
                                <th class="px-3 py-3 text-left text-xs text-gray-400 font-semibold">EMPLOYEE</th>
                                <th class="px-3 py-3 text-center text-xs text-gray-500 font-semibold">BASIC</th>
                                <th class="px-3 py-3 text-center text-xs text-blue-400 font-semibold">ALLOWANCES</th>
                                <th class="px-3 py-3 text-center text-xs text-green-500 font-semibold">P</th>
                                <th class="px-3 py-3 text-center text-xs text-red-400 font-semibold">A</th>
                                <th class="px-3 py-3 text-center text-xs text-blue-400 font-semibold">NIGHT</th>
                                <th class="px-3 py-3 text-center text-xs text-indigo-400 font-semibold">OT PAY</th>
                                <th class="px-3 py-3 text-center text-xs text-orange-500 font-semibold">GROSS</th>
                                <th class="px-3 py-3 text-center text-xs text-red-500 font-semibold">ADV</th>
                                <th class="px-3 py-3 text-center text-xs text-pink-500 font-semibold">DEDUCT</th>
                                <th class="px-3 py-3 text-center text-xs text-green-600 font-semibold">NET SALARY</th>
                                <th class="px-3 py-3 text-center text-xs text-gray-400 font-semibold">SIGN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($salaryData as $i => $row)
                            <tr class="hover:bg-gray-50 {{ $row->netSalary < 0 ? 'bg-red-50' : '' }}">
                                <td class="px-3 py-2.5 text-gray-300 text-xs">{{ $i+1 }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs shrink-0">{{ strtoupper(substr($row->emp->name,0,2)) }}</div>
                                        <div>
                                            <div class="font-semibold text-gray-800 text-xs">{{ $row->emp->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $row->emp->department }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-center text-xs font-medium text-gray-700">{{ $row->basic > 0 ? number_format($row->basic) : '—' }}</td>
                                <td class="px-3 py-2.5 text-center text-xs text-blue-600">
                                    @if($row->totalAllowances > 0)
                                        <span title="Bike: {{ number_format($row->bikeAllow) }} | Mobile: {{ number_format($row->mobileAllow) }} | Other: {{ number_format($row->otherAllow) }} | Comm: {{ number_format($row->commission) }}" class="cursor-help">
                                            +{{ number_format($row->totalAllowances) }}
                                        </span>
                                    @else <span class="text-gray-300">—</span> @endif
                                </td>
                                <td class="px-3 py-2.5 text-center"><span class="px-1.5 py-0.5 {{ $row->presentDays > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }} rounded text-xs font-bold">{{ $row->presentDays }}</span></td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="px-1.5 py-0.5 {{ $row->effectiveAbsent > 0 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-300' }} rounded text-xs font-bold">
                                        {{ $row->effectiveAbsent }}
                                        @if($row->lateAbsents > 0)<span class="text-orange-500">(+{{ $row->lateAbsents }}L)</span>@endif
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-center text-blue-600 text-xs">{{ $row->nightPay > 0 ? '+'.number_format($row->nightPay) : '—' }}</td>
                                <td class="px-3 py-2.5 text-center text-indigo-600 text-xs">{{ $row->overtimePay > 0 ? '+'.number_format($row->overtimePay) : '—' }}</td>
                                <td class="px-3 py-2.5 text-center font-bold text-orange-600 text-xs">{{ $row->grossSalary > 0 ? number_format($row->grossSalary) : '—' }}</td>
                                <td class="px-3 py-2.5 text-center text-red-500 text-xs">{{ $row->advance > 0 ? '−'.number_format($row->advance) : '—' }}</td>
                                <td class="px-3 py-2.5 text-center text-pink-600 text-xs">
                                    @php $d = $row->lateDeductTotal + $row->absentDeductTotal; @endphp
                                    {{ $d > 0 ? '−'.number_format($d) : '—' }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    @if($isPastMonth && $row->manualNet !== null)
                                        <span class="font-bold text-sm text-green-600">Rs. {{ number_format($row->manualNet) }}</span>
                                    @elseif($row->netSalary > 0)
                                        <span class="font-bold text-sm text-green-600">Rs. {{ number_format($row->netSalary) }}</span>
                                    @elseif($row->netSalary < 0)
                                        <span class="font-bold text-sm text-red-600">Rs. {{ number_format($row->netSalary) }}</span>
                                    @else
                                        <span class="text-gray-300 text-xs italic">Pending</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-center"><div class="border border-gray-300 rounded h-7 w-20 inline-block"></div></td>
                            </tr>
                            @empty
                            <tr><td colspan="13" class="text-center py-12 text-gray-300"><div class="text-3xl mb-2">💰</div><p>Koi data nahi</p></td></tr>
                            @endforelse
                            @if($salaryData->count() > 0)
                            <tr class="bg-gray-800 text-white text-xs">
                                <td colspan="2" class="px-3 py-3 font-bold">TOTAL — {{ $useEmployees->count() }} employees</td>
                                <td class="px-3 py-3 text-center text-gray-300">{{ $salaryData->sum('basic') > 0 ? number_format($salaryData->sum('basic')) : '—' }}</td>
                                <td class="px-3 py-3 text-center text-blue-300">{{ $salaryData->sum('totalAllowances') > 0 ? '+'.number_format($salaryData->sum('totalAllowances')) : '—' }}</td>
                                <td colspan="4" class="text-center text-gray-500">—</td>
                                <td class="px-3 py-3 text-center font-bold text-yellow-300">{{ $totalGross > 0 ? number_format($totalGross) : '—' }}</td>
                                <td class="px-3 py-3 text-center font-bold text-red-300">{{ $totalAdvSum > 0 ? '−'.number_format($totalAdvSum) : '—' }}</td>
                                <td class="px-3 py-3 text-center text-pink-300">—</td>
                                <td class="px-3 py-3 text-center font-bold text-green-300 text-sm">{{ $totalNet > 0 ? 'Rs. '.number_format($totalNet) : 'Pending' }}</td>
                                <td></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t bg-gray-50 text-xs text-gray-400 flex justify-between rounded-b-xl">
                    <span>Generated: {{ now()->format('d M Y, h:i A') }}</span>
                    <span>{{ $activeBranch }} · {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}</span>
                </div>
            </div>
        </div>

    </main>
</div>

{{-- ══ ADD EMPLOYEE MODAL ══ --}}
<div id="addEmployeeModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAddEmployeeModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden max-h-[92vh] flex flex-col">

        <div class="bg-green-600 text-white px-6 py-4 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-bold">👤➕ Employee Add Karein</h3>
                <p class="text-xs text-green-100 mt-0.5">{{ $activeBranch }} — {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}</p>
            </div>
            <button onclick="closeAddEmployeeModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center font-bold text-lg">×</button>
        </div>

        <div class="overflow-y-auto flex-1">
            <form method="POST" action="{{ route('employee.store') }}">
                @csrf
                <input type="hidden" name="branch" value="{{ $activeBranch }}">
                <input type="hidden" name="month"  value="{{ $month }}">
                <input type="hidden" name="year"   value="{{ $year }}">

                {{-- SECTION 1: Basic Info --}}
                <div class="px-6 pt-5 pb-4">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold">1</span>
                        Employee Info
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Name <span class="text-red-400">*</span></label>
                            <input type="text" name="name" required placeholder="Ali Hassan"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-green-400 focus:ring-1 focus:ring-green-100 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Designation</label>
                            <input type="text" name="designation" placeholder="Manager"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-green-400 outline-none">
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Salary & Allowances --}}
                <div class="px-6 py-4 border-t border-dashed border-gray-200">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">2</span>
                        Salary & Allowances <span class="text-gray-400 font-normal normal-case">(fixed monthly amounts)</span>
                    </p>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">💰 Basic Salary (Rs.)</label>
                            <input type="number" name="basic_salary" id="newBasic" min="0" placeholder="25000"
                                class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:border-green-400 outline-none"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-blue-600 mb-1">🚲 Bike Allowance</label>
                            <input type="number" name="bike_allowance" id="newBike" min="0" placeholder="0"
                                class="w-full border border-blue-200 bg-blue-50 rounded-lg px-3 py-2 text-sm focus:border-blue-400 outline-none"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-blue-600 mb-1">📱 Mobile Allowance</label>
                            <input type="number" name="mobile_allowance" id="newMobile" min="0" placeholder="0"
                                class="w-full border border-blue-200 bg-blue-50 rounded-lg px-3 py-2 text-sm focus:border-blue-400 outline-none"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-blue-600 mb-1">📦 Other Allowance</label>
                            <input type="number" name="other_allowance" id="newOther" min="0" placeholder="0"
                                class="w-full border border-blue-200 bg-blue-50 rounded-lg px-3 py-2 text-sm focus:border-blue-400 outline-none"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-blue-600 mb-1">💼 Commission</label>
                            <input type="number" name="commission" id="newCommission" min="0" placeholder="0"
                                class="w-full border border-blue-200 bg-blue-50 rounded-lg px-3 py-2 text-sm focus:border-blue-400 outline-none"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">🌙 Night Rate (per shift)</label>
                            <input type="number" name="night_rate" id="newNightRate" min="0" value="500"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-green-400 outline-none"
                                oninput="calcNewNet()">
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Deduction Rules --}}
                <div class="px-6 py-4 border-t border-dashed border-gray-200">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center text-red-600 font-bold">3</span>
                        Deduction Rules <span class="text-gray-400 font-normal normal-case">(per occurrence)</span>
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-red-500 mb-1">⏰ Late Deduction (Rs. per late)</label>
                            <input type="number" name="late_deduction" id="newLateDeduct" min="0" placeholder="0"
                                class="w-full border border-red-200 bg-red-50 rounded-lg px-3 py-2 text-sm focus:border-red-400 outline-none"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-red-500 mb-1">❌ Absent Deduction (Rs. per absent)</label>
                            <input type="number" name="absent_deduction" id="newAbsentDeduct" min="0" placeholder="0"
                                class="w-full border border-red-200 bg-red-50 rounded-lg px-3 py-2 text-sm focus:border-red-400 outline-none"
                                oninput="calcNewNet()">
                        </div>
                    </div>
                </div>

                {{-- SECTION 4: This Month's Data --}}
                <div class="px-6 py-4 border-t border-dashed border-gray-200">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold">4</span>
                        {{ \Carbon\Carbon::create($year,$month)->format('F Y') }} Ka Data
                        <span class="text-gray-400 font-normal normal-case">(agar hai toh)</span>
                    </p>
                    <div class="grid grid-cols-4 gap-2 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-green-600 mb-1">✅ Present</label>
                            <input type="number" name="present_days" id="newPresent" min="0" value="0"
                                class="w-full border border-green-200 bg-green-50 rounded-lg px-2 py-2 text-sm text-center text-green-700 font-bold"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-red-500 mb-1">❌ Absent</label>
                            <input type="number" name="absent_days" id="newAbsent" min="0" value="0"
                                class="w-full border border-red-200 bg-red-50 rounded-lg px-2 py-2 text-sm text-center text-red-600 font-bold"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-yellow-500 mb-1">🟡 Leave</label>
                            <input type="number" name="leave_days" min="0" value="0"
                                class="w-full border border-yellow-200 bg-yellow-50 rounded-lg px-2 py-2 text-sm text-center text-yellow-600 font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-orange-500 mb-1">⏰ Late</label>
                            <input type="number" name="late_count" id="newLate" min="0" value="0"
                                class="w-full border border-orange-200 bg-orange-50 rounded-lg px-2 py-2 text-sm text-center text-orange-600 font-bold"
                                oninput="calcNewNet()">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-blue-500 mb-1">🌙 Night Shifts</label>
                            <input type="number" name="night_duties" id="newNightDuties" min="0" value="0"
                                class="w-full border border-blue-200 bg-blue-50 rounded-lg px-2 py-2 text-sm text-center text-blue-600 font-bold"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-indigo-500 mb-1">⏱ Overtime Hours</label>
                            <input type="number" name="overtime_hours" id="newOT" min="0" step="0.5" value="0"
                                class="w-full border border-indigo-200 bg-indigo-50 rounded-lg px-2 py-2 text-sm text-center text-indigo-600 font-bold"
                                oninput="calcNewNet()">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-red-500 mb-1">💳 Advance</label>
                            <input type="number" name="advance" id="newAdvance" min="0" value="0"
                                class="w-full border border-red-200 bg-red-50 rounded-lg px-2 py-2 text-sm text-center text-red-600 font-bold"
                                oninput="calcNewNet()">
                        </div>
                    </div>
                </div>

                {{-- NET SALARY PREVIEW --}}
                <div class="px-6 py-4 border-t border-dashed border-gray-200">
                    <div class="bg-gradient-to-r from-gray-50 to-purple-50 border border-purple-100 rounded-xl px-4 py-3">
                        <div class="grid grid-cols-4 gap-2 text-center text-xs mb-3 pb-3 border-b border-purple-100">
                            <div>
                                <div class="text-gray-400 mb-1">Earned Basic</div>
                                <div id="previewEarned" class="font-bold text-gray-700">—</div>
                            </div>
                            <div>
                                <div class="text-blue-400 mb-1">+ Allowances</div>
                                <div id="previewAllowances" class="font-bold text-blue-600">—</div>
                            </div>
                            <div>
                                <div class="text-orange-400 mb-1">= Gross</div>
                                <div id="previewGross" class="font-bold text-orange-600">—</div>
                            </div>
                            <div>
                                <div class="text-red-400 mb-1">− Deductions</div>
                                <div id="previewDeductions" class="font-bold text-red-500">—</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-600">💵 Estimated Net Salary:</span>
                            <span id="newEmpNetPreview" class="text-xl font-bold text-purple-700">—</span>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="closeAddEmployeeModal()" class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-8 py-2 rounded-lg font-semibold text-sm hover:bg-green-700 shadow-sm transition">💾 Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- LATE RULE MODAL --}}
<div id="lateRuleModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" onclick="document.getElementById('lateRuleModal').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-80 z-10">
        <h3 class="text-base font-bold text-gray-800 mb-1">⚙️ Late to Absent Rule</h3>
        <p class="text-xs text-gray-500 mb-4">Kitne baar late = 1 absent?</p>
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            <div class="flex items-center gap-3 mb-5">
                <input type="number" name="late_to_absent" min="1" max="10" value="{{ $lateToAbsentRule }}"
                    class="w-20 border-2 border-indigo-300 rounded-lg px-3 py-2 text-center text-xl font-bold text-indigo-700">
                <span class="text-sm text-gray-600">baar late = 1 absent</span>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-semibold">Save</button>
                <button type="button" onclick="document.getElementById('lateRuleModal').classList.add('hidden')" class="flex-1 border border-gray-300 py-2 rounded-lg text-sm text-gray-600">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const LATE_RULE     = {{ $lateToAbsentRule }};
const IS_PAST_MONTH = {{ $isPastMonth ? 'true' : 'false' }};

// ── TABS ──
function showTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('border-blue-600','text-blue-600');
        b.classList.add('border-transparent','text-gray-500');
    });
    document.getElementById('panel-' + name).classList.remove('hidden');
    const btn = document.getElementById('tab-' + name);
    btn.classList.add('border-blue-600','text-blue-600');
    btn.classList.remove('border-transparent','text-gray-500');
}

// ── DAILY ATTENDANCE ──
function updateRow(sel) {
    const row = sel.closest('tr');
    const isAbsent = sel.value === 'absent';
    row.querySelectorAll('input[type=time]').forEach(i => {
        i.classList.toggle('opacity-30', isAbsent);
        i.classList.toggle('pointer-events-none', isAbsent);
    });
}
function checkLate(input, empId) {
    const late = input.value && input.value > '09:30' ? 1 : 0;
    document.getElementById('late-' + empId).value = late;
    const badge = document.getElementById('late-badge-' + empId);
    badge.textContent = late ? 'LATE' : '—';
    badge.className = late
        ? 'bg-orange-100 text-orange-600 px-2 py-0.5 rounded text-xs font-semibold cursor-pointer'
        : 'bg-gray-100 text-gray-300 px-2 py-0.5 rounded text-xs font-semibold cursor-pointer';
}
function toggleLate(empId) {
    const inp   = document.getElementById('late-' + empId);
    const badge = document.getElementById('late-badge-' + empId);
    inp.value   = inp.value == '1' ? '0' : '1';
    badge.textContent = inp.value == '1' ? 'LATE' : '—';
    badge.className   = inp.value == '1'
        ? 'bg-orange-100 text-orange-600 px-2 py-0.5 rounded text-xs font-semibold cursor-pointer'
        : 'bg-gray-100 text-gray-300 px-2 py-0.5 rounded text-xs font-semibold cursor-pointer';
}
function markAll(status) {
    document.querySelectorAll('select[name^="attendance"]').forEach(s => { s.value = status; updateRow(s); });
}

// ── ADD EMPLOYEE MODAL ──
function openAddEmployeeModal()  { document.getElementById('addEmployeeModal').classList.remove('hidden'); calcNewNet(); }
function closeAddEmployeeModal() { document.getElementById('addEmployeeModal').classList.add('hidden'); }

// ── NET SALARY LIVE CALCULATOR ──
function calcNewNet() {
    const basic        = parseFloat(document.getElementById('newBasic')?.value)        || 0;
    const bike         = parseFloat(document.getElementById('newBike')?.value)         || 0;
    const mobile       = parseFloat(document.getElementById('newMobile')?.value)       || 0;
    const other        = parseFloat(document.getElementById('newOther')?.value)        || 0;
    const commission   = parseFloat(document.getElementById('newCommission')?.value)   || 0;
    const nightRate    = parseFloat(document.getElementById('newNightRate')?.value)    || 500;
    const lateDeduct   = parseFloat(document.getElementById('newLateDeduct')?.value)   || 0;
    const absentDeduct = parseFloat(document.getElementById('newAbsentDeduct')?.value) || 0;
    const present      = parseInt(document.getElementById('newPresent')?.value)        || 0;
    const absent       = parseInt(document.getElementById('newAbsent')?.value)         || 0;
    const late         = parseInt(document.getElementById('newLate')?.value)           || 0;
    const nightDuties  = parseInt(document.getElementById('newNightDuties')?.value)    || 0;
    const ot           = parseFloat(document.getElementById('newOT')?.value)           || 0;
    const advance      = parseFloat(document.getElementById('newAdvance')?.value)      || 0;

    const preview    = document.getElementById('newEmpNetPreview');
    const prevEarned = document.getElementById('previewEarned');
    const prevAllow  = document.getElementById('previewAllowances');
    const prevGross  = document.getElementById('previewGross');
    const prevDeduct = document.getElementById('previewDeductions');

    if (basic <= 0) {
        [preview, prevEarned, prevAllow, prevGross, prevDeduct].forEach(el => { if(el) el.textContent = '—'; });
        if (preview) preview.className = 'text-xl font-bold text-gray-400';
        return;
    }

    const perDay      = basic / 26;
    const perHour     = perDay / 8;
    const lateAbsents = Math.floor(late / Math.max(1, LATE_RULE));

    // Earnings
    const earnedBasic     = Math.max(0, present - lateAbsents) * perDay;
    const totalAllowances = bike + mobile + other + commission;
    const nightPay        = nightDuties * nightRate;
    const otPay           = ot * perHour;
    const gross           = earnedBasic + totalAllowances + nightPay + otPay;

    // Deductions
    const effectAbsent      = absent + lateAbsents;
    const lateDeductTotal   = late * lateDeduct;
    const absentDeductTotal = effectAbsent * absentDeduct;
    const totalDeductions   = lateDeductTotal + absentDeductTotal + advance;

    const net = gross - totalDeductions;

    if (prevEarned) prevEarned.textContent = 'Rs ' + Math.round(earnedBasic).toLocaleString();
    if (prevAllow)  prevAllow.textContent  = totalAllowances > 0 ? '+Rs ' + Math.round(totalAllowances).toLocaleString() : '—';
    if (prevGross)  prevGross.textContent  = 'Rs ' + Math.round(gross).toLocaleString();
    if (prevDeduct) prevDeduct.textContent = totalDeductions > 0 ? '−Rs ' + Math.round(totalDeductions).toLocaleString() : '—';
    if (preview) {
        preview.textContent = 'Rs. ' + Math.round(net).toLocaleString();
        preview.className   = 'text-xl font-bold ' + (net >= 0 ? 'text-purple-700' : 'text-red-500');
    }
}
</script>

<style>
@media print {
    aside, nav, .tab-btn, button, select { display: none !important; }
    .tab-panel { display: none !important; }
    #panel-salary { display: block !important; }
    #addEmployeeModal, #lateRuleModal { display: none !important; }
    body { background: white !important; }
}
</style>
</x-layout>
BLADEEOF
echo "Done"