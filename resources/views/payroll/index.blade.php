<x-layout>

<div class="flex bg-gray-100 min-h-screen">

    <!-- SIDEBAR -->
    <x-sidebar></x-sidebar>

   
    <div class="flex-1 p-6">

        <div class="mb-6">
        
            <h1 class="text-2xl font-bold text-gray-800">Monthly Payroll</h1>
            <p class="text-sm text-gray-500">Employee salary & attendance summary</p>
        </div>

        <div class="bg-white rounded-lg shadow border p-6">

            <!-- TOP BAR -->
            <div class="flex justify-between items-center mb-4">

                <input type="text"
                    placeholder="Search employee..."
                    class="w-1/3 px-4 py-2 border rounded focus:ring focus:ring-red-200">

                <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    + Generate Payroll
                </button>

            </div>

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
                            <th class="p-3 text-center text-green-600">Bonus</th>
                            <th class="p-3 text-center text-red-600">Advance</th>
                            <th class="p-3 text-center">Net Salary</th>
                            <th class="p-3 text-center">Salary Slip</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                     @foreach($payrolls as $p)
<tr class="hover:bg-gray-50 transition">

    <!-- Employee -->
    <td class="p-3 font-semibold text-gray-800">
        {{ $p->employee->name }}
    </td>

    <!-- Present -->
    <td class="p-3 text-center">
        <span class="px-2 py-1 bg-green-100 text-green-700 rounded">
            {{ $p->present }}
        </span>
    </td>

    <!-- Absent -->
    <td class="p-3 text-center">
        <span class="px-2 py-1 bg-red-100 text-red-700 rounded">
            {{ $p->absent }}
        </span>
    </td>

    <!-- Bonus -->
    <td class="p-3 text-center text-green-600 font-medium">
        {{ number_format($p->bonus,2) }}
    </td>

    <!-- Advance -->
    <td class="p-3 text-center text-red-600 font-medium">
        {{ number_format($p->advance,2) }}
    </td>

    <!-- Net Salary -->
    <td class="p-3 text-center font-bold text-blue-600">
        {{ number_format($p->net,2) }}
    </td>

    <!-- SLIP BUTTON (FIXED) -->
    <td class="p-3 text-center">
        <a href="/salary-slip/{{ $p->employee->id }}/{{ date('m') }}"
           class="bg-blue-500 text-white px-3 py-1 rounded text-xs">
            Slip
        </a>
    </td>

</tr>
@endforeach

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>

</x-layout>