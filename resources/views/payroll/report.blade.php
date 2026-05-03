<x-layout>

<div class="flex min-h-screen bg-slate-100">

    <x-sidebar />

    <div class="flex-1">

        <!-- HEADER -->
        <div class="bg-white border-b shadow-sm sticky top-0 z-10">
            <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        Attendance Report
                    </h1>
                    <p class="text-xs text-gray-500">
                        Track attendance, overtime, night duty & salary slips
                    </p>
                </div>

            </div>
        </div>

        <div class="p-6 space-y-6">

            <!-- FILTERS -->
            <form method="GET"
                  class="bg-white border rounded-xl p-5 shadow-sm flex flex-col md:flex-row gap-4 md:items-end">

                <div class="w-full md:w-1/3">
                    <label class="text-xs text-gray-500">Employee</label>
                    <select name="employee_id"
                        class="w-full mt-1 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-1/3">
                    <label class="text-xs text-gray-500">Search</label>
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search employee..."
                        class="w-full mt-1 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200">
                </div>

                <div class="w-full md:w-1/3">
                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition">
                        Apply Filter
                    </button>
                </div>

            </form>

            <!-- TABLE -->
            <div class="bg-white border rounded-xl shadow-sm overflow-hidden">

                <table class="w-full text-sm">

                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="p-4 text-left">Employee</th>
                            <th class="p-4 text-left">Date</th>
                            <th class="p-4 text-left">In</th>
                            <th class="p-4 text-left">Out</th>
                            <th class="p-4 text-center">Late</th>
                            <th class="p-4 text-center">Overtime</th>
                            <th class="p-4 text-center">Night</th>
                            <th class="p-4 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                        @forelse($attendances as $att)

                            <tr class="hover:bg-gray-50 transition">

                                <!-- NAME -->
                                <td class="p-4 font-medium text-gray-800">
                                    {{ $att->employee->name ?? 'N/A' }}
                                </td>

                                <!-- DATE -->
                                <td class="p-4 text-gray-600">
                                    {{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}
                                </td>

                                <!-- CHECK IN -->
                                <td class="p-4">
                                    {{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '--' }}
                                </td>

                                <!-- CHECK OUT -->
                                <td class="p-4">
                                    {{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '--' }}
                                </td>

                                <!-- LATE -->
                                <td class="p-4 text-center text-red-600 font-semibold">
                                    {{ $att->late ?? 0 }}
                                </td>

                                <!-- OVERTIME (FROM DB) -->
                                <td class="p-4 text-center text-blue-600 font-semibold">
                                    {{ round(($att->overtime_minutes ?? 0) / 60, 2) }} hrs
                                </td>

                                <!-- NIGHT (FROM DB) -->
                                <td class="p-4 text-center text-purple-600 font-semibold">
                                    {{ $att->night ?? 0 }}
                                </td>

                                <!-- STATUS -->
                                <td class="p-4 text-center">

                                    <div class="flex items-center justify-center gap-2">

                                        <span class="px-3 py-1 text-xs rounded-full font-medium
                                            {{ $att->status=='present' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $att->status=='absent' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ $att->status=='leave' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                            {{ ucfirst($att->status) }}
                                        </span>

                                        <!-- SLIP -->
                                        <a href="{{ route('payroll.reportslip', $att->employee_id) }}"
                                           class="text-xs bg-gray-900 hover:bg-black text-white px-3 py-1 rounded-md transition">
                                            Slip
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center p-8 text-gray-400">
                                    No attendance records found
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</x-layout>