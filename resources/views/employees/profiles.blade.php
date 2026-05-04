<x-layout>

<div class="flex min-h-screen bg-gray-50">

    <x-sidebar />

    <div class="flex-1 p-8">

        <!-- HEADER -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                        Employees Directory
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">HR Management System</p>
                </div>
                <a href="/employees/create"
                   class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl shadow-md text-sm font-semibold transition">
                    + Add Employee
                </a>
            </div>

            <!-- SEARCH -->
            <div class="mt-6">
                <form method="GET">
                    <div class="relative w-full max-w-md">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search employee by name, CNIC, department..."
                            class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl shadow-sm
                                   focus:ring-2 focus:ring-red-500 focus:border-red-500
                                   bg-white text-sm transition"
                        >
                        <div class="absolute left-3 top-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- GRID LAYOUT - No Slider, Fixed Height Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            
            @forelse($employees as $emp)

            @php
                $empAtt  = $attendanceData[$emp->id] ?? collect();
                $advance = (float) $empAtt->sum('advance');
                $night   = (float) $empAtt->sum('night');

                $totalAllowance =
                    (float) ($emp->bike_allowance ?? 0) +
                    (float) ($emp->mobile_allowance ?? 0) +
                    (float) ($emp->overtime_rate ?? 0) +
                    (float) ($emp->commission ?? 0) +
                    (float) ($emp->other_allowance ?? 0) +
                    $night;

                $totalDeduction =
                    (float) ($emp->late_deduction ?? 0) +
                    (float) ($emp->absent_deduction ?? 0) +
                    $advance;

                $finalSalary = (float) $emp->basic_salary + $totalAllowance - $totalDeduction;
            @endphp

            <!-- CARD - Fixed Height -->
            <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col h-full">

                <!-- CARD HEADER -->
                <div class="bg-gradient-to-r from-red-600 to-red-500 px-5 py-4 rounded-t-2xl">
                    <h2 class="text-lg font-bold text-white truncate">{{ $emp->name }}</h2>
                    <p class="text-xs text-red-100 mt-1 truncate">
                        {{ $emp->department ?? 'N/A' }} 
                        @if($emp->branch) • {{ $emp->branch }} @endif
                    </p>
                </div>

                <!-- CARD BODY -->
                <div class="p-4 flex-1">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">CNIC</span>
                            <span class="font-medium text-gray-800">{{ $emp->cnic ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Contact</span>
                            <span class="font-medium text-gray-800">{{ $emp->contact ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Joining Date</span>
                            <span class="font-medium text-gray-800">{{ $emp->joining_date ? date('d M Y', strtotime($emp->joining_date)) : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm border-t border-gray-100 pt-2 mt-2">
                            <span class="text-gray-500">Basic Salary</span>
                            <span class="font-bold text-green-600">Rs. {{ number_format($emp->basic_salary, 0) }}</span>
                        </div>
                    </div>

                    <!-- Final Salary -->
                    <div class="mt-3 p-2 rounded-lg bg-gray-50">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-gray-600">Final Salary</span>
                            <span class="text-lg font-bold {{ $finalSalary < 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rs. {{ number_format($finalSalary, 0) }}
                            </span>
                        </div>
                    </div>

                    <!-- Toggle Button -->
                    <button onclick="toggleDetails({{ $emp->id }}, this)"
                        class="mt-3 w-full text-gray-500 text-xs font-medium flex items-center justify-center gap-1 hover:text-red-600 transition py-1">
                        <svg class="w-3 h-3 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        View Details
                    </button>
                </div>

                <!-- EXPANDABLE DETAILS (Fixed size, opens below) -->
                <div id="details-{{ $emp->id }}"
                     class="max-h-0 overflow-hidden transition-all duration-300 bg-gray-50 border-t border-gray-100">
                    <div class="p-4 space-y-3 text-sm">
                        <!-- Allowances -->
                        <div>
                            <h4 class="text-xs font-bold text-green-600 mb-2">Allowances</h4>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs"><span class="text-gray-500">Bike</span><span>Rs. {{ number_format($emp->bike_allowance ?? 0, 0) }}</span></div>
                                <div class="flex justify-between text-xs"><span class="text-gray-500">Mobile</span><span>Rs. {{ number_format($emp->mobile_allowance ?? 0, 0) }}</span></div>
                                <div class="flex justify-between text-xs"><span class="text-gray-500">Overtime</span><span>Rs. {{ number_format($emp->overtime_rate ?? 0, 0) }}</span></div>
                                <div class="flex justify-between text-xs"><span class="text-gray-500">Commission</span><span>Rs. {{ number_format($emp->commission ?? 0, 0) }}</span></div>
                                <div class="flex justify-between text-xs"><span class="text-gray-500">Other</span><span>Rs. {{ number_format($emp->other_allowance ?? 0, 0) }}</span></div>
                                <div class="flex justify-between text-xs font-semibold text-green-700 border-t border-green-200 pt-1 mt-1">
                                    <span>Night Bonus</span><span>Rs. {{ number_format($night, 0) }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-bold bg-green-50 p-1 rounded">
                                    <span>Total</span><span>Rs. {{ number_format($totalAllowance, 0) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Deductions -->
                        <div>
                            <h4 class="text-xs font-bold text-red-600 mb-2">Deductions</h4>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs"><span class="text-gray-500">Late</span><span>Rs. {{ number_format($emp->late_deduction ?? 0, 0) }}</span></div>
                                <div class="flex justify-between text-xs"><span class="text-gray-500">Absent</span><span>Rs. {{ number_format($emp->absent_deduction ?? 0, 0) }}</span></div>
                                <div class="flex justify-between text-xs font-semibold text-red-700 border-t border-red-200 pt-1 mt-1">
                                    <span>Advance</span><span>Rs. {{ number_format($advance, 0) }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-bold bg-red-50 p-1 rounded">
                                    <span>Total</span><span>Rs. {{ number_format($totalDeduction, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD FOOTER -->
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex gap-2">
                    <a href="/employees/{{ $emp->id }}/edit"
                       class="flex-1 text-center bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                        Edit
                    </a>
                    <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('Delete this employee?')" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                            Delete
                        </button>
                    </form>
                </div>

            </div>

            @empty
                <div class="col-span-full text-gray-500 p-8 text-center">
                    No employees found. Click "Add Employee" to get started.
                </div>
            @endforelse

        </div>

    </div>

</div>

<script>
function toggleDetails(id, btn) {
    let el = document.getElementById('details-' + id);
    let icon = btn.querySelector('svg');
    
    if (el.style.maxHeight && el.style.maxHeight !== "0px") {
        el.style.maxHeight = "0px";
        if (icon) icon.style.transform = "rotate(0deg)";
    } else {
        // Close all other open details first
        document.querySelectorAll('[id^="details-"]').forEach(other => {
            if (other.id !== 'details-' + id) {
                other.style.maxHeight = "0px";
                let otherBtn = document.querySelector(`[onclick*="toggleDetails(${other.id.split('-')[1]})"]`);
                if (otherBtn) {
                    let otherIcon = otherBtn.querySelector('svg');
                    if (otherIcon) otherIcon.style.transform = "rotate(0deg)";
                }
            }
        });
        
        el.style.maxHeight = el.scrollHeight + "px";
        if (icon) icon.style.transform = "rotate(180deg)";
    }
}
</script>

</x-layout>