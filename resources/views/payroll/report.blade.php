<x-layout>
<div class="flex min-h-screen bg-slate-100">

    <x-sidebar />

    <div class="flex-1">

        <!-- TOP BAR -->
        <div class="bg-white border-b sticky top-0 z-10">
            <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                <div>
                    <h1 class="text-xl font-semibold text-gray-800">
                        Attendance Report
                    </h1>
                    <p class="text-xs text-gray-500">
                        Filter employee attendance by month & status
                    </p>
                </div>

                <!-- QUICK ACTION -->
                <a href="{{ route('attendance.report') }}"
                   class="text-sm bg-slate-900 text-white px-4 py-2 rounded-lg hover:bg-black">
                    Reset
                </a>

            </div>
        </div>

        <div class="p-6 space-y-6">

            <!-- FILTER BAR -->
            <form method="GET"
                  class="bg-white border rounded-xl p-4 flex flex-col md:flex-row gap-3 md:items-end">

                <!-- Employee -->
                <div class="w-full md:w-1/4">
                    <label class="text-xs text-gray-500">Employee</label>
                    <select name="employee_id"
                        class="w-full mt-1 border rounded-lg px-3 py-2 text-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div class="w-full md:w-1/4">
                    <label class="text-xs text-gray-500">Search</label>
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Name, department..."
                        class="w-full mt-1 border rounded-lg px-3 py-2 text-sm">
                </div>

                <!-- Month -->
                <div class="w-full md:w-1/4">
                    <label class="text-xs text-gray-500">Month</label>
                    <input type="month"
                        name="month"
                        value="{{ request('month') }}"
                        class="w-full mt-1 border rounded-lg px-3 py-2 text-sm">
                </div>

                <!-- BUTTON -->
                <div class="w-full md:w-1/4">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2.5 rounded-lg">
                        Apply Filters
                    </button>
                </div>

            </form>

            <!-- SUMMARY CARDS -->
            @if($attendances->count() > 0)

                @php
                    $present = $attendances->where('status','present')->count();
                    $absent  = $attendances->where('status','absent')->count();
                    $leave   = $attendances->where('status','leave')->count();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div class="bg-white border rounded-xl p-5">
                        <p class="text-xs text-gray-500">Present</p>
                        <p class="text-2xl font-bold text-green-600">{{ $present }}</p>
                    </div>

                    <div class="bg-white border rounded-xl p-5">
                        <p class="text-xs text-gray-500">Absent</p>
                        <p class="text-2xl font-bold text-red-500">{{ $absent }}</p>
                    </div>

                    <div class="bg-white border rounded-xl p-5">
                        <p class="text-xs text-gray-500">Leave</p>
                        <p class="text-2xl font-bold text-yellow-500">{{ $leave }}</p>
                    </div>

                </div>

            @endif

            <!-- TABLE -->
            <div class="bg-white border rounded-xl overflow-hidden">

                <table class="w-full text-sm">

                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                        <tr>
                            <th class="p-4 text-left">Employee</th>
                            <th class="p-4 text-left">Date</th>
                            <th class="p-4 text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($attendances as $att)

                            <tr class="border-t hover:bg-slate-50">

                                <td class="p-4 font-medium text-gray-800">
                                    {{ $att->employee->name ?? 'N/A' }}
                                </td>

                                <td class="p-4 text-gray-600">
                                    {{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}
                                </td>

                                <td class="p-4">

                                    @php
                                        $status = $att->status;
                                    @endphp

                                    <span class="px-3 py-1 text-xs rounded-full
                                        {{ $status=='present' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $status=='absent' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $status=='leave' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    ">
                                        {{ ucfirst($status) }}
                                    </span>

                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="3" class="text-center p-10 text-gray-400">
                                    No records found
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