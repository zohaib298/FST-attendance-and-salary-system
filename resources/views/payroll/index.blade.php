<x-layout>
<div class="flex gap-1">
    <x-sidebar></x-sidebar>
    @php
$month          = $month          ?? now()->month;
$year           = $year           ?? now()->year;
$activeBranch   = $activeBranch   ?? request('branch') ?? '';
$allBranchNames = $allBranchNames ?? collect();
$settings       = $settings       ?? [];
$employees      = $employees      ?? collect();
$allAttendances = $allAttendances ?? collect();
$useEmployees   = $employees;

$daysInMonth     = \Carbon\Carbon::create($year, $month)->daysInMonth;
$workingDays     = collect();
for ($d = 1; $d <= $daysInMonth; $d++) {
    $date = \Carbon\Carbon::create($year, $month, $d);
    if (!$date->isSunday()) $workingDays->push($date);
}
$totalWorkingDays = $workingDays->count();
$isPastMonth      = \Carbon\Carbon::create($year, $month)->lt(now()->startOfMonth());
$isCurrentMonth   = ($month == now()->month && $year == now()->year);
$lateToAbsentRule = $settings['late_to_absent'] ?? 3;

$salaryData = collect();
foreach ($useEmployees as $emp) {
    $empAtt      = $allAttendances[$emp->id] ?? collect();
    $presentDays = $empAtt->where('status','present')->count();
    $absentDays  = $empAtt->where('status','absent')->count();
    $leaveDays   = $empAtt->where('status','leave')->count();
    $halfDays    = $empAtt->where('status','halfday')->count();
    $nightDuties = (int)$empAtt->sum('night');
    $totalAdv    = (float)$empAtt->sum('advance');
    $overtimeHrs = (float)$empAtt->sum('overtime');
    $lateDays    = (int)$empAtt->where('late',1)->count();
    $lateAbsents = (int)floor($lateDays / $lateToAbsentRule);

    $basic       = (float)($emp->basic_salary ?? 0);
    $firstAtt    = $empAtt->first();
    $bikeAllow   = (float)(isset($firstAtt->bike_allowance)   ? $firstAtt->bike_allowance   : ($emp->bike_allowance   ?? 0));
    $mobileAllow = (float)(isset($firstAtt->mobile_allowance) ? $firstAtt->mobile_allowance : ($emp->mobile_allowance ?? 0));
    $otherAllow  = (float)(isset($firstAtt->other_allowance)  ? $firstAtt->other_allowance  : ($emp->other_allowance  ?? 0));
    $commission  = (float)(isset($firstAtt->commission)       ? $firstAtt->commission       : ($emp->commission       ?? 0));
    $nightRate       = (float)($emp->night_rate       ?? 500);
    $lateDeductPer   = (float)($emp->late_deduction   ?? 0);
    $absentDeductPer = (float)($emp->absent_deduction ?? 0);

    $perDay  = $basic > 0 ? round($basic / 26) : 0;
    $perHour = $perDay > 0 ? round($perDay / 8) : 0;

    $effectivePresent  = $presentDays + ($halfDays * 0.5);
    $earnedBasic       = max(0, ($effectivePresent + $leaveDays - $lateAbsents)) * $perDay;
    $totalAllowances   = $bikeAllow + $mobileAllow + $otherAllow + $commission;
    $nightPay          = $nightDuties * $nightRate;
    $overtimePay       = $overtimeHrs * $perHour;
    $grossSalary       = $earnedBasic + $totalAllowances + $nightPay + $overtimePay;
    $lateDeductTotal   = $lateDays * $lateDeductPer;
    $absentDeductTotal = ($absentDays + $lateAbsents) * $absentDeductPer;
    $manualNet         = $emp->manual_net_salary ?? null;
    $autoNet    = $grossSalary - $lateDeductTotal - $absentDeductTotal - $totalAdv;
    $netSalary  = ($isPastMonth && $manualNet !== null) ? (float)$manualNet : $autoNet;
    $totalDeductions = $lateDeductTotal + $absentDeductTotal;

    $salaryData->push((object)[
        'emp'              => $emp,
        'presentDays'      => $presentDays,
        'halfDays'         => $halfDays,
        'absentDays'       => $absentDays,
        'leaveDays'        => $leaveDays,
        'nightDuties'      => $nightDuties,
        'lateDays'         => $lateDays,
        'lateAbsents'      => $lateAbsents,
        'effectiveAbsent'  => $absentDays + $lateAbsents,
        'basic'            => $basic,
        'perDay'           => $perDay,
        'perHour'          => $perHour,
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
        'totalDeductions'  => $totalDeductions,
        'advance'          => $totalAdv,
        'grossSalary'      => $grossSalary,
        'netSalary'        => $netSalary,
        'manualNet'        => $manualNet,
        'nightRate'        => $nightRate,
        'lateDeductPer'    => $lateDeductPer,
        'absentDeductPer'  => $absentDeductPer,
        'runningSalary'    => $earnedBasic + $totalAllowances + $nightPay + $overtimePay,
    ]);
}

$totalGross    = $salaryData->sum('grossSalary');
$totalNet      = $isPastMonth
    ? $salaryData->sum(fn($r) => $r->manualNet !== null ? $r->manualNet : $r->netSalary)
    : $salaryData->sum('netSalary');
$totalAdvSum   = $salaryData->sum('advance');
$totalDeductSum= $salaryData->sum('totalDeductions');
$totalPresent  = $salaryData->sum('presentDays');
$totalAbsent   = $salaryData->sum('effectiveAbsent');

$prevMonth = $month-1 < 1  ? 12 : $month-1;
$prevYear  = $month-1 < 1  ? $year-1 : $year;
$nextMonth = $month+1 > 12 ? 1  : $month+1;
$nextYear  = $month+1 > 12 ? $year+1 : $year;
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500;600&display=swap');

:root {
    --blue-primary: #1a56db;
    --blue-dark:    #1e429f;
    --blue-light:   #e8f0fe;
    --blue-mid:     #3f83f8;
    --blue-pale:    #eff6ff;
    --sidebar-bg:   #1e3a5f;

    --green:        #10b981;
    --green-lt:     #d1fae5;
    --green-bg:     #f0fdf4;
    --red:          #ef4444;
    --red-lt:       #fee2e2;
    --red-bg:       #fef2f2;
    --amber:        #f59e0b;
    --amber-lt:     #fef3c7;
    --amber-bg:     #fffbeb;
    --orange:       #f97316;
    --orange-lt:    #ffedd5;
    --violet:       #8b5cf6;
    --violet-lt:    #ede9fe;

    --surface:      #ffffff;
    --bg:           #f1f5ff;
    --border:       #e5e7eb;
    --border-2:     #d1d9ff;

    --text-1:       #111827;
    --text-2:       #374151;
    --text-3:       #6b7280;
    --text-4:       #9ca3af;

    --radius:       12px;
    --radius-sm:    8px;
    --shadow-sm:    0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow:       0 4px 12px rgba(26,86,219,.08), 0 1px 3px rgba(0,0,0,.05);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

.pr-wrap {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    color: var(--text-1);
    overflow-x: hidden;
    width: 100%;
}

/* ── TOP BAR ── */
.hr-topbar {
    background: linear-gradient(135deg, #1e3a5f, var(--blue-primary));
    color: white;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    height: 56px;
    position: sticky;
    top: 0;
    z-index: 40;
    box-shadow: 0 2px 12px rgba(26,86,219,.3);
}
.topbar-left { display: flex; align-items: center; gap: 10px; }
.topbar-left h1 { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
.badge-branch  { background: rgba(255,255,255,0.18); color: white; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
.badge-past    { background: #fbbf24; color: #78350f; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.badge-current { background: #6ee7b7; color: #065f46; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }

.month-nav { display: flex; align-items: center; }
.month-nav a { background: rgba(255,255,255,0.15); color: white; border: none; padding: 6px 12px; font-size: 13px; text-decoration: none; transition: background .15s; line-height: 1; }
.month-nav a:first-child { border-radius: 7px 0 0 7px; }
.month-nav a:last-child  { border-radius: 0 7px 7px 0; }
.month-nav a:hover { background: rgba(255,255,255,.28); }
.month-nav .mn-label { background: rgba(255,255,255,0.1); color: white; padding: 6px 16px; font-family: 'DM Mono', monospace; font-size: 11px; font-weight: 600; border-left: 1px solid rgba(255,255,255,.2); border-right: 1px solid rgba(255,255,255,.2); white-space: nowrap; }

/* ── BRANCH BAR ── */
.branch-bar { background: white; border-bottom: 1px solid var(--border); padding: 8px 20px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.branch-bar .lbl { font-family: 'DM Mono', monospace; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-4); margin-right: 4px; }
.br-btn { padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; border: 1px solid var(--border); color: var(--text-3); text-decoration: none; background: white; transition: all .15s; }
.br-btn:hover  { border-color: var(--blue-mid); color: var(--blue-primary); }
.br-btn.active { background: var(--blue-primary); color: #fff; border-color: var(--blue-primary); }

/* ── CONTENT ── */
.pr-content { padding: 16px 20px 48px; }

/* ── ALERTS ── */
.alert { padding: 9px 14px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 500; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; border: 1px solid; }
.alert.success { background: var(--green-bg); color: #065f46; border-color: #a7f3d0; }
.alert.error   { background: var(--red-bg);   color: #991b1b; border-color: #fca5a5; }
.alert.info    { background: var(--amber-bg); color: #92400e; border-color: #fde68a; }

/* ── STAT GRID ── */
.stat-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; margin-bottom: 16px; }
@media(max-width:1200px){ .stat-grid { grid-template-columns: repeat(4,1fr); } }
@media(max-width:640px)  { .stat-grid { grid-template-columns: repeat(2,1fr); } }

.stat-card { background: white; border-radius: 10px; padding: 13px 12px 10px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); border-top: 3px solid transparent; transition: box-shadow .15s, transform .15s; }
.stat-card:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
.stat-card.blue   { border-top-color: var(--blue-primary); }
.stat-card.green  { border-top-color: var(--green); }
.stat-card.red    { border-top-color: var(--red); }
.stat-card.amber  { border-top-color: var(--amber); }
.stat-card.violet { border-top-color: var(--violet); }
.stat-label { font-size: 10px; font-weight: 600; color: var(--text-4); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.stat-val   { font-size: 20px; font-weight: 800; line-height: 1; letter-spacing: -.02em; }
.stat-val.blue   { color: var(--blue-primary); }
.stat-val.green  { color: var(--green); }
.stat-val.red    { color: var(--red); }
.stat-val.amber  { color: var(--amber); }
.stat-val.violet { color: var(--violet); }
.stat-sub { font-size: 9px; color: var(--text-4); margin-top: 3px; font-weight: 500; }

/* ── ACTION BAR ── */
.action-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.search-box { display: flex; align-items: center; background: white; border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; box-shadow: var(--shadow-sm); }
.search-box:focus-within { border-color: var(--blue-mid); }
.search-box input { border: none; outline: none; padding: 7px 12px; font-family: 'DM Sans', sans-serif; font-size: 12px; color: var(--text-1); background: transparent; width: 180px; }
.search-box input::placeholder { color: var(--text-4); }
.search-box button { background: var(--blue-primary); color: white; border: none; padding: 7px 14px; font-size: 11px; font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif; }
.search-box button:hover { background: var(--blue-dark); }

.btn-blue  { display:inline-flex; align-items:center; gap:5px; background:var(--blue-primary); color:white; border:none; padding:7px 14px; border-radius:7px; font-size:11px; font-weight:700; cursor:pointer; text-decoration:none; transition:background .15s; font-family:'DM Sans',sans-serif; }
.btn-blue:hover  { background: var(--blue-dark); color:white; }
.btn-green { display:inline-flex; align-items:center; gap:5px; background:var(--green); color:white; border:none; padding:7px 14px; border-radius:7px; font-size:11px; font-weight:700; cursor:pointer; text-decoration:none; transition:background .15s; font-family:'DM Sans',sans-serif; }
.btn-green:hover { background: #059669; }

/* ── TABLE WRAPPER ── */
.table-wrap { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden; }

.table-header {
    padding: 14px 18px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    background: linear-gradient(135deg, #1e3a5f, var(--blue-primary));
}
.table-title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: white; }
.table-title .chip-badge { font-family: 'DM Mono', monospace; font-size: 9px; font-weight: 600; padding: 2px 8px; border-radius: 20px; background: rgba(255,255,255,0.2); color: white; }
.table-title .chip-badge.past { background: #fbbf24; color: #78350f; }
.table-meta { font-family: 'DM Mono', monospace; font-size: 9px; color: rgba(255,255,255,0.6); }

/* ── PAYROLL TABLE — NO X-SCROLL ── */
.table-scroll { width: 100%; overflow-x: auto; }

.pr-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    table-layout: fixed;
    min-width: 900px;
}

/* Column widths */
.pr-table col.c-num    { width: 32px; }
.pr-table col.c-emp    { width: 150px; }
.pr-table col.c-basic  { width: 78px; }
.pr-table col.c-allow  { width: 72px; }
.pr-table col.c-att    { width: 52px; }
.pr-table col.c-money  { width: 68px; }
.pr-table col.c-net    { width: 90px; }
.pr-table col.c-action { width: 85px; }

.pr-table thead tr { background: #f4f7ff; }
.pr-table thead th {
    padding: 9px 6px;
    font-family: 'DM Mono', monospace;
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--text-3);
    text-align: center;
    border-bottom: 2px solid var(--border);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pr-table thead th.left { text-align: left; padding-left: 14px; }

.pr-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; }
.pr-table tbody tr:hover { background: #f0f6ff; }
.pr-table tbody tr:last-child { border-bottom: none; }
.pr-table td {
    padding: 10px 6px;
    text-align: center;
    color: var(--text-2);
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pr-table td.left { text-align: left; padding-left: 14px; }

/* employee cell */
.emp-cell { display: flex; align-items: center; gap: 7px; }
.emp-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg, var(--blue-light), #c7d2fe);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 10px; color: var(--blue-primary);
    border: 2px solid #e0e7ff; flex-shrink: 0;
    font-family: 'DM Mono', monospace;
}
.emp-name { font-size: 12px; font-weight: 700; color: var(--sidebar-bg); line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 108px; }
.emp-dept { font-family: 'DM Mono', monospace; font-size: 9px; color: var(--text-4); margin-top: 1px; }

/* chips */
.chip { display:inline-flex; align-items:center; justify-content:center; padding:2px 7px; border-radius:5px; font-family:'DM Mono',monospace; font-size:10px; font-weight:600; min-width:28px; }
.chip.green  { background: var(--green-lt);  color: #065f46; }
.chip.red    { background: var(--red-lt);    color: #991b1b; }
.chip.yellow { background: var(--amber-lt);  color: #92400e; }
.chip.orange { background: var(--orange-lt); color: #9a3412; }
.chip.blue   { background: var(--blue-light);color: var(--blue-dark); }
.chip.violet { background: var(--violet-lt); color: #5b21b6; }
.chip.gray   { background: #f3f4f6;          color: var(--text-3); }

/* money */
.money { font-family:'DM Mono',monospace; font-weight:600; font-size:11px; }
.money.pos   { color: var(--green); }
.money.neg   { color: var(--red); }
.money.neu   { color: var(--text-4); }
.money.amber { color: var(--amber); }
.money.bold  { font-size:12px; font-weight:700; }

/* allowances tooltip */
.allow-wrap { position:relative; cursor:pointer; display:inline-block; }
.allow-wrap .tip { display:none; position:absolute; bottom:calc(100% + 6px); left:50%; transform:translateX(-50%); background:var(--sidebar-bg); color:#fff; border-radius:8px; padding:8px 12px; font-family:'DM Mono',monospace; font-size:10px; line-height:1.8; white-space:nowrap; z-index:999; box-shadow:0 8px 24px rgba(0,0,0,.25); }
.allow-wrap .tip::after { content:''; position:absolute; top:100%; left:50%; transform:translateX(-50%); border:5px solid transparent; border-top-color:var(--sidebar-bg); }
.allow-wrap:hover .tip { display:block; }

/* att bar */
.att-bar { display:flex; height:3px; border-radius:3px; overflow:hidden; background:#f3f4f6; width:44px; margin:3px auto 0; }
.att-bar .seg-p { background:var(--green); }
.att-bar .seg-a { background:var(--red); }
.att-bar .seg-l { background:var(--amber); }

/* breakdown tooltip */
.breakdown-wrap { position:relative; cursor:pointer; }
.breakdown-wrap .bd-tip { display:none; position:absolute; bottom:calc(100% + 6px); left:50%; transform:translateX(-50%); background:white; border:1.5px solid var(--border-2); border-radius:var(--radius); padding:12px 14px; font-size:10px; line-height:1.9; white-space:nowrap; z-index:999; box-shadow:0 12px 32px rgba(26,86,219,.15); min-width:190px; }
.breakdown-wrap .bd-tip::after { content:''; position:absolute; top:100%; left:50%; transform:translateX(-50%); border:6px solid transparent; border-top-color:white; }
.breakdown-wrap:hover .bd-tip { display:block; }
.bd-row { display:flex; justify-content:space-between; gap:16px; }
.bd-row.total { border-top:1px solid var(--border); margin-top:3px; padding-top:3px; font-weight:700; }
.bd-label { color:var(--text-3); font-family:'DM Mono',monospace; font-size:9.5px; }
.bd-val   { font-family:'DM Mono',monospace; font-size:10px; font-weight:600; color:var(--text-1); }
.bd-val.pos  { color:var(--green); }
.bd-val.neg  { color:var(--red); }
.bd-val.blue { color:var(--blue-primary); }

/* net pill */
.net-pill { display:inline-flex; flex-direction:column; align-items:center; padding:5px 8px; border-radius:7px; border:1.5px solid; min-width:78px; }
.net-pill.pos  { background:var(--green-bg); border-color:#a7f3d0; }
.net-pill.neg  { background:var(--red-bg);   border-color:#fca5a5; }
.net-pill.pend { background:#f9fafb;          border-color:var(--border); }
.net-pill .net-rs  { font-family:'DM Mono',monospace; font-size:11px; font-weight:700; }
.net-pill.pos .net-rs  { color:#065f46; }
.net-pill.neg .net-rs  { color:#991b1b; }
.net-pill.pend .net-rs { color:var(--text-3); }
.net-pill .net-tag { font-family:'DM Mono',monospace; font-size:7.5px; font-weight:700; letter-spacing:.06em; margin-top:1px; text-transform:uppercase; }
.net-pill.pos .net-tag  { color:#6ee7b7; }
.net-pill.neg .net-tag  { color:#fca5a5; }
.net-pill.pend .net-tag { color:var(--text-4); }

/* action buttons */
.btn-slip { display:inline-flex; align-items:center; gap:3px; padding:4px 8px; border-radius:5px; background:var(--blue-light); color:var(--blue-primary); font-size:10px; font-weight:700; text-decoration:none; font-family:'DM Mono',monospace; border:1px solid #bfdbfe; transition:all .15s; }
.btn-slip:hover { background:var(--blue-primary); color:white; }
.btn-edit { display:inline-flex; align-items:center; gap:3px; padding:4px 8px; border-radius:5px; background:var(--amber-lt); color:#92400e; font-size:10px; font-weight:700; font-family:'DM Mono',monospace; border:1px solid #fcd34d; transition:all .15s; cursor:pointer; }
.btn-edit:hover { background:var(--amber); color:white; border-color:var(--amber); }

/* tfoot */
.pr-table tfoot tr { background: var(--sidebar-bg); }
.pr-table tfoot td { padding:11px 6px; color:#93c5fd; font-family:'DM Mono',monospace; font-size:10px; font-weight:600; text-align:center; }
.pr-table tfoot td.left { text-align:left; padding-left:14px; }
.pr-table tfoot td.bright  { color:#fff; font-size:11px; font-weight:700; }
.pr-table tfoot td.gold-t  { color:#fcd34d; font-size:11px; font-weight:700; }
.pr-table tfoot td.red-t   { color:#fca5a5; }
.pr-table tfoot td.green-t { color:#6ee7b7; font-size:11px; font-weight:700; }

/* table footer */
.table-footer { padding:10px 18px; border-top:1px solid #f3f4f6; background:#f8faff; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:6px; }
.table-footer .gen-time  { font-family:'DM Mono',monospace; font-size:9px; color:var(--text-4); }
.table-footer .late-rule { font-family:'DM Mono',monospace; font-size:9px; color:var(--orange); font-weight:600; }

/* empty */
.empty-state { text-align:center; padding:48px 20px; }
.empty-state .icon { font-size:34px; margin-bottom:8px; }
.empty-state p { color:var(--text-4); font-size:12px; }

/* ── EDIT MODAL ── */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:1000; display:none; align-items:center; justify-content:center; padding:16px; }
.modal-overlay.open { display:flex; }
.modal-box { background:white; border-radius:14px; width:100%; max-width:600px; box-shadow:0 24px 64px rgba(26,86,219,.22); display:flex; flex-direction:column; max-height:92vh; }
.modal-head { padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg,#f8faff,#fff); border-radius:14px 14px 0 0; }
.modal-head h3 { font-size:15px; font-weight:800; color:var(--sidebar-bg); }
.modal-head p  { font-size:10px; color:var(--text-4); font-family:'DM Mono',monospace; margin-top:2px; }
.modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-3); padding:3px 6px; border-radius:5px; }
.modal-close:hover { background:#f3f4f6; color:var(--text-1); }
.modal-body { padding:18px 22px; overflow-y:auto; flex:1; }
.modal-foot { padding:14px 22px; border-top:1px solid var(--border); display:flex; gap:8px; justify-content:flex-end; background:#f8faff; border-radius:0 0 14px 14px; }

.modal-section { margin-bottom:18px; }
.modal-section-title { font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-4); font-family:'DM Mono',monospace; margin-bottom:10px; padding-bottom:5px; border-bottom:1px solid #f3f4f6; }

.field-grid       { display:grid; grid-template-columns:1fr 1fr;     gap:10px; }
.field-grid.three { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; }
.field-group { display:flex; flex-direction:column; gap:4px; }
.field-group label { font-size:10px; font-weight:600; color:var(--text-3); font-family:'DM Mono',monospace; }
.field-group input { border:1.5px solid var(--border); border-radius:7px; padding:7px 10px; font-family:'DM Sans',sans-serif; font-size:12px; color:var(--text-1); outline:none; width:100%; transition:border-color .15s, box-shadow .15s; }
.field-group input:focus { border-color:var(--blue-mid); box-shadow:0 0 0 3px rgba(63,131,248,.1); }
.field-group .hint { font-size:9.5px; color:var(--text-4); font-family:'DM Mono',monospace; }

.att-summary { display:grid; grid-template-columns:repeat(5,1fr); gap:7px; margin-bottom:14px; }
.att-box { text-align:center; padding:9px 5px; border-radius:8px; border:1px solid var(--border); }
.att-box .att-num { font-size:18px; font-weight:800; font-family:'DM Mono',monospace; line-height:1; }
.att-box .att-lbl { font-size:8.5px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--text-4); margin-top:2px; }
.att-box.present { background:var(--green-bg); border-color:#a7f3d0; }
.att-box.present .att-num { color:var(--green); }
.att-box.absent  { background:var(--red-bg);   border-color:#fca5a5; }
.att-box.absent  .att-num { color:var(--red); }
.att-box.leave   { background:var(--amber-bg); border-color:#fde68a; }
.att-box.leave   .att-num { color:var(--amber); }
.att-box.late    { background:var(--orange-lt); border-color:#fdba74; }
.att-box.late    .att-num { color:var(--orange); }
.att-box.night   { background:var(--violet-lt); border-color:#c4b5fd; }
.att-box.night   .att-num { color:var(--violet); }

.live-preview { background:linear-gradient(135deg,var(--sidebar-bg),#1e429f); border-radius:10px; padding:13px 15px; margin-top:14px; color:white; font-family:'DM Mono',monospace; }
.lp-title { font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#93c5fd; margin-bottom:9px; }
.lp-row { display:flex; justify-content:space-between; align-items:center; padding:2px 0; font-size:10.5px; }
.lp-label { color:#93c5fd; }
.lp-val   { color:white; font-weight:600; }
.lp-val.pos  { color:#6ee7b7; }
.lp-val.neg  { color:#fca5a5; }
.lp-val.gold { color:#fcd34d; }
.lp-divider { border:none; border-top:1px solid rgba(255,255,255,.15); margin:5px 0; }
.lp-total { font-size:13px; font-weight:800; }

.btn-save   { background:var(--blue-primary); color:white; border:none; padding:9px 22px; border-radius:7px; font-family:'DM Sans',sans-serif; font-size:12px; font-weight:700; cursor:pointer; transition:background .15s; }
.btn-save:hover { background:var(--blue-dark); }
.btn-cancel { background:white; color:var(--text-3); border:1.5px solid var(--border); padding:9px 16px; border-radius:7px; font-family:'DM Sans',sans-serif; font-size:12px; font-weight:600; cursor:pointer; transition:all .15s; }
.btn-cancel:hover { border-color:var(--text-3); color:var(--text-1); }

@media print {
    .no-print { display:none !important; }
    .pr-wrap  { background:#fff; }
    .table-wrap { box-shadow:none; border:none; }
    .stat-grid,.action-bar,.branch-bar,.hr-topbar { display:none; }
    .btn-slip,.btn-edit { display:none !important; }
    .pr-table { font-size:9px; }
    .pr-table td,.pr-table th { padding:4px 5px; }
    @page { margin:8mm; size:A4 landscape; }
}
</style>

<div class="pr-wrap">

    {{-- TOP BAR --}}
    <div class="hr-topbar no-print">
        <div class="topbar-left">
            <h1>
                💰 Payroll Sheet
                <span class="badge-branch">{{ $activeBranch }}</span>
                @if($isPastMonth)   <span class="badge-past">Past Month</span>
                @elseif($isCurrentMonth) <span class="badge-current">Current</span>
                @endif
            </h1>
        </div>
        <div class="month-nav">
            <a href="?branch={{ $activeBranch }}&month={{ $prevMonth }}&year={{ $prevYear }}">&larr;</a>
            <span class="mn-label">{{ \Carbon\Carbon::create($year,$month)->format('F Y') }}</span>
            <a href="?branch={{ $activeBranch }}&month={{ $nextMonth }}&year={{ $nextYear }}">&rarr;</a>
        </div>
    </div>

    {{-- BRANCH BAR --}}
    @if(isset($allBranchNames) && count($allBranchNames) > 0)
    <div class="branch-bar no-print">
        <span class="lbl">Branch:</span>
        @foreach($allBranchNames as $br)
        <a href="?branch={{ $br }}&month={{ $month }}&year={{ $year }}"
           class="br-btn {{ $activeBranch===$br ? 'active' : '' }}">{{ $br }}</a>
        @endforeach
    </div>
    @endif

    <div class="pr-content">

        @if(session('success'))
        <div class="alert success no-print">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert error no-print">❌ {{ session('error') }}</div>
        @endif
        @if($isPastMonth)
        <div class="alert info no-print">
            ✏️ <strong>Past Month:</strong> Har employee ki salary manually edit ki ja sakti hai. "Edit" button se commission, allowances, overtime, advance set karein.
        </div>
        @endif

        {{-- STAT GRID --}}
        <div class="stat-grid no-print">
            <div class="stat-card blue">
                <div class="stat-label">Employees</div>
                <div class="stat-val blue">{{ $salaryData->count() }}</div>
                <div class="stat-sub">{{ $activeBranch }}</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Gross Payroll</div>
                <div class="stat-val green" style="font-size:16px;">{{ $totalGross > 0 ? 'Rs '.number_format($totalGross) : '—' }}</div>
                <div class="stat-sub">Before deductions</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">Net Payable</div>
                <div class="stat-val blue" style="font-size:16px;">{{ $totalNet > 0 ? 'Rs '.number_format($totalNet) : '—' }}</div>
                <div class="stat-sub">After all cuts</div>
            </div>
            <div class="stat-card red">
                <div class="stat-label">Advances</div>
                <div class="stat-val red" style="font-size:16px;">{{ $totalAdvSum > 0 ? 'Rs '.number_format($totalAdvSum) : '—' }}</div>
                <div class="stat-sub">Recovered</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Total Present</div>
                <div class="stat-val green">{{ $totalPresent }}</div>
                <div class="stat-sub">Days (all staff)</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-label">Total Absent</div>
                <div class="stat-val amber">{{ $totalAbsent }}</div>
                <div class="stat-sub">Incl. late→absent</div>
            </div>
            <div class="stat-card violet">
                <div class="stat-label">Deductions</div>
                <div class="stat-val violet" style="font-size:16px;">{{ $totalDeductSum > 0 ? 'Rs '.number_format($totalDeductSum) : '—' }}</div>
                <div class="stat-sub">Late + absent</div>
            </div>
        </div>

        {{-- ACTION BAR --}}
        <div class="action-bar no-print">
            <form method="GET" class="search-box">
                <input type="hidden" name="branch" value="{{ $activeBranch }}">
                <input type="hidden" name="month"  value="{{ $month }}">
                <input type="hidden" name="year"   value="{{ $year }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Employee search...">
                <button type="submit">🔍 Search</button>
            </form>
            <div style="display:flex;gap:8px;">
                <button onclick="window.print()" class="btn-green">🖨️ Print</button>
                <a href="?branch={{ $activeBranch }}&month={{ $month }}&year={{ $year }}" class="btn-blue">🔄 Refresh</a>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-wrap">
            <div class="table-header">
                <div class="table-title">
                    💰 Payroll — {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}
                    <span class="chip-badge {{ $isPastMonth ? 'past' : '' }}">
                        {{ $activeBranch }} · {{ $isPastMonth ? 'Past' : ($isCurrentMonth ? 'Current' : 'Future') }}
                    </span>
                </div>
                <div class="table-meta">
                    ⏰ {{ $lateToAbsentRule }} late = 1 absent · {{ $totalWorkingDays }} working days · Basic ÷ 26
                </div>
            </div>

            <div class="table-scroll">
                <table class="pr-table">
                    <colgroup>
                        <col class="c-num">
                        <col class="c-emp">
                        <col class="c-basic">
                        <col class="c-allow">
                        <col class="c-att">
                        <col class="c-att">
                        <col class="c-att">
                        <col class="c-att">
                        <col class="c-money">
                        <col class="c-money">
                        <col class="c-money">
                        <col class="c-money">
                        <col class="c-money">
                        <col class="c-net">
                        <col class="c-action">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="left">#</th>
                            <th class="left">Employee</th>
                            <th>Basic</th>
                            <th>Allow.</th>
                            <th>✅ Pres</th>
                            <th>❌ Abs</th>
                            <th>🟡 Lv</th>
                            <th>⏰ Late</th>
                            <th>🌙 Night</th>
                            <th>⏱ OT</th>
                            <th>💳 Adv</th>
                            <th>⚠️ Ded</th>
                            <th>Gross</th>
                            <th>Net Salary</th>
                            <th class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaryData as $i => $row)
                        @php
                            $displayNet = ($isPastMonth && $row->manualNet !== null) ? $row->manualNet : $row->netSalary;
                            $totalDays  = max(1, $row->presentDays + $row->absentDays + $row->leaveDays);
                            $pPct = round(($row->presentDays / $totalDays) * 100);
                            $aPct = round(($row->effectiveAbsent / $totalDays) * 100);
                            $lPct = max(0, 100 - $pPct - $aPct);
                            $attPct = $totalWorkingDays > 0 ? round(($row->presentDays / $totalWorkingDays) * 100) : 0;
                        @endphp
                        <tr>
                            <td class="left">
                                <span style="font-family:'DM Mono',monospace;font-size:9px;color:var(--text-4);">{{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="left">
                                <div class="emp-cell">
                                    <div class="emp-avatar">{{ strtoupper(substr($row->emp->name,0,2)) }}</div>
                                    <div>
                                        <div class="emp-name" title="{{ $row->emp->name }}">{{ $row->emp->name }}</div>
                                        <div class="emp-dept">{{ $attPct }}% att.</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="money bold" style="color:var(--text-1);">{{ $row->basic > 0 ? number_format($row->basic) : '—' }}</span>
                                @if($row->perDay > 0)
                                <div style="font-family:'DM Mono',monospace;font-size:8.5px;color:var(--text-4);margin-top:1px;">{{ number_format($row->perDay) }}/d</div>
                                @endif
                            </td>
                            <td>
                                @if($row->totalAllowances > 0)
                                <div class="allow-wrap">
                                    <span class="chip blue">+{{ number_format($row->totalAllowances) }}</span>
                                    <div class="tip">
                                        🚲 Bike: Rs {{ number_format($row->bikeAllow) }}<br>
                                        📱 Mobile: Rs {{ number_format($row->mobileAllow) }}<br>
                                        📦 Other: Rs {{ number_format($row->otherAllow) }}<br>
                                        💼 Comm: Rs {{ number_format($row->commission) }}
                                    </div>
                                </div>
                                @else
                                <span class="chip gray">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="chip green">{{ $row->presentDays }}{{ $row->halfDays > 0 ? '+'.$row->halfDays.'h' : '' }}</span>
                                <div class="att-bar">
                                    <div class="seg-p" style="width:{{ $pPct }}%"></div>
                                    <div class="seg-a" style="width:{{ $aPct }}%"></div>
                                    <div class="seg-l" style="width:{{ $lPct }}%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="chip {{ $row->effectiveAbsent > 0 ? 'red' : 'gray' }}">
                                    {{ $row->effectiveAbsent }}@if($row->lateAbsents > 0)<span style="font-size:8px;opacity:.7;">+{{ $row->lateAbsents }}L</span>@endif
                                </span>
                            </td>
                            <td><span class="chip {{ $row->leaveDays > 0 ? 'yellow' : 'gray' }}">{{ $row->leaveDays }}</span></td>
                            <td><span class="chip {{ $row->lateDays > 0 ? 'orange' : 'gray' }}">{{ $row->lateDays }}</span></td>
                            <td>
                                @if($row->nightPay > 0)
                                <span class="money pos">+{{ number_format($row->nightPay) }}</span>
                                @else
                                <span class="money neu">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row->overtimePay > 0)
                                <span class="money" style="color:var(--violet);">+{{ number_format($row->overtimePay) }}</span>
                                @else
                                <span class="money neu">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row->advance > 0)
                                <span class="money neg">−{{ number_format($row->advance) }}</span>
                                @else
                                <span class="money neu">—</span>
                                @endif
                            </td>
                            <td>
                                @php $d = $row->lateDeductTotal + $row->absentDeductTotal; @endphp
                                @if($d > 0)
                                <div class="allow-wrap">
                                    <span class="money neg">−{{ number_format($d) }}</span>
                                    <div class="tip">
                                        ⏰ Late ({{ $row->lateDays }}×): −Rs {{ number_format($row->lateDeductTotal) }}<br>
                                        ❌ Absent ({{ $row->effectiveAbsent }}×): −Rs {{ number_format($row->absentDeductTotal) }}
                                    </div>
                                </div>
                                @else
                                <span class="money neu">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="breakdown-wrap">
                                    <span class="money amber bold">{{ $row->grossSalary > 0 ? number_format($row->grossSalary) : '—' }}</span>
                                    <div class="bd-tip">
                                        <div style="font-family:'DM Mono',monospace;font-size:9.5px;font-weight:700;color:var(--text-1);margin-bottom:5px;padding-bottom:5px;border-bottom:1px solid var(--border);">📋 Breakdown</div>
                                        <div class="bd-row"><span class="bd-label">Earned Basic</span><span class="bd-val">Rs {{ number_format($row->earnedBasic) }}</span></div>
                                        <div class="bd-row"><span class="bd-label">+ Allowances</span><span class="bd-val pos">Rs {{ number_format($row->totalAllowances) }}</span></div>
                                        @if($row->nightPay > 0)
                                        <div class="bd-row"><span class="bd-label">+ Night Pay</span><span class="bd-val pos">Rs {{ number_format($row->nightPay) }}</span></div>
                                        @endif
                                        @if($row->overtimePay > 0)
                                        <div class="bd-row"><span class="bd-label">+ OT Pay</span><span class="bd-val pos">Rs {{ number_format($row->overtimePay) }}</span></div>
                                        @endif
                                        <div class="bd-row total"><span class="bd-label" style="color:var(--amber);">= Gross</span><span class="bd-val blue">Rs {{ number_format($row->grossSalary) }}</span></div>
                                        @if($d > 0)
                                        <div class="bd-row"><span class="bd-label">− Deductions</span><span class="bd-val neg">Rs {{ number_format($d) }}</span></div>
                                        @endif
                                        @if($row->advance > 0)
                                        <div class="bd-row"><span class="bd-label">− Advance</span><span class="bd-val neg">Rs {{ number_format($row->advance) }}</span></div>
                                        @endif
                                        <div class="bd-row total"><span class="bd-label" style="color:var(--green);">= Net</span><span class="bd-val pos">Rs {{ number_format($displayNet) }}</span></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($displayNet > 0)
                                <div class="net-pill pos">
                                    <span class="net-rs">Rs {{ number_format($displayNet) }}</span>
                                    <span class="net-tag">{{ ($isPastMonth && $row->manualNet !== null) ? 'MANUAL' : 'AUTO' }}</span>
                                </div>
                                @elseif($displayNet < 0)
                                <div class="net-pill neg">
                                    <span class="net-rs">Rs {{ number_format($displayNet) }}</span>
                                    <span class="net-tag">NEGATIVE</span>
                                </div>
                                @else
                                <div class="net-pill pend">
                                    <span class="net-rs">—</span>
                                    <span class="net-tag">PENDING</span>
                                </div>
                                @endif
                            </td>
                            <td class="no-print">
                                <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
                                    @if($isPastMonth)
                                    <button class="btn-edit"
                                        onclick="openEditModal({
                                            id: {{ $row->emp->id }},
                                            name: '{{ addslashes($row->emp->name) }}',
                                            basic: {{ $row->basic }},
                                            perDay: {{ $row->perDay }},
                                            perHour: {{ $row->perHour }},
                                            presentDays: {{ $row->presentDays }},
                                            absentDays: {{ $row->absentDays }},
                                            leaveDays: {{ $row->leaveDays }},
                                            lateDays: {{ $row->lateDays }},
                                            lateAbsents: {{ $row->lateAbsents }},
                                            nightDuties: {{ $row->nightDuties }},
                                            nightRate: {{ $row->nightRate }},
                                            bikeAllow: {{ $row->bikeAllow }},
                                            mobileAllow: {{ $row->mobileAllow }},
                                            otherAllow: {{ $row->otherAllow }},
                                            commission: {{ $row->commission }},
                                            overtime: {{ $row->overtime }},
                                            advance: {{ $row->advance }},
                                            lateDeductPer: {{ $row->lateDeductPer }},
                                            absentDeductPer: {{ $row->absentDeductPer }},
                                            manualNet: {{ $row->manualNet ?? 'null' }}
                                        })">✏️ Edit</button>
                                    @else
                                    <span style="font-size:9px;color:var(--text-4);font-family:'DM Mono',monospace;">🔒 Auto</span>
                                    @endif
                                    <a href="/salary-slip/{{ $row->emp->id }}/{{ $month }}/{{ $year }}" class="btn-slip">📄</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15">
                                <div class="empty-state">
                                    <div class="icon">💼</div>
                                    <p>Koi employee nahi mila is branch mein</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    @if($salaryData->count() > 0)
                    <tfoot>
                        <tr>
                            <td colspan="2" class="left">
                                <span style="color:white;font-weight:700;font-size:11px;">TOTALS</span>
                                <span style="color:#93c5fd;font-size:9px;margin-left:6px;">{{ $useEmployees->count() }} emp</span>
                            </td>
                            <td class="bright">{{ $salaryData->sum('basic') > 0 ? number_format($salaryData->sum('basic')) : '—' }}</td>
                            <td style="color:#93c5fd;">{{ $salaryData->sum('totalAllowances') > 0 ? '+'.number_format($salaryData->sum('totalAllowances')) : '—' }}</td>
                            <td class="bright">{{ $totalPresent }}</td>
                            <td class="red-t">{{ $totalAbsent }}</td>
                            <td>{{ $salaryData->sum('leaveDays') }}</td>
                            <td>{{ $salaryData->sum('lateDays') }}</td>
                            <td style="color:#6ee7b7;">{{ $salaryData->sum('nightPay') > 0 ? '+'.number_format($salaryData->sum('nightPay')) : '—' }}</td>
                            <td style="color:#c4b5fd;">{{ $salaryData->sum('overtimePay') > 0 ? '+'.number_format($salaryData->sum('overtimePay')) : '—' }}</td>
                            <td class="red-t">{{ $totalAdvSum > 0 ? '−'.number_format($totalAdvSum) : '—' }}</td>
                            <td class="red-t">{{ $totalDeductSum > 0 ? '−'.number_format($totalDeductSum) : '—' }}</td>
                            <td class="gold-t">{{ $totalGross > 0 ? number_format($totalGross) : '—' }}</td>
                            <td class="green-t">{{ $totalNet > 0 ? 'Rs '.number_format($totalNet) : 'Pending' }}</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <div class="table-footer">
                <span class="gen-time">Generated: {{ now()->format('d M Y, h:i A') }}</span>
                <span class="late-rule">⏰ {{ $lateToAbsentRule }} late = 1 absent · Per-day = Basic ÷ 26</span>
                <span class="gen-time">{{ $activeBranch }} · {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ══ EDIT MODAL ══ --}}
<div class="modal-overlay no-print" id="payrollEditModal">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h3 id="modal-emp-name">Employee</h3>
                <p id="modal-emp-meta">—</p>
            </div>
            <button class="modal-close" onclick="closeEditModal()">×</button>
        </div>
        <div class="modal-body">

            <div class="modal-section">
                <div class="modal-section-title">📊 Attendance Summary</div>
                <div class="att-summary">
                    <div class="att-box present"><div class="att-num" id="m-present">0</div><div class="att-lbl">Present</div></div>
                    <div class="att-box absent"> <div class="att-num" id="m-absent">0</div> <div class="att-lbl">Absent</div></div>
                    <div class="att-box leave">  <div class="att-num" id="m-leave">0</div>  <div class="att-lbl">Leave</div></div>
                    <div class="att-box late">   <div class="att-num" id="m-late">0</div>   <div class="att-lbl">Late</div></div>
                    <div class="att-box night">  <div class="att-num" id="m-night-disp">0</div><div class="att-lbl">Nights</div></div>
                </div>
            </div>

            <div class="modal-section">
                <div class="modal-section-title">💼 Allowances & Commission</div>
                <div class="field-grid">
                    <div class="field-group"><label>🚲 Bike Allowance</label><input type="number" id="m-bike" placeholder="0" min="0" oninput="recalc()"></div>
                    <div class="field-group"><label>📱 Mobile Allowance</label><input type="number" id="m-mobile" placeholder="0" min="0" oninput="recalc()"></div>
                    <div class="field-group"><label>📦 Other Allowance</label><input type="number" id="m-other" placeholder="0" min="0" oninput="recalc()"></div>
                    <div class="field-group"><label>💼 Commission</label><input type="number" id="m-commission" placeholder="0" min="0" oninput="recalc()"></div>
                </div>
            </div>

            <div class="modal-section">
                <div class="modal-section-title">⏱ Overtime & 🌙 Night & 💳 Advance</div>
                <div class="field-grid three">
                    <div class="field-group">
                        <label>⏱ Overtime Hours</label>
                        <input type="number" id="m-overtime" placeholder="0" min="0" step="0.5" oninput="recalc()">
                        <span class="hint" id="m-ot-hint">@ Rs —/hr</span>
                    </div>
                    <div class="field-group">
                        <label>🌙 Night Duties</label>
                        <input type="number" id="m-night" placeholder="0" min="0" oninput="recalc()">
                        <span class="hint" id="m-night-hint">@ Rs —/night</span>
                    </div>
                    <div class="field-group">
                        <label>💳 Advance</label>
                        <input type="number" id="m-advance" placeholder="0" min="0" oninput="recalc()">
                        <span class="hint">Subtract hoga</span>
                    </div>
                </div>
            </div>

            <div class="modal-section">
                <div class="modal-section-title">✏️ Manual Override (Optional)</div>
                <div class="field-grid">
                    <div class="field-group">
                        <label>Net Salary Override</label>
                        <input type="number" id="m-manual-net" placeholder="Empty = auto calculate" min="0" oninput="recalc()">
                        <span class="hint">Fill karne se auto override hogi</span>
                    </div>
                    <div class="field-group">
                        <label>Absent Deduct/Day</label>
                        <input type="number" id="m-absent-deduct" placeholder="0" min="0" oninput="recalc()">
                    </div>
                </div>
            </div>

            <div class="live-preview">
                <div class="lp-title">⚡ Live Preview</div>
                <div class="lp-row"><span class="lp-label">Earned Basic</span><span class="lp-val" id="lp-basic">Rs 0</span></div>
                <div class="lp-row"><span class="lp-label">+ Allowances</span><span class="lp-val pos" id="lp-allow">Rs 0</span></div>
                <div class="lp-row"><span class="lp-label">+ Night Pay</span><span class="lp-val pos" id="lp-night">Rs 0</span></div>
                <div class="lp-row"><span class="lp-label">+ Overtime</span><span class="lp-val pos" id="lp-ot">Rs 0</span></div>
                <hr class="lp-divider">
                <div class="lp-row"><span class="lp-label">= Gross</span><span class="lp-val gold lp-total" id="lp-gross">Rs 0</span></div>
                <div class="lp-row"><span class="lp-label">− Deductions</span><span class="lp-val neg" id="lp-deduct">Rs 0</span></div>
                <div class="lp-row"><span class="lp-label">− Advance</span><span class="lp-val neg" id="lp-advance">Rs 0</span></div>
                <hr class="lp-divider">
                <div class="lp-row"><span class="lp-label">✅ Net Payable</span><span class="lp-val pos lp-total" id="lp-net" style="font-size:15px;">Rs 0</span></div>
                <div style="margin-top:5px;font-size:9px;color:#93c5fd;" id="lp-override-note"></div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel" onclick="closeEditModal()">Cancel</button>
            <button class="btn-save" onclick="savePayroll()">💾 Save</button>
        </div>
    </div>
</div>

<script>
const CSRF = "{{ csrf_token() }}";
const MONTH = {{ $month }};
const YEAR  = {{ $year }};
const LATE_TO_ABSENT = {{ $lateToAbsentRule }};
let currentEmpId = null;
let currentData  = {};

function openEditModal(data) {
    currentEmpId = data.id;
    currentData  = data;
    document.getElementById('modal-emp-name').textContent = data.name;
    document.getElementById('modal-emp-meta').textContent =
        `Basic: Rs ${fmt(data.basic)}  ·  /day: Rs ${fmt(data.perDay)}  ·  /hr: Rs ${fmt(data.perHour)}`;
    document.getElementById('m-present').textContent    = data.presentDays;
    document.getElementById('m-absent').textContent     = data.absentDays + (data.lateAbsents > 0 ? '+'+data.lateAbsents+'L' : '');
    document.getElementById('m-leave').textContent      = data.leaveDays;
    document.getElementById('m-late').textContent       = data.lateDays;
    document.getElementById('m-night-disp').textContent = data.nightDuties;
    document.getElementById('m-ot-hint').textContent    = `@ Rs ${fmt(data.perHour)}/hr`;
    document.getElementById('m-night-hint').textContent = `@ Rs ${fmt(data.nightRate)}/night`;
    setVal('m-bike',         data.bikeAllow);
    setVal('m-mobile',       data.mobileAllow);
    setVal('m-other',        data.otherAllow);
    setVal('m-commission',   data.commission);
    setVal('m-overtime',     data.overtime);
    setVal('m-night',        data.nightDuties);
    setVal('m-advance',      data.advance);
    setVal('m-absent-deduct',data.absentDeductPer);
    document.getElementById('m-manual-net').value = data.manualNet !== null ? data.manualNet : '';
    recalc();
    document.getElementById('payrollEditModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('payrollEditModal').classList.remove('open');
}
function setVal(id, val) {
    document.getElementById(id).value = (val && val != 0) ? val : '';
}
function getNum(id) {
    return parseFloat(document.getElementById(id).value) || 0;
}
function fmt(n) {
    return Number(n).toLocaleString('en-PK');
}

function recalc() {
    const d = currentData;
    const bike       = getNum('m-bike');
    const mobile     = getNum('m-mobile');
    const other      = getNum('m-other');
    const commission = getNum('m-commission');
    const overtimeH  = getNum('m-overtime');
    const nightD     = getNum('m-night');
    const advance    = getNum('m-advance');
    const absentDed  = getNum('m-absent-deduct');
    const manualNet  = document.getElementById('m-manual-net').value;

    const lateAbsents   = Math.floor(d.lateDays / LATE_TO_ABSENT);
    const effectPresent = d.presentDays + d.leaveDays - lateAbsents;
    const earnedBasic   = Math.max(0, effectPresent) * d.perDay;
    const totalAllow    = bike + mobile + other + commission;
    const nightPay      = nightD * d.nightRate;
    const otPay         = overtimeH * d.perHour;
    const gross         = earnedBasic + totalAllow + nightPay + otPay;
    const deductions    = (d.lateDays * (d.lateDeductPer || 0)) + ((d.absentDays + lateAbsents) * absentDed);
    const autoNet       = gross - deductions - advance;
    const isOverride    = manualNet !== '' && manualNet !== null;
    const netDisplay    = isOverride ? parseFloat(manualNet) : autoNet;

    document.getElementById('lp-basic').textContent   = 'Rs ' + fmt(earnedBasic);
    document.getElementById('lp-allow').textContent   = 'Rs ' + fmt(totalAllow);
    document.getElementById('lp-night').textContent   = 'Rs ' + fmt(nightPay);
    document.getElementById('lp-ot').textContent      = 'Rs ' + fmt(otPay);
    document.getElementById('lp-gross').textContent   = 'Rs ' + fmt(gross);
    document.getElementById('lp-deduct').textContent  = 'Rs ' + fmt(deductions);
    document.getElementById('lp-advance').textContent = 'Rs ' + fmt(advance);
    document.getElementById('lp-net').textContent     = 'Rs ' + fmt(netDisplay);
    document.getElementById('lp-override-note').textContent = isOverride
        ? '⚠️ Manual override active'
        : '✅ Auto-calculated';
}

function savePayroll() {
    const payload = {
        employee_id:       currentEmpId,
        month:             MONTH,
        year:              YEAR,
        bike_allowance:    getNum('m-bike'),
        mobile_allowance:  getNum('m-mobile'),
        other_allowance:   getNum('m-other'),
        commission:        getNum('m-commission'),
        overtime:          getNum('m-overtime'),
        night_duties:      getNum('m-night'),
        advance:           getNum('m-advance'),
        absent_deduction:  getNum('m-absent-deduct'),
        manual_net_salary: document.getElementById('m-manual-net').value || null,
    };
    fetch('/payroll/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) { closeEditModal(); window.location.reload(); }
        else alert('❌ Error: ' + (res.message || 'Save fail ho gaya'));
    })
    .catch(() => alert('❌ Network error. Dobara try karein.'));
}

document.getElementById('payrollEditModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

</x-layout>