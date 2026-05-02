<x-layout>

<div class="min-h-screen bg-slate-100 py-10">

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow p-8">

        <!-- HEADER -->
        <div class="text-center border-b pb-4 mb-6">
            <h1 class="text-2xl font-bold">Attendance Slip</h1>
            <p class="text-xs text-gray-500">
                {{ $month }} / {{ $year }}
            </p>
        </div>

        <!-- EMPLOYEE -->
        <div class="grid grid-cols-2 text-sm mb-6">
            <div>
                <p><b>Name:</b> {{ $employee->name }}</p>
                <p><b>CNIC:</b> {{ $employee->cnic }}</p>
                <p><b>Department:</b> {{ $employee->department }}</p>
            </div>

            <div class="text-right">
                <p><b>Basic:</b> {{ number_format($basic,0) }}</p>
                <p><b>Net Salary:</b> <span class="text-green-600 font-bold">{{ number_format($net,0) }}</span></p>
            </div>
        </div>

        <!-- SUMMARY -->
        <div class="border rounded p-4 text-sm mb-6">
            <div class="grid grid-cols-3 gap-3">
                <p>Present: <b>{{ $present }}</b></p>
                <p>Absent: <b class="text-red-600">{{ $absent }}</b></p>
                <p>Leave: <b class="text-yellow-600">{{ $leave }}</b></p>
            </div>
        </div>

        <!-- DAILY ATTENDANCE (IMPORTANT PART) -->
        <div class="border rounded p-4">

            <h2 class="font-semibold mb-3">Daily Attendance</h2>

            <div class="max-h-72 overflow-y-auto border rounded">

                <table class="w-full text-sm">

                    <thead class="bg-gray-100 text-xs">
                        <tr>
                            <th class="p-2 text-left">Date</th>
                            <th class="p-2 text-left">In</th>
                            <th class="p-2 text-left">Out</th>
                            <th class="p-2 text-center">Status</th>
                            <th class="p-2 text-center">Late</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($attendances as $att)

                            @php
                                $late = 0;

                                $in = $att->check_in
                                    ? \Carbon\Carbon::parse($att->date.' '.$att->check_in)
                                    : null;

                                $office = \Carbon\Carbon::parse($att->date.' 09:30:00');

                                if($in && $in->gt($office)){
                                    $late = 1;
                                }
                            @endphp

                            <tr class="border-t">

                                <td class="p-2">
                                    {{ \Carbon\Carbon::parse($att->date)->format('d M') }}
                                </td>

                                <td class="p-2">
                                    {{ $att->check_in ?? '--' }}
                                </td>

                                <td class="p-2">
                                    {{ $att->check_out ?? '--' }}
                                </td>

                                <td class="p-2 text-center">
                                    <span class="text-xs px-2 py-1 rounded
                                        {{ $att->status=='present' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $att->status=='absent' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $att->status=='leave' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                        {{ ucfirst($att->status) }}
                                    </span>
                                </td>

                                <td class="p-2 text-center text-red-600">
                                    {{ $late }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

        <!-- PRINT -->
        <div class="mt-6 text-center">
            <button onclick="window.print()"
                class="bg-black text-white px-5 py-2 rounded">
                Print
            </button>
        </div>

    </div>

</div>

</x-layout>