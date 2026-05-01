<x-layout>
<div class="flex min-h-screen bg-gray-50">

    <!-- Sidebar -->
    <x-sidebar />

    <!-- Main Content -->
    <div class="flex-1 p-8 space-y-6">

        <!-- HEADER -->
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Monthly Attendance Report
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                View employee attendance by month
            </p>
        </div>

        <!-- FILTER CARD -->
        <div class="bg-white border rounded-xl shadow-sm p-6">

            <form method="GET" action="{{ route('attendance.report') }}"
                  class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Employee -->
                <div>
                    <label class="text-xs text-gray-500">Employee</label>
                    <select name="employee_id"
                        class="w-full mt-1 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-300 outline-none">
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Month -->
                <div>
                    <label class="text-xs text-gray-500">Month</label>
                    <input type="month" name="month"
                        value="{{ request('month') }}"
                        class="w-full mt-1 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-300 outline-none">
                </div>

                <!-- Button -->
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full bg-gray-900 hover:bg-black text-white text-sm py-2.5 rounded-lg transition">
                        Generate Report
                    </button>
                </div>

            </form>
        </div>

        <!-- SUMMARY -->
        @if($attendances->isNotEmpty())

            @php
                $present = $attendances->where('status','present')->count();
                $absent  = $attendances->where('status','absent')->count();
                $leave   = $attendances->where('status','leave')->count();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="bg-white border rounded-xl shadow-sm p-6 text-center">
                    <p class="text-gray-500 text-xs">Present</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $present }}</p>
                </div>

                <div class="bg-white border rounded-xl shadow-sm p-6 text-center">
                    <p class="text-gray-500 text-xs">Absent</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $absent }}</p>
                </div>

                <div class="bg-white border rounded-xl shadow-sm p-6 text-center">
                    <p class="text-gray-500 text-xs">Leave</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $leave }}</p>
                </div>

            </div>
        @endif

        <!-- TABLE -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">

            <table class="w-full text-sm">

                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="p-4 text-left">Date</th>
                        <th class="p-4 text-left">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @forelse($attendances as $att)
                        <tr class="hover:bg-gray-50 transition">

                            <td class="p-4 text-gray-700">
                                {{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}
                            </td>

                            <td class="p-4">

                                @if($att->status == 'present')
                                    <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                        Present
                                    </span>

                                @elseif($att->status == 'absent')
                                    <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700">
                                        Absent
                                    </span>

                                @else
                                    <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                                        Leave
                                    </span>
                                @endif

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="p-8 text-center text-gray-400">
                                No attendance records found
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>
</div>
</x-layout>