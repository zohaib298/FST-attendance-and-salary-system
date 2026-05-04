<x-layout>

@php
    $useEmployees    = $employees ?? collect();
    $daysInMonth     = \Carbon\Carbon::create($year, $month)->daysInMonth;
    $workingDays     = collect();
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = \Carbon\Carbon::create($year, $month, $d);
        if (!$date->isSunday()) $workingDays->push($date);
    }
    $totalWorkingDays = $workingDays->count();
    $isPastMonth      = \Carbon\Carbon::create($year, $month)->lt(now()->startOfMonth());
    $isCurrentMonth   = ($month == now()->month && $year == now()->year);
    $allAttendances   = $allAttendances  ?? collect();
    $todayAttendance  = $todayAttendance ?? collect();
    $present = $todayAttendance->where('status', 'present')->count();
    $absent  = $todayAttendance->where('status', 'absent')->count();
    $leave   = $todayAttendance->where('status', 'leave')->count();
    $late    = $todayAttendance->where('late', 1)->count();
    $prevMonth = $month - 1 < 1  ? 12 : $month - 1;
    $prevYear  = $month - 1 < 1  ? $year - 1 : $year;
    $nextMonth = $month + 1 > 12 ? 1  : $month + 1;
    $nextYear  = $month + 1 > 12 ? $year + 1 : $year;
    $officeStart      = \Carbon\Carbon::createFromTime(9, 30, 0);
    $lateToAbsentRule = $settings['late_to_absent'] ?? 3;
@endphp

<style>
:root {
    --blue-primary: #1a56db;
    --blue-dark:    #1e429f;
    --blue-light:   #e8f0fe;
    --blue-mid:     #3f83f8;
    --sidebar-bg:   #1e3a5f;
    --header-bg:    #1a56db;
}

/* TOP HEADER */
.hr-topbar {
    background: var(--blue-primary);
    color: white;
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    border-radius: 0;
}
.hr-topbar h1 {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.hr-topbar .badge-branch {
    background: rgba(255,255,255,0.2);
    color: white;
    font-size: 12px;
    padding: 3px 12px;
    border-radius: 20px;
    font-weight: 500;
}
.hr-topbar .badge-past {
    background: #fbbf24;
    color: #78350f;
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
}

/* MONTH NAV */
.month-nav {
    display: flex;
    align-items: center;
    gap: 0;
}
.month-nav a {
    background: rgba(255,255,255,0.15);
    color: white;
    border: none;
    padding: 7px 14px;
    font-size: 14px;
    text-decoration: none;
    transition: background 0.2s;
}
.month-nav a:first-child { border-radius: 8px 0 0 8px; }
.month-nav a:last-child  { border-radius: 0 8px 8px 0; }
.month-nav a:hover { background: rgba(255,255,255,0.28); }
.month-nav span {
    background: rgba(255,255,255,0.1);
    color: white;
    padding: 7px 20px;
    font-size: 13px;
    font-weight: 700;
    min-width: 150px;
    text-align: center;
    border-left: 1px solid rgba(255,255,255,0.2);
    border-right: 1px solid rgba(255,255,255,0.2);
}

/* BRANCH BAR */
.branch-bar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 10px 24px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.branch-bar .label {
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-right: 4px;
}
.branch-btn {
    padding: 5px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    text-decoration: none;
    background: white;
    transition: all 0.15s;
}
.branch-btn:hover { border-color: var(--blue-mid); color: var(--blue-primary); }
.branch-btn.active { background: var(--blue-primary); color: white; border-color: var(--blue-primary); }

/* STAT CARDS */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
@media(max-width:900px){ .stat-grid{ grid-template-columns: repeat(3,1fr); } }
@media(max-width:600px){ .stat-grid{ grid-template-columns: repeat(2,1fr); } }

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.stat-card .label { font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
.stat-card .value { font-size: 26px; font-weight: 800; line-height: 1; }
.stat-card .sub   { font-size: 11px; margin-top: 4px; }
.stat-card.blue   { border-top: 3px solid var(--blue-primary); }
.stat-card.green  { border-top: 3px solid #10b981; }
.stat-card.red    { border-top: 3px solid #ef4444; }
.stat-card.yellow { border-top: 3px solid #f59e0b; }
.stat-card.orange { border-top: 3px solid #f97316; }
.stat-card.purple { border-top: 3px solid #8b5cf6; }

/* TABS */
.tab-bar {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px 12px 0 0;
    padding: 0 16px;
    display: flex;
    border-bottom: none;
}
.tab-btn {
    padding: 13px 20px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    border: none;
    background: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    transition: all 0.15s;
    margin-bottom: -1px;
}
.tab-btn:hover { color: var(--blue-primary); }
.tab-btn.active { color: var(--blue-primary); border-bottom-color: var(--blue-primary); }

/* TABLE PANEL */
.tab-panel {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0 0 12px 12px;
}
.panel-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    background: #f8faff;
    border-radius: 0;
}
.panel-header h2 { font-size: 14px; font-weight: 700; color: #1e3a5f; margin: 0; }

/* BLUE BUTTON */
.btn-blue {
    background: var(--blue-primary);
    color: white;
    border: none;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s;
}
.btn-blue:hover { background: var(--blue-dark); color: white; }

.btn-outline {
    background: white;
    color: var(--blue-primary);
    border: 1px solid var(--blue-primary);
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-outline:hover { background: var(--blue-light); }

/* DATA TABLE */
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table thead tr { background: #f1f5ff; }
.data-table th { padding: 11px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #4b5563; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 2px solid #e5e7eb; }
.data-table th.center, .data-table td.center { text-align: center; }
.data-table td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
.data-table tr:hover td { background: #f8faff; }
.data-table tr:last-child td { border-bottom: none; }

/* STATUS BADGES */
.badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.badge-green  { background: #d1fae5; color: #065f46; }
.badge-red    { background: #fee2e2; color: #991b1b; }
.badge-yellow { background: #fef3c7; color: #92400e; }
.badge-orange { background: #ffedd5; color: #9a3412; }
.badge-blue   { background: #dbeafe; color: #1e40af; }
.badge-gray   { background: #f3f4f6; color: #6b7280; }

/* STATUS SELECT */
.status-select {
    border-radius: 8px;
    padding: 5px 8px;
    font-size: 12px;
    font-weight: 600;
    border: 1.5px solid;
    outline: none;
    cursor: pointer;
    width: 115px;
}
.status-present { border-color: #10b981; background: #d1fae5; color: #065f46; }
.status-absent  { border-color: #ef4444; background: #fee2e2; color: #991b1b; }
.status-leave   { border-color: #f59e0b; background: #fef3c7; color: #92400e; }
.status-halfday { border-color: #f97316; background: #ffedd5; color: #9a3412; }

/* AVATAR */
.avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, var(--blue-light), #c7d2fe);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 12px; color: var(--blue-primary);
    border: 2px solid #e0e7ff; flex-shrink: 0;
}

/* LATE RULE BANNER */
.late-banner {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    color: #9a3412;
    margin-bottom: 16px;
}

/* PANEL FOOTER */
.panel-footer {
    padding: 12px 20px;
    border-top: 1px solid #f3f4f6;
    background: #f8faff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 0 0 12px 12px;
}

/* INPUT STYLES */
.num-input {
    border: 1.5px solid #e5e7eb;
    border-radius: 6px;
    padding: 4px 6px;
    text-align: center;
    font-size: 12px;
    width: 52px;
    outline: none;
    transition: border-color 0.15s;
}
.num-input:focus { border-color: var(--blue-mid); }

.time-display {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 8px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    border: 1.5px solid; user-select: none;
}

@media print {
    .no-print { display: none !important; }
    * { visibility: hidden; }
    #salary-print-section, #salary-print-section * { visibility: visible !important; }
    #salary-print-section { position: fixed; top: 0; left: 0; width: 100%; }
    @page { margin: 8mm; size: A4 landscape; }
}
</style>

<div class="flex min-h-screen bg-gray-100">
    <x-sidebar />
    <main class="flex-1" style="overflow-x:hidden;">

        {{-- TOP BAR --}}
        <div class="hr-topbar no-print">
            <h1>
                🏢 HR Dashboard
                <span class="badge-branch">{{ $activeBranch }}</span>
                @if($isPastMonth)<span class="badge-past">Past Month</span>@endif
            </h1>
            <div class="month-nav">
                <a href="?branch={{ $activeBranch }}&month={{ $prevMonth }}&year={{ $prevYear }}">&larr;</a>
                <span>{{ \Carbon\Carbon::create($year,$month)->format('F Y') }}</span>
                <a href="?branch={{ $activeBranch }}&month={{ $nextMonth }}&year={{ $nextYear }}">&rarr;</a>
            </div>
        </div>

        {{-- BRANCH SELECTOR --}}
        <div class="branch-bar no-print">
            <span class="label">Branch:</span>
            @foreach($allBranchNames as $br)
                <a href="?branch={{ $br }}&month={{ $month }}&year={{ $year }}"
                   class="branch-btn {{ $activeBranch === $br ? 'active' : '' }}">{{ $br }}</a>
            @endforeach
        </div>

        <div style="padding: 20px;">

            {{-- ALERTS --}}
            @if(session('success'))
            <div class="no-print" style="background:#d1fae5;color:#065f46;padding:10px 16px;border-radius:8px;margin-bottom:12px;font-size:13px;">
                ✅ {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="no-print" style="background:#fee2e2;color:#991b1b;padding:10px 16px;border-radius:8px;margin-bottom:12px;font-size:13px;">
                ❌ {{ session('error') }}
            </div>
            @endif

            {{-- LATE RULE BANNER --}}
            <div class="late-banner no-print">
                <span>⏰ <strong>Late Rule:</strong> Har <strong>{{ $lateToAbsentRule }}</strong> baar late = 1 din absent</span>
                <button onclick="document.getElementById('lateRuleModal').classList.remove('hidden')" class="btn-outline">
                    ⚙️ Change
                </button>
            </div>

            {{-- STATS --}}
            <div class="stat-grid no-print">
                <div class="stat-card blue">
                    <div class="label">Employees</div>
                    <div class="value" style="color:var(--blue-primary);">{{ $useEmployees->count() }}</div>
                </div>
                <div class="stat-card green">
                    <div class="label">Present</div>
                    <div class="value" style="color:#10b981;">{{ $present }}</div>
                    <div class="sub" style="color:#6ee7b7;">Today</div>
                </div>
                <div class="stat-card red">
                    <div class="label">Absent</div>
                    <div class="value" style="color:#ef4444;">{{ $absent }}</div>
                    <div class="sub" style="color:#fca5a5;">Today</div>
                </div>
                <div class="stat-card yellow">
                    <div class="label">Leave</div>
                    <div class="value" style="color:#f59e0b;">{{ $leave }}</div>
                    <div class="sub" style="color:#fcd34d;">Today</div>
                </div>
                <div class="stat-card orange">
                    <div class="label">Late</div>
                    <div class="value" style="color:#f97316;">{{ $late }}</div>
                    <div class="sub" style="color:#fdba74;">Today</div>
                </div>
                <div class="stat-card purple">
                    <div class="label">Working Days</div>
                    <div class="value" style="color:#8b5cf6;">{{ $totalWorkingDays }}</div>
                </div>
            </div>

            {{-- TABS --}}
            <div class="tab-bar no-print">
                <button onclick="showTab('daily')"   id="tab-daily"   class="tab-btn active">📋 Daily Attendance</button>
                <button onclick="showTab('monthly')" id="tab-monthly" class="tab-btn">📊 Monthly Summary</button>
            </div>

            {{-- ═══════════════════════════════════ --}}
            {{-- TAB 1: DAILY ATTENDANCE             --}}
            {{-- ═══════════════════════════════════ --}}
            <div id="panel-daily" class="tab-panel no-print">
                <div class="panel-header">
                    <div>
                        <h2>📅 {{ $isCurrentMonth ? now()->format('l, d F Y') : \Carbon\Carbon::create($year,$month)->format('F Y').' — Past Month' }}
                            <span style="font-size:12px;color:#9ca3af;margin-left:6px;">({{ $activeBranch }})</span>
                        </h2>
                        <p style="font-size:11px;color:#9ca3af;margin:2px 0 0;">Office Start: 9:30 AM &nbsp;|&nbsp; Office End: 7:00 PM</p>
                    </div>
                    <button onclick="openAddEmployeeModal()" class="btn-blue">👤 Add Employee</button>
                </div>

                <form method="POST" action="{{ route('attendance.store') }}" id="dailyAttendanceForm">
                    @csrf
                    <input type="hidden" name="branch" value="{{ $activeBranch }}">
                    <input type="hidden" name="month"  value="{{ $month }}">
                    <input type="hidden" name="year"   value="{{ $year }}">
                    <input type="hidden" name="date"   value="{{ now()->format('Y-m-d') }}">
                    <input type="hidden" name="update_monthly_summary" value="1">

                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Designation</th>
                                    <th class="center">Status</th>
                                    <th class="center">Check In</th>
                                    <th class="center">Check Out</th>
                                    <th class="center" style="color:#f97316;">Late</th>
                                    <th class="center" style="color:#6366f1;">OT Hrs</th>
                                    <th class="center" style="color:#3b82f6;">Night</th>
                                    
                                    <th class="center">Notes</th>
                                    <th class="center" style="color:#ef4444;">Del</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($useEmployees as $i => $emp)
                                @php
                                    $att      = $todayAttendance[$emp->id] ?? null;
                                    $status   = $att ? $att->status : 'present';
                                    $checkIn  = $att ? $att->check_in  : '09:30';
                                    $checkOut = $att ? $att->check_out : '19:00';
                                    $lateAuto = ($checkIn && \Carbon\Carbon::parse($checkIn)->gt($officeStart)) ? 1 : 0;
                                    $lateVal  = $att ? $att->late : $lateAuto;
                                @endphp
                                <tr>
                                    <td style="color:#d1d5db;font-size:12px;">{{ $i+1 }}</td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div class="avatar">{{ strtoupper(substr($emp->name,0,2)) }}</div>
                                            <div>
                                                <div style="font-weight:700;font-size:13px;color:#1e3a5f;">{{ $emp->name }}</div>
                                                <div style="font-size:11px;color:#9ca3af;">ID: {{ $emp->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-size:12px;color:#6b7280;">{{ $emp->department }}</td>
                                    <td class="center">
                                        <select name="attendance[{{ $emp->id }}]" onchange="updateRow(this)"
                                            class="status-select status-{{ $status }}">
                                            <option value="present" {{ $status=='present'?'selected':'' }}>✅ Present</option>
                                            <option value="absent"  {{ $status=='absent' ?'selected':'' }}>❌ Absent</option>
                                            <option value="leave"   {{ $status=='leave'  ?'selected':'' }}>🟡 Leave</option>
                                            <option value="halfday" {{ $status=='halfday'?'selected':'' }}>🔸 Half Day</option>
                                        </select>
                                    </td>
                                    {{-- CHECK IN --}}
                                    <td class="center">
                                        <div style="position:relative;display:inline-block;">
                                            <span class="time-display {{ $lateVal ? 'badge-orange' : 'badge-green' }} {{ $status=='absent'?'opacity-30 pointer-events-none':'' }}"
                                                style="border-color:{{ $lateVal ? '#f97316' : '#10b981' }};"
                                                onclick="showTimeInput('checkin',{{ $emp->id }})"
                                                id="checkin-display-{{ $emp->id }}">
                                                🕐 {{ \Carbon\Carbon::parse($checkIn)->format('h:i A') }}
                                            </span>
                                            <input type="time" name="checkin[{{ $emp->id }}]"
                                                id="checkin-input-{{ $emp->id }}" value="{{ $checkIn }}"
                                                onchange="onTimeChange('checkin',{{ $emp->id }})"
                                                onblur="hideTimeInput('checkin',{{ $emp->id }})"
                                                style="display:none;position:absolute;top:0;left:0;border:1.5px solid var(--blue-mid);border-radius:8px;padding:4px 8px;font-size:12px;background:white;box-shadow:0 2px 8px rgba(0,0,0,0.1);z-index:10;width:120px;">
                                        </div>
                                    </td>
                                    {{-- CHECK OUT --}}
                                    <td class="center">
                                        <div style="position:relative;display:inline-block;">
                                            <span class="time-display badge-blue {{ $status=='absent'?'opacity-30 pointer-events-none':'' }}"
                                                style="border-color:#3b82f6;"
                                                onclick="showTimeInput('checkout',{{ $emp->id }})"
                                                id="checkout-display-{{ $emp->id }}">
                                                🕖 {{ \Carbon\Carbon::parse($checkOut)->format('h:i A') }}
                                            </span>
                                            <input type="time" name="checkout[{{ $emp->id }}]"
                                                id="checkout-input-{{ $emp->id }}" value="{{ $checkOut }}"
                                                onchange="onTimeChange('checkout',{{ $emp->id }})"
                                                onblur="hideTimeInput('checkout',{{ $emp->id }})"
                                                style="display:none;position:absolute;top:0;left:0;border:1.5px solid var(--blue-mid);border-radius:8px;padding:4px 8px;font-size:12px;background:white;box-shadow:0 2px 8px rgba(0,0,0,0.1);z-index:10;width:120px;">
                                        </div>
                                    </td>
                                    {{-- LATE --}}
                                    <td class="center">
                                        <input type="hidden" name="late[{{ $emp->id }}]" id="late-{{ $emp->id }}" value="{{ $lateVal }}">
                                        <span id="late-badge-{{ $emp->id }}"
                                            class="{{ $lateVal ? 'badge badge-orange' : 'badge badge-gray' }}"
                                            style="cursor:pointer;"
                                            onclick="toggleLate({{ $emp->id }})">
                                            {{ $lateVal ? 'LATE' : '—' }}
                                        </span>
                                    </td>
                                    <td class="center"><input type="number" name="overtime[{{ $emp->id }}]" min="0" max="12" step="0.5" value="{{ $att ? $att->overtime : 0 }}" class="num-input" style="border-color:#c7d2fe;"></td>
                                    <td class="center"><input type="number" name="night[{{ $emp->id }}]"    min="0" value="{{ $att ? $att->night : 0 }}"    class="num-input" style="border-color:#bfdbfe;"></td>
                                    <td class="center"><input type="text"   name="notes[{{ $emp->id }}]"    value="{{ $att ? $att->notes : '' }}" placeholder="..." style="border:1.5px solid #e5e7eb;border-radius:6px;padding:4px 8px;font-size:12px;width:88px;outline:none;"></td>
                                    <td class="center">
                                        <button type="button" onclick="confirmDelete({{ $emp->id }}, '{{ addslashes($emp->name) }}')"
                                            style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" style="text-align:center;padding:48px;color:#9ca3af;">
                                        <div style="font-size:32px;margin-bottom:8px;">👥</div>
                                        <p style="margin-bottom:12px;">Koi employee nahi — Add karein</p>
                                        <button type="button" onclick="openAddEmployeeModal()" class="btn-blue">👤 Add Employee</button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="panel-footer">
                        <button type="button" onclick="markAll('present')"
                            style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                            ✅ All Present
                        </button>
                        <button type="submit" class="btn-blue" style="padding:10px 28px;font-size:14px;">
                            💾 Save Attendance
                        </button>
                    </div>
                </form>
            </div>

            {{-- ═══════════════════════════════════ --}}
            {{-- TAB 2: MONTHLY SUMMARY              --}}
            {{-- ═══════════════════════════════════ --}}
            <div id="panel-monthly" class="tab-panel hidden no-print">
                <div class="panel-header">
                    <div>
                        <h2>📊 Monthly Summary — {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}
                            <span style="font-size:12px;color:#9ca3af;margin-left:6px;">({{ $activeBranch }})</span>
                        </h2>
                        <span style="font-size:11px;color:#f97316;">⏰ {{ $lateToAbsentRule }} late = 1 absent</span>
                    </div>
                    <button onclick="openAddEmployeeModal()" class="btn-blue">👤 Add Employee</button>
                </div>

                @if($isPastMonth)
                <div style="background:#fffbeb;border-bottom:1px solid #fde68a;padding:8px 20px;font-size:12px;color:#92400e;">
                    ✏️ <strong>Past Month View</strong>
                </div>
                @else
                <div style="background:#f0fdf4;border-bottom:1px solid #bbf7d0;padding:8px 20px;font-size:12px;color:#166534;">
                    🔄 <strong>Current Month</strong> — Data daily attendance se auto update hota hai
                </div>
                @endif

                <form method="POST" action="{{ route('monthly.summary.save') }}">
                    @csrf
                    <input type="hidden" name="branch" value="{{ $activeBranch }}">
                    <input type="hidden" name="month"  value="{{ $month }}">
                    <input type="hidden" name="year"   value="{{ $year }}">

                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th class="center" style="color:#10b981;">✅ Present</th>
                                    <th class="center" style="color:#ef4444;">❌ Absent</th>
                                    <th class="center" style="color:#f59e0b;">🟡 Leave</th>
                                    <th class="center" style="color:#f97316;">⏰ Late</th>
                                    <th class="center" style="color:#3b82f6;">🌙 Night</th>
                                    <th class="center" style="color:#6366f1;">⏱ OT Hrs</th>
                                    
                                    <th class="center" style="color:#ef4444;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($useEmployees as $row)
                                @php
                                    $empAtt      = $allAttendances[$row->id] ?? collect();
                                    $presentDays = $empAtt->where('status','present')->count();
                                    $absentDays  = $empAtt->where('status','absent')->count();
                                    $leaveDays   = $empAtt->where('status','leave')->count();
                                    $nightDuties = (int)$empAtt->sum('night');
                                   
                                    $overtimeHrs = (float)$empAtt->sum('overtime');
                                    $lateDays    = (int)$empAtt->where('late',1)->count();
                                    $lateAbsents = (int)floor($lateDays / $lateToAbsentRule);
                                @endphp
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div class="avatar">{{ strtoupper(substr($row->name,0,2)) }}</div>
                                            <div>
                                                <div style="font-weight:700;font-size:13px;color:#1e3a5f;">{{ $row->name }}</div>
                                                <div style="font-size:11px;color:#9ca3af;">{{ $row->department }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center">
                                        <input type="number" name="present[{{ $row->id }}]" min="0" max="{{ $totalWorkingDays }}" value="{{ $presentDays }}"
                                            class="num-input" style="border-color:#6ee7b7;background:#f0fdf4;color:#065f46;font-weight:700;">
                                    </td>
                                    <td class="center">
                                        <input type="number" name="absent[{{ $row->id }}]" min="0" value="{{ $absentDays }}"
                                            class="num-input" style="border-color:#fca5a5;background:#fef2f2;color:#991b1b;font-weight:700;">
                                    </td>
                                    <td class="center">
                                        <input type="number" name="leave[{{ $row->id }}]" min="0" value="{{ $leaveDays }}"
                                            class="num-input" style="border-color:#fcd34d;background:#fffbeb;color:#92400e;font-weight:700;">
                                    </td>
                                    <td class="center">
                                        <div>
                                            <input type="number" name="late[{{ $row->id }}]" min="0" value="{{ $lateDays }}"
                                                class="num-input" style="border-color:#fdba74;background:#fff7ed;color:#9a3412;font-weight:700;">
                                            @if($lateAbsents > 0)
                                                <div style="font-size:10px;color:#ef4444;margin-top:2px;">−{{ $lateAbsents }} absent</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="center">
                                        <input type="number" name="night[{{ $row->id }}]" min="0" value="{{ $nightDuties }}"
                                            class="num-input" style="border-color:#93c5fd;background:#eff6ff;color:#1e40af;font-weight:700;">
                                    </td>
                                    <td class="center">
                                        <input type="number" name="overtime[{{ $row->id }}]" min="0" step="0.5" value="{{ $overtimeHrs }}"
                                            class="num-input" style="border-color:#a5b4fc;background:#eef2ff;color:#3730a3;font-weight:700;">
                                    </td>
                                   
                                    <td class="center">
                                        <input type="hidden" name="emp_ids[]" value="{{ $row->id }}">
                                        <button type="button" onclick="confirmDelete({{ $row->id }}, '{{ addslashes($row->name) }}')"
                                            style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;border-radius:6px;padding:5px 12px;font-size:12px;cursor:pointer;font-weight:600;">
                                            🗑️ Delete
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" style="text-align:center;padding:48px;color:#9ca3af;">
                                        <div style="font-size:32px;margin-bottom:8px;">📊</div>
                                        <p>Koi employee nahi</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="panel-footer">
                        <button type="button" onclick="openAddEmployeeModal()"
                            style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                            👤 Add Employee
                        </button>
                        <button type="submit" class="btn-blue" style="padding:10px 28px;font-size:14px;">
                            💾 Save Monthly Data
                        </button>
                    </div>
                </form>
            </div>

        </div>{{-- /padding --}}
    </main>
</div>

{{-- ═══════════════════ DELETE MODAL ═══════════════════ --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden no-print" style="display:none;">
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(2px);z-index:50;" onclick="closeDeleteModal()"></div>
    <div style="position:fixed;inset:0;z-index:51;display:flex;align-items:center;justify-content:center;">
        <div style="background:white;border-radius:16px;width:100%;max-width:380px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="background:#ef4444;color:white;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-weight:700;font-size:15px;">🗑️ Employee Delete Karein?</div>
                    <div style="font-size:11px;opacity:0.8;margin-top:2px;">Yeh action undo nahi ho sakti</div>
                </div>
                <button onclick="closeDeleteModal()" style="background:rgba(255,255,255,0.2);border:none;color:white;width:28px;height:28px;border-radius:50%;font-size:16px;cursor:pointer;">×</button>
            </div>
            <div style="padding:20px;">
                <div style="display:flex;align-items:center;gap:12px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px;margin-bottom:16px;">
                    <div class="avatar" id="deleteEmpAvatar" style="background:#fee2e2;color:#ef4444;border-color:#fecaca;">--</div>
                    <div>
                        <div style="font-weight:700;color:#1f2937;" id="deleteEmpName">Employee</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:2px;">Sari attendance aur records bhi delete ho jayengi</div>
                    </div>
                </div>
                <form method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="branch" value="{{ $activeBranch }}">
                    <input type="hidden" name="month"  value="{{ $month }}">
                    <input type="hidden" name="year"   value="{{ $year }}">
                    <div style="display:flex;gap:10px;">
                        <button type="button" onclick="closeDeleteModal()"
                            style="flex:1;border:1px solid #e5e7eb;color:#6b7280;padding:10px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;background:white;">
                            Cancel
                        </button>
                        <button type="submit"
                            style="flex:1;background:#ef4444;color:white;border:none;padding:10px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;">
                            🗑️ Haan, Delete Karo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ ADD EMPLOYEE MODAL ═══════════════════ --}}
<div id="addEmployeeModal" style="display:none;position:fixed;inset:0;z-index:50;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(2px);" onclick="closeAddEmployeeModal()"></div>
    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;z-index:51;padding:20px;">
        <div style="background:white;border-radius:16px;width:100%;max-width:680px;max-height:92vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="background:var(--blue-primary);color:white;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
                <div>
                    <div style="font-weight:700;font-size:15px;">👤 Employee Add Karein</div>
                    <div style="font-size:11px;opacity:0.8;margin-top:2px;">{{ $activeBranch }} — {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}</div>
                </div>
                <button onclick="closeAddEmployeeModal()" style="background:rgba(255,255,255,0.2);border:none;color:white;width:28px;height:28px;border-radius:50%;font-size:16px;cursor:pointer;">×</button>
            </div>

            <div style="overflow-y:auto;flex:1;">
                <form method="POST" action="{{ route('employee.store') }}">
                    @csrf
                    <input type="hidden" name="branch" value="{{ $activeBranch }}">
                    <input type="hidden" name="month"  value="{{ $month }}">
                    <input type="hidden" name="year"   value="{{ $year }}">

                    {{-- Section 1 --}}
                    <div style="padding:20px;border-bottom:1px dashed #e5e7eb;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:12px;">1 · Employee Info</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Name <span style="color:#ef4444;">*</span></label>
                                <input type="text" name="name" required placeholder="Ali Hassan"
                                    style="width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;">
                            </div>
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Designation</label>
                                <input type="text" name="designation" placeholder="Manager"
                                    style="width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;">
                            </div>
                        </div>
                    </div>

                    {{-- Section 2 --}}
                    <div style="padding:20px;border-bottom:1px dashed #e5e7eb;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:12px;">2 · Salary & Allowances</div>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">💰 Basic Salary</label>
                                <input type="number" name="basic_salary" id="newBasic" min="0" placeholder="25000"
                                    style="width:100%;border:2px solid #e5e7eb;border-radius:8px;padding:8px 12px;font-size:13px;font-weight:700;outline:none;" oninput="calcNewNet()">
                            </div>
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#3b82f6;display:block;margin-bottom:4px;">🚲 Bike Allow</label>
                                <input type="number" name="bike_allowance" id="newBike" min="0" placeholder="0"
                                    style="width:100%;border:1.5px solid #bfdbfe;border-radius:8px;padding:8px 12px;font-size:13px;background:#eff6ff;outline:none;" oninput="calcNewNet()">
                            </div>
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#3b82f6;display:block;margin-bottom:4px;">📱 Mobile Allow</label>
                                <input type="number" name="mobile_allowance" id="newMobile" min="0" placeholder="0"
                                    style="width:100%;border:1.5px solid #bfdbfe;border-radius:8px;padding:8px 12px;font-size:13px;background:#eff6ff;outline:none;" oninput="calcNewNet()">
                            </div>
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#3b82f6;display:block;margin-bottom:4px;">📦 Other Allow</label>
                                <input type="number" name="other_allowance" id="newOther" min="0" placeholder="0"
                                    style="width:100%;border:1.5px solid #bfdbfe;border-radius:8px;padding:8px 12px;font-size:13px;background:#eff6ff;outline:none;" oninput="calcNewNet()">
                            </div>
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#3b82f6;display:block;margin-bottom:4px;">💼 Commission</label>
                                <input type="number" name="commission" id="newCommission" min="0" placeholder="0"
                                    style="width:100%;border:1.5px solid #bfdbfe;border-radius:8px;padding:8px 12px;font-size:13px;background:#eff6ff;outline:none;" oninput="calcNewNet()">
                            </div>
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">🌙 Night Rate</label>
                                <input type="number" name="night_rate" id="newNightRate" min="0" value="500"
                                    style="width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;" oninput="calcNewNet()">
                            </div>
                        </div>
                    </div>

                    {{-- Section 3 --}}
                    <div style="padding:20px;border-bottom:1px dashed #e5e7eb;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:12px;">3 · Deduction Rules</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#ef4444;display:block;margin-bottom:4px;">⏰ Late Deduction (per late)</label>
                                <input type="number" name="late_deduction" id="newLateDeduct" min="0" placeholder="0"
                                    style="width:100%;border:1.5px solid #fecaca;border-radius:8px;padding:8px 12px;font-size:13px;background:#fef2f2;outline:none;" oninput="calcNewNet()">
                            </div>
                            <div>
                                <label style="font-size:12px;font-weight:600;color:#ef4444;display:block;margin-bottom:4px;">❌ Absent Deduction (per absent)</label>
                                <input type="number" name="absent_deduction" id="newAbsentDeduct" min="0" placeholder="0"
                                    style="width:100%;border:1.5px solid #fecaca;border-radius:8px;padding:8px 12px;font-size:13px;background:#fef2f2;outline:none;" oninput="calcNewNet()">
                            </div>
                        </div>
                    </div>

                    {{-- Section 4 --}}
                    <div style="padding:20px;border-bottom:1px dashed #e5e7eb;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:12px;">4 · {{ \Carbon\Carbon::create($year,$month)->format('F Y') }} Ka Data</div>
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:10px;">
                            <div>
                                <label style="font-size:11px;font-weight:600;color:#10b981;display:block;margin-bottom:4px;">✅ Present</label>
                                <input type="number" name="present_days" id="newPresent" min="0" value="0"
                                    style="width:100%;border:1.5px solid #6ee7b7;border-radius:8px;padding:7px;font-size:13px;text-align:center;background:#f0fdf4;color:#065f46;font-weight:700;outline:none;" oninput="calcNewNet()">
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:600;color:#ef4444;display:block;margin-bottom:4px;">❌ Absent</label>
                                <input type="number" name="absent_days" id="newAbsent" min="0" value="0"
                                    style="width:100%;border:1.5px solid #fca5a5;border-radius:8px;padding:7px;font-size:13px;text-align:center;background:#fef2f2;color:#991b1b;font-weight:700;outline:none;" oninput="calcNewNet()">
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:600;color:#f59e0b;display:block;margin-bottom:4px;">🟡 Leave</label>
                                <input type="number" name="leave_days" min="0" value="0"
                                    style="width:100%;border:1.5px solid #fcd34d;border-radius:8px;padding:7px;font-size:13px;text-align:center;background:#fffbeb;color:#92400e;font-weight:700;outline:none;">
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:600;color:#f97316;display:block;margin-bottom:4px;">⏰ Late</label>
                                <input type="number" name="late_count" id="newLate" min="0" value="0"
                                    style="width:100%;border:1.5px solid #fdba74;border-radius:8px;padding:7px;font-size:13px;text-align:center;background:#fff7ed;color:#9a3412;font-weight:700;outline:none;" oninput="calcNewNet()">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                            <div>
                                <label style="font-size:11px;font-weight:600;color:#3b82f6;display:block;margin-bottom:4px;">🌙 Night Shifts</label>
                                <input type="number" name="night_duties" id="newNightDuties" min="0" value="0"
                                    style="width:100%;border:1.5px solid #93c5fd;border-radius:8px;padding:7px;font-size:13px;text-align:center;background:#eff6ff;color:#1e40af;font-weight:700;outline:none;" oninput="calcNewNet()">
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:600;color:#6366f1;display:block;margin-bottom:4px;">⏱ OT Hours</label>
                                <input type="number" name="overtime_hours" id="newOT" min="0" step="0.5" value="0"
                                    style="width:100%;border:1.5px solid #a5b4fc;border-radius:8px;padding:7px;font-size:13px;text-align:center;background:#eef2ff;color:#3730a3;font-weight:700;outline:none;" oninput="calcNewNet()">
                            </div>
                           
                        </div>
                    </div>

                    {{-- NET PREVIEW --}}
                    <div style="padding:16px 20px;background:#f8faff;border-bottom:1px solid #e5e7eb;">
                        <div style="background:white;border:1.5px solid #e0e7ff;border-radius:10px;padding:14px;">
                            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;text-align:center;font-size:12px;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #f3f4f6;">
                                <div><div style="color:#9ca3af;margin-bottom:4px;">Earned Basic</div><div id="previewEarned" style="font-weight:700;color:#374151;">—</div></div>
                                <div><div style="color:#3b82f6;margin-bottom:4px;">+ Allowances</div><div id="previewAllowances" style="font-weight:700;color:#3b82f6;">—</div></div>
                                <div><div style="color:#f97316;margin-bottom:4px;">= Gross</div><div id="previewGross" style="font-weight:700;color:#f97316;">—</div></div>
                                <div><div style="color:#ef4444;margin-bottom:4px;">− Deductions</div><div id="previewDeductions" style="font-weight:700;color:#ef4444;">—</div></div>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-size:13px;font-weight:600;color:#6b7280;">💵 Estimated Net:</span>
                                <span id="newEmpNetPreview" style="font-size:20px;font-weight:800;color:#7c3aed;">—</span>
                            </div>
                        </div>
                    </div>

                    <div style="padding:16px 20px;background:#f9fafb;display:flex;justify-content:flex-end;gap:10px;">
                        <button type="button" onclick="closeAddEmployeeModal()"
                            style="border:1px solid #e5e7eb;color:#6b7280;padding:9px 20px;border-radius:8px;font-size:13px;background:white;cursor:pointer;">
                            Cancel
                        </button>
                        <button type="submit" class="btn-blue" style="padding:9px 28px;">💾 Save Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ LATE RULE MODAL ═══════════════════ --}}
<div id="lateRuleModal" style="display:none;position:fixed;inset:0;z-index:50;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);" onclick="document.getElementById('lateRuleModal').style.display='none'"></div>
    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;z-index:51;">
        <div style="background:white;border-radius:16px;padding:24px;width:300px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 style="font-size:15px;font-weight:700;color:#1e3a5f;margin:0 0 4px;">⚙️ Late to Absent Rule</h3>
            <p style="font-size:12px;color:#9ca3af;margin:0 0 16px;">Kitne baar late = 1 absent?</p>
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                    <input type="number" name="late_to_absent" min="1" max="10" value="{{ $lateToAbsentRule }}"
                        style="width:72px;border:2px solid var(--blue-mid);border-radius:8px;padding:8px;text-align:center;font-size:20px;font-weight:800;color:var(--blue-primary);outline:none;">
                    <span style="font-size:13px;color:#6b7280;">baar late = 1 absent</span>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn-blue" style="flex:1;justify-content:center;">Save</button>
                    <button type="button" onclick="document.getElementById('lateRuleModal').style.display='none'"
                        style="flex:1;border:1px solid #e5e7eb;color:#6b7280;border-radius:8px;padding:8px;font-size:13px;background:white;cursor:pointer;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const LATE_RULE = {{ $lateToAbsentRule }};

// TABS
function showTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + name).classList.remove('hidden');
    document.getElementById('tab-' + name).classList.add('active');
}

// DELETE MODAL
function confirmDelete(empId, empName) {
    document.getElementById('deleteEmpAvatar').textContent = empName.substring(0,2).toUpperCase();
    document.getElementById('deleteEmpName').textContent   = empName;
    document.getElementById('deleteForm').action = '/employees/' + empId;
    document.getElementById('deleteModal').style.display = 'block';
}
function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }

// TIME INPUT
function showTimeInput(type, empId) {
    document.getElementById(type+'-display-'+empId).style.display = 'none';
    const inp = document.getElementById(type+'-input-'+empId);
    inp.style.display = 'block';
    inp.focus();
}
function hideTimeInput(type, empId) {
    setTimeout(function() {
        document.getElementById(type+'-input-'+empId).style.display  = 'none';
        document.getElementById(type+'-display-'+empId).style.display = '';
    }, 200);
}
function onTimeChange(type, empId) {
    const input   = document.getElementById(type+'-input-'+empId);
    const display = document.getElementById(type+'-display-'+empId);
    const val = input.value;
    if (!val) return;
    const [h,m] = val.split(':').map(Number);
    const ampm  = h >= 12 ? 'PM' : 'AM';
    const h12   = h % 12 || 12;
    const fmt   = h12+':'+String(m).padStart(2,'0')+' '+ampm;

    if (type === 'checkin') {
        const isLate  = val > '09:30';
        const lateInp = document.getElementById('late-'+empId);
        const lateBdg = document.getElementById('late-badge-'+empId);
        if (lateInp) {
            lateInp.value = isLate ? '1' : '0';
            lateBdg.textContent = isLate ? 'LATE' : '—';
            lateBdg.className   = isLate ? 'badge badge-orange' : 'badge badge-gray';
        }
        display.textContent = '🕐 '+fmt;
        display.style.borderColor  = isLate ? '#f97316' : '#10b981';
        display.style.background   = isLate ? '#fff7ed' : '#f0fdf4';
        display.style.color        = isLate ? '#9a3412' : '#065f46';
    } else {
        display.textContent = '🕖 '+fmt;
    }
    input.style.display   = 'none';
    display.style.display = '';
}

function updateRow(sel) {
    const isAbsent = sel.value === 'absent';
    sel.className = 'status-select status-' + sel.value;
    const row = sel.closest('tr');
    row.querySelectorAll('.time-display').forEach(el => {
        el.style.opacity = isAbsent ? '0.3' : '1';
        el.style.pointerEvents = isAbsent ? 'none' : '';
    });
}

function toggleLate(empId) {
    const inp   = document.getElementById('late-'+empId);
    const badge = document.getElementById('late-badge-'+empId);
    inp.value = inp.value == '1' ? '0' : '1';
    badge.textContent = inp.value == '1' ? 'LATE' : '—';
    badge.className   = inp.value == '1' ? 'badge badge-orange' : 'badge badge-gray';
}

function markAll(status) {
    document.querySelectorAll('select[name^="attendance"]').forEach(s => { s.value = status; updateRow(s); });
}

function openAddEmployeeModal()  { document.getElementById('addEmployeeModal').style.display = 'block'; calcNewNet(); }
function closeAddEmployeeModal() { document.getElementById('addEmployeeModal').style.display = 'none'; }

// NET SALARY PREVIEW
function calcNewNet() {
    const g = id => parseFloat(document.getElementById(id)?.value) || 0;
    const gi = id => parseInt(document.getElementById(id)?.value)  || 0;
    const basic=g('newBasic'), bike=g('newBike'), mobile=g('newMobile'), other=g('newOther');
    const commission=g('newCommission'), nightRate=g('newNightRate')||500;
    const lateDeduct=g('newLateDeduct'), absentDeduct=g('newAbsentDeduct');
    const present=gi('newPresent'), absent=gi('newAbsent'), late=gi('newLate');
   

    const prev=document.getElementById('newEmpNetPreview');
    if (basic<=0) {
        ['newEmpNetPreview','previewEarned','previewAllowances','previewGross','previewDeductions']
            .forEach(id => { const el=document.getElementById(id); if(el) el.textContent='—'; });
        return;
    }
    const perDay=basic/26, perHour=perDay/8;
    const lateAbsents=Math.floor(late/Math.max(1,LATE_RULE));
    const earnedBasic=Math.max(0,present-lateAbsents)*perDay;
    const totalAllow=bike+mobile+other+commission;
    const nightPay=nightDuties*nightRate, otPay=ot*perHour;
    const gross=earnedBasic+totalAllow+nightPay+otPay;
   
    const net=gross-deductions;

    const set=(id,v) => { const el=document.getElementById(id); if(el) el.textContent=v; };
    set('previewEarned',     'Rs '+Math.round(earnedBasic).toLocaleString());
    set('previewAllowances', totalAllow>0 ? '+Rs '+Math.round(totalAllow).toLocaleString() : '—');
    set('previewGross',      'Rs '+Math.round(gross).toLocaleString());
    set('previewDeductions', deductions>0 ? '−Rs '+Math.round(deductions).toLocaleString() : '—');
    if(prev) {
        prev.textContent = 'Rs. '+Math.round(net).toLocaleString();
        prev.style.color = net>=0 ? '#7c3aed' : '#ef4444';
    }
}
</script>

</x-layout>