<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <x-sidebar></x-sidebar>

    <div class="flex-1 p-8">

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Employee Details</h1>
            <p class="text-sm text-gray-500">Fill employee details, allowances & deductions</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">

            <form method="POST" action="{{ route('employees.store') }}"
                  class="grid grid-cols-1 md:grid-cols-3 gap-5">

                @csrf

                <!-- BASIC INFO -->
                <div class="md:col-span-3">
                    <h2 class="text-lg font-bold text-gray-700 border-b pb-2">
                        Basic Information
                    </h2>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Full Name</label>
                    <input type="text" name="name" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">CNIC</label>
                    <input type="text" name="cnic" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Department</label>
                    <input type="text" name="department" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Branch</label>
                    <input type="text" name="branch" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Basic Salary</label>
                    <input type="number" name="basic_salary" class="border rounded-lg p-3 w-full">
                </div>

                <!-- ALLOWANCES -->
                <div class="md:col-span-3 mt-6">
                    <h2 class="text-lg font-bold text-green-600 border-b pb-2">
                        Allowances (Extra Benefits)
                    </h2>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Bike Allowance</label>
                    <input type="number" name="bike_allowance" value="0" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Mobile Allowance</label>
                    <input type="number" name="mobile_allowance" value="0" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Overtime Rate</label>
                    <input type="number" name="overtime_rate" value="0" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Commission</label>
                    <input type="number" name="commission" value="0" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Other Allowance</label>
                    <input type="number" name="other_allowance" value="0" class="border rounded-lg p-3 w-full">
                </div>

                <!-- DEDUCTIONS -->
                <div class="md:col-span-3 mt-6">
                    <h2 class="text-lg font-bold text-red-600 border-b pb-2">
                        Deductions (Salary Cuts)
                    </h2>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Late Deduction</label>
                    <input type="number" name="late_deduction" value="0" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Absent Deduction</label>
                    <input type="number" name="absent_deduction" value="0" class="border rounded-lg p-3 w-full">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Allowed Leaves</label>
                    <input type="number" name="allowed_leaves" value="0" class="border rounded-lg p-3 w-full">
                </div>

                <!-- BUTTON -->
                <div class="md:col-span-3 flex justify-end mt-6">
                    <button class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700">
                        Save Employee
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

</x-layout>