<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <x-sidebar></x-sidebar>

    <main class="flex-1 p-8">

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-800">HR Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">
                Manage employees and daily attendance
            </p>
        </div>

        <div class="bg-white border rounded-lg shadow-sm mb-8">

            <div class="px-6 py-4 border-b">
                <h2 class="text-md font-semibold text-gray-700">
                    Add New Employee
                </h2>
            </div>

            <form method="POST" action="{{ route('employees.store') }}"
                  class="p-6 grid grid-cols-1 md:grid-cols-5 gap-4">

                @csrf

                <div>
                    <label class="text-xs text-gray-500">Name</label>
                    <input type="text" name="name"
                        class="w-full border rounded px-3 py-2 mt-1 focus:outline-none focus:ring-1 focus:ring-gray-400"
                        required>
                </div>

                <div>
                    <label class="text-xs text-gray-500">CNIC</label>
                    <input type="text" name="cnic"
                        class="w-full border rounded px-3 py-2 mt-1 focus:outline-none focus:ring-1 focus:ring-gray-400">
                </div>

                <div>
                    <label class="text-xs text-gray-500">Department</label>
                    <input type="text" name="department"
                        class="w-full border rounded px-3 py-2 mt-1 focus:outline-none focus:ring-1 focus:ring-gray-400"
                        required>
                </div>

                <div>
                    <label class="text-xs text-gray-500">Branch</label>
                    <select name="branch"
                        class="w-full border rounded px-3 py-2 mt-1 focus:outline-none focus:ring-1 focus:ring-gray-400"
                        required>
                        <option value="">Select</option>
                        <option>Lahore</option>
                        <option>Karachi</option>
                        <option>Rawalpindi</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs text-gray-500">Basic Salary</label>
                    <input type="number" name="basic_salary"
                        class="w-full border rounded px-3 py-2 mt-1 focus:outline-none focus:ring-1 focus:ring-gray-400"
                        required>
                </div>

                <div class="md:col-span-5 flex justify-end mt-2">
                    <button class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-black transition">
                        Save Employee
                    </button>
                </div>

            </form>

        </div>


        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6 text-sm">
                {{ session('success') }}
            </div>
        @endif

        
        <div class="bg-white border rounded-lg shadow-sm">

            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-md font-semibold text-gray-700">
                    Today's Attendance
                </h2>

                <span class="text-xs text-gray-500">
                    {{ date('d M Y') }}
                </span>
            </div>

            <form method="POST" action="{{ route('attendance.store') }}">
                @csrf

                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="p-3 text-left">Employee</th>
                                <th class="p-3 text-center">Present</th>
                                <th class="p-3 text-center">Absent</th>
                                <th class="p-3 text-center">Leave</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">

                            @foreach($employees as $emp)
                            <tr class="hover:bg-gray-50">

                                <td class="p-3 font-medium text-gray-800">
                                    {{ $emp->name }}
                                </td>

                                <td class="text-center">
                                    <input type="radio"
                                        name="attendance[{{ $emp->id }}]"
                                        value="present" checked>
                                </td>

                                <td class="text-center">
                                    <input type="radio"
                                        name="attendance[{{ $emp->id }}]"
                                        value="absent">
                                </td>

                                <td class="text-center">
                                    <input type="radio"
                                        name="attendance[{{ $emp->id }}]"
                                        value="leave">
                                </td>

                            </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="p-6 border-t flex justify-end">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                        Save Attendance
                    </button>
                </div>

            </form>

        </div>

    </main>

</div>

</x-layout>