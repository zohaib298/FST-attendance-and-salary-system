<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <x-sidebar />

    <main class="flex-1 p-8">

        <!-- HEADER -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Monthly Attendance Report
                </h1>

                @isset($employee)
                    <p class="text-sm text-blue-600 mt-1">
                        Employee: {{ $employee->name }}
                    </p>
                @endisset
            </div>

            <!-- 🔥 MONTH/YEAR FILTER -->
            <form method="GET" class="flex gap-2 items-center">

                <select name="month" class="border px-3 py-2 rounded text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0,0,0,$m,1)) }}
                        </option>
                    @endfor
                </select>

                <select name="year" class="border px-3 py-2 rounded text-sm">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>

                <button class="bg-black text-white px-4 py-2 rounded text-sm">
                    Filter
                </button>

            </form>

        </div>

        <!-- ================= TABLE ================= -->
        <div class="bg-white border rounded-lg overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="p-3 text-left">Employee</th>
                        <th class="p-3 text-center">Present</th>
                        <th class="p-3 text-center">Absent</th>
                        <th class="p-3 text-center">Leave</th>
                    </tr>
                </thead>

                <tbody>

                    {{-- ALL EMPLOYEES --}}
                    @isset($employees)

                        @foreach($employees as $emp)

                            @php
                                $empAtt = $attendances[$emp->id] ?? collect();

                                $present = $empAtt->where('status', 'present')->count();
                                $absent  = $empAtt->where('status', 'absent')->count();
                                $leave   = $empAtt->where('status', 'leave')->count();
                            @endphp

                            <tr class="border-t">

                                <td class="p-3 font-semibold">
                                    {{ $emp->name }}
                                </td>

                                <td class="p-3 text-center text-green-600">
                                    {{ $present }}
                                </td>

                                <td class="p-3 text-center text-red-600">
                                    {{ $absent }}
                                </td>

                                <td class="p-3 text-center text-yellow-600">
                                    {{ $leave }}
                                </td>

                            </tr>

                        @endforeach

                    @endisset


                    {{-- SINGLE EMPLOYEE --}}
                    @isset($employee)

                        @php
                            $present = $attendances->where('status', 'present')->count();
                            $absent  = $attendances->where('status', 'absent')->count();
                            $leave   = $attendances->where('status', 'leave')->count();
                        @endphp

                        <tr class="border-t">

                            <td class="p-3 font-semibold">
                                {{ $employee->name }}
                            </td>

                            <td class="p-3 text-center text-green-600">
                                {{ $present }}
                            </td>

                            <td class="p-3 text-center text-red-600">
                                {{ $absent }}
                            </td>

                            <td class="p-3 text-center text-yellow-600">
                                {{ $leave }}
                            </td>

                        </tr>

                    @endisset


                    {{-- EMPTY --}}
                    @if(!isset($employees) && !isset($employee))
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">
                                No data found
                            </td>
                        </tr>
                    @endif

                </tbody>

            </table>

        </div>

    </main>

</div>

</x-layout>