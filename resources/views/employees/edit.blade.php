<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <x-sidebar />

    <div class="flex-1 p-6">

        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                Edit Employee Details
            </h1>
            <p class="text-sm text-gray-500">
                Update employee record in HR system
            </p>
        </div>

        <!-- FORM CARD -->
        <div class="bg-white p-6 rounded-xl shadow max-w-3xl">
<form method="POST" action="{{ url('/employees/' . $employee->id) }}">
    @csrf
    @method('PUT')

                <!-- BASIC INFO -->
                <h2 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">
                    Basic Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

                    <div>
                        <label class="text-sm text-gray-600">Full Name</label>
                        <input type="text" name="name"
                            value="{{ $employee->name }}"
                            class="w-full border p-2 rounded mt-1">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">CNIC</label>
                        <input type="text" name="cnic"
                            value="{{ $employee->cnic }}"
                            class="w-full border p-2 rounded mt-1">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Department</label>
                        <input type="text" name="department"
                            value="{{ $employee->department }}"
                            class="w-full border p-2 rounded mt-1">
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Branch</label>
                        <input type="text" name="branch"
                            value="{{ $employee->branch }}"
                            class="w-full border p-2 rounded mt-1">
                    </div>

                </div>

                <!-- SALARY -->
                <h2 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">
                    Salary Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

                    <div>
                        <label class="text-sm text-gray-600">Basic Salary</label>
                        <input type="number" name="basic_salary"
                            value="{{ $employee->basic_salary }}"
                            class="w-full border p-2 rounded mt-1">
                    </div>

                </div>

                <!-- ACTION BUTTON -->
                <div class="text-right">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                        Update Employee
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

</x-layout>