<x-layout>

<div class="flex min-h-screen bg-gray-100">

    <x-sidebar />

    <div class="flex-1 p-10">

        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg p-8">

            <!-- HEADER -->
            <div class="text-center border-b pb-4 mb-6">
                <h2 class="text-3xl font-bold">Salary Slip</h2>
                <p class="text-sm text-gray-500">Monthly Payroll Report</p>
            </div>

            <!-- DETAILS -->
            <div class="space-y-2">
                <p><strong>Name:</strong> {{ $employee->name }}</p>
                <p><strong>Month:</strong> {{ $month }}</p>
            </div>

            <hr class="my-6">

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>Basic Salary</span>
                    <span>{{ number_format($basic, 0) }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Present Days</span>
                    <span>{{ $presentDays }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Absent Days</span>
                    <span>{{ $absentDays }}</span>
                </div>
            </div>

            <hr class="my-6">

            <div class="flex justify-between bg-gray-100 p-4 rounded">
                <span class="font-bold">Final Salary</span>
                <span class="text-green-600 font-bold">
                    {{ number_format($finalSalary, 0) }}
                </span>
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

    x-sidebar,
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