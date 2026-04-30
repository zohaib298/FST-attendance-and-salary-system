<x-layout>

<div class="flex min-h-screen bg-gray-100">

   
    <x-sidebar></x-sidebar>

  
    <div class="flex-1 p-6 bg-gray-100">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-6">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Employees Profiles
                </h1>
                <p class="text-sm text-gray-500">
                    Fire & Safety Pvt Ltd HR System
                </p>
            </div>

            <!-- BUTTON -->
            <a href="/employees/create"
               class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 transition">
                Add Employee
            </a>

        </div>

        <!-- GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach($employees as $emp)

            <div class="bg-white border rounded-lg shadow hover:shadow-lg transition overflow-hidden">

                <!-- HEADER -->
                <div class="bg-red-600 p-4 text-white">
                    <h2 class="text-lg font-semibold">
                        {{ $emp->name }}
                    </h2>
                    <p class="text-sm opacity-90">
                        {{ $emp->department }} • {{ $emp->branch }}
                    </p>
                </div>

                <!-- BODY -->
                <div class="p-5 text-sm space-y-3 text-gray-700">

                    <div class="flex justify-between">
                        <span class="text-gray-500">CNIC</span>
                        <span>{{ $emp->cnic }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Basic Salary</span>
                        <span class="font-bold text-green-600">
                            {{ number_format($emp->basic_salary,2) }}
                        </span>
                    </div>

                    <hr>

                    <div class="font-semibold text-green-600">Allowances</div>

                    <div class="flex justify-between">
                        <span>Bike</span>
                        <span>{{ number_format($emp->bike_allowance,2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Mobile</span>
                        <span>{{ number_format($emp->mobile_allowance,2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Overtime</span>
                        <span>{{ number_format($emp->overtime_rate,2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Commission</span>
                        <span>{{ number_format($emp->commission,2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Other</span>
                        <span>{{ number_format($emp->other_allowance,2) }}</span>
                    </div>

                    <hr>

                    <div class="font-semibold text-red-600">Deductions</div>

                    <div class="flex justify-between">
                        <span>Late</span>
                        <span>{{ number_format($emp->late_deduction,2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Absent</span>
                        <span>{{ number_format($emp->absent_deduction,2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Allowed Leaves</span>
                        <span class="font-semibold">
                            {{ $emp->allowed_leaves }}
                        </span>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="p-4 bg-gray-50 text-right">
                    <button class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900 transition">
                        View Profile
                    </button>
                </div>

            </div>

            @endforeach

        </div>

    </div>
</div>

</x-layout>