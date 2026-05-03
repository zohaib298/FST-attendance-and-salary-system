<x-layout>

<div class="flex bg-gray-100 min-h-screen">

    <x-sidebar></x-sidebar>

    <div class="flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Monthly Payroll</h1>
            <p class="text-sm text-gray-500">Employee salary & attendance summary</p>
        </div>

        <div class="bg-white rounded-lg shadow border p-6">

            <form method="GET" class="flex gap-2 items-center mb-4">
                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search employee..."
                    class="px-4 py-2 border rounded w-1/3 focus:ring focus:ring-blue-200">

                <button type="submit"
                    class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-black flex items-center gap-2">
                    <i class="bi bi-search"></i>
                    Search
                </button>
            </form>

            <h2 class="text-lg font-semibold border-b pb-2 mb-4">
                Payroll Sheet
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="p-3 text-left">Employee</th>
                            <th class="p-3 text-center">Present</th>
                            <th class="p-3 text-center">Absent</th>
                            <th class="p-3 text-center">Leave</th>
                            <th class="p-3 text-center text-green-600">Bonus</th>
                            <th class="p-3 text-center text-red-600">Advance</th>
                            <th class="p-3 text-center text-purple-600">Running Salary</th>
                            <th class="p-3 text-center">Net Salary</th>
                            <th class="p-3 text-center">Salary Slip</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                        @forelse($payrolls as $p)
                        <tr class="hover:bg-gray-50">

                            <td class="p-3 font-semibold text-gray-800">
                                {{ $p->employee->name }}
                            </td>

                            <td class="p-3 text-center">
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded">
                                    {{ $p->present }}
                                </span>
                            </td>

                            <td class="p-3 text-center">
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded">
                                    {{ $p->absent }}
                                </span>
                            </td>

                            <td class="p-3 text-center">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded">
                                    {{ $p->leave }}
                                </span>
                            </td>

                            <td class="p-3 text-center text-green-600 font-medium">
                                {{ number_format($p->bonus ?? 0, 0) }}
                            </td>

                            <td class="p-3 text-center text-red-600 font-medium">
                                {{ number_format($p->advance ?? 0, 0) }}
                            </td>

                            <!-- ✅ RUNNING SALARY -->
                            <td class="p-3 text-center font-bold text-purple-600">
                                {{ number_format($p->running, 0) }}
                            </td>

                            <td class="p-3 text-center font-bold text-blue-600">
                                {{ number_format($p->net, 0) }}
                            </td>

                            <td class="p-3 text-center">
                                <a href="/salary-slip/{{ $p->employee->id }}/{{ $month }}"
                                   class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600">
                                    View Slip
                                </a>
                            </td>

                        </tr>

                        @empty
                        <tr>
                            <td colspan="9" class="text-center p-4 text-gray-500">
                                No employees found
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