<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <x-sidebar />

    <div class="flex-1 p-6">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">

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

        <!-- GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach($employees as $emp)

            <div class="bg-white rounded-xl shadow hover:shadow-lg transition">

                <!-- HEADER -->
                <div class="bg-red-600 text-white p-4 rounded-t-xl">

                    <h2 class="text-lg font-semibold">
                        {{ $emp->name }}
                    </h2>

                    <p class="text-xs opacity-90">
                        {{ $emp->department }} • {{ $emp->branch }}
                    </p>

                </div>

                <!-- BODY -->
                <div class="p-4 text-sm space-y-2">

                    <div class="flex justify-between">
                        <span class="text-gray-500">CNIC</span>
                        <span>{{ $emp->cnic }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Salary</span>
                        <span class="text-green-600 font-bold">
                            {{ number_format($emp->basic_salary,0) }}
                        </span>
                    </div>

                    <hr>

                    <!-- SIMPLE STATS -->
                    <div class="grid grid-cols-2 gap-2 text-xs">

                        <div class="bg-gray-100 p-2 rounded">
                            <p class="text-gray-500">Bike</p>
                            <p class="font-semibold">{{ $emp->bike_allowance }}</p>
                        </div>

                        <div class="bg-gray-100 p-2 rounded">
                            <p class="text-gray-500">Mobile</p>
                            <p class="font-semibold">{{ $emp->mobile_allowance }}</p>
                        </div>

                        <div class="bg-gray-100 p-2 rounded">
                            <p class="text-gray-500">Late</p>
                            <p class="font-semibold text-red-500">{{ $emp->late_deduction }}</p>
                        </div>

                        <div class="bg-gray-100 p-2 rounded">
                            <p class="text-gray-500">Absent</p>
                            <p class="font-semibold text-red-500">{{ $emp->absent_deduction }}</p>
                        </div>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="flex justify-between items-center bg-gray-50 p-3 rounded-b-xl">

                    <!-- EDIT -->
                    <a href="/employees/{{ $emp->id }}/edit"
                       class="text-yellow-600 text-sm font-semibold hover:underline">
                        Edit
                    </a>

                    <!-- SALARY SLIP -->
                    <a href="/salary-slip/{{ $emp->id }}/{{ date('m') }}"
                       class="bg-black text-white px-3 py-1 rounded text-xs">
                        Slip
                    </a>

                </div>

            </div>

            @endforeach

        </div>

    </div>

</div>

</x-layout>