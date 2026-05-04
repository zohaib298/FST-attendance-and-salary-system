<x-layout>

@php
    $selectedMonth = request('month', now()->format('Y-m'));
    $selectedMonthLabel = \Carbon\Carbon::parse($selectedMonth)->format('F Y');
    $csrfToken = csrf_token();
    
    // Past month check - agar selected month current ya future hai toh editing band
    $isPastMonth = \Carbon\Carbon::parse($selectedMonth)->startOfMonth()->lt(now()->startOfMonth());
@endphp

<div class="flex min-h-screen bg-slate-100">
    <x-sidebar />
    <div class="flex-1">

        <!-- HEADER -->
        <div class="bg-white border-b shadow-sm sticky top-0 z-10">
            <div class="px-6 py-4">
                <h1 class="text-2xl font-bold text-gray-800">Attendance Report</h1>
                <p class="text-xs text-gray-500">Month ke hisab se attendance dekhein aur edit karein</p>
            </div>
        </div>

        <div class="p-6 space-y-6">

            <!-- MONTH FILTER -->
            <form method="GET" class="bg-white border rounded-xl p-5 shadow-sm flex flex-col md:flex-row gap-4 md:items-end">
                <div class="w-full md:w-1/3">
                    <label class="text-xs text-gray-500">Month Select Karein</label>
                    <select name="month" class="w-full mt-1 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200">
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($m)->format('F Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/3">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Employee search..."
                        class="w-full mt-1 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200">
                </div>
                <div class="w-full md:w-1/3">
                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition">
                        Apply Filter
                    </button>
                </div>
            </form>

            {{-- Current/Future month notice --}}
            @if(!$isPastMonth)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3">
                <span class="text-amber-500 text-xl">🔒</span>
                <div>
                    <p class="text-sm font-medium text-amber-800">Manual Editing Disabled</p>
                    <p class="text-xs text-amber-600">Sirf past months mein manual edit ho sakta hai. Current aur future months mein attendance automatic system se hoti hai.</p>
                </div>
            </div>
            @endif

            <!-- SUMMARY TABLE -->
            <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="p-4 text-left">Employee</th>
                            <th class="p-4 text-center">Present</th>
                            <th class="p-4 text-center">Absent</th>
                            <th class="p-4 text-center">Leave</th>
                            <th class="p-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($summaries as $row)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-medium text-gray-800">{{ $row['name'] }}</td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">{{ $row['present'] }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">{{ $row['absent'] }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium">{{ $row['leave'] }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex gap-2 justify-center">
                                    @if($isPastMonth)
                                        {{-- Sirf past month mein Edit button dikhega --}}
                                        <button onclick="openModal({{ $row['employee_id'] }}, '{{ addslashes($row['name']) }}')"
                                            class="text-xs bg-gray-900 hover:bg-black text-white px-3 py-1.5 rounded-md transition">
                                            Edit Dates
                                        </button>
                                    @else
                                        {{-- Current/Future month mein disabled badge --}}
                                        <span class="text-xs bg-gray-100 text-gray-400 px-3 py-1.5 rounded-md cursor-not-allowed" title="Sirf past months edit ho sakte hain">
                                            🔒 Auto
                                        </span>
                                    @endif
                                    <button onclick="openPrintSlip({{ $row['employee_id'] }}, '{{ addslashes($row['name']) }}')"
                                        class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-md transition">
                                        Print Slip
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center p-8 text-gray-400">Koi record nahi mila</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-4xl mx-4 shadow-xl max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between p-5 border-b">
            <h2 class="font-semibold text-gray-800" id="modalTitle">Edit Attendance</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        
        <!-- BULK TIME APPLY SECTION -->
        <div class="p-5 border-b bg-gray-50">
            <div class="flex items-end gap-4 flex-wrap">
                <div class="text-sm font-medium text-gray-700">⚡ Bulk Apply Time:</div>
                <div>
                    <label class="text-xs text-gray-500 block">Check-in Time</label>
                    <input type="time" id="bulkCheckIn" value="09:30" 
                        class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block">Check-out Time</label>
                    <input type="time" id="bulkCheckOut" value="19:00" 
                        class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200">
                </div>
                <button onclick="applyBulkTimeToAllDays()"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                    📌 Apply to ALL Days
                </button>
                <button onclick="applyBulkTimeToPresentDays()"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                    ✅ Apply to Present Days Only
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-2">⚠️ "Apply to ALL Days" sabhi (Present/Absent/Leave) days me lag jayega. "Apply to Present Days Only" sirf Present wale days me lagega.</p>
        </div>
        
        <div class="overflow-y-auto flex-1 p-5 space-y-2" id="modalBody">
            <p class="text-gray-400 text-sm text-center">Loading...</p>
        </div>
        <div class="p-5 border-t flex justify-end">
            <button onclick="saveChanges()"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- PRINT SLIP MODAL -->
<div id="printSlipModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-5xl mx-4 shadow-xl max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-5 border-b">
            <h2 class="font-semibold text-gray-800" id="printSlipTitle">Attendance Slip</h2>
            <div class="flex gap-2 items-center">
                <button onclick="triggerPrint()"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition">
                    🖨️ Print
                </button>
                <button onclick="closePrintSlip()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
        </div>
        <div class="overflow-y-auto flex-1 p-6" id="printSlipBody">
            <p class="text-gray-400 text-sm text-center">Loading...</p>
        </div>
    </div>
</div>

<script>
let currentEmployeeId = null;
let currentEmployeeName = null;
let attendanceData = {};
const selectedMonth = "{{ $selectedMonth }}";
const selectedMonthLabel = "{{ $selectedMonthLabel }}";
const csrfToken = "{{ $csrfToken }}";
const isPastMonth = {{ $isPastMonth ? 'true' : 'false' }};

const DEFAULT_CHECK_IN = "09:30";
const DEFAULT_CHECK_OUT = "19:00";

// ==================== EDIT MODAL ====================
function openModal(empId, empName) {
    // Safety check — agar past month nahi toh modal nahi khulega
    if (!isPastMonth) {
        alert('⚠️ Sirf past months ki attendance manually edit ho sakti hai.');
        return;
    }

    currentEmployeeId = empId;
    currentEmployeeName = empName;
    document.getElementById('modalTitle').textContent = empName + ' — ' + selectedMonthLabel;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('modalBody').innerHTML = '<p class="text-gray-400 text-sm text-center">Loading...</p>';

    fetch(`/attendance/dates?employee_id=${empId}&month=${selectedMonth}`)
        .then(r => r.json())
        .then(data => {
            attendanceData = {};
            let html = `
            <div class="mb-3 pb-2 border-b sticky top-0 bg-white">
                <div class="grid grid-cols-6 gap-2 text-xs font-semibold text-gray-500 px-2 pb-2">
                    <div>Date</div>
                    <div>Status</div>
                    <div>Check-in</div>
                    <div>Check-out</div>
                    <div>Overtime</div>
                    <div>Nights / Advance</div>
                </div>
            </div>
            `;
            data.forEach(row => {
                let checkIn = row.check_in || '';
                let checkOut = row.check_out || '';
                if (row.status === 'present') {
                    if (!checkIn) checkIn = DEFAULT_CHECK_IN;
                    if (!checkOut) checkOut = DEFAULT_CHECK_OUT;
                }
                
                attendanceData[row.date] = {
                    status: row.status,
                    check_in: checkIn,
                    check_out: checkOut,
                    overtime: row.overtime || '',
                    nights: row.nights || '',
                    advance: row.advance || ''
                };
                html += `
                <div class="grid grid-cols-6 gap-2 items-center py-2 border-b last:border-0 px-2">
                    <div class="text-sm text-gray-700 font-medium">${formatDate(row.date)}</div>
                    <div>
                        <select data-date="${row.date}" data-field="status"
                            class="status-select w-full border rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-200">
                            <option value="present" ${row.status === 'present' ? 'selected' : ''}>Present</option>
                            <option value="absent"  ${row.status === 'absent'  ? 'selected' : ''}>Absent</option>
                            <option value="leave"   ${row.status === 'leave'   ? 'selected' : ''}>Leave</option>
                        </select>
                    </div>
                    <div>
                        <input type="time" data-date="${row.date}" data-field="check_in"
                            value="${checkIn}" placeholder="Check-in"
                            class="check-in w-full border rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-200"
                            ${row.status !== 'present' ? 'disabled' : ''}>
                    </div>
                    <div>
                        <input type="time" data-date="${row.date}" data-field="check_out"
                            value="${checkOut}" placeholder="Check-out"
                            class="check-out w-full border rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-200"
                            ${row.status !== 'present' ? 'disabled' : ''}>
                    </div>
                    <div>
                        <input type="text" data-date="${row.date}" data-field="overtime"
                            value="${row.overtime || ''}" placeholder="e.g., 2hr, 1.5hr"
                            class="overtime w-full border rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div>
                        <input type="text" data-date="${row.date}" data-field="nights_advance"
                            value="${row.nights || row.advance || ''}" placeholder="Nights / Advance"
                            class="nights-advance w-full border rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-200">
                    </div>
                </div>`;
            });
            document.getElementById('modalBody').innerHTML = html;

            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', function() {
                    const date = this.dataset.date;
                    const checkInInput = document.querySelector(`input[data-date="${date}"].check-in`);
                    const checkOutInput = document.querySelector(`input[data-date="${date}"].check-out`);
                    
                    if (this.value === 'present') {
                        checkInInput.disabled = false;
                        checkOutInput.disabled = false;
                        if (!checkInInput.value) checkInInput.value = DEFAULT_CHECK_IN;
                        if (!checkOutInput.value) checkOutInput.value = DEFAULT_CHECK_OUT;
                    } else {
                        checkInInput.disabled = true;
                        checkOutInput.disabled = true;
                        checkInInput.value = '';
                        checkOutInput.value = '';
                    }
                });
            });
        });
}

function applyBulkTimeToAllDays() {
    const bulkCheckIn = document.getElementById('bulkCheckIn').value;
    const bulkCheckOut = document.getElementById('bulkCheckOut').value;
    
    document.querySelectorAll('#modalBody .check-in').forEach(input => {
        input.value = bulkCheckIn;
    });
    document.querySelectorAll('#modalBody .check-out').forEach(input => {
        input.value = bulkCheckOut;
    });
    
    alert(`✅ Applied ${bulkCheckIn} - ${bulkCheckOut} to ALL days of ${currentEmployeeName}`);
}

function applyBulkTimeToPresentDays() {
    const bulkCheckIn = document.getElementById('bulkCheckIn').value;
    const bulkCheckOut = document.getElementById('bulkCheckOut').value;
    let appliedCount = 0;
    
    document.querySelectorAll('#modalBody .check-in').forEach(input => {
        if (!input.disabled) {
            input.value = bulkCheckIn;
            appliedCount++;
        }
    });
    document.querySelectorAll('#modalBody .check-out').forEach(input => {
        if (!input.disabled) {
            input.value = bulkCheckOut;
        }
    });
    
    alert(`✅ Applied ${bulkCheckIn} - ${bulkCheckOut} to ${appliedCount} Present days of ${currentEmployeeName}`);
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function saveChanges() {
    const statusSelects = document.querySelectorAll('#modalBody .status-select');
    const checkIns = document.querySelectorAll('#modalBody .check-in');
    const checkOuts = document.querySelectorAll('#modalBody .check-out');
    const overtimes = document.querySelectorAll('#modalBody .overtime');
    const nightsAdvances = document.querySelectorAll('#modalBody .nights-advance');
    
    const updates = [];
    
    statusSelects.forEach(select => {
        const date = select.dataset.date;
        updates.push({
            date: date,
            status: select.value,
            check_in: '',
            check_out: '',
            overtime: '',
            nights: '',
            advance: ''
        });
    });
    
    checkIns.forEach(input => {
        const date = input.dataset.date;
        const update = updates.find(u => u.date === date);
        if (update && !input.disabled) {
            update.check_in = input.value;
        }
    });
    
    checkOuts.forEach(input => {
        const date = input.dataset.date;
        const update = updates.find(u => u.date === date);
        if (update && !input.disabled) {
            update.check_out = input.value;
        }
    });
    
    overtimes.forEach(input => {
        const date = input.dataset.date;
        const update = updates.find(u => u.date === date);
        if (update) {
            update.overtime = input.value;
        }
    });
    
    nightsAdvances.forEach(input => {
        const date = input.dataset.date;
        const update = updates.find(u => u.date === date);
        if (update) {
            const value = input.value;
            if (!isNaN(value) && value !== '') {
                update.advance = value;
                update.nights = '';
            } else {
                update.nights = value;
                update.advance = '';
            }
        }
    });

    fetch('/attendance/bulk-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ employee_id: currentEmployeeId, updates })
    })
    .then(r => r.json())
    .then(() => { closeModal(); window.location.reload(); });
}

// ==================== PRINT SLIP ====================
function openPrintSlip(empId, empName) {
    document.getElementById('printSlipTitle').textContent = empName + ' — Attendance Slip';
    document.getElementById('printSlipModal').classList.remove('hidden');
    document.getElementById('printSlipBody').innerHTML = '<p class="text-gray-400 text-sm text-center">Loading...</p>';

    fetch(`/attendance/dates?employee_id=${empId}&month=${selectedMonth}`)
        .then(r => r.json())
        .then(data => {
            const present = data.filter(x => x.status === 'present').length;
            const absent  = data.filter(x => x.status === 'absent').length;
            const leave   = data.filter(x => x.status === 'leave').length;
            
            let totalOvertime = 0;
            let totalNights = 0;
            let totalAdvance = 0;

            const badge = (s) => {
                if (s === 'present') return `<span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700 font-medium">Present</span>`;
                if (s === 'absent')  return `<span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 font-medium">Absent</span>`;
                return `<span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium">Leave</span>`;
            };

            let html = `
            <div class="mb-5 pb-4 border-b">
                <p class="text-lg font-semibold text-gray-800">${empName}</p>
                <p class="text-sm text-gray-500">${selectedMonthLabel}</p>
                <div class="flex gap-3 mt-3 flex-wrap">
                    <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">Present: ${present}</span>
                    <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">Absent: ${absent}</span>
                    <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium">Leave: ${leave}</span>
                </div>
            </div>
            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="p-2 text-left border border-gray-200">#</th>
                        <th class="p-2 text-left border border-gray-200">Date</th>
                        <th class="p-2 text-left border border-gray-200">Day</th>
                        <th class="p-2 text-center border border-gray-200">Status</th>
                        <th class="p-2 text-center border border-gray-200">Check-in</th>
                        <th class="p-2 text-center border border-gray-200">Check-out</th>
                        <th class="p-2 text-center border border-gray-200">Overtime</th>
                        <th class="p-2 text-center border border-gray-200">Nights</th>
                        <th class="p-2 text-center border border-gray-200">Advance</th>
                    </tr>
                </thead>
                <tbody>`;

            data.forEach((row, i) => {
                const dt    = new Date(row.date);
                const day   = dt.toLocaleDateString('en-PK', { weekday: 'long' });
                const isFri = dt.getDay() === 5;
                
                let checkInTime = row.check_in || '';
                let checkOutTime = row.check_out || '';
                if (row.status === 'present') {
                    if (!checkInTime) checkInTime = DEFAULT_CHECK_IN;
                    if (!checkOutTime) checkOutTime = DEFAULT_CHECK_OUT;
                }
                checkInTime = checkInTime || '-';
                checkOutTime = checkOutTime || '-';
                
                const overtime = row.overtime || '-';
                const nights = row.nights || '-';
                const advance = row.advance || '-';
                
                if (row.overtime && !isNaN(parseFloat(row.overtime))) {
                    totalOvertime += parseFloat(row.overtime);
                }
                if (row.nights && !isNaN(parseFloat(row.nights))) {
                    totalNights += parseFloat(row.nights);
                }
                if (row.advance && !isNaN(parseFloat(row.advance))) {
                    totalAdvance += parseFloat(row.advance);
                }
                
                html += `
                <tr class="${isFri ? 'bg-blue-50' : 'hover:bg-gray-50'} border-b border-gray-100">
                    <td class="p-2 text-gray-400 border border-gray-200 text-center">${i + 1}</td>
                    <td class="p-2 text-gray-700 border border-gray-200">${formatDate(row.date)}</td>
                    <td class="p-2 text-gray-500 border border-gray-200">${day}</td>
                    <td class="p-2 text-center border border-gray-200">${badge(row.status)}</td>
                    <td class="p-2 text-center border border-gray-200 font-mono text-xs">${checkInTime}</td>
                    <td class="p-2 text-center border border-gray-200 font-mono text-xs">${checkOutTime}</td>
                    <td class="p-2 text-center border border-gray-200">${overtime}</td>
                    <td class="p-2 text-center border border-gray-200">${nights}</td>
                    <td class="p-2 text-center border border-gray-200">${advance !== '-' ? 'Rs. ' + advance : '-'}</td>
                </tr>`;
            });

            html += `</tbody>
            <tfoot class="bg-gray-50">
                <tr class="font-semibold">
                    <td colspan="6" class="p-2 text-right border border-gray-200">Total:</td>
                    <td class="p-2 text-center border border-gray-200">${totalOvertime > 0 ? totalOvertime + 'hr' : '-'}</td>
                    <td class="p-2 text-center border border-gray-200">${totalNights > 0 ? totalNights : '-'}</td>
                    <td class="p-2 text-center border border-gray-200">${totalAdvance > 0 ? 'Rs. ' + totalAdvance : '-'}</td>
                </tr>
            </tfoot>
            </table>`;
            document.getElementById('printSlipBody').innerHTML = html;
        });
}

function closePrintSlip() {
    document.getElementById('printSlipModal').classList.add('hidden');
}

function triggerPrint() {
    window.print();
}

function formatDate(d) {
    const dt = new Date(d);
    return dt.toLocaleDateString('en-PK', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

</x-layout>