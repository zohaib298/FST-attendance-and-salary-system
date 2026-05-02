<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <x-sidebar />

    <main class="flex-1 p-8">

        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">HR Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">
                Manage employees and daily attendance system
            </p>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">

            <div class="bg-white p-4 rounded shadow border">
                <p class="text-gray-500 text-sm">Total Employees</p>
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ $employees->count() ?? 0 }}
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

        <!-- FILTER -->
        <form method="GET" class="mb-6 flex gap-2">

            <input type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search employee..."
                class="border px-3 py-2 rounded">

            <select name="branch" class="border px-3 py-2 rounded">
                <option value="">All Branches</option>
                <option value="Lahore" {{ request('branch') == 'Lahore' ? 'selected' : '' }}>Lahore</option>
                <option value="Karachi" {{ request('branch') == 'Karachi' ? 'selected' : '' }}>Karachi</option>
                <option value="Rawalpindi" {{ request('branch') == 'Rawalpindi' ? 'selected' : '' }}>Rawalpindi</option>
            </select>

            <button class="bg-black text-white px-4 py-2 rounded">
                Filter
            </button>

        </form>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- ================= ATTENDANCE TABLE ================= -->
        <div class="bg-white border rounded-lg shadow-sm">

            <!-- HEADER -->
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-700">
                    Daily Attendance
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
                                <th class="p-3 text-left">Branch</th>

                                <!-- NEW -->
                                <th class="p-3 text-center">Check In</th>
                                <th class="p-3 text-center">Check Out</th>

                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">

                            @forelse($employees as $emp)

                                @php
                                    $att = $todayAttendance[$emp->id] ?? null;
                                @endphp

                                <tr class="hover:bg-gray-50">

                                    <td class="p-3 font-medium text-gray-800">
                                        {{ $emp->name }}
                                    </td>

                                    <td class="p-3 text-gray-600">
                                        {{ $emp->branch }}
                                    </td>

                                    <!-- CHECK IN -->
                                    <td class="p-3 text-center">
                                        <input type="time"
                                            name="checkin[{{ $emp->id }}]"
                                            value="{{ $att->check_in ?? '' }}"
                                            class="border rounded px-2 py-1">
                                    </td>

                                    <!-- CHECK OUT -->
                                    <td class="p-3 text-center">
                                        <input type="time"
                                            name="checkout[{{ $emp->id }}]"
                                            value="{{ $att->check_out ?? '' }}"
                                            class="border rounded px-2 py-1">
                                    </td>

                                    <!-- STATUS -->
                                    <td class="p-3 text-center">

                                        <select name="attendance[{{ $emp->id }}]"
                                            class="border rounded px-2 py-1">

                                            <option value="present" {{ ($att->status ?? '') == 'present' ? 'selected' : '' }}>
                                                🟢 Present
                                            </option>

                                            <option value="absent" {{ ($att->status ?? '') == 'absent' ? 'selected' : '' }}>
                                                🔴 Absent
                                            </option>

                                            <option value="leave" {{ ($att->status ?? '') == 'leave' ? 'selected' : '' }}>
                                                🟡 Leave
                                            </option>

                                        </select>

                                        <div class="text-xs mt-1">
                                            <span class="
                                                {{ ($att->status ?? '') == 'present' ? 'text-green-600' : '' }}
                                                {{ ($att->status ?? '') == 'absent' ? 'text-red-600' : '' }}
                                                {{ ($att->status ?? '') == 'leave' ? 'text-yellow-600' : '' }}
                                            ">
                                                {{ ucfirst($att->status ?? 'present') }}
                                            </span>
                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5" class="text-center p-4 text-gray-500">
                                        No employees found
                                    </td>
                                </tr>

                            @endforelse

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