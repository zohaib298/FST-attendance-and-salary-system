<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <x-sidebar></x-sidebar>

    <main class="flex-1 p-8">

        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">HR Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">
                Manage employees and daily attendance system
            </p>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">

            <div class="bg-white p-4 rounded shadow border">
                <p class="text-gray-500 text-sm">Total Employees</p>
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ $employees->count() }}
                </h2>
            </div>

            <div class="bg-green-50 p-4 rounded shadow border">
                <p class="text-gray-500 text-sm">Present Today</p>
                <h2 class="text-2xl font-bold text-green-600">
                    {{ $present ?? 0 }}
                </h2>
            </div>

            <div class="bg-red-50 p-4 rounded shadow border">
                <p class="text-gray-500 text-sm">Absent Today</p>
                <h2 class="text-2xl font-bold text-red-600">
                    {{ $absent ?? 0 }}
                </h2>
            </div>

            <div class="bg-yellow-50 p-4 rounded shadow border">
                <p class="text-gray-500 text-sm">On Leave</p>
                <h2 class="text-2xl font-bold text-yellow-600">
                    {{ $leave ?? 0 }}
                </h2>
            </div>

        </div>

        <!-- ADD EMPLOYEE -->
        <div class="bg-white border rounded-lg shadow-sm mb-8">

            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-700">
                    Add New Employee
                </h2>
            </div>

            <form method="POST" action="{{ route('employees.store') }}"
                  class="p-6 grid grid-cols-1 md:grid-cols-5 gap-4">

                @csrf

                <input type="text" name="name" placeholder="Name"
                    class="border rounded px-3 py-2">

                <input type="text" name="cnic" placeholder="CNIC"
                    class="border rounded px-3 py-2">

                <input type="text" name="department" placeholder="Department"
                    class="border rounded px-3 py-2">

                <select name="branch" class="border rounded px-3 py-2">
                    <option value="">Branch</option>
                    <option>Lahore</option>
                    <option>Karachi</option>
                    <option>Rawalpindi</option>
                </select>

                <input type="number" name="basic_salary" placeholder="Salary"
                    class="border rounded px-3 py-2">

                <div class="md:col-span-5 flex justify-end">
                    <button class="bg-gray-900 text-white px-6 py-2 rounded hover:bg-black">
                        Save Employee
                    </button>
                </div>

            </form>

        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- ATTENDANCE -->
        <div class="bg-white border rounded-lg shadow-sm">

            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-700">
                    Today's Attendance
                </h2>

                <span class="text-sm text-gray-500">
                    {{ date('d M Y') }}
                </span>
            </div>

            <form method="POST" action="{{ route('attendance.store') }}">
                @csrf

                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="p-3 text-left">Employee</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>

                       <tbody class="divide-y">
@foreach($employees as $emp)

@php
    $currentStatus = $todayAttendance[$emp->id]->status ?? 'present';
@endphp

<tr class="hover:bg-gray-50">

    <td class="p-3 font-medium text-gray-800">
        {{ $emp->name }}
    </td>

    <td class="p-3 text-center">

        <!-- DROPDOWN (AUTO SELECT CURRENT STATUS) -->
        <select name="attendance[{{ $emp->id }}]"
            class="border rounded px-2 py-1">

            <option value="present" {{ $currentStatus == 'present' ? 'selected' : '' }}>
                🟢 Present
            </option>

            <option value="absent" {{ $currentStatus == 'absent' ? 'selected' : '' }}>
                🔴 Absent
            </option>

            <option value="leave" {{ $currentStatus == 'leave' ? 'selected' : '' }}>
                🟡 Leave
            </option>

        </select>

        <!-- CURRENT STATUS SHOW -->
        <div class="text-xs mt-1">
            @if($currentStatus == 'present')
                <span class="text-green-600">Present</span>
            @elseif($currentStatus == 'absent')
                <span class="text-red-600">Absent</span>
            @else
                <span class="text-yellow-600">Leave</span>
            @endif
        </div>

    </td>

</tr>
@endforeach
</tbody>

                    </table>

                </div>

                <div class="p-6 border-t flex justify-end">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Save Attendance
                    </button>
                </div>

            </form>

        </div>

    </main>

</div>

</x-layout>