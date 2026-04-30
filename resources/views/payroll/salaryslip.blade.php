<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <x-sidebar />
    </div>

    <div class="flex-1 p-10">

        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg p-8">

            <!-- COMPANY HEADER -->
            <div class="text-center border-b pb-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800">FST HR SYSTEM</h2>
                <p class="text-sm text-gray-500">Official Salary Slip</p>
            </div>

            <!-- EMPLOYEE INFO -->
            <div class="grid grid-cols-2 gap-2 text-sm">
                <p><strong>Name:</strong> {{ $employee->name }}</p>
                <p><strong>Month:</strong> {{ $month }}</p>
                <p><strong>Employee ID:</strong> {{ $employee->id }}</p>
            </div>

            <hr class="my-6">

            <!-- SALARY BREAKDOWN -->
            <div class="space-y-2 text-sm">

                <div class="flex justify-between">
                    <span>Basic Salary</span>
                    <span>{{ number_format($basic,0) }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Present Days</span>
                    <span>{{ $presentDays }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Absent Days</span>
                    <span>{{ $absentDays }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Per Day Rate</span>
                    <span>{{ number_format($basic/30,0) }}</span>
                </div>

            </div>

            <hr class="my-6">

            <!-- FINAL SALARY BOX -->
            <div class="bg-green-100 p-4 rounded flex justify-between">
                <span class="font-bold text-gray-800">Net Salary</span>
                <span class="text-green-700 font-bold text-lg">
                    {{ number_format($finalSalary,0) }}
                </span>
            </div>

            <!-- SIGNATURE -->
            <div class="mt-10 flex justify-between text-sm text-gray-500">
                <div>
                    <p>Employee Signature</p>
                    <div class="border-t w-32 mt-6"></div>
                </div>

                <div>
                    <p>Authorized By</p>
                    <div class="border-t w-32 mt-6"></div>
                </div>
            </div>

            <!-- PRINT BUTTON -->
            <div class="mt-6 text-right">
                <button onclick="window.print()"
                    class="bg-black text-white px-5 py-2 rounded">
                    Print Slip
                </button>
            </div>

        </div>

    </div>

</div>

<!-- PRINT CSS -->
<style>
@media print {

    .sidebar {
        display: none !important;
    }

    button {
        display: none !important;
    }

    body {
        background: white !important;
    }

    .shadow-lg {
        box-shadow: none !important;
    }

    .bg-gray-100 {
        background: white !important;
    }

    .p-10 {
        padding: 0 !important;
    }

}
</style>

</x-layout>