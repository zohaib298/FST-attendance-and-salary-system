<x-layout>

<div class="flex min-h-screen bg-gray-50">

    <x-sidebar />

    <div class="flex-1 p-8">

        <!-- ================= HEADER ================= -->
        <div class="mb-8">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">

                <!-- TITLE -->
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                        Employees Directory
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        HR Management System
                    </p>
                </div>

                <!-- ADD BUTTON -->
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
                            <i class="bi bi-search"></i>
                        </div>

                    </div>

                </form>

            </div>

        </div>

        <!-- ================= SLIDER ================= -->
        <div class="relative">

            <!-- LEFT -->
            <button onclick="move(-1)"
                class="absolute left-0 top-1/2 -translate-y-1/2 z-20 bg-white border shadow-md text-gray-700 p-3 rounded-full hover:bg-gray-100">
                <i class="bi bi-chevron-left text-xl"></i>
            </button>

            <!-- RIGHT -->
            <button onclick="move(1)"
                class="absolute right-0 top-1/2 -translate-y-1/2 z-20 bg-white border shadow-md text-gray-700 p-3 rounded-full hover:bg-gray-100">
                <i class="bi bi-chevron-right text-xl"></i>
            </button>

            <!-- VIEWPORT -->
            <div class="overflow-hidden px-10">

                <div id="slider" class="flex transition-transform duration-500">

                    @forelse($employees as $emp)

                    @php
                        $totalAllowance =
                            $emp->bike_allowance +
                            $emp->mobile_allowance +
                            $emp->overtime_rate +
                            $emp->commission +
                            $emp->other_allowance;

                        $totalDeduction =
                            $emp->late_deduction +
                            $emp->absent_deduction +
                            ($emp->advance ?? 0);

                        $finalSalary = $emp->basic_salary + $totalAllowance - $totalDeduction;
                    @endphp

                    <!-- CARD -->
                    <div class="flex-shrink-0 w-1/3 p-3">

                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition border border-gray-100">

                            <!-- HEADER -->
                            <div class="bg-gradient-to-r from-red-600 to-red-500 text-white p-4 rounded-t-2xl">
                                <h2 class="text-lg font-semibold">{{ $emp->name }}</h2>
                                <p class="text-xs opacity-90">
                                    {{ $emp->department }} • {{ $emp->branch }}
                                </p>
                            </div>

                            <!-- BODY -->
                            <div class="p-5 text-sm space-y-3">

                                <div class="flex justify-between text-gray-600">
                                    <span>CNIC</span>
                                    <span class="text-gray-900 font-medium">{{ $emp->cnic }}</span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-gray-600">Basic Salary</span>
                                    <span class="text-green-600 font-bold">
                                        {{ number_format($emp->basic_salary,0) }}
                                    </span>
                                </div>

                                <div class="border-t pt-3 flex justify-between">
                                    <span class="font-semibold text-gray-700">Final Salary</span>
                                    <span class="text-green-700 font-bold text-lg">
                                        {{ number_format($finalSalary,0) }}
                                    </span>
                                </div>

                                <!-- BUTTON -->
                                <button onclick="toggleDetails({{ $emp->id }}, this)"
                                    class="mt-3 text-gray-600 text-sm font-medium flex items-center gap-2 hover:text-red-600 transition">

                                    <i class="bi bi-chevron-down transition-transform duration-300"></i>
                                    View Full Details
                                </button>

                            </div>

                            <!-- EXPAND -->
                            <div id="details-{{ $emp->id }}"
                                 class="max-h-0 overflow-hidden transition-all duration-500 bg-gray-50 border-t px-5 text-sm space-y-5">

                                <!-- ALLOWANCES -->
                                <div class="pt-4">
                                    <h3 class="text-green-600 font-semibold mb-2">Allowances</h3>

                                    <div class="space-y-1 text-gray-700">
                                        <div class="flex justify-between"><span>Bike</span><span>{{ number_format($emp->bike_allowance,0) }}</span></div>
                                        <div class="flex justify-between"><span>Mobile</span><span>{{ number_format($emp->mobile_allowance,0) }}</span></div>
                                        <div class="flex justify-between"><span>Overtime</span><span>{{ number_format($emp->overtime_rate,0) }}</span></div>
                                        <div class="flex justify-between"><span>Commission</span><span>{{ number_format($emp->commission,0) }}</span></div>
                                        <div class="flex justify-between"><span>Other</span><span>{{ number_format($emp->other_allowance,0) }}</span></div>
                                    </div>
                                </div>

                                <!-- DEDUCTIONS -->
                                <div>
                                    <h3 class="text-red-600 font-semibold mb-2">Deductions</h3>

                                    <div class="space-y-1 text-gray-700">
                                        <div class="flex justify-between"><span>Late</span><span>{{ number_format($emp->late_deduction,0) }}</span></div>
                                        <div class="flex justify-between"><span>Absent</span><span>{{ number_format($emp->absent_deduction,0) }}</span></div>
                                        <div class="flex justify-between"><span>Advance</span><span>{{ number_format($emp->advance ?? 0,0) }}</span></div>
                                    </div>
                                </div>

                            </div>

                            <!-- FOOTER -->
                            <div class="px-5 py-3 border-t flex justify-end bg-white rounded-b-2xl">
                                <a href="/employees/{{ $emp->id }}/edit"
                                   class="text-yellow-600 text-sm font-semibold hover:underline">
                                    Edit Employee
                                </a>
                            </div>

                        </div>

                    </div>

                    @empty
                        <div class="text-gray-500">No employee found</div>
                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ICONS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- SCRIPT -->
<script>
let index = 0;

function move(step) {

    let slider = document.getElementById('slider');
    let total = slider.children.length;
    let visible = 3;

    index += step;

    if (index < 0) index = 0;
    if (index > total - visible) index = total - visible;

    slider.style.transform = "translateX(-" + (index * 33.33) + "%)";
}

function toggleDetails(id, btn) {

    let el = document.getElementById('details-' + id);
    let icon = btn.querySelector('i');

    if (el.style.maxHeight && el.style.maxHeight !== "0px") {
        el.style.maxHeight = "0px";
        icon.classList.remove('rotate-180');
    } else {
        el.style.maxHeight = el.scrollHeight + "px";
        icon.classList.add('rotate-180');
    }
}
</script>

<style>
.rotate-180 {
    transform: rotate(180deg);
}
</style>

</x-layout>