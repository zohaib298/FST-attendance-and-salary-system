<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <x-sidebar />

    <div class="flex-1 p-6">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-4">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Employees Directory
                </h1>
                <p class="text-sm text-gray-500">HR System</p>
            </div>

            <a href="/employees/create"
               class="bg-red-600 text-white px-5 py-2 rounded hover:bg-red-700">
                + Add Employee
            </a>

        </div>

        <!-- 🔍 SEARCH -->
        <form method="GET" action="{{ route('employees.profiles') }}" class="mb-6 flex gap-2">

            <input 
                type="text"
                name="search"
                id="searchBox"
                value="{{ request('search') }}"
                placeholder="Search employee..."
                class="border px-3 py-2 rounded w-72"
            >

        </form>

        <!-- GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

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

            <div class="bg-white rounded-xl shadow hover:shadow-lg transition">

                <!-- HEADER -->
                <div class="bg-red-600 text-white p-4 rounded-t-xl">
                    <h2 class="text-lg font-semibold">{{ $emp->name }}</h2>
                    <p class="text-xs opacity-90">
                        {{ $emp->department }} • {{ $emp->branch }}
                    </p>
                </div>

                <!-- BODY -->
                <div class="p-4 text-sm space-y-2 text-gray-700">

                    <div class="flex justify-between">
                        <span>CNIC</span>
                        <span>{{ $emp->cnic }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Basic Salary</span>
                        <span class="text-green-600 font-bold">
                            {{ number_format($emp->basic_salary,0) }}
                        </span>
                    </div>

                    <hr>

                    <p class="text-green-600 font-semibold mt-2">Allowances</p>

                    <div class="flex justify-between">
                        <span>Bike</span>
                        <span>{{ number_format($emp->bike_allowance,0) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Mobile</span>
                        <span>{{ number_format($emp->mobile_allowance,0) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Overtime</span>
                        <span>{{ number_format($emp->overtime_rate,0) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Commission</span>
                        <span>{{ number_format($emp->commission,0) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Other</span>
                        <span>{{ number_format($emp->other_allowance,0) }}</span>
                    </div>

                    <hr>

                    <p class="text-red-600 font-semibold mt-2">Deductions</p>

                    <div class="flex justify-between">
                        <span>Late</span>
                        <span>{{ number_format($emp->late_deduction,0) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Absent</span>
                        <span>{{ number_format($emp->absent_deduction,0) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Advance</span>
                        <span>{{ number_format($emp->advance ?? 0,0) }}</span>
                    </div>

                    <hr>

                    <div class="flex justify-between font-bold text-lg">
                        <span>Final Salary</span>
                        <span class="text-green-700">
                            {{ number_format($finalSalary, 0) }}
                        </span>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="flex justify-between items-center bg-gray-50 p-3 rounded-b-xl">

                    <a href="/employees/{{ $emp->id }}/edit"
                       class="text-yellow-600 text-sm font-semibold hover:underline">
                        Edit
                    </a>

                </div>

            </div>

            @empty
                <div class="col-span-3 text-center text-gray-500">
                    No employee found
                </div>
            @endforelse

        </div>

    </div>

</div>

<!-- 🔥 AUTO RESET SEARCH -->
<script>
document.getElementById('searchBox').addEventListener('input', function () {
    if (this.value.length === 0) {
        window.location.href = "{{ route('employees.profiles') }}";
    }
});
</script>

</x-layout>