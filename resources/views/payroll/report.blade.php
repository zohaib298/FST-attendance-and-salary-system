<x-layout>
<div class="flex min-h-screen bg-gray-50">

    <!-- Sidebar -->
    <x-sidebar />

    <!-- Main Content -->
    <div class="flex-1 p-10">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-800">
                Monthly Attendance Report
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                View employee attendance by month
            </p>
        </div>

        <!-- Filter -->
        <div class="bg-white border rounded-lg p-6 mb-8">

            <form method="GET" action="{{ route('attendance.report') }}"
                  class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Employee -->
                <div>
                    <label class="text-xs text-gray-500">Employee</label>
                    <select name="employee_id"
                        class="w-full mt-1 border rounded-md px-3 py-2 text-sm">
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
                        class="w-full mt-1 border rounded-md px-3 py-2 text-sm">
                </div>

                <!-- Button -->
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full bg-gray-900 text-white text-sm py-2.5 rounded-md">
                        Generate Report
                    </button>
                </div>

            </form>
        </div>

        <!-- Summary -->
        @if($attendances->isNotEmpty())

            @php
                $present = $attendances->where('status','present')->count();
                $absent  = $attendances->where('status','absent')->count();
                $leave   = $attendances->where('status','leave')->count();
            @endphp

            <div class="grid grid-cols-3 gap-4 mb-8">

                <div class="bg-white border rounded-lg p-5 text-center">
                    <p class="text-gray-500 text-xs">Present</p>
                    <p class="text-xl font-semibold">{{ $present }}</p>
                </div>

                <div class="bg-white border rounded-lg p-5 text-center">
                    <p class="text-gray-500 text-xs">Absent</p>
                    <p class="text-xl font-semibold">{{ $absent }}</p>
                </div>

                <div class="bg-white border rounded-lg p-5 text-center">
                    <p class="text-gray-500 text-xs">Leave</p>
                    <p class="text-xl font-semibold">{{ $leave }}</p>
                </div>

            </div>
        @endif

        <!-- Table -->
        <div class="bg-white border rounded-lg overflow-hidden">

            <table class="w-full text-sm">

                <thead class="bg-gray-100 text-gray-600">
                    <tr>
                        <th class="p-4 text-left">Date</th>
                        <th class="p-4 text-left">Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($attendances as $att)
                        <tr class="border-t hover:bg-gray-50">

                            <td class="p-4 text-gray-700">
                                {{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}
                            </td>

                            <td class="p-4 text-gray-600">
                                {{ ucfirst($att->status) }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="p-6 text-center text-gray-400">
                                No records found
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>
</div>
</x-layout>